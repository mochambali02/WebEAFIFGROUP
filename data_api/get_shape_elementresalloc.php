<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	if  ( !isset($webea_page_parent_mainview) )
	{
		exit();
	}
	$sRootPath = dirname(dirname(__FILE__));
	require_once $sRootPath . '/globals.php';
	SafeStartSession();
	BuildOSLCConnectionString();
	$sOSLC_URL 	= $g_sOSLCString . "rs/cfresourceallocation/";
	$aAuthors	= array();
	$aRoles		= array();
	if ( !strIsEmpty($sOSLC_URL) )
	{
		$sParas = '';
		$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
		AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
		$sOSLC_URL 	.= $sParas;
		$xmlDoc = HTTPGetXML($sOSLC_URL);
		if ($xmlDoc != null)
		{
			$xnRoot = $xmlDoc->documentElement;
			$xnShape = GetXMLFirstChild($xnRoot);
			if ($xnShape != null && $xnShape->childNodes != null)
			{
				if ( GetOSLCError($xnShape) )
				{
					foreach ($xnShape->childNodes as $xnOProps)
					{
						if ($xnOProps->nodeName === 'oslc:property')
						{
							$xnOProp2 = GetXMLFirstChild($xnOProps);
							if ($xnOProp2 != null && $xnOProp2->childNodes != null)
							{
								$sFieldName = '';
								$sKey 	= '';
								$sValue		= '';
								foreach ($xnOProp2->childNodes as $xn)
								{
									if ( strIsEmpty($sFieldName) )
									{
										GetXMLNodeValue($xn, 'oslc:name', $sFieldName);
									}
									else
									{
										$sKey 	= '';
										$sValue = '';
										if ($xn->nodeName === 'oslc:allowedValue')
										{
											$sValue = $xn->nodeValue;
											$sKey	= str_replace(' ', '', $sValue);
											$sKey	= strtolower($sKey);
										}
										if ( !strIsEmpty($sValue) )
										{
											if ( $sFieldName === 'resourcename')
											{
												$aAuthors[$sKey] = $sValue;
											}
											elseif ( $sFieldName === 'role')
											{
												$aRoles[$sKey] = $sValue;
											}
										}
									}
								}
							}
						}
					}
				}
				else
				{
				}
			}
		}
		else
		{
			return null;
		}
	}
?>