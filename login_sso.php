<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	ob_start();
	$sRootPath = dirname(__FILE__);
	require_once $sRootPath . '/globals.php';
	$sErrorMsg = '';
	$sOpenIDCode = SafeGetInternalArrayParameter($_GET, 'code');
	$sOpenIDState = SafeGetInternalArrayParameter($_GET, 'state');
	$sModelNo = $sOpenIDState;
	$sModelNameInConfig = 'model' . $sModelNo;
	$aSettings = ParseINIFile2Array('includes/webea_config.ini');
	SafeStartSession();
	$_SESSION['model_no']				= $sModelNo;
	$_SESSION['protocol'] 				= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'sscs_protocol', 'http');
	$_SESSION['server'] 				= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'sscs_server', 'localhost');
	$_SESSION['port'] 					= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'sscs_port', '80');
	$_SESSION['db_alias']				= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'sscs_db_alias', 'ea');
	$_SESSION['use_ssl']				= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'sscs_use_ssl', 'false');
	$_SESSION['enforce_certs']	 		= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'sscs_enforce_certs', 'true');
	$_SESSION['model_user']				= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'sscs_model_user', '');
	$_SESSION['model_pwd']				= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'sscs_model_pwd', '');
	$_SESSION['eao_access_code']		= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'sscs_access_code', '');
	$_SESSION['max_communication_time']	= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'sscs_max_communication_time', '30');
	SetUseSSLFlagBasedOnProtocol();
	if (strIsEmpty($sOpenIDCode))
	{
		$gLog->Write2Log('login_sso:  THERE IS A PROBLEM  OpenID Code is empty');
		exit();
	}
	$web_page_parent_sso = true;
	global $g_sOSLCString;
	BuildOSLCConnectionString();
	$sOSLC_URL = $g_sOSLCString . 'login/';
	$sOpenIDRedirectURI = GetOpenIDRedirectURL();
	$sBody =  'sso=OpenID;'
			. 'code='. $sOpenIDCode . ';'
			. 'redirecturi=' . $sOpenIDRedirectURI . ';';
	$sResponse = HTTPPostXMLRaw($sOSLC_URL, $sBody, $httpCode, $sErrorMsg);
	if ( strIsEmpty($sErrorMsg) )
	{
		$sAuthType = 'sso';
		include('./data_api/process_login.php');
	}
	unset($web_page_parent_sso);
	$_SESSION['login_error'] = $sErrorMsg;
	if ( !strIsEmpty($sErrorMsg) )
	{
		$sURLLocation  = 'location: login.php';
		header($sURLLocation);
		exit();
	}
?>