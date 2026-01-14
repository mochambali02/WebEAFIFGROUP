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
$sURL 	= $sURL . 'modelmail/updatemail/';
$sIdentifier = SafeGetInternalArrayParameter($_POST, 'identifier');
$sLoginGUID	= SafeGetInternalArrayParameter($_SESSION, 'login_guid');
$bRead	= SafeGetInternalArrayParameter($_POST, 'read');
$sFlag	= SafeGetInternalArrayParameter($_POST, 'flag');
define('XMLNS_DCTERMS', 'http://microsoft.com/wsdl/types/');
define('XMLNS_SS', 'http://microsoft.com/wsdl/types/');
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="' . XMLNS_DCTERMS . '" xmlns:ss="' . XMLNS_SS . '"></rdf:RDF>');
$modelmail = $xml->addChild('modelmail', null, XMLNS_SS);
$modelmail->addChild('useridentifier', _x($sLoginGUID), XMLNS_SS);
$modelmail->addChild('identifier', $sIdentifier, XMLNS_DCTERMS);
if (!strIsEmpty($bRead))
	$modelmail->addChild('read', _x($bRead), XMLNS_SS);
if (!strIsEmpty($sFlag))
	$modelmail->addChild('flag', _x($sFlag), XMLNS_SS);
$sXML = $xml->asXML();
$xmlRespDoc = HTTPPostXML($sURL, $sXML);
