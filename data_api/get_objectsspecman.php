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
	!isset($webea_page_parent_mainview)  ||
	isset($sObjectGUID) === false ||
	isset($_SESSION['authorized']) === false
) {
	exit();
}
BuildOSLCConnectionString();
$sOSLC_URL = $g_sOSLCString . 'pbstructure/descendanthierarchy/';
if (!strIsEmpty($sObjectGUID)) {
	$sOSLC_URL .= $sObjectGUID . '/';
}
$sParas = '';
$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
$sOSLC_URL 	.= $sParas;
$xmlDoc = HTTPGetXML($sOSLC_URL);
$sParentObjGUID = '';
$sParentObjName = '';
$sObjName 		= '';
$sObjGUID 		= '';
$sObjType 		= '';
$sObjModified	= '';
$sObjResType 	= '';
$sObjStereo		= '';
$sObjNType 		= '0';
$sStructure 	= '';
$sObjLocked		= '';
$sObjLockedType	= '';
$sObjAuthor		= '';
$sObjAlias		= '';
$sObjNotes		= '';
$aObjStereotypes = array();
if ($xmlDoc != null) {
	$xnRoot = $xmlDoc->documentElement;
	$xnDesc = $xnRoot->firstChild;
	if ($xnDesc != null && $xnDesc->childNodes != null) {
		if (GetOSLCError($xnDesc)) {
			foreach ($xnDesc->childNodes as $xnObjProp) {
				GetXMLNodeValue($xnObjProp, 'dcterms:title', 		$sObjName);
				GetXMLNodeValue($xnObjProp, 'dcterms:identifier', 	$sObjGUID);
				GetXMLNodeValue($xnObjProp, 'ss:resourcetype', 		$sObjResType);
				GetXMLNodeValue($xnObjProp, 'ss:iconidentifier', 	$sObjNType);
				GetXMLNodeValue($xnObjProp, 'ss:ntype', 			$sObjNType);
				AddStereotypeToArray($xnObjProp, $aObjStereotypes);
				GetXMLNodeValue($xnObjProp, 'dcterms:type', 		$sObjType);
				GetXMLNodeValue($xnObjProp, 'dcterms:modified', 	$sObjModified);
				GetXMLNodeValue($xnObjProp, 'dcterms:description', 	$sObjNotes);
				GetXMLNodeValue($xnObjProp, 'ss:locked', 			$sObjLocked);
				GetXMLNodeValue($xnObjProp, 'ss:locktype', 			$sObjLockedType);
				GetXMLNodeValue($xnObjProp, 'ss:alias', 			$sObjAlias);
				GetAuthorFromXMLNode($xnObjProp, $sObjAuthor);
				if ($xnObjProp->nodeName === "ss:locked")
					$bAddElements = false;
				elseif ($xnObjProp->nodeName === "ss:descendants") {
					$xnDDesc = $xnObjProp->firstChild;
					if ($xnDDesc != null) {
						$iHierarchyLevel = 1;
						$bObjectAdded = false;
						foreach ($xnDDesc->childNodes as $xnDMem) {
							$sDObjName			= '';
							$sDObjType			= '';
							$sDObjGUID			= '';
							$sDObjResType		= '';
							$sDObjNType			= '';
							$sDObjNotes			= '';
							$sDObjModified		= '';
							$sDObjAuthor		= '';
							$sDObjAlias			= '';
							$sDCStructure		= '';
							$sDCObjStructure	= '';
							$aDObjStereotypes 	= array();
							if ($xnDMem != null) {
								$xnDRes = $xnDMem->firstChild;
								if ($xnDRes != null) {
									$bObjectAdded = false;
									foreach ($xnDRes->childNodes as $xnDObj) {
										GetXMLNodeValue($xnDObj, 'dcterms:title', 		$sDObjName);
										GetXMLNodeValue($xnDObj, 'dcterms:type', 		$sDObjType);
										GetXMLNodeValue($xnDObj, 'dcterms:identifier', 	$sDObjGUID);
										GetXMLNodeValue($xnDObj, 'dcterms:description',	$sDObjNotes);
										GetXMLNodeValue($xnDObj, 'dcterms:modified', 	$sDObjModified);
										GetXMLNodeValue($xnDObj, 'ss:resourcetype', 	$sDObjResType);
										GetXMLNodeValue($xnDObj, 'ss:iconidentifier', 	$sDObjNType);
										GetXMLNodeValue($xnDObj, 'ss:ntype', 			$sDObjNType);
										AddStereotypeToArray($xnDObj, 					$aDObjStereotypes);
										GetAuthorFromXMLNode($xnDObj, 					$sDObjAuthor);
										GetXMLNodeValueAttr($xnDObj, 'ss:pbstructure', 'rdf:resource', $sDCStructure);
										$sDCObjStructure = TrimInternalURL($sDCStructure, '/pbstructure/');
										GetXMLNodeValue($xnDObj, 'ss:alias', 			$sDObjAlias);
										if ($xnDObj->nodeName === "ss:nestedresources") {
											AddObject2Array($aObjs, $iHierarchyLevel, $sDObjGUID, $sDObjName, $sDObjType, $sDObjResType, $aDObjStereotypes, $sDObjNType, $sDObjNotes, $sDObjModified, $sDObjAuthor, $sDObjAlias, $sDCObjStructure);
											ProcessChildObjects($aObjs, $iHierarchyLevel, $xnDObj);
											$bObjectAdded = true;
										}
									}
									if (!$bObjectAdded) {
										AddObject2Array($aObjs, $iHierarchyLevel, $sDObjGUID, $sDObjName, $sDObjType, $sDObjResType, $aDObjStereotypes, $sDObjNType, $sDObjNotes, $sDObjModified, $sDObjAuthor, $sDObjAlias, $sDCObjStructure);
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
function AddObject2Array(&$aObjs, $iLevel, $sGUID, $sName, $sType, $sResType, $aStereotypes, $sNType, $sNotes, $sModified, $sAuthor, $sAlias, $sStructure)
{
	$sStereotype = '';
	if (count($aStereotypes) > 0) {
		$sStereotype = getPrimaryStereotype($aStereotypes);
	}
	$aRow				= array();
	$aRow['level']		= $iLevel;
	$aRow['name']		= $sName;
	$aRow['guid']		= $sGUID;
	$aRow['notes']		= $sNotes;
	$aRow['type']		= $sType;
	$aRow['restype']	= $sResType;
	$aRow['stereotype']	= $sStereotype;
	$aRow['modified']	= $sModified;
	$aRow['author']		= $sAuthor;
	$aRow['alias']		= $sAlias;
	$aRow['imageurl'] 	= GetObjectImagePath($sType, $sResType, $sStereotype, $sNType, '16');
	$aRow['haschild']	= ((!strIsEmpty($sStructure) && (substr($sGUID, 0, 3) === 'pk_' || substr($sGUID, 0, 3) === 'mr_')) ? 'true' : 'false');
	$aObjs[] = $aRow;
}
function ProcessChildObjects(&$aObjs, $iLevel, $xnNode)
{
	$iLevel = $iLevel + 1;
	$sObjName			= '';
	$sObjType			= '';
	$sObjGUID			= '';
	$sObjResType		= '';
	$sObjNType			= '';
	$sObjNotes			= '';
	$sObjModified		= '';
	$sObjAuthor			= '';
	$sObjAlias			= '';
	$sStructure			= '';
	$sObjStructure		= '';
	$aObjStereotypes 	= array();
	if ($xnNode != null) {
		$xnDesc = $xnNode->firstChild;
		$xnDescMem = $xnDesc->firstChild;
		if ($xnDescMem != null) {
			foreach ($xnDesc->childNodes as $xnMem) {
				if ($xnMem != null) {
					$xnRes = $xnMem->firstChild;
					$bObjectAdded = false;
					foreach ($xnRes->childNodes as $xnObjProp) {
						GetXMLNodeValue($xnObjProp, 'dcterms:title', 		$sObjName);
						GetXMLNodeValue($xnObjProp, 'dcterms:type', 		$sObjType);
						GetXMLNodeValue($xnObjProp, 'dcterms:identifier', 	$sObjGUID);
						GetXMLNodeValue($xnObjProp, 'dcterms:description',	$sObjNotes);
						GetXMLNodeValue($xnObjProp, 'dcterms:modified', 	$sObjModified);
						GetXMLNodeValue($xnObjProp, 'ss:resourcetype', 		$sObjResType);
						GetXMLNodeValue($xnObjProp, 'ss:iconidentifier', 	$sObjNType);
						GetXMLNodeValue($xnObjProp, 'ss:ntype', 			$sObjNType);
						AddStereotypeToArray($xnObjProp, 					$aObjStereotypes);
						GetAuthorFromXMLNode($xnObjProp, 					$sObjAuthor);
						GetXMLNodeValue($xnObjProp, 'ss:alias', 			$sObjAlias);
						GetXMLNodeValueAttr($xnObjProp, 'ss:pbstructure', 'rdf:resource', $sStructure);
						$sObjStructure = TrimInternalURL($sStructure, '/pbstructure/');
						if ($xnObjProp->nodeName === "ss:nestedresources") {
							AddObject2Array($aObjs, $iLevel, $sObjGUID, $sObjName, $sObjType, $sObjResType, $aObjStereotypes, $sObjNType, $sObjNotes, $sObjModified, $sObjAuthor, $sObjAlias, $sObjStructure);
							ProcessChildObjects($aObjs, $iLevel, $xnObjProp);
							$bObjectAdded = true;
						}
					}
					if (!$bObjectAdded) {
						AddObject2Array($aObjs, $iLevel, $sObjGUID, $sObjName, $sObjType, $sObjResType, $aObjStereotypes, $sObjNType, $sObjNotes, $sObjModified, $sObjAuthor, $sObjAlias, $sObjStructure);
					}
				}
			}
		}
	}
}
