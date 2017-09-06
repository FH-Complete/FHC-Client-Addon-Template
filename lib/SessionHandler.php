<?php

namespace ClientAddon;

class SessionHandler
{
	/**
	 *
	 */
	public static function start($sessionName = null)
	{
		//
		if (session_status() != PHP_SESSION_ACTIVE)
		{
			session_start();
		}

		//
		if ($sessionName != null || (is_string($sessionName) && trim($sessionName) != ''))
		{
			$_SESSION[$sessionName] = array();
		}
	}

	/**
	 *
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
	 *
	 */
	public static function get($sessionName, $name)
	{
		return isset($_SESSION[$sessionName][$name]) ? $_SESSION[$sessionName][$name] : null;
	}

	/**
	 *
	 */
	public static function unset($sessionName, $name)
	{
		if (isset($_SESSION[$sessionName]))
		{
			unset($_SESSION[$sessionName][$name]);
		}
	}

	/**
	 *
	 */
	public static function flush($sessionName)
	{
		unset($_SESSION[$sessionName]);
	}
}
