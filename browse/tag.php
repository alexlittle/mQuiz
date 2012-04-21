<?php 

$tag = optional_param('tag','',PARAM_TEXT);

if($tag == ""){
	$cloud = $API->tagCloud();
	
	foreach($cloud->tags AS $t){
		$w = (($t->weight - $cloud->min)*200/($cloud->max - $cloud->min))+100;
		printf("<a href='browse.php?view=tag&tag=%s' style='font-size:%d%%;'>%s</a> ",$t->tagtext,$w,$t->tagtext);
	}
} else {
	printf("Quizzes tagged with '%s':",$tag);
	$quiz = $API->tagCloud($tag);
	foreach($quiz as $q){
		echo "<div class='quizlist'>";
		printf("<a href='%sm/#%s'>%s</a>",$CONFIG->homeAddress,$q->ref,$q->title);
		if($q->description != ""){
			printf(" - <span>%s</span>",$q->description);
		}
		echo "</div>";
	}
	
}
