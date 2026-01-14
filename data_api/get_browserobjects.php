<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if (!isset($webea_page_parent_browser)) {
	exit();
}
$sRootPath = dirname(dirname(__FILE__));
require_once $sRootPath . '/globals.php';
SafeStartSession();
$sOSLC_URL = $g_sOSLCString . 'pbstructure/detstructure/';
$aBrowserObjs = array();
if (
	$sObjectGUID === 'addmodelroot' ||
	$sObjectGUID === 'addviewpackage' ||
	$sObjectGUID === 'addelement' ||
	$sObjectGUID === 'editelementnote' ||
	$sObjectGUID === 'addelementchgmgmt' ||
	$sObjectGUID === 'editelementresalloc' ||
	$sObjectGUID === 'editelementtest'
) {
	$sLinkTypeGUID 	= '';
	if (!strIsEmpty($sLinkType)) {
		$iPos = strpos($sLinkType, '|');
		if ($iPos !== false) {
			$sLinkTypeGUID = substr($sLinkType, 0, $iPos);
		}
	}
} elseif (
	$sObjectGUID === 'addelementresalloc' ||
	$sObjectGUID === 'addelementtest'
) {
	$sLinkTypeGUID = $sLinkType;
}
if ((!strIsEmpty($sObjectGUID)) && ($sObjectGUID !== 'initialize')) {
	if (isset($sLinkTypeGUID)) {
		if ($sLinkTypeGUID !== '') {
			$sOSLC_URL .= $sLinkTypeGUID . '/';
		}
	} else {
		$sOSLC_URL .= $sObjectGUID . '/';
	}
}
$sParas = '';
$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
$sOSLC_URL 	.= $sParas;
$xmlDoc = HTTPGetXML($sOSLC_URL);
$sParentObjGUID = '';
$sParentObjName = '';
if ($xmlDoc != null) {
	$xnRoot = $xmlDoc->documentElement;
	$xnDesc = $xnRoot->firstChild;
	if ($xnDesc != null && $xnDesc->childNodes != null) {
		$sObjName 		= '';
		$sObjGUID 		= '';
		$sObjType 		= '';
		$sObjResType 	= '';
		$sObjStructure 	= '';
		$sObjStereo		= '';
		$sObjNType 		= '0';
		$sStructure 	= '';
		$sObjLinkType	= '';
		$sObjLink		= '';
		$sObjLocked		= '';
		$sObjLockedType	= '';
		$aStereotypes 	= array();
		foreach ($xnDesc->childNodes as $xnObjProp) {
			GetXMLNodeValue($xnObjProp, 'dcterms:title', $sObjName);
			GetXMLNodeValue($xnObjProp, 'dcterms:identifier', $sObjGUID);
			GetXMLNodeValue($xnObjProp, 'ss:resourcetype', $sObjResType);
			GetXMLNodeValue($xnObjProp, 'ss:iconidentifier', $sObjNType);
			GetXMLNodeValue($xnObjProp, 'ss:ntype', $sObjNType);
			AddStereotypeToArray($xnObjProp, $aStereotypes);
			GetXMLNodeValue($xnObjProp, 'dcterms:type', $sObjType);
			GetXMLNodeValueAttr($xnObjProp, 'ss:pbstructure', 'rdf:resource', $sStructure);
			$sObjStructure = TrimInternalURL($sStructure, '/pbstructure/');
			GetXMLNodeValueAttr($xnObjProp, 'ss:hyperlinktarget', 'ss:type', $sObjLinkType);
			GetXMLNodeValueAttr($xnObjProp, 'ss:hyperlinktarget', 'rdf:resource', $sObjLink);
			$sObjLink = TrimInternalURL($sObjLink, '/pbstructure/');
			GetXMLNodeValue($xnObjProp, 'ss:locked', $sObjLocked);
			GetXMLNodeValue($xnObjProp, 'ss:locktype', $sObjLockedType);
			GetXMLNodeValue($xnObjProp, 'ss:ancestors', $sAncestors);
		}
		if (count($aStereotypes) > 0) {
			$sObjStereo = getPrimaryStereotype($aStereotypes);
		}
		if ($sObjGUID === '') {
			$row['text'] 		= $sHome;
			$row['guid'] 		= 'home';
			$row['imageurl'] 	= 'home.png';
		} else {
			$row['text'] 		= $sObjName;
			$row['guid'] 		= $sObjGUID;
			$row['imageurl'] 	= GetObjectImagePath($sObjType, $sObjResType, $sObjStereo, $sObjNType, 16);
		}
		$row['type'] 		= $sObjType;
		$row['ntype'] 		= $sObjNType;
		$row['restype'] 	= $sObjResType;
		$row['structid'] 	= $sObjStructure;
		$row['haschild']	= (!strIsEmpty($sObjStructure) ? 'true' : 'false');
		$row['linktype']	= $sObjLinkType;
		$row['hyper']		= $sObjLink;
		$row['locked']		= $sObjLocked;
		$row['lockedtype']	= $sObjLockedType;
		$aBrowserObjs[] = $row;
		if (!isset($sAncestors)) {
			$row['text'] 		= '';
			$row['guid'] 		= 'home_link';
			$row['type'] 		= '';
			$row['ntype'] 		= '';
			$row['restype'] 	= '';
			$row['structid'] 	= '';
			$row['imageurl'] 	= '';
			$row['haschild']	= '';
			$row['linktype']	= '';
			$row['hyper']		= '';
			$row['locked']		= '';
			$row['lockedtype']	= '';
			$aBrowserObjs[] = $row;
		}
		if (GetOSLCError($xnDesc)) {
			foreach ($xnDesc->childNodes as $xnObjProp) {
				if ($xnObjProp->nodeName === "ss:locked")
					$bAddElements = false;
				if ($xnObjProp->nodeName === "ss:ancestors") {
					$xnADesc = $xnObjProp->firstChild;
					$xnADescMem = $xnADesc->firstChild;
					if ($xnADescMem != null) {
						$sObjName 		= '';
						$sObjGUID 		= '';
						$sObjType 		= '';
						$sObjResType 	= '';
						$sObjStructure 	= '';
						$sObjStereo		= '';
						$sObjNType 		= '0';
						$sStructure 	= '';
						$sObjLinkType	= '';
						$sObjLink		= '';
						$sObjLocked		= '';
						$sObjLockedType	= '';
						$aStereotypes 	= array();
						$xnObj = $xnADescMem->firstChild;
						$xnObjProps = $xnObj->getElementsByTagNameNS("*", "*");
						if ($xnObjProps != null) {
							foreach ($xnObjProps as $xnObjProp) {
								GetXMLNodeValue($xnObjProp, 'dcterms:title', $sObjName);
								GetXMLNodeValue($xnObjProp, 'dcterms:identifier', $sObjGUID);
								GetXMLNodeValue($xnObjProp, 'ss:resourcetype', $sObjResType);
								GetXMLNodeValue($xnObjProp, 'ss:iconidentifier', $sObjNType);
								GetXMLNodeValue($xnObjProp, 'ss:ntype', $sObjNType);
								AddStereotypeToArray($xnObjProp, $aStereotypes);
								GetXMLNodeValue($xnObjProp, 'dcterms:type', $sObjType);
								GetXMLNodeValueAttr($xnObjProp, 'ss:pbstructure', 'rdf:resource', $sStructure);
								$sObjStructure = TrimInternalURL($sStructure, '/pbstructure/');
								GetXMLNodeValueAttr($xnObjProp, 'ss:hyperlinktarget', 'ss:type', $sObjLinkType);
								GetXMLNodeValueAttr($xnObjProp, 'ss:hyperlinktarget', 'rdf:resource', $sObjLink);
								$sObjLink = TrimInternalURL($sObjLink, '/pbstructure/');
								GetXMLNodeValue($xnObjProp, 'ss:locked', $sObjLocked);
								GetXMLNodeValue($xnObjProp, 'ss:locktype', $sObjLockedType);
							}
							if (count($aStereotypes) > 0) {
								$sObjStereo = getPrimaryStereotype($aStereotypes);
							}
							$row['text'] 		= $sObjName;
							$row['guid'] 		= $sObjGUID;
							$row['type'] 		= $sObjType;
							$row['ntype'] 		= $sObjNType;
							$row['restype'] 	= $sObjResType;
							$row['structid'] 	= $sObjStructure;
							$row['imageurl'] 	= GetObjectImagePath($sObjType, $sObjResType, $sObjStereo, $sObjNType, 16);
							$row['haschild']	= (!strIsEmpty($sObjStructure) ? 'true' : 'false');
							$row['linktype']	= $sObjLinkType;
							$row['hyper']		= $sObjLink;
							$row['locked']		= $sObjLocked;
							$row['lockedtype']	= $sObjLockedType;
							if (substr($sObjGUID, 0, 4) === 'mr_{') {
								if ($aBrowserObjs[0]['ntype'] === '0') {
									$aBrowserObjs[0]['ntype'] = '6';
									$aBrowserObjs[0]['imageurl'] = 'images/element16/viewsimple.png';
								}
							}
							$aBrowserObjs[] = $row;
						}
					}
				} elseif ($xnObjProp->nodeName === "ss:descendants") {
					$xnDDesc = $xnObjProp->firstChild;
					$xnMembers = $xnDDesc->getElementsByTagNameNS("*", "member");
					if ($xnMembers != null) {
						foreach ($xnMembers as $xnMem) {
							$xnObj = $xnMem->firstChild;
							$sObjName 		= '';
							$sObjGUID 		= '';
							$sObjType 		= '';
							$sObjResType 	= '';
							$sObjStructure 	= '';
							$sObjStereo		= '';
							$sObjNType 		= '0';
							$sStructure 	= '';
							$sObjLinkType	= '';
							$sObjLink		= '';
							$sObjLocked		= '';
							$sObjLockedType	= '';
							$aStereotypes 	= array();
							if ((substr($sObjectGUID, 0, 4) === 'mr_{') || ($sObjectGUID === 'addviewpackage')) {
								$sObjNType = '6';
							}
							$xnObjProps = $xnObj->getElementsByTagNameNS("*", "*");
							if ($xnObjProps != null) {
								foreach ($xnObjProps as $xnObjProp) {
									GetXMLNodeValue($xnObjProp, 'dcterms:title', $sObjName);
									GetXMLNodeValue($xnObjProp, 'dcterms:identifier', $sObjGUID);
									GetXMLNodeValue($xnObjProp, 'ss:resourcetype', $sObjResType);
									GetXMLNodeValue($xnObjProp, 'ss:iconidentifier', $sObjNType);
									GetXMLNodeValue($xnObjProp, 'ss:ntype', $sObjNType);
									AddStereotypeToArray($xnObjProp, $aStereotypes);
									GetXMLNodeValue($xnObjProp, 'dcterms:type', $sObjType);
									GetXMLNodeValueAttr($xnObjProp, 'ss:pbstructure', 'rdf:resource', $sStructure);
									$sObjStructure = TrimInternalURL($sStructure, '/pbstructure/');
									GetXMLNodeValueAttr($xnObjProp, 'ss:hyperlinktarget', 'ss:type', $sObjLinkType);
									GetXMLNodeValueAttr($xnObjProp, 'ss:hyperlinktarget', 'rdf:resource', $sObjLink);
									$sObjLink = TrimInternalURL($sObjLink, '/pbstructure/');
									GetXMLNodeValue($xnObjProp, 'ss:locked', $sObjLocked);
									GetXMLNodeValue($xnObjProp, 'ss:locktype', $sObjLockedType);
								}
								if (count($aStereotypes) > 0) {
									$sObjStereo = getPrimaryStereotype($aStereotypes);
								}
								$row['text'] 		= $sObjName;
								$row['guid'] 		= $sObjGUID;
								$row['type'] 		= $sObjType;
								$row['ntype'] 		= $sObjNType;
								$row['restype'] 	= $sObjResType;
								$row['structid'] 	= $sObjStructure;
								$row['imageurl'] 	= GetObjectImagePath($sObjType, $sObjResType, $sObjStereo, $sObjNType, 16);
								$row['haschild']	= (!strIsEmpty($sObjStructure) ? 'true' : 'false');
								$row['linktype']	= $sObjLinkType;
								$row['hyper']		= $sObjLink;
								$row['locked']		= $sObjLocked;
								$row['lockedtype']	= $sObjLockedType;
								$aBrowserObjs[] = $row;
							}
						}
					}
				}
			}
		}
	}
}
