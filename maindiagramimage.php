<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if (!isset($webea_page_parent_mainview)) {
	exit();
}
$sRootPath = dirname(__FILE__);
require_once $sRootPath . '/globals.php';
SafeStartSession();
if (isset($_SESSION['authorized']) === false) {
	$sErrorMsg = _glt('Your session appears to have timed out');
	setResponseCode(440, $sErrorMsg);
	exit();
}
if ($sResType === "Diagram") {
	$g_sViewingMode		= '2';
	if (!strIsEmpty($sObjectGUID)) {
		$bShowMiniProps = IsSessionSettingTrue('show_miniproperties');
		$sImgBin 	= '';
		$sImgMap 	= '';
		include('./data_api/get_diagramimage.php');
		echo '<div id="main-diagram-image" class="diagram-related-bkcolor' . GetShowBrowserMinPropsStyleClasses() . '">';
		if (strlen($sImgBin) > 0) {
			echo '<div class="main-diagram-inner">';
			echo $sImgMap;
			if (isset($_SESSION['ios_scroll'])) {
				$bRemoveMap = SafeGetInternalArrayParameter($_SESSION, 'ios_scroll');
				if ($bRemoveMap === 'true')
					printf('<img  id="diagram-image" src="data:image/png;base64,%s" alt="" usemap=""/>', $sImgBin);
				else if ($bRemoveMap === 'false')
					printf('<img id="diagram-image" src="data:image/png;base64,%s" alt="" usemap="#diagrammap"/>', $sImgBin);
				else
					printf('<img id="diagram-image" src="data:image/png;base64,%s" alt="" usemap="#diagrammap"/>', $sImgBin);
			} else {
				printf('<img id="diagram-image" src="data:image/png;base64,%s" alt="" usemap="#diagrammap"/>', $sImgBin);
			}
			echo '</div>';
			echo '<iframe id="main_diagram_iframe" style="display:none;"></iframe>';
		} else {
			$sOSLCErrorMsg = BuildOSLCErrorString();
			if (strIsEmpty($sOSLCErrorMsg)) {
				$sOSLCErrorMsg =  _glt('Invalid or missing diagram');
				$sOSLCErrorMsg .= '<br><br>';
				$sLink = '<a href="' . g_csHelpLocation . 'model_repository/webea_troubleshoot.html">Troubleshooting WebEA</a>';
				$sOSLCErrorMsg .= str_replace('%LINK%', $sLink, _glt('See Troubleshooting help topic'));
			}
			echo '<div class="main-diagram-inner">' . $sOSLCErrorMsg . '</div>';
		}
		echo '</div>';
	}
}
