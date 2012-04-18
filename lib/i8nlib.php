<?php
function getstring($name, $args=array()){
	global $CONFIG,$API,$USER;
	if (isset($_SESSION["session_lang"]) && $_SESSION["session_lang"] != ""){
		$sesslang = $_SESSION["session_lang"];
	} else {
		$sesslang = $CONFIG->defaultlang;
	}
	if(!isset($_SESSION["lang_strings"])){
		include_once $CONFIG->homePath.'lang/'.$sesslang.".php";
	 	$_SESSION["lang_strings"] =  $LANG;
	}
	
	$langstrs =  $_SESSION["lang_strings"];
	if (isset($langstrs[$name]) && trim($langstrs[$name]) != ""){
		return vsprintf($langstrs[$name], $args);
	}
	
	writeToLog('warning','lang',$name.' not found for '.$sesslang);
	return $name. " not found for ".$sesslang;

}

function setLang($lang, $redirect=false){
	global $USER,$API;
	$_SESSION["session_lang"] = $lang;
	unset($_SESSION["lang_strings"]);
	
	if($USER->userid != 0){
		$API->setUserProperty($USER->userid,'lang',$lang);
	}
	
	if($redirect){
		//redirect back to same page (to avoid the form resubmission popup)
		$url = "http" . ((!empty($_SERVER["HTTPS"])) ? "s" : "") . "://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		writeToLog('info','langchange','changed to: '.$lang);
		header('Location: '.$url);  
	    die; 
	}
}


