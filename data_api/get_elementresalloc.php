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
	$sRootPath = dirname(dirname(__FILE__));
	require_once $sRootPath . '/globals.php';
	BuildOSLCConnectionString();
	$sOSLC_URL = $g_sOSLCString . 'feature/resourceallocation/';
	$sResource = htmlspecialchars($sResource);
	$sRole = htmlspecialchars($sRole);
	$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	$sXML  = '<?xml version="1.0" encoding="utf-8"?>';
	$sXML .= '<rdf:RDF xmlns:oslc_am="http://open-services.net/ns/am#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:ss="http://www.sparxsystems.com.au/oslc_am#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#">';
	$sXML .= '  <ss:resourceallocation>';
	$sXML .= '    <ss:resourceidentifier>' . $sParentGUID . '</ss:resourceidentifier>';
	$sXML .= '    <ss:resourcename><foaf:Person><foaf:name>' . $sResource . '</foaf:name></foaf:Person></ss:resourcename>';
	$sXML .= '    <ss:role>' . $sRole . '</ss:role>';
	if ( !strIsEmpty($sLoginGUID) )
	{
		$sXML .= '<ss:useridentifier>' . $sLoginGUID . '</ss:useridentifier>';
	}
	$sXML .= '  </ss:resourceallocation>';
	$sXML .= '</rdf:RDF>';
	$xmlRespDoc = null;
	$xmlRespDoc = HTTPPostXML($sOSLC_URL, $sXML);
	$sOSLCErrorMsg = BuildOSLCErrorString();
	if ( strIsEmpty($sOSLCErrorMsg) )
	{
		if ($xmlRespDoc != null)
		{
			$sType			= '';
			$sResType		= '';
			$sStereotype	= '';
			$sNType			= '';
			$sRolesCSV		= '';
			$sAuthorsCSV	= '';
			$aStereotypes 	= array();
			$xnRoot = $xmlRespDoc->documentElement;
			$xnRes  = GetXMLFirstChild($xnRoot);
			if ($xnRes != null && $xnRes->childNodes != null)
			{
				if ( GetOSLCError($xnRes) )
				{
					foreach ($xnRes->childNodes as $xnObjProp)
					{
						GetXMLNodeValue($xnObjProp, 'dcterms:title', 		$aObjectDetails['name']);
						GetXMLNodeValue($xnObjProp, 'dcterms:identifier', 	$aObjectDetails['guid']);
						GetXMLNodeValue($xnObjProp, 'ss:resourcetype', 		$sResType);
						GetXMLNodeValue($xnObjProp, 'dcterms:type', 		$sType);
						AddStereotypeToArray($xnObjProp, 					$aStereotypes);
						GetXMLNodeValue($xnObjProp, 'ss:iconidentifier', 	$sNType);
						GetXMLNodeValue($xnObjProp, 'ss:ntype', 			$sNType);
						if ($xnObjProp->nodeName === 'ss:features')
						{
							$xnFDesc = GetXMLFirstChild($xnObjProp);
							if ($xnFDesc != null)
							{
								$xnTests = GetXMLFirstChild($xnFDesc);
								if ($xnTests != null)
								{
									$xnTDesc = GetXMLFirstChild($xnTests);
									if ($xnTDesc != null)
									{
										$xnTMem  = GetXMLFirstChild($xnTDesc);
										if ($xnTMem != null)
										{
											$xnTest = GetXMLFirstChild($xnTMem);
											if ($xnTest != null && $xnTest->childNodes != null)
											{
												$StartDate = '';
												$EndDate = '';
												foreach ($xnTest->childNodes as $xnTestProp)
												{
													GetXMLNodeValue($xnTestProp, 'ss:role',		 		$aResAllocDetails['role']);
													GetXMLNodeValue($xnTestProp, 'ss:startdate', 		$sStartDate);
													GetXMLNodeValue($xnTestProp, 'ss:enddate', 			$sEndDate);
													GetXMLNodeValue($xnTestProp, 'ss:percentagecomplete', $aResAllocDetails['percentcomplete']);
													GetXMLNodeValue($xnTestProp, 'ss:expectedtime', 	$aResAllocDetails['expectedtime']);
													GetXMLNodeValue($xnTestProp, 'ss:allocatedtime', 	$aResAllocDetails['allocatedtime']);
													GetXMLNodeValue($xnTestProp, 'ss:expendedtime', 	$aResAllocDetails['expendedtime']);
													GetXMLNodeValue($xnTestProp, 'dcterms:description', $aResAllocDetails['notes']);
													GetXMLNodeValue($xnTestProp, 'ss:history', 			$aResAllocDetails['history']);
													if ($xnTestProp->nodeName==="ss:resourcename")
													{
														$xnNode = $xnTestProp->firstChild;
														$xnNode = $xnNode->firstChild;
														$aResAllocDetails['resource'] = $xnNode->nodeValue;
													}
													GetXMLNodeValue($xnTestProp, 'ss:allowedrole', $sRolesCSV);
													GetXMLNodeValue($xnTestProp, 'ss:allowedname', $sAuthorsCSV);
												}
												$iPos = strpos($sStartDate, ' ');
												if ($iPos!==false)
												{
													$sStartDate = substr($sStartDate, 0, $iPos);
													$sStartDate = trim($sStartDate);
												}
												$aResAllocDetails['startdate'] = $sStartDate;
												$iPos = strpos($sEndDate, ' ');
												if ($iPos!==false)
												{
													$sEndDate = substr($sEndDate, 0, $iPos);
													$sEndDate = trim($sEndDate);
												}
												$aResAllocDetails['enddate'] = $sEndDate;
											}
										}
									}
								}
							}
						}
					}
					if (count($aStereotypes)> 0)
					{
						$sStereotype = getPrimaryStereotype($aStereotypes);
						$aObjectDetails['stereotypes'] = $aStereotypes;
					}
					$aResAllocDetails['allocatedtime'] = trim($aResAllocDetails['allocatedtime'], '.');
					$aObjectDetails['type'] 		= $sType;
					$aObjectDetails['restype'] 		= $sResType;
					$aObjectDetails['stereotype'] 	= $sStereotype;
					$aObjectDetails['ntype'] 		= $sNType;
					$aObjectDetails['imageurl']		= GetObjectImagePath($sType, $sResType, $sStereotype, $sNType, '16');
					if ( !strIsEmpty($sRolesCSV) )
						$aRoles		= explode(',', $sRolesCSV);
					if ( !strIsEmpty($sAuthorsCSV) )
						$aAuthors	= explode(',', $sAuthorsCSV);
				}
			}
		}
	}
?>