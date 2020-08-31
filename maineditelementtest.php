<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	if  ( !isset($webea_page_parent_mainview) )
	{
		exit();
	}
	$sRootPath = dirname(__FILE__);
	require_once $sRootPath . '/globals.php';
	SafeStartSession();
	if (isset($_SESSION['authorized']) === false)
	{
		$sErrorMsg = _glt('Your session appears to have timed out');
		setResponseCode(440, $sErrorMsg);
		exit();
	}
	$sParentGUID 	= '';
	$sTestName 		= '';
	if ( !strIsEmpty($sLinkType) )
	{
		$iPos = strpos($sLinkType, '|');
		if ($iPos !== FALSE)
		{
			$sParentGUID = substr($sLinkType, 0, $iPos);
			$sTestName = substr($sLinkType, $iPos+1);
		}
	}
	$sTestClass		= SafeGetInternalArrayParameter($_POST, 'hyper');
	$aObjectDetails	= array();
	$aTestDetails	= array();
	$aTypes			= array();
	$aClassTypes	= array();
	$aStatuses		= array();
	$aAuthors		= array();
	$sOSLCErrorMsg 	= '';
	include('./data_api/get_elementtest.php');
	$sObjectName 	= SafeGetArrayItem1Dim($aObjectDetails, 'name');
	$sObjImageURL	= SafeGetArrayItem1Dim($aObjectDetails, 'imageurl');
	$sObjectResType = SafeGetArrayItem1Dim($aObjectDetails, 'restype');
	$sObjectName 	= GetPlainDisplayName($sObjectName);
	$sTesttName 	= SafeGetArrayItem1Dim($aTestDetails, 'name');
	$sClassType 	= SafeGetArrayItem1Dim($aTestDetails, 'classtype');
	$sType	 		= SafeGetArrayItem1Dim($aTestDetails, 'type');
	$sStatus 		= SafeGetArrayItem1Dim($aTestDetails, 'status');
	$sRunBy	 		= SafeGetArrayItem1Dim($aTestDetails, 'runby');
	$sCheckedBy		= SafeGetArrayItem1Dim($aTestDetails, 'checkedby');
	$sLastRun 		= SafeGetArrayItem1Dim($aTestDetails, 'lastrun');
	$sNotes	 		= SafeGetArrayItem1Dim($aTestDetails, 'notes');
	$sInput	 		= SafeGetArrayItem1Dim($aTestDetails, 'input');
	$sAcceptCrit	= SafeGetArrayItem1Dim($aTestDetails, 'acceptancecriteria');
	$sResults 		= SafeGetArrayItem1Dim($aTestDetails, 'results');
	$sReadOnlyProp = '';
	if ( IsMobileBrowser() )
	{
		$sReadOnlyProp = 'readonly="readonly"';
	}
	$iCnt = count($aAuthors);
	$sMainClass = '';
	if (IsSessionSettingTrue('show_browser'))
	{
		$sMainClass = ' class="show-browser"';
	}
	echo '<div id="main-edit-elementtest" ' . $sMainClass . '>';
	echo '<div id="edit-elementtest-layout">';
	$sRichEditCtrlsCSV = '\'edit-elementtest-notes-field,edit-elementtest-input-field,edit-elementtest-acceptance-field,edit-elementtest-results-field\'';
	$sDatePickerCtrlsCSV = '\'edit-elementtest-lastrun-field|edit-elementtest-lastrun-img\'';
	echo '<div class="webea-page-header">';
	echo '  <img alt="" src="images/spriteplaceholder.png" class="propsprite-testedit" onload="OnLoad_SetupSpecialCtrls(' . $sRichEditCtrlsCSV . ',' . $sDatePickerCtrlsCSV . ')"> ';
	echo    _glt('Edit Test for') . '&nbsp;<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sObjImageURL) . '">';
	echo '  <div class="objectname-in-header">' . htmlspecialchars($sObjectName) . '</div>';
	echo '</div>';
	echo '<div id="edit-elementtest-form">';
	echo '<form class="edit-elementtest-form" role="form" onsubmit="OnFormRunEditElementTest(event)">';
	echo '<table id="edit-elementtest-table"><tbody>';
	echo '<tr class="edit-elementtest-line">';
	echo '<td class="edit-elementtest-label field-label-vert-align-middle">' . _glt('Test name') . '<span class="field-label-required">&nbsp;*</span></td>';
	echo '<td class="edit-elementtest-field"><input id="edit-elementtest-name-field" class="webea-main-styled-textbox" name="name" type="text" maxlength="255" value="'. htmlspecialchars($sTesttName) .'"></td>';
	echo '</tr>';
	echo '<tr class="edit-elementtest-line">';
	echo '<td class="edit-elementtest-label field-label-vert-align-middle">' . _glt('Class type') . '<span class="field-label-required">&nbsp;*</span></td>';
	echo '<td class="edit-elementtest-field">';
	$aAttribs				= array();
	$aAttribs['id']			= 'edit-elementtest-classtype';
	$aAttribs['name']		= 'classtype';
	$aAttribs['maxlength']	= '10';
	$aAttribs['class']		= 'webea-main-styled-combo';
	echo BuildHTMLComboFromArray($aClassTypes, $aAttribs, false, $sClassType);
	echo '</td>';
	echo '</tr>';
	echo '<tr class="edit-elementtest-line">';
	echo '<td class="edit-elementtest-label field-label-vert-align-middle">' . _glt('Type') . '<span class="field-label-required">&nbsp;*</span></td>';
	echo '<td class="edit-elementtest-field">';
	$aAttribs['id']			= 'edit-elementtest-type';
	$aAttribs['name']		= 'type';
	$aAttribs['maxlength']	= '50';
	$aAttribs['class']		= 'webea-main-styled-combo';
	echo BuildHTMLComboFromArray($aTypes, $aAttribs, false, $sType);
	echo '</td>';
	echo '</tr>';
	echo '<tr class="edit-elementtest-line">';
	echo '<td class="edit-elementtest-label field-label-vert-align-middle">' . _glt('Status') . '<span class="field-label-required">&nbsp;*</span></td>';
	echo '<td class="edit-elementtest-field">';
	$aAttribs['id']			= 'edit-elementtest-status';
	$aAttribs['name']		= 'status';
	$aAttribs['maxlength']	= '32';
	$aAttribs['class']		= 'webea-main-styled-combo';
	echo BuildHTMLComboFromArray($aStatuses, $aAttribs, false, $sStatus);
	echo '</td>';
	echo '</tr>';
	echo '<tr class="edit-elementtest-line">';
	echo '<td class="edit-elementtest-label field-label-vert-align-middle">' . _glt('Run by') . '</td>';
	echo '<td class="edit-elementtest-field">';
	$aAttribs['id']			= 'edit-elementtest-runby';
	$aAttribs['name']		= 'runby';
	$aAttribs['maxlength']	= '255';
	$aAttribs['class']		= 'webea-main-styled-combo';
	echo BuildHTMLComboFromArray($aAuthors, $aAttribs, true, $sRunBy);
	echo '</td>';
	echo '</tr>';
	echo '<tr class="edit-elementtest-line">';
	echo '<td class="edit-elementtest-label field-label-vert-align-middle">' . _glt('Last run') . '</td>';
	echo '<td class="edit-elementtest-field"><input id="edit-elementtest-lastrun-field" name="lastrun" value="' . $sLastRun . '" placeholder="yyyy-mm-dd" onblur="OnDateFieldLostFocus(\'edit-elementtest-lastrun-field\')" ' . $sReadOnlyProp . '>';
	echo '<div style="display: none;"><img id="edit-elementtest-lastrun-img" class="ss-calendar-icon" alt="" title="Choose date from calendar" src="images/spriteplaceholder.png"></div>';
	echo '</td>';
	echo '</tr>';
	echo '<tr class="edit-elementtest-line">';
	echo '<td class="edit-elementtest-label field-label-vert-align-middle">' . _glt('Checked by') . '&nbsp;&nbsp;</td>';
	echo '<td class="edit-elementtest-field">';
	$aAttribs['id']			= 'edit-elementtest-checkedby';
	$aAttribs['name']		= 'checkedby';
	$aAttribs['maxlength']	= '255';
	echo BuildHTMLComboFromArray($aAuthors, $aAttribs, true, $sCheckedBy);
	$aAttribs['class']		= 'webea-main-styled-combo';
	echo '</td>';
	echo '</tr>';
	echo '<tr class="edit-elementtest-line">';
	echo '<td class="edit-elementtest-label field-label-vert-align-top">' . _glt('Description') . '</td>';
	echo '<td class="edit-elementtest-field"><div id="edit-elementtest-notes-div"><textarea id="edit-elementtest-notes-field" name="notes" onfocus="EnsureInputFieldVisible(this)">' . ConvertEANoteToHTML($sNotes) . '</textarea></div></td>';
	echo '</tr>';
	echo '<tr class="edit-elementtest-line">';
	echo '<td class="edit-elementtest-label field-label-vert-align-top">' . _glt('Input') . '</td>';
	echo '<td class="edit-elementtest-field"><div id="edit-elementtest-input-div"><textarea id="edit-elementtest-input-field" name="input" onfocus="EnsureInputFieldVisible(this)">' . ConvertEANoteToHTML($sInput) . '</textarea></div></td>';
	echo '</tr>';
	echo '<tr class="edit-elementtest-line">';
	echo '<td class="edit-elementtest-label field-label-vert-align-top">' . _glt('Acceptance Criteria') . '</td>';
	echo '<td class="edit-elementtest-field"><div id="edit-elementtest-acceptance-div"><textarea id="edit-elementtest-acceptance-field" name="acceptance" onfocus="EnsureInputFieldVisible(this)">' . ConvertEANoteToHTML($sAcceptCrit) . '</textarea></div></td>';
	echo '</tr>';
	echo '<tr class="edit-elementtest-line">';
	echo '<td class="edit-elementtest-label field-label-vert-align-top">' . _glt('Results') . '</td>';
	echo '<td class="edit-elementtest-field last-field"><div id="edit-elementtest-results-div"><textarea id="edit-elementtest-results-field" name="results" onfocus="EnsureInputFieldVisible(this)">' . ConvertEANoteToHTML($sResults) . '</textarea></div></td>';
	echo '</tr>';
	echo '<tr class="edit-elementtest-line">';
	echo '<td class="edit-elementtest-label">&nbsp;</td>';
	echo '<td class="edit-elementtest-field"><input class="webea-main-styled-button edit-elementtest-submit" ' . (strIsEmpty( $sOSLCErrorMsg) ? '' : ' disabled=""' ). 'type="submit" value="' . _glt('Save') . '"></td>';
	echo '</tr>';
	echo '</tbody></table>';
	echo '<div id="edit-elementtest-message"' . (strIsEmpty($sOSLCErrorMsg) ? '>' : ' class="add-elementresalloc-message-error">'). $sOSLCErrorMsg . '</div>';
	echo '<input type="hidden" name="key1" value="' . $sParentGUID . '">';
	echo '<input type="hidden" name="key2" value="' . $sTestName . '">';
	echo '<input type="hidden" name="key3" value="' . $sTestClass . '">';
	echo '<input type="hidden" name="parentguid" value="' . $sParentGUID . '">';
	echo '<input type="hidden" name="parentname" value="' . htmlspecialchars($sObjectName, ENT_COMPAT) . '">';
	echo '<input type="hidden" name="parentimageurl" value="' . $sObjImageURL . '">';
	$sLoginGUID 	= SafeGetInternalArrayParameter($_SESSION, 'login_guid', '');
	echo '<input type="hidden" name="loginguid" value="' . $sLoginGUID . '">';
	echo '</form>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
?>