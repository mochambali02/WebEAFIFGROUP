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
	if ($_SERVER["REQUEST_METHOD"] !== "POST" )
	{
		exit();
	}
	SafeStartSession();
	BuildOSLCConnectionString();
	$sOSLC_URL = $g_sOSLCString . 'sp/';
	$xmlDoc = HTTPGetXML($sOSLC_URL);
	$aRows = array();
	if ($xmlDoc != null)
	{
		$sDatabaseManager	= '';
		$sAppAuthor			= '';
		$sAppAuthorWebsite	= '';
		$sPCSVersion		= '';
		$sVersion			= '';
		$sLicense			= '';
		$sLicenseExpiry		= '';
		$sReadOnly			= '';
		$sSecurityEnabled	= '';
		$sProtocol			= SafeGetInternalArrayParameter($_SESSION, 'protocol', '');
		$sServer			= SafeGetInternalArrayParameter($_SESSION, 'server', '');
		$sPort				= SafeGetInternalArrayParameter($_SESSION, 'port', '');
		$xnRoot = $xmlDoc->documentElement;
		$xnOSP 	= GetXMLFirstChild($xnRoot);
		if ($xnOSP != null)
		{
			foreach ($xnOSP->childNodes as $xnOSPC)
			{
				GetXMLNodeValue($xnOSPC, 'dcterms:title', $sDatabaseManager);
				if ($xnOSPC->nodeName == 'dcterms:publisher')
				{
					$xnOPub 	= GetXMLFirstChild($xnOSPC);
					if ($xnOPub != null)
					{
						foreach ($xnOPub->childNodes as $xnOPubC)
						{
							GetXMLNodeValue($xnOPubC, 'dcterms:title', $sAppAuthor);
							GetXMLNodeValue($xnOPubC, 'dcterms:identifier', $sAppAuthorWebsite);
							GetXMLNodeValue($xnOPubC, 'ss:proversion', $sPCSVersion);
							GetXMLNodeValue($xnOPubC, 'ss:version', $sVersion);
							GetXMLNodeValue($xnOPubC, 'ss:readonlyconnection', $sReadOnly);
							GetXMLNodeValue($xnOPubC, 'ss:securityenabledmodel', $sSecurityEnabled);
							GetXMLNodeValue($xnOPubC, 'ss:prolicense', $sLicense);
							GetXMLNodeValue($xnOPubC, 'ss:prolicenseexpiry', $sLicenseExpiry);
						}
					}
				}
			}
			if ( substr($sAppAuthorWebsite, 0, 7) === 'http://' )
				$sAppAuthorWebsite = substr($sAppAuthorWebsite, 7);
			$sAppAuthorWebsite = trim($sAppAuthorWebsite, "/");
			$aRow['databasemanager']	= $sDatabaseManager;
			$aRow['appauthor'] 			= $sAppAuthor;
			$aRow['appauthorwebsite']	= $sAppAuthorWebsite;
			$aRow['pcsversion']			= $sPCSVersion;
			$aRow['version'] 			= $sVersion;
			$aRow['license'] 			= $sLicense;
			$aRow['licenseexpiry'] 		= $sLicenseExpiry;
			$aRow['protocol'] 			= $sProtocol;
			$aRow['server'] 			= $sServer;
			$aRow['port']	 			= $sPort;
			$aRow['readonly'] 			= $sReadOnly;
			$aRow['securityenabled']	= $sSecurityEnabled;
		}
	}
	$sLoginUser 	= SafeGetInternalArrayParameter($_SESSION, 'login_user', '');
	$sLoginFullName = SafeGetInternalArrayParameter($_SESSION, 'login_fullname', '');
	$sReviewSession = SafeGetInternalArrayParameter($_SESSION, 'review_session');
	$sReviewSessionName = SafeGetInternalArrayParameter($_SESSION, 'review_session_name');
	$aRow['loginuser'] 					= $sLoginUser;
	$aRow['loginfullname'] 				= $sLoginFullName;
	$aRow['reviewsession'] 				= $sReviewSession;
	$aRow['reviewsessionname']			= $sReviewSessionName;
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($aRow);
?>