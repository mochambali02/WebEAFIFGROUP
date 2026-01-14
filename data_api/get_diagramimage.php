<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if (
	!isset($webea_page_parent_mainview) ||
	isset($sObjectGUID) === false
) {
	exit();
}
$sRootPath = dirname(dirname(__FILE__));
require_once $sRootPath . '/globals.php';
SafeStartSession();
if (isset($_SESSION['authorized']) === false) {
	$sErrorMsg = _glt('Your session appears to have timed out');
	setResponseCode(440, $sErrorMsg);
	exit();
}
BuildOSLCConnectionString();
$sOSLC_URL = $g_sOSLCString . "completeresource/" . $sObjectGUID . "/";
if (!strIsEmpty($sObjectGUID)) {
	if (!strIsEmpty($sOSLC_URL)) {
		if (isset($bShowMiniProps) === false) {
			$bShowMiniProps = false;
		}
		$sStatusCode = '';
		$sStatusMessage = '';
		$sParas = '';
		$sLoginGUID  = SafeGetInternalArrayParameter($_SESSION, 'login_guid');
		AddURLParameter($sParas, 'useridentifier', $sLoginGUID);
		$sResourceFeaturesFilter = "di";
		if (!strIsEmpty($sResourceFeaturesFilter)) {
			AddURLParameter($sParas, 'features', $sResourceFeaturesFilter);
		}
		$sOSLC_URL 	.= $sParas;
		$xmlDoc = HTTPGetXML($sOSLC_URL);
		if ($xmlDoc != null) {
			$xnRoot = $xmlDoc->documentElement;
			$xnDesc = GetXMLFirstChild($xnRoot);
			if ($xnDesc != null && $xnDesc->childNodes != null) {
				if (GetOSLCError($xnDesc)) {
					$xnFeatures = null;
					foreach ($xnDesc->childNodes as $xnDescB) {
						if ($xnDescB->nodeName === "ss:features")
							$xnFeatures = GetXMLFirstChild($xnDescB);
						else if ($xnDescB->nodeName === "ss:locked")
							$sDiagLocked = '1';
					}
					if ($xnFeatures !== null) {
						foreach ($xnFeatures->childNodes as $xnDescC) {
							if ($xnDescC->nodeName === "ss:diagramimage") {
								$xnImgDesc = $xnDescC->firstChild;
								$xnImgProps = $xnImgDesc->getElementsByTagNameNS("*", "*");
								if ($xnImgProps !== null) {
									foreach ($xnImgProps as $xnImgProp) {
										if ($xnImgProp->nodeName === "ss:image") {
											$sImgBin = $xnImgProp->nodeValue;
										} elseif ($xnImgProp->nodeName === "ss:imagemap") {
											$sImgMap = $xnImgProp->nodeValue;
										}
									}
								}
							}
						}
					}
					if (!strIsEmpty($sImgMap)) {
						$sImgMap = str_replace('<![CDATA[', '', $sImgMap);
						$sImgMap = str_replace("]]>", "", $sImgMap);
						$sJSFunct = 'javascript:load_diagram_object(\'';
						if ($bShowMiniProps) {
							$sJSFunct = 'javascript:load_miniprops_object(\'';
						}
						if (substr_count($sImgMap, '" alt="') > 0) {
							$iLastPos = 0;
							$iPos = mb_strpos($sImgMap, 'href="$element=', $iLastPos);
							while ($iPos !== false) {
								$iPos2 = mb_strpos($sImgMap, '" alt="', $iPos);
								$iPos3 = mb_strpos($sImgMap, '"', $iPos2 + 8);
								$sGUID = mb_substr($sImgMap, $iPos + 15, 38);
								$sAlt  = mb_substr($sImgMap, $iPos2 + 7, ($iPos3 - $iPos2) - 7);
								$sAlt  = mb_strtolower($sAlt);
								$sHref = '$element=' . $sGUID;
								if ($sAlt === 'package') {
									$sHref = $sJSFunct . 'pk_' . $sGUID;
								} elseif ($sAlt === 'element') {
									$sHref = $sJSFunct . 'el_' . $sGUID;
								}
								$sImgMap = mb_substr($sImgMap, 0, $iPos + 6) . $sHref . '\')' . mb_substr($sImgMap, $iPos2);
								$iLastPos = $iPos2;
								$iPos = mb_strpos($sImgMap, 'href="$element=', $iLastPos);
							}
						} else {
							$sImgMap = str_replace('$element=', $sJSFunct . 'el_', $sImgMap);
							$sImgMap = str_replace('}', "}');", $sImgMap);
						}
						$sImgMap = '<map id = "diagrammap" name="diagrammap">' . $sImgMap . '</map>';
					}
				}
			}
		}
	}
}
