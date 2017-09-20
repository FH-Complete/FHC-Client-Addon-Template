<?php

define('APPLICATION_PATH', dirname(__FILE__).'/../'); // define the application path to resolve all the other inlcudes

require_once APPLICATION_PATH.'/lib/ClientAddon.php'; // includes the main components

$clientAddon = new ClientAddon($_GET, $_POST); // instantiate a ClientAddon object

$clientAddon->call(); // takes care about the call perfomed on this page and calls the correct remote web service

$clientAddon->printResults(); // DO NOT print anything else before this call
