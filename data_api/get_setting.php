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
$sReturn = '';
$sVariableName = isset($_GET['varname']) ? $_GET['varname'] : '';
if ($sVariableName === 'default_diagram') {
	SafeStartSession();
	$sReturn = isset($_SESSION['default_diagram']) ? $_SESSION['default_diagram'] : '';
} elseif ($sVariableName === 'timer_check') {
	$sReturn = 'false&';
	SafeStartSession();
	$sAuth = isset($_SESSION['authorized']) ? $_SESSION['authorized'] : '';
	$sModelNo = isset($_SESSION['model_no']) ? $_SESSION['model_no'] : '';
	$sReturn = $sAuth . '&' . $sModelNo;
}
echo $sReturn;
