<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	if  ( !isset($webea_page_parent_getprops) )
	{
		exit();
	}
	ob_start();
	$sRootPath = dirname(dirname(__FILE__));
	require_once $sRootPath . '/globals.php';
	SafeStartSession();
	$sName			= '';
	$sImageType		= '';
	$sImageBin		= '';
	BuildOSLCConnectionString();
	if ( mb_substr($sImageAssetGUID,0,4) === 'ia_{' )
	{
		$sOSLC_URL = $g_sOSLCString . 'iaimage/';
	}
	else
	{
		$sOSLC_URL = $g_sOSLCString . 'imimage/';
	}
	if ( !strIsEmpty($sImageAssetGUID) )
	{
		$sOSLC_URL = $sOSLC_URL . $sImageAssetGUID . '/';
	}
	$sParas = '';
	$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
	$sOSLC_URL 	.= $sParas;
	$xmlDoc = HTTPGetXML($sOSLC_URL);
	if ($xmlDoc != null)
	{
		$xnRoot = $xmlDoc->documentElement;
		if ($xnRoot !== null)
		{
			$xnObj 	= $xnRoot->firstChild;
			if ($xnObj != null)
			{
				foreach ($xnObj->childNodes as $xnObjProp)
				{
					GetXMLNodeValue($xnObjProp, 'dcterms:title', $sName);
					GetXMLNodeValue($xnObjProp, 'ss:type', $sImageType);
					if ($xnObjProp->nodeName == 'ss:image')
					{
						$sImageBin = $xnObjProp->nodeValue;
					}
				}
			}
		}
	}
?>