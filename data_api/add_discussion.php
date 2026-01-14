<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	exit();
}
$sRootPath = dirname(dirname(__FILE__));
require_once $sRootPath . '/globals.php';
SafeStartSession();
$sGUID 		= SafeGetInternalArrayParameter($_POST, 'guid');
$sAuthor 	= SafeGetInternalArrayParameter($_POST, 'author');
$sComments 	= SafeGetInternalArrayParameter($_POST, 'comments');
$sLoginGUID	= SafeGetInternalArrayParameter($_POST, 'loginguid');
$sSessionReviewGUID	= SafeGetInternalArrayParameter($_POST, 'sessionreviewguid');
$sComments 	= htmlspecialchars($sComments);
$sAuthor 	= htmlspecialchars($sAuthor);
BuildOSLCConnectionString();
$sURL		= $g_sOSLCString;
$sXML 		= '';
if (strIsEmpty($sGUID)) {
	$sErrorMsg = 'GUID is empty';
	setResponseCode(400, $sErrorMsg);
	return $sErrorMsg;
}
if (strIsEmpty($sComments)) {
	$sErrorMsg = 'Comment is empty';
	setResponseCode(400, $sErrorMsg);
	return $sErrorMsg;
}
if ($_POST["isreply"] === "true") {
	$sURL 	= $sURL . 'cf/reply/';
	$sXML = '<?xml version="1.0" encoding="utf-8"?>';
	$sXML .= '<rdf:RDF xmlns:oslc_am="http://open-services.net/ns/am#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/"  xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:ss="http://www.sparxsystems.com.au/oslc_am#">';
	$sXML .= '<ss:reply>';
	$sXML .= '<dcterms:description>' . $sComments . '</dcterms:description>';
	$sXML .= '<dcterms:creator><foaf:Person><foaf:name>' . $sAuthor . '</foaf:name></foaf:Person></dcterms:creator>';
	$sXML .= '<dcterms:created>' . date("Y-m-d h:i:s A") . '</dcterms:created>';
	$sXML .= '<ss:discussionidentifier>' . $sGUID . '</ss:discussionidentifier>';
	if (!strIsEmpty($sSessionReviewGUID)) {
		$sXML .= '<ss:reviewresourceidentifier>' . $sSessionReviewGUID . '</ss:reviewresourceidentifier>';
	}
	$sXML .= '<ss:useridentifier>' . $sLoginGUID . '</ss:useridentifier>';
	$sXML .= '</ss:reply>';
	$sXML .= '</rdf:RDF>';
} else {
	$sURL 	= $sURL . 'cf/discussion/';
	$sXML  = '<?xml version="1.0" encoding="utf-8"?>';
	$sXML .= '<rdf:RDF xmlns:oslc_am="http://open-services.net/ns/am#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/"  xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:ss="http://www.sparxsystems.com.au/oslc_am#">';
	$sXML .= '<ss:discussion>';
	$sXML .= '<dcterms:description>' . $sComments . '</dcterms:description>';
	$sXML .= '<dcterms:creator><foaf:Person><foaf:name>' . $sAuthor . '</foaf:name></foaf:Person></dcterms:creator>';
	$sXML .= '<dcterms:created>' . date("Y-m-d h:i:s A") . '</dcterms:created>';
	$sXML .= '<ss:resourceidentifier>' . $sGUID . '</ss:resourceidentifier>';
	if (!strIsEmpty($sSessionReviewGUID)) {
		$sXML .= '<ss:reviewresourceidentifier>' . $sSessionReviewGUID . '</ss:reviewresourceidentifier>';
	}
	$sXML .= '<ss:useridentifier>' . $sLoginGUID . '</ss:useridentifier>';
	$sXML .= '</ss:discussion>';
	$sXML .= '</rdf:RDF>';
}
$xmlRespDoc = HTTPPostXML($sURL, $sXML);
$sOSLCErrorMsg = BuildOSLCErrorString();
echo $sOSLCErrorMsg;
