<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	$sRootPath = dirname(__FILE__);
	require_once $sRootPath . '/globals.php';
	SafeStartSession();
	if ($_SERVER["REQUEST_METHOD"] !== "POST")
	{
		$sErrorMsg = _glt('Direct navigation is not allowed');
		setResponseCode(405, $sErrorMsg);
		exit();
	}
	if (isset($_SESSION['authorized']) === false)
	{
		echo '<div id="main-content-empty">' . _glt('Your session appears to have timed out') . '</div>';
		setResponseCode(440);
		exit();
	}
	$sHTMLDoc = '';
	include('./data_api/get_linkeddoc.php');
	$sMainClass = '';
	if (IsSessionSettingTrue('show_browser'))
	{
		$sMainClass = ' class="show-browser"';
	}
	if (strIsEmpty($sHTMLDoc))
	{
		echo '<div id="linked-document-section-empty"' . $sMainClass . '>' . _glt('No content') . '</div>';
	}
	else
	{
		$sHTMLDoc = trim($sHTMLDoc);
		if ( substr($sHTMLDoc, 0, 9) === '<![CDATA[' )
			$sHTMLDoc = substr($sHTMLDoc, 9);
		if ( substr($sHTMLDoc, -3) === ']]>' )
			$sHTMLDoc = substr($sHTMLDoc, 0, -3);
		$sHTMLDoc = ConvertHyperlinksInLinkedDoc($sHTMLDoc);
		echo '<div id="linked-document-section"' . $sMainClass . '>';
		echo '<div class="linked-document">' . $sHTMLDoc . '</div>';
		echo '</div>' . PHP_EOL;
	}
?>