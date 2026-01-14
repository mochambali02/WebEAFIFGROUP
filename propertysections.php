<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST"  &&  !isset($webea_page_parent_mainview)) {
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
function WriteSectionCommon($aCommonProps, $bIsMiniProps = false)
{
	echo '<div id="common-section">';
	$sObjectGUID 	= SafeGetArrayItem1Dim($aCommonProps, 'guid');
	$sObjectName 	= SafeGetArrayItem1Dim($aCommonProps, 'name');
	$sImageURL		= SafeGetArrayItem1Dim($aCommonProps, 'imageurl');
	$sObjectResType = SafeGetArrayItem1Dim($aCommonProps, 'restype');
	$sObjectModified  = SafeGetArrayItem1Dim($aCommonProps, 'modified');
	$sObjectStatus	= SafeGetArrayItem1Dim($aCommonProps, 'status');
	$sObjectVersion	= SafeGetArrayItem1Dim($aCommonProps, 'version');
	$sObjectPhase	= SafeGetArrayItem1Dim($aCommonProps, 'phase');
	$aCompositeInfo	= SafeGetArrayItem1Dim($aCommonProps, 'composite_info');
	$sModDate = RemoveTime($sObjectModified);
	$sModTime = RemoveDate($sObjectModified);
	$sCreated = SafeGetArrayItem1Dim($aCommonProps, 'created');
	$sCreated = RemoveTime($sCreated);;
	$sType 			= SafeGetArrayItem1Dim($aCommonProps, 'type');
	$aStereotypes 	= SafeGetArrayItem1Dim($aCommonProps, 'stereotypes');
	$sStereotype 	= getPrimaryFQStereotype($aStereotypes);
	$sClassName 	= SafeGetArrayItem1Dim($aCommonProps, 'classname');
	$sClassGUID 	= SafeGetArrayItem1Dim($aCommonProps, 'classguid');
	$sClassImageURL	= SafeGetArrayItem1Dim($aCommonProps, 'classimageurl');
	echo '<div class="common-item">';
	$sPropertiesDetails  = '';
	if (strIsTrue($bIsMiniProps)) {
		$sPropertiesDetails .= '<tr><td class="common-label">' . _glt('Name') . '</td>';
		$sPropertiesDetails .= '<td class="common-value">' . $sObjectName;
		$sPropertiesDetails .= '<img alt="" title="Full Properties" onclick="load_object(\'' . $sObjectGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\',\'' . $sImageURL . '\')" src="images/spriteplaceholder.png" class="mainsprite-mp-info">';
		$sPropertiesDetails .= '</td>';
		$sPropertiesDetails .= '</tr>';
	}
	if (!strIsEmpty($sType) || !strIsEmpty($sStereotype)) {
		$sPropertiesDetails .= '<tr><td class="common-label">' . _glt('Type') . '</td><td class="common-value">';
		$sPropertiesDetails .= $sType;
		if (!strIsEmpty($sType) && !strIsEmpty($sStereotype))
			$sPropertiesDetails .= '&nbsp;&nbsp;&nbsp;';
		if (!strIsEmpty($sStereotype)) {
			$sPropertiesDetails .= buildStereotypeDisplayHTML($sStereotype, $aStereotypes, true);
		}
		$sPropertiesDetails .= '</td></tr>';
	}
	if (!empty($aCompositeInfo)) {
		$sCompositeDiagramName = SafeGetArrayItem1Dim($aCompositeInfo, 'text');
		$sCompositeDiagramGUID = SafeGetArrayItem1Dim($aCompositeInfo, 'guid');
		$sCompositeDiagramImageURL = SafeGetArrayItem1Dim($aCompositeInfo, 'imageurl');
		$sPropertiesDetails .= '<tr><td class="common-label">' . _glt('Composite') . '</td><td class="common-value">';
		$sPropertiesDetails .= '<div class="composite-diagram-name w3-link" title="' . _glt('View Composite Diagram') . '" onclick="load_object(\'' . $sCompositeDiagramGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sCompositeDiagramName) . '\',\'' . $sCompositeDiagramImageURL . '\')" >';
		$sPropertiesDetails .= '<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sCompositeDiagramImageURL) . '"><a style="vertical-align: 2px;"> ' . htmlspecialchars($sCompositeDiagramName);
		$sPropertiesDetails .= '</a></div>';
		$sPropertiesDetails .= '</td></tr>';
	}
	$sPropertiesDetails .= WriteArrayLabelValueProperty($aCommonProps, 'author', _glt('Author'), 'common-label', 'common-value', false);
	$sPropertiesDetails .= WriteArrayLabelValueProperty($aCommonProps, 'created', _glt('Created'), 'common-label', 'common-value', false);
	if (!strIsEmpty($sClassGUID)) {
		$sType  = '<div class="w3-link" onclick="load_object(\'' . $sClassGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sType) . '\',\'' . $sClassImageURL . '\')">';
		$sType .= '<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sClassImageURL) . '" style="float: none;"><a style="vertical-align: 2px;">&nbsp;' . htmlspecialchars($sClassName) . '</a></div>';
	} else {
		$sType  = htmlspecialchars(_glt($sType));
	}
	$sPropertiesDetails .= WriteArrayLabelValueProperty($aCommonProps, 'modified', _glt('Modified'), 'common-label', 'common-value', false);
	$sPCSEdition = SafeGetInternalArrayParameter($_SESSION, 'pro_cloud_license');
	if ($sObjectResType === 'Diagram' && (!IsSessionSettingTrue('readonly_model') && $sPCSEdition !== 'Express')) {
		$sObjectGenerated = SafeGetArrayItem1Dim($aCommonProps, 'generated');
		$sImageInSync = SafeGetArrayItem1Dim($aCommonProps, 'imageinsync');
		if (strIsEmpty($sObjectGenerated)) {
			$sObjectGenerated = _glt('<awaiting>');
		} else {
			$sObjectGenerated = htmlspecialchars($sObjectGenerated);
		}
		#TODO: Currently the OSLC miniprops call does not retrieve the info regarding when the image was generated, so we are excluding this field. We could add this at a later date.
		if (!$bIsMiniProps) {
			$sPropertiesDetails .= '<tr>';
			$sPropertiesDetails .= '<td class="common-label">' . _glt('Generated') . '</td>';
			$sPropertiesDetails .= '<td class="common-value">' . $sObjectGenerated;
			$sPropertiesDetails .= '<input class="webea-main-styled-button" id="generatediagram-action-button"' . ((strIsTrue($sImageInSync)) ? '' : ' disabled=""') . ' type="button" onclick="OnRequestDiagramRegenerate(\'' . ConvertStringToParameter($sObjectGUID) . '\')" title="' . _glt('Mark the current diagram as requiring re-generation') . '"></td>';
			$sPropertiesDetails .= '</tr>';
			$sPropertiesDetails .= '<tr id="diagram-regeneration-label-line"' . ((strIsTrue($sImageInSync)) ? ' style="display: none;"' : '') . '>';
			$sPropertiesDetails .= '<td class="common-label">&nbsp;</td>';
			$sPropertiesDetails .= '<td class="common-value"><div class="diagram-regeneration-label">  ' . _glt('Image pending regeneration') . '  </div></td>';
			$sPropertiesDetails .= '</tr>';
		}
	}
	$sPropertiesDetails .= WriteArrayLabelValueProperty($aCommonProps, 'version', _glt('Version'), 'common-label', 'common-value', false);
	$sPropertiesDetails .= WriteArrayLabelValueProperty($aCommonProps, 'phase', _glt('Phase'), 'common-label', 'common-value', false);
	$sPropertiesDetails .= WriteArrayLabelValueProperty($aCommonProps, 'status', _glt('Status'), 'common-label', 'common-value', false);
	$sPropertiesDetails .= WriteArrayLabelValueProperty($aCommonProps, 'alias', _glt('Alias'), 'common-label', 'common-value', false);
	$sPropertiesDetails .= WriteArrayLabelValueProperty($aCommonProps, 'guid_raw', _glt('GUID'), 'common-label', 'common-value', false);
	echo '<div>';
	echo '<table class="common-table">';
	echo $sPropertiesDetails;
	echo '</table>';
	echo '</div>';
	echo '</div>';
	echo '</div>' . PHP_EOL;
}
function WriteArrayLabelValueProperty($aProps, $sItemName, $sLabel, $sTDLabelClass, $sTDValueClass, $bEchoResult = true)
{
	$sReturn = '';
	$sValue = SafeGetArrayItem1Dim($aProps, $sItemName);
	if (!strIsEmpty($sValue)) {
		$sValue = htmlspecialchars($sValue);
		$sReturn .= '<tr>';
		$sReturn .= '<td class="' . $sTDLabelClass . '">' . $sLabel . '</td>';
		$sReturn .= '<td class="' . $sTDValueClass . '">' . $sValue . '</td>';
		$sReturn .= '</tr>';
	}
	if ($bEchoResult) {
		echo $sReturn;
	}
	return $sReturn;
}
function WriteLabelValueProperty($sLabel, $sValue, $sTDLabelID, $sTDValueID, $bEchoResult = true)
{
	$sReturn = '';
	if (!strIsEmpty($sValue)) {
		$sReturn .= '<tr>';
		$sReturn .= '<td id=' . $sTDLabelID . '>' . $sLabel . '</td>';
		$sReturn .= '<td id=' . $sTDValueID . '>' . $sValue . '</td>';
		$sReturn .= '</tr>';
	}
	if ($bEchoResult) {
		echo $sReturn;
	}
	return $sReturn;
}
function WriteSectionAttributes($a, $bIsMini = false)
{
	if (count($a) <= 0) {
		return;
	}
	if ($bIsMini) {
		$sSectionID = 'attribute-mini-section';
		echo '<div id="' . $sSectionID . '" style="display:block">';
		echo '<div>';
	} else {
		$sSectionID = 'attribute-section';
		echo '<div id="' . $sSectionID . '" style="' . GetSectionVisibility($sSectionID) . '">';
		echo '<div class="properties-header">Attributes</div>';
		echo '<div class="properties-content">';
	}
	$iCnt = count($a);
	for ($i = 0; $i < $iCnt; $i++) {
		$sName 			= SafeGetArrayItem2Dim($a, $i, 'name');
		$sGUID 			= SafeGetArrayItem2Dim($a, $i, 'guid');
		$sType 			= SafeGetArrayItem2Dim($a, $i, 'type');
		$sScope 		= SafeGetArrayItem2Dim($a, $i, 'scope');
		$sDefault 		= SafeGetArrayItem2Dim($a, $i, 'default');
		$sAlias 		= SafeGetArrayItem2Dim($a, $i, 'alias');
		$sStereotype 	= SafeGetArrayItem2Dim($a, $i, 'sterotype');
		$sClassName 	= SafeGetArrayItem2Dim($a, $i, 'classname');
		$sClassGUID 	= SafeGetArrayItem2Dim($a, $i, 'classguid');
		$sClassImageURL	= SafeGetArrayItem2Dim($a, $i, 'classimageurl');
		if (substr($sDefault, 0, 26) == '<Image type="EAShapeScript')
			$sDefault = '<EAShapeScript>';
		$sName			= htmlspecialchars($sName);
		$sDefault		= htmlspecialchars($sDefault);
		$sType 			= htmlspecialchars($sType);
		$sAlias 		= htmlspecialchars($sAlias);
		$sStereotype 	= formatWithStereotypeChars($sStereotype);
		echo '<div class="attribute-item property-section-item">';
		$s = mb_strtolower($sScope);
		$s = ($s === 'private' || $s === 'protected' || $s === 'public' || $s === 'package') ? 'propsprite-attribute' . $s : 'propsprite-attribute';
		if (!strIsEmpty($sClassGUID)) {
			$sType  = '<a class="w3-link" onclick="load_object(\'' . $sClassGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sType) . '\',\'' . $sClassImageURL . '\')">';
			$sType .= '<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sClassImageURL) . '" style="float: none;">&nbsp;' . htmlspecialchars($sClassName) . '</a>';
		}
		$aAttribItemHdr  = '<div class="attribute-item-hdr">';
		$aAttribItemHdr .= '<img alt="" src="images/spriteplaceholder.png" class="' . $s . '">';
		$aAttribItemHdr .= '<div class="attribute-name">' . $sName;
		$aAttribItemHdr .= WriteValueInSentence($sStereotype, '&nbsp;', '', false);
		$aAttribItemHdr .= WriteValueInSentence($sType, ':&nbsp;', '', false);
		$aAttribItemHdr .= WriteValueInSentence($sDefault, '&nbsp;=&nbsp;', '', false);
		$aAttribItemHdr .= '</div>';
		$aAttribItemHdr .= '</div>';
		$sNotes 		= SafeGetArrayItem2Dim($a, $i, 'notes');
		$sMultiplicity 	= SafeGetArrayItem2Dim($a, $i, 'multiplicity');
		$sIsStatic 		= SafeGetArrayItem2Dim($a, $i, 'isstatic');
		$sContainment 	= SafeGetArrayItem2Dim($a, $i, 'containment');
		$sAttribExInfo = '';
		if (
			!strIsEmpty($sNotes) || !strIsEmpty($sMultiplicity) || (!strIsEmpty($sIsStatic) && $sIsStatic !== 'False') ||
			(!strIsEmpty($sContainment) && $sContainment !== 'Not Specified' && $sContainment !== 'False') ||
			array_key_exists(("taggedvalues"), $a[$i])
		) {
			if (!strIsEmpty($sNotes)) {
				$sAttribExInfo .= '<div class="attribute-notes">' . $sNotes . '</div>';
			}
			if (
				!strIsEmpty($sMultiplicity) ||
				!strIsEmpty($sAlias)  ||
				(!strIsEmpty($sIsStatic) && $sIsStatic !== "False") ||
				(!strIsEmpty($sContainment) && $sContainment !== "Not Specified" && $sContainment !== "False")
			) {
				$sAttribExInfo .= '<div class="attribute-properties">';
				if (!strIsEmpty($sAlias)) {
					$sAttribExInfo .= '<div class="attribute-property"><span class="attribute-property-label">' . _glt('Alias') . '&nbsp;=&nbsp;' . $sAlias . '</span>&nbsp;</div>';
				}
				if (!strIsEmpty($sMultiplicity)) {
					$sAllowDups = SafeGetArrayItem2Dim($a, $i, 'allowdups');
					$sIsOrdered = SafeGetArrayItem2Dim($a, $i, 'isordered');
					$sAttribExInfo .= '<div class="attribute-property">';
					$sAttribExInfo .= '<span class="attribute-property-label">' . _glt('Multiplicity') . ':</span>&nbsp;(&nbsp;' . $sMultiplicity;
					if ($sAllowDups == 'True')
						$sAttribExInfo .= '<span class="attribute-property-label">, ' . _glt('Allows duplicates') . '</span>&nbsp;';
					if ($sIsOrdered == 'True')
						$sAttribExInfo .= '<span class="attribute-property-label">, ' . _glt('Ordered') . '</span>&nbsp;';
					$sAttribExInfo .= ' ) </div>';
				}
				if ($sIsStatic === "True") {
					$sAttribExInfo .= '<div class="attribute-property">';
					$sAttribExInfo .= '<span class="attribute-property-label">' . _glt('Is static') . '</span>&nbsp;';
					$sAttribExInfo .= '</div>';
				}
				if (!strIsEmpty($sContainment) && $sContainment !== "Not Specified" && $sContainment !== "False") {
					$sAttribExInfo .= '<div class="attribute-property">';
					$sAttribExInfo .= '<span class="attribute-property-label">' . _glt('Containment') . ':</span>&nbsp;' . $sContainment;
					$sAttribExInfo .= '</div>';
				}
				$sAttribExInfo .= '</div>';
			}
			if (array_key_exists(("taggedvalues"), $a[$i])) {
				$aTV = $a[$i]['taggedvalues'];
				$sAttribExInfo .= '<div class="attribute-taggedvalues">';
				$sAttribExInfo .= '<div id="attribute-taggedvalues-' . $sGUID . '" ';
				$sAttribExInfo .=     ' class="attribute-taggedvalues-header">' . _glt('Properties') . '</div>';
				$sAttribExInfo .= '<div class="attribute-taggedvalues-items">';
				$iTVCnt = count($aTV);
				for ($iTV = 0; $iTV < $iTVCnt; $iTV++) {
					$sAttribExInfo .= '<div class="attribute-taggedvalue-item">';
					$sName = SafeGetArrayItem2Dim($aTV, $iTV, 'name');
					$sValue = SafeGetArrayItem2Dim($aTV, $iTV, 'value');
					$sNotes = SafeGetArrayItem2Dim($aTV, $iTV, 'notes');
					$sNotes = (substr($sNotes, 0, 8) == 'Values: ') ? '' : $sNotes;
					if ($sValue === '<memo>') {
						$sValue = '';
					}
					$sName = htmlspecialchars($sName);
					$sValue = htmlspecialchars($sValue);
					$sNotes = htmlspecialchars($sNotes);
					$sAttribExInfo .= '<div class="attribute-taggedvalue-data">' . $sName;
					if (!strIsEmpty($sValue)) {
						$sAttribExInfo .= '&nbsp;=&nbsp;' . $sValue;
					}
					$sAttribExInfo .= '</div>';
					if (!strIsEmpty($sNotes)) {
						$sAttribExInfo .= '<div class="attribute-taggedvalue-notes">' . $sNotes . '</div>';
					}
					$sAttribExInfo .= '</div>';
				}
				$sAttribExInfo .= '</div>';
				$sAttribExInfo .= '</div>';
			}
		}
		if (!strIsEmpty($sAttribExInfo)) {
			$sSectionName = 'collapsible-' . $sGUID;
			echo '<div id="' . $sSectionName . '" onclick="OnToggleCollapsibleSection(this)" ';
			echo     ' class="collapsible-section-header collapsible-section-header-closed">';
			echo '<img alt="" src="images/spriteplaceholder.png" class="collapsible-section-header-closed-icon">';
			echo $aAttribItemHdr;
			echo '</div>';
			echo '<div class="collapsible-section w3-hide">';
			echo $sAttribExInfo;
			echo '</div>';
		} else {
			echo '<div class="non-collapsible-guid">';
			echo $aAttribItemHdr;
			echo '</div>';
		}
		echo '</div>' . PHP_EOL;
	}
	echo '</div>';
	echo '</div>' . PHP_EOL;
}
function WriteSectionOperations($a, $bIsMini = false)
{
	if (count($a) <= 0) {
		return;
	}
	if ($bIsMini) {
		$sSectionID = 'operation-mini-section';
		echo '<div id="' . $sSectionID . '" style="display:block">';
		echo '<div class="miniprops-content">';
	} else {
		$sSectionID = 'operation-section';
		echo '<div id="' . $sSectionID . '" style="' . GetSectionVisibility($sSectionID) . '">';
		echo '<div class="properties-header">Operations</div>';
		echo '<div class="properties-content">';
	}
	$iCnt = count($a);
	for ($i = 0; $i < $iCnt; $i++) {
		$sName 			= SafeGetArrayItem2Dim($a, $i, 'name');
		$sGUID 			= SafeGetArrayItem2Dim($a, $i, 'guid');
		$sScope 		= SafeGetArrayItem2Dim($a, $i, 'scope');
		$sParameters 	= SafeGetArrayItem2Dim($a, $i, 'parastring');
		$sParametersFormatted = SafeGetArrayItem2Dim($a, $i, 'parastringformat');
		$sType 			= SafeGetArrayItem2Dim($a, $i, 'classifier');
		$sStereotype 	= SafeGetArrayItem2Dim($a, $i, 'sterotype');
		$sAlias 		= SafeGetArrayItem2Dim($a, $i, 'alias');
		$sClassifierName 	= SafeGetArrayItem2Dim($a, $i, 'classifiername');
		$sClassifierGUID 	= SafeGetArrayItem2Dim($a, $i, 'classifierguid');
		$sClassifierImageURL = SafeGetArrayItem2Dim($a, $i, 'classifierimageurl');
		$sName			= htmlspecialchars($sName);
		$sType 			= htmlspecialchars($sType);
		$sStereotype 	= formatWithStereotypeChars($sStereotype);
		$sAlias 		= htmlspecialchars($sAlias);
		echo '<div class="operation-item property-section-item">';
		$s = mb_strtolower($sScope);
		$s = ($s === 'private' || $s === 'protected' || $s === 'public' || $s === 'package') ? 'propsprite-operation' . $s : 'propsprite-operation';
		$sOperationItemHdr = '<div class="operation-item-hdr">';
		$sOperationItemHdr .= '<img alt="" src="images/spriteplaceholder.png" class="' . $s . '">';
		$sOperationItemHdr .= '<div class="operation-name">';
		$sOperationItemHdr .= $sName;
		$sOperationItemHdr .= WriteValueInSentence($sStereotype, '&nbsp;', '', false);
		$sOperationItemHdr .= '(' . $sParameters . ')';
		if (strIsEmpty($sClassifierGUID)) {
			$sOperationItemHdr .= WriteValueInSentence($sType, ':&nbsp;', '', false);
		} else {
			$sOperationItemHdr .= ':&nbsp;<a class="w3-link" onclick="load_object(\'' . $sClassifierGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sClassifierName) . '\',\'' . $sClassifierImageURL . '\')">';
			$sOperationItemHdr .= '<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sClassifierImageURL) . '" style="float: none;">&nbsp;' . htmlspecialchars($sClassifierName);
			$sOperationItemHdr .= '</a>';
		}
		$sOperationItemHdr .= '</div>';
		$sOperationItemHdr .= '</div>';
		$sOperationExInfo = '';
		$sNotes = SafeGetArrayItem2Dim($a, $i, 'notes');
		if (!strIsEmpty($sNotes)) {
			$sOperationExInfo .= '<div class="operation-notes">' . $sNotes . '</div>';
		}
		$sIsStatic 		= SafeGetArrayItem2Dim($a, $i, 'isstatic');
		$sIsAbstract	= SafeGetArrayItem2Dim($a, $i, 'isabstract');
		$sIsReturnArray	= SafeGetArrayItem2Dim($a, $i, 'isreturnarray');
		$sIsQuery 		= SafeGetArrayItem2Dim($a, $i, 'isquery');
		$sIsSynch 		= SafeGetArrayItem2Dim($a, $i, 'issynch');
		if ((!strIsEmpty($sIsStatic) 		&& $sIsStatic !== "False") ||
			(!strIsEmpty($sIsAbstract) 	&& $sIsAbstract !== "False") ||
			(!strIsEmpty($sIsReturnArray) 	&& $sIsReturnArray !== "False") ||
			(!strIsEmpty($sIsQuery)		&& $sIsQuery !== "False") ||
			(!strIsEmpty($sIsSynch) 		&& $sIsSynch !== "False") ||
			(!strIsEmpty($sAlias))
		) {
			$sOperationExInfo .= '<div class="operation-properties">';
			if (!strIsEmpty($sAlias)) {
				$sOperationExInfo .= '<div class="operation-property"><span class="operation-property-label">' . _glt('Alias') . '&nbsp;=&nbsp;' . $sAlias . '</span>&nbsp;</div>';
			}
			if (!strIsEmpty($sIsStatic) && $sIsStatic !== "False") {
				$sOperationExInfo .= '<div class="operation-property"><span class="operation-property-label">' . _glt('Is static') . '</span>&nbsp;</div>';
			}
			if (!strIsEmpty($sIsAbstract) && $sIsAbstract !== "False") {
				$sOperationExInfo .= '<div class="operation-property"><span class="operation-property-label">' . _glt('Is abstract') . '</span>&nbsp;</div>';
			}
			if (!strIsEmpty($sIsReturnArray) && $sIsReturnArray != "=False") {
				$sOperationExInfo .= '<div class="operation-property"><span class="operation-property-label">' . _glt('Is return array') . '</span>&nbsp;</div>';
			}
			if (!strIsEmpty($sIsQuery) && $sIsQuery !== "False") {
				$sOperationExInfo .= '<div class="operation-property"><span class="operation-property-label">' . _glt('Is query') . '</span>&nbsp;</div>';
			}
			if (!strIsEmpty($sIsSynch) && $sIsSynch !== "False") {
				$sOperationExInfo .= '<div class="operation-property"><span class="operation-property-label">' . _glt('Is synchronized') . '</span>&nbsp;</div>';
			}
			$sOperationExInfo .= '</div>';
		}
		if (array_key_exists(("taggedvalues"), $a[$i])) {
			$aTV = $a[$i]['taggedvalues'];
			$sOperationExInfo .= '<div class="operation-taggedvalues">';
			$sOperationExInfo .= '<div id="operation-taggedvalues-' . $sGUID . '" ';
			$sOperationExInfo .=     ' class="operation-taggedvalues-header">' . _glt('Properties') . '</div>';
			$sOperationExInfo .= '<div class="operation-taggedvalues-items">';
			$iTVCnt = count($aTV);
			for ($iTV = 0; $iTV < $iTVCnt; $iTV++) {
				$sOperationExInfo .= '<div class="operation-taggedvalue-item">';
				$sName = SafeGetArrayItem2Dim($aTV, $iTV, 'name');
				$sValue = SafeGetArrayItem2Dim($aTV, $iTV, 'value');
				$sNotes = SafeGetArrayItem2Dim($aTV, $iTV, 'notes');
				$sNotes = (substr($sNotes, 0, 8) == 'Values: ') ? '' : $sNotes;
				if ($sValue === '<memo>') {
					$sValue = '';
				}
				$sName = htmlspecialchars($sName);
				$sValue = htmlspecialchars($sValue);
				$sNotes = htmlspecialchars($sNotes);
				$sOperationExInfo .= '<div class="operation-taggedvalue-data">' . $sName;
				if (!strIsEmpty($sValue)) {
					$sOperationExInfo .= '&nbsp;=&nbsp;' . $sValue;
				}
				$sOperationExInfo .= '</div>';
				if (!strIsEmpty($sNotes)) {
					$sOperationExInfo .= '<div class="operation-taggedvalue-notes">' . $sNotes . '</div>';
				}
				$sOperationExInfo .= '</div>';
			}
			$sOperationExInfo .= '</div>';
			$sOperationExInfo .= '</div>' . PHP_EOL;
		}
		if (!strIsEmpty($sOperationExInfo)) {
			$sSectionName = 'collapsible-' . $sGUID;
			echo '<div id="' . $sSectionName . '" onclick="OnToggleCollapsibleSection(this)" ';
			echo     ' class="collapsible-section-header collapsible-section-header-closed" >';
			echo '<img alt="" src="images/spriteplaceholder.png" class="collapsible-section-header-closed-icon">';
			echo $sOperationItemHdr;
			echo '</div>';
			echo '<div class="collapsible-section w3-hide">';
			echo $sOperationExInfo;
			echo '</div>';
		} else {
			echo '<div class="non-collapsible-guid">';
			echo $sOperationItemHdr;
			echo '</div>';
		}
		echo '</div>' . PHP_EOL;
	}
	echo '</div>';
	echo '</div>' . PHP_EOL;
}
function WriteSectionExternalData($a)
{
	echo '<div id="externaldata-section">';
	$iCnt = count($a);
	for ($i = 0; $i < $iCnt; $i++) {
		$sName			= SafeGetArrayItem2Dim($a, $i, 'name');
		$sGUID		 	= SafeGetArrayItem2Dim($a, $i, 'guid');
		$sNotes		 	= SafeGetArrayItem2Dim($a, $i, 'notes');
		$sValue		 	= SafeGetArrayItem2Dim($a, $i, 'value');
		$sExtSrcType 	= SafeGetArrayItem2Dim($a, $i, 'extsrctype');
		$sExtSrcUrl	 	= SafeGetArrayItem2Dim($a, $i, 'extsrcfullurl');
		echo '<div class="non-collapsible-guid">';
		echo '<table class="common-table" style="padding-bottom: 10px;padding-left: 30px;"><tbody>';
		if (!strIsEmpty($sName))
			echo '<tr><td class="common-label">Name:</td><td class="common-value">' . $sName . '</td></tr>';
		if (!strIsEmpty($sGUID))
			echo '<tr><td class="common-label">GUID</td><td class="common-value">' . $sGUID . '</td></tr>';
		if (!strIsEmpty($sNotes))
			echo '<tr><td class="common-label">Notes</td><td class="common-value">' . $sNotes . '</td></tr>';
		if (!strIsEmpty($sValue))
			echo '<tr><td class="common-label">Value</td><td class="common-value">' . $sValue . '</td></tr>';
		if (!strIsEmpty($sExtSrcType))
			echo '<tr><td class="common-label">Type</td><td class="common-value">' . $sExtSrcType . '</td></tr>';
		if (!strIsEmpty($sExtSrcUrl))
			echo '<tr><td class="common-label">URL</td><td class="common-value"><a href="' . $sExtSrcUrl . '">Link</a></td></tr>';
		echo '</tbody></table>';
		echo '</div>';
	}
	echo '</div>';
}
function WriteSectionTaggedValues($a, $bIsMini = false)
{
	if ($bIsMini) {
		$sSectionID = 'taggedvalue-mini-section';
		echo '<div id="' . $sSectionID . '" style="display:block">';
		echo '<div>';
	} else {
		$sSectionID = 'taggedvalue-section';
		echo '<div id="' . $sSectionID . '" style="' . GetSectionVisibility($sSectionID) . '">';
		echo '<div class="properties-header">Tagged Values</div>';
		echo '<div class="properties-content">';
	}
	$aTagGroups = array();
	$iCnt = count($a);
	for ($i = 0; $i < $iCnt; $i++) {
		$sGroup  = SafeGetArrayItem2Dim($a, $i, 'group');
		if ($sGroup == '') {
			WriteTaggedValue($a, $i);
		} else {
			if (!in_array($sGroup, $aTagGroups)) {
				$aTagGroups[] = $sGroup;
			}
		}
	}
	asort($aTagGroups);
	foreach ($aTagGroups as $sCurrentGroup) {
		$sTVGroupHdr  = '<div class="taggedvalue-group-name">';
		$sTVGroupHdr .= '<div class="propsprite-taggedvaluegroup"></div>';
		$sTVGroupHdr .= '<div class="taggedvalue-name">' . $sCurrentGroup . '</div>';
		$sTVGroupHdr .= '</div>';
		echo '<div class="taggedvalue-group-item property-section-item">';
		$sCollapsibleGroupID = 'collapsible-group-' . $sCurrentGroup;
		echo '<div id="' . $sCollapsibleGroupID . '" onclick="OnToggleCollapsibleSection(this)" ';
		echo     ' class="collapsible-section-header collapsible-section-header-opened">';
		echo '<img alt="" src="images/spriteplaceholder.png" class="collapsible-section-header-opened-icon">';
		echo $sTVGroupHdr;
		echo '</div>';
		echo '<div class="collapsible-section w3-show">';
		$iCnt = count($a);
		for ($i = 0; $i < $iCnt; $i++) {
			$sGroup  = SafeGetArrayItem2Dim($a, $i, 'group');
			if ($sGroup == $sCurrentGroup) {
				WriteTaggedValue($a, $i);
			}
		}
		echo '</div>';
		echo '</div>';
	}
	echo '</div>';
	echo '</div>';
}
function WriteTaggedValue($a, $i)
{
	echo '<div class="taggedvalue-item property-section-item">';
	$sName  = SafeGetArrayItem2Dim($a, $i, 'name');
	$sValue = SafeGetArrayItem2Dim($a, $i, 'value');
	$sNotes = SafeGetArrayItem2Dim($a, $i, 'notes');
	$sTagType = SafeGetArrayItem2Dim($a, $i, 'type');
	$sGUID  = SafeGetArrayItem2Dim($a, $i, 'guid');
	$aRefResource  = SafeGetArrayItem2Dim($a, $i, 'referredresource');
	if ($aRefResource !== '') {
		$sValue  = '';
		$refCount = count($aRefResource);
		$refName = '';
		$refGUID = '';
		$refType = '';
		$refResType = '';
		for ($i = 0; $i < $refCount; $i++) {
			$refName = 	SafeGetArrayItem2Dim($aRefResource, $i, 'name');
			$refGUID = SafeGetArrayItem2Dim($aRefResource, $i, 'guid');
			$refType = SafeGetArrayItem2Dim($aRefResource, $i, 'type');
			$refResType = SafeGetArrayItem2Dim($aRefResource, $i, 'restype');
			$refImageURL = SafeGetArrayItem2Dim($aRefResource, $i, 'imageurl');
			$sValue .= '<a class="w3-link" onclick="load_object(\'' . $refGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($refName) . '\',\'' . $refImageURL . '\')">';
			$sValue .= '<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($refImageURL) . '" style="float: none;">&nbsp;' . htmlspecialchars($refName);
			$sValue .= '</a>';
			if ($i !== $refCount - 1) {
				$sValue .= ', ';
			}
		}
	}
	$sColon = "";
	if ($sValue === '<memo>') {
		$sValue = '';
		if (substr($sNotes, 0, 11) === '<Checklist>') {
			$aTV = ConvertTVXMLToArray($sNotes);
			$iTVCnt = count($aTV);
			if ($iTVCnt > 0) {
				$sNotes  = '<table class="taggedvalue-notes-checklist"><tbody>';
				for ($iTV = 0; $iTV < $iTVCnt; $iTV++) {
					$sSubValue	= SafeGetArrayItem2Dim($aTV, $iTV, 'checked');
					if ($sSubValue === "True") {
						$sSubValue	= '<img alt="True" src="images/spriteplaceholder.png" class="propsprite-tick">';
					} else {
						$sSubValue	= '<img alt="False" src="images/spriteplaceholder.png" class="propsprite-untick">';
					}
					$sSubName	= SafeGetArrayItem2Dim($aTV, $iTV, 'text');
					$sNotes    .= WriteLabelValueProperty($sSubValue, $sSubName, 'checklist-tickbox', 'checklist-text', false);
				}
				$sNotes .= '</tbody></table>';
			} else {
				$sNotes = htmlspecialchars($sNotes);
			}
		} else if (substr($sNotes, 0, 12) === '<MatrixData>') {
			$sNotes = '<Matrix Configuration Data>';
			$sNotes = htmlspecialchars($sNotes);
		} else if (substr($sNotes, 0, 11) === '<modelview>') {
			$sNotes = '<Model View Configuration Data>';
			$sNotes = htmlspecialchars($sNotes);
		} else if (substr($sNotes, 0, 17) === '<DocumentOptions>') {
			$sNotes = '<Document Generation Data>';
			$sNotes = htmlspecialchars($sNotes);
		} else if (substr($sNotes, 0, 12) === '<chart type=') {
			$sNotes = '<Chart Configuration Data>';
			$sNotes = htmlspecialchars($sNotes);
		} else {
			$sNotes = htmlspecialchars($sNotes);
		}
	}
	if (substr($sNotes, 0, 8) === 'Values: ') {
		$sNotes = '';
	}
	if (substr($sNotes, 0, 14) === '<tagStructure>') {
		$sValue = '';
		$aTV = ConvertTVXMLToArray($sNotes);
		$iTVCnt = count($aTV);
		if ($iTVCnt > 0) {
			$sNotes  = '<table class="taggedvalue-notes-stv"><tbody>';
			for ($iTV = 0; $iTV < $iTVCnt; $iTV++) {
				$sSubName	= SafeGetArrayItem2Dim($aTV, $iTV, 'name');
				$sSubValue	= SafeGetArrayItem2Dim($aTV, $iTV, 'value');
				$sNotes    .= WriteLabelValueProperty($sSubName, $sSubValue, 'taggedvalue-label', 'taggedvalue-value', false);
			}
			$sNotes .= '</tbody></table>';
		} else {
			$sNotes = htmlspecialchars($sNotes);
			$sValue = htmlspecialchars($sValue);
		}
	}
	$sProtocol = '';
	$sSpecialType = '';
	$iPos = strpos($sValue, '://');
	if ($iPos !== false) {
		$sProtocol = substr($sValue, 0, $iPos);
	}
	$bIsURL = false;
	$iPos = strpos($sNotes, 'Type=');
	if ($iPos !== false) {
		$iPosEnd = strpos($sNotes, ';', $iPos + 5);
		if ($iPosEnd !== false) {
			$sSpecialType = mb_substr($sNotes, $iPos + 5, $iPosEnd - ($iPos + 5));
			if ($sSpecialType === 'URL') {
				$bIsURL = true;
			}
			$sNotes = mb_substr($sNotes, 0, $iPos) . mb_substr($sNotes, $iPosEnd + 1);
		}
	}
	if ($sTagType !== '' && $sTagType !== null) {
		$iPos = strpos($sTagType, 'Type=');
		$iPosEnd = strpos($sTagType, ';', $iPos + 5);
		$sSpecialType = mb_substr($sTagType, $iPos + 5, $iPosEnd - ($iPos + 5));
		if ($sSpecialType === 'URL') {
			$bIsURL = true;
		}
	}
	$sName = htmlspecialchars($sName);
	$sTVItemHdr  = '<div class="propsprite-taggedvaluesingle"></div>';
	$sTVItemHdr .= '<div class="taggedvalue-name">';
	$sTVItemHdr .= $sName;
	if ($bIsURL || ($sProtocol === 'http' || $sProtocol === 'https')) {
		$sPrefix = '';
		if ($sProtocol !== 'http' && $sProtocol !== 'https') {
			$sPrefix = '//';
		}
		if ($sValue !== '') {
			$sValue = '<a target="_blank" rel="noopener noreferrer" href="' . $sPrefix . $sValue . '">' . $sValue . '</a>';
		}
	}
	$sTVItemHdr .= WriteValueInSentence($sValue, '&nbsp;:&nbsp;', '', false);
	$sTVItemHdr .= '</div>';
	if (!strIsEmpty($sNotes)) {
		$sSectionName = 'collapsible-' . $sGUID;
		echo '<div id="' . $sSectionName . '" onclick="OnToggleCollapsibleSection(' . 'this' . ')" ';
		echo     	' class="collapsible-section-header collapsible-section-header-closed">';
		echo '  <img alt="" src="images/spriteplaceholder.png" class="collapsible-section-header-closed-icon">';
		echo $sTVItemHdr;
		echo '</div>';
		echo '<div class="collapsible-section w3-hide">';
		echo '  <div class="taggedvalue-notes">' . $sNotes . '</div>';
		echo '</div>';
	} else {
		echo '<div class="non-collapsible-guid">';
		echo $sTVItemHdr;
		echo '</div>';
	}
	echo '</div>' . PHP_EOL;
}
function WriteSectionReview($sObjectGUID, $sObjectName, $aTaggedValues, $aReviewDiscuss, $aReviewDiagrams, $aReviewNoDiscuss)
{
	$sReviewStatus	= GetTaggedValue($aTaggedValues, 'Status', 'EAReview::Status');
	$sReviewStartDate = GetTaggedValue($aTaggedValues, 'StartDate', 'EAReview::StartDate');
	$sReviewEndDate	= GetTaggedValue($aTaggedValues, 'EndDate', 'EAReview::EndDate');
	echo '<div id="review-section">';
	echo '<div class="properties-header">' . _glt('Review Summary') . '</div>';
	WriteSectionReviewSummary($aReviewDiscuss, $sReviewStatus, $sReviewStartDate, $sReviewEndDate, $aReviewDiagrams, $aReviewNoDiscuss);
	if (count($aReviewDiscuss) > 0 || count($aReviewNoDiscuss) > 0) {
		echo '<div class="properties-header" style="padding-top: 16px;">' . _glt('Objects in Review') . '</div>';
		WriteSectionReviewDiscussions($aReviewDiscuss, $aReviewNoDiscuss);
	}
	echo '</div>';
}
function WriteSectionRequirements($a)
{
	$sTableAttr = 'id="requirement-table" class="property-table"';
	$aHeader = ['Name', 'Type', 'Status', 'Difficulty', 'Priority'];
	$aFields = ['name', 'type', 'status', 'difficulty', 'priority'];
	$i = 0;
	foreach ($a as &$row) {
		$reqID = 'requirement_' . $i;
		$row['tr_attributes'] = 'onclick="load_prop_details(\'' . ConvertStringToParameter($reqID) . '\',\'\',\'\',\'props-requirement-details\')"';
		$i++;
	}
	echo '<div id="requirement-section" style="display:none">';
	echo '<div class="properties-header">Requirements</div>';
	WriteListTable($sTableAttr, $aHeader, $a, $aFields);
	echo '</div>';
	echo '<div id="requirement-details" class="property-details">';
	$i = 0;
	foreach ($a as $aReq) {
		$reqID = 'requirement_' . $i;
		echo '<div id="' . $reqID . '" style="display: none;">';
		echo '<div class="properties-header"><a style="color: #3777bf;" title="' . _glt('Return to list') . '"  href="javascript:show_section(\'requirements\')">' . 'Requirements' . '</a>';
		echo '<img alt="" src="images/spriteplaceholder.png" class="propsprite-separator">';
		echo htmlspecialchars($aReq['name']) . '</div>';
		echo '<div class="property-details-container">';
		WritePropertyField('Name', $aReq['name'], '', 'style="width: 300px;"');
		echo '<div class="prop-row">';
		echo '<div class="prop-column">';
		WritePropertyField('Type', $aReq['type']);
		WritePropertyField('Status', $aReq['status']);
		echo '</div>';
		echo '<div class="prop-column">';
		WritePropertyField('Difficulty', $aReq['difficulty']);
		WritePropertyField('Priority', $aReq['priority']);
		echo '</div>';
		echo '<div class="prop-column">';
		WritePropertyField('Stability', $aReq['stability']);
		echo '</div>';
		echo '</div>';
		echo '<div class="prop-row">';
		echo '<div class="prop-column">';
		WritePropertyField('Modified', $aReq['modified'], '', 'style="width:150px;"');
		echo '</div>';
		echo '</div>';
		WritePropertyNoteField('Description', $aReq['notes']);
		echo '</div>';
		echo '</div>';
		$i++;
	}
	echo '</div>';
}
function WriteSectionConstraints($a)
{
	echo '<div id="constraint-section" style="display:none">';
	echo '<div class="properties-header">Constraints</div>';
	echo '<div class="properties-content">';
	$iCnt = count($a);
	for ($i = 0; $i < $iCnt; $i++) {
		$sName = SafeGetArrayItem2Dim($a, $i, 'name');
		$sNotes = SafeGetArrayItem2Dim($a, $i, 'notes');
		$sName = htmlspecialchars($sName);
		echo '<div class="constraint-item">';
		echo '<div class="constraint-name">';
		echo '<img alt="" src="images/spriteplaceholder.png" class="constraint-icon">';
		echo '<span class="constraint-type">' . $a[$i]['type'] . '.</span>&nbsp;';
		echo '<span class="constraint-name-text">' . $sName . '</span>&nbsp;';
		echo '</div>';
		if (!strIsEmpty($sNotes)) {
			echo '<div class="constraint-desc">';
			echo '<span class="constraint-notes">' . $sNotes . '</span>';
			echo '</div>';
		}
		echo '<div class="constraint-status">[&nbsp;' . $a[$i]['status'] . '.&nbsp;]</div>';
		echo '</div>';
	}
	echo '</div>';
	echo '</div>' . PHP_EOL;
}
function WriteSectionScenarios($a)
{
	echo '<div id="scenario-section" style="display:none">';;
	echo '<div class="properties-header">Scenarios</div>';
	echo '<div class="properties-content">';
	$iCnt = count($a);
	for ($i = 0; $i < $iCnt; $i++) {
		$sType = SafeGetArrayItem2Dim($a, $i, 'type');
		$sName = SafeGetArrayItem2Dim($a, $i, 'name');
		$sName = htmlspecialchars($sName);
		echo '<div class="scenario-item">';
		echo '<img alt="" src="images/spriteplaceholder.png" class="scenario-icon">';
		if (!strIsEmpty($sType) && !strIsEmpty($sName)) {
			echo '<div class="scenario-name">' . $sType . '&nbsp;.&nbsp' . $sName . '</div>';
		} elseif (strIsEmpty($sType) && !strIsEmpty($sName)) {
			echo '<div class="scenario-name">' . $sName . '</div>';
		}
		echo '<div class="scenario-desc">';
		$sNotes = SafeGetArrayItem2Dim($a, $i, 'notes');
		if (!strIsEmpty($sNotes)) {
			echo '<span class="scenario-notes">' . $sNotes . '</span>';
		}
		if (array_key_exists(("steps"), $a[$i])) {
			$aSteps = $a[$i]['steps'];
			$iStepsCnt = count($aSteps);
			for ($in = 0; $in < $iStepsCnt; $in++) {
				$sStepName = SafeGetArrayItem2Dim($aSteps, $in, 'stepname');
				$sLevel = SafeGetArrayItem2Dim($aSteps, $in, 'level');
				$sUses = SafeGetArrayItem2Dim($aSteps, $in, 'uses');
				$sStepName = htmlspecialchars($sStepName);
				$sUses = htmlspecialchars($sUses);
				echo '<div class="scenario-step">&nbsp;' . $sLevel . '.' . '&nbsp;' . $sStepName . '</div>';
				if (!strIsEmpty($sUses)) {
					echo '<div class="scenario-step-uses">';
					echo '<span class="scenario-step-uses-label">Uses:</span><span id="scenario-step-uses-text">&nbsp;' . $sUses . '</span>';
					echo '</div>';
				}
			}
		}
		echo '</div>';
		echo '</div>';
	}
	echo '</div>';
	echo '</div>' . PHP_EOL;
}
function WriteTableInputRow($sLabel, $sField)
{
	echo '<tr>';
	echo '<td>';
	echo $sLabel;
	echo '</td>';
	echo '<td>';
	echo $sField;
	echo '</td>';
	echo '</tr>';
}
function WriteSectionReviewSummary($a, $sReviewStatus, $sReviewStartDate, $sReviewEndDate, $aDiagrams, $aNoDiscuss)
{
	$iElementNotDiscussCnt = 0;
	$iDiscussTopicCnt 	= 0;
	$iPriorLowCnt		= 0;
	$iPriorMediumCnt	= 0;
	$iPriorHighCnt 		= 0;
	$iPriorNoneCnt		= 0;
	$iStatusOpenCnt 	= 0;
	$iStatusAwaitCnt 	= 0;
	$iStatusClosedCnt 	= 0;
	$iReviewDiagramCnt 	= 0;
	echo '<div id="reviewsummary-section" class="properties-content">';
	$iElementCnt 	= count($a);
	for ($iEle = 0; $iEle < $iElementCnt; $iEle++) {
		if (array_key_exists(("discussions"), $a[$iEle])) {
			$aDiscussions = $a[$iEle]['discussions'];
			$iDiscussCnt = count($aDiscussions);
			$iDiscussTopicCnt += $iDiscussCnt;
			for ($iD = 0; $iD < $iDiscussCnt; $iD++) {
				$sPriority		= mb_strtolower(SafeGetArrayItem2Dim($aDiscussions, $iD, 'priority'));
				$sStatus 		= mb_strtolower(SafeGetArrayItem2Dim($aDiscussions, $iD, 'status'));
				if ($sPriority === 'low')
					$iPriorLowCnt += 1;
				elseif ($sPriority === 'medium')
					$iPriorMediumCnt += 1;
				elseif ($sPriority === 'high')
					$iPriorHighCnt += 1;
				else
					$iPriorNoneCnt += 1;
				if ($sStatus === 'open')
					$iStatusOpenCnt += 1;
				elseif ($sStatus === 'awaiting review')
					$iStatusAwaitCnt += 1;
				elseif ($sStatus === 'closed')
					$iStatusClosedCnt += 1;
			}
		}
	}
	echo '<div class="reviewsummary-line"><div class="reviewsummary-lvl1-col1">' . _glt('Review Status') . '</div><div class="reviewsummary-lvl1-col2">' . $sReviewStatus . '</div></div>';
	echo '<div class="reviewsummary-line"><div class="reviewsummary-lvl1-col1">' . _glt('Start') . '</div><div class="reviewsummary-lvl1-col2">' . $sReviewStartDate . '</div></div>';
	echo '<div class="reviewsummary-line"><div class="reviewsummary-lvl1-col1">' . _glt('End') . '</div><div class="reviewsummary-lvl1-col2">' . $sReviewEndDate . '</div></div>';
	$iElementNotDiscussCnt = count($aNoDiscuss);
	echo '<div class="reviewsummary-line"><div class="reviewsummary-lvl1-col1">' . _glt('Elements discussed') . '</div><div class="reviewsummary-lvl1-col2">' . $iElementCnt . '</div></div>';
	echo '<div class="reviewsummary-line"><div class="reviewsummary-lvl1-col1">' . _glt('Elements not discussed') . '</div><div class="reviewsummary-lvl1-col2">' . $iElementNotDiscussCnt . '</div></div>';
	echo '<div class="reviewsummary-line"><div class="reviewsummary-lvl1-col1">' . _glt('Discussion Topics') . '</div><div class="reviewsummary-lvl1-col2">' . $iDiscussTopicCnt . '</div></div>';
	echo '<div class="reviewsummary-line-lvl2-hdr">' . _glt('Priority') . '</div>';
	echo '<div class="reviewsummary-line-lvl2"><div class="reviewsummary-lvl2-col1"><img class="reviewsummary-lvl2-img propsprite-discusspriorityhigh" alt="" src="images/spriteplaceholder.png">&nbsp;' . _glt('High') . '</div><div class="reviewsummary-lvl2-col2">' . $iPriorHighCnt . '</div></div>';
	echo '<div class="reviewsummary-line-lvl2"><div class="reviewsummary-lvl2-col1"><img class="reviewsummary-lvl2-img propsprite-discussprioritymed" alt="" src="images/spriteplaceholder.png">&nbsp;' . _glt('Medium') . '</div><div class="reviewsummary-lvl2-col2">' . $iPriorMediumCnt . '</div></div>';
	echo '<div class="reviewsummary-line-lvl2"><div class="reviewsummary-lvl2-col1"><img class="reviewsummary-lvl2-img propsprite-discussprioritylow" alt="" src="images/spriteplaceholder.png">&nbsp;' . _glt('Low') . '</div><div class="reviewsummary-lvl2-col2">' . $iPriorLowCnt . '</div></div>';
	echo '<div class="reviewsummary-line-lvl2"><div class="reviewsummary-lvl2-col1"><img class="reviewsummary-lvl2-img propsprite-discussprioritynone" alt="" src="images/spriteplaceholder.png"">&nbsp;' . _glt('<none>') . '</div><div class="reviewsummary-lvl2-col2">' . $iPriorNoneCnt . '</div></div>';
	echo '<div class="reviewsummary-line-lvl2-hdr">' . _glt('Status') . '</div>';
	echo '<div class="reviewsummary-line-lvl2"><div class="reviewsummary-lvl2-col1"><img class="reviewsummary-lvl2-img propsprite-discussstatusopen" alt="" src="images/spriteplaceholder.png">&nbsp;' . _glt('Open') . '</div><div class="reviewsummary-lvl2-col2">' . $iStatusOpenCnt . '</div></div>';
	echo '<div class="reviewsummary-line-lvl2"><div class="reviewsummary-lvl2-col1"><img class="reviewsummary-lvl2-img propsprite-discussstatusawait" alt="" src="images/spriteplaceholder.png">&nbsp;' . _glt('Awaiting Review') . '</div><div class="reviewsummary-lvl2-col2">' . $iStatusAwaitCnt . '</div></div>';
	echo '<div class="reviewsummary-line-lvl2"><div class="reviewsummary-lvl2-col1"><img class="reviewsummary-lvl2-img propsprite-discussstatuscomplete" alt="" src="images/spriteplaceholder.png">&nbsp;' . _glt('Closed') . '</div><div class="reviewsummary-lvl2-col2">' . $iStatusClosedCnt . '</div></div>';
	echo '</div>' . PHP_EOL;
	$iReviewDiagramCnt = count($aDiagrams);
	echo '<div class="properties-header" style="padding-top: 16px;">' . _glt('Review Diagrams') . '</div>';
	echo '<div id="reviewdiagram-section" class="properties-content">';
	if ($iReviewDiagramCnt > 0) {
		$sName 		= '';
		$sGUID 		= '';
		$sImageURL	= '';
		for ($i = 0; $i < $iReviewDiagramCnt; $i++) {
			$sName = SafeGetArrayItem2Dim($aDiagrams, $i, 'name');
			$sGUID = SafeGetArrayItem2Dim($aDiagrams, $i, 'guid');
			$sImageURL = SafeGetArrayItem2Dim($aDiagrams, $i, 'imageurl');
			echo '<div class="reviewsummary-line w3-link"><div onclick="load_object(\'' . $sGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sName) . '\',\'' . $sImageURL . '\')">';
			echo '<img alt="" title="Diagram" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sImageURL) . '">&nbsp;' . htmlspecialchars($sName) . '</div></div>';
		}
	}
	echo '</div>' . PHP_EOL;
}
function WriteSectionReviewDiscussions($a, $aNoDiscuss)
{
	echo '<div id="reviewdiscussion-section" class="properties-content">';
	$iElementCnt = count($aNoDiscuss);
	for ($iEle = 0; $iEle < $iElementCnt; $iEle++) {
		$sObjName 	= SafeGetArrayItem2Dim($aNoDiscuss, $iEle, 'name');
		$sObjType 	= SafeGetArrayItem2Dim($aNoDiscuss, $iEle, 'type');
		$sObjResType = SafeGetArrayItem2Dim($aNoDiscuss, $iEle, 'restype');
		$sObjGUID 	= SafeGetArrayItem2Dim($aNoDiscuss, $iEle, 'guid');
		$sObjStereo = SafeGetArrayItem2Dim($aNoDiscuss, $iEle, 'stereotype');
		$sObjImageURL = SafeGetArrayItem2Dim($aNoDiscuss, $iEle, 'imageurl');
		$sObjName 	= GetPlainDisplayName($sObjName);
		$sObjName = htmlspecialchars($sObjName);
		echo '<div class="reviewdiscussion-item">';
		if (!strIsEmpty($sObjType)) {
			echo '<div id=reviewdiscussion-element-link class="w3-link" onclick="load_object(\'' . $sObjGUID . '\',\'false\',\'props\',\'\',\'' . ConvertStringToParameter($sObjName) . '\',\'' . $sObjImageURL . '\')">';
			echo '<img class="mainprop-object-image ' . GetObjectImageSpriteName($sObjImageURL) . '" src="images/spriteplaceholder.png" alt="">&nbsp;' . $sObjName;
			echo '</div>';
		}
		echo '</div>';
	}
	$iElementCnt = count($a);
	for ($iEle = 0; $iEle < $iElementCnt; $iEle++) {
		$sObjName 	= SafeGetArrayItem2Dim($a, $iEle, 'name');
		$sObjType 	= SafeGetArrayItem2Dim($a, $iEle, 'type');
		$sObjResType = SafeGetArrayItem2Dim($a, $iEle, 'restype');
		$sObjGUID 	= SafeGetArrayItem2Dim($a, $iEle, 'guid');
		$sObjStereo = SafeGetArrayItem2Dim($a, $iEle, 'stereotype');
		$sObjImageURL = SafeGetArrayItem2Dim($a, $iEle, 'imageurl');
		$sObjName = htmlspecialchars($sObjName);
		echo '<div class="reviewdiscussion-item">';
		if (!strIsEmpty($sObjType) && !strIsEmpty($sObjName)) {
			echo '<div id=reviewdiscussion-element-link class="w3-link" onclick="load_object(\'' . $sObjGUID . '\',\'false\',\'props\',\'\',\'' . ConvertStringToParameter($sObjName) . '\',\'' . $sObjImageURL . '\')">';
			echo '<img class="mainprop-object-image ' . GetObjectImageSpriteName($sObjImageURL) . '" src="images/spriteplaceholder.png" alt="">&nbsp;' . $sObjName;
			echo '</div>';
			if (array_key_exists(("discussions"), $a[$iEle])) {
				echo '<div class="reviewdiscussion-item-topics">';
				$aDiscussions = $a[$iEle]['discussions'];
				$iDiscussCnt = count($aDiscussions);
				for ($iD = 0; $iD < $iDiscussCnt; $iD++) {
					$sDiscussText 	= SafeGetArrayItem2Dim($aDiscussions, $iD, 'discussion');
					$sDiscussGUID 	= SafeGetArrayItem2Dim($aDiscussions, $iD, 'guid');
					$sPriority		= SafeGetArrayItem2Dim($aDiscussions, $iD, 'priority');
					$sPriorityImageClass = SafeGetArrayItem2Dim($aDiscussions, $iD, 'priorityimageclass');
					$sPriorityTooltip = str_replace('%PRIORITY%', $sPriority, _glt('Priority: xx'));
					$sStatus 		= SafeGetArrayItem2Dim($aDiscussions, $iD, 'status');
					$sStatusImageClass = SafeGetArrayItem2Dim($aDiscussions, $iD, 'statusimageclass');
					$sStatusTooltip = str_replace('%STATUS%', $sStatus, _glt('Status: xx'));
					$aReplies = null;
					$iReplyCnt = 0;
					if (array_key_exists(("replies"), $aDiscussions[$iD])) {
						$aReplies = $aDiscussions[$iD]['replies'];
						$iReplyCnt = count($aReplies);
					}
					if ($iReplyCnt > 0) {
						echo '<div class="reviewdiscussion-discussion-item collapsible-section-header-closed">';
						echo '<img alt="" src="images/spriteplaceholder.png" class="reviewdiscussion-discussion-item-icon collapsible-section-header-closed-icon show-cursor-pointer" onclick="OnToggleReviewDiscussionReplies(\'' . $sDiscussGUID . '\')">';
					} else {
						echo '<div class="reviewdiscussion-discussion-item">';
					}
					WriteAvatarImage($aDiscussions[$iD]['avatarid'], 'false');
					echo '<div class="reviewdiscussion-discussion-item-states" >';
					echo '<span id="review-prioritymenu-button"><img alt="" src="images/spriteplaceholder.png" class="' . $sPriorityImageClass . '" title="' . $sPriorityTooltip . '" height="16" width="16">&nbsp;</span>';
					echo '<span id="review-statusmenu-button"><img alt="" src="images/spriteplaceholder.png" class="' . $sStatusImageClass . '" title="' . $sStatusTooltip . '" height="16" width="16">&nbsp;</span>';
					echo '</div>';
					echo '<div class="reviewdiscussion-discussion-item-text' . ($iReplyCnt > 0 ? ' show-cursor-pointer' : '') . '" onclick="OnToggleReviewDiscussionReplies(\'' . $sDiscussGUID . '\')">' . $sDiscussText . '</div>';
					echo '<div class="reviewdiscussion-discussion-item-text-footer">';
					echo '<div class="reviewdiscussion-discussion-item-text-footer-dateauthor">' . $aDiscussions[$iD]['created'] . '&nbsp; &nbsp;' . $aDiscussions[$iD]['author'] . '</div>';
					echo '</div>';
					if ($iReplyCnt > 0) {
						echo '<div class="reviewdiscussion-discussion-item-replies" id="mpreplies_' . $sDiscussGUID . '">';
						for ($iR = 0; $iR < $iReplyCnt; $iR++) {
							$sReplyAuthor 	= SafeGetArrayItem2Dim($aReplies, $iR, 'replyauthor');
							$sReplyCreated 	= SafeGetArrayItem2Dim($aReplies, $iR, 'replycreated');
							$sReplyText 	= SafeGetArrayItem2Dim($aReplies, $iR, 'replytext');
							$sReplyAvatarID 	= SafeGetArrayItem2Dim($aReplies, $iR, 'replyavatarid');
							$sReplyAvatarImage 	= SafeGetArrayItem2Dim($aReplies, $iR, 'replyavatarimage');
							echo '<div class="reviewdiscussion-discussion-item-reply">';
							WriteAvatarImage($sReplyAvatarID, 'true');
							echo '<div class="reviewdiscussion-discussion-item-reply-text">' . $sReplyText . '</div>';
							echo '<div class="reviewdiscussion-discussion-item-reply-text-footer">';
							echo '<div class="reviewdiscussion-discussion-item-reply-text-footer-dateauthor">' . $sReplyCreated . '&nbsp; &nbsp;' . $sReplyAuthor . '</div>';
							echo '</div>';
							echo '</div>';
						}
						echo '</div>';
					}
					echo '</div>';
				}
				echo '</div>';
			}
		}
		echo '</div>';
		if ($iEle < $iElementCnt - 1) {
			echo '<hr class="reviewdiscussion-hr">';
		}
	}
	echo '</div>' . PHP_EOL;
}
function ConvertTVXMLToArray($sXML)
{
	$aTV = array();
	$xmlDoc = new DOMDocument();
	$validXML = SafeXMLLoad($xmlDoc, $sXML);
	if ($xmlDoc !== null && $validXML) {
		$xnRoot = $xmlDoc->documentElement;
		if ($xnRoot->nodeName === 'tagStructure') {
			foreach ($xnRoot->childNodes as $xnProp) {
				$aRow		= array();
				GetXMLNodeValueAttr($xnProp, 'property', 'name', $aRow['name']);
				GetXMLNodeValue($xnProp, 'property', $aRow['value']);
				$aTV[] 		= $aRow;
			}
		} else if ($xnRoot->nodeName === 'Checklist') {
			foreach ($xnRoot->childNodes as $xnProp) {
				$aRow		= array();
				GetXMLNodeValueAttr($xnProp, 'Item', 'Text', $aRow['text']);
				GetXMLNodeValueAttr($xnProp, 'Item', 'Checked', $aRow['checked']);
				$aTV[] 		= $aRow;
			}
		}
	}
	return $aTV;
}
function WriteSectionTest($a, $sObjectGUID, $bObjectLocked, $sObjectName, $sObjectImageURL, $sLinkType, $sObjectHyper, $bIsMini = false)
{
	if ($bIsMini) {
		echo '<div id="test-mini-section" style="display:block">';
		$iCnt = count($a);
		for ($i = 0; $i < $iCnt; $i++) {
			$sTestItemExInfo = '';
			$sType 		= SafeGetArrayItem2Dim($a, $i, 'type');
			$sClassType = SafeGetArrayItem2Dim($a, $i, 'classtype');
			$sName	 	= SafeGetArrayItem2Dim($a, $i, 'name');
			$sGUID	 	= SafeGetArrayItem2Dim($a, $i, 'guid');
			$sStatus 	= SafeGetArrayItem2Dim($a, $i, 'status');
			$sLrun 		= SafeGetArrayItem2Dim($a, $i, 'lastrun');
			$sRunBy 	= SafeGetArrayItem2Dim($a, $i, 'runby');
			$sChkdBy 	= SafeGetArrayItem2Dim($a, $i, 'checkedby');
			echo '<div class="test-item property-section-item">';
			if (!$bObjectLocked) {
				if (IsSessionSettingTrue('login_perm_test')) {
					if (IsSessionSettingTrue('edit_objectfeature_tests')) {
						echo '<div class="test-item-edit">';
						echo '<input class="test-item-edit-button" type="button" value="&#160;" onclick="load_object(\'editelementtest\',\'false\',\'' . $sObjectGUID . '|' . ConvertStringToParameter($sName) . '\',\'' . $sClassType . '\',\'' . ConvertStringToParameter($sObjectName) . '\',\'' . $sObjectImageURL . '\')" />';
						echo '</div>';
					}
				}
			}
			$sTestItemHdr  = '<img alt="" src="images/spriteplaceholder.png" class="test-item-icon">';
			$sTestItemHdr .= '<div class="test-name">';
			$s = '';
			if (!strIsEmpty($sType))
				$s .= $sType . ' ';
			if (!strIsEmpty($sClassType))
				$s .= '<span class="test-classtype">' . $sClassType . '</span> ';
			if (!strIsEmpty($s)) {
				$s = substr($s, 0, strlen($s) - 1);
				$s .= '.  ';
			}
			$sTestItemHdr .= $s . $sName;
			$sTestItemHdr .= '</div>';
			$sTestItemHdr .= '<div class="test-runstate">';
			if ($sStatus === 'Not Run') {
				$sTestItemHdr .= '<img alt="" src="images/spriteplaceholder.png" class="test-runstate-notrun"><span style="padding-left: 10px;">' . _glt('Not run yet') . '</span>';
			} else {
				$sStatusClassName = mb_strtolower($sStatus);
				if ((stripos(',deferred,fail,not run yet,pass,cancelled,', ',' . $sStatusClassName . ',') !== false))
					$sStatusClassName = 'test-runstate-' . $sStatusClassName;
				else
					$sStatusClassName = 'test-runstate-userdef';
				$sTestItemHdr .= '<div class="test-runstate-status"><img alt="" src="images/spriteplaceholder.png" class="' . $sStatusClassName . '"><span style="padding-left: 10px;">' . $sStatus . '</span></div>';
			}
			$sTestItemHdr .= '</div>';
			if (!strIsEmpty($sLrun) && !strIsEmpty($sRunBy)) {
				$sTestItemExInfo .= '<span class="test-status-label"> [ ' . _glt('Last Run at') . '</span>';
				$sTestItemExInfo .= '<span class="test-status-value">' . $sLrun . '</span>';
				$sTestItemExInfo .= '<span class="test-status-label"> ' . _glt('by') . ' </span><span class="test-status-value">' . htmlspecialchars($sRunBy) . '</span>';
				if (!strIsEmpty($sChkdBy)) {
					$sTestItemExInfo .= '<span class="test-status-label">' . _glt('and checked by') . ' </span><span class="test-status-value">' . htmlspecialchars($sChkdBy) . '</span>';
				}
				$sTestItemExInfo .= '<span class="test-status-label"> ]</span>';
			}
			$sDesc = SafeGetArrayItem2Dim($a, $i, 'notes');
			$sInput = SafeGetArrayItem2Dim($a, $i, 'input');
			$sAccptCr = SafeGetArrayItem2Dim($a, $i, 'acceptance');
			$sResults = SafeGetArrayItem2Dim($a, $i, 'results');
			if (!strIsEmpty($sDesc)) {
				$sTestItemExInfo .= '<div class="testblock-desc-label">' . _glt('Description') . ':</div><div class="testblock-desc">' . $sDesc . '</div>';
			}
			if (!strIsEmpty($sInput)) {
				$sTestItemExInfo .= '<div class="testblock-input-label">' . _glt('Input') . ':</div><div class="testblock-input">' . $sInput . '</div>';
			}
			if (!strIsEmpty($sAccptCr)) {
				$sTestItemExInfo .= '<div class="testblock-acceptance-label">' . _glt('Acceptance Criteria') . ':</div><div class="testblock-acceptance">' . $sAccptCr . '</div>';
			}
			if (!strIsEmpty($sResults)) {
				$sTestItemExInfo .= '<div class="testblock-results-label">' . _glt('Results') . ':</div><div class="testblock-results">' . $sResults . '</div>';
			}
			if (!strIsEmpty($sTestItemExInfo)) {
				$sSectionName = 'collapsible-' . $sGUID;
				echo '<div id="' . $sSectionName . '" onclick="OnToggleCollapsibleSection(this)" ';
				echo     ' class="collapsible-section-header collapsible-section-header-closed">';
				echo '<img alt="" src="images/spriteplaceholder.png" class="collapsible-section-header-closed-icon">';
				echo $sTestItemHdr . '</div>';
				echo '<div class="collapsible-section w3-hide">';
				echo '<div class="test-ex-info" >';
				echo $sTestItemExInfo;
				echo '</div>';
				echo '</div>';
			} else {
				echo '<div class="non-collapsible-guid">';
				echo $sTestItemHdr;
				echo '</div>';
			}
			echo '</div>' . PHP_EOL;
		}
		echo '</div>' . PHP_EOL;
	} else {
		if ($sLinkType === 'props-tests') {
			echo '<div id="test-section" style="display:block;">';
		} else {
			echo '<div id="test-section" style="display:none;">';
		}
		$sTableAttr = 'id="test-table" class="property-table"';
		$i = 0;
		foreach ($a as &$row) {
			if ($row['status'] === 'Not Run') {
				$row['statusicon'] = '&nbsp&nbsp&nbsp<img alt="" src="images/spriteplaceholder.png" class="test-runstate-notrun" title="' . $row['status'] . '">';
			} else {
				$sStatusClassName = mb_strtolower($row['status']);
				if ((stripos(',deferred,fail,not run yet,pass,cancelled,', ',' . $sStatusClassName . ',') !== false))
					$sStatusClassName = 'test-runstate-' . $sStatusClassName;
				else
					$sStatusClassName = 'test-runstate-userdef';
				$row['statusicon'] = '&nbsp&nbsp&nbsp<img alt="" src="images/spriteplaceholder.png" class="' . $sStatusClassName . '" title="' . $row['status'] . '">';
			}
			$testID = 'test_' . $i;
			$row['tr_attributes'] = 'onclick="load_prop_details(\'' . ConvertStringToParameter($testID) . '\',\'' . $sObjectGUID . '\',\'\',\'props-test-details\')"';
			$i++;
		}
		$aHeader = ['Test', 'Status', 'Class', 'Type', 'Last Run'];
		$aFields = ['name', 'statusicon', 'classtype', 'type', 'lastrun'];
		echo '<div class="properties-header">Tests</div>';
		WriteListTable($sTableAttr, $aHeader, $a, $aFields);
		echo '</div>';
		echo '<div id="test-details" class="property-details">';
		$i = 0;
		foreach ($a as $aTest) {
			$testID = 'test_' . $i;
			if (($sLinkType = 'props-test-details') && ($sObjectHyper === $testID))
				echo '<div id="' . $testID . '" style="display: block;">';
			else
				echo '<div id="' . $testID . '" style="display: none;">';
			echo '<div class="test-item-edit">';
			echo '<input class="test-item-edit-button" value="&nbsp;" onclick="load_object(\'editelementtest\',\'false\',\'' . $sObjectGUID . '|' . $aTest['name'] . '\',\'' . $aTest['classtype'] . '\',\'' . $sObjectName . '\',\'' . $sObjectImageURL . '\')" type="button">';
			echo '</div>';
			echo '<div class="properties-header"><a style="color: #3777bf;" title="' . _glt('Return to list') . '"  href="javascript:show_section(\'tests\')">' . ucfirst($aHeader[0]) . 's</a>';
			echo '<img alt="" src="images/spriteplaceholder.png" class="propsprite-separator">';
			echo $aTest['name'] . '</div>';
			echo '<div class="property-details-container">';
			WritePropertyField('Name', $aTest['name'], '', 'style="width: 300px;"');
			echo '<div class="prop-row">';
			echo '<div class="prop-column">';
			WritePropertyField('Class', $aTest['classtype']);
			WritePropertyField('Type', $aTest['type']);
			echo '</div>';
			echo '<div class="prop-column">';
			WritePropertyField('Status', $aTest['status']);
			WritePropertyField('Last Run', $aTest['lastrun']);
			echo '</div>';
			echo '<div class="prop-column">';
			WritePropertyField('Run By', $aTest['runby']);
			WritePropertyField('Checked By', $aTest['checkedby']);
			echo '</div>';
			echo '</div>';
			WritePropertyNoteField('Description', $aTest['notes']);
			WritePropertyNoteField('Input', $aTest['input']);
			WritePropertyNoteField('Acceptance Criteria', $aTest['acceptance']);
			WritePropertyNoteField('Results', $aTest['results']);
			echo '</div>';
			echo '</div>';
			$i++;
		}
		echo '</div>';
	}
}
function WritePropertyNoteField($sLabel, $sValue, $sLabelAttr = '', $sInputAttr = '')
{
	echo '<div class="prop-field">';
	echo '<div class="prop-label-textarea">';
	echo $sLabel . ':';
	echo '</div>';
	echo '<div class="prop-field-textarea">';
	echo $sValue;
	echo '</div>';
	echo '</div>';
}
function WritePropertyField($sLabel, $sValue, $sLabelAttr = '', $sInputAttr = '')
{
	echo '<div class="prop-field">';
	echo '<div class="prop-label" ' . $sLabelAttr . '>';
	echo $sLabel . ':';
	echo '</div>';
	echo '<div class="prop-text">';
	echo '<div ' . $sInputAttr . ' class="prop-text-field">' . htmlspecialchars($sValue) . '</div>';
	echo '</div>';
	echo '</div>';
}
function WriteListTable($sTableAttr, $aHeader, $aData, $aFields)
{
	echo '<table ' . $sTableAttr . '>';
	echo '<tbody>';
	echo '<tr>';
	foreach ($aHeader as $header) {
		echo '<th>' . _glt($header) . '</th>';
	}
	echo '</tr>';
	foreach ($aData as $aRow) {
		echo '<tr ' . SafeGetArrayItem1Dim($aRow, 'tr_attributes') . '>';
		foreach ($aFields as $field) {
			$sFieldContent = SafeGetArrayItem1Dim($aRow, $field);
			if ($field != 'statusicon')
				$sFieldContent = htmlspecialchars($sFieldContent);
			echo '<td>' . $sFieldContent . '</td>';
		}
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';
}
function WriteSectionResourceAllocs($a, $sObjectGUID, $bObjectLocked, $sObjectName, $sObjectImageURL, $sLinkType, $sObjectHyper, $bIsMini = false)
{
	if ($bIsMini) {
		echo '<div id="resource-mini-section" style="display:block">';
		$iCnt = count($a);
		for ($i = 0; $i < $iCnt; $i++) {
			$sResource 	= SafeGetArrayItem2Dim($a, $i, 'resource');
			$sRole 		= SafeGetArrayItem2Dim($a, $i, 'role');
			$sGUID 		= SafeGetArrayItem2Dim($a, $i, 'guid');
			echo '<div class="resource-item property-section-item property-section-item">';
			if (!$bObjectLocked) {
				if (IsSessionSettingTrue('login_perm_resalloc')) {
					if (IsSessionSettingTrue('edit_objectfeature_resources')) {
						echo '<div class="resource-item-edit">';
						echo '<input class="resource-item-edit-button" type="button" value="&#160;" onclick="load_object(\'editelementresalloc\',\'false\',\'' .  $sObjectGUID . '|' . ConvertStringToParameter($sResource) . '\',\'' . ConvertStringToParameter($sRole) . '\',\'' . ConvertStringToParameter($sObjectName) . '\',\'' . $sObjectImageURL . '\')" />';
						echo '</div>';
					}
				}
			}
			$sResourceItemHdr  = '<img alt="" src="images/spriteplaceholder.png" class="resource-item-icon">';
			$sResourceItemHdr .= '<div class="resources-name">';
			$sResourceItemHdr .= htmlspecialchars($sResource) . '&nbsp;-&nbsp;' . htmlspecialchars($sRole) . '</div>';
			$sStartDt =  SafeGetArrayItem2Dim($a, $i, 'sdate');
			$sEndDt =  SafeGetArrayItem2Dim($a, $i, 'edate');
			if (!strIsEmpty($sStartDt) || !strIsEmpty($sEndDt)) {
				$sResourceItemHdr .= '<div class="resources-dates1">';
				$sResourceItemHdr .= '<div class="resources-date-line">';
				if (!strIsEmpty($sStartDt)) {
					$sResourceItemHdr .= '<span class="resources-date-value">' . $sStartDt . '&nbsp;</span>';
				}
				if (!strIsEmpty($sEndDt)) {
					$sResourceItemHdr .= '<span class="resources-date-label2">' . _glt('until') . '</span><span class="resources-date-value">' . $sEndDt . '</span>';
				}
				$sResourceItemHdr .= '</div>';
				$sResourceItemHdr .= '</div>';
			}
			$sPercentage = SafeGetArrayItem2Dim($a, $i, 'percentage');
			$sPercentage = Trim($sPercentage, '.');
			if (!strIsEmpty($sPercentage)) {
				$sResourceItemHdr .= '<div class="resources-dates2">';
				$sResourceItemHdr .= '<div class="resources-date-line">';
				$sResourceItemHdr .= '<span class="resources-date-value">' . $sPercentage . '&#37;&nbsp;</span><span class="resources-date-label">' . _glt('completed') . '</span>';
				$sResourceItemHdr .= '</div>';
				$sResourceItemHdr .= '</div>';
			}
			$sNotes 	= SafeGetArrayItem2Dim($a, $i, 'notes');
			$sHistory 	= SafeGetArrayItem2Dim($a, $i, 'history');
			$sAllocated = SafeGetArrayItem2Dim($a, $i, 'atime');
			$sExpected 	= SafeGetArrayItem2Dim($a, $i, 'exptime');
			$sExpended 	= SafeGetArrayItem2Dim($a, $i, 'expendtime');
			if (
				!strIsEmpty($sNotes) || !strIsEmpty($sHistory) || (!strIsEmpty($sAllocated) && $sAllocated !== '0') ||
				(!strIsEmpty($sExpected) && $sExpected !== "0") || (!strIsEmpty($sExpended) && $sExpended !== "0")
			) {
				$sSectionName = 'collapsible-' . $sGUID;
				echo '<div id="' . $sSectionName . '" onclick="OnToggleCollapsibleSection(this)" ';
				echo     ' class="collapsible-section-header collapsible-section-header-closed">';
				echo '<img alt="" src="images/spriteplaceholder.png" class="collapsible-section-header-closed-icon">';
				echo $sResourceItemHdr;
				echo '</div>';
				echo '<div class="collapsible-section w3-hide">';
				if (!strIsEmpty($sNotes)) {
					echo '<div class="resources-notes">' . $sNotes . '</div>';
				}
				if (!strIsEmpty($sHistory)) {
					echo '<div class="resources-history-header">History: </div><div class="resources-history">' . $sHistory . '</div>';
				}
				if ((!strIsEmpty($sAllocated) && $sAllocated !== "0") ||
					(!strIsEmpty($sExpected) && $sExpected !== "0") ||
					(!strIsEmpty($sExpended) && $sExpended != "0")
				) {
					echo '<div class="resources-times-div"><div class="resources-times">';
					echo '<span class="resources-time-label"> (' . _glt('Actual') . ':</span><span class="resources-time-value">' . $sExpended . '</span>';
					echo '<span class="resources-time-label"> ' . _glt('Expected') . ':</span><span class="resources-time-value">' . $sExpected . '</span>';
					echo '<span class="resources-time-label"> ' . _glt('Time') . ':</span><span class="resources-time-value">' . $sAllocated . '</span><span class="resources-time-label"> )</span>';
					echo '</div></div>';
				}
				echo '</div>';
			} else {
				echo '<div class="non-collapsible-guid">';
				echo $sResourceItemHdr;
				echo '</div>';
			}
			echo '</div>' . PHP_EOL;
		}
		echo '</div>' . PHP_EOL;
	} else {
		$propType = 'resource';
		$aHeader = ['Resource', 'Task', '% Complete', 'Start', 'End'];
		$aFields = ['resource', 'role', 'percentage', 'sdate', 'edate'];
		$sTableAttr = 'id="' . $propType . '-table" class="property-table"';
		$sPropDetailsID = 'props-' . $propType . '-details';
		$i = 0;
		foreach ($a as &$row) {
			$propID = $propType . '_' . $i;
			$row['tr_attributes'] = 'onclick="load_prop_details(\'' . ConvertStringToParameter($propID) . '\',\'' . $sObjectGUID . '\',\'\',\'' . $sPropDetailsID . '\')"';
			$i++;
		}
		$sSectionID = 'resource-section';
		echo '<div id="' . $sSectionID . '" style="' . GetSectionVisibility($sSectionID) . '">';
		echo '<div class="properties-header">Resources</div>';
		WriteListTable($sTableAttr, $aHeader, $a, $aFields);
		echo '</div>' . PHP_EOL;
		echo '<div id="' . $propType . '-details" class="property-details">';
		$i = 0;
		foreach ($a as $aItem) {
			$sHeaderLabel = ucfirst($propType) . 's</a><img alt="" src="images/spriteplaceholder.png" class="propsprite-separator">' . $aItem['resource'];
			$sRole = SafeGetArrayItem1Dim($aItem, 'role');
			if ($sRole !== '') {
				$sHeaderLabel .= ' - ' . htmlspecialchars($sRole);
			}
			$propID = $propType . '_' . $i;
			if (($sLinkType = $sPropDetailsID) && ($sObjectHyper === $propID))
				echo '<div id="' . $propID . '" style="display: block;">';
			else
				echo '<div id="' . $propID . '" style="display: none;">';
			echo '<div class="' . $propType . '-item-edit">';
			echo '<input class="' . $propType . '-item-edit-button" value="&nbsp;" onclick="load_object(\'editelementresalloc\',\'false\',\'' . $sObjectGUID . '|' . $aItem['resource'] . '\',\'' . $aItem['role'] . '\',\'' . $sObjectName . '\',\'' . $sObjectImageURL . '\')" type="button">';
			echo '</div>';
			echo '<div class="properties-header"><a style="color: #3777bf;" title="' . _glt('Return to list') . '" href="javascript:show_section(\'' . $propType . 's\')">' . $sHeaderLabel . '</div>';
			echo '<div class="property-details-container">';
			echo '<div class="prop-row">';
			echo '<div class="prop-column">';
			WritePropertyField('Resource', $aItem['resource']);
			WritePropertyField('Role', $aItem['role']);
			echo '</div>';
			echo '<div class="prop-column">';
			WritePropertyField('Start date', $aItem['sdate']);
			WritePropertyField('End date', $aItem['edate']);
			echo '</div>';
			echo '<div class="prop-column">';
			WritePropertyField('% Complete', $aItem['percentage']);
			echo '</div>';
			echo '</div>';
			echo '<div class="prop-row">';
			echo '<div class="prop-column" style="width: 320px;">';
			WritePropertyField('Expected time', $aItem['exptime']);
			WritePropertyField('Allocated time', $aItem['atime']);
			WritePropertyField('Time expended', $aItem['expendtime']);
			echo '</div>';
			echo '</div>';
			WritePropertyNoteField('Description', $aItem['notes']);
			WritePropertyNoteField('History', $aItem['history']);
			echo '</div>';
			echo '</div>';
			$i++;
		}
		echo '</div>';
	}
}
function WriteSectionRunStates($a)
{
	echo '<div id="runstate-section" style="display:none">';
	echo '<div class="properties-header">Run States</div>';
	echo '<div class="properties-content">';
	$iCnt = count($a);
	for ($i = 0; $i < $iCnt; $i++) {
		$sName 			= SafeGetArrayItem2Dim($a, $i, 'name');
		$sGUID 			= SafeGetArrayItem2Dim($a, $i, 'guid');
		$sValue 		= SafeGetArrayItem2Dim($a, $i, 'value');
		$sOperator 		= SafeGetArrayItem2Dim($a, $i, 'operator');
		$sOperator 		= str_replace('<![CDATA[', '', $sOperator);
		$sOperator 		= str_replace("]]>", "", $sOperator);
		$sName			= htmlspecialchars($sName);
		$sValue			= htmlspecialchars($sValue);
		$sOperator 		= htmlspecialchars($sOperator);
		echo '<div class="runstate-item property-section-item">';
		$sRunStateItemHdr  = '<div class="runstate-item-hdr">';
		$sRunStateItemHdr .= '<img alt="" src="images/spriteplaceholder.png" class="runstate-item-icon">';
		$sRunStateItemHdr .= '<div class="runstate-name">' . $sName;
		$sRunStateItemHdr .= WriteValueInSentence($sOperator, '&nbsp;', '&nbsp;', false);
		$sRunStateItemHdr .= WriteValueInSentence($sValue, '&nbsp;', '', false);
		$sRunStateItemHdr .= '</div>';
		$sRunStateItemHdr .= '</div>';
		$sNotes 		= SafeGetArrayItem2Dim($a, $i, 'notes');
		$sRunStateExInfo = '';
		if (!strIsEmpty($sNotes)) {
			$sRunStateExInfo .= '<div class="runstate-notes">' . $sNotes . '</div>';
		}
		if (!strIsEmpty($sRunStateExInfo)) {
			$sSectionName = 'collapsible-' . $sGUID;
			echo '<div id="' . $sSectionName . '" onclick="OnToggleCollapsibleSection(this)" ';
			echo     ' class="collapsible-section-header collapsible-section-header-closed">';
			echo '<img alt="" src="images/spriteplaceholder.png" class="collapsible-section-header-closed-icon">';
			echo $sRunStateItemHdr;
			echo '</div>';
			echo '<div class="collapsible-section w3-hide">';
			echo $sRunStateExInfo;
			echo '</div>';
		} else {
			echo '<div class="non-collapsible-guid">';
			echo $sRunStateItemHdr;
			echo '</div>';
		}
		echo '</div>' . PHP_EOL;
	}
	echo '</div>';
	echo '</div>' . PHP_EOL;
}
function WriteSectionLocation($sInternalName, $sSectionName, $sDefault, $aP, $a, $bUseAlternativeColour = false, $bIsMini = false)
{
	if (empty($aP) && count($a) <= 0) {
		return;
	}
	if ($bIsMini) {
		$sSectionID = 'location-mini-section';
		echo '<div id="' . $sSectionID . '" style="display:block">';
		echo '<div class="miniprops-header">Location</div>';
		echo '<div class="miniprops-content">';
	} else {
		$sSectionID = 'location-section';
		echo '<div id="' . $sSectionID . '" style="padding-top: 6px; ' . GetSectionVisibility($sSectionID) . '">';
		echo '<div class="properties-header">Location</div>';
		echo '<div class="properties-content">';
	}
	if (empty($aP) === false) {
		$sName 		= SafeGetArrayItem1Dim($aP, 'text');
		$sName 		= GetPlainDisplayName($sName);
		$sGUID 		= SafeGetArrayItem1Dim($aP, 'guid');
		$sResType 	= SafeGetArrayItem1Dim($aP, 'restype');
		$sImageURL	= SafeGetArrayItem1Dim($aP, 'imageurl');
		if (strIsEmpty($sGUID) && $sName === _glt('<Unnamed object>')) {
			SafeStartSession();
			$sName = isset($_SESSION['model_name']) ? htmlspecialchars($_SESSION['model_name']) : 'Home';
			$sImageURL = 'home.png';
		}
		echo '<div id="parent-section">';
		echo '<div class="parent-desc w3-link" onclick="load_object(\'' . $sGUID . '\',\'true\',\'\',\'\',\'' . ConvertStringToParameter($sName) . '\',\'' . $sImageURL . '\')">';
		echo '<img alt="" title="' . $sResType . '" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sImageURL) . '"> ' . htmlspecialchars($sName);
		echo '</div>';
		echo '</div>' . PHP_EOL;
	}
	if (empty($a) === false) {
		$aD = $a['diagrams'];
		echo '<div id="usage-section">';
		$iCnt = count($aD);
		if ($iCnt > 0) {
			$sName 		= '';
			$sGUID 		= '';
			$sImageURL	= '';
			echo '<table class="usage-table">';
			for ($i = 0; $i < $iCnt; $i++) {
				$sName = SafeGetArrayItem2Dim($aD, $i, 'name');
				$sGUID = SafeGetArrayItem2Dim($aD, $i, 'guid');
				$sImageURL = SafeGetArrayItem2Dim($aD, $i, 'imageurl');
				$aClassifierUsage = SafeGetArrayItem2Dim($aD, $i, 'classifierusage');
				echo '<tr><td class="usage-name w3-link" onclick="load_object(\'' . $sGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sName) . '\',\'' . $sImageURL . '\')" >';
				echo '<img alt="" title="Diagram" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sImageURL) . '"> ' . htmlspecialchars($sName);
				echo '</td></tr>' . PHP_EOL;
			}
			echo '</table>';
		}
		echo '</div>' . PHP_EOL;
	}
	echo '</div>';
	if ((array_key_exists('instances', $a)) && (count($a['instances']) > 0)) {
		$aI = $a['instances'];
		if ($bIsMini) {
			echo '<div class="miniprops-header" style="padding-top:0px;">Instances</div>';
			echo '<div class="miniprops-content">';
		} else {
			echo '<div class="properties-header">Instances</div>';
			echo '<div class="properties-content">';
		}
		if (empty($a) === false) {
			$iCnt = count($aI);
			if ($iCnt > 0) {
				$sName 		= '';
				$sGUID 		= '';
				$sImageURL	= '';
				echo '<table class="usage-table">';
				for ($i = 0; $i < $iCnt; $i++) {
					$sName = SafeGetArrayItem2Dim($aI, $i, 'name');
					$sGUID = SafeGetArrayItem2Dim($aI, $i, 'guid');
					$sImageURL = SafeGetArrayItem2Dim($aI, $i, 'imageurl');
					$aClassifierUsage = SafeGetArrayItem2Dim($aI, $i, 'classifierusage');
					echo '<tr><td class="usage-name w3-link" onclick="load_object(\'' . $sGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sName) . '\',\'' . $sImageURL . '\')" >';
					echo '<img alt="" title="Diagram" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sImageURL) . '"> ' . htmlspecialchars($sName);
					echo '</td></tr>' . PHP_EOL;
					foreach ($aClassifierUsage as $aClassifierDiagram) {
						echo '<tr><td class="instance-diagram-name w3-link" onclick="load_object(\'' . $aClassifierDiagram['guid'] . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($aClassifierDiagram['name']) . '\',\'' . $aClassifierDiagram['imageurl'] . '\')">';
						echo '<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($aClassifierDiagram['imageurl']) . '"> ' . htmlspecialchars($aClassifierDiagram['name']);
						echo '</td></tr>' . PHP_EOL;
					}
				}
				echo '</table>';
			}
		}
		echo '</div>';
	}
	echo '</div>';
}
function WriteSectionRelationships($sInternalName, $sSectionName, $sDefault, $a, $bIsMini = false)
{
	if (count($a) <= 0) {
		return;
	}
	if ($bIsMini) {
		$sSectionID = 'relationship-mini-section';
		echo '<div id="' . $sSectionID . '" style="display:block">';
		echo '<div class="miniprops-content">';
		$iCnt = count($a);
		if ($iCnt > 0) {
			$iOutCnt = 0;
			$iInCnt = 0;
			for ($i = 0; $i < $iCnt; $i++) {
				$sLinkDirection	= SafeGetArrayItem2Dim($a, $i, 'linkdirection');
				if ($sLinkDirection	=== 'Outgoing') {
					$iOutCnt = $iOutCnt + 1;
				}
				if ($sLinkDirection	=== 'Incoming') {
					$iInCnt = $iInCnt + 1;
				}
			}
			if ($iOutCnt > 0) {
				echo '<div class="relationship-outgoing">';
				for ($i = 0; $i < $iCnt; $i++) {
					$sLinkDirection	= SafeGetArrayItem2Dim($a, $i, 'linkdirection');
					if ($sLinkDirection	=== 'Outgoing') {
						$sConnectorName = SafeGetArrayItem2Dim($a, $i, 'connectorname');
						$sConnectorType = SafeGetArrayItem2Dim($a, $i, 'connectortype');
						$sConnectorName2 = $sConnectorType . ' connector ' . $sConnectorName;
						$sConnectorGUID = SafeGetArrayItem2Dim($a, $i, 'connectorguid');
						$sConnectorImageURL = SafeGetArrayItem2Dim($a, $i, 'connectorimageurl');
						$sElementName 	= SafeGetArrayItem2Dim($a, $i, 'elementname');
						$sElementName 	= GetPlainDisplayName($sElementName);
						$sElementGUID 	= SafeGetArrayItem2Dim($a, $i, 'elementguid');
						$sElementImageURL = SafeGetArrayItem2Dim($a, $i, 'elementimageurl');
						$sDirection 	= SafeGetArrayItem2Dim($a, $i, 'direction');
						echo '<div class="relationship-item">';
						echo '<img alt="" src="images/spriteplaceholder.png" class="relationship-item-out">';
						echo '<a class="w3-link" onclick="load_object(\'' . $sConnectorGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sConnectorName2) . '\',\'' . $sConnectorImageURL . '\')">';
						echo $sConnectorType . '</a>';
						echo '&nbsp;' . _glt('to') . '&nbsp;';
						echo '<a class="w3-link" onclick="load_object(\'' . $sElementGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sElementName) . '\',\'' . $sElementImageURL . '\')">';
						echo '<img src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sElementImageURL) . '" alt="">&nbsp;';
						echo htmlspecialchars($sElementName);
						echo '</a>';
						echo '</div>' . PHP_EOL;
					}
				}
				echo '</div>';
			}
			if ($iInCnt > 0) {
				echo '<div class="relationship-incoming">';
				for ($i = 0; $i < $iCnt; $i++) {
					$sLinkDirection	= SafeGetArrayItem2Dim($a, $i, 'linkdirection');
					if ($sLinkDirection	=== 'Incoming') {
						$sConnectorName = SafeGetArrayItem2Dim($a, $i, 'connectorname');
						$sConnectorType = SafeGetArrayItem2Dim($a, $i, 'connectortype');
						$sConnectorName2 = $sConnectorType . ' connector ' . $sConnectorName;
						$sConnectorGUID = SafeGetArrayItem2Dim($a, $i, 'connectorguid');
						$sConnectorImageURL = SafeGetArrayItem2Dim($a, $i, 'connectorimageurl');
						$sElementName 	= SafeGetArrayItem2Dim($a, $i, 'elementname');
						$sElementName 	= GetPlainDisplayName($sElementName);
						$sElementGUID 	= SafeGetArrayItem2Dim($a, $i, 'elementguid');
						$sElementImageURL = SafeGetArrayItem2Dim($a, $i, 'elementimageurl');
						$sDirection 	= SafeGetArrayItem2Dim($a, $i, 'direction');
						echo '<div class="relationship-item">';
						echo '<img alt="" src="images/spriteplaceholder.png" class="relationship-item-in">';
						echo '<a class="w3-link" onclick="load_object(\'' . $sConnectorGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sConnectorName2) . '\',\'' . $sConnectorImageURL . '\')">';
						echo $sConnectorType . '</a>';
						echo '&nbsp;' . _glt('from') . '&nbsp;';
						echo '<a class="w3-link" onclick="load_object(\'' . $sElementGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sElementName) . '\',\'' . $sElementImageURL . '\')">';
						echo '<img src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sElementImageURL) . '" alt="">&nbsp;';
						echo htmlspecialchars($sElementName);
						echo '</a>';
						echo '</div>' . PHP_EOL;
					}
				}
				echo '</div>';
			}
			echo '</div>' . PHP_EOL;
		}
		echo '</div>';
	} else {
		$sSectionID = 'relationship-section';
		echo '<div id="' . $sSectionID . '" style="' . GetSectionVisibility($sSectionID) . '">';
		echo '<div class="properties-header">' . _glt('Relationships') . '</div>';
		$sTableAttr = 'id="relationship-table" class="property-table"';
		$aHeader = ['Type', 'Direction', 'Target'];
		echo '<table ' . $sTableAttr . '>';
		echo '<tbody>';
		echo '<tr>';
		foreach ($aHeader as $header) {
			echo '<th>' . $header . '</th>';
		}
		echo '</tr>';
		foreach ($a as $aRow) {
			$sLinkDirection	= SafeGetArrayItem1Dim($aRow, 'linkdirection');
			$sConnectorName = SafeGetArrayItem1Dim($aRow, 'connectorname');
			$sConnectorType = SafeGetArrayItem1Dim($aRow, 'connectortype');
			$sConnectorName2 = $sConnectorType . ' connector ' . $sConnectorName;
			$sConnectorGUID = SafeGetArrayItem1Dim($aRow, 'connectorguid');
			$sConnectorImageURL = SafeGetArrayItem1Dim($aRow, 'connectorimageurl');
			$sElementName 	= SafeGetArrayItem1Dim($aRow, 'elementname');
			$sElementName 	= GetPlainDisplayName($sElementName);
			$sElementGUID 	= SafeGetArrayItem1Dim($aRow, 'elementguid');
			$sElementImageURL = SafeGetArrayItem1Dim($aRow, 'elementimageurl');
			$sDirection 	= SafeGetArrayItem1Dim($aRow, 'direction');
			if ($sLinkDirection === 'Outgoing')
				$sLinkImgClass = 'relationship-item-out';
			else
				$sLinkImgClass = 'relationship-item-in';
			$sConnectorType = '<a>' . $sConnectorType . '</a>';
			$sConnectorDirection = '<img alt="" src="images/spriteplaceholder.png" class="' . $sLinkImgClass . '">' . $sLinkDirection;
			$sTargetName = '<a class="w3-link" >';
			$sTargetName .=  '<img src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sElementImageURL) . '" alt="">&nbsp;';
			$sTargetName .=  htmlspecialchars($sElementName);
			$sTargetName .=  '</a>';
			echo '<tr>';
			echo '<td class="w3-link" onclick="load_object(\'' . $sConnectorGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sConnectorName2) . '\',\'' . $sConnectorImageURL . '\')">' . $sConnectorType . '</td>';
			echo '<td class="relationship-direction">' . $sConnectorDirection . '</td>';
			echo '<td class="w3-link"  onclick="load_object(\'' . $sElementGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sElementName) . '\',\'' . $sElementImageURL . '\')">' . $sTargetName . '</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
		echo '</div>';
	}
}
function WriteSectionChangeManagement1($a, $sChgMgmtType, $sObjectGUID, $bObjectLocked, $sObjectName, $sObjectImageURL, $sLinkType, $sObjectHyper, $bIsMini = false)
{
	if ($bIsMini) {
		$sDate1FieldName = 'requestedon';
		$sDate2FieldName = 'completedon';
		$sPerson1FieldName = 'requestedby';
		$sPerson2FieldName = 'completedby';
		$sPerson1Desc = _glt('Requested by');
		$sPerson2Desc = _glt('Completed by');
		if ($sChgMgmtType === 'defect' || $sChgMgmtType === 'event') {
			$sDate1FieldName = 'reportedon';
			$sDate2FieldName = 'resolvedon';
			$sPerson1FieldName = 'reportedby';
			$sPerson2FieldName = 'resolvedby';
			$sPerson1Desc = _glt('Reported by');
			$sPerson2Desc = _glt('Resolved by');
		} elseif ($sChgMgmtType === 'issue') {
			$sDate1FieldName = 'raisedon';
			$sDate2FieldName = 'completedon';
			$sPerson1FieldName = 'raisedby';
			$sPerson2FieldName = 'completedby';
			$sPerson1Desc = _glt('Raised by');
			$sPerson2Desc = _glt('Completed by');
		} elseif ($sChgMgmtType === 'decision') {
			$sDate1FieldName = 'date';
			$sDate2FieldName = 'effective';
			$sPerson1FieldName = 'owner';
			$sPerson2FieldName = 'author';
			$sPerson1Desc = _glt('Owner');
			$sPerson2Desc = _glt('Author');
		}
		echo '<div id="' . $sChgMgmtType . '-mini-section">';
		$iCnt = count($a);
		for ($i = 0; $i < $iCnt; $i++) {
			$sName	 	= SafeGetArrayItem2Dim($a, $i, 'name');
			$sStatus	= SafeGetArrayItem2Dim($a, $i, 'status');
			$sPriority	= SafeGetArrayItem2Dim($a, $i, 'priority');
			$sGUID 		= SafeGetArrayItem2Dim($a, $i, 'guid');
			$sName		= htmlspecialchars($sName);
			echo '<div class="' . $sChgMgmtType . '-item property-section-item">';
			$sItemHdr  	= '<img alt="" src="images/spriteplaceholder.png" class="' . $sChgMgmtType . '-item-icon">';
			$sItemHdr  .= '<div class="chgmgmt-name">';
			if (!strIsEmpty($sStatus))
				$sItemHdr .= $sStatus . ', ';
			if (!strIsEmpty($sPriority))
				$sItemHdr .= $sPriority . ' ' . _glt('priority') . '.  ';
			$sItemHdr  .=  $sName . ' </div>';
			$sDate1 	=  SafeGetArrayItem2Dim($a, $i, $sDate1FieldName);
			$sDate2 	=  SafeGetArrayItem2Dim($a, $i, $sDate2FieldName);
			if (!strIsEmpty($sDate1) || !strIsEmpty($sDate2)) {
				$sItemHdr .= '<div class="chgmgmt-dates1">';
				$sItemHdr .= '<div class="chgmgmt-date-line">';
				if (!strIsEmpty($sDate1)) {
					$sItemHdr .= '<span class="chgmgmt-date-value">' . $sDate1 . '&nbsp;</span>';
				}
				if (!strIsEmpty($sDate2)) {
					$sItemHdr .= '<span class="chgmgmt-date-label2">' . _glt('until') . '</span><span class="chgmgmt-date-value">' . $sDate2 . '</span>';
				}
				$sItemHdr .= '</div>';
				$sItemHdr .= '</div>';
			}
			$sNotes 	= SafeGetArrayItem2Dim($a, $i, 'notes');
			$sHistory 	= SafeGetArrayItem2Dim($a, $i, 'history');
			$sPerson1 	=  SafeGetArrayItem2Dim($a, $i, $sPerson1FieldName);
			$sPerson2 	=  SafeGetArrayItem2Dim($a, $i, $sPerson2FieldName);
			$sVersion 	=  SafeGetArrayItem2Dim($a, $i, 'version');
			$sPerson1	= htmlspecialchars($sPerson1);
			$sPerson2	= htmlspecialchars($sPerson2);
			$sVersion	= htmlspecialchars($sVersion);
			if (!strIsEmpty($sNotes) || !strIsEmpty($sHistory) || !strIsEmpty($sPerson1) || !strIsEmpty($sPerson2) || !strIsEmpty($sVersion)) {
				$sSectionName = 'collapsible-' . $sGUID;
				echo '<div id="' . $sSectionName . '" onclick="OnToggleCollapsibleSection(this)" ';
				echo     ' class="collapsible-section-header collapsible-section-header-closed">';
				echo '<img alt="" src="images/spriteplaceholder.png" class="collapsible-section-header-closed-icon">';
				echo $sItemHdr;
				echo '</div>';
				echo '<div class="collapsible-section w3-hide">';
				if (!strIsEmpty($sNotes)) {
					echo '<div class="chgmgmt-notes">' . $sNotes . '</div>';
				}
				if (!strIsEmpty($sHistory)) {
					echo '<div class="chgmgmt-history-header">' . _glt('History') . ': </div><div class="chgmgmt-history">' . $sHistory . '</div>';
				}
				if (!strIsEmpty($sPerson1) || !strIsEmpty($sPerson2) || !strIsEmpty($sVersion)) {
					echo '<div class="chgmgmt-times-div"><div class="chgmgmt-times">';
					echo '<span class="chgmgmt-time-label"> [ </span>';
					if (!strIsEmpty($sPerson1))
						echo '<span class="chgmgmt-time-label"> ' . $sPerson1Desc . ' </span><span class="chgmgmt-time-value"> ' . $sPerson1 . '.</span>&nbsp;';
					if (!strIsEmpty($sPerson2))
						echo '<span class="chgmgmt-time-label"> ' . $sPerson2Desc . ' </span><span class="chgmgmt-time-value"> ' . $sPerson2 . '.</span>&nbsp;';
					if (!strIsEmpty($sVersion))
						echo '<span class="chgmgmt-time-label"> ' . _glt('Version') . ':</span><span class="chgmgmt-time-value"> ' . $sVersion . '</span>&nbsp;';
					echo '<span class="chgmgmt-time-label">]</span>';
					echo '</div></div>';
				}
				echo '</div>';
			} else {
				echo '<div class="non-collapsible-guid">';
				echo $sItemHdr;
				echo '</div>';
			}
			echo '</div>' . PHP_EOL;
		}
		echo '</div>' . PHP_EOL;
	} else {
		$propType = $sChgMgmtType;
		$sTableAttr = 'id="' . $propType . '-table" class="property-table"';
		$sPropDetailsID = 'props-' . $propType . '-details';
		if ($sLinkType === 'props-' . $sChgMgmtType . 's') {
			echo '<div id="' . $sChgMgmtType . '-section" style="display:block">';
		} else {
			echo '<div id="' . $sChgMgmtType . '-section" style="display:none">';
		}
		$sTableAttr = 'id="' . $sChgMgmtType . '-table" class="property-table"';
		if (($sChgMgmtType === 'change') || ($sChgMgmtType === 'task')) {
			$aHeader = [ucfirst($sChgMgmtType), 'Status', 'Priority', 'Requested On', 'Requested By'];
			$aFields = ['name', 'status', 'priority', 'requestedon', 'requestedby'];
		} elseif (($sChgMgmtType === 'defect') || ($sChgMgmtType === 'event')) {
			$aHeader = [ucfirst($sChgMgmtType), 'Status', 'Priority', 'Reported On', 'Reported By'];
			$aFields = ['name', 'status', 'priority', 'reportedon', 'reportedby'];
		} elseif (($sChgMgmtType === 'issue')) {
			$aHeader = [ucfirst($sChgMgmtType), 'Status', 'Priority', 'Raised On', 'Raised By'];
			$aFields = ['name', 'status', 'priority', 'raisedon', 'raisedby'];
		} elseif ($sChgMgmtType === 'decision') {
			$aHeader = [ucfirst($sChgMgmtType), 'Status', 'Impact', 'Date', 'Author'];
			$aFields = ['name', 'status', 'impact', 'date', 'author'];
		}
		$i = 0;
		foreach ($a as &$row) {
			$propID = $propType . '_' . $i;
			$row['tr_attributes'] = 'onclick="load_prop_details(\'' . ConvertStringToParameter($propID) . '\',\'' . $sObjectGUID . '\',\'\',\'' . $sPropDetailsID . '\')"';
			$i++;
		}
		echo '<div class="properties-header">' . ucfirst($propType) . 's</div>';
		WriteListTable($sTableAttr, $aHeader, $a, $aFields);
		echo '</div>' . PHP_EOL;
		echo '<div id="' . $propType . '-details" class="property-details">';
		$i = 0;
		foreach ($a as $aItem) {
			$sHeaderLabel = ucfirst($propType) . 's</a><img alt="" src="images/spriteplaceholder.png" class="propsprite-separator">' . $aItem['name'];
			$propID = $propType . '_' . $i;
			$sPriorityDesc = 'Priority';
			$sPriorityFieldName = 'priority';
			$sDate1Desc = 'Requested on';
			$sDate1FieldName = 'requestedon';
			$sDate2Desc = 'Completed on';
			$sDate2FieldName = 'completedon';
			$sPerson1Desc = _glt('Requested by');
			$sPerson1FieldName = 'requestedby';
			$sPerson2Desc = _glt('Completed by');
			$sPerson2FieldName = 'completedby';
			$sVersionDesc = 'Version';
			$sVersionFieldName = 'version';
			if ($propType === 'defect' || $propType === 'event') {
				$sDate1Desc = 'Reported on';
				$sDate1FieldName = 'reportedon';
				$sDate2Desc = 'Resolved on';
				$sDate2FieldName = 'resolvedon';
				$sPerson1Desc = _glt('Reported by');
				$sPerson1FieldName = 'reportedby';
				$sPerson2Desc = _glt('Resolved by');
				$sPerson2FieldName = 'resolvedby';
			} elseif ($propType === 'issue') {
				$sDate1Desc = _glt('Raised on');
				$sDate1FieldName = 'raisedon';
				$sDate2Desc = _glt('Completed on');
				$sDate2FieldName = 'completedon';
				$sPerson1Desc = _glt('Raised by');
				$sPerson1FieldName = 'raisedby';
				$sPerson2Desc = _glt('Completed by');
				$sPerson2FieldName = 'completedby';
			} elseif ($propType === 'decision') {
				$sPriorityDesc = _glt('Impact');
				$sPriorityFieldName = 'impact';
				$sDate1Desc = _glt('Date');
				$sDate1FieldName = 'date';
				$sDate2Desc = _glt('Effective');
				$sDate2FieldName = 'effective';
				$sPerson1Desc = _glt('Owner');
				$sPerson1FieldName = 'owner';
				$sPerson2Desc = _glt('Author');
				$sPerson2FieldName = 'author';
				$sVersionDesc = 'Version / ID';
			}
			if (($sLinkType = $sPropDetailsID) && ($sObjectHyper === $propID))
				echo '<div id="' . $propID . '" style="display: block;">';
			else
				echo '<div id="' . $propID . '" style="display: none;">';
			echo '<div class="properties-header"><a style="color: #3777bf;" title="' . _glt('Return to list') . '" href="javascript:show_section(\'' . $propType . 's\')">' . $sHeaderLabel . '</div>';
			echo '<div class="property-details-container">';
			WritePropertyField('Name', $aItem['name'], '', 'style="width: 240px;"');
			echo '<div class="prop-row">';
			echo '<div class="prop-column">';
			WritePropertyField('Status', $aItem['status']);
			WritePropertyField($sPriorityDesc, $aItem[$sPriorityFieldName]);
			echo '</div>';
			echo '<div class="prop-column">';
			WritePropertyField($sPerson1Desc, $aItem[$sPerson1FieldName]);
			WritePropertyField($sDate1Desc, $aItem[$sDate1FieldName]);
			echo '</div>';
			echo '<div class="prop-column">';
			WritePropertyField($sPerson2Desc, $aItem[$sPerson2FieldName]);
			WritePropertyField($sDate2Desc, $aItem[$sDate2FieldName]);
			echo '</div>';
			echo '</div>';
			echo '<div class="prop-row">';
			WritePropertyField($sVersionDesc, $aItem['version']);
			echo '</div>';
			WritePropertyNoteField('Description', $aItem['notes']);
			WritePropertyNoteField('History', $aItem['history']);
			echo '</div>';
			echo '</div>';
			$i++;
		}
		echo '</div>';
	}
}
function WriteSectionChangeManagement2($a, $sChgMgmtType, $sObjectGUID, $bObjectLocked, $sObjectName, $sObjectImageURL, $sLinkType, $sObjectHyper)
{
	$propType = $sChgMgmtType;
	$sTableAttr = 'id="' . $propType . '-table" class="property-table"';
	$sPropDetailsID = 'props-' . $propType . '-details';
	$sSectionID = $sChgMgmtType . '-section';
	$sHeader = ucfirst($sChgMgmtType) . 's';
	echo '<div id="' . $sSectionID . '" style="' . GetSectionVisibility($sSectionID) . '">';
	echo '<div class="properties-header">' . $sHeader . '</div>';
	echo '<div class="properties-content">';
	$iCnt = count($a);
	for ($i = 0; $i < $iCnt; $i++) {
		$sName	 	= SafeGetArrayItem2Dim($a, $i, 'name');
		$sNotes		= SafeGetArrayItem2Dim($a, $i, 'notes');
		$sType		= SafeGetArrayItem2Dim($a, $i, 'type');
		$sWeight	= SafeGetArrayItem2Dim($a, $i, 'weight');
		$sGUID 		= SafeGetArrayItem2Dim($a, $i, 'guid');
		$sName	= htmlspecialchars($sName);
		echo '<div class="' . $sChgMgmtType . '-item property-section-item">';
		$sItemHdr  	= '<img alt="" src="images/spriteplaceholder.png" class="' . $sChgMgmtType . '-item-icon">';
		$sItemHdr  .= '<div class="chgmgmt-name">';
		if (!strIsEmpty($sType))
			$sItemHdr .= $sType . ' ' . $sChgMgmtType . '.  ';
		$sItemHdr  .=  $sName . ' </div>';
		if (!strIsEmpty($sNotes) || (!strIsEmpty($sWeight) && $sWeight !== "0")) {
			$sSectionName = 'collapsible-' . $sGUID;
			echo '<div id="' . $sSectionName . '" onclick="OnToggleCollapsibleSection(this)" ';
			echo     ' class="collapsible-section-header collapsible-section-header-closed">';
			echo '<img alt="" src="images/spriteplaceholder.png" class="collapsible-section-header-closed-icon">';
			echo $sItemHdr;
			echo '</div>';
			echo '<div class="collapsible-section w3-hide">';
			if (!strIsEmpty($sNotes)) {
				echo '<div class="chgmgmt-notes">' . $sNotes . '</div>';
			}
			if (!strIsEmpty($sWeight) && $sWeight !== "0") {
				echo '<div class="chgmgmt-times-div"><div class="chgmgmt-times">';
				echo '<span class="chgmgmt-time-label"> [ </span>';
				echo '<span class="chgmgmt-time-label"> Weight is </span><span class="chgmgmt-time-value"> ' . number_format($sWeight, 2, '.', '') . '</span>&nbsp;';
				echo '<span class="chgmgmt-time-label">]</span>';
				echo '</div></div>';
			}
			echo '</div>';
		} else {
			echo '<div class="non-collapsible-guid">';
			echo $sItemHdr;
			echo '</div>';
		}
		echo '</div>' . PHP_EOL;
	}
	echo '</div>' . PHP_EOL;
	echo '</div>';
}
function WriteSectionSummary($aCommonProps)
{
	$sStatus = SafeGetArrayItem1Dim($aCommonProps, 'status');
	$sVersion = SafeGetArrayItem1Dim($aCommonProps, 'version');
	$sPhase = SafeGetArrayItem1Dim($aCommonProps, 'phase');
	$aCompositeInfo	= SafeGetArrayItem1Dim($aCommonProps, 'composite_info');
	$sObjectResType = SafeGetArrayItem1Dim($aCommonProps, 'restype');
	$sObjectGUID = SafeGetArrayItem1Dim($aCommonProps, 'guid');
	echo '<div id="summary-section">';
	if (!empty($aCompositeInfo)) {
		$sCompositeDiagramName = SafeGetArrayItem1Dim($aCompositeInfo, 'text');
		$sCompositeDiagramGUID = SafeGetArrayItem1Dim($aCompositeInfo, 'guid');
		$sCompositeDiagramImageURL = SafeGetArrayItem1Dim($aCompositeInfo, 'imageurl');
		echo '<a class="w3-grey-text">Composite </a>';
		echo '<a class="composite-diagram-name w3-link" title="' . _glt('View Composite Diagram') . '" onclick="load_object(\'' . $sCompositeDiagramGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sCompositeDiagramName) . '\',\'' . $sCompositeDiagramImageURL . '\')" >';
		echo '<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sCompositeDiagramImageURL) . '">';
		echo ' ' . htmlspecialchars($sCompositeDiagramName);
		echo '</a>';
		echo '<br>';
	}
	if (!strIsEmpty($sStatus)) {
		echo $sStatus;
		echo '<br>';
	}
	if (!strIsEmpty($sVersion)) {
		echo '<a class="w3-grey-text">Version </a>' . SafeGetArrayItem1Dim($aCommonProps, 'version');
		echo ' ';
	}
	if (!strIsEmpty($sPhase)) {
		echo '<a class="w3-grey-text">Phase </a>' . SafeGetArrayItem1Dim($aCommonProps, 'phase');
	}
	echo '<br>';
	echo SafeGetArrayItem1Dim($aCommonProps, 'created') . '<a class="w3-grey-text"> created by </a>' . SafeGetArrayItem1Dim($aCommonProps, 'author');
	echo '<br>';
	echo SafeGetArrayItem1Dim($aCommonProps, 'modified') . '<a class="w3-grey-text"> last modified</a>';
	echo '</div>';
}
function GetSectionVisibility($sSectionID)
{
	$sSelectedTab = SafeGetInternalArrayParameter($_SESSION, 'selected_tab');
	$sSectionID = '#' . $sSectionID;
	if ($sSectionID === $sSelectedTab)
		return 'display:block';
	else
		return 'display:none';
}
function WriteSectionNotes($sNotes, $sObjectGUID, $sObjectName, $bObjLocked, $sObjImageURL, $sLinkType, $bIsReviewElement, $bIsMini = false)
{
	if ($bIsMini) {
		if (!strIsEmpty($sNotes)) {
			$sPlainNotes = ConvertHyperlinksInEAComments($sNotes);
			echo '<div id="note-mini-section" style="padding-bottom: 12px">';
			if (strIsEmpty($sPlainNotes))
				$sPlainNotes = '&nbsp;';
			echo '<div class="notes-note">' . $sPlainNotes . '</div>';
			echo '<br>';
			echo '</div>' . PHP_EOL;
		}
	} else {
		$sSectionID = 'note-section';
		$sPlainNotes = ConvertHyperlinksInEAComments($sNotes);
		if (($sLinkType === 'props-notes') || ($sLinkType === 'props') || ($sLinkType === '')) {
			if ($bIsReviewElement)
				echo '<div id="note-section" class="notes-section" style="display:none;">';
			else
				echo '<div id="note-section" class="notes-section" style="display:block;">';
		} else {
			echo '<div id="note-section" class="notes-section" style="display:none;">';
		}
		echo '<div class="properties-header">Notes</div>';
		echo '<div class="properties-content">';
		if (!$bObjLocked) {
			if (IsSessionSettingTrue('login_perm_element')) {
				if (IsSessionSettingTrue('edit_object_notes')) {
					echo '<div class="notes-section-edit">';
					echo '<input class="notes-section-edit-button" type="button" value="&#160;" onclick="load_object(\'editelementnote\',\'false\',\'' . $sObjectGUID . '|' . ConvertStringToParameter($sObjectName) . '\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\',\'' . $sObjImageURL . '\')" title="' . _glt('Edit Notes') . '" />';
					echo '</div>';
				}
			}
		}
		if (strIsEmpty($sPlainNotes))
			$sPlainNotes = '&nbsp;';
		echo '<div class="notes-note">' . $sPlainNotes . '</div>';
		echo '</div>';
		echo '</div>' . PHP_EOL;
	}
}
function WriteReviewsSection($aDiscussions, $sObjectGUID, $bAddDiscussions, $sAuthor, $sLoginGUID, $sSessionReviewGUID)
{
	$aReviews = array();
	foreach ($aDiscussions as $disc) {
		$aRow = array();
		$aRow['reviewname'] = $disc['reviewname'];
		$aRow['reviewguid'] = $disc['reviewguid'];
		if (!(in_array($aRow, $aReviews)) && ($disc['reviewguid'] !== '')) {
			$aReviews[] = $aRow;
		}
	}
	foreach ($aReviews as &$rev) {
		$rev['reviewdiscussions'] = array();
		foreach ($aDiscussions as $disc) {
			if ($disc['reviewguid'] === $rev['reviewguid']) {
				$rev['reviewdiscussions'][] = $disc;
			}
		}
	}
	$sReviewName 		= '';
	$sReviewGUID 		= '';
	$aReviewDiscussions = array();
	foreach ($aReviews as $aReview) {
		$sReviewName 	 	= $aReview['reviewname'];
		$sReviewGUID 		= $aReview['reviewguid'];
		$aReviewDiscussions = $aReview['reviewdiscussions'];
		if ((strIsEmpty($sSessionReviewGUID)) || ($sSessionReviewGUID === $sReviewGUID)) {
			echo '<div class="review-name w3-link" onclick="load_object(\'' . $sReviewGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sReviewName) . '\',\'images/element16/review.png\')">';
			echo '<img alt="" title="Review" src="images/spriteplaceholder.png" class="propsprite-review">';
			echo ' ' . $aReview['reviewname'];
			echo '</div>';
			$iCnt = count($aReviewDiscussions);
			for ($i = 0; $i < $iCnt; $i++) {
				$sDiscussText 		= SafeGetArrayItem2Dim($aReviewDiscussions, $i, 'discussion');
				$sDiscussGUID 		= SafeGetArrayItem2Dim($aReviewDiscussions, $i, 'guid');
				$sReviewGUID  		= SafeGetArrayItem2Dim($aReviewDiscussions, $i, 'reviewguid');
				$sExpandDiscussEvent = ' onclick="OnTogglePropertiesReviewDiscussionReplies(\'' . $sDiscussGUID . '\')"';
				$sReplyID			= 'replies_' . $sDiscussGUID;
				$sStatusButtonID	= 'sb_' . $sDiscussGUID;
				$sPriority			= SafeGetArrayItem2Dim($aReviewDiscussions, $i, 'priority');
				$sPriorityImageClass = SafeGetArrayItem2Dim($aReviewDiscussions, $i, 'priorityimageclass');
				$sPriorityTooltip 	= str_replace('%PRIORITY%', $sPriority, _glt('Priority: xx'));
				$sStatus 			= SafeGetArrayItem2Dim($aReviewDiscussions, $i, 'status');
				$sStatusImageClass	= SafeGetArrayItem2Dim($aReviewDiscussions, $i, 'statusimageclass');
				$sStatusTooltip 	= str_replace('%STATUS%', $sStatus, _glt('Status: xx'));
				$bHasReplies 		= array_key_exists(("replies"), $aReviewDiscussions[$i]);
				$sIsDisabled 		= '';
				if ($bAddDiscussions) {
					if (!strIsEmpty($sReviewGUID)) {
						if ($sSessionReviewGUID === $sReviewGUID) {
							$bCanReply = true;
						} else {
							$bCanReply = false;
						}
					} else {
						$bCanReply = true;
					}
				} else {
					$bCanReply = false;
				}
				if ($bAddDiscussions || $bHasReplies) {
					echo '<div class="review-item collapsible-section-header-closed">';
					echo '<img alt="" src="images/spriteplaceholder.png" class="review-item-icon collapsible-section-header-closed-icon show-cursor-pointer" ' . $sExpandDiscussEvent . '>';
				} else {
					echo '<div class="review-item">';
					$sExpandDiscussEvent = '';
					$sIsDisabled 		=  'disabled="true"';
				}
				WriteAvatarImage($aReviewDiscussions[$i]['avatarid'], 'false');
				echo '<div class="discussion-item-states" >';
				WriteDiscussionStatusMenus($bCanReply, $sDiscussGUID, $sPriorityImageClass, $sPriorityTooltip, $sStatusImageClass, $sStatusTooltip);
				echo '</div>';
				echo '<div class="review-item-text"' . $sIsDisabled . $sExpandDiscussEvent . '>' . $sDiscussText . '</div>';
				echo '<div class="review-item-text-footer">';
				echo '<div class="review-item-text-footer-dateauthor">' . $aReviewDiscussions[$i]['created'] . '&nbsp; &nbsp;' . $aReviewDiscussions[$i]['author'] . '</div>';
				echo '</div>';
				echo '<div class="review-item-replies" id="' . $sReplyID . '">';
				if (array_key_exists(("replies"), $aReviewDiscussions[$i])) {
					$aReplies = $aReviewDiscussions[$i]['replies'];
					$iReplyCnt = count($aReplies);
					for ($iR = 0; $iR < $iReplyCnt; $iR++) {
						$sReplyAuthor 	= SafeGetArrayItem2Dim($aReplies, $iR, 'replyauthor');
						$sReplyAvatarID	= SafeGetArrayItem2Dim($aReplies, $iR, 'replyavatarid');
						$sReplyAvatarImage	= SafeGetArrayItem2Dim($aReplies, $iR, 'replyavatarimage');
						$sReplyCreated 	= SafeGetArrayItem2Dim($aReplies, $iR, 'replycreated');
						$sReplyText 	= SafeGetArrayItem2Dim($aReplies, $iR, 'replytext');
						echo '<div class="review-item-reply">';
						WriteAvatarImage($sReplyAvatarID, 'true');
						echo '<div class="review-item-reply-text">' . $sReplyText . '</div>';
						echo '<div class="review-item-reply-text-footer">';
						echo '<div class="review-item-reply-text-footer-dateauthor">' . $sReplyCreated . '&nbsp; &nbsp;' . $sReplyAuthor . '</div>';
						echo '</div>';
						echo '</div>';
					}
				}
				if ($bAddDiscussions) {
					if ($bCanReply) {
						echo '<div class="discussion-reply-form" id="ddrf_' . $sDiscussGUID . '"><form id="drf_' . $sDiscussGUID . '" method="post">';
						echo '<div class="discussion-reply-comment-send">';
						echo '<div class="discussion-reply-send-div">';
						echo '<input class="webea-main-styled-button discussion-reply-send-button" type="submit" value="" onclick="OnFormRunAddReply(event, this.form)">';
						echo '</div>';
						echo '<div class="discussion-reply-comment-div">';
						echo '<textarea class="discussion-reply-new-comment" name="comments" placeholder="' . _glt('Post reply') . '"></textarea>';
						echo '</div>';
						echo '</div>';
						echo '<input type="hidden" name="guid" value="' . $sDiscussGUID . '"> ';
						echo '<input type="hidden" name="author" value="' . $sAuthor . '"> ';
						echo '<input type="hidden" name="isreply" value="true"> ';
						echo '<input type="hidden" name="objectguid" value="' . $sObjectGUID . '"> ';
						echo '<input type="hidden" name="loginguid" value="' . $sLoginGUID . '">';
						if (!strIsEmpty($sSessionReviewGUID) && $sSessionReviewGUID === $sReviewGUID) {
							echo '<input type="hidden" name="sessionreviewguid" value="' . $sSessionReviewGUID . '">';
						}
						echo '</form></div>';
					} else {
						echo '<div class="discussion-reply-form" id="ddrf_' . $sDiscussGUID . '"><form id="drf_' . $sDiscussGUID . '" method="post">';
						echo '<div class="discussion-reply-join-review-message">' . _glt('Join the review to post a reply') . '</div>';
						echo '</form></div>';
					}
				}
				echo '</div>';
				echo '</div>';
			}
		}
	}
	if ($bAddDiscussions) {
		if (!strIsEmpty($sSessionReviewGUID)) {
			echo '<div class="review-filtered-message">';
			echo _glt('Filtered to display the current review only');
			echo '</div>';
			echo '<div id="discussion-form"><form id="discussion-form1" method="post">';
			echo '<div id="discussion-comment-send">';
			echo '<div id="discussion-send-div">';
			echo '<input class="webea-main-styled-button" id="discussion-send-button" type="submit" value="" onclick="OnFormRunAddDiscussion(event)">';
			echo '</div>';
			echo '<div id="discussion-comment-div">';
			echo '<textarea class="discussion-new-comment" name="comments" placeholder="' . _glt('Create Review Topic') . '"></textarea>';
			echo '</div>';
			echo '</div>';
			echo '<input type="hidden" name="guid" value="' . $sObjectGUID . '"> ';
			echo '<input type="hidden" name="author" value="' . $sAuthor . '"> ';
			echo '<input type="hidden" name="isreply" value="false"> ';
			echo '<input type="hidden" name="loginguid" value="' . $sLoginGUID . '">';
			echo '<input type="hidden" name="sessionreviewguid" value="' . $sSessionReviewGUID . '">';
			echo '</form></div>';
		} else {
			echo '<div class="review-new-topic-message">';
			echo _glt('Join a review to create a new topic');
			echo '</div>';
		}
	}
}
function WriteDiscussionsSection($aDiscussions, $sObjectGUID, $bAddDiscussions, $sAuthor, $sLoginGUID, $sSessionReviewGUID)
{
	$iCnt = count($aDiscussions);
	for ($i = 0; $i < $iCnt; $i++) {
		$sDiscussText 		= SafeGetArrayItem2Dim($aDiscussions, $i, 'discussion');
		$sDiscussGUID 		= SafeGetArrayItem2Dim($aDiscussions, $i, 'guid');
		$sReviewGUID  		= SafeGetArrayItem2Dim($aDiscussions, $i, 'reviewguid');
		$sExpandDiscussEvent = ' onclick="OnToggleDiscussionReplies(this)"';
		$sReplyID			= 'replies_' . $sDiscussGUID;
		$sStatusButtonID	= 'sb_' . $sDiscussGUID;
		$sPriority			= SafeGetArrayItem2Dim($aDiscussions, $i, 'priority');
		$sPriorityImageClass = SafeGetArrayItem2Dim($aDiscussions, $i, 'priorityimageclass');
		$sPriorityTooltip = str_replace('%PRIORITY%', $sPriority, _glt('Priority: xx'));
		$sStatus 			= SafeGetArrayItem2Dim($aDiscussions, $i, 'status');
		$sStatusImageClass	= SafeGetArrayItem2Dim($aDiscussions, $i, 'statusimageclass');
		$sStatusTooltip = str_replace('%STATUS%', $sStatus, _glt('Status: xx'));
		$bHasReplies 		= array_key_exists(("replies"), $aDiscussions[$i]);
		$sIsDisabled 		= '';
		if ($bAddDiscussions) {
			if (!strIsEmpty($sSessionReviewGUID)) {
				$bCanReply = false;
			} else {
				$bCanReply = true;
			}
		} else {
			$bCanReply = false;
		}
		if (strIsEmpty($sReviewGUID)) {
			if (($bAddDiscussions && $bCanReply) || $bHasReplies) {
				echo '<div class="discussion-item collapsible-section-header-closed">';
				echo '<img alt="" src="images/spriteplaceholder.png" class="discussion-item-icon collapsible-section-header-closed-icon show-cursor-pointer" ' . $sExpandDiscussEvent . '>';
			} else {
				echo '<div class="discussion-item">';
				$sExpandDiscussEvent = '';
				$sIsDisabled 		=  'disabled="true"';
			}
			WriteAvatarImage($aDiscussions[$i]['avatarid'], 'false');
			echo '<div class="discussion-item-states" >';
			WriteDiscussionStatusMenus($bCanReply, $sDiscussGUID, $sPriorityImageClass, $sPriorityTooltip, $sStatusImageClass, $sStatusTooltip);
			echo '</div>';
			echo '<div class="discussion-item-text2"' . $sIsDisabled . $sExpandDiscussEvent . '><div class="discussion-item-text">' . $sDiscussText . '</div></div>';
			echo '<div class="discussion-item-text-footer">';
			echo '<div class="discussion-item-text-footer-dateauthor">' . $aDiscussions[$i]['created'] . '&nbsp; &nbsp;' . $aDiscussions[$i]['author'] . '</div>';
			echo '</div>';
			echo '<div class="discussion-item-replies" id="' . $sReplyID . '">';
			if (array_key_exists(("replies"), $aDiscussions[$i])) {
				$aReplies = $aDiscussions[$i]['replies'];
				$iReplyCnt = count($aReplies);
				for ($iR = 0; $iR < $iReplyCnt; $iR++) {
					$sReplyAuthor 	= SafeGetArrayItem2Dim($aReplies, $iR, 'replyauthor');
					$sReplyAvatarID	= SafeGetArrayItem2Dim($aReplies, $iR, 'replyavatarid');
					$sReplyAvatarImage	= SafeGetArrayItem2Dim($aReplies, $iR, 'replyavatarimage');
					$sReplyCreated 	= SafeGetArrayItem2Dim($aReplies, $iR, 'replycreated');
					$sReplyText 	= SafeGetArrayItem2Dim($aReplies, $iR, 'replytext');
					echo '<div class="discussion-item-reply">';
					WriteAvatarImage($sReplyAvatarID, 'true');
					echo '<div class="discussion-item-reply-text">' . $sReplyText . '</div>';
					echo '<div class="discussion-item-reply-text-footer">';
					echo '<div class="discussion-item-reply-text-footer-dateauthor">' . $sReplyCreated . '&nbsp; &nbsp;' . $sReplyAuthor . '</div>';
					echo '</div>';
					echo '</div>';
				}
			}
			if ($bAddDiscussions) {
				if ($bCanReply) {
					echo '<div class="discussion-reply-form" id="ddrf_' . $sDiscussGUID . '"><form id="drf_' . $sDiscussGUID . '" method="post">';
					echo '<div class="discussion-reply-comment-send">';
					echo '<div class="discussion-reply-send-div">';
					echo '<input class="webea-main-styled-button discussion-reply-send-button" type="submit" value="" onclick="OnFormRunAddReply(event, this.form)">';
					echo '</div>';
					echo '<div class="discussion-reply-comment-div">';
					echo '<textarea class="discussion-reply-new-comment" name="comments" placeholder="' . _glt('Post reply') . '"></textarea>';
					echo '</div>';
					echo '</div>';
					echo '<input type="hidden" name="guid" value="' . $sDiscussGUID . '"> ';
					echo '<input type="hidden" name="author" value="' . $sAuthor . '"> ';
					echo '<input type="hidden" name="isreply" value="true"> ';
					echo '<input type="hidden" name="objectguid" value="' . $sObjectGUID . '"> ';
					echo '<input type="hidden" name="loginguid" value="' . $sLoginGUID . '">';
					echo '</form></div>';
				}
			}
			echo '</div>';
			echo '</div>';
		}
	}
	if ($bAddDiscussions) {
		if (strIsEmpty($sSessionReviewGUID)) {
			echo '<div id="discussion-form"><form id="discussion-form1" method="post">';
			echo '<div id="discussion-comment-send">';
			echo '<div id="discussion-send-div">';
			echo '<input class="webea-main-styled-button" id="discussion-send-button" type="submit" value="" onclick="OnFormRunAddDiscussion(event)">';
			echo '</div>';
			echo '<div id="discussion-comment-div">';
			echo '<textarea class="discussion-new-comment" name="comments" placeholder="' . _glt('Create new Discussion') . '"></textarea>';
			echo '</div>';
			echo '</div>';
			echo '<input type="hidden" name="guid" value="' . $sObjectGUID . '"> ';
			echo '<input type="hidden" name="author" value="' . $sAuthor . '"> ';
			echo '<input type="hidden" name="isreply" value="false"> ';
			echo '<input type="hidden" name="loginguid" value="' . $sLoginGUID . '">';
			echo '</form></div>';
		} else {
			echo '<div class="review-new-topic-message">';
			echo _glt('General discussions cannot be added while in a review');
			echo '</div>';
		}
	}
}
function WriteAvatarCSS($aAvatars)
{
	if (IsSessionSettingTrue('use_avatars')) {
		echo '<style>';
		foreach ($aAvatars as $aAvatar) {
			$sImage = str_replace("\n", '', $aAvatar['avatarimage']);
			echo '#' . 'avatarimg-' . $aAvatar['avatarid'] . ' {';
			echo 'background-image: url("data:image/png; base64,' . $sImage . '");';
			echo '}';
		}
		echo '</style>';
	}
}
function WriteDiscussionStatusMenus($bIsEnabled, $sDiscussGUID, $sPriorityImageClass, $sPriorityTooltip, $sStatusImageClass, $sStatusTooltip)
{
	if ($bIsEnabled) {
		echo '<span id="prioritymenu-button">';
		echo '<img alt="" id="priorityimage_' . $sDiscussGUID . '" src="images/spriteplaceholder.png" class="' . $sPriorityImageClass . '" title="' . $sPriorityTooltip . '" onclick="show_menu(this)">&nbsp;';
		echo '<div class="prioritymenu-content" id="prioritymenu-content-' . $sDiscussGUID . '">';
		echo '<div class="contextmenu-header">' . _glt('Priority') . '</div>';
		echo '<div class="contextmenu-items">';
		echo '<div class="contextmenu-item" onclick="set_discuss_state(\'' . $sDiscussGUID . '\',\'priority\',\'High\',\'' . str_replace('%PRIORITY%', _glt('High'), _glt('Priority: xx')) . '\')"><img alt="" src="images/spriteplaceholder.png" class="propsprite-discusspriorityhigh"></img>' . _glt('High') . '</div>';
		echo '<div class="contextmenu-item" onclick="set_discuss_state(\'' . $sDiscussGUID . '\',\'priority\',\'Medium\',\'' . str_replace('%PRIORITY%', _glt('Medium'), _glt('Priority: xx')) . '\')"><img alt="" src="images/spriteplaceholder.png" class="propsprite-discussprioritymed"></img>' . _glt('Medium') . '</div>';
		echo '<div class="contextmenu-item" onclick="set_discuss_state(\'' . $sDiscussGUID . '\',\'priority\',\'Low\',\'' . str_replace('%PRIORITY%', _glt('Low'), _glt('Priority: xx')) . '\')"><img alt="" src="images/spriteplaceholder.png" class="propsprite-discussprioritylow"></img>' . _glt('Low') . '</div>';
		echo '<hr>';
		echo '<div class="contextmenu-item" onclick="set_discuss_state(\'' . $sDiscussGUID . '\',\'priority\',\'None\')"><img alt="" src="images/spriteplaceholder.png" class="propsprite-discussprioritynone"></img>' . _glt('<none>') . '</div>';
		echo '</div>';
		echo '</div>';
		echo '</span>';
		echo '<span id="statusmenu-button">';
		echo '<img alt="" id="statusimage_' . $sDiscussGUID . '" src="images/spriteplaceholder.png" class="' . $sStatusImageClass . '" title="' . $sStatusTooltip . '" onclick="show_menu(this)">&nbsp;';
		echo '<div class="statusmenu-content" id="statusmenu-content-' . $sDiscussGUID . '">';
		echo '<div class="contextmenu-header">' . _glt('Status') . '</div>';
		echo '<div class="contextmenu-items">';
		echo '<div class="contextmenu-item" onclick="set_discuss_state(\'' . $sDiscussGUID . '\',\'status\',\'Open\',\'' . str_replace('%STATUS%', _glt('Open'), _glt('Status: xx')) . '\', this)"><img alt="" src="images/spriteplaceholder.png" class="propsprite-discussstatusopen"></img>' . _glt('Open') . '</div>';
		echo '<div class="contextmenu-item" onclick="set_discuss_state(\'' . $sDiscussGUID . '\',\'status\',\'Awaiting Review\',\'' . str_replace('%STATUS%', _glt('Awaiting Review'), _glt('Status: xx')) . '\', this)"><img alt="" src="images/spriteplaceholder.png" class="propsprite-discussstatusawait"></img>' . _glt('Awaiting Review') . '</div>';
		echo '<div class="contextmenu-item" onclick="set_discuss_state(\'' . $sDiscussGUID . '\',\'status\',\'Closed\',\'' . str_replace('%STATUS%', _glt('Closed'), _glt('Status: xx')) . '\', this)"><img alt="" src="images/spriteplaceholder.png" class="propsprite-discussstatuscomplete"></img>' . _glt('Closed') . '</div>';
		echo '</div>';
		echo '</div>';
		echo '</span>';
	} else {
		echo '<span id="prioritymenu-button" disabled="true">';
		echo '<img alt="" id="priorityimage_' . $sDiscussGUID . '" src="images/spriteplaceholder.png" class="' . $sPriorityImageClass . '" title="' . $sPriorityTooltip . '">&nbsp;';
		echo '</span>';
		echo '<span id="statusmenu-button" disabled="true">';
		echo '<img alt="" id="statusimage_' . $sDiscussGUID . '" src="images/spriteplaceholder.png" class="' . $sStatusImageClass . '" title="' . $sStatusTooltip . '">&nbsp;';
		echo '</span>';
	}
}
