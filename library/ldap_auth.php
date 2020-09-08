<?php 
include('..\..\library\LDAP.php');

if  (!isset($sUserID) || !isset($sModelNameInConfig)) {
    if  ( !isset($sUserID) )
		$gLog->Write2Log('process_login:   THERE IS A PROBLEM  UserID is not defined!');
    if  ( !isset($sModelNameInConfig) )
        $gLog->Write2Log('process_login:   THERE IS A PROBLEM  ModelNameInConfig is not defined!');
    exit();
}
$model_name_mapping   = $aSettings['ldap_to_model'][$sModelNameInConfig];   // Get model mapping to LDAP Group
$model_in_array       = explode(';',$model_name_mapping);
$conf                 = $aSettings['ldap_settings'];      // Get configuration for ldap account
$ldap_criteria        = $conf['ldap_criteria'];           // Get search criteria
$ldap_host            = $conf['host'];                    // Get Hostname
$ldap_username        = $conf['username'];                // Get Username
$ldap_password        = $conf['password'];                // Get Password
$distinguised_name    = $conf['dn'];                      // Get DN

$ldap = new LDAP($ldap_username, $ldap_password, $ldap_criteria, $model_in_array, $ldap_host, $distinguised_name);
$ldap->setUserToFind($sUserID);
$ldap->connect();
$ldap->auth();
echo $ldap->getResult();