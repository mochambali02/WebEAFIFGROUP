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
	$sHyper = '';
	$sWatchlistClause = SafeGetInternalArrayParameter($_POST, 'clause');
	if ( strIsEmpty($sWatchlistClause) )
	{
		$sWatchlistClause = SafeGetInternalArrayParameter($_POST, 'hyper');
	}
	if (empty($sWatchlistClause))
	{
		setResponseCode(400);
		exit();
	}
	$aRows = array();
	$sOSLCErrorMsg 	= '';
	include('./data_api/get_watchlistresults.php');
	if ( strIsEmpty($sOSLCErrorMsg) )
	{
		$iRows = count($aRows);
		if ($iRows <= 0)
		{
			echo '<div class="search-results-empty">' . _glt('No Results Found') . '</div>';
			exit();
		}
		else
		{
			echo '<div id="watchlist-results-count">' . $iRows . '</div>';
			echo '<div class="search-results-div-inner">';
			echo '<table id="search-results"> <tbody>' . PHP_EOL;
			echo '<tr>';
			echo '  <th></th>';
			echo '  <th>' . _glt('Type') . '</th>';
			echo '  <th>' . _glt('Name') . '</th>';
			echo '  <th>' . _glt('Author') . '</th>';
			echo '  <th>' . _glt('Modified') . '</th>';
			echo '</tr>';
			for ($iRowID = 0; $iRowID < $iRows; $iRowID++)
			{
				$sName 		= $aRows[$iRowID]['text'];
				$sAuthor 	= $aRows[$iRowID]['author'];
				$sAlias 	= $aRows[$iRowID]['alias'];
				$sAuthor	= htmlspecialchars($sAuthor);
				if (strIsEmpty($sName) && !strIsEmpty($sAlias))
					$sName = $sAlias;
				else if ($aRows[$iRowID]['ntype'] === '19' && !strIsEmpty($sAlias))
					$sName = $sAlias;
				$sNameInLink = LimitDisplayString($sName, 255);
				$sName = htmlspecialchars($sName);
				$sHref = 'javascript:load_object(\'' . $aRows[$iRowID]['guid'] . '\',\'\',\'\',\'\',\'' . ConvertStringToParameter($sNameInLink) . '\',\'' . $aRows[$iRowID]['imageurl'] . '\')';
				$sImage = '<img src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($aRows[$iRowID]['imageurl']) . '" alt=""/>';
				echo '<tr onclick="' . $sHref . '">';
				echo '  <td class="search-results-image">' . $sImage . '</td>';
				echo '  <td class="noWrapCell">' . $aRows[$iRowID]['type'] . '</td>';
				echo '  <td class="search-results-name">' . $sName . '</td>';
				echo '  <td class="noWrapCell">' . $sAuthor . '</td>';
				echo '  <td class="noWrapCell">' . $aRows[$iRowID]['modified'] . '</td>';
				echo '</tr>';
			}
			echo '</tbody></table>' . PHP_EOL;
			echo '</div>';
		}
	}
	else
	{
		echo '<div class="search-results-empty">' . $sOSLCErrorMsg . '</div>';
	}
?>