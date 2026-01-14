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
$sVariableName = isset($_GET['varname']) ? $_GET['varname'] : '';
$sVariableValue = isset($_GET['varval']) ? $_GET['varval'] : '';
SafeStartSession();
if ($sVariableName === 'review_session') {
	$sReviewSessionName = isset($_GET['varval2']) ? $_GET['varval2'] : '';
	$_SESSION['review_session'] = $sVariableValue;
	$_SESSION['review_session_name'] = $sReviewSessionName;
} else {
	$_SESSION[$sVariableName] = $sVariableValue;
}
if (strIsEmpty($sVariableName)  &&  strIsEmpty($sVariableValue)) {
	echo '';
} else {
	echo $sVariableName . " = " . $sVariableValue;
}
