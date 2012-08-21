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
	$target_path = __DIR__."/uploads/".basename( $_FILES['modulefile']['name']);
	if(!move_uploaded_file($_FILES['modulefile']['tmp_name'], $target_path)) {
		echo "There was an error uploading the file, please try again!\n";
		die;
	}

	$zip = new ZipArchive;
	$res = $zip->open("uploads/".basename( $_FILES['modulefile']['name']));
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
	$versionid = $xml->meta->versionid;
	echo "versionid: ".$versionid."\n";
	$course_title = $xml->meta->title;
	$title = array();
	foreach($course_title as $ct){
		foreach($ct->attributes() as $a => $b) {
			$title[strval($b)] = strval($ct);
		}
	}
	$title = json_encode($title);
	echo $title;
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
		//throw new InvalidArgumentException('$dirPath must be a directory');
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