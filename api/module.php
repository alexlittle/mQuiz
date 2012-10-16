<?php 
include_once('../config.php');
$modid = optional_param("modid",0,PARAM_INT);
$lang = optional_param("lang","en",PARAM_TEXT);

$module = $API->getModule($modid, $lang);

if($module instanceof Module){
	echo json_encode($module);
} else {
	$modules = $API->getModules($lang);
	echo json_encode($modules);
}
?>