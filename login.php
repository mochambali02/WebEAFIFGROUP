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
	$sRootPath = dirname(__FILE__);
	require_once $sRootPath . '/globals.php';
	SafeStartSession();
?>
<!DOCTYPE html>
<html>
<head>
	<title>Web EA - login</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" href="./favicon.ico?<?php echo filemtime('favicon.ico') ?>" />
	<link type="text/css" rel="stylesheet" href="styles/webea.css?v=<?php echo g_csWebEAVersion; ?>"/>
	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script type="text/javascript" src="js/webea.js"></script>
<style>
body {background-color:#eaeaea;}
[type="radio"] {border: 0; clip:rect(0 0 0 0);height:1px;margin:-1px;overflow:hidden;padding:0;position:absolute;width: 1px;}
[type="radio"] + span {display: block;}
[type="radio"] + span:before {content: '';display: inline-block;width: 1em;height: 1em;vertical-align: -0.25em;border: 0.1em solid #fff;box-shadow: 0 0 0 1px #000;margin-right: 1em;}
[type="radio"]:checked + span:before {background: #3777bf;ox-shadow: 0 0 0 3px #555555;}
#login-auth-header {color:white;}
#main-footer {color:#333;}
.login-field-img-layout-img {padding: 4px 0px 0px 5px;}
#main-tl-swoosh {display:none;}
</style>
</head>
<body>
	<div id="main-tl-swoosh"><img src="images/spriteplaceholder.png" class="mainsprite-procloudswoosh" alt=""/></div>
	<header>
		<img src="images/logo-login.png" alt="" height="32" width="111"/>
	</header>
<?php
		$aSettings = ParseINIFile2Array('includes/webea_config.ini');
		include ('nojs.php');
		echo '<div class="w3-hide">';
		include_once('stringsforjs.php');
		echo '</div>' . PHP_EOL;
		$sErrorMsg 			= '';
		$sModelName			= '';
		$sModelNo			= '';
		$sAuthType			= '';
		$sAccessCode		= '';
		$sModelFriendlyName = '';
		$sUserID 			= '';
		$sPassword			= '';
		$sCurrentStep 		= 'prompt_model';
		$sShowAccessCode 	= '1';
		$sShowLoginFields 	= '1';
		$sShowInsecureWarning = '1';
		$sAllowBlankPwd		= '0';
		$sDeviceLayout		= 'd';
		$sLoginSSOType		= '';
		$sLoginOpenIDURL	= '';
		$sAutoLoadModel		= SafeGetInternalArrayParameter($_GET, 'm', '');
		$sAutoLoadObject	= SafeGetInternalArrayParameter($_GET, 'o', '');
		SafeStartSession();
		if ( !strIsEmpty($sAutoLoadModel) )
		{
			$sModelNo = $sAutoLoadModel;
		}
		else
		{
			$sModelNo = SafeGetInternalArrayParameter($_SESSION, 'model_no');
		}
		$sErrorMsg = SafeGetInternalArrayParameter($_SESSION, 'login_error');
		$_SESSION['login_error'] = '';
		echo '<div id="main-page-overlay">&nbsp;</div>';
		if ( isset($_SESSION['authorized']) && isset($_SESSION['model_no']) )
		{
			$sURLLocation = 'location: index.php';
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
		echo '<div id="webea-main-content">';
		$aMissingModules = CheckMissingModules();
		if (empty($aMissingModules))
		{
			$iCnt = 0;
			if (isset($aSettings['model_list']))
			{
				$iCnt = count($aSettings['model_list']);
			}
			echo '<div id="sign-in">';
			if ( $iCnt > 0 )
			{
				echo '<form class="form-signin" name="login-form" role="form" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method = "post">';
				echo '<div id="signin-prompt">';
				echo '<div id="login-busy-loader" style="display:none">';
				echo '	<img src="images/mainwait.gif" alt="" class="main-spinner" height="62" width="62">';
				echo '</div>';
				echo '<div id="login-model-section">';
				echo 	'<div class="login-header-header">';
				echo 		'<div class="login-header-text">' . _glt('Select Model') . '</div>';
				echo 	'</div>';
				echo 	'<div class="login-content-row">';
				echo 		'<fieldset class="login-model-section-fieldset">';
				echo 			'<div id="signin-prompt-models">';
				for ($i=1; $i<=$iCnt; $i++)
				{
					$sValue 	= SafeGetArrayItem2DimByName($aSettings, 'model_list', 'model' . $i, '');
					if ( !strIsEmpty($sValue) )
					{
						$sValue 		= htmlspecialchars($sValue);
						$sAccessCode	= SafeGetArrayItem2DimByName($aSettings, 'model' . $i, 'auth_code', '');
						$sProtocol		= SafeGetArrayItem2DimByName($aSettings, 'model' . $i, 'sscs_protocol', 'http');
						echo '<label class="login-model-radio-label" for="model' . $i . '">';
						echo '	<input type="radio" name="modelname" id="model' . $i . '" value="model' . $i . (($i === (int)$sModelNo) ? '" checked="checked"' : '"') . '/>';
						echo '	<span>' . $sValue . '</span>';
						echo '</label>';
					}
				}
				echo 			'</div>';
				echo 		'</fieldset>';
				echo 	'</div>';
				echo '</div>';
				echo '<div id="login-auth-section" style="display:none;">';
				echo 	'<div class="login-header-header">';
				echo 		'<div class="login-header-text">'._glt('Login').'</div>';
				echo 		'<div id="login-auth-header">'.htmlspecialchars($sModelFriendlyName).'</div>';
				echo 	'</div>';
				echo 	'<div class="login-content-row ">';
				echo 		'<div id="login-auth-sso-section" style="display:none;">';
				echo 			'<div id="login-ssoauth-type-openid">';
				echo 				'<div class="login-action-button-sso login-action-button-sso-openid" title="' . _glt('Login with OpenID') . '" onclick="OnClickLoginOpenIDButton()">';
				echo 					'<div class="login-sso-icon"><img src="images/spriteplaceholder.png" class="mainsprite-login-sso-openid-white" alt="" /></div>';
				echo 					'<div id="openid-button-label">' . _glt('Login with OpenID') . '</div>';
				echo 				'</div>';
				echo 			'</div>';
				echo 			'<div id="login-ssoauth-type-ntlm">';
				echo 				'<div class="login-action-button-sso login-action-button-sso-ntlm" title="' . _glt('Login with NTML') . '" onclick="OnClickLoginNTLMButton()">';
				echo 					'<div class="login-ntlm-icon"><img src="images/spriteplaceholder.png" class="mainsprite-login-sso-ntlm-white" alt="" /></div>';
				echo 					'<div id="ntlm-button-label">' . _glt('Login with Windows ID') . '</div>';
				echo 				'</div>';
				echo 			'</div>';
				echo '<div id="login-ssoauth-or">';
				echo 'OR';
				echo '</div>';
				echo 		'</div>';
				echo 		'<div id="login-auth-access-div">';
				echo 			'<div class="login-accesscode-div">';
				echo 				'<div class="login-field-div">';
				echo 					'<div class="login-field-img-layout"><div class="mainsprite-login-key"></div></div><input id="login-accesscode-text" class="login-textbox" type="password" name="access-code" placeholder="' . _glt('Access code') . '" title="' . _glt('Access code') . '" onkeypress="return OnKeyPressLogin(event)" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"/>';
				echo 				'</div>';
				echo 			'</div>';
				echo 		'</div>';
				echo 		'<div id="login-auth-basic-section">';
				echo 			'<div class="login-field-div"><div class="login-field-img-layout"><div class="mainsprite-login-user"></div></div><input id="login-userid-text" class="login-textbox" type="text" name="userid" value="' . $sUserID . '" placeholder="' . _glt('User') . '" title="' . _glt('User') . '" onkeypress="return OnKeyPressLogin(event)" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"/></div>';
				echo 			'<div class="login-field-div"><div class="login-field-img-layout"><div class="mainsprite-login-pwd"></div></div><input id="login-password-text" class="login-textbox" type="password" name="password" placeholder="' . _glt('Password') . '" title="' . _glt('Password') . '" onkeypress="return OnKeyPressLogin(event)" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"/>';
				if ($sShowInsecureWarning==='1')
				{
					echo '<div id="login-insecure-warning-div"><img alt="" src="images/spriteplaceholder.png" class="mainsprite-warning-login" title="' . _glt('HTTP Warning') . '" ></div>';
				}
				echo 			'</div>';
				echo 		'</div>';
				echo '<div id="login-openid-link" class="login-hyperlink" style="display:none;"><a>' . _glt('Switch to') . '&nbsp;</a><a class="w3-link" onclick="OnClickOpenIDLogin()" title="' . _glt('Switch to') . ' ' . _glt('OpenID Login') . '">' . _glt('OpenID Login') . '</a></div>';
				echo 	'</div>';
				echo '</div>';
				echo '</div>';
				if ($sErrorMsg !== '')
				{
					$sErrorMsg = $sErrorMsg . WriteHelpHyperlink();
				}
				echo 	'<div class="login-content-row login-padding-btm-20">';
				echo '<div id="login-error">' . $sErrorMsg . '</div>';
				echo '<div class="login-action-buttons-div1"><div class="login-action-buttons-div2">';
				echo '<input class="login-action-button" type="button" name="back" id="login-back-button" value="' . _glt('Back') . '" ' .  ( ( $sCurrentStep === 'prompt_auth' ) ? '' : ' style="display:none;"') . ' onclick="OnClickLoginBackButton(this)">';
				echo '<div class="login-action-layoutoption-button login-next-button" onclick="OnClickLoginNextButton(this,\'d\')"><img src="images/spriteplaceholder.png" class="mainsprite-login-layout-desktop" alt="" /><div class="login-layoutoption-btn-text">Desktop &gt;</div></div>';
				echo '<div class="login-action-layoutoption-button login-next-button" onclick="OnClickLoginNextButton(this,\'t\')"><img src="images/spriteplaceholder.png" class="mainsprite-login-layout-tablet" alt="" /><div class="login-layoutoption-btn-text">Tablet &gt;</div></div>';
				echo '<div class="login-action-layoutoption-button login-next-button" onclick="OnClickLoginNextButton(this,\'m\')"><img src="images/spriteplaceholder.png" class="mainsprite-login-layout-mobile" alt="" /><div class="login-layoutoption-btn-text">Mobile &gt;</div></div>';
				echo '<input class="login-action-button" type="button" name="finish" id="login-finish-button" value="' . _glt('Login') . '" style="display:none;" onclick="OnClickLoginLoginButton(this)">';
				echo '</div></div>';
				echo '<input type="hidden" name="autoloadmodel" id="login-auto-load-model" value="' . $sAutoLoadModel . '">';
				echo '<input type="hidden" name="autoloadobject" id="login-auto-load-object" value="' . $sAutoLoadObject . '">';
				echo '<input type="hidden" name="devicelayout" id="login-layout-option" value="' . $sDeviceLayout . '">';
				echo 	'</div>';
				echo '</form>';
				echo '</div>';
			}
			else
			{
				echo '<div id="signin-prompt"></div>';
				echo '<div class="login-configure-error">There are no models configured, please get your system administrator to define at least one model.</div>';
			}
		}
		else
		{
			echo '<div id="sign-in">';
			echo '<div class="login-configure-error">';
			echo 'Your web server environment does not have the following modules installed:' . g_csHTTPNewLine . g_csHTTPNewLine;
			foreach ($aMissingModules as $sModule)
			{
				echo '&nbsp;&nbsp;- ' . $sModule . g_csHTTPNewLine;
			}
			echo g_csHTTPNewLine. 'Contact your web master to correct this.';
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';
		echo '<div id="webea-warning-message">';
		echo '<div class="webea-warning-message-text" id="webea-warning-message-text"></div>';
		echo '</div>';
		echo '<div id="webea-error-message">';
		echo '<div class="webea-error-message-text" id="webea-error-message-text"></div>';
		echo '</div>';
?>
	<footer>
		<div id="main-footer">
    	<div class="main-footer-copyright"> Copyright &copy; <?php echo g_csCopyRightYears; ?> Sparx Systems Pty Ltd.  All rights reserved.</div>
     	<div class="main-footer-version"> WebEA v<?php echo g_csWebEAVersion; ?> </div>
		</div>
	</footer>
<?php
	function CheckMissingModules()
	{
		$aModules = array('core', 'curl', 'date','gettext','hash','json','libxml','mbstring','pcre','session','standard','tokenizer');
		$aUnavailable = array();
		foreach ($aModules as $sModule)
		{
			if (extension_loaded($sModule) !== true)
			{
				$aUnavailable[] = $sModule;
			}
		}
		return $aUnavailable;
	}
?>
<script>
	var g_modelDetails;
	var g_deviceLayout;
	window.onpageshow = function(event) {
		if (event.persisted)
		{
			LoginNotBusy();
		}
	};
	function OnKeyPressLogin(element)
	{
		if ($("#login-finish-button").css('display') != 'none')
		{
			if (element.keyCode == 13)
			{
				OnClickLoginLoginButton(element);
				return false;
			}
		}
		return true;
	}
	function OnClickLoginNextButton(element, sLayout)
	{
		g_modelDetails = [];
		g_deviceLayout = sLayout;
		var sErrorMessage = '';
		$("#login-error").html('');
		var ele = $('input[name=modelname]:checked')[0];
		if (!ele)
		{
			sErrorMessage = 'No model selected!';
			$("#login-error").html(sErrorMessage);
			return;
		}
		var sValue = ele.id;
		if (!sValue)
		{
			sErrorMessage = 'No model selected!';
			$("#login-error").html(sErrorMessage);
			return;
		}
		var sModelNo = sValue.substr(5);
		console.log(sModelNo);
		var eleNext = ele.nextElementSibling;
		var sModelFriendlyName = '';
		if (eleNext)
		{
			sModelFriendlyName = $(eleNext).text();
		}
		if (sModelFriendlyName === '')
		{
			sErrorMessage = 'Unable to determine model name!';
			$("#login-error").html(sErrorMessage);
			return;
		}
		$("#login-selected-model-name").val(sModelFriendlyName);
		var sURL = './data_api/select_model.php';
		$.ajax(
		{
			type: "POST",
			url: sURL,
			data:
			{
				modelNumber: sModelNo,
				deviceLayout: sLayout
			},
			beforeSend: function()
			{
				LoginBusy();
			},
			success: function (modelDetails)
			{
				if (modelDetails == null)
				{
					sErrorMessage = get_translate_string('unable_to_retrieve_server_details');
					$("#login-error").html(sErrorMessage);
					return;
				}
				modelDetails['modelFriendlyName'] = sModelFriendlyName;
				g_modelDetails = modelDetails;
					LoginNotBusy();
					ContinueLogin();
			},
			error: function(jqXHR)
			{
				if ( jqXHR.status === 200 )
				{
					sErrorMessage = jqXHR.responseText;
					sErrorMessage = sErrorMessage.substring(0, sErrorMessage.indexOf('{'));
				}
				else
				{
					Write2Console(jqXHR.statusText + ": Error during the initial retrieval of model details:" + jqXHR.responseText);
					sErrorMessage = jqXHR.responseText;
				}
				$("#login-error").html(sErrorMessage);
				LoginNotBusy();
			},
			complete: function()
			{
			}
		});
	}
	function OnClickLoginBackButton()
	{
		$("#login-error").html('');
		g_modelDetails = [];
		$("#login-model-section").show();
		$("#login-auth-section").hide();
		$(".login-next-button").show();
		$('#login-openid-link').hide();
		$("#login-back-button").hide();
		$("#login-finish-button").hide();
		$("#login-step").val('prompt_model');
	}
	function ContinueLogin()
	{
		g_sFieldToFocus = '';
		if (g_modelDetails === undefined)
		{
			return;
		}
		if (!g_modelDetails['requiresAccessCode']
			&& (!g_modelDetails['securityEnabled'] || g_modelDetails['credentialsSetInConfig']))
		{
			OnClickLoginLoginButton();
			return;
		}
		$("#login-model-section").hide();
		$(".login-next-button").hide();
		$("#login-auth-section").show();
		$("#login-auth-header").text(g_modelDetails['modelFriendlyName']);
		if (g_modelDetails['allowOpenIDAuthentication'] || g_modelDetails['allowWindowsAuthentication'] )
		{
			$("#login-auth-sso-section").show();
			if (g_modelDetails['allowWindowsAuthentication'])
			{
				$("#login-ssoauth-type-ntlm").show();
			}
			else
			{
				$("#login-ssoauth-type-ntlm").hide();
			}
			if (g_modelDetails['allowOpenIDAuthentication'])
			{
				$("#login-ssoauth-type-openid").show();
			}
			else
			{
				$("#login-ssoauth-type-openid").hide();
			}
			if (g_modelDetails['allowBasicAuthentication'])
			{
				$("#login-ssoauth-or").show();
			}
			else
			{
				$("#login-ssoauth-or").hide();
			}
		}
		else
		{
			$("#login-auth-sso-section").hide();
			$("#login-ssoauth-type-openid").hide();
			$("#login-ssoauth-type-ntlm").hide();
			$("#login-ssoauth-or").hide();
		}
		if (g_modelDetails['requiresAccessCode'] && (!g_modelDetails['allowOpenIDAuthentication'] && !g_modelDetails['allowWindowsAuthentication']))
		{
			$("#login-auth-access-div").show();
		}
		else
		{
			$("#login-auth-access-div").hide();
		}
		if (g_modelDetails['allowBasicAuthentication'])
		{
			$("#login-auth-basic-section").show();
			$("#login-finish-button").show();
			g_sFieldToFocus = '#login-userid-text';
		}
		else
		{
			$("#login-auth-basic-section").hide();
			$("#login-finish-button").hide();
		}
		if (g_modelDetails['protocol']==='https')
		{
			$("#login-insecure-warning-div").hide();
		}
		else
		{
			$("#login-insecure-warning-div").show();
		}
		$("#login-back-button").show();
		if ( g_modelDetails['requiresAccessCode'] && (!g_modelDetails['allowOpenIDAuthentication'] && !g_modelDetails['allowWindowsAuthentication']) )
		{
			$("#login-finish-button").show();
			g_sFieldToFocus = '#login-accesscode-text';
		}
		if ( g_sFieldToFocus != '' )
		{
			$(g_sFieldToFocus).focus();
		}
	}
	function OnClickLoginLoginButton()
	{
		var sErrorMessage = '';
		var bSSOLogin = false;
		$("#login-error").html('');
		var accessCode = $("#login-accesscode-text").val();
		if (g_modelDetails['requiresAccessCode'] && (!g_modelDetails['allowOpenIDAuthentication'] && !g_modelDetails['allowWindowsAuthentication']))
		{
			if (!accessCode)
			{
				sErrorMessage = 'Access code is required!';
				$("#login-error").html(sErrorMessage);
				return;
			}
		}
		if (g_modelDetails['allowBasicAuthentication'])
		{
			var sUserID = '';
			var sPwd = '';
			sUserID = $("#login-userid-text").val();
			sPwd = $("#login-password-text").val();
			sUserID = sUserID.replace(/^\s+|\s+$/gm, '');
			var sAllowBlankPwd = $("#login-allow-blank-pwd").val();
			if (g_modelDetails['allowBlankPassword'])
			{
				if (!sUserID)
					sErrorMessage = 'Model User is required!';
			}
			else
			{
				if ( !sUserID && !sPwd)
					sErrorMessage = 'Model User and password are both required!';
				else if (!sUserID)
					sErrorMessage = 'Model User is required!';
				else if (!sPwd)
					sErrorMessage = 'Password is required!';
			}
		}
		if (sErrorMessage !== '')
		{
			$("#login-error").html(sErrorMessage);
			return;
		}
		var sSupportsSSO = (g_modelDetails['allowOpenIDAuthentication'] || g_modelDetails['allowWindowsAuthentication'])?'1':'0';
		if (!sUserID)
		{
			sUserID = '';
		}
		if (!sPwd)
		{
			sPwd = '';
		}
		var sURL = './data_api/login_regular.php';
		$.ajax(
		{
			type: "POST",
			data:
			{
				modelNumber: g_modelDetails['modelNumber'],
				accessCode: accessCode,
				username: sUserID,
				password: sPwd,
				supportssso: sSupportsSSO
			},
			url: sURL,
			beforeSend: function()
			{
				LoginBusy();
			},
			success: function(result, status, xhr)
			{
				window.location.replace('./index.php');
				return;
			},
			error: function(xhr, status, error)
			{
				$("#login-error").html(xhr.statusText);
			},
			complete: function(xhr, status)
			{
				LoginNotBusy();
			}
		});
	}
	function OnClickLoginOpenIDButton()
	{
		if (g_modelDetails['openIDURL'] === undefined || g_modelDetails['openIDURL'] === '')
		{
			var sErrorMessage = 'No OpenID URL defined in model';
			$("#login-error").html(sErrorMessage);
		}
		window.open(g_modelDetails['openIDURL'], '_self');
	}
	function OnClickLoginNTLMButton()
	{
		sModelNo = g_modelDetails['modelNumber'];
		var sURL = './data_api/ntlm/login_ntlm.php';
		$.ajax(
		{
			type: "POST",
			async: false,
			data:
			{
				modelno: sModelNo
			},
			url: sURL,
			beforeSend: function()
			{
				LoginBusy();
			},
			success: function(response, status, xhr)
			{
				let result = $.parseJSON(response);
				if(result.data.status == 'failed'){
					let sErrorMessage = result.data.message;
					webea_error_message(sErrorMessage);
					ContinueLogin();
					setTimeout(function(){ window.location.replace('./'+result['url']+''); }, 3000);
				} else {
					window.location.replace('./'+result['url']+'');
				}
				return;
			},
			error: function(xhr, status, error)
			{
				sErrorMessage = "Failed automatic Windows login: " + xhr.statusText;
				webea_error_message(sErrorMessage);
				ContinueLogin();
			},
			complete: function(xhr, status)
			{
				LoginNotBusy();
			}
		});
	}
	window.onload = function() {
		var isHtml5Compatible = document.createElement('canvas').getContext != undefined;
		if (!isHtml5Compatible)
		{
			webea_warning_message('Warning: HTML5 Support not detected. WebEA may not function as intended in this browser');
		}
	};
	function LoginBusy()
	{
		$("#login-busy-loader").show();
	}
	function LoginNotBusy()
	{
		$("#login-busy-loader").hide();
	}
</script>
</body>
</html>