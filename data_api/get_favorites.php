<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if (!isset($webea_page_parent_browser)) {
	exit();
}
require_once __DIR__ . '/../security.php';
require_once __DIR__ . '/../globals.php';
SafeStartSession();
BuildOSLCConnectionString();
$sOSLC_URL = $g_sOSLCString . 'fpstructure/';
$sParas = '';

$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
$sSortOrder  = SafeGetInternalArrayParameter($_SESSION, 'object_order');
AddURLParameter($sParas, 'orderby', $sSortOrder);
AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
$sOSLC_URL 	.= $sParas;
$xmlDoc = HTTPGetXML($sOSLC_URL);
$aData = XMLToArray($xmlDoc);
$aFavRoot = SafeGetChildArray($aData, ['rdf:RDF', 'rdf:Description']);
if ((!array_key_exists('ss:userfavorite', $aFavRoot)) &&
	(!array_key_exists('ss:groupfavorite', $aFavRoot))
) {
	$bHasFavorites = false;
} else {
	$bHasFavorites = true;
}
