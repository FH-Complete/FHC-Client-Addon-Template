<?php

/**
 * Hook example about what you can do
 */
function hookTest($code, $response)
{
	// Check the code returned from a remote call
	switch ($code)
	{
		case SUCCESS:
			$str = SUCCESS;
			break;
		case FHC_CORE_ERROR:
			$str = FHC_CORE_ERROR;
			break;
		case CONNECTION_ERROR:
			$str = CONNECTION_ERROR;
			break;
		case JSON_PARSE_ERROR:
			$str = JSON_PARSE_ERROR;
			break;
		case UNAUTHORIZED:
			$str = UNAUTHORIZED;
			break;
		case NO_RESPONSE_BODY:
			$str = NO_RESPONSE_BODY;
			break;
		case NO_DATA:
			$str = NO_DATA;
			break;
		case INCOMPLETE_RESPONSE:
			$str = INCOMPLETE_RESPONSE;
			break;
		case MISSING_REQUIRED_PARAMETERS:
			$str = MISSING_REQUIRED_PARAMETERS;
			break;
		default:
			$str = ADDON_ERROR;
	}

	// Save a parameter in session
	CoreClient\CacheHandler::addSessionParam('hookTest', true);

	// Checks if response is a success
	if (CoreClient\DataHandler::hasData($response))
	{
		return CoreClient\DataHandler::success($response->retval); // return a success
	}
	// Check if response is a success and contains data
	elseif (CoreClient\DataHandler::isSuccess($response))
	{
		return CoreClient\DataHandler::success($response->retval); // return a success
	}
	// Checks if response is an error
	elseif (CoreClient\DataHandler::isError($response))
	{
		return CoreClient\DataHandler::error($code, $response); // return an error
	}
}
