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
	$sXML  = '<?xml version="1.0" encoding="utf-8"?>';
	$sXML .= '<rdf:RDF xmlns:oslc_am="http://open-services.net/ns/am#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/"  xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:ss="http://www.sparxsystems.com.au/oslc_am#">';
	$sXML .= '<oslc_am:Resource>';
	$sXML .= '<dcterms:title>' . EncloseInCDATA($sName) . '</dcterms:title>';
	if ( $sObjectType==='modelroot' )
	{
		$sXML .= '<dcterms:type>ModelRoot</dcterms:type>';
		$sXML .= '<ss:resourcetype>Package</ss:resourcetype>';
	}
	else
	{
		$sXML .= '<dcterms:type>Package</dcterms:type>';
		$sXML .= '<ss:resourcetype>Package</ss:resourcetype>';
		$sXML .= '<ss:iconidentifier>' . $sIconStyle . '</ss:iconidentifier>';
		if ( !strIsEmpty($sParentGUID) )
		{
			$sXML .= '<ss:parentresourceidentifier>' . $sParentGUID . '</ss:parentresourceidentifier>';
		}
	}
	if ( !strIsEmpty($sAuthor) )
	{
		$sXML .= '<dcterms:creator><foaf:Person><foaf:name>' . $sAuthor . '</foaf:name></foaf:Person></dcterms:creator>';
	}
	if ( !strIsEmpty($sLoginGUID) )
	{
		$sXML .= '<ss:useridentifier>' . $sLoginGUID . '</ss:useridentifier>';
	}
	if ( !strIsEmpty($sNotes) )
		$sXML .= '<dcterms:description>' . EncloseInCDATA($sNotes) . '</dcterms:description>';
	$sXML .= '</oslc_am:Resource>'
		  .  '</rdf:RDF>';
	$xmlRespDoc = HTTPPostXML($sURL, $sXML);
?>