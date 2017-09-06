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
		callRESTFulGet('loadKontaktByPersonID', {person_id: 1}, errorCallback, successCallback, true);
	});

	$("#saveKontaktByPersonID").click(function() {
		callRESTFulPost('saveKontaktByPersonID', {kontakt_id: 1}, errorCallback, successCallback);
	});

	$("#testNoHook").click(function() {
		callRESTFulGet('testNoHook', null, errorCallback, successCallback);
	});

	$("#testHook").click(function() {
		callRESTFulGet('testHook', null, errorCallback, successCallback);
	});

	$("#login").click(function() {
		callRESTFulGet('login', {username: "admin", password: "1q2w3"}, errorCallback, successCallback);
	});

});
