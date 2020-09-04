<?php 
include('..\..\library\utils.php');

/**
 * Created by Achmad Fatkharrofiqi
 * Updated at 28/08/2020
 */
class LDAP{

    private $username;
    private $password;
    private $ldapCriteria;
    private $modelNameMapping;
    private $host;
    private $ldapConnect;
    private $userId;
    private $dn;
    private $url = "index.php";
    private $group = [];
    private $result;
    private $response;

    public function __construct($username, $password, $ldapCriteria, $modelNameMapping, $ldapHost, $dn){
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setLdapCriteria($ldapCriteria);
        $this->setModelNameMapping($modelNameMapping);
        $this->setHost($ldapHost);
        $this->setDn($dn);
    }

    public function toString(){
        return "username = {$this->username} 
                password = {$this->password}
                ldapCriteria = {$this->ldapCriteria}
                modelNameMapping = {$this->modelNameMapping}
                host = {$this->host}
                ldapConnect = {$this->ldapConnect}
                userId = {$this->userId}
                dn = {$this->dn}
                url = {$this->url}
                group = {$this->group}
                response = {$this->response}";
    }

    public function connect(){
        $this->ldapConnect = ldap_connect($this->getHost());
        ldap_set_option($this->ldapConnect, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($this->ldapConnect, LDAP_OPT_PROTOCOL_VERSION, 3);
        return $this;
    }

    public function setUserToFind($userId){
        $this->userId = $userId;
    }

    public function auth(){
        $response = [];
        if(@ldap_bind($this->ldapConnect, $this->getUsername(), $this->getPassword())){
            $username   = (strpos($this->userId, '\\') == true) ? explode('\\', $this->userId)[1] : $this->userId;
            $criteria   = $this->getLdapCriteria();
            $filter     = "($criteria=$username)";  // Filter LDAP
            $result     = ldap_search($this->ldapConnect, $this->dn ,$filter);
            if(!empty($result)){
                $entries    = ldap_get_entries($this->ldapConnect, $result);
                if(count($entries) <= 1) {
                    $this->setResponse('failed', "Search with criteria $filter not found");
                } else {
                    $memberOf   = $entries[0]['memberof'] ?? [];   // Get all member
                    if(count($memberOf) != 0) {
                        $groups = [];
                        foreach($memberOf as $key => $member){ 
                            if($key === "count") continue;         // Skip key with the name of "count"
                            $group    = explode(',', $member)[0];  // Get first attribute of member
                            $group    = str_replace("CN=",'',$group);  // Get the name of member
                            $groups[] = $group;
                        }
                        $this->setGroup($groups);
                        if(in_array($this->getModelNameMapping(), $this->getGroup())) {
                            $this->setResponse('success', 'Member/Group found', $groups);
                        }else{
                            $this->setResponse('failed', "You don't have access of this model");
                        }
                    } else {
                        $this->setResponse('failed', "Member/Group not found");
                    }
                }
            } else {
                $this->setResponse('failed', "Unable to search");
            }
        } else {
            $this->setResponse('failed', "ldap_bind(): Unable to bind to server: Invalid credentials");
        }
        if($this->getResponse()['status'] != 'success'){
            $this->url = 'logout.php';
        }

        $this->setResult(['url' => $this->url, 'data' => $this->getResponse()]);
    }

    public function getResult(){
        return json_encode($this->result);
    }

    public function setResult($result){
        $this->result = $result;
    }

    public function getGroup(){
        return $this->group;
    }

    public function setGroup($group){
        // array_push($this->geGroup(), $group);
        $this->group = $group;
    }

    public function getResponse(){
        return $this->response;
    }

    public function setResponse($status, $message, $data = null){
        $this->response = generate_response($status, $message, $data);
    }

    public function getHost(){
		return $this->host;
	}

	public function setHost($host){
		$this->host = $host;
	}

    public function getUsername(){
		return $this->username;
	}

	public function setUsername($username){
		$this->username = $username;
	}

	public function getPassword(){
		return $this->password;
	}

	public function setPassword($password){
		$this->password = $password;
	}

	public function getLdapCriteria(){
		return $this->ldapCriteria;
	}

	public function setLdapCriteria($ldapCriteria){
		$this->ldapCriteria = $ldapCriteria;
	}

	public function getModelNameMapping(){
		return $this->modelNameMapping;
	}

	public function setModelNameMapping($modelNameMapping){
		$this->modelNameMapping = $modelNameMapping;
    }
    
    public function getDn(){
		return $this->dn;
	}

	public function setDn($dn){
		$this->dn = $dn;
	}
}