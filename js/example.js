//
function errorCallback(jqXHR, textStatus, errorThrown)
{
	console.error("A blocking error occurred");

	console.error(jqXHR);
	console.error(textStatus);
	console.error(errorThrown);
}

//
function successCallback(response)
{
	if (isSuccess(response))
	{
		console.log("Is a success");
		if (hasData(response))
		{
			console.log("...and contains data");
		}
	}
	else
	{
		console.log("A non blocking error occurred: " + response.code);
	}
}

//
$(document).ready(function() {

	$("#loadKontaktByPersonID").click(function() {
		callRESTFulGet('loadKontaktByPersonID', null, errorCallback, successCallback, CACHE_ENABLED);
	});

	$("#saveKontaktByPersonID").click(function() {
		callRESTFulPost('saveKontaktByPersonID', null, errorCallback, successCallback);
	});

	$("#testHookNoLogin").click(function() {
		callRESTFulGet('testHookNoLogin', null, errorCallback, successCallback, CACHE_OVERWRITE);
	});

	$("#testNoHook").click(function() {
		callRESTFulGet('testNoHook', null, errorCallback, successCallback, CACHE_ENABLED);
	});

	$("#login").click(function() {
		callRESTFulGet('login', {username: "admin", password: "1q2w3"}, errorCallback, successCallback);
	});

	$("#loadPersonData").click(function() {
		callRESTFulGet('loadPersonData', null, errorCallback, successCallback);
	});

});
