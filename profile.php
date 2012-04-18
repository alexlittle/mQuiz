<?php
include_once("config.php");
$PAGE = "profile";

$password = optional_param("password","",PARAM_TEXT);
$repeatpassword = optional_param("repeatpassword","",PARAM_TEXT);
$firstname = optional_param("firstname",$USER->firstname,PARAM_TEXT);
$surname = optional_param("surname",$USER->lastname,PARAM_TEXT);
$email = optional_param("email",$USER->username,PARAM_TEXT);
$submit = optional_param("submit","",PARAM_TEXT);

$ref = optional_param("ref",$CONFIG->homeAddress."index.php",PARAM_TEXT);

if ($submit != ""){
	if ($email == ""){
		array_push($MSG,"Enter your email");
	} else	if(!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email) ) {
		array_push($MSG,"Invalid email address format");
	} 
	
	// check all fields completed
	if ($firstname == ""){
		array_push($MSG,"Enter your firstname");
	}
	if ($surname == ""){
		array_push($MSG,"Enter your surname");
	}
	
	// check email/username not in use by anyone else
	if ($API->checkUserNameNotInUse($email) == true){
		array_push($MSG,"Email already in use");
	}
	
	// update user details
	if(count($MSG) == 0){
		if ($API->updateUser($email, $firstname, $surname)){
			array_push($MSG,"You details have been updated" );
			// reload user
			$USER = new User($email);
		} else {
			array_push($MSG,"An error occured whilst updating your details" );
		}
	}

	// check password long enough
	if (strlen($password) > 0){
		if (strlen($password) < 6){
			array_push($MSG,"Your password must be 6 characters or more");
		}
		// check passwords match
		if ($password != $repeatpassword){
			array_push($MSG,"Your passwords don't match");
		}
		
		if (strlen($password) >= 6 && $password == $repeatpassword){
			if($API->updateUserPassword($password)){
				array_push($MSG,"Your password has been updated");
			}
		}
	}
}

include_once("./includes/header.php");
echo "<h1>".getstring("profile.title")."</h1>";

if(!empty($MSG)){
	echo "<ul>";
	foreach ($MSG as $err){
		echo "<li>".$err."</li>";
	}
	echo "</ul>";
}
?>


<form method="post" action="">
<div class="formblock">
	<div class="formlabel"><?php echo getstring('register.email'); ?></div>
		<div class="formfield"><input type="text" name="email" value="<?php echo $email; ?>"></input></div>
	</div>
	<div class="formblock">
		<div class="formlabel"><?php echo getstring("register.firstname"); ?></div>
		<div class="formfield"><input type="text" name="firstname" value="<?php echo $firstname; ?>"></input></div>
	</div>
	<div class="formblock">
		<div class="formlabel"><?php echo getstring("register.surname"); ?></div>
		<div class="formfield"><input type="text" name="surname" value="<?php echo $surname; ?>"></input></div>
	</div>
	
	<div class="formblock">
		<div class="formfield"><?php echo getstring("profile.password.info"); ?></div>
	</div>
	<div class="formblock">
		<div class="formlabel"><?php echo getstring("profile.newpassword"); ?></div>
		<div class="formfield"><input type="password" name="password" value=""></input></div>
	</div>
	<div class="formblock">
		<div class="formlabel"><?php echo getstring("profile.repeatnewpassword"); ?></div>
		<div class="formfield"><input type="password" name="repeatpassword" value=""></input></div>
	</div>
	<div class="formblock">
		<div class="formlabel">&nbsp;</div>
		<div class="formfield"><input type="submit" name="submit" value="<?php echo getstring("profile.submit.button"); ?>"></input></div>
	</div>
</form>




<?php 
include_once("./includes/footer.php");
?>