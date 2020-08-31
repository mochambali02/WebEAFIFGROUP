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
	BuildOSLCConnectionString();
	$sURL		= $g_sOSLCString;
	$sResponse	= 'error: Not Found';
	$sRawGUID	= SafeGetInternalArrayParameter($_GET, 'rawguid');
	$sGUID		= '';
	$sResType	= '';
	$sType		= '';
	if ( !strIsEmpty($sRawGUID) )
	{
		$sURL 	.= 'unknownresource/ur_{' . $sRawGUID . '}/';
		$sParas = '';
		$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
		AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
		$sURL 	.= $sParas;
		ob_start();
		$xmlDoc = HTTPGetXML($sURL);
		ob_end_clean();
		if ($xmlDoc != null)
		{
			$xnRoot = $xmlDoc->documentElement;
			$xnFirst= GetXMLFirstChild($xnRoot);
			if ($xnFirst != null)
			{
				if ( $xnFirst->nodeName === 'oslc:Error' )
				{
					$sErrorCode = '';
					$sErrorMsg = '';
					ReadOSLCError($xnFirst, $sErrorCode, $sErrorMsg);
					if ( $sErrorCode === '403' && mb_strtolower($sErrorMsg) === 'invalid user security identifier')
					{
						$sResponse = 'error: ' . $sErrorCode . ' - ' . $sErrorMsg;
					}
					else
					{
						$sResponse = 'error: Supplied GUID could not be found.';
					}
				}
				else
				{
					foreach ($xnFirst->childNodes as $xn)
					{
						GetXMLNodeValue($xn, 'dcterms:identifier', $sGUID);
						GetXMLNodeValue($xn, 'dcterms:type', $sType);
						GetXMLNodeValue($xn, 'ss:resourcetype', $sResType);
					}
					if ( !strIsEmpty($sGUID) )
					{
						$sResponse = 'success: ' . $sGUID;
					}
				}
			}
		}
	}
	else
	{
		$sResponse = 'error: No GUID supplied.';
	}
	echo $sResponse;
?>