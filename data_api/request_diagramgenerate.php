<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	if ($_SERVER["REQUEST_METHOD"] !== "POST" )
	{
		exit();
	}
	$sRootPath = dirname(dirname(__FILE__));
	require_once $sRootPath . '/globals.php';
	SafeStartSession();
	BuildOSLCConnectionString();
	$sURL		= $g_sOSLCString;
	$sXML 		= '';
	$sResults	= 'Not Found';
	$sObjectGUID = SafeGetInternalArrayParameter($_POST, 'objectguid');
	if ( !strIsEmpty($sObjectGUID) )
	{
		$sURL 	.= 'gu/';
		$sParas = '';
		$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
		AddURLParameter($sParas, 'term', 'image');
		AddURLParameter($sParas, 'resourceidentifier', $sObjectGUID);
		AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
		$sURL 	.= $sParas;
		ob_start();
		$xmlDoc = HTTPGetXML($sURL);
		ob_end_clean();
		if ($xmlDoc != null)
		{
			$xnRoot = $xmlDoc->documentElement;
			$xnGU 	= GetXMLFirstChild($xnRoot);
			if ($xnGU != null)
			{
				if ($xnGU->nodeName ==='oslc:Error')
				{
					GetOSLCError($xnGU);
					$sResults = BuildOSLCErrorString();
				}
				else
				{
					foreach ($xnGU->childNodes as $xn)
					{
						GetXMLNodeValue($xn, 'ss:update', $sResults);
					}
					if ( $sResults === 'complete' )
					{
						$sResults = '';
					}
				}
			}
		}
	}
	else
	{
		$sResults = 'Marking the current diagram as needing regeneration failed, because no diagram ID specified.';
	}
	echo $sResults;
?>