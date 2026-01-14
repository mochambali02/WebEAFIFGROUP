<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/globals.php';
SafeStartSession();
$sSelectedFeatures = SafeGetInternalArrayParameter($_SESSION, 'selected_features');
$aSelectedFeatures = str_getcsv($sSelectedFeatures);
WritePropsViewConfigDialog($aSelectedFeatures);
function WritePropsViewConfigDialog($aSelectedFeatures)
{
	$aFeatureList = GetSortedAndEnabledFeatureList();
	$sDialogBody = '';
	$sDialogBody .= '<div style="width: 50%; display:inline-block; padding-right:16px; border-right:solid 1px #ddd;" >';
	$sDialogBody .= '<div style="padding: 0px 8px 8px 8px;font-size: 1em;font-weight: bold;"><div style="display:inline-block;">Feature *</div><div style="display:inline-block;float: right;">Visible</div></div>';
	$sDialogBody .= '<ul id="propsview-config-feature-list" class="sortable" style="padding-left:0px;">';
	foreach ($aFeatureList as $sFeatureName) {
		$sChecked = '';
		if (in_array($sFeatureName['id'], $aSelectedFeatures)) {
			$sChecked = 'checked';
		}
		$sDialogBody .= '<li class="config-props-list-item" feature-id="' . $sFeatureName['id'] . '">';
		$sDialogBody .= '<div class="icon-vert-arrows icon16" title="Drag row up or down to adjust feature order."></div>';
		$sDialogBody .= $sFeatureName['name'];
		$sDialogBody .= '<div class="onoffswitch" style="float:right;">';
		$sDialogBody .= '<input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="onoffswitch_' . $sFeatureName['id'] . '" ' . $sChecked . '>';
		$sDialogBody .= '<label class="onoffswitch-label" for="onoffswitch_' . $sFeatureName['id'] . '"></label>';
		$sDialogBody .= '</div>';
		$sDialogBody .= '</li>';
	}
	$sDialogBody .= '</ul>';
	$sDialogBody .= '</div>';
	$sDialogBody .= '<div style="width: 45%; display:inline-block; height:100%;  vertical-align: top;">';
	$sChecked = '';
	if (IsSessionSettingTrue('propsview_hide_empty')) {
		$sChecked = 'checked';
	}
	$sDialogBody .= '<div class="config-props-line"><div class="float-right">* Drag features up/down in the list to change their order.</div></div>';
	$sDialogBody .= '<div class="config-props-line"><div class="float-right"><div style="display:inline-block;font-size: 1em;vertical-align: top;margin-right: 8px;margin-top: 2px;">Hide Empty Feature Sections</div><div style="display:inline-block;" class="onoffswitch" style="float:right;"><input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="onoffswitch_hide_empty" ' . $sChecked . '><label class="onoffswitch-label" for="onoffswitch_hide_empty"></label></div></div></div>';
	$sDialogBody .= '<div class="config-props-line"><div class="float-right"><a href="javascript:PropsViewConfigSetAllVisible()" style="color: #3777bf;">[Set All Visible]</a> | <a href="javascript:PropsViewConfigSetNoneVisible()" style="color: #3777bf;">[Set All Hidden]</a></div></div>';
	$sDialogBody .= '<div class="config-props-line"><div class="float-right"><a href="javascript:PropsViewConfigResetVisibility()" style="color: #3777bf;">[Restore Default Visibility]</a></div></div>';
	$sDialogBody .= '<div class="props-view-config-line"><div class="float-right"><a href="javascript:PropsViewConfigResetOrder()" style="color: #3777bf;">[Restore Default Order]</a></div></div>';
	$sDialogBody .= '</div>';
	echo '<div class="dialog-header">';
	echo '<div class="dialog-header-title">';
	echo 'Configure Properties View';
	echo '</div>';
	echo '<button class="dialog-header-close-button" onclick="CloseDialog()"><div class="close-icon button-icon icon16"></div></button>';
	echo '</div>';
	echo '<div class="dialog-body">' . $sDialogBody . '</div>';
	echo '<div class="dialog-footer">';
	echo '<div class="dialog-buttons-container">';
	echo '<input class="webea-main-styled-button dialog-button dialog-button-ok" type="submit" onclick="OnSaveAndClosePropsConfig(this)" value="' . _glt('Save') . ' & ' . _glt('Close') . '">';
	echo '<input class="webea-main-styled-button dialog-button dialog-button-close" type="submit" onclick="CloseDialog()" value="' . _glt('Close') . '">';
	echo '</div>';
	echo '</div>';
}
function GetSortedAndEnabledFeatureList()
{
	$aFeatureList = [];
	$aFeatureList[] = ['id' => 'nt', 'name' => 'Notes'];
	if (IsSessionSettingTrue('prop_visible_taggedvalues'))
		$aFeatureList[] = ['id' => 'tv', 'name' => 'Tagged Values'];
	if (IsSessionSettingTrue('prop_visible_relationships'))
		$aFeatureList[] = ['id' => 'lt', 'name' => 'Relationships'];
	if (IsSessionSettingTrue('prop_visible_attributes'))
		$aFeatureList[] = ['id' => 'at', 'name' => 'Attributes'];
	if (IsSessionSettingTrue('prop_visible_operations'))
		$aFeatureList[] = ['id' => 'op', 'name' => 'Operations'];
	if (IsSessionSettingTrue('prop_visible_files'))
		$aFeatureList[] = ['id' => 'fl', 'name' => 'Files'];
	if (IsSessionSettingTrue('prop_visible_resourcealloc'))
		$aFeatureList[] = ['id' => 'ra', 'name' => 'Resources'];
	if (IsSessionSettingTrue('prop_visible_features'))
		$aFeatureList[] = ['id' => 'mf', 'name' => 'Features'];
	if (IsSessionSettingTrue('prop_visible_changes'))
		$aFeatureList[] = ['id' => 'ch', 'name' => 'Changes'];
	if (IsSessionSettingTrue('prop_visible_documents'))
		$aFeatureList[] = ['id' => 'dm', 'name' => 'Documents'];
	if (IsSessionSettingTrue('prop_visible_defects'))
		$aFeatureList[] = ['id' => 'df', 'name' => 'Defects'];
	if (IsSessionSettingTrue('prop_visible_issues'))
		$aFeatureList[] = ['id' => 'is', 'name' => 'Issues'];
	if (IsSessionSettingTrue('prop_visible_tasks'))
		$aFeatureList[] = ['id' => 'tk', 'name' => 'Tasks'];
	if (IsSessionSettingTrue('prop_visible_events'))
		$aFeatureList[] = ['id' => 'ev', 'name' => 'Events'];
	if (IsSessionSettingTrue('prop_visible_decisions'))
		$aFeatureList[] = ['id' => 'dc', 'name' => 'Decisions'];
	if (IsSessionSettingTrue('show_discuss'))
		$aFeatureList[] = ['id' => 'dr', 'name' => 'Discussions'];
	$sFeatureOrder = SafeGetInternalArrayParameter($_SESSION, 'feature_order', 'nt,tv,lt,at,op,fl,ra,mf,ch,dm,df,is,tk,ev,dc,dr');
	$aFeatureOrder = str_getcsv($sFeatureOrder);
	foreach ($aFeatureOrder as $sOrderID) {
		foreach ($aFeatureList as $aFeature) {
			if ($sOrderID === $aFeature['id']) {
				$aSortedFeatureList[] = $aFeature;
			}
		}
	}
	foreach ($aFeatureList as $aFeature) {
		if (!in_array($aFeature['id'], $aFeatureOrder)) {
			$aSortedFeatureList[] = $aFeature;
		}
	}
	$aFeatureList = $aSortedFeatureList;
	return $aFeatureList;
}
