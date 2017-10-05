<?php

namespace CoreClient;

/**
 * Handles errors and success
 */
class DataHandler
{
	// Properties of a returned value
	const CODE = 'code';
	const RESPONSE = 'response';

	/**
	 * Checks if the parameter is a success
	 */
	public static function isSuccess($response)
	{
		$isSuccess = false;

		if (is_object($response) && isset($response->error) && $response->error == FHC_CORE_SUCCESS)
        {
            $isSuccess = true;
        }

		return $isSuccess;
	}

	/**
	 * Checks if the parameter is an error
	 */
	public static function isError($response)
	{
		return !DataHandler::isSuccess($response);
	}

	/**
	 * IF $response == success && contains data THEN true, ELSE false
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
	 * Generate a success whith the given parameter (optional)
	 */
	public static function success($response = null)
	{
		return DataHandler::_getReturnObject(SUCCESS, $response);
	}

	/**
	 * Generate an error whith the given parameter (optional) and code (required)
	 */
	public static function error($code, $response = null)
	{
		return DataHandler::_getReturnObject($code, $response);
	}

	/**
	 * Returns an object that can be used managed with the methods of this class
	 * to communicate between methods and functions
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
