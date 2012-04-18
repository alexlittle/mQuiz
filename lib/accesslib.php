<?php


function userLogin($username,$password,$log = true){
	global $USER,$MSG;

	if($username == ""){
		array_push($MSG,getstring('warning.login.noemail'));
		return false;
	}
    if($password == ""){
    	array_push($MSG,getstring('warning.login.nopassword'));
        return false;
    }   
    
    $USER = new User($username);
    $USER->setUsername($username);
    if ($USER instanceof User)  {
            if($USER->validPassword($password)){
            	$_SESSION["session_username"] = $USER->getUsername();
    			setcookie("user",$USER->getUsername(),time() + 60*60*24*30, "/mQuiz");
                
    			setLang($USER->getProp('lang'));
                if($log){
                	writeToLog('info','login','user logged in');
                }
                return true;
            } else {
            	array_push($MSG,getstring('warning.login.invalid'));
            	writeToLog('info','loginfailure','username: '.$username);
            	unset($USER);
                return false;   
            }       
    } else {
        return false;   
    }   
}   


/**
 * Start a session
 *
 * @return string | false
 */ 
function startSession($ses = 'mQuiz') {
	ini_set('session.cache_expire', 60*60*24*30);
	session_set_cookie_params(60*60*24*30);
    ini_set('session.gc_maxlifetime', 60*60*24*30);
    session_name($ses);
    session_start();
    
    // Reset the expiration time upon page load
    if (isset($_COOKIE[$ses])){
    	setcookie($ses, $_COOKIE[$ses], time() + 60*60*24*30, "/mQuiz");
    }
}
/**
 * Clear all session variables
 * 
 */ 
function clearSession() {
    $_SESSION["session_username"] = ""; 
    session_destroy();                  
} 
 
/**
 * Checks if current user is logged in
 * if not, they get redirected to homepage 
 * 
 */
function checkLogin(){
    global $USER,$CONFIG;
    $url = "http" . ((!empty($_SERVER["HTTPS"])) ? "s" : "") . "://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    if(!isset($USER->username)){
        header('Location: '.$CONFIG->homeAddress.'login.php?ref='.urlencode($url));  
        die; 
    }
}

function isLoggedIn(){
	global $USER;
	if(isset($_SESSION["session_username"]) && $_SESSION["session_username"] != ""){
		return true;
	} else {
		return false;
	}
}


