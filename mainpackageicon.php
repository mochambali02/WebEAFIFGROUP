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
	$sRootPath = dirname(__FILE__);
	require_once $sRootPath . '/globals.php';
	SafeStartSession();
	if (isset($_SESSION['authorized']) === false)
	{
		$sErrorMsg = _glt('Your session appears to have timed out');
		setResponseCode(440, $sErrorMsg);
		exit();
	}
	$g_sViewingMode		= '1';
	$bAddElements	= IsSessionSettingTrue('add_objects') && IsSessionSettingTrue('login_perm_element');
	$bAddPackages	= IsSessionSettingTrue('add_objecttype_package') && IsSessionSettingTrue('login_perm_element');
	$bShowMiniProps = IsSessionSettingTrue('show_miniproperties');
	$aObjs = array();
	include('./data_api/get_objects.php');
	echo '<div id="main-package-icon" class="package-related-bkcolor ' . GetShowBrowserMinPropsStyleClasses() . '">';
	$iCnt = count($aObjs);
	if ( $iCnt > 0 )
	{
		echo '<div class="main-package-inner">';
		echo '<div id="icon-view">';
		for ($i=0; $i<$iCnt; $i++)
		{
			$sName 		= GetPlainDisplayName($aObjs[$i]['text']);
			$sNameEsc	= htmlspecialchars($sName);
			$sGUID		= $aObjs[$i]['guid'] ;
			$sResType	= $aObjs[$i]['restype'];
			$sHasChild	= $aObjs[$i]['haschild'];
			$sLinkType	= $aObjs[$i]['linktype'];
			$sHyper		= $aObjs[$i]['hyper'];
			$sImageURL	= $aObjs[$i]['imageurl'];
			$bObjLocked	= strIsTrue($aObjs[$i]['locked']);
			$sObjLockedType	= $aObjs[$i]['lockedtype'];
				echo '<div class="icon-view-item" onclick="load_object(\'' . $sGUID . '\',\'' . $sHasChild. '\',\'' . $sLinkType . '\',\'' . $sHyper . '\',\'' . ConvertStringToParameter($sName) . '\',\'' . $sImageURL . '\')">';
			echo '<div class="package-icon-img-image">';
			if ($sHasChild === 'true' && $bObjLocked)
			{
				echo '<span class="package-icon-img-span element48-lockedhaschildoverlay" alt="" title="' . _glt('View child objects for locked') . ' ' . $sResType . '"></span>';
			}
			elseif ($sHasChild === 'true' && !$bObjLocked )
			{
				echo '<span class="package-icon-img-span element48-haschildoverlay" alt="" title="' . _glt('View child objects') . '"></span>';
			}
			elseif ($sHasChild === 'false' && $bObjLocked)
			{
				echo '<span class="package-icon-img-span element48-lockedoverlay" alt="" title="' . $sResType . ' ' . _glt('is locked') . '"></span>';
			}
			echo '  <img src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName($sImageURL) . '" alt="" title=""/>';
			echo '</div>';
			echo '<h3><div>' . $sNameEsc . '</div></h3>';
			echo '</div>';
		}
		if ($bAddElements)
		{
			$sAddGUID 			= 'addelement';
			$sAddObjectName 	= _glt('Add new element');
			$sAddObjectImageName = 'element48-add';
			$bCanAdd 			= true;
			if ( strIsEmpty($sObjectGUID) )
			{
				$sAddGUID		= 'addmodelroot';
				$sAddObjectName = _glt('Add Root Node');
				$sAddObjectImageName = 'element48-addmodelroot';
				$bCanAdd 		= $bAddPackages;
			}
			elseif ( substr($sObjectGUID,0,3) === 'mr_' )
			{
				$sAddGUID 		= 'addviewpackage';
				$sAddObjectName = _glt('Add View');
				$sAddObjectImageName = 'element48-addviewpackage';
				$bCanAdd 		= $bAddPackages;
			}
			if ( $bCanAdd )
			{
				echo '<div class="icon-view-item w3-italic" title="' . $sAddObjectName . '" onclick="load_object(\'' . $sAddGUID . '\',\'false\',\'' . $sObjectGUID . '|' . addslashes($sObjectName) . '\',\'\',\'' . ConvertStringToParameter($sObjectName) .'\',\'' . $sObjectImageURL . '\')">';
				echo '<div class="package-icon-img-image">';
				echo '  <img src="images/spriteplaceholder.png" class="' . $sAddObjectImageName . '" alt="" title="' . $sAddObjectName . '"/>';
				echo '</div>';
				echo '<h3><div>&lt;' . _glt('New') . '&gt;</div></h3>';
				echo '</div>';
			}
		}
		echo '</div>' . PHP_EOL;
		echo '</div>' . PHP_EOL;
	}
	else
	{
		$sOSLCErrorMsg = BuildOSLCErrorString();
		if ( strIsEmpty($sOSLCErrorMsg) )
		{
			if ( strIsEmpty($sObjectGUID) )
			{
				echo '<div class="main-package-inner">' . _glt('Problem reading model root') . '</div>';
			}
			else
			{
				if ($bAddElements)
				{
					$sAddGUID 			= 'addelement';
					$sAddObjectName 	= _glt('Add new element');
					$sAddObjectImageName = 'element48-add';
					$bCanAdd 			= true;
					if ( strIsEmpty($sObjectGUID) )
					{
						$sAddGUID = 'addmodelroot';
						$sAddObjectName = _glt('Add Root Node');
						$sAddObjectImageName = 'element48-addmodelroot';
						$bCanAdd 		= $bAddPackages;
					}
					elseif ( substr($sObjectGUID,0,3) === 'mr_' )
					{
						$sAddGUID = 'addviewpackage';
						$sAddObjectName = _glt('Add View');
						$sAddObjectImageName = 'element48-addviewpackage';
						$bCanAdd 		= $bAddPackages;
					}
					if ( $bCanAdd )
					{
						echo '<div class="main-package-inner">';
						echo '<div id="icon-view">';
						echo '<div class="icon-view-item w3-italic" title="' . $sAddObjectName . '" onclick="load_object(\'' . $sAddGUID . '\',\'false\',\'' . $sObjectGUID . '|' . addslashes($sObjectName) . '\',\'\',\'' . ConvertStringToParameter($sObjectName) .'\',\'' . $sObjectImageURL . '\')">';
						echo '<div class="package-icon-img-image">';
						echo '  <img src="images/spriteplaceholder.png" class="' . $sAddObjectImageName . '" alt="" title="' . $sAddObjectName . '"/>';
						echo '</div>';
						echo '<h3><div>&lt;' . _glt('New') . '&gt;</div></h3>';
						echo '</div>';
						echo '</div>';
						echo '</div>';
					}
				}
				else
				{
					echo '<div class="main-package-inner">' . _glt('No child elements') . '</div>';
				}
			}
		}
		else
		{
			echo '<div class="main-package-inner">' . $sOSLCErrorMsg . '</div>';
		}
	}
	echo '</div>' . PHP_EOL;
?>