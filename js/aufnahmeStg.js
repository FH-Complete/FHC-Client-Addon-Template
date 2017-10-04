/**
 *
 */
function loadPhrasesSuccess(response)
{
	if (ClientAddon.hasData(response))
	{
		$.each(response.response, function(index, value) {

			$("#phrases").append(
				"<div>" + value.text + "</div>"
			);

			if (index < 3) return false;
		});
	}
	else
	{
		alert("No phrases are present for this app and language");
	}
}

/**
 *
 */
function loadPersonSuccess(response)
{
	if (ClientAddon.hasData(response))
	{
		var person = response.response[0];

		$("#personalData").html("");

		$("#personalData").append(
			"<div>Person_id: " + person.person_id + "</div>"
		);
		$("#personalData").append(
			"<div>Nachname: " + person.nachname + "</div>"
		);
		$("#personalData").append(
			"<div>Vorname: " + person.vorname + "</div>"
		);
	}
	else
	{
		alert("No data are present for you!!!");
	}
}

//
$(document).ready(function() {

	checkLogin(checkLoginSuccessStg);

	$("#btnLogout").click(callLogout);

	$("#btnLoadPersonData").click(function() {
		loadPerson(loadPersonSuccess);
	});

	$("#btnSavePersonData").click(savePersonData);

	$("#btnLoadKontaktByPersonID").click(loadKontaktByPersonID);

	$("#btnLoadKontaktByKontaktID").click(loadKontaktByKontaktID);

});
