<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	error_reporting(E_ERROR | E_WARNING | E_NOTICE);
	setcookie('webea_dummy', 'webea', 0, '/' );
	$sRootPath = dirname(__FILE__);
	require_once $sRootPath . '/globals.php';
	$sAutoLoadModel		= SafeGetInternalArrayParameter($_GET, 'm', '');
	$sAutoLoadObject	= SafeGetInternalArrayParameter($_GET, 'o', '');
	SafeStartSession();
	$bRequiresLogin = false;
	if (isset($_SESSION['authorized']) === false)
	{
		$bRequiresLogin = true;
	}
	else
	{
		$sCurrModelNo = SafeGetInternalArrayParameter($_SESSION, 'model_no', '');
		if ( !strIsEmpty($sAutoLoadModel) )
		{
			if ( $sAutoLoadModel !== $sCurrModelNo )
			{
				if (session_destroy())
				{
					$bRequiresLogin = true;
				}
			}
		}
	}
	if ($bRequiresLogin)
	{
		$sURLLocation = 'location: login.php';
		$sURLParameters = '';
		if ( !strIsEmpty($sAutoLoadModel) )
		{
			$sURLParameters .= 'm=' . $sAutoLoadModel;
		}
		if ( !strIsEmpty($sAutoLoadObject) )
		{
			if ( !strIsEmpty($sURLParameters) )
				$sURLParameters .= '&';
			$sURLParameters .= 'o=' . $sAutoLoadObject;
		}
		if ( !strIsEmpty($sURLParameters) )
		{
			$sURLLocation .= '?' . $sURLParameters;
		}
		header($sURLLocation);
		exit();
	}
	setcookie('webea_session', session_id(), 0, '/' );
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>WebEA<?php echo ' - ' . htmlspecialchars($_SESSION['model_name']);?></title>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<link type="text/css" rel="stylesheet" href="styles/webea.css?v=<?php echo filemtime('styles/webea.css') ; ?>" />
	<link type="text/css" rel="stylesheet" href="styles/jquery.datepick.blue.css" />
	<link rel="shortcut icon" href="./favicon.ico?<?php echo filemtime('favicon.ico') ?>" />
	<script src="js/jquery.min.js"></script>
	<script src="js/webea.js"></script>
	<script src="js/nicedit.js"></script>
	<script src="js/jquery.plugin.js"></script>
	<script src="js/jquery.datepick.js"></script>
