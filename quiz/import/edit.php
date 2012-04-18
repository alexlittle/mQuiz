<?php 
include_once("../../config.php");
$PAGE = "import";
include_once("../../includes/header.php");
global $IMPORT_INFO;
$IMPORT_INFO = array();
$ref = optional_param('ref',"",PARAM_TEXT);
$q = $API->getQuizForUser($ref,$USER->userid);

if($q == null){
	echo "Quiz not found";
	include_once("../includes/footer.php");
	die;
}

if ($API->quizHasAttempts($ref)){
	printf("<div class='info'>Sorry, you cannot edit this quiz as attempts have already been made on it.</div>");

	include_once("../../includes/footer.php");
	die;
}

$submit = optional_param("submit","",PARAM_TEXT);
$title = optional_param("title",$q->title,PARAM_TEXT);
$description = optional_param("description",$q->description,PARAM_TEXT);
$content = optional_param("content",$q->props['content'],PARAM_TEXT);
$format = optional_param("format","gift",PARAM_TEXT);

$supported_qtypes = array('truefalse','multichoice','essay','shortanswer','numerical');
if ($submit != ""){

	if($title == ""){
		array_push($MSG,getstring('import.quiz.error.notitle'));
	}
	if($content == ""){
		array_push($MSG,getstring('import.quiz.error.nocontent'));
	}
	$questions_to_import = array();

	if($format == 'gift'){
		include_once('./gift/import.php');
		$import = new qformat_gift();
			
		$lines = explode("\n",$content);
		$questions = $import->readquestions($lines);
			
		foreach($questions as $qu){
			if (in_array($qu->qtype, $supported_qtypes)){
				array_push($questions_to_import,$qu);
			} else {
				if($qu->qtype != 'category'){
					array_push($IMPORT_INFO, $qu->qtype." question type not yet supported ('".$qu->questiontext."')");
				}
			}
		}
	}

	if(count($questions_to_import) == 0){
		array_push($MSG,getstring('import.quiz.error.nosuppportedquestions'));
	}

	if(count($MSG) == 0){
		// now do the actual import
		if($format == 'gift'){
			$quizdraft = optional_param("quizdraft",0,PARAM_INT);
			// update title and content
			$API->updateQuiz($ref,$title,$quizdraft,$description);
			$API->setProp('quiz',$q->quizid,'content',$content);
			// remove current questions/responses (will add them again below)
			$API->removeQuiz($q->quizid);
			
			$importer = new GIFTImporter();
			$importer->quizid = $q->quizid;
			$importer->import($questions_to_import);
				
			$API->setProp('quiz', $q->quizid, 'maxscore', $importer->quizmaxscore);
		}

		$q = $API->getQuizById($q->quizid);
		
		// store JSON object for quiz (for caching)
		$json = json_encode($API->getQuizObject($q->ref));
		$API->setProp('quiz', $q->quizid, 'json', $json);
		
		printf("<div class='info'>%s<p>Why not <a href='%s'>try your quiz</a> out now?</p></div>", getstring("quiz.edit.saved"),$CONFIG->homeAddress."m/#".$ref);
		
		if(!empty($IMPORT_INFO)){
			echo "<div class='info'>Some of your questions were not imported:<ul>";
			foreach ($IMPORT_INFO as $info){
				echo "<li>".$info."</li>";
			}
			echo "</ul></div>";
		}
		include_once("../../includes/footer.php");
		die;
	}
}
?>

<h1><?php echo getstring("quiz.edit.title"); ?></h1>

<?php 
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
		<div class="formfield"><input type="text" name="title" size="60" value="<?php echo $title; ?>"></input></div>
	</div>
	<div class="formblock">
		<div class="formlabel">&nbsp;</div>
		<div class='formfield'>
			<input type="checkbox" name="quizdraft" value="1"
			<?php 
				if($q->draft == 1){
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
		<div class="formlabel">Quiz Questions<br/><small>(enter in <a target='_blank' href='http://microformats.org/wiki/gift'>GIFT format</a>)</small></div>
		<div class="formfield"><textarea name="content" cols="100" rows="20"><?php echo stripslashes($content); ?></textarea></div>
	</div>
	<div class="formblock">
		<div class="formlabel">&nbsp;</div>
		<div class="formfield"><input type="submit" name="submit" value="Save changes"></input></div>
	</div>
	
</form>


<?php 
include_once("../../includes/footer.php");
?>