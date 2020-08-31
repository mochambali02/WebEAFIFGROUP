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
	$sKey1  		= SafeGetInternalArrayParameter($_POST, 'key1');
	$sKey2  		= SafeGetInternalArrayParameter($_POST, 'key2');
	$sKey3  		= SafeGetInternalArrayParameter($_POST, 'key3');
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
	$sGUID			= SafeGetInternalArrayParameter($_POST, 'parentguid');
	$sParentName	= SafeGetInternalArrayParameter($_POST, 'parentname');
	$sParentImageURL= SafeGetInternalArrayParameter($_POST, 'parentimageurl');
	$sLoginGUID		= SafeGetInternalArrayParameter($_POST, 'loginguid');
	$sMissingField = '';
	if (strIsEmpty($sName))
		$sMissingField .= _glt('Name') . ', ';
	if (strIsEmpty($sClassType))
		$sMissingField .= _glt('Class Type') . ', ';
	if (strIsEmpty($sType))
		$sMissingField .= _glt('Type') . ', ';
	if (strIsEmpty($sStatus))
		$sMissingField .= _glt('Status') . ', ';
	if (strIsEmpty($sGUID))
		$sMissingField .= _glt('Element ID') . ', ';
	if ($sRunBy==='[blank]')
		$sRunBy = '';
	if ($sCheckedBy==='[blank]')
		$sCheckedBy = '';
	if (!strIsEmpty($sMissingField))
	{
		$sMissingField = substr($sMissingField, 0, strlen($sMissingField)-2 );
		$sErrorMsg = str_replace('%FIELDS%', $sMissingField, _glt('The mandatory fields missing'));
		setResponseCode(400, $sErrorMsg);
		return $sErrorMsg;
	}
	if ( !strIsEmpty($sLastRun) )
	{
		if (!IsDateValid($sLastRun))
		{
			$sErrorMsg = str_replace('%FIELD%', _glt('Last Run'), _glt('Invalid date value entered for FIELD'));
			setResponseCode(400, $sErrorMsg);
			return $sErrorMsg;
		}
	}
	$sReturn = '';
	$sNotes		= ConvertHTMLToEANote($sNotes);
	$sInput		= ConvertHTMLToEANote($sInput);
	$sAcceptance= ConvertHTMLToEANote($sAcceptance);
	$sResults	= ConvertHTMLToEANote($sResults);
	$xmlRespDoc = null;
	include('./data_api/update_elementtest.php');
	$sOSLCErrorMsg = BuildOSLCErrorString();
	if ( strIsEmpty($sOSLCErrorMsg) )
	{
		$sSuccessAttrib = GetOSLCSuccess($xmlRespDoc);
		if (strpos($sSuccessAttrib, '/oslc/am/test/')!==false)
		{
			$sName	= htmlspecialchars($sName);
			$sParentName	= htmlspecialchars($sParentName);
			$sParentDetails = '<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sParentImageURL) . '"> ' . $sParentName;
			$sReturn = 'success: ' . str_replace(array('%TSTNAME%','%OBJNAME%'), array($sName, $sParentDetails), _glt('Test updated'));
		}
	}
	else
	{
		$sReturn = $sOSLCErrorMsg;
	}
	echo $sReturn;
?>