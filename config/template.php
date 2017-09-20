<?php

// WARNING: Don't edit this file. Copy or rename it as config.php before change it

$activeConnection = 'DEFAULT'; //

//
$connection = array(
	'DEFAULT' => array(
		USERNAME => 'fhcomplete', //
		PASSWORD => '****', //
		API_KEY_NAME => 'FHC-API-KEY', //
		API_KEY_VALUE => 'testapikey@fhcomplete.org', //
		PROTOCOL => 'https', //
	    HOST => 'fhcomplete.org', //
	    PATH => 'fhcomplete', //
	    ROUTER => 'index.ci.php', //
	    WS_PATH => 'api/v1' //
	)
);

//
$route = array(
	LOCAL_LOGIN_CALL => array(
		REMOTE_WS => 'CheckUserAuth/CheckByUsernamePassword',
		HOOK => 'hookLogin',
		USERNAME => 'username'
	),
    'testHook'  => array(	//
        REMOTE_WS => 'Test/Test', //
        HOOK => 'hookTest', //
		LOGIN_REQUIRED => false	//
    ),
	'testNoHook'  => 'Test/Test'
);
