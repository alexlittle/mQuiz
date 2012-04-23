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
	printf("<div class='info'>%s</div>", getstring("quiz.new.saved",$CONFIG->homeAddress."m/?preview=true#".$q->ref));
} else {
	printf("<div class='info'>%s</div>", getstring("quiz.edit.saved",$CONFIG->homeAddress."m/?preview=true#".$q->ref));
}
?>
<div id="share">
	<h2>Share:</h2>
	<div id="tweet">
		<?php 
		printf('<a href="https://twitter.com/share" class="twitter-share-button"
			data-url="%sm/#%s" data-text="Try my new quiz \'%s\'" 
			data-size="large" data-count="none"
			data-hashtags="mquiz">Tweet</a>',$CONFIG->homeAddress,$q->ref,$q->title);
		?>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
	</div>
	<div id="invite">
	
	
	</div>
</div>


<?php
include_once("../includes/footer.php");