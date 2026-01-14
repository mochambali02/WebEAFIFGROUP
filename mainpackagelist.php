<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if (!isset($webea_page_parent_mainview)) {
	exit();
}
$sRootPath = dirname(__FILE__);
require_once $sRootPath . '/globals.php';
SafeStartSession();
if (isset($_SESSION['authorized']) === false) {
	$sErrorMsg = _glt('Your session appears to have timed out');
	setResponseCode(440, $sErrorMsg);
	exit();
}
$g_sViewingMode		= '1';
$bAddElements	= IsSessionSettingTrue('add_objects') && IsSessionSettingTrue('login_perm_element');
$bAddPackages	= IsSessionSettingTrue('add_objecttype_package') && IsSessionSettingTrue('login_perm_element');
$bShowMiniProps = IsSessionSettingTrue('show_miniproperties');
$aObjs = array();
$sErrorMsg = '';
include('./data_api/get_objects.php');
echo '<div id="main-package-list" class="package-related-bkcolor ' . GetShowBrowserMinPropsStyleClasses() . '">';
$iCnt = count($aObjs);
if ($iCnt > 0) {
	echo '<div class="main-package-inner">';
	echo '<table id="package-list-table"> <tbody>' . PHP_EOL;
	echo '<tr class="package-list-tr">';
	echo '  <th class="package-list-th"></th>';
	echo '  <th class="package-list-th">' . _glt('Name') . '</th>';
	echo '  <th class="package-list-th">' . _glt('Type') . '</th>';
	echo '  <th class="package-list-th">' . _glt('Author') . '</th>';
	echo '  <th class="package-list-th">' . _glt('Modified') . '</th>';
	echo '</tr>' . PHP_EOL;
	for ($i = 0; $i < $iCnt; $i++) {
		$sName 		= GetPlainDisplayName(SafeGetArrayItem2Dim($aObjs, $i, 'text'));
		$sGUID		= SafeGetArrayItem2Dim($aObjs, $i, 'guid');
		$sResType	= SafeGetArrayItem2Dim($aObjs, $i, 'restype');
		$sHasChild	= SafeGetArrayItem2Dim($aObjs, $i, 'haschild');
		$sType		= SafeGetArrayItem2Dim($aObjs, $i, 'type');
		$sNotes		= SafeGetArrayItem2Dim($aObjs, $i, 'notes');
		$sNType		= SafeGetArrayItem2Dim($aObjs, $i, 'ntype');
		$sLinkType	= SafeGetArrayItem2Dim($aObjs, $i, 'linktype');
		$sHyper		= SafeGetArrayItem2Dim($aObjs, $i, 'hyper');
		$sImageURL	= SafeGetArrayItem2Dim($aObjs, $i, 'imageurl');
		$bObjLocked	= SafeGetArrayItem2Dim($aObjs, $i, 'locked');
		$sObjLockedType	= SafeGetArrayItem2Dim($aObjs, $i, 'lockedtype');
		$sAuthor 	= SafeGetArrayItem2Dim($aObjs, $i, 'author');
		$sAlias 	= SafeGetArrayItem2Dim($aObjs, $i, 'alias');
		$sModified 	= SafeGetArrayItem2Dim($aObjs, $i, 'modified');
		$sImageURL	= AdjustImagePath($sImageURL, '16');
		if (strIsEmpty($sName) && !strIsEmpty($sAlias))
			$sName = $sAlias;
		else if ($sNType === '19' && !strIsEmpty($sAlias))
			$sName = $sAlias;
		$sNameInLink = LimitDisplayString($sName, 255);
		$sName 		= htmlspecialchars($sName);
		$sAuthor	= htmlspecialchars($sAuthor);
		$sHref = 'javascript:load_object(\'' . $sGUID . '\',\'\',\'\',\'\',\'' . ConvertStringToParameter($sNameInLink) . '\',\'' . $sImageURL . '\')';
		$sImageHTML  = '';
		$sImageHTML .= '<div class="package-list-img-image">';
		if ($sHasChild === 'true' && $bObjLocked) {
			$sImageHTML .= '<span class="package-list-img-span element16-lockedhaschildoverlay" alt="" title="' . _glt('View child objects for locked') . ' ' . $sResType . '"></span>';
		} elseif ($sHasChild === 'true' && !$bObjLocked) {
			$sImageHTML .= '<span class="package-list-img-span element16-haschildoverlay" alt="" title="' . _glt('View child objects') . '"></span>';
		} elseif ($sHasChild === 'false' && $bObjLocked) {
			$sImageHTML .= '<span class="package-list-img-span element16-lockedoverlay" alt="" title="' . $sResType . ' ' . _glt('is locked') . '"></span>';
		}
		$sImageHTML .= '  <img src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sImageURL) . '" alt="" title=""/>';
		$sImageHTML .= '</div>';
		$sEvenLine = '';
		if (($i % 2) === 0)
			$sEvenLine = ' package-list-tr-nth-child';
		echo '<tr class="package-list-tr' . $sEvenLine . '" onclick="' . $sHref . '">';
		echo '  <td class="package-list-td">' . $sImageHTML . '</td>';
		echo '  <td class="package-list-td package-list-td-name">' . $sName . '</td>';
		echo '  <td class="package-list-td noWrapCell">' . $sType . '</td>';
		echo '  <td class="package-list-td noWrapCell">' . $sAuthor . '</td>';
		echo '  <td class="package-list-td noWrapCell">' . $sModified . '</td>';
		echo '</tr>' . PHP_EOL;
	}
	if ($bAddElements) {
		$sAddGUID 			= 'addelement';
		$sAddObjectName 	= _glt('Add new element');
		$sAddObjectImageName = 'element16-add';
		$bCanAdd 			= true;
		if (strIsEmpty($sObjectGUID)) {
			$sAddGUID 		= 'addmodelroot';
			$sAddObjectName = _glt('Add Root Node');
			$sAddObjectImageName = 'element16-addmodelroot';
			$bCanAdd 		= $bAddPackages;
		} elseif (substr($sObjectGUID, 0, 3) === 'mr_') {
			$sAddGUID 		= 'addviewpackage';
			$sAddObjectName = _glt('Add View');
			$sAddObjectImageName = 'element16-addviewpackage';
			$bCanAdd 		= $bAddPackages;
		}
		if ($bCanAdd) {
			$sNameInLink = LimitDisplayString($sObjectName, 255);
			echo '<tr class="package-list-tr" title="' . $sAddObjectName . '" onclick="load_object(\'' . $sAddGUID . '\',\'false\',\'' . $sObjectGUID . '|' . addslashes($sObjectName) . '\',\'\',\'' . ConvertStringToParameter($sNameInLink) . '\',\'' . $sObjectImageURL . '\')">';
			echo '  <td class="package-list-td">';
			echo '    <div class="package-list-img-image">';
			echo '      <img src="images/spriteplaceholder.png" class="' . $sAddObjectImageName . '" alt="" title=""/>';
			echo '    </div>';
			echo '  </td>';
			echo '  <td class="package-list-td package-list-td-name w3-italic">&lt;' . _glt('New') . '&gt;</td>';
			echo '  <td class="package-list-td noWrapCell"></td>';
			echo '  <td class="package-list-td noWrapCell"></td>';
			echo '  <td class="package-list-td noWrapCell"></td>';
			echo '</tr>' . PHP_EOL;
		}
	}
	echo '</tbody></table>';
	echo '</div>';
	echo '</div>' . PHP_EOL;
} else {
	$sOSLCErrorMsg = BuildOSLCErrorString();
	if (strIsEmpty($sOSLCErrorMsg)) {
		if (strIsEmpty($sObjectGUID)) {
			echo '<div class="main-package-inner">' . _glt('Problem reading model root') . '</div>';
		} else {
			if ($bAddElements) {
				$sAddGUID 			= 'addelement';
				$sAddObjectName 	= _glt('Add new element');
				$sAddObjectImageName = 'element16-add';
				$bCanAdd 			= true;
				if (strIsEmpty($sObjectGUID)) {
					$sAddGUID 		= 'addmodelroot';
					$sAddObjectName = _glt('AAdd Root Node');
					$sAddObjectImageName = 'element16-addmodelroot';
					$bCanAdd 		= $bAddPackages;
				} elseif (substr($sObjectGUID, 0, 3) === 'mr_') {
					$sAddGUID 		= 'addviewpackage';
					$sAddObjectName = _glt('Add View');
					$sAddObjectImageName = 'element16-addviewpackage';
					$bCanAdd 		= $bAddPackages;
				}
				if ($bCanAdd) {
					echo '<div class="main-package-inner">';
					echo '<table id="package-list-table"> <tbody>' . PHP_EOL;
					echo '<tr class="package-list-tr">';
					echo '  <th class="package-list-th"></th>';
					echo '  <th class="package-list-th">' . _glt('Name') . '</th>';
					echo '  <th class="package-list-th">' . _glt('Type') . '</th>';
					echo '  <th class="package-list-th">' . _glt('Author') . '</th>';
					echo '  <th class="package-list-th">' . _glt('Modified') . '</th>';
					echo '</tr>' . PHP_EOL;
					echo '<tr class="package-list-tr" title="' . _glt('Add new element') . '" onclick="load_object(\'' . $sAddGUID . '\',\'false\',\'' . $sObjectGUID . '|' . addslashes($sObjectName) . '\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\',\'' . $sObjectImageURL . '\')">';
					echo '  <td class="package-list-td">';
					echo '    <div class="package-list-img-image">';
					echo '      <img src="images/spriteplaceholder.png" class="' . $sAddObjectImageName . '" alt="" title=""/>';
					echo '    </div>';
					echo '  </td>';
					echo '  <td class="package-list-td package-list-td-name w3-italic">&lt;' . _glt('New') . '&gt;</td>';
					echo '  <td class="package-list-td noWrapCell"></td>';
					echo '  <td class="package-list-td noWrapCell"></td>';
					echo '  <td class="package-list-td noWrapCell"></td>';
					echo '</tr>' . PHP_EOL;
					echo '</tbody></table>';
					echo '</div>';
				}
			} else {
				echo '<div class="main-package-inner">' . _glt('No child elements') . '</div>';
			}
		}
	} else {
		echo '<div class="main-package-inner">' . $sOSLCErrorMsg . '</div>';
	}
}
echo '</div>' . PHP_EOL;
