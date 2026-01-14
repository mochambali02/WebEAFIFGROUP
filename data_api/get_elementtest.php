<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
$sRootPath = dirname(dirname(__FILE__));
require_once $sRootPath . '/globals.php';
SafeStartSession();
if (
	$_SERVER["REQUEST_METHOD"] !== "POST" ||
	isset($sObjectGUID) === false ||
	isset($_SESSION['authorized']) === false
) {
	exit();
}
$aObjectDetails = array();
BuildOSLCConnectionString();
$sOSLC_URL = $g_sOSLCString . 'feature/test/';
$sTestName = htmlspecialchars($sTestName);
$sTestClass = htmlspecialchars($sTestClass);
$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
$sXML  = '<?xml version="1.0" encoding="utf-8"?>';
$sXML .= '<rdf:RDF xmlns:oslc_am="http://open-services.net/ns/am#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:ss="http://www.sparxsystems.com.au/oslc_am#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#">';
$sXML .= '  <ss:test>';
$sXML .= '    <ss:resourceidentifier>' . $sParentGUID . '</ss:resourceidentifier>';
$sXML .= '    <dcterms:title>' . $sTestName . '</dcterms:title>';
$sXML .= '    <ss:classtype>' . $sTestClass . '</ss:classtype>';
if (!strIsEmpty($sLoginGUID)) {
	$sXML .= '<ss:useridentifier>' . $sLoginGUID . '</ss:useridentifier>';
}
$sXML .= '  </ss:test>';
$sXML .= '</rdf:RDF>';
$xmlRespDoc = HTTPPostXML($sOSLC_URL, $sXML);
$sOSLCErrorMsg = BuildOSLCErrorString();
if (strIsEmpty($sOSLCErrorMsg)) {
	if ($xmlRespDoc != null) {
		$sType			= '';
		$sResType		= '';
		$sStereotype	= '';
		$sNType			= '';
		$sAuthorCSV		= '';
		$sTypeCSV		= '';
		$sStatusCSV		= '';
		$sClassTypeCSV	= '';
		$aStereotypes 	= array();
		$xnRoot = $xmlRespDoc->documentElement;
		$xnRes  = GetXMLFirstChild($xnRoot);
		if ($xnRes != null && $xnRes->childNodes != null) {
			if (GetOSLCError($xnRes)) {
				foreach ($xnRes->childNodes as $xnObjProp) {
					GetXMLNodeValue($xnObjProp, 'dcterms:title', 		$aObjectDetails['name']);
					GetXMLNodeValue($xnObjProp, 'dcterms:identifier', 	$aObjectDetails['guid']);
					GetXMLNodeValue($xnObjProp, 'ss:resourcetype', 		$sResType);
					GetXMLNodeValue($xnObjProp, 'dcterms:type', 		$sType);
					AddStereotypeToArray($xnObjProp, 					$aStereotypes);
					GetXMLNodeValue($xnObjProp, 'ss:iconidentifier', 	$sNType);
					GetXMLNodeValue($xnObjProp, 'ss:ntype', 			$sNType);
					if ($xnObjProp->nodeName === 'ss:features') {
						$xnFDesc = GetXMLFirstChild($xnObjProp);
						if ($xnFDesc != null) {
							$xnTests = GetXMLFirstChild($xnFDesc);
							if ($xnTests != null) {
								$xnTDesc = GetXMLFirstChild($xnTests);
								if ($xnTDesc != null) {
									$xnTMem  = GetXMLFirstChild($xnTDesc);
									if ($xnTMem != null) {
										$xnTest = GetXMLFirstChild($xnTMem);
										if ($xnTest != null && $xnTest->childNodes != null) {
											$sLastRun = '';
											foreach ($xnTest->childNodes as $xnTestProp) {
												GetXMLNodeValue($xnTestProp, 'dcterms:title', 		$aTestDetails['name']);
												GetXMLNodeValue($xnTestProp, 'dcterms:type', 		$aTestDetails['type']);
												GetXMLNodeValue($xnTestProp, 'ss:classtype', 		$aTestDetails['classtype']);
												GetXMLNodeValue($xnTestProp, 'ss:status', 			$aTestDetails['status']);
												GetXMLNodeValue($xnTestProp, 'ss:lastrun', 			$sLastRun);
												GetXMLNodeValue($xnTestProp, 'dcterms:description', $aTestDetails['notes']);
												GetXMLNodeValue($xnTestProp, 'ss:input', 			$aTestDetails['input']);
												GetXMLNodeValue($xnTestProp, 'ss:acceptancecriteria', $aTestDetails['acceptancecriteria']);
												GetXMLNodeValue($xnTestProp, 'ss:results', 			$aTestDetails['results']);
												if ($xnTestProp->nodeName === "ss:runby") {
													$xnNode = $xnTestProp->firstChild;
													$xnNode = $xnNode->firstChild;
													$aTestDetails['runby'] = $xnNode->nodeValue;
												} elseif ($xnTestProp->nodeName === "ss:checkedby") {
													$xnNode = $xnTestProp->firstChild;
													$xnNode = $xnNode->firstChild;
													$aTestDetails['checkedby'] = $xnNode->nodeValue;
												}
												GetXMLNodeValue($xnTestProp, 'ss:allowedname', $sAuthorCSV);
												GetXMLNodeValue($xnTestProp, 'ss:allowedrole', $sTypeCSV);
												GetXMLNodeValue($xnTestProp, 'ss:allowedstatus', $sStatusCSV);
												GetXMLNodeValue($xnTestProp, 'ss:allowedclasstype', $sClassTypeCSV);
											}
											$iPos = strpos($sLastRun, ' ');
											if ($iPos !== false) {
												$sLastRun = substr($sLastRun, 0, $iPos);
												$sLastRun = trim($sLastRun);
											}
											$aTestDetails['lastrun'] = $sLastRun;
										}
									}
								}
							}
						}
					}
				}
				if (count($aStereotypes) > 0) {
					$sStereotype = getPrimaryStereotype($aStereotypes);
					$aObjectDetails['stereotypes'] = $aStereotypes;
				}
				$aObjectDetails['type'] 		= $sType;
				$aObjectDetails['restype'] 		= $sResType;
				$aObjectDetails['stereotype'] 	= $sStereotype;
				$aObjectDetails['ntype'] 		= $sNType;
				$aObjectDetails['imageurl']		= GetObjectImagePath($sType, $sResType, $sStereotype, $sNType, '16');
				if (!strIsEmpty($sTypeCSV))
					$aTypes		= explode(',', $sTypeCSV);
				if (!strIsEmpty($sClassTypeCSV))
					$aClassTypes = explode(',', $sClassTypeCSV);
				if (!strIsEmpty($sStatusCSV))
					$aStatuses	= explode(',', $sStatusCSV);
				if (!strIsEmpty($sAuthorCSV))
					$aAuthors	= explode(',', $sAuthorCSV);
			}
		}
	}
}
