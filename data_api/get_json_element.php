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
$sObjectGUID = SafeGetInternalArrayParameter($_GET, 'objectguid');
BuildOSLCConnectionString();
$sOSLC_URL = $g_sOSLCString . 'pbstructure/nohierarchy/';
if (!strIsEmpty($sObjectGUID)) {
	$sOSLC_URL = $sOSLC_URL . $sObjectGUID . '/';
}
$sParas = '';
$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
$sOSLC_URL 	.= $sParas;
ob_start();
$xmlDoc = HTTPGetXML($sOSLC_URL);
ob_end_clean();
$aRow = array();
if ($xmlDoc != null) {
	$sObjName		= '';
	$sObjAlias		= '';
	$sObjGUID		= '';
	$sObjType		= '';
	$sObjResType	= '';
	$sObjStructure	= '';
	$sObjStereo		= '';
	$sObjNType		= '';
	$sObjLinkType	= '';
	$sObjLink 		= '';
	$sFirstDescGUID = '';
	$sCompositeGUID = '';
	$sFirstDescResType = '';
	$sStructure		= '';
	$sLink			= '';
	$sErrorCode		= '';
	$sErrorMsg		= '';
	$aStereotypes 	= array();
	$xnRoot = $xmlDoc->documentElement;
	if ($xnRoot !== null) {
		$xnObj 	= $xnRoot->firstChild;
		if ($xnObj != null) {
			if (GetOSLCError($xnObj)) {
				foreach ($xnObj->childNodes as $xnObjProp) {
					GetXMLNodeValue($xnObjProp, 'dcterms:title', $sObjName);
					GetXMLNodeValue($xnObjProp, 'ss:alias', $sObjAlias);
					GetXMLNodeValue($xnObjProp, 'dcterms:identifier', $sObjGUID);
					GetXMLNodeValue($xnObjProp, 'ss:resourcetype', $sObjResType);
					GetXMLNodeValue($xnObjProp, 'dcterms:type', $sObjType);
					if ($xnObjProp->nodeName == 'ss:hyperlinktarget') {
						$sObjLinkType 	= $xnObjProp->getAttribute('ss:type');
						$sLink 			= $xnObjProp->getAttribute('rdf:resource');
						if (strpos($sLink, '/oslc/am/') !== FALSE) {
							$iPos = strpos($sLink, '/resource/');
							if ($iPos !== FALSE) {
								$sObjLink = TrimInternalURL($sLink, '/resource/');
							} else {
								$iPos = strpos($sLink, '/completeresource/');
								if ($iPos !== FALSE) {
									$sObjLink = TrimInternalURL($sLink, '/completeresource/');
								} else {
									$iPos = strpos($sLink, '/imagemgr/');
									if ($iPos !== FALSE) {
										$sObjLink = TrimInternalURL($sLink, '/imagemgr/');
									} else {
										$sObjLink = $sLink;
									}
								}
							}
						} else {
							$sObjLink = $sLink;
						}
					}
					if ($xnObjProp->nodeName == 'ss:pbstructure') {
						$sStructure 	= $xnObjProp->getAttribute('rdf:resource');
						$sObjStructure 	= TrimInternalURL($sStructure, '/pbstructure/');
					}
					GetXMLNodeValue($xnObjProp, 'ss:iconidentifier', $sObjNType);
					GetXMLNodeValue($xnObjProp, 'ss:ntype', $sObjNType);
					GetXMLNodeValue($xnObjProp, 'ss:firstdescendantresourceidentifier', $sFirstDescGUID);
					GetXMLNodeValue($xnObjProp, 'ss:compositeresourceidentifier', $sCompositeGUID);
					AddStereotypeToArray($xnObjProp, $aStereotypes);
				}
				if (count($aStereotypes) > 0) {
					$sObjStereo = getPrimaryStereotype($aStereotypes);
					$aRow['stereotypes'] = $aStereotypes;
				}
				if (substr($sObjName, 0, 14) === '$elementimg://') {
					$sObjGUID = 'el_' . substr($sObjName, 14);
					$sObjLink = $sObjGUID;
					$sObjLinkType = 'elementimage';
				} else if (substr($sObjName, 0, 10) === '$matrix://') {
					$sObjGUID = 'matrix';
					$sObjLink = substr($sObjName, 10);
				}
				if (!empty($aRow['stereotypes'])) {
					if ($aRow['stereotypes'][0]['name'] === 'EAMatrixSpecification') {
						$sObjGUID = 'matrix';
						$sObjLink = $sObjName;
					}
				}
			}
			$aRow['name'] 		= $sObjName;
			$aRow['alias'] 		= $sObjAlias;
			$aRow['guid'] 		= $sObjGUID;
			$aRow['namealias'] 	= GetMeaningfulObjectName($sObjType, $sObjName, $sObjAlias);
			$aRow['type'] 		= $sObjType;
			$aRow['restype'] 	= $sObjResType;
			$aRow['haschild']	= (!strIsEmpty($sObjStructure) ? 'true' : 'false');
			$aRow['linktype']	= $sObjLinkType;
			$aRow['hyper']		= $sObjLink;
			$aRow['imageurl'] 	= GetObjectImagePath($sObjType, $sObjResType, $sObjStereo, $sObjNType, '16');
			$aRow['errorcode'] 	= $g_sLastOSLCErrorCode;
			$aRow['errormsg'] 	= $g_sLastOSLCErrorMsg;
			$aRow['ntype'] 		= $sObjNType;
			$aRow['firstdescguid']	= $sFirstDescGUID;
			$aRow['compositeguid']	= $sCompositeGUID;
		}
	}
}
header('Content-Type: application/json; charset=utf-8');
$jsonStr = json_encode($aRow);
if ($jsonStr === FALSE)
	echo '{}';
else
	echo $jsonStr;
