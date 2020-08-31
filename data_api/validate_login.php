<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	if  ( !isset($sAuthType) || !isset($sModelName) )
	{
		exit();
	}
	$sRootPath = dirname(dirname(__FILE__));
	require_once $sRootPath . '/globals.php';
	$sErrorMsg			= 'Unknown login error';
	$sReadonlyModel 	= 'true';
	$sValidLicense 		= 'false';
	$sLicense	 		= '';
	$sLicenseExpiry		= '';
	$sSecurityEnabledModel = 'true';
	$sLoginGUID			= '';
	$sLoginFullName		= '';
	$sVersion			= '';
	$sPermElement		= '0';
	$sPermTest			= '0';
	$sPermResAlloc		= '0';
	$sPermMaintenance	= '0';
	$sPermProjMan		= '0';
	$_SESSION['security_enabled_model']	= 'false';
	$_SESSION['readonly_model'] = 'true';
	$_SESSION['valid_license'] 	= 'false';
	$_SESSION['login_user'] 	= '';
	$_SESSION['login_guid'] 	= '';
	$_SESSION['login_fullname'] = '';
	$_SESSION['login_perm_element']	= '0';
	$_SESSION['login_perm_test'] 	= '0';
	$_SESSION['login_perm_resalloc'] = '0';
	$_SESSION['login_perm_maintenance'] = '0';
	$_SESSION['login_perm_projman'] = '0';
	if ($sAuthType === 'access_login' || $sAuthType === 'login' )
	{
		$sErrorMsg = run_login($sUserID, $sPassword);
	}
	else
	{
		if ( !strIsEmpty($sModelName) )
		{
			$sErrorMsg = run_login($sUserID, $sPassword);
		}
		else
		{
			$sErrorMsg = '';
		}
	}
	function determine_valid_license($sLicense, $sLicenseExpiry)
	{
		$sReturn = 'false';
		$sToday = date('Y-m-d');
		if ( !strIsEmpty($sLicense) && $sLicenseExpiry>=$sToday )
			$sReturn = 'true';
		return $sReturn;
	}
	function run_login($sUserID, $sPassword)
	{
		$sErrorMsg = '';
		$sReadonlyModel 	= 'true';
		$sValidLicense 		= 'false';
		$sLicense	 		= '';
		$sLicenseExpiry		= '';
		$sOSLCVersion		= '';
		$sSecurityEnabledModel = 'true';
		$sLoginGUID			= '';
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
		global $g_sOSLCString;
		BuildOSLCConnectionString();
		$sURL 		= $g_sOSLCString;
		$sURL 	   .= 'login/';
		$sPayload 	= 'uid=' . $sUserID . ';pwd=' . $sPassword;
		$_SESSION['ssl_userid'] = $sUserID;
		$_SESSION['ssl_password'] = $sPassword;
		$sResponse 	= HTTPPostXMLRaw($sURL, $sPayload, $sErrorMsg);
		AddItemToSystemOutput('validate_login:  response=' . $sResponse);
		if ( strIsEmpty($sErrorMsg) )
		{
			AddItemToSystemOutput('validate_login:  error msg empty');
			$xmlDoc = null;
			if ( !strIsEmpty($sResponse) && $sResponse!==false)
			{
				$xmlDoc = new DOMDocument();
				SafeXMLLoad($xmlDoc, $sResponse);
			}
			if ($xmlDoc != null)
			{
				$xnRoot = $xmlDoc->documentElement;
				if ($xnRoot != null)
				{
					if ($xnRoot->nodeName === "rdf:RDF")
					{
						$xnObj = GetXMLFirstChild($xnRoot);
						if ($xnObj != null)
						{
							if ($xnObj->nodeName === "ss:login")
							{
								if ($xnObj->childNodes != null)
								{
									foreach ($xnObj->childNodes as $xnObjProp)
									{
										GetXMLNodeValue($xnObjProp, 'ss:readonlyconnection', 	$sReadonlyModel);
										GetXMLNodeValue($xnObjProp, 'ss:prolicense', 			$sLicense);
										GetXMLNodeValue($xnObjProp, 'ss:prolicenseexpiry', 		$sLicenseExpiry);
										GetXMLNodeValue($xnObjProp, 'ss:version', 			$sOSLCVersion);
										GetXMLNodeValue($xnObjProp, 'ss:securityenabledmodel', 	$sSecurityEnabledModel);
										GetXMLNodeValue($xnObjProp, 'ss:useridentifier', 		$sLoginGUID);
										if ($xnObjProp->nodeName==="ss:userfullname")
										{
											$xnNode 		= $xnObjProp->firstChild;
											$xnNode 		= $xnNode->firstChild;
											$sLoginFullName	= $xnNode->nodeValue;
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
									if ( strIsEmpty($sLoginGUID) && !strIsTrue($sSecurityEnabledModel))
									{
										$sLoginGUID = 'nonsecuritymodel';
									}
								}
							}
							else
							{
								$sErrorMsg = _glt('unexpected response was received');
							}
						}
					}
					else
					{
						$sErrorMsg = _glt('unexpected response was received');
					}
				}
				else
				{
					if (strIsEmpty($sResponse) )
					{
						$sErrorMsg = _glt('no response from the server');
					}
					else
					{
						$sErrorMsg = _glt('unexpected response was received');
					}
				}
			}
			else
			{
				if (strIsEmpty($sResponse) )
				{
					$sErrorMsg = _glt('no response from the server');
				}
				else
				{
					$sErrorMsg = _glt('unexpected response was received');
				}
			}
		}
		else
		{
		}
		if ( !strIsEmpty($sLoginGUID) )
		{
			$sErrorMsg = '';
			$sSecurityEnabledModel = 'true';
			$_SESSION['security_enabled_model']	= $sSecurityEnabledModel;
			$_SESSION['readonly_model'] 		= strIsTrue($sReadonlyModel) ? 'true' : 'false';
			$_SESSION['pro_cloud_license'] 		= $sLicense;
			$_SESSION['pro_cloud_license_expiry'] = $sLicenseExpiry;
			if (strIsEmpty($sOSLCVersion))
			{
				$_SESSION['oslc_version'] 			= htmlspecialchars('<Version number not found>');
			}
			else
			{
				$_SESSION['oslc_version'] 			= $sOSLCVersion;
			}
			$_SESSION['valid_license'] 			= 'true';
			if ( $sLoginGUID !== 'nonsecuritymodel')
			{
				$_SESSION['login_user'] 		= $sUserID;
				$_SESSION['login_guid'] 		= $sLoginGUID;
				$_SESSION['login_fullname'] 	= $sLoginFullName;
				$_SESSION['login_perm_element']	= strIsTrue($sPermElement) ? '1' : '0';
				$_SESSION['login_perm_test'] 	= strIsTrue($sPermTest) ? '1' : '0';
				$_SESSION['login_perm_resalloc'] = strIsTrue($sPermResAlloc) ? '1' : '0';
				$_SESSION['login_perm_maintenance'] = strIsTrue($sPermMaintenance) ? '1' : '0';
				$_SESSION['login_perm_projman'] = strIsTrue($sPermProjMan) ? '1' : '0';
				$_SESSION['login_perm_creatediagram'] = strIsTrue($sPermDiagramCreate) ? '1' : '0';
				$_SESSION['login_perm_updatediagram'] = strIsTrue($sPermDiagramUpdate) ? '1' : '0';
				$_SESSION['login_perm_relmatrix'] = strIsTrue($sPermRelMatrix) ? '1' : '0';
			}
			else
			{
				$_SESSION['login_perm_element']	= '1';
				$_SESSION['login_perm_test'] 	= '1';
				$_SESSION['login_perm_resalloc'] = '1';
				$_SESSION['login_perm_maintenance'] = '1';
				$_SESSION['login_perm_projman'] = '1';
				$_SESSION['login_perm_creatediagram'] = '1';
				$_SESSION['login_perm_updatediagram'] = '1';
				$_SESSION['login_perm_relmatrix'] = '1';
			}
		}
		else
		{
			if ( $sErrorMsg==='Unknown login error' || strIsEmpty($sErrorMsg) )
			{
				$sErrorMsg = g_csHTTPNewLine . _glt('invalid login details');
			}
			else
			{
				$sErrorMsg = g_csHTTPNewLine . $sErrorMsg;
			}
		}
		return $sErrorMsg;
	}
?>