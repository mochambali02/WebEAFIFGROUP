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
	$sURL 		= $sURL . 'pu/resourceallocation/';
	$sXML =
		  '<?xml version="1.0" encoding="utf-8"?>'
		. '<rdf:RDF xmlns:oslc_am="http://open-services.net/ns/am#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/"  xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:ss="http://www.sparxsystems.com.au/oslc_am#">'
		. '<ss:resourceallocation>'
		. '<ss:key>'
		. '  <rdf:Description>'
		. '	   <ss:resourceidentifier>' . $sKey1 . '</ss:resourceidentifier>'
		. '	   <ss:resourcename><foaf:Person><foaf:name>' . EncloseInCDATA($sKey2) . '</foaf:name></foaf:Person></ss:resourcename>'
		. '	   <ss:role>' . EncloseInCDATA($sKey3) . '</ss:role>'
		. '  </rdf:Description>'
		. '</ss:key>'
		. '<ss:startdate>' . $sStartDate . '</ss:startdate>'
		. '<ss:enddate>' . $sEndDate . '</ss:enddate>';
	if ( $sKey2 != $sResource )
		$sXML .= '<ss:resourcename><foaf:Person><foaf:name>' . EncloseInCDATA($sResource) . '</foaf:name></foaf:Person></ss:resourcename>';
	if ( $sKey3 != $sRole )
		$sXML .= '<ss:role>' . EncloseInCDATA($sRole) . '</ss:role>';
	$sXML .= '<ss:percentagecomplete>' . $sPercentComp . '</ss:percentagecomplete>';
	$sXML .= '<ss:expectedtime>' . $sExpectedTime . '</ss:expectedtime>';
	$sXML .= '<ss:allocatedtime>' . $sAllocTime . '</ss:allocatedtime>';
	$sXML .= '<ss:expendedtime>' . $sExpendedTime . '</ss:expendedtime>';
	$sXML .= '<dcterms:description>' . EncloseInCDATA($sNotes) . '</dcterms:description>';
	$sXML .= '<ss:history>' . EncloseInCDATA($sHistory) . '</ss:history>';
	if ( !strIsEmpty($sLoginGUID) )
		$sXML .= '<ss:useridentifier>' . $sLoginGUID . '</ss:useridentifier>';
	$sXML .= '</ss:resourceallocation>'
		  .  '</rdf:RDF>';
	$xmlRespDoc = HTTPPostXML($sURL, $sXML);
?>