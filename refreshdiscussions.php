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
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	$sErrorMsg = _glt('Direct navigation is not allowed');
	setResponseCode(405, $sErrorMsg);
	exit();
}
SafeStartSession();
if (isset($_SESSION['authorized']) === false) {
	$sErrorMsg = _glt('Your session appears to have timed out');
	setResponseCode(440, $sErrorMsg);
	exit();
}
$sObjectGUIDEnc = SafeGetInternalArrayParameter($_POST, 'objectguid');
$bUseAlternativeColour = SafeGetInternalArrayParameter($_POST, 'usealtcolour');
$sObjectGUID 	= urldecode($sObjectGUIDEnc);
$sResType 		= GetResTypeFromGUID($sObjectGUID);
$aDiscussions 	= array();
include('./data_api/get_discussions.php');
$sOSLCErrorMsg = BuildOSLCErrorString();
if (strIsEmpty($sOSLCErrorMsg)) {
	include('./propertiesdiscussions.php');
} else {
	echo 'error: ' . $sOSLCErrorMsg;
}
