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

$route = array(
	LOCAL_LOGIN_CALL => array(
		REMOTE_WS => 'CheckUserAuth/CheckByUsernamePassword',
		HOOK => 'hookLogin',
		USERNAME => 'username'
	),
    'testHook' => array(
        REMOTE_WS => 'Test/Test',
        HOOK => 'hookTest',
		LOGIN_REQUIRED => false
    ),
	'testNoHook' => 'Test/Test',
    'loadPersonData' => 'person/Person/Person',
	'loadPhrases' => array(
        REMOTE_WS => 'system/Phrase/Phrases',
		LOGIN_REQUIRED => false
    ),
    'loadKontaktByPersonID' => array(
        REMOTE_WS => 'person/Kontakt/KontaktByPersonID',
        HOOK => 'hookGetKontakt'
    ),
    'saveKontaktByPersonID' => array(
        REMOTE_WS => 'person/Kontakt/Kontakt',
        HOOK => 'hookSaveKontakt'
    )
);
