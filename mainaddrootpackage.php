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
		$sErrorMsg = _glt('Your session appears to have timed out') ;
		setResponseCode(440, $sErrorMsg);
		exit();
	}
	$sParentGUID 	= '';
	$sParentName 	= '';
	if ( !strIsEmpty($sLinkType) )
	{
		$iPos = strpos($sLinkType, '|');
		if ($iPos!==false)
		{
			$sParentGUID = substr($sLinkType, 0, $iPos);
			$sParentName = substr($sLinkType, $iPos+1);
		}
	}
	$sMainClass = '';
	if (IsSessionSettingTrue('show_browser'))
	{
		$sMainClass = ' class="show-browser"';
	}
	echo '<div id="main-add-element" ' . $sMainClass . '>';
	echo '<div id="add-element-layout">';
	if ( $sAddObjectType === 'modelroot' )
	{
		echo '<div class="webea-page-header"><img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName('images/element16/addmodelroot.png') . '"> ' . _glt('Add Root Node') . '</div>';
		echo '<div id="add-element-form">';
		echo '<form class="add-element-form" role="form" onsubmit="OnFormRunAddRootPackage(event)">';
		echo '<table id="add-element-table" style="width: 100%;">';
		echo '<tbody>';
		echo '<tr class="add-element-line" style="height: 60px;">';
		echo '<td class="add-element-label field-label-vert-align-top">' . _glt('Name') . '<span class="field-label-required">&nbsp;*&nbsp;</span></td>';
		echo '<td class="add-element-field" style="width: 80%;"><textarea id="add-element-name-field" class="webea-main-styled-textbox" name="name" maxlength="255" onfocus="EnsureInputFieldVisible(this)"></textarea></td>';
		echo '</tr>';
		echo '<tr class="add-element-line" style="height: 54px;">';
		echo '<td>&nbsp;</td>';
		echo '<td class="add-element-field"><input class="webea-main-styled-button add-element-submit" type="submit" value="' . _glt('Add') . '"></td>';
		echo '</tr>';
		echo '</tbody>';
		echo '</table>';
		echo '<input type="hidden" name="objecttype" value="' . $sAddObjectType . '">';
		echo '<input type="hidden" name="parentguid" value="' . $sParentGUID . '">';
		echo '<input type="hidden" name="parentname" value="' . htmlspecialchars($sParentName, ENT_COMPAT) . '">';
		$sLoginGUID 	= SafeGetInternalArrayParameter($_SESSION, 'login_guid', '');
		$sLoginFullName = SafeGetInternalArrayParameter($_SESSION, 'login_fullname', '');
		if ( !strIsEmpty( $sLoginGUID ) )
		{
			echo '<input type="hidden" name="loginguid" value="' . $sLoginGUID . '">';
		}
		if ( !strIsEmpty( $sLoginFullName ) )
		{
			echo '<input type="hidden" name="author" value="' . $sLoginFullName . '">';
		}
		echo '</form>';
		echo '</div>';
	}
	else
	{
		echo '<div class="webea-page-header"><img alt="" src="images/spriteplaceholder.png" class="' . GetObjectImageSpriteName('images/element16/addviewpackage.png') . '" onload="OnLoad_SetupSpecialCtrls(\'add-element-notes-field\',\'\')"> ' . _glt('Add View') . '</div>';
		echo '<div id="add-element-form">';
		echo '<form class="add-element-form" role="form" onsubmit="OnFormRunAddRootPackage(event)">';
		echo '<table id="add-element-table" style="width: 100%;">';
		echo '<tbody>';
		echo '<tr class="add-element-line" style="height: 136px;">';
		echo '<td class="add-element-label field-label-vert-align-top">' . _glt('Icon Style') . '<span class="field-label-required">&nbsp;*&nbsp;</span></td>';
		echo '<td class="add-element-field" style="width: 80%;">';
		echo '<div id="add-modelroot-icon-style-div">';
		echo '<label class="add-model-root-icon-style-radio-label"><input class="add-model-root-icon-style-radio" type="radio" name="iconstyle" value="6" checked="true"/><img alt="" src="images/spriteplaceholder.png" class="element16-viewsimple">&nbsp;' . _glt('Simple') . '</label>' . g_csHTTPNewLine;
		echo '<label class="add-model-root-icon-style-radio-label"><input class="add-model-root-icon-style-radio" type="radio" name="iconstyle" value="1"/><img alt="" src="images/spriteplaceholder.png" class="element16-viewusecase">&nbsp;' . _glt('Use Case') . '</label>' . g_csHTTPNewLine;
		echo '<label class="add-model-root-icon-style-radio-label"><input class="add-model-root-icon-style-radio" type="radio" name="iconstyle" value="2"/><img alt="" src="images/spriteplaceholder.png" class="element16-viewdynamic">&nbsp;' . _glt('Dynamic') . '</label>' . g_csHTTPNewLine;
		echo '<label class="add-model-root-icon-style-radio-label"><input class="add-model-root-icon-style-radio" type="radio" name="iconstyle" value="3"/><img alt="" src="images/spriteplaceholder.png" class="element16-viewclass">&nbsp;' . _glt('Class View') . '</label>' . g_csHTTPNewLine;
		echo '<label class="add-model-root-icon-style-radio-label"><input class="add-model-root-icon-style-radio" type="radio" name="iconstyle" value="4"/><img alt="" src="images/spriteplaceholder.png" class="element16-viewcomponent">&nbsp;' . _glt('Component') . '</label>' . g_csHTTPNewLine;
		echo '<label class="add-model-root-icon-style-radio-label"><input class="add-model-root-icon-style-radio" type="radio" name="iconstyle" value="5"/><img alt="" src="images/spriteplaceholder.png" class="element16-viewdeployment">&nbsp;' . _glt('Deployment') . '</label>' ;
		echo '</div>';
		echo '</td>';
		echo '</tr>';
		echo '<tr class="add-element-line" style="height: 60px;">';
		echo '<td class="add-element-label field-label-vert-align-top">' . _glt('Name') . '<span class="field-label-required">&nbsp;*&nbsp;</span></td>';
		echo '<td class="add-element-field" style="width: 80%;"><textarea id="add-element-name-field" class="webea-main-styled-textbox" name="name" maxlength="255" onfocus="EnsureInputFieldVisible(this)"></textarea></td>';
		echo '</tr>';
		echo '<tr class="add-element-line" style="height: 160px;">';
		echo '<td class="add-element-label field-label-vert-align-top">' . _glt('Notes') . '</td>';
		echo '<td class="add-element-field last-field" style="width: 80%;"><div id="add-element-notes-div"><textarea id="add-element-notes-field" name="notes" onfocus="EnsureInputFieldVisible(this)"></textarea></div></td>';
		echo '</tr>';
		echo '<tr class="add-element-line" style="height: 54px;">';
		echo '<td>&nbsp;</td>';
		echo '<td class="add-element-field"><input class="webea-main-styled-button add-element-submit" type="submit" value="' . _glt('Add') . '"></td>';
		echo '</tr>';
		echo '</tbody>';
		echo '</table>';
		echo '<input type="hidden" name="objecttype" value="' . $sAddObjectType . '">';
		echo '<input type="hidden" name="parentguid" value="' . $sParentGUID . '">';
		echo '<input type="hidden" name="parentname" value="' . htmlspecialchars($sParentName, ENT_COMPAT) . '">';
		$sLoginGUID 	= SafeGetInternalArrayParameter($_SESSION, 'login_guid', '');
		$sLoginFullName = SafeGetInternalArrayParameter($_SESSION, 'login_fullname', '');
		if ( !strIsEmpty( $sLoginGUID ) )
		{
			echo '<input type="hidden" name="loginguid" value="' . $sLoginGUID . '">';
		}
		if ( !strIsEmpty( $sLoginFullName ) )
		{
			echo '<input type="hidden" name="author" value="' . $sLoginFullName . '">';
		}
		echo '</form>';
		echo '</div>';
	}
	echo '</div>';
	echo '</div>';
