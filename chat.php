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
AllowedMethods('POST');
BuildOSLCConnectionString();
$g_sOSLCString = $GLOBALS['g_sOSLCString'];
$sURL	= $g_sOSLCString;
$aMessages = GetChatMessages($sURL);
$aMembers = GetChatMembers($sURL, 'All');
$iErrorCode = http_response_code();
echo '<div id="collab-chat-left">';
echo WriteChatDialogContents($aMembers, $aMessages);
echo '</div>';
function GetChatMessages($sURL)
{
	$sXML 	= '';
	$aMessages	= [];
	$sURL 	= $sURL . 'modelchat/messages/';
	$sLoginGUID	= SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	$sChatName	= SafeGetInternalArrayParameter($_SESSION, 'chat_name');
	$sChatType	= SafeGetInternalArrayParameter($_SESSION, 'chat_type');
	$sChatDays	= SafeGetInternalArrayParameter($_SESSION, 'chat_days');
	if (strIsEmpty($sChatName))
		return $aMessages;
	define('XMLNS_SS', 'http://www.sparxsystems.com.au/oslc_am#');
	define('XMLNS_FOAF', 'http://xmlns.com/foaf/0.1/');
	$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:ss="' . XMLNS_SS . '"></rdf:RDF>');
	$chatmember = $xml->addChild('chatmember', null, XMLNS_SS);
	$memberfullname = $chatmember->addChild('memberfullname', null, XMLNS_SS);
	$person = $memberfullname->addChild('Person', null, XMLNS_FOAF);
	$person->addChild('name', $sChatName, XMLNS_FOAF);
	$person->addChild('nick', $sChatName, XMLNS_FOAF);
	$chatmember->addChild('type', _x($sChatType), XMLNS_SS);
	$chatmember->addChild('days', $sChatDays, XMLNS_SS);
	$chatmember->addChild('useridentifier', _x($sLoginGUID), XMLNS_SS);
	$sXML = $xml->asXML();
	$xmlRespDoc = HTTPPostXML($sURL, $sXML);
	$aData = XMLToArray($xmlRespDoc);
	return $aData;
}
function GetChatMembers($sURL, $sType = 'All')
{
	$aData = [];
	$sOSLC_URL 	= $sURL . "modelchat/members/";
	$sParas = '';
	$sLoginGUID = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
	AddURLParameter($sParas, 'type', $sType);
	$sOSLC_URL 	.= $sParas;
	$xmlDoc = null;
	if (!strIsEmpty($sOSLC_URL)) {
		$xmlDoc = HTTPGetXML($sOSLC_URL);
	}
	$aData = XMLToArray($xmlDoc);
	$aMembers = SafeGetChildArray($aData, ['rdf:RDF', 'ss:modelchat', 'ss:chatmembers', 'rdf:Description', 'rdfs:member']);
	return $aMembers;
}
function WriteChatDialogContents($aMembers, $aMessages)
{
	$aMonitoredUserList = [];
	$aMonitoredGroupList = [];
	$aFullUserList = [];
	$aFullGroupList = [];
	$sName = '';
	$sType = '';
	$sLoginUser 	= SafeGetInternalArrayParameter($_SESSION, 'login_user', '');
	$aAvatarsRaw = SafeGetChildArray($aMessages, ['rdf:RDF', 'rdf:Description']);
	ConvertToChildArrayItem('@attributes', $aAvatarsRaw);
	$aAvatars = [];
	foreach ($aAvatarsRaw as $aAvatar) {
		$sAvatarID = SafeGetArrayItem($aAvatar, ['@attributes', 'about']);
		$sAvatarImage = SafeGetArrayItem($aAvatar, ['ss:image']);
		$aRow = ['avatarid' => $sAvatarID, 'avatarimage' => $sAvatarImage];
		$aAvatars[] = $aRow;
	}
	$aMessages = SafeGetChildArray($aMessages, ['rdf:RDF', 'ss:chatmessages', 'rdfs:member']);
	$sCurrentUser 	= SafeGetInternalArrayParameter($_SESSION, 'login_user', '');
	foreach ($aMembers as $aMember) {
		$aUser = [];
		$sName = SafeGetArrayItem($aMember, ['ss:chatmember', 'ss:memberfullname', 'foaf:Person', 'foaf:name']);
		$sNick = SafeGetArrayItem($aMember, ['ss:chatmember', 'ss:memberfullname', 'foaf:Person', 'foaf:nick']);
		$sMonitor = SafeGetArrayItem($aMember, ['ss:chatmember', 'ss:monitor']);
		$sType = SafeGetArrayItem($aMember, ['ss:chatmember', 'ss:type']);
		if ($sType === 'User') {
			if ($sCurrentUser !== $sNick) {
				$aUser = [$sName, $sType, $sMonitor];
				if (strIsTrue($sMonitor)) {
					$aMonitoredUserList[] = $aUser;
				}
				$aFullUserList[] = $aUser;
			}
		} else if ($sType === 'Group') {
			$aGroup = [$sName, $sType, $sMonitor];
			if (strIsTrue($sMonitor)) {
				$aMonitoredGroupList[] = $aGroup;
			}
			$aFullGroupList[] = $aGroup;
		}
	}
	$sHTML = '';
	$sHTML .= '<div class="chat-dialog-contents">';
	if (!IsSessionSettingTrue('show_chat')) {
		$sHTML .=  '<div class="disabled-collab-message">';
		$sHTML .=  _glt('Chat is not enabled for this WebEA model connection');
		$sHTML .=  '</div>';
	} else if (strIsEmpty($sLoginUser)) {
		$sHTML .=  '<div class="disabled-collab-message">';
		$sHTML .=  _glt('Chat is not supported in models without user security');
		$sHTML .=  '</div>';
	} else {
		$sHTML .= '<div class="chat-menu-container">';
		$sHTML .= WriteChatSelectionMenu($aMonitoredUserList, $aMonitoredGroupList, $aFullUserList, $aFullGroupList);
		$sHTML .= WriteChatSettingsMenu($aFullGroupList);
		$sHTML .= '</div>';
		$sHTML .= WriteChatMessageInput();
		$sHTML .= WriteChatMessages($aMessages, $aAvatars);
	}
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteChatSelectionMenu($aMonitoredUserList, $aMonitoredGroupList, $aFullUserList, $aFullGroupList)
{
	$sCurrentChatName = SafeGetInternalArrayParameter($_SESSION, 'chat_name');
	$sCurrentChatDisplayName = $sCurrentChatName;
	if (strIsEmpty($sCurrentChatName))
		$sCurrentChatDisplayName = _glt('Select a Chat Group...');
	$sHTML = '';
	$sHTML .= WriteChatUserSubMenu($aFullUserList);
	$sHTML .= WriteChatGroupSubMenu($aFullGroupList);
	$sHTML .= '<div class="chat-menu-select-chat" chat-name="' . $sCurrentChatName . '" onclick="ShowChatSelectMenu(this)">';
	$sHTML .= '<div id="chat-menu-hamburger-icon" class="icon16 small-blue-hamburger"></div>';
	$sHTML .= $sCurrentChatDisplayName;
	$sHTML .= '</div>';
	$sHTML .= '<div class="select-chat-contents hide-menu">';
	$sHTML .=  '<div class="contextmenu-items">';
	foreach ($aMonitoredUserList as $aUser) {
		$sHTML .=  '<div class="contextmenu-item" chat-name="' . $aUser[0] . '" chat-type="' . $aUser[1] . '" chat-monitor="' . $aUser[2] . '" onclick="SelectChat(this)">' . $aUser[0] . '</div>';
	}
	if (!empty($aMonitoredUserList))
		$sHTML .=  '<hr>';
	foreach ($aMonitoredGroupList as $aGroup) {
		$sHTML .=  '<div class="contextmenu-item" chat-name="' . $aGroup[0] . '" chat-type="' . $aGroup[1] . '" chat-monitor="' . $aGroup[2] . '" onclick="SelectChat(this)">' . $aGroup[0] . '</div>';
	}
	if (!empty($aMonitoredGroupList))
		$sHTML .=  '<hr>';
	$sHTML .= '<div class="contextmenu-item small-padding-right" onclick="ChatShowUsersMenu()">' . _glt('User Chats') . '<a class="contextmenu-right-arrow icon16 arrow-right-icon"></a></div>';
	$sHTML .=  '<div class="contextmenu-item small-padding-right" onclick="ChatShowGroupsMenu()">' . _glt('Group Chats') . '<a class="contextmenu-right-arrow icon16 arrow-right-icon"></a></div>';
	$sHTML .= '</div>';
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteChatSettingsMenu($aFullGroupList)
{
	$aDaysList = ['Today', 'Today+', '3', '7', '30', '90', 'All'];
	$sChatDays = SafeGetInternalArrayParameter($_SESSION, 'chat_days');
	$sHTML = '';
	$sHTML .= '<div class="chat-menu-settings-button" onclick="ShowMenu(this)">';
	$sHTML .= '<div id="chat-menu-settings-icon" class="icon16 options-icon" ></div>';
	$sHTML .= '<div class="icon16 down-arrow-icon chat-menu-down-arrow" ></div>';
	$sHTML .= '</div>';
	$sHTML .= '<div class="chat-settings-contents hide-menu">';
	$sHTML .= WriteChatGroupMonitorSubMenu($aFullGroupList);
	$sHTML .=  '<div class="contextmenu-items">';
	$sHTML .=  '<div class="contextmenu-item small-padding-right" onclick="ShowMenuByID(\'chat-monitor-group-menu\')">' . _glt('Monitor Groups') . '<a style="float:right;" class="contextmenu-right-arrow icon16 arrow-right-icon"></a></div>';
	$sHTML .= '<hr>';
	foreach ($aDaysList as $sDaysVal) {
		$sDaysLabel = $sDaysVal;
		if ($sDaysVal !== 'Today' && $sDaysVal !== 'Today+' && $sDaysVal !== 'All') {
			$sDaysLabel = $sDaysVal . ' Days';
		}
		if ($sDaysVal === 'Today+')
			$sDaysLabel = 'Today +';
		if ($sDaysVal === 'All')
			$sHTML .= '<hr>';
		$sHTML .=  '<div class="contextmenu-item small-margin" chat-days="' . $sDaysVal . '" onclick="ChatSetDays(this)">';
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
function WriteChatMessageInput()
{
	$sChatName = SafeGetInternalArrayParameter($_SESSION, 'chat_name');
	$sChatType = SafeGetInternalArrayParameter($_SESSION, 'chat_type');
	$sChatMonitor = SafeGetInternalArrayParameter($_SESSION, 'chat_monitor');
	$sHTML = '';
	$sHTML .= '<div class="chat-message-input-container">';
	$sHTML .= '<form class="chat-message-form" role="form" >';
	$sHTML .=  '<textarea class="chat-message-input" name="chat-message"></textarea>';
	$sHTML .=  '<button class="chat-send-btn" chat-name="' . $sChatName . '" chat-type="' . $sChatType . '" chat-monitor="' . $sChatMonitor . '" onclick="OnChatSend(this)">' . _glt('Send') . '</button>';
	$sHTML .= '</form>';
	$sHTML .=  '</div>';
	return $sHTML;
}
function WriteChatMessages($aMessages, $aAvatars)
{
	$sChatName	= SafeGetInternalArrayParameter($_SESSION, 'chat_name');
	$sChatDays	= SafeGetInternalArrayParameter($_SESSION, 'chat_days');
	if (($sChatDays !== 'Today') &&
		($sChatDays !== 'Today+') &&
		($sChatDays !== 'All')
	) {
		$sChatDays = $sChatDays . ' Days';
	}
	$arraySize = sizeof($aMessages);
	if ($arraySize === 1) {
		if (array_key_exists('@attributes', $aMessages))
			$aMessages = [];
	}
	ConvertToChildArrayItem('ss:chatmessage', $aMessages);
	$sHTML = '';
	$sHTML .= '<div class="chat-messages-container-overlay">';
	$sHTML .= '<img src="images/mainwait.gif" alt="" class="navbar-spinner" height="26" width="26">';
	$sHTML .= '</div>';
	$sHTML .= '<div class="chat-messages-container">';
	$sPreviousName = '';
	foreach ($aMessages as $aMessage) {
		$sName = SafeGetArrayItem($aMessage, ['ss:chatmessage', 'ss:sender', 'rdf:Description', 'ss:memberfullname', 'foaf:Person', 'foaf:name']);
		$sFirstName = explode(' ', $sName)[0];
		$sNick = SafeGetArrayItem($aMessage, ['ss:chatmessage', 'ss:sender', 'rdf:Description', 'ss:memberfullname', 'foaf:Person', 'foaf:nick']);
		$sAvatar = SafeGetArrayItem($aMessage, ['ss:chatmessage', 'ss:sender', 'rdf:Description', 'ss:avatar', '@attributes', 'resource']);
		$sType = SafeGetArrayItem($aMessage, ['ss:chatmessage', 'ss:sender', 'rdf:Description', 'ss:memberfullname', 'ss:type']);
		$sSequence = SafeGetArrayItem($aMessage, ['ss:chatmessage', 'ss:sequence']);
		$sDate = SafeGetArrayItem($aMessage, ['ss:chatmessage', 'ss:date']);
		$sMessage = SafeGetArrayItem($aMessage, ['ss:chatmessage', 'ss:message']);
		$sDisplayDate = date("y-M-d - H:i", strtotime($sDate));
		if ($sName === $sPreviousName) {
			$sAvatar = 'empty';
		}
		$sPreviousName = $sName;
		StripCDATA($sMessage);
		$sHTML .= '<div class="chat-message-container">';
		$sHTML .= '<div class="chat-message-line1">';
		$sHTML .= WriteChatAvatarImage($sAvatar);
		$sHTML .= '<div class="chat-message-username">';
		$sHTML .= $sFirstName;
		$sHTML .= '</div>';
		$sHTML .= '<div class="chat-message-datetime">';
		$sHTML .= $sDisplayDate;
		$sHTML .= '</div>';
		$sHTML .= '</div>';
		$sHTML .= '<div class="chat-message-line2">';
		$sHTML .= '<div class="chat-message-text">';
		$sHTML .= _hRichText($sMessage);
		$sHTML .= '</div>';
		$sHTML .= '</div>';
		$sHTML .= '</div>';
	}
	if (empty($aMessages) && !strIsEmpty($sChatName)) {
		$sHTML .= '<div class="chat-message-container">';
		$sHTML .= '<div class="chat-empty">' . _glt('No discussions to display for the selected timeframe') . ' (' . $sChatDays . ')</div>';
		$sHTML .= '</div>';
	}
	$sHTML .= WriteAvatarCSS($aAvatars);
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteChatUserSubMenu($aFullUserList)
{
	$sHTML = '';
	$sHTML .= '<div class="user-chat-menu-contents hide-menu contextmenu">';
	$sHTML .= WriteContextMenuHeader(_glt('Select User'));
	$sHTML .=  '<div class="contextmenu-items chat-user-submenu-contents">';
	foreach ($aFullUserList as $sUser) {
		$sHTML .=  '<div class="contextmenu-item" chat-name="' . $sUser[0] . '"  chat-type="' . $sUser[1] . '" chat-monitor="' . $sUser[2] . '" onclick="SelectChat(this)">' . $sUser[0] . '</div>';
	}
	$sHTML .= '</div>';
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteChatGroupSubMenu($aFullGroupList)
{
	$sHTML = '';
	$sHTML .= '<div class="group-chat-menu-contents hide-menu contextmenu">';
	$sHTML .= WriteContextMenuHeader(_glt('Select Group'));
	$sHTML .=  '<div class="contextmenu-items  chat-group-submenu-contents">';
	foreach ($aFullGroupList as $aGroup) {
		$sHTML .=  '<div class="contextmenu-item" chat-name="' . $aGroup[0] . '"  chat-type="' . $aGroup[1] . '" chat-monitor="' . $aGroup[2] . '" onclick="SelectChat(this)">' . $aGroup[0] . '</div>';
	}
	$sHTML .= '</div>';
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteChatGroupMonitorSubMenu($aFullGroupList)
{
	$sHTML = '';
	$sHTML .= '<div id="chat-monitor-group-menu" class="chat-monitor-group-menu-contents hide-menu contextmenu">';
	$sHTML .= WriteContextMenuHeader(_glt('Monitor Group'));
	$sHTML .=  '<div class="contextmenu-items  chat-group-submenu-contents">';
	foreach ($aFullGroupList as $aGroup) {
		$sHTML .=  '<div class="contextmenu-item small-margin" chat-name="' . $aGroup[0] . '"  chat-type="' . $aGroup[1] . '" chat-monitor="' . $aGroup[2] . '" onclick="ChatMonitorGroup(this)">';
		if ($aGroup[2] === 'true')
			$sHTML .= '<a class="contextmenu-tick icon16 tick-icon"></a>';
		else
			$sHTML .= '<a class="contextmenu-tick blank-icon"></a>';
		$sHTML .= $aGroup[0];
		$sHTML .= '</div>';
	}
	$sHTML .= '</div>';
	$sHTML .= '</div>';
	return $sHTML;
}
function WriteChatAvatarImage($sImageID)
{
	$sHTML = '';
	if (IsSessionSettingTrue('use_avatars')) {
		if ($sImageID === '') {
			$sHTML .= '<div class="chat-avatar chat-avatar-default"></div>';
		} else if ($sImageID === 'empty') {
			$sHTML .= '<div class="chat-avatar chat-avatar-empty"></div>';
		} else {
			$sHTML .= '<div class="chat-avatar" id="avatarimg-' . _h($sImageID) . '"></div>';
		}
	} else {
		$sHTML .= '<div class="chat-avatar chat-avatar-default"></div>';
	}
	return $sHTML;
}
?>
<script>
	var unsaved = false;
</script>