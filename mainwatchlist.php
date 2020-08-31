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
	$aWatchList = array();
	$sWatchlistOptions = GetWatchListOptionString();
	echo '<div id="main-watchlist">';
	echo '	<div id="watchlist-layout">';
	echo '	    <div id="watchlist-header">';
	echo '			<div id="watchlist-headerimage"><img src="images/spriteplaceholder.png" class="propsprite-watchlist" alt=""/></div>';
	echo '			<div id="watchlist-headertext">' . _glt('Watchlist summary') . '</div>';
	echo '			<div id="watchlist-headertextbutton"><input id="watchlist-config-button" type="button" value="&#160;" onclick="load_object(\'watchlistconfig\',\'\',\'\',\'\',\'' . _glt('Watchlist configuration') . '\',\'images/element16/watchlistconfig.png\')" title="' . _glt('Configure the Watchlist') . '"/></div>';
	echo '		</div>';
	$sOSLCErrorMsg 	= '';
	include('./data_api/get_watchlist.php');
	if ( strIsEmpty($sOSLCErrorMsg) )
	{
		$iRecentDiscuss 	= SafeGetArrayItem1DimInt($aWatchList, 'recentdiscuss');
		$iRecentReviews 	= SafeGetArrayItem1DimInt($aWatchList, 'recentreview');
		$iRecentDiagrams 	= SafeGetArrayItem1DimInt($aWatchList, 'recentdiag');
		$iRecentElements 	= SafeGetArrayItem1DimInt($aWatchList, 'recentelem');
		$iResAllocActive	= SafeGetArrayItem1DimInt($aWatchList, 'resallocactive');
		$iResAllocToday		= SafeGetArrayItem1DimInt($aWatchList, 'resalloctoday');
		$iResAllocOverdue	= SafeGetArrayItem1DimInt($aWatchList, 'resallocoverdue');
		$iTestPassed	 	= SafeGetArrayItem1DimInt($aWatchList, 'testrecentpass');
		$iTestFailed	 	= SafeGetArrayItem1DimInt($aWatchList, 'testrecentfail');
		$iTestDeferred	 	= SafeGetArrayItem1DimInt($aWatchList, 'testrecentdefer');
		$iTestNotChecked 	= SafeGetArrayItem1DimInt($aWatchList, 'testrecentnotchk');
		$iTestNotRun	 	= SafeGetArrayItem1DimInt($aWatchList, 'testnotrun');
		$iChangeVerified	= SafeGetArrayItem1DimInt($aWatchList, 'changeverified');
		$iChangeRequested	= SafeGetArrayItem1DimInt($aWatchList, 'changerequested');
		$iChangeCompleted 	= SafeGetArrayItem1DimInt($aWatchList, 'changecompleted');
		$iChangeNew		 	= SafeGetArrayItem1DimInt($aWatchList, 'changenew');
		$iChangeIncomplete 	= SafeGetArrayItem1DimInt($aWatchList, 'changeincomplete');
		$iDefectVerified	= SafeGetArrayItem1DimInt($aWatchList, 'defectverified');
		$iDefectRequested	= SafeGetArrayItem1DimInt($aWatchList, 'defectrequested');
		$iDefectCompleted 	= SafeGetArrayItem1DimInt($aWatchList, 'defectcompleted');
		$iDefectNew		 	= SafeGetArrayItem1DimInt($aWatchList, 'defectnew');
		$iDefectIncomplete 	= SafeGetArrayItem1DimInt($aWatchList, 'defectincomplete');
		$iIssueVerified		= SafeGetArrayItem1DimInt($aWatchList, 'issueverified');
		$iIssueRequested	= SafeGetArrayItem1DimInt($aWatchList, 'issuerequested');
		$iIssueCompleted 	= SafeGetArrayItem1DimInt($aWatchList, 'issuecompleted');
		$iIssueNew		 	= SafeGetArrayItem1DimInt($aWatchList, 'issuenew');
		$iIssueIncomplete 	= SafeGetArrayItem1DimInt($aWatchList, 'issueincomplete');
		$iTaskVerified	 	= SafeGetArrayItem1DimInt($aWatchList, 'taskverified');
		$iTaskRequested	 	= SafeGetArrayItem1DimInt($aWatchList, 'taskrequested');
		$iTaskCompleted 	= SafeGetArrayItem1DimInt($aWatchList, 'taskcompleted');
		$iTaskNew		 	= SafeGetArrayItem1DimInt($aWatchList, 'tasknew');
		$iTaskIncomplete 	= SafeGetArrayItem1DimInt($aWatchList, 'taskincomplete');
		$iEventRequested	= SafeGetArrayItem1DimInt($aWatchList, 'eventrequested');
		$iEventCompleted 	= SafeGetArrayItem1DimInt($aWatchList, 'eventcompleted');
		$iEventNew		 	= SafeGetArrayItem1DimInt($aWatchList, 'eventnew');
		$iEventIncomplete 	= SafeGetArrayItem1DimInt($aWatchList, 'eventincomplete');
		$iDecisionVerified	= SafeGetArrayItem1DimInt($aWatchList, 'decisionverified');
		$iDecisionRequested	= SafeGetArrayItem1DimInt($aWatchList, 'decisionrequested');
		$iDecisionCompleted	= SafeGetArrayItem1DimInt($aWatchList, 'decisioncompleted');
		$iDecisionNew		= SafeGetArrayItem1DimInt($aWatchList, 'decisionnew');
		$iDecisionIncomplete= SafeGetArrayItem1DimInt($aWatchList, 'decisionincomplete');
		$sRecentDiscussCrit	= SafeGetArrayItem1Dim($aWatchList, 'recentdiscusscrit');
		$sRecentDiagramsCrit= SafeGetArrayItem1Dim($aWatchList, 'recentdiagcrit');
		$sRecentElementsCrit= SafeGetArrayItem1Dim($aWatchList, 'recentelemcrit');
		$sRecentReviewsCrit = SafeGetArrayItem1Dim($aWatchList, 'recentreviewcrit');
		$sResAllocActiveCrit= SafeGetArrayItem1Dim($aWatchList, 'resallocactivecrit');
		$sResAllocTodayCrit	= SafeGetArrayItem1Dim($aWatchList, 'resalloctodaycrit');
		$sResAllocOverdueCrit= SafeGetArrayItem1Dim($aWatchList, 'resallocoverduecrit');
		$sTestPassedCrit 	= SafeGetArrayItem1Dim($aWatchList, 'testrecentpasscrit');
		$sTestFailedCrit 	= SafeGetArrayItem1Dim($aWatchList, 'testrecentfailcrit');
		$sTestDeferredCrit 	= SafeGetArrayItem1Dim($aWatchList, 'testrecentdefercrit');
		$sTestNotCheckedCrit= SafeGetArrayItem1Dim($aWatchList, 'testrecentnotchkcrit');
		$sTestNotRunCrit 	= SafeGetArrayItem1Dim($aWatchList, 'testnotruncrit');
		$sChangeVerifiedCrit= SafeGetArrayItem1Dim($aWatchList, 'changeverifiedcrit');
		$sChangeRequestedCrit= SafeGetArrayItem1Dim($aWatchList, 'changerequestedcrit');
		$sChangeCompletedCrit= SafeGetArrayItem1Dim($aWatchList, 'changecompletedcrit');
		$sChangeNewCrit	 	= SafeGetArrayItem1Dim($aWatchList, 'changenewcrit');
		$sChangeIncompleteCrit = SafeGetArrayItem1Dim($aWatchList, 'changeincompletecrit');
		$sDefectVerifiedCrit= SafeGetArrayItem1Dim($aWatchList, 'defectverifiedcrit');
		$sDefectRequestedCrit= SafeGetArrayItem1Dim($aWatchList, 'defectrequestedcrit');
		$sDefectCompletedCrit= SafeGetArrayItem1Dim($aWatchList, 'defectcompletedcrit');
		$sDefectNewCrit	 	= SafeGetArrayItem1Dim($aWatchList, 'defectnewcrit');
		$sDefectIncompleteCrit = SafeGetArrayItem1Dim($aWatchList, 'defectincompletecrit');
		$sIssueVerifiedCrit	= SafeGetArrayItem1Dim($aWatchList, 'issueverifiedcrit');
		$sIssueRequestedCrit= SafeGetArrayItem1Dim($aWatchList, 'issuerequestedcrit');
		$sIssueCompletedCrit= SafeGetArrayItem1Dim($aWatchList, 'issuecompletedcrit');
		$sIssueNewCrit	 	= SafeGetArrayItem1Dim($aWatchList, 'issuenewcrit');
		$sIssueIncompleteCrit= SafeGetArrayItem1Dim($aWatchList, 'issueincompletecrit');
		$sTaskVerifiedCrit 	= SafeGetArrayItem1Dim($aWatchList, 'taskverifiedcrit');
		$sTaskRequestedCrit	= SafeGetArrayItem1Dim($aWatchList, 'taskrequestedcrit');
		$sTaskCompletedCrit	= SafeGetArrayItem1Dim($aWatchList, 'taskcompletedcrit');
		$sTaskNewCrit	 	= SafeGetArrayItem1Dim($aWatchList, 'tasknewcrit');
		$sTaskIncompleteCrit= SafeGetArrayItem1Dim($aWatchList, 'taskincompletecrit');
		$sEventRequestedCrit= SafeGetArrayItem1Dim($aWatchList, 'eventrequestedcrit');
		$sEventCompletedCrit = SafeGetArrayItem1Dim($aWatchList, 'eventcompletedcrit');
		$sEventNewCrit	 	= SafeGetArrayItem1Dim($aWatchList, 'eventnewcrit');
		$sEventIncompleteCrit= SafeGetArrayItem1Dim($aWatchList, 'eventincompletecrit');
		$sDecisionVerifiedCrit = SafeGetArrayItem1Dim($aWatchList, 'decisionverifiedcrit');
		$sDecisionRequestedCrit = SafeGetArrayItem1Dim($aWatchList, 'decisionrequestedcrit');
		$sDecisionCompletedCrit = SafeGetArrayItem1Dim($aWatchList, 'decisioncompletedcrit');
		$sDecisionNewCrit	= SafeGetArrayItem1Dim($aWatchList, 'decisionnewcrit');
		$sDecisionIncompleteCrit = SafeGetArrayItem1Dim($aWatchList, 'decisionincompletecrit');
		if ( $iRecentDiscuss 	> 0 || $iRecentDiagrams > 0 || $iRecentElements 	> 0 || $iRecentReviews 		> 0 ||
			 $iResAllocActive 	> 0 || $iResAllocToday 	> 0 || $iResAllocOverdue 	> 0 ||
			 $iTestPassed 		> 0 || $iTestFailed 	> 0 || $iTestDeferred 		> 0 || $iTestNotChecked		> 0 || $iTestNotRun 		> 0 ||
			 $iChangeVerified 	> 0 || $iChangeRequested> 0 || $iChangeCompleted 	> 0 || $iChangeNew			> 0 || $iChangeIncomplete 	> 0 ||
			 $iDefectVerified 	> 0 || $iDefectRequested> 0 || $iDefectCompleted 	> 0 || $iDefectNew			> 0 || $iDefectIncomplete 	> 0 ||
			 $iIssueVerified 	> 0 || $iIssueRequested	> 0 || $iIssueCompleted 	> 0 || $iIssueNew			> 0 || $iIssueIncomplete 	> 0 ||
			 $iTaskVerified 	> 0 || $iTaskRequested	> 0 || $iTaskCompleted 		> 0 || $iTaskNew			> 0 || $iTaskIncomplete 	> 0 ||
									   $iEventRequested > 0 || $iEventCompleted 	> 0 || $iEventNew			> 0 || $iEventIncomplete 	> 0 ||
			 $iDecisionVerified	> 0 || $iDecisionRequested > 0 || $iDecisionCompleted > 0 || $iDecisionNew		> 0 || $iDecisionIncomplete > 0 )
		{
			echo '<div id="watchlist-body">';
			echo '<table id="watchlist-table"><tbody>';
			if ( $iRecentDiscuss 	> 0 ||
				 $iRecentDiagrams 	> 0 ||
				 $iRecentElements 	> 0 ||
				 $iRecentReviews 	> 0 )
			{
				echo '<tr class="watchlist-line">';
				echo '<td class="watchlist-option-hdr">' . _glt('Recent') . '</td>';
				echo '</tr>';
				WriteWatchListItem($iRecentDiscuss, _glt('%NUM% recent discussion%S%'), $sRecentDiscussCrit);
				WriteWatchListItem($iRecentReviews, _glt('%NUM% recently review%S%'), $sRecentReviewsCrit);
				WriteWatchListItem($iRecentDiagrams, _glt('%NUM% recently modified diagram%S%'), $sRecentDiagramsCrit);
				WriteWatchListItem($iRecentElements, _glt('%NUM% recently modified element%S%'), $sRecentElementsCrit);
			}
			if ( $iResAllocActive 	> 0 ||
				 $iResAllocToday 	> 0 ||
				 $iResAllocOverdue 	> 0 )
			{
				echo '<tr class="watchlist-line">';
				echo '<td class="watchlist-option-hdr">' . _glt('Resource Allocations') . '&nbsp;&nbsp;&nbsp;<img alt="" src="images/spriteplaceholder.png" class="propsprite-resalloc"/></td>';
				echo '</tr>';
				WriteWatchListItem($iResAllocActive, _glt('%NUM% active resource task%S%'), $sResAllocActiveCrit);
				WriteWatchListItem($iResAllocToday, _glt('%NUM% resource task%S% ending today'), $sResAllocTodayCrit);
				WriteWatchListItem($iResAllocOverdue, _glt('%NUM% resource task%S% overdue'), $sResAllocOverdueCrit);
			}
			if ( $iTestPassed	 	> 0 ||
				 $iTestFailed 		> 0 ||
				 $iTestDeferred 	> 0 ||
				 $iTestNotChecked 	> 0 ||
				 $iTestNotRun 		> 0 )
			{
				echo '<tr class="watchlist-line">';
				echo '<td class="watchlist-option-hdr">' . _glt('Tests') . '&nbsp;&nbsp;&nbsp;<img alt="" src="images/spriteplaceholder.png" class="propsprite-test"/></td>';
				echo '</tr>';
				WriteWatchListItem($iTestPassed, _glt('%NUM% Test%S% recently passed'), $sTestPassedCrit);
				WriteWatchListItem($iTestFailed, _glt('%NUM% Test%S% recently failed'), $sTestFailedCrit);
				WriteWatchListItem($iTestDeferred, _glt('%NUM% Test%S% recently deferred'), $sTestDeferredCrit);
				WriteWatchListItem($iTestNotChecked, _glt('%NUM% Test%S% recently not checked'), $sTestNotCheckedCrit);
				WriteWatchListItem($iTestNotRun, _glt('%NUM% Test%S% not run'), $sTestNotRunCrit);
			}
			if ( $iChangeVerified	> 0 ||
				 $iChangeRequested 	> 0 ||
				 $iChangeCompleted 	> 0 ||
				 $iChangeNew 		> 0 ||
				 $iChangeIncomplete > 0 )
			{
				echo '<tr class="watchlist-line">';
				echo '<td class="watchlist-option-hdr">' . _glt('Changes') . '&nbsp;&nbsp;&nbsp;<img alt="" src="images/spriteplaceholder.png" class="propsprite-change"/></td>';
				echo '</tr>';
				WriteWatchListItem($iChangeVerified, _glt('%NUM% verified change%S%'), $sChangeVerifiedCrit);
				WriteWatchListItem($iChangeRequested, _glt('%NUM% recently requested change%S%'), $sChangeRequestedCrit);
				WriteWatchListItem($iChangeCompleted, _glt('%NUM% recently completed change%S%'), $sChangeCompletedCrit);
				WriteWatchListItem($iChangeNew, _glt('%NUM% new change%S%'), $sChangeNewCrit);
				WriteWatchListItem($iChangeIncomplete, _glt('%NUM% incomplete change%S%'), $sChangeIncompleteCrit);
			}
			if ( $iDefectVerified	> 0 ||
				 $iDefectRequested 	> 0 ||
				 $iDefectCompleted 	> 0 ||
				 $iDefectNew 		> 0 ||
				 $iDefectIncomplete > 0 )
			{
				echo '<tr class="watchlist-line">';
				echo '<td class="watchlist-option-hdr">' . _glt('Defects') . '&nbsp;&nbsp;&nbsp;<img alt="" src="images/spriteplaceholder.png" class="propsprite-defect"/></td>';
				echo '</tr>';
				WriteWatchListItem($iDefectVerified, _glt('%NUM% verified defect%S%'), $sDefectVerifiedCrit);
				WriteWatchListItem($iDefectRequested, _glt('%NUM% recently reported defect%S%'), $sDefectRequestedCrit);
				WriteWatchListItem($iDefectCompleted, _glt('%NUM% recently resolved defect%S%'), $sDefectCompletedCrit);
				WriteWatchListItem($iDefectNew, _glt('%NUM% new defect%S%'), $sDefectNewCrit);
				WriteWatchListItem($iDefectIncomplete, _glt('%NUM% incomplete defect%S%'), $sDefectIncompleteCrit);
			}
			if ( $iIssueVerified	> 0 ||
				 $iIssueRequested 	> 0 ||
				 $iIssueCompleted 	> 0 ||
				 $iIssueNew 		> 0 ||
				 $iIssueIncomplete 	> 0 )
			{
				echo '<tr class="watchlist-line">';
				echo '<td class="watchlist-option-hdr">' . _glt('Issues') . '&nbsp;&nbsp;&nbsp;<img alt="" src="images/spriteplaceholder.png" class="propsprite-issue"/></td>';
				echo '</tr>';
				WriteWatchListItem($iIssueVerified, _glt('%NUM% verified issue%S%'), $sIssueVerifiedCrit);
				WriteWatchListItem($iIssueRequested, _glt('%NUM% recently reported issue%S%'), $sIssueRequestedCrit);
				WriteWatchListItem($iIssueCompleted, _glt('%NUM% recently resolved issue%S%'), $sIssueCompletedCrit);
				WriteWatchListItem($iIssueNew, _glt('%NUM% new issue%S%'), $sIssueNewCrit);
				WriteWatchListItem($iIssueIncomplete, _glt('%NUM% incomplete issue%S%'), $sIssueIncompleteCrit);
			}
			if ( $iTaskVerified		> 0 ||
				 $iTaskRequested 	> 0 ||
				 $iTaskCompleted 	> 0 ||
				 $iTaskNew 			> 0 ||
				 $iTaskIncomplete 	> 0 )
			{
				echo '<tr class="watchlist-line">';
				echo '<td class="watchlist-option-hdr">' . _glt('Tasks') . '&nbsp;&nbsp;&nbsp;<img alt="" src="images/spriteplaceholder.png" class="propsprite-task"/></td>';
				echo '</tr>';
				WriteWatchListItem($iTaskVerified, _glt('%NUM% verified task%S%'), $sTaskVerifiedCrit);
				WriteWatchListItem($iTaskRequested, _glt('%NUM% recently requested task%S%'), $sTaskRequestedCrit);
				WriteWatchListItem($iTaskCompleted, _glt('%NUM% recently completed task%S%'), $sTaskCompletedCrit);
				WriteWatchListItem($iTaskNew, _glt('%NUM% new task%S%'), $sTaskNewCrit);
				WriteWatchListItem($iTaskIncomplete, _glt('%NUM% incomplete task%S%'), $sTaskIncompleteCrit);
			}
			if ( $iEventRequested 	> 0 ||
				 $iEventCompleted 	> 0 ||
				 $iEventNew 		> 0 ||
				 $iEventIncomplete 	> 0 )
			{
				echo '<tr class="watchlist-line">';
				echo '<td class="watchlist-option-hdr">' . _glt('Events') . '&nbsp;&nbsp;&nbsp;<img alt="" src="images/spriteplaceholder.png" class="propsprite-event"/></td>';
				echo '</tr>';
				WriteWatchListItem($iEventRequested, _glt('%NUM% recently requested event%S%'), $sEventRequestedCrit);
				WriteWatchListItem($iEventNew, _glt('%NUM% high priority event%S%'), $sEventNewCrit);
				WriteWatchListItem($iEventIncomplete, _glt('%NUM% incomplete event%S%'), $sEventIncompleteCrit);
			}
			if ( $iDecisionVerified		> 0 ||
				 $iDecisionRequested 	> 0 ||
				 $iDecisionCompleted 	> 0 ||
				 $iDecisionNew 			> 0 ||
				 $iDecisionIncomplete 	> 0 )
			{
				echo '<tr class="watchlist-line">';
				echo '<td class="watchlist-option-hdr">' . _glt('Decisions') . '&nbsp;&nbsp;&nbsp;<img alt="" src="images/spriteplaceholder.png" class="propsprite-decision"/></td>';
				echo '</tr>';
				WriteWatchListItem($iDecisionVerified, _glt('%NUM% verified decision%S%'), $sDecisionVerifiedCrit);
				WriteWatchListItem($iDecisionRequested, _glt('%NUM% recently requested decision%S%'), $sDecisionRequestedCrit);
				WriteWatchListItem($iDecisionCompleted, _glt('%NUM% recently completed decision%S%'), $sDecisionCompletedCrit);
				WriteWatchListItem($iDecisionNew, _glt('%NUM% new decision%S%'), $sDecisionNewCrit);
				WriteWatchListItem($iDecisionIncomplete, _glt('%NUM% incomplete decision%S%'), $sDecisionIncompleteCrit);
			}
			echo '</tbody></table>';
			echo '</div>';
		}
		else
		{
			echo '<div class="watchlist-nodata">' . _glt('No watchlist activity found') . '</div>';
		}
	}
	else
	{
		if ( $sOSLCErrorMsg === '400 - Specify valid Watch List content' )
		{
			echo '<div class="watchlist-nodata">' . _glt('No watchlist items configured') . '</div>';
		}
		else
		{
			echo '<div class="watchlist-nodata">' . $sOSLCErrorMsg . '</div>';
		}
	}
	echo '<div id="watchlist-footer">';
	echo '	<input class="webea-main-styled-button watchlist-ok" type="button" value="' . _glt('OK') . '" onclick="MoveToPrevItemInNavigationHistory()"/>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	function WriteWatchListItem($i, $sText, $sClause)
	{
		if ( !isset($sClause) )
		{
			$sClause = '';
		}
		if ( $i > 0 )
		{
			if ( $i > 1 )
			{
				$sText = str_replace('%S%', 's', $sText);
				$sText = str_replace('%SS%', 'ies', $sText);
			}
			else
			{
				$sText = str_replace('%S%', '', $sText);
				$sText = str_replace('%SS%', 'y', $sText);
			}
			$sCount = strval($i);
			if ( !strIsEmpty($sClause) )
			{
				$sClause = preg_replace('/\s+/', '', $sClause);
				$sCount = '<a class="w3-link" onclick="OnClickWatchlistItem(\'' . $sClause . '\',\'' . _glt('Watchlist results') . '\')">' . $sCount . '</a>';
			}
			$sText = str_replace('%NUM%', $sCount, $sText);
			echo '<tr class="watchlist-line">';
			echo '<td class="watchlist-label field-label-vert-align-top">' . $sText . '</td>';
			echo '</tr>';
		}
	}
?>