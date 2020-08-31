<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	session_start();
	$sErrorMsg = '';
	include('./data_api/send_logout.php');
	$sCurrModelNo = '';
	if (array_key_exists('model_no', $_SESSION))
	{
		if (isset($_SESSION['model_no']))
			$sCurrModelNo = $_SESSION['model_no'];
	}
	setcookie('webea_lastpage_' . $sCurrModelNo, '', time()-60, '/' );
	setcookie('webea_session', '', time()-60, '/' );
	if (session_destroy())
	{
		header("Location: login.php");
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta content="text/html;charset=utf-8" http-equiv="Content-Type">
</head>
<body>
</body>
</html>