<?php

require_once APPLICATION_PATH.'/vendor/autoload.php';
require_once APPLICATION_PATH.'/lib/constants.php';
require_once APPLICATION_PATH.'/lib/DataHandler.php';
require_once APPLICATION_PATH.'/lib/CacheHandler.php';

/**
 * ClientAddon
 */
class ClientAddon
{
    const HOOK_DIR = 'hooks'; // hooks directory name
    const CONFIG_DIR = 'config'; // config directory name
    const CONFIG_FILENAME = 'config.php'; // config file name

    const HTTP_GET_METHOD = 'GET'; // http get method name
    const HTTP_POST_METHOD = 'POST'; // http post method name
	const URI_TEMPLATE = '%s://%s/%s/%s/%s/'; // URI format

	const CACHE_PARAMETER = 'cache'; // cache parameter name
	const CACHE_ENABLED = 'enabled'; // cache enabled value
	const CACHE_DISABLED = 'disabled'; // cache disabled value
	const CACHE_OVERWRITE = 'overwrite'; // cache overwrite value

	private $_routeArray;		// contains the routing configuration array
	private $_connectionArray;	// contains the connection parameters configuration array

	private $_remoteWSAlias;	// contains the called alias of the remote web service

	private $_remoteWSName;		// contains the name of the remote web service to call
	private $_hook;				// contains the name of the eventual hook to call
	private $_loginRequired;	// true: the user must be logged to perform this call
								// false: the user should not be logged to perform this call

	private $_cache;				// contains the desired cache mode
	private $_httpMethod;			// http method that was used to call this server
									// it will be the same method that will be used to call the remote web service
	private $_callParametersArray;	// contains the parameters to give to the remote web service

	private $_callResult; // will contains the result of the called remote web service

    /**
     * Object initialization, takes as parameters $_GET and $_POST
     */
    public function __construct($httpGet, $httpPost)
    {
		$this->_setPropertiesDefault(); // properties initialization

        $this->_loadConfig(); // loads the configurations

        $this->_setHTTPMethod($httpGet, $httpPost); // finds out what's the http method used to call this server

        $this->_parseParameters($httpGet, $httpPost); // parse and store parameters from $_GET and $_POST
    }

    // --------------------------------------------------------------------------------------------
    // Public methods

    /**
     *
     */
    public function call()
    {
		$this->_loadHooks(); //

		ClientAddon\DataHandler::startSession(); //
		ClientAddon\CacheHandler::startSession(); //

		//
		if (!$this->_checkRequiredParameters())
		{
			$this->_error(MISSING_REQUIRED_PARAMETERS);
		}
		//
		elseif (!$this->_checkLogin())
		{
			$this->_error(LOGIN_REQUIRED);
		}
		else
		{
			$response = null;

			// If the cache is enabled try to search in the cache
			if ($this->_cache == CACHE_ENABLED)
			{
				$response = ClientAddon\CacheHandler::get($this->_remoteWSName);
			}
			else // otherwise clean the cache for this call
			{
				ClientAddon\CacheHandler::unset($this->_remoteWSName);
			}

			// If nothing was found in the cache then call the server
			if ($response == null)
			{
		        $uri = $this->_generateURI(); // URI of the remote web service

		        try
		        {
		            if ($this->_isGET()) // if the call was performed using a HTTP GET
		            {
		                $response = $this->_callGET($uri);
		            }
		            else // if the call was performed using a HTTP POST
		            {
		                $response = $this->_callPOST($uri);
		            }
				}
		        catch (\Httpful\Exception\ConnectionErrorException $cee) // connection error
		        {
					$this->_error(CONNECTION_ERROR);
		        }
				// otherwise another error has occurred, most likely the result of the remote web service is not json
				// so a parse error is raised
		        catch (Exception $e)
		        {
					$this->_error(JSON_PARSE_ERROR);
		        }
			}

			// If the cache is enabled or should be overwritten, store the result
			if ($this->_cache == CACHE_ENABLED || $this->_cache == CACHE_OVERWRITE)
			{
				ClientAddon\CacheHandler::set($this->_remoteWSName, $response);
			}

            $this->_checkResponse($response); // check t

			$this->_setDataLogin();
		}
    }

	/**
	 *
	 */
	private function _setDataLogin()
	{
		if ($this->_remoteWSAlias == LOCAL_LOGIN_CALL && $this->_callResult->{ClientAddon\DataHandler::CODE} == SUCCESS)
		{
			if (isset($this->_routeArray[LOCAL_LOGIN_CALL])
				&& isset($this->_routeArray[LOCAL_LOGIN_CALL][USERNAME])
				&& isset($this->_callParametersArray[USERNAME]))
			{
				ClientAddon\DataHandler::set($this->_routeArray[LOCAL_LOGIN_CALL][USERNAME], $this->_callParametersArray[USERNAME]);
			}
		}
	}

