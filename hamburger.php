<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	if  ( !isset($webea_page_parent_index) )
	{
		exit();
	}
	echo '<div class="header-buttons">';
	echo '  <div class="search-dropdown">';
	echo '	  <div id="search-button" title=""><div><div class="mainsprite-search32white">&#160;</div></div></div>';
	echo '	  <div id="search-menu">';
	echo '	    <div class="hamburger-contextmenu-arrow-bottom"></div><div class="hamburger-contextmenu-arrow-top"></div>';
	echo '	    <div class="contextmenu-content">';
	WriteSearchMenu();
	echo '		</div>';
	echo '	  </div>';
	echo '	</div>';
	$sBrowserStyle = '';
	if (IsSessionSettingTrue('show_browser'))
	{
		$sBrowserStyle = 'class="mainsprite-tick"';
	}
	$sMiniPropsStyle = '';
	if (IsSessionSettingTrue('show_miniproperties'))
	{
		$sMiniPropsStyle = 'class="mainsprite-tick"';
	}
	$sSystemOutputStyle = '';
	if (IsSessionSettingTrue('show_system_output'))
	{
		$sSystemOutputStyle = 'class="mainsprite-tick"';
	}
	$sMainLayoutNo = SafeGetInternalArrayParameter($_SESSION, 'mainlayout', '1');
	$sShowRadioIcons = 'class="hamburger-radio-icon"';
	$sShowRadioList = '';
	$sShowRadioNotes = '';
	if ($sMainLayoutNo === '2')
	{
		$sShowRadioIcons = '';
		$sShowRadioList = 'class="hamburger-radio-icon"';
	}
	elseif ($sMainLayoutNo === '3')
	{
		$sShowRadioIcons = '';
		$sShowRadioNotes = 'class="hamburger-radio-icon"';
	}
	echo '  <div class="hamburger-dropdown">';
	echo '	  <div id="hamburger-button" title=""><div><div class="mainsprite-hamburger32white">&#160;</div></div></div>';
	echo '	  <div id="hamburger-menu">';
	echo '	    <div class="hamburger-contextmenu-arrow-bottom"></div><div class="hamburger-contextmenu-arrow-top"></div>';
	echo '	    <div class="contextmenu-content">';
	WriteHamburger($sBrowserStyle, $sMiniPropsStyle, $sShowRadioIcons, $sShowRadioList, $sShowRadioNotes);
	echo '	    </div>';
	echo '    </div>';
	echo '  </div>';
	echo '</div>';
?>
<script>
	$(document).ready( function ()
	{
		$(".contextmenu-item").click(function()
		{
			$("#search-menu").hide();
			$("#hamburger-menu").hide();
		});
		$('#search-button').click(function()
		{
			$('#search-menu').toggle();
		});
		$('#hamburger-button').click(function()
		{
			$('#hamburger-menu').toggle();
		});
	});
	$(document).mouseup(function (e)
	{
		hide_menu(e,"#hamburger-button","#hamburger-menu");
		hide_menu(e,"#search-button","#search-menu");
		hide_menu(e,"#navbar-hamburger-button","#navbar-hamburger-menu");
		hide_menu(e,"#navbar-search-button","#navbar-search-menu");
		hide_menu(e,"#select-feature-button","#select-feature-menu");
	});
	$(document).bind( "touchend", function(e)
	{
		hide_menu(e,"#hamburger-button","#hamburger-menu");
		hide_menu(e,"#search-button","#search-menu");
		hide_menu(e,"#navbar-hamburger-button","#navbar-hamburger-menu");
		hide_menu(e,"#navbar-search-button","#navbar-search-menu");
		hide_menu(e,"#select-feature-button","#select-feature-menu");
	});
</script>