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
	$sSearch		= '';
	$sTerm			= '';
	$sCategory		= '';
	$sSearchType	= '';
	$sWhen			= '';
	if ( !strIsEmpty($sObjectHyper) )
	{
		$a = explode('&', $sObjectHyper);
		if ($a)
		{
			foreach ($a as $sOption)
			{
				list($sOptionName, $sOptionValue) = explode('=', $sOption);
				$sOptionName = trim($sOptionName);
				$sOptionValue = trim($sOptionValue);
				if ($sOptionName==='search')
				{
					$sSearch = $sOptionValue;
				}
				elseif ($sOptionName==='term')
				{
					$sTerm = urldecode($sOptionValue);
				}
				elseif ($sOptionName==='category')
				{
					$sCategory = $sOptionValue;
				}
				elseif ($sOptionName==='type')
				{
					$sSearchType = $sOptionValue;
				}
				elseif ($sOptionName==='recent')
				{
					$sWhen = $sOptionValue;
				}
			}
		}
	}
	$iErrorCode = http_response_code();
	echo '<div id="main-search">';
	echo '	<div id="search-custom-layout">';
	echo '		<div class="webea-page-header">' . _glt('Custom Search') . '</div>';
	echo '		<div id="search-form">';
	echo '			<form class = "search-form" role="form" onsubmit="OnFormRunCustomSearch(event,\'' . _glt('Search Results') . '\')">';
	echo '				<div class="search-searchcriteria-group">';
	echo '					<div class="search-field-label"><label>' . _glt('Term') . '</label></div>';
	echo '					<div><input id="search-criteria-field" type="text" autocapitalize="none" placeholder="' . _glt('<search term>') . '" name="term" max-length="50"' . (strIsEmpty($sTerm) ? '' : ' value="' . $sTerm . '"' ) . '/></div>';
	echo '				</div>';
	echo '				<div class="search-searchcriteria-group">';
	echo '					<div class="search-field-label"><label>' . _glt('Search for') . '</label></div>';
	echo '					<select id="search-searchfor-combo" name="category" onclick="onClickSearchFor(this)" class="webea-main-styled-combo">';
	echo '						<option value="diagram"' . (strIsEmpty($sCategory) || $sCategory==='diagram' ? ' selected' : '' ) . '>Diagrams</option>';
	echo '						<option value="element"' . ($sCategory==='element' ? ' selected' : '' ) . '>Elements</option>';
	echo '						<option value="package"' . ($sCategory==='package' ? ' selected' : '' ) . '>Packages</option>';
	if (IsSessionSettingTrue('show_discuss'))
	{
		echo '					<option value="discussion"' . ($sCategory==='discussion' ? ' selected' : '' ) . '>Discussions</option>';
		echo '					<option value="review"' . ($sCategory==='review' ? ' selected' : '' ) . '>Reviews</option>';
	}
	echo '					</select>';
	echo '				</div>';
	echo '				<div class="search-searchcriteria-group">';
	echo '					<div class="search-field-label"><label>' . _glt('Search in') . '</label></div>';
	echo '					<select id="search-searchtype-combo" name="type" class="webea-main-styled-combo">';
	echo '						<option value="name"' . (strIsEmpty($sSearchType) || $sSearchType==='name' ? ' selected' : '' ) . '>Name</option>';
	echo '						<option value="simple"' . ($sSearchType==='simple' ? ' selected' : '' ) . '>Name,&nbsp;Alias&nbsp;and&nbsp;Notes</option>';
	echo '						<option value="author"' . ($sSearchType==='author' ? ' selected' : '' ) . '>Author</option>';
	echo '						<option value="guid"' . ($sSearchType==='guid' ? ' selected' : '' ) . '>ID</option>';
	echo '					</select>';
	echo '				</div>';
	echo '				<div class="search-searchcriteria-group">';
	echo '					<div class="search-field-label"><label>' . _glt('When') . '</label></div>';
	echo '					<div>';
	echo '						<select id="search-when-combo" name="recent" class="webea-main-styled-combo">';
	echo '							<option value="0d"' . ($sWhen==='0d' ? ' selected' : '' ) . '>Today</option>';
	echo '							<option value="-3d"' . ((strIsEmpty($sWhen) && strIsEmpty($sObjectHyper)) || $sWhen==='-3d' ? ' selected' : '' ) . '>Last 3 days</option>';
	echo '							<option value="-7d"' . ($sWhen==='-7d' ? ' selected' : '' ) . '>Last 7 days</option>';
	echo '							<option value="-14d"' . ($sWhen==='-14d' ? ' selected' : '' ) . '>Last 14 days</option>';
	echo '							<option value="-30d"' . ($sWhen==='-30d' ? ' selected' : '' ) . '>Last 30 days</option>';
	echo '							<option value="-365d"' . ($sWhen==='-365d' ? ' selected' : '' ) . '>Last 12 months</option>';
	echo '							<option value="Any"' . (strIsEmpty($sWhen) && !strIsEmpty($sObjectHyper) ? ' selected' : '' ) . '>Any</option>';
	echo '						</select>';
	echo '					</div>';
	echo '				</div>';
	echo '				<div id="search-message" class=""></div>';
	echo '				<div class="webea-main-styled-button search-action-button" onclick="OnFormRunCustomSearch(event,\'' . _glt('Search Results') . '\')"><div class="search-button-icon"></div><div class="webea-styled-button-text">' . _glt('Run Search') . '</div></div>';
	echo '			</form>';
	echo '		</div>';
	echo '	</div>';
	echo '</div>';
?>