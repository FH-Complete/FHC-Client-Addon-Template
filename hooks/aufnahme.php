<?php

function hookKontakt($code, $response)
{
	if (ClientAddon\DataHandler::hasData($response))
	{
		ClientAddon\CacheHandler::addSessionParam('kontakt_id', $response->retval[0]->kontakt_id);

		return ClientAddon\DataHandler::success($response->retval);
	}
}
