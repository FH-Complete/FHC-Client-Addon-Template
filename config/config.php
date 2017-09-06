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
	    WEBSERVICES => 'api/v1'
	)
);

$router = array(
	LOGIN => array(
		API => 'CheckUserAuth/CheckByUsernamePassword',
		HOOK => 'hookLogin',
		USERNAME => 'username'
	),
    'testHook' => array(
        API => 'Test/Test',
        HOOK => 'hookTest',
		LOGIN => false
    ),
	'testNoHook' => array(
        API => 'Test/Test'
    ),
    'loadPersonData' => 'person/Person/Person',
	'loadPhrases' => array(
        API => 'system/Phrase/Phrases',
		LOGIN => false
    ),
    'loadKontaktByPersonID' => array(
        API => 'person/Kontakt/KontaktByPersonID',
        HOOK => 'hookGetKontakt'
    ),
    'saveKontaktByPersonID' => array(
        API => 'person/Kontakt/Kontakt',
        HOOK => 'hookSaveKontakt'
    )
);
