<?php 

$inits = $API->browseAlpha();

$first = "";
for($i= 65; $i<91; $i++){
	if(isset($inits[chr($i)])){
		printf("<a href='?view=alpha&init=%s' title='%d quizzes'>%s</a> ",chr($i),$inits[chr($i)],chr($i));
		if($first == ""){
			$first = chr($i);
		}
		unset($inits[chr($i)]);
	} else {
		printf("%s ",chr($i));
	}
}
foreach($inits as $k=>$v){
	printf("<a href='?view=alpha&init=%s' title='%d quizzes'>%s</a> ",$k,$v,$k);
}

$init = optional_param("init",$first,PARAM_TEXT);

$list = $API->browseAlpha($init);
foreach($list as $r){
	echo "<div class='quizlist'>";
	printf("<a href='%sm/#%s'>%s</a>",$CONFIG->homeAddress,$r->ref,$r->title);
	if($r->description != ""){
		printf(" - <span>%s</span>",$r->description);
	}
	echo "</div>";
}

?>