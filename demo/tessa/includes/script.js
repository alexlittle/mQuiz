
function initMQuiz(){
	var opts = {
			'menu':[],
			'allowregister': true,
			//'url':'http://localhost/mquiz/api/?format=json',
			'url':'http://mquiz.org/api/?format=json',
			'timeout': 60000,
			'cacheexpire':10
			};
	$('#modules').hide();
	mQ.init(opts);
	mQ.onLogin = function(){
		$('#mq').empty();
		$('#modules').show();
		loadQuizzesFromCache();
		mQ.store.set('userlang','en');
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
	
	// add to tracker
	mQ.track({'location':document.location.href,'datetime':Date()});
	
	//set page lang
	showLang();
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
	//add tracker
	mQ.track({'location':document.location.href,'datetime':Date()});
	
	//set page lang
	showLang();
}

//display lang
function showLang(){
	
	
	// hide all span elements, then show again based on language selected
	var userlang = mQ.store.get("userlang");
	var langelements = $('[class=multilang]');
	var userlangfound = false;
	var newpagelang = userlang;
	// first find if the users preferred language is available on this page
	for(var i =0; i< langelements.length; i++){
		if($(langelements[i]).attr('lang') == userlang){
			userlangfound = true;
		}
	}
	
	if(!userlangfound){
		//find if a similar language exists (eg en for en_en)
		var userlangsimilar = false;
		for(var i =0; i< langelements.length; i++){
			if($(langelements[i]).attr('lang').startsWith(userlang) || userlang.startsWith($(langelements[i]).attr('lang'))){
				userlangsimilar = true;
				newpagelang = $(langelements[i]).attr('lang');
			}
		}
		
		if(!userlangsimilar){
			//fallback to the first language shown on the page
			if(langelements.length>0){
				newpagelang = $(langelements[0]).attr('lang');
			}
		}
	}
	
	// set the language in the page
	langelements.each(function(i){
		$(this).show();
		if($(this).attr('lang') != newpagelang){
			$(this).hide();
		}
	});

	/* set the select box to show current lang*/
	$("select#langchange option").each(function () {
        $(this).attr('selected','false');
      });
	$("select#langchange option").each(function () {
		if($(this).val() == newpagelang){
			$(this).attr('selected','selected');
		}
      });
	$('#langchange').val(newpagelang);
	$('#langchange').selectmenu("refresh", true);

}

function updateLang(){
	mQ.store.set('userlang',$('#langchange').val());
	showLang();
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

String.prototype.startsWith = function(str) 
{return (this.match("^"+str)==str)};
