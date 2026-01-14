<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST"  &&   !isset($webea_page_parent_mainview)) {
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
if (isset($sObjectGUID) === false) {
	$sObjectGUID = SafeGetInternalArrayParameter($_POST, 'objectguid');
}
if (strIsEmpty($sObjectGUID)) {
	return;
}
$sSelectedFeatureID = SafeGetInternalArrayParameter($_SESSION, 'selected_feature');
if (!isset($aCommonProps)) {
	$aCommonProps 	= array();
	$aAttributes 	= array();
	$aOperations 	= array();
	$aConstraints 	= array();
	$aTaggedValues 	= array();
	$aExternalData 	= array();
	$aDiscussions 	= array();
	$aReviewDiscuss = array();
	$aReviewDiagrams = array();
	$aReviewNoDiscuss = array();
	$aTests 		= array();
	$aResAllocs 	= array();
	$aScenarios 	= array();
	$aRequirements 	= array();
	$aRunStates 	= array();
	$aUsages	 	= array();
	$aDocument	 	= array();
	$aParentInfo	= array();
	$aRelationships = array();
	$aChanges 		= array();
	$aDefects 		= array();
	$aIssues 		= array();
	$aTasks 		= array();
	$aEvents 		= array();
	$aDecisions 	= array();
	$aRisks 		= array();
	$aEfforts 		= array();
	$aMetrics 		= array();
	$sResourceFeaturesFilter = 'dr,ld,lt,us';
	if (!strIsEmpty($sSelectedFeatureID) && ($sSelectedFeatureID !== 'nt'))
		$sResourceFeaturesFilter = $sResourceFeaturesFilter . ',' . $sSelectedFeatureID;
	if ($sSelectedFeatureID === 'op')
		$sResourceFeaturesFilter .= ',pr';
	include('./data_api/get_properties.php');
	include('propertysections.php');
}
if (count($aCommonProps) === 0) {
	return;
}
$sObjectGUID 	= SafeGetArrayItem1Dim($aCommonProps, 'guid');
$sObjectAlias 	= SafeGetArrayItem1Dim($aCommonProps, 'alias');
$sObjectName 	= SafeGetArrayItem1Dim($aCommonProps, 'name');
$sObjectType 	= SafeGetArrayItem1Dim($aCommonProps, 'type');
$sObjectResType = SafeGetArrayItem1Dim($aCommonProps, 'restype');
$sObjectStereotype = SafeGetArrayItem1Dim($aCommonProps, 'stereotype');
$aObjectStereotypes = SafeGetArrayItem1Dim($aCommonProps, 'stereotypes');
$sObjImageURL	= SafeGetArrayItem1Dim($aCommonProps, 'imageurl');
$sHasChildren	= SafeGetArrayItem1Dim($aCommonProps, 'haschild');
$sObjectAuthor	= SafeGetArrayItem1Dim($aCommonProps, 'author');
$sObjectModified = SafeGetArrayItem1Dim($aCommonProps, 'modified');
$sObjectStatus	= SafeGetArrayItem1Dim($aCommonProps, 'status');
$sObjectVersion	= SafeGetArrayItem1Dim($aCommonProps, 'version');
$sObjectPhase	= SafeGetArrayItem1Dim($aCommonProps, 'phase');
$sObjectNType	= SafeGetArrayItem1Dim($aCommonProps, 'ntype');
$bObjLocked		= strIsTrue(SafeGetArrayItem1Dim($aCommonProps, 'locked'));
$sObjLockedType	= SafeGetArrayItem1Dim($aCommonProps, 'lockedtype');
$sObjClassName 	= SafeGetArrayItem1Dim($aCommonProps, 'classname');
$sObjClassGUID 	= SafeGetArrayItem1Dim($aCommonProps, 'classguid');
$sObjClassImageURL	= SafeGetArrayItem1Dim($aCommonProps, 'classimageurl');
$sNotes = SafeGetArrayItem1Dim($aCommonProps, 'notes');
echo '<div class="mini-properties-name-section" style="min-height: 30px;padding-bottom: 12px;font-size: 0.8em;overflow-x: hidden;">';
$sObjectImageHTML = '<div class="mp-object-image">';
if ($sObjectType === 'Artifact' && $sObjectStereotype === 'Image') {
	$sImageBin =  SafeGetArrayItem1Dim($aCommonProps, 'imagepreview');
	if (!strIsEmpty($sImageBin)) {
		$sObjectImageHTML .= '<img class="mp-object-image-asset" src="data:image/png;base64,' . $sImageBin . '" alt="" title=""/>';
	}
} else {
	$sObjectImageHTML .= '<img src="' . $sObjImageURL . '" alt="" title="" height="32" width="32"/>';
}
$sObjectImageHTML .= '</div>';
if (IsHyperlink($sObjectType, $sObjectName, $sObjectNType)) {
	$sDisplayName = $sObjectName;
	if (!strIsEmpty($sObjectAlias)) {
		$sDisplayName = $sObjectAlias;
	}
	$sDisplayName = GetPlainDisplayName($sDisplayName);
	echo $sObjectImageHTML;
	echo '<div class="object-name">' . htmlspecialchars($sDisplayName) . '</div>';
	echo '<div class="object-line2">';
	echo _glt('Hyperlink') . '&nbsp;';
	echo '</div>';
} elseif ($sObjectType === 'Text' && $sObjectStereotype === 'NavigationCell') {
	$sDisplayName = $sObjectAlias;
	$sDisplayName = GetPlainDisplayName($sDisplayName);
	echo $sObjectImageHTML;
	echo '<div class="object-name">' . htmlspecialchars($sDisplayName) . '</div>';
	echo '<div class="object-line2">';
	echo _glt('Navigation Cell') . '&nbsp;';
	echo '</div>';
} elseif ($sObjectType === 'Text' && strIsEmpty($sObjectStereotype)) {
	$sDisplayName = GetPlainDisplayName($sObjectName);
	echo $sObjectImageHTML;
	echo '<div class="object-name">' . htmlspecialchars($sDisplayName) . '</div>';
	echo '<div class="object-line2">';
	echo _glt('Text') . '&nbsp;';
	echo '</div>';
} elseif ($sObjectType === 'Note' && strIsEmpty($sObjectStereotype)) {
	$sDisplayName = GetPlainDisplayName('');
	echo $sObjectImageHTML;
	echo '<div class="object-name">' . htmlspecialchars($sDisplayName) . '</div>';
	echo '<div class="object-line2">';
	echo _glt('Note') . '&nbsp;';
	echo '</div>';
} elseif (ShouldIgnoreName($sObjectType, $sObjectName, $sObjectNType)) {
	$sDisplayObjType = $sObjectType;
	if (!strIsEmpty($sObjClassGUID)) {
		$sDisplayObjType  = '<a class="w3-link" onclick="load_object(\'' . $sObjClassGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sObjectType) . '\',\'' . $sObjClassImageURL . '\')">';
		$sDisplayObjType .= '<img src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sObjClassImageURL) . '" alt="" style="float: none;">&nbsp;' . htmlspecialchars($sObjClassName) . '</a>';
	} else {
		$sDisplayObjType = htmlspecialchars(_glt($sDisplayObjType));
	}
	if (($sObjectType === 'Artifact') && ($sObjectStereotype === 'ExternalReference')) {
		$sObjImageURL16 = 'images/element16/document.png';
	} else {
		$sObjImageURL16 = AdjustImagePath($sObjImageURL, '16');
	}
	$sPrefix = substr($sObjectGUID, 0, 2);
	if ($sPrefix !== 'mr') {
		echo '<a class="w3-link"  style="color: #3777bf;" onclick="load_object(\'' . $sObjectGUID . '\',\'false\',\'props\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\', \'' . $sObjImageURL16 . '\')">';
		echo $sObjectImageHTML . '&nbsp';
		echo '<div class="mp-object-name" title="">' . htmlspecialchars(GetPlainDisplayName($sObjectName)) . '&nbsp;';
		echo '</a>';
	} else {
		echo $sObjectImageHTML . '&nbsp';
		echo '<div class="mp-object-name" title="">' . htmlspecialchars(GetPlainDisplayName($sObjectName)) . '&nbsp;';
		echo '</a>';
	}
	echo '</div>';
	echo '<div class="object-line2">';
	if (!strIsEmpty($sObjectAlias)) {
		echo '<div class="object-alias">' . $sObjectAlias . '</div>';
	}
	echo (strIsEmpty($sDisplayObjType) ? '' : $sDisplayObjType . '&nbsp;');
	$sStereoHTML = buildStereotypeDisplayHTML($sObjectStereotype, $aObjectStereotypes, false);
	if (!strIsEmpty($sStereoHTML)) {
		echo ('&nbsp;' . $sStereoHTML . '&nbsp;&nbsp;');
	}
	echo '</div>';
	$sDocType = SafeGetArrayItem1Dim($aDocument, 'type');
	if ($sDocType === 'MDOC_HTML_CACHE' || $sDocType === 'MDOC_HTML_EDOC1' || $sDocType === 'ExtDoc') {
		echo '<div class="object-document object-document-linked-doc">';
		if ($sDocType === 'MDOC_HTML_EDOC1' || $sDocType === 'MDOC_HTML_CACHE') {
			if ($sDocType === 'MDOC_HTML_EDOC1') {
				$sLoadObject = 'load_object(\'' . $sObjectGUID . '\',\'false\',\'encryptdoc\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\', \'' . AdjustImagePath($sObjImageURL, '16') . '\')';
				echo '<img class="mainprop-object-image ' . GetObjectImageSpriteName('images/element16/encrypteddoc.png') . '" src="images/spriteplaceholder.png" alt=""> ' . _glt('Linked Document') . ' ';
				echo '<br><div id="miniprops-linked-document-pwd"><input id="linked-document-pwd-field" placeholder="' . _glt('password') . '" type="password" onkeypress="return onLinkDocPWDKeyDown(event,\'' . $sObjectGUID . '\',\'' . ConvertStringToParameter($sObjectName) . '\', \'' .  AdjustImagePath($sObjImageURL, '16') . '\')" value="">';
				echo '<button class="linked-document-open-button webea-main-styled-button" onclick="' . $sLoadObject . '">' . _glt('Open Document') . '</button> </div>';
			} else {
				$sLoadObject = 'load_object(\'' . $sObjectGUID . '\',\'false\',\'document\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\', \'' . AdjustImagePath($sObjImageURL, '16') . '\')';
				echo '<img class="mainprop-object-image ' . GetObjectImageSpriteName('images/element16/document.png') . '" src="images/spriteplaceholder.png" alt=""> ' . _glt('Linked Document') . ' ';
				echo '<button class="linked-document-open-button webea-main-styled-button" onclick="' . $sLoadObject . '">' . _glt('Open Document') . '</button> ';
			}
		} elseif ($sDocType === 'ExtDoc') {
			if (
				isset($_SERVER['HTTP_USER_AGENT']) &&
				(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') === false) &&
				(strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0') === false)
			) {
				$sDocContent = SafeGetArrayItem1Dim($aDocument, 'content');
				echo '<img class="mainprop-object-image ' . GetObjectImageSpriteName('images/element16/storeddocument.png') . '" src="images/spriteplaceholder.png" alt=""> ' . _glt('Stored Document') . ' ';
				echo '<a href="data:application/octet-stream;charset=utf-8;base64,' . $sDocContent . '" download="' . $sObjectName . '"><button class="stored-document-download-button webea-main-styled-button">'  . _glt('Download') . '</button></a>';
			}
		}
		echo "</div>";
	} elseif ($sObjectNType === '32') {
		$sImageAssetGUID	= SafeGetArrayItem1Dim($aCommonProps, 'imageasset');
		if (!strIsEmpty($sImageAssetGUID)) {
			echo '<div class="object-document">';
			echo '<img class="mainprop-object-image ' . GetObjectImageSpriteName('images/element16/imageasset.png') . '" src="images/spriteplaceholder.png" alt=""> ' . _glt('Image Asset') . ' ';
			echo '<a href="data_api/dl_model_image.php?objectguid=' . $sImageAssetGUID . '" download="' . $sObjectName . '"><button class="stored-document-download-button webea-main-styled-button">'  . _glt('Download') . '</button></a>';
			echo "</div>";
		}
	}
} else {
}
echo '</div>';
$sLinkType 		= SafeGetInternalArrayParameter($_POST, 'linktype');
if ($sObjectType !== 'ModelRoot') {
	WriteSectionSummary($aCommonProps);
	$sNavButtonHTML = '';
	if (!isset($sObjImageURL16))
		$sObjImageURL16 = '';
	if (!isset($sPrefix))
		$sPrefix = '';
	if (!isset($bShowingMainProps)) {
		$sNavButtonHTML .= '<button onclick="load_object(\'' . $sObjectGUID . '\',\'false\',\'props\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\', \'' . $sObjImageURL16 . '\')" id="props-tab-notes" class="properties-tab" value="notes" type="button"><img alt="" style="vertical-align: bottom;margin-right: 2px;" src="images/spriteplaceholder.png" class="propsprite-properties">Full Properties</button>';
	}
	if (($sPrefix === 'pk' || ($sPrefix === 'el' && $sHasChildren === 'true')) &&
		(!IsSessionSettingTrue('show_browser'))
	) {
		if ((!isset($_POST['linktype'])) ||
			(isset($_POST['linktype']) && $_POST['linktype'] === 'props')
		) {
			if ($sPrefix === 'pk')
				$sLabel = 'View Contents';
			else
				$sLabel = 'View Children';
			$sNavButtonHTML .= '<button onclick="load_object(\'' . $sObjectGUID . '\',\'false\',\'child\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\', \'' . $sObjImageURL16 . '\')" id="props-tab-notes" class="properties-tab" value="notes" type="button"><img alt="" style="vertical-align: bottom;margin-right: 2px;" src="images/spriteplaceholder.png" class="propsprite-viewcontents">' . $sLabel . '</button>';
		}
	}
	if (!strIsEmpty($sNavButtonHTML)) {
		echo '<div style="padding: 0 0 8px 8px;">';
		echo $sNavButtonHTML;
		echo '</div>';
	}
}
if (IsSessionSettingTrue('prop_visible_location')) {
	WriteSectionLocation('location', _glt('Location'), '1', $aParentInfo, $aUsages, false, true);
}
if ($sObjectType !== 'ModelRoot') {
	$sLabel = 'Notes';
	if (!isset($sSelectedFeatureID))
		$sSelectedFeatureID = 'nt';
	if ($sSelectedFeatureID === 'ts')
		$sLabel = 'Tests';
	else if ($sSelectedFeatureID === 'tv')
		$sLabel = 'Tagged Values';
	else if ($sSelectedFeatureID === 'lt')
		$sLabel = 'Relationships';
	else if ($sSelectedFeatureID === 'at')
		$sLabel = 'Attributes';
	else if ($sSelectedFeatureID === 'op')
		$sLabel = 'Operations';
	else if ($sSelectedFeatureID === 'ra')
		$sLabel = 'Resources';
	else if ($sSelectedFeatureID === 'ch')
		$sLabel = 'Changes';
	else if ($sSelectedFeatureID === 'df')
		$sLabel = 'Defects';
	else if ($sSelectedFeatureID === 'is')
		$sLabel = 'Issues';
	else if ($sSelectedFeatureID === 'tk')
		$sLabel = 'Tasks';
	else if ($sSelectedFeatureID === 'ev')
		$sLabel = 'Events';
	else if ($sSelectedFeatureID === 'dc')
		$sLabel = 'Decisions';
	else if ($sSelectedFeatureID === 'dr')
		$sLabel = 'Discussions';
	else if ($sSelectedFeatureID === 'nt')
		$sLabel = 'Notes';
	else
		$sLabel = 'Notes';
	WriteSelectFeatureMenu($sObjectGUID, $sLabel);
	echo '<div id="feature-mini-sections">';
	if ($sSelectedFeatureID === 'ts')
		WriteSectionTest($aTests, $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, '', true);
	else if ($sSelectedFeatureID === 'tv')
		WriteSectionTaggedValues($aTaggedValues, true);
	else if ($sSelectedFeatureID === 'lt')
		WriteSectionRelationships('relationships', _glt('Relationships'), '1', $aRelationships, true);
	else if ($sSelectedFeatureID === 'at')
		WriteSectionAttributes($aAttributes, true);
	else if ($sSelectedFeatureID === 'op')
		WriteSectionOperations($aOperations, true);
	else if ($sSelectedFeatureID === 'ra')
		WriteSectionResourceAllocs($aResAllocs, $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, '', true);
	else if ($sSelectedFeatureID === 'ch')
		WriteSectionChangeManagement1($aChanges, 'change', $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, '', true);
	else if ($sSelectedFeatureID === 'df')
		WriteSectionChangeManagement1($aDefects, 'defect', $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, '', true);
	else if ($sSelectedFeatureID === 'is')
		WriteSectionChangeManagement1($aIssues, 'issue', $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, '', true);
	else if ($sSelectedFeatureID === 'tk')
		WriteSectionChangeManagement1($aTasks, 'task', $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, '', true);
	else if ($sSelectedFeatureID === 'ev')
		WriteSectionChangeManagement1($aEvents, 'event', $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, '', true);
	else if ($sSelectedFeatureID === 'dc')
		WriteSectionChangeManagement1($aDecisions, 'decision', $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, '', true);
	else if ($sSelectedFeatureID === 'nt')
		WriteSectionNotes($sNotes, $sObjectGUID, $sObjectName, $bObjLocked, $sObjImageURL, $sLinkType, false, true);
	else
		WriteSectionNotes($sNotes, $sObjectGUID, $sObjectName, $bObjLocked, $sObjImageURL, $sLinkType, false, true);
	echo '</div>';
}
function WriteSelectFeatureMenu($sGUID, $sLabel)
{
	echo '<div>';
	echo '<div id="select-feature-heading">' . $sLabel . '</div>';
	echo '<div id="select-feature-button">';
	echo '<img alt="" id="select-feature-image" src="images/spriteplaceholder.png" class="mainsprite-hamburger24blue" title="Select Feature" onclick="show_menu(this)">&nbsp;';
	echo '<div id="select-feature-menu">';
	echo '<div class="contextmenu-header">' . _glt('Select Feature') . '</div>';
	echo '<div class="contextmenu-items">';
	echo '<div class="contextmenu-item" onclick="select_feature(\'nt\',\'Notes\',\'' . $sGUID . '\')"><img alt=""  style="top: 0px;" src="images/spriteplaceholder.png" class="propsprite-note"></img>' . _glt('Notes') . '</div>';
	if (IsSessionSettingTrue('prop_visible_taggedvalues'))
		echo '<div class="contextmenu-item" onclick="select_feature(\'tv\',\'Tagged Values\',\'' . $sGUID . '\')"><img alt=""  src="images/spriteplaceholder.png" class="propsprite-taggedvalue"></img>' . _glt('Tagged Values') . '</div>';
	if (IsSessionSettingTrue('prop_visible_relationships'))
		echo '<div class="contextmenu-item" onclick="select_feature(\'lt\',\'Relationships\',\'' . $sGUID . '\')"><img alt=""  src="images/spriteplaceholder.png" class="propsprite-relationship"></img>' . _glt('Relationships') . '</div>';
	if (IsSessionSettingTrue('prop_visible_attributes'))
		echo '<div class="contextmenu-item" onclick="select_feature(\'at\',\'Attributes\',\'' . $sGUID . '\')"><img alt=""  src="images/spriteplaceholder.png" style="left: 0px;" class="propsprite-attribute"></img>' . _glt('Attributes') . '</div>';
	if (IsSessionSettingTrue('prop_visible_operations'))
		echo '<div class="contextmenu-item" onclick="select_feature(\'op\',\'Operations\',\'' . $sGUID . '\')"><img alt=""  src="images/spriteplaceholder.png" style="left: 0px;" class="propsprite-operation"></img>' . _glt('Operations') . '</div>';
	if (IsSessionSettingTrue('prop_visible_testing'))
		echo '<div class="contextmenu-item" onclick="select_feature(\'ts\',\'Tests\',\'' . $sGUID . '\')"><img alt=""  src="images/spriteplaceholder.png" class="propsprite-test"></img>' . _glt('Tests') . '</div>';
	if (IsSessionSettingTrue('prop_visible_resourcealloc'))
		echo '<div class="contextmenu-item" onclick="select_feature(\'ra\',\'Resources\',\'' . $sGUID . '\')"><img alt=""  src="images/spriteplaceholder.png" class="propsprite-resource"></img>' . _glt('Resources') . '</div>';
	if (IsSessionSettingTrue('prop_visible_changes'))
		echo '<div class="contextmenu-item" onclick="select_feature(\'ch\',\'Changes\',\'' . $sGUID . '\')"><img alt=""  src="images/spriteplaceholder.png" class="propsprite-change"></img>' . _glt('Changes') . '</div>';
	if (IsSessionSettingTrue('prop_visible_defects'))
		echo '<div class="contextmenu-item" onclick="select_feature(\'df\',\'Defects\',\'' . $sGUID . '\')"><img alt=""  src="images/spriteplaceholder.png" class="propsprite-defect"></img>' . _glt('Defects') . '</div>';
	if (IsSessionSettingTrue('prop_visible_issues'))
		echo '<div class="contextmenu-item" onclick="select_feature(\'is\',\'Issues\',\'' . $sGUID . '\')"><img alt=""  src="images/spriteplaceholder.png" class="propsprite-issue"></img>' . _glt('Issues') . '</div>';
	if (IsSessionSettingTrue('prop_visible_tasks'))
		echo '<div class="contextmenu-item" onclick="select_feature(\'tk\',\'Tasks\',\'' . $sGUID . '\')"><img alt=""  src="images/spriteplaceholder.png" class="propsprite-task"></img>' . _glt('Tasks') . '</div>';
	if (IsSessionSettingTrue('prop_visible_events'))
		echo '<div class="contextmenu-item" onclick="select_feature(\'ev\',\'Events\',\'' . $sGUID . '\')"><img alt=""  src="images/spriteplaceholder.png" class="propsprite-event"></img>' . _glt('Events') . '</div>';
	if (IsSessionSettingTrue('prop_visible_decisions'))
		echo '<div class="contextmenu-item" onclick="select_feature(\'dc\',\'Decisions\',\'' . $sGUID . '\')"><img alt=""  src="images/spriteplaceholder.png" class="propsprite-decision"></img>' . _glt('Decisions') . '</div>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
}
