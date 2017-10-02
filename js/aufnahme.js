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
	alert("wait!!!");
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
			username: $("#username").val(),
			password: $("#password").val()
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
		successCallback
	);
}
