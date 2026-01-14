<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if (!isset($webea_page_parent_index) && $_SERVER["REQUEST_METHOD"] !== "POST") {
	exit();
}
$sRootPath = dirname(__FILE__);
require_once $sRootPath . '/globals.php';
SafeStartSession();
if (isset($_SESSION['authorized']) === false) {
	$sErrorMsg = _glt('Your session appears to have timed out');
	return $sErrorMsg;
}
$sReviewSession = isset($_POST['reviewguid']) ? $_POST['reviewguid'] : '';
$sReviewSessionName = isset($_POST['reviewname']) ? $_POST['reviewname'] : '';
if (strIsEmpty($sReviewSession)) {
	$sReviewSession = SafeGetInternalArrayParameter($_SESSION, 'review_session');
	$sReviewSessionName = SafeGetInternalArrayParameter($_SESSION, 'review_session_name');
}
$sDisplayReviewName = LimitDisplayString($sReviewSessionName, 30);
echo '<div style="width: 100%; display: table;">';
echo '    <div style="display: table-row">';
echo '        <div style="width: 110px; display: table-cell; vertical-align: top;">&nbsp;</div>';
echo '        <div class="statusbar-item-about-cell">';
echo '            <div class="statusbar-item-about" onclick="OnShowAboutPage(\'' . g_csWebEAVersion . '\')">';
echo '                <img src="images/spriteplaceholder.png" class="mainsprite-about" alt="A" title="' . _glt('Show About screen') . '">';
echo '            </div>';
echo '        </div>';
if (isset($_SESSION['ios_scroll'])) {
	$sDevice = '';
	$sScrollState = '';
	$sScrollClass = '';
	$bIOSScroll = SafeGetInternalArrayParameter($_SESSION, 'ios_scroll');
	$iPod    = stripos($_SERVER['HTTP_USER_AGENT'], "iPod");
	$iPhone  = stripos($_SERVER['HTTP_USER_AGENT'], "iPhone");
	$iPad    = stripos($_SERVER['HTTP_USER_AGENT'], "iPad");
	if ($iPod)
		$sDevice = "iPod";
	else if ($iPhone)
		$sDevice = "iPhone";
	else if ($iPad)
		$sDevice = "iPad";
	if ($bIOSScroll) {
		$sScrollState = 'On';
		$sScrollClass = 'propsprite-greendot';
	} else if ($bIOSScroll === false) {
		$sScrollState = 'Off';
		$sScrollClass = 'propsprite-reddot';
	} else {
		$sScrollState = 'Off';
		$sScrollClass = 'propsprite-reddot';
	}
	echo '<div class="statusbar-scroll-mode-cell">';
	echo '<div class="statusbar-item-scroll-mode" onclick="toggle_scrolling()">';
	echo '<img alt="" src="images/spriteplaceholder.png" id="scroll-mode-icon" class="' . $sScrollClass . '"/>&nbsp;';
	echo '<div style="display: inline-block;padding-right: 4px;">' . $sDevice . ' Diagram Scroll:</div>';
	echo '<div id="scroll-mode">';
	echo $sScrollState;
	echo '</div>';
	echo '</div>';
	echo '</div>';
}
if (!strIsEmpty($sReviewSession)) {
	echo '    <div class="statusbar-item-review-cell">';
	echo '		<div class="statusbar-item-review" onclick="load_object(\'' . $sReviewSession . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sReviewSessionName) . '\',\'images/element16/review.png\')">';
	echo '		<img alt="" src="images/spriteplaceholder.png" class="propsprite-review"/>&nbsp;';
	echo 		_glt('Review') . ' "' .  	   htmlspecialchars($sDisplayReviewName) . '"';
	echo '		</div>';
	echo '    </div>';
}
echo '    </div>';
echo '</div>';
echo PHP_EOL;
