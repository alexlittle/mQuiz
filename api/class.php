<?php 

include_once('../config.php');
header('Content-type: application/json; charset=UTF-8');
$modid = optional_param("modid",0,PARAM_INT);

$username = optional_param("username","",PARAM_TEXT);
$password = optional_param("password","",PARAM_TEXT);
$login = userLogin($username,$password);

$response = new stdClass();

if (!userLogin($username,$password,false)){
	$response->login = false;
	echo json_encode($response);
	die;
}

$response = $API->getClassProgress($modid);
echo json_encode($response);


?>