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
echo '<div id="webea-nojs-popup">';
echo '<div class="webea-nojs-popup-section">';
echo '<div class="webea-nojs-image"><img alt="" src="images/spriteplaceholder.png" class="mainsprite-warning"></div>';
echo '<div class="webea-nojs-line1">WebEA requires Javascript, please enable and refresh.</div>';
echo '</div>';
echo '</div>';
?>
<script>
	document.getElementById("webea-nojs-popup").style.display = 'none';
	$(document).ready(function() {
		$("#webea-main-content").show();
	});
</script>