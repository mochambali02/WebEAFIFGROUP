<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if (!isset($webea_page_parent_mainview)) {
	exit();
}
include('./data_api/get_matrixprofilenames.php');
echo 	'<div id="main-matrix-profiles">';
echo		'<div class="webea-page-header">' . _glt('Matrix Profiles') . '</div>';
if (empty($aMatrixProfiles)) {
	echo '<div>' . _glt('No Matrix Profiles found') . '</div>';
} else {
	$aMatrixProfiles = array_merge(array(_glt('Select Profile')), $aMatrixProfiles);
	echo '<form class="matrix-profile-form" role="form" onsubmit="OnFormViewMatrix(event)">';
	echo '	<div class="field-group">';
	echo '		<div class="matrix-profile-field-label"><label>' . _glt('Select Profile') . '</label></div>';
	$aAttribs['id']			= 'matrix-profile';
	$aAttribs['name']		= 'matrix-profile';
	$aAttribs['onchange']	= 'OnSelectMatrixProfile(this)';
	$aAttribs['maxlength']	= '255';
	$aAttribs['class']		= 'webea-main-styled-combo';
	$aAttribs['style']		= 'width: 80%; max-width: 500px; min-width: 240px;';
	echo BuildHTMLComboFromArray($aMatrixProfiles, $aAttribs, false, _glt('Select Profile'));
	echo '	</div>';
	echo '<div class="field-group">';
	echo '<div class="matrix-profile-group-header">' . _glt('Source') . '</div>';
	echo '<div class="inline-group">';
	echo '<div class="matrix-profile-label">' . _glt('Package') . ':</div>';
	echo '<div id="matrix-source-package" class="matrix-profile-value"></div>';
	echo '</div>';
	echo '<div class="inline-group">';
	echo '<div class="matrix-profile-label">' . _glt('Type') . ':</div>';
	echo '<div id="matrix-source-type" class="matrix-profile-value"></div>';
	echo '</div>';
	echo '<br>';
	echo '<div class="matrix-profile-group-header">' . _glt('Target') . '</div>';
	echo '<div class="inline-group">';
	echo '<div class="matrix-profile-label">' . _glt('Package') . ':</div>';
	echo '<div id="matrix-target-package" class="matrix-profile-value"></div>';
	echo '</div>';
	echo '<div class="inline-group">';
	echo '<div class="matrix-profile-label">' . _glt('Type') . ':</div>';
	echo '<div id="matrix-target-type" class="matrix-profile-value"></div>';
	echo '</div>';
	echo '<br>';
	echo '<div class="matrix-profile-group-header">' . _glt('Link') . '</div>';
	echo '<div class="inline-group">';
	echo '<div class="matrix-profile-label">' . _glt('Type') . ':</div>';
	echo '<div id="matrix-link-type" class="matrix-profile-value"></div>';
	echo '</div>';
	echo '<div class="inline-group">';
	echo '<div class="matrix-profile-label">' . _glt('Direction') . ':</div>';
	echo '<div id="matrix-link-direction" class="matrix-profile-value"></div>';
	echo '</div>';
	echo '<br>';
	echo '</div>';
	echo '<input id="matrix-profile-view-matrix" class="webea-main-styled-button mainsprite-matrixwhite" disabled="true" value="' . _glt('View Matrix') . '" type="submit">';
	echo '</form>';
}
echo	'</div>';
