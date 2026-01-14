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
if (!isset($webea_page_parent_index)) {
	AllowedMethods('POST');
}
CheckAuthorisation();
$sMailID = isset($_POST['mailid']) ? $_POST['mailid'] : '';
$sLinkType = isset($_POST['linktype']) ? $_POST['linktype'] : '';
if (($sLinkType === 'new') ||
	($sLinkType === 'reply') ||
	($sLinkType === 'replyall') ||
	($sLinkType === 'forward')
) {
	$sMessage = '';
	$aMailUserList = [];
	$aMailItem = [];
	$sReplyTo = '';
	$sSubject = '';
	BuildOSLCConnectionString();
	$aMailUserList = GetMailUserList($g_sOSLCString);
	if (!strIsEmpty($sMailID)) {
		$aMailItem = GetMailItem($sMailID, $g_sOSLCString);
		$sMessage = WriteMailMessage($aMailItem, $sLinkType);
	}
	if (!IsHTTPSuccess(http_response_code())) {
		exit();
	}
	echo '<div class="default-dialog" style="max-height:600px;">';
	WriteMailHeading($sLinkType);
	echo '<div class="dialog-body">';
	echo '<form class="create-model-mail-form" role="form" onsubmit="OnSendMail(event)">';
	echo '<div class="mail-field-line">';
	echo '<div class="mail-field-label">';
	echo '<button id="mail-to-button" type="button" onclick="ShowMenu(this.parentElement)">To:</button>';
	echo '</div>';
	WriteSelectRecipient($aMailUserList);
	if ($sLinkType === 'reply') {
		$sSenderName = SafeGetArrayItem($aMailItem, ['ss:sender', 'foaf:Person', 'foaf:name']);
		$sSenderNick = SafeGetArrayItem($aMailItem, ['ss:sender', 'foaf:Person', 'foaf:nick']);
		$sReplyTo = $sSenderName;
		if (!strIsEmpty($sSenderNick)) {
			$sReplyTo .= ' <' . $sSenderNick . '>';
		}
		$sReplyTo .= ', ';
		$sSubject = SafeGetArrayItem($aMailItem, ['ss:subject']);
		$sSubject = 'RE: ' . $sSubject;
	} else if ($sLinkType === 'replyall') {
		$sSenderName = SafeGetArrayItem($aMailItem, ['ss:sender', 'foaf:Person', 'foaf:name']);
		$sSenderNick = SafeGetArrayItem($aMailItem, ['ss:sender', 'foaf:Person', 'foaf:nick']);
		$sReplyTo = $sSenderName;
		if (!strIsEmpty($sSenderNick)) {
			$sReplyTo .= ' <' . $sSenderNick . '>';
		}
		$sReplyTo .= ', ';
		$aRecipients = SafeGetChildArray($aMailItem, ['ss:recipient']);
		$sCurrentUser = $_SESSION['login_user'];
		foreach ($aRecipients as $aRecipient) {
			$sRecipientName = SafeGetArrayItem($aRecipient, ['foaf:Person', 'foaf:name']);
			$sRecipientNick = SafeGetArrayItem($aRecipient, ['foaf:Person', 'foaf:nick']);
			if ($sRecipientNick !== $sCurrentUser) {
				$sReplyTo .= $sRecipientName;
				if (!strIsEmpty($sRecipientNick)) {
					$sReplyTo .= ' <' . $sRecipientNick . '>';
				}
				$sReplyTo .= ', ';
			}
		}
		$sSubject = SafeGetArrayItem($aMailItem, ['ss:subject']);
		$sSubject = 'RE: ' . $sSubject;
	} else if ($sLinkType === 'forward') {
		$sSubject = SafeGetArrayItem($aMailItem, ['ss:subject']);
		$sSubject = 'FW: ' . $sSubject;
	}
	echo '<input class="mail-field-value" value="' . $sReplyTo . '" name="recipient">';
	echo '</div>';
	WriteMailSubject($sSubject);
	$sFlag = SafeGetArrayItem($aMailItem, ['ss:flag']);
	if (strIsEmpty($sFlag))
		$sFlag = 'None';
	WriteSelectFlag($sFlag);
	WriteMailMessageInput($sMessage);
	echo '</form>';
	echo '</div>';
	echo '<div class="dialog-footer">';
	echo '<div class="dialog-buttons-container">';
	WriteMailSendButton();
	echo '</div>';
	echo '</div>';
	echo '</div>';
} else if (($sLinkType === 'view') ||
	($sLinkType === 'viewsent')
) {
	$sMessage = '';
	$aMailUserList = [];
	$aMailItem = [];
	$sTo = '';
	$sSubject = '';
	BuildOSLCConnectionString();
	if (!strIsEmpty($sMailID)) {
		$aMailItem = GetMailItem($sMailID, $g_sOSLCString);
		$sSenderName = SafeGetArrayItem($aMailItem, ['ss:sender', 'foaf:Person', 'foaf:name']);
		$sSenderNick = SafeGetArrayItem($aMailItem, ['ss:sender', 'foaf:Person', 'foaf:nick']);
		$sFrom = $sSenderName;
		$aRecipients = SafeGetChildArray($aMailItem, ['ss:recipient']);
		$bIsFirst = true;
		foreach ($aRecipients as $aRecipient) {
			$sRecipientName = SafeGetArrayItem($aRecipient, ['foaf:Person', 'foaf:name']);
			$sRecipientNick = SafeGetArrayItem($aRecipient, ['foaf:Person', 'foaf:nick']);
			if (!$bIsFirst) {
				$sTo .= ', ';
			} else {
				$bIsFirst = false;
			}
			$sTo .= $sRecipientName;
		}
		$sSentDate = SafeGetArrayItem1Dim($aMailItem, 'ss:date');
		$sSubject = SafeGetArrayItem($aMailItem, ['ss:subject']);
		$sMessage = SafeGetArrayItem1Dim($aMailItem, 'ss:message');
		StripCDATA($sMessage);
		$sMessage = ConvertEAHyperlinks($sMessage);
	}
	echo '<div class="default-dialog mail-message-dialog">';
	WriteMailHeading($sLinkType);
	echo '<div class="dialog-body">';
	echo WriteMailReplyButtons($sLinkType);
	if (strIsEmpty($sFrom)) {
		$sFrom = $_SESSION['login_fullname'];
	}
	WriteLabelAndValue(_glt('From'), $sFrom, '', '');
	WriteLabelAndValue(_glt('To'), $sTo, '', '');
	WriteLabelAndValue(_glt('Sent'), $sSentDate, '', 'style="width:initial; min-width:140px;"');
	WriteLabelAndValue(_glt('Subject'), $sSubject, '', '');
	echo '<div class="field-line">';
	echo '<div class="mail-message">';
	echo $sMessage;
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '<div class="dialog-footer">';
	echo '<div class="dialog-buttons-container">';
	echo '<input class="mail-button mail-send-button" type="submit" onclick="MailMessageClose()" value="Close">';
	echo '</div>';
} else if (($sLinkType === 'preview') || ($sLinkType === 'previewsent')) {
	BuildOSLCConnectionString();
	$aMailItem = GetMailItem($sMailID, $g_sOSLCString);
	$sHTML = WriteMailMessage($aMailItem, $sLinkType);
	echo $sHTML;
}
?>
<script>
</script>