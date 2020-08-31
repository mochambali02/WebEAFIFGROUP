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
	$sGUID			= SafeGetInternalArrayParameter($_POST, 'guid');
	$sName 			= SafeGetInternalArrayParameter($_POST, 'name');
	$sStatus		= SafeGetInternalArrayParameter($_POST, 'status');
	$sPriority		= SafeGetInternalArrayParameter($_POST, 'priority');
	$sAuthor1		= SafeGetInternalArrayParameter($_POST, 'author1');
	$sDate1			= SafeGetInternalArrayParameter($_POST, 'date1');
	$sAuthor2		= SafeGetInternalArrayParameter($_POST, 'author2');
	$sDate2			= SafeGetInternalArrayParameter($_POST, 'date2');
	$sVersion		= SafeGetInternalArrayParameter($_POST, 'version');
	$sWeight		= SafeGetInternalArrayParameter($_POST, 'weight');
	$sType			= SafeGetInternalArrayParameter($_POST, 'type');
	$sNotes 		= SafeGetInternalArrayParameter($_POST, 'notes');
	$sHistory		= SafeGetInternalArrayParameter($_POST, 'history');
	$sFeatureType 	= SafeGetInternalArrayParameter($_POST, 'featuretype');
	$sParentName	= SafeGetInternalArrayParameter($_POST, 'parentname');
	$sParentImageURL= SafeGetInternalArrayParameter($_POST, 'parentimageurl');
	$sLoginGUID		= SafeGetInternalArrayParameter($_POST, 'loginguid');
	if ($sStatus==='[blank]')
		$sStatus = '';
	if ($sPriority==='[blank]')
		$sPriority = '';
	if ($sAuthor1==='[blank]')
		$sAuthor1 = '';
	if ($sAuthor2==='[blank]')
		$sAuthor2 = '';
	$sMissingField = '';
	if (  strIsEmpty($sFeatureType) )
		$sMissingField .= 'FeatureType, ';
	if (  strIsEmpty($sName) )
		$sMissingField .= 'Name, ';
	if ( $sFeatureType!=='risk')
	{
		if ( strIsEmpty($sStatus) )
			$sMissingField .= 'Status, ';
	}
	if ( strIsEmpty($sGUID) )
		$sMissingField .= 'Element ID, ';
	if ( !strIsEmpty($sMissingField))
	{
		$sMissingField = substr($sMissingField, 0, strlen($sMissingField)-2 );
		$sErrorMsg = str_replace('%FIELDS%', $sMissingField, _glt('The mandatory fields missing'));
		setResponseCode(400, $sErrorMsg);
		return $sErrorMsg;
	}
	$sDate1Desc 	= '';
	$sDate2Desc 	= '';
	$sPerson1Desc 	= '';
	$sPerson2Desc 	= '';
	GetChgMgmtFieldNames($sFeatureType, 2, $sDate1Desc, $sDate2Desc, $sPerson1Desc, $sPerson2Desc);
	if ( !strIsEmpty($sDate1) )
	{
		if (!IsDateValid($sDate1))
		{
			$sErrorMsg = str_replace('%FIELD%', $sDate1Desc, _glt('Invalid date value entered for FIELD'));
			setResponseCode(400, $sErrorMsg);
			return $sErrorMsg;
		}
	}
	if ( !strIsEmpty($sDate2) )
	{
		if (!IsDateValid($sDate2))
		{
			$sErrorMsg = str_replace('%FIELD%', $sDate2Desc, _glt('Invalid date value entered for FIELD'));
			setResponseCode(400, $sErrorMsg);
			return $sErrorMsg;
		}
	}
	$sNotes		= ConvertHTMLToEANote($sNotes);
	$sHistory	= ConvertHTMLToEANote($sHistory);
	$xmlRespDoc = null;
	include('./data_api/add_elementchgmgmt.php');
	$sReturn = '';
	$sOSLCErrorMsg = BuildOSLCErrorString();
	if ( strIsEmpty($sOSLCErrorMsg) )
	{
		$sSuccessAttrib = GetOSLCSuccess($xmlRespDoc);
		if (strpos($sSuccessAttrib, '/oslc/am/' . $sFeatureType . '/')!==false)
		{
			$sName	= htmlspecialchars($sName);
			$sParentName	= htmlspecialchars($sParentName);
			$sParentNameAndIcon = '<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sParentImageURL) . '"> ' . $sParentName;
			$sReturn = 'success: ' . str_replace(array('%CHTYPE%','%CHNAME%','%OBJNAME%'), array(mb_ucfirst($sFeatureType), $sName, $sParentNameAndIcon), _glt('Change management added'));
		}
	}
	else
	{
		$sReturn = $sOSLCErrorMsg;
	}
	echo $sReturn;
?>