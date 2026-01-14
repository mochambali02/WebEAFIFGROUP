<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/globals.php';
require_once __DIR__ . '/htmlpurifier.php';
SafeStartSession();
CheckAuthorisation();
if (!IsHTTPSuccess(http_response_code())) {
	exit();
}
include('./data_api/get_collaborate.php');
$sHyper	= SafeGetInternalArrayParameter($_POST, 'hyper', '');
$sLinkType	= SafeGetInternalArrayParameter($_POST, 'linktype', 'chat');
if (strIsEmpty($sLinkType)) {
	$sLinkType = SafeGetInternalArrayParameter($_SESSION, 'collaborate_tab', 'chat');
}
$_SESSION['collaborate_tab'] = $sLinkType;
BuildOSLCConnectionString();
WriteCollabHeader($sLinkType);
WriteCollabContents($sLinkType);
function WriteCollabHeader($sLinkType)
{
	$aTabs = [
		['id' => 'reviews',	'label' => _glt('Reviews')],
		['id' => 'discussions',	'label' => _glt('Discussions')],
		['id' => 'comments',	'label' => _glt('Comments')],
		['id' => 'chat',	'label' => _glt('Chat')],
		['id' => 'modelmail',	'label' => _glt('Mail')]
	];
	echo '<div class="collab-header">';
	echo WriteCollabHeaderDropdown($sLinkType, $aTabs);
	echo WriteCollabHeaderTabs($sLinkType, $aTabs);
	echo '<div id="collab-header-options">';
	echo WriteCollabContextMenu($sLinkType);
	echo '</div>';
	echo '</div>';
}
function WriteCollabContents($sLinkType)
{
	switch ($sLinkType) {
		case 'reviews':
			echo WriteCollabReviewDetails();
			echo WriteCollabReviewHistory();
			break;
		case 'discussions':
			echo WriteCollabDiscussionDetails();
			echo WriteCollabDiscussionHistory();
			break;
		case 'comments':
			echo WriteCollabCommentDetails();
			echo WriteCollabCommentHistory();
			break;
		case 'chat':
			include('chat.php');
			echo WriteCollabChatHistory();
			break;
		case 'modelmail':
			echo WriteCollabMailPreview();
			echo WriteCollabMailList('inbox', []);
			break;
		case 'modelmailsent':
			echo WriteCollabMailPreview();
			echo WriteCollabMailList('sent', []);
			break;
		default:
			break;
	}
}
function WriteCollabHeaderDropdown($sLinkType, $aTabs)
{
	$sSelectedTabHTML = '';
	$sContextMenuHTML = '';
	$sContextMenuHTML .= '<div id="collab-tab-contextmenu" class="contextmenu hide-menu">';
	$sContextMenuHTML .= '<div class="contextmenu-items">';
	foreach ($aTabs as $aTab) {
		$sArrorVisibility = 'hidden';
		if ($sLinkType === 'modelmailsent')
			$sLinkType = 'modelmail';
		if ($aTab['id'] === $sLinkType) {
			$sSelectedTabHTML = '<button id="collab-tab-' . $sLinkType . '" class="collab-tab" onclick="ShowMenu(this)">' . _glt($aTab['label']) . '  <div class="collab-icon-chevron icon-blue-chevron icon16"></div></button>';
		}
		$sContextMenuHTML .=  '<div class="contextmenu-item small-margin" onclick="LoadObject(\'collaborate\',\'\',\'' . $aTab['id'] . '\',\'\',\'' . _glt('Collaborate') . '\',\'propsprite-discussion\',false)">';
		if ($aTab['id'] === $sLinkType)
			$sContextMenuHTML .= '<a class="contextmenu-tick icon16 tick-icon"></a>';
		else
			$sContextMenuHTML .= '<a class="contextmenu-tick blank-icon"></a>';
		$sContextMenuHTML .= $aTab['label'];
		$sContextMenuHTML .= '</div>';
	}
	$sContextMenuHTML .= '</div>';
	$sContextMenuHTML .= '</div>';
	$sHTML = '';
	$sHTML .= '<div id="collab-header-selection-button">';
	$sHTML .= $sSelectedTabHTML;
	$sHTML .= $sContextMenuHTML;
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteCollabHeaderTabs($sLinkType, $aTabs)
{
	$sHTML = '';
	$sHTML .= '<div id="collab-header-selection-tabs">';
	if ($sLinkType === 'modelmailsent')
		$sLinkType = 'modelmail';
	foreach ($aTabs as $aTab) {
		$sArrorVisibility = 'hidden';
		if ($aTab['id'] === $sLinkType) {
			$sArrorVisibility = '';
		}
		$sHTML .= '<button id="collab-tab-' . $aTab['id'] . '" class="collab-tab" onclick="LoadObject(\'collaborate\',\'\',\'' . $aTab['id'] . '\',\'\',\'' . _glt('Collaborate') . '\',\'propsprite-discussion\',false)"><div class="up-arrow-collab" ' . $sArrorVisibility . '><a></a></div>' . $aTab['label'] . '</button>';
	}
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteCollabContextMenu($sLinkType)
{
	$sHTML = '';
	switch ($sLinkType) {
		case 'chat':
			break;
		case 'modelmail':
			$sHTML .= WriteCollabContextMenuMail($sLinkType);
			break;
		case 'modelmailsent':
			$sHTML .= WriteCollabContextMenuMail($sLinkType);
			break;
		case 'comments':
			$sHTML .= WriteCollabContextMenuComments();
			break;
		case 'discussions':
			$sHTML .= WriteCollabContextMenuDiscussions();
			break;
		case 'reviews':
			$sHTML .= WriteCollabContextMenuReviews();
			break;
		default:
			break;
	}
	return $sHTML;
}
function WriteCollabContextMenuMail($sLinkType)
{
	$sInboxTickClass = 'blank-icon';
	$sSentTickClass = 'blank-icon';
	if ($sLinkType === 'modelmail')
		$sInboxTickClass = 'icon16 tick-icon';
	else if ($sLinkType === 'modelmailsent')
		$sSentTickClass = 'icon16 tick-icon';
	$sHTML = '';
	$sHTML .= '<button id="collab-contextmenu-button" class="mail-button" title="Mail Options" onclick="ShowMenu(this)">';
	$sHTML .= '<div class="collab-icon-ellipsis button-icon icon16"></div>';
	$sHTML .= '</button>';
	$sHTML .= '<div id="collab-context-menu" class="contextmenu hide-menu">';
	$sHTML .= WriteContextMenuHeader(_glt('Mail Options'));
	$sHTML .= '<div class="contextmenu-items">';
	$sHTML .= '<div class="contextmenu-item small-margin" onclick="LoadMailMessage(\'\',\'new\')"><div class="mail-icon-compose contextmenu-icon icon16"></div>' . '' . _glt('Compose') . '</div>';
	$sHTML .= '<hr>';
	$sHTML .= '<div class="contextmenu-item small-margin" onclick="LoadObject(\'collaborate\',\'\',\'modelmail\',\'\',\'Collaborate\',\'propsprite-discussion\',false)">' . '<a class="contextmenu-tick ' . $sInboxTickClass . '" ></a>' . _glt('Inbox') . '</div>';
	$sHTML .= '<div class="contextmenu-item small-margin" onclick="LoadObject(\'collaborate\',\'\',\'modelmailsent\',\'\',\'Collaborate\',\'propsprite-discussion\',false)">' . '<a class="contextmenu-tick ' . $sSentTickClass . '"></a>' . _glt('Sent Mail') . '</div>';
	$sHTML .= '</div>';
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteCollabContextMenuChat()
{
	$aDaysList = ['Today', '7', '30', '90'];
	$sChatDays	= SafeGetInternalArrayParameter($_SESSION, 'chat_history_timeframe');
	$sHTML = '';
	$sHTML .= '<button id="collab-contextmenu-button" class="mail-button" title="Chat History Menu" onclick="ShowMenu(this)">';
	$sHTML .= '<div class="collab-icon-ellipsis button-icon icon16"></div>';
	$sHTML .= '</button>';
	$sHTML .= '<div id="collab-context-menu" class="collab-chat-menu contextmenu hide-menu">';
	$sHTML .= WriteContextMenuHeader(_glt('Chat Timeframe'));
	$sHTML .=  '<div class="contextmenu-items">';
	foreach ($aDaysList as $sDaysVal) {
		$sDaysLabel = $sDaysVal;
		if ($sDaysVal !== 'Today' && $sDaysVal !== 'All') {
			$sDaysLabel = $sDaysVal . ' Days';
		}
		if ($sDaysVal === 'All')
			$sHTML .= '<hr>';
		$sHTML .=  '<div class="contextmenu-item small-margin" timeframe="' . $sDaysVal . '" onclick="CollabChatSetHistoryTimeframe(this)">';
		if ($sDaysVal === $sChatDays)
			$sHTML .= '<a class="contextmenu-tick icon16 tick-icon"></a>';
		else
			$sHTML .= '<a class="contextmenu-tick blank-icon"></a>';
		$sHTML .= $sDaysLabel;
		$sHTML .= '</div>';
	}
	$sHTML .= '</div>';
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteCollabContextMenuComments()
{
	$aDaysList = ['Today', '7', '30', '90'];
	$sTimeframe	= SafeGetInternalArrayParameter($_SESSION, 'comment_history_timeframe');
	$sHTML = '';
	$sHTML .= '<button id="collab-contextmenu-button" class="mail-button" title="Comment History Menu" onclick="ShowMenu(this)">';
	$sHTML .= '<div class="collab-icon-ellipsis button-icon icon16"></div>';
	$sHTML .= '</button>';
	$sHTML .= '<div id="collab-context-menu" class="collab-comments-menu contextmenu hide-menu">';
	$sHTML .= WriteContextMenuHeader(_glt('Comment Timeframe'));
	$sHTML .=  '<div class="contextmenu-items">';
	foreach ($aDaysList as $sDaysVal) {
		$sDaysLabel = $sDaysVal;
		if ($sDaysVal !== 'Today' && $sDaysVal !== 'All') {
			$sDaysLabel = $sDaysVal . ' Days';
		}
		if ($sDaysVal === 'All')
			$sHTML .= '<hr>';
		$sHTML .=  '<div class="contextmenu-item small-margin" timeframe="' . $sDaysVal . '" onclick="CollabCommentsSetHistoryTimeframe(this)">';
		if ($sDaysVal === $sTimeframe)
			$sHTML .= '<a class="contextmenu-tick icon16 tick-icon"></a>';
		else
			$sHTML .= '<a class="contextmenu-tick blank-icon"></a>';
		$sHTML .= $sDaysLabel;
		$sHTML .= '</div>';
	}
	$sHTML .= '</div>';
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteCollabContextMenuDiscussions()
{
	$aDaysList = ['Today', '7', '30', '90'];
	$sTimeframe	= SafeGetInternalArrayParameter($_SESSION, 'discussion_history_timeframe');
	$sHTML = '';
	$sHTML .= '<button id="collab-contextmenu-button" class="mail-button" title="Discussion History Menu" onclick="ShowMenu(this)">';
	$sHTML .= '<div class="collab-icon-ellipsis button-icon icon16"></div>';
	$sHTML .= '</button>';
	$sHTML .= '<div id="collab-context-menu" class="collab-comments-menu contextmenu hide-menu">';
	$sHTML .= WriteContextMenuHeader(_glt('Discussion Timeframe'));
	$sHTML .=  '<div class="contextmenu-items">';
	foreach ($aDaysList as $sDaysVal) {
		$sDaysLabel = $sDaysVal;
		if ($sDaysVal !== 'Today' && $sDaysVal !== 'All') {
			$sDaysLabel = $sDaysVal . ' Days';
		}
		if ($sDaysVal === 'All')
			$sHTML .= '<hr>';
		$sHTML .=  '<div class="contextmenu-item small-margin" timeframe="' . $sDaysVal . '" onclick="CollabDiscussionsSetHistoryTimeframe(this)">';
		if ($sDaysVal === $sTimeframe)
			$sHTML .= '<a class="contextmenu-tick icon16 tick-icon"></a>';
		else
			$sHTML .= '<a class="contextmenu-tick blank-icon"></a>';
		$sHTML .= $sDaysLabel;
		$sHTML .= '</div>';
	}
	$sHTML .= '</div>';
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteCollabContextMenuReviews()
{
	$aDaysList = ['Today', '7', '30', '90'];
	$sTimeframe	= SafeGetInternalArrayParameter($_SESSION, 'review_history_timeframe');
	$sHTML = '';
	$sHTML .= '<button id="collab-contextmenu-button" class="mail-button" title="Review History Menu" onclick="ShowMenu(this)">';
	$sHTML .= '<div class="collab-icon-ellipsis button-icon icon16"></div>';
	$sHTML .= '</button>';
	$sHTML .= '<div id="collab-context-menu" class="collab-comments-menu contextmenu hide-menu">';
	$sHTML .= WriteContextMenuHeader(_glt('Review Timeframe'));
	$sHTML .=  '<div class="contextmenu-items">';
	foreach ($aDaysList as $sDaysVal) {
		$sDaysLabel = $sDaysVal;
		if ($sDaysVal !== 'Today' && $sDaysVal !== 'All') {
			$sDaysLabel = $sDaysVal . ' Days';
		}
		if ($sDaysVal === 'All')
			$sHTML .= '<hr>';
		$sHTML .=  '<div class="contextmenu-item small-margin" timeframe="' . $sDaysVal . '" onclick="CollabReviewsSetHistoryTimeframe(this)">';
		if ($sDaysVal === $sTimeframe)
			$sHTML .= '<a class="contextmenu-tick icon16 tick-icon"></a>';
		else
			$sHTML .= '<a class="contextmenu-tick blank-icon"></a>';
		$sHTML .= $sDaysLabel;
		$sHTML .= '</div>';
	}
	$sHTML .= '</div>';
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteCollabChatHistory()
{
	$sTodaysDate = date("Ymd");
	$aHistoryMessages = [];
	if (IsSessionSettingTrue('show_chat')) {
		$aHistoryMessages = GetChatHistory();
		if (array_key_exists('@attributes', $aHistoryMessages))
			$aHistoryMessages = [];
		ConvertToChildArrayItem('ss:chatmessage', $aHistoryMessages);
		if (array_key_exists('oslc:Error', $aHistoryMessages))
			$aHistoryMessages = [];
	}
	$aHeaderList = [
		_glt('Posted At'),
		'Icon',
		_glt('Chat Group'),
		_glt('Posted By'),
		_glt('Post')
	];
	$sHTML = '';
	$sHTML .= '<div id="collab-chat-right">';
	$sHTML .= '<div class="chat-history-header">' . _glt('Chat History') . '</div>';
	$sHTML .= '<table id="chat-recent-tb">';
	$sHTML .= WriteCollabTableHeader($aHeaderList);
	$sHTML .= '<tbody>';
	$aChatHistoryTable = [];
	$i = 0;
	$sPreviousDateGroup = 'init';
	$iGroupCount = 0;
	foreach ($aHistoryMessages as $aMessage) {
		$aRow = [];
		$sCurrentUser = $_SESSION['login_fullname'];
		$aMessage = SafeGetArrayItem1Dim($aMessage, 'ss:chatmessage');
		$sSenderName = SafeGetArrayItem($aMessage, ['ss:sender', 'rdf:Description', 'ss:memberfullname', 'foaf:Person', 'foaf:name']);
		$sRecipientName = SafeGetArrayItem($aMessage, ['ss:recipient', 'rdf:Description', 'ss:memberfullname', 'foaf:Person', 'foaf:name']);
		$sNick = SafeGetArrayItem($aMessage, ['ss:sender', 'rdf:Description', 'ss:memberfullname', 'foaf:Person', 'foaf:nick']);
		$sSenderType = SafeGetArrayItem($aMessage, ['ss:sender', 'rdf:Description', 'ss:type']);
		$sRecipientType = SafeGetArrayItem($aMessage, ['ss:recipient', 'rdf:Description', 'ss:type']);
		$sChatGroup = $sRecipientName;
		$sPostedBy = $sSenderName;
		$sIcon = '<div class="icon-red-right icon16"></div>';
		if ($sCurrentUser === $sRecipientName) {
			$sChatGroup = $sSenderName;
		}
		if ($sPostedBy === $sCurrentUser) {
			$sIcon = '<div class="icon-green-left icon16"></div>';
		}
		$sDate = SafeGetArrayItem($aMessage, ['ss:date']);
		$sDisplayDate = date("y-M-d - H:i", strtotime($sDate));
		$sMessage = SafeGetArrayItem($aMessage, ['ss:message']);
		StripCDATA($sMessage);
		$aRow = ['icon' => $sIcon, 'chatgroup' => $sChatGroup, 'type' => $sRecipientType, 'date' => $sDate, 'displaydate' => $sDisplayDate, 'postedby' => $sPostedBy, 'message' => $sMessage];
		$aChatHistoryTable[] = $aRow;
	}
	usort($aChatHistoryTable, 'sortByDate');
	$sTodaysDate = date("Ymd");
	$sExpandedGroups = '';
	$aExpandedGroups = [];
	$sExpandedGroups = SafeGetInternalArrayParameter($_SESSION, 'chat_expanded_groups');
	$aExpandedGroups = str_getcsv($sExpandedGroups);
	foreach ($aChatHistoryTable as $aRow) {
		$bIsExpanded = false;
		$sDate = $aRow['date'];
		$sDateGroup = GetDateGroup($sTodaysDate, $sDate);
		if ($sDateGroup !== $sPreviousDateGroup) {
			$iGroupCount++;
			if (($iGroupCount === 1) ||
				(in_array($sDateGroup, $aExpandedGroups))
			) {
				$bIsExpanded = true;
			}
			$sHTML .= WriteTableGroupRow($sDateGroup, false, $bIsExpanded);
		}
		if (($iGroupCount === 1) ||
			(in_array($sDateGroup, $aExpandedGroups))
		) {
			$sVisibility = '';
		} else {
			$sVisibility = ' hidden ';
		}
		$sHTML .= '<tr class="chat-list-row collapsible-row" chat-name="' . $aRow['chatgroup'] . '" chat-type="' . $aRow['type'] . '" group="' . $sDateGroup . '" onclick="SelectChat(this)" ' . $sVisibility . '>';
		$sHTML .= '<td class="date-cell">' . $aRow['displaydate'] . '</td>';
		$sHTML .= '<td class="icon-cell">' . $aRow['icon'] . '</td>';
		$sHTML .= '<td class="chatgroup-cell">' . $aRow['chatgroup'] . '</td>';
		$sHTML .= '<td class="postedby-cell">' . $aRow['postedby'] . '</td>';
		$sHTML .= '<td class="message-cell">' . $aRow['message'] . '</td>';
		$sHTML .= '</tr>';
		$sPreviousDateGroup = $sDateGroup;
		$i++;
	}
	$sHTML .= '</tbody>';
	$sHTML .= '</table>';
	$sHTML .= '</div>';
	return $sHTML;
}
function sortByDate($a, $b)
{
	return strcmp($b['date'], $a['date']);
}
function sortByPostedDate($a, $b)
{
	return strcmp($b['posteddate'], $a['posteddate']);
}
function WriteCollabCommentHistory()
{
	$aCommentHistoryTable = [];
	$sTodaysDate = date("Ymd");
	$aCommentList = GetCommentsHistory();
	if (array_key_exists('@attributes', $aCommentList))
		$aCommentList = [];
	$aHeaderList = [
		['date-cell', _glt('Modified')],
		['name-cell', _glt('Element')],
		['status-cell', _glt('Status')],
		['type-cell', _glt('Type')],
		['alias-cell', _glt('Alias')]
	];
	$sHTML = '';
	$sHTML .= '<div id="collab-comments-right">';
	$sHTML .= '<div class="comment-history-header">' . _glt('Comment History') . '</div>';
	$sHTML .= '<table id="comments-history-tb" class="collab-tb">';
	$sHTML .= WriteCollabTableHeader($aHeaderList, true);
	$sHTML .= '<tbody>';
	ConvertToChildArrayItem('oslc_am:Resource', $aCommentList);
	foreach ($aCommentList as $aComment) {
		$aRow = [];
		$sIcon = '';
		$aComment = SafeGetArrayItem1Dim($aComment, 'oslc_am:Resource');
		$sName = SafeGetArrayItem1Dim($aComment, 'dcterms:title');
		$sType = SafeGetArrayItem1Dim($aComment, 'dcterms:type');
		$sResType = SafeGetArrayItem1Dim($aComment, 'ss:resourcetype');
		$sStatus = SafeGetArrayItem1Dim($aComment, 'ss:status');
		$sCommentID = SafeGetArrayItem1Dim($aComment, 'ss:commentidentifier');
		$sAlias = SafeGetArrayItem1Dim($aComment, 'dcterms:title');
		$sAuthor = SafeGetArrayItem($aComment, ['dcterms:creator', 'foaf:Person', 'foaf:name']);
		$sModified = SafeGetArrayItem1Dim($aComment, 'ss:modified');
		$sPostedDate = SafeGetArrayItem1Dim($aComment, 'ss:posted');
		$sDisplayPostedDate = date("Y-m-d", strtotime($sPostedDate));
		$sGUID = SafeGetArrayItem1Dim($aComment, 'dcterms:identifier');
		$sImageURL = GetObjectImagePath($sType, $sResType, '', '', 16);
		$sIcon = BuildImageHTML($sResType, $sImageURL, false, false, '', '16');
		$aRow = ['icon' => $sIcon, 'guid' => $sGUID, 'commentid' => $sCommentID, 'restype' => $sResType, 'name' => $sName, 'type' => $sType, 'status' => $sStatus, 'alias' => $sAlias, 'date' => $sModified, 'posteddate' => $sDisplayPostedDate, 'posteddatetime' => $sPostedDate];
		$aCommentHistoryTable[] = $aRow;
	}
	usort($aCommentHistoryTable, 'sortByDate');
	$aDateList = [];
	foreach ($aCommentHistoryTable as $aRow) {
		$sDate = $aRow['posteddate'];
		if (!in_array($sDate, $aDateList)) {
			$aDateList[] = $sDate;
		}
	}
	rsort($aDateList);
	$i = 0;
	$sPreviousDateGroup = 'init';
	$iGroupCount = 0;
	foreach ($aDateList as $sDate) {
		foreach ($aCommentHistoryTable as $aRow) {
			if ($sDate === $aRow['posteddate']) {
				$bIsExpanded = false;
				$sDateGroup = GetDateGroup($sTodaysDate, $aRow['posteddatetime']);
				if ($sDateGroup !== $sPreviousDateGroup) {
					$iGroupCount++;
					if ($iGroupCount === 1) {
						$bIsExpanded = true;
					}
					$sHTML  .= WriteTableGroupRow($sDateGroup, false, $bIsExpanded);
				}
				if ($iGroupCount === 1) {
					$sVisibility = '';
				} else {
					$sVisibility = ' hidden ';
				}
				$sHTML .= '<tr class="comment-list-row collapsible-row" guid="' . $aRow['guid'] . '" restype="' . $aRow['restype'] . '" group="' . $sDateGroup . '" commentid="' . $aRow['commentid'] . '" onclick="SelectCommentElement(this)" ' . $sVisibility . '>';
				$sHTML .= '<td class="date-cell">' . $aRow['date'] . '</td>';
				$sHTML .= '<td class="name-cell">' . $aRow['icon'] .  $aRow['name'] . '</td>';
				$sHTML .= '<td class="status-cell">' . $aRow['status'] . '</td>';
				$sHTML .= '<td class="type-cell">' . $aRow['type'] . '</td>';
				$sHTML .= '<td class="alias-cell">' . $aRow['alias'] . '</td>';
				$sHTML .= '</tr>';
				$sPreviousDateGroup = $sDateGroup;
				$i++;
			}
		}
	}
	$sHTML .= '</tbody>';
	$sHTML .= '</table>';
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteCollabDiscussionHistory()
{
	$aDiscussionList = [];
	$sTodaysDate = date("Ymd");
	$aDiscussionList = GetDiscussionHistory();
	if (array_key_exists('@attributes', $aDiscussionList))
		$aDiscussionList = [];
	$aHeaderList = [
		['date-cell', _glt('Posted')],
		['name-cell', _glt('Element')],
		['discussion-cell', _glt('Discussion')],
		['postedby-cell', _glt('Posted By')],
		['status-cell', _glt('Topic Status')]
	];
	$sHTML = '';
	$sHTML .= '<div id="collab-discussions-right">';
	$sHTML .= '<div class="collab-table-header">' . _glt('Discussion History') . '</div>';
	$sHTML .= '<table id="discussion-history-tb" class="collab-tb">';
	$sHTML .= WriteCollabTableHeader($aHeaderList, true);
	$sHTML .= '<tbody>';
	$i = 0;
	$sPreviousDateGroup = 'init';
	$iGroupCount = 0;
	ConvertToChildArrayItem('oslc_am:Resource', $aDiscussionList);
	foreach ($aDiscussionList as $aDiscussion) {
		$sDateGroup = '';
		$sVisibility = '';
		$aDiscussion = SafeGetArrayItem1Dim($aDiscussion, 'oslc_am:Resource');
		$sName = SafeGetArrayItem1Dim($aDiscussion, 'dcterms:title');
		$sType = SafeGetArrayItem1Dim($aDiscussion, 'dcterms:type');
		$sResType = SafeGetArrayItem1Dim($aDiscussion, 'ss:resourcetype');
		$sGUID = SafeGetArrayItem1Dim($aDiscussion, 'dcterms:identifier');
		$sImageURL = GetObjectImagePath($sType, $sResType, '', '', 16);
		$sIcon = BuildImageHTML($sResType, $sImageURL, false, false, '', '16');
		$sPostedDate = SafeGetArrayItem($aDiscussion, ['ss:cdiscussion', 'rdf:Description', 'ss:posted']);
		$sTopic = SafeGetArrayItem($aDiscussion, ['ss:cdiscussion', 'rdf:Description', 'ss:topic']);
		$sPostedBy = SafeGetArrayItem($aDiscussion, ['ss:cdiscussion', 'rdf:Description', 'dcterms:creator', 'foaf:Person', 'foaf:name']);
		$sStatus = SafeGetArrayItem($aDiscussion, ['ss:cdiscussion', 'rdf:Description', 'ss:status']);
		$bIsExpanded = false;
		$sDateGroup = GetDateGroup($sTodaysDate, $sPostedDate);
		if ($sDateGroup !== $sPreviousDateGroup) {
			$iGroupCount++;
			if ($iGroupCount === 1) {
				$bIsExpanded = true;
			}
			$sHTML  .= WriteTableGroupRow($sDateGroup, false, $bIsExpanded);
		}
		if ($iGroupCount === 1) {
			$sVisibility = '';
		} else {
			$sVisibility = ' hidden ';
		}
		$sHTML .= '<tr class="comment-list-row collapsible-row" guid="' . $sGUID . '" restype="' . $sResType . '" group="' . $sDateGroup . '" onclick="SelectDiscussionElement(this)" ' . $sVisibility . '>';
		$sHTML .= '<td class="date-cell">' . $sPostedDate . '</td>';
		$sHTML .= '<td class="name-cell">' . $sIcon . $sName  . '</td>';
		$sHTML .= '<td class="discussion-cell">' . $sTopic . '</td>';
		$sHTML .= '<td class="postedby-cell">' . $sPostedBy . '</td>';
		$sHTML .= '<td class="status-cell">' . $sStatus . '</td>';
		$sHTML .= '</tr>';
		$sPreviousDateGroup = $sDateGroup;
		$i++;
	}
	$sHTML .= '</tbody>';
	$sHTML .= '</table>';
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteCollabReviewHistory()
{
	$aReviewList = [];
	$sTodaysDate = date("Ymd");
	$aReviewList = GetReviewHistory();
	if (array_key_exists('@attributes', $aReviewList))
		$aReviewList = [];
	$aHeaderList = [
		['date-cell', _glt('Posted')],
		['name-cell', _glt('Element')],
		['review-cell', _glt('Review')],
		['discussion-cell', _glt('Discussion')],
		['postedby-cell', _glt('Posted By')],
		['status-cell', _glt('Topic Status')]
	];
	$sHTML = '';
	$sHTML .= '<div id="collab-reviews-right">';
	$sHTML .= '<div class="collab-table-header">' . _glt('Review History') . '</div>';
	$sHTML .= '<table id="review-history-tb" class="collab-tb">';
	$sHTML .= WriteCollabTableHeader($aHeaderList, true);
	$sHTML .= '<tbody>';
	$i = 0;
	$sPreviousDateGroup = 'init';
	$iGroupCount = 0;
	ConvertToChildArrayItem('oslc_am:Resource', $aReviewList);
	foreach ($aReviewList as $aReview) {
		$sDateGroup = '';
		$sVisibility = '';
		$aReview = SafeGetArrayItem1Dim($aReview, 'oslc_am:Resource');
		$sName = SafeGetArrayItem1Dim($aReview, 'dcterms:title');
		$sType = SafeGetArrayItem1Dim($aReview, 'dcterms:type');
		$sResType = SafeGetArrayItem1Dim($aReview, 'ss:resourcetype');
		$sGUID = SafeGetArrayItem1Dim($aReview, 'dcterms:identifier');
		$sImageURL = GetObjectImagePath($sType, $sResType, '', '', 16);
		$sIcon = BuildImageHTML($sResType, $sImageURL, false, false, '', '16');
		$sPostedDate = SafeGetArrayItem($aReview, ['ss:creview', 'rdf:Description', 'ss:posted']);
		$sReview = SafeGetArrayItem($aReview, ['ss:creview', 'rdf:Description', 'ss:review']);
		$sTopic = SafeGetArrayItem($aReview, ['ss:creview', 'rdf:Description', 'ss:topic']);
		$sPostedBy = SafeGetArrayItem($aReview, ['ss:creview', 'rdf:Description', 'dcterms:creator', 'foaf:Person', 'foaf:name']);
		$sStatus = SafeGetArrayItem($aReview, ['ss:creview', 'rdf:Description', 'ss:status']);
		$bIsExpanded = false;
		$sDateGroup = GetDateGroup($sTodaysDate, $sPostedDate);
		if ($sDateGroup !== $sPreviousDateGroup) {
			$iGroupCount++;
			if ($iGroupCount === 1) {
				$bIsExpanded = true;
			}
			$sHTML  .= WriteTableGroupRow($sDateGroup, false, $bIsExpanded);
		}
		if ($iGroupCount === 1) {
			$sVisibility = '';
		} else {
			$sVisibility = ' hidden ';
		}
		$sHTML .= '<tr class="comment-list-row collapsible-row" guid="' . $sGUID . '" restype="' . $sResType . '" group="' . $sDateGroup . '" onclick="SelectReviewElement(this)" ' . $sVisibility . '>';
		$sHTML .= '<td class="date-cell">' . $sPostedDate . '</td>';
		$sHTML .= '<td class="name-cell">' . $sIcon . $sName  . '</td>';
		$sHTML .= '<td class="review-cell">' . $sReview . '</td>';
		$sHTML .= '<td class="discussion-cell">' . $sTopic . '</td>';
		$sHTML .= '<td class="postedby-cell">' . $sPostedBy . '</td>';
		$sHTML .= '<td class="status-cell">' . $sStatus . '</td>';
		$sHTML .= '</tr>';
		$sPreviousDateGroup = $sDateGroup;
		$i++;
	}
	$sHTML .= '</tbody>';
	$sHTML .= '</table>';
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteCollabTableHeader($aHeaderList, $bHasClass = false)
{
	$sHTML = '';
	$sHTML .= '<thead>';
	$sHTML .= '<tr class="no-filter">';
	foreach ($aHeaderList as $sHeader) {
		if ($sHeader === 'Icon')
			$sHeader = '';
		if ($bHasClass)
			$sHTML .= '<th class="' . $sHeader[0] . '"><div class="table-header-text">' . $sHeader[1] . '</div></th>';
		else
			$sHTML .= '<th class=""><div class="table-header-text">' . $sHeader . '</div></th>';
	}
	$sHTML .=  '</tr>';
	$sHTML .= '</thead>';
	return $sHTML;
}
function WriteCollabCommentDetails()
{
	echo '<div class="collab-left-overlay">';
	echo '<img src="images/mainwait.gif" alt="" class="navbar-spinner" height="26" width="26">';
	echo '</div>';
	echo '<div id="collab-comments-left">';
	if (!IsSessionSettingTrue('show_comments')) {
		echo  '<div class="disabled-collab-message">';
		echo  _glt('Comments are not enabled for this WebEA model connection');
		echo  '</div>';
	}
	echo '</div>';
}
function WriteCollabDiscussionDetails()
{
	echo '<div class="collab-left-overlay">';
	echo '<img src="images/mainwait.gif" alt="" class="navbar-spinner" height="26" width="26">';
	echo '</div>';
	echo '<div id="collab-discussions-left">';
	if (!IsSessionSettingTrue('show_discuss')) {
		echo  '<div class="disabled-collab-message">';
		echo  _glt('Discussions are not enabled for this WebEA model connection');
		echo  '</div>';
	}
	echo '</div>';
}
function WriteCollabReviewDetails()
{
	$sLoginUser 	= SafeGetInternalArrayParameter($_SESSION, 'login_user', '');
	echo '<div class="collab-left-overlay">';
	echo '<img src="images/mainwait.gif" alt="" class="navbar-spinner" height="26" width="26">';
	echo '</div>';
	echo '<div id="collab-reviews-left">';
	if (!IsSessionSettingTrue('show_discuss')) {
		echo  '<div class="disabled-collab-message">';
		echo  _glt('Discussions are not enabled for this WebEA model connection');
		echo  '</div>';
	} else if (strIsEmpty($sLoginUser)) {
		echo  '<div class="disabled-collab-message">';
		echo  _glt('Reviews are not supported in models without user security');
		echo  '</div>';
	}
	echo '</div>';
}
function WriteCollabMailList($sLinkType, $aData)
{
	$aMail = [];
	$sTodaysDate = date("Ymd");
	$sPreviousDateGroup = 'init';
	$aData = GetMailList($sLinkType);
	echo '<div id="collab-mail-right">';
	echo '<div id="mail-list">';
	if ($sLinkType === 'inbox') {
		$aMail = SafeGetArrayItem1Dim($aData, 'rdf:RDF');
		$aMail = SafeGetArrayItem1Dim($aMail, 'ss:modelmails');
		$aMail = SafeGetArrayItem1Dim($aMail, 'ss:receivedmail');
		$aMail = SafeGetArrayItem1Dim($aMail, 'rdf:Description');
		$aMail = SafeGetArrayItem1Dim($aMail, 'rdfs:member');
		if ((is_array($aMail)) &&
			(array_key_exists('ss:modelmail', $aMail))
		) {
			$aSingleMail = [];
			$aSingleMail[] = $aMail;
			$aMail = $aSingleMail;
		}
		if (!is_array($aMail)) {
			$aMail = [];
		}
		$aMail = array_reverse($aMail);
		echo '<div class="chat-history-header">' . _glt('Inbox') . '</div>';
		echo '<table id="mail-list-tb" list-type="inbox">';
		echo '<thead>';
		echo '<tr class="sortable-header no-filter">';
		echo '<th class="mail-col-default mail-col-flag"><div class="sort-icon"></div></th>';
		echo '<th class="mail-col-default mail-col-sender">' . _glt('Sender') . '<div class="sort-icon"></div></th>';
		echo '<th class="mail-col-default mail-col-to">' . _glt('To') . '<div class="sort-icon"></div></th>';
		echo '<th class="mail-col-default">' . _glt('Subject') . '<div class="sort-icon"></div></th>';
		echo '<th class="mail-col-default mail-col-date">' . _glt('Sent') . '<div class="sort-icon"></div></th>';
		echo '<th class="mail-col-summary">&nbsp&nbspMessage<div class="sort-icon"></div></th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		$sMailRows = '';
		if (!empty($aMail)) {
			$aRowCount = count($aMail);
			$i = 1;
			$iGroupCount = 0;
			foreach ($aMail as $aMailItem) {
				$bIsExpanded = false;
				$sName = '';
				$aMailItem = SafeGetArrayItem1Dim($aMailItem, 'ss:modelmail');
				$sMailJSON = json_encode($aMailItem);
				$sName = SafeGetArrayItem1Dim($aMailItem, 'ss:sender');
				$sName = SafeGetArrayItem1Dim($sName, 'foaf:Person');
				$sName = SafeGetArrayItem1Dim($sName, 'foaf:name');
				$aRecipients = SafeGetArrayItem1Dim($aMailItem, 'ss:recipient');
				ConvertToChildArrayItem('foaf:Person', $aRecipients);
				$sRecipient = GetMailRecipients($aRecipients);
				$sMailID = SafeGetArrayItem1Dim($aMailItem, 'dcterms:identifier');
				$sSubject = SafeGetArrayItem1Dim($aMailItem, 'ss:subject');
				$sFlag = SafeGetArrayItem1Dim($aMailItem, 'ss:flag');
				$sDate = SafeGetArrayItem1Dim($aMailItem, 'ss:date');
				$sMessage = SafeGetArrayItem1Dim($aMailItem, 'ss:message');
				$bIsRead = SafeGetArrayItem1Dim($aMailItem, 'ss:read');
				$sDateGroup = GetDateGroup($sTodaysDate, $sDate);
				if ($bIsRead !== 'true') {
					$bIsRead = 'false';
				}
				$sMailRow = '';
				if ($sDateGroup !== $sPreviousDateGroup) {
					$iGroupCount++;
					if ($iGroupCount === 1) {
						$bIsExpanded = true;
					}
					$sMailRow .= WriteTableGroupRow($sDateGroup, true, $bIsExpanded);
				}
				if ($iGroupCount === 1) {
					$sVisibility = '';
				} else {
					$sVisibility = ' hidden ';
				}
				$sMailRow .= '<tr class="mail-list-row collapsible-row" mailid="' . _h($sMailID) . '" read="' . $bIsRead . '" group="' . $sDateGroup . '" ' . $sVisibility . '>';
				$sMailRow .= '<td class="mail-col-default mail-col-flag">' . WriteMailFlagIcon($sFlag) . '</td>';
				$sMailRow .= '<td class="mail-col-default mail-col-sender"><div>' . _h($sName) . '</div></td>';
				$sMailRow .= '<td class="mail-col-default mail-col-to">' . _h($sRecipient) . '</td>';
				$sMailRow .= '<td class="mail-col-default">' . _h($sSubject) . '</td>';
				$sMailRow .= '<td class="mail-col-default mail-col-date">' . _h($sDate) . '</td>';
				$sMailRow .= '<td class="mail-col-summary">';
				$sMailRow .= '<div>' . WriteMailFlagIcon($sFlag) .  _h($sName) . '<a style="float:right;">' . _h($sDate) . '</a>' . '</div>';
				$sMailRow .= '<div style="margin-left:8px;">' . _h($sSubject) . '</div>';
				$sMailRow .= '</td>';
				$sMailRow .= '</tr>';
				$sMailRows .= $sMailRow;
				$sPreviousDateGroup = $sDateGroup;
				$i++;
			}
		}
		echo $sMailRows;
		echo '</tbody>';
		echo '</table>';
	} else {
		$aMail = SafeGetArrayItem1Dim($aData, 'rdf:RDF');
		$aMail = SafeGetArrayItem1Dim($aMail, 'ss:modelmails');
		$aMail = SafeGetArrayItem1Dim($aMail, 'ss:sentmail');
		$aMail = SafeGetArrayItem1Dim($aMail, 'rdf:Description');
		$aMail = SafeGetArrayItem1Dim($aMail, 'rdfs:member');
		echo '<div class="chat-history-header">' . _glt('Sent Mail') . '</div>';
		echo '<table id="mail-list-tb" list-type="sent">';
		echo '<thead>';
		echo '<tr class="sortable-header no-filter">';
		echo '<th class="mail-col-default" style="width:0px;"><div style="width:0px;" class="sort-icon"></div></th>';
		echo '<th class="mail-col-default mail-col-sender">' . _glt('Sender') . '<div class="sort-icon"></div></th>';
		echo '<th class="mail-col-default">' . _glt('Subject') . '<div class="sort-icon"></div></th>';
		echo '<th class="mail-col-default mail-col-date">' . _glt('Sent') . '<div class="sort-icon"></div></th>';
		echo '<th class="mail-col-summary">&nbsp&nbsp' . _glt('Message') . '<div class="sort-icon"></div></th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		$sMailRows = '';
		if (!empty($aMail)) {
			ConvertToChildArrayItem('ss:modelmail', $aMail);
			foreach ($aMail as $aMailItem) {
				$aMailItem = SafeGetArrayItem1Dim($aMailItem, 'ss:modelmail');
				$sName = SafeGetArrayItem1Dim($aMailItem, 'ss:recipient');
				if ((is_array($sName)) && (array_key_exists('foaf:Person', $sName))) {
					$sName = SafeGetArrayItem1Dim($sName, 'foaf:Person');
					$sName = SafeGetArrayItem1Dim($sName, 'foaf:name');
				} else {
					$sNamesString = '';
					$bIsfirst = true;
					foreach ($sName as $sNameItem) {
						if (!$bIsfirst) {
							$sNamesString .= ', ';
						}
						$sNameItem = SafeGetArrayItem1Dim($sNameItem, 'foaf:Person');
						$sNamesString .= SafeGetArrayItem1Dim($sNameItem, 'foaf:name');
						$bIsfirst = false;
					}
					$sName = $sNamesString;
				}
				$sSubject = SafeGetArrayItem1Dim($aMailItem, 'ss:subject');
				$sDate = SafeGetArrayItem1Dim($aMailItem, 'ss:date');
				$sMessage = SafeGetArrayItem1Dim($aMailItem, 'ss:message');
				$sMailID = SafeGetArrayItem1Dim($aMailItem, 'dcterms:identifier');
				$sFlag = SafeGetArrayItem1Dim($aMailItem, 'ss:flag');
				$sMailRow = '';
				$sMailRow .= '<tr mailid="' . _h($sMailID) . '">';
				$sMailRow .= '<td class="mail-col-default"></td>';
				$sMailRow .= '<td class="mail-col-default"><div>' . _h($sName) . '</div></td>';
				$sMailRow .= '<td class="mail-col-default">' . _h($sSubject) . '</td>';
				$sMailRow .= '<td class="mail-col-default">' . _h($sDate) . '</td>';
				$sMailRow .= '<td class="mail-col-summary">';
				$sMailRow .= '<div>' . _h($sName) . '<a style="float:right;">' . _h($sDate) . '</a>' . '</div>';
				$sMailRow .= '<div style="margin-left:8px;">' . _h($sSubject) . '</div>';
				$sMailRow .= '</td>';
				$sMailRow .= '</tr>';
				$sMailRows = $sMailRow . $sMailRows;
			}
		}
		echo $sMailRows;
		echo '</tbody>';
		echo '</table>';
	}
	echo '</div>';
	echo '</div>';
}
function WriteTableGroupRow($sGroup, $bIconInCell, $bIsExpanded)
{
	$sState = 'collapsed';
	if ($bIsExpanded)
		$sState = 'expanded';
	$sMailRow = '';
	if ($bIconInCell) {
		$sMailRow .= '<tr class="table-group-row" group="' . $sGroup . '" state="' . $sState . '" onclick="OnClickTableGroup(this)"><td class="mail-list-group-icon-cell"><div class="table-group-icon"></div></td><td colspan="3">' . $sGroup . '</td></tr>';
	} else {
		$sMailRow .= '<tr class="table-group-row" group="' . $sGroup . '" state="' . $sState . '" onclick="OnClickTableGroup(this)"><td colspan="5"><div class="table-group-icon" style="display:inline-block;margin-right:8px;"></div>' . $sGroup . '</td></tr>';
	}
	return $sMailRow;
}
function WriteCollabMailPreview()
{
	$sLoginUser 	= SafeGetInternalArrayParameter($_SESSION, 'login_user', '');
	$sHTML = '';
	$sHTML .= '<div id="collab-mail-left">';
	$sHTML .= '<div id="mail-preview">';
	if (!IsSessionSettingTrue('show_mail')) {
		$sHTML .=  '<div class="disabled-collab-message">';
		$sHTML .=  _glt('Model Mail is not enabled for this WebEA model connection');
		$sHTML .=  '</div>';
	} else if (strIsEmpty($sLoginUser)) {
		$sHTML .=  '<div class="disabled-collab-message">';
		$sHTML .=  _glt('Model Mail is not supported in models without user security');
		$sHTML .=  '</div>';
	}
	$sHTML .= '</div>';
	$sHTML .= '</div>';
	return $sHTML;
}
function GetDateGroup($sTodaysDate, $sDate)
{
	$sDateGroup = '';
	$sDayOfWeek = date('D', strtotime("today"));
	$sTodaysDateDay = date('d', strtotime($sTodaysDate));
	$sTodaysDateMonth = date('m', strtotime($sTodaysDate));
	$sTodaysDateYear = date('y', strtotime($sTodaysDate));
	$sToday = strtotime("today");
	$sYesterday = strtotime("yesterday");
	if ($sDayOfWeek === 'Mon') {
		$sThisWeekStart = strtotime('monday 00:00:00');
		$sLastWeekStart = strtotime('-1 week monday 00:00:00');
	} else {
		$sThisWeekStart = strtotime('-1 week monday 00:00:00');
		$sLastWeekStart = strtotime('-2 week monday 00:00:00');
	}
	$sThisMonthStart = strtotime("first day of this month");
	$sThisMonthEnd = strtotime("last day of this month");
	$sLastMonthStart = strtotime("first day of previous month");
	$sLastMonthEnd = strtotime("last day of previous month");
	$sDay = date('d', strtotime($sDate));
	$sMonth = date('m', strtotime($sDate));
	$sYear = date('y', strtotime($sDate));
	if (($sTodaysDateYear === $sYear) &&
		($sTodaysDateMonth === $sMonth) &&
		($sTodaysDateDay === $sDay)
	) {
		$sDateGroup = 'Today';
	} else if (strtotime($sDate) > $sYesterday) {
		$sDateGroup = 'Yesterday';
	} else if (strtotime($sDate) > $sThisWeekStart) {
		$sDateGroup = 'This Week';
	} else if (strtotime($sDate) > $sLastWeekStart) {
		$sDateGroup = 'Last Week';
	} else if ((strtotime($sDate) > $sThisMonthStart) &&
		(strtotime($sDate) < $sThisMonthEnd)
	) {
		$sDateGroup = 'This Month';
	} else if ((strtotime($sDate) > $sLastMonthStart) &&
		(strtotime($sDate) < $sThisMonthStart)
	) {
		$sDateGroup = 'Last Month';
	} else if (strtotime($sDate) < $sLastMonthStart) {
		$sDateGroup = 'Older';
	}
	return $sDateGroup;
}
function GetMailRecipients($aRecipients)
{
	$sHTML = '';
	$bFirst = true;
	foreach ($aRecipients as $aRecipient) {
		if (!$bFirst) {
			$sHTML .= ', ';
		}
		$sHTML .= SafeGetArrayItem($aRecipient, ['foaf:Person', 'foaf:name']);
		$bFirst = false;
	}
	return $sHTML;
}
?>
<script>
	var header = $('.sortable-header th');
	var table = $('table');
	var inverse = false;
	SortTable(header, table, inverse);
	$(document).ready(function() {
		$("#mail-list-tb tbody tr").on('click', function() {
			if (!$(this).hasClass("table-group-row")) {
				$(".mail-list-row-selected").removeClass("mail-list-row-selected");
				$(this).addClass("mail-list-row-selected");
				var mailid = $(this).attr("mailid");
				var mailIsRead = $(this).attr("read");
				var listType = $("#mail-list-tb").attr("list-type");
				if ($('#mail-preview').is(':visible')) {
					if (listType === 'inbox') {
						if (mailIsRead === 'true') {
							$("#mail-markunread-button").prop("disabled", false);
							$("#mail-markread-button").prop("disabled", true);
						} else {
							$("#mail-markunread-button").prop("disabled", true);
							$("#mail-markread-button").prop("disabled", false);
						}
					}
					LoadMailMessagePreview(mailid);
				} else {
					if (listType === 'inbox') {
						MailMarkAsRead();
					}
					MailView();
				}
			}
		});
		if ($('#mail-preview').is(':visible')) {
			$("#mail-list-tb").children().first().next().children().first().next().click();
		}
		$(document).ready(function() {
			$(".myInput").on("keyup", function() {
				var value = $(this).val().toLowerCase();
				var colNum = $(this).parent().index();
				$("#mail-list-tb tr").not(".no-filter").filter(function() {
					$(this).toggle($(this).find("td:visible").eq(colNum).text().toLowerCase().indexOf(value) > -1);
				});
			});
		});
		$(".chat-message-input").keypress(function(e) {
			if ((e.keyCode) == 13 && e.shiftKey) {} else if ((e.keyCode) == 13) {
				e.preventDefault();
				$(".chat-send-btn")[0].click();
			}
		});
	});
</script>