	/**
	 *
	 */
	private function _getDataLogin()
	{
		if (isset($this->_routeArray[LOCAL_LOGIN_CALL]) && isset($this->_routeArray[LOCAL_LOGIN_CALL][USERNAME]))
		{
			return ClientAddon\DataHandler::get($this->_routeArray[LOCAL_LOGIN_CALL][USERNAME]);
		}
	}

	/**
	 * Set the header content type and print out in json format
	 */
	public function printResults()
	{
		header('Content-Type: application/json');

		echo json_encode($this->_callResult); // encode the result of the remote web service to json
	}

    // --------------------------------------------------------------------------------------------
    // Private methods

	/**
     * initialization of the properties of this object
     */
	private function _setPropertiesDefault()
	{
		$this->_routeArray = null;
		$this->_connectionArray = null;

		$this->_remoteWSAlias = null;

		$this->_remoteWSName = null;
		$this->_hook = null;
		$this->_loginRequired = true; // by default to perform a call the user must be logged in

		$this->_cache = CACHE_DISABLED; // by default caching is not enabled
		$this->_httpMethod = null;

		$this->_callParametersArray = array();

		// The default result of the remote web service is an error
		$this->_callResult = ClientAddon\DataHandler::error(ADDON_ERROR);
	}

    /**
     * Loads the config file present in the config directory and sets the properties _routeArray and _connectionArray
     */
    private function _loadConfig()
    {
        require_once APPLICATION_PATH.'/'.ClientAddon::CONFIG_DIR.'/'.ClientAddon::CONFIG_FILENAME;

		$this->_routeArray = $route;
		$this->_connectionArray = $connection[$activeConnection];
    }

    /**
     * Finds out what's the http method used to call this server and set the properties _httpMethod
     */
    private function _setHTTPMethod($get, $post)
    {
        if (isset($get) && count($get) > 0) //
        {
            $this->_httpMethod = ClientAddon::HTTP_GET_METHOD;
        }
        elseif (isset($post) && count($post) > 0) //
        {
            $this->_httpMethod = ClientAddon::HTTP_POST_METHOD;
        }
    }

    /**
     *
     */
    private function _parseParameters($httpGet, $httpPost)
    {
        if ($this->_isGET()) // if the call was performed using a HTTP GET
        {
            $httpParameters = $httpGet;
        }
        else // if the call was performed using a HTTP POST
        {
            $httpParameters = $httpPost;
        }

        foreach ($httpParameters as $parameterName => $parameterValue)
        {
			// If the parameter is the name of the remote web service
            if ($parameterName == REMOTE_WS)
            {
                if (array_key_exists($parameterValue, $this->_routeArray))
                {
					$this->_remoteWSAlias = $parameterValue; // store the called alias of the remote web service
					$routeConfigEntry = $this->_routeArray[$this->_remoteWSAlias]; // configuration entry for this web service

					// If $routeConfigEntry contains an array for this remote web service alias
                    if (is_array($routeConfigEntry))
                    {
						// If is set the name of the remote web service store it into property _remoteWSName
                        if (isset($routeConfigEntry[REMOTE_WS]))
                        {
                            $this->_remoteWSName = $routeConfigEntry[REMOTE_WS];
                        }
						// If is set the name of the hook to be called later, store it into property _hook
                        if (isset($routeConfigEntry[HOOK]))
                        {
                            $this->_hook = $routeConfigEntry[HOOK];
                        }
						// If is set that the login is required for this call, store it into property _loginRequired
						if (isset($routeConfigEntry[LOGIN_REQUIRED]))
                        {
                            $this->_loginRequired = $routeConfigEntry[LOGIN_REQUIRED];
                        }
                    }
					// Otherwise it contains only the name of the remote web service
                    else
                    {
                        $this->_remoteWSName = $routeConfigEntry;
                    }
                }
            }
			// If the parameter is used to set the cache type
            elseif ($parameterName == ClientAddon::CACHE_PARAMETER)
            {
				$this->_cache = CACHE_DISABLED; // By default caching is not enabled
				// If it contains a valid value then set the property
				if ($parameterValue == CACHE_ENABLED || $parameterValue == CACHE_DISABLED || $parameterValue == CACHE_OVERWRITE)
				{
					$this->_cache = $parameterValue;
				}
            }
			// Otherwise is a parameter to give to the remote web service
            else
            {
                $this->_callParametersArray[$parameterName] = $parameterValue;
            }
        }
    }

    /**
     *
     */
    private function _isGET()
    {
        return $this->_httpMethod == ClientAddon::HTTP_GET_METHOD;
    }

    /**
     *
     */
    private function _isPOST()
    {
        return $this->_httpMethod == ClientAddon::HTTP_POST_METHOD;
    }

