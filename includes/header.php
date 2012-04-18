<?php 
header('Content-Type:text/html; charset=UTF-8');

//first detect if using mobile device and redirect accordingly
$uagent_obj = new uagent_info();
if($uagent_obj->DetectIphone() || $uagent_obj->DetectAndroidPhone()){
	header('Location: '.$CONFIG->homeAddress.'m/');
	die;
}

global $PAGE,$CONFIG,$MSG,$API,$HEADER;

$nologinpages = array ("login","index","register","faqs","terms","about","phoneapps","reset");

if (!in_array($PAGE,$nologinpages)){
	checkLogin();
} 

$lang = optional_param("lang","",PARAM_TEXT);
if ($lang != ""){
	setLang($lang,true);
}

?>
<!DOCTYPE html>
<html manifest="m/mquiz.appcache">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<META name="description" content="mQuiz: mobile quiz application"/> 
	<META name="keywords" content="mquiz,quiz,assessment,mobile,android"/> 
	<title><?php echo getstring("app.title");?></title>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>includes/jquery-1.7.min.js"></script>
	<script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>includes/quiz.js"></script>
	<script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>/m/includes/mquiz.js"></script>
	<link rel="StyleSheet" href="<?php echo $CONFIG->homeAddress; ?>includes/style.css" type="text/css" media="screen">
	<link rel="shortcut icon" href="<?php echo $CONFIG->homeAddress; ?>images/favicon.ico" />
	<?php 
    	echo $HEADER;
    ?>
    
    <script type="text/javascript">
    	function init(){
    		<?php 
    	    	if(isLoggedIn()){
    	    		printf("store.set('username','%s');",$USER->username);
    	    		printf("store.set('displayname','%s');",$USER->firstname." ".$USER->lastname);
    	    		printf("store.set('password','%s');",$USER->password);
    	    		
    	    	} 
    	    	if(!$uagent_obj->DetectIphone() && !$uagent_obj->DetectAndroidPhone()){
    	    		printf("store.set('source','%s');",$CONFIG->homeAddress);
    	    	}
    	    ?>
    	}
    </script>
</head>

<body onload="init()">

<div id="page">
	<div id="header">
		<div id="logo">
			<a href="<?php echo $CONFIG->homeAddress; ?>index.php" class="logo">mQuiz</a>
		</div>
		<div id="menu">
			<ul>
				<li><a href="<?php echo $CONFIG->homeAddress; ?>my/results.php">My Results</a></li>
				<li><a href="<?php echo $CONFIG->homeAddress; ?>my/quizzes.php">My Quizzes</a></li>
				<li><a href="<?php echo $CONFIG->homeAddress; ?>quiz/new.php">Create New Quiz</a></li>
				<li><a href="<?php echo $CONFIG->homeAddress; ?>m/">Mobile</a></li>
			</ul>
		</div>
		<div id="userlogin">
			<ul>
				<?php 
					if (isLoggedIn()){
				?>
						<li><?php echo $USER->firstname." ".$USER->lastname; ?></li>
						<li><a href="<?php echo $CONFIG->homeAddress; ?>profile.php">Profile</a></li>
						<li><a href="<?php echo $CONFIG->homeAddress; ?>logout.php">Logout</a></li>
				<?php 
					} else {
						$url = "http" . ((!empty($_SERVER["HTTPS"])) ? "s" : "") . "://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
				?>
						<li><a href="<?php echo $CONFIG->homeAddress; ?>login.php">Login</a></li>
						<li><a href="<?php echo $CONFIG->homeAddress; ?>register.php?ref=<?php echo $url; ?>">Register</a></li>
				<?php 
					}
				?>
				
				<!-- li><form action="" method="post" name="langform" id="langform">
				<select name="lang" onchange="document.langform.submit();">
					<?php 
						foreach ($CONFIG->langs as $key => $value){
							if (isset($_SESSION["session_lang"]) &&  $_SESSION["session_lang"] == $key){
								echo "<option value='".$key."' selected='selected'>".$value."</option>";
							} else {
								echo "<option value='".$key."'>".$value."</option>";
							}
						}
					?>
				</select>
				</form></li -->
			</ul>
		</div>
		<div style="clear:both"></div>
	</div>

<div id="content">