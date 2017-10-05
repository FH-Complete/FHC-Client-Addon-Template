<?php

namespace CoreClient;

/**
 * Manages the user session
 */
class SessionHandler
{
	/**
	 * Start the session
	 */
	public static function start($sessionName = null)
	{
		// If the session is not present
		if (session_status() != PHP_SESSION_ACTIVE)
		{
			session_start();
		}

		// If $sessionName is valid then initialize a session with this name
		if ($sessionName != null || (is_string($sessionName) && trim($sessionName) != ''))
		{
			$_SESSION[$sessionName] = array();
		}
	}

	/**
	 * Add a value into this session identified by the parameter name
	 */
	public static function set($sessionName, $name = null, $value = null)
	{
		if (!isset($_SESSION[$sessionName])) $_SESSION[$sessionName] = array();

		if ($name != null)
		{
			$_SESSION[$sessionName][$name] = $value;
		}
	}

	/**
	 * Get a value from this session identified by the parameter name
	 */
	public static function get($sessionName, $name)
	{
		return isset($_SESSION[$sessionName][$name]) ? $_SESSION[$sessionName][$name] : null;
	}

	/**
	 * Remove a value from this session identified by the parameter name
	 */
	public static function unset($sessionName, $name)
	{
		if (isset($_SESSION[$sessionName]))
		{
			unset($_SESSION[$sessionName][$name]);
		}
	}

	/**
	 * Clean this session
	 */
	public static function flush($sessionName)
	{
		unset($_SESSION[$sessionName]);
	}
}
