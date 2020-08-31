<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	if  ( !isset($webea_page_parent_mainview) ||
		   isset($sObjectGUID) === false ||
		   isset($_SESSION['authorized']) === false)
	{
		exit();
	}
	$sRootPath = dirname(dirname(__FILE__));
	require_once $sRootPath . '/globals.php';
	BuildOSLCConnectionString();
	$sOSLC_URL 	= $g_sOSLCString . "rm/profiles/";
	$sParas = '';
	$sLoginGUID = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
	$sOSLC_URL 	.= $sParas;
	$xmlDoc = null;
	if ( !strIsEmpty($sOSLC_URL) )
	{
		$xmlDoc = HTTPGetXML($sOSLC_URL);
	}
	$aMatrixProfiles = array();
	if ($xmlDoc != null)
	{
		$xnRoot = $xmlDoc->documentElement;
		$xnDesc = $xnRoot->firstChild;
		if ($xnDesc != null && $xnDesc->childNodes != null)
		{
			$sProfileName	= '';
			$bIsSupported	= '';
			foreach ($xnDesc->childNodes as $xnMem)
			{
				if ($xnMem->nodeName === 'rdfs:member')
				{
					$xnMemDesc = $xnMem->firstChild;
					if ($xnMemDesc->childNodes != null)
					{
						foreach ($xnMemDesc->childNodes as $xnMemDescChild)
						{
							GetXMLNodeValue($xnMemDescChild, 'dcterms:title', $sProfileName);
							GetXMLNodeValue($xnMemDescChild, 'ss:supported', $bIsSupported);
						}
						if($bIsSupported === 'true')
						{
							$aMatrixProfiles[] = $sProfileName;
						}
					}
				}
			}
		}
	}
?>