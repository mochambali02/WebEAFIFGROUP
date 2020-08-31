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
	if ($_SERVER["REQUEST_METHOD"] !== "POST")
	{
		$sErrorMsg = _glt('Direct navigation is not allowed');
		setResponseCode(405, $sErrorMsg);
		exit();
	}
	if (isset($_SESSION['authorized']) === false)
	{
		$sErrorMsg = _glt('Your session appears to have timed out');
		setResponseCode(440, $sErrorMsg);
		exit();
	}
	$sResource 		= SafeGetInternalArrayParameter($_POST, 'resource');
	$sRole			= SafeGetInternalArrayParameter($_POST, 'role');
	$sStartDate		= SafeGetInternalArrayParameter($_POST, 'startdate');
	$sEndDate		= SafeGetInternalArrayParameter($_POST, 'enddate');
	$sGUID			= SafeGetInternalArrayParameter($_POST, 'guid');
	$sPercentComp 	= SafeGetInternalArrayParameter($_POST, 'percentcomp');
	$sExpectedTime 	= SafeGetInternalArrayParameter($_POST, 'expectedtime');
	$sAllocTime		= SafeGetInternalArrayParameter($_POST, 'alloctime');
	$sExpendedTime 	= SafeGetInternalArrayParameter($_POST, 'expendedtime');
	$sNotes 		= SafeGetInternalArrayParameter($_POST, 'notes');
	$sHistory		= SafeGetInternalArrayParameter($_POST, 'history');
	$sParentName	= SafeGetInternalArrayParameter($_POST, 'parentname');
	$sParentImageURL= SafeGetInternalArrayParameter($_POST, 'parentimageurl');
	$sLoginGUID		= SafeGetInternalArrayParameter($_POST, 'loginguid');
	$sMissingField = '';
	if ( $sResource==='[blank]' )
		$sResource = '';
	if ( $sRole=='[blank]' )
		$sRole = '';
	if (strIsEmpty($sResource))
		$sMissingField .= _glt('Resource') . ', ';
	if (strIsEmpty($sRole))
		$sMissingField .= _glt('Role') . ', ';
	if (strIsEmpty($sStartDate))
		$sMissingField .= _glt('Start Date') . ', ';
	if (strIsEmpty($sEndDate))
		$sMissingField .= _glt('End Date') . ', ';
	if (strIsEmpty($sGUID))
		$sMissingField .= _glt('Element ID') . ', ';
	if ( !strIsEmpty($sMissingField) )
	{
		$sMissingField = substr($sMissingField, 0, strlen($sMissingField)-2 );
		$sErrorMsg = str_replace('%FIELDS%', $sMissingField, _glt('The mandatory fields missing'));
		setResponseCode(400, $sErrorMsg);
		return $sErrorMsg;
	}
	if ( !strIsEmpty($sStartDate) )
	{
		if (!IsDateValid($sStartDate))
		{
			$sErrorMsg = str_replace('%FIELD%', _glt('Start Date'), _glt('Invalid date value entered for FIELD'));
			setResponseCode(400, $sErrorMsg);
			return $sErrorMsg;
		}
	}
	$sNotes		= ConvertHTMLToEANote($sNotes);
	$sHistory	= ConvertHTMLToEANote($sHistory);
	$xmlRespDoc = null;
	include('./data_api/add_elementresalloc.php');
	$sReturn = '';
	$sOSLCErrorMsg = BuildOSLCErrorString();
	if ( strIsEmpty($sOSLCErrorMsg) )
	{
		$sSuccessAttrib = GetOSLCSuccess($xmlRespDoc);
		if (strpos($sSuccessAttrib, '/oslc/am/resourceallocation/ra_')!==false)
		{
			$sResource	= htmlspecialchars($sResource);
			$sParentName	= htmlspecialchars($sParentName);
			$sParentNameAndIcon = '<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sParentImageURL) . '"> ' . $sParentName;
			$sReturn = 'success: ' . str_replace(array('%RESNAME%','%OBJNAME%'), array($sResource, $sParentNameAndIcon), _glt('Resource allocation added'));
		}
	}
	else
		$sReturn = $sOSLCErrorMsg;
	echo $sReturn;
?>