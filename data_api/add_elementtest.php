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
	BuildOSLCConnectionString();
	$sURL		= $g_sOSLCString;
	$sXML 		= '';
	$sURL 	= $sURL . 'cf/test/';
	$sXML =
		  '<?xml version="1.0" encoding="utf-8"?>'
		. '<rdf:RDF xmlns:oslc_am="http://open-services.net/ns/am#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/"  xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:ss="http://www.sparxsystems.com.au/oslc_am#">'
		. '<ss:test>'
		. '<dcterms:title>' . EncloseInCDATA($sName) . '</dcterms:title>'
		. '<dcterms:type>' . htmlspecialchars($sType) . '</dcterms:type>'
		. '<ss:classtype>' . htmlspecialchars($sClassType) . '</ss:classtype>'
		. '<ss:status>' . htmlspecialchars($sStatus) . '</ss:status>'
		. '<ss:resourceidentifier>' . $sGUID . '</ss:resourceidentifier>';
	if ( !strIsEmpty($sLastRun) )
		$sXML .= '<ss:lastrun>' . $sLastRun . '</ss:lastrun>';
	if ( !strIsEmpty($sRunBy) )
		$sXML .= '<ss:runby><foaf:Person><foaf:name>' . EncloseInCDATA($sRunBy) . '</foaf:name></foaf:Person></ss:runby>';
	if ( !strIsEmpty($sCheckedBy) )
		$sXML .= '<ss:checkedby><foaf:Person><foaf:name>' . EncloseInCDATA($sCheckedBy) . '</foaf:name></foaf:Person></ss:checkedby>';
	if ( !strIsEmpty($sNotes) )
		$sXML .= '<dcterms:description>' . EncloseInCDATA($sNotes) . '</dcterms:description>';
	if ( !strIsEmpty($sInput) )
		$sXML .= '<ss:input>' . EncloseInCDATA($sInput) . '</ss:input>';
	if ( !strIsEmpty($sAcceptance) )
		$sXML .= '<ss:acceptancecriteria>' . EncloseInCDATA($sAcceptance) . '</ss:acceptancecriteria>';
	if ( !strIsEmpty($sResults) )
		$sXML .= '<ss:results>' . EncloseInCDATA($sResults) . '</ss:results>';
	if ( !strIsEmpty($sLoginGUID) )
		$sXML .= '<ss:useridentifier>' . $sLoginGUID . '</ss:useridentifier>';
	$sXML .= '</ss:test>'
		  .  '</rdf:RDF>';
	$xmlRespDoc = HTTPPostXML($sURL, $sXML);
?>