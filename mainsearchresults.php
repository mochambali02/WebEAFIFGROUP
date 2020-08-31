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
	$sSearch 		= urlencode(SafeGetInternalArrayParameter($_GET, 'search'));
	$sCategory 		= urlencode(SafeGetInternalArrayParameter($_GET, 'category'));
	$sSearchType 	= urlencode(SafeGetInternalArrayParameter($_GET, 'type'));
	$sTerm 			= rawurlencode(SafeGetInternalArrayParameter($_GET, 'term'));
	$sWhen 			= urlencode(SafeGetInternalArrayParameter($_GET, 'recent'));
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
					$sTerm = $sOptionValue;
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
	if ( $sCategory !== 'discussion' && $sCategory !== 'review' && strIsEmpty($sWhen) && strIsEmpty($sTerm) )
	{
		setResponseCode(456, _glt('No term or timeframe'));
		exit();
	}
	$sSearch = 'custom';
	$aRows = array();
	include('./data_api/get_json_search.php');
	$iRows = count($aRows);
	if ($iRows <= 0)
	{
		$sOSLCErrorMsg = BuildOSLCErrorString();
		if ( strIsEmpty($sOSLCErrorMsg) )
		{
			echo '<div id="search-results-div"><div class="search-results-div-inner">' . _glt('No Results Found') . '</div></div>';
		}
	}
	else
	{
		echo '<div id="search-results-count">' . $iRows . '</div>';
		echo '<div id="search-results-div" class="search-related-bkcolor">';
		echo '<div class="search-results-div-inner">';
		echo '<table id="search-results"> <tbody>' . PHP_EOL;
		echo '<tr>';
		echo '  <th></th>';
		echo '  <th>' . _glt('Name') . '</th>';
		echo '  <th>' . _glt('Type') . '</th>';
		echo '  <th>' . _glt('Author') . '</th>';
		echo '  <th>' . _glt('Modified') . '</th>';
		echo '</tr>' . PHP_EOL;
		for ($iRowID = 0; $iRowID < $iRows; $iRowID++)
		{
			$Name 		= $aRows[$iRowID]['text'];
			$sAuthor 	= $aRows[$iRowID]['author'];
			$sAlias 	= $aRows[$iRowID]['alias'];
			$sAuthor	= htmlspecialchars($sAuthor);
			if ( strIsEmpty($Name) && !strIsEmpty($sAlias))
				$Name = $sAlias;
			else if ($aRows[$iRowID]['ntype'] === '19' && !strIsEmpty($sAlias))
				$Name = $sAlias;
			$NameInLink = LimitDisplayString($Name, 255);
			$Name = htmlspecialchars($Name);
			$Href = 'javascript:load_object(\'' . $aRows[$iRowID]['guid'] . '\',\'\',\'\',\'\',\'' . ConvertStringToParameter($NameInLink) . '\',\'' . $aRows[$iRowID]['imageurl'] . '\')';
			$sImage = '<img src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($aRows[$iRowID]['imageurl']) . '" alt=""/>';
			echo '<tr onclick="' . $Href . '">';
			echo '  <td class="search-results-image">' . $sImage . '</td>';
			echo '  <td class="search-results-name">' . $Name . '</td>';
			echo '  <td class="noWrapCell">' . $aRows[$iRowID]['type'] . '</td>';
			echo '  <td class="noWrapCell">' . $sAuthor . '</td>';
			echo '  <td class="noWrapCell">' . $aRows[$iRowID]['modified'] . '</td>';
			echo '</tr>' . PHP_EOL;
		}
		echo '</tbody></table>';
		echo '</div>';
		echo '</div>' . PHP_EOL;
	}
?>