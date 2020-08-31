<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	if  ( !isset($webea_page_parent_mainview) ||
		   isset($sCategory) === false ||
		   isset($sTerm) === false ||
		   isset($sWhen) === false )
	{
		exit();
	}
	$sRootPath = dirname(dirname(__FILE__));
	require_once $sRootPath . '/globals.php';
	SafeStartSession();
	BuildOSLCConnectionString();
	$sOSLC_URL = $g_sOSLCString . 'ps/';
	if ( !strIsEmpty($sCategory) )
	{
		$sParas = '';
		AddURLParameter($sParas, 'category', $sCategory);
		if ( !strIsEmpty($sSearchType) )
			AddURLParameter($sParas, 'search', $sSearchType);
		if ( !strIsEmpty($sTerm) )
			AddURLParameter($sParas, 'term', $sTerm);
		if ( !strIsEmpty($sWhen) )
			AddURLParameter($sParas, 'recent', $sWhen);
		$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
		AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
		$sOSLC_URL 	.= $sParas;
		$xmlDoc = HTTPGetXML($sOSLC_URL);
		$aRow = array();
		if ($xmlDoc != null)
		{
			$xnRoot = $xmlDoc->documentElement;
			$xnDesc = $xnRoot->firstChild;
			if ($xnDesc != null)
			{
				if ($xnDesc->nodeName==='oslc:Error')
				{
					$sErrorMsg = '';
					foreach ($xnDesc->childNodes as $xnMem)
					{
						GetXMLNodeValue($xnMem, 'oslc:message', $sErrorMsg);
						GetXMLNodeValue($xnMem, 'oslc:statusCode', $GLOBALS['g_sLastOSLCErrorCode']);
					}
					if ( !strIsEmpty($sErrorMsg) )
					{
						if ($GLOBALS['g_sLastOSLCErrorCode'] === '403' && $sErrorMsg === 'Invalid User Security Identifier')
						{
							$GLOBALS['g_sLastOSLCErrorMsg'] = $sErrorMsg;
						}
						else
						{
							echo '<div class="search-results-empty">' . $sErrorMsg . '</div>';
							exit();
						}
					}
				}
				else
				{
					foreach ($xnDesc->childNodes as $xnMem)
					{
						$xnObj = $xnMem->firstChild;
						$sObjName 		= '';
						$sObjAlias		= '';
						$sObjGUID 		= '';
						$sObjType 		= '';
						$sObjResType 	= '';
						$sObjStereo		= '';
						$sObjNType 		= '0';
						$sObjCreated    = '';
						$sObjModified   = '';
						$sObjAuthor		= '';
						$aStereotypes 	= array();
						$xnObjProps = $xnObj->getElementsByTagNameNS("*", "*");
						foreach ($xnObjProps as $xnObjProp)
						{
							GetXMLNodeValue($xnObjProp, 'dcterms:title', $sObjName);
							GetXMLNodeValue($xnObjProp, 'ss:alias', $sObjAlias);
							GetXMLNodeValue($xnObjProp, 'dcterms:created', $sObjCreated);
							GetXMLNodeValue($xnObjProp, 'dcterms:modified', $sObjModified);
							GetXMLNodeValue($xnObjProp, 'dcterms:identifier', $sObjGUID);
							GetXMLNodeValue($xnObjProp, 'ss:resourcetype', $sObjResType);
							GetXMLNodeValue($xnObjProp, 'ss:iconidentifier', $sObjNType);
							GetXMLNodeValue($xnObjProp, 'ss:ntype', $sObjNType);
							AddStereotypeToArray($xnObjProp, $aStereotypes);
							GetXMLNodeValue($xnObjProp, 'dcterms:type', $sObjType);
							GetAuthorFromXMLNode($xnObjProp, $sObjAuthor);
						}
						if (count($aStereotypes)>0)
						{
							$sObjStereo = getPrimaryStereotype($aStereotypes);
							$aRow['stereotypes'] = $aStereotypes;
						}
						$aRow['text'] 		= $sObjName;
						$aRow['alias'] 		= $sObjAlias;
						$aRow['modified']	= $sObjModified;
						$aRow['created'] 	= $sObjCreated;
						$aRow['guid'] 		= $sObjGUID;
						$aRow['type'] 		= $sObjType;
						$aRow['restype'] 	= $sObjResType;
						$aRow['stereotype'] = $sObjStereo;
						$aRow['ntype'] 		= $sObjNType;
						$aRow['imageurl'] 	= GetObjectImagePath($sObjType, $sObjResType, $sObjStereo, $sObjNType, 16);
						$aRow['author'] 	= $sObjAuthor;
						$aRows[] 			= $aRow;
					}
				}
			}
		}
	}
?>