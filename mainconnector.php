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
	$aCommonProps 	= array();
	$aSourceProps 	= array();
	$aTargetProps 	= array();
	include('./data_api/get_connectorproperties.php');
	if ($aCommonProps['guid'] === null)
	{
		echo '<div id="main-content-empty">';
		echo _glt('NoConnector') . g_csHTTPNewLine . g_csHTTPNewLine;
		echo '<a href="javascript:MoveToPrevItemInNavigationHistory()">' . _glt('Back') . '</a>';
		echo '</div>';
		exit();
	}
	$sConnectorName 	= htmlspecialchars(SafeGetArrayItem1Dim($aCommonProps, 'name'));
	$sConnectorType 	= SafeGetArrayItem1Dim($aCommonProps, 'type');
	$sConnectorStereotype = SafeGetArrayItem1Dim($aCommonProps, 'stereotype');
	$aConnectorStereotypes = SafeGetArrayItem1Dim($aCommonProps, 'stereotypes');
	$sConnectorGUID 	= SafeGetArrayItem1Dim($aCommonProps, 'guid');
	$sConnectorAlias 	= SafeGetArrayItem1Dim($aCommonProps, 'alias');
	$sConnectorNotes 	= SafeGetArrayItem1Dim($aCommonProps, 'notes');
	$sConnectorDirection = SafeGetArrayItem1Dim($aCommonProps, 'direction');
	$sConnectorImageURL	= SafeGetArrayItem1Dim($aCommonProps, 'imageurl');
	echo '<div class="main-connector-details">';
	echo '<div id="object-main-details">';
	echo '<div class="object-image">';
	echo '<img src="' . $sConnectorImageURL . '" alt="" title="' . $sConnectorType . '" height="64" width="64"/>';
	echo '</div>';
	echo '<div class="object-name">';
	$sStereoHTML = buildStereotypeDisplayHTML($sConnectorStereotype, $aConnectorStereotypes, false);
	if ( !strIsEmpty($sStereoHTML) )
	{
		echo ( '&nbsp;' . $sStereoHTML . '&nbsp;' );
	}
	echo $sConnectorType . ' connector ' . $sConnectorName;
	echo '</div>';
	$sSrcObjName 		= SafeGetArrayItem1Dim($aSourceProps, 'name');
	$sSrcObjType 		= SafeGetArrayItem1Dim($aSourceProps, 'type');
	$sSrcObjResType 	= SafeGetArrayItem1Dim($aSourceProps, 'restype');
	$sSrcObjImageURL	= SafeGetArrayItem1Dim($aSourceProps, 'imageurl');
	$sSrcObjGUID 		= SafeGetArrayItem1Dim($aSourceProps, 'guid');
	$sTrgtObjName 		= SafeGetArrayItem1Dim($aTargetProps, 'name');
	$sTrgtObjType 		= SafeGetArrayItem1Dim($aTargetProps, 'type');
	$sTrgtObjResType 	= SafeGetArrayItem1Dim($aTargetProps, 'restype');
	$sTrgtObjImageURL	= SafeGetArrayItem1Dim($aTargetProps, 'imageurl');
	$sTrgtObjGUID 		= SafeGetArrayItem1Dim($aTargetProps, 'guid');
	$sSrcObjName		= GetPlainDisplayName($sSrcObjName);
	$sTrgtObjName 		= GetPlainDisplayName($sTrgtObjName);
	echo '<div class="object-line2">';
	echo _glt('from') . '&nbsp;&nbsp;';
	echo '<a class="w3-link" onclick="load_object(\'' . $sSrcObjGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sSrcObjName) . '\',\'' . $sSrcObjImageURL . '\')">';
	echo '<img src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sSrcObjImageURL) . '" alt="" title="' . $sSrcObjResType . '">&nbsp;' . htmlspecialchars($sSrcObjName) . '</a>';
	echo '&nbsp;&nbsp;' . _glt('to') . '&nbsp;&nbsp;';
	echo '<a class="w3-link" onclick="load_object(\'' . $sTrgtObjGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sTrgtObjName) . '\',\'' . $sTrgtObjImageURL . '\')">';
	echo '<img src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sTrgtObjImageURL) . '" alt="" title="' . $sTrgtObjResType . '">&nbsp;' . htmlspecialchars($sTrgtObjName) . '</a>';
	echo '</div>';
	echo '</div>';
	if ( !strIsEmpty($sConnectorNotes) )
	{
		$sPlainNotes = ConvertHyperlinksInEAComments($sConnectorNotes);
		echo '<div class="notes-section">';
		echo '<div class="notes-note">' . $sPlainNotes . '</div>';
		echo '</div>' . PHP_EOL;
	}
	$sSrcObjRole 		= SafeGetArrayItem1Dim($aCommonProps, 'sourcerole');
	$sSrcObjAlias 		= SafeGetArrayItem1Dim($aCommonProps, 'sourcealias');
	$sSrcObjStereotype	= SafeGetArrayItem1Dim($aCommonProps, 'sourcestereotype');
	$aSrcObjStereotypes	= SafeGetArrayItem1Dim($aCommonProps, 'sourcestereotypes');
	$sSrcObjRoleDesc	= SafeGetArrayItem1Dim($aCommonProps, 'sourceroledesc');
	$sSrcObjMultiplicity= SafeGetArrayItem1Dim($aCommonProps, 'sourcemultiplicity');
	$sSrcObjOrdered		= SafeGetArrayItem1Dim($aCommonProps, 'sourceordered');
	$sSrcObjAccess		= SafeGetArrayItem1Dim($aCommonProps, 'sourceaccess');
	$sSrcObjAggregation	= SafeGetArrayItem1Dim($aCommonProps, 'sourceaggregation');
	$sSrcObjScope		= SafeGetArrayItem1Dim($aCommonProps, 'sourcescope');
	$sSrcObjMembertype	= SafeGetArrayItem1Dim($aCommonProps, 'sourcemembertype');
	$sSrcObjChangable	= SafeGetArrayItem1Dim($aCommonProps, 'sourcechangeable');
	$sSrcObjContainment = SafeGetArrayItem1Dim($aCommonProps, 'sourcecontainment');
	$sSrcObjNavigability= SafeGetArrayItem1Dim($aCommonProps, 'sourcenavigability');
	$sSrcObjDerived		= SafeGetArrayItem1Dim($aCommonProps, 'sourcederived');
	$sSrcObjDerivedUnion= SafeGetArrayItem1Dim($aCommonProps, 'sourcederivedunion');
	$sSrcObjOwned		= SafeGetArrayItem1Dim($aCommonProps, 'sourceowned');
	$sSrcObjAllowDup	= SafeGetArrayItem1Dim($aCommonProps, 'sourceallowduplicates');
	$sTrgtObjRole 		= SafeGetArrayItem1Dim($aCommonProps, 'targetrole');
	$sTrgtObjAlias 		= SafeGetArrayItem1Dim($aCommonProps, 'targetalias');
	$sTrgtObjRoleDesc	= SafeGetArrayItem1Dim($aCommonProps, 'targetroledesc');
	$sTrgtObjStereotype	= SafeGetArrayItem1Dim($aCommonProps, 'targetstereotype');
	$aTrgtObjStereotypes = SafeGetArrayItem1Dim($aCommonProps, 'targetstereotypes');
	$sTrgtObjMultiplicity = SafeGetArrayItem1Dim($aCommonProps, 'targetmultiplicity');
	$sTrgtObjOrdered	= SafeGetArrayItem1Dim($aCommonProps, 'targetordered');
	$sTrgtObjAccess		= SafeGetArrayItem1Dim($aCommonProps, 'targetaccess');
	$sTrgtObjAggregation= SafeGetArrayItem1Dim($aCommonProps, 'targetaggregation');
	$sTrgtObjScope		= SafeGetArrayItem1Dim($aCommonProps, 'targetscope');
	$sTrgtObjMembertype	= SafeGetArrayItem1Dim($aCommonProps, 'targetmembertype');
	$sTrgtObjChangable	= SafeGetArrayItem1Dim($aCommonProps, 'targetchangeable');
	$sTrgtObjContainment= SafeGetArrayItem1Dim($aCommonProps, 'targetcontainment');
	$sTrgtObjNavigability= SafeGetArrayItem1Dim($aCommonProps, 'targetnavigability');
	$sTrgtObjDerived	= SafeGetArrayItem1Dim($aCommonProps, 'targetderived');
	$sTrgtObjDerivedUnion= SafeGetArrayItem1Dim($aCommonProps, 'targetderivedunion');
	$sTrgtObjOwned		= SafeGetArrayItem1Dim($aCommonProps, 'targetowned');
	$sTrgtObjAllowDup	= SafeGetArrayItem1Dim($aCommonProps, 'targetallowduplicates');
	echo '<div class="connector-extend-prop-table-div">';
	echo '<table class="connector-extend-prop-table">';
	echo '<tr><th>&nbsp;</th><th>' . _glt('Source') . '</th><th>' . _glt('Target') . '</th></tr>';
	echo '<tr><td class="w3-grey-text">' . _glt('Role') . '</td><td class="cell-lrborders">' . $sSrcObjRole . '</td><td>' . $sTrgtObjRole . '</td></tr>';
	echo '<tr><td class="w3-grey-text">' . _glt('Comment') . '</td><td class="cell-lrborders">' . $sSrcObjRoleDesc . '</td><td>' . $sTrgtObjRoleDesc . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-subheader" colspan="3">' . _glt('Multiplicity') . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-firstcol w3-grey-text">' . _glt('Multiplicity') . '</td><td class="cell-lrborders">' . $sSrcObjMultiplicity . '</td><td>' . $sTrgtObjMultiplicity . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-firstcol w3-grey-text">' . _glt('Ordered') . '</td><td class="cell-lrborders">' . ConvertBoolToText($sSrcObjOrdered) . '</td><td>' . ConvertBoolToText($sTrgtObjOrdered) . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-firstcol w3-grey-text">' . _glt('Allow duplicates') . '</td><td class="cell-lrborders">' . ConvertBoolToText($sSrcObjAllowDup) . '</td><td>' . ConvertBoolToText($sTrgtObjAllowDup) . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-subheader" colspan="3">' . _glt('Details') . '</td></tr>';
	$sSrcObjStereoHTML 	= buildStereotypeDisplayHTML($sSrcObjStereotype, $aSrcObjStereotypes, false);
	$sTrgtObjStereoHTML = buildStereotypeDisplayHTML($sTrgtObjStereotype, $aTrgtObjStereotypes, false);
	echo '<tr><td class="connector-extend-prop-table-firstcol w3-grey-text">' . _glt('Stereotype') . '</td><td class="cell-lrborders">' . $sSrcObjStereoHTML . '</td><td>' . $sTrgtObjStereoHTML . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-firstcol w3-grey-text">' . _glt('Alias') . '</td><td class="cell-lrborders">' . $sSrcObjAlias . '</td><td>' . $sTrgtObjAlias . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-firstcol w3-grey-text">' . _glt('Access') . '</td><td class="cell-lrborders">' . $sSrcObjAccess . '</td><td>' . $sTrgtObjAccess . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-firstcol w3-grey-text">' . _glt('Navigability') . '</td><td class="cell-lrborders">' . $sSrcObjNavigability . '</td><td>' . $sTrgtObjNavigability . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-firstcol w3-grey-text">' . _glt('Aggregation') . '</td><td class="cell-lrborders">' . $sSrcObjAggregation . '</td><td>' . $sTrgtObjAggregation . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-firstcol w3-grey-text">' . _glt('Scope') . '</td><td class="cell-lrborders">' . $sSrcObjScope . '</td><td>' . $sTrgtObjScope . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-subheader" colspan="3">' . _glt('Advanced') . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-firstcol w3-grey-text">' . _glt('Member type') . '</td><td class="cell-lrborders">' . $sSrcObjMembertype . '</td><td>' . $sTrgtObjMembertype . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-firstcol w3-grey-text">' . _glt('Changable') . '</td><td class="cell-lrborders">' . $sSrcObjChangable . '</td><td>' . $sTrgtObjChangable . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-firstcol w3-grey-text">' . _glt('Containment') . '</td><td class="cell-lrborders">' . $sSrcObjContainment . '</td><td>' . $sTrgtObjContainment . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-firstcol w3-grey-text">' . _glt('Derived') . '</td><td class="cell-lrborders">' . ConvertBoolToText($sSrcObjDerived) . '</td><td>' . ConvertBoolToText($sTrgtObjDerived) . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-firstcol w3-grey-text">' . _glt('Derived Union') . '</td><td class="cell-lrborders">' . ConvertBoolToText($sSrcObjDerivedUnion) . '</td><td>' . ConvertBoolToText($sTrgtObjDerivedUnion) . '</td></tr>';
	echo '<tr><td class="connector-extend-prop-table-firstcol w3-grey-text">' . _glt('Owned') . '</td><td class="cell-lrborders">' . ConvertBoolToText($sSrcObjOwned) . '</td><td>' . ConvertBoolToText($sTrgtObjOwned) . '</td></tr>';
	echo '</table>';
	echo '</div>';
	echo '</div>';
?>