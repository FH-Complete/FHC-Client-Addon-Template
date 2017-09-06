<?php

/**
 *
 */
function hookTest($code, $response)
{
	$str = '';

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
		case MISSING_API_PARAMETER:
			$str = MISSING_API_PARAMETER;
	}

	if (ClientAddon\DataHandler::hasData($response))
	{
		$str .= ' and has data';
	}

	error_log($str);

	if (ClientAddon\DataHandler::isSuccess($response))
	{
		return ClientAddon\DataHandler::success($response->retval);
	}
	elseif (ClientAddon\DataHandler::isError($response))
	{
		return ClientAddon\DataHandler::error($code, $response);
	}
}