	/**
     *
     */
    private function _checkRequiredParameters()
    {
		$checkRequiredParameters = false;

        if ($this->_remoteWSName != '')
        {
			$checkRequiredParameters = true;
        }

		return $checkRequiredParameters;
    }

	/**
	 * _checkLogin - Checks if
	 */
	private function _checkLogin()
	{
		$checkLogin = false; // by

		//
		if ($this->_remoteWSAlias == LOCAL_LOGIN_CALL)
		{
			$this->_cache = CACHE_ENABLED; // store the result in the cache
			$this->_loginRequired = false; //
		}

		//
		if ($this->_loginRequired == false)
		{
			$checkLogin = true;
		}

		//
		if ($this->_getDataLogin() != null)
		{
			$checkLogin = true;
		}

		return $checkLogin;
	}

    /**
     * Generate the URI to call the remote web service
     */
    private function _generateURI()
    {
        $uri = sprintf(
            ClientAddon::URI_TEMPLATE,
            $this->_connectionArray[PROTOCOL],
            $this->_connectionArray[HOST],
            $this->_connectionArray[PATH],
            $this->_connectionArray[ROUTER],
            $this->_connectionArray[WS_PATH]
        ).$this->_remoteWSName;

		// If the call was performed using a HTTP GET then append the query string to the URI
        if ($this->_isGET())
        {
			$queryString = '';

			// Create the query string
			foreach ($this->_callParametersArray as $name => $value)
			{
				$queryString .= ($queryString == '' ? '?' : '&').$name.'='.$value;
			}

            $uri .= $queryString;
        }

        return $uri;
    }

    /**
     *
     */
    private function _callGET($uri)
    {
        return \Httpful\Request::get($uri)
            ->authenticateWith($this->_connectionArray[USERNAME], $this->_connectionArray[PASSWORD])
            ->addHeader($this->_connectionArray[API_KEY_NAME], $this->_connectionArray[API_KEY_VALUE])
            ->expectsJson()
            ->send();
    }

    /**
     *
     */
    private function _callPOST($uri)
    {
        return \Httpful\Request::post($uri)
			->authenticateWith($this->_connectionArray[USERNAME], $this->_connectionArray[PASSWORD])
			->addHeader($this->_connectionArray[API_KEY_NAME], $this->_connectionArray[API_KEY_VALUE])
            ->expectsJson()
            ->body($this->_callParametersArray)
            ->sendsJson()
            ->send();
    }

    /**
     * Loads files that contain hooks from the hook directory
     */
    private function _loadHooks()
    {
        if (($files = glob(APPLICATION_PATH.'/'.ClientAddon::HOOK_DIR.'/*.php')) != false)
        {
            foreach ($files as $file)
            {
                require_once $file;
            }
        }
    }

    /**
     *
     */
    private function _checkResponse($response)
    {
        if (is_object($response))
        {
            if (isset($response->body) && is_object($response->body))
            {
                if (isset($response->body->status) && $response->body->status === false)
                {
					$this->_error(UNAUTHORIZED);
                }
                else
                {
                    if (isset($response->body->error) && $response->body->error === FHC_CORE_SUCCESS)
                    {
                        if (isset($response->body->retval))
                        {
                            if ((is_string($response->body->retval) && trim($response->body->retval) == '')
								|| (is_array($response->body->retval) && count($response->body->retval) == 0)
                                || (is_object($response->body->retval) && count((array)$response->body->retval) == 0))
                            {
								$this->_error(NO_DATA);
                            }
                            else
                            {
								//
                                if (!is_array($response->body->retval))
                                {
                                    $response->body->retval = array(0 => $response->body->retval);
                                }

                                $this->_success($response->body);
                            }
                        }
                        else
                        {
							$this->_error(INCOMPLETE_RESPONSE);
                        }
                    }
                    else
                    {
						$this->_error(FHC_CORE_ERROR, $response->body);
                    }
                }
            }
            else
            {
				$this->_error(NO_RESPONSE_BODY);
            }
        }
    }

	/**
	*
	*/
	private function _success($response = null)
	{
		$success = $this->_callHook(SUCCESS, $response);

		if ($success == null)
		{
			$success = ClientAddon\DataHandler::success($response->retval);
		}

		$this->_callResult = $success;
	}

	/**
	 *
	 */
	private function _error($code, $response = null)
	{
		$error = $this->_callHook($code, $response);

		if ($error == null)
		{
			$error = ClientAddon\DataHandler::error($code, $response);
		}

		$this->_callResult = $error;
	}

	/**
	 * Calls the hook (if it is valid) configurated in the route array for this call
	 * and return the result of the call
	 */
	private function _callHook($code, $response = null)
	{
		$callHook = null;

		if ((is_string($this->_hook) && function_exists($this->_hook))
			|| ($this->_hook != null && is_callable($this->_hook)))
		{
			$callHook = call_user_func($this->_hook, $code, $response);
		}

		return $callHook;
	}
}
