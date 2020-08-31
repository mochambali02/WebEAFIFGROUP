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
	SafeStartSession();
	if ($_SERVER["REQUEST_METHOD"] !== "POST" )
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
	$sGUID  		= SafeGetInternalArrayParameter($_POST, 'guid');
	$sType  		= SafeGetInternalArrayParameter($_POST, 'type');
	$sValue  		= SafeGetInternalArrayParameter($_POST, 'value');
	BuildOSLCConnectionString();
	$sURL		= $g_sOSLCString;
	$sXML 		= '';
	$sURL 		= $sURL . 'pu/discussion/';
	$sUpdate = '';
	$sLoginNode = '';
	$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	if ($sType === 'status')
	{
		$sUpdate = '<ss:status>' . $sValue . '</ss:status>';
	}
	else if ($sType === 'priority')
	{
		$sUpdate = '<ss:priority>' . $sValue . '</ss:priority>';
	}
	if ( !strIsEmpty($sLoginGUID) )
	{
		$sLoginNode = '<ss:useridentifier>' . $sLoginGUID . '</ss:useridentifier>';
	}
	$sXML  = '<?xml version="1.0" encoding="utf-8"?>';
	$sXML .= '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:ss="http://www.sparxsystems.com.au/oslc_am#">';
	$sXML .= '<ss:discussion>';
	$sXML .= '<ss:key>';
	$sXML .= '<rdf:Description>';
	$sXML .= '<dcterms:identifier>' . $sGUID . '</dcterms:identifier>';
	$sXML .= '</rdf:Description>';
	$sXML .= '</ss:key>';
	$sXML .= $sUpdate;
	$sXML .= $sLoginNode;
	$sXML .= '</ss:discussion>';
	$sXML .= '</rdf:RDF>';
	$xmlRespDoc = HTTPPostXML($sURL, $sXML);
	$sOSLCErrorMsg = BuildOSLCErrorString();
	echo $sOSLCErrorMsg;
?>