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
$sGUID			= SafeGetInternalArrayParameter($_POST, 'parentguid');
$sNotes 		= SafeGetInternalArrayParameter($_POST, 'notes');
$sParentName	= SafeGetInternalArrayParameter($_POST, 'parentname');
$sParentImageURL = SafeGetInternalArrayParameter($_POST, 'parentimageurl');
$sLoginGUID		= SafeGetInternalArrayParameter($_POST, 'loginguid');
$sMissingField = '';
if (strIsEmpty($sGUID))
	$sMissingField .= _glt('Element ID') . ', ';
if (!strIsEmpty($sMissingField)) {
	$sMissingField = substr($sMissingField, 0, strlen($sMissingField) - 2);
	$sErrorMsg = str_replace('%FIELDS%', $sMissingField, _glt('The mandatory fields missing'));
	setResponseCode(400, $sErrorMsg);
	return $sErrorMsg;
}
$sNotes		= ConvertHTMLToEANote($sNotes);
$sReturn = '';
$xmlRespDoc = null;
include('./data_api/update_elementnote.php');
$sOSLCErrorMsg = BuildOSLCErrorString();
if (strIsEmpty($sOSLCErrorMsg)) {
	$sSuccessAttrib = GetOSLCSuccess($xmlRespDoc);
	if (
		strpos($sSuccessAttrib, '/oslc/am/completeresource/') !== false ||
		strpos($sSuccessAttrib, '/oslc/am/resource/') !== false
	) {
		$sParentName	= htmlspecialchars($sParentName);
		$sParentDetails = '<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sParentImageURL) . '"> ' . $sParentName;
		$sReturn = 'success: ' . str_replace('%OBJNAME%', $sParentDetails, _glt('Note updated'));
	}
} else {
	$sReturn = $sOSLCErrorMsg;
}
echo $sReturn;
