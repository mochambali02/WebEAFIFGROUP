<?php 

print_r('achmad');
exit;

function dd($data){
    echo "<pre>";
    print_r($data);
    exit;
}

function generate_response($status, $message, $group){
    return [
        'status'    => $status,
        'message'   => $message,
        'group'     => $group,
    ];
}

if  (!isset($sUserID) || !isset($sModelNameInConfig)) {
    if  ( !isset($sUserID) )
		$gLog->Write2Log('process_login:   THERE IS A PROBLEM  UserID is not defined!');
    if  ( !isset($sModelNameInConfig) )
        $gLog->Write2Log('process_login:   THERE IS A PROBLEM  ModelNameInConfig is not defined!');
    exit();
}

$ldap_service_account = $aSettings['ldap_service_account'];                 // Get configuration for ldap account
$ldap_criteria        = $aSettings['ldap_criteria']['criteria'];            // Get search criteria
$model_name_mapping   = $aSettings['ldap_to_model'][$sModelNameInConfig];   // Get model mapping

$ldap_dn       = $ldap_service_account['username'];
$ldap_password = $ldap_service_account['password'];

$ldap_con      = ldap_connect("WIN-4Q04UEVS310.braindevs.com");
$response = [];

ldap_set_option($ldap_con, LDAP_OPT_REFERRALS, 0);
ldap_set_option($ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);

if(@ldap_bind($ldap_con, $ldap_dn, $ldap_password)){
    $username   = (strpos($sUserID, '\\') == true) ? explode('\\', $sUserID)[1] : $sUserID;
    $filter     = "($ldap_criteria=$username)";  // Filter LDAP
    $result     = ldap_search($ldap_con,"dc=braindevs,dc=com",$filter) or die('unable to search');
    $entries    = ldap_get_entries($ldap_con, $result);
    if(count($entries) <= 1) {
        $response = generate_response('failed', "Search with criteria $filter not found", null);
    } else {
        $memberOf   = $entries[0]['memberof'] ?? [];   // Get all member
        if(count($memberOf) != 0) {
            $groups = [];
            foreach($memberOf as $key => $member){ 
                if($key === "count") continue;       // Skip key with the name of "count"
                $group    = explode(',', $member)[0];  // Get first attribute of member
                $group    = str_replace("CN=",'',$group);  // Get the name of member
                $groups[] = $group;
            }
            if(in_array($model_name_mapping, $groups)) {
                $response = generate_response('success', 'Member/Group found', $groups);
            }else{
                $response = generate_response('failed', "You don't have access of this model", null);
            }
        } else {
            $response = generate_response('failed', "Member/Group not found", null);
        }
    }
} else {
    $response = generate_response('failed', "ldap_bind(): Unable to bind to server: Invalid credentials", null);
}

$url = 'index.php';
if($response['status'] != 'success'){
    $url = 'logout.php';
}

echo json_encode(['url' => $url, 'data'=>$response]);