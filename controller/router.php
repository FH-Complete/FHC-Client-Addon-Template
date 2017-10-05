<?php

define('APPLICATION_PATH', dirname(__FILE__).'/../'); // define the application path to resolve all the other inlcudes

require_once APPLICATION_PATH.'/lib/CoreClient.php'; // includes the main components

$coreClient = new coreClient(); // instantiate a coreClient object

$coreClient->call(); // takes care about the call perfomed on this page and calls the correct remote web service

$coreClient->printResults(); // DO NOT print anything else before this call
