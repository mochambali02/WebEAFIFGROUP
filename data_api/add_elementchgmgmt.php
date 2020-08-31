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
	if ($sFeatureType !== 'risk')
	{
		$sURL 	= $sURL . 'cf/maintenanceitem/';
		$sDate1Desc 	= '';
		$sDate2Desc 	= '';
		$sPerson1Desc 	= '';
		$sPerson2Desc 	= '';
		GetChgMgmtFieldNames($sFeatureType, 2, $sDate1Desc, $sDate2Desc, $sPerson1Desc, $sPerson2Desc);
		$sXML =
			  '<?xml version="1.0" encoding="utf-8"?>'
			. '<rdf:RDF xmlns:oslc_am="http://open-services.net/ns/am#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/"  xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:ss="http://www.sparxsystems.com.au/oslc_am#">'
			. '<ss:' . $sFeatureType . '>'
			. '<dcterms:title>' . htmlspecialchars($sName) . '</dcterms:title>'
			. '<ss:status>' . $sStatus . '</ss:status>'
			. '<ss:priority>' . $sPriority . '</ss:priority>'
			. '<ss:resourceidentifier>' . $sGUID . '</ss:resourceidentifier>';
		if ( !strIsEmpty($sAuthor1) )
			$sXML .= '<ss:' . $sPerson1Desc . '><foaf:Person><foaf:name>' . htmlspecialchars($sAuthor1) . '</foaf:name></foaf:Person></ss:' . $sPerson1Desc . '>';
		if ( !strIsEmpty($sDate1) )
			$sXML .= '<ss:' . $sDate1Desc . '>' . $sDate1 . '</ss:' . $sDate1Desc . '>';
		if ( !strIsEmpty($sAuthor2) )
			$sXML .= '<ss:' . $sPerson2Desc . '><foaf:Person><foaf:name>' . htmlspecialchars($sAuthor2) . '</foaf:name></foaf:Person></ss:' . $sPerson2Desc . '>';
		if ( !strIsEmpty($sDate2) )
			$sXML .= '<ss:' . $sDate2Desc . '>' . $sDate2 . '</ss:' . $sDate2Desc . '>';
		if ( !strIsEmpty($sVersion) )
			$sXML .= '<ss:version>' . $sVersion . '</ss:version>';
		if ( !strIsEmpty($sNotes) )
			$sXML .= '<dcterms:description>' . EncloseInCDATA($sNotes) . '</dcterms:description>';
		if ( !strIsEmpty($sHistory) )
			$sXML .= '<ss:history>' . EncloseInCDATA($sHistory) . '</ss:history>';
		if ( !strIsEmpty($sLoginGUID) )
			$sXML .= '<ss:useridentifier>' . $sLoginGUID . '</ss:useridentifier>';
		$sXML .= '</ss:' . $sFeatureType . '>'
			  .  '</rdf:RDF>';
	}
	else
	{
		$sURL 	= $sURL . 'cf/projectmanagementitem/';
		$sXML =
			  '<?xml version="1.0" encoding="utf-8"?>'
			. '<rdf:RDF xmlns:oslc_am="http://open-services.net/ns/am#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/"  xmlns:ss="http://www.sparxsystems.com.au/oslc_am#">'
			. '<ss:' . $sFeatureType . '>'
			. '<dcterms:title>' . htmlspecialchars($sName) . '</dcterms:title>'
			. '<ss:resourceidentifier>' . $sGUID . '</ss:resourceidentifier>';
		if ( !strIsEmpty($sType) )
			$sXML .= '<dcterms:type>' . $sType . '</dcterms:type>';
		if ( !strIsEmpty($sWeight) )
			$sXML .= '<ss:weight>' . $sWeight . '</ss:weight>';
		if ( !strIsEmpty($sNotes) )
			$sXML .= '<dcterms:description>' . EncloseInCDATA($sNotes) . '</dcterms:description>';
		if ( !strIsEmpty($sLoginGUID) )
			$sXML .= '<ss:useridentifier>' . $sLoginGUID . '</ss:useridentifier>';
		$sXML .= '</ss:' . $sFeatureType . '>'
			  .  '</rdf:RDF>';
	}
	$xmlRespDoc = HTTPPostXML($sURL, $sXML);
?>