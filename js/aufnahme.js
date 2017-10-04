/**
 *
 */
function genericErrorCallback(jqXHR, textStatus, errorThrown)
{
	alert("A gereric error has occurred. Contanct the administrator or try later: " + this._remoteWSAlias);
}

/**
 *
 */
function loginSuccess(response)
{
	if (ClientAddon.hasData(response))
	{
		window.location.replace(PROTOCOL + "://" + HOST + "/" + PROJECT + "/" + "aufnahmeStg.html");
	}
	else
	{
		alert("Username and password are not valid!");
	}
}

/**
 *
 */
function logoutSuccess(response)
{
	window.location.replace(PROTOCOL + "://" + HOST + "/" + PROJECT + "/" + "aufnahmeLogin.html");
}

/**
 *
 */
function checkLoginSuccessLogin(response)
{
	if (ClientAddon.hasData(response))
	{
		window.location.replace(PROTOCOL + "://" + HOST + "/" + PROJECT + "/" + "aufnahmeStg.html");
	}
}

/**
 *
 */
function checkLoginSuccessStg(response)
{
	if (!ClientAddon.hasData(response))
	{
		window.location.replace(PROTOCOL + "://" + HOST + "/" + PROJECT + "/" + "aufnahmeLogin.html");
	}
	else
	{
		loadPhrases(loadPhrasesSuccess);
		loadPerson(loadPersonSuccess);
	}
}

/**
 *
 */
function callLogin()
{
	ClientAddon.callRESTFulGet(
		LOGIN_CALL_NAME,
		{
			email: $("#username").val(),
			code: $("#password").val()
		},
		genericErrorCallback,
		loginSuccess
	);
}

/**
 *
 */
function callLogout()
{
	ClientAddon.callRESTFulGet(
		LOGOUT_CALL_NAME,
		null,
		genericErrorCallback,
		logoutSuccess
	);
}

/**
 *
 */
function checkLogin(successCallBack)
{
	ClientAddon.callRESTFulGet(
		"login",
		null,
		genericErrorCallback,
		successCallBack
	);
}

/**
 *
 */
function loadPhrases(successCallback)
{
	ClientAddon.callRESTFulGet(
		"phrases",
		{
			app: "aufnahme",
			sprache: "German",
			blockTags: "no",
			cache: CACHE_DISABLED
		},
		genericErrorCallback,
		successCallback
	);
}

/**
 *
 */
function loadPerson(successCallback)
{
	ClientAddon.callRESTFulGet(
		"loadPerson",
		null,
		genericErrorCallback,
		successCallback,
		CACHE_OVERWRITE
	);
}

/**
 *
 */
function loadKontaktByPersonID()
{
	ClientAddon.callRESTFulGet(
		"loadKontaktByPersonID",
		null,
		genericErrorCallback,
		function(response) {
			if (ClientAddon.hasData(response))
			{
				alert("PersonID: " + response.response[0].person_id + " - KontaktID: " + response.response[0].kontakt_id);
			}
			else
			{
				alert("No data loadKontaktByPersonID");
			}
		}
	);
}

/**
 *
 */
function loadKontaktByKontaktID()
{
	ClientAddon.callRESTFulGet(
		"loadKontaktByKontaktID",
		null,
		genericErrorCallback,
		function(response) {
			if (ClientAddon.hasData(response))
			{
				alert("kontakt: " + response.response[0].kontakt);
			}
			else
			{
				alert("No data loadKontaktByKontaktID");
			}
		}
	);
}

/**
 *
 */
function savePersonData()
{
	ClientAddon.callRESTFulPost(
		"savePerson",
		{
			nachname: $("#nachname").val(),
			vorname: $("#vorname").val(),
			aktiv: true,
			geschlecht: "u"
		},
		genericErrorCallback,
		function() {
			alert("Saved!!!");
		}
	);
}
