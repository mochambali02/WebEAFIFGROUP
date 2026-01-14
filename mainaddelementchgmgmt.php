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
$sChgMgmtType 	= '';
if (!strIsEmpty($sLinkType)) {
	$iPos = strpos($sLinkType, '|');
	if ($iPos !== false) {
		$sParentGUID = substr($sLinkType, 0, $iPos);
		$sChgMgmtType = substr($sLinkType, $iPos + 1);
	}
}
$aAuthors	= array();
$aStatuses	= array();
$aPriorities = array();
$aTypes		= array();
include('./data_api/get_shape_elementchgmgmt.php');
$sOSLCErrorMsg = BuildOSLCErrorString();
if (strIsEmpty($sOSLCErrorMsg)) {
	$aObjectDetails	= array();
	$sObjectGUID = $sParentGUID;
	include('./data_api/get_element.php');
	$sOSLCErrorMsg = BuildOSLCErrorString();
	if (strIsEmpty($sOSLCErrorMsg)) {
		$sObjectName 	= SafeGetArrayItem1Dim($aObjectDetails, 'namealias');
		$sObjImageURL	= SafeGetArrayItem1Dim($aObjectDetails, 'imageurl');
		$sObjectResType = SafeGetArrayItem1Dim($aObjectDetails, 'restype');
		$sObjectName 	= GetPlainDisplayName($sObjectName);
		$sDate1Desc 	= '';
		$sDate2Desc 	= '';
		$sPerson1Desc 	= '';
		$sPerson2Desc 	= '';
		GetChgMgmtFieldNames($sChgMgmtType, 1, $sDate1Desc, $sDate2Desc, $sPerson1Desc, $sPerson2Desc);
		$sToday = date('Y-m-d');
		$sReadOnlyProp = '';
		if (IsMobileBrowser()) {
			$sReadOnlyProp = 'readonly="readonly"';
		}
		$sMainClass = '';
		if (IsSessionSettingTrue('show_browser')) {
			$sMainClass = ' class="show-browser"';
		}
		echo '<div id="main-add-elementchgmgmt" ' . $sMainClass . '>';
		echo '<div id="add-elementchgmgmt-layout">';
		$sRichEditCtrlsCSV = '\'add-elementchgmgmt-notes-field,add-elementchgmgmt-history-field\'';
		$sDatePickerCtrlsCSV = '\'add-elementchgmgmt-date1-field|add-elementchgmgmt-date1-img,add-elementchgmgmt-date2-field|add-elementchgmgmt-date2-img\'';
		$sText = '';
		$sImageClass = '';
		GetChgMgtAddText($sChgMgmtType, $sText, $sImageClass);
		echo '<div class="webea-page-header">';
		echo '  <img alt="" src="images/spriteplaceholder.png" class="' . $sImageClass . '" onload="OnLoad_SetupSpecialCtrls(' . $sRichEditCtrlsCSV . ',' . $sDatePickerCtrlsCSV . ')"> ';
		echo    $sText . ' <img alt="' . $sObjectResType . '" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sObjImageURL) . '">';
		echo '  <div class="objectname-in-header">' .  htmlspecialchars($sObjectName) . '</div>';
		echo '</div>';
		echo '<div id="add-elementchgmgmt-form">';
		echo '<form class="add-elementchgmgmt-form" role="form" onsubmit="OnFormRunAddElementChgMgmt(event)">';
		echo '<table id="add-elementchgmgmt-table"><tbody>';
		echo '<tr class="add-elementchgmgmt-line">';
		echo '<td class="add-elementchgmgmt-label">' . _glt('Name') . '<span class="field-label-required">&nbsp;*</span></td>';
		echo '<td class="add-elementchgmgmt-field"><input id="add-elementchgmgmt-name-field" class="webea-main-styled-textbox" name="name" maxlength="255"></td>';
		echo '</tr>';
		if ($sChgMgmtType !== 'risk') {
			echo '<tr class="add-elementchgmgmt-line">';
			echo '<td class="add-element-label">' . _glt('Status') . '<span class="field-label-required">&nbsp;*</span></td>';
			echo '<td class="add-elementchgmgmt-field">';
			$aAttribs				= array();
			$aAttribs['id']			= 'add-elementchgmgmt-status-field';
			$aAttribs['name']		= 'status';
			$aAttribs['maxlength']	= '50';
			$aAttribs['class']		= 'webea-main-styled-combo';
			echo BuildHTMLComboFromArray($aStatuses, $aAttribs, true);
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementchgmgmt-line">';
			echo '<td class="add-element-label">' . _glt('Priority') . '<span class="field-label-required">&nbsp;*</span></td>';
			echo '<td class="add-elementchgmgmt-field">';
			$aAttribs				= array();
			$aAttribs['id']			= 'add-elementchgmgmt-priority-field';
			$aAttribs['name']		= 'priority';
			$aAttribs['maxlength']	= '50';
			$aAttribs['class']		= 'webea-main-styled-combo';
			echo BuildHTMLComboFromArray($aPriorities, $aAttribs);
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementchgmgmt-line">';
			echo '<td class="add-element-label">' . $sPerson1Desc . '</td>';
			echo '<td class="add-elementchgmgmt-field">';
			$aAttribs				= array();
			$aAttribs['id']			= 'add-elementchgmgmt-author1-field';
			$aAttribs['name']		= 'author1';
			$aAttribs['maxlength']	= '255';
			$aAttribs['class']		= 'webea-main-styled-combo';
			echo BuildHTMLComboFromArray($aAuthors, $aAttribs);
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementchgmgmt-line">';
			echo '<td class="add-elementchgmgmt-label">' . $sDate1Desc . '&nbsp;&nbsp;</td>';
			echo '<td class="add-elementchgmgmt-field"><input id="add-elementchgmgmt-date1-field" class="webea-main-styled-date" name="date1" placeholder="yyyy-mm-dd" value="' . $sToday . '" onblur="OnDateFieldLostFocus(\'add-elementchgmgmt-date1-field\')" ' . $sReadOnlyProp . '>';
			echo '<div style="display: none;"><img id="add-elementchgmgmt-date1-img" class="ss-calendar-icon" alt="" title="Choose date from calendar" src="images/spriteplaceholder.png"></div>';
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementchgmgmt-line">';
			echo '<td class="add-element-label">' . $sPerson2Desc . '</td>';
			echo '<td class="add-elementchgmgmt-field">';
			$aAttribs				= array();
			$aAttribs['id']			= 'add-elementchgmgmt-author2-field';
			$aAttribs['name']		= 'author2';
			$aAttribs['maxlength']	= '255';
			$aAttribs['class']		= 'webea-main-styled-combo';
			echo BuildHTMLComboFromArray($aAuthors, $aAttribs, true);
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementchgmgmt-line">';
			echo '<td class="add-elementchgmgmt-label">' . $sDate2Desc . '&nbsp;&nbsp;</td>';
			echo '<td class="add-elementchgmgmt-field"><input id="add-elementchgmgmt-date2-field" class="webea-main-styled-date" name="date2" placeholder="yyyy-mm-dd" onblur="OnDateFieldLostFocus(\'add-elementchgmgmt-date2-field\')" ' . $sReadOnlyProp . '>';
			echo '<div style="display: none;"><img id="add-elementchgmgmt-date2-img" class="ss-calendar-icon" alt="" title="Choose date from calendar" src="images/spriteplaceholder.png"></div>';
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementchgmgmt-line">';
			echo '<td class="add-elementchgmgmt-label">' . _glt('Version') . '</td>';
			echo '<td class="add-elementchgmgmt-field"><input id="add-elementchgmgmt-version-field" class="webea-main-styled-number" name="version" value="1.0" maxlength="10"></td>';
			echo '</tr>';
		} else {
			echo '<tr class="add-elementchgmgmt-line">';
			echo '<td class="add-elementchgmgmt-label">' . _glt('Type') . '<span class="field-label-required">&nbsp;*</span></td>';
			echo '<td class="add-elementchgmgmt-field">';
			$aAttribs				= array();
			$aAttribs['id']			= 'add-elementchgmgmt-type-field';
			$aAttribs['name']		= 'type';
			$aAttribs['maxlength']	= '12';
			$aAttribs['class']		= 'webea-main-styled-combo';
			echo BuildHTMLComboFromArray($aTypes, $aAttribs, true);
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementchgmgmt-line">';
			echo '<td class="add-elementchgmgmt-label">' . _glt('Weight') . '</td>';
			echo '<td class="add-elementchgmgmt-field"><input id="add-elementchgmgmt-weight-field" class="webea-main-styled-number" name="weight" type="number" value="1" maxlength="10"></td>';
			echo '</tr>';
		}
		echo '<tr class="add-elementchgmgmt-line">';
		echo '<td class="add-elementchgmgmt-label field-label-vert-align-top">' . _glt('Description') . '</td>';
		echo '<td class="add-elementchgmgmt-field"><div id="add-elementchgmgmt-notes-div"><textarea id="add-elementchgmgmt-notes-field" name="notes" onfocus="EnsureInputFieldVisible(this)"></textarea></div></td>';
		echo '</tr>';
		if ($sChgMgmtType !== 'risk') {
			echo '<tr class="add-elementchgmgmt-line">';
			echo '<td class="add-elementchgmgmt-label field-label-vert-align-top">' . _glt('History') . '</td>';
			echo '<td class="add-elementchgmgmt-field"><div id="add-elementchgmgmt-notes-div"><textarea id="add-elementchgmgmt-history-field" name="history" onfocus="EnsureInputFieldVisible(this)"></textarea></div></td>';
			echo '</tr>';
		}
		echo '<tr class="add-elementchgmgmt-line">';
		echo '<td class="add-elementchgmgmt-label">&nbsp;</td>';
		echo '<td class="add-elementchgmgmt-field"><input class="webea-main-styled-button add-elementchgmgmt-submit" type="submit" value="' . _glt('Add') . '"></td>';
		echo '</tr>';
		echo '</tbody></table>';
		echo '<input type="hidden" name="guid" value="' . $sObjectGUID . '">';
		echo '<input type="hidden" name="featuretype" value="' . $sChgMgmtType . '">';
		echo '<input type="hidden" name="parentname" value="' . htmlspecialchars($sObjectName, ENT_COMPAT) . '">';
		echo '<input type="hidden" name="parentimageurl" value="' . $sObjImageURL . '">';
		$sLoginGUID 	= SafeGetInternalArrayParameter($_SESSION, 'login_guid', '');
		echo '<input type="hidden" name="loginguid" value="' . $sLoginGUID . '">';
		echo '</form>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}
}
