<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if (!isset($webea_page_parent_mainview)) {
	exit();
}
$sRootPath = dirname(dirname(__FILE__));
require_once $sRootPath . '/globals.php';
SafeStartSession();
BuildOSLCConnectionString();
$sOSLC_URL 	= $g_sOSLCString . 'resourcetypes/';
if (!strIsEmpty($sOSLC_URL)) {
	$sParas = '';
	$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
	$sOSLC_URL 	.= $sParas;
	$xmlDoc = HTTPGetXML($sOSLC_URL);
	if ($xmlDoc != null) {
		$xnRoot = $xmlDoc->documentElement;
		$xnDesc = GetXMLFirstChild($xnRoot);
		if ($xnDesc != null && $xnDesc->childNodes != null) {
			if (GetOSLCError($xnDesc)) {
				foreach ($xnDesc->childNodes as $xnMemTechs) {
					$xnTech = GetXMLFirstChild($xnMemTechs);
					if ($xnTech != null && $xnTech->childNodes != null) {
						if ($xnTech->nodeName === 'ss:technology') {
							$sTechnology 	= '';
							$aDiagram 		= array();
							foreach ($xnTech->childNodes as $xnTechC) {
								if ($xnTechC != null) {
									if ($xnTechC->nodeName === 'dcterms:title')
										$sTechnology = $xnTechC->nodeValue;
									if ($xnTechC->nodeName === 'ss:diagramtype') {
										if ($xnTechC != null && $xnTechC->childNodes != null) {
											$xnTechD = GetXMLFirstChild($xnTechC);
											if ($xnTechC != null && $xnTechC->childNodes != null) {
												foreach ($xnTechD->childNodes as $xnTechM) {
													$sDTName = '';
													$sDTFQName = '';
													$sDTAlias = '';
													$xnTechMD = GetXMLFirstChild($xnTechM);
													if ($xnTechMD != null && $xnTechMD->childNodes != null) {
														foreach ($xnTechMD->childNodes as $xn) {
															GetXMLNodeValue($xn, 'dcterms:title', 	$sDTName);
															GetXMLNodeValue($xn, 'ss:fqname', 		$sDTFQName);
															GetXMLNodeValue($xn, 'ss:alias',	 	$sDTAlias);
														}
													}
													$aRow			= array();
													$aRow['tech']	= '';
													$aRow['name']	= $sDTName;
													$aRow['fqname']	= $sDTFQName;
													$aRow['alias']	= $sDTAlias;
													$aDiagramTypes[] = $aRow;
												}
											}
										}
									}
								}
							}
							if ($sTechnology !== '') {
								$aDiagramTechs[] = $sTechnology;
								$iCnt = count($aDiagramTypes);
								for ($i = 0; $i < $iCnt; $i++) {
									if (SafeGetArrayItem2Dim($aDiagramTypes, $i, 'tech') === '') {
										$aDiagramTypes[$i]['tech'] = $sTechnology;
									}
								}
							}
						}
					}
				}
			} else {
			}
		}
	} else {
		return null;
	}
}
