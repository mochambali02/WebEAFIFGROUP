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
	echo '<div id="jsmess_modelroot">' . htmlspecialchars(_glt('ModelRoot')) . '</div>';
	echo '<div id="error_navigating_to_home">' . htmlspecialchars(_glt('Error navigating to home')) . '</div>';
	echo '<div id="unable_to_load_objects_for_other_models">' . htmlspecialchars(_glt('Unable to load objects for other models')) . '</div>';
	echo '<div id="invalid_full_webea_url">' . htmlspecialchars(_glt('Invalid full WebEA URL')) . '</div>';
	echo '<div id="error_while_searching">' . htmlspecialchars(_glt('Error while searching')) . '</div>';
	echo '<div id="unable_to_retrieve_object_details">' . htmlspecialchars(_glt('Unable to retrieve object details')) . '</div>';
	echo '<div id="error_selecting_diagram_object">' . htmlspecialchars(_glt('Error selecting diagram object')) . '</div>';
	echo '<div id="unable_to_retrieve_server_details">' . htmlspecialchars(_glt('Unable to retrieve server details')) . '</div>';
	echo '<div id="error_retrieving_server_details">' . htmlspecialchars(_glt('Error retrieving server details')) . '</div>';
	echo '<div id="guid_not_found_in_model">' . htmlspecialchars(_glt('GUID not found in model')) . '</div>';
	echo '<div id="error_occurred_while_locating_guid">' . htmlspecialchars(_glt('Error occurred while locating GUID')) . '</div>';
	echo '<div id="invalid_guid_format">' . htmlspecialchars(_glt('Invalid GUID format')) . '</div>';
	echo '<div id="blank_guid_supplied">' . htmlspecialchars(_glt('Blank GUID supplied')) . '</div>';
	echo '<div id="invalid_webea_guid">' . htmlspecialchars(_glt('Invalid WebEA GUID')) . '</div>';
	echo '<div id="error_setting_discussion_state">' . htmlspecialchars(_glt('Error setting discussion state')) . '</div>';
	echo '<div id="browser_is_disabled">' . htmlspecialchars(_glt('Browser is disabled')) . '</div>';
	echo '<div id="miniprops_is_disabled">' . htmlspecialchars(_glt('Mini Properties is disabled')) . '</div>';
	echo '<div id="browser_and_miniprops_are_disabled">' . htmlspecialchars(_glt('Browser and Mini Properties disabled')) . '</div>';
	echo '<div id="matrix_profile_incomplete">' . htmlspecialchars(_glt('Matrix profile is incomplete')) . '</div>';
	echo '<div id="navbar_show_browser">' . htmlspecialchars(_glt('Show Browser')) . '</div>';
	echo '<div id="navbar_hide_browser">' . htmlspecialchars(_glt('Hide Browser')) . '</div>';
	echo '<div id="navbar_show_properties">' . htmlspecialchars(_glt('Show Properties View')) . '</div>';
	echo '<div id="navbar_hide_properties">' . htmlspecialchars(_glt('Hide Properties View')) . '</div>';
?>