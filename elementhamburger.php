<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	if  ( !isset($webea_page_parent_mainview) )
	{
		exit();
	}
	$sContextMenu = '';
	$sCMPart1 = '';
	$sCMPart2 = '';
	if (IsSessionSettingTrue('add_objects') && IsSessionSettingTrue('login_perm_element'))
	{
		$sCMPart1 .= '<div class="contextmenu-item" onclick="load_object(\'addelement\',\'false\',\'' . $sObjectGUID . '|' . addslashes($sObjectName) . '\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\',\'' . $sObjImageURL . '\')">';
		$sCMPart1 .= '<img alt="" src="images/spriteplaceholder.png" class="element16-add">' . _glt('Add Object') . '</div>';
	}
	if (IsSessionSettingTrue('add_object_features'))
	{
		if ( IsSessionSettingTrue('login_perm_test') )
		{
			if (IsSessionSettingTrue('add_objectfeature_tests'))
			{
				$sCMPart1 .= '<div class="contextmenu-item" onclick="load_object(\'addelementtest\',\'false\',\'' . $sObjectGUID . '\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\',\'' . $sObjImageURL . '\')">';
				$sCMPart1 .= '<img alt="" src="images/spriteplaceholder.png" class="propsprite-testadd">' . _glt('Add test') . '</div>';
			}
		}
		if ( IsSessionSettingTrue('login_perm_resalloc') )
		{
			if (IsSessionSettingTrue('add_objectfeature_resources'))
			{
				$sCMPart1 .= '<div class="contextmenu-item" onclick="load_object(\'addelementresalloc\',\'false\',\'' . $sObjectGUID . '\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\',\'' . $sObjImageURL . '\')">';
				$sCMPart1 .= '<img alt="" src="images/spriteplaceholder.png" class="propsprite-resallocadd">' . _glt('Add resource') . '</div>';
			}
		}
		if ( IsSessionSettingTrue('login_perm_maintenance') )
		{
			if (IsSessionSettingTrue('add_objectfeature_changes'))
			{
				$sCMPart2 .= '<div class="contextmenu-item" onclick="load_object(\'addelementchgmgmt\',\'false\',\'' . $sObjectGUID . '|change' . '\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\',\'' . $sObjImageURL . '\')">';
				$sCMPart2 .= '<img alt="" src="images/spriteplaceholder.png" class="propsprite-changeadd">' . _glt('Add change') . '</div>';
			}
			if (IsSessionSettingTrue('add_objectfeature_defects'))
			{
				$sCMPart2 .= '<div class="contextmenu-item" onclick="load_object(\'addelementchgmgmt\',\'false\',\'' . $sObjectGUID . '|defect' . '\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\',\'' . $sObjImageURL . '\')">';
				$sCMPart2 .= '<img alt="" src="images/spriteplaceholder.png" class="propsprite-defectadd">' . _glt('Add defect') . '</div>';
			}
			if (IsSessionSettingTrue('add_objectfeature_issues'))
			{
				$sCMPart2 .= '<div class="contextmenu-item" onclick="load_object(\'addelementchgmgmt\',\'false\',\'' . $sObjectGUID . '|issue' . '\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\',\'' . $sObjImageURL . '\')">';
				$sCMPart2 .= '<img alt="" src="images/spriteplaceholder.png" class="propsprite-issueadd">' . _glt('Add issue') . '</div>';
			}
			if (IsSessionSettingTrue('add_objectfeature_tasks'))
			{
				$sCMPart2 .= '<div class="contextmenu-item" onclick="load_object(\'addelementchgmgmt\',\'false\',\'' . $sObjectGUID . '|task' . '\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\',\'' . $sObjImageURL . '\')">';
				$sCMPart2 .= '<img alt="" src="images/spriteplaceholder.png" class="propsprite-taskadd">' . _glt('Add task') . '</div>';
			}
		}
		if ( IsSessionSettingTrue('login_perm_projman') )
		{
			if (IsSessionSettingTrue('add_objectfeature_risks'))
			{
				$sCMPart2 .= '<div class="contextmenu-item" onclick="load_object(\'addelementchgmgmt\',\'false\',\'' . $sObjectGUID . '|risk' . '\',\'\',\'' . ConvertStringToParameter($sObjectName) . '\',\'' . $sObjImageURL . '\')">';
				$sCMPart2 .= '<img alt="" src="images/spriteplaceholder.png" class="propsprite-riskadd">' . _glt('Add risk') . '</div>';
			}
		}
		if ( !strIsEmpty($sCMPart1) || !strIsEmpty($sCMPart2) )
		{
			$sContextMenu .= '<div class="element-hamburger-dropdown">';
			$sContextMenu .= '<div id="element-hamburger-button" title="' . _glt('Add feature to element') . '"><div><div class="mainsprite-hamburger24blue">&#160</div></div></div>';
			$sContextMenu .= '<div id="element-hamburger-menu">';
			$sContextMenu .= '<div class="contextmenu-arrow-bottom"></div><div class="contextmenu-arrow-top"></div>';
			$sContextMenu .= '<div class="contextmenu-content">';
			$sContextMenu .= '<div class="contextmenu-header">' . _glt('Add Item') . '</div>';
			$sContextMenu .= '<div class="contextmenu-items">';
			$sContextMenu .= $sCMPart1;
			if ( !strIsEmpty($sCMPart1) && !strIsEmpty($sCMPart2) )
				$sContextMenu .= '<hr>';
			$sContextMenu .= $sCMPart2;
			$sContextMenu .= '</div>';
			$sContextMenu .= '</div>';
			$sContextMenu .= '</div>';
			$sContextMenu .= '</div>';
		}
		echo $sContextMenu;
	}
?>
<script>
$(document).ready( function ()
{
	$(".contextmenu-item").click(function()
	{
		$("#hamburger-menu").hide();
	});
	$('#element-hamburger-button').click(function()
	{
		$('#element-hamburger-menu').toggle();
	});
});
$(document).mouseup(function (e)
{
	hide_menu(e,"#element-hamburger-button","#element-hamburger-menu");
});
</script>