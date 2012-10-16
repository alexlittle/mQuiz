<?php 
header('Content-Type:text/html; charset=UTF-8');

//first detect if using mobile device and redirect accordingly
$uagent_obj = new uagent_info();
if($uagent_obj->DetectTierTablet() || $uagent_obj->DetectTierIphone()){
	header('Location: '.$CONFIG->homeAddress.'m/');
	die;
}

global $PAGE,$CONFIG,$MSG,$API,$HEADER;

$nologinpages = array ("login","index","register","faqs","terms","about","phoneapps","reset","search","browse","contact");

if (!in_array($PAGE,$nologinpages)){
	checkLogin();
} 

$lang = optional_param("lang","",PARAM_TEXT);
if ($lang != ""){
	setLang($lang,true);
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<META name="description" content="mQuiz: mobile quiz application"/> 
	<META name="keywords" content="mquiz,quiz,assessment,mobile,android"/> 
	<title><?php echo getstring("app.title");?></title>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>m/includes/lib/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>m/includes/lib/jquery-ui-1.8.19.custom.min.js"></script>
	<script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>includes/script.php"></script>
	<script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>m/includes/mquizengine.js"></script>
	<link rel="StyleSheet" href="<?php echo $CONFIG->homeAddress; ?>includes/style.css" type="text/css" media="screen">
	<link rel="shortcut icon" href="<?php echo $CONFIG->homeAddress; ?>images/favicon.ico" />
	<?php 
    	echo $HEADER;
    ?>
</head>

<body onload="initPage()">

	<div id="header"> 
	    <div class="content">  
	        <div id="logo"><h1><a href="<?php echo $CONFIG->homeAddress; ?>">mQuiz</a></h1></div>
	
	
	        <div id="menu">
	            <ul>
	            <li <?php if($PAGE == 'index') echo "class='selected'"; ?>><a href="<?php echo $CONFIG->homeAddress; ?>">Home</a></li>
	            <li <?php if($PAGE == 'myresults') echo "class='selected'"; ?>><a href="<?php echo $CONFIG->homeAddress; ?>my/results.php">My Results</a></li>
				<li <?php if($PAGE == 'myquizzes') echo "class='selected'"; ?>><a href="<?php echo $CONFIG->homeAddress; ?>my/quizzes.php">My Quizzes</a></li>
				<li <?php if($PAGE == 'browse') echo "class='selected'"; ?>><a href="<?php echo $CONFIG->homeAddress; ?>browse.php">Browse Quizzes</a></li>
				<li <?php if($PAGE == 'newquiz' || $PAGE == 'import') echo "class='selected'"; ?>><a href="<?php echo $CONFIG->homeAddress; ?>quiz/new.php">Create New Quiz</a></li>
				<li><a href="<?php echo $CONFIG->homeAddress; ?>m/">Mobile</a></li>
	            </ul>
	        </div>
	        <div id="login">
	        	<?php 
					if (isLoggedIn()){
				?>
	        		Welcome, <?php echo $USER->firstname." ".$USER->lastname; ?>
					<a href="<?php echo $CONFIG->homeAddress; ?>profile.php">Profile</a>
					<a href="<?php echo $CONFIG->homeAddress; ?>logout.php">Logout</a>
				<?php 
					} else {
						$url = "http" . ((!empty($_SERVER["HTTPS"])) ? "s" : "") . "://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
				?>
						Welcome, please <a href="<?php echo $CONFIG->homeAddress; ?>login.php">log in</a> or
						<a href="<?php echo $CONFIG->homeAddress; ?>register.php?ref=<?php echo $url; ?>">register</a>
				<?php 
					}
				?>	
	        </div>
	    </div>
	    <div style="clear:both;"></div>
	</div>

<div id="main">
<div class="content">