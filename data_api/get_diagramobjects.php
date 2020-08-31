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
		   isset($sObjectGUID) === false ||
		   isset($_SESSION['authorized']) === false)
	{
		exit();
	}
	$sRootPath = dirname(dirname(__FILE__));
	require_once $sRootPath . '/globals.php';
	BuildOSLCConnectionString();
	$sOSLC_URL = $g_sOSLCString . 'diagramobjects/descendants/';
	if ( !strIsEmpty($sObjectGUID) )
	{
		$sOSLC_URL .= $sObjectGUID . '/';
	}
	$sParas = '';
	$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
	$sOSLC_URL 	.= $sParas;
	$xmlDoc = HTTPGetXML($sOSLC_URL);
	$sParentObjGUID = '';
	$sParentObjName = '';
	if ($xmlDoc != null)
	{
		$xnRoot = $xmlDoc->documentElement;
		$xnDesc = $xnRoot->firstChild;
		if ($xnDesc != null && $xnDesc->childNodes != null)
		{
			if ( GetOSLCError($xnDesc) )
			{
				$iHierarchyLevel = 1;
				foreach ($xnDesc->childNodes as $xnObjProp)
				{
					if ($xnObjProp->nodeName === "ss:diagramobjects")
					{
						$xnDCDesc = $xnObjProp->firstChild;
						foreach ($xnDCDesc->childNodes as $xnDCDescMem)
						{
							$sDCObjName			= '';
							$sDCObjType			= '';
							$sDCObjGUID			= '';
							$sDCObjResType		= '';
							$sDCObjNType		= '';
							$sDCObjNotes		= '';
							$sDCObjModified		= '';
							$sDCObjAuthor		= '';
							$sDCObjAlias		= '';
							$aDCObjStereotypes 	= array();
							if ($xnDCDescMem != null)
							{
								$xnDCRes = $xnDCDescMem->firstChild;
								if ($xnDCRes != null)
								{
									$bObjectAdded = false;
									foreach ($xnDCRes->childNodes as $xnDCObj)
									{
										GetXMLNodeValue($xnDCObj, 'dcterms:title', 		$sDCObjName);
										GetXMLNodeValue($xnDCObj, 'dcterms:type', 		$sDCObjType);
										GetXMLNodeValue($xnDCObj, 'dcterms:identifier', $sDCObjGUID);
										GetXMLNodeValue($xnDCObj, 'dcterms:description',$sDCObjNotes);
										GetXMLNodeValue($xnDCObj, 'dcterms:modified', 	$sDCObjModified);
										GetXMLNodeValue($xnDCObj, 'ss:resourcetype', 	$sDCObjResType);
										GetXMLNodeValue($xnDCObj, 'ss:iconidentifier', 	$sDCObjNType);
										GetXMLNodeValue($xnDCObj, 'ss:ntype', 			$sDCObjNType);
										AddStereotypeToArray($xnDCObj, 					$aDCObjStereotypes);
										GetAuthorFromXMLNode($xnDCObj, 					$sDCObjAuthor);
										GetXMLNodeValue($xnDCObj, 'ss:alias', 			$sDCObjAlias);
										if ($xnDCObj->nodeName === "ss:nestedresources")
										{
											AddObject2Array($aObjs, $iHierarchyLevel, $sDCObjGUID, $sDCObjName, $sDCObjType, $sDCObjResType, $aDCObjStereotypes, $sDCObjNType, $sDCObjNotes, $sDCObjModified, $sDCObjAuthor, $sDCObjAlias);
											ProcessChildDiagramObject($aObjs, $iHierarchyLevel, $xnDCObj);
											$bObjectAdded = true;
										}
									}
									if ( !$bObjectAdded )
									{
										AddObject2Array($aObjs, $iHierarchyLevel, $sDCObjGUID, $sDCObjName, $sDCObjType, $sDCObjResType, $aDCObjStereotypes, $sDCObjNType, $sDCObjNotes, $sDCObjModified, $sDCObjAuthor, $sDCObjAlias);
									}
								}
							}
						}
					}
				}
			}
		}
	}
	function AddObject2Array(&$aObjs, $iLevel, $sGUID, $sName, $sType, $sResType, $aStereotypes, $sNType, $sNotes, $sModified, $sAuthor, $sAlias)
	{
		$sStereotype = '';
		if (count($aStereotypes)> 0)
		{
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
		$aRow['imageurl'] 	= GetObjectImagePath($sType, $sResType, $sStereotype,$sNType, '16');
		$aObjs[] = $aRow;
	}
	function ProcessChildDiagramObject(&$aObjs, $iLevel, $xnNode)
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
		$aObjStereotypes 	= array();
		if ($xnNode != null)
		{
			$xnDesc = $xnNode->firstChild;
			$xnDescMem = $xnDesc->firstChild;
			if ($xnDescMem != null)
			{
				foreach ($xnDesc->childNodes as $xnMem)
				{
					if ($xnMem != null)
					{
						$xnRes = $xnMem->firstChild;
						$bObjectAdded = false;
						foreach ($xnRes->childNodes as $xnObjProp)
						{
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
							if ($xnObjProp->nodeName === "ss:nestedresources")
							{
								AddObject2Array($aObjs, $iLevel, $sObjGUID, $sObjName, $sObjType, $sObjResType, $aObjStereotypes, $sObjNType, $sObjNotes, $sObjModified, $sObjAuthor, $sObjAlias);
								ProcessChildDiagramObject($aObjs, $iLevel, $xnObjProp);
								$bObjectAdded = true;
							}
						}
						if ( !$bObjectAdded )
						{
							AddObject2Array($aObjs, $iLevel, $sObjGUID, $sObjName, $sObjType, $sObjResType, $aObjStereotypes, $sObjNType, $sObjNotes, $sObjModified, $sObjAuthor, $sObjAlias);
						}
					}
				}
			}
		}
	}
?>