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

const CODE = "code";
const SUCCESS = "OK";
const RESPONSE = "response";
const ROUTER_URL = "/ClientAddonTemplate/controller/router.php";
const HTTP_GET_METHOD = "GET";
const HTTP_POST_METHOD = "POST";

/**
 *
 */
function _onSuccess(response, textStatus, jqXHR)
{
	this._successCallback(response);
}

/**
 *
 */
function _onError(jqXHR, textStatus, errorThrown)
{
    this._errorCallback(jqXHR, textStatus, errorThrown);
}

/**
 *
 */
function _cpObjProps(obj)
{
    var returnObj = {};

    for (var prop in obj)
    {
        returnObj[prop] = obj[prop];
    }

    return returnObj;
}

/**
 *
 */
function _checkParameters(apiName, parameters, errorCallback, successCallback)
{
    var valid = true;

    //
    if (typeof apiName != "string" || apiName == "")
    {
        console.error("Invalid API name");
        valid = false;
    }

    //
    if (typeof parameters != "object" && parameters != null)
    {
		console.error("Invalid parameters, must be an object");
        valid = false;
    }

    //
    if (typeof errorCallback != "function" || typeof successCallback != "function")
    {
        console.error("Invalid callbacks, they must be functions");
        valid = false;
    }

    return valid;
}

/**
 *
 */
function _callRESTFul(apiName, parameters, type, errorCallback, successCallback, cache)
{
    if (_checkParameters(apiName, parameters, errorCallback, successCallback))
    {
        var data = _cpObjProps(parameters);

        data.api = apiName;
        data.cache = (cache === true) ? true : false;

        $.ajax({
            url: ROUTER_URL,
            type: type,
            dataType: "json",
            data: data,
			_errorCallback: errorCallback,
            _successCallback: successCallback,
            success: _onSuccess,
            error: _onError
        });
    }
}

/**
 *
 */
function callRESTFulGet(apiName, parameters, errorCallback, successCallback, cache)
{
    _callRESTFul(apiName, parameters, HTTP_GET_METHOD, errorCallback, successCallback, cache);
}

/**
 *
 */
function callRESTFulPost(apiName, parameters, errorCallback, successCallback, cache)
{
    _callRESTFul(apiName, parameters, HTTP_POST_METHOD, errorCallback, successCallback, cache);
}

/**
 *
 */
function isSuccess(response)
{
	var isSuccess = false;

    if (jQuery.type(response) == "object" && response.hasOwnProperty(CODE) && response.hasOwnProperty(RESPONSE))
    {
        if (response.code == SUCCESS)
        {
            isSuccess = true;
        }
    }

	return isSuccess;
}

/**
 *
 */
function isError(response)
{
	return !isSuccess(response);
}

/**
 *
 */
function hasData(response)
{
	var hasData = false;

    if (isSuccess(response))
    {
		if ((jQuery.type(response.response) == "object" && !jQuery.isEmptyObject(response.response))
			|| (jQuery.isArray(response.response) && response.response.length > 0))
		{
			hasData = true;
		}
    }

	return hasData;
}
