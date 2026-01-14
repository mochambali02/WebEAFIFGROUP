<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
ValidateURL();
header("X-Frame-Options: deny");
header("X-XSS-Protection: 1");
header("X-Content-Type-Options: nosniff");
function ValidateURL()
{
	if (!array_key_exists('PATH_INFO', $_SERVER)) {
		return;
	}
	if (isset($_SERVER['PATH_INFO']) !== true) {
		return;
	}
	if ($_SERVER['PATH_INFO'] === '') {
		return;
	}
	echo "Invalid URL";
	http_response_code(404);
	exit();
}
function AllowedMethods()
{
	$allowed = '';
	for ($i = 0; $i < func_num_args(); $i++) {
		if ($_SERVER['REQUEST_METHOD'] === func_get_arg($i)) {
			return;
		}
	}
	for ($i = 0; $i < func_num_args(); $i++) {
		if (strIsEmpty($allowed)) {
			$allowed = func_get_arg($i);
		} else {
			$allowed .= ', ' . func_get_arg($i);
		}
	}
	header('Allowed: ' . $allowed);
	http_response_code(405);
	exit();
}
function CheckAuthorisation()
{
	if (isset($_SESSION['authorized']) === false) {
		$sErrorMsg = _glt('Your session appears to have timed out');
		setResponseCode(440, $sErrorMsg);
		exit();
	}
}
