<?php

define('FHC_CORE_SUCCESS',  0); // success value from the core

define('SUCCESS', 'OK'); // success code in the addon

// Blocking errors
define('ADDON_ERROR',					'ERR0000');
define('FHC_CORE_ERROR',				'ERR0001');
define('CONNECTION_ERROR',  			'ERR0002');
define('JSON_PARSE_ERROR',    			'ERR0003');
define('NO_RESPONSE_BODY',    			'ERR0004');
define('INCOMPLETE_RESPONSE',   		'ERR0005');
define('UNAUTHORIZED',          		'ERR0006');
define('MISSING_REQUIRED_PARAMETERS',	'ERR0007');
define('MISSING_SESSION_PARAMETERS',	'ERR0008');

// Non blocking errors (warnings)
define('NO_DATA',               		'WAR0001');
define('LOGIN_REQUIRED',				'WAR0002');
define('WRONG_HOOK',					'WAR0003');

// Connection parameters names
define('USERNAME',			'username');
define('PASSWORD',			'password');
define('API_KEY_NAME',		'api_key_name');
define('API_KEY_VALUE',		'api_key_value');
define('PROTOCOL',			'protocol');
define('HOST',				'host');
define('PATH',				'path');
define('ROUTER',			'router');
define('WS_PATH',			'ws_path');

// Alias of the remote web service that manage the user login
define('LOCAL_LOGIN_CALL',	'login');
define('LOCAL_LOGOUT_CALL',	'logout');

// Route parameters names
define('REMOTE_WS',			'remote_ws');
define('HOOK',				'hook');
define('AUTH',				'auth');
define('SESSION_PARAMS',	'session_params');

// Cache parameters
define('CACHE_PARAMETER',	'cache'); // cache parameter name
define('CACHE_ENABLED',		'enabled'); // cache enabled value
define('CACHE_DISABLED',	'disabled'); // cache disabled value
define('CACHE_OVERWRITE',	'overwrite'); // cache overwrite value
