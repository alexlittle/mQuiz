<?php
include_once("../config.php");
$PAGE = "invite";
include_once("../includes/header.php");

$qref = required_param("qref",PARAM_TEXT);
$new = optional_param("new","",PARAM_TEXT);
$q = $API->getQuizForUser($qref, $USER->userid);

if(!$q){
	die;
}

printf("<h1>%s</h1>",$q->title);
if($new == "true"){
	printf("<div class='info'>%s</div>", getstring("quiz.new.saved",$CONFIG->homeAddress."m/?preview=true#".$q->ref));
} else if($new == "false"){
	printf("<div class='info'>%s</div>", getstring("quiz.edit.saved",$CONFIG->homeAddress."m/?preview=true#".$q->ref));
}
?>
<div id="share">
	<h2>Share:</h2>
	<div id="tweet">
		<?php 
		printf('<a href="https://twitter.com/share" class="twitter-share-button"
			data-url="%sm/#%s" data-text="Try my new quiz \'%s\'" 
			data-size="large" data-count="none"
			data-hashtags="mquiz">Tweet</a>',$CONFIG->homeAddress,$q->ref,$q->title);
		?>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
	</div>
	<div id="invite">
		<h2>Invite by email:</h2>
		<div class="formblock">
			<div class="formlabel">Email addresses:</div>
			<div class="formfield"><textarea rows="5" cols="80" name="emails" id="emails"></textarea></div>
		</div>
		<div class="formblock">
			<div class="formlabel">&nbsp;</div>
			<div class="formfield"><input type="button" value="Send" onclick="invite();" id="sendBtn"/></div>
		</div>
		<div class="formblock">
			<div class="formlabel">&nbsp;</div>
			<div class="formfield"><div id="sending"></div></div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$('#sending').hide();

	function invite(){
		var qref = '<?php echo $q->ref; ?>';
		var emails = $('#emails').val();
		$('#sending').empty();
		if(emails == ""){
			$('#sending').append("Please enter some email addresses");
			$('#sending').show();
			return;
		}
		$('#sendBtn').attr('disabled', 'disabled');
		$('#sending').append("Sending invitations...");
		$('#sending').show();
		$.ajax({
			   data:{'method':'invite','username':store.get('username'),'password':store.get('password'),'qref':qref,'emails':emails}, 
			   success:function(data){
 					if(data.result){
 						$('#sending').empty();
 						$('#sending').append("Invitations sent");
 						$('#emails').val("");
 						$('#sendBtn').removeAttr('disabled');
 					} else {
 						$('#sending').empty();
 						$('#sending').append("Sorry an error occured trying to send the invitations");
 						$('#sendBtn').removeAttr('disabled');
 					}
			   }, 
			   error:function(data){
				   $('#sending').empty();
				   $('#sending').append("Sorry an error occured trying to send the invitations");
				   $('#sendBtn').removeAttr('disabled');
			   }
			});

	}
</script>
<?php
include_once("../includes/footer.php");
