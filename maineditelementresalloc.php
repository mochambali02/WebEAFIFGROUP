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
$sResource 		= '';
if (!strIsEmpty($sLinkType)) {
	$iPos = strpos($sLinkType, '|');
	if ($iPos !== false) {
		$sParentGUID = substr($sLinkType, 0, $iPos);
		$sResource = substr($sLinkType, $iPos + 1);
	}
}
$sRole		= SafeGetInternalArrayParameter($_POST, 'hyper');
$aObjectDetails	= array();
$aResAllocDetails = array();
$aRoles			= array();
$aAuthors		= array();
$sOSLCErrorMsg	= '';
include('./data_api/get_elementresalloc.php');
$sObjectName 	= SafeGetArrayItem1Dim($aObjectDetails, 'name');
$sObjImageURL	= SafeGetArrayItem1Dim($aObjectDetails, 'imageurl');
$sObjectResType = SafeGetArrayItem1Dim($aObjectDetails, 'restype');
$sObjectName 	= GetPlainDisplayName($sObjectName);
$sEditResource	= SafeGetArrayItem1Dim($aResAllocDetails, 'resource');
$sEditRole 		= SafeGetArrayItem1Dim($aResAllocDetails, 'role');
$sStartDate		= SafeGetArrayItem1Dim($aResAllocDetails, 'startdate');
$sEndDate		= SafeGetArrayItem1Dim($aResAllocDetails, 'enddate');
$sPercentComplete = SafeGetArrayItem1Dim($aResAllocDetails, 'percentcomplete');
$sExpectedTime	= SafeGetArrayItem1Dim($aResAllocDetails, 'expectedtime');
$sAllocatedTime	= SafeGetArrayItem1Dim($aResAllocDetails, 'allocatedtime');
$sExpendedTime	= SafeGetArrayItem1Dim($aResAllocDetails, 'expendedtime');
$sNotes 		= SafeGetArrayItem1Dim($aResAllocDetails, 'notes');
$sHistory		= SafeGetArrayItem1Dim($aResAllocDetails, 'history');
$sReadOnlyProp = '';
if (IsMobileBrowser()) {
	$sReadOnlyProp = 'readonly="readonly"';
}
$sMainClass = '';
if (IsSessionSettingTrue('show_browser')) {
	$sMainClass = ' class="show-browser"';
}
echo '<div id="main-edit-elementresalloc"' . $sMainClass . '>';
echo '<div id="edit-elementresalloc-layout">';
$sRichEditCtrlsCSV = '\'edit-elementresalloc-notes-field,edit-elementresalloc-history-field\'';
$sDatePickerCtrlsCSV = '\'edit-elementresalloc-startdate-field|edit-elementresalloc-startdate-img,edit-elementresalloc-enddate-field|edit-elementresalloc-enddate-img\'';
echo '<div class="webea-page-header">';
echo '  <img alt="" src="images/spriteplaceholder.png" class="propsprite-resallocedit" onload="OnLoad_SetupSpecialCtrls(' . $sRichEditCtrlsCSV . ',' . $sDatePickerCtrlsCSV . ')"> ';
echo    _glt('Edit resource allocation for') . '&nbsp;<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sObjImageURL) . '">';
echo '  <div class="objectname-in-header">' . htmlspecialchars($sObjectName) . '</div>';
echo '</div>';
echo '<div id="edit-elementresalloc-form">';
echo '<form class="edit-elementresalloc-form" role="form" onsubmit="OnFormRunEditElementResAlloc(event)">';
echo '<table id="edit-elementresalloc-table"><tbody>';
echo '<tr class="edit-elementresalloc-line">';
echo '<td class="edit-elementresalloc-label">' . _glt('Resource') . '<span class="field-label-required"> *</span></td>';
echo '<td class="edit-elementresalloc-field">';
$aAttribs				= array();
$aAttribs['id']			= 'edit-elementresalloc-resource-field';
$aAttribs['name']		= 'resource';
$aAttribs['maxlength']	= '255';
$aAttribs['class']		= 'webea-main-styled-combo';
echo BuildHTMLComboFromArray($aAuthors, $aAttribs, true, $sEditResource);
echo '</td>';
echo '</tr>';
echo '<tr class="edit-elementresalloc-line">';
echo '<td class="edit-elementresalloc-label">' . _glt('Role') . '<span class="field-label-required"> *</span></td>';
echo '<td class="edit-elementresalloc-field">';
$aAttribs['id']			= 'edit-elementresalloc-role-field';
$aAttribs['name']		= 'role';
$aAttribs['maxlength']	= '255';
$aAttribs['class']		= 'webea-main-styled-combo';
echo BuildHTMLComboFromArray($aRoles, $aAttribs, true, $sEditRole);
echo '</td>';
echo '</tr>';
echo '<tr class="edit-elementresalloc-line">';
echo '<td class="edit-elementresalloc-label">' . _glt('Start date') . '<span class="field-label-required"> *</span></td>';
echo '<td class="edit-elementresalloc-field"><input id="edit-elementresalloc-startdate-field" class="webea-main-styled-date" name="startdate" value="' . $sStartDate . '" placeholder="yyyy-mm-dd" onblur="OnDateFieldLostFocus(\'edit-elementresalloc-startdate-field\')"' . $sReadOnlyProp . '>';
echo '<div style="display: none;"><img id="edit-elementresalloc-startdate-img" class="ss-calendar-icon" alt="" title="Choose date from calendar" src="images/spriteplaceholder.png"></div>';
echo '</td>';
echo '</tr>';
echo '<tr class="edit-elementresalloc-line">';
echo '<td class="edit-elementresalloc-label">' . _glt('End date') . '<span class="field-label-required"> *</span></td>';
echo '<td class="edit-elementresalloc-field"><input id="edit-elementresalloc-enddate-field" class="webea-main-styled-date" name="enddate" value="' . $sEndDate . '" placeholder="yyyy-mm-dd" onblur="OnDateFieldLostFocus(\'edit-elementresalloc-enddate-field\')"' . $sReadOnlyProp . '>';
echo '<div style="display: none;"><img id="edit-elementresalloc-enddate-img" class="ss-calendar-icon" alt="" title="Choose date from calendar" src="images/spriteplaceholder.png"></div>';
echo '</td>';
echo '</tr>';
echo '<tr class="edit-elementresalloc-line">';
echo '<td class="edit-elementresalloc-label">' . _glt('Percent Complete') . '&nbsp;</td>';
echo '<td class="edit-elementresalloc-field"><input id="edit-elementresalloc-percentcomp-field" class="webea-main-styled-number" name="percentcomp" type="number" value="' . $sPercentComplete . '" min="0" max="100" step="1" maxlength="3"></td>';
echo '</tr>';
echo '<tr class="edit-elementresalloc-line">';
echo '<td class="edit-elementresalloc-label"><label>' . _glt('Expected') . '</label></td>';
echo '<td class="edit-elementresalloc-field"><input id="edit-elementresalloc-expectedtime-field" class="webea-main-styled-number" name="expectedtime"	type="number" value="' . $sExpectedTime . '" min="0" step="1" maxlength="8"></td>';
echo '</tr>';
echo '<tr class="edit-elementresalloc-line">';
echo '<td class="edit-elementresalloc-label"><label>' . _glt('Allocated') . '</label></td>';
echo '<td class="edit-elementresalloc-field"><input id="edit-elementresalloc-allocatedtime-field" class="webea-main-styled-number" name="allocatedtime" type=" number" value="' . $sAllocatedTime . '" min="0" step="any" maxlength="8"></td>';
echo '</tr>';
echo '<tr class="edit-elementresalloc-line">';
echo '<td class="edit-elementresalloc-label"><label>' . _glt('Actual') . '</label></td>';
echo '<td class="edit-elementresalloc-field"><input id="edit-elementresalloc-expendedtime-field" class="webea-main-styled-number" name="expendedtime" type="number" value="' . $sExpendedTime . '" min="0" step="1" maxlength="8"></td>';
echo '</tr>';
echo '<tr class="edit-elementresalloc-line">';
echo '<td class="edit-elementresalloc-label field-label-vert-align-top">' . _glt('Description') . '</td>';
echo '<td class="edit-elementresalloc-field"><div id="edit-elementresalloc-notes-div"><textarea id="edit-elementresalloc-notes-field" name="notes" onfocus="EnsureInputFieldVisible(this)">' . ConvertEANoteToHTML($sNotes) . '</textarea></div></td>';
echo '</tr>';
echo '<tr class="edit-elementresalloc-line">';
echo '<td class="edit-elementresalloc-label field-label-vert-align-top"><label>' . _glt('History') . '</label></td>';
echo '<td class="edit-elementresalloc-field"><div id="edit-elementresalloc-history-div"><textarea id="edit-elementresalloc-history-field" name="history" onfocus="EnsureInputFieldVisible(this)">' . ConvertEANoteToHTML($sHistory) . '</textarea></div></td>';
echo '</tr>';
echo '<tr class="edit-elementresalloc-line">';
echo '<td class="edit-elementresalloc-label">&nbsp;</td>';
echo '<td class="edit-elementresalloc-field"><input class="webea-main-styled-button edit-elementresalloc-submit" ' . (strIsEmpty($sOSLCErrorMsg) ? '' : ' disabled=""') . 'type="submit" value="' . _glt('Save') . '"></td>';
echo '</tr>';
echo '</tbody></table>';
echo '<div id="edit-elementresalloc-message"' . (strIsEmpty($sOSLCErrorMsg) ? '>' : ' class="add-elementresalloc-message-error">' . $sOSLCErrorMsg) . '</div>';
echo '<input type="hidden" name="key1" value="' . $sParentGUID . '">';
echo '<input type="hidden" name="key2" value="' . $sResource . '">';
echo '<input type="hidden" name="key3" value="' . $sRole . '">';
echo '<input type="hidden" name="guid" value="' . $sParentGUID . '">';
echo '<input type="hidden" name="parentname" value="' . htmlspecialchars($sObjectName, ENT_COMPAT) . '">';
echo '<input type="hidden" name="parentimageurl" value="' . $sObjImageURL . '">';
$sLoginGUID 	= SafeGetInternalArrayParameter($_SESSION, 'login_guid', '');
echo '<input type="hidden" name="loginguid" value="' . $sLoginGUID . '">';
echo '</form>';
echo '</div>';
echo '</div>';
echo '</div>';
