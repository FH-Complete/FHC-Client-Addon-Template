<?php

namespace ClientAddon;

require_once APPLICATION_PATH.'/lib/SessionHandler.php';

class DataHandler
{
	//
	const CODE = 'code';
	const RESPONSE = 'response';

	//
	const SESSION_ID = 'sessionId';
	const SESSION_NAME = 'data';

	/**
	 *
	 */
	public static function isSuccess($response)
	{
		$isSuccess = false;

		if (is_object($response) && isset($response->error) && $response->error === FHC_CORE_SUCCESS)
        {
            $isSuccess = true;
        }

		return $isSuccess;
	}

	/**
	 *
	 */
	public static function isError($response)
	{
		return !DataHandler::isSuccess($response);
	}

	/**
	 *
	 */
	public static function hasData($response)
	{
		$hasData = false;

		if (DataHandler::isSuccess($response))
		{
			if (isset($response->retval) &&
				((is_string($response->retval) && trim($response->retval) != '')
				|| (is_array($response->retval) && count($response->retval) > 0)
				|| (is_object($response->retval) && count((array)$response->retval) > 0)))
			{
				$hasData = true;
			}
		}

		return $hasData;
	}

	/**
	 *
	 */
	public static function success($response = null)
	{
		return DataHandler::_getReturnObject(SUCCESS, $response);
	}

	/**
	 *
	 */
	public static function error($code, $response = null)
	{
		return DataHandler::_getReturnObject($code, $response);
	}

	/**
	 *
	 */
	public static function startSession()
	{
		SessionHandler::start();

		SessionHandler::set(DataHandler::SESSION_NAME, DataHandler::SESSION_ID, session_id());
	}

	/**
	 *
	 */
	public static function set($name, $value)
	{
		SessionHandler::set(DataHandler::SESSION_NAME, $name, $value);
	}

	/**
	 *
	 */
	public static function get($name)
	{
		return SessionHandler::get(DataHandler::SESSION_NAME, $name);
	}

	/**
	 *
	 */
	private static function _getReturnObject($code, $response = null)
	{
		$returnObject = new \stdClass(); //

		$returnObject->{DataHandler::CODE} = $code;

		if ($response != null)
		{
			$returnObject->{DataHandler::RESPONSE} = $response;
		}

		return $returnObject;
	}
}
