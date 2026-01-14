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
$g_sViewingMode		= '2';
$aObjs = array();
include('./data_api/get_diagramobjects.php');
echo '<div id="main-diagram-list" class="diagram-related-bkcolor">';
$iCnt = count($aObjs);
if ($iCnt > 0) {
	echo '<div class="main-diagram-inner">';
	echo '<div id="diagram-specman">';
	for ($i = 0; $i < $iCnt; $i++) {
		$sLevel		= SafeGetArrayItem2Dim($aObjs, $i, 'level');
		$sName 		= GetPlainDisplayName(SafeGetArrayItem2Dim($aObjs, $i, 'name'));
		$sNameEsc	= htmlspecialchars($sName);
		$sGUID		= SafeGetArrayItem2Dim($aObjs, $i, 'guid');
		$sResType	= SafeGetArrayItem2Dim($aObjs, $i, 'restype');
		$sHasChild	= SafeGetArrayItem2Dim($aObjs, $i, 'haschild');
		$sNotes		= SafeGetArrayItem2Dim($aObjs, $i, 'notes');
		$sNType		= SafeGetArrayItem2Dim($aObjs, $i, 'ntype');
		$sImageURL	= SafeGetArrayItem2Dim($aObjs, $i, 'imageurl');
		$bObjLocked	= SafeGetArrayItem2Dim($aObjs, $i, 'locked');
		$sObjLockedType	= SafeGetArrayItem2Dim($aObjs, $i, 'lockedtype');
		$sAlias 	= SafeGetArrayItem2Dim($aObjs, $i, 'alias');
		$sImageURL	= AdjustImagePath($sImageURL, '16');
		if (strIsEmpty($sName) && !strIsEmpty($sAlias))
			$sName = $sAlias;
		else if ($sNType === '19' && !strIsEmpty($sAlias))
			$sName = $sAlias;
		$sNameInLink = LimitDisplayString($sName, 255);
		$sName 		= htmlspecialchars($sName);
		$sLoadObject = 'javascript:load_object(\'' . $sGUID . '\',\'\',\'\',\'\',\'' . ConvertStringToParameter($sNameInLink) . '\',\'' . $sImageURL . '\')';
		if ($sHasChild === 'true' && $bObjLocked) {
			$sImageLink = '<img class="diagram-specman-img-image" style="background:url(' . $sImageURL . ') no-repeat 0px 0px" src="images/element16/lockedhaschildoverlay.png" alt="" title="' . _glt('View child objects for locked') . ' ' . $sResType . '" height="16" width="16"/>';
		} elseif ($sHasChild === 'true' && !$bObjLocked) {
			$sImageLink = '<img class="diagram-specman-img-image" style="background:url(' . $sImageURL . ') no-repeat 0px 0px" src="images/element16/haschildoverlay.png" alt="" title="' . _glt('View child objects') . '" height="16" width="16"/>';
		} elseif ($sHasChild === 'false' && $bObjLocked) {
			$sImageLink = '<img class="diagram-specman-img-image" style="background:url(' . $sImageURL . ') no-repeat 0px 0px" src="images/element16/lockedoverlay.png" alt="" title="' . $sResType . ' ' . _glt('is locked') . '" height="16" width="16"/>';
		} else {
			$sImageLink = '<img class="diagram-specman-img-image" src="' . $sImageURL . '" alt="" title="" height="16" width="16"/>';
		}
		$sIdentStyle = ' style="padding-left: ' . ((($sLevel - 1) * 20) + 4) . 'px;"';
		$sNameSize = 'diagram-specman-item-name-size3';
		if ($sLevel > 0 && $sLevel < 3)
			$sNameSize = 'diagram-specman-item-name-size' . $sLevel;
		echo '<div class="diagram-specman-item" ' . $sIdentStyle . '>';
		echo '<div class="diagram-specman-item-name ' . $sNameSize . '" onclick="' . $sLoadObject . '">' . $sImageLink . $sName . '</div>';
		echo '<div class="diagram-specman-item-notes">' . $sNotes . '</div>';
		echo '</div>';
	}
	echo '</div>';
	echo '</div>';
	echo '</div>' . PHP_EOL;
} else {
	$sOSLCErrorMsg = BuildOSLCErrorString();
	if (strIsEmpty($sOSLCErrorMsg)) {
		echo '<div class="main-diagram-inner">' . _glt('No objects on the diagram') . '</div>';
	} else {
		echo '<div class="main-diagram-inner">' . $sOSLCErrorMsg . '</div>';
	}
}
echo '</div>' . PHP_EOL;
