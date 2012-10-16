<?php 

//TODO - this can be removed - use the /api/modules/ instead
include_once("../../config.php");

$lang=optional_param("lang",$CONFIG->defaultlang,PARAM_TEXT);
$mods = $API->getModules($lang);


header('Content-type: application/json; charset=UTF-8');

echo json_encode($mods);


?>
