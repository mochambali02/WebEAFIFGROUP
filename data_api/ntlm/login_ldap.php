<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
	ob_start();
	$sRootPath = dirname(dirname(dirname(__FILE__)));
	require_once $sRootPath . '/globals.php';
	SafeStartSession();
	$sErrorMsg = '';
	$sUserID = '';
	if ($_SERVER["REQUEST_METHOD"] !== "POST")
	{
		setResponseCode(405, _glt('Direct navigation is not allowed'));
		exit();
	}
	$web_page_parent_ntlm = true;
	$aSettings = ParseINIFile2Array('../../includes/webea_config.ini');
	$sModelNameInConfig = 'model' . $_SESSION['model_no'];
	global $g_sOSLCString;
	// inject configuration for url
	$_SESSION['db_alias'] = $aSettings['ldap_settings']['pro_cloud_name_db_alias'];
	$_SESSION['port']	  = $aSettings['ldap_settings']['pro_cloud_port_db_alias'];
	
	BuildOSLCConnectionString();
	$sOSLC_URL = $g_sOSLCString . 'login/';
	$sBody = 'sso=NTLM;';
    $sResponse = HTTPPostXMLRaw($sOSLC_URL, $sBody, $httpCode, $sErrorMsg);
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
								GetXMLNodeValue($xnObjProp, 'ss:version', 				$sOSLCVersion);
								GetXMLNodeValue($xnObjProp, 'ss:securityenabledmodel', 	$sSecurityEnabledModel);
								GetXMLNodeValue($xnObjProp, 'ss:useridentifier', 		$sLoginGUID);
								GetXMLNodeValue($xnObjProp, 'ss:validuser', 			$sValidUser);
								GetXMLNodeValue($xnObjProp, 'ss:accesstoken',	 		$sLoginAccessToken);
								GetXMLNodeValue($xnObjProp, 'ss:refreshtoken',	 		$sLoginRefreshToken);
								if ($xnObjProp->nodeName==="ss:userfullname")
								{
									$xnNode 		= $xnObjProp->firstChild;
									$xnNode 		= $xnNode->firstChild;
									$sLoginFullName	= $xnNode->nodeValue;
									$xnNode 		= $xnNode->nextSibling;
									if ($xnNode)
									{
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
			$sErrorMsg = 'Username and password do not match!';
		}
		else
		{
			$sErrorMsg = _glt('unexpected response was received');
		}
    }
    $all_model            = $aSettings['ldap_to_model'];
    $all_model_name       = $aSettings['model_list'];
    $model_name_mapping   = $all_model[$sModelNameInConfig];   // Get model mapping to LDAP Group
	$conf                 = $aSettings['ldap_settings'];      // Get configuration for ldap account
	$ldap_criteria        = $conf['ldap_criteria'];           // Get search criteria
	$ldap_host            = $conf['host'];                    // Get Hostname
	$ldap_username        = $conf['username'];                // Get Username
	$ldap_password        = $conf['password'];                // Get Password
	$distinguised_name    = $conf['dn'];                      // Get DN
	include('..\..\library\LDAP.php');

	$ldap = new LDAP($ldap_username, $ldap_password, $ldap_criteria, $model_name_mapping, $ldap_host, $distinguised_name);
	$ldap->setUserToFind($sUserID);
	$ldap->connect();
	$ldap->auth();
    $groups = $ldap->getGroup();
    
    $models = [];
    foreach($groups as $group) {
        foreach($all_model as $key => $model) {
            if($model == $group) {
                $models[$key] = $all_model_name[$key];
            }
        }
    }
	
	if ( strIsEmpty($sErrorMsg) )
	{
		echo json_encode(['response'=>'success', 'models'=>$models]);
	}
	unset($web_page_parent_ntlm);
	if ( !strIsEmpty($sErrorMsg) )
	{
		if ($httpCode == 422)
		{
			setResponseCode(401, $sErrorMsg);
		}
		else
		{
			setResponseCode($httpCode, $sErrorMsg);
		}
	}
?>