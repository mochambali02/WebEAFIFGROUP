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
	if ($_SERVER["REQUEST_METHOD"] !== "POST")
	{
		$sErrorMsg = _glt('Direct navigation is not allowed');
		setResponseCode(405, $sErrorMsg);
		exit();
	}
	SafeStartSession();
	if (isset($_SESSION['authorized']) === false)
	{
		$sErrorMsg = _glt('Your session appears to have timed out');
		setResponseCode(440, $sErrorMsg);
		exit();
	}
	$sPeriod 			= SafeGetInternalArrayParameter($_POST, 'period');
	$sRecentDiscuss		= SafeGetInternalArrayParameter($_POST, 'recentdiscuss');
	$sRecentReview		= SafeGetInternalArrayParameter($_POST, 'recentreview');
	$sRecentDiag 		= SafeGetInternalArrayParameter($_POST, 'recentdiag');
	$sRecentElem		= SafeGetInternalArrayParameter($_POST, 'recentelem');
	$sResAllocActive 	= SafeGetInternalArrayParameter($_POST, 'resallocactive');
	$sResAllocToday 	= SafeGetInternalArrayParameter($_POST, 'resalloctoday');
	$sResAllocOverdue 	= SafeGetInternalArrayParameter($_POST, 'resallocoverdue');
	$sTestPassed 		= SafeGetInternalArrayParameter($_POST, 'testrecentpass');
	$sTestFailed		= SafeGetInternalArrayParameter($_POST, 'testrecentfail');
	$sTestDeferred 		= SafeGetInternalArrayParameter($_POST, 'testrecentdefer');
	$sTestNotChecked	= SafeGetInternalArrayParameter($_POST, 'testrecentnotchk');
	$sTestNotRun 		= SafeGetInternalArrayParameter($_POST, 'testnotrun');
	$sChangeVerified	= SafeGetInternalArrayParameter($_POST, 'changeverified');
	$sChangeRequested	= SafeGetInternalArrayParameter($_POST, 'changerequested');
	$sChangeCompleted	= SafeGetInternalArrayParameter($_POST, 'changecompleted');
	$sChangeNew			= SafeGetInternalArrayParameter($_POST, 'changenew');
	$sChangeIncomplete	= SafeGetInternalArrayParameter($_POST, 'changeincomplete');
	$sDefectVerified	= SafeGetInternalArrayParameter($_POST, 'defectverified');
	$sDefectRequested	= SafeGetInternalArrayParameter($_POST, 'defectrequested');
	$sDefectCompleted	= SafeGetInternalArrayParameter($_POST, 'defectcompleted');
	$sDefectNew			= SafeGetInternalArrayParameter($_POST, 'defectnew');
	$sDefectIncomplete	= SafeGetInternalArrayParameter($_POST, 'defectincomplete');
	$sIssueVerified		= SafeGetInternalArrayParameter($_POST, 'issueverified');
	$sIssueRequested	= SafeGetInternalArrayParameter($_POST, 'issuerequested');
	$sIssueCompleted	= SafeGetInternalArrayParameter($_POST, 'issuecompleted');
	$sIssueNew			= SafeGetInternalArrayParameter($_POST, 'issuenew');
	$sIssueIncomplete	= SafeGetInternalArrayParameter($_POST, 'issueincomplete');
	$sTaskVerified		= SafeGetInternalArrayParameter($_POST, 'taskverified');
	$sTaskRequested		= SafeGetInternalArrayParameter($_POST, 'taskrequested');
	$sTaskCompleted		= SafeGetInternalArrayParameter($_POST, 'taskcompleted');
	$sTaskNew			= SafeGetInternalArrayParameter($_POST, 'tasknew');
	$sTaskIncomplete	= SafeGetInternalArrayParameter($_POST, 'taskincomplete');
	$sEventRequested	= SafeGetInternalArrayParameter($_POST, 'eventrequested');
	$sEventCompleted	= SafeGetInternalArrayParameter($_POST, 'eventcompleted');
	$sEventNew			= SafeGetInternalArrayParameter($_POST, 'eventnew');
	$sEventIncomplete	= SafeGetInternalArrayParameter($_POST, 'eventincomplete');
	$sDecisionVerified	= SafeGetInternalArrayParameter($_POST, 'decisionverified');
	$sDecisionRequested	= SafeGetInternalArrayParameter($_POST, 'decisionrequested');
	$sDecisionCompleted	= SafeGetInternalArrayParameter($_POST, 'decisioncompleted');
	$sDecisionNew		= SafeGetInternalArrayParameter($_POST, 'decisionnew');
	$sDecisionIncomplete = SafeGetInternalArrayParameter($_POST, 'decisionincomplete');
	$sResponse	= '';
	$sMissingField = '';
	if (empty($sPeriod))
		$sMissingField .= 'Period, ';
	if (!empty($sMissingField))
	{
		$sMissingField = substr($sMissingField, 0, strlen($sMissingField)-2 );
		$sErrorMsg = str_replace('%FIELDS%', $sMissingField, _glt('The mandatory fields missing'));
		setResponseCode(400, $sErrorMsg);
		return $sErrorMsg;
	}
	$sWatchListOptions  = '';
	$sWatchListOptions .= ';period=' . $sPeriod;
	$sWatchListOptions .= ';recentdiscuss=' . GetCheckboxValue($sRecentDiscuss);
	$sWatchListOptions .= ';recentreview=' . GetCheckboxValue($sRecentReview);
	$sWatchListOptions .= ';recentdiag=' . GetCheckboxValue($sRecentDiag);
	$sWatchListOptions .= ';recentelem=' . GetCheckboxValue($sRecentElem);
	$sWatchListOptions .= ';resallocactive=' . GetCheckboxValue($sResAllocActive);
	$sWatchListOptions .= ';resalloctoday=' . GetCheckboxValue($sResAllocToday);
	$sWatchListOptions .= ';resallocoverdue=' . GetCheckboxValue($sResAllocOverdue);
	$sWatchListOptions .= ';testrecentpass=' . GetCheckboxValue($sTestPassed);
	$sWatchListOptions .= ';testrecentfail=' . GetCheckboxValue($sTestFailed);
	$sWatchListOptions .= ';testrecentdefer=' . GetCheckboxValue($sTestDeferred);
	$sWatchListOptions .= ';testrecentnotchk=' . GetCheckboxValue($sTestNotChecked);
	$sWatchListOptions .= ';testnotrun=' . GetCheckboxValue($sTestNotRun);
	$sWatchListOptions .= ';changeverified=' . GetCheckboxValue($sChangeVerified);
	$sWatchListOptions .= ';changerequested=' . GetCheckboxValue($sChangeRequested);
	$sWatchListOptions .= ';changecompleted=' . GetCheckboxValue($sChangeCompleted);
	$sWatchListOptions .= ';changenew=' . GetCheckboxValue($sChangeNew);
	$sWatchListOptions .= ';changeincomplete=' . GetCheckboxValue($sChangeIncomplete);
	$sWatchListOptions .= ';defectverified=' . GetCheckboxValue($sDefectVerified);
	$sWatchListOptions .= ';defectrequested=' . GetCheckboxValue($sDefectRequested);
	$sWatchListOptions .= ';defectcompleted=' . GetCheckboxValue($sDefectCompleted);
	$sWatchListOptions .= ';defectnew=' . GetCheckboxValue($sDefectNew);
	$sWatchListOptions .= ';defectincomplete=' . GetCheckboxValue($sDefectIncomplete);
	$sWatchListOptions .= ';issueverified=' . GetCheckboxValue($sIssueVerified);
	$sWatchListOptions .= ';issuerequested=' . GetCheckboxValue($sIssueRequested);
	$sWatchListOptions .= ';issuecompleted=' . GetCheckboxValue($sIssueCompleted);
	$sWatchListOptions .= ';issuenew=' . GetCheckboxValue($sIssueNew);
	$sWatchListOptions .= ';issueincomplete=' . GetCheckboxValue($sIssueIncomplete);
	$sWatchListOptions .= ';taskverified=' . GetCheckboxValue($sTaskVerified);
	$sWatchListOptions .= ';taskrequested=' . GetCheckboxValue($sTaskRequested);
	$sWatchListOptions .= ';taskcompleted=' . GetCheckboxValue($sTaskCompleted);
	$sWatchListOptions .= ';tasknew=' . GetCheckboxValue($sTaskNew);
	$sWatchListOptions .= ';taskincomplete=' . GetCheckboxValue($sTaskIncomplete);
	$sWatchListOptions .= ';eventrequested=' . GetCheckboxValue($sEventRequested);
	$sWatchListOptions .= ';eventcompleted=' . GetCheckboxValue($sEventCompleted);
	$sWatchListOptions .= ';eventnew=' . GetCheckboxValue($sEventNew);
	$sWatchListOptions .= ';eventincomplete=' . GetCheckboxValue($sEventIncomplete);
	$sWatchListOptions .= ';decisionverified=' . GetCheckboxValue($sDecisionVerified);
	$sWatchListOptions .= ';decisionrequested=' . GetCheckboxValue($sDecisionRequested);
	$sWatchListOptions .= ';decisioncompleted=' . GetCheckboxValue($sDecisionCompleted);
	$sWatchListOptions .= ';decisionnew=' . GetCheckboxValue($sDecisionNew);
	$sWatchListOptions .= ';decisionincomplete=' . GetCheckboxValue($sDecisionIncomplete);
	$sWatchListOptions .= ';';
	$sReturn = '';
	if ( count($_COOKIE) > 0 )
	{
		$iRetentionDays = (int)SafeGetInternalArrayParameter($_SESSION, 'cookie_retention', '365');
		$sModelName = SafeGetInternalArrayParameter($_SESSION, 'model_name', 'unknown');
		$sModelName = CookieNameEncoding($sModelName);
		$sUserName = SafeGetInternalArrayParameter($_SESSION, 'login_user', 'nouser');
		$sUserName = CookieNameEncoding($sUserName);
		$sCookieName = 'webea_' . $sModelName . '_' . $sUserName . '_watchlist_options';
		$sWatchListOptions = AbbreviateWLOptions($sWatchListOptions);
		setcookie($sCookieName, $sWatchListOptions, time() + (86400 * $iRetentionDays), '/');
		$sReturn = 'success: ' . _glt('watchlist saved to cookies');
	}
	else
	{
		$_SESSION['watchlist_options'] = $sWatchListOptions;
		$sReturn = 'success: ' . _glt('watchlist saved to session');
	}
	echo $sReturn;
	function GetCheckboxValue($sValue)
	{
		$sReturn = '0';
		if ($sValue === 'on')
			$sReturn = '1';
		return $sReturn;
	}
?>