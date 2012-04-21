<?php
include_once("config.php");
$PAGE = "browse";
include_once("./includes/header.php");

$view = optional_param('view','alpha',PARAM_TEXT);
$tag = optional_param('tag','',PARAM_TEXT);
$init = optional_param("init","",PARAM_TEXT);

?>
<h1>Browse Quizzes</h1>

<div id="viewby">
	<?php 
	if($view == "alpha" && $init == ""){
		printf("Alphabetical list");
	} else {
		printf("<a href='?view=alpha'>Alphabetical list</a>");
	}
	echo " | ";
	if($view == "tag" && $tag == ""){
		printf("Tag cloud");
	} else {
		printf("<a href='?view=tag'>Tag cloud</a>");
	}
	?>
</div>
<?php 
switch($view){
	case 'alpha':
		include_once('browse/alpha.php');
		break;
	case 'tag':
		include_once('browse/tag.php');
		break;
}
include_once("./includes/footer.php");