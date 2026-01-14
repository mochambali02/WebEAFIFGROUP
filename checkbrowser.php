<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
require_once __DIR__ . '/security.php';
?>
<div id="webea-nojs-popup">
	<div class="webea-nojs-popup-section">
		<div class="webea-nojs-image">
			<img alt="" src="images/spriteplaceholder.png" class="mainsprite-warning">
		</div>
		<div class="webea-nojs-line1">WebEA requires Javascript, please enable and refresh.</div>
	</div>
</div>
<div id="webea-nocookies-popup">
	<div class="webea-nocookies-popup-section">
		<div class="webea-nocookies-image">
			<img alt="" src="images/spriteplaceholder.png" class="mainsprite-warning">
		</div>
		<div class="webea-nocookies-line1">WebEA requires cookies, please enable and refresh.</div>
	</div>
</div>
<script>
	document.getElementById("webea-nojs-popup").style.display = 'none';
	$(document).ready(function() {
		$("#webea-main-content").show();
		CheckCookiesEnabled();
	});
</script>