?>
<script>
function onClickAddElementType(sObjectType)
{
	var ele1 = document.getElementById('add-element-diagram-tdtech1');
	var ele2 = document.getElementById('add-element-diagram-tdtech2');
	var ele3 = document.getElementById('add-element-diagram-tdtype1');
	var ele4 = document.getElementById('add-element-diagram-tdtype2');
	if (ele1)
	{
		if ( sObjectType == 'diagram' )
		{
			ele1.style.display = 'table-cell';
			ele2.style.display = 'table-cell';
			ele3.style.display = 'table-cell';
			ele4.style.display = 'table-cell';
		}
		else
		{
			ele1.style.display = 'none';
			ele2.style.display = 'none';
			ele3.style.display = 'none';
			ele4.style.display = 'none';
		}
	}
}
function onClickAddElementDiaTech(sObjectType, sDiagramTypes)
{
	var eleTech = document.getElementById('add-element-diagramtech-field');
	var eleTypes = document.getElementById('add-element-diagramtype-field');
	if (eleTech && eleTypes)
	{
		try
		{
			var sTech = eleTech.value;
			var aDTs = JSON.parse(sDiagramTypes);
			for (var i = eleTypes.options.length-1 ; i>=0 ; i--)
			{
				eleTypes.remove(i);
			}
			for (i = 0; i<aDTs.length; i++)
			{
				if (aDTs[i]['tech'] === sTech)
				{
					var sLabel	= aDTs[i]['alias'];
					if ( sLabel === ''  )
						sLabel 	= aDTs[i]['name'];
					var sValue	= aDTs[i]['fqname'];
					if ( sValue === ''  )
						sValue 	= aDTs[i]['name'];
					var el = document.createElement("option");
					el.textContent = sLabel;
					el.value = sValue;
					eleTypes.appendChild(el);
				}
			}
		}
		catch(e)
		{
		}
	}
}
</script>