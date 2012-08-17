<?php 

header('Content-type: application/json; charset=UTF-8');

$dir = dirname("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

$modules = Array();

$modules[0] = new stdClass();
$modules[0]->title = "Antenatal Care";
$modules[0]->version = "20120817142059";
$modules[0]->shortname = "anc";
$modules[0]->url = $dir. "/anc-20120817142059.zip";

$modules[1] = new stdClass();
$modules[1]->title = "Postnatal Care";
$modules[1]->version = "20120817142214";
$modules[1]->shortname = "pnc";
$modules[1]->url = $dir. "/pnc-20120817142214.zip";

$modules[2] = new stdClass();
$modules[2]->title = "Nutrition";
$modules[2]->version = "20120817142205";
$modules[2]->shortname = "nut";
$modules[2]->url = $dir. "/nut-20120817142205.zip";

$modules[3] = new stdClass();
$modules[3]->title = "Labour and Delivery Care";
$modules[3]->version = "20120817142149";
$modules[3]->shortname = "ldc";
$modules[3]->url = $dir. "/ldc-20120817142149.zip";

$modules[4] = new stdClass();
$modules[4]->title = "Literacy: Reading and Writing for a Range of Purposes";
$modules[4]->version = "20120817142047";
$modules[4]->shortname = "tessa-lit1";
$modules[4]->url = $dir. "/tessa-lit1-20120817142047.zip";

$modules[5] = new stdClass();
$modules[5]->title = "Video demo";
$modules[5]->version = "20120817142012";
$modules[5]->shortname = "video";
$modules[5]->url = $dir. "/video-20120817142012.zip";

$modules[6] = new stdClass();
$modules[6]->title = "Integrated Management of Newborn and Childhood Illness";
$modules[6]->version = "20120817142139";
$modules[6]->shortname = "imci";
$modules[6]->url = $dir. "/imci-20120817142139.zip";
echo json_encode($modules);

?>
