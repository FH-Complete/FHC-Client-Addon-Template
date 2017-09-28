<?php

function hookTest($code, $response)
{
	if (ClientAddon\DataHandler::isSuccess($response))
	{
		return ClientAddon\DataHandler::success($response->retval);
	}
	elseif (ClientAddon\DataHandler::isError($response))
	{
		return ClientAddon\DataHandler::error($code, $response);
	}
}

function hookLogin($code, $response)
{
	if (ClientAddon\DataHandler::isSuccess($response))
	{
		return ClientAddon\DataHandler::success($response->retval);
	}
	elseif (ClientAddon\DataHandler::isError($response))
	{
		return ClientAddon\DataHandler::error($code, $response);
	}
}

function hookGetKontakt($code, $response)
{
	if (ClientAddon\DataHandler::isSuccess($response))
	{
		return ClientAddon\DataHandler::success($response->retval);
	}
	elseif (ClientAddon\DataHandler::isError($response))
	{
		return ClientAddon\DataHandler::error($code, $response);
	}
}

function hookSaveKontakt($code, $response)
{
	if (ClientAddon\DataHandler::isSuccess($response))
	{
		return ClientAddon\DataHandler::success($response->retval);
	}
	elseif (ClientAddon\DataHandler::isError($response))
	{
		return ClientAddon\DataHandler::error($code, $response);
	}
}
