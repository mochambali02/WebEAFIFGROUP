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
	if ($_SERVER["REQUEST_METHOD"] !== "POST" ||
		isset($sObjectGUID) === false ||
		isset($_SESSION['authorized']) === false)
	{
		exit();
	}
	BuildOSLCConnectionString();
	$sOSLC_URL = $g_sOSLCString . 'resource/';
	if ( !strIsEmpty($sObjectGUID) )
	{
		$sOSLC_URL = $sOSLC_URL . $sObjectGUID . '/';
	}
	$sParas = '';
	$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
	$sOSLC_URL 	.= $sParas;
	$xmlDoc = HTTPGetXML($sOSLC_URL);
	$aObjectDetails = array();
	if ($xmlDoc != null)
	{
		$sObjName		= '';
		$sObjAlias		= '';
		$sObjGUID		= '';
		$sObjType		= '';
		$sObjResType	= '';
		$sObjStructure	= '';
		$sObjNotes		= '';
		$sObjStereo		= '';
		$sObjNType		= '';
		$sObjLinkType	= '';
		$sObjLink 		= '';
		$aStereotypes = array();
		$xnRoot = $xmlDoc->documentElement;
		$xnObj 	= $xnRoot->firstChild;
		if ($xnObj != null && $xnObj->childNodes != null)
		{
			if ( GetOSLCError($xnObj) )
			{
				foreach ($xnObj->childNodes as $xnObjProp)
				{
					GetXMLNodeValue($xnObjProp, 'dcterms:title', $sObjName);
					GetXMLNodeValue($xnObjProp, 'ss:alias', $sObjAlias);
					GetXMLNodeValue($xnObjProp, 'dcterms:identifier', $sObjGUID);
					GetXMLNodeValue($xnObjProp, 'ss:resourcetype', $sObjResType);
					GetXMLNodeValue($xnObjProp, 'dcterms:type', $sObjType);
					GetXMLNodeValue($xnObjProp, 'dcterms:description', 	$sObjNotes);
					if ($xnObjProp->nodeName === 'ss:hyperlinktarget')
					{
						$sObjLinkType 	= $xnObjProp->getAttribute('ss:type');
						$sLink 			= $xnObjProp->getAttribute('rdf:resource');
						$iPos = strpos($sLink, '/resource/');
						if ( $iPos !== FALSE )
						{
							$sObjLink 		= TrimInternalURL($sLink, '/resource/');
						}
						else
						{
							$sObjLink 		= TrimInternalURL($sLink, '/completeresource/');
						}
					}
					if ($xnObjProp->nodeName === 'ss:pbstructure')
					{
						$sStructure 	= $xnObjProp->getAttribute('rdf:resource');
						$sObjStructure 	= TrimInternalURL($sStructure, '/pbstructure/');
					}
					GetXMLNodeValue($xnObjProp, 'ss:iconidentifier', $sObjNType);
					GetXMLNodeValue($xnObjProp, 'ss:ntype', $sObjNType);
					AddStereotypeToArray($xnObjProp, $aStereotypes);
				}
				if (count($aStereotypes)> 0)
				{
					$sObjStereo = getPrimaryStereotype($aStereotypes);
					$aObjectDetails['stereotypes'] = $aStereotypes;
				}
				$aObjectDetails['name'] 	= $sObjName;
				$aObjectDetails['alias'] 	= $sObjAlias;
				$aObjectDetails['guid'] 	= $sObjGUID;
				$aObjectDetails['type'] 	= $sObjType;
				$aObjectDetails['namealias']= GetMeaningfulObjectName($sObjType, $sObjName, $sObjAlias);
				$aObjectDetails['restype'] 	= $sObjResType;
				$aObjectDetails['haschild']	= (!strIsEmpty($sObjStructure)?'true':'false');
				$aObjectDetails['notes'] 	= $sObjNotes;
				$aObjectDetails['linktype']	= $sObjLinkType;
				$aObjectDetails['hyper']	= $sObjLink;
				$sObjImageURL = GetObjectImagePath($sObjType, $sObjResType, $sObjStereo, $sObjNType, '16');
				if (($sObjResType === 'Package') && (isset($sObjectImageURL)))
				{
					$sObjImageURL = AdjustImagePath($sObjectImageURL, '16');
				}
				$aObjectDetails['imageurl']	= $sObjImageURL;
			}
			else
			{
			}
		}
	}
?>