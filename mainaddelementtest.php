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
	$aTypes			= array();
	$aClassTypes	= array();
	$aStatuses		= array();
	$aAuthors		= array();
	include('./data_api/get_shape_elementtest.php');
	$sOSLCErrorMsg = BuildOSLCErrorString();
	if ( strIsEmpty($sOSLCErrorMsg) )
	{
		$sObjectGUID 	= $sLinkType;
		$aObjectDetails	= array();
		include('./data_api/get_element.php');
		$sOSLCErrorMsg = BuildOSLCErrorString();
		if ( strIsEmpty($sOSLCErrorMsg) )
		{
			$sObjectName 	= SafeGetArrayItem1Dim($aObjectDetails, 'namealias');
			$sObjImageURL	= SafeGetArrayItem1Dim($aObjectDetails, 'imageurl');
			$sObjectResType = SafeGetArrayItem1Dim($aObjectDetails, 'restype');
			$sObjectName 	= GetPlainDisplayName($sObjectName);
			$sReadOnlyProp = '';
			if ( IsMobileBrowser() )
			{
				$sReadOnlyProp = 'readonly="readonly"';
			}
			$sMainClass = '';
			if (IsSessionSettingTrue('show_browser'))
			{
				$sMainClass = ' class="show-browser"';
			}
			echo '<div id="main-add-elementtest" ' . $sMainClass . '>';
			echo '<div id="add-elementtest-layout">';
			$sRichEditCtrlsCSV = '\'add-elementtest-notes-field,add-elementtest-input-field,add-elementtest-acceptance-field,add-elementtest-results-field\'';
			$sDatePickerCtrlsCSV = '\'add-elementtest-lastrun-field|add-elementtest-lastrun-img\'';
			echo '<div class="webea-page-header">';
			echo '  <img alt="" src="images/spriteplaceholder.png" class="propsprite-testadd" onload="OnLoad_SetupSpecialCtrls(' . $sRichEditCtrlsCSV . ',' . $sDatePickerCtrlsCSV . ')"> ';
			echo    _glt('Add Test to') . '&nbsp;<img alt="' . $sObjectResType . '" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sObjImageURL) . '">';
			echo '  <div class="objectname-in-header">' . htmlspecialchars($sObjectName) . '</div>';
			echo '</div>';
			echo '<div id="add-elementtest-form">';
			echo '<form class="add-elementtest-form" role="form" onsubmit="OnFormRunAddElementTest(event)">';
			echo '<table id="add-elementtest-table"><tbody>';
			echo '<tr class="add-elementtest-line">';
			echo '<td class="add-elementtest-label field-label-vert-align-top">' . _glt('Test name') . '<span class="field-label-required">&nbsp;*</span></td>';
			echo '<td class="add-elementtest-field"><input id="add-elementtest-name-field" class="webea-main-styled-textbox" name="name" maxlength="255"></input></td>';
			echo '</tr>';
			echo '<tr class="add-elementtest-line">';
			echo '<td class="add-elementtest-label field-label-vert-align-middle">' . _glt('Class type') . '<span class="field-label-required">&nbsp;*</span></td>';
			echo '<td class="add-elementtest-field">';
			$aAttribs				= array();
			$aAttribs['id']			= 'add-elementtest-classtype';
			$aAttribs['maxlength']	= '10';
			$aAttribs['name']		= 'classtype';
			$aAttribs['class']		= 'webea-main-styled-combo';
			echo BuildHTMLComboFromArray($aClassTypes, $aAttribs);
			echo '</span>';
			echo '</tr>';
			echo '<tr class="add-elementtest-line">';
			echo '<td class="add-elementtest-label field-label-vert-align-middle">' . _glt('Type') . '<span class="field-label-required">&nbsp;*</span></td>';
			echo '<td class="add-elementtest-field">';
			$aAttribs['id']			= 'add-elementtest-type';
			$aAttribs['name']		= 'type';
			$aAttribs['maxlength']	= '50';
			$aAttribs['class']		= 'webea-main-styled-combo';
			echo BuildHTMLComboFromArray($aTypes, $aAttribs);
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementtest-line">';
			echo '<td class="add-elementtest-label field-label-vert-align-middle">' . _glt('Status') . '<span class="field-label-required">&nbsp;*</span></td>';
			echo '<td class="add-elementtest-field">';
			$aAttribs['id']			= 'add-elementtest-status';
			$aAttribs['name']		= 'status';
			$aAttribs['maxlength']	= '32';
			$aAttribs['class']		= 'webea-main-styled-combo';
			echo BuildHTMLComboFromArray($aStatuses, $aAttribs);
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementtest-line">';
			echo '<td class="add-elementtest-label field-label-vert-align-middle">' . _glt('Run by') . '</td>';
			echo '<td class="add-elementtest-field">';
			$aAttribs['id']			= 'add-elementtest-runby';
			$aAttribs['name']		= 'runby';
			$aAttribs['maxlength']	= '255';
			$aAttribs['class']		= 'webea-main-styled-combo';
			echo BuildHTMLComboFromArray($aAuthors, $aAttribs, true);
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementtest-line">';
			echo '<td class="add-elementtest-label field-label-vert-align-middle">' . _glt('Last run') . '</td>';
			echo '<td class="add-elementtest-field"><input id="add-elementtest-lastrun-field"  class="webea-main-styled-date"  name="lastrun" placeholder="yyyy-mm-dd" onblur="OnDateFieldLostFocus(\'add-elementtest-lastrun-field\')" ' . $sReadOnlyProp . '>';
			echo '<div style="display: none;"><img id="add-elementtest-lastrun-img" class="ss-calendar-icon" alt="cal" src="images/spriteplaceholder.png"></div>';
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementtest-line">';
			echo '<td class="add-elementtest-label field-label-vert-align-middle">' . _glt('Checked by') . '&nbsp;&nbsp;</td>';
			echo '<td class="add-elementtest-field">';
			$aAttribs['id']			= 'add-elementtest-checkedby';
			$aAttribs['name']		= 'checkedby';
			$aAttribs['maxlength']	= '255';
			$aAttribs['class']		= 'webea-main-styled-combo';
			echo BuildHTMLComboFromArray($aAuthors, $aAttribs, true);
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementtest-line">';
			echo '<td class="add-elementtest-label field-label-vert-align-top">' . _glt('Description') . '</td>';
			echo '<td class="add-elementtest-field"><div id="add-elementtest-notes-div"><textarea id="add-elementtest-notes-field" name="notes" onfocus="EnsureInputFieldVisible(this)"></textarea></div></td>';
			echo '</tr>';
			echo '<tr class="add-elementtest-line">';
			echo '<td class="add-elementtest-label field-label-vert-align-top">' . _glt('Input') . '</td>';
			echo '<td class="add-elementtest-field"><div id="add-elementtest-input-div"><textarea id="add-elementtest-input-field" name="input" onfocus="EnsureInputFieldVisible(this)"></textarea></div></td>';
			echo '</tr>';
			echo '<tr class="add-elementtest-line">';
			echo '<td class="add-elementtest-label field-label-vert-align-top">' . _glt('Acceptance Criteria') . '</td>';
			echo '<td class="add-elementtest-field"><div id="add-elementtest-acceptance-div"><textarea id="add-elementtest-acceptance-field" name="acceptance" onfocus="EnsureInputFieldVisible(this)"></textarea></div></td>';
			echo '</tr>';
			echo '<tr class="add-elementtest-line">';
			echo '<td class="add-elementtest-label field-label-vert-align-top">' . _glt('Results') . '</td>';
			echo '<td class="add-elementtest-field last-field"><div id="add-elementtest-results-div"><textarea id="add-elementtest-results-field" name="results" onfocus="EnsureInputFieldVisible(this)"></textarea></div></td>';
			echo '</tr>';
			echo '<tr class="add-elementtest-line">';
			echo '<td class="add-elementtest-label">&nbsp;</td>';
			echo '<td class="add-elementtest-field"><input class="webea-main-styled-button add-elementtest-submit" type="submit" value="' . _glt('Add') . '"></td>';
			echo '</tr>';
			echo '</tbody></table>';
			echo '<input type="hidden" name="guid" value="' . $sObjectGUID . '">';
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
?>