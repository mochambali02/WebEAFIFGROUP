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
	$sName  		= SafeGetInternalArrayParameter($_POST, 'name');
	$sObjectType 	= SafeGetInternalArrayParameter($_POST, 'objecttype');
	$sParentGUID 	= SafeGetInternalArrayParameter($_POST, 'parentguid');
	$sParentName	= SafeGetInternalArrayParameter($_POST, 'parentname');
	$sIconStyle		= SafeGetInternalArrayParameter($_POST, 'iconstyle');
	$sNotes 		= SafeGetInternalArrayParameter($_POST, 'notes');
	$sAuthor 		= SafeGetInternalArrayParameter($_POST, 'author');
	$sLoginGUID 	= SafeGetInternalArrayParameter($_POST, 'loginguid');
	$sMissingField = '';
	if (strIsEmpty($sObjectType))
	{
		$sMissingField .= _glt('Object type') . ', ';
	}
	if (strIsEmpty($sName))
	{
		$sMissingField .= _glt('Name') . ', ';
	}
	if ( $sObjectType==='view' && strIsEmpty($sParentGUID) )
	{
		$sMissingField .= _glt('Container') . ', ';
	}
	if ( $sObjectType==='view' && strIsEmpty($sIconStyle) )
	{
		$sMissingField .= _glt('Icon Style') . ', ';
	}
	if (!strIsEmpty($sMissingField))
	{
		$sMissingField = substr($sMissingField, 0, strlen($sMissingField)-2 );
		$sErrorMsg = str_replace('%FIELDS%', $sMissingField, _glt('The mandatory fields missing'));
		setResponseCode(400, $sErrorMsg);
		return $sErrorMsg;
	}
	if ($sIconStyle!=='1' && $sIconStyle!=='2' && $sIconStyle!=='3' && $sIconStyle!=='4' & $sIconStyle!=='5')
	{
		$sIconStyle = '0';
	}
	$sReturn = '';
	$sNotes		= ConvertHTMLToEANote($sNotes);
	$xmlRespDoc = null;
	include('./data_api/add_rootpackage.php');
	$sOSLCErrorMsg = BuildOSLCErrorString();
	if ( strIsEmpty($sOSLCErrorMsg) )
	{
		$sSuccessAttrib = GetOSLCSuccess($xmlRespDoc);
		if (strpos($sSuccessAttrib, '/oslc/am/completeresource/')!==false ||
			strpos($sSuccessAttrib, '/oslc/am/resource/')!==false)
		{
			$sName	= htmlspecialchars($sName);
			if ( $sObjectType==='modelroot' )
			{
				$sParentName = 'the Model';
			}
			$sParentName	= htmlspecialchars($sParentName);
			$sType = _glt('Root Node');
			if ( $sObjectType === 'view')
			{
				$sType = 'View';
			}
			$sReturn = 'success: ' . str_replace(array('%OBJTYPE%','%OBJNAME%','%PARENT%'), array($sType, $sName, $sParentName), _glt('Object added'));
		}
	}
	else
	{
		$sReturn = $sOSLCErrorMsg;
	}
	echo $sReturn;
?>