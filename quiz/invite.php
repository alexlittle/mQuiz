<?php
include_once("../config.php");
$PAGE = "invite";
include_once("../includes/header.php");

$qref = required_param("qref",PARAM_TEXT);
$new = optional_param("new",0,PARAM_BOOL);
$q = $API->getQuizForUser($qref, $USER->userid);

if(!$q){
	die;
}

printf("<h1>%s</h1>",$q->title);
if($new == true){
	printf("<div class='info'>%s<p>Why not <a href='%s'>try your quiz</a> out now?</p></div>", getstring("quiz.new.saved"),$CONFIG->homeAddress."m/?preview=true#".$q->ref);
} else {
	printf("<div class='info'>%s<p>Why not <a href='%s'>try your quiz</a> out now?</p></div>", getstring("quiz.edit.saved"),$CONFIG->homeAddress."m/?preview=true#".$q->ref);
}
include_once("../includes/footer.php");