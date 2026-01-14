<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if (!isset($aDiscussions)) {
	exit();
}
$sRootPath = dirname(__FILE__);
require_once $sRootPath . '/globals.php';
require_once $sRootPath . '/propertysections.php';
SafeStartSession();
$bUseAlternativeColour = false;
if (isset($_SESSION['authorized']) === false) {
	echo '<div id="main-content-empty">' . _glt('Your session appears to have timed out') . '</div>';
	setResponseCode(440);
	exit();
}
$bAddDiscussions = (IsSessionSettingTrue('add_discuss'));
$sAuthor 	= SafeGetInternalArrayParameter($_SESSION, 'login_fullname', '');
if (strIsEmpty($sAuthor))
	$sAuthor = 'Web User';
$sLoginGUID = SafeGetInternalArrayParameter($_SESSION, 'login_guid', '');
$sSessionReviewGUID = SafeGetInternalArrayParameter($_SESSION, 'review_session');
$iReviewCount = 0;
foreach ($aDiscussions as $disc) {
	if (!strIsEmpty($disc['reviewguid'])) {
		$iReviewCount++;
	}
}
if ((isset($sObjectType)) === false || ($sObjectType != 'ModelRoot')) {
	if ((isset($bIsMini) && (strIsTrue($bIsMini)))) {
		echo '<div id="discussion-mini-section" style="display:block;">';
	} else {
		if ((isset($sLinkType)) && ($sLinkType === 'props' || ($sLinkType === '')))
			echo '<div id="discussion-section" style="display:none;">';
		else
			echo '<div id="discussion-section" style="display:block;">';
	}
	echo '<div class="properties-header">Reviews</div>';
	echo '<div class="properties-content">';
	WriteReviewsSection($aDiscussions, $sObjectGUID, $bAddDiscussions, $sAuthor, $sLoginGUID, $sSessionReviewGUID);
	echo '</div>';
	echo '<div class="properties-header">Discussions</div>';
	echo '<div class="properties-content">';
	WriteDiscussionsSection($aDiscussions, $sObjectGUID, $bAddDiscussions, $sAuthor, $sLoginGUID, $sSessionReviewGUID);
	echo '</div>';
	echo '</div>' . PHP_EOL;
	if (isset($aAvatars)) {
		WriteAvatarCSS($aAvatars);
	}
}
echo BuildSystemOutputDataDIV();
