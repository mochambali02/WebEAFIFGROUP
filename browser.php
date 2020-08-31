<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	$sRootPath = dirname(__FILE__);
	require_once $sRootPath . '/globals.php';
	if  ( !isset($webea_page_parent_mainview) )
	{
		exit();
	}
	SafeStartSession();
	$sHome 			= isset($_SESSION['model_name']) ? $_SESSION['model_name'] : 'Home';
	BuildOSLCConnectionString();
	$webea_page_parent_browser = true;
	include('./data_api/get_browserobjects.php');
	unset($webea_page_parent_browser);
	$sBrowserParentGUID = '';
	$sBrowserParentName = '';
	$sBrowserParentImageURL = '';
	$sSelectedObjectGUID = '';
	if (isset($sLinkTypeGUID))
	{
		$sSelectedObjectGUID = $sLinkTypeGUID;
	}
	else
	{
		$sSelectedObjectGUID = $sObjectGUID;
	}
	echo '<div id="main-browser-view">';
	echo '<div class="browser-view-section">';
		$iCnt = count($aBrowserObjs);
		for ($i=0; $i<$iCnt; $i++)
		{
			$sObjName = SafeGetArrayItem2Dim($aBrowserObjs, $i, 'text');
			$sObjGUID = SafeGetArrayItem2Dim($aBrowserObjs, $i, 'guid');
			$sObjType = SafeGetArrayItem2Dim($aBrowserObjs, $i, 'type');
			$sObjNType = SafeGetArrayItem2Dim($aBrowserObjs, $i, 'ntype');
			$sObjResType = SafeGetArrayItem2Dim($aBrowserObjs, $i, 'restype');
			$sObjStructure = SafeGetArrayItem2Dim($aBrowserObjs, $i, 'structid');
			$sImageURL = SafeGetArrayItem2Dim($aBrowserObjs, $i, 'imageurl');
			$bIsLocked =	SafeGetArrayItem2Dim($aBrowserObjs, $i, 'locked');
			$bHasChild 		= SafeGetArrayItem2Dim($aBrowserObjs, $i, 'haschild');
			$sImageURL = AdjustImagePath($sImageURL, '16');
			$sName = htmlspecialchars($sObjName);
			if ($sObjGUID === 'home_link')
			{
				$sHref = 'javascript:load_object(\'\',\'true\',\'\',\'\',\'' . ConvertStringToParameter($sHome) . '\',\'home.png\')';
			}
			elseif ( $sObjResType !== 'Diagram')
			{
				$sHref = 'javascript:load_object(\'' . $sObjGUID. '\',\'\',\'props\',\'\',\'' . ConvertStringToParameter($sObjName) . '\',\'' . $sImageURL . '\')';
			}
			else
			{
				$sHref = 'javascript:load_object(\'' . $sObjGUID. '\',\'\',\'\',\'\',\'' . ConvertStringToParameter($sObjName) . '\',\'' . $sImageURL . '\')';
			}
			if ($i===0)
			{
				echo '<table class="browser-current-table"><tbody>';
				$sSelected = ($sSelectedObjectGUID === $sObjGUID)? 'selected' : '';
				if ($sObjGUID === 'home')
				{
					echo '<tr class="browser-table-tr" style="cursor: default;">';
				}
				else
				{
					echo '<tr class="browser-table-tr" onclick="' . $sHref . '">';
				}
				echo '  <td class="browser-item-image-td" style="width: 18px;"><img alt="" src="images/spriteplaceholder.png" class="' .  GetObjectImageSpriteName($sImageURL) . '"></td>';
				echo '  <td class="browser-item-name-td ' . $sSelected . '" title="'. GetPlainDisplayName($sName) . '">' . GetPlainDisplayName($sName) . '</td>';
				echo '</tr>' . PHP_EOL;
				echo '</tbody></table>';
				$sBrowserParentGUID = $sObjGUID;
				$sBrowserParentName = $sName;
				$sBrowserParentImageURL = $sImageURL;
			}
			else if ($i===1)
			{
				echo '<div class="browser-contents-elements-div">';
				echo '<table id="context-browser-table"><tbody>' . PHP_EOL;
				if ($aBrowserObjs[0]['guid'] !== 'home')
				{
					echo '<tr class="browser-table-tr" onclick="'. $sHref . '">';
					echo '  <td class="browser-item-image-td"><img src="images/spriteplaceholder.png" class="mainsprite-browserup" alt="" title=""></td>';
					echo '  <td class="browser-item-name-td">...</td>';
					echo '</tr>' . PHP_EOL;
				}
			}
			else
			{
				$sSelected = ($sSelectedObjectGUID === $sObjGUID)? 'selected' : '';
				echo '<tr class="browser-table-tr" onclick="' . $sHref . '">';
				echo '  <td class="browser-item-image-td">' . BuildImageHTML($sObjResType, $sImageURL, $bHasChild, $bIsLocked, '16'). '</td>';
				echo '  <td class="browser-item-name-td ' . $sSelected . '" title="'. GetPlainDisplayName($sName) . '">' . GetPlainDisplayName($sName) . '</td>';
				echo '</tr>' . PHP_EOL;
			}
		}
		$bAddElements	= IsSessionSettingTrue('add_objects') && IsSessionSettingTrue('login_perm_element');
		$bAddPackages	= IsSessionSettingTrue('add_objecttype_package') && IsSessionSettingTrue('login_perm_element');
		if ($bAddElements)
		{
			$sAddGUID 			= 'addelement';
			$sAddObjectName 	= _glt('Add new element');
			$sAddObjectImageName = 'element16-add';
			$bCanAdd 			= true;
			if ( (strIsEmpty($sObjectGUID)) || ($sObjectGUID === 'addmodelroot'))
			{
				$sAddGUID 		= 'addmodelroot';
				$sAddObjectName = _glt('Add Root Node');
				$sAddObjectImageName = 'element16-addmodelroot';
				$bCanAdd 		= $bAddPackages;
				$sBrowserParentGUID = '';
				$sBrowserParentName = '';
				$sBrowserParentImageURL = '';
			}
			elseif ( (substr($sObjectGUID,0,3) === 'mr_') || $sObjectGUID === 'addviewpackage' )
			{
				$sAddGUID 		= 'addviewpackage';
				$sAddObjectName = _glt('Add View');
				$sAddObjectImageName = 'element16-addviewpackage';
				$bCanAdd 		= $bAddPackages;
			}
			if ( $bCanAdd )
			{
				$sNameInLink = LimitDisplayString($sBrowserParentName, 255);
				$sHref = 'load_object(\'' . $sAddGUID . '\',\'false\',\'' . $sBrowserParentGUID . '|' . addslashes($sBrowserParentName) . '\',\'\',\'' . ConvertStringToParameter($sNameInLink) .'\',\'' . $sBrowserParentImageURL . '\')';
				echo '<tr class="browser-table-tr" onclick="' . $sHref . '">';
				echo '  <td class="browser-item-image-td">';
				echo '    <div class="package-list-img-image">';
				echo '      <img src="images/spriteplaceholder.png" class="' . $sAddObjectImageName . '" alt="" title=""/>';
				echo '    </div>';
				echo '</td>';
				echo '  <td class="browser-item-name-td" title="'. $sAddObjectName . '">&lt;' . _glt('New') . '&gt;</td>';
				echo '</tr>' . PHP_EOL;
			}
		}
		echo '</tbody></table>';
		echo '</div>';
	echo '</div>';
	echo '</div>';
