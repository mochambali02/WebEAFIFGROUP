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
	$sURL 	= $sURL . 'cf/resource/';
	$sXML =
		  '<?xml version="1.0" encoding="utf-8"?>'
		. '<rdf:RDF xmlns:oslc_am="http://open-services.net/ns/am#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/"  xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:ss="http://www.sparxsystems.com.au/oslc_am#">'
		. '<oslc_am:Resource>'
		. '<dcterms:title>' . EncloseInCDATA($sName) . '</dcterms:title>';
	if ( $sElementType !== 'Diagram')
		$sXML .= '<dcterms:type>' . $sElementType . '</dcterms:type>';
	else
	{
		$sXML .= '<dcterms:type>' . $sDiagramType . '</dcterms:type>';
		$sXML .= '<ss:resourcetype>Diagram</ss:resourcetype>';
	}
	if ( !strIsEmpty($sParentGUID) )
		$sXML .= '<ss:parentresourceidentifier>' . $sParentGUID . '</ss:parentresourceidentifier>';
	if ( !strIsEmpty($sKeywords) )
		$sXML .= '<dcterms:subject>' . $sKeywords . '</dcterms:subject>';
	if ( !strIsEmpty($sAuthor) )
	{
		$sXML .=
			  '<dcterms:creator>'
			. '<foaf:Person>'
			. '<foaf:name>' . $sAuthor . '</foaf:name>'
			. '</foaf:Person>'
			. '</dcterms:creator>';
	}
	if ( !strIsEmpty($sLoginGUID) )
	{
		$sXML .= '<ss:useridentifier>' . $sLoginGUID . '</ss:useridentifier>';
	}
	if ( !strIsEmpty($sNotes) )
		$sXML .= '<dcterms:description>' . EncloseInCDATA($sNotes) . '</dcterms:description>';
	if ( !strIsEmpty($sAlias) )
		$sXML .= '<ss:alias>' . EncloseInCDATA($sAlias) . '</ss:alias>';
	if ( !strIsEmpty($sStatus) )
		$sXML .= '<ss:status>' . $sStatus . '</ss:status>';
	if ( !strIsEmpty($sComplexity) )
		$sXML .= '<ss:complexity>' . $sComplexity . '</ss:complexity>';
	if ( !strIsEmpty($sStereotype) )
		$sXML .= '<ss:stereotype><ss:stereotypename><ss:name>' . $sStereotype . '</ss:name></ss:stereotypename></ss:stereotype>';
	if ( !strIsEmpty($sPhase) )
		$sXML .= '<ss:phase>' . $sPhase . '</ss:phase>';
	if ( !strIsEmpty($sVersion) )
		$sXML .= '<ss:version>' . $sVersion . '</ss:version>';
	$sXML .= '</oslc_am:Resource>'
		  .  '</rdf:RDF>';
	$xmlRespDoc = HTTPPostXML($sURL, $sXML);
?>