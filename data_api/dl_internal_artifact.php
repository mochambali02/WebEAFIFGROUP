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
CheckAuthorisation();
$sObjectGUID = SafeGetInternalArrayParameter($_GET, 'guid');
$filedata = $sObjectGUID;
$webea_page_parent_mainview = true;
$sResourceFeaturesFilter = '';
$aCommonProps 	= array();
$aAttributes 	= array();
$aOperations 	= array();
$aFiles 	= array();
$aConstraints 	= array();
$aTaggedValues 	= array();
$aExternalData 	= array();
$aDiscussions 	= array();
$aReviewDiscuss = array();
$aReviewDiagrams = array();
$aReviewNoDiscuss = array();
$aTests 	= array();
$aResAllocs 	= array();
$aScenarios 	= array();
$aRequirements 	= array();
$aRunStates 	= array();
$aUsages	 	= array();
$aDocument	 	= array();
$aParentInfo	= array();
$aRelationships = array();
$aFeatures	= array();
$aChanges 	= array();
$aDocuments 	= array();
$aDefects 	= array();
$aIssues 	= array();
$aTasks 	= array();
$aEvents 	= array();
$aDecisions 	= array();
$aRisks 	= array();
$aEfforts 	= array();
$aMetrics 	= array();
include('get_properties.php');
$filedata = $aDocument['content'];
$sObjectName = SafeGetArrayItem1Dim($aCommonProps, 'name');
$sDocContent = SafeGetArrayItem1Dim($aDocument, 'content');
$sExtension = SafeGetArrayItem1Dim($aDocument, 'extension');
$sFileName = $sObjectName;
$iExtLength = strlen($sExtension);
$sObjectNameEnd = substr($sObjectName, -$iExtLength);
if ($sExtension !== $sObjectNameEnd) {
	$sFileName = $sObjectName . $sExtension;
}
$base64 = $filedata;
$decoded = base64_decode($base64);
file_put_contents($sFileName, $decoded);
if (file_exists($sFileName)) {
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="' . basename($sFileName) . '"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($sFileName));
	readfile($sFileName);
	unlink($sFileName);
	exit;
}
