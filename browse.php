<?php
include_once("config.php");
$PAGE = "browse";
include_once("./includes/header.php");

$view = optional_param('view','alpha',PARAM_TEXT);

?>
<h1>Browse Quizzes</h1>

<?php 
switch($view){
	case 'alpha':
		include_once('browse/alpha.php');
		break;
}
include_once("./includes/footer.php");