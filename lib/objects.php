<?php

class QuizAttempt {
	public $quizref;
	public $username;
	public $maxscore;
	public $userscore;
	public $quizdate;
	public $submituser;
}


class QuizAttemptResponse {
	public $qaid;
	public $userScore;
	public $questionRef;
	public $questionResponseRef;
	public $text;
}

?>