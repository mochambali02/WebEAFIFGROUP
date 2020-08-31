<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	$sRootPath = dirname(dirname(__FILE__));
	require_once $sRootPath . '/globals.php';
	SafeStartSession();
	if ($_SERVER["REQUEST_METHOD"] !== "POST" ||
		isset($sObjectGUID) === false ||
		isset($_SESSION['authorized']) === false)
	{
		exit();
	}
	if ( !strIsEmpty($sObjectGUID) )
	{
		BuildOSLCConnectionString();
		$sOSLC_URL 	= $g_sOSLCString . "completediscussions/";
		$sOSLC_URL 	= $sOSLC_URL . $sObjectGUID . "/";
		$sParas = '';
		$sLoginGUID = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
		$sReviewSession = SafeGetInternalArrayParameter($_SESSION, 'review_session');
		AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
		$sOSLC_URL 	.= $sParas;
		if ( !strIsEmpty($sOSLC_URL) )
		{
			ob_start();
			$xmlDoc = HTTPGetXML($sOSLC_URL);
			ob_end_clean();
			if ($xmlDoc != null)
			{
				$xnRoot = $xmlDoc->documentElement;
				$xnObj = $xnRoot->firstChild;
				if ($xnObj != null && $xnObj->childNodes != null)
				{
					if ( GetOSLCError($xnObj) )
					{
						$aDiscussions = getDiscussions2($xnRoot);
						$aAvatars = getAvatars($xnObj);
					}
				}
			}
			else
			{
				return null;
			}
		}
	}
?>