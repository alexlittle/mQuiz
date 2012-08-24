<?php
include_once("../config.php");
$PAGE = "modules";
include_once("../includes/header.php");

if(!isAdmin()){
	include_once("../includes/footer.php");
	die;
}

$modid = required_param("modid",PARAM_INT);
$module = $API->getModule($modid, 'en');

echo "<h2><a href='recent.php'>All modules</a> &gt; ".$module->title."</h2>";

$users = $API->getModuleParticipants($modid);

echo "<div class='participant'>";
printf('<div class="pname"><b>Name</b></div>');
printf('<div class="pprogress"><b>Percent completed</b></div>');
printf('<div class="precent"><b>Last activity date</b></div>');
echo "<div style='clear:both'></div>";
	echo "</div>";
foreach($users as $u){
	echo "<div class='participant'>";
	printf('<div class="pname">%s %s</div>',$u->firstname, $u->lastname);
	
	$percent = $u->ActivitiesComplete*100/$u->TotalActivites;
	printf('<div class="pprogress">%.0f%%</div>',$percent);
	
	$mostrecent = strtotime($u->MostRecentActivity);
	printf('<div class="precent">%s</div>',date('d M Y H:i',$mostrecent));
	echo "<div style='clear:both'></div>";
	echo "</div>";
}


?>




<?php
include_once("../includes/footer.php");