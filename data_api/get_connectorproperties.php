<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if (
	!isset($webea_page_parent_mainview) ||
	isset($sObjectGUID) === false ||
	isset($_SESSION['authorized']) === false
) {
	exit();
}
$sRootPath = dirname(dirname(__FILE__));
require_once $sRootPath . '/globals.php';
$aCommonProps = array();
$aSourceProps = array();
$aTargetProps = array();
if (!strIsEmpty($sObjectGUID)) {
	BuildOSLCConnectionString();
	$sOSLC_URL 	= $g_sOSLCString . "linktyperesource/";
	$sOSLC_URL 	= $sOSLC_URL . $sObjectGUID . "/";
	if (!strIsEmpty($sOSLC_URL)) {
		$aCommonProps['imageurl'] = '';
		$aCommonProps['guid'] = '';
		$aCommonProps['type'] = '';
		$sParas = '';
		$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
		AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
		$sOSLC_URL 	.= $sParas;
		$xmlDoc = HTTPGetXML($sOSLC_URL);
		if ($xmlDoc != null) {
			$xnRoot = $xmlDoc->documentElement;
			$xnObj = $xnRoot->firstChild;
			if ($xnObj != null) {
				if (GetOSLCError($xnObj)) {
					$aStereotypes 		= array();
					$aSrcStereotypes 	= array();
					$aTrgStereotypes 	= array();
					foreach ($xnObj->childNodes as $xnObjProp) {
						GetXMLNodeValue($xnObjProp, 'dcterms:identifier', 	$aCommonProps['guid']);
						GetXMLNodeValue($xnObjProp, 'dcterms:title', 		$aCommonProps['name']);
						GetXMLNodeValue($xnObjProp, 'dcterms:type', 		$aCommonProps['type']);
						GetXMLNodeValue($xnObjProp, 'dcterms:description', 	$aCommonProps['notes']);
						AddStereotypeToArray($xnObjProp, $aStereotypes);
						GetXMLNodeValue($xnObjProp, 'ss:alias', 			$aCommonProps['alias']);
						GetXMLNodeValue($xnObjProp, 'ss:direction', 		$aCommonProps['direction']);
						GetXMLNodeValue($xnObjProp, 'ss:sourcerole', 		$aCommonProps['sourcerole']);
						GetXMLNodeValue($xnObjProp, 'ss:sourcealias', 		$aCommonProps['sourcealias']);
						AddStereotypeToArray($xnObjProp, $aSrcStereotypes, 'ss:sourcestereotype');
						GetXMLNodeValue($xnObjProp, 'ss:sourceroledescription', $aCommonProps['sourceroledesc']);
						GetXMLNodeValue($xnObjProp, 'ss:sourcemultiplicity', $aCommonProps['sourcemultiplicity']);
						GetXMLNodeValue($xnObjProp, 'ss:sourceordered', 	$aCommonProps['sourceordered']);
						GetXMLNodeValue($xnObjProp, 'ss:sourceaccess',	 	$aCommonProps['sourceaccess']);
						GetXMLNodeValue($xnObjProp, 'ss:sourceaggregation',	$aCommonProps['sourceaggregation']);
						GetXMLNodeValue($xnObjProp, 'ss:sourcescope',	 	$aCommonProps['sourcescope']);
						GetXMLNodeValue($xnObjProp, 'ss:sourcemembertype', 	$aCommonProps['sourcemembertype']);
						GetXMLNodeValue($xnObjProp, 'ss:sourcechangeable', 	$aCommonProps['sourcechangeable']);
						GetXMLNodeValue($xnObjProp, 'ss:sourcecontainment',	$aCommonProps['sourcecontainment']);
						GetXMLNodeValue($xnObjProp, 'ss:sourcenavigability', $aCommonProps['sourcenavigability']);
						GetXMLNodeValue($xnObjProp, 'ss:sourcederived',		$aCommonProps['sourcederived']);
						GetXMLNodeValue($xnObjProp, 'ss:sourcederivedunion', $aCommonProps['sourcederivedunion']);
						GetXMLNodeValue($xnObjProp, 'ss:sourceowned',		$aCommonProps['sourceowned']);
						GetXMLNodeValue($xnObjProp, 'ss:sourceallowduplicates',	$aCommonProps['sourceallowduplicates']);
						GetXMLNodeValue($xnObjProp, 'ss:targetrole', 		$aCommonProps['targetrole']);
						GetXMLNodeValue($xnObjProp, 'ss:targetalias', 		$aCommonProps['targetalias']);
						GetXMLNodeValue($xnObjProp, 'ss:targetroledescription', $aCommonProps['targetroledesc']);
						AddStereotypeToArray($xnObjProp, $aTrgStereotypes, 'ss:targetstereotype');
						GetXMLNodeValue($xnObjProp, 'ss:targetmultiplicity', $aCommonProps['targetmultiplicity']);
						GetXMLNodeValue($xnObjProp, 'ss:targetordered', 	$aCommonProps['targetordered']);
						GetXMLNodeValue($xnObjProp, 'ss:targetaccess',	 	$aCommonProps['targetaccess']);
						GetXMLNodeValue($xnObjProp, 'ss:targetaggregation',	$aCommonProps['targetaggregation']);
						GetXMLNodeValue($xnObjProp, 'ss:targetscope',	 	$aCommonProps['targetscope']);
						GetXMLNodeValue($xnObjProp, 'ss:targetmembertype',	$aCommonProps['targetmembertype']);
						GetXMLNodeValue($xnObjProp, 'ss:targetchangeable', 	$aCommonProps['targetchangeable']);
						GetXMLNodeValue($xnObjProp, 'ss:targetcontainment',	$aCommonProps['targetcontainment']);
						GetXMLNodeValue($xnObjProp, 'ss:targetnavigability', $aCommonProps['targetnavigability']);
						GetXMLNodeValue($xnObjProp, 'ss:targetderived',		$aCommonProps['targetderived']);
						GetXMLNodeValue($xnObjProp, 'ss:targetderivedunion', $aCommonProps['targetderivedunion']);
						GetXMLNodeValue($xnObjProp, 'ss:targetowned',		$aCommonProps['targetowned']);
						GetXMLNodeValue($xnObjProp, 'ss:targetallowduplicates',	$aCommonProps['targetallowduplicates']);
						if ($xnObjProp->nodeName === "ss:linkends") {
							$xnDescription = GetXMLFirstChild($xnObjProp);
							if ($xnDescription != null) {
								foreach ($xnDescription->childNodes as $xnMem) {
									$xnLinkEnd = GetXMLFirstChild($xnMem);
									if ($xnLinkEnd != null) {
										$sLE_GUID 		= '';
										$sLE_Name 		= '';
										$sLE_Type 		= '';
										$sLE_Stereotype = '';
										$sLE_EndType 	= '';
										$sLE_NType 		= '';
										$sLE_ResType 	= '';
										$aLEStereotypes = array();
										foreach ($xnLinkEnd->childNodes as $xnLEProp) {
											GetXMLNodeValue($xnLEProp, 'dcterms:identifier', $sLE_GUID);
											GetXMLNodeValue($xnLEProp, 'dcterms:title', 	$sLE_Name);
											GetXMLNodeValue($xnLEProp, 'dcterms:type', 		$sLE_Type);
											AddStereotypeToArray($xnLEProp, $aLEStereotypes);
											GetXMLNodeValue($xnLEProp, 'ss:endtype', 		$sLE_EndType);
											GetXMLNodeValue($xnLEProp, 'ss:ntype', 			$sLE_NType);
											GetXMLNodeValue($xnLEProp, 'ss:resourcetype',	$sLE_ResType);
										}
										$sLE_Stereotype = getPrimaryStereotype($aLEStereotypes);
										if ($sLE_EndType === 'Source') {
											$aSourceProps['guid'] 		= $sLE_GUID;
											$aSourceProps['name'] 		= $sLE_Name;
											$aSourceProps['type'] 		= $sLE_Type;
											$aSourceProps['stereotype'] = $sLE_Stereotype;
											$aSourceProps['ntype'] 		= $sLE_NType;
											$aSourceProps['restype'] 	= $sLE_ResType;
											$aSourceProps['imageurl'] 	= GetObjectImagePath($sLE_Type, $sLE_ResType, $sLE_Stereotype, $sLE_NType, '16');
										} elseif ($sLE_EndType === 'Target') {
											$aTargetProps['guid'] 		= $sLE_GUID;
											$aTargetProps['name'] 		= $sLE_Name;
											$aTargetProps['type'] 		= $sLE_Type;
											$aTargetProps['stereotype'] = $sLE_Stereotype;
											$aTargetProps['ntype'] 		= $sLE_NType;
											$aTargetProps['restype'] 	= $sLE_ResType;
											$aTargetProps['imageurl'] 	= GetObjectImagePath($sLE_Type, $sLE_ResType, $sLE_Stereotype, $sLE_NType, '16');
										}
									}
								}
							}
						}
					}
					if (count($aStereotypes) > 0) {
						$aCommonProps['stereotype'] = getPrimaryStereotype($aStereotypes);
						$aCommonProps['stereotypes'] = $aStereotypes;
					}
					if (count($aSrcStereotypes) > 0) {
						$aCommonProps['sourcestereotype'] = getPrimaryStereotype($aSrcStereotypes);
						$aCommonProps['sourcestereotypes'] = $aSrcStereotypes;
					}
					if (count($aTrgStereotypes) > 0) {
						$aCommonProps['targetstereotype'] = getPrimaryStereotype($aTrgStereotypes);
						$aCommonProps['targetstereotypes'] = $aTrgStereotypes;
					}
				}
			}
			$aCommonProps['imageurl'] = GetObjectImagePath($aCommonProps['type'], 'Connector', '', '', '64');
			if (strlen($aCommonProps['guid']) > 3) {
				$aCommonProps['guid_raw'] = substr($aCommonProps['guid'], 3);
			}
		} else {
			return null;
		}
	}
}
