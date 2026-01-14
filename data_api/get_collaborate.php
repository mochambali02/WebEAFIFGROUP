<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
require_once __DIR__ . '/../security.php';
require_once __DIR__ . '/../globals.php';
SafeStartSession();
CheckAuthorisation();
function GetChatHistory()
{
	BuildOSLCConnectionString();
	$g_sOSLCString = $GLOBALS['g_sOSLCString'];
	$sOSLC_URL 	= $g_sOSLCString . "modelchat/history/";
	$sParas = '';
	$sLoginGUID = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
	$sOSLC_URL 	.= $sParas;
	$xmlDoc = null;
	if (!strIsEmpty($sOSLC_URL)) {
		$xmlDoc = HTTPGetXML($sOSLC_URL);
	}
	$aData = XMLToArray($xmlDoc);
	$aData = SafeGetChildArray($aData, ['rdf:RDF', 'ss:chathistory', 'rdfs:member']);
	return $aData;
}
function GetCommentsHistory()
{
	$aData = [];
	if (!IsSessionSettingTrue('show_comments')) {
		return $aData;
	}
	$sDays	= SafeGetInternalArrayParameter($_SESSION, 'comment_history_timeframe', '7');
	BuildOSLCConnectionString();
	$g_sOSLCString = $GLOBALS['g_sOSLCString'];
	$sOSLC_URL 	= $g_sOSLCString . "comment/history/";
	$sParas = '';
	$sLoginGUID = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
	AddURLParameter($sParas, 'days', $sDays);
	$sOSLC_URL 	.= $sParas;
	$xmlDoc = null;
	if (!strIsEmpty($sOSLC_URL)) {
		$xmlDoc = HTTPGetXML($sOSLC_URL);
	}
	$aData = XMLToArray($xmlDoc);
	if (empty($aData)) {
		echo 'No comments found';
		exit();
	}
	$aData = SafeGetChildArray($aData, ['rdf:RDF', 'rdf:Description', 'rdfs:member']);
	return $aData;
}
function GetDiscussionHistory()
{
	$aData = [];
	if (!IsSessionSettingTrue('show_discuss')) {
		return $aData;
	}
	$sDays	= SafeGetInternalArrayParameter($_SESSION, 'discussion_history_timeframe', '7');
	BuildOSLCConnectionString();
	$g_sOSLCString = $GLOBALS['g_sOSLCString'];
	$sOSLC_URL 	= $g_sOSLCString . "discussion/history/";
	$sParas = '';
	$sLoginGUID = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
	AddURLParameter($sParas, 'days', $sDays);
	$sOSLC_URL 	.= $sParas;
	$xmlDoc = null;
	if (!strIsEmpty($sOSLC_URL)) {
		$xmlDoc = HTTPGetXML($sOSLC_URL);
	}
	$aData = XMLToArray($xmlDoc);
	if (empty($aData)) {
		echo 'No discussions found';
		exit();
	}
	$aData = SafeGetChildArray($aData, ['rdf:RDF', 'rdf:Description', 'rdfs:member']);
	return $aData;
}
function GetReviewHistory()
{
	$aData = [];
	if (!IsSessionSettingTrue('show_discuss')) {
		return $aData;
	}
	$sDays	= SafeGetInternalArrayParameter($_SESSION, 'review_history_timeframe', '7');
	BuildOSLCConnectionString();
	$g_sOSLCString = $GLOBALS['g_sOSLCString'];
	$sOSLC_URL 	= $g_sOSLCString . "review/history/";
	$sParas = '';
	$sLoginGUID = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
	AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
	AddURLParameter($sParas, 'days', $sDays);
	$sOSLC_URL 	.= $sParas;
	$xmlDoc = null;
	if (!strIsEmpty($sOSLC_URL)) {
		$xmlDoc = HTTPGetXML($sOSLC_URL);
	}
	$aData = XMLToArray($xmlDoc);
	if (empty($aData)) {
		echo 'No reviews found';
		exit();
	}
	$aData = SafeGetChildArray($aData, ['rdf:RDF', 'rdf:Description', 'rdfs:member']);
	return $aData;
}
function GetMailList($sLinkType)
{
	$aData = [];
	if (!IsSessionSettingTrue('show_mail')) {
		return $aData;
	}
	$g_sOSLCString = $GLOBALS['g_sOSLCString'];
	$sMailID	= '';
	$sMailDays  = SafeGetInternalArrayParameter($_SESSION, 'mail_days');
	$sDateAdjustment = '-' . $sMailDays . 'days';
	$sStartDate =  date('Y-m-d', strtotime($sDateAdjustment));
	if ($sLinkType === 'inbox') {
		$sTypeParam = 'receivedmail';
		$sMailNode = 'ss:receivedmail';
	} else if ($sLinkType === 'sent') {
		$sTypeParam = 'sentmail';
		$sMailNode = 'ss:sentmail';
	}
	BuildOSLCConnectionString();
	if (($sLinkType === 'inbox') || ($sLinkType === 'sent')) {
		BuildOSLCConnectionString();
		$sOSLC_URL 	= $g_sOSLCString . "modelmail/";
		$sParas = '';
		$sLoginGUID = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
		AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
		AddURLParameter($sParas, 'type', $sTypeParam);
		AddURLParameter($sParas, 'options', 'exclude_mail_message');
		AddURLParameter($sParas, 'startdate', $sStartDate);
		$sOSLC_URL 	.= $sParas;
		$xmlDoc = null;
		if (!strIsEmpty($sOSLC_URL)) {
			$xmlDoc = HTTPGetXML($sOSLC_URL);
		}
		$aData = XMLToArray($xmlDoc);
		if (empty($aData)) {
			echo 'No mail found';
			exit();
		}
	}
	return $aData;
}
