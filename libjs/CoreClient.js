/**
 * FH-Complete
 *
 * @package	FHC-Helper
 * @author	FHC-Team
 * @copyright   Copyright (c) 2016 fhcomplete.org
 * @license GPLv3
 * @link    https://fhcomplete.org
 * @since	Version 1.0.0
 */

//--------------------------------------------------------------------------------------------------------------------
// Constants

// Success
const SUCCESS = "OK";

// Properties present in a response
const CODE = "code";
const RESPONSE = "response";

// HTTP method parameters
const HTTP_GET_METHOD = "GET";
const HTTP_POST_METHOD = "POST";

// Cache modes
const CACHE_ENABLED = "enabled";
const CACHE_DISABLED = "disabled";
const CACHE_OVERWRITE = "overwrite";

// Parameters for the php layer
const CACHE = "cache";
const REMOTE_WS = "remote_ws";

// Default name of the login call
const LOGIN_CALL_NAME = "login";
const LOGOUT_CALL_NAME = "logout";

// Default veil timeout
const VEIL_TIMEOUT = 1000;

/**
 * Definition and initialization of object CoreClient
 */
var CoreClient = {
	//------------------------------------------------------------------------------------------------------------------
	// Properties

	_veilCallersCounter: 0, // count the number of callers that want to activate the veil

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Performs a call using the HTTP GET method
	 * wsParameters is an object
	 * callParameters is an object
	 */
	callRESTFulGet: function(remoteWSAlias, wsParameters, callParameters) {
	    CoreClient._callRESTFul(remoteWSAlias, wsParameters, HTTP_GET_METHOD, callParameters);
	},

	/**
	 * Performs a call using the HTTP POST method
	 * wsParameters is an object
	 * callParameters is an object
	 */
	callRESTFulPost: function(remoteWSAlias, wsParameters, callParameters) {
	    CoreClient._callRESTFul(remoteWSAlias, wsParameters, HTTP_POST_METHOD, callParameters);
	},

	/**
	 * Checks if the response is a success
	 */
	isSuccess: function(response) {
		var isSuccess = false;

	    if (jQuery.type(response) == "object" && response.hasOwnProperty(CODE) && response.hasOwnProperty(RESPONSE))
	    {
	        if (response.code == SUCCESS)
	        {
	            isSuccess = true;
	        }
	    }

		return isSuccess;
	},

	/**
	 * Checks if the response is an error
	 */
	isError: function(response) {
		return !CoreClient.isSuccess(response);
	},

	/**
	 * Checks if the response has data
	 */
	hasData: function(response) {
		var hasData = false;

	    if (CoreClient.isSuccess(response))
	    {
			if ((jQuery.type(response.response) == "object" && !jQuery.isEmptyObject(response.response))
				|| (jQuery.isArray(response.response) && response.response.length > 0))
			{
				hasData = true;
			}
	    }

		return hasData;
	},

	/**
	 * Show a veil
	 */
	showVeil: function(veilTimeout) {
		if (typeof veilTimeout == "number")
		{
			CoreClient._veilTimeout = veilTimeout;
		}
		else
		{
			CoreClient._veilTimeout = VEIL_TIMEOUT;
		}
		CoreClient._showVeil();
	},

	/**
	 * Hide a veil that was shown before
	 */
	hideVeil: function() {
		CoreClient._hideVeil();
	},

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Generate the router URI using the connection parameters
	 */
	_generateRouterURI: function() {
		return PROTOCOL + "://" + HOST + "/" + PROJECT + "/" + PATH + "/" + ROUTER;
	},

	/**
	 * Method to print debug info after a web services has been called
	 */
	_printDebug: function(parameters, response, errorThrown) {

		if (DEBUG === true) // If global const DEBUG is true, but really true!
		{
			// Print info about called remote web service alias
			console.log("Called alias: " + parameters.remote_ws);
			console.log("Call parameters:"); // parameters given to this call
			console.log(parameters);

			if (response != null) // if there is a response...
			{
				console.log("WS Response:");
				console.log(response); // ...print it
			}
			if (errorThrown != null) // if there is a jQuery error...
			{
				console.log("jQuery error:");
				console.log(errorThrown); // ...print it
			}
			console.log("--------------------------------------------------------------------------------------------");
		}
	},

	/**
	 * Method to call if the ajax call has succeeded
	 */
	_onSuccess: function(response, textStatus, jqXHR) {

		CoreClient._printDebug(this._data, response); // debug time!

		// Call the success callback saved in _successCallback property
		// NOTE: this is not referred to CoreClient but to the ajax object
		this._successCallback(response);
	},

	/**
	 * Method to call if the ajax call has raised an error
	 */
	_onError: function(jqXHR, textStatus, errorThrown) {

		CoreClient._printDebug(this._data, null, errorThrown); // debug time!

		 // Call the error callback saved in _errorCallback property
		 // NOTE: this is not referred to CoreClient but to the ajax object
	    this._errorCallback(jqXHR, textStatus, errorThrown);
	},

	/**
	 * Instantiate a new object and copy in it the properties from the parameter
	 */
	_cpObjProps: function(obj) {
	    var returnObj = {};

	    for (var prop in obj)
	    {
	        returnObj[prop] = obj[prop];
	    }

	    return returnObj;
	},

	/**
	 * Method to show the veil
	 */
	_showVeil: function() {
		if (CoreClient._veilCallersCounter == 0)
		{
			$("<div class=\"veil\"></div>").appendTo('body');
		}

		CoreClient._veilCallersCounter++;
	},

	/**
	 * Method to hide the veil
	 */
	_hideVeil: function() {
		window.setTimeout(function() {
			if (CoreClient._veilCallersCounter >= 0)
			{
				if (CoreClient._veilCallersCounter > 0)
				{
					CoreClient._veilCallersCounter--;
				}

				if (CoreClient._veilCallersCounter == 0)
				{
					$(".veil").remove();
				}
			}
		},
		this._veilTimeout);
	},

	/**
	 * Checks call parameters, if they are present and are valid
	 * It generates and returns all the parameters needed to perform an ajax remote call
	 * NOTE: console.error is used here because those are not messages for the final user,
	 *		but for the web interface developer
	 */
	_checkAndGenerateAjaxParams: function(remoteWSAlias, wsParameters, type, callParameters) {
	    var valid = true; // by default they are ok
		// Returned parameters
		var ajaxParameters = {
			url: CoreClient._generateRouterURI(),
			dataType: "json", // always json!
			type: type // set HTTP method, GET or POST
		};

	    // remoteWSAlias must be a non empty string
	    if (typeof remoteWSAlias != "string" || remoteWSAlias == "")
	    {
	        console.error("Invalid API name");
	        valid = false;
	    }

	    // wsParameters must be an object
	    if (typeof wsParameters == "object")
	    {
			var data = CoreClient._cpObjProps(wsParameters); // copy the properties of wsParameters into a new object
			data[REMOTE_WS] = remoteWSAlias; // remote web service alias
			// Stores them into ajaxParameters
			// NOTE: property data is not possible to get later,
			//		so the variable data is saved also in _data and it will be used later
			ajaxParameters.data = data;
			ajaxParameters._data = data;
	    }
		else
		{
			console.error("Invalid web service parameters, must be an object");
			valid = false;
		}


		// Checks if callParameters is an object
	    if (typeof callParameters == "object")
	    {
			// If present, errorCallback must be a function
		    if (callParameters.hasOwnProperty("errorCallback"))
			{
				if (typeof callParameters.errorCallback == "function")
				{
					ajaxParameters._errorCallback = callParameters.errorCallback; // save as property the callback error
					ajaxParameters.error = CoreClient._onError; // function to call if an error occurred
				}
				else
				{
					console.error("Invalid errorCallback, it must be a function");
					valid = false;
				}
		    }

			// If present, successCallback must be a function
		    if (callParameters.hasOwnProperty("successCallback"))
		    {
				if (typeof callParameters.successCallback == "function")
				{
					ajaxParameters._successCallback = callParameters.successCallback; // save as property the callback success
					ajaxParameters.success = CoreClient._onSuccess; // function to call if succeeded
				}
				else
				{
					console.error("Invalid successCallback, it must be a function");
					valid = false;
				}
		    }

			// If present, cache must be one this values
		    if (callParameters.hasOwnProperty("cache"))
			{
				if (callParameters.cache != CACHE_ENABLED
					&& callParameters.cache != CACHE_DISABLED
					&& callParameters.cache != CACHE_OVERWRITE)
			    {
			        console.error("Invalid cache parameter, must be: CACHE_ENABLED, CACHE_DISABLED or CACHE_OVERWRITE");
			        valid = false;
			    }
				else
				{
					data[CACHE] = callParameters.cache;
				}
			}
			else // if not specified by default...
			{
				if (type == HTTP_GET_METHOD) // ...if GET is performed...
				{
					data[CACHE] = CACHE_ENABLED; // ...set enabled by default
				}
				else if (type == HTTP_POST_METHOD) // ...if POST is performed...
				{
					data[CACHE] = CACHE_DISABLED; // ...set disabled by default
				}
			}

			// If present, veilTimeout must be a number and cannot be less then 0 or greater then 60000
		    if (callParameters.hasOwnProperty("veilTimeout") && typeof callParameters.veilTimeout == "number")
		    {
				if (callParameters.veilTimeout > 0 && callParameters.veilTimeout < 60000)
				{
					ajaxParameters._veilTimeout = callParameters.veilTimeout;
					ajaxParameters.beforeSend = CoreClient._showVeil;
					ajaxParameters.complete = CoreClient._hideVeil;
				}
				else if(callParameters.veilTimeout == 0)
				{
					// veil is disabled
				}
				else
				{
					console.error("Invalid veilTimeout parameter, must be a number >= 0 and <= 60000");
					valid = false;
				}
		    }
			else // is not present or the value is invalid
			{
				ajaxParameters._veilTimeout = VEIL_TIMEOUT;
				ajaxParameters.beforeSend = CoreClient._showVeil;
				ajaxParameters.complete = CoreClient._hideVeil;
			}
		}

		if (valid === false)
		{
			ajaxParameters = null;
		}

	    return ajaxParameters;
	},

	/**
	 * Performs a call to the server were the PHP layer is running
	 * - remoteWSAlias: alias of the core web service to call
	 * - wsParameters: parameters to give to the core web service
	 * - type: POST or GET HTTP method
	 * - callParameters is an object and could contains:
	 *	- errorCallback: function to call after an error has been raised
	 *	- successCallback: function to call after succeeded
	 *	- cache: desired cache mode
	 *	- veilTimeout: veil timeout
	 */
	_callRESTFul: function(remoteWSAlias, wsParameters, type, callParameters) {
		// Retrives the parameters for the ajax call
		var ajaxParameters = CoreClient._checkAndGenerateAjaxParams(remoteWSAlias, wsParameters, type, callParameters);

		// Checks the given parameters if they are present and are valid
	    if (ajaxParameters != null)
	    {
			// ajax call
	        $.ajax(ajaxParameters);
	    }
	}
};
