<?php

namespace ClientAddon;

require_once APPLICATION_PATH.'/lib/SessionHandler.php';

/**
 * Manages the cache
 */
class CacheHandler
{
	const SESSION_NAME = 'cache'; // name of the cache into the session

	/**
	 * Initialize the cache
	 */
	public static function startSession()
	{
		SessionHandler::start();

		SessionHandler::set(CacheHandler::SESSION_NAME);
	}

	/**
	 * Add a value into the cache identified by the parameter name
	 */
	public static function set($name, $value)
	{
		SessionHandler::set(CacheHandler::SESSION_NAME, $name, $value);
	}

	/**
	 * Get a value from the cache identified by the parameter name
	 */
	public static function get($name)
	{
		return SessionHandler::get(CacheHandler::SESSION_NAME, $name);
	}

	/**
	 * Remove a value from the cache identified by the parameter name
	 */
	public static function unset($name)
	{
		SessionHandler::unset(CacheHandler::SESSION_NAME, $name);
	}

	/**
	 * Clean all the cached data
	 */
	public static function flush()
	{
		SessionHandler::flush(CacheHandler::SESSION_NAME);
	}

	/**
	 * Add a new session parameter, so other calls can you this new parameter
	 * NOTE: if this parameter already exists in session, it will be overwritten
	 */
	public static function addSessionParam($name, $value)
	{
		$addSessionParam = false; // by default it is a fail

		$sessionDataLogin = CacheHandler::get(LOCAL_LOGIN_CALL); // get login data from cache

		// If login data from cache exists and the name is valid
		if ($sessionDataLogin != null
			&& isset($sessionDataLogin->{DataHandler::RESPONSE})
			&& is_array($sessionDataLogin->{DataHandler::RESPONSE})
			&& count($sessionDataLogin->{DataHandler::RESPONSE}) > 0
			&& $name!= null
			&& $name != '')
		{
			// Get session parameters from login data
			$dataLogin = $sessionDataLogin->{DataHandler::RESPONSE}[0];

			if (is_object($dataLogin))
			{
				$dataLogin->{$name} = $value; // store the new value

				// Store the changed obj in cache
				CacheHandler::set(LOCAL_LOGIN_CALL, $sessionDataLogin);

				$addSessionParam = true; // return a success
			}
		}

		return $addSessionParam;
	}
}
