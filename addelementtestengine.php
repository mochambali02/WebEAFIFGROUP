<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
$sRootPath = dirname(__FILE__);
require_once $sRootPath . '/globals.php';
SafeStartSession();
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	$sErrorMsg = _glt('Direct navigation is not allowed');
	setResponseCode(405, $sErrorMsg);
	exit();
}
if (isset($_SESSION['authorized']) === false) {
	$sErrorMsg = _glt('Your session appears to have timed out');
	setResponseCode(440, $sErrorMsg);
	exit();
}
$sName  		= SafeGetInternalArrayParameter($_POST, 'name');
$sClassType 	= SafeGetInternalArrayParameter($_POST, 'classtype');
$sType			= SafeGetInternalArrayParameter($_POST, 'type');
$sStatus 		= SafeGetInternalArrayParameter($_POST, 'status');
$sRunBy 		= SafeGetInternalArrayParameter($_POST, 'runby');
$sLastRun 		= SafeGetInternalArrayParameter($_POST, 'lastrun');
$sCheckedBy		= SafeGetInternalArrayParameter($_POST, 'checkedby');
$sNotes 		= SafeGetInternalArrayParameter($_POST, 'notes');
$sInput 		= SafeGetInternalArrayParameter($_POST, 'input');
$sAcceptance	= SafeGetInternalArrayParameter($_POST, 'acceptance');
$sResults 		= SafeGetInternalArrayParameter($_POST, 'results');
$sGUID			= SafeGetInternalArrayParameter($_POST, 'guid');
$sParentName	= SafeGetInternalArrayParameter($_POST, 'parentname');
$sParentImageURL = SafeGetInternalArrayParameter($_POST, 'parentimageurl');
$sLoginGUID		= SafeGetInternalArrayParameter($_POST, 'loginguid');
$sMissingField = '';
if (strIsEmpty($sName))
	$sMissingField .= 'Name, ';
if (strIsEmpty($sClassType))
	$sMissingField .= 'Class Type, ';
if (strIsEmpty($sType))
	$sMissingField .= 'Type, ';
if (strIsEmpty($sStatus))
	$sMissingField .= 'Status, ';
if (strIsEmpty($sGUID))
	$sMissingField .= 'Element ID, ';
if ($sRunBy === '[blank]')
	$sRunBy = '';
if ($sCheckedBy === '[blank]')
	$sCheckedBy = '';
if (!strIsEmpty($sMissingField)) {
	$sMissingField = substr($sMissingField, 0, strlen($sMissingField) - 2);
	$sErrorMsg = str_replace('%FIELDS%', $sMissingField, _glt('The mandatory fields missing'));
	setResponseCode(400, $sErrorMsg);
	return $sErrorMsg;
}
if (!strIsEmpty($sLastRun)) {
	if (!IsDateValid($sLastRun)) {
		$sErrorMsg = str_replace('%FIELD%', _glt('Last Run'), _glt('Invalid date value entered for FIELD'));
		setResponseCode(400, $sErrorMsg);
		return $sErrorMsg;
	}
}
$sNotes		= ConvertHTMLToEANote($sNotes);
$sInput		= ConvertHTMLToEANote($sInput);
$sAcceptance = ConvertHTMLToEANote($sAcceptance);
$sResults	= ConvertHTMLToEANote($sResults);
$xmlRespDoc = null;
include('./data_api/add_elementtest.php');
$sReturn = '';
$sOSLCErrorMsg = BuildOSLCErrorString();
if (strIsEmpty($sOSLCErrorMsg)) {
	$sSuccessAttrib = GetOSLCSuccess($xmlRespDoc);
	if (strpos($sSuccessAttrib, '/oslc/am/test/ts_') !== false) {
		$sName	= htmlspecialchars($sName);
		$sParentName	= htmlspecialchars($sParentName);
		$sParentNameAndIcon = '<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sParentImageURL) . '"> ' . $sParentName;
		$sReturn = 'success: ' . str_replace(array('%TSTNAME%', '%OBJNAME%'), array($sName, $sParentNameAndIcon), _glt('Test added'));
	}
} else {
	$sReturn = $sOSLCErrorMsg;
}
echo $sReturn;
