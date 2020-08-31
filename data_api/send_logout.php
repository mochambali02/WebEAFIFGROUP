<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	$sRootPath = dirname(dirname(__FILE__));
	require_once $sRootPath . '/globals.php';
	$sErrorMsg	= 'Unknown logout error';
	SafeStartSession();
	BuildOSLCConnectionString();
	$sURL 		= $g_sOSLCString;
	$sURL 	   .= 'logout/';
	$sParam		= "";
	$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	AddURLParameter($sParam, 'useridentifier', $sLoginGUID);
	$sURL		= $sURL . $sParam;
	$sErr		= '';
	$sXML = HTTPGetXMLRaw($sURL, $httpCode, $sErr);
	if ( strIsEmpty($sErr) )
	{
		if ( mb_substr($sXML, 0, 6) === '<html>' )
		{
			$sErrorMsg = g_csHTTPNewLine . _glt('server is not configured to support OSLC');
		}
		else
		{
			$sErrorMsg	= '';
		}
	}
	else
	{
		$sErrorMsg = g_csHTTPNewLine . $sErr;
	}
?>