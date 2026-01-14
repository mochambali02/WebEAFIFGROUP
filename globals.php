<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
define('g_csCopyRightYears', '2016 - 2019');
define('g_csWebEAVersion', 	'3.0.25.1690');
define('g_csOSLCVersion', 	'3.0.25.1810');
define('g_csHelpLocation', 	'http://sparxsystems.com/enterprise_architect_user_guide/14.0/');
define('g_csHTTPNewLine', 	'<br>');
define('g_cbDebugging', true);
$g_sOSLCString 		= '';
$gLog 				= new Logging(g_cbDebugging);
$sRootPath 			= dirname(__FILE__);
$g_aLocaleText 		= ParseLocaleStrings2Array($sRootPath . '/includes/webea_strings.ini');
$g_sViewingMode		= '0';
$g_sLastOSLCErrorCode	= '';
$g_sLastOSLCErrorMsg	= '';
class Logging
{
	private $m_sLogFilename, $fp, $m_bDebugging;
	public function __construct($sDebug)
	{
		$this->m_bDebugging = false;
		if (strIsTrue($sDebug));
		$this->m_bDebugging = true;
	}
	public function SetLogfilePath($sPath)
	{
		$this->m_sLogFilename = $sPath;
	}
	public function ClearLog()
	{
		if (!is_resource($this->fp)) {
			$bFileOK = $this->open();
		} else {
			$bFileOK = true;
		}
		if ($bFileOK) {
			ftruncate($this->fp, 0);
			rewind($this->fp);
			fclose($this->fp);
		}
	}
	public function Write2Log($message)
	{
		if ($this->m_bDebugging) {
			$bFileOK = false;
			if (!is_resource($this->fp)) {
				$bFileOK = $this->open();
			} else {
				$bFileOK = true;
			}
			if ($bFileOK) {
				$sPage = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
				$time = @date('[Y-m-d H:i:s.u]');
				fwrite($this->fp, $time . ' (' . $sPage . ') ' . $message . PHP_EOL);
			}
		}
	}
	public function close()
	{
		fclose($this->fp);
	}
	private function open()
	{
		$bOK = false;
		$sLogFilename_default = '/tmp/webea_logfile.log';
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			$sLogFilename_default = 'c:/temp/webea_logfile.log';
		$sPath = $this->m_sLogFilename ? $this->m_sLogFilename : $sLogFilename_default;
		$sFolder = '';
		$iPos = strripos($sPath, '/');
		if ($iPos !== FALSE) {
			$sFolder = substr($sPath, 0, $iPos);
		}
		if ($sFolder !== '' && is_dir($sFolder)) {
			$this->fp = fopen($sPath, 'a') or exit('Can\'t open ' . $sPath . '!');
			$bOK = true;
		}
		return $bOK;
	}
}
function is_session_started()
{
	if (php_sapi_name() !== 'cli') {
		if (version_compare(phpversion(), '5.4.0', '>=')) {
			return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
		} else {
			return session_id() === '' ? FALSE : TRUE;
		}
	}
	return FALSE;
}
function SafeStartSession()
{
	if (is_session_started() === FALSE) {
		session_start();
	}
}
function LoadDotEnv($sPath)
{
	if (!file_exists($sPath)) {
		return;
	}
	$lines = file($sPath, FILE_IGNORE_NEW_LINES);
	if ($lines === false) {
		return;
	}
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line === '' || $line[0] === '#') {
			continue;
		}
		$iPos = strpos($line, '=');
		if ($iPos === false) {
			continue;
		}
		$key = trim(substr($line, 0, $iPos));
		$val = trim(substr($line, $iPos + 1));
		if ($key === '') {
			continue;
		}
		if ((strlen($val) >= 2) && (($val[0] === '"' && $val[strlen($val) - 1] === '"') || ($val[0] === "'" && $val[strlen($val) - 1] === "'"))) {
			$val = substr($val, 1, -1);
		}
		if (getenv($key) === false) {
			putenv($key . '=' . $val);
			$_ENV[$key] = $val;
			$_SERVER[$key] = $val;
		}
	}
}
function IsSessionSettingTrue($sName)
{
	$sValue = SafeGetInternalArrayParameter($_SESSION, $sName, 'false');
	$bReturn = strIsTrue($sValue);
	return $bReturn;
}
function _glt($sLookup)
{
	global $gLog;
	global $g_aLocaleText;
	$sReturn = $sLookup;
	if (isset($g_aLocaleText)) {
		if (is_array($g_aLocaleText)) {
			if (array_key_exists($sLookup, $g_aLocaleText)) {
				$sReturn = $g_aLocaleText[$sLookup];
			} else {
				$gLog->Write2Log('The string value "' . $sLookup . '" not found in string definition!');
			}
		}
	}
	return $sReturn;
}
function ParseLocaleStrings2Array($sFilename)
{
	global $gLog;
	$aLocaleText = array();
	if (file_exists($sFilename)) {
		$aLocaleText = parse_ini_file($sFilename, false);
	} else {
		$gLog->Write2Log('The internationalization file "' . $sFilename . '" was not found.');
	}
	return $aLocaleText;
}
function strIsEmpty($sValue)
{
	$bReturn = true;
	if (isset($sValue) === true) {
		if ($sValue !== '')
			$bReturn = false;
	}
	return $bReturn;
}
function strIsTrue($sValue)
{
	$bReturn = false;
	if (isset($sValue) === true) {
		$sValue = mb_strtolower($sValue);
		if ($sValue === '1' || $sValue === 't' || $sValue === 'true') {
			$bReturn = true;
		}
	}
	return $bReturn;
}
function BuildOSLCConnectionString()
{
	SafeStartSession();
	$sProtocol	= SafeGetInternalArrayParameter($_SESSION, 'protocol', 'http');
	$sServer	= SafeGetInternalArrayParameter($_SESSION, 'server', 'localhost');
	$sPort		= SafeGetInternalArrayParameter($_SESSION, 'port', '80');
	$sDBAlias	= SafeGetInternalArrayParameter($_SESSION, 'db_alias', 'modelname');
	$GLOBALS['g_sOSLCString'] = $sProtocol . '://' . $sServer . ':' . $sPort . '/' . $sDBAlias . '/oslc/am/';
	return '';
}
function SafeGetArrayItem1Dim($a, $sItemName)
{
	$sReturn = '';
	if ($a !== null) {
		if (array_key_exists($sItemName, $a)) {
			$sReturn = $a[$sItemName];
		}
	}
	return $sReturn;
}
function SafeGetArrayItem1DimInt($a, $sItemName)
{
	$iReturn = (int)0;
	if ($a !== null) {
		if (array_key_exists($sItemName, $a)) {
			$iReturn = (int)$a[$sItemName];
		}
	}
	return $iReturn;
}
function SafeGetArrayItem2Dim($a, $iRow, $sItemName)
{
	$sReturn = '';
	if ($iRow < 0) {
		$iRow = 0;
	}
	if ($a !== null) {
		if (count($a) > $iRow) {
			if (array_key_exists($sItemName, $a[$iRow])) {
				$sReturn = $a[$iRow][$sItemName];
			}
		}
	}
	return $sReturn;
}
function SafeGetArrayItem2DimByName($a, $sName, $sItemName, $sDefault = '')
{
	$sReturn = $sDefault;
	if (!strIsEmpty($sName)) {
		if ($a !== null) {
			if (count($a) > 0) {
				if (array_key_exists($sName, $a)) {
					if (array_key_exists($sItemName, $a[$sName])) {
						$sReturn = $a[$sName][$sItemName];
					}
				}
			}
		}
	}
	return $sReturn;
}
function SafeGetArrayItem2DimByNameValuePair($a, $sInName, $sSearchItem, $sReturnFieldName)
{
	$sReturn = '';
	if (!strIsEmpty($sInName) && !strIsEmpty($sReturnFieldName)) {
		if ($a !== null) {
			$iCnt = count($a);
			if ($iCnt > 0) {
				for ($i = 0; $i < $iCnt; $i++) {
					if ($sSearchItem === SafeGetArrayItem2Dim($a, $i, $sInName)) {
						$sReturn = SafeGetArrayItem2Dim($a, $i, $sReturnFieldName);
						break;
					}
				}
			}
		}
	}
	return $sReturn;
}
function SafeGetInternalArrayParameter($a, $sParameterName, $sDefault = '')
{
	$sReturn = $sDefault;
	if (array_key_exists($sParameterName, $a)) {
		if (isset($a[$sParameterName]))
			$sReturn = $a[$sParameterName];
	}
	return $sReturn;
}
$sRootPath = dirname(__FILE__);
require_once $sRootPath . '/includes/httpmethods.php';
function SafeXMLLoad(&$xmlDoc, $sXML, $bHuge = false)
{
	$sHideErrors = SafeGetInternalArrayParameter($_SESSION, 'hide_xml_errors', 'true');
	if (strIsTrue($sHideErrors))
		$bPrevState = libxml_use_internal_errors(true);
	else
		$bPrevState = libxml_use_internal_errors(false);
	if ($bHuge)
		$isValid = $xmlDoc->loadXML($sXML, LIBXML_PARSEHUGE);
	else
		$isValid = $xmlDoc->loadXML($sXML);
	libxml_clear_errors();
	libxml_use_internal_errors($bPrevState);
	return ($isValid !== false);
}
function SafeHTMLLoad(&$dDoc, $sHTML, $bHuge = false)
{
	$sHideErrors = SafeGetInternalArrayParameter($_SESSION, 'hide_xml_errors', 'true');
	if (strIsTrue($sHideErrors))
		$bPrevState = libxml_use_internal_errors(true);
	else
		$bPrevState = libxml_use_internal_errors(false);
	if ($bHuge)
		$bIsValid = $dDoc->loadHTML($sXML, LIBXML_PARSEHUGE);
	else
		$bIsValid = $dDoc->loadHTML($sXML);
	libxml_clear_errors();
	libxml_use_internal_errors($bPrevState);
	return ($bIsValid !== false);
}
function GetXMLFirstChild($xn)
{
	$x = null;
	if ($xn !== null) {
		$x = $xn->firstChild;
		if ($x !== null) {
			while ($x !== null && ($x->nodeType === XML_TEXT_NODE || $x->nodeType === XML_COMMENT_NODE)) {
				$x = $x->nextSibling;
			}
		}
	}
	return $x;
}
function GetXMLNodeValue($xn, $sName, &$sValue)
{
	if ($xn !== null) {
		if ($xn->nodeName === $sName) {
			$sValue = $xn->nodeValue;
		}
	}
}
function GetXMLNodeAttribute($xn, $sAttributeName)
{
	$sValue = '';
	if ($xn !== null) {
		if ($xn->nodeType !== XML_TEXT_NODE && $xn->nodeType !== XML_COMMENT_NODE) {
			if ($xn->hasAttribute($sAttributeName)) {
				$sValue = $xn->getAttribute($sAttributeName);
			}
		}
	}
	return $sValue;
}
function GetXMLNodeBoolValue($xn, $sName, &$sValue)
{
	if ($xn !== null) {
		if ($xn->nodeName === $sName) {
			$sValue = 'False';
			$s = $xn->nodeValue;
			if (strIsTrue($s)) {
				$sValue = 'True';
			}
		}
	}
}
function GetXMLNodeValueAttr($xn, $sName, $sAttr, &$sValue)
{
	if ($xn !== null) {
		if ($xn->nodeName === $sName) {
			$sValue = $xn->getAttribute($sAttr);
		}
	}
}
function GetAuthorFromXMLNode($xn, &$sValue)
{
	if ($xn !== null) {
		if ($xn->nodeName === "dcterms:creator") {
			$xnFC = $xn->firstChild;
			$xnFC = $xnFC->firstChild;
			$sValue = $xnFC->nodeValue;
		}
	}
}
function formatWithStereotypeChars($sStereo)
{
	$sReturn = $sStereo;
	if (!strIsEmpty($sStereo)) {
		$sReturn =  '&#xAB;' . htmlspecialchars($sStereo) . '&#xBB;';
	}
	return $sReturn;
}
function AddStereotypeToArray($xn, &$aStereotypes, $sStereoNodeName = '')
{
	if ($xn !== null) {
		if (strIsEmpty($sStereoNodeName)) {
			$sStereoNodeName = 'ss:stereotype';
		}
		if ($xn->nodeName === $sStereoNodeName) {
			$xnFC = GetXMLFirstChild($xn);
			if ($xnFC !== null) {
				$sName 		= '';
				$sFQName 	= '';
				foreach ($xnFC->childNodes as $xnST) {
					GetXMLNodeValue($xnST, 'ss:name', $sName);
					GetXMLNodeValue($xnST, 'ss:fqname', $sFQName);
				}
				$aRow			= array();
				$aRow['name']	= $sName;
				$aRow['fqname']	= $sFQName;
				$aStereotypes[] = $aRow;
			}
		}
	}
}
function getPrimaryStereotype($aStereotypes)
{
	$sName = '';
	if ($aStereotypes !== null) {
		if (count($aStereotypes) > 0) {
			$sName = SafeGetArrayItem2Dim($aStereotypes, 0, 'name');
		}
	}
	return $sName;
}
function getPrimaryFQStereotype($aStereotypes)
{
	$sName = '';
	if (is_array($aStereotypes)) {
		if ($aStereotypes !== null) {
			if (count($aStereotypes) > 0) {
				$sName = SafeGetArrayItem2Dim($aStereotypes, 0, 'fqname');
				if (strIsEmpty($sName))
					$sName = SafeGetArrayItem2Dim($aStereotypes, 0, 'name');
			}
		}
	}
	return $sName;
}
function getFQStereotypeCSV($aStereotypes)
{
	$sCSV = '';
	if ($aStereotypes !== null) {
		$i = 0;
		$iCnt = count($aStereotypes);
		if ($iCnt > 0) {
			$sName = '';
			for ($i = 0; $i < $iCnt; $i++) {
				$sName = SafeGetArrayItem2Dim($aStereotypes, $i, 'fqname');
				if (strIsEmpty($sName))
					$sName = SafeGetArrayItem2Dim($aStereotypes, $i, 'name');
				$sCSV .= $sName . ',';
			}
			$sCSV = trim($sCSV, ',');
		}
	}
	return $sCSV;
}
function buildStereotypeDisplayHTML($sStereotype, $aStereotypes, $bUseFQName)
{
	$sST 		= '';
	$sHTML 		= '';
	$sBtnHTML 	= '';
	if (!strIsEmpty($sStereotype)) {
		$sST 	= $sStereotype;
		if ($bUseFQName) {
			$sST = getPrimaryFQStereotype($aStereotypes);
		}
		if ($aStereotypes !== null) {
			if (count($aStereotypes) > 1) {
				$sST .= '...';
				$sStereotypeCSV = getFQStereotypeCSV($aStereotypes);
				if (!strIsEmpty($sStereotypeCSV)) {
					$sBtnHTML .= '&nbsp;<input class="show-stereotypes-button" onclick="OnShowStereotypeList(event, \'' . ConvertStringToParameter($sStereotypeCSV) . '\')"';
					$sBtnHTML .=       ' title="' . _glt('Show stereotypes') . '" type="button" value="...">';
				}
			}
		}
		$sHTML .= formatWithStereotypeChars($sST);
		$sHTML .= $sBtnHTML;
	}
	return $sHTML;
}
function IsMobileBrowser()
{
	$bReturn = false;
	$sUserAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
	if (preg_match("/phone|iphone|itouch|ipod|symbian|android|htc_|htc-|palmos|blackberry|opera mini|iemobile|windows ce|nokia|fennec|hiptop|kindle|mot |mot-|webos\/|samsung|sonyericsson|^sie-|nintendo/", $sUserAgent)) {
		$bReturn = true;
	} else if (preg_match("/mobile|pda;|avantgo|eudoraweb|minimo|netfront|brew|teleca|lg;|lge |wap;| wap /", $sUserAgent)) {
		$bReturn = true;
	}
	return $bReturn;
}
function GetSystemOutputSessionArray()
{
	$a = array();
	if (isset($_SESSION['system_output'])) {
		$a = $_SESSION['system_output'];
		if (!is_array($a)) {
			$a = array();
		}
	}
	return $a;
}
function SaveSystemOutputSessionArray($a)
{
	if (is_array($a)) {
		$_SESSION['system_output'] = $a;
	} else {
		$_SESSION['system_output'] = array();
		global $gLog;
		$gLog->Write2Log('SaveSystemOutputSessionArray:    system_output is not an array');
	}
}
function AddItemToSystemOutput($s)
{
	if (g_cbDebugging) {
		if (IsSessionSettingTrue('show_system_output')) {
			if (!strIsEmpty($s)) {
				$a = GetSystemOutputSessionArray();
				$a[] = $s;
				SaveSystemOutputSessionArray($a);
			}
		}
	}
}
function ExtractSystemOutputDetails(&$xmlDoc, &$aSystemOutput = array())
{
	if (!g_cbDebugging) {
		return;
	}
	if (!IsSessionSettingTrue('show_system_output')) {
		return;
	}
	if ($xmlDoc === null) {
		return;
	}
	$bSaveSystemOutput2Session = false;
	$xnRoot = $xmlDoc->documentElement;
	if ($xnRoot !== null && $xnRoot->childNodes !== null) {
		if (empty($aSystemOutput)) {
			$bSaveSystemOutput2Session = true;
			$aSystemOutput = GetSystemOutputSessionArray();
		}
		$sTotalTime = '';
		$iCnt = 0;
		foreach ($xnRoot->childNodes as $xnD) {
			if ($xnD !== null && $xnD->nodeName === 'ss:debug') {
				$sTotalTime = GetXMLNodeAttribute($xnD, 'ss:requestprocessingtime');
				foreach ($xnD->childNodes as $xn) {
					$sStatement	= '';
					$sTime		= '';
					$sTime = GetXMLNodeAttribute($xn, 'ss:time');
					$sStatement	= $xn->nodeValue;
					$sStatement = str_replace('<![CDATA[', '', $sStatement);
					$sStatement = str_replace("]]>", "", $sStatement);
					$sStatement = $sTime . ' - ' . $sStatement;
					$aSystemOutput[] = $sStatement;
					$iCnt += 1;
				}
			}
		}
		if ($iCnt === 0) {
			$aSystemOutput[] = $sTotalTime . ' - no SQL statements were returned by OSLC';
		}
		if ($bSaveSystemOutput2Session) {
			SaveSystemOutputSessionArray($aSystemOutput);
		}
	}
}
function BuildSystemOutputDataDIV()
{
	if (!g_cbDebugging) {
		return '';
	}
	if (!IsSessionSettingTrue('show_system_output')) {
		return '';
	}
	$aSystemOutput = GetSystemOutputSessionArray();
	$sRet	= '';
	$iCnt = count($aSystemOutput);
	if ($iCnt > 0) {
		$sRet		   .= '<div class="webea-sysout-data" style="display:none">';
		for ($i = 0; $i < $iCnt; $i++) {
			$sStatement	= $aSystemOutput[$i];
			$sRet	   .= '<div class="webea-sysout-data-entry">';
			$sRet	   .= htmlspecialchars($sStatement);
			$sRet	   .= '</div>';
		}
		$sRet		   .= '</div>';
	}
	SaveSystemOutputSessionArray(array());
	return $sRet;
}
function RemoveTime($sDateTime)
{
	$sReturn = $sDateTime;
	$iPos = stripos($sDateTime, ' ', 0);
	if ($iPos !== FALSE) {
		$sReturn = substr($sDateTime, 0, $iPos);
	}
	return $sReturn;
}
function RemoveDate($sDateTime)
{
	$sReturn = $sDateTime;
	$iPos = stripos($sDateTime, ' ', 0);
	if ($iPos !== FALSE) {
		$sReturn = substr($sDateTime, $iPos + 1);
	}
	return $sReturn;
}
function microSecondsToMilli($t)
{
	$ms = round($t * 1000);
	$format = array_sum(explode(' ', $ms)) . 'ms';
	return $format;
}
function IsDateValid(&$sDate)
{
	$bRet = false;
	$s = trim($sDate);
	if (mb_strlen($s) >= 1 && mb_strlen($s) <= 2) {
		$s = date("m") . '-' . $s;
	}
	if (mb_strlen($s) >= 3 && mb_strlen($s) <= 5) {
		$s = date("Y") . '-' . $s;
	}
	$s = str_replace('/', '-', $s);
	$s = str_replace(' ', '-', $s);
	$s = str_replace('.', '-', $s);
	if (mb_strlen($s) == 8 && mb_substr($s, 2, 1) == '-' && mb_substr($s, 5, 1) == '-') {
		$s = mb_substr(date("Y"), 0, 2) . $s;
	}
	$aDateParts  = explode('-', $s);
	if (count($aDateParts) == 3) {
		if (checkdate($aDateParts[1], $aDateParts[2], $aDateParts[0])) {
			$sDate = date('Y-m-d', mktime(0, 0, 0, $aDateParts[1], $aDateParts[2], $aDateParts[0]));
			$bRet = true;
		}
	}
	return $bRet;
}
function setResponseCode($code, $reason = null)
{
	$code = intval($code);
	if ($reason !== '') {
		$reason = $reason . WriteHelpHyperlink();
	}
	if (version_compare(phpversion(), '5.4', '>') && is_null($reason)) {
		http_response_code($code);
	} else {
		header(trim("HTTP/1.0 $code $reason"));
		echo $reason;
	}
}
function HandleKnownErrors($sError)
{
	$sReturn = $sError;
	$sError = mb_strtolower($sError);
	if (mb_strpos($sError, 'error:140770fc', 0) !== false) {
		$sReturn = _glt('unknown protocol error');
	}
	if (mb_strpos($sError, 'connection refused', 0) !== false) {
		$sReturn = _glt('no response from the server');
	}
	if ($sError === 'recv failure: connection reset by peer') {
		$sReturn = _glt('invalid connection configuration');
	}
	if ($sError === 'request does not contain user id') {
		$sReturn = _glt('Security is enabled but no credentials were provided');
	}
	if (((mb_strpos($sError, 'operation timed out after', 0) !== false) ||
			(mb_strpos($sError, 'connection timed out after', 0) !== false)) &&
		mb_strpos($sError, 'milliseconds', 0) !== false
	) {
		$sReturn = _glt('Server could not be found');
	}
	return $sReturn;
}
function GetHTTPErrorMessage($httpCode)
{
	$sReturn = '';
	if ($httpCode == 401) {
		$sReturn = _glt('unauthorized credentials');
	} elseif ($httpCode == 403) {
		$sReturn = _glt('A secure connection is required');
	} elseif ($httpCode == 455) {
		$sReturn = _glt('selected database is shutdown');
	} elseif ($httpCode == 456) {
		$sReturn = _glt('selected database is not defined');
	} elseif ($httpCode == 457) {
		$sReturn = _glt('selected database does not support pro');
	} elseif ($httpCode == 458) {
		$sReturn = _glt('invalid cloud connection string');
	} elseif ($httpCode == 501) {
		$sReturn = _glt('server is not configured to support OSLC');
	}
	return $sReturn;
}
function GetOSLCErrorFromXML($sXML)
{
	$bNoError = true;
	$xmlDoc = null;
	if (!strIsEmpty($sXML) && $sXML !== false) {
		$xmlDoc = new DOMDocument();
		SafeXMLLoad($xmlDoc, $sXML);
	}
	if ($xmlDoc != null) {
		$xnRoot = $xmlDoc->documentElement;
		$xnDesc = GetXMLFirstChild($xnRoot);
		$bNoError = GetOSLCError($xnDesc);
	}
	return $bNoError;
}
function GetOSLCSuccess($xmlDoc)
{
	$sReturn = '';
	if ($xmlDoc != null) {
		$xnRoot 	= $xmlDoc->documentElement;
		$xn 		= $xnRoot->firstChild;
		$sReturn 	= GetXMLNodeAttribute($xn, 'rdf:about');
	}
	return $sReturn;
}
function GetOSLCError($xn)
{
	$GLOBALS['g_sLastOSLCErrorCode'] = '';
	$GLOBALS['g_sLastOSLCErrorMsg']  = '';
	$bNoError = true;
	if ($xn != null && $xn->childNodes != null) {
		foreach ($xn->childNodes as $xnC) {
			GetXMLNodeValue($xnC, 'oslc:statusCode', $GLOBALS['g_sLastOSLCErrorCode']);
			GetXMLNodeValue($xnC, 'oslc:message', $GLOBALS['g_sLastOSLCErrorMsg']);
		}
		if (!strIsEmpty($GLOBALS['g_sLastOSLCErrorCode']) || !strIsEmpty($GLOBALS['g_sLastOSLCErrorMsg'])) {
			$bNoError = false;
		}
	}
	return $bNoError;
}
function Check4OSLCErrorFromXML($sXML, &$sErrorCode, &$sErrorMsg)
{
	$bNoError = true;
	$xmlDoc = null;
	if (!strIsEmpty($sXML) && $sXML !== false) {
		$xmlDoc = new DOMDocument();
		SafeXMLLoad($xmlDoc, $sXML);
	}
	if ($xmlDoc != null) {
		$xnRoot = $xmlDoc->documentElement;
		$xnDesc = GetXMLFirstChild($xnRoot);
		$bNoError = ReadOSLCError($xnDesc, $sErrorCode, $sErrorMsg);
	}
	return $bNoError;
}
function ReadOSLCError($xn, &$sErrorCode, &$sErrorMsg)
{
	$sErrorCode = '';
	$sErrorMsg  = '';
	$bNoError = true;
	if ($xn != null && $xn->childNodes != null) {
		foreach ($xn->childNodes as $xnC) {
			GetXMLNodeValue($xnC, 'oslc:statusCode', $sErrorCode);
			GetXMLNodeValue($xnC, 'oslc:message', $sErrorMsg);
		}
	}
	return $bNoError;
}
function BuildOSLCErrorString()
{
	if ($GLOBALS['g_sLastOSLCErrorMsg'] === 'Request does not contain User ID') {
		$GLOBALS['g_sLastOSLCErrorMsg'] = _glt('Security credentials were not provided');
	}
	$sErrorMsg = '';
	if (!strIsEmpty($GLOBALS['g_sLastOSLCErrorMsg'])) {
		if (!strIsEmpty($GLOBALS['g_sLastOSLCErrorCode']))
			$sErrorMsg = $GLOBALS['g_sLastOSLCErrorCode'] . ' - ' . $GLOBALS['g_sLastOSLCErrorMsg'];
		else
			$sErrorMsg = $GLOBALS['g_sLastOSLCErrorMsg'];
	} elseif (!strIsEmpty($GLOBALS['g_sLastOSLCErrorCode'])) {
		$sErrorMsg = $GLOBALS['g_sLastOSLCErrorCode'] . ' - Unknown OSLC error';
	}
	return $sErrorMsg;
}
function GetOSLCCallName($sURL)
{
	$sRet = '';
	if (!strIsEmpty($sURL)) {
		$iPos = mb_strpos($sURL, '/oslc/am/');
		if ($iPos !== false) {
			$sRet = mb_substr($sURL, $iPos + 8);
		}
		$iPos = mb_strpos($sRet, '/?');
		if ($iPos !== false) {
			$sRet = mb_substr($sRet, 0, $iPos + 1);
		}
	}
	return $sRet;
}
function TrimInternalURL($sFullURL, $sFind)
{
	$sNew = '';
	if (!strIsEmpty($sFullURL)) {
		$iPos = strpos($sFullURL, $sFind);
		if ($iPos !== FALSE) {
			$sFullURL = substr($sFullURL, $iPos + strlen($sFind));
			$sNew = trim($sFullURL, "/");
		} else {
			$sNew = $sFullURL;
		}
		$sNew = urldecode($sNew);
	}
	return $sNew;
}
function CookieNameEncoding($sCookieName)
{
	$sReturn = $sCookieName;
	$sReturn = str_replace('\t', '0d09', $sReturn);
	$sReturn = str_replace('\r', '0d13', $sReturn);
	$sReturn = str_replace('\n', '0d10', $sReturn);
	$sReturn = str_replace(' ',  '0d32', $sReturn);
	$sReturn = str_replace('!',  '0d33', $sReturn);
	$sReturn = str_replace('#',  '0d35', $sReturn);
	$sReturn = str_replace('%',  '0d37', $sReturn);
	$sReturn = str_replace('\'', '0d39', $sReturn);
	$sReturn = str_replace('&',  '0d38', $sReturn);
	$sReturn = str_replace('*',  '0d42', $sReturn);
	$sReturn = str_replace('+',  '0d43', $sReturn);
	$sReturn = str_replace(',',  '0d44', $sReturn);
	$sReturn = str_replace(':',  '0d58', $sReturn);
	$sReturn = str_replace(';',  '0d59', $sReturn);
	$sReturn = str_replace('=',  '0d61', $sReturn);
	$sReturn = str_replace('?',  '0d63', $sReturn);
	$sReturn = str_replace('@',  '0d64', $sReturn);
	$sReturn = str_replace('.',  '0d89', $sReturn);
	$sReturn = str_replace('/',  '0d90', $sReturn);
	$sReturn = str_replace('[',  '0d91', $sReturn);
	$sReturn = str_replace("\\", '0d92', $sReturn);
	$sReturn = str_replace(']',  '0d93', $sReturn);
	$sReturn = str_replace('^',  '0d94', $sReturn);
	$sReturn = str_replace('{',  '0d123', $sReturn);
	$sReturn = str_replace('|',  '0d124', $sReturn);
	$sReturn = str_replace('}',  '0d125', $sReturn);
	$sReturn = str_replace('~',  '0d126', $sReturn);
	return $sReturn;
}
function AddURLParameter(&$sParaString, $sParaName, $sParaValue, $bIncludeBlank = false)
{
	if (!strIsEmpty($sParaValue) || $bIncludeBlank) {
		$iPos = strpos($sParaString, '?');
		if (strIsEmpty($sParaString) || $iPos === false) {
			$sParaString .= '?';
		} else {
			$sParaString .= '&';
		}
		$sParaString .= $sParaName . '=' . $sParaValue;
	}
}
function EncloseInCDATA($sString)
{
	$sReturn = $sString;
	if (!strIsEmpty($sString)) {
		$sReturn = '<![CDATA[' . $sString . ']]>';
	}
	return $sReturn;
}
function GetResTypeFromGUID($sGUID)
{
	$sResType 	= '';
	$sPrefix 	= '';
	if (!strIsEmpty($sGUID)) {
		$sPrefix = substr($sGUID, 0, 4);
		if ($sPrefix === 'mr_{') {
			$sResType = 'ModelRoot';
		} else if ($sPrefix === 'pk_{') {
			$sResType = 'Package';
		} else if ($sPrefix === 'dg_{' || $sPrefix === 'di_{') {
			$sResType = 'Diagram';
		} else if ($sPrefix === 'el_{') {
			$sResType = 'Element';
		} else if ($sPrefix === 'lt_{') {
			$sResType = 'Connector';
		} else if ($sGUID === 'search') {
			$sResType = 'Search';
		} else if ($sGUID === 'searchresults') {
			$sResType = 'Search Results';
		} else if ($sGUID === 'watchlist') {
			$sResType = 'Watchlist';
		} else if ($sGUID === 'watchlistconfig') {
			$sResType = 'Watchlist configuration';
		} else if ($sGUID === 'watchlistresults') {
			$sResType = 'Watchlist results';
		} elseif ($sGUID === 'addmodelroot') {
			$sResType = 'Add Root Node';
		} elseif ($sGUID === 'addviewpackage') {
			$sResType = 'Add View';
		} else if ($sGUID === 'addelement') {
			$sResType = 'Add Element';
		} else if ($sGUID === 'addelementtest') {
			$sResType = 'Add Element Test';
		} else if ($sGUID === 'editelementresalloc') {
			$sResType = 'Add Element Resource';
		} else if ($sGUID === 'addelementchgmgmt') {
			$sResType = 'Add Element Change Management';
		} else if ($sGUID === 'matrix') {
			$sResType = 'Matrix';
		} else if ($sGUID === 'matrixprofiles') {
			$sResType = 'Matrix Profiles';
		}
	}
	return $sResType;
}
function IsGUIDAddEditAction($sGUID)
{
	$bRet = false;
	if (
		$sGUID === 'addmodelroot' ||
		$sGUID === 'addviewpackage' ||
		$sGUID === 'addelement' ||
		$sGUID === 'addelementtest' ||
		$sGUID === 'addelementresalloc' ||
		$sGUID === 'addelementchgmgmt'
	) {
		$bRet = true;
	} elseif (
		$sGUID === 'editelementnote' ||
		$sGUID === 'editelementtest' ||
		$sGUID === 'editelementresalloc'
	) {
		$bRet = true;
	}
	return $bRet;
}
function GetMeaningfulObjectName($sType, $sName, &$sAlias)
{
	$sReturn = $sName;
	if (substr($sName, 0, 1) === '$') {
		if (strpos($sName, '://') !== false) {
			if (strIsEmpty($sAlias)) {
				$sReturn = 'Hyperlink';
			} else {
				$sReturn = $sAlias;
			}
			$sAlias = '';
		}
	}
	return $sReturn;
}
function GetPlainDisplayName($sName)
{
	$sReturn = $sName;
	if (strIsEmpty($sReturn)) {
		$sReturn = trim($sReturn);
		if (strIsEmpty($sReturn)) {
			$sReturn 	= _glt('<Unnamed object>');
		}
	}
	return $sReturn;
}
function GetMultiplicityString($sLower, $sUpper)
{
	$s = '';
	if ($sLower !== '1' || $sUpper !== '1') {
		$s = $sLower;
		if ($sUpper !== $sLower) {
			$s = $s . '..' . $sUpper;
		}
		$s = ' [' . $s . ']';
	}
	return $s;
}
function WriteValueInSentence($sValue, $sPrefix, $sSuffix, $bEchoResult = true)
{
	$sReturn = '';
	if (!strIsEmpty($sValue)) {
		$sReturn = $sPrefix . $sValue . $sSuffix;
		if ($bEchoResult) {
			echo $sReturn;
		}
	}
	return $sReturn;
}
function WriteArrayValueInSentence($aProps, $sItemName, $sPrefix, $sSuffix, $bEchoResult = true)
{
	$sReturn = '';
	$sValue = SafeGetArrayItem1Dim($aProps, $sItemName);
	if (!strIsEmpty($sValue)) {
		$sReturn = $sPrefix . $sValue . $sSuffix;
		if ($bEchoResult) {
			echo $sReturn;
		}
	}
	$sReturn = htmlspecialchars($sReturn);
	return $sReturn;
}
function GetObjectImageName($sType, $sResType, $sStereoType = '', $sNType = '0')
{
	$csPlainNames = ',abstraction,action,actionPin,activity,activityfinal,activityinitial,activityparameter,activitypartition,activityregion,actor,aggregation,assembly,association,associationClass,associationend,attribute,centralbuffernode,change,choice,class,collaboration,collaborationlink,comment,communicationpath,component,connector,constraint,controlflow,datastore,datatype,decision,defect,delegate,dependency,deployment,device,diagramframe,diagramgate,entrypoint,enumeration,event,exceptionhandler,exitpoint,expansionnode,expansionregion,extend,extension,feature,generalization,guielement,include,informationflow,informationitem,instantiation,interaction,interactionfragment,interactionoccurrence,interactionstate,interface,interruptflow,invokes,issue,manifest,mergenode,message,messageendpoint,modelroot,nesting,node,note,notelink,object,objectflow,objectnode,occurrence,operation,packageimport,packagelink,packagemerge,packagingcomponent,parameterset,part,port,precedes,primitivetype,profile,profileapplication,providedinterface,proxyconnector,realisation,recursion,redefinition,region,report,represents,requiredinterface,requirement,review,risk,rolebinding,screen,self-message,sequence,signal,state,stateflow,statemachine,stereotagvalue,stereotype,substitution,task,test,text,trace,trigger,umldiagram,usage,usecase,usecaselink';
	$sImageName = '';
	$sType = mb_strtolower($sType);
	$sStereoType = mb_strtolower($sStereoType);
	if ($sType === 'artifact') {
		if ($sStereoType === 'database connection') $sImageName = 'dbconnection';
		elseif ($sStereoType === 'sqlquery')		$sImageName = 'dbsqlquery';
		elseif ($sStereoType === 'encrypteddocument') $sImageName = 'encrypteddoc';
		elseif ($sStereoType === 'eareview') 		$sImageName = 'review';
		elseif ($sStereoType === 'image')	 		$sImageName = 'imageasset';
		elseif ($sNType === '1')	 				$sImageName = 'sbpialm';
		elseif ($sNType === '2')	 				$sImageName = 'sbpiautodesk';
		elseif ($sNType === '3')	 				$sImageName = 'sbpibugzilla';
		elseif ($sNType === '4')	 				$sImageName = 'sbpidropbox';
		elseif ($sNType === '5')	 				$sImageName = 'sbpiea';
		elseif ($sNType === '6')	 				$sImageName = 'sbpijazz';
		elseif ($sNType === '7')	 				$sImageName = 'sbpijira';
		elseif ($sNType === '8')	 				$sImageName = 'sbpiservicenow';
		elseif ($sNType === '9')	 				$sImageName = 'sbpitfs';
		elseif ($sNType === '10')	 				$sImageName = 'sbpiwrike';
		elseif ($sNType === '11')	 				$sImageName = 'sbpisharepoint';
		elseif ($sNType === '12')	 				$sImageName = 'sbpiconfluence';
		else										$sImageName = 'document';
	} elseif ($sResType === 'Diagram') {
		if ($sType === 'package')					$sImageName = 'diagrampackage';
		elseif ($sType === 'object')				$sImageName = 'diagramobject';
		elseif ($sType === 'component')				$sImageName = 'diagramcomponent';
		elseif ($sType === 'deployment')			$sImageName = 'diagramdeployment';
		elseif ($sType === 'usecase')				$sImageName = 'diagramusecase';
		elseif ($sType === 'use case')				$sImageName = 'diagramusecase';
		elseif ($sType === 'activity')				$sImageName = 'diagramactivity';
		elseif ($sType === 'compositestructure')	$sImageName = 'diagramcomposite';
		elseif ($sType === 'state')					$sImageName = 'diagramstate';
		elseif ($sType === 'statechart')			$sImageName = 'diagramstate';
		elseif ($sType === 'communication')			$sImageName = 'diagramcommunication';
		elseif ($sType === 'collaboration')			$sImageName = 'diagramcommunication';
		elseif ($sType === 'sequence')				$sImageName = 'diagramsequence';
		elseif ($sType === 'timing')				$sImageName = 'diagramtiming';
		elseif ($sType === 'interaction')			$sImageName = 'diagraminteraction';
		elseif ($sType === 'interactionoverview')	$sImageName = 'diagraminteraction';
		elseif ($sType === 'custom')				$sImageName = 'diagramcustom';
		elseif ($sType === 'analysis')				$sImageName = 'diagramanalysis';
		else										$sImageName = 'diagram';
	} elseif ($sType === 'class') {
		if ($sStereoType === 'table')				$sImageName = 'dbtable';
		elseif ($sStereoType === 'function')		$sImageName = 'dbfunction';
		elseif ($sStereoType === 'dbsequence')		$sImageName = 'dbsequence';
		elseif ($sStereoType === 'procedure')		$sImageName = 'dbprocedure';
		elseif ($sStereoType === 'view')			$sImageName = 'dbview';
		elseif ($sStereoType === 'materialized view') $sImageName = 'dbmaterialview';
		else										$sImageName = 'class';
	} elseif ($sType === 'text') {
		if ($sStereoType === 'navigationcell')		$sImageName = 'navcell';
		else										$sImageName = 'text';
	} elseif ($sResType === 'connector') {
		if ($sType === 'erlink')					$sImageName = '?';
		elseif ($sType === 'package')				$sImageName = 'packagelink';
		elseif ($sType === 'protocolconformance') 	$sImageName = '?';
		elseif ($sType === 'protocoltransition')	$sImageName = '?';
		else 										$sImageName = $sType;
	} elseif ($sType === 'modelroot') {
		$sImageName = 'modelroot';
	} elseif ($sResType === 'Package' || $sType === 'package') {
		if ($sNType === '1')						$sImageName = 'viewusecase';
		elseif ($sNType === '2')					$sImageName = 'viewdynamic';
		elseif ($sNType === '3')					$sImageName = 'viewclass';
		elseif ($sNType === '4')					$sImageName = 'viewcomponent';
		elseif ($sNType === '5')					$sImageName = 'viewdeployment';
		elseif ($sNType === '6')					$sImageName = 'viewsimple';
		elseif ($sNType === '20')					$sImageName = 'packagecomponent';
		else										$sImageName = 'package';
	} elseif ($sType === 'statenode') {
		if ($sNType === '3')						$sImageName = 'statestart';
		elseif ($sNType === '4')					$sImageName = 'statestop';
		elseif ($sNType === '5')					$sImageName = 'entrypoint';
		elseif ($sNType === '6')					$sImageName = 'exitpoint';
		elseif ($sNType === '10')					$sImageName = 'statestop';
		elseif ($sNType === '11')					$sImageName = 'decision';
		elseif ($sNType === '12')					$sImageName = 'exitpoint';
		elseif ($sNType === '13')					$sImageName = 'statestop';
		elseif ($sNType === '14')					$sImageName = 'exitpoint';
		elseif ($sNType === '101')					$sImageName = 'statestop';
		elseif ($sNType === '102')					$sImageName = 'exitpoint';
		else 										$sImageName = 'statestart';
	} elseif ($sType === 'executionenvironment')		$sImageName = 'executionenv';
	elseif ($sType === 'entity')					$sImageName = 'class';
	elseif ($sType === 'hyperlink')					$sImageName = 'change';
	elseif ($sType === 'interruptibleactivityregion') $sImageName = 'interruptactregion';
	elseif ($sType === 'deploymentspecification') 	$sImageName = 'deploymentspec';
	elseif ($sType === 'initial state')				$sImageName = 'statestart';
	elseif ($sType === 'CollaborationOccurrence') 	$sImageName = 'collaboration';
	elseif ($sType === 'boundary')					$sImageName = 'umldiagram';
	elseif ($sType === 'interactionstate')			$sImageName = 'state';
	elseif ($sType === 'synchronization')			$sImageName = 'forkjoin';
	elseif ($sType === 'synch(h)')					$sImageName = 'forkjoin';
	elseif ($sType === 'synch(v)')					$sImageName = 'forkjoin';
	elseif ($sType === 'sub-activity')				$sImageName = 'forkjoin';
	elseif ((stripos($csPlainNames, "," . $sType . ",") !== false)) 	$sImageName = $sType;
	else {
		if ($sResType === 'Package')				$sImageName = 'package';
		elseif ($sResType === 'Diagram')			$sImageName = 'diagram';
		else 										$sImageName = 'class';
	}
	return $sImageName;
}
function GetObjectImagePath($sType, $sResType, $sStereoType = '', $sNType = '0', $sSize = '64')
{
	$sImagePath = '';
	if (IsHyperlink($sType, '', $sNType)) {
		$sImagePath = 'images/element' . $sSize . '/hyperlink.png';
	} else {
		$sImagePath = 'images/element' . $sSize . '/' . GetObjectImageName($sType, $sResType, $sStereoType, $sNType) . '.png';
	}
	return $sImagePath;
}
function GetObjectImageSpriteName($sObjectImageName)
{
	$sSpriteName = $sObjectImageName;
	$iPos = strrpos($sSpriteName, 'images/');
	if ($iPos !== false) {
		$sSpriteName = substr($sSpriteName, $iPos + 7);
	}
	$sSpriteName = str_replace('.png', '', $sSpriteName);
	$sSpriteName = str_replace('/', '-', $sSpriteName);
	if ($sSpriteName === 'home')
		$sSpriteName 	= 'mainsprite-root';
	elseif ($sSpriteName === 'element16-search')
		$sSpriteName 	= 'mainsprite-search16color';
	elseif ($sSpriteName === 'element16-searchresults')
		$sSpriteName 	= 'mainsprite-searchresults';
	elseif ($sSpriteName === 'element16-watchlist')
		$sSpriteName 	= 'mainsprite-watchlist16color';
	elseif ($sSpriteName === 'element16-watchlistconfig')
		$sSpriteName 	= 'mainsprite-watchlistconfig';
	elseif ($sSpriteName === 'element16-watchlistresults')
		$sSpriteName 	= 'mainsprite-watchlistresults';
	elseif ($sSpriteName === 'element16-matrix')
		$sSpriteName 	= 'mainsprite-matrixcolor';
	elseif ($sSpriteName === 'element16-testadd')
		$sSpriteName 	= 'propsprite-testadd';
	elseif ($sSpriteName === 'element16-resallocadd')
		$sSpriteName	= 'propsprite-resallocadd';
	elseif ($sSpriteName === 'element16-changeadd')
		$sSpriteName	= 'propsprite-changeadd';
	elseif ($sSpriteName === 'element16-defectadd')
		$sSpriteName	= 'propsprite-defectadd';
	elseif ($sSpriteName === 'element16-issueadd')
		$sSpriteName	= 'propsprite-issueadd';
	elseif ($sSpriteName === 'element16-taskadd')
		$sSpriteName	= 'propsprite-taskadd';
	elseif ($sSpriteName === 'element16-riskadd')
		$sSpriteName	= 'propsprite-riskadd';
	elseif ($sSpriteName === 'element16-noteedit')
		$sSpriteName	= 'propsprite-noteedit';
	elseif ($sSpriteName === 'element16-testedit')
		$sSpriteName	= 'propsprite-testedit';
	elseif ($sSpriteName === 'element16-resallocedit')
		$sSpriteName	= 'propsprite-resallocedit';
	return $sSpriteName;
}
function ParseINIFile2Array($sFilename, $bEncrypt = true)
{
	global $gLog;
	$aSettings = array();
	LoadDotEnv(dirname(__FILE__) . '/.env');
	if (file_exists($sFilename)) {
		$p_ini = parse_ini_file($sFilename, true);
		foreach ($p_ini as $sNamespace => $properties) {
			$sName = $sNamespace;
			$sExtends = '';
			$iPos = stripos($sNamespace, ':');
			if ($iPos !== false) {
				list($sName, $sExtends) = explode(':', $sNamespace);
				$sName = trim($sName);
				$sExtends = trim($sExtends);
			}
			if (!isset($aSettings[$sName])) {
				$aSettings[$sName] = array();
			}
			if (isset($p_ini[$sExtends])) {
				foreach ($p_ini[$sExtends] as $sProp => $sVal) {
					if ($sProp === 'password' && $bEncrypt) {
						$sVal = md5($sVal);
					}
					$aSettings[$sName][$sProp] = $sVal;
				}
			}
			$bFoundAuthCode = false;
			foreach ($properties as $sProp => $sVal) {
				if ($sProp === 'auth_code' && $bEncrypt) {
					$sVal = md5($sVal);
					$bFoundAuthCode = true;
				} elseif ($sProp === 'auth_code' && !$bEncrypt) {
					$bFoundAuthCode = true;
				}
				$aSettings[$sName][$sProp] = $sVal;
			}
			if (!$bFoundAuthCode) {
				if ($sName !== 'model_list') {
					if ($bEncrypt) {
						$aSettings[$sName]['auth_code'] = md5('');
					} else {
						$aSettings[$sName]['auth_code'] = '';
					}
				}
			}
		}
	} else {
		$gLog->Write2Log('The configuration file "' . $sFilename . '" was not found.');
	}
	$ldapEnvMap = array(
		'WEBEA_LDAP_HOST' => 'host',
		'WEBEA_LDAP_DN' => 'dn',
		'WEBEA_LDAP_CRITERIA' => 'ldap_criteria',
		'WEBEA_LDAP_USERNAME' => 'username',
		'WEBEA_LDAP_PASSWORD' => 'password',
		'WEBEA_PRO_CLOUD_DB_ALIAS' => 'pro_cloud_name_db_alias',
		'WEBEA_PRO_CLOUD_PORT' => 'pro_cloud_port_db_alias'
	);
	foreach ($ldapEnvMap as $envKey => $settingKey) {
		$envVal = getenv($envKey);
		if ($envVal !== false && $envVal !== '') {
			if (!isset($aSettings['ldap_settings'])) {
				$aSettings['ldap_settings'] = array();
			}
			$aSettings['ldap_settings'][$settingKey] = $envVal;
		}
	}
	return $aSettings;
}
function LimitDisplayString($sStr, $iMaxLen = 30)
{
	$sNew = $sStr;
	if ($iMaxLen <= 0) {
		$iMaxLen = 30;
	}
	if (strlen($sStr) > $iMaxLen + 3) {
		$sNew = substr($sStr, 0, $iMaxLen) . '...';
	} elseif (strIsEmpty($sNew)) {
		$sNew = _glt('<Unnamed object>');
	}
	return $sNew;
}
function IsHyperlink($sObjectType, $sObjectName, $sObjectNType)
{
	$bIsHyperLink = false;
	if ($sObjectNType === '19') {
		$bIsHyperLink = true;
	}
	return $bIsHyperLink;
}
function IsNavigateObject($sObjectType, $sObjectName, $sObjectNType, $sObjectStereo)
{
	$bIsNavigate = false;
	if ($sObjectNType === '19') {
		$bIsNavigate = true;
	} elseif ($sObjectType === 'Text' && sObjectStereo === 'NavigationCell') {
		$bIsNavigate = true;
	}
	return $bIsNavigate;
}
function ShouldIgnoreName($sObjectType, $sObjectName, $sObjectNType)
{
	$bIsHyperLink = IsHyperlink($sObjectType, $sObjectName, $sObjectNType);
	if ($bIsHyperLink) {
		return true;
	} else {
		$csTypes2IgnoreNames = ',note,text,';
		$sObjectType = mb_strtolower($sObjectType);
		return (stripos($csTypes2IgnoreNames, ',' . $sObjectType . ',') === false);
	}
}
function AdjustImagePath($sImagePath, $sNewSize)
{
	$sReturn = $sImagePath;
	if ($sNewSize === '16') {
		$sReturn = str_replace('/element48/', '/element16/', $sReturn);
		$sReturn = str_replace('/element64/', '/element16/', $sReturn);
	} elseif ($sNewSize === '48') {
		$sReturn = str_replace('/element16/', '/element48/', $sReturn);
		$sReturn = str_replace('/element64/', '/element48/', $sReturn);
	} elseif ($sNewSize === '64') {
		$sReturn = str_replace('/element16/', '/element64/', $sReturn);
		$sReturn = str_replace('/element48/', '/element64/', $sReturn);
	}
	return $sReturn;
}
function ConvertEANoteToHTML($sNotes)
{
	$sReturn = $sNotes;
	$sReturn = preg_replace("/\r\n?|\n\r?/", "<br />", $sNotes);
	$sReturn = htmlspecialchars($sReturn);
	return $sReturn;
}
function ConvertBoolToText($bool)
{
	$sReturn = $bool;
	if ($sReturn  === '0') {
		$sReturn  = 'False';
	} else if ($sReturn  === '1') {
		$sReturn  = 'True';
	}
	return $sReturn;
}
function ConvertHTMLToEANote($sNotes)
{
	$sReturn = $sNotes;
	if (substr_count($sReturn, '<div>') > 0) {
		$sReturn = str_replace('</div>', "", $sReturn);
		$aNotes = explode('<div>', $sReturn);
		foreach ($aNotes as &$line) {
			if (substr_count($line, '<br>') == 0) {
				$line = $line . "\r\n";
			}
		}
		$sReturn = implode($aNotes);
	}
	if (substr_count($sReturn, '<p>') > 0) {
		$sReturn = str_replace('<em>', "<i>", $sReturn);
		$sReturn = str_replace('</em>', "</i>", $sReturn);
		$sReturn = str_replace('<strong>', "<b>", $sReturn);
		$sReturn = str_replace('</strong>', "</b>", $sReturn);
		$sReturn = str_replace('</p>', "", $sReturn);
		$aNotes = explode('<p>', $sReturn);
		foreach ($aNotes as &$line) {
			if (substr_count($line, '<br>') == 0) {
				$line = $line . "\r\n";
			}
		}
		$sReturn = implode($aNotes);
	}
	if ($sReturn === '<br>' || $sReturn === '<br />' || $sReturn === '<br/>')
		$sReturn = '';
	$sReturn = str_replace(array('<br>', '<br/>', '<br />'), "\r\n", $sReturn);
	return $sReturn;
}
function ConvertStringToParameter($sName)
{
	$sName = addslashes($sName);
	$sName  = htmlspecialchars($sName);
	$sName = preg_replace('~[\r\n]+~', '\\\r\n', $sName);
	return $sName;
}
function ConvertHyperlinksInEAComments($sNotes)
{
	$sPlainNotes = $sNotes;
	$iPos = mb_strpos($sPlainNotes, '<a href="', 0);
	while ($iPos !== false) {
		$iPos2 = mb_strpos($sPlainNotes, '</a>', $iPos + 8);
		$sHref = mb_substr($sPlainNotes, $iPos + 8, $iPos2 - ($iPos + 8));
		$sHref = trim($sHref, '\'"');
		$sNewHref = $sHref;
		$iPosAdjust = 0;
		$bHRefHandled = false;
		if (mb_substr($sHref, 0, 11) === '$package://') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sNewHref = 'javascript:load_diagram_object(\'pk_' . mb_substr($sHref, 11, $iPos3 - 11) . '\', \'false\')"' . mb_substr($sHref, $iPos3 + 1);
				$sPlainNotes = str_replace($sHref, $sNewHref, $sPlainNotes);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 11) === '$diagram://') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sNewHref = 'javascript:load_diagram_object(\'dg_' . mb_substr($sHref, 11, $iPos3 - 11) . '\', \'false\')"' . mb_substr($sHref, $iPos3 + 1);
				$sPlainNotes = str_replace($sHref, $sNewHref, $sPlainNotes);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 14) === '$diagramimg://') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sNewHref = 'javascript:load_diagram_object(\'dg_' . mb_substr($sHref, 14, $iPos3 - 14) . '\', \'false\')"' . mb_substr($sHref, $iPos3 + 1);
				$sPlainNotes = str_replace($sHref, $sNewHref, $sPlainNotes);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 11) === '$element://') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sNewHref = 'javascript:load_diagram_object(\'el_' . mb_substr($sHref, 11, $iPos3 - 11) . '\', \'false\')"' . mb_substr($sHref, $iPos3 + 1);
				$sPlainNotes = str_replace($sHref, $sNewHref, $sPlainNotes);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 14) === '$elementimg://') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sNewHref = 'javascript:load_diagram_object(\'el_' . mb_substr($sHref, 14, $iPos3 - 14) . '\', \'false\')"' . mb_substr($sHref, $iPos3 + 1);
				$sPlainNotes = str_replace($sHref, $sNewHref, $sPlainNotes);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 15) === '$imageman://id=') {
			$iPos3 = mb_strpos($sHref, ';', 0);
			if ($iPos3 !== false) {
				$sNewHref = 'data_api/dl_model_image.php?objectguid=im_{' . mb_substr($sHref, 15, $iPos3 - 15) . '}&' . mb_substr($sHref, $iPos3 + 1);
				$sNewHref = str_replace(';', '&', $sNewHref);
				$sPlainNotes = str_replace($sHref, $sNewHref, $sPlainNotes);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 8) === '$inet://') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sNewHref = mb_substr($sHref, 8, $iPos3 - 7) . mb_substr($sHref, $iPos3 + 1);
				$sPlainNotes = str_replace($sHref, $sNewHref, $sPlainNotes);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 8) === '$help://') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sNewHref = g_csHelpLocation . mb_substr($sHref, 8, $iPos3 - 7) . mb_substr($sHref, $iPos3 + 1);
				$sPlainNotes = str_replace($sHref, $sNewHref, $sPlainNotes);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 6) === '$path=') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sNewHref = 'file:///' . mb_substr($sHref, 6, $iPos3 - 6) . mb_substr($sHref, $iPos3);
				$sPlainNotes = str_replace($sHref, $sNewHref, $sPlainNotes);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 10) === '$matrix://') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sProfile = mb_substr($sHref, 10, $iPos3 - 10);
				$sNewHref = 'javascript:load_object(\'matrix\', \'\', \'\', \'' . $sProfile . '\', \'Matrix - ' . $sProfile . '\', \'images/element16/matrix.png\')' . mb_substr($sHref, $iPos3);
				$sPlainNotes = str_replace($sHref, $sNewHref, $sPlainNotes);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		}
		$iPos2 = $iPos2 + $iPosAdjust;
		$iPos = mb_strpos($sPlainNotes, '<a href=', $iPos2);
	}
	return $sPlainNotes;
}
function ConvertHyperlinksInLinkedDoc($sHTML)
{
	$sAdjustedHTML = $sHTML;
	$iPos = mb_strpos($sAdjustedHTML, '<a href="', 0);
	while ($iPos !== false) {
		$iPos2 = mb_strpos($sAdjustedHTML, '</a>', $iPos + 8);
		$sHref = mb_substr($sAdjustedHTML, $iPos + 8, $iPos2 - ($iPos + 8));
		$sHref = trim($sHref, '\'"');
		$sNewHref = $sHref;
		$iPosAdjust = 0;
		$bHRefHandled = false;
		if (mb_substr($sHref, 0, 11) === '$package://') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sNewHref = 'javascript:load_diagram_object(\'pk_' . mb_substr($sHref, 11, $iPos3 - 11) . '\', \'false\')"' . mb_substr($sHref, $iPos3 + 1);
				$sAdjustedHTML = str_replace($sHref, $sNewHref, $sAdjustedHTML);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 11) === '$diagram://') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sNewHref = 'javascript:load_diagram_object(\'dg_' . mb_substr($sHref, 11, $iPos3 - 11) . '\', \'false\')"' . mb_substr($sHref, $iPos3 + 1);
				$sAdjustedHTML = str_replace($sHref, $sNewHref, $sAdjustedHTML);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 14) === '$diagramimg://') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sNewHref = 'javascript:load_diagram_object(\'dg_' . mb_substr($sHref, 14, $iPos3 - 14) . '\', \'false\')"' . mb_substr($sHref, $iPos3 + 1);
				$sAdjustedHTML = str_replace($sHref, $sNewHref, $sAdjustedHTML);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 11) === '$element://') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sNewHref = 'javascript:load_diagram_object(\'el_' . mb_substr($sHref, 11, $iPos3 - 11) . '\', \'false\')"' . mb_substr($sHref, $iPos3 + 1);
				$sAdjustedHTML = str_replace($sHref, $sNewHref, $sAdjustedHTML);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 14) === '$elementimg://') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sNewHref = 'javascript:load_diagram_object(\'el_' . mb_substr($sHref, 14, $iPos3 - 14) . '\', \'false\')"' . mb_substr($sHref, $iPos3 + 1);
				$sAdjustedHTML = str_replace($sHref, $sNewHref, $sAdjustedHTML);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 15) === '$imageman://id=') {
			$iPos3 = mb_strpos($sHref, ';', 0);
			if ($iPos3 !== false) {
				$sNewHref = 'data_api/dl_model_image.php?objectguid=im_{' . mb_substr($sHref, 15, $iPos3 - 15) . '}&' . mb_substr($sHref, $iPos3 + 1);
				$sNewHref = str_replace(';', '&', $sNewHref);
				$sAdjustedHTML = str_replace($sHref, $sNewHref, $sAdjustedHTML);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 10) === '$matrix://') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sProfile = mb_substr($sHref, 10, $iPos3 - 10);
				$sNewHref = 'javascript:load_object(\'matrix\', \'\', \'\', \'' . $sProfile . '\', \'Matrix - ' . $sProfile . '\', \'images/element16/matrix.png\')' . mb_substr($sHref, $iPos3);
				$sAdjustedHTML = str_replace($sHref, $sNewHref, $sAdjustedHTML);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (
			mb_substr($sHref, 0, 6) === 'ftp://'   ||
			mb_substr($sHref, 0, 7) === 'http://'  ||
			mb_substr($sHref, 0, 8) === 'https://' ||
			mb_substr($sHref, 0, 6) === 'tcp://'
		) {
			$bHRefHandled = true;
		} elseif (mb_substr($sHref, 0, 8) === '$help://') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sNewHref = g_csHelpLocation . mb_substr($sHref, 8, $iPos3 - 7) . ' target="_blank" rel="noopener noreferrer"' . mb_substr($sHref, $iPos3 + 1);
				$sAdjustedHTML = str_replace($sHref, $sNewHref, $sAdjustedHTML);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		} elseif (mb_substr($sHref, 0, 6) === '$path=') {
			$iPos3 = mb_strpos($sHref, '"', 0);
			if ($iPos3 !== false) {
				$sNewHref = 'file:///' . mb_substr($sHref, 6, $iPos3 - 6) . mb_substr($sHref, $iPos3);
				$sAdjustedHTML = str_replace($sHref, $sNewHref, $sAdjustedHTML);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
				$bHRefHandled = true;
			}
		}
		if ($bHRefHandled === false && $iPos > 0) {
			$sHref = mb_substr($sAdjustedHTML, $iPos, ($iPos2 + 4) - $iPos);
			$iPos3 = mb_stripos($sHref, '"', 10);
			if ($iPos3 !== false) {
				$sNewHref = mb_substr($sHref, $iPos3 + 2);
				if (mb_substr($sNewHref, -4) === '</a>') {
					$sNewHref = mb_substr($sNewHref, 0, mb_strlen($sNewHref) - 4);
				}
				$sAdjustedHTML = str_replace($sHref, $sNewHref, $sAdjustedHTML);
				$iPosAdjust = mb_strlen($sNewHref) - mb_strlen($sHref);
			}
		}
		$iPos2 = $iPos2 + $iPosAdjust;
		$iPos = mb_stripos($sAdjustedHTML, '<a href=', $iPos2);
	}
	return $sAdjustedHTML;
}
function WritePropertySectionContentHeader($sSectionName, $sText, $sDefault, $bUseAlternativeColour = false)
{
	if (isset($_SESSION['prop_expanded_' . $sSectionName]) === false) {
		$_SESSION['prop_expanded_' . $sSectionName] = $sDefault;
		$sExpanded 		= $sDefault;
	} else {
		$sExpanded 		= $_SESSION['prop_expanded_' . $sSectionName];
	}
	$sSectionHdrName 	= 'section-heading-closed';
	$sSectionHdrName2 	= '';
	if ($bUseAlternativeColour)
		$sSectionHdrName2 = ' section-heading-miniprops-closed';
	$sTooltip			= 'Expand/Collapse section';
	$sShow				= '';
	if ($sExpanded === '1') {
		$sSectionHdrName = 'section-heading-open';
		if ($bUseAlternativeColour)
			$sSectionHdrName2 = ' section-heading-miniprops-open';
		$sShow			= ' w3-show';
	}
	echo '<h4 class="' . $sSectionHdrName . $sSectionHdrName2 . '" onclick="OnPropAccordion(\'' . $sSectionName . '\')" title="' . $sTooltip . '">' . $sText;
	echo '<img alt="" src="images/spriteplaceholder.png" class="' . $sSectionHdrName . '-icon">';
	echo '</h4>';
	echo '<div id="' . $sSectionName . '" class="w3-accordion-content ' . $sShow . '">';
}
function ValidateGUID($sInputGUID)
{
	$sReturn = '';
	$sPrefix = mb_substr($sInputGUID, 0, 4);
	if (
		$sPrefix === 'pk_{' || $sPrefix === 'mr_{' ||
		$sPrefix === 'dg_{' || $sPrefix === 'di_{' ||
		$sPrefix === 'el_{'
	) {
		if (mb_strlen($sInputGUID) == 41) {
			if (mb_substr($sInputGUID, 40, 1) === '}') {
				$sRawGUID = mb_substr($sInputGUID, 4, 36);
				if (preg_match('/^[A-Fa-f0-9]{8}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{12}$/', $sRawGUID)) {
					$sReturn = $sInputGUID;
				}
			}
		}
	} else if ($sInputGUID === 'search') {
		$sReturn = 'search';
	} else if ($sInputGUID === 'searchresults') {
		$sReturn = 'searchresults';
	} else if ($sInputGUID === 'addmodelroot') {
		$sResType = 'addmodelroot';
	} else if ($sInputGUID === 'addviewpackage') {
		$sResType = 'addviewpackage';
	} else if ($sInputGUID === 'addelement') {
		$sReturn = 'addelement';
	}
	return $sReturn;
}
function getDiscussions($xnDiscussions)
{
	$aDiscussions = array();
	if ($xnDiscussions->nodeName !== 'ss:discussions') {
		return $aDiscussions;
	}
	$aDiscussions = getDiscussions2($xnDiscussions);
	return $aDiscussions;
}
function getDiscussions2($xnDiscussions)
{
	$aAvatars = array();
	$aDiscussions = array();
	$xnDesc = $xnDiscussions->firstChild;
	if ($xnDesc !== null) {
		foreach ($xnDesc->childNodes as $sMembers) {
			$aReplies 		= array();
			$sDiscussion	= '';
			$sGUID			= '';
			$sAuthor		= '';
			$sCreated		= '';
			$sReviewName	= '';
			$sReviewGUID	= '';
			$sReviewType	= '';
			$sReviewCreator = '';
			$sReviewImageURL = '';
			$sReplies		= '';
			$sAvatarID		= '';
			$sAvatarImage	= '';
			$sPriority 		= '';
			$sStatus 		= '';
			$xnDiscuss = $sMembers->firstChild;
			if ($xnDiscuss !== null && $xnDiscuss->childNodes !== null) {
				foreach ($xnDiscuss->childNodes as $xn) {
					GetXMLNodeValue($xn, 'dcterms:description', $sDiscussion);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'dcterms:created', 	$sCreated);
					GetXMLNodeValueAttr($xn, 'ss:avatar', 'rdf:resource', $sAvatarID);
					GetXMLNodeValue($xn, 'ss:priority', $sPriority);
					GetXMLNodeValue($xn, 'ss:status', $sStatus);
					if ($xn->nodeName === 'dcterms:creator') {
						$xnPerson = $xn->firstChild;
						$xnFOAF   = $xnPerson->firstChild;
						$sAuthor  = $xnFOAF->nodeValue;
					} elseif ($xn->nodeName === 'ss:reviewresource') {
						$xnNodeC = $xn->firstChild;
						if ($xnNodeC !== null && $xnNodeC->childNodes !== null) {
							foreach ($xnNodeC->childNodes as $xnReviewMembers) {
								$xnReviewNode = $xnReviewMembers->firstChild;
								foreach ($xnReviewNode->childNodes as $xnReviewInfo) {
									GetXMLNodeValue($xnReviewInfo, 'dcterms:title', $sReviewName);
									GetXMLNodeValue($xnReviewInfo, 'dcterms:identifier', $sReviewGUID);
									GetXMLNodeValue($xnReviewInfo, 'dcterms:type', $sReviewType);
									if ($xnReviewInfo->nodeName === 'dcterms:creator') {
										$xnNode 	 	= $xnReviewInfo->firstChild;
										$xnNode			= $xnNode->firstChild;
										$sReviewCreator	= $xnNode->nodeValue;
									}
								}
							}
						}
					} elseif ($xn->nodeName === 'ss:replies') {
						$xnNodeC = $xn->firstChild;
						foreach ($xnNodeC->childNodes as $xnReplyMembers) {
							$sReply			= '';
							$sReplyAuthor	= '';
							$sReplyAvatarID = '';
							$sReplyAvatarImage	= '';
							$sReplyCreated	= '';
							$sReplyGUID		= '';
							$xnReplyNode = $xnReplyMembers->firstChild;
							foreach ($xnReplyNode->childNodes as $xnReplyInfo) {
								GetXMLNodeValue($xnReplyInfo, 'dcterms:description', $sReply);
								GetXMLNodeValue($xnReplyInfo, 'dcterms:created', $sReplyCreated);
								GetXMLNodeValue($xnReplyInfo, 'ss:discussionidentifier', $sReplyGUID);
								GetXMLNodeValueAttr($xnReplyInfo, 'ss:avatar', 'rdf:resource', $sReplyAvatarID);
								if ($xnReplyInfo->nodeName === 'dcterms:creator') {
									$xnNode 	 	= $xnReplyInfo->firstChild;
									$xnNode			= $xnNode->firstChild;
									$sReplyAuthor	= $xnNode->nodeValue;
								}
							}
							if (!strIsEmpty($sReply) || !strIsEmpty($sReplyAuthor) || !strIsEmpty($sReplyCreated)) {
								$aRow				= array();
								$aRow['replytext']	= $sReply;
								$aRow['replyguid']	= $sReplyGUID;
								$aRow['replyauthor'] = $sReplyAuthor;
								$aRow['replyavatarid'] = $sReplyAvatarID;
								$aRow['replyavatarimage'] = $sAvatarImage;
								$aRow['replycreated'] = $sReplyCreated;
								$aReplies[]			= $aRow;
							}
						}
					}
				}
			}
			if (!strIsEmpty($sDiscussion) && !strIsEmpty($sAuthor) && !strIsEmpty($sCreated)) {
				$sPriorityImageClass = '';
				$sStatusImageClass = '';
				$sPriorityImageClass = GetPriorityImageClass($sPriority);
				$sStatusImageClass = GetStatusImageClass($sStatus);
				$aRow				= array();
				$aRow['discussion']	= $sDiscussion;
				$aRow['guid']		= $sGUID;
				$aRow['author']		= $sAuthor;
				$aRow['avatarid']	= $sAvatarID;
				$aRow['avatarimage'] = $sAvatarImage;
				$aRow['created']	= $sCreated;
				$aRow['reviewname']	= $sReviewName;
				$aRow['reviewguid']	= $sReviewGUID;
				$aRow['reviewtype']	= $sReviewType;
				$aRow['reviewcreator']	= $sReviewCreator;
				$aRow['reviewimageurl']	= 'images/element16/review.png';
				$aRow['priority']			= $sPriority;
				$aRow['priorityimageclass']	= $sPriorityImageClass;
				$aRow['status']				= $sStatus;
				$aRow['statusimageclass']	= $sStatusImageClass;
				if (count($aReplies) > 0) {
					$sReplies		= "replies";
					$aRow[$sReplies] = $aReplies;
				}
				$aDiscussions[] 	= $aRow;
			}
		}
	}
	return $aDiscussions;
}
function GetPriorityImageClass($sPriority)
{
	$sPriorityImageClass = '';
	if ($sPriority === 'High')
		$sPriorityImageClass = 'propsprite-discusspriorityhigh';
	elseif ($sPriority === 'Medium')
		$sPriorityImageClass = 'propsprite-discussprioritymed';
	elseif ($sPriority === 'Low')
		$sPriorityImageClass = 'propsprite-discussprioritylow';
	elseif ($sPriority === 'None')
		$sPriorityImageClass = 'propsprite-discussprioritynone';
	return $sPriorityImageClass;
}
function GetStatusImageClass($sStatus)
{
	$sStatusImageClass = '';
	if ($sStatus === 'Awaiting Review')
		$sStatusImageClass = 'propsprite-discussstatusawait';
	elseif ($sStatus === 'Closed')
		$sStatusImageClass = 'propsprite-discussstatuscomplete';
	elseif ($sStatus === 'Open')
		$sStatusImageClass = 'propsprite-discussstatusopen';
	return $sStatusImageClass;
}
function getAvatars($xnObj)
{
	$aAvatars = array();
	while ($xnObj) {
		if ($xnObj->nodeName === "rdf:Description") {
			$sNodeID = $xnObj->getAttribute("rdf:about");
			if (substr($sNodeID, 0, 3) === 'AID') {
				$sAvatarID 			= $sNodeID;
				$sAvatarImage 		= '';
				foreach ($xnObj->childNodes as $xnObjProp) {
					GetXMLNodeValue($xnObjProp, 'ss:image',		 	$sAvatarImage);
				}
				$aRow					= array();
				$aRow['avatarid']		= $sAvatarID;
				$aRow['avatarimage']	= $sAvatarImage;
				$aAvatars[] 			= $aRow;
			}
		}
		$xnObj = $xnObj->nextSibling;
	}
	return $aAvatars;
}
function WriteAvatarImage($sImageID, $bIsReply)
{
	if (IsSessionSettingTrue('use_avatars')) {
		if ($sImageID === '') {
			echo '<div class="discussion-avatar avatarimg-default"></div>';
		} else {
			echo '<div class="discussion-avatar" id="avatarimg-' . $sImageID . '"></div>';
		}
	} else {
		if (strIsTrue($bIsReply)) {
			echo '<div class="discussion-avatar propsprite-reply"></div>';
		} else {
			echo '<div class="discussion-avatar propsprite-discuss"></div>';
		}
	}
}
function BuildHTMLComboFromArray($aItems, $aAttribs, $bAllowBlank = false, $sDefaultValue = '')
{
	$sHTML 	= '';
	$iCnt = count($aItems);
	if ($iCnt > 0)
		$sHTML 	= '<select ';
	else
		$sHTML 	= '<input ';
	if (is_array($aAttribs)) {
		foreach ($aAttribs as $sKey => $sValue) {
			if ($iCnt > 0 || ($iCnt === 0 && $sValue !== 'webea-main-styled-combo')) {
				$sHTML .= ' ' . $sKey . '="' . $sValue . '"';
			} elseif ($iCnt === 0 && $sValue === 'webea-main-styled-combo') {
				$sHTML .= ' class="webea-main-styled-textbox2"';
			}
		}
	}
	if ($iCnt > 0) {
		$sHTML .= '>' . PHP_EOL;
		if ($bAllowBlank) {
			$sHTML .= '<option value="[blank]">&nbsp;</option>';
		}
		if (is_array($aItems)) {
			foreach ($aItems as $sKey => $sValue) {
				$bSelectedFound = false;
				if (!strIsEmpty($sDefaultValue) && $sDefaultValue === $sValue) {
					$sHTML .= '<option value="' . htmlspecialchars($sValue) . '" selected>' . htmlspecialchars($sValue) . '</option>';
					$bSelectedFound = true;
				} else
					$sHTML .= '<option value="' . htmlspecialchars($sValue) . '">' . htmlspecialchars($sValue) . '</option>';
			}
			if (!$bSelectedFound && !strIsEmpty($sDefaultValue) && $bAllowBlank) {
				$sHTML .= '<option value="' . htmlspecialchars($sDefaultValue) . '" selected>' . htmlspecialchars($sDefaultValue) . '</option>';
			}
		}
		$sHTML .= '</select>';
	} else {
		if (!strIsEmpty($sDefaultValue)) {
			$sHTML .= ' value="' . $sDefaultValue . '"';
		}
		$sHTML .= '>' . PHP_EOL;
	}
	return $sHTML;
}
function BuildHTMLComboFrom2DimArray($aItems, $aAttribs, $bAllowBlank = false, $sDefaultValue = '')
{
	$sHTML 	= '';
	$iCnt = count($aItems);
	if ($iCnt > 0)
		$sHTML 	= '<select ';
	else
		$sHTML 	= '<input ';
	if (is_array($aAttribs)) {
		foreach ($aAttribs as $sKey => $sValue) {
			if ($iCnt > 0 || ($iCnt === 0 && $sValue !== 'webea-main-styled-combo')) {
				$sHTML .= ' ' . $sKey . '="' . $sValue . '"';
			} elseif ($iCnt === 0 && $sValue === 'webea-main-styled-combo') {
				$sHTML .= ' class="webea-main-styled-textbox2"';
			}
		}
	}
	if ($iCnt > 0) {
		$sHTML .= '>' . PHP_EOL;
		if ($bAllowBlank) {
			$sHTML .= '<option value="[blank]">&nbsp;</option>';
		}
		if (is_array($aItems)) {
			for ($i = 0; $i < $iCnt; $i++) {
				$sLabel = SafeGetArrayItem2Dim($aItems, $i, 'label');
				$sValue = SafeGetArrayItem2Dim($aItems, $i, 'value');
				$bSelectedFound = false;
				if (!strIsEmpty($sDefaultValue) && $sDefaultValue === $sValue) {
					$sHTML .= '<option value="' . htmlspecialchars($sValue) . '" selected>' . htmlspecialchars($sLabel) . '</option>';
					$bSelectedFound = true;
				} else
					$sHTML .= '<option value="' . htmlspecialchars($sValue) . '">' . htmlspecialchars($sLabel) . '</option>';
			}
			if (!$bSelectedFound && !strIsEmpty($sDefaultValue) && $bAllowBlank) {
				$sHTML .= '<option value="' . htmlspecialchars($sDefaultValue) . '" selected>' . htmlspecialchars($sDefaultValue) . '</option>';
			}
		}
		$sHTML .= '</select>';
	} else {
		if (!strIsEmpty($sDefaultValue)) {
			$sHTML .= ' value="' . $sDefaultValue . '"';
		}
		$sHTML .= '>' . PHP_EOL;
	}
	return $sHTML;
}
function GetChgMgmtFieldNames($sChgMgmtType, $iFormatType, &$sDate1Desc, &$sDate2Desc, &$sPerson1Desc, &$sPerson2Desc)
{
	$sDate1Desc 		= _glt('Requested on');
	$sDate2Desc 		= _glt('Completed on');
	$sPerson1Desc 		= _glt('Requested by');
	$sPerson2Desc 		= _glt('Completed by');
	if ($sChgMgmtType === 'defect' || $sChgMgmtType === 'event') {
		$sDate1Desc 	= _glt('Reported on');
		$sDate2Desc 	= _glt('Resolved on');
		$sPerson1Desc 	= _glt('Reported by');
		$sPerson2Desc 	= _glt('Resolved by');
	} elseif ($sChgMgmtType === 'issue') {
		$sDate1Desc 	= _glt('Raised on');
		$sDate2Desc 	= _glt('Completed on');
		$sPerson1Desc 	= _glt('Raised by');
		$sPerson2Desc 	= _glt('Completed by');
	} elseif ($sChgMgmtType === 'decision') {
		$sDate1Desc 	= _glt('Date');
		$sDate2Desc 	= _glt('Effective');
		$sPerson1Desc 	= _glt('Owner');
		$sPerson2Desc 	= _glt('Author');
	}
	if ($iFormatType === 1) {
		$sDate1Desc 	= str_replace(' ', '&nbsp;', $sDate1Desc);
		$sDate2Desc 	= str_replace(' ', '&nbsp;', $sDate2Desc);
		$sPerson1Desc 	= str_replace(' ', '&nbsp;', $sPerson1Desc);
		$sPerson2Desc 	= str_replace(' ', '&nbsp;', $sPerson2Desc);
	} elseif ($iFormatType === 2) {
		$sDate1Desc 	= str_replace(' ', '', mb_strtolower($sDate1Desc));
		$sDate2Desc 	= str_replace(' ', '', mb_strtolower($sDate2Desc));
		$sPerson1Desc 	= str_replace(' ', '', mb_strtolower($sPerson1Desc));
		$sPerson2Desc 	= str_replace(' ', '', mb_strtolower($sPerson2Desc));
	}
}
function GetWatchListOptions()
{
	$aWatchList = array();
	$aWatchList['period']		 	= GetIndividualWatchListIntOption('period');
	$aWatchList['recentdiscuss'] 	= GetIndividualWatchListBooleanOption('recentdiscuss');
	$aWatchList['recentreview'] 	= GetIndividualWatchListBooleanOption('recentreview');
	$aWatchList['recentdiag'] 		= GetIndividualWatchListBooleanOption('recentdiag');
	$aWatchList['recentelem'] 		= GetIndividualWatchListBooleanOption('recentelem');
	$aWatchList['resallocactive']	= GetIndividualWatchListBooleanOption('resallocactive');
	$aWatchList['resalloctoday'] 	= GetIndividualWatchListBooleanOption('resalloctoday');
	$aWatchList['resallocoverdue']	= GetIndividualWatchListBooleanOption('resallocoverdue');
	$aWatchList['testrecentpass'] 	= GetIndividualWatchListBooleanOption('testrecentpass');
	$aWatchList['testrecentfail'] 	= GetIndividualWatchListBooleanOption('testrecentfail');
	$aWatchList['testrecentdefer']	= GetIndividualWatchListBooleanOption('testrecentdefer');
	$aWatchList['testrecentnotchk']	= GetIndividualWatchListBooleanOption('testrecentnotchk');
	$aWatchList['testnotrun']		= GetIndividualWatchListBooleanOption('testnotrun');
	$aWatchList['changeverified'] 	= GetIndividualWatchListBooleanOption('changeverified');
	$aWatchList['changerequested'] 	= GetIndividualWatchListBooleanOption('changerequested');
	$aWatchList['changecompleted']	= GetIndividualWatchListBooleanOption('changecompleted');
	$aWatchList['changenew']		= GetIndividualWatchListBooleanOption('changenew');
	$aWatchList['changeincomplete']	= GetIndividualWatchListBooleanOption('changeincomplete');
	$aWatchList['defectverified'] 	= GetIndividualWatchListBooleanOption('defectverified');
	$aWatchList['defectrequested'] 	= GetIndividualWatchListBooleanOption('defectrequested');
	$aWatchList['defectcompleted']	= GetIndividualWatchListBooleanOption('defectcompleted');
	$aWatchList['defectnew']		= GetIndividualWatchListBooleanOption('defectnew');
	$aWatchList['defectincomplete']	= GetIndividualWatchListBooleanOption('defectincomplete');
	$aWatchList['issueverified'] 	= GetIndividualWatchListBooleanOption('issueverified');
	$aWatchList['issuerequested'] 	= GetIndividualWatchListBooleanOption('issuerequested');
	$aWatchList['issuecompleted']	= GetIndividualWatchListBooleanOption('issuecompleted');
	$aWatchList['issuenew']			= GetIndividualWatchListBooleanOption('issuenew');
	$aWatchList['issueincomplete']	= GetIndividualWatchListBooleanOption('issueincomplete');
	$aWatchList['taskverified'] 	= GetIndividualWatchListBooleanOption('taskverified');
	$aWatchList['taskrequested'] 	= GetIndividualWatchListBooleanOption('taskrequested');
	$aWatchList['taskcompleted']	= GetIndividualWatchListBooleanOption('taskcompleted');
	$aWatchList['tasknew']			= GetIndividualWatchListBooleanOption('tasknew');
	$aWatchList['taskincomplete']	= GetIndividualWatchListBooleanOption('taskincomplete');
	$aWatchList['eventrequested'] 	= GetIndividualWatchListBooleanOption('eventrequested');
	$aWatchList['eventcompleted']	= GetIndividualWatchListBooleanOption('eventcompleted');
	$aWatchList['eventnew']			= GetIndividualWatchListBooleanOption('eventnew');
	$aWatchList['eventincomplete']	= GetIndividualWatchListBooleanOption('eventincomplete');
	$aWatchList['decisionverified'] 	= GetIndividualWatchListBooleanOption('decisionverified');
	$aWatchList['decisionrequested'] 	= GetIndividualWatchListBooleanOption('decisionrequested');
	$aWatchList['decisioncompleted']	= GetIndividualWatchListBooleanOption('decisioncompleted');
	$aWatchList['decisionnew']			= GetIndividualWatchListBooleanOption('decisionnew');
	$aWatchList['decisionincomplete']	= GetIndividualWatchListBooleanOption('decisionincomplete');
	return $aWatchList;
}
function GetWatchListOptionString()
{
	$sWatchListOptions = '';
	if (count($_COOKIE) > 0) {
		$sModelName = SafeGetInternalArrayParameter($_SESSION, 'model_name', 'unknown');
		$sModelName = CookieNameEncoding($sModelName);
		$sUserName = SafeGetInternalArrayParameter($_SESSION, 'login_user', 'nouser');
		$sUserName = CookieNameEncoding($sUserName);
		$sCookieName = 'webea_' . $sModelName . '_' . $sUserName . '_watchlist_options';
		$sWatchListOptions = SafeGetInternalArrayParameter($_COOKIE, $sCookieName, '');
		$sWatchListOptions = ExpandWLOptions($sWatchListOptions);
	} else {
		$sWatchListOptions = SafeGetInternalArrayParameter($_SESSION, 'watchlist_options', '');
	}
	if (strIsEmpty($sWatchListOptions)) {
		$sWatchListOptions = SafeGetInternalArrayParameter($_SESSION, 'watchlist_options_model', '');
	}
	return $sWatchListOptions;
}
function GetIndividualWatchListIntOption($sOption)
{
	$iReturn = 0;
	$sWatchListOptions = GetWatchListOptionString();
	if (!strIsEmpty($sWatchListOptions)) {
		$iPos = strpos($sWatchListOptions, ';' . $sOption . '=', 0);
		if ($iPos !== FALSE) {
			$iPos += strlen($sOption) + 2;
			$iPosEnd = strpos($sWatchListOptions, ';', $iPos);
			if ($iPosEnd !== FALSE) {
				$sValue = substr($sWatchListOptions, $iPos, $iPosEnd - $iPos);
				$iReturn = (int)$sValue;
			}
		}
	}
	return $iReturn;
}
function GetIndividualWatchListBooleanOption($sOption)
{
	$iReturn = 0;
	$sWatchListOptions = GetWatchListOptionString();
	if (!strIsEmpty($sWatchListOptions)) {
		$iPos = strpos($sWatchListOptions, ';' . $sOption . '=1;', 0);
		if ($iPos !== FALSE) {
			$iReturn = 1;
		}
	}
	return $iReturn;
}
function ExpandWLOptions($sWLOptions)
{
	$sReturn = $sWLOptions;
	if (!strIsEmpty($sWLOptions)) {
		$sReturn = str_replace('p=', 'period=', $sReturn);
		$sReturn = str_replace(';re1=', ';recentdiscuss=', $sReturn);
		$sReturn = str_replace(';re2=', ';recentreview=', $sReturn);
		$sReturn = str_replace(';re3=', ';recentdiag=', $sReturn);
		$sReturn = str_replace(';re4=', ';recentelem=', $sReturn);
		$sReturn = str_replace(';ra1=', ';resallocactive=', $sReturn);
		$sReturn = str_replace(';ra2=', ';resalloctoday=', $sReturn);
		$sReturn = str_replace(';ra3=', ';resallocoverdue=', $sReturn);
		$sReturn = str_replace(';te1=', ';testrecentpass=', $sReturn);
		$sReturn = str_replace(';te2=', ';testrecentfail=', $sReturn);
		$sReturn = str_replace(';te3=', ';testrecentdefer=', $sReturn);
		$sReturn = str_replace(';te4=', ';testrecentnotchk=', $sReturn);
		$sReturn = str_replace(';te5=', ';testnotrun=', $sReturn);
		$sReturn = str_replace(';ch1=', ';changeverified=', $sReturn);
		$sReturn = str_replace(';ch2=', ';changerequested=', $sReturn);
		$sReturn = str_replace(';ch3=', ';changecompleted=', $sReturn);
		$sReturn = str_replace(';ch4=', ';changenew=', $sReturn);
		$sReturn = str_replace(';ch5=', ';changeincomplete=', $sReturn);
		$sReturn = str_replace(';de1=', ';defectverified=', $sReturn);
		$sReturn = str_replace(';de2=', ';defectrequested=', $sReturn);
		$sReturn = str_replace(';de3=', ';defectcompleted=', $sReturn);
		$sReturn = str_replace(';de4=', ';defectnew=', $sReturn);
		$sReturn = str_replace(';de5=', ';defectincomplete=', $sReturn);
		$sReturn = str_replace(';is1=', ';issueverified=', $sReturn);
		$sReturn = str_replace(';is2=', ';issuerequested=', $sReturn);
		$sReturn = str_replace(';is3=', ';issuecompleted=', $sReturn);
		$sReturn = str_replace(';is4=', ';issuenew=', $sReturn);
		$sReturn = str_replace(';is5=', ';issueincomplete=', $sReturn);
		$sReturn = str_replace(';ta1=', ';taskverified=', $sReturn);
		$sReturn = str_replace(';ta2=', ';taskrequested=', $sReturn);
		$sReturn = str_replace(';ta3=', ';taskcompleted=', $sReturn);
		$sReturn = str_replace(';ta4=', ';tasknew=', $sReturn);
		$sReturn = str_replace(';ta5=', ';taskincomplete=', $sReturn);
		$sReturn = str_replace(';ev1=', ';eventrequested=', $sReturn);
		$sReturn = str_replace(';ev2=', ';eventcompleted=', $sReturn);
		$sReturn = str_replace(';ev3=', ';eventnew=', $sReturn);
		$sReturn = str_replace(';ev4=', ';eventincomplete=', $sReturn);
		$sReturn = str_replace(';dc1=', ';decisionverified=', $sReturn);
		$sReturn = str_replace(';dc2=', ';decisionrequested=', $sReturn);
		$sReturn = str_replace(';dc3=', ';decisioncompleted=', $sReturn);
		$sReturn = str_replace(';dc4=', ';decisionnew=', $sReturn);
		$sReturn = str_replace(';dc5=', ';decisionincomplete=', $sReturn);
	}
	return $sReturn;
}
function AbbreviateWLOptions($sWLOptions)
{
	$sReturn = $sWLOptions;
	if (!strIsEmpty($sWLOptions)) {
		$sReturn = str_replace('period=', 'p=', $sReturn);
		$sReturn = str_replace(';recentdiscuss=', ';re1=', $sReturn);
		$sReturn = str_replace(';recentreview=', ';re2=', $sReturn);
		$sReturn = str_replace(';recentdiag=', ';re3=', $sReturn);
		$sReturn = str_replace(';recentelem=', ';re4=', $sReturn);
		$sReturn = str_replace(';resallocactive=', ';ra1=', $sReturn);
		$sReturn = str_replace(';resalloctoday=', ';ra2=', $sReturn);
		$sReturn = str_replace(';resallocoverdue=', ';ra3=', $sReturn);
		$sReturn = str_replace(';testrecentpass=', ';te1=', $sReturn);
		$sReturn = str_replace(';testrecentfail=', ';te2=', $sReturn);
		$sReturn = str_replace(';testrecentdefer=', ';te3=', $sReturn);
		$sReturn = str_replace(';testrecentnotchk=', ';te4=', $sReturn);
		$sReturn = str_replace(';testnotrun=', ';te5=', $sReturn);
		$sReturn = str_replace(';changeverified=', ';ch1=', $sReturn);
		$sReturn = str_replace(';changerequested=', ';ch2=', $sReturn);
		$sReturn = str_replace(';changecompleted=', ';ch3=', $sReturn);
		$sReturn = str_replace(';changenew=', ';ch4=', $sReturn);
		$sReturn = str_replace(';changeincomplete=', ';ch5=', $sReturn);
		$sReturn = str_replace(';defectverified=', ';de1=', $sReturn);
		$sReturn = str_replace(';defectrequested=', ';de2=', $sReturn);
		$sReturn = str_replace(';defectcompleted=', ';de3=', $sReturn);
		$sReturn = str_replace(';defectnew=', ';de4=', $sReturn);
		$sReturn = str_replace(';defectincomplete=', ';de5=', $sReturn);
		$sReturn = str_replace(';issueverified=', ';is1=', $sReturn);
		$sReturn = str_replace(';issuerequested=', ';is2=', $sReturn);
		$sReturn = str_replace(';issuecompleted=', ';is3=', $sReturn);
		$sReturn = str_replace(';issuenew=', ';is4=', $sReturn);
		$sReturn = str_replace(';issueincomplete=', ';is5=', $sReturn);
		$sReturn = str_replace(';taskverified=', ';ta1=', $sReturn);
		$sReturn = str_replace(';taskrequested=', ';ta2=', $sReturn);
		$sReturn = str_replace(';taskcompleted=', ';ta3=', $sReturn);
		$sReturn = str_replace(';tasknew=', ';ta4=', $sReturn);
		$sReturn = str_replace(';taskincomplete=', ';ta5=', $sReturn);
		$sReturn = str_replace(';eventrequested=', ';ev1=', $sReturn);
		$sReturn = str_replace(';eventcompleted=', ';ev2=', $sReturn);
		$sReturn = str_replace(';eventnew=', ';ev3=', $sReturn);
		$sReturn = str_replace(';eventincomplete=', ';ev4=', $sReturn);
		$sReturn = str_replace(';decisionverified=', ';dc1=', $sReturn);
		$sReturn = str_replace(';decisionrequested=', ';dc2=', $sReturn);
		$sReturn = str_replace(';decisioncompleted=', ';dc3=', $sReturn);
		$sReturn = str_replace(';decisionnew=', ';dc4=', $sReturn);
		$sReturn = str_replace(';decisionincomplete=', ';dc5=', $sReturn);
	}
	return $sReturn;
}
function GetTaggedValue($aTaggedValues, $sTVName, $sFQTVName)
{
	$sReturn = '';
	$sReturn	= SafeGetArrayItem2DimByNameValuePair($aTaggedValues, 'name', $sTVName, 'value');
	if (strIsEmpty($sReturn) && !strIsEmpty($sFQTVName)) {
		$sReturn	= SafeGetArrayItem2DimByNameValuePair($aTaggedValues, 'name', $sFQTVName, 'value');
	}
	return $sReturn;
}
function GetChgMgtAddText($sChangeMgtType, &$sText, &$sImageClass)
{
	$sText = '';
	$sImageClass = '';
	if ($sChangeMgtType === 'change') {
		$sImageClass = 'propsprite-changeadd';
		$sText = _glt('Add change to');
	} elseif ($sChangeMgtType === 'defect') {
		$sImageClass = 'propsprite-defectadd';
		$sText = _glt('Add defect to');
	} elseif ($sChangeMgtType === 'issue') {
		$sImageClass = 'propsprite-issueadd';
		$sText = _glt('Add issue to');
	} elseif ($sChangeMgtType === 'task') {
		$sImageClass = 'propsprite-taskadd';
		$sText = _glt('Add task to');
	} elseif ($sChangeMgtType === 'risk') {
		$sImageClass = 'propsprite-riskadd';
		$sText = _glt('Add risk to');
	}
}
function mb_ucfirst($string)
{
	$strlen = mb_strlen($string);
	$firstChar = mb_substr($string, 0, 1);
	$otherChars = mb_substr($string, 1, $strlen - 1);
	return mb_strtoupper($firstChar) . $otherChars;
}
function BuildWebUID($sFeatureAbbr, $sParentGUID, $iItemNo)
{
	$sNum 		= str_pad((string)$iItemNo, 5, "0", STR_PAD_LEFT);
	$sParent 	= substr($sParentGUID, 4);
	$sParent 	= trim($sParent, '}');
	return $sFeatureAbbr . '-' . $sParent . '-' . $sNum;
}
function AreEAServerOptionsEnabled()
{
	$b = false;
	if (IsSessionSettingTrue('add_patterns'))
		$b = true;
	elseif (IsSessionSettingTrue('edit_diagrams'))
		$b = true;
	return $b;
}
function GetShowBrowserMinPropsStyleClasses()
{
	$sRet = '';
	if (IsSessionSettingTrue('show_browser') && IsSessionSettingTrue('show_miniproperties')) {
		$sRet = ' show-browserminiprops';
	} elseif (IsSessionSettingTrue('show_browser')) {
		$sRet = ' show-browser';
	} elseif (IsSessionSettingTrue('show_miniproperties')) {
		$sRet = ' show-miniprops';
	}
	return $sRet;
}
function GetShowBrowserStyleClasses()
{
	$sRet = '';
	if (IsSessionSettingTrue('show_browser')) {
		$sRet = ' show-browser';
	}
	return $sRet;
}
function WriteHamburger($sBrowserStyle, $sMiniPropsStyle, $sShowRadioIcons, $sShowRadioList, $sShowRadioNotes)
{
	global $sSystemOutputStyle;
	echo '        <div class="contextmenu-header">' . _glt('Menu') . '</div>';
	echo '          <div class="contextmenu-items">';
	echo '	          <div class="hamburger-show-browser contextmenu-item" onclick="show_browser()"><div id="hamburger-pnl-browser" ' . $sBrowserStyle . '><div class="mainsprite-browser"><div class="hamburger-item-text">' . _glt('Browser') . '</div></div></div></div>';
	echo '	          <div class="hamburger-show-miniprops contextmenu-item" onclick="show_mini_properties()"><div id="hamburger-pnl-miniprops" ' . $sMiniPropsStyle . '><div class="mainsprite-miniprops"><div class="hamburger-item-text">' . _glt('Properties') . '</div></div></div></div>';
	if (g_cbDebugging) {
		echo '	          <div class="contextmenu-item" onclick="show_system_output()"><div id="hamburger-systemoutput" ' . $sSystemOutputStyle . '><div class="mainsprite-systemoutput"><div class="hamburger-item-text">' . _glt('System Output') . '</div></div></div></div>';
	}
	echo '            <div id="contextmenu-package">';
	echo '            <div class="contextmenu-items-hdr">' . _glt('Package Layout') . '</div>';
	echo '            <div class="contextmenu-item-inner">';
	echo '              <div class="contextmenu-item" onclick="set_main_layout(\'1\')"><div id="main-layout-icons" ' . $sShowRadioIcons . '><div id="hamburger-iconview-icon"><div class="hamburger-item-text">' . _glt('Icon view') . '</div></div></div></div>';
	echo '              <div class="contextmenu-item" onclick="set_main_layout(\'2\')"><div id="main-layout-list" ' . $sShowRadioList . ' ><div id="hamburger-listview-icon"><div class="hamburger-item-text">' . _glt('List view') . '</div></div></div></div>';
	echo '              <div class="contextmenu-item" onclick="set_main_layout(\'3\')"><div id="main-layout-notes" ' . $sShowRadioNotes . '><div id="hamburger-notesview-icon"><div class="hamburger-item-text">' . _glt('Notes view') . '</div></div></div></div>';
	echo '            </div>';
	echo '	        </div>';
	$sPropertyLayoutNo = $_SESSION['propertylayout'];
	$sWideCMState = (($sPropertyLayoutNo == '1') ? 'enabled' : 'disabled');
	$sSplitCMState = (($sPropertyLayoutNo == '2') ? 'enabled' : 'disabled');
	echo '	        <hr>';
	echo '		    <div class="contextmenu-item contextmenu-about" onclick="OnShowAboutPage(\'' . g_csWebEAVersion . '\')"><div id="hamburger-about-icon"><div class="hamburger-item-text">' . _glt('About WebEA') . '</div></div></div>';
	echo '	        <div id="contextmenu-logout">';
	echo '	          <div class="contextmenu-item" onclick="OnLogoff(this)"><div class="hamburger-logout-icon"><div class="hamburger-item-text">' . _glt('Logout') . '</div></div></div>';
	echo '	        </div>';
	echo '	      </div>';
}
function WriteSearchMenu()
{
	echo '		  <div class="contextmenu-header">' . _glt('Search') . '</div>';
	echo '		  <div class="contextmenu-items">';
	$iDays = SafeGetArrayItem1DimInt($_SESSION, 'recent_search_days');
	if ($iDays == 0) {
		$iDays = 3;
	}
	$sDays = (string)$iDays;
	echo '			<div class="contextmenu-item" onclick="OnPromptForGotoGUID()"><img alt="" src="images/spriteplaceholder.png" class="mainsprite-gotolink">' . _glt('Goto item') . '</div>';
	echo '			<hr>';
	if (IsSessionSettingTrue('show_discuss')) {
		echo '			<div class="contextmenu-item" onclick="OnRunPredefinedSearch(\'category=review&amp;term=&amp;recent=-' . $sDays . 'd\',\'' . _glt('Search Results') . '\')"><img alt="" title="' . _glt('Display list of recent reviews') . '" src="images/spriteplaceholder.png" class="mainsprite-recentreview">' . _glt('Reviews') . '</div>';
		echo '			<div class="contextmenu-item" onclick="OnRunPredefinedSearch(\'category=discussion&amp;term=&amp;recent=-' . $sDays . 'd\',\'' . _glt('Search Results') . '\')"><img alt="" title="' . _glt('Display list of discussions') . '" src="images/spriteplaceholder.png" class="mainsprite-recentdiscuss">' . _glt('Discussions') . '</div>';
		echo '			<hr>';
	}
	echo '			<div class="contextmenu-item" onclick="OnRunPredefinedSearch(\'category=diagram&amp;term=&amp;recent=-' . $sDays . 'd\',\'' . _glt('Search Results') . '\')"><img alt="" title="' . _glt('Display list of recently modified diagrams') . '" src="images/spriteplaceholder.png" class="mainsprite-recentdiagram">' . _glt('Diagrams') . '</div>';
	echo '			<div class="contextmenu-item" onclick="OnRunPredefinedSearch(\'category=element&amp;term=&amp;recent=-' . $sDays . 'd\',\'' . _glt('Search Results') . '\')"><img alt="" title="' . _glt('Display list of recently modified elements') . '" src="images/spriteplaceholder.png" class="mainsprite-recentelement">' . _glt('Elements') . '</div>';
	echo '			<div class="contextmenu-item" onclick="load_object(\'watchlist\',\'\',\'\',\'\',\'' . _glt('Watchlist') . '\',\'images/element16/watchlist.png\')"><img alt="" src="images/spriteplaceholder.png" class="mainsprite-watchlist16color">' . _glt('Watchlist') . '</div>';
	echo '	        <hr>';
	echo '			<div class="contextmenu-item" onclick="load_object(\'matrixprofiles\',\'\',\'\',\'\',\'' . _glt('Matrix Profiles') . '\',\'images/element16/matrix.png\')"><img alt="" src="images/spriteplaceholder.png" class="mainsprite-matrixcolor">' . _glt('Matrix') . '</div>';
	echo '			<hr>';
	echo '			<div class="contextmenu-item" onclick="load_object(\'search\',\'\',\'\',\'\',\'' . _glt('Search') . '\',\'images/element16/search.png\')"><img alt="" src="images/spriteplaceholder.png" class="mainsprite-search16color">' . _glt('Custom search') . '</div>';
	echo '		  </div>';
}
function GetCurrentURL()
{
	$sFullURL  = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
	$sFullURL .= '://' . $_SERVER['HTTP_HOST'];
	$sDocPath = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
	$iLastPos = strripos($sDocPath, '?');
	if ($iLastPos !== FALSE && $iLastPos >= 0) {
		$sDocPath = substr($sDocPath, 0, $iLastPos);
	}
	$iLastPos = strripos($sDocPath, '/');
	if ($iLastPos !== FALSE && $iLastPos >= 0) {
		$sDocPath = substr($sDocPath, 0, $iLastPos);
	}
	$iLastPos = strripos($sDocPath, '/data_api');
	if ($iLastPos !== FALSE && $iLastPos >= 0) {
		$sDocPath = substr($sDocPath, 0, $iLastPos);
	}
	$sFullURL .= $sDocPath;
	return $sFullURL;
}
function GetOpenIDRedirectURL()
{
	return GetCurrentURL() . '/login_sso.php';
}
function WriteHelpHyperlink()
{
	return '&nbsp&nbsp<a href="' . g_csHelpLocation . 'model_repository/webea_troubleshoot.html" title="Help" class="error-message-help-link">[Help]</a>';
}
function SetUseSSLFlagBasedOnProtocol()
{
	$sProtocol = SafeGetInternalArrayParameter($_SESSION, 'protocol', '');
	if (!strIsEmpty($sProtocol)) {
		if ($sProtocol === 'https') {
			$_SESSION['use_ssl'] = 'true';
		} else if ($sProtocol === 'http') {
			$_SESSION['use_ssl'] = 'false';
		}
	}
}
