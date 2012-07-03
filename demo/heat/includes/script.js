
function initMQuiz(){
	var opts = {
			'menu':[],
			'allowregister': true,
			//'url':'https://localhost/mquiz/api/?format=json',
			'url':'http://mquiz.org/api/?format=json',
			'timeout': 60000,
			'cacheexpire':60
			};
	$('#modules').hide();
	mQ.init(opts);
	mQ.onLogin = function(){
		$('#mq').empty();
		$('#modules').show();
		loadQuizzesFromCache();
	}
	mQ.onRegister = function(){
		$('#mq').empty();
		$('#modules').show();
		loadQuizzesFromCache();
		document.location = 'index.html';
	}
}

function inithome(){
	initMQuiz();
	mQ.onLogout = function(){
		$('#modules').hide();
		$('#menu').hide();
		document.location = 'index.html';
	}
	if(mQ.loggedIn()){
		mQ.showUsername();
		$('#modules').show();
	} else {
		$('#modules').hide();
		mQ.showLogin('#modules');
	}
}


function init(){
	initMQuiz();
	mQ.onLogout = function(){
		$('#modules').hide();
		$('#menu').hide();
		document.location = '../index.html';
	}
	if(mQ.loggedIn()){
		mQ.showUsername();
	} else {
		document.location = "../index.html";
	}
}

function hChange(){
	if($(location).attr('hash')){
		mQ.showPage($(location).attr('hash'));
	} else {
		mQ.showPage('#results');
	}
}

function playVid(file){
	window.plugins.videoPlayer.play("file:///sdcard/heat/"+file);
}
