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
$sParentGUID 	= '';
$sParentName 	= '';
if (!strIsEmpty($sLinkType)) {
	$iPos = strpos($sLinkType, '|');
	if ($iPos !== FALSE) {
		$sParentGUID = substr($sLinkType, 0, $iPos);
		$sParentName = substr($sLinkType, $iPos + 1);
		$sParentName = trim($sParentName);
	}
}
$sObjectGUID_bkup = $sObjectGUID;
$sObjectGUID 	= $sParentGUID;
$aObjectDetails	= array();
include('./data_api/get_element.php');
$sOSLCErrorMsg = BuildOSLCErrorString();
if (strIsEmpty($sOSLCErrorMsg)) {
	$sObjectGUID 	= $sObjectGUID_bkup;
	$sObjectName 	= GetPlainDisplayName($sObjectName);
	$sResType 		= SafeGetArrayItem1Dim($aObjectDetails, 'restype');
	$sNotes			= SafeGetArrayItem1Dim($aObjectDetails, 'notes');
	$sImageURL		= SafeGetArrayItem1Dim($aObjectDetails, 'imageurl');
	$sMainClass = '';
	if (IsSessionSettingTrue('show_browser')) {
		$sMainClass = ' class="show-browser"';
	}
	echo '<div id="main-edit-elementnote"' . $sMainClass . '>';
	echo '<div id="edit-elementnote-layout">';
	echo '<div class="webea-page-header">';
	echo '<img alt="" src="images/spriteplaceholder.png" class="propsprite-noteedit" onload="OnLoad_SetupSpecialCtrls(\'edit-elementnote-notes-field\',\'\')"> ';
	echo   _glt('Edit note for') . '&nbsp;<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sImageURL) . '">';
	echo   '<div class="objectname-in-header">' . htmlspecialchars($sParentName) . '</div>';
	echo '</div>';
	echo '<div id="edit-elementnote-form">';
	echo '<form class="edit-elementnote-form" role="form" onsubmit="OnFormRunEditElementNote(event)">';
	echo '<div class="edit-elementnote-label">' . _glt('Notes') . '</div>';
	echo '<div id="edit-elementnote-notes-div"><textarea id="edit-elementnote-notes-field" name="notes" onfocus="EnsureInputFieldVisible(this)">' . ConvertEANoteToHTML($sNotes) . '</textarea></div>';
	echo '<input type="hidden" name="parentguid" value="' . $sParentGUID . '">';
	echo '<input type="hidden" name="parentname" value="' . htmlspecialchars($sParentName, ENT_COMPAT) . '">';
	echo '<input type="hidden" name="parentimageurl" value="' . $sImageURL . '">';
	$sLoginGUID 	= SafeGetInternalArrayParameter($_SESSION, 'login_guid', '');
	echo '<input type="hidden" name="loginguid" value="' . $sLoginGUID . '">';
	echo '<div"><input class="webea-main-styled-button edit-elementnote-submit" type="submit" value="' . _glt('Save') . '"></div>';
	echo '</form>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
}
