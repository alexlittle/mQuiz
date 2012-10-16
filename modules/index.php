<?php
include_once("../config.php");
$PAGE = "modules";
include_once("../includes/header.php");

if(!isAdmin()){
	include_once("../includes/footer.php");
	die;
}

$submit=optional_param("submit","",PARAM_TEXT);

echo "hello admin!";
echo "<pre>";
if($submit != ""){
	$filename = basename( $_FILES['modulefile']['name']);
	
	$target_path = $CONFIG->modulePath.$filename;
	if(!move_uploaded_file($_FILES['modulefile']['tmp_name'], $target_path)) {
		echo "There was an error uploading the file, please try again!\n";
		die;
	}
	
	$zip = new ZipArchive;
	$res = $zip->open("uploads/".$filename);
	if ($res !== TRUE) {
		echo 'failed: not a valid zip file\n';
		die;
	}
	$zip->extractTo('temp/');
	$zip->close();
	$xml = null;
	echo "zip uploaded and extracted\n";
	if ($handle = opendir('temp/')) {
		while (false !== ($entry = readdir($handle))) {
			if($entry != "." && $entry != ".."){
				$xml = simplexml_load_file('temp/'.$entry."/module.xml");
				echo "module.xml loaded\n";
			}
		}
		closedir($handle);
	}
	deleteDir("temp");
	if(!$xml){
		echo "invalid module.xml file";
		die;
	}
	
	$course_title = $xml->meta->title;
	$title = array();
	foreach($course_title as $ct){
		foreach($ct->attributes() as $a => $b) {
			$title[strval($b)] =  strval($ct);
		}
	}
	$title = json_encode($title);
	print_r($title);
	echo "\n";
	$versionid = intval($xml->meta->versionid,10);
	$shortname = strval($xml->meta->shortname);
	$modid = $API->addModule($versionid, $title, $shortname, $filename);
	
	//Now add the sections
	$sections = $xml->structure->section;
	foreach($sections as $section){
		foreach($section->attributes() as $a => $b) {
			if($a == "id"){
				$xmlid = strval($b);
			}
		}
		$title = array();
		foreach($section->title as $st){
			foreach($st->attributes() as $k => $v) {
				$title[strval($v)] =  strval($st);
			}
		}
		$title = json_encode($title);
		print_r($title);
		echo "\n";
		// add section to db
		$sectid = $API->addSection($modid, $xmlid, $title);
		
		//now add the activities
		foreach($section->activities->activity as $activity){
			foreach($activity->attributes() as $k => $v) {
				if($k == "id"){
					$xmlid = strval($v);
				}
				if($k == "type"){
					$type = strval($v);
				}
				if($k == "digest"){
					$digest = strval($v);
				}
			}
			$title = array();
			foreach($activity->title as $at){
				foreach($at->attributes() as $k => $v) {
					$title[strval($v)] = strval($at);
				}
			}
			$title = json_encode($title);
			print_r($title);
			echo "\n";
			$API->addActivity($sectid, $xmlid, $title, $digest, $type);
		}
	}
	echo "Module added \n";
	
}

echo "</pre>";
?>

<form action="" method="post" enctype="multipart/form-data">
	<input name="modulefile" type="file"><br /> <input type="submit"
		value="submit" name="submit">
</form>

<?php
include_once("../includes/footer.php");

function deleteDir($dirPath) {
	if (! is_dir($dirPath)) {
		return;
	}
	if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
		$dirPath .= '/';
	}
	$files = glob($dirPath . '*', GLOB_MARK);
	foreach ($files as $file) {
		if (is_dir($file)) {
			deleteDir($file);
		} else {
			unlink($file);
		}
	}
	rmdir($dirPath);
}