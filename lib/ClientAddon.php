<?php

require_once APPLICATION_PATH.'/vendor/autoload.php';
require_once APPLICATION_PATH.'/lib/constants.php';
require_once APPLICATION_PATH.'/lib/DataHandler.php';
require_once APPLICATION_PATH.'/lib/CacheHandler.php';

/**
 * Manages the communications between the web interface of the user and the FHC core
 * Taking care of the session of the user
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
	private $_httpMethod;			// http method used to call this server
	private $_callParametersArray;	// contains the parameters to give to the remote web service

	private $_callResult; 			// contains the result of the called remote web service

    /**
     * Object initialization, takes as parameters $_GET and $_POST
     */
    public function __construct()
    {
		$this->_setPropertiesDefault(); // properties initialization

        $this->_loadConfig(); // loads the configurations

        $this->_setHTTPMethod(); // finds out what's the http method used to call this server

        $this->_parseParameters(); // parse and store parameters from $_GET and $_POST
    }

    // --------------------------------------------------------------------------------------------
    // Public methods

    /**
     * Performs a call to a remote web service
	 * - Loads all the hooks
	 * - Initialize data session
	 * - Initialize cache
	 * - Checks the parameters from $_GET or $_POST
	 * - Checks if the user should be logged before calling the remote web service
	 * - Manages the cache depending on the cache mode:
	 *		- ENABLED: try to get data from cache, if cache is empty stores data from the
	 *				   remote web service (only on success) into the cache
	 *		- OVERWRITE: stores data from the remote web service (only on success) into the cache,
	 *					 optionally overwriting an already present data in the cache
	 *		- DISABLED: get data from the remote web service, the cache will be cleaned
	 * - Generates the URI of the remote web service
	 * - Performs a call to the remote web service
	 * - Checks the result from the remote web service (handles possible errors)
	 * - Optionally it stores login data
	 * - Finally stores the resuls into _callResult property
     */
    public function call()
    {
		$call = ClientAddon\DataHandler::error(FHC_CORE_ERROR); // by default returns an error

		$this->_loadHooks(); // Loads all hooks files present in the hook directory

		ClientAddon\DataHandler::startSession(); // Initialize data session
		ClientAddon\CacheHandler::startSession(); // Initialize cache

		// Checks if the required parameters are present and are valid
		if (!$this->_checkRequiredParameters())
		{
			$call = $this->_error(MISSING_REQUIRED_PARAMETERS);
		}
		// Checks if the login succeeded or not, if login is not required then is a success
		elseif (!$this->_checkLogin())
		{
			$call = $this->_error(LOGIN_REQUIRED);
		}
		else // otherwise perform the remote web service call
		{
			$response = null;

			// If the cache is enabled try to search in the cache
			if ($this->_cache == ClientAddon::CACHE_ENABLED)
			{
				$response = ClientAddon\CacheHandler::get($this->_remoteWSAlias);
			}
			else // otherwise clean the cache for this call
			{
				ClientAddon\CacheHandler::unset($this->_remoteWSAlias);
			}

			// If nothing was found in the cache then call the server
			if ($response == null)
			{
		        $uri = $this->_generateURI(); // URI of the remote web service

		        try
		        {
		            if ($this->_isGET()) // if the call was performed using a HTTP GET...
		            {
		                $response = $this->_callGET($uri); // ...calls the remote web service with the HTTP GET method
		            }
		            else // else if the call was performed using a HTTP POST...
		            {
		                $response = $this->_callPOST($uri); // ...calls the remote web service with the HTTP GET method
		            }
				}
		        catch (\Httpful\Exception\ConnectionErrorException $cee) // connection error
		        {
					$call = $this->_error(CONNECTION_ERROR);
		        }
				// otherwise another error has occurred, most likely the result of the
				// remote web service is not json so a parse error is raised
		        catch (Exception $e)
		        {
					$call = $this->_error(JSON_PARSE_ERROR);
		        }
			}

			// If data are not retrived from the cache
			if ($this->_cache != ClientAddon::CACHE_ENABLED)
			{
				// Checks the response of the remote web service and handles possible errors
				// Eventually here is also called a hook, so the data could have been manipulated
				$call = $this->_checkResponse($response);
			}
			else // otherwise if data are from the cache
			{
				$call = $response;
			}

			// If no errors occurred
			if ($call->{ClientAddon\DataHandler::CODE} == SUCCESS)
			{
				// If the cache is enabled or should be overwritten, store the result
				if ($this->_cache == ClientAddon::CACHE_ENABLED || $this->_cache == ClientAddon::CACHE_OVERWRITE)
				{
					ClientAddon\CacheHandler::set($this->_remoteWSAlias, $call);
				}

				// If this is the login call...
				if ($this->_remoteWSAlias == LOCAL_LOGIN_CALL)
				{
					$this->_setDataLogin($call); // ...stores login data in the data session
				}
			}
		}

		$this->_callResult = $call; // stores manipulated data in property _callResult
    }

	/**
	 * Stores login data (user data) into data session
	 */
	private function _setDataLogin($response)
	{
		if (isset($this->_routeArray[LOCAL_LOGIN_CALL])
			&& isset($this->_routeArray[LOCAL_LOGIN_CALL][USERNAME])
			&& isset($this->_callParametersArray[USERNAME]))
		{
			ClientAddon\DataHandler::set($this->_routeArray[LOCAL_LOGIN_CALL][USERNAME], $this->_callParametersArray[USERNAME]);
		}
	}

	/**
	 * Retrives login data (user data) from data session
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
     * Initialization of the properties of this object
     */
	private function _setPropertiesDefault()
	{
		$this->_routeArray = null;
		$this->_connectionArray = null;

		$this->_remoteWSAlias = null;

		$this->_remoteWSName = null;
		$this->_hook = null;
		$this->_loginRequired = true; // by default to perform a call the user must be logged in

		$this->_cache = ClientAddon::CACHE_DISABLED; // by default caching is not enabled
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
    private function _setHTTPMethod()
    {
        if (isset($_GET) && count($_GET) > 0) //
        {
            $this->_httpMethod = ClientAddon::HTTP_GET_METHOD;
        }
        elseif (isset($_POST) && count($_POST) > 0) //
        {
            $this->_httpMethod = ClientAddon::HTTP_POST_METHOD;
        }
    }

    /**
     * Parse the parameters given via HTTP GET or POST methods (present in $_GET and $_POST)
	 * It stores parameters for the remote web service into _callParametersArray property.
	 * The other given parameters are used to understand, using the configured route, what remote web service
	 * to call, optionally what hook to call after and if login is required in order to be able to call
	 * this remote web service. Also used to set the cache mode.
     */
    private function _parseParameters()
    {
        if ($this->_isGET()) // if the call was performed using a HTTP GET
        {
            $httpParameters = $_GET;
        }
        else // if the call was performed using a HTTP POST
        {
            $httpParameters = $_POST;
        }

		// Loops through the parameters
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
						if (isset($routeConfigEntry[AUTH]))
                        {
                            $this->_loginRequired = $routeConfigEntry[AUTH];
                        }
                    }
					// Otherwise it contains only the name of the remote web service
                    else
                    {
                        $this->_remoteWSName = $routeConfigEntry;
                    }
                }
            }
			// If the parameter is used to set the cache mode
            elseif ($parameterName == ClientAddon::CACHE_PARAMETER)
            {
				$this->_cache = ClientAddon::CACHE_DISABLED; // By default caching is not enabled
				// If it contains a valid value then set the property
				if ($parameterValue == ClientAddon::CACHE_ENABLED
					|| $parameterValue == ClientAddon::CACHE_DISABLED
					|| $parameterValue == ClientAddon::CACHE_OVERWRITE)
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
     * Returns true if the HTTP method used to call this server is GET
     */
    private function _isGET()
    {
        return $this->_httpMethod == ClientAddon::HTTP_GET_METHOD;
    }

    /**
     * Returns true if the HTTP method used to call this server is POST
     */
    private function _isPOST()
    {
        return $this->_httpMethod == ClientAddon::HTTP_POST_METHOD;
    }

	/**
     * Checks if all the required parametes to perform a remote call are present and valid
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
	 * Checks if the login succeeded or not
	 * - If this is a login remote call, no login is required and the cache is enabled
	 * - If this call doesn't require to be logged in, then the login succeeded
	 * - If login data are present in data session, then the login succeeded
	 */
	private function _checkLogin()
	{
		$checkLogin = false; // fails by default

		// If this is a remote call to log in
		if ($this->_remoteWSAlias == LOCAL_LOGIN_CALL)
		{
			$this->_cache = ClientAddon::CACHE_ENABLED; // store the result in the cache
			$this->_loginRequired = false; // no login is required to perform a login ;)
		}

		// If no login is required to perform this remote call
		if ($this->_loginRequired == false)
		{
			$checkLogin = true; // it's a success
		}

		// If the data of the user are present in data session
		if ($this->_getDataLogin() != null)
		{
			$checkLogin = true; // it's a success
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
     * Performs a remote call using the GET HTTP method
	 * NOTE: parameters in a HTTP GET call are placed into the URI
     */
    private function _callGET($uri)
    {
        return \Httpful\Request::get($uri)
            ->authenticateWith($this->_connectionArray[USERNAME], $this->_connectionArray[PASSWORD])
            ->addHeader($this->_connectionArray[API_KEY_NAME], $this->_connectionArray[API_KEY_VALUE])
            ->expectsJson() // parse from json
            ->send();
    }

    /**
     * Performs a remote call using the POST HTTP method
     */
    private function _callPOST($uri)
    {
        return \Httpful\Request::post($uri)
			->authenticateWith($this->_connectionArray[USERNAME], $this->_connectionArray[PASSWORD])
			->addHeader($this->_connectionArray[API_KEY_NAME], $this->_connectionArray[API_KEY_VALUE])
            ->expectsJson() // parse from json
            ->body($this->_callParametersArray) // post parameters
            ->sendsJson() // encode into json
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
     * Checks the response from the remote web service
	 *
     */
    private function _checkResponse($response)
    {
		$checkResponse = ClientAddon\DataHandler::error(ADDON_ERROR); // by default returns an error

        if (is_object($response)) // must be an object returned by the json decode
        {
            if (isset($response->body) && is_object($response->body)) // the response must have a body
            {
				// If is present the property status and is equal to false
                if (isset($response->body->status) && $response->body->status === false)
                {
					// the call is unauthorized, no given username and password or they are not valid
					$checkResponse = $this->_error(UNAUTHORIZED);
                }
                else // otherwise the remote web service has given a valid response
                {
					// If the response is a success
                    if (isset($response->body->error) && $response->body->error == FHC_CORE_SUCCESS)
                    {
						// If property retval is present
                        if (isset($response->body->retval))
                        {
							// If no data are present
                            if ((is_string($response->body->retval) && trim($response->body->retval) == '')
								|| (is_array($response->body->retval) && count($response->body->retval) == 0)
                                || (is_object($response->body->retval) && count((array)$response->body->retval) == 0))
                            {
								$checkResponse = $this->_error(NO_DATA);
                            }
                            else // otherwise is a total success!!!
                            {
								// If retval is not an array, convert it to array
								// In this way the output is standardized, is always an array
                                if (!is_array($response->body->retval))
                                {
                                    $response->body->retval = array(0 => $response->body->retval);
                                }

                                $checkResponse = $this->_success($response->body); // returns a success
                            }
                        }
                        else // otherwise the response is incomplete
                        {
							$checkResponse = $this->_error(INCOMPLETE_RESPONSE);
                        }
                    }
                    else // otherwise the response is an error
                    {
						$checkResponse = $this->_error(FHC_CORE_ERROR, $response->body);
                    }
                }
            }
            else // if the response has no body
            {
				$checkResponse = $this->_error(NO_RESPONSE_BODY);
            }
        }

		return $checkResponse;
    }

	/**
	 * Called in case the response from the remote web service is a success
	 * It calls the hook giving as parameters SUCCESS and the whole response from the remote web service
	 */
	private function _success($response = null)
	{
		$result = $this->_callHook(SUCCESS, $response); // call the hook

		if ($result == null) // if no hook is present or if doesn't return a valid value
		{
			$result = ClientAddon\DataHandler::success($response->retval); // return a success
		}

		return $result;
	}

	/**
	 * Called in case the response from the remote web service is an error
	 * It calls the hook giving as parameters the error code genereted in method _checkResponse
	 * and the whole response from the remote web service
	 */
	private function _error($code, $response = null)
	{
		$result = $this->_callHook($code, $response); // call the hook

		if ($result == null) // if no hook is present or if doesn't return a valid value
		{
			$result = ClientAddon\DataHandler::error($code, $response); // return an error
		}

		return $result;
	}

	/**
	 * Calls the hook (if it is present and valid) configurated in the route array for this call
	 * and returns the result of the call.
	 * It gives as parameters to the hook the code genereted in method _checkResponse
	 * and the whole response from the remote web service.
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
