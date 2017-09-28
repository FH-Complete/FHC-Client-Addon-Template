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

// Chache modes
const CACHE_ENABLED = "enabled";
const CACHE_DISABLED = "disabled";
const CACHE_OVERWRITE = "overwrite";

// Parameters for the php layer
const CACHE = "cache";
const REMOTE_WS = "remote_ws";

// Default name of the login call
const LOGIN_CALL_NAME = "login";

/**
 * Definition and initialization of object ClientAddon
 */
var ClientAddon = {
	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Performs a call using the HTTP GET method
	 * parameters is an object
	 * errorCallback and successCallback are functions references
	 */
	callRESTFulGet: function(remoteWSAlias, parameters, errorCallback, successCallback, cache) {
	    ClientAddon._callRESTFul(remoteWSAlias, parameters, HTTP_GET_METHOD, errorCallback, successCallback, cache);
	},

	/**
	 * Performs a call using the HTTP POST method
	 * parameters is an object
	 * errorCallback and successCallback are functions references
	 */
	callRESTFulPost: function(remoteWSAlias, parameters, errorCallback, successCallback, cache) {
	    ClientAddon._callRESTFul(remoteWSAlias, parameters, HTTP_POST_METHOD, errorCallback, successCallback, cache);
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
		return !ClientAddon.isSuccess(response);
	},

	/**
	 * Checks if the response has data
	 */
	hasData: function(response) {
		var hasData = false;

	    if (ClientAddon.isSuccess(response))
	    {
			if ((jQuery.type(response.response) == "object" && !jQuery.isEmptyObject(response.response))
				|| (jQuery.isArray(response.response) && response.response.length > 0))
			{
				hasData = true;
			}
	    }

		return hasData;
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
	 * Method to call if the ajax call has succeeded
	 */
	_onSuccess: function(response, textStatus, jqXHR) {
		// Call the success callback saved in _successCallback property
		// NOTE: this is not referred to ClientAddon but to the ajax object
		this._successCallback(response);
	},

	/**
	 * Method to call if the ajax call has raised an error
	 */
	_onError: function(jqXHR, textStatus, errorThrown) {
		 // Call the error callback saved in _errorCallback property
		 // NOTE: this is not referred to ClientAddon but to the ajax object
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
	 * Checks call parameters, if they are present and are valid
	 * NOTE: console.error is used here because those are not messages for the final user,
	 * 		 but for the web interface developer
	 */
	_checkParameters: function(remoteWSAlias, parameters, errorCallback, successCallback) {
	    var valid = true; // by default they are ok, we trust you!

	    // remoteWSAlias must be a non empty string
	    if (typeof remoteWSAlias != "string" || remoteWSAlias == "")
	    {
	        console.error("Invalid API name");
	        valid = false;
	    }

	    // parameters must be an object, not null of course
	    if (typeof parameters != "object" && parameters != null)
	    {
			console.error("Invalid parameters, must be an object");
	        valid = false;
	    }

	    // errorCallback and successCallback must be a function
	    if (typeof errorCallback != "function" || typeof successCallback != "function")
	    {
	        console.error("Invalid callbacks, they must be functions");
	        valid = false;
	    }

	    return valid;
	},

	/**
	 * Performs a call to the server were the PHP layer is running
	 * - remoteWSAlias: alias of the core web service to call
	 * - parameters: parameters to give to the core web service
	 * - type: POST or GET HTTP method
	 * - errorCallback: function to call after an error has been raised
	 * - successCallback: function to call after succeeded
	 * - cache: desired cache mode (optional)
	 */
	_callRESTFul: function(remoteWSAlias, parameters, type, errorCallback, successCallback, cache) {
		// Checks the given parameters if they are present and are valid
	    if (ClientAddon._checkParameters(remoteWSAlias, parameters, errorCallback, successCallback))
	    {
	        var data = ClientAddon._cpObjProps(parameters); // copy the properties of parameters into a new object

	        data[REMOTE_WS] = remoteWSAlias; // remote web service alias
	        data[CACHE] = (cache != null && cache != '') ? cache : CACHE_DISABLED; // cache mode

			// ajax call
	        $.ajax({
	            url: ClientAddon._generateRouterURI(),
	            type: type,
	            dataType: "json", // always json!
	            data: data,
				_errorCallback: errorCallback, // save as property the callback error
	            _successCallback: successCallback, // save as property the callback success
	            success: ClientAddon._onSuccess, // function to call if succeeded
	            error: ClientAddon._onError // function to call if an error occurred
	        });
	    }
	}
};
