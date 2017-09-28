// WARNING: this is only an example file, do not edit or copy

// Example of a callback error function
// NOTE: This is called when the ajax call had some problems
function errorCallback(jqXHR, textStatus, errorThrown)
{
	console.error("A web interface blocking error occurred");

	console.error(jqXHR);
	console.error(textStatus);
	console.error(errorThrown);
}

// Example of a callback success function
// NOTE: This is called when the ajax call succeeded, but it could contain a logic error or success
// obtained from the remote web service call
function successCallback(response)
{
	// How to check if a response is a success
	if (ClientAddon.isSuccess(response))
	{
		console.log("Is a success");
		if (ClientAddon.hasData(response)) // How to check if a response is a success and contains data
		{
			console.log("...and contains data");
		}
	}
	else if (ClientAddon.isError(response)) // How to check if a response is an error
	{
		console.log("A web interface non blocking error occurred: " + response.code);
	}
}

// Object that contains parameters to send to the call
var loginParameters = {
	username: "myuserame",
	password: "*******"
};

// Example GET call to login
// NOTE: The last parameter is not specified because the cache will be automatically managed,
// no way to change the behaviour
ClientAddon.callRESTFulGet(
	LOGIN_CALL_NAME,	// Name of the remote call, an alias it will be translated to a call to the core
	loginParameters,	// Object that contains parameters to send to the remote web service
	errorCallback,		// Function reference to manage errors
	successCallback		// Function reference to manage success
);

// Example GET call. AUTH = no, HOOK = yes, CACHE = enabled
ClientAddon.callRESTFulGet(
	'testHookNoLogin',
	null,
	errorCallback,
	successCallback,
	CACHE_ENABLED
);

// Example GET call. AUTH = yes, HOOK = no, CACHE = disabled
ClientAddon.callRESTFulGet(
	'testNoHook',
	null,
	errorCallback,
	successCallback,
	CACHE_DISABLED
);

// Example GET call. AUTH = yes, HOOK = no, CACHE = overwrite, SESSION_PARAMS = person_id
// NOTE: If a person_id is specified as parameter, it will be overwritten by the session parameter
ClientAddon.callRESTFulGet(
	'loadPersonData',
	null,
	errorCallback,
	successCallback,
	CACHE_OVERWRITE
);

// Object that contains parameters to send to the remote web service
var saveDataPersonParameters = {
	name: "me",
	surname: "alwaysme"
};

// Example POST call. AUTH = yes, HOOK = no, CACHE = overwrite
ClientAddon.callRESTFulPost(
	'savePersonData',
	saveDataPersonParameters,
	errorCallback,
	successCallback,
	CACHE_OVERWRITE
);
