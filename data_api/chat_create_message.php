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
require_once
 __DIR__ . '/../globals.php';
SafeStartSession();
AllowedMethods('POST');
CheckAuthorisation();
BuildOSLCConnectionString();
$sURL	= $g_sOSLCString;
$sXML 	= '';
$sURL 	= $sURL . 'modelchat/createmessage/';
$sLoginGUID	= SafeGetInternalArrayParameter($_SESSION, 'login_guid');
$sMessage	= SafeGetInternalArrayParameter($_POST, 'chat-message');
$sChatName	= SafeGetInternalArrayParameter($_SESSION, 'chat_name');
$sChatType	= SafeGetInternalArrayParameter($_SESSION, 'chat_type');
define('XMLNS_RDF', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
define('XMLNS_RDFs', 'http://www.w3.org/2000/01/rdf-schema#');
define('XMLNS_SS', 'http://www.sparxsystems.com.au/oslc_am#');
define('XMLNS_FOAF', 'http://xmlns.com/foaf/0.1/');
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><rdf:RDF xmlns:rdf="' . XMLNS_RDF . '" xmlns:rdfs="' . XMLNS_RDFs . '"  xmlns:foaf="' . XMLNS_FOAF . '" xmlns:ss="' . XMLNS_SS . '"></rdf:RDF>');
$chatmessage = $xml->addChild('chatmessage', null, XMLNS_SS);
$recipient = $chatmessage->addChild('recipient', null, XMLNS_SS);
$description = $recipient->addChild('Description', null, XMLNS_RDF);
$memberfullname = $description->addChild('memberfullname', null, XMLNS_SS);
$person = $memberfullname->addChild('Person', null, XMLNS_SS);
$person->addChild('name', $sChatName, XMLNS_FOAF);
$person->addChild('nick', $sChatName, XMLNS_FOAF);
$description->addChild('type', _x($sChatType), XMLNS_SS);
$chatmessage->addChild('message', _x($sMessage), XMLNS_SS);
$chatmessage->addChild('useridentifier', _x($sLoginGUID), XMLNS_SS);
$sXML = $xml->asXML();
$xmlRespDoc = HTTPPostXML($sURL, $sXML);
