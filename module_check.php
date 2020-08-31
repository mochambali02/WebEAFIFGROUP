<!DOCTYPE html>
<html>
<head>
	<title>Web EA - Module Check</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link type="text/css" rel="stylesheet" href="styles/webea.css"/>
</head>
<body>
	<!-- Set warning level -->
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
	echo '<header>';
	echo '	<img src="images/logo-full.png" alt="" height="31" width="104"/>';
	echo '</header>';
	echo '<div class="webea-page-contents">';
	echo '<div class="modchk_content">';
	echo '<table id="modchk-table">';
	echo '<tbody>';
	echo '<tr><th>Module</th><th>'.str_repeat('&nbsp;', 7).'Status</th></tr>';
	WriteModuleStatus('core');
	WriteModuleStatus('curl');
	WriteModuleStatus('date');
	WriteModuleStatus('gettext');
	WriteModuleStatus('hash');
	WriteModuleStatus('json');
	WriteModuleStatus('libxml');
	WriteModuleStatus('mbstring');
	WriteModuleStatus('pcre');
	WriteModuleStatus('session');
	WriteModuleStatus('standard');
	WriteModuleStatus('tokenizer');
	echo '<tr class="modchk-mod-line">';
	echo '<td class="modchk-mod-label1">' . 'HTML5' . ' </td>';
	echo '<td id="modchk-mod-html5" class="modchk-mod-value1">';
	echo '<img src="images/spriteplaceholder.png" alt="" align="left"/>&nbsp;&nbsp;&nbsp;(Check requires JavaScript)';
	echo '</td>';
	echo '</tr>';
	echo '</tbody>';
	echo '</table>';
	echo '</div>';
	echo '</div>';
	echo '<footer>';
	echo '<div id="main-footer">';
    echo '<div class="main-footer-copyright"> Copyright &copy; ' . g_csCopyRightYears . ' Sparx Systems Pty Ltd.  All rights reserved.</div> ';
    echo '<div class="main-footer-version"> WebEA v' . g_csWebEAVersion . '</div>';
	echo '</div>';
	echo '</footer>';
	echo '</body>';
	echo '</html>';
	function WriteModuleStatus($sModuleName)
	{
		$bModLoaded = extension_loaded($sModuleName);
		if ($bModLoaded)
		{
			echo '<tr class="modchk-mod-line">';
			echo '<td class="modchk-mod-label1">' . $sModuleName . ' </td>';
			echo '<td class="modchk-mod-value1"><img src="images/spriteplaceholder.png" alt="" class="mainsprite-tick" align="left"/>&nbsp;&nbsp;&nbsp;Available</td>';
			echo '</tr>';
		}
		else
		{
			echo '<tr class="modchk-mod-line">';
			echo '<td class="modchk-mod-label2">' . $sModuleName . ' </td>';
			echo '<td class="modchk-mod-value1"><img src="images/spriteplaceholder.png" alt="" class="mainsprite-warning" align="left" style="margin-left: 0px;"/>&nbsp;&nbsp;&nbsp;NOT Available</td>';
			echo '</tr>';
		}
	}
?>
<style>
.webea-page-contents
{
	height: 100%;
	width: 100%;
}
.webea-config-page-header
{
	font-size: 20px;
	padding-bottom: 10px;
	padding-top: 0px;
	color: #3777bf;
}
#modchk-table
{
	width: 100%;
	overflow: auto;
	border-bottom: 2px solid #dddddd;
	border-spacing: 0;
	max-width: 600px;
}
#modchk-table th
{
    text-align: left;
    border-bottom: 1px solid #cccccc;
    color: #9F9F9F;
    padding: 4px 5px 4px 30px;
    cursor: default;
}
#modchk-header-status
{
	padding-right: 60px;
}
#modchk-table tr
{
    line-height: 1.6em;
}
#modchk-table td
{
    vertical-align: top;
    border-bottom: 1px solid #cccccc;
    color: #333;
	padding: 4px 5px 4px 30px;
}
.modchk_content
{
	top: 34px;
	bottom: 20px;
	right: 4px;
	left: 4px;
	background-color: white;
	border-radius: 5px;
	padding: 10px 10px 10px 10px;
	position: absolute;
	min-height: 200px;
	border-bottom: 2px solid #dddddd;
}
</style>
<script>
	var isHtml5Compatible = document.createElement('canvas').getContext != undefined;
	if (isHtml5Compatible)
	{
		document.getElementById("modchk-mod-html5").innerHTML = '<img src="images/spriteplaceholder.png" alt="" class="mainsprite-tick" align="left"/>&nbsp;&nbsp;&nbsp;Available</td>';
	}
	else
	{
		document.getElementById("modchk-mod-html5").innerHTML = '<td class="modchk-mod-value1"><img src="images/spriteplaceholder.png" alt="" class="mainsprite-warning" align="left" style="margin-left: 0px;"/>&nbsp;&nbsp;&nbsp;NOT Available</td>';
	}
</script>