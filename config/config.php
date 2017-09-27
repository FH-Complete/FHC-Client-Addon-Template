<?php

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
	LOCAL_LOGIN_CALL => array(
		REMOTE_WS => 'CheckUserAuth/CheckUserAuth',
		HOOK => 'hookLogin'
	),
	'testHookNoLogin'  => array(
        REMOTE_WS => 'Test/Test',
        HOOK => 'hookTest',
		AUTH => false
    ),
	'testNoHook'  => 'Test/Test',
	'loadPersonData' => array(
		REMOTE_WS => 'person/Person/Person',
		AUTH => true,
		SESSION_PARAMS => array('person_id')
	),
	'loadPhrases' => array(
        REMOTE_WS => 'system/Phrase/Phrases',
		AUTH => false
    ),
    'loadKontaktByPersonID' => array(
        REMOTE_WS => 'person/Kontakt/KontaktByPersonID',
        HOOK => 'hookGetKontakt',
		SESSION_PARAMS => array('person_id')
    ),
    'saveKontaktByPersonID' => array(
        REMOTE_WS => 'person/Kontakt/Kontakt',
        HOOK => 'hookSaveKontakt',
		AUTH => false,
		SESSION_PARAMS => array('kontakt_id')
    )
);
