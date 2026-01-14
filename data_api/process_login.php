<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
SafeStartSession();
global $gLog;
if (!isset($sResponse) || !isset($_SESSION['model_no']) || !isset($aSettings)) {
	if (!isset($sResponse))
		$gLog->Write2Log('process_login:   THERE IS A PROBLEM  Response is not defined!');
	if (!isset($_SESSION['modelno']))
		$gLog->Write2Log('process_login:   THERE IS A PROBLEM  Model number is not defined!');
	if (!isset($aSettings))
		$gLog->Write2Log('process_login:   THERE IS A PROBLEM  aSettings is not defined!');
	exit();
}
$sErrorMsg			= '';
$sReadonlyModel 	= 'true';
$sValidLicense 		= 'false';
$sLicense	 		= '';
$sLicenseExpiry		= '';
$sSecurityEnabledModel = 'true';
$sLoginGUID			= '';
$sValidUser			= '';
$sLoginAccessToken	= '';
$sLoginRefreshToken	= '';
$sLoginFullName		= '';
$sVersion			= '';
$sPermElement		= '0';
$sPermTest			= '0';
$sPermResAlloc		= '0';
$sPermMaintenance	= '0';
$sPermProjMan		= '0';
$sPermDiagramCreate = '0';
$sPermDiagramUpdate = '0';
$sPermRelMatrix		= '0';
$_SESSION['security_enabled_model']	= 'false';
$_SESSION['readonly_model'] = 'true';
$_SESSION['valid_license'] 	= 'false';
$_SESSION['login_user'] 	= '';
$_SESSION['login_guid'] 	= '';
$_SESSION['login_access_token']	= '';
$_SESSION['login_refresh_token'] = '';
$_SESSION['login_fullname'] = '';
$_SESSION['login_perm_element']	= '0';
$_SESSION['login_perm_test'] 	= '0';
$_SESSION['login_perm_resalloc'] = '0';
$_SESSION['login_perm_maintenance'] = '0';
$_SESSION['login_perm_projman'] = '0';
$_SESSION['login_perm_creatediagram'] = '0';
$_SESSION['login_perm_updatediagram'] = '0';
$_SESSION['login_perm_relmatrix'] = '0';
$xmlDoc = null;
if (!strIsEmpty($sResponse) && $sResponse !== false) {
	$xmlDoc = new DOMDocument();
	SafeXMLLoad($xmlDoc, $sResponse);
}
if ($xmlDoc != null) {
	$xnRoot = $xmlDoc->documentElement;
	if ($xnRoot != null) {
		if ($xnRoot->nodeName === "rdf:RDF") {
			$xnObj = GetXMLFirstChild($xnRoot);
			if ($xnObj != null) {
				if ($xnObj->nodeName === "ss:login") {
					if ($xnObj->childNodes != null) {
						foreach ($xnObj->childNodes as $xnObjProp) {
							GetXMLNodeValue($xnObjProp, 'ss:readonlyconnection', 	$sReadonlyModel);
							GetXMLNodeValue($xnObjProp, 'ss:prolicense', 			$sLicense);
							GetXMLNodeValue($xnObjProp, 'ss:prolicenseexpiry', 		$sLicenseExpiry);
							GetXMLNodeValue($xnObjProp, 'ss:version', 				$sOSLCVersion);
							GetXMLNodeValue($xnObjProp, 'ss:securityenabledmodel', 	$sSecurityEnabledModel);
							GetXMLNodeValue($xnObjProp, 'ss:useridentifier', 		$sLoginGUID);
							GetXMLNodeValue($xnObjProp, 'ss:validuser', 			$sValidUser);
							GetXMLNodeValue($xnObjProp, 'ss:accesstoken',	 		$sLoginAccessToken);
							GetXMLNodeValue($xnObjProp, 'ss:refreshtoken',	 		$sLoginRefreshToken);
							if ($xnObjProp->nodeName === "ss:userfullname") {
								$xnNode 		= $xnObjProp->firstChild;
								$xnNode 		= $xnNode->firstChild;
								$sLoginFullName	= $xnNode->nodeValue;
								$xnNode 		= $xnNode->nextSibling;
								if ($xnNode) {
									$sUserID		= $xnNode->nodeValue;
								}
							}
							GetXMLNodeValue($xnObjProp, 'ss:elementpermission', $sPermElement);
							GetXMLNodeValue($xnObjProp, 'ss:diagramcreatepermission', $sPermDiagramCreate);
							GetXMLNodeValue($xnObjProp, 'ss:diagramupdatepermission', $sPermDiagramUpdate);
							GetXMLNodeValue($xnObjProp, 'ss:testpermission', $sPermTest);
							GetXMLNodeValue($xnObjProp, 'ss:resourceallocationpermission', $sPermResAlloc);
							GetXMLNodeValue($xnObjProp, 'ss:maintenanceitempermission', $sPermMaintenance);
							GetXMLNodeValue($xnObjProp, 'ss:projectmanagementitempermission', $sPermProjMan);
							GetXMLNodeValue($xnObjProp, 'ss:relationshipmatrixpermission', $sPermRelMatrix);
						}
						if (strIsEmpty($sLoginGUID) && !strIsTrue($sSecurityEnabledModel)) {
							$sLoginGUID = 'nonsecuritymodel';
						}
					}
				} else {
					$sErrorMsg = _glt('unexpected response was received');
				}
			}
		} else {
			$sErrorMsg = _glt('unexpected response was received');
		}
	} else {
		if (strIsEmpty($sResponse)) {
			$sErrorMsg = _glt('no response from the server');
		} else {
			$sErrorMsg = _glt('unexpected response was received');
		}
	}
} else {
	if (strIsEmpty($sResponse)) {
		$sErrorMsg = _glt('no response from the server');
	} else {
		$sErrorMsg = _glt('unexpected response was received');
	}
}
if (!strIsEmpty($sErrorMsg)) {
	setResponseCode(500, $sErrorMsg);
	return;
}
if (strIsEmpty($sLoginGUID)) {
	if ($sErrorMsg === 'Unknown login error' || strIsEmpty($sErrorMsg)) {
		if (isset($web_page_parent_sso)) {
			$sErrorMsg = str_replace('%USER%', $sLoginFullName, _glt('invalid sso user'));
		} else if (isset($web_page_parent_ntlm)) {
			$sErrorMsg = str_replace('%USER%', $sLoginFullName, _glt('invalid ntlm user'));
		} else {
			$sErrorMsg = _glt('invalid login details');
		}
	} else {
		$sErrorMsg = $sErrorMsg;
	}
	setResponseCode(401, $sErrorMsg);
	return;
}
$sErrorMsg = '';
$sSecurityEnabledModel = 'true';
$_SESSION['security_enabled_model']	= $sSecurityEnabledModel;
$_SESSION['readonly_model'] 		= strIsTrue($sReadonlyModel) ? 'true' : 'false';
$_SESSION['pro_cloud_license'] 		= $sLicense;
$_SESSION['pro_cloud_license_expiry'] = $sLicenseExpiry;
if (strIsEmpty($sOSLCVersion)) {
	$_SESSION['oslc_version'] 			= htmlspecialchars('<Version number not found>');
} else {
	$_SESSION['oslc_version'] 			= $sOSLCVersion;
}
$_SESSION['valid_license'] 			= 'true';
if ($sLoginGUID !== 'nonsecuritymodel') {
	$_SESSION['login_user'] 		= $sUserID;
	$_SESSION['login_guid'] 		= $sLoginGUID;
	$_SESSION['login_access_token']	= $sLoginAccessToken;
	$_SESSION['login_refresh_token'] = $sLoginRefreshToken;
	$_SESSION['login_fullname'] 	= $sLoginFullName;
	$_SESSION['login_perm_element']	= strIsTrue($sPermElement) ? '1' : '0';
	$_SESSION['login_perm_test'] 	= strIsTrue($sPermTest) ? '1' : '0';
	$_SESSION['login_perm_resalloc'] = strIsTrue($sPermResAlloc) ? '1' : '0';
	$_SESSION['login_perm_maintenance'] = strIsTrue($sPermMaintenance) ? '1' : '0';
	$_SESSION['login_perm_projman'] = strIsTrue($sPermProjMan) ? '1' : '0';
	$_SESSION['login_perm_creatediagram'] = strIsTrue($sPermDiagramCreate) ? '1' : '0';
	$_SESSION['login_perm_updatediagram'] = strIsTrue($sPermDiagramUpdate) ? '1' : '0';
	$_SESSION['login_perm_relmatrix'] = strIsTrue($sPermRelMatrix) ? '1' : '0';
} else {
	$_SESSION['login_perm_element']	= '1';
	$_SESSION['login_perm_test'] 	= '1';
	$_SESSION['login_perm_resalloc'] = '1';
	$_SESSION['login_perm_maintenance'] = '1';
	$_SESSION['login_perm_projman'] = '1';
	$_SESSION['login_perm_creatediagram'] = '1';
	$_SESSION['login_perm_updatediagram'] = '1';
	$_SESSION['login_perm_relmatrix'] = '1';
}
$sModelNameInConfig = 'model' . $_SESSION['model_no'];
$_SESSION['authorized']				= 'true';
$_SESSION['auth_code']				= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'auth_code', '');
$_SESSION['login_prompt']			= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'login_prompt', 'false');
$_SESSION['login_allow_blank_pwd']	= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'login_allow_blank_pwd', 'false');
$_SESSION['default_diagram']		= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'default_diagram', '');
$_SESSION['participate_in_reviews']	= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'participate_in_reviews', 'false');
$_SESSION['recent_search_days']		= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'recent_search_days', '3');
$_SESSION['use_avatars']			= (IsSessionSettingTrue('security_enabled_model') ? SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'use_avatars', 'true') : 'false');
$sMainLayout						= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'default_main_layout', 'list');
$sMainLayout						= mb_strtolower($sMainLayout);
$_SESSION['mainlayout']				= ($sMainLayout === 'notes' ? '3' : ($sMainLayout === 'list' ? '2' : '1'));
$_SESSION['diagramlayout']			= '1';
$_SESSION['miniprops_navigates']	= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'miniprops_navigates', 'true')) ? '1' : '0';
$_SESSION['navigate_to_diagram']	= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'navigate_to_diagram', 'true')) ? '1' : '0';
$iLastPageTimeout					= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'lastpage_timeout', '30');
if ((int)$iLastPageTimeout < 0 || (int)$iLastPageTimeout === 0)
	(int)$iLastPageTimeout = 30;
