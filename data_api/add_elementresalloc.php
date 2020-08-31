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
	$sURL 	= $sURL . 'cf/resourceallocation/';
	$sXML =
		  '<?xml version="1.0" encoding="utf-8"?>'
		. '<rdf:RDF xmlns:oslc_am="http://open-services.net/ns/am#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/"  xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:ss="http://www.sparxsystems.com.au/oslc_am#">'
		. '<ss:resourceallocation>'
		. '<ss:resourcename><foaf:Person><foaf:name>' . EncloseInCDATA($sResource) . '</foaf:name></foaf:Person></ss:resourcename>'
		. '<ss:role>' . EncloseInCDATA($sRole) . '</ss:role>'
		. '<ss:startdate>' . $sStartDate . '</ss:startdate>'
		. '<ss:enddate>' . $sEndDate . '</ss:enddate>'
		. '<ss:resourceidentifier>' . $sGUID . '</ss:resourceidentifier>';
	if ( !strIsEmpty($sPercentComp) && $sPercentComp !== '0' )
		$sXML .= '<ss:percentagecomplete>' . $sPercentComp . '</ss:percentagecomplete>';
	if ( !strIsEmpty($sExpectedTime) && $sExpectedTime !== '0' )
		$sXML .= '<ss:expectedtime>' . $sExpectedTime . '</ss:expectedtime>';
	if ( !strIsEmpty($sAllocTime) && $sAllocTime !== '0' )
		$sXML .= '<ss:allocatedtime>' . $sAllocTime . '</ss:allocatedtime>';
	if ( !strIsEmpty($sExpendedTime) && $sExpendedTime !== '0' )
		$sXML .= '<ss:expendedtime>' . $sExpendedTime . '</ss:expendedtime>';
	if ( !strIsEmpty($sNotes) )
		$sXML .= '<dcterms:description>' . EncloseInCDATA($sNotes) . '</dcterms:description>';
	if ( !strIsEmpty($sHistory) )
		$sXML .= '<ss:history>' . EncloseInCDATA($sHistory) . '</ss:history>';
	if ( !strIsEmpty($sLoginGUID) )
		$sXML .= '<ss:useridentifier>' . $sLoginGUID . '</ss:useridentifier>';
	$sXML .= '</ss:resourceallocation>'
		  .  '</rdf:RDF>';
	$xmlRespDoc = HTTPPostXML($sURL, $sXML);
?>