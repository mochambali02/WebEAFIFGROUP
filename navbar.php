<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	if  ( !isset($webea_page_parent_index) && $_SERVER["REQUEST_METHOD"] !== "POST" )
	{
		exit();
	}
	$sRootPath = dirname(__FILE__);
	require_once $sRootPath . '/globals.php';
	SafeStartSession();
	if (isset($_SESSION['authorized']) === false)
	{
		$sErrorMsg = _glt('Your session appears to have timed out');
		return $sErrorMsg;
	}
	$sCurrGUID 		= isset($_POST['guid']) ? $_POST['guid'] : '';
	$sCurrHasChild 	= isset($_POST['haschild']) ? $_POST['haschild'] : '';
	$sCurrLinkType 	= isset($_POST['linktype']) ? $_POST['linktype'] : '';
	$sCurrHyper 	= isset($_POST['hyper']) ? $_POST['hyper'] : '';
	$sCurrName	 	= isset($_POST['name']) ? $_POST['name'] : '';
	$sCurrImageURL 	= isset($_POST['imageurl']) ? $_POST['imageurl'] : '';
	$sCurrResType	= GetResTypeFromGUID($sCurrGUID);
	$sCurrName 		= GetPlainDisplayName($sCurrName);
	$bShowHomeWithoutLink = true;
	$webea_page_parent_navbar = true;
	$sHome = isset($_SESSION['model_name']) ? htmlspecialchars($_SESSION['model_name']) : 'Home';
	echo '<div class="navbar">';
	echo '<div id="navbar-busy-loader">';
	echo '<img src="images/navbarwait.gif" alt="" class="navbar-spinner" height="26" width="26">';
	echo '</div>';
	echo '<div class="navbar-panel-left" id="navbar-panel-left">';
	if (IsSessionSettingTrue('show_browser'))
	{
		echo '<div id="navbar-browser-button" title="' . _glt('Hide Browser') . '" onclick="show_browser()"><div><div id="mainsprite-navbarbrowsericon" class="mainsprite-navbarbrowsercollapse">&#160;</div></div></div>';
	}
	else
	{
		echo '<div id="navbar-browser-button" title="' . _glt('Show Browser') . '" onclick="show_browser()"><div><div id="mainsprite-navbarbrowsericon" class="mainsprite-navbarbrowserexpand">&#160;</div></div></div>';
	}
	echo '<div id="navbar-home-button" title="' . _glt('Navigate to initial page') . '" onclick="load_home(false)"><div><div class="mainsprite-home24">&#160;</div></div></div>';
	echo '<div class="navbar-path-dropdown">';
	echo '<div id="navbar-path-button" title="' . _glt('Complete path to root node') . '" onclick="show_path()"><div><div class="mainsprite-path">&#160;</div></div></div>';
	echo '<div id="path-menu">';
	echo '<div class="contextmenu-arrow-bottom"></div><div class="contextmenu-arrow-top"></div>';
	echo '<div class="contextmenu-content">';
	echo '<div class="contextmenu-header">Path</div>';
	include('objectpath.php');
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '<div class="navbar-spacer"><img alt="|" src="images/spriteplaceholder.png" class="mainsprite-bar36"></div>';
	echo '<div class="navbar-spacer-2"><img alt="|" src="images/spriteplaceholder.png" class="mainsprite-bar36"></div>';
	$bCurrentShown = false;
	if ( IsGUIDAddEditAction($sCurrGUID)  )
	{
		$sImageClass = '';
		$sText = '';
		$bHasParentAndChild = true;
		echo '<div id="navbar-current-name"><div id="navbar-current-name-text">';
		if ( $sCurrGUID==='addmodelroot' )
		{
			$sImageClass = 'element16-addmodelroot';
			$sText = _glt('Add Root Node');
			$bHasParentAndChild = false;
		}
		elseif ( $sCurrGUID==='addviewpackage' )
		{
			$sImageClass = 'element16-addviewpackage';
			$sText = _glt('Add view to');
		}
		elseif ( $sCurrGUID==='addelement' )
		{
			$sImageClass = 'element16-add';
			$sText = _glt('Add element to');
		}
		elseif ( $sCurrGUID==='addelementtest' )
		{
			$sImageClass = 'propsprite-testadd';
			$sText = _glt('Add Test to');
		}
		elseif ( $sCurrGUID==='addelementresalloc' )
		{
			$sImageClass = 'propsprite-resallocadd';
			$sText = _glt('Add resource allocation to');
		}
		elseif ( $sCurrGUID==='addelementchgmgmt' )
		{
			$sObjectGUID = '';
			$sChangeMgtType = '';
			list($sObjectGUID, $sChangeMgtType) = explode('|', $sCurrLinkType);
			$sObjectGUID = trim($sObjectGUID);
			$sChangeMgtType = trim($sChangeMgtType);
			GetChgMgtAddText($sChangeMgtType, $sText, $sImageClass);
		}
		elseif ( $sCurrGUID==='editelementnote' )
		{
			$sImageClass = 'propsprite-noteedit';
			$sText = _glt('Edit note for');
		}
		elseif ( $sCurrGUID==='editelementtest' )
		{
			$sImageClass = 'propsprite-testedit';
			$sText = _glt('Edit Test for');
		}
		elseif ( $sCurrGUID==='editelementresalloc' )
		{
			$sImageClass = 'propsprite-resallocedit';
			$sText = _glt('Edit resource allocation for');
		}
		$sName = htmlspecialchars($sCurrName);
		echo '<img alt="" src="images/spriteplaceholder.png" class="' . $sImageClass . '">&nbsp;' . $sText . '&nbsp;';
		if ( $bHasParentAndChild )
		{
			echo '<img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName(AdjustImagePath($sCurrImageURL, '16')) . '">&nbsp;' . $sName . '&nbsp;';
		}
		echo '</div></div>';
		$bCurrentShown = true;
	}
	else if ( !strIsEmpty($sCurrResType) )
	{
		$sName = htmlspecialchars($sCurrName);
		echo '<div id="navbar-current-name"><div id="navbar-current-name-text">';
		$sImagePath = AdjustImagePath($sCurrImageURL, '16');
		if ( $sCurrLinkType === 'document' )
		{
			echo '<img alt="" src="images/spriteplaceholder.png" class="element16-document">&nbsp;' . _glt('Linked Document for') . '&nbsp;';
		}
		elseif ( $sCurrLinkType === 'encryptdoc' )
		{
			echo _glt('Encrypted Document for') . '&nbsp;';
		}
		$sPrefix = substr($sCurrGUID,0,4);
		if ($sPrefix === 'lt_{')
		{
			$s = trim($sName);
			$a = explode(' ', $s);
			if (count($a)>1)
			{
				$a[0] = _glt($a[0]);
				$a[1] = _glt($a[1]);
			}
			$sName = implode(' ', $a);
			$sName = htmlspecialchars($sCurrName);
		}
		elseif ($sPrefix !== 'mr_{' && $sPrefix !== 'pk_{' && $sPrefix !== 'dg_{' && $sPrefix !== 'di_{' && $sPrefix !== 'el_{' && $sCurrGUID !== 'matrix')
		{
			$sName = _glt($sName);
			$sName = htmlspecialchars($sCurrName);
		}
		elseif ( $sCurrGUID === 'matrix' )
		{
			$sName = htmlspecialchars(_glt('Matrix') .' - '. $sCurrHyper);
			$sImagePath = $sImageURL;
		}
		echo '<img alt="" title="' . $sCurrResType . '" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sImagePath) . '">&nbsp;' . $sName . '&nbsp;';
		echo '</div></div>';
		$bCurrentShown = true;
	}
	if ($bCurrentShown === false)
	{
		echo '<div id="navbar-current-name"><div id="navbar-current-name-text"><img alt="" src="images/spriteplaceholder.png" class="mainsprite-root">&nbsp;' . $sHome . '&nbsp;</div></div>';
	}
	echo '</div>';
	echo '<div class="navbar-panel-right" id="navbar-panel-right">';
	echo '<div id="navbar-expand-button" title="' . _glt('Expand the Navigation Bar') . '" onclick="expand_navbar()"><div><div class="mainsprite-navbarexpand">&#160;</div></div></div>';
	echo '<div id="navbar-collapse-button" title="' . _glt('Collapse the Navigation Bar') . '" onclick="collapse_navbar()"><div><div class="mainsprite-navbarcollapse">&#160;</div></div></div>';
	echo '<div class="navbar-spacer-2"><img alt="|" src="images/spriteplaceholder.png" class="mainsprite-bar36"></div>';
	$sRefreshLink = 'refresh_current()';
	echo '<div id="navbar-refresh-button" title="' . _glt('Refresh the current view') . '" onclick="' . $sRefreshLink. '"><div><div class="mainsprite-refresh24grey">&#160;</div></div></div>';
	echo '<div class="navbar-spacer"><img alt="|" src="images/spriteplaceholder.png" class="mainsprite-bar36"></div>';
	$sFirst3Chars 	= substr($sCurrGUID,0 ,3);
	if ( $sFirst3Chars === 'mr_' ||
		 $sFirst3Chars === 'pk_' ||
		 $sFirst3Chars === 'dg_' ||
		 $sFirst3Chars === 'el_' ||
		 $sCurrGUID    === 'matrix' )
	{
		if ( $sCurrGUID === 'matrix' )
		{
			$sPlainGUID = 'matrix_' . $sCurrHyper;
		}
		else
		{
			$sPlainGUID = substr($sCurrGUID, 4);
			$sPlainGUID = trim($sPlainGUID, '{}');
		}
		$sFullURL  = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
		$sFullURL .= '://' . $_SERVER['HTTP_HOST'];
		$sWebsiteRoot = '';
		$sDocPath = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		$iLastPos = strripos($sDocPath, '/');
		if ( $iLastPos !== FALSE )
		{
			if ( $iLastPos > 0 )
			{
				$sWebsiteRoot = substr($sDocPath, 0, $iLastPos);
			}
		}
		$sFullURL .= $sWebsiteRoot;
		$sFullURL .= '?m=' . SafeGetInternalArrayParameter($_SESSION, 'model_no', '');
		$sFullURL .= '&o=' . $sPlainGUID;
		echo '<div id="navbar-share-button" title="' . _glt('Obtain direct link to the current page') . '" onclick="OnShowCurrentLink(\'' . $sPlainGUID . '\',\'' . $sFullURL . '\')"><div><div class="mainsprite-showlink">&#160;</div></div></div>';
	}
	else
	{
		echo '<div id="navbar-share-button" disabled="true" title="' . _glt('Obtain direct link to the current page') . '"><div><div class="mainsprite-showlink">&#160;</div></div></div>';
	}
	if (isset($sResType))
	{
		if ($sResType === 'Diagram')
		{
			if ($sCurrLinkType === 'edit')
			{
			}
			else
			{
			}
		}
	}
	if (isset($sResType))
	{
		if ($sResType === 'Diagram')
		{
			if ($sCurrLinkType === 'edit')
			{
			}
		}
	}
	if (( ( $sCurrResType === "Diagram" || $sCurrResType === "Package") && $sCurrLinkType !== "props") ||
		( (	$sCurrResType === "Element" && ($sCurrHasChild === "true" || $sCurrLinkType === "child") ) ) )
	{
		$sLoadInfo = 'load_object(\'' . $sCurrGUID . '\', \'' . $sCurrHasChild . '\', \'props\', \'' . $sCurrHyper . '\', \'' . ConvertStringToParameter($sCurrName) . '\', \'' . $sCurrImageURL . '\')';
		echo '<div id="navbar-info-button" title="' . _glt('View the object properties') . '" onclick="' . $sLoadInfo . '"><div><div class="mainsprite-info">&#160;</div></div></div>';
	}
	else
	{
		echo '<div id="navbar-info-button" disabled="true"><div class="mainsprite-info">&#160;</div></div>';
	}
	if (IsSessionSettingTrue('show_miniproperties'))
	{
		echo '<div id="navbar-properties-button" title="'._glt('Show Properties View').'" onclick="show_mini_properties()"><div id="mainsprite-navbarpropsicon" class="mainsprite-navbarpropscollapse">&#160;</div></div>';
	}
	else
	{
		echo '<div id="navbar-properties-button" title="'._glt('Hide Properties View').'" onclick="show_mini_properties()"><div id="mainsprite-navbarpropsicon" class="mainsprite-navbarpropsexpand">&#160;</div></div>';
	}
	echo '<div class="navbar-spacer-2"><img alt="|" src="images/spriteplaceholder.png" class="mainsprite-bar36"></div>';
	echo '<div class="navbar-search-dropdown">';
	echo '  <input id="navbar-search-button" value="&nbsp;" type="button" title="Search" onclick="show_navbar_search()">';
	echo '	<div id="navbar-search-menu">';
	echo '	  <div class="contextmenu-arrow-bottom"></div><div class="contextmenu-arrow-top"></div>';
	echo '	  <div class="contextmenu-content">';
	WriteSearchMenu();
	echo '	  </div>';
	echo '	</div>';
	echo '</div>';
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
	echo '<div class="navbar-hamburger-dropdown">';
	echo '  <input id="navbar-hamburger-button" value="&nbsp;" type="button" title="Complete path back to root node" onclick="show_navbar_hamburger()">';
	echo '  <div id="navbar-hamburger-menu">';
	echo '	  <div class = "contextmenu-arrow-bottom"></div><div class = "contextmenu-arrow-top"></div>';
	echo '	  <div class="contextmenu-content">';
	WriteHamburger($sBrowserStyle, $sMiniPropsStyle, $sShowRadioIcons, $sShowRadioList, $sShowRadioNotes);
	echo '    </div>';
	echo '  </div>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo PHP_EOL;
	echo BuildSystemOutputDataDIV();
	unset($webea_page_parent_navbar);
?>
<script>
function show_path()
{
	$('#path-menu').toggle();
}
function show_navbar_search()
{
	$('#navbar-search-menu').toggle();
}
function show_navbar_hamburger()
{
	$('#navbar-hamburger-menu').toggle();
}
$(document).mouseup(function (e)
{
	hide_menu(e, "#navbar-path-button","#path-menu");
});
function expand_navbar()
{
	var pr = document.getElementById("navbar-panel-right");
	pr.className += " navbar-panel-right-expanded";
	var pl = document.getElementById("navbar-panel-left");
	pl.className += " navbar-panel-left-collapsed";
}
function collapse_navbar()
{
	var pr = document.getElementById("navbar-panel-right");
	pr.className = "navbar-panel-right";
	var pl = document.getElementById("navbar-panel-left");
	pl.className = "navbar-panel-left";
}
</script>