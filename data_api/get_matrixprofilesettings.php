<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST"  &&   !isset($webea_page_parent_mainview)) {
	exit();
}
$sRootPath = dirname(dirname(__FILE__));
require_once $sRootPath . '/globals.php';
$bIsPost = 'false';
if (isset($_POST['profile'])) {
	$bIsPost = 'true';
	$sProfileName = $_POST['profile'];
} else {
	$sProfileName = $sObjectHyper;
}
BuildOSLCConnectionString();
$sOSLC_URL 	= $g_sOSLCString . "rm/settings/";
$sParas = '';
$sLoginGUID = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
AddURLParameter($sParas, 'profile', $sProfileName);
AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
$sOSLC_URL 	.= $sParas;
$aSettings = array();
$xmlDoc = null;
if (!strIsEmpty($sOSLC_URL)) {
	$xmlDoc = HTTPGetXML($sOSLC_URL);
}
if ($xmlDoc != null) {
	$xnRoot = $xmlDoc->documentElement;
	$xnDesc = $xnRoot->firstChild;
	if (GetOSLCError($xnDesc)) {
		if ($xnDesc != null && $xnDesc->childNodes != null) {
			$sSourcePackageName 	= '';
			$sSourcePackageGUID 	= '';
			$sTargetPackageName 	= '';
			$sTargetPackageGUID 	= '';
			$sSourceType			= '';
			$sTargetType			= '';
			$sLinkType				= '';
			$sLinkDirection			= '';
			$aSettings = array();
			foreach ($xnDesc->childNodes as $xnDescChild) {
				if ($xnDescChild->nodeName === 'ss:sourcepackage') {
					$xnDesc = $xnDescChild->firstChild;
					foreach ($xnDesc->childNodes as $xnDescChild) {
						GetXMLNodeValue($xnDescChild, 'dcterms:title', $sSourcePackageName);
						GetXMLNodeValue($xnDescChild, 'dcterms:identifier', $sSourcePackageGUID);
					}
				} elseif ($xnDescChild->nodeName === 'ss:targetpackage') {
					$xnDesc = $xnDescChild->firstChild;
					foreach ($xnDesc->childNodes as $xnDescChild) {
						GetXMLNodeValue($xnDescChild, 'dcterms:title', $sTargetPackageName);
						GetXMLNodeValue($xnDescChild, 'dcterms:identifier', $sTargetPackageGUID);
					}
				} elseif ($xnDescChild->nodeName === 'ss:sourcecontenttype') {
					$xnDesc = $xnDescChild->firstChild;
					foreach ($xnDesc->childNodes as $xnDescChild) {
						GetXMLNodeValue($xnDescChild, 'dcterms:title', $sSourceType);
					}
				} elseif ($xnDescChild->nodeName === 'ss:targetcontenttype') {
					$xnDesc = $xnDescChild->firstChild;
					foreach ($xnDesc->childNodes as $xnDescChild) {
						GetXMLNodeValue($xnDescChild, 'dcterms:title', $sTargetType);
					}
				} elseif ($xnDescChild->nodeName === 'ss:linktype') {
					$xnDesc = $xnDescChild->firstChild;
					foreach ($xnDesc->childNodes as $xnDescChild) {
						GetXMLNodeValue($xnDescChild, 'dcterms:title', $sLinkType);
					}
				} elseif ($xnDescChild->nodeName === 'ss:linkdirectiontype') {
					$xnDesc = $xnDescChild->firstChild;
					foreach ($xnDesc->childNodes as $xnDescChild) {
						GetXMLNodeValue($xnDescChild, 'dcterms:title', $sLinkDirection);
					}
				} elseif ($xnDescChild->nodeName === 'ss:options') {
					$aOptions = array();
					$xnDesc = $xnDescChild->firstChild;
					foreach ($xnDesc->childNodes as $xnMem) {
						if ($xnMem->nodeName === 'rdfs:member') {
							$xnMemDesc = $xnMem->firstChild;
							if ($xnMemDesc->childNodes != null) {
								foreach ($xnMemDesc->childNodes as $xnMemDescChild) {
									GetXMLNodeValue($xnMemDescChild, 'dcterms:title', $aOptions[]);
								}
							}
						}
					}
				}
			}
			$aSettings['sourcepackagename'] = $sSourcePackageName;
			$aSettings['sourcepackageguid'] = $sSourcePackageGUID;
			$aSettings['targetpackagename'] = $sTargetPackageName;
			$aSettings['targetpackageguid'] = $sTargetPackageGUID;
			$aSettings['sourcetype'] 		= $sSourceType;
			$aSettings['targettype'] 		= $sTargetType;
			$aSettings['linktype'] 			= $sLinkType;
			$aSettings['linkdirection'] 	= $sLinkDirection;
			$aSettings['options'] 			= $aOptions;
			$aSettings['lastoslcerror'] 	= '';
			if ($bIsPost === 'true') {
				header('Content-Type: application/json; charset=utf-8');
				echo json_encode($aSettings);
			}
		}
	} else {
		$aSettings['sourcepackagename'] = '';
		$aSettings['sourcepackageguid'] = '';
		$aSettings['targetpackagename'] = '';
		$aSettings['targetpackageguid'] = '';
		$aSettings['sourcetype'] 		= '';
		$aSettings['targettype'] 		= '';
		$aSettings['linktype'] 			= '';
		$aSettings['linkdirection'] 	= '';
		$aSettings['options'] 			= '';
		$aSettings['lastoslcerror'] 	= BuildOSLCErrorString();
		if ($bIsPost === 'true') {
			ob_clean();
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode($aSettings);
		}
	}
}
