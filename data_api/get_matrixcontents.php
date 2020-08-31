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
	if  ( !isset($webea_page_parent_mainview) ||
		   isset($sObjectGUID) === false ||
		   isset($_SESSION['authorized']) === false)
	{
		exit();
	}
	BuildOSLCConnectionString();
	$sOSLC_URL 	= $g_sOSLCString . "rm/results/";
	$sParas = '';
	$sLoginGUID = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	AddURLParameter($sParas, 'profile', $sObjectHyper);
	AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
	$sOSLC_URL 	.= $sParas;
	$xmlDoc = null;
	if ( !strIsEmpty($sOSLC_URL) )
	{
		$xmlDoc = HTTPGetXML($sOSLC_URL);
	}
	$aTargetElements = array();
	$aSourceElements = array();
	if ($xmlDoc != null)
	{
		$xnRoot = $xmlDoc->documentElement;
		$xnDesc = $xnRoot->firstChild;
		if ($xnDesc != null && $xnDesc->childNodes != null)
		{
			foreach ($xnDesc->childNodes as $xnDescChild)
			{
				if ($xnDescChild->nodeName === 'ss:sourcecontents')
				{
					$aSourceElements = array();
					$i = 0;
					$xnDesc = $xnDescChild->firstChild;
					foreach ($xnDesc->childNodes as $xnMember)
					{
						$aRow = array();
						$sSourceName = '';
						$sSourceGUID = '';
						$sSourceType = '';
						$sSourceClassifier = '';
						$xnRes = $xnMember->firstChild;
						foreach ($xnRes->childNodes as $xnResChild)
						{
							GetXMLNodeValue($xnResChild, 'dcterms:title', $sSourceName);
							GetXMLNodeValue($xnResChild, 'dcterms:identifier', $sSourceGUID);
							GetXMLNodeValue($xnResChild, 'dcterms:type', $sSourceType);
							GetXMLNodeValue($xnResChild, 'ss:classifierresourcename', $sSourceClassifier);
						}
						if ($sSourceClassifier !== '')
						{
							$aRow['sourcedisplayname'] = $sSourceName . ':' . $sSourceClassifier;
							$sKey = $sSourceName . ':' . $sSourceClassifier;
						}
						else
						{
							$sSourceName = GetPlainDisplayName($sSourceName);
							$aRow['sourcedisplayname'] = $sSourceName;
							$sKey = $sSourceName;
						}
						$aRow['sourcename'] = $sSourceName;
						$aRow['sourceguid'] = $sSourceGUID;
						$aRow['sourcetype'] = $sSourceType;
						$aRow['sourceclassifier'] = $sSourceClassifier;
						$aSourceElements[strtolower($sKey) .'_'. $i] = $aRow;
						$i++;
					}
				}
				elseif ($xnDescChild->nodeName === 'ss:targetcontents')
				{
					$sTargetName = '';
					$sTargetGUID = '';
					$sTargetType = '';
					$sTargetClassifier = '';
					$aTargetElements = array();
					$i =0;
					$xnDesc = $xnDescChild->firstChild;
					foreach ($xnDesc->childNodes as $xnMember)
					{
						$aRow = array();
						$xnRes = $xnMember->firstChild;
						foreach ($xnRes->childNodes as $xnResChild)
						{
							GetXMLNodeValue($xnResChild, 'dcterms:title', $sTargetName);
							GetXMLNodeValue($xnResChild, 'dcterms:identifier', $sTargetGUID);
							GetXMLNodeValue($xnResChild, 'dcterms:type', $sTargetType);
							GetXMLNodeValue($xnResChild, 'ss:classifierresourcename', $sTargetClassifier);
						}
						if ($sTargetClassifier !== '')
						{
							$aRow['targetdisplayname'] = $sTargetName . ':' . $sTargetClassifier;
							$sKey = $sTargetName . ':' . $sTargetClassifier;
						}
						else
						{
							$sTargetName = GetPlainDisplayName($sTargetName);
							$aRow['targetdisplayname'] = $sTargetName;
							$sKey = $sTargetName;
						}
						$aRow['targetname'] = $sTargetName;
						$aRow['targetguid'] = $sTargetGUID;
						$aRow['targettype'] = $sTargetType;
						$aTargetElements[strtolower($sKey) .'_'. $i] = $aRow;
						$i++;
					}
				}
				elseif ($xnDescChild->nodeName === 'ss:linkcontents')
				{
					$aRelationships = array();
					$xnDesc = $xnDescChild->firstChild;
					foreach ($xnDesc->childNodes as $xnMember)
					{
						$aRow = array();
						$sRelGUID			= '';
						$sRelType			= '';
						$sRelName			= '';
						$sRelDirection		= '';
						$sRelSourceGUID		= '';
						$sRelTargetGUID		= '';
						$xnMemberDesc = $xnMember->firstChild;
						foreach ($xnMemberDesc->childNodes as $xnRelationship)
						{
							GetXMLNodeValue($xnRelationship, 'dcterms:identifier', $sRelGUID);
							GetXMLNodeValue($xnRelationship, 'dcterms:type', $sRelType);
							GetXMLNodeValue($xnRelationship, 'dcterms:title', $sRelName);
							GetXMLNodeValue($xnRelationship, 'ss:directiontype', $sRelDirection);
							GetXMLNodeValue($xnRelationship, 'ss:sourceresourceidentifier', $sRelSourceGUID);
							GetXMLNodeValue($xnRelationship, 'ss:targetresourceidentifier', $sRelTargetGUID);
							GetXMLNodeValue($xnRelationship, 'ss:overlay', $sOverlay);
						}
						$aRow['identifier'] = $sRelGUID;
						$aRow['type'] = $sRelType;
						$aRow['name'] = $sRelName;
						$aRow['directiontype'] = $sRelDirection;
						$aRow['sourceresourceidentifier'] = $sRelSourceGUID;
						$aRow['targetresourceidentifier'] = $sRelTargetGUID;
						$aRow['overlay'] = $sOverlay;
						$aRelationships[] = $aRow;
					}
				}
			}
		}
	}
?>