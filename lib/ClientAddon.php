<?php

require_once APPLICATION_PATH.'/vendor/autoload.php';
require_once APPLICATION_PATH.'/lib/constants.php';
require_once APPLICATION_PATH.'/lib/DataHandler.php';
require_once APPLICATION_PATH.'/lib/CacheHandler.php';

class ClientAddon
{
	//
    const HOOK_DIR = 'hooks';
    const CONFIG_DIR = 'config';
    const CONFIG_FILENAME = 'config.php';

	//
    const URI_TEMPLATE = '%s://%s/%s/%s/%s/';
    const HTTP_GET_METHOD = 'GET';
    const HTTP_POST_METHOD = 'POST';

	const CACHE_PARAMETER = 'cache';

	private $_router;			//
	private $_connection;		//

	private $_hook;				//
	private $_apiCalled;		//
	private $_loginRequired;	//

	private $_cache;			//
	private $_apiName;			// REQUIRED
	private $_httpMethod;		//
	private $_callParameters;	//

	private $_callResult;		//

    /**
     *
     */
    public function __construct($get, $post)
    {
		$this->_setPropertiesDefault(); //

        $this->_loadConfig(); //

        $this->_setHTTPMethod($get, $post); //

        $this->_parseParameters($get, $post); //
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

			if ($this->_cache === true)
			{
				$response = ClientAddon\CacheHandler::get($this->_apiName);
			}
			else
			{
				ClientAddon\CacheHandler::unset($this->_apiName);
			}

			if ($response == null)
			{
		        $uri = $this->_generateURI();

		        try
		        {
		            if ($this->_isGET()) //
		            {
		                $response = $this->_callGET($uri);
		            }
		            else //
		            {
		                $response = $this->_callPOST($uri);
		            }
				}
		        catch (\Httpful\Exception\ConnectionErrorException $cee)
		        {
					$this->_error(CONNECTION_ERROR);
		        }
		        catch (Exception $e)
		        {
					$this->_error(JSON_PARSE_ERROR);
		        }
			}

			if ($this->_cache === true)
			{
				ClientAddon\CacheHandler::set($this->_apiName, $response);
			}

            $this->_checkResponse($response);

			$this->_setDataLogin();
		}
    }

	/**
	 *
	 */
	private function _setDataLogin()
	{
		if ($this->_apiCalled == LOGIN && $this->_callResult->{ClientAddon\DataHandler::CODE} == SUCCESS)
		{
			if (isset($this->_router[LOGIN])
				&& isset($this->_router[LOGIN][USERNAME])
				&& isset($this->_callParameters[USERNAME]))
			{
				ClientAddon\DataHandler::set($this->_router[LOGIN][USERNAME], $this->_callParameters[USERNAME]);
			}
		}
	}

	/**
	 *
	 */
	private function _getDataLogin()
	{
		if (isset($this->_router[LOGIN]) && isset($this->_router[LOGIN][USERNAME]))
		{
			return ClientAddon\DataHandler::get($this->_router[LOGIN][USERNAME]);
		}
	}

	/**
	 *
	 */
	public function printResults()
	{
		header('Content-Type: application/json');

		echo json_encode($this->_callResult);
	}

    // --------------------------------------------------------------------------------------------
    // Private methods

	/**
     *
     */
	private function _setPropertiesDefault()
	{
		$this->_router = null;
		$this->_connection = null;

		$this->_hook = null;
		$this->_apiCalled = null;
		$this->_loginRequired = true;

		$this->_cache = true;
		$this->_apiName = null;
		$this->_httpMethod = null;
		$this->_callParameters = array();

		$this->_callResult = null;
	}

    /**
     *
     */
    private function _loadConfig()
    {
        require_once APPLICATION_PATH.'/'.ClientAddon::CONFIG_DIR.'/'.ClientAddon::CONFIG_FILENAME;

		$this->_router = $router;
		$this->_connection = $connection[$activeConnection];
    }

    /**
     *
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
    private function _parseParameters($get, $post)
    {
        if ($this->_isGET()) //
        {
            $parameters = $get;
        }
        else //
        {
            $parameters = $post;
        }

        foreach ($parameters as $name => $value)
        {
            if ($name == API)
            {
                if (array_key_exists($value, $this->_router))
                {
					$this->_apiCalled = $value; //

                    if (is_array($this->_router[$value]))
                    {
                        if (isset($this->_router[$value][API]))
                        {
                            $this->_apiName = $this->_router[$value][API];
                        }
                        if (isset($this->_router[$value][HOOK]))
                        {
                            $this->_hook = $this->_router[$value][HOOK];
                        }
						if (isset($this->_router[$value][LOGIN]))
                        {
                            $this->_loginRequired = $this->_router[$value][LOGIN];
                        }
                    }
                    else
                    {
                        $this->_apiName = $this->_router[$value];
                    }
                }
            }
            elseif ($name == ClientAddon::CACHE_PARAMETER)
            {
                $this->_cache = ($value === true || $value == 'true') ? true : false;
            }
            else
            {
                $this->_callParameters[$name] = $value;
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

        if ($this->_apiName != '')
        {
			$checkRequiredParameters = true;
        }

		return $checkRequiredParameters;
    }

	/**
	 *
	 */
	private function _checkLogin()
	{
		$checkLogin = false;

		//
		if ($this->_apiCalled == LOGIN)
		{
			$this->_cache = true; //
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
     *
     */
    private function _generateURI()
    {
        $uri = sprintf(
            ClientAddon::URI_TEMPLATE,
            $this->_connection[PROTOCOL],
            $this->_connection[HOST],
            $this->_connection[PATH],
            $this->_connection[ROUTER],
            $this->_connection[WEBSERVICES]
        ).$this->_apiName;

        if ($this->_isGET()) //
        {
			$queryString = '';

			foreach ($this->_callParameters as $name => $value)
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
            ->authenticateWith($this->_connection[USERNAME], $this->_connection[PASSWORD])
            ->addHeader($this->_connection[API_KEY_NAME], $this->_connection[API_KEY_VALUE])
            ->expectsJson()
            ->send();
    }

    /**
     *
     */
    private function _callPOST($uri)
    {
        return \Httpful\Request::post($uri)
			->authenticateWith($this->_connection[USERNAME], $this->_connection[PASSWORD])
			->addHeader($this->_connection[API_KEY_NAME], $this->_connection[API_KEY_VALUE])
            ->expectsJson()
            ->body($this->_callParameters)
            ->sendsJson()
            ->send();
    }

    /**
     *
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
	 *
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
