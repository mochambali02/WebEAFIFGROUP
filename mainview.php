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
	if ($_SERVER["REQUEST_METHOD"] !== "POST"  && !isset($webea_page_parent_index) )
	{
		$sErrorMsg = _glt('Direct navigation is not allowed');
		setResponseCode(405, $sErrorMsg);
		exit();
	}
	SafeStartSession();
	$sObjectGUIDEnc = SafeGetInternalArrayParameter($_POST, 'objectguid');
	$sObjectGUID 	= urldecode($sObjectGUIDEnc);
	$sResType 		= GetResTypeFromGUID($sObjectGUID);
	$sObjectHasChild = SafeGetInternalArrayParameter($_POST, 'haschild');
	$sLinkType 		= SafeGetInternalArrayParameter($_POST, 'linktype');
	$sObjectName	= SafeGetInternalArrayParameter($_POST, 'objectname');
	$sObjectImageURL = SafeGetInternalArrayParameter($_POST, 'imageurl');
	$sObjectHyper	= SafeGetInternalArrayParameter($_POST, 'hyper');
	if (isset($_SESSION['authorized']) === false)
	{
		$sErrorMsg = _glt('Your session appears to have timed out');
		return $sErrorMsg;
	}
	$g_sViewingMode		= '0';
	$webea_page_parent_mainview = true;
	$sCurrModelNo = SafeGetInternalArrayParameter($_SESSION, 'model_no', '');
	echo '<div id="webea-display-warning-message" class = '.GetShowBrowserMinPropsStyleClasses().'>';
	if (IsSessionSettingTrue('show_miniproperties') && IsSessionSettingTrue('show_browser'))
	{
		echo '<div class="webea-display-warning-message-text" id="webea-display-warning-message-text">';
		echo _glt('Browser should be disabled');
		echo ' - <a href="javascript:show_browser()">' . _glt('Disable Browser') . '</a>';
		echo '<br><br>';
		echo ''._glt('Mini Properties should be disabled');
		echo ' - <a href="javascript:show_mini_properties()">' . _glt('Disable Mini Properties') . '</a>';
		echo '</div>';
	}
	elseif (IsSessionSettingTrue('show_miniproperties'))
	{
		echo '<div class="webea-display-warning-message-text" id="webea-display-warning-message-text">';
		echo ''._glt('Mini Properties should be disabled');
		echo ' - <a href="javascript:show_mini_properties()">' . _glt('Disable Mini Properties') . '</a>';
		echo '</div>';
	}
	elseif (IsSessionSettingTrue('show_browser'))
	{
		echo '<div class="webea-display-warning-message-text" id="webea-display-warning-message-text">';
		echo _glt('Browser should be disabled');
		echo ' - <a href="javascript:show_browser()">' . _glt('Disable Browser') . '</a>';
		echo '</div>';
	}
	echo '</div>';
	echo '<div id="page-built-with-model-no">' . $sCurrModelNo . '</div>';
	$bSupportsMiniProps = false;
	$bShowingBrowser = false;
	if ( IsSessionSettingTrue('show_browser') &&
		 ($sLinkType   !== 'edit') &&
		 ($sObjectGUID !== 'matrix') &&
		 ($sObjectGUID !== 'matrixprofiles') &&
		 ($sObjectGUID !== 'search') &&
		 ($sObjectGUID !== 'searchresults') &&
		 ($sObjectGUID !== 'watchlist') &&
		 ($sObjectGUID !== 'watchlistconfig') &&
		 ($sObjectGUID !== 'watchlistresults') &&
		 ($sResType    !== "Connector"))
	{
		include('browser.php');
		$bShowingBrowser = true;
	}
	if ( $sObjectGUID === 'initialize' )
	{
	}
	elseif ( $sObjectGUID === 'addmodelroot' )
	{
		$sAddObjectType = 'modelroot';
		include('mainaddrootpackage.php');
	}
	elseif ( $sObjectGUID === 'addviewpackage' )
	{
		$sAddObjectType = 'view';
		include('mainaddrootpackage.php');
	}
	elseif ( $sObjectGUID === 'addelement' )
	{
		include('mainaddelement.php');
	}
	elseif ( $sObjectGUID === 'addelementtest' )
	{
		include('mainaddelementtest.php');
	}
	elseif ( $sObjectGUID === 'addelementresalloc' )
	{
		include('mainaddelementresalloc.php');
	}
	elseif ( $sObjectGUID === 'addelementchgmgmt' )
	{
		include('mainaddelementchgmgmt.php');
	}
	elseif ( $sObjectGUID === 'editelementnote' )
	{
		include('maineditelementnote.php');
	}
	elseif ( $sObjectGUID === 'editelementtest' )
	{
		include('maineditelementtest.php');
	}
	elseif ( $sObjectGUID === 'editelementresalloc' )
	{
		include('maineditelementresalloc.php');
	}
	elseif ( $sObjectGUID === 'search' )
	{
		include('mainsearch.php');
	}
	elseif ( $sObjectGUID === 'searchresults' )
	{
		include('mainsearchresults.php');
	}
	elseif ( $sObjectGUID === 'matrixprofiles' )
	{
		include('mainmatrixprofiles.php');
	}
	elseif ( $sObjectGUID === 'matrix' )
	{
		include('mainmatrix.php');
	}
	elseif ( $sObjectGUID === 'watchlist' )
	{
		include('mainwatchlist.php');
	}
	elseif ( $sObjectGUID === 'watchlistconfig' )
	{
		include('mainwatchlistconfig.php');
	}
	elseif ( $sObjectGUID === 'watchlistresults' )
	{
		include('mainwatchlistresults.php');
	}
	else
	{
		if ( $sLinkType === "document" || $sLinkType === "encryptdoc" )
		{
			include('mainlinkeddoc.php');
		}
		elseif ( $sResType === "Connector" )
		{
			include('mainconnector.php');
		}
		elseif ( $sLinkType === 'props' || ($sResType==='Element' && $sLinkType !== 'child') )
		{
			include('mainproperties.php');
			$bSupportsMiniProps = true;
		}
		else
		{
			if ($sResType!=='Element' || $sObjectHasChild==='true' || ($sResType==='Element' && $sLinkType === 'child') )
			{
				if ($sResType === "Diagram")
				{
					$sDiagramLayoutNo = SafeGetInternalArrayParameter($_SESSION, 'diagramlayout', '1');
					if ($sDiagramLayoutNo === '2')
					{
						include('maindiagramlist.php');
					}
					elseif ($sDiagramLayoutNo === '3')
					{
						include('maindiagramspecman.php');
					}
					else
					{
						include('maindiagramimage.php');
					}
					$bSupportsMiniProps = true;
				}
				else if ($sResType === "ModelRoot" || $sResType === "Package" || $sResType === "Element" || strIsEmpty($sResType))
				{
					$bSupportsMiniProps = true;
					if ( $bShowingBrowser )
					{
						include('mainproperties.php');
					}
					else
					{
						$sMainLayoutNo = SafeGetInternalArrayParameter($_SESSION, 'mainlayout', '1');
						if ($sMainLayoutNo === '2')
						{
							include('mainpackagelist.php');
						}
						elseif ($sMainLayoutNo === '3')
						{
							include('mainpackagespecman.php');
						}
						else
						{
							include('mainpackageicon.php');
						}
					}
				}
			}
		}
	}
	if (IsSessionSettingTrue('show_miniproperties') &&
		($sLinkType   !== 'edit') &&
		($sObjectGUID !== 'searchresults') &&
		($bSupportsMiniProps))
	{
		echo '<div id="main-mini-properties-view">';
		echo '  <div id="miniprops-busy-loader"><img src="images/navbarwait.gif" alt="" class="miniprops-spinner" height="26" width="26"></div>';
		echo '  <div id="mini-properties-view-section">';
		include('miniproperties.php');
		echo '  </div>';
		echo '</div>';
	}
	echo '<div id="webea-viewing-mode" class="w3-hide">' . $g_sViewingMode . '</div>';
	echo '<div id="webea-miniprops-navigates" class="w3-hide">' . (strIsTrue(SafeGetInternalArrayParameter($_SESSION, 'miniprops_navigates', 'true')) ? '1' : '0') . '</div>';
	echo '<div id="webea-navigate-to-diagram" class="w3-hide">' . (strIsTrue(SafeGetInternalArrayParameter($_SESSION, 'navigate_to_diagram', 'true')) ? '1' : '0') . '</div>';
	echo '<div id="webea-last-oslc-error-code" class="w3-hide">' . $g_sLastOSLCErrorCode . '</div>';
	echo '<div id="webea-last-oslc-error-msg" class="w3-hide">' . $g_sLastOSLCErrorMsg . '</div>';
	echo BuildSystemOutputDataDIV();
	unset($webea_page_parent_mainview);
?>