<?php 

class Mailer{
	
	function resetPassword($user,$password){
		global $CONFIG;
		$subject = 'mQuiz: Password Reset' ;
		$url_reset = $CONFIG->homeAddress."profile.php";
		$message = "
				<p>Hi ".$user->firstname.",</p>
				<p>Your new mQuiz password is: ".$password."</p>
				<p>You can change your password to something more memorable on <a href='".$url_reset."'>your profile page</a>.</p>
				<p>We hope you enjoy using mQuiz!</p>
			";
		$this->sendMail($user->email,$subject,$message);
		$this->sendMail($CONFIG->emailfrom,$subject,$message);
	}
	
	function sendQuizCreated($to,$name, $quiztitle, $qref){
		global $CONFIG;
		$subject = 'mQuiz: Quiz created' ;
		$url_edit = $CONFIG->homeAddress."quiz/edit.php?ref=".$qref;
		$url_share = $CONFIG->homeAddress."m/#".$qref;
		$url_preview = $CONFIG->homeAddress."m/?preview=true#".$qref;
		$url_track = $CONFIG->homeAddress."quiz/view.php?ref=".$qref;
		$message = "
			<p>Hi ".$name.",</p>
			<p>Your new mQuiz '".$quiztitle."' has been created.</p>
			<p><b>Preview</b> your quiz: <a href='".$url_preview."'>".$url_preview."</a></p>
			<p><b>Edit</b> your quiz: <a href='".$url_edit."'>".$url_edit."</a>.</p>
			<p><b>Share</b> your quiz: <a href='".$url_share."'>".$url_share."</a></p>
			<p><b>Track</b> responses to your quiz: <a href='".$url_track."'>".$url_track."</a></p>
			<p>We hope you enjoy using mQuiz! We really appreciate your feedback, please let us know how it works for you.</p>
		";
		$this->sendMail($to,$subject,$message);
		$this->sendMail($CONFIG->emailfrom,$subject,$message);
	}
	
	function sendSignUpNotification($name){
		global $CONFIG;
		$to = $CONFIG->emailfrom ;
		$subject = 'mQuiz: New Signup' ;
		$message = $name. ' just signed up to mQuiz' ;
		$this->sendMail($to,$subject,$message);
	}
	
	function invite($to, $name, $quiztitle, $qref){
		global $CONFIG;
		$subject = 'mQuiz: '.$quiztitle ;
		$url_take = $CONFIG->homeAddress."m/#".$qref;
		$message = "
			<p>Hi,</p>
			<p>".$name." has invited you to try: '".$quiztitle."'</p>
			<p>Take the quiz here: <a href='".$url_take."'>".$url_take."</a>.</p>
			<p>We hope you enjoy using mQuiz!</p>
		";
		$this->sendMail($to,$subject,$message);
		$this->sendMail($CONFIG->emailfrom,$subject,$message);
	}
	
	private function sendMail($to,$subject,$message){
		global $CONFIG;
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
		$headers .= "From: ".$CONFIG->emailfrom . "\r\n";
		$message .= "<p>Alex: alex@mquiz.org</p>";
		mail($to, $subject, $message, $headers );
	}
}

?>