<?php

define('APPLICATION_PATH', dirname(__FILE__).'/../');

require_once APPLICATION_PATH.'/lib/ClientAddon.php';

$clientAddon = new ClientAddon($_GET, $_POST); //

$clientAddon->call(); //

$clientAddon->printResults(); //
