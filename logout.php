<?php
    include_once("config.php");
    clearSession();
    writeToLog('info','logout','user logged out');
?>
<!DOCTYPE html>
<html manifest="m/mquiz.appcache">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>includes/jquery-1.7.min.js"></script>
	<script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>m/includes/mquiz.js"></script>
	
	<script type="text/javascript">
    	function init(){
			store.clear();
			store.init();
			document.location = "<?php echo $CONFIG->homeAddress; ?>";
		}
    </script>
		
</head>
<body onload="init()">
</body>
</html>