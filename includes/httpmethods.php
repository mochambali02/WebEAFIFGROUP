<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if (!function_exists('http_parse_headers')) {
	function http_parse_headers($raw_headers)
	{
		$headers = array();
		$key = '';
		foreach (explode("\n", $raw_headers) as $i => $h) {
			$h = explode(':', $h, 2);
			if (isset($h[1])) {
				if (!isset($headers[$h[0]])) {
					$headers[$h[0]] = trim($h[1]);
				} elseif (is_array($headers[$h[0]])) {
					$headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
				} else {
					$headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
				}
				$key = $h[0];
			} else {
				if (substr($h[0], 0, 1) == "\t")
					$headers[$key] .= "\r\n\t" . trim($h[0]);
				elseif (!$key)
					$headers[0] = trim($h[0]);
			}
		}
		return $headers;
	}
}
function getBearerParams($autheticateHeader)
{
	global $gLog;
	$params[] = array();
	$autheticateHeader = str_replace('Bearer ', '', $autheticateHeader);
	foreach (explode(',', $autheticateHeader) as $i => $param) {
		$param = explode('=', $param, 2);
		$params[$param[0]] = trim($param[1], '"');
	}
	return $params;
}
function HTTPGetXML($sURL)
{
	global $gLog;
	if (strIsEmpty($sURL)) {
		return null;
	}
	$sOSLCCallName = '';
	$sPHPPage = '';
	$aSystemOutput = array();
	$bShowingSysOut = (IsSessionSettingTrue('show_system_output') && g_cbDebugging);
	if ($bShowingSysOut) {
		$sPHPPage = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
		$sOSLCCallName = GetOSLCCallName($sURL);
		$aSystemOutput 		= GetSystemOutputSessionArray();
		$aSystemOutput[]	= ' --  (' . $sPHPPage . ') start - GET  ' . $sOSLCCallName;
		$timeStart = microtime(true);
	}
	$xmlDoc = null;
	$sErrorMsg = '';
	$sXML = HTTPGetXMLRaw($sURL, $httpCode, $sErrorMsg);
	if (!strIsEmpty($sErrorMsg)) {
		if (
			($sErrorMsg === _glt('selected database does not support pro')) ||
			($sErrorMsg === _glt('selected database is shutdown')) ||
			($sErrorMsg === 'Request Error: ' . _glt('Server could not be found')) ||
			($sErrorMsg === _glt('A secure connection is required'))
		) {
			echo '<div id="webea-display-warning-message" class="http-error" style="display: block;">';
			echo '<div class="webea-display-warning-message-text" id="webea-display-warning-message-text">';
			echo $sErrorMsg . WriteHelpHyperlink();
			echo '</div>';
			echo '</div>';
		} else {
			echo $sErrorMsg . WriteHelpHyperlink();
		}
	}
	if (!strIsEmpty($sXML) && $sXML !== false) {
		$xmlDoc = new DOMDocument();
		SafeXMLLoad($xmlDoc, $sXML, true);
	}
	if ($bShowingSysOut) {
		$timeEnd = microtime(true);
		$timeDiff = $timeEnd - $timeStart;
		$sRunTime = microSecondsToMilli($timeDiff);
		ExtractSystemOutputDetails($xmlDoc, $aSystemOutput);
		$aSystemOutput[]	= ' --  (' . $sPHPPage . ') end  - ' . $sRunTime . ' to run GET ' . $sOSLCCallName;
		SaveSystemOutputSessionArray($aSystemOutput);
	}
	return $xmlDoc;
}
function HTTPGetXMLRaw($sURL, &$httpCode, &$sErrorMsg, $bCheckOSLCError = true)
{
	return HTTPSendRequest(CURLOPT_HTTPGET, $sURL, '', $httpCode, $sErrorMsg, $bCheckOSLCError);
}
function HTTPPostXML($sURL, $sInXML, $bCheckOSLCError = true)
{
	global $gLog;
	if (strIsEmpty($sURL)) {
		return null;
	}
	$sOSLCCallName = '';
	$sPHPPage = '';
	$aSystemOutput = array();
	$bShowingSysOut = (IsSessionSettingTrue('show_system_output') && g_cbDebugging);
	if ($bShowingSysOut) {
		$sPHPPage = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
		$sOSLCCallName = GetOSLCCallName($sURL);
		$aSystemOutput 		= GetSystemOutputSessionArray();
		$aSystemOutput[]	= ' --  (' . $sPHPPage . ') start - POST  ' . $sOSLCCallName;
		$timeStart = microtime(true);
	}
	$xmlDoc = null;
	$sErrorMsg = '';
	$httpCode = -1;
	$sOutXML = HTTPPostXMLRaw($sURL, $sInXML, $httpCode, $sErrorMsg, $bCheckOSLCError);
	if (!strIsEmpty($sErrorMsg))
		echo $sErrorMsg;
	if (!strIsEmpty($sOutXML) && $sOutXML !== false) {
		$xmlDoc = new DOMDocument();
		SafeXMLLoad($xmlDoc, $sOutXML, true);
	}
	if ($bShowingSysOut) {
		$timeEnd = microtime(true);
		$timeDiff = $timeEnd - $timeStart;
		$sRunTime = microSecondsToMilli($timeDiff);
		$gLog->Write2Log('HTTPPostXML:    ' . $sRunTime . '    ' . $sURL);
		ExtractSystemOutputDetails($xmlDoc, $aSystemOutput);
		$aSystemOutput[]	= ' --  (' . $sPHPPage . ') end  - ' . $sRunTime . ' to run POST ' . $sOSLCCallName;
		SaveSystemOutputSessionArray($aSystemOutput);
	}
	return $xmlDoc;
}
function HTTPPostXMLRaw($sURL, $sPostBody, &$httpCode, &$sErrorMsg, $bCheckOSLCError = true)
{
	return HTTPSendRequest(CURLOPT_POST, $sURL, $sPostBody, $httpCode, $sErrorMsg, $bCheckOSLCError);
}
function HTTPSendRequest($method, $sURL, $sPostBody, &$httpCode, &$sErrorMsg, $bCheckOSLCError = true)
{
	SafeStartSession();
	global $gLog;
	$sBody = '';
	if (strIsEmpty($sURL)) {
		return $sBody;
	}
	$iMaxCommTime = (int)SafeGetInternalArrayParameter($_SESSION, 'max_communication_time', '30');
	ini_set('max_execution_time', ($iMaxCommTime * 3));
	$aHeaders = array();
	$sLicense = 'IMPORTANT!!     Creating software that imitates or pretends to be WebEA is not permitted.';
	$sLicense = 'Circumvention, reverse engineering or otherwise bypassing any restrictions imposed by this license';
	$sLicense = 'is NOT permitted and will be considered a breach of copyright.';
	$aHeaders[] = 'puna: paetukutuku';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $sURL);
	if ($method == CURLOPT_POST) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sPostBody);
		$aHeaders[] = 'Content-type: text/xml;';
	}
	curl_setopt($ch, CURLOPT_TIMEOUT, $iMaxCommTime);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$sEAOAccessCode = SafeGetInternalArrayParameter($_SESSION, 'eao_access_code', '');
	if (!strIsEmpty($sEAOAccessCode)) {
		$aHeaders[] = 'EAO-Access-Code: ' . $sEAOAccessCode;
	}
	$headers = apache_request_headers();

	if (IsSessionSettingTrue('use_ssl')) {
		$sUsername	= SafeGetInternalArrayParameter($_SESSION, 'ssl_userid', '');
		$sPassword	= SafeGetInternalArrayParameter($_SESSION, 'ssl_password', '');
		if (strIsEmpty($sUsername)) {
			$sUsername	= SafeGetInternalArrayParameter($_SESSION, 'model_user', '');
			$sPassword	= SafeGetInternalArrayParameter($_SESSION, 'model_pwd', '');
		}
		$accessToken = SafeGetInternalArrayParameter($_SESSION, 'login_access_token', '');
		$refreshToken = SafeGetInternalArrayParameter($_SESSION, 'login_refresh_token', '');
		if (!strIsEmpty($accessToken)) {
			$aHeaders[] = 'Authorization: Bearer ' . $accessToken;
		} else {
			curl_setopt($ch, CURLOPT_USERNAME, $sUsername);
			curl_setopt($ch, CURLOPT_PASSWORD, $sPassword);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		}
		$sEnforceCerts = SafeGetInternalArrayParameter($_SESSION, 'enforce_certs', 'true');
		if (strIsTrue($sEnforceCerts)) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		} else {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}
	}
	if (isset($headers['Authorization'])) {
		$aHeaders[] = 'Authorization: ' . $headers['Authorization'];
	}
	$aHeaders[] = 'Authentication-Context: ' . session_id();
	curl_setopt($ch, CURLOPT_HEADER, TRUE);

	$resend = true;
	while ($resend) {
		$resend = false;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeaders);
		$sResponse = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlInfo = curl_getinfo($ch);
		$headerSize = $curlInfo['header_size'];
		$sHeaders = substr($sResponse, 0, $headerSize);
		$headers = http_parse_headers($sHeaders);
		$sBody = substr($sResponse, $headerSize);
		if ($httpCode == 401) {
			$ntlmAuthHeader = '';
			$bearerAuthHeader = '';
			if (array_key_exists('WWW-Authenticate', $headers)) {
				if (is_array($headers['WWW-Authenticate'])) {
					foreach ($headers['WWW-Authenticate'] as $authHeader) {
						if (substr($authHeader, 0, 4) == 'NTLM') {
							$ntlmAuthHeader = $authHeader;
						}
						if (substr($authHeader, 0, 7) == 'Bearer ') {
							$bearerAuthHeader = $authHeader;
						}
					}
				} else {
					if (substr($headers['WWW-Authenticate'], 0, 4) == 'NTLM') {
						$ntlmAuthHeader = $headers['WWW-Authenticate'];
					}
					if (substr($headers['WWW-Authenticate'], 0, 7) == 'Bearer ') {
						$bearerAuthHeader = $headers['WWW-Authenticate'];
					}
				}
			}
			if (!strIsEmpty($ntlmAuthHeader) && strIsEmpty($refreshToken)) {
				unset($_SESSION['authorized']);
				global $web_page_parent_ntlm;
				if (isset($web_page_parent_ntlm)) {
					header('HTTP/1.1 401 Access Denied');
					header('WWW-Authenticate: ' . $ntlmAuthHeader);
					exit();
				}
			} else if (!strIsEmpty($bearerAuthHeader)) {
				$params = getBearerParams($bearerAuthHeader);
				if ($params['realm'] === 'OpenID') {
					if (!strIsEmpty($accessToken) && !strIsEmpty($refreshToken)) {
						$aHeaders[] = 'Authorization: OpenID refresh_token="' . $refreshToken . '",redirect_uri="' . GetOpenIDRedirectURL() . '"';
						$_SESSION['login_access_token'] = '';
						$_SESSION['login_refresh_token'] = '';
						$accessToken = '';
						$refreshToken = '';
						$resend = true;
						continue;
					}
					unset($_SESSION['authorized']);
				}
			}
		} else if ($httpCode == 200) {
			$accessTokenHeader = 'OpenID-AccessToken';
			$refreshTokenHeader = 'OpenID-RefreshToken';
			if (isset($headers[$accessTokenHeader])) {
				$accessToken = $headers[$accessTokenHeader];
				$_SESSION['login_access_token']	= $accessToken;
			}
			if (isset($headers[$refreshTokenHeader])) {
				$refreshToken = $headers[$refreshTokenHeader];
				$_SESSION['login_refresh_token']	= $refreshToken;
			}
		}
		if (curl_errno($ch)) {
			$sErrorMsg = 'Request Error: ' . HandleKnownErrors(curl_error($ch));
		} else {
			$sErrorMsg = GetHTTPErrorMessage($httpCode);
			if (!strIsEmpty($httpCode) && $bCheckOSLCError) {
				$sErrCode = '';
				$sErrMsg = '';
				Check4OSLCErrorFromXML($sBody, $sErrCode, $sErrMsg);
				if (!strIsEmpty($sErrMsg)) {
					$GLOBALS['g_sLastOSLCErrorCode'] = $sErrCode;
					$GLOBALS['g_sLastOSLCErrorMsg']  = $sErrMsg;
					$sErrorMsg = BuildOSLCErrorString();
				}
				if (substr($sBody, 0, 6) === '<html>') {
					$sBody = '';
				}
			}
		}
	}
	curl_close($ch);
	return $sBody;
}
