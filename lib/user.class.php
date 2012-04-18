<?php
///////////////////////////////////////
// User Class
///////////////////////////////////////

class User {

    private $phpsessid;
    public $userid;
    public $firstname;
    public $lastname;
    public $username;
    public $passhash;
    public $email;
    public $props = array();
    
    /**
     * Constructor
     * 
     * @param string $username (optional)
     * @return User (this)
     */
    function user($username = "") {
        if ($username != ""){  
        	$this->setUsername($username);
            $this->load();
            return $this;     
        }
    }
    
    /**
     * Loads the data for the user from the database
     * This will not return a "group" (even though groups are 
     * stored in the Users table)
     * 
     * @param String $style (optional - default 'long') may be 'short' or 'long'
     * @return User object (this) (or Error object)
     */
    function load(){
    	global $API;
    	$API->getUser($this);
    	if($this->userid != ""){
    		$API->getUserProperties($this);
    	}
    }
    
    /**
     * Check whether supplied password matches that in database
     * 
     * @param string $password
     * @return boolean
     */
    function validPassword($password){	
    	global $API;
        return $API->userValidatePassword($this->username,$password);    
    }
    

    /////////////////////////////////////////////////////
    // getter/setter functions
    // the reason for having these is that the variables are private
    // as we don't want these vars to appear in the REST API output
    // but do need way of setting/getting these variables in other parts 
    // of the code
    /////////////////////////////////////////////////////
    
    /** 
     * get PHPSessionID
     * 
     * @return string 
     */
    function getPHPSessID(){
        return $this->phpsessid;   
    }
    
    /** 
     * set PHPSessionID
     * 
     * @param string 
     */
    function setPHPSessID($phpsessid){
        $this->phpsessid = $phpsessid;   
    }
    

	function getUsername(){
        return $this->username;   
    }
    
    
	function setUsername($username){
        $this->username = $username;   
    }   
    
    function getProp($name){
    	if ( isset($this->props[$name])){
    		return $this->props[$name];
    	} else {
    		return null;
    	}
    }
}

?>