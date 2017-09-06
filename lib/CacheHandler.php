<?php

namespace ClientAddon;

require_once APPLICATION_PATH.'/lib/SessionHandler.php';

class CacheHandler
{
	const SESSION_NAME = 'cache';

	/**
	 *
	 */
	public static function startSession()
	{
		SessionHandler::start();

		SessionHandler::set(CacheHandler::SESSION_NAME);
	}

	/**
	 *
	 */
	public static function set($name, $value)
	{
		SessionHandler::set(CacheHandler::SESSION_NAME, $name, $value);
	}

	/**
	 *
	 */
	public static function get($name)
	{
		return SessionHandler::get(CacheHandler::SESSION_NAME, $name);
	}

	/**
	 *
	 */
	public static function unset($name)
	{
		SessionHandler::unset(CacheHandler::SESSION_NAME, $name);
	}

	/**
	 *
	 */
	public static function flush()
	{
		SessionHandler::unset(CacheHandler::SESSION_NAME);
	}
}
