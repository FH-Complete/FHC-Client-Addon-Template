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

/**
 * Definition and initialization of object CoreClient
 */
var CoreClient = {
	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Performs a call using the HTTP GET method
	 * parameters is an object
	 * errorCallback and successCallback are functions references
	 */
	callRESTFulGet: function(remoteWSAlias, parameters, errorCallback, successCallback, cache) {
	    CoreClient._callRESTFul(remoteWSAlias, parameters, HTTP_GET_METHOD, errorCallback, successCallback, cache);
	},

	/**
	 * Performs a call using the HTTP POST method
	 * parameters is an object
	 * errorCallback and successCallback are functions references
	 */
	callRESTFulPost: function(remoteWSAlias, parameters, errorCallback, successCallback, cache) {
	    CoreClient._callRESTFul(remoteWSAlias, parameters, HTTP_POST_METHOD, errorCallback, successCallback, cache);
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
	_printDebug: function(remoteWSAlias, parameters, response, errorThrown) {

		if (DEBUG === true) // If global const DEBUG is true, but really true!
		{
			// Print info about called remote web service alias
			console.log("Called alias: " + remoteWSAlias);
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

		CoreClient._printDebug(this._remoteWSAlias, this._data, response); // debug time!

		// Call the success callback saved in _successCallback property
		// NOTE: this is not referred to CoreClient but to the ajax object
		this._successCallback(response);
	},

	/**
	 * Method to call if the ajax call has raised an error
	 */
	_onError: function(jqXHR, textStatus, errorThrown) {

		CoreClient._printDebug(this._remoteWSAlias, this._data, null, errorThrown); // debug time!

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
	    if (CoreClient._checkParameters(remoteWSAlias, parameters, errorCallback, successCallback))
	    {
	        var data = CoreClient._cpObjProps(parameters); // copy the properties of parameters into a new object

	        data[REMOTE_WS] = remoteWSAlias; // remote web service alias

	        data[CACHE] = cache;
			if (cache == null) // if no cache mode is given...
			{
				if (type == HTTP_GET_METHOD) // ...and a GET is performed...
				{
					data[CACHE] = CACHE_ENABLED; // ...set enabled by default
				}
				else if (type == HTTP_POST_METHOD) // ...else if a POST is performed...
				{
					data[CACHE] = CACHE_DISABLED; // ...set disabled by default
				}
			}

			// ajax call
	        $.ajax({
	            url: CoreClient._generateRouterURI(),
	            type: type,
	            dataType: "json", // always json!
	            data: data,
				_data: data,
				_remoteWSAlias: remoteWSAlias, // store the alias of the core web service to call as a property of this object
				_errorCallback: errorCallback, // save as property the callback error
	            _successCallback: successCallback, // save as property the callback success
	            success: CoreClient._onSuccess, // function to call if succeeded
	            error: CoreClient._onError // function to call if an error occurred
	        });
	    }
	}
};
