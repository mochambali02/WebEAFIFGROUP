<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
header("X-Frame-Options: deny");
header("X-XSS-Protection: 1");
header("X-Content-Type-Options: nosniff");
CheckModules();
function CheckModules()
{
	$aModules = array('core', 'curl', 'date', 'gettext', 'hash', 'json', 'libxml', 'mbstring', 'pcre', 'session', 'standard', 'tokenizer', 'xml');
	$aUnavailable = array();
	foreach ($aModules as $sModule) {
		if (extension_loaded($sModule) !== true) {
			$aUnavailable[] = $sModule;
		}
	}
	if (!empty($aUnavailable)) {
?>

		<head>
			<link type="text/css" rel="stylesheet" href="styles/webea.css" />
		</head>
<?php
		echo '<div id="sign-in">';
		echo '<div class="login-configure-error">';
		echo 'Your web server environment does not have the following modules installed and enabled:<br><br>';
		foreach ($aUnavailable as $sModule) {
			echo '&nbsp;&nbsp;- ' . $sModule . '<br>';
		}
		echo '<br>Contact your web master to correct this.';
		echo '</div>';
		echo '</div>';
		exit();
	}
}
?>