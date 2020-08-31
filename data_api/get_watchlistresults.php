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
	BuildOSLCConnectionString();
	$sOSLC_URL = $g_sOSLCString . 'wlresource/';
	SafeStartSession();
	if  ( !isset($webea_page_parent_mainview) ||
		   isset($sWatchlistClause) === false ||
		   isset($_SESSION['authorized']) === false)
	{
		exit();
	}
	$sPostData = '';
	if ( !strIsEmpty($sWatchlistClause) )
	{
		$sLoginNode = '';
		$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
		if ( !strIsEmpty($sLoginGUID) )
		{
			$sLoginNode = '<ss:useridentifier>' . $sLoginGUID . '</ss:useridentifier>';
		}
		$sPostData  = '<?xml version="1.0" encoding="utf-8"?>';
		$sPostData .= '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:ss="http://www.sparxsystems.com.au/oslc_am#">';
		$sPostData .= '<ss:watchlist rdf:about="' . $sOSLC_URL . '">';
		$sPostData .= '<ss:clause>' . $sWatchlistClause  . '</ss:clause>';
		$sPostData .= $sLoginNode;
		$sPostData .= '</ss:watchlist>';
		$sPostData .= '</rdf:RDF>';
		$xmlRespDoc = null;
		$xmlRespDoc = HTTPPostXML($sOSLC_URL, $sPostData);
		$sOSLCErrorMsg = BuildOSLCErrorString();
		if ( strIsEmpty($sOSLCErrorMsg) )
		{
			if ($xmlRespDoc != null)
			{
				$xnRoot = $xmlRespDoc->documentElement;
				$xnDesc = GetXMLFirstChild($xnRoot);
				if ($xnDesc != null && $xnDesc->childNodes != null)
				{
					foreach ($xnDesc->childNodes as $xnMem)
					{
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
						$xnRes = GetXMLFirstChild($xnMem);
						if ($xnRes != null)
						{
							$xnObjProps = $xnRes->getElementsByTagNameNS("*", "*");
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
							$aRows[] = $aRow;
						}
					}
				}
			}
		}
	}
?>