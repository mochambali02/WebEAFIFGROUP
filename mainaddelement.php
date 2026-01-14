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
	if ($iPos !== false) {
		$sParentGUID = substr($sLinkType, 0, $iPos);
		$sParentName = substr($sLinkType, $iPos + 1);
	}
}
$aDiagramTechs		= array();
$aDiagramTypes		= array();
include('./data_api/get_shape_element.php');
$sOSLCErrorMsg = BuildOSLCErrorString();
if (strIsEmpty($sOSLCErrorMsg)) {
	$sMainClass = '';
	if (IsSessionSettingTrue('show_browser')) {
		$sMainClass = ' class="show-browser"';
	}
	echo '<div id="main-add-element" ' . $sMainClass . '>';
	echo '<div id="add-element-layout">';
	echo '<div class="webea-page-header"><img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName('images/element16/add.png') . '" onload="OnLoad_SetupSpecialCtrls(\'add-element-notes-field\',\'\')"> ' . _glt('Add Object') . '</div>';
	echo '<div id="add-element-form">';
	echo '<form class="add-element-form" role="form" onsubmit="OnFormRunAddElement(event)">';
	echo '<table id="add-element-table" style="width: 100%;">';
	echo '<tbody>';
	echo '<tr class="add-element-line">';
	echo '<td class="add-element-label"><span class="noWrapCell">' . _glt('Element type') . '<span class="field-label-required">&nbsp;*</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>';
	echo '<td class="add-element-field">';
	$aComboOptions = array();
	$sPrefix = substr($sLinkType, 0, 2);
	if ((IsSessionSettingTrue('add_objecttype_package')) && ($sPrefix !== 'el'))
		array_push($aComboOptions, array('label' => 'Package', 'value' => 'package'));
	if ((IsSessionSettingTrue('add_diagrams') && IsSessionSettingTrue('login_perm_creatediagram')) && ($sPrefix !== 'el'))
		array_push($aComboOptions, array('label' => 'Diagram', 'value' => 'diagram'));
	if (IsSessionSettingTrue('add_objecttype_review'))
		array_push($aComboOptions, array('label' => 'Review', 'value' => 'review'));
	if (IsSessionSettingTrue('add_objecttype_actor'))
		array_push($aComboOptions, array('label' => 'Actor', 'value' => 'actor'));
	if (IsSessionSettingTrue('add_objecttype_change'))
		array_push($aComboOptions, array('label' => 'Change', 'value' => 'change'));
	if (IsSessionSettingTrue('add_objecttype_component'))
		array_push($aComboOptions, array('label' => 'Component', 'value' => 'component'));
	if (IsSessionSettingTrue('add_objecttype_feature'))
		array_push($aComboOptions, array('label' => 'Feature', 'value' => 'feature'));
	if (IsSessionSettingTrue('add_objecttype_issue'))
		array_push($aComboOptions, array('label' => 'Issue', 'value' => 'issue'));
	if (IsSessionSettingTrue('add_objecttype_node'))
		array_push($aComboOptions, array('label' => 'Node', 'value' => 'node'));
	if (IsSessionSettingTrue('add_objecttype_requirement'))
		array_push($aComboOptions, array('label' => 'Requirement', 'value' => 'requirement'));
	if (IsSessionSettingTrue('add_objecttype_task'))
		array_push($aComboOptions, array('label' => 'Task', 'value' => 'task'));
	if (IsSessionSettingTrue('add_objecttype_usecase'))
		array_push($aComboOptions, array('label' => 'UseCase', 'value' => 'usecase'));
	$aAttribs = array();
	$aAttribs['id']			= 'add-element-elementtype';
	$aAttribs['name']		= 'elementtype';
	$aAttribs['class']		= 'webea-main-styled-combo';
	$aAttribs['onclick']		= 'onClickAddElementType(this.value)';
	$aAttribs['onchange']		= 'onClickAddElementType(this.value)';
	echo BuildHTMLComboFrom2DimArray($aComboOptions, $aAttribs);
	echo '</td>';
	echo '</tr>';
	$bDiagramIsFirstEntry = false;
	if ($aComboOptions[0]['value'] === 'diagram')
		$bDiagramIsFirstEntry = true;
	echo '<tr id="add-element-diagram-tdtech-row1" class="add-element-line" style="display: ' . ($bDiagramIsFirstEntry ? 'block' : 'none') . ';">';
	echo '<td id="add-element-diagram-tdtech1" class="add-element-label field-label-vert-align-middle"><span class="noWrapCell">' . _glt('Technology') . '<span class="field-label-required">&nbsp;*</span></span></td>';
	echo '<td id="add-element-diagram-tdtech2" class="add-element-field" onclick="onClickAddElementDiaTech(this.value, \'' . htmlentities(json_encode($aDiagramTypes)) . '\')" onchange="onClickAddElementDiaTech(this.value, \'' . htmlentities(json_encode($aDiagramTypes)) . '\')">';
	$aAttribs				= array();
	$aAttribs['id']			= 'add-element-diagramtech-field';
	$aAttribs['name']		= 'diagramtech';
	$aAttribs['class']		= 'webea-main-styled-combo';
	echo BuildHTMLComboFromArray($aDiagramTechs, $aAttribs);
	echo '</td>';
	echo '</tr>';
	$iCnt = count($aDiagramTechs);
	$aTypes = array();
	if ($i > 0) {
		$sTech = SafeGetArrayItem2Dim($aDiagramTypes, 0, 'tech');
		if (!strIsEmpty($sTech)) {
			$iCnt = count($aDiagramTypes);
			for ($i = 0; $i < $iCnt; $i++) {
				if (SafeGetArrayItem2Dim($aDiagramTypes, $i, 'tech') === $sTech) {
					$sLabel			= SafeGetArrayItem2Dim($aDiagramTypes, $i, 'alias');
					if (strIsEmpty($sLabel))
						$sLabel 	= SafeGetArrayItem2Dim($aDiagramTypes, $i, 'name');
					$sValue			= SafeGetArrayItem2Dim($aDiagramTypes, $i, 'fqname');
					if (strIsEmpty($sValue))
						$sValue 	= SafeGetArrayItem2Dim($aDiagramTypes, $i, 'name');
					$aRow			= array();
					$aRow['label']	= $sLabel;
					$aRow['value']	= $sValue;
					$aTypes[] = $aRow;
				}
			}
		}
	}
	echo '<tr id="add-element-diagram-tdtech-row2"  class="add-element-line" style="display: ' . ($bDiagramIsFirstEntry ? 'block' : 'none') . ';">';
	echo '<td id="add-element-diagram-tdtype1" class="add-element-label field-label-vert-align-middle"><span class="noWrapCell">' . _glt('Diagram type') . '<span class="field-label-required">&nbsp;*</span></span></td>';
	echo '<td id="add-element-diagram-tdtype2" class="add-element-field">';
	$aAttribs				= array();
	$aAttribs['id']			= 'add-element-diagramtype-field';
	$aAttribs['name']		= 'diagramtype';
	$aAttribs['class']		= 'webea-main-styled-combo';
	echo BuildHTMLComboFrom2DimArray($aTypes, $aAttribs);
	echo '</td>';
	echo '</tr>';
	echo '<tr class="add-element-line" style="height: 60px;">';
	echo '<td class="add-element-label field-label-vert-align-top">' . _glt('Name') . '<span class="field-label-required">&nbsp;*&nbsp;</span></td>';
	echo '<td class="add-element-field" style="width: 80%;"><textarea id="add-element-name-field" class="webea-main-styled-textbox" name="name" maxlength="255" onfocus="EnsureInputFieldVisible(this)"></textarea></td>';
	echo '</tr>';
	echo '<tr class="add-element-line" style="height: 160px;">';
	echo '<td class="add-element-label field-label-vert-align-top">' . _glt('Notes') . '</td>';
	echo '<td class="add-element-field last-field" style="width: 80%;"><div id="add-element-notes-div"><textarea id="add-element-notes-field" name="notes" onfocus="EnsureInputFieldVisible(this)"></textarea></div></td>';
	echo '</tr>';
	echo '<tr class="add-element-line" style="height: 54px;">';
	echo '<td>&nbsp;</td>';
	echo '<td class="add-element-field"><input class="webea-main-styled-button add-element-submit" type="submit" value="' . _glt('Add') . '"></td>';
	echo '</tr>';
	echo '</tbody>';
	echo '</table>';
	echo '<input type="hidden" name="parentguid" value="' . $sParentGUID . '">';
	echo '<input type="hidden" name="parentname" value="' . htmlspecialchars($sParentName, ENT_COMPAT) . '">';
	$sLoginGUID 	= SafeGetInternalArrayParameter($_SESSION, 'login_guid', '');
	$sLoginFullName = SafeGetInternalArrayParameter($_SESSION, 'login_fullname', '');
	if (!strIsEmpty($sLoginGUID))
		echo '<input type="hidden" name="loginguid" value="' . $sLoginGUID . '">';
	if (!strIsEmpty($sLoginFullName))
		echo '<input type="hidden" name="author" value="' . $sLoginFullName . '">';
	echo '</form>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
}
?>
<script>
	function onClickAddElementType(sObjectType) {
		var ele1 = document.getElementById('add-element-diagram-tdtech-row1');
		var ele2 = document.getElementById('add-element-diagram-tdtech-row2');
		if (ele1) {
			if (sObjectType == 'diagram') {
				ele1.style.display = 'block';
				ele2.style.display = 'block';
			} else {
				ele1.style.display = 'none';
				ele2.style.display = 'none';
			}
		}
	}

	function onClickAddElementDiaTech(sObjectType, sDiagramTypes) {
		var eleTech = document.getElementById('add-element-diagramtech-field');
		var eleTypes = document.getElementById('add-element-diagramtype-field');
		if (eleTech && eleTypes) {
			try {
				var sTech = eleTech.value;
				var aDTs = JSON.parse(sDiagramTypes);
				for (var i = eleTypes.options.length - 1; i >= 0; i--) {
					eleTypes.remove(i);
				}
				for (i = 0; i < aDTs.length; i++) {
					if (aDTs[i]['tech'] === sTech) {
						var sLabel = aDTs[i]['alias'];
						if (sLabel === '')
							sLabel = aDTs[i]['name'];
						var sValue = aDTs[i]['fqname'];
						if (sValue === '')
							sValue = aDTs[i]['name'];
						var el = document.createElement("option");
						el.textContent = sLabel;
						el.value = sValue;
						eleTypes.appendChild(el);
					}
				}
			} catch (e) {}
		}
	}
</script>