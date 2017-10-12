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
	if (CoreClient.isSuccess(response))
	{
		console.log("Is a success");
		if (CoreClient.hasData(response)) // How to check if a response is a success and contains data
		{
			console.log("...and contains data");
		}
	}
	else if (CoreClient.isError(response)) // How to check if a response is an error
	{
		console.log("A web interface non blocking error occurred: " + response.code);
	}
}

// Object that contains parameters to send to the call
var loginParameters = {
	username: "myuserame",
	password: "*******"
};

$(document).ready(function() { // needed to use the veil
	// Example GET call to login
	// NOTE: The last parameter is not specified because the cache will be automatically managed,
	// no way to change the behaviour
	CoreClient.callRESTFulGet(
		LOGIN_CALL_NAME,	// Name of the remote call, an alias it will be translated to a call to the core
		loginParameters,	// Object that contains parameters to send to the remote web service
		{
			errorCallback: errorCallback,		// Function reference to manage errors
			successCallback: successCallback,	// Function reference to manage success
			veilTimeout: 2000					// overwrite veil timeout
		}
	);

	// Example GET call. AUTH = no, HOOK = yes, CACHE = enabled
	CoreClient.callRESTFulGet(
		'testHookNoLogin',
		null,
		{
			errorCallback: errorCallback,
			successCallback: successCallback,
			cache: CACHE_ENABLED
		}
	);

	// Example GET call. AUTH = yes, HOOK = no, CACHE = disabled
	CoreClient.callRESTFulGet(
		'testNoHook',
		null,
		{
			errorCallback: errorCallback,
			successCallback: successCallback,
			cache: CACHE_DISABLED
		}
	);

	// Example GET call. AUTH = yes, HOOK = no, CACHE = overwrite, SESSION_PARAMS = person_id
	// NOTE: If a person_id is specified as parameter, it will be overwritten by the session parameter
	CoreClient.callRESTFulGet(
		'loadPersonData',
		null,
		{
			errorCallback: errorCallback,
			successCallback: successCallback,
			cache: CACHE_OVERWRITE
		}
	);

	// Object that contains parameters to send to the remote web service
	var saveDataPersonParameters = {
		name: "me",
		surname: "alwaysme"
	};

	// Example POST call. AUTH = yes, HOOK = no, CACHE = overwrite
	CoreClient.callRESTFulPost(
		'savePersonData',
		saveDataPersonParameters,
		{
			errorCallback: errorCallback,
			successCallback: successCallback,
			cache: CACHE_OVERWRITE
		}
	);
});
