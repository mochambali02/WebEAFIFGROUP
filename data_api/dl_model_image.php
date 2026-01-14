<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
ob_start();
$sRootPath = dirname(dirname(__FILE__));
require_once $sRootPath . '/globals.php';
SafeStartSession();
$sObjectGUID = SafeGetInternalArrayParameter($_GET, 'objectguid');
$sName			= '';
$sImageType		= '';
$sImageBin		= '';
if (strIsEmpty($sObjectGUID)) {
	exit();
}
BuildOSLCConnectionString();
if (mb_substr($sObjectGUID, 0, 4) === 'ia_{') {
	$sOSLC_URL = $g_sOSLCString . 'iaimage/';
} else {
	$sOSLC_URL = $g_sOSLCString . 'imimage/';
}
if (!strIsEmpty($sObjectGUID)) {
	$sOSLC_URL = $sOSLC_URL . $sObjectGUID . '/';
}
$sParas = '';
$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
$sOSLC_URL 	.= $sParas;
$xmlDoc = HTTPGetXML($sOSLC_URL);
if ($xmlDoc != null) {
	$xnRoot = $xmlDoc->documentElement;
	if ($xnRoot !== null) {
		$xnObj 	= $xnRoot->firstChild;
		if ($xnObj != null) {
			foreach ($xnObj->childNodes as $xnObjProp) {
				GetXMLNodeValue($xnObjProp, 'dcterms:title', $sName);
				GetXMLNodeValue($xnObjProp, 'ss:type', $sImageType);
				if ($xnObjProp->nodeName == 'ss:image') {
					$sImageBin = $xnObjProp->nodeValue;
				}
			}
		}
	}
}
$sMime = '';
$sExt = '';
$bOverrideExt = false;
if (mb_substr($sObjectGUID, 0, 4) === 'ia_{') {
	$sExt = strtolower(trim($sImageType, '.'));
} else {
	$sImageType = strtolower($sImageType);
	if ($sImageType === 'enhmetafile')
		$sExt = 'emf';
	elseif ($sImageType === 'metafile')
		$sExt = 'wmf';
	else {
		$sExt = 'png';
		$bOverrideExt = false;
	}
}
$sNewName = $sName;
$sOrigExt = strtolower(substr(strrchr($sName, '.'), 1));
if (strIsEmpty($sOrigExt) || $sOrigExt !== $sExt) {
	$iPos = strripos($sName, '.');
	if ($iPos > -1) {
		$sNewName = substr($sName, 0, $iPos) . '.' . $sExt;
	} else {
		$sNewName = $sName . '.' . $sExt;
	}
}
switch ($sExt) {
	case 'bmp':
		$sMime = 'image/bmp';
		break;
	case 'emf':
		$sMime = 'application/x-emf';
		break;
	case 'jpeg':
	case 'jpg':
		$sMime = 'image/jpeg';
		break;
	case 'gif':
		$sMime = 'image/gif';
		break;
	case 'png':
		$sMime = 'image/png';
		break;
	case 'wmf':
		$sMime = 'application/x-msmetafile';
		break;
	default:
		$sMime = 'application/octet-stream';
}
ob_end_clean();
header('Content-Type: ' . $sMime . '; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $sNewName . '"');
echo base64_decode($sImageBin);
