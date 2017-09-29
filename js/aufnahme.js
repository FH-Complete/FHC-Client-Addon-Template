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
	if (ClientAddon.isSuccess(response))
	{
		console.log("Is a success");
		if (ClientAddon.hasData(response))
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
		ClientAddon.callRESTFulGet('loadKontaktByPersonID', null, errorCallback, successCallback, CACHE_ENABLED);
	});

	$("#saveKontaktByPersonID").click(function() {
		ClientAddon.callRESTFulPost('saveKontaktByPersonID', null, errorCallback, successCallback);
	});

	$("#testHookNoLogin").click(function() {
		ClientAddon.callRESTFulGet('testHookNoLogin', null, errorCallback, successCallback, CACHE_OVERWRITE);
	});

	$("#testNoHook").click(function() {
		ClientAddon.callRESTFulGet('testNoHook', null, errorCallback, successCallback, CACHE_ENABLED);
	});

	$("#login").click(function() {
		ClientAddon.callRESTFulGet('login', {username: "admin", password: "1q2w3"}, errorCallback, successCallback);
	});

	$("#loadPersonData").click(function() {
		ClientAddon.callRESTFulGet('loadPersonData', null, errorCallback, successCallback);
	});

});
