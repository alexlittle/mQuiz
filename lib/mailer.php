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
				<p>Alex: alex@mquiz.org</p>
			";
		$this->sendMail($user->email,$subject,$message);
		$this->sendMail($CONFIG->emailfrom,$subject,$message);
	}
	
	function sendQuizCreated($to,$name, $quiztitle, $quizrefid){
		global $CONFIG;
		$subject = 'mQuiz: Quiz created' ;
		$url_edit = $CONFIG->homeAddress."quiz/edit.php?ref=".$quizrefid;
		$url_take = $CONFIG->homeAddress."m/#".$quizrefid;
		$message = "
			<p>Hi ".$name.",</p>
			<p>Your new mQuiz '".$quiztitle."' has been created.</p>
			<p>Share your quiz with this link: <a href='".$url_take."'>".$url_take."</a>, you can also try it out yourself with this link.</p>
			<p>To edit your quiz visit: <a href='".$url_edit."'>".$url_edit."</a>.</p>
			<p>We hope you enjoy using mQuiz!</p>
			<p>Alex: alex@mquiz.org</p>
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
	
	private function sendMail($to,$subject,$message){
		global $CONFIG;
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= "From: ".$CONFIG->emailfrom . "\r\n";
		mail($to, $subject, $message, $headers );
	}
}

?>