<?php 
include_once("../../config.php");

$dir = dirname("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
$dir .= "/uploads";
$lang=optional_param("lang","en",PARAM_TEXT);
$mods = $API->getModules($lang,$dir);


header('Content-type: application/json; charset=UTF-8');

echo json_encode($mods);


?>
