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
$sURL 	= $sURL . 'modelmail/createmail/';
$sLoginGUID	= SafeGetInternalArrayParameter($_SESSION, 'login_guid');
$sRecipients	= SafeGetInternalArrayParameter($_POST, 'recipient');
$sSubject	= SafeGetInternalArrayParameter($_POST, 'subject');
$sMessage	= SafeGetInternalArrayParameter($_POST, 'message');
$sFlag	= SafeGetInternalArrayParameter($_POST, 'flag');
$aRecipients = explode(',', $sRecipients);
$aRecipientsList = [];
foreach ($aRecipients as &$sRecipient) {
	$sRecipient = ltrim($sRecipient);
	$sRecipient = rtrim($sRecipient);
	if (!strIsEmpty($sRecipient)) {
		$aRecipient	= explode('<', $sRecipient);
		if ((count($aRecipient) === 2) &&
			(substr($aRecipient[1], -1) === '>')
		) {
			$aRecipientsList[] = ['name' => $aRecipient[0], 'nick' => '<' . $aRecipient[1]];
		} else {
			$aRecipientsList[] = ['name' => $aRecipient[0], 'nick' => ''];
		}
	}
}
$sMessage	= ConvertHTMLToEANote($sMessage);
$sXML  = '<?xml version="1.0" encoding="utf-8"?>';
$sXML .= '<rdf:RDF xmlns:oslc_am="http://open-services.net/ns/am#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/"  xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:ss="http://www.sparxsystems.com.au/oslc_am#">';
$sXML .= '<ss:modelmail>';
foreach ($aRecipientsList as $aRec) {
	$sXML .= '<ss:recipient>';
	$sXML .= '<foaf:Person>';
	$sXML .= '<foaf:name>';
	$sXML .=  _x($aRec['name']);
	$sXML .= '</foaf:name>';
	if (!strIsEmpty($aRec['nick'])) {
		$sXML .= '<foaf:nick>';
		$sXML .=  _x($aRec['nick']);
		$sXML .= '</foaf:nick>';
	}
	$sXML .= '</foaf:Person>';
	$sXML .= '</ss:recipient>';
}
$sXML .= '<ss:subject>' .  _x($sSubject) . '</ss:subject>';
if (!strIsEmpty($sFlag) && ($sFlag !== 'None')) {
	$sXML .= '<ss:flag>' .  _x($sFlag) . '</ss:flag>';
}
$sXML .= '<ss:message>' . _x($sMessage) . '</ss:message>';
if (!strIsEmpty($sLoginGUID)) {
	$sXML .= '<ss:useridentifier>' . _x($sLoginGUID) . '</ss:useridentifier>';
}
$sXML .= '</ss:modelmail>';
$sXML .= '</rdf:RDF>';
$xmlRespDoc = HTTPPostXML($sURL, $sXML);
