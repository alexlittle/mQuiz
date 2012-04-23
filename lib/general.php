<?php 

function validEmailAddress($email){
	if(!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email)){
		return false;
	} else {
		return true;
	}
	
}

?>