$_SESSION['lastpage_timeout']		= $iLastPageTimeout;
$_SESSION['prop_visible_location']  = strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_location_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_instances']  = strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_visible_instances', 'true')) ? '1' : '0';
$_SESSION['prop_visible_relationships'] = strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_relationships_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_taggedvalues'] = strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_taggedvalues_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_testing'] 	= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_testing_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_resourcealloc'] = strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_resourcealloc_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_attributes'] = strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_attributes_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_operations'] = strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_operations_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_runstates'] = strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_runstates_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_changes'] 	= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_changes_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_defects'] 	= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_defects_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_issues'] 	= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_issues_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_tasks'] 	= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_tasks_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_events'] 	= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_events_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_decisions'] = strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_decisions_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_efforts'] 	= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_efforts_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_risks'] 	= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_risks_visible', 'true')) ? '1' : '0';
$_SESSION['prop_visible_metrics'] 	= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'prop_sec_metrics_visible', 'true')) ? '1' : '0';
$_SESSION['show_discuss']			= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'show_discuss', 'false');
$_SESSION['add_discuss']			= (IsSessionSettingTrue('show_discuss') ? SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_discuss', 'false') : 'false');
$_SESSION['add_objects']			= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objects', 'false');
$_SESSION['add_diagrams']			= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_diagrams', 'false');
$_SESSION['edit_diagrams']			= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'edit_diagrams', 'false');
$_SESSION['edit_object_notes']		= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'edit_object_notes', 'false');
$_SESSION['add_objecttype_package']	= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objecttype_package', 'false');
$_SESSION['add_objecttype_review']	= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objecttype_review', 'false');
$_SESSION['add_objecttype_actor']	= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objecttype_actor', 'false');
$_SESSION['add_objecttype_change']	= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objecttype_change', 'false');
$_SESSION['add_objecttype_component'] = SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objecttype_component', 'false');
$_SESSION['add_objecttype_feature']	= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objecttype_feature', 'false');
$_SESSION['add_objecttype_issue']	= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objecttype_issue', 'false');
$_SESSION['add_objecttype_node']	= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objecttype_node', 'false');
$_SESSION['add_objecttype_requirement'] = SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objecttype_requirement', 'false');
$_SESSION['add_objecttype_task']	= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objecttype_task', 'false');
$_SESSION['add_objecttype_usecase']	= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objecttype_usecase', 'false');
$_SESSION['add_object_features']	= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_object_features', 'false');
$_SESSION['add_objectfeature_tests'] = (IsSessionSettingTrue('add_object_features') ? SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objectfeature_tests', 'false') : 'false');
$_SESSION['add_objectfeature_resources'] = (IsSessionSettingTrue('add_object_features') ? SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objectfeature_resources', 'false') : 'false');
$_SESSION['add_objectfeature_changes'] = (IsSessionSettingTrue('add_object_features') ? SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objectfeature_changes', 'false') : 'false');
$_SESSION['add_objectfeature_defects'] = (IsSessionSettingTrue('add_object_features') ? SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objectfeature_defects', 'false') : 'false');
$_SESSION['add_objectfeature_issues'] = (IsSessionSettingTrue('add_object_features') ? SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objectfeature_issues', 'false') : 'false');
$_SESSION['add_objectfeature_tasks'] = (IsSessionSettingTrue('add_object_features') ? SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objectfeature_tasks', 'false') : 'false');
$_SESSION['add_objectfeature_risks'] = (IsSessionSettingTrue('add_object_features') ? SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'add_objectfeature_risks', 'false') : 'false');
$_SESSION['edit_objectfeature_tests'] = SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'edit_objectfeature_tests', 'false');
$_SESSION['edit_objectfeature_resources'] = SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'edit_objectfeature_resources', 'false');
$iPod    = stripos($_SERVER['HTTP_USER_AGENT'], "iPod");
$iPhone  = stripos($_SERVER['HTTP_USER_AGENT'], "iPhone");
$iPad    = stripos($_SERVER['HTTP_USER_AGENT'], "iPad");
if ($iPod || $iPhone || $iPad) {
	$_SESSION['ios_scroll'] = false;
}
if (IsSessionSettingTrue('readonly_model') || !IsSessionSettingTrue('valid_license')) {
	$sReason = 'read-only';
	if (IsSessionSettingTrue('readonly_model') && !IsSessionSettingTrue('valid_license'))
		$sReason = 'read-only and invalid license';
	elseif (!IsSessionSettingTrue('valid_license'))
		$sReason = 'invalid license';
	$gLog->Write2Log($sModelName . ' is ' . $sReason . ', overriding edit options.');
	$_SESSION['add_discuss']			= 'false';
	$_SESSION['add_objects']			= 'false';
	$_SESSION['edit_object_notes']		= 'false';
	$_SESSION['add_object_features']	= 'false';
	$_SESSION['add_objectfeature_tests'] = 'false';
	$_SESSION['add_objectfeature_resources'] = 'false';
	$_SESSION['add_objectfeature_changes'] = 'false';
	$_SESSION['add_objectfeature_defects'] = 'false';
	$_SESSION['add_objectfeature_issues'] = 'false';
	$_SESSION['add_objectfeature_tasks'] = 'false';
	$_SESSION['add_objectfeature_risks'] = 'false';
	$_SESSION['edit_objectfeature_tests'] = 'false';
	$_SESSION['edit_objectfeature_resources'] = 'false';
}
if ($_SESSION['pro_cloud_license'] === 'Express') {
	$_SESSION['add_objects']			= 'false';
	$_SESSION['add_object_features']	= 'false';
	$_SESSION['add_objectfeature_tests'] = 'false';
	$_SESSION['add_objectfeature_resources'] = 'false';
	$_SESSION['add_objectfeature_changes'] = 'false';
	$_SESSION['add_objectfeature_defects'] = 'false';
	$_SESSION['add_objectfeature_issues'] = 'false';
	$_SESSION['add_objectfeature_tasks'] = 'false';
	$_SESSION['add_objectfeature_risks'] = 'false';
}
$_SESSION['cookie_retention']		= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'cookie_retention', '365');
$sWLPeriod			= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_period', '0');
$sWatchListOptions = ';';
$sWatchListOptions .= 'period=' . strval((int)$sWLPeriod) . ';';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_recent_discuss', 'false')) ? 'recentdiscuss=1;' : 'recentdiscuss=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_recent_reviews', 'false')) ? 'recentreview=1;' : 'recentreview=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_recent_diagram', 'false')) ? 'recentdiag=1;' : 'recentdiag=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_recent_element', 'false')) ? 'recentelem=1;' : 'recentelem=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_resalloc_active', 'false')) ? 'resallocactive=1;' : 'resallocactive=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_resalloc_today', 'false')) ? 'resalloctoday=1;' : 'resalloctoday=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_resalloc_overdue', 'false')) ? 'resallocoverdue=1;' : 'resallocoverdue=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_test_recentpass', 'false')) ? 'testrecentpass=1;' : 'testrecentpass=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_test_recentfail', 'false')) ? 'testrecentfail=1;' : 'testrecentfail=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_test_recentdefer', 'false')) ? 'testrecentdefer=1;' : 'testrecentdefer=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_test_recentnotchk', 'false')) ? 'testrecentnotchk=1;' : 'testrecentnotchk=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_test_notrun', 'false')) ? 'testnotrun=1;' : 'testnotrun=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_change_verified', 'false')) ? 'changeverified=1;' : 'changeverified=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_change_requested', 'false')) ? 'changerequested=1;' : 'changerequested=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_change_completed', 'false')) ? 'changecompleted=1;' : 'changecompleted=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_change_new', 'false')) ? 'changenew=1;' : 'changenew=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_change_incomplete', 'false')) ? 'changeincomplete=1;' : 'changeincomplete=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_defect_verified', 'false')) ? 'defectverified=1;' : 'defectverified=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_defect_requested', 'false')) ? 'defectrequested=1;' : 'defectrequested=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_defect_completed', 'false')) ? 'defectcompleted=1;' : 'defectcompleted=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_defect_new', 'false')) ? 'defectnew=1;' : 'defectnew=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_defect_incomplete', 'false')) ? 'defectincomplete=1;' : 'defectincomplete=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_issue_verified', 'false')) ? 'issueverified=1;' : 'issueverified=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_issue_requested', 'false')) ? 'issuerequested=1;' : 'issuerequested=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_issue_completed', 'false')) ? 'issuecompleted=1;' : 'issuecompleted=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_issue_new', 'false')) ? 'issuenew=1;' : 'issuenew=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_issue_incomplete', 'false')) ? 'issueincomplete=1;' : 'issueincomplete=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_task_verified', 'false')) ? 'taskverified=1;' : 'taskverified=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_task_requested', 'false')) ? 'taskrequested=1;' : 'taskrequested=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_task_completed', 'false')) ? 'taskcompleted=1;' : 'taskcompleted=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_task_new', 'false')) ? 'tasknew=1;' : 'tasknew=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_task_incomplete', 'false')) ? 'taskincomplete=1;' : 'taskincomplete=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_event_requested', 'false')) ? 'eventrequested=1;' : 'eventrequested=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_event_new', 'false')) ? 'eventnew=1;' : 'eventnew=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_event_incomplete', 'false')) ? 'eventincomplete=1;' : 'eventincomplete=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_decision_verified', 'false')) ? 'decisionverified=1;' : 'decisionverified=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_decision_requested', 'false')) ? 'decisionrequested=1;' : 'decisionrequested=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_decision_completed', 'false')) ? 'decisioncompleted=1;' : 'decisioncompleted=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_decision_new', 'false')) ? 'decisionnew=1;' : 'decisionnew=0;';
$sWatchListOptions .= strIsTrue(SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'wl_decision_incomplete', 'false')) ? 'decisionincomplete=1;' : 'decisionincomplete=0;';
$_SESSION['watchlist_options_model'] = $sWatchListOptions;
$_SESSION['hide_xml_errors']		= SafeGetArrayItem2DimByName($aSettings, $sModelNameInConfig, 'hide_xml_errors', 'true');
$sURLLocation = 'location: index.php';
# add by Achmad Fatkharrofiqi 24/08/2020
require_once __DIR__ . '/../lib/ldap_auth.php';
//header($sURLLocation);
//exit();
