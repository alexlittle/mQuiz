<?php 
include_once("../config.php");

$format = optional_param("format","plain",PARAM_TEXT);

if($format == 'json'){
	header('Content-type: application/json; charset=UTF-8');
} else {
	header("Content-type:text/plain;charset:utf-8");
}

$username = optional_param("username","",PARAM_TEXT);
$password = optional_param("password","",PARAM_TEXT);

$response = new stdClass();

if (!userLogin($username,$password,false)){
	$response->login = false;
	echo json_encode($response);
	die;
}

?>