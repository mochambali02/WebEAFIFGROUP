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
$g_sViewingMode		= '3';
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
if ($sResType === 'Diagram') {
	$sResourceFeaturesFilter = 'dip';
} else {
	$sResourceFeaturesFilter = '';
}
include('./data_api/get_properties.php');
include('propertysections.php');
$sMainClass = '';
$sMainClassEx = '';
if (IsSessionSettingTrue('show_browser')) {
	$sMainClass = ' show-browser';
	$sMainClassEx = ' class="show-browser"';
}
$sOSLCErrorMsg = BuildOSLCErrorString();
if (strIsEmpty($sOSLCErrorMsg)) {
	if (SafeGetArrayItem1Dim($aCommonProps, 'type') === 'ModelRoot' || SafeGetArrayItem1Dim($aCommonProps, 'guid') === '') {
		return;
	}
	$sPropertyLayoutNo = '1';
	if (isset($_SESSION['propertylayout'])) {
		$sPropertyLayoutNo = $_SESSION['propertylayout'];
	}
	$sShowBrowserMinProps = 'class="' . GetShowBrowserMinPropsStyleClasses() . '"';
	echo '<div id="properties-container" ' . $sShowBrowserMinProps . '>';
	echo '<div id="properties-main" class="properties-main' . $sPropertyLayoutNo . $sMainClass . '">';
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
	$sNotes 		= SafeGetArrayItem1Dim($aCommonProps, 'notes');
	$sObjClassName 	= SafeGetArrayItem1Dim($aCommonProps, 'classname');
	$sObjClassGUID 	= SafeGetArrayItem1Dim($aCommonProps, 'classguid');
	$sObjClassImageURL	= SafeGetArrayItem1Dim($aCommonProps, 'classimageurl');
	$sReviewStatus	= '';
	$sReviewStartDate = '';
	$sReviewEndDate	= '';
	$bIsReviewElement = false;
	if (($sObjectResType === 'Element' && $sObjectType === 'Artifact' && $sObjectStereotype === 'EAReview')) {
		$bIsReviewElement = true;
		$sReviewStatus	= GetTaggedValue($aTaggedValues, 'Status', 'EAReview::Status');
		$sReviewStartDate = GetTaggedValue($aTaggedValues, 'StartDate', 'EAReview::StartDate');
		$sReviewEndDate	= GetTaggedValue($aTaggedValues, 'EndDate', 'EAReview::EndDate');
	}
	echo '<div id="object-main-details">';
	if ($sObjectResType === 'Package' || $sObjectResType === 'Element') {
		if (!$bObjLocked) {
			echo '<div class="object-navigation">';
			include('elementhamburger.php');
			echo '</div>';
		}
	}
	echo '<div class="object-image">';
	if ($sHasChildren === 'true' && $bObjLocked) {
		$sImageLink = '<img style="background:url(' . $sObjImageURL . ')" src="images/element64/lockedhaschildoverlay.png" alt="" title="' . _glt('View child objects for locked') . ' ' . $sObjectResType . '" height="64" width="64"/>';
	} elseif ($sHasChildren === 'true' && !$bObjLocked) {
		if ($sMainClass !== ' show-browser') {
			$sImageLink = '<img style="background:url(' . $sObjImageURL . ')" src="images/element64/haschildoverlay.png" alt="" title="' . _glt('View child objects') . '" height="64" width="64"/>';
		} else {
			$sImageLink = '<img style="background:url(' . $sObjImageURL . ')" src="images/element64/haschildoverlay.png" alt="" height="64" width="64"/>';
		}
	} elseif ($sHasChildren === 'false' && $bObjLocked) {
		$sImageLink = '<img style="background:url(' . $sObjImageURL . ')" src="images/element64/lockedoverlay.png" alt="" title="' . $sObjectResType . ' ' . _glt('is locked') . '" height="64" width="64"/>';
	} else {
		$sImageLink = '<img src="' . $sObjImageURL . '" alt="" title="" height="64" width="64"/>';
	}
	if (($sObjectResType === 'ModelRoot' || $sObjectResType === 'Package' || $sHasChildren === 'true') && $sMainClass !== ' show-browser') {
		if ($sObjectResType === 'Element' && $sHasChildren === 'true') {
			echo '<a class="w3-link" onclick="load_object(\'' . $sObjectGUID . '\',\'false\',\'child\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\', \'' . $aCommonProps['imageurl'] . '\')">';
		} else {
			echo '<a class="w3-link" onclick="load_object(\'' . $sObjectGUID . '\',\'true\',\'\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\', \'' . $aCommonProps['imageurl'] . '\')">';
		}
		echo $sImageLink;
		echo '</a>';
	} elseif ($sObjectResType === 'Diagram') {
		echo '<a class="w3-link" onclick="load_object(\'' . $sObjectGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\', \'' . $aCommonProps['imageurl'] . '\')">';
		echo $sImageLink;
		echo '</a>';
	} elseif ($sObjectType === 'Artifact' && $sObjectStereotype === 'Image') {
		$sImageBin =  SafeGetArrayItem1Dim($aCommonProps, 'imagepreview');
		if (!strIsEmpty($sImageBin)) {
			$sImageLink = '<div class="object-image-asset-div"><img class="object-image-asset" src="data:image/png;base64,' . $sImageBin . '" alt="" title=""/></div>';
			echo $sImageLink;
		}
	} else {
		echo $sImageLink;
	}
	echo '</div>';
	if (IsHyperlink($sObjectType, $sObjectName, $sObjectNType)) {
		$sDisplayName = $sObjectName;
		if (!strIsEmpty($sObjectAlias)) {
			$sDisplayName = $sObjectAlias;
		}
		$sDisplayName 	= GetPlainDisplayName($sDisplayName);
		echo '<div class="object-name">' . htmlspecialchars($sDisplayName) . '</div>';
		echo '<div class="object-line2">';
		echo _glt('Hyperlink') . '&nbsp;';
		echo '</div>';
		echo '</div>';
	} elseif (ShouldIgnoreName($sObjectType, $sObjectName, $sObjectNType)) {
		$sDisplayObjType = $sObjectType;
		if (!strIsEmpty($sObjClassGUID)) {
			$sDisplayObjType  = '<a class="w3-link" onclick="load_object(\'' . $sObjClassGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sObjClassName) . '\',\'' . $sObjClassImageURL . '\')">';
			$sDisplayObjType .= '<img src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sObjClassImageURL) . '" alt="" style="float: none;">&nbsp;' . htmlspecialchars($sObjClassName) . '</a>';
		} else {
			$sDisplayObjType = htmlspecialchars(_glt($sDisplayObjType));
		}
		echo '<div class="object-name" title="' . htmlspecialchars(GetPlainDisplayName($sObjectName)) . '">' . htmlspecialchars(GetPlainDisplayName($sObjectName)) . '</div>';
		echo '<div class="object-line2" style="padding: 0 0 12px 84px;">';
		if (!strIsEmpty($sObjectAlias)) {
			echo '<div class="object-alias">' . $sObjectAlias . '</div>';
		}
		echo (strIsEmpty($sDisplayObjType) ? '' : $sDisplayObjType . '&nbsp;');
		$sStereoHTML = buildStereotypeDisplayHTML($sObjectStereotype, $aObjectStereotypes, false);
		if (!strIsEmpty($sStereoHTML)) {
			echo ('&nbsp;' . $sStereoHTML . '&nbsp;&nbsp;');
		}
		echo '</div>';
		echo '</div>';
		$sDocType = SafeGetArrayItem1Dim($aDocument, 'type');
		if ($sDocType === 'MDOC_HTML_CACHE' || $sDocType === 'MDOC_HTML_EDOC1' || $sDocType === 'ExtDoc') {
			echo '<div class="object-document">';
			if ($sDocType === 'MDOC_HTML_EDOC1' || $sDocType === 'MDOC_HTML_CACHE') {
				if ($sDocType === 'MDOC_HTML_EDOC1') {
					$sLoadObject = 'load_object(\'' . $sObjectGUID . '\',\'false\',\'encryptdoc\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\', \'' . AdjustImagePath($sObjImageURL, '16') . '\')';
					echo '<img class="mainprop-object-image ' . GetObjectImageSpriteName('images/element16/encrypteddoc.png') . '" src="images/spriteplaceholder.png" alt=""> ' . _glt('Linked Document') . ' ';
					echo '<input id="linked-document-pwd-field" placeholder="' . _glt('password') . '" type="password" onkeypress="return onLinkDocPWDKeyDown(event,\'' . $sObjectGUID . '\',\'' . ConvertStringToParameter($sObjectName) . '\', \'' .  AdjustImagePath($sObjImageURL, '16') . '\')" value="">';
				} else {
					$sLoadObject = 'load_object(\'' . $sObjectGUID . '\',\'false\',\'document\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\', \'' . AdjustImagePath($sObjImageURL, '16') . '\')';
					echo '<img class="mainprop-object-image ' . GetObjectImageSpriteName('images/element16/document.png') . '" src="images/spriteplaceholder.png" alt=""> ' . _glt('Linked Document') . ' ';
				}
				echo '<button class="linked-document-open-button webea-main-styled-button" onclick="' . $sLoadObject . '">' . _glt('Open Document') . '</button> ';
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
		echo '</div>';
	}
	$aProps = array();
	if ($bIsReviewElement)
		array_push($aProps, 'summary');
	array_push($aProps, 'notes');
	if (IsSessionSettingTrue('prop_visible_location'))
		array_push($aProps, 'location');
	if (count($aTaggedValues) > 0 && IsSessionSettingTrue('prop_visible_taggedvalues'))
		array_push($aProps, 'tagged values');
	if (count($aRelationships) > 0 && IsSessionSettingTrue('prop_visible_relationships'))
		array_push($aProps, 'relationships');
	if (count($aAttributes) > 0 && IsSessionSettingTrue('prop_visible_attributes'))
		array_push($aProps, 'attributes');
	if (count($aOperations) > 0 && IsSessionSettingTrue('prop_visible_operations'))
		array_push($aProps, 'operations');
	if (count($aRunStates) > 0 && IsSessionSettingTrue('prop_visible_runstates'))
		array_push($aProps, 'run states');
	if (count($aRequirements) > 0)
		array_push($aProps, 'requirements');
	if (count($aConstraints) > 0)
		array_push($aProps, 'constraints');
	if (count($aScenarios) > 0)
		array_push($aProps, 'scenarios');
	if (count($aTests) > 0 && IsSessionSettingTrue('prop_visible_testing'))
		array_push($aProps, 'tests');
	if (count($aResAllocs) > 0 && IsSessionSettingTrue('prop_visible_resourcealloc'))
		array_push($aProps, 'resources');
	if (count($aChanges) > 0 && IsSessionSettingTrue('prop_visible_changes'))
		array_push($aProps, 'changes');
	if (count($aDefects) > 0 && IsSessionSettingTrue('prop_visible_defects'))
		array_push($aProps, 'defects');
	if (count($aIssues) > 0 && IsSessionSettingTrue('prop_visible_issues'))
		array_push($aProps, 'issues');
	if (count($aTasks) > 0 && IsSessionSettingTrue('prop_visible_tasks'))
		array_push($aProps, 'tasks');
	if (count($aEvents) > 0 && IsSessionSettingTrue('prop_visible_events'))
		array_push($aProps, 'events');
	if (count($aDecisions) > 0 && IsSessionSettingTrue('prop_visible_decisions'))
		array_push($aProps, 'decisions');
	if (count($aEfforts) > 0 && IsSessionSettingTrue('prop_visible_efforts'))
		array_push($aProps, 'efforts');
	if (count($aRisks) > 0 && IsSessionSettingTrue('prop_visible_risks'))
		array_push($aProps, 'risks');
	if (count($aMetrics) > 0 && IsSessionSettingTrue('prop_visible_metrics'))
		array_push($aProps, 'metrics');
	if (IsSessionSettingTrue('show_discuss')) {
		if (count($aDiscussions) > 0 || IsSessionSettingTrue('add_discuss')) {
			array_push($aProps, 'discussions');
		}
	}
	$sPropertiesDetails = '';
	$sPCSEdition = SafeGetInternalArrayParameter($_SESSION, 'pro_cloud_license');
	if ($sObjectResType === 'Diagram' && (!IsSessionSettingTrue('readonly_model') && $sPCSEdition !== 'Express')) {
		$sObjectGenerated = SafeGetArrayItem1Dim($aCommonProps, 'generated');
		$sImageInSync = SafeGetArrayItem1Dim($aCommonProps, 'imageinsync');
		if (strIsEmpty($sObjectGenerated)) {
			$sObjectGenerated = _glt('<awaiting>');
		} else {
			$sObjectGenerated = htmlspecialchars($sObjectGenerated);
		}
		$sPropertiesDetails .= '<div class="generate-diagram-section">';
		$sPropertiesDetails .= '<a class="w3-grey-text">' . _glt('Generated') . ' </a>';
		$sPropertiesDetails .= '' . $sObjectGenerated;
		$sPropertiesDetails .= '<input class="webea-main-styled-button" id="generatediagram-action-button"' . ((strIsTrue($sImageInSync)) ? '' : ' disabled=""') . ' type="button" onclick="OnRequestDiagramRegenerate(\'' . ConvertStringToParameter($sObjectGUID) . '\')" title="' . _glt('Mark the current diagram as requiring re-generation') . '"></td>';
		$sPropertiesDetails .= '';
		$sPropertiesDetails .= '<div id="diagram-regeneration-label-line"' . ((strIsTrue($sImageInSync)) ? ' style="display: none;"' : '') . '>';
		$sPropertiesDetails .= '<div class="diagram-regeneration-label">  ' . _glt('Image pending regeneration') . '  </div>';
		$sPropertiesDetails .= '</div>';
		$sPropertiesDetails .= '</div>';
	}
	echo $sPropertiesDetails;
	$bShowingMiniProps = SafeGetInternalArrayParameter($_SESSION, 'show_miniproperties');
	if (!strIsTrue($bShowingMiniProps))
		WriteSectionSummary($aCommonProps);
	if ($bIsReviewElement) {
		$sPartInReviews = SafeGetInternalArrayParameter($_SESSION, 'participate_in_reviews');
		if (strIsTrue($sPartInReviews)) {
			$sReviewSession = SafeGetInternalArrayParameter($_SESSION, 'review_session');
			$bJoin  = false;
			$bLeave = false;
			$sJoinTitle = '';
			$sLeaveTitle = '';
			if (strIsEmpty($sReviewSession)) {
				$bJoin = true;
			} else {
				if ($sReviewSession === $sObjectGUID) {
					$bLeave = true;
				}
			}
			echo '<div class="review-session-section"><div class="review-session-actions">';
			echo '<input class="review-session-action-button" name="join" id="review-session-join-button" value="' . _glt('Join Review') . '" type="button" title="' . $sJoinTitle . '" onclick="OnJoinLeaveReviewSession(\'' . $sObjectGUID . '\', \'' . ConvertStringToParameter($sObjectName) . '\', \'' . $sObjectGUID . '\')" ' . ($bJoin ? '>' : 'disabled="">');
			echo '<input class="review-session-action-button" name="leave" id="review-session-leave-button" value="' . _glt('Leave Review') . '" type="button" onclick="OnJoinLeaveReviewSession(\'\', \'\', \'' . $sObjectGUID . '\')" ' . ($bLeave ? '>' : 'disabled="">');
			echo '</div></div>';
		}
	}
	$bShowingMainProps = true;
	WritePropertiesTabs($aProps, $sObjectGUID, $sObjectHasChild, $sObjectName, $sObjectImageURL, $bIsReviewElement);
	echo '<div id="property-sections">';
	if ($bIsReviewElement)
		WriteSectionReview($sObjectGUID, $sObjectName, $aTaggedValues, $aReviewDiscuss, $aReviewDiagrams, $aReviewNoDiscuss);
	WriteSectionNotes($sNotes, $sObjectGUID, $sObjectName, $bObjLocked, $sObjImageURL, $sLinkType, $bIsReviewElement);
	WriteSectionLocation('location', _glt('Location'), '1', $aParentInfo, $aUsages);
	if (count($aTaggedValues) > 0 && IsSessionSettingTrue('prop_visible_taggedvalues')) {
		WriteSectionTaggedValues($aTaggedValues);
	}
	if (IsSessionSettingTrue('prop_visible_relationships')) {
		WriteSectionRelationships('relationships', _glt('Relationships'), '1', $aRelationships);
	}
	if (count($aAttributes) > 0 && IsSessionSettingTrue('prop_visible_attributes')) {
		WriteSectionAttributes($aAttributes);
	}
	if (count($aOperations) > 0 && IsSessionSettingTrue('prop_visible_operations')) {
		WriteSectionOperations($aOperations);
	}
	if (count($aRunStates) > 0 && IsSessionSettingTrue('prop_visible_runstates')) {
		WriteSectionRunStates($aRunStates, 'runstate');
	}
	if (count($aRequirements) > 0) {
		WriteSectionRequirements($aRequirements);
	}
	if (count($aConstraints) > 0) {
		WriteSectionConstraints($aConstraints);
	}
	if (count($aScenarios) > 0) {
		WriteSectionScenarios($aScenarios);
	}
	if (count($aTests) > 0 && IsSessionSettingTrue('prop_visible_testing')) {
		WriteSectionTest($aTests, $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, $sObjectHyper);
	}
	if (count($aResAllocs) > 0 && IsSessionSettingTrue('prop_visible_resourcealloc')) {
		WriteSectionResourceAllocs($aResAllocs, $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, $sObjectHyper);
	}
	if (count($aChanges) > 0 && IsSessionSettingTrue('prop_visible_changes')) {
		WriteSectionChangeManagement1($aChanges, 'change', $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, $sObjectHyper);
	}
	if (count($aDefects) > 0 && IsSessionSettingTrue('prop_visible_defects')) {
		WriteSectionChangeManagement1($aDefects, 'defect', $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, $sObjectHyper);
	}
	if (count($aIssues) > 0 && IsSessionSettingTrue('prop_visible_issues')) {
		WriteSectionChangeManagement1($aIssues, 'issue', $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, $sObjectHyper);
	}
	if (count($aTasks) > 0 && IsSessionSettingTrue('prop_visible_tasks')) {
		WriteSectionChangeManagement1($aTasks, 'task', $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, $sObjectHyper);
	}
	if (count($aEvents) > 0 && IsSessionSettingTrue('prop_visible_events')) {
		WriteSectionChangeManagement1($aEvents, 'event', $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, $sObjectHyper);
	}
	if (count($aDecisions) > 0 && IsSessionSettingTrue('prop_visible_decisions')) {
		WriteSectionChangeManagement1($aDecisions, 'decision', $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, $sObjectHyper);
	}
	if (count($aEfforts) > 0 && IsSessionSettingTrue('prop_visible_efforts')) {
		WriteSectionChangeManagement2($aEfforts, 'effort', $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, $sObjectHyper);
	}
	if (count($aRisks) > 0 && IsSessionSettingTrue('prop_visible_risks')) {
		WriteSectionChangeManagement2($aRisks, 'risk', $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, $sObjectHyper);
	}
	if (count($aMetrics) > 0 && IsSessionSettingTrue('prop_visible_metrics')) {
		WriteSectionChangeManagement2($aMetrics, 'metric', $sObjectGUID, $bObjLocked, $sObjectName, $sObjImageURL, $sLinkType, $sObjectHyper);
	}
	include('propertiesdiscussions.php');
	echo '</div>';
	echo '</div>';
	echo '</div>';
} else {
	if (SafeGetArrayItem1Dim($aCommonProps, 'guid') === '') {
		echo '<div id="main-content-empty" ' . $sMainClassEx . '>';
		$s = _glt('NoElementLine1') . g_csHTTPNewLine . g_csHTTPNewLine;
		$sMessage = '';
		if (!strIsEmpty($s) && $s !== 'NoElementLine1') {
			$sMessage .=  $s . g_csHTTPNewLine . g_csHTTPNewLine;
		}
		$s = _glt('NoElementLine2');
		if (!strIsEmpty($s) && $s !== 'NoElementLine2') {
			$sMessage .=  $s . g_csHTTPNewLine . g_csHTTPNewLine;
		}
		$sMessage .= '<a href="javascript:MoveToPrevItemInNavigationHistory()">' . _glt('Back') . '</a>';
		echo $sMessage;
		echo '</div>';
		exit();
	} else {
		echo '<div id="main-content-empty">' . $sOSLCErrorMsg . '</div>';
	}
}
function WritePropertiesTabs($aProps, $sGUID, $sHasChild, $sName, $sImageURL, $bIsReviewElement)
{
	echo '<div id="properties-tabs">';
	foreach ($aProps as $prop) {
		if ($prop !== "location")
			$propSingular = substr($prop, 0, -1);
		else
			$propSingular = $prop;
		$propSingular = str_replace(' ', '', $propSingular);
		$sButtonID = preg_replace('/\s+/', '', $prop);
		if ($prop === 'summary') {
			$sStyle = 'style="background-color: rgb(238, 238, 238);"';
			$propSingular = 'review';
		} else if ($prop === 'notes' && !$bIsReviewElement) {
			$sStyle = 'style="background-color: rgb(238, 238, 238);"';
		} else {
			$sStyle = '';
		}
		echo '<button onclick="show_section(\'' . $prop . '\')" id="props-tab-' . $sButtonID . '" class="properties-tab" value="' . $prop . '" onclick="" ' . $sStyle . ' type="button">';
		echo '<img alt="" style="vertical-align: bottom;margin-right: 2px;" src="images/spriteplaceholder.png" class="propsprite-' . $propSingular . '">';
		echo ucfirst($prop);
		echo '</button>';
	}
	echo '</div>';
}
