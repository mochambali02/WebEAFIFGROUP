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
	$aAuthors	= array();
	$aRoles		= array();
	include('./data_api/get_shape_elementresalloc.php');
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
			echo '<div id="main-add-elementresalloc" ' . $sMainClass . '>';
			echo '<div id="add-elementresalloc-layout">';
			$sRichEditCtrlsCSV = '\'add-elementresalloc-notes-field\'';
			$sDatePickerCtrlsCSV = '\'add-elementresalloc-startdate-field|add-elementresalloc-startdate-img,add-elementresalloc-enddate-field|add-elementresalloc-enddate-img\'';
			echo '<div class="webea-page-header">';
			echo '  <img alt="" src="images/spriteplaceholder.png" class="propsprite-resallocadd" onload="OnLoad_SetupSpecialCtrls(' . $sRichEditCtrlsCSV . ',' . $sDatePickerCtrlsCSV . ')"> ';
			echo    _glt('Add resource allocation to');
			echo '  <img alt="' . $sObjectResType . '" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sObjImageURL) . '">';
			echo '  <div class="objectname-in-header">' .  htmlspecialchars($sObjectName) . '</div>';
			echo '</div>';
			echo '<div id="add-elementresalloc-form">';
			echo '<form class="add-elementresalloc-form" role="form" onsubmit="OnFormRunAddElementResAlloc(event)">';
			echo '<table id="add-elementresalloc-table"><tbody>';
			echo '<tr class="add-elementresalloc-line">';
			echo '<td class="add-elementresalloc-label">' . _glt('Resource') . '<span class="field-label-required"> *</span></td>';
			echo '<td class="add-elementresalloc-field">';
			$aAttribs				= array();
			$aAttribs['id']			= 'add-elementresalloc-resource-field';
			$aAttribs['name']		= 'resource';
			$aAttribs['maxlength']	= '255';
			$aAttribs['class']		= 'webea-main-styled-combo';
			echo BuildHTMLComboFromArray($aAuthors, $aAttribs, true);
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementresalloc-line">';
			echo '<td class="add-elementresalloc-label">' . _glt('Role') . '<span class="field-label-required"> *</span></td>';
			echo '<td class="add-elementresalloc-field">';
			$aAttribs['id']			= 'add-elementresalloc-role-field';
			$aAttribs['name']		= 'role';
			$aAttribs['maxlength']	= '255';
			$aAttribs['class']		= 'webea-main-styled-combo';
			echo BuildHTMLComboFromArray($aRoles, $aAttribs, true);
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementresalloc-line">';
			echo '<td class="add-elementresalloc-label">' . _glt('Start date') . '<span class="field-label-required"> *</span></td>';
			echo '<td class="add-elementresalloc-field"><input id="add-elementresalloc-startdate-field" class="webea-main-styled-date" name="startdate" placeholder="yyyy-mm-dd" onblur="OnDateFieldLostFocus(\'add-elementresalloc-startdate-field\')" ' . $sReadOnlyProp . '>';
			echo '<div style="display: none;"><img id="add-elementresalloc-startdate-img" class="ss-calendar-icon" alt="" title="Choose date from calendar" src="images/spriteplaceholder.png" height="19" width="17"></div>';
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementresalloc-line">';
			echo '<td class="add-elementresalloc-label">' . _glt('End date') . '<span class="field-label-required"> *</span></td>';
			echo '<td class="add-elementresalloc-field"><input id="add-elementresalloc-enddate-field" class="webea-main-styled-date" name="enddate" placeholder="yyyy-mm-dd" onblur="OnDateFieldLostFocus(\'add-elementresalloc-enddate-field\')" ' . $sReadOnlyProp . '>';
			echo '<div style="display: none;"><img id="add-elementresalloc-enddate-img" class="ss-calendar-icon" alt="" title="Choose date from calendar" src="images/spriteplaceholder.png" height="19" width="17"></div>';
			echo '</td>';
			echo '</tr>';
			echo '<tr class="add-elementresalloc-line">';
			echo '<td class="add-elementresalloc-label">' . _glt('Percent Complete') . '&nbsp;</td>';
			echo '<td class="add-elementresalloc-field"><input id="add-elementresalloc-percentcomp-field" class="webea-main-styled-number" name="percentcomp" type="number" value="0" min="0" max="100" step="1" maxlength="3"></td>';
			echo '</tr>';
			echo '<tr class="add-elementresalloc-line">';
			echo '<td class="add-elementresalloc-label field-label-vert-align-top">' . _glt('Description') . '</td>';
			echo '<td class="add-elementresalloc-field"><div id="add-elementresalloc-notes-div"><textarea id="add-elementresalloc-notes-field" name="notes" onfocus="EnsureInputFieldVisible(this)"></textarea></div></td>';
			echo '</tr>';
			echo '<tr class="add-elementresalloc-line">';
			echo '<td class="add-elementresalloc-label">&nbsp;</td>';
			echo '<td class="add-elementresalloc-field"><input class="webea-main-styled-button add-elementresalloc-submit" type="submit" value="' . _glt('Add') . '"></td>';
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