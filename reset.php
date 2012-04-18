<?php
include_once("config.php");
global $PAGE,$MSG,$API;
$PAGE = "reset";
$submit = optional_param("submit","",PARAM_TEXT);
$email = optional_param("email","",PARAM_TEXT);

include_once("./includes/header.php");
echo "<h1>".getstring("reset.title")."</h1>";

if ($submit != ""){
	// check email entered
	if($email == ""){
		array_push($MSG, "Please enter your email address");
	} else if(!$API->checkUserExists($email)){
		array_push($MSG, "Email address not found.");
	}
	
	if(empty($MSG) && $API->resetPassword($email)){
		printf("A new password has been emailed to you.");
		include_once("./includes/footer.php");
		die;
	}
}

if(!empty($MSG)){
	echo "<div class='warning'><ul>";
	foreach ($MSG as $err){
		echo "<li>".$err."</li>";
	}
	echo "</ul></div>";
}

echo "<p>".getstring("reset.text")."</h1>";
?>

<form method="post" action="">
	<div class="formblock">
		<div class="formlabel"><?php echo getstring('register.email'); ?></div>
		<div class="formfield"><input type="text" name="email" value="<?php echo $email; ?>"></input></div>
	</div>
	<div class="formblock">
		<div class="formlabel">&nbsp;</div>
		<div class="formfield">
			<input type="submit" name="submit" value="<?php echo getstring("reset.submit.button"); ?>"></input>
		</div>
	</div>
</form>

<?php 
include_once("./includes/footer.php");
?>