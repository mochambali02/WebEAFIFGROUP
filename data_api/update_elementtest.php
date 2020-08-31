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
	$sURL 		= $sURL . 'pu/test/';
	$sXML =
		  '<?xml version="1.0" encoding="utf-8"?>'
		. '<rdf:RDF xmlns:oslc_am="http://open-services.net/ns/am#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/"  xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:ss="http://www.sparxsystems.com.au/oslc_am#">'
		. '<ss:test>'
		. '<ss:key>'
		. '  <rdf:Description>'
		. '		<ss:resourceidentifier>' .  $sKey1 . '</ss:resourceidentifier>'
		. '		<dcterms:title>' . EncloseInCDATA($sKey2) . '</dcterms:title>'
		. '		<ss:classtype>' . $sKey3 . '</ss:classtype>'
		. '  </rdf:Description>'
		. '</ss:key>'
		. '<dcterms:type>' . $sType . '</dcterms:type>'
		. '<ss:status>' . $sStatus . '</ss:status>'
		. '<ss:lastrun>' . $sLastRun . '</ss:lastrun>'
		. '<ss:runby><foaf:Person><foaf:name>' . EncloseInCDATA($sRunBy) . '</foaf:name></foaf:Person></ss:runby>'
		. '<ss:checkedby><foaf:Person><foaf:name>' . EncloseInCDATA($sCheckedBy) . '</foaf:name></foaf:Person></ss:checkedby>';
	if ( $sKey2 != $sName )
		$sXML .= '<dcterms:title>' . EncloseInCDATA($sName) . '</dcterms:title>';
	if ( $sKey3 != $sClassType )
		$sXML .= '<ss:classtype>' . $sClassType . '</ss:classtype>';
	$sXML .= '<dcterms:description>' . EncloseInCDATA($sNotes) . '</dcterms:description>';
	$sXML .= '<ss:input>' . EncloseInCDATA($sInput) . '</ss:input>';
	$sXML .= '<ss:acceptancecriteria>' . EncloseInCDATA($sAcceptance) . '</ss:acceptancecriteria>';
	$sXML .= '<ss:results>' . EncloseInCDATA($sResults) . '</ss:results>';
	if ( !strIsEmpty($sLoginGUID) )
		$sXML .= '<ss:useridentifier>' . $sLoginGUID . '</ss:useridentifier>';
	$sXML .= '</ss:test>'
		  .  '</rdf:RDF>';
	$xmlRespDoc = HTTPPostXML($sURL, $sXML);
?>