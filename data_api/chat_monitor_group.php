<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
require_once __DIR__ . '/../security.php';
require_once __DIR__ . '/../globals.php';
SafeStartSession();
AllowedMethods('POST');
CheckAuthorisation();
BuildOSLCConnectionString();
$sURL	= $g_sOSLCString;
$sXML 	= '';
$sURL 	= $sURL . 'modelchat/monitor/';
$sLoginGUID	= SafeGetInternalArrayParameter($_SESSION, 'login_guid');
$sChatName	= SafeGetInternalArrayParameter($_SESSION, 'chat_name');
$sChatType	= SafeGetInternalArrayParameter($_SESSION, 'chat_type');
$sGroupName	= SafeGetInternalArrayParameter($_POST, 'group_name');
$sGroupMonitor	= SafeGetInternalArrayParameter($_POST, 'group_monitor');
$sChatMonitor = 'true';
if (!strIsEmpty($sGroupName)) {
	$sChatName = $sGroupName;
	$sChatType = 'Group';
	if ($sGroupMonitor === 'true')
		$sChatMonitor = 'false';
}
define('XMLNS_RDF', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
define('XMLNS_RDFs', 'http://www.w3.org/2000/01/rdf-schema#');
define('XMLNS_SS', 'http://www.sparxsystems.com.au/oslc_am#');
define('XMLNS_FOAF', 'http://xmlns.com/foaf/0.1/');
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><rdf:RDF xmlns:rdf="' . XMLNS_RDF . '" xmlns:rdfs="' . XMLNS_RDFs . '"  xmlns:foaf="' . XMLNS_FOAF . '" xmlns:ss="' . XMLNS_SS . '"></rdf:RDF>');
$chatmember = $xml->addChild('chatmember', null, XMLNS_SS);
$memberfullname = $chatmember->addChild('memberfullname', null, XMLNS_SS);
$person = $memberfullname->addChild('Person', null, XMLNS_FOAF);
$person->addChild('name', $sChatName, XMLNS_FOAF);
$person->addChild('nick', $sChatName, XMLNS_FOAF);
$chatmember->addChild('type', _x($sChatType), XMLNS_SS);
$chatmember->addChild('monitor', $sChatMonitor, XMLNS_SS);
$chatmember->addChild('useridentifier', _x($sLoginGUID), XMLNS_SS);
$sXML = $xml->asXML();
$xmlRespDoc = HTTPPostXML($sURL, $sXML);