function BuildImageHTML($sResType, $sImageURL, $sHasChild, $bObjLocked, $sNewSize = '')
{
	if ( !strIsEmpty($sNewSize) )
	{
		$sImageURL = AdjustImagePath($sImageURL, $sNewSize);
	}
	$sImageHTML  = '';
	$sImageHTML .= '<div class="inline-element-image">';
	$bShowChildren = strIsTrue($sHasChild);
	if ( $sResType === 'Package' || $sResType === 'ModelRoot' )
		$bShowChildren = false;
	if ( $bShowChildren && $bObjLocked )
	{
		$sImageHTML .= '<span class="package-list-img-span element16-lockedhaschildoverlay" alt="" title="' . _glt('View child objects for locked') . ' ' . $sResType . '"></span>';
	}
	elseif ( $bShowChildren && !$bObjLocked )
	{
		$sImageHTML .= '<span class="package-list-img-span element16-haschildoverlay" alt="" title="' . _glt('View child objects') . '"></span>';
	}
	elseif ( !$bShowChildren && $bObjLocked )
	{
		$sImageHTML .= '<span class="package-list-img-span element16-lockedoverlay" alt="" title="' . $sResType . ' ' . _glt('is locked') . '"></span>';
	}
	$sImageHTML .= '  <img src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sImageURL) . '" alt="" title=""/>';
	$sImageHTML .= '</div>';
	return $sImageHTML;
}
?>