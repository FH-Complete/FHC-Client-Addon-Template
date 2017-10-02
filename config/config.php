<?php

$debug = true;

$activeConnection = 'DEFAULT';

$connection = array(
	'DEFAULT' => array(
		USERNAME => 'admin',
		PASSWORD => '1q2w3',
		API_KEY_NAME => 'FHC-API-KEY',
		API_KEY_VALUE => 'testapikey@fhcomplete.org',
		PROTOCOL => 'http',
	    HOST => 'debian.dev',
	    PATH => 'core',
	    ROUTER => 'index.ci.php',
	    WS_PATH => 'api/v1'
	)
);

$cacheEnabled = true;

$route = array(
	LOCAL_LOGIN_CALL => 'CheckUserAuth/CheckUserAuth',
	LOCAL_LOGOUT_CALL => LOCAL_LOGOUT_CALL,
	"phrases" => array(
		REMOTE_WS => 'system/Phrase/Phrases',
		AUTH => false,
		CACHE_PARAMETER => CACHE_ENABLED
	),
	"loadPerson" => array(
		REMOTE_WS => 'person/Person/Person',
		SESSION_PARAMS => array('person_id')
	)
);
