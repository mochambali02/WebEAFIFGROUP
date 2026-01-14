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
SafeStartSession();
CheckAuthorisation();
BuildOSLCConnectionString();
$sURL	= $g_sOSLCString;
echo WriteChatNotification($sURL);
function WriteChatNotification($sURL)
{
	$bIsEmpty = true;
	$sHTML = '';
	if (IsSessionSettingTrue('show_chat')) {
		$sHTMLMenu = WriteNewChatList($sURL, $bIsEmpty);
		$sHTML .= '<div class="statusbar-chat-cell">';
		if (!$bIsEmpty) {
			$sHTML .= '<div class="statusbar-item-new-chat" onclick="ShowMenu(this)">';
			$sHTML .=	'<img alt="" src="images/spriteplaceholder.png" class="statusbar-chat-icon propsprite-discussion"/>&nbsp;';
			$sHTML .= _glt('New Chat');
			$sHTML .= '</div>';
			$sHTML .=  '<div id="chat-newmsg-context-menu" class="hide-menu">';
			$sHTML .= $sHTMLMenu;
			$sHTML .= '</div>';
		}
		$sHTML .= '</div>';
	}
	return $sHTML;
}
function WriteNewChatList($sURL, &$bIsEmpty = true)
{
	$sHTML = '';
	$aNewChatList = GetNewChatList($sURL);
	if (!IsHTTPSuccess(http_response_code())) {
		exit();
	}
	foreach ($aNewChatList as $aRow) {
		$sName = SafeGetArrayItem($aRow, ['ss:chatmember', 'ss:memberfullname', 'foaf:Person', 'foaf:name']);
		$sNick = SafeGetArrayItem($aRow, ['ss:chatmember', 'ss:memberfullname', 'foaf:Person', 'foaf:nick']);
		$sMonitor = SafeGetArrayItem($aRow, ['ss:chatmember', 'ss:monitor']);
		$sType = SafeGetArrayItem($aRow, ['ss:chatmember', 'ss:type']);
		$sHTML .=  '<div class="contextmenu-item" chat-name="' . $sName . '" chat-type="' . $sType . '" chat-monitor="true" onclick="SelectChat(this)">' . $sName . '</div>';
		if (!strIsEmpty($sName)) {
			$bIsEmpty = false;
		}
	}
	return $sHTML;
}
function GetNewChatList($sURL)
{
	$sXML 	= '';
	$sOSLC_URL 	= $sURL . "modelchat/pollmessages/";
	$sParas = '';
	$sLoginGUID = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
	$sOSLC_URL 	.= $sParas;
	$xmlDoc = null;
	if (!strIsEmpty($sOSLC_URL)) {
		$xmlDoc = HTTPGetXML($sOSLC_URL);
	}
	if (!IsHTTPSuccess(http_response_code())) {
		exit();
	}
	$aData = XMLToArray($xmlDoc);
	$aNewChatList = SafeGetChildArray($aData, ['rdf:RDF', 'ss:modelchat', 'ss:chatmembers', 'rdf:Description', 'rdfs:member']);
	return $aNewChatList;
}
