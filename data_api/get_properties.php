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
	if  ( (!isset($webea_page_parent_mainview) && $_SERVER["REQUEST_METHOD"] !== "POST" ) ||
		   isset($sObjectGUID) === false ||
		   isset($_SESSION['authorized']) === false)
	{
		exit();
	}
	if ( !strIsEmpty($sObjectGUID) )
	{
		BuildOSLCConnectionString();
		$sOSLC_URL 	= $g_sOSLCString . "completeresource/";
		$sOSLC_URL 	= $sOSLC_URL . $sObjectGUID . "/";
		$sParas = '';
		$sLoginGUID = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
		$sReviewSession = SafeGetInternalArrayParameter($_SESSION, 'review_session');
		AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
		if ( !strIsEmpty($sResourceFeaturesFilter) )
		{
			AddURLParameter($sParas, 'features', $sResourceFeaturesFilter);
		}
		$sOSLC_URL 	.= $sParas;
		if ( !strIsEmpty($sOSLC_URL) )
		{
			$xmlDoc = HTTPGetXML($sOSLC_URL);
			if ($xmlDoc != null)
			{
				$aStereotypes = array();
				$xnRoot = $xmlDoc->documentElement;
				$xnObj = $xnRoot->firstChild;
				if ($xnObj != null)
				{
					if ( GetOSLCError($xnObj) )
					{
						$aCommonProps['haschild'] = 'false';
						foreach ($xnObj->childNodes as $xnObjProp)
						{
							GetXMLNodeValue($xnObjProp, 'dcterms:identifier', 	$aCommonProps['guid']);
							GetXMLNodeValue($xnObjProp, 'ss:alias',		 		$aCommonProps['alias']);
							GetXMLNodeValue($xnObjProp, 'dcterms:title', 		$aCommonProps['name']);
							GetXMLNodeValue($xnObjProp, 'dcterms:type', 		$aCommonProps['type']);
							GetXMLNodeValue($xnObjProp, 'dcterms:created', 		$aCommonProps['created']);
							GetXMLNodeValue($xnObjProp, 'dcterms:modified', 	$aCommonProps['modified']);
							GetXMLNodeValue($xnObjProp, 'ss:version', 			$aCommonProps['version']);
							GetXMLNodeValue($xnObjProp, 'ss:status', 			$aCommonProps['status']);
							GetXMLNodeValue($xnObjProp, 'ss:phase', 			$aCommonProps['phase']);
							GetXMLNodeValue($xnObjProp, 'dcterms:description', 	$aCommonProps['notes']);
							GetXMLNodeValue($xnObjProp, 'ss:complexity', 		$aCommonProps['complexity']);
							GetXMLNodeValue($xnObjProp, 'ss:resourcetype', 		$aCommonProps['restype']);
							GetXMLNodeValue($xnObjProp, 'ss:iconidentifier', 	$aCommonProps['ntype']);
							GetXMLNodeValue($xnObjProp, 'ss:ntype', 			$aCommonProps['ntype']);
							GetXMLNodeValue($xnObjProp, 'ss:sbpitypeid', 		$sSbpiid);
							if ($sSbpiid !== null)
							{
								$aCommonProps['ntype'] = strval($sSbpiid+1);
							}
							GetXMLNodeValue($xnObjProp, 'ss:sbpitypename', 		$aCommonProps['sbpiname']);
							AddStereotypeToArray($xnObjProp, 					$aStereotypes);
							GetAuthorFromXMLNode($xnObjProp, $aCommonProps['author']);
							GetXMLNodeValue($xnObjProp, 'ss:locked', 			$aCommonProps['locked']);
							GetXMLNodeValue($xnObjProp, 'ss:locktype',	 		$aCommonProps['lockedtype']);
							GetXMLNodeValue($xnObjProp, 'ss:parentresourceidentifier',$aParentInfo['guid']);
							if ($xnObjProp->nodeName === 'ss:nestedresources')
							{
								$aCommonProps['haschild'] = 'true';
							}
							elseif ($xnObjProp->nodeName === 'ss:imageasset')
							{
								$aCommonProps['imageasset'] = $xnObjProp->getAttribute('rdf:resource');
							}
							elseif ($xnObjProp->nodeName === 'ss:parentresource')
							{
								$aParentInfo = array();
								$xnClassDesc = GetXMLFirstChild($xnObjProp);
								if ($xnClassDesc != null && $xnClassDesc->childNodes != null)
								{
									$xnClassMem = GetXMLFirstChild($xnClassDesc);
									if ($xnClassMem != null && $xnClassMem->childNodes != null)
									{
										$aPStereotypes 	= array();
										$xnClassOSLCAM = GetXMLFirstChild($xnClassMem);
										foreach ($xnClassOSLCAM->childNodes as $xnNodeC)
										{
											GetXMLNodeValue($xnNodeC, 'dcterms:title', 		$aParentInfo['text']);
											GetXMLNodeValue($xnNodeC, 'dcterms:identifier', $aParentInfo['guid']);
											AddStereotypeToArray($xnNodeC, $aPStereotypes);
											GetXMLNodeValue($xnNodeC, 'dcterms:type', 	$aParentInfo['type']);
											GetXMLNodeValue($xnNodeC, 'ss:iconidentifier', 	$aParentInfo['ntype']);
											GetXMLNodeValue($xnNodeC, 'ss:ntype',		 	$aParentInfo['ntype']);
											GetXMLNodeValue($xnNodeC, 'ss:resourcetype', 	$aParentInfo['restype']);
										}
										if (count($aPStereotypes)>0)
										{
											$aParentInfo['stereotype'] = getPrimaryStereotype($aPStereotypes);
											$aParentInfo['stereotypes'] = $aPStereotypes;
										}
										$aParentInfo['imageurl'] = GetObjectImagePath($aParentInfo['type'], $aParentInfo['restype'], SafeGetArrayItem1Dim($aParentInfo, 'stereotype'), $aParentInfo['ntype'], 16);
									}
								}
							}
							elseif ($xnObjProp->nodeName === 'ss:compositeresource')
							{
								$aCompositeInfo = array();
								$xnClassDesc = GetXMLFirstChild($xnObjProp);
								if ($xnClassDesc != null && $xnClassDesc->childNodes != null)
								{
									$xnClassMem = GetXMLFirstChild($xnClassDesc);
									if ($xnClassMem != null && $xnClassMem->childNodes != null)
									{
										$xnClassOSLCAM = GetXMLFirstChild($xnClassMem);
										foreach ($xnClassOSLCAM->childNodes as $xnNodeC)
										{
											GetXMLNodeValue($xnNodeC, 'dcterms:title', 		$aCompositeInfo['text']);
											GetXMLNodeValue($xnNodeC, 'dcterms:identifier', $aCompositeInfo['guid']);
											GetXMLNodeValue($xnNodeC, 'dcterms:type', 	$aCompositeInfo['type']);
											GetXMLNodeValue($xnNodeC, 'ss:resourcetype', 	$aCompositeInfo['restype']);
										}
									$aCompositeInfo['imageurl'] = GetObjectImagePath($aCompositeInfo['type'], $aCompositeInfo['restype'], '', '', 16);
									}
								}
							}
							elseif ($xnObjProp->nodeName === 'ss:classifierresource')
							{
								$xnClassDesc = GetXMLFirstChild($xnObjProp);
								if ($xnClassDesc != null && $xnClassDesc->childNodes != null)
								{
									$xnClassMem = GetXMLFirstChild($xnClassDesc);
									if ($xnClassMem != null && $xnClassMem->childNodes != null)
									{
										$aCStereotypes 	= array();
										$xnClassOSLCAM = GetXMLFirstChild($xnClassMem);
										foreach ($xnClassOSLCAM->childNodes as $xnNodeC)
										{
											GetXMLNodeValue($xnNodeC, 'dcterms:title', 		$aCommonProps['classname']);
											GetXMLNodeValue($xnNodeC, 'dcterms:identifier', $aCommonProps['classguid']);
											AddStereotypeToArray($xnNodeC, $aCStereotypes);
											GetXMLNodeValue($xnNodeC, 'ss:classifiername', 	$aCommonProps['classtype']);
											GetXMLNodeValue($xnNodeC, 'ss:iconidentifier', 	$aCommonProps['classntype']);
											GetXMLNodeValue($xnNodeC, 'ss:ntype',		 	$aCommonProps['classntype']);
											GetXMLNodeValue($xnNodeC, 'ss:resourcetype', 	$aCommonProps['classrestype']);
										}
										if (count($aCStereotypes)>0)
										{
											$aCommonProps['classstereotype'] = getPrimaryStereotype($aCStereotypes);
											$aCommonProps['classstereotypes'] = $aCStereotypes;
										}
										$aCommonProps['classimageurl'] = GetObjectImagePath($aCommonProps['classtype'], $aCommonProps['classrestype'], SafeGetArrayItem1Dim($aCommonProps, 'classstereotype'), $aCommonProps['classntype'], 16);
									}
								}
							}
							elseif ($xnObjProp->nodeName === "ss:features")
							{
								$xnDescription = $xnObjProp->firstChild;
								if ($xnDescription != null)
								{
									foreach ($xnDescription->childNodes as $xnFeature)
									{
										if ($xnFeature->nodeName === "ss:attributes")
										{
											$aAttributes = getAttributes($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:operations")
										{
											$aOperations = getOperations($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:constraints")
										{
											$aConstraints = getConstraints($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:taggedvalues")
										{
											$aTaggedValues = getTaggedValues($xnFeature, $aExternalData);
										}
										elseif ($xnFeature->nodeName === "ss:discussions")
										{
											$aDiscussions = getDiscussions($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:reviewdiscussions")
										{
											$aReviewDiscuss = getReviewDiscussions($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:reviewdiagrams")
										{
											$aReviewDiagrams = getReviewDiagrams($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:reviewnodiscussions")
										{
											$aReviewNoDiscuss = getReviewNoDiscussions($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:tests")
										{
											$aTests = getTests($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:resourceallocations")
										{
											$aResAllocs = getResourceAlloc($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:scenarios")
										{
											$aScenarios = getScenarios($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:requirements")
										{
											$aRequirements = getRequirements($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:runstates")
										{
											$aRunStates = getRunStates($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:usages")
										{
											$aUsages = getUsage($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:linkeddocument")
										{
											$aDocument = getDocument($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:changes")
										{
											$aChanges = getChanges($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:defects")
										{
											$aDefects = getDefects($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:issues")
										{
											$aIssues = getIssues($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:tasks")
										{
											$aTasks = getTasks($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:events")
										{
											$aEvents = getEvents($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:decisions")
										{
											$aDecisions = getDecisions($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:efforts")
										{
											$aEfforts = getEfforts($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:risks")
										{
											$aRisks = getRisks($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:metrics")
										{
											$aMetrics = getMetrics($xnFeature);
										}
										elseif ($xnFeature->nodeName === "ss:diagramimage")
										{
											$xnImageDesc = GetXMLFirstChild($xnFeature);
											if ($xnImageDesc != null && $xnImageDesc->childNodes != null)
											{
												foreach ($xnImageDesc->childNodes as $xnImageC)
												{
													GetXMLNodeValue($xnImageC, 'dcterms:created', $aCommonProps['generated']);
													GetXMLNodeValue($xnImageC, 'ss:imageinsync', $aCommonProps['imageinsync']);
												}
											}
										}
									}
								}
							}
							elseif ($xnObjProp->nodeName === "ss:linkedresources")
							{
								$aRelationships = getRelationshipsPart1($xnObjProp);
							}
						}
						if ((count($aDiscussions)> 0) || (count($aReviewDiscuss)> 0))
						{
							$aAvatars = getAvatars($xnObj);
						}
						if (count($aRelationships)> 0)
						{
							$xnObj = $xnObj->nextSibling;
							getRelationshipsPart2($xnObj, $aRelationships);
						}
						$aCommonProps['stereotype'] = '';
						if (count($aStereotypes)> 0)
						{
							$aCommonProps['stereotype'] = getPrimaryStereotype($aStereotypes);
							$aCommonProps['stereotypes'] = $aStereotypes;
						}
						if ( substr($aParentInfo['guid'],0,4)==='mr_{'  && isset($aCommonProps['ntype'])===false )
						{
							$aCommonProps['ntype'] = '6';
						}
						if ( $aCommonProps['type']==='Package' &&  substr($aParentInfo['guid'],0,4)!=='mr_{' && isset($aCommonProps['ntype'])===true )
						{
							$aCommonProps['ntype'] = '';
						}
						$aCommonProps['imageurl'] = GetObjectImagePath($aCommonProps['type'], $aCommonProps['restype'], SafeGetArrayItem1Dim($aCommonProps, 'stereotype'), $aCommonProps['ntype'], '64');
						if ( strlen($aCommonProps['guid']) > 3)
						{
							$aCommonProps['guid_raw'] = substr($aCommonProps['guid'], 3);
						}
						if (isset($aCompositeInfo))
						{
							$aCommonProps['composite_info'] = $aCompositeInfo;
						}
						if ( !strIsEmpty(SafeGetArrayItem1Dim($aCommonProps, 'imageasset')) )
						{
							$aCommonProps['imageasset'] = TrimInternalURL($aCommonProps['imageasset'], '/iaimage/');
							$aCommonProps['imagepreview'] = '';
							if ( $aCommonProps['type'] === 'Artifact' && $aCommonProps['stereotype'] === 'Image' )
							{
								$sImageAssetGUID = SafeGetArrayItem1Dim($aCommonProps, 'imageasset');
								$webea_page_parent_getprops = true;
								include('./data_api/get_model_image.php');
								unset($webea_page_parent_getprops);
								$aCommonProps['imagepreview'] = $sImageBin;
							}
						}
					}
				}
			}
			else
			{
				return null;
			}
		}
	}
	function getConstraints($xnConstraints)
	{
		$aConstraints = array();
		if ($xnConstraints->nodeName !== 'ss:constraints')
		{
			return $aConstraints;
		}
		$xnDesc = $xnConstraints->firstChild;
		if ($xnDesc != null)
		{
			$iItemNo = 1;
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnConstraint = $xnMembers->firstChild;
				$sName			= '';
				$sGUID			= '';
				$sNotes			= '';
				$sStatus 		= '';
				$sType			= '';
				$sParentGUID	= '';
				foreach ($xnConstraint->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'ss:status', 			$sStatus);
					GetXMLNodeValue($xn, 'dcterms:type', 		$sType);
					GetXMLNodeValue($xn, 'ss:resourceidentifier',$sParentGUID);
				}
				if ( !strIsEmpty($sName) || !strIsEmpty($sType) || !strIsEmpty($sGUID) || !strIsEmpty($sNotes) )
				{
					$aRow			= array();
					$aRow['name']	= $sName;
					$aRow['oslcguid']= $sGUID;
					$aRow['guid']	= BuildWebUID('cn', $sParentGUID, $iItemNo);
					$aRow['notes']	= $sNotes;
					$aRow['type']	= $sType;
					$aRow['status']	= $sStatus;
					$aConstraints[] = $aRow;
				}
				$iItemNo += 1;
			}
		}
		return $aConstraints;
	}
	function getTaggedValues($xnTaggedValues, &$aExternalData)
	{
		$aTaggedValues	= array();
		$aExternalData	= array();
		if ($xnTaggedValues->nodeName !== 'ss:taggedvalues')
		{
			return $aTaggedValues;
		}
		$xnDesc = $xnTaggedValues->firstChild;
		if ($xnDesc != null)
		{
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnTV = $xnMembers->firstChild;
				$sName	= '';
				$sGroup = '';
				$sGUID	= '';
				$sValue	= '';
				$sNotes	= '';
				$aRefResource 	= '';
				$sExtSrcType	= '';
				$sExtSrcFullUrl = '';
				foreach ($xnTV->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'ss:group', 			$sGroup);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'ss:value', 			$sValue);
					GetXMLNodeValue($xn, 'ss:type', 			$sType);
					GetXMLNodeValueAttr($xn, 'ss:sbpintegrate', 'ss:type', $sExtSrcType);
					GetXMLNodeValueAttr($xn, 'ss:sbpintegrate', 'rdf:resource', $sExtSrcFullUrl);
					if ($xn->nodeName === 'ss:referredresource')
					{
						$aRefResource = array();
						$xnClDesc = $xn->firstChild;
						foreach ($xnClDesc->childNodes as $xnRefMembers)
						{
							$xnRes = $xnRefMembers->firstChild;
							$sRefName			= '';
							$sRefGUID			= '';
							$sRefType			= '';
							$sRefResourceType	= '';
							$sRefStereotype		= '';
							$sRefNtype			= '';
							foreach ($xnRes->childNodes as $xnCl)
							{
								GetXMLNodeValue($xnCl, 'dcterms:title', 		$sRefName);
								GetXMLNodeValue($xnCl, 'dcterms:identifier', 	$sRefGUID);
								GetXMLNodeValue($xnCl, 'dcterms:type', 			$sRefType);
								GetXMLNodeValue($xnCl, 'ss:resourcetype', 		$sRefResourceType);
								GetXMLNodeValue($xnCl, 'ss:stereotype', 		$sRefStereotype);
								GetXMLNodeValue($xnCl, 'ss:ntype', 				$sRefNtype);
							}
							$sRefImageURL = GetObjectImagePath($sRefType, $sRefResourceType, $sRefStereotype, $sRefNtype, 16);
							if ( !strIsEmpty($sRefName) || !strIsEmpty($sRefGUID) || !strIsEmpty($sRefType) || !strIsEmpty($sRefResourceType))
							{
								$aRow			= array();
								$aRow['name']	= $sRefName;
								$aRow['guid']	= $sRefGUID;
								$aRow['type']	= $sRefType;
								$aRow['imageurl'] = $sRefImageURL;
								$aRow['restype']	= $sRefResourceType;
								$aRefResource[] = $aRow;
							}
						}
					}
				}
				if ( !strIsEmpty($sName) || !strIsEmpty($sValue) || !strIsEmpty($sGUID) || !strIsEmpty($sNotes) || !strIsEmpty($sGroup))
				{
					$aRow			= array();
					$aRow['name']	= $sName;
					$aRow['group']	= $sGroup;
					$aRow['guid']	= $sGUID;
					$aRow['notes']	= $sNotes;
					$aRow['value']	= $sValue;
					$aRow['type'] 	= $sType;
					if ( $sName !== 'SSIntegrate' && $sName !== 'SSIntegrateDate')
					{
						$aRow['referredresource']	= $aRefResource;
						$aTaggedValues[] = $aRow;
					}
					else
					{
						$aRow['extsrctype']	= $sExtSrcType;
						$aRow['extsrcfullurl']	= $sExtSrcFullUrl;
						$aExternalData[] = $aRow;
					}
				}
			}
		}
		return $aTaggedValues;
	}
	function getAttributes($xnAttributes)
	{
		$aAttributes = array();
		if ($xnAttributes->nodeName !== "ss:attributes")
		{
			return $aAttributes;
		}
		$xnDesc = $xnAttributes->firstChild;
		if ($xnDesc != null)
		{
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnAttribute = $xnMembers->firstChild;
				$aTaggedValues	= array();
				$sName			= '';
				$sType			= '';
				$sNotes			= '';
				$sStereotype	= '';
				$sAlias 		= '';
				$sDefault	 	= '';
				$sScope			= '';
				$sIsStatic	 	= 'False';
				$sContainment 	= 'False';
				$sIsCollection 	= 'False';
				$sIsOrdered	 	= 'False';
				$sIsConst	 	= 'False';
				$sAllowDups	 	= 'False';
				$sLowerBound 	= '1';
				$sUpperBound 	= '1';
				$sClassName		= '';
				$sClassGUID		= '';
				$sClassStereotype = '';
				$sClassType		= '';
				$sClassNType	= '';
				$sClassResType 	= '';
				$aStereotypes 	= array();
				foreach ($xnAttribute->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					AddStereotypeToArray($xn, $aStereotypes);
					GetXMLNodeValue($xn, 'ss:classifiername', 	$sType);
					GetXMLNodeValue($xn, 'ss:defaultvalue', 	$sDefault);
					GetXMLNodeValue($xn, 'ss:alias', 			$sAlias);
					GetXMLNodeValue($xn, 'ss:scope', 			$sScope);
					GetXMLNodeValue($xn, 'ss:containment',		$sContainment);
					GetXMLNodeBoolValue($xn, 'ss:isstatic',		$sIsStatic);
					GetXMLNodeBoolValue($xn, 'ss:iscollection',	$sIsCollection);
					GetXMLNodeBoolValue($xn, 'ss:isordered',	$sIsOrdered);
					GetXMLNodeBoolValue($xn, 'ss:isconst',		$sIsConst);
					GetXMLNodeBoolValue($xn, 'ss:allowduplicates',$sAllowDups);
					GetXMLNodeValue($xn, 'ss:lowerbound',		$sLowerBound);
					GetXMLNodeValue($xn, 'ss:upperbound',		$sUpperBound);
					if ($xn->nodeName==="ss:classifierresource")
					{
						$xnClassDesc = GetXMLFirstChild($xn);
						if ($xnClassDesc != null && $xnClassDesc->childNodes != null)
						{
							$xnClassMem = GetXMLFirstChild($xnClassDesc);
							if ($xnClassMem != null && $xnClassMem->childNodes != null)
							{
								$aCStereotypes 	= array();
								$xnClassOSLCAM = GetXMLFirstChild($xnClassMem);
								foreach ($xnClassOSLCAM->childNodes as $xnNodeC)
								{
									GetXMLNodeValue($xnNodeC, 'dcterms:title', 		$sClassName);
									GetXMLNodeValue($xnNodeC, 'dcterms:identifier', $sClassGUID);
									AddStereotypeToArray($xnNodeC, $aCStereotypes);
									GetXMLNodeValue($xnNodeC, 'ss:classifiername', 	$sClassType);
									GetXMLNodeValue($xnNodeC, 'ss:ntype',		 	$sClassNType);
									GetXMLNodeValue($xnNodeC, 'ss:resourcetype', 	$sClassResType);
								}
								if (count($aCStereotypes)>0)
								{
									$sClassStereotype = getPrimaryStereotype($aCStereotypes);
								}
							}
						}
					}
					elseif ($xn->nodeName==="ss:taggedvalues")
					{
						$xnNodeC = $xn->firstChild;
						foreach ($xnNodeC->childNodes as $xnTVMembers)
						{
							$sTVName	= '';
							$sTVValue	= '';
							$sTVNotes	= '';
							$sTVGUID	= '';
							$xnTVNode = $xnTVMembers->firstChild;
							foreach ($xnTVNode->childNodes as $xnTVItem)
							{
								GetXMLNodeValue($xnTVItem, 'dcterms:title', $sTVName);
								GetXMLNodeValue($xnTVItem, 'ss:value', $sTVValue);
								GetXMLNodeValue($xnTVItem, 'dcterms:identifier', $sTVGUID);
								GetXMLNodeValue($xnTVItem, 'dcterms:description', $sTVNotes);
							}
							if ( !strIsEmpty($sTVName) || !strIsEmpty($sTVValue) || !strIsEmpty($sTVGUID) || !strIsEmpty($sTVNotes) )
							{
								$aRow			= array();
								$aRow['name']	= $sTVName;
								$aRow['value']	= $sTVValue;
								$aRow['notes']	= $sTVNotes;
								$aRow['guid']	= $sTVGUID;
								$aTaggedValues[] = $aRow;
							}
						}
					}
				}
				if ( !strIsEmpty($sName) || !strIsEmpty($sType) || !strIsEmpty($sGUID) || !strIsEmpty($sNotes) || !strIsEmpty($sStereotype) || !strIsEmpty($sAlias) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['guid']		= $sGUID;
					$aRow['notes']		= $sNotes;
					$aRow['type']		= $sType;
					if (count($aStereotypes)>0)
					{
						$aRow['sterotype'] = getPrimaryStereotype($aStereotypes);
						$aRow['stereotypes'] = $aStereotypes;
					}
					$aRow['default']	= $sDefault;
					$aRow['alias']		= $sAlias;
					$aRow['scope']		= $sScope;
					$aRow['isstatic']	= $sIsStatic;
					$aRow['containment']= $sContainment;
					$aRow['iscollection']= $sIsCollection;
					$aRow['isordered']	= $sIsOrdered;
					$aRow['isconst']	= $sIsConst;
					$aRow['allowdups']	= $sAllowDups;
					$aRow['multiplicity']= GetMultiplicityString($sLowerBound, $sUpperBound);
					$aRow['classname']	= $sClassName;
					$aRow['classguid']	= $sClassGUID;
					$aRow['classstereo']= $sClassStereotype;
					$aRow['classtype']	= $sClassType;
					$aRow['classntype']	= $sClassResType;
					$aRow['classimageurl']= GetObjectImagePath($sClassType, $sClassResType, $sClassStereotype, $sClassNType, 16);
					if (count($aTaggedValues)> 0)
					{
						$aRow["taggedvalues"]= $aTaggedValues;
					}
					$aAttributes[] 		= $aRow;
				}
			}
		}
		return $aAttributes;
	}
	function getOperations($xnOperations)
	{
		$aOperations = array();
		if ($xnOperations->nodeName !== "ss:operations")
		{
			return $aOperations;
		}
		$xnDesc = $xnOperations->firstChild;
		if ($xnDesc != null)
		{
			foreach ($xnDesc->childNodes as $sMembers)
			{
				$aParameters	= array();
				$aTaggedValues	= array();
				$sName			= '';
				$sType			= '';
				$sNotes			= '';
				$sGUID 			= '';
				$sStereotype	= '';
				$sAlias		 	= '';
				$sScope 		= '';
				$sParameters 	= '';
				$sClassifierName	= '';
				$sClassifierGUID	= '';
				$sClassifierType	= '';
				$sClassifierResType = '';
				$sClassifierNType 	= '';
				$sClassifierStereo 	= '';
				$sClassifierImageURL= '';
				$sParametersFormatted = '';
				$sIsStatic	 	= 'False';
				$sIsAbstract 	= 'False';
				$sIsReturnArray	= 'False';
				$sIsQuery	 	= 'False';
				$sIsSynch	 	= 'False';
				$sIsConst	 	= 'False';
				$sIsPure	 	= 'False';
				$aStereotypes 	= array();
				$xnOperation = $sMembers->firstChild;
				foreach ($xnOperation->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					AddStereotypeToArray($xn, $aStereotypes);
					GetXMLNodeValue($xn, 'ss:classifiername', 	$sType);
					GetXMLNodeValue($xn, 'ss:alias', 			$sAlias);
					GetXMLNodeValue($xn, 'ss:scope', 			$sScope);
					GetXMLNodeBoolValue($xn, 'ss:isstatic',		$sIsStatic);
					GetXMLNodeBoolValue($xn, 'ss:isabstract',	$sIsAbstract);
					GetXMLNodeBoolValue($xn, 'ss:isreturnarray',$sIsReturnArray);
					GetXMLNodeBoolValue($xn, 'ss:isquery',		$sIsQuery);
					GetXMLNodeBoolValue($xn, 'ss:issynch',		$sIsSynch);
					GetXMLNodeBoolValue($xn, 'ss:isconst',		$sIsConst);
					GetXMLNodeBoolValue($xn, 'ss:ispure',		$sIsPure);
					if ($xn->nodeName==='ss:classifierresource')
					{
						$aCStereotypes 	= array();
						$xnDesc = $xn->firstChild;
						foreach ($xnDesc->childNodes as $xnMem)
						{
							$xnNode = $xnMem->firstChild;
							foreach ($xnNode->childNodes as $xnItem)
							{
								GetXMLNodeValue($xnItem, 'dcterms:title', 		$sClassifierName);
								GetXMLNodeValue($xnItem, 'dcterms:identifier', 	$sClassifierGUID);
								GetXMLNodeValue($xnItem, 'dcterms:type', 		$sClassifierType);
								GetXMLNodeValue($xnItem, 'dcterms:resourcetype', $sClassifierResType);
								GetXMLNodeValue($xnItem, 'ss:ntype',		 	$sClassifierNType);
								AddStereotypeToArray($xnItem, $aCStereotypes);
							}
						}
						if (count($aCStereotypes)>0)
						{
							$sClassifierStereo = getPrimaryStereotype($aCStereotypes);
						}
						$sClassifierImageURL = GetObjectImagePath($sClassifierType, $sClassifierResType, $sClassifierStereo, $sClassifierNType, 16);
					}
					elseif ($xn->nodeName==='ss:parameters')
					{
						$xnNodeC = $xn->firstChild;
						foreach ($xnNodeC->childNodes as $xnParaMembers)
						{
							$sParaName		= '';
							$sParaType		= '';
							$sParaGUID		= '';
							$sParaNotes		= '';
							$sParaDefault	= '';
							$sParaDirection	= '';
							$sClassName		= '';
							$sClassGUID		= '';
							$sClassStereotype = '';
							$sClassType		= '';
							$sClassNType	= '';
							$sClassResType 	= '';
							$xnParaNode = $xnParaMembers->firstChild;
							foreach ($xnParaNode->childNodes as $xnParaItem)
							{
								GetXMLNodeValue($xnParaItem, 'dcterms:title', $sParaName);
								GetXMLNodeValue($xnParaItem, 'dcterms:identifier', $sParaGUID);
								GetXMLNodeValue($xnParaItem, 'dcterms:description', $sParaNotes);
								GetXMLNodeValue($xnParaItem, 'ss:defaultvalue', $sParaDefault);
								GetXMLNodeValue($xnParaItem, 'ss:classifiername', $sParaType);
								GetXMLNodeValue($xnParaItem, 'ss:paramdirection', $sParaDirection);
								if ($xnParaItem->nodeName==="ss:classifierresource")
								{
									$xnClassDesc = GetXMLFirstChild($xnParaItem);
									if ($xnClassDesc != null && $xnClassDesc->childNodes != null)
									{
										$xnClassMem = GetXMLFirstChild($xnClassDesc);
										if ($xnClassMem != null && $xnClassMem->childNodes != null)
										{
											$aCStereotypes 	= array();
											$xnClassOSLCAM = GetXMLFirstChild($xnClassMem);
											foreach ($xnClassOSLCAM->childNodes as $xnNodeC)
											{
												GetXMLNodeValue($xnNodeC, 'dcterms:title', 		$sClassName);
												GetXMLNodeValue($xnNodeC, 'dcterms:identifier', $sClassGUID);
												AddStereotypeToArray($xnNodeC, $aCStereotypes);
												GetXMLNodeValue($xnNodeC, 'ss:classifiername', 	$sClassType);
												GetXMLNodeValue($xnNodeC, 'ss:ntype',		 	$sClassNType);
												GetXMLNodeValue($xnNodeC, 'ss:resourcetype', 	$sClassResType);
											}
											if (count($aCStereotypes)>0)
											{
												$sClassStereotype = getPrimaryStereotype($aCStereotypes);
											}
										}
									}
								}
							}
							if ( !strIsEmpty($sParaName) &&  !strIsEmpty($sParaType) )
							{
								$sParaName = htmlspecialchars($sParaName);
								if ( !strIsEmpty($sClassGUID) )
								{
									$sClassImageURL= GetObjectImagePath($sClassType, $sClassResType, $sClassStereotype, $sClassNType, 16);
									$sParaType  = '<a class="w3-link" onclick="load_object(\'' . $sClassGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sClassName) . '\',\'' . $sClassImageURL . '\')">';
									$sParaType .= '<img src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sClassImageURL) . '" alt="" style="float: none;">&nbsp;' . htmlspecialchars($sClassName) . '</a>';
								}
								$sCurrentPara = $sParaName . ":" . $sParaType;
								if ( !strIsEmpty($sParaDefault) )
									$sCurrentPara .= "=" . $sParaDefault;
								$sCurrentPara .= ", ";
								$sParameters .= $sCurrentPara;
							}
							elseif ( !strIsEmpty($sParaName) &&  strIsEmpty($sParaType) )
							{
								$sParameters .= $sParaName . ", ";
							}
							$aRow			= array();
							$aRow['name']	= $sParaName;
							$aRow['guid']	= $sParaGUID;
							$aRow['type']	= $sParaType;
							$aRow['default']= $sParaDefault;
							$aRow['notes']	= $sParaNotes;
							$aRow['direction'] = $sParaDirection;
							$aRow['classname']	= $sClassName;
							$aRow['classguid']	= $sClassGUID;
							$aRow['classstereo']= $sClassStereotype;
							$aRow['classtype']	= $sClassType;
							$aRow['classntype']	= $sClassResType;
							$aRow['classimageurl']= GetObjectImagePath($sClassType, $sClassResType, $sClassStereotype, $sClassNType, 16);
							$aParameters[]  = $aRow;
						}
					}
					elseif ($xn->nodeName==="ss:taggedvalues")
					{
						$xnNodeC = $xn->firstChild;
						foreach ($xnNodeC->childNodes as $xnTVMembers)
						{
							$sTVName	= '';
							$sTVValue	= '';
							$sTVNotes	= '';
							$sTVGUID	= '';
							$xnTVNode = $xnTVMembers->firstChild;
							foreach ($xnTVNode->childNodes as $xnTVItem)
							{
								GetXMLNodeValue($xnTVItem, 'dcterms:title', $sTVName);
								GetXMLNodeValue($xnTVItem, 'ss:value', $sTVValue);
								GetXMLNodeValue($xnTVItem, 'dcterms:identifier', $sTVGUID);
								GetXMLNodeValue($xnTVItem, 'dcterms:description', $sTVNotes);
							}
							if ( !strIsEmpty($sTVName) || !strIsEmpty($sTVValue) || !strIsEmpty($sTVGUID) || !strIsEmpty($sTVNotes) )
							{
								$aRow			= array();
								$aRow['name']	= $sTVName;
								$aRow['value']	= $sTVValue;
								$aRow['notes']	= $sTVNotes;
								$aRow['guid']	= $sTVGUID;
								$aTaggedValues[] = $aRow;
							}
						}
					}
				}
				$sParameters = rtrim($sParameters, ', ');
				if ( !strIsEmpty($sName) || !strIsEmpty($sType) || !strIsEmpty($sGUID) || !strIsEmpty($sNotes) || !strIsEmpty($sStereotype) || !strIsEmpty($sAlias) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['guid']		= $sGUID;
					$aRow['notes']		= $sNotes;
					$aRow['classifier']	= $sType;
					if (count($aStereotypes)>0)
					{
						$aRow['sterotype'] = getPrimaryStereotype($aStereotypes);
						$aRow['stereotypes'] = $aStereotypes;
					}
					$aRow['alias']		= $sAlias;
					$aRow['classifiername']	= $sClassifierName;
					$aRow['classifierguid']	= $sClassifierGUID;
					$aRow['classifierimageurl']	= $sClassifierImageURL;
					$aRow['parastring']	= $sParameters;
					$aRow['parastringformat'] = $sParametersFormatted;
					$aRow['scope']		= $sScope;
					$aRow['isstatic']	= $sIsStatic;
					$aRow['isabstract']	= $sIsAbstract;
					$aRow['isreturnarray'] = $sIsReturnArray;
					$aRow['isquery']	= $sIsQuery;
					$aRow['issynch']	= $sIsSynch;
					$aRow['isconst']	= $sIsConst;
					$aRow['ispure']		= $sIsPure;
					$aRow['parameters'] = $aParameters;
					if (count($aTaggedValues)> 0)
					{
						$aRow['taggedvalues']= $aTaggedValues;
					}
					$aOperations[] 		= $aRow;
				}
			}
		}
		return $aOperations;
	}
	function getTests($xnTests)
	{
		$aTests = array();
		if ($xnTests->nodeName !== "ss:tests")
		{
			return $aTests;
		}
		$xnDesc = $xnTests->firstChild;
		if ($xnDesc != null)
		{
			$iItemNo = 1;
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnTest = $xnMembers->firstChild;
				$sName			= '';
				$sType			= '';
				$sClassType		= '';
				$sStatus		= '';
				$sNotes			= '';
				$sInput			= '';
				$sAcCrit		= '';
				$sResults		= '';
				$sGUID			= '';
				$sRunBy			= '';
				$sChkdBy		= '';
				$sLrun			= '';
				$sParentGUID	= '';
				foreach ($xnTest->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:type', 		$sType);
					GetXMLNodeValue($xn, 'ss:classtype', 		$sClassType);
					GetXMLNodeValue($xn, 'ss:status', 			$sStatus);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'ss:input', 			$sInput);
					GetXMLNodeValue($xn, 'ss:acceptancecriteria', $sAcCrit);
					GetXMLNodeValue($xn, 'ss:results', 			$sResults);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'ss:resourceidentifier', $sParentGUID);
					if ($xn->nodeName==="ss:runby")
					{
						$xnNode = $xn->firstChild;
						$xnNode = $xnNode->firstChild;
						$sRunBy = $xnNode->nodeValue;
					}
					elseif ($xn->nodeName==="ss:checkedby")
					{
						$xnNode  = $xn->firstChild;
						$xnNode  = $xnNode->firstChild;
						$sChkdBy = $xnNode->nodeValue;
					}
					elseif ($xn->nodeName==="ss:lastrun")
					{
						$sLrun = $xn->nodeValue;
						$sLrun = RemoveTime($sLrun);
					}
				}
				if ( !strIsEmpty($sName) || !strIsEmpty($sType) || !strIsEmpty($sGUID) || !strIsEmpty($sNotes) || !strIsEmpty($sClassType) || !strIsEmpty($sInput) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['oslcguid']	= $sGUID;
					$aRow['guid']		= BuildWebUID('ts', $sParentGUID, $iItemNo);
					$aRow['notes']		= $sNotes;
					$aRow['type']		= $sType;
					$aRow['status']		= $sStatus;
					$aRow['lastrun']	= $sLrun;
					$aRow['runby']		= $sRunBy;
					$aRow['checkedby']	= $sChkdBy;
					$aRow['input']		= $sInput;
					$aRow['acceptance']	= $sAcCrit;
					$aRow['classtype']	= $sClassType;
					$aRow['results']	= $sResults;
					$aTests[] 			= $aRow;
				}
				$iItemNo += 1;
			}
		}
		return $aTests;
	}
	function getResourceAlloc($xnResources)
	{
		$aResources = array();
		if ($xnResources->nodeName !== "ss:resourceallocations")
		{
			return $aResources;
		}
		$xnDesc = $xnResources->firstChild;
		if ($xnDesc != null)
		{
			$iItemNo = 1;
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnResource = $xnMembers->firstChild;
				$sResource	= '';
				$sRole		= '';
				$sSdate		= '';
				$sEdate		= '';
				$sPercentage= '';
				$sExptime	= '';
				$sAtime		= '';
				$sExpendtime= '';
				$sHistory	= '';
				$sGUID		= '';
				$sNotes		= '';
				$sParentGUID= '';
				foreach ($xnResource->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'ss:role', $sRole);
					GetXMLNodeValue($xn, 'ss:percentagecomplete', $sPercentage);
					GetXMLNodeValue($xn, 'ss:expectedtime', $sExptime);
					GetXMLNodeValue($xn, 'ss:allocatedtime', $sAtime);
					GetXMLNodeValue($xn, 'ss:input', $sInput);
					GetXMLNodeValue($xn, 'ss:expendedtime', $sExpendtime);
					GetXMLNodeValue($xn, 'ss:history', $sHistory);
					GetXMLNodeValue($xn, 'dcterms:identifier', $sGUID);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'ss:resourceidentifier', $sParentGUID);
					if ($xn->nodeName==="ss:startdate")
					{
						$sSdate = $xn->nodeValue;
						$sSdate = RemoveTime($sSdate);
					}
					else if ($xn->nodeName==="ss:enddate")
					{
						$sEdate = $xn->nodeValue;
						$sEdate = RemoveTime($sEdate);
					}
					else if ($xn->nodeName==="ss:resourcename")
					{
						$xnNode	   = $xn->firstChild;
						$xnNode	   = $xnNode->firstChild;
						$sResource = $xnNode->nodeValue;
					}
				}
				if ( !strIsEmpty($sResource) || !strIsEmpty($sRole) || !strIsEmpty($sGUID) )
				{
					$sAtime				= trim($sAtime, '.');
					$aRow				= array();
					$aRow['resource']	= $sResource;
					$aRow['oslcguid']	= $sGUID;
					$aRow['guid']		= BuildWebUID('ra', $sParentGUID, $iItemNo);
					$aRow['notes']		= $sNotes;
					$aRow['role']		= $sRole;
					$aRow['atime']		= $sAtime;
					$aRow['sdate']		= $sSdate;
					$aRow['edate']		= $sEdate;
					$aRow['percentage']	= $sPercentage;
					$aRow['exptime']	= $sExptime;
					$aRow['expendtime']	= $sExpendtime;
					$aRow['history']	= $sHistory;
					$aResources[] 		= $aRow;
				}
				$iItemNo += 1;
			}
		}
		return $aResources;
	}
	function getScenarios($xnScenarios)
	{
		$aScenarios = array();
		if ($xnScenarios->nodeName !== "ss:scenarios")
		{
			return $aScenarios;
		}
		$xnDesc = $xnScenarios->firstChild;
		if ($xnDesc != null)
		{
			$iItemNo = 1;
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnScenario = $xnMembers->firstChild;
				$aSenarioSteps 	= array();
				$sName			= '';
				$sType			= '';
				$sGUID			= '';
				$sNotes			= '';
				$sSteps			= '';
				$sParentGUID	= '';
				foreach ($xnScenario->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:type', 		$sType);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'ss:resourceidentifier', $sParentGUID);
					if ($xn->nodeName==="ss:structuredspecification")
					{
						GetXMLNodeValue($xn, 'ss:structuredspecification', $sCData);
						if ( !strIsEmpty($sCData) )
						{
							$sCData = str_replace("<![CDATA[", "", $sCData);
							$sCData = str_replace("]]>", "", $sCData);
							$domDoc = new DOMDocument;
							SafeXMLLoad($domDoc, $sCData);
							$xnScenRoot = $domDoc->documentElement;
							foreach ($xnScenRoot->childNodes as $xnScenStep)
							{
								$sStepName  = '';
								$sStepGuid  = '';
								$sStepLevel = '';
								$sUses 		= '';
								$sUsesList 	= '';
								$sState     = '';
								$sResults	= '';
								$sTrigger   = '';
								$sLink      = '';
								GetXMLNodeValueAttr($xnScenStep, 'step', 'name', $sStepName);
								GetXMLNodeValueAttr($xnScenStep, 'step', 'guid', $sStepGuid);
								GetXMLNodeValueAttr($xnScenStep, 'step', 'level', $sStepLevel);
								GetXMLNodeValueAttr($xnScenStep, 'step', 'uses', $sUses );
								GetXMLNodeValueAttr($xnScenStep, 'step', 'useslist', $sUsesList);
								GetXMLNodeValueAttr($xnScenStep, 'step', 'result', $sResults);
								GetXMLNodeValueAttr($xnScenStep, 'step', 'state', $sState);
								GetXMLNodeValueAttr($xnScenStep, 'step', 'trigger', $sTrigger);
								GetXMLNodeValueAttr($xnScenStep, 'step', 'link', $sLink);
								if ( !strIsEmpty($sStepName) || !strIsEmpty($sStepLevel) || !strIsEmpty($sUses) )
								{
									$aRow			= array();
									$aRow['stepname']= $sStepName;
									$aRow['guid']	= $sStepGuid;
									$aRow['level']	= $sStepLevel;
									$aRow['uses']	= $sUses;
									$aRow['useslist']= $sUsesList;
									$aRow['result']	= $sResults;
									$aRow['state']	= $sState;
									$aRow['trigger']= $sTrigger;
									$aRow['link']	= $sLink;
									$aSenarioSteps[]= $aRow;
								}
							}
						}
					}
				}
				if ( !strIsEmpty($sGUID) || !strIsEmpty($sName) || !strIsEmpty($sType) || !strIsEmpty($sNotes) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['type']		= $sType;
					$aRow['oslcguid']	= $sGUID;
					$aRow['guid']		= BuildWebUID('sc', $sParentGUID, $iItemNo);
					$aRow['notes']		= $sNotes;
					if (count($aSenarioSteps)> 0)
					{
						$aRow["steps"] = $aSenarioSteps;
					}
					$aScenarios[]	= $aRow;
				}
				$iItemNo += 1;
			}
		}
		return $aScenarios;
	}
	function getRequirements($xnRequirements)
	{
		$aRequirements = array();
		if ($xnRequirements->nodeName !== "ss:requirements")
		{
			return $aRequirements;
		}
		$xnDesc = $xnRequirements->firstChild;
		if ($xnDesc != null)
		{
			$iItemNo = 1;
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnRequire = $xnMembers->firstChild;
				$sName		= '';
				$sNotes		= '';
				$sType		= '';
				$sMod		= '';
				$sStatus	= '';
				$sStability	= '';
				$sDiff		= '';
				$sPrior		= '';
				$sGUID		= '';
				$sParentGUID= '';
				foreach ($xnRequire->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'dcterms:type', 		$sType);
					GetXMLNodeValue($xn, 'dcterms:modified', 	$sMod);
					GetXMLNodeValue($xn, 'ss:status', 			$sStatus);
					GetXMLNodeValue($xn, 'ss:stability', 		$sStability);
					GetXMLNodeValue($xn, 'ss:difficulty', 		$sDiff);
					GetXMLNodeValue($xn, 'ss:priority', 		$sPrior);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'ss:resourceidentifier', $sParentGUID);
				}
				if ( !strIsEmpty($sGUID) || !strIsEmpty($sName) || !strIsEmpty($sMod) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['notes']		= $sNotes;
					$aRow['type']		= $sType;
					$aRow['modified']	= $sMod;
					$aRow['status']		= $sStatus;
					$aRow['stability']	= $sStability;
					$aRow['difficulty']	= $sDiff;
					$aRow['priority']	= $sPrior;
					$aRow['oslcguid']	= $sGUID;
					$aRow['guid']		= BuildWebUID('rq', $sParentGUID, $iItemNo);
					$aRequirements[] 	= $aRow;
				}
				$iItemNo += 1;
			}
		}
		return $aRequirements;
	}
	function getRunStates($xnRunStates)
	{
		$aRunStates = array();
		if ($xnRunStates->nodeName !== "ss:runstates")
		{
			return $aRunStates;
		}
		$xnDesc = $xnRunStates->firstChild;
		if ($xnDesc != null)
		{
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnRunState = $xnMembers->firstChild;
				$sName		= '';
				$sNotes		= '';
				$sOperator	= '';
				$sValue		= '';
				$sGUID		= '';
				foreach ($xnRunState->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'ss:operator', 		$sOperator);
					GetXMLNodeValue($xn, 'ss:value',		 	$sValue);
					GetXMLNodeValue($xn, 'ss:status', 			$sStatus);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
				}
				if ( !strIsEmpty($sGUID) || !strIsEmpty($sName) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['notes']		= $sNotes;
					$aRow['operator']	= $sOperator;
					$aRow['value']		= $sValue;
					$aRow['guid']		= $sGUID;
					$aRunStates[] 		= $aRow;
				}
			}
		}
		return $aRunStates;
	}
	function getParentInfo($sOSLC_URL, &$aParentInfo, &$aCommonProps)
	{
		$sGUID			= $aCommonProps['guid'];
		$sParentGUID 	= $aParentInfo['guid'];
		if ( strIsEmpty($sParentGUID) )
		{
			return;
		}
		$sPB_URL = $sOSLC_URL . 'pbstructure/nohierarchy/';
		$sPB_URL = $sPB_URL . $sParentGUID . '/';
		$sParas = '';
		$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
		AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
		$sPB_URL 	.= $sParas;
		$xmlDoc = HTTPGetXML($sPB_URL);
		$sParentObjName		= '';
		$sParentbjType		= '';
		$sParentbjResType	= '';
		$sParentObjStereo	= '';
		$sParentObjNType	= '';
		$aStereotypes 		= array();
		if ($xmlDoc != null)
		{
			$xnRoot = $xmlDoc->documentElement;
			$xnDesc = $xnRoot->firstChild;
			if ($xnDesc != null)
			{
				foreach ($xnDesc->childNodes as $xnObjProp)
				{
					GetXMLNodeValue($xnObjProp, 'dcterms:title', $aParentInfo['text']);
					GetXMLNodeValue($xnObjProp, 'dcterms:type', $aParentInfo['type']);
					GetXMLNodeValue($xnObjProp, 'ss:resourcetype', $aParentInfo['restype']);
					GetXMLNodeValue($xnObjProp, 'ss:iconidentifier', $sParentObjNType);
					GetXMLNodeValue($xnObjProp, 'ss:ntype', $sParentObjNType);
					AddStereotypeToArray($xnObjProp, $aStereotypes);
				}
				if (count($aStereotypes)>0)
				{
					$sParentObjStereo = getPrimaryStereotype($aStereotypes);
					$aParentInfo['stereotypes'] = $aStereotypes;
				}
				$aParentInfo['imageurl'] 	= GetObjectImagePath($aParentInfo['type'], $aParentInfo['restype'], $sParentObjStereo, $sParentObjNType, 16);
			}
		}
	}
	function getUsage($xnUsages)
	{
		$aUsages = array();
		$aInstances = array();
		$aDiagramUsage = array();
		$aStereotypes = array();
		if ($xnUsages->nodeName !== "ss:usages")
		{
			return $aUsages;
		}
		$xnDesc = $xnUsages->firstChild;
		if ($xnDesc != null)
		{
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnUsage = $xnMembers->firstChild;
				$sTitle	= '';
				$sGUID	= '';
				$sType	= '';
				$sImageURL = '';
				$sStereotype = '';
				$aClassifierUsage = array();
				foreach ($xnUsage->childNodes as $xnInfo)
				{
					GetXMLNodeValue($xnInfo, 'dcterms:title', 		$sTitle);
					GetXMLNodeValue($xnInfo, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xnInfo, 'dcterms:type', 		$sType);
					GetXMLNodeValue($xnInfo, 'ss:resourcetype',		$sResType);
					GetXMLNodeValue($xnInfo, 'ss:usagetype',		$sUsageType);
					AddStereotypeToArray($xnInfo, $aStereotypes);
					if ( strIsEmpty($sTitle) )
					{
						$sTitle = _glt('<Unnamed object>');
					}
					if ($xnInfo->nodeName === 'ss:usages')
					{
						$xnDesc = GetXMLFirstChild($xnInfo);
						foreach ($xnDesc->childNodes as $xnMem)
						{
						$xnRes = GetXMLFirstChild($xnMem);
							foreach ($xnRes->childNodes as $xnInfo)
							{
								GetXMLNodeValue($xnInfo, 'dcterms:title', 		$sCDTitle);
								GetXMLNodeValue($xnInfo, 'dcterms:identifier', 	$sCDGUID);
								GetXMLNodeValue($xnInfo, 'dcterms:type', 		$sCDType);
								GetXMLNodeValue($xnInfo, 'ss:resourcetype',		$sCDResType);
								$sCDImageURL = GetObjectImagePath($sCDType, $sCDResType, '', '0', '16');
							}
							array_push($aClassifierUsage, array ( "name" => $sCDTitle, "guid" => $sCDGUID, "type" => $sCDType, "imageurl" => $sCDImageURL));
						}
					}
				}
				if (count($aStereotypes)>0)
				{
					$sStereotype = getPrimaryStereotype($aStereotypes);
				}
				$sImageURL = GetObjectImagePath($sType, $sResType, $sStereotype, '0', '16');
				if ( !strIsEmpty($sGUID) || !strIsEmpty($sTitle) )
				{
					if ($sUsageType === "Diagram")
					{
						array_push($aDiagramUsage, array ( "name" => $sTitle, "guid" => $sGUID, "type" => $sType, "imageurl" => $sImageURL,  "classifierusage" => $aClassifierUsage ));
					}
					else
					{
						array_push($aInstances, array ( "name" => $sTitle, "guid" => $sGUID, "type" => $sType, "imageurl" => $sImageURL,  "classifierusage" => $aClassifierUsage ));
					}
				}
				$aUsages['instances'] = $aInstances;
				$aUsages['diagrams'] = $aDiagramUsage;
			}
		}
		return $aUsages;
	}
	function getDocument($xn)
	{
		$aDocument = array();
		if ($xn->nodeName !== "ss:linkeddocument")
		{
			return $aDocument;
		}
		$aDocument['content'] 	= 'no content';
		$aDocument['type']		= '';
		$sContent = '';
		$xnDesc = $xn->firstChild;
		if ($xnDesc != null)
		{
			foreach ($xnDesc->childNodes as $xnInfo)
			{
				GetXMLNodeValue($xnInfo, 'dcterms:identifier',	$aDocument['guid']);
				GetXMLNodeValue($xnInfo, 'dcterms:type', 		$aDocument['type']);
				GetXMLNodeValue($xnInfo, 'ss:extension', 		$aDocument['extension']);
				GetXMLNodeValue($xnInfo, 'ss:content', 			$sContent);
			}
		}
		if ( $aDocument['type']==='ExtDoc' )
		{
			$aDocument['content'] = $sContent;
		}
		return $aDocument;
	}
	function getReviewDiagrams($xn)
	{
		$aReviewDiagrams = array();
		if ($xn->nodeName !== "ss:reviewdiagrams")
		{
			return $aReviewDiagrams;
		}
		$xnDesc = $xn->firstChild;
		if ($xnDesc != null)
		{
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnRes 	= $xnMembers->firstChild;
				$sName	= '';
				$sType	= '';
				$sGUID	= '';
				foreach ($xnRes->childNodes as $xnResC)
				{
					GetXMLNodeValue($xnResC, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xnResC, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xnResC, 'dcterms:type', 		$sType);
				}
				if ( !strIsEmpty($sGUID) || !strIsEmpty($sName) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['guid']		= $sGUID;
					$aRow['type']		= $sType;
					$aRow['imageurl']	= GetObjectImagePath($sType, 'Diagram', '', '', 16);
					$aReviewDiagrams[] 	= $aRow;
				}
			}
		}
		return $aReviewDiagrams;
	}
	function getReviewNoDiscussions($xn)
	{
		$aReviewNoDiscuss = array();
		if ($xn->nodeName !== "ss:reviewnodiscussions")
		{
			return $aReviewNoDiscuss;
		}
		$xnDesc = $xn->firstChild;
		if ($xnDesc != null)
		{
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnRes 		= $xnMembers->firstChild;
				$sName		= '';
				$sType		= '';
				$sNType		= '';
				$sStereotype = '';
				$sResType	= '';
				$sGUID		= '';
				$aStereotypes = array();
				foreach ($xnRes->childNodes as $xnResC)
				{
					GetXMLNodeValue($xnResC, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xnResC, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xnResC, 'dcterms:type', 		$sType);
					GetXMLNodeValue($xnResC, 'ss:resourcetype', 	$sResType);
					GetXMLNodeValue($xnResC, 'ss:iconidentifier',	$sNType);
					GetXMLNodeValue($xnResC, 'ss:ntype',		 	$sNType);
					AddStereotypeToArray($xnResC, $aStereotypes);
				}
				if (count($aStereotypes)>0)
				{
					$sStereotype = getPrimaryStereotype($aStereotypes);
				}
				if ( !strIsEmpty($sGUID) || !strIsEmpty($sName) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['guid']		= $sGUID;
					$aRow['type']		= $sType;
					$aRow['restype']	= $sResType;
					$aRow['stereotype']	= $sStereotype;
					$aRow['ntype']		= $sNType;
					$aRow['imageurl']	= GetObjectImagePath($sType, $sResType, $sStereotype, $sNType, 16);
					$aReviewNoDiscuss[] = $aRow;
				}
			}
		}
		return $aReviewNoDiscuss;
	}
	function getRelationshipsPart1($xnRelationships)
	{
		$aConnectors = array();
		if ($xnRelationships->nodeName !== "ss:linkedresources")
		{
			return $aConnectors;
		}
		$xnDesc = $xnRelationships->firstChild;
		if ($xnDesc != null)
		{
			foreach ($xnDesc->childNodes as $xnLinks)
			{
				$sLinkID		= '';
				$sResource		= '';
				$sLinkID 		= $xnLinks->getAttribute('rdf:ID');
				$sResource	 	= $xnLinks->getAttribute('rdf:resource');
				$sElementGUID 	= TrimInternalURL($sResource, '/completeresource/');
				$sElementName	= '[Unknown]';
				if ( !strIsEmpty($sElementGUID) || !strIsEmpty($sLinkID) )
				{
					$aRow = array("linkid" => $sLinkID,
								  "connectorname" => '',
								  "connectortype" => '',
								  "connectorguid" => '',
								  "connectorimageurl" => '',
								  "direction" => '',
								  "linkdirection" => '',
								  "elementguid" => $sElementGUID,
								  "elementname" => $sElementName,
								  "elementimageurl" => '');
					array_push($aConnectors, $aRow);
				}
			}
		}
		return $aConnectors;
	}
	function getRelationshipsPart2($xnObj, &$aRelationships)
	{
		while ($xnObj)
		{
			if ($xnObj->nodeName === "rdf:Description")
			{
				$sLinkID = $xnObj->getAttribute("rdf:about");
				if (substr($sLinkID, 0, 4) === '#LID')
				{
					$sConnName 			= '';
					$sConnType 			= '';
					$sConnGUID 			= '';
					$sConDir			= '';
					$sLinkDir			= '';
					$sOtherName			= '';
					$sOtherType			= '';
					$sOtherResType	 	= '';
					$sOtherStereotype	= '';
					$sOtherNType		= '';
					$sOtherImageURL		= '';
					foreach ($xnObj->childNodes as $xnObjProp)
					{
						GetXMLNodeValue($xnObjProp, 'dcterms:title',	 	$sConnName);
						GetXMLNodeValue($xnObjProp, 'dcterms:type',		 	$sConnType);
						GetXMLNodeValue($xnObjProp, 'dcterms:identifier', 	$sConnGUID);
						GetXMLNodeValue($xnObjProp, 'ss:direction',		 	$sConnDir);
						GetXMLNodeValue($xnObjProp, 'ss:linkdirection',	 	$sLinkDir);
						GetXMLNodeValue($xnObjProp, 'ss:othername',		 	$sOtherName);
						GetXMLNodeValue($xnObjProp, 'ss:othertype',		 	$sOtherType);
						GetXMLNodeValue($xnObjProp, 'ss:otherrestype',		$sOtherResType);
						GetXMLNodeValue($xnObjProp, 'ss:otherstereotype', 	$sOtherStereotype);
						GetXMLNodeValue($xnObjProp, 'ss:otherntype',		$sOtherNType);
					}
					$sLinkID = substr($sLinkID, 1);
					$iCnt = count($aRelationships);
					for ($i=0; $i<$iCnt; $i++)
					{
						if ($aRelationships[$i]['linkid'] === $sLinkID)
						{
							$aRelationships[$i]['connectorname']	= $sConnName;
							$aRelationships[$i]['connectortype']	= $sConnType;
							$aRelationships[$i]['connectorguid']	= $sConnGUID;
							$aRelationships[$i]['connectorimageurl']= GetObjectImagePath($sConnType, 'Connector', '', '', '16');
							$aRelationships[$i]['direction']		= $sConnDir;
							$aRelationships[$i]['linkdirection']	= $sLinkDir;
							$aRelationships[$i]['elementname']		= $sOtherName;
							$aRelationships[$i]['elementimageurl']	= GetObjectImagePath($sOtherType, $sOtherResType, $sOtherStereotype, $sOtherNType, '16');
							break;
						}
					}
				}
			}
			$xnObj = $xnObj->nextSibling;
		}
	}
	function getChanges($xnChanges)
	{
		$aChanges = array();
		if ($xnChanges->nodeName !== "ss:changes")
		{
			return $aChanges;
		}
		$xnDesc = $xnChanges->firstChild;
		if ($xnDesc != null)
		{
			$iItemNo = 1;
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnChange = $xnMembers->firstChild;
				$sName		= '';
				$sNotes		= '';
				$sHistory	= '';
				$sStatus	= '';
				$sRequestedOn='';
				$sCompletedOn='';
				$sRequestedBy='';
				$sCompletedBy='';
				$sPriority	= '';
				$sVersion	= '';
				$sParentGUID= '';
				$sGUID		= '';
				foreach ($xnChange->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'ss:history', 			$sHistory);
					GetXMLNodeValue($xn, 'ss:status', 			$sStatus);
					GetXMLNodeValue($xn, 'ss:requestedon', 		$sRequestedOn);
					GetXMLNodeValue($xn, 'ss:completedon', 		$sCompletedOn);
					GetXMLNodeValue($xn, 'ss:priority', 		$sPriority);
					GetXMLNodeValue($xn, 'ss:version', 			$sVersion);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'ss:resourceidentifier',$sParentGUID);
					if ($xn->nodeName==="ss:requestedby")
					{
						$xnNode = $xn->firstChild;
						$xnNode = $xnNode->firstChild;
						$sRequestedBy = $xnNode->nodeValue;
					}
					if ($xn->nodeName==="ss:completedby")
					{
						$xnNode = $xn->firstChild;
						$xnNode = $xnNode->firstChild;
						$sCompletedBy = $xnNode->nodeValue;
					}
				}
				if ( !strIsEmpty($sGUID) || !strIsEmpty($sName) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['notes']		= $sNotes;
					$aRow['history']	= $sHistory;
					$aRow['status']		= $sStatus;
					$aRow['requestedon']= RemoveTime($sRequestedOn);
					$aRow['requestedby']= $sRequestedBy;
					$aRow['completedon']= RemoveTime($sCompletedOn);
					$aRow['completedby']= $sCompletedBy;
					$aRow['priority']	= $sPriority;
					$aRow['version']	= $sVersion;
					$aRow['oslcguid']	= $sGUID;
					$aRow['guid']		= BuildWebUID('ch', $sParentGUID, $iItemNo);
					$aRow['parentguid']	= $sParentGUID;
					$aChanges[] 		= $aRow;
				}
				$iItemNo += 1;
			}
		}
		return $aChanges;
	}
	function getDefects($xnDefects)
	{
		$aDefects = array();
		if ($xnDefects->nodeName !== "ss:defects")
		{
			return $aDefects;
		}
		$xnDesc = $xnDefects->firstChild;
		if ($xnDesc != null)
		{
			$iItemNo = 1;
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnDefect = $xnMembers->firstChild;
				$sName		= '';
				$sNotes		= '';
				$sHistory	= '';
				$sStatus	= '';
				$sReportedOn= '';
				$sReportedBy= '';
				$sResolvedOn= '';
				$sResolvedBy= '';
				$sPriority	= '';
				$sVersion	= '';
				$sGUID		= '';
				$sParentGUID= '';
				foreach ($xnDefect->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'ss:history', 			$sHistory);
					GetXMLNodeValue($xn, 'ss:status', 			$sStatus);
					GetXMLNodeValue($xn, 'ss:reportedon', 		$sReportedOn);
					GetXMLNodeValue($xn, 'ss:resolvedon', 		$sResolvedOn);
					GetXMLNodeValue($xn, 'ss:priority', 		$sPriority);
					GetXMLNodeValue($xn, 'ss:version', 			$sVersion);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'ss:resourceidentifier',$sParentGUID);
					if ($xn->nodeName==="ss:reportedby")
					{
						$xnNode = $xn->firstChild;
						$xnNode = $xnNode->firstChild;
						$sReportedBy = $xnNode->nodeValue;
					}
					if ($xn->nodeName==="ss:resolvedby")
					{
						$xnNode = $xn->firstChild;
						$xnNode = $xnNode->firstChild;
						$sResolvedBy = $xnNode->nodeValue;
					}
				}
				if ( !strIsEmpty($sGUID) || !strIsEmpty($sName) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['notes']		= $sNotes;
					$aRow['history']	= $sHistory;
					$aRow['status']		= $sStatus;
					$aRow['reportedon'] = RemoveTime($sReportedOn);
					$aRow['reportedby'] = $sReportedBy;
					$aRow['resolvedon'] = RemoveTime($sResolvedOn);
					$aRow['resolvedby'] = $sResolvedBy;
					$aRow['priority']	= $sPriority;
					$aRow['version']	= $sVersion;
					$aRow['oslcguid']	= $sGUID;
					$aRow['guid']		= BuildWebUID('df', $sParentGUID, $iItemNo);
					$aRow['parentguid']	= $sParentGUID;
					$aDefects[] 		= $aRow;
				}
				$iItemNo += 1;
			}
		}
		return $aDefects;
	}
	function getIssues($xnIssues)
	{
		$aIssues = array();
		if ($xnIssues->nodeName !== "ss:issues")
		{
			return $aIssues;
		}
		$xnDesc = $xnIssues->firstChild;
		if ($xnDesc != null)
		{
			$iItemNo = 1;
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnIssue = $xnMembers->firstChild;
				$sName		= '';
				$sNotes		= '';
				$sHistory	= '';
				$sStatus	= '';
				$sRaisedOn	= '';
				$sRaisedBy	= '';
				$sCompletedOn='';
				$sCompletedBy='';
				$sPriority	= '';
				$sVersion	= '';
				$sGUID		= '';
				$sParentGUID= '';
				foreach ($xnIssue->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'ss:history', 			$sHistory);
					GetXMLNodeValue($xn, 'ss:status', 			$sStatus);
					GetXMLNodeValue($xn, 'ss:raisedon', 		$sRaisedOn);
					GetXMLNodeValue($xn, 'ss:completedon', 		$sCompletedOn);
					GetXMLNodeValue($xn, 'ss:priority', 		$sPriority);
					GetXMLNodeValue($xn, 'ss:version', 			$sVersion);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'ss:resourceidentifier',$sParentGUID);
					if ($xn->nodeName==="ss:raisedby")
					{
						$xnNode = $xn->firstChild;
						$xnNode = $xnNode->firstChild;
						$sRaisedBy = $xnNode->nodeValue;
					}
					if ($xn->nodeName==="ss:completedby")
					{
						$xnNode = $xn->firstChild;
						$xnNode = $xnNode->firstChild;
						$sCompletedBy = $xnNode->nodeValue;
					}
				}
				if ( !strIsEmpty($sGUID) || !strIsEmpty($sName) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['notes']		= $sNotes;
					$aRow['history']	= $sHistory;
					$aRow['status']		= $sStatus;
					$aRow['raisedon'] 	= RemoveTime($sRaisedOn);
					$aRow['raisedby'] 	= $sRaisedBy;
					$aRow['completedon'] = RemoveTime($sCompletedOn);
					$aRow['completedby'] = $sCompletedBy;
					$aRow['priority']	= $sPriority;
					$aRow['version']	= $sVersion;
					$aRow['oslcguid']	= $sGUID;
					$aRow['guid']		= BuildWebUID('is', $sParentGUID, $iItemNo);
					$aRow['parentguid']	= $sParentGUID;
					$aIssues[] 			= $aRow;
				}
				$iItemNo += 1;
			}
		}
		return $aIssues;
	}
	function getTasks($xnTasks)
	{
		$aTasks = array();
		if ($xnTasks->nodeName !== "ss:tasks")
		{
			return $aTasks;
		}
		$xnDesc = $xnTasks->firstChild;
		if ($xnDesc != null)
		{
			$iItemNo = 1;
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnTask		= $xnMembers->firstChild;
				$sName		= '';
				$sNotes		= '';
				$sHistory	= '';
				$sStatus	= '';
				$sRequestedOn='';
				$sRequestedBy='';
				$sCompletedOn='';
				$sCompletedBy='';
				$sPriority	= '';
				$sVersion	= '';
				$sGUID		= '';
				$sParentGUID= '';
				foreach ($xnTask->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'ss:history', 			$sHistory);
					GetXMLNodeValue($xn, 'ss:status', 			$sStatus);
					GetXMLNodeValue($xn, 'ss:requestedon', 		$sRequestedOn);
					GetXMLNodeValue($xn, 'ss:completedon', 		$sCompletedOn);
					GetXMLNodeValue($xn, 'ss:priority', 		$sPriority);
					GetXMLNodeValue($xn, 'ss:version', 			$sVersion);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'ss:resourceidentifier',$sParentGUID);
					if ($xn->nodeName==="ss:requestedby")
					{
						$xnNode = $xn->firstChild;
						$xnNode = $xnNode->firstChild;
						$sRequestedBy = $xnNode->nodeValue;
					}
					if ($xn->nodeName==="ss:completedby")
					{
						$xnNode = $xn->firstChild;
						$xnNode = $xnNode->firstChild;
						$sCompletedBy = $xnNode->nodeValue;
					}
				}
				if ( !strIsEmpty($sGUID) || !strIsEmpty($sName) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['notes']		= $sNotes;
					$aRow['history']	= $sHistory;
					$aRow['status']		= $sStatus;
					$aRow['requestedon']= RemoveTime($sRequestedOn);
					$aRow['requestedby']= $sRequestedBy;
					$aRow['completedon'] = RemoveTime($sCompletedOn);
					$aRow['completedby'] = $sCompletedBy;
					$aRow['priority']	= $sPriority;
					$aRow['version']	= $sVersion;
					$aRow['oslcguid']	= $sGUID;
					$aRow['guid']		= BuildWebUID('tk', $sParentGUID, $iItemNo);
					$aRow['parentguid']	= $sParentGUID;
					$aTasks[] 			= $aRow;
				}
				$iItemNo += 1;
			}
		}
		return $aTasks;
	}
	function getEvents($xnEvents)
	{
		$aEvents = array();
		if ($xnEvents->nodeName !== "ss:events")
		{
			return $aEvents;
		}
		$xnDesc = $xnEvents->firstChild;
		if ($xnDesc != null)
		{
			$iItemNo = 1;
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnEvent	 = $xnMembers->firstChild;
				$sName		= '';
				$sNotes		= '';
				$sHistory	= '';
				$sStatus	= '';
				$sReportedOn= '';
				$sReportedBy= '';
				$sResolvedOn= '';
				$sResolvedBy= '';
				$sPriority	= '';
				$sVersion	= '';
				$sGUID		= '';
				$sParentGUID= '';
				foreach ($xnEvent->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'ss:history', 			$sHistory);
					GetXMLNodeValue($xn, 'dcterms:type', 			$sStatus);
					GetXMLNodeValue($xn, 'ss:reportedon', 		$sReportedOn);
					GetXMLNodeValue($xn, 'ss:resolvedon', 		$sResolvedOn);
					GetXMLNodeValue($xn, 'ss:priority', 		$sPriority);
					GetXMLNodeValue($xn, 'ss:version', 			$sVersion);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'ss:resourceidentifier',$sParentGUID);
					if ($xn->nodeName==="ss:reportedby")
					{
						$xnNode = $xn->firstChild;
						$xnNode = $xnNode->firstChild;
						$sReportedBy = $xnNode->nodeValue;
					}
					if ($xn->nodeName==="ss:resolvedby")
					{
						$xnNode = $xn->firstChild;
						$xnNode = $xnNode->firstChild;
						$sResolvedBy = $xnNode->nodeValue;
					}
				}
				if ( !strIsEmpty($sGUID) || !strIsEmpty($sName) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['notes']		= $sNotes;
					$aRow['history']	= $sHistory;
					$aRow['status']		= $sStatus;
					$aRow['reportedon'] = RemoveTime($sReportedOn);
					$aRow['reportedby'] = $sReportedBy;
					$aRow['resolvedon'] = RemoveTime($sResolvedOn);
					$aRow['resolvedby'] = $sResolvedBy;
					$aRow['priority']	= $sPriority;
					$aRow['version']	= $sVersion;
					$aRow['oslcguid']	= $sGUID;
					$aRow['guid']		= BuildWebUID('ev', $sParentGUID, $iItemNo);
					$aRow['parentguid']	= $sParentGUID;
					$aEvents[] 			= $aRow;
				}
				$iItemNo += 1;
			}
		}
		return $aEvents;
	}
	function getDecisions($xnDecisions)
	{
		$aDecisions = array();
		if ($xnDecisions->nodeName !== "ss:decisions")
		{
			return $aDecisions;
		}
		$xnDesc = $xnDecisions->firstChild;
		if ($xnDesc != null)
		{
			$iItemNo = 1;
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnDecision = $xnMembers->firstChild;
				$sName		= '';
				$sNotes		= '';
				$sHistory	= '';
				$sStatus	= '';
				$sOwner		= '';
				$sDate		= '';
				$sAuthor	= '';
				$sEffective = '';
				$sImpact	= '';
				$sVersion	= '';
				$sGUID		= '';
				foreach ($xnDecision->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'ss:history', 			$sHistory);
					GetXMLNodeValue($xn, 'ss:status', 			$sStatus);
					GetXMLNodeValue($xn, 'ss:date', 			$sDate);
					GetXMLNodeValue($xn, 'ss:effective', 		$sEffective);
					GetXMLNodeValue($xn, 'ss:impact',	 		$sImpact);
					GetXMLNodeValue($xn, 'ss:version', 			$sVersion);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'ss:resourceidentifier',$sParentGUID);
					if ($xn->nodeName==="ss:owner")
					{
						$xnNode = $xn->firstChild;
						$xnNode = $xnNode->firstChild;
						$sOwner = $xnNode->nodeValue;
					}
					if ($xn->nodeName==="ss:author")
					{
						$xnNode = $xn->firstChild;
						$xnNode = $xnNode->firstChild;
						$sAuthor = $xnNode->nodeValue;
					}
				}
				if ( !strIsEmpty($sGUID) || !strIsEmpty($sName) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['notes']		= $sNotes;
					$aRow['history']	= $sHistory;
					$aRow['status']		= $sStatus;
					$aRow['date'] 		= RemoveTime($sDate);
					$aRow['effective'] 	= RemoveTime($sEffective);
					$aRow['owner'] 		= $sOwner;
					$aRow['author'] 	= $sAuthor;
					$aRow['impact']		= $sImpact;
					$aRow['version']	= $sVersion;
					$aRow['oslcguid']	= $sGUID;
					$aRow['guid']		= BuildWebUID('dc', $sParentGUID, $iItemNo);
					$aRow['parentguid']	= $sParentGUID;
					$aDecisions[] 		= $aRow;
				}
				$iItemNo += 1;
			}
		}
		return $aDecisions;
	}
	function getEfforts($xnEfforts)
	{
		$aEfforts = array();
		if ($xnEfforts->nodeName !== "ss:efforts")
		{
			return $aEfforts;
		}
		$xnDesc = $xnEfforts->firstChild;
		if ($xnDesc != null)
		{
			$iItemNo = 1;
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnEffort = $xnMembers->firstChild;
				$sName		= '';
				$sType		= '';
				$sNotes		= '';
				$sTime		= '';
				$GUID		= '';
				$sParentGUID= '';
				foreach ($xnEffort->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:type', 		$sType);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'ss:time', 			$sTime);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'ss:resourceidentifier',$sParentGUID);
				}
				if ( !strIsEmpty($sGUID) || !strIsEmpty($sName) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['notes']		= $sNotes;
					$aRow['type']		= $sType;
					$aRow['time']		= $sTime;
					$aRow['oslcguid']	= $sGUID;
					$aRow['guid']		= BuildWebUID('ef', $sParentGUID, $iItemNo);
					$aRow['parentguid']	= $sParentGUID;
					$aEfforts[] 		= $aRow;
				}
				$iItemNo += 1;
			}
		}
		return $aEfforts;
	}
	function getRisks($xnRisks)
	{
		$aRisks = array();
		if ($xnRisks->nodeName !== "ss:risks")
		{
			return $aEfforts;
		}
		$xnDesc = $xnRisks->firstChild;
		if ($xnDesc != null)
		{
			$iItemNo = 1;
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnRisk = $xnMembers->firstChild;
				$sName		= '';
				$sType		= '';
				$sNotes		= '';
				$sWeight	= '';
				$GUID		= '';
				$sParentGUID= '';
				foreach ($xnRisk->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:type', 		$sType);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'ss:weight', 			$sWeight);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'ss:resourceidentifier',$sParentGUID);
				}
				if ( !strIsEmpty($sGUID) || !strIsEmpty($sName) )
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['notes']		= $sNotes;
					$aRow['type']		= $sType;
					$aRow['weight']		= $sWeight;
					$aRow['oslcguid']	= $sGUID;
					$aRow['guid']		= BuildWebUID('ri', $sParentGUID, $iItemNo);
					$aRow['parentguid']	= $sParentGUID;
					$aRisks[] 		= $aRow;
				}
				$iItemNo += 1;
			}
		}
		return $aRisks;
	}
	function getMetrics($xnMetrics)
	{
		$aMetrics = array();
		if ($xnMetrics->nodeName !== "ss:metrics")
		{
			return $aEfforts;
		}
		$xnDesc = $xnMetrics->firstChild;
		if ($xnDesc != null)
		{
			$iItemNo = 1;
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$xnMetric = $xnMembers->firstChild;
				$sName		= '';
				$sType		= '';
				$sNotes		= '';
				$sWeight	= '';
				$GUID		= '';
				$sParentGUID= '';
				foreach ($xnMetric->childNodes as $xn)
				{
					GetXMLNodeValue($xn, 'dcterms:title', 		$sName);
					GetXMLNodeValue($xn, 'dcterms:type', 		$sType);
					GetXMLNodeValue($xn, 'dcterms:description', $sNotes);
					GetXMLNodeValue($xn, 'ss:weight', 			$sWeight);
					GetXMLNodeValue($xn, 'dcterms:identifier', 	$sGUID);
					GetXMLNodeValue($xn, 'ss:resourceidentifier',$sParentGUID);
				}
				if (!strIsEmpty($sGUID) || !strIsEmpty($sName))
				{
					$aRow				= array();
					$aRow['name']		= $sName;
					$aRow['notes']		= $sNotes;
					$aRow['type']		= $sType;
					$aRow['weight']		= $sWeight;
					$aRow['oslcguid']	= $sGUID;
					$aRow['guid']		= BuildWebUID('me', $sParentGUID, $iItemNo);
					$aRow['parentguid']	= $sParentGUID;
					$aMetrics[] 		= $aRow;
				}
				$iItemNo += 1;
			}
		}
		return $aMetrics;
	}
	function getReviewDiscussions($xnReviewDiscussions)
	{
		$aReviewDiscussions = array();
		$xnDesc = $xnReviewDiscussions->firstChild;
		if ($xnDesc !== null)
		{
			foreach ($xnDesc->childNodes as $xnMembers)
			{
				$sObjName		= '';
				$sObjType		= '';
				$sObjGUID		= '';
				$sObjResType	= '';
				$sObjNType		= '';
				$sObjStereo		= '';
				$aDiscussions	= array();
				$aStereotypes 	= array();
				$xnOSLCAM = $xnMembers->firstChild;
				if ($xnOSLCAM != null)
				{
					foreach ($xnOSLCAM->childNodes as $xnProps)
					{
						GetXMLNodeValue($xnProps, 'dcterms:title', 		$sObjName);
						GetXMLNodeValue($xnProps, 'dcterms:type', 		$sObjType);
						GetXMLNodeValue($xnProps, 'dcterms:identifier', $sObjGUID);
						GetXMLNodeValue($xnProps, 'ss:resourcetype', 	$sObjResType);
						GetXMLNodeValue($xnProps, 'ss:iconidentifier',	$sObjNType);
						GetXMLNodeValue($xnProps, 'ss:ntype', 			$sObjNType);
						AddStereotypeToArray($xnProps, $aStereotypes);
						if ($xnProps->nodeName === "ss:discussions")
						{
							$xnDesc2 = $xnProps->firstChild;
							if ($xnDesc2 !== null)
							{
								foreach ($xnDesc2->childNodes as $xnDiscussMem)
								{
									$xnDiscuss = $xnDiscussMem->firstChild;
									if ($xnDiscuss !== null)
									{
										$aReplies 			= array();
										$sDiscussion		= '';
										$sAvatarID			= '';
										$sDiscussGUID		= '';
										$sDiscussAuthor		= '';
										$sDiscussCreated	= '';
										$sReplies			= '';
										$sPriority 			= '';
										$sStatus 			= '';
										$sPriorityImageURL	= '';
										$sStatusImageURL	= '';
										foreach ($xnDiscuss->childNodes as $xn)
										{
											GetXMLNodeValue($xn, 'dcterms:description', $sDiscussion);
											GetXMLNodeValueAttr($xn, 'ss:avatar', 'rdf:resource', $sAvatarID);
											$sAvatarImage = '';
											GetXMLNodeValue($xn, 'dcterms:identifier', 	$sDiscussGUID);
											GetXMLNodeValue($xn, 'dcterms:created', 	$sDiscussCreated);
											GetXMLNodeValue($xn, 'ss:status', $sStatus);
											GetXMLNodeValue($xn, 'ss:priority', $sPriority);
											if ($xn->nodeName === 'dcterms:creator')
											{
												$xnPerson 		= $xn->firstChild;
												$xnFOAF   		= $xnPerson->firstChild;
												$sDiscussAuthor = $xnFOAF->nodeValue;
											}
											elseif ($xn->nodeName === 'ss:replies')
											{
												$xnNodeC = $xn->firstChild;
												foreach ($xnNodeC->childNodes as $xnReplyMembers)
												{
													$sReply			= '';
													$sReplyAvatarID = '';
													$sReplyAuthor	= '';
													$sReplyCreated	= '';
													$sReplyGUID		= '';
													$xnReplyNode = $xnReplyMembers->firstChild;
													foreach ($xnReplyNode->childNodes as $xnReplyInfo)
													{
														GetXMLNodeValue($xnReplyInfo, 'dcterms:description', 	$sReply);
														GetXMLNodeValueAttr($xnReplyInfo, 'ss:avatar', 'rdf:resource', $sReplyAvatarID);
														GetXMLNodeValue($xnReplyInfo, 'dcterms:created', 		$sReplyCreated);
														GetXMLNodeValue($xnReplyInfo, 'ss:discussionidentifier', $sReplyGUID);
														if ($xnReplyInfo->nodeName === 'dcterms:creator')
														{
															$xnPerson 		= $xnReplyInfo->firstChild;
															$xnFOAF   		= $xnPerson->firstChild;
															$sReplyAuthor	= $xnFOAF->nodeValue;
														}
													}
													if ( !strIsEmpty($sReply) || !strIsEmpty($sReplyAuthor) || !strIsEmpty($sReplyCreated) )
													{
														$aRow				= array();
														$aRow['replytext']	= $sReply;
														$aRow['replyavatarid']	= $sReplyAvatarID;
														$aRow['replyguid']	= $sReplyGUID;
														$aRow['replyauthor']= $sReplyAuthor;
														$aRow['replycreated']= $sReplyCreated;
														$aReplies[]			= $aRow;
													}
												}
											}
										}
										if ( !strIsEmpty($sDiscussion) && !strIsEmpty($sDiscussAuthor) && !strIsEmpty($sDiscussCreated) )
										{
											$sPriorityImageClass = GetPriorityImageClass($sPriority);
											$sStatusImageClass = GetStatusImageClass($sStatus);
											$aRow				= array();
											$aRow['discussion']	= $sDiscussion;
											$aRow['avatarid']	= $sAvatarID;
											$aRow['guid']		= $sDiscussGUID;
											$aRow['author']		= $sDiscussAuthor;
											$aRow['created']	= $sDiscussCreated;
											$aRow['priority']			= $sPriority;
											$aRow['priorityimageclass']	= $sPriorityImageClass;
											$aRow['status']				= $sStatus;
											$aRow['statusimageclass']	= $sStatusImageClass;
											if (count($aReplies)> 0)
											{
												$aRow['replies']= $aReplies;
											}
											$aDiscussions[] 	= $aRow;
										}
									}
								}
							}
						}
					}
				}
				if (count($aStereotypes)>0)
				{
					$sObjStereo = getPrimaryStereotype($aStereotypes);
				}
				if ( !strIsEmpty($sObjGUID) || !strIsEmpty($sObjType) || !strIsEmpty($sObjName) )
				{
					$a					= array();
					$a['name']			= $sObjName;
					$a['type']			= $sObjType;
					$a['guid']			= $sObjGUID;
					$a['restype']		= $sObjResType;
					$a['ntype']			= $sObjNType;
					$a['stereotype']	= $sObjStereo;
					$a['imageurl']		= GetObjectImagePath($sObjType, $sObjResType, $sObjStereo, $sObjNType, '16');
					$a['discussions']	= $aDiscussions;
					$aReviewDiscussions[] = $a;
				}
			}
		}
		return $aReviewDiscussions;
	}
?>