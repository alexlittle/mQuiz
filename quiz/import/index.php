<?php 
include_once("../../config.php");
$PAGE = "import";

global $IMPORT_INFO;
$IMPORT_INFO = array();

$submit = optional_param("submit","",PARAM_TEXT);
$title = optional_param("title","",PARAM_HTML);
$quizdraft = optional_param('quizdraft',0,PARAM_INT);
$description = optional_param("description","",PARAM_TEXT);
$tags = optional_param("tags","",PARAM_TEXT);
$content = optional_param("content","",PARAM_TEXT);
$format = optional_param("format","gift",PARAM_TEXT);


if ($submit != ""){
	
	if($title == ""){
		array_push($MSG,getstring('import.quiz.error.notitle'));
	}
	if($content == ""){
		array_push($MSG,getstring('import.quiz.error.nocontent'));
	}
	
	if($format == 'gift'){
		$q = $API->createQuizfromGIFT($content,$title,$quizdraft,$description,$tags);
		//die;
		if($q){
			// send mail to owner
			$m = new Mailer();
			$m->sendQuizCreated($USER->email,$USER->firstname, $title, $q->qref);
			header(sprintf("Location:  %squiz/options.php?qref=%s&new=true",$CONFIG->homeAddress, $q->qref));
			die;
		}
	}

}

include_once("../../includes/header.php");
echo "<h1>Create quiz in GIFT format</h1>";

if(!empty($MSG)){
	echo "<div class='warning'><ul>";
	foreach ($MSG as $err){
		echo "<li>".$err."</li>";
    }
    echo "</ul></div>";
}
?>

<form method="post" action="">
	<div class="formblock">
		<div class="formlabel"><?php echo getstring('import.quiz.title'); ?></div>
		<div class="formfield"><input type="text" name="title" size="60" value="<?php echo htmlentities($title); ?>"></input></div>
	</div>
	<div id="options" class="formblock">
		<div class='formlabel'>&nbsp;</div>
		<div class='formfield'>
			<input type="checkbox" name="quizdraft" value="1"
			<?php 
				if($quizdraft == 1){
					echo "checked='checked'";
				}
			?>
			/> Save as draft only
		</div>
	</div>
	<div class="formblock">
		<div class='formlabel'>Description<br/><small>(optional, max 300 characters, no HTML)</small></div>
		<div class='formfield'>
			<textarea name="description" cols="80" rows="3" maxlength="300"><?php echo $description; ?></textarea>
		</div>
	</div>
	<div class="formblock">
		<div class="formlabel">Tags</div>
		<div class="formfield">
			<input type="text" name="tags" value="<?php echo $tags; ?>" size="60"/><br/>
			<small>comma separated</small>
		</div>
	</div>
	<div class="formblock">
		<div class="formlabel">Quiz Questions<br/><small>(enter in <a target='_blank' href='http://microformats.org/wiki/gift'>GIFT format</a>)</small></div>
		<div class="formfield"><textarea name="content" cols="80" rows="20"><?php echo stripslashes($content); ?></textarea></div>
	</div>
	<div class="formblock">
		<div class="formlabel">&nbsp;</div>
		<div class="formfield"><input type="submit" name="submit" value="<?php echo getstring("import.quiz.add.button"); ?>"></input></div>
	</div>
	
</form>


<?php 
include_once("../../includes/footer.php");
?>