</head>
<body>
	<?php
		$webea_page_parent_index = true;
		$modelName = SafeGetInternalArrayParameter($_SESSION, 'model_name');
		echo '<div id="main-tl-swoosh"><img src="images/spriteplaceholder.png" class="mainsprite-procloudswoosh" alt=""/></div>';
		echo '<header>';
		echo '	<div id="header-left">';
		echo '		<div id="main-tl-logo"><img src="images/logo-full.png" alt="" height="32" width="111"/></div>';
		echo '		<div id="project-title">' . htmlspecialchars($modelName) . '</div>';
		echo '	</div>';
		echo '	<div id="header-right">';
					include('hamburger.php');
		echo '	</div>';
		echo '</header>';
		echo '<div class="w3-hide">';
		echo '  <div id="model-default-diagram">' . htmlspecialchars($_SESSION['default_diagram']) . '</div>';
		echo '  <div id="model-lastpage-timeout">' . $_SESSION['lastpage_timeout'] . '</div>';
		include('stringsforjs.php');
		echo '</div>' . PHP_EOL;
		include ('nojs.php');
		echo '<div id="webea-main-content">';
		echo '<div id="main-navbar">' . PHP_EOL;
		include('navbar.php');
		echo '</div>' . PHP_EOL;
		$_POST['objectguid'] = 'initialize';
		$bShowSystemOutput =  IsSessionSettingTrue('show_system_output');
		echo '<div id="main-contents" ' . (($bShowSystemOutput)? 'class="show_sysoutput"' : '') .  '>' . PHP_EOL;
		echo '<div id="main-busy-loader1"><img src="images/mainwait.gif" alt="" class="main-spinner" height="62" width="62"></div>';
		echo '<div id="main-contents-sub">' . PHP_EOL;
		$_POST['objectguid'] = 'initialize';
		echo '<div id="main-content-center">';
		include('mainview.php');
		echo '</div>' . PHP_EOL;
		echo '</div>' . PHP_EOL;
		echo '</div>' . PHP_EOL;
		echo '<div id="main-statusbar" ' . (($bShowSystemOutput)? 'class="show_sysoutput"' : '') .  '>';
		include('statusbar.php');
		echo '</div>' . PHP_EOL;
		echo '<div id="main-page-overlay">&nbsp;</div>';
		echo '<div id="webea-session-timeout">';
		echo '<div class="session-timeout-section">';
		echo '<div class="session-timeout-image"><img src="images/spriteplaceholder.png" class="mainsprite-warning" alt=""></div>';
		echo '<div class="session-timeout-line1">Session Expired!</div>';
		echo '<div class="session-timeout-line2">Your session has timed out. Click <a href="logout.php">here</a> to re-login.</div>';
		echo '</div>';
		echo '</div>';
		echo '<div id="webea-about-dialog">';
		echo '<div class="webea-about-dialog-title">' . _glt('About WebEA') . '<input class="webea-dialog-close-button" type="button" value="" onclick="OnClickClosePopupDialog(\'#webea-about-dialog\')"/></div>';
		echo '<div class="webea-about-dialog-body">';
		echo '<div><img src="images/spriteplaceholder.png" class="mainsprite-webealogos" alt=""></div>';
		echo '<div class="webea-about-dialog-appversion"><div id="webea-about-line-value-appversion"></div></div>';
		echo '<div class="webea-about-dialog-copyright">Copyright &copy; ' . g_csCopyRightYears . ' Sparx Systems Pty Ltd.  All rights reserved.</div>';
		echo '<div class="webea-about-dialog-agreement">The use of this product is subject to the terms of the End User License Agreement (EULA), unless otherwise specified.</div>';
		echo '<div class="webea-about-line-header collapsible-plusminussection-closed" onclick="OnTogglePlusMinusState(this, \'webea-about-sspcs-section\')"><div class="collapsible-block-text">' . _glt('Sparx Systems Pro Cloud Services') . '</div></div>';
		echo '<div id="webea-about-sspcs-section" style="display: none;">';
		echo '<div id="webea-about-line-author"><div class="webea-about-line-label">' . _glt('Author') . '</div><div class="webea-about-line-value" id="webea-about-line-value-author"></div></div>';
		echo '<div id="webea-about-line-server"><div class="webea-about-line-label">' . _glt('Host server') . '</div><div class="webea-about-line-value" id="webea-about-line-value-server"></div> </div>';
		echo '<div id="webea-about-line-pcsversion"><div class="webea-about-line-label">' . _glt('PCS Version') . '</div><div class="webea-about-line-value" id="webea-about-line-value-pcsversion"></div> </div>';
		echo '<div id="webea-about-line-sscsversion"><div class="webea-about-line-label">' . _glt('OSLC Version') . '</div><div class="webea-about-line-value" id="webea-about-line-value-sscsversion"></div> </div>';
		echo '<div id="webea-about-line-sscslicense"><div class="webea-about-line-label">' . _glt('License') . '</div><div class="webea-about-line-value" id="webea-about-line-value-sscslicense"></div> </div>';
		echo '<div id="webea-about-line-sscslicenseexpiry"><div class="webea-about-line-label">' . _glt('License Expiry') . '</div><div class="webea-about-line-value" id="webea-about-line-value-sscslicenseexpiry"></div> </div>';
		echo '</div>';
		echo '<div class="webea-about-line-header collapsible-plusminussection-closed" onclick="OnTogglePlusMinusState(this, \'webea-about-model-section\')"><div class="collapsible-block-text">' . _glt('Model details') . '</div></div>';
		echo '<div id="webea-about-model-section" style="display: none;">';
		echo '<div id="webea-about-line-port"><div class="webea-about-line-label">' . _glt('Read only') . '</div><div class="webea-about-line-value" id="webea-about-line-value-readonly"></div> </div>';
		echo '<div id="webea-about-line-security"><div class="webea-about-line-label">' . _glt('User security') . '</div><div class="webea-about-line-value" id="webea-about-line-value-security"></div> </div>';
		echo '<div id="webea-about-line-user"><div class="webea-about-line-label">' . _glt('User') .'</div><div class="webea-about-line-value" id="webea-about-line-value-user"></div> </div>';
		echo '<div id="webea-about-line-review"><div class="webea-about-line-label">' . _glt('Reviewing') . '</div><div class="webea-about-line-value" id="webea-about-line-value-review"></div> </div>';
		echo '</div>';
		echo '<div class="webea-about-line-cancel"><input class="webea-main-styled-button webea-about-cancel" type="button" value="' . _glt('Close') . '" onclick="OnClickClosePopupDialog(\'#webea-about-dialog\')"/> </div>';
		echo '</div>';
		echo '</div>';
		echo '<div id="webea-messagebox-dialog">';
		echo '<div class="webea-messagebox-title"><div id="webea-messagebox-title-text">' . _glt('WebEA error') . '</div><input class="webea-dialog-close-button" type="button" value="" onclick="OnClickClosePopupDialog(\'#webea-messagebox-dialog\')"/></div>';
		echo '<div class="webea-messagebox-body">';
		echo '<div class="webea-messagebox-line"><div class="webea-messagebox-image"><img src="images/spriteplaceholder.png" class="mainsprite-warning margin-right-10" alt=""></div></div>';
		echo '<div id="webea-messagebox-line-message"><div class="webea-messagebox-line-value" id="webea-messagebox-line-value-message"></div> </div>';
		echo '<div class="webea-about-line-cancel"><input class="webea-main-styled-button webea-about-cancel" type="button" value="' . _glt('OK') . '" onclick="OnClickClosePopupDialog(\'#webea-messagebox-dialog\')"/> </div>';
		echo '</div>';
		echo '</div>';
		echo '<div id="webea-success-message">';
		echo '<div class="webea-success-message-text" id="webea-success-message-text"></div>';
		echo '</div>';
		echo '<div id="webea-error-message">';
		echo '<div class="webea-error-message-text" id="webea-error-message-text"></div>';
		echo '</div>';
		if ((g_csOSLCVersion !== $_SESSION['oslc_version']) && (!isset($_SESSION['hide_mismatch_warning'])))
		{
			echo '<div id="webea-display-warning-message" class="oslc-mismatch-warning" style="display: block;">';
			echo '<div class="webea-display-warning-message-text" id="webea-display-warning-message-text">';
			echo _glt('Version mismatch') . ' <a href="'.g_csHelpLocation.'model_repository/webea_troubleshoot.html">[Help]</a><a href="javascript:toggle_visibility(&quot;.oslc-mismatch-warning&quot;)" style="float: right;">'._glt('Close').'</a>';
			echo '<br><br>';
			echo str_replace('%VERSION%', g_csWebEAVersion, _glt('WebEA Version: xx'));
			echo '<br>';
			echo str_replace('%VERSION%', $_SESSION['oslc_version'], _glt('OSLC Version: xx'));
			echo '<br><br>';
			echo str_replace('%VERSION%', g_csOSLCVersion, _glt('Intended OSLC version: xx'));
			echo '<br>';
			echo '</div>';
			echo '</div>';
			$_SESSION['hide_mismatch_warning'] = true;
		}
		echo '<div id="webea-show-link-dialog">';
		echo '<div class="webea-show-link-title"><div id="webea-show-link-title-text">' . _glt('Link to WebEA item') . '</div><input class="webea-dialog-close-button" type="button" value="" onclick="OnClickClosePopupDialog(\'#webea-show-link-dialog\')"/></div>';
		echo '<div class="webea-show-link-body">';
		echo '<div class="webea-show-link-line" style="padding-top: 20px;">' . _glt('GUID of the current item') . '</div>';
		echo '<div class="webea-show-link-line"><input id="webea-show-link-textarea" class="webea-main-styled-textbox-small" type="text" maxlength="40"></div>';
		echo '<div class="webea-show-link-line" style="padding-top: 20px;">' . _glt('Full URL') . '</div>';
		echo '<div class="webea-show-link-line"><input id="webea-show-fulllink-textarea" class="webea-main-styled-textbox-small" type="text" maxlength="100"></div>';
		echo '<div class="webea-about-line-cancel"><input class="webea-main-styled-button webea-about-cancel" type="button" value="' . _glt('Close') . '" onclick="OnClickClosePopupDialog(\'#webea-show-link-dialog\')"/> </div>';
		echo '</div>';
		echo '</div>';
		echo '<div id="webea-goto-link-dialog">';
		echo '<div class="webea-goto-link-title"><div id="webea-goto-link-title-text">' . _glt('Goto WebEA item') . '</div><input class="webea-dialog-close-button" type="button" value="" onclick="OnClickClosePopupDialog(\'#webea-goto-link-dialog\')"/></div>';
		echo '<div class="webea-goto-link-body">';
		echo '<div class="webea-goto-link-line" style="padding-top: 20px;">' . _glt('Paste a WebEA GUID below') . '</div>';
		echo '<div class="webea-goto-link-line"><input id="webea-goto-link-textarea" class="webea-main-styled-textbox-small" type="text" maxlength="40" onkeypress="OnGotoGUIDTextKeypress(event)"></div>';
		echo '<div class="webea-about-line-cancel"><input class="webea-main-styled-button webea-about-cancel" type="button" value="' . _glt('Go') . '" onclick="OnWebEAGotoGUID()"/> </div>';
		echo '</div>';
		echo '</div>';
		echo '<div id="webea-stereotype-list">';
		echo '<div class="webea-stereotype-list-body">';
		echo '<div class="webea-stereotype-list-hdr">' . _glt('Stereotypes') . '</div>';
		echo '<div id="webea-stereotype-list-items">&nbsp;</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		if ($bShowSystemOutput)
		{
			echo '<div style="bottom:0px; height:160px; width:100%; position:absolute; display:inline-block; background-color: white;border-radius: 4px;" onkeypress="OnKeyUpSystemOutput(event)">';
			echo '<div style="overflow: auto; height:100%; width:100%;">';
			echo '<div class="sysout-btn-wrapper">';
			echo '<button class="sysout-delete-btn" title="Clear" onclick="OnClickClearSystemOutput()"><img src="images/spriteplaceholder.png" class="mainsprite-sysout-delete" alt=""/></button>';
			echo '<button class="sysout-copy-btn" title="Copy All" onclick="OnClickCopySystemOutput()"><img src="images/spriteplaceholder.png" class="mainsprite-sysout-copy" alt=""/></button>';
			echo '</div>';
			echo '<div id="webea-system-output">';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
		unset($webea_page_parent_index);
	?>
<script>
	$(document).ready(function(event)
	{
		var sSessionID = get_cookie_value('webea_session');
		var sPageModelNo = $("#page-built-with-model-no").text();
		var sLastPage = get_cookie_value('webea_lastpage_' + sPageModelNo);
		var sURLParameters = window.location.search;
		var sCurrPage = get_current_history_state();
		if ( typeof(sCurrPage) !== 'undefined' && (sLastPage !== null && sLastPage !== '') )
		{
			if ( sURLParameters !== '' )
			{
				var sObject2Load = '';
				var sModelNo2Load = '';
				if ( sURLParameters.substring(0,1) === '?' )
				{
					sURLParameters = sURLParameters.substring(1);
				}
				var a = sURLParameters.split("&");
				if (a.length > 0)
				{
					for (var i=0; i < a.length; i++)
					{
						if (a[i].substring(0,2)==='m=')
						{
							sModelNo2Load = a[i].substring(2);
						}
						if (a[i].substring(0,2)==='o=')
						{
							sObject2Load = a[i].substring(2);
						}
					}
					if ( sObject2Load!=='' && sModelNo2Load!=='' )
					{
						if (sPageModelNo===sModelNo2Load)
						{
							load_object_by_guid(sObject2Load, true);
						}
						else
						{
							webea_alert(get_translate_string('unable_to_load_objects_for_other_models'));
						}
					}
					else
					{
						webea_alert(get_translate_string('invalid_full_webea_url'));
					}
				}
				return;
			}
		}
		if ( sLastPage === null || sLastPage === '' )
		{
			var sURLParameters = window.location.search;
			var sObject2Load = '';
			var a = sURLParameters.split("&");
			if (a.length > 0)
			{
				for (var i=0; i < a.length; i++)
				{
					if (a[i].substring(0,2)==='o=')
					{
						sObject2Load = a[i].substring(2);
					}
				}
			}
			if ( sObject2Load !== '' )
			{
				var sPrefix = String(sObject2Load).substr(0,3);
				if (sPrefix === 'mr_'  || sPrefix === 'pk_' || sPrefix === 'dg_' || sPrefix === 'el_')
				{
					replace_history_state('', '', '', '', '', '', 'WebEA - ' + get_translate_string('jsmess_modelroot'), 'index.php');
					load_diagram_object(sObject2Load, false);
				}
				else
				{
					replace_history_state('', '', '', '', '', '', 'WebEA - ' + get_translate_string('jsmess_modelroot'), 'index.php');
					load_object_by_guid(sObject2Load, false);
				}
			}
			else
			{
				var bSomethingLoaded = load_home(true)
			}
		}
		else
		{
			var sCurrPage = get_current_history_state();
			if ( sCurrPage == sLastPage )
			{
				load_object2_fromstring(sLastPage, false);
			}
			else
			{
				replace_history_state('', '', '', '', '', '', 'WebEA - ' + get_translate_string('jsmess_modelroot'), 'index.php');
				load_object2_fromstring(sLastPage, true);
			}
			clear_last_page();
		}
		g_TimerRef = window.setInterval(TimerCheck, 15000);
	});
	function OnClickClosePopupDialog(sDialogName)
	{
		$( "#main-page-overlay" ).hide();
		$( sDialogName ).hide();
	}
	window.addEventListener('popstate', function(event) {
		OnIndexPopState(event);
	}, false);
	window.addEventListener('beforeunload', function(event) {
		OnStoreLastPage(event);
	});
</script>
</body>
</html>