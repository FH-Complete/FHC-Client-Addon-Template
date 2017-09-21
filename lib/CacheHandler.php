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
		SessionHandler::unset(CacheHandler::SESSION_NAME);
	}
}
