
var DATA_CACHE_EXPIRY = 60; // no of mins before the data should be updated from server;

var Q = null;
var mQ = new mQuiz();

function mQuiz(){
	this.inQuiz = false;
	this.opts = {};
	this.onLogin = function(){};
	this.onLogout = function(){};
	this.store = null;
	
	this.init = function(opts){
		this.opts = opts;
		this.store = new Store();
		this.store.init();
		
		if(!this.opts.url){
			this.opts.url = "../api/?format=json";
		}
		$.ajaxSetup({
			url: this.opts.url,
			type: "POST",
			headers:{},
			dataType:'json',
			timeout: 20000
		});
	}
	
	this.confirmExitQuiz = function(page){
		if(mQ.inQuiz){
			var endQuiz = confirm("Are you sure you want to leave this quiz?");
			if(endQuiz){
				mQ.inQuiz = false;
			} else {
				return;
			}
		}
		if(mQ.store.get('source') != "" && mQ.store.get('source') != null){
			document.location = mQ.store.get('source') ;
		} else {
			document.location = '#home';
		}
	};
	
	this.loadQuiz = function(qref,force){
		document.location = "#"+qref;
		$('#content').empty();
		mQ.showLoading('quiz');
		// find if this quiz is already in the cache
		var quiz = mQ.quizInCache(qref);
		if(!quiz || force){
			// load from server
			$.ajax({
				   data:{'method':'getquiz','username':mQ.store.get('username'),'password':mQ.store.get('password'),'qref':qref}, 
				   success:function(data){
					   if(data.error){
						   alert(data.error);
						   mQ.inQuiz = false;
						   document.location = "#select";
						   return;
					   }
					   //check for any error messages
					   if(data && !data.error){
						   //save to local cache and then load
						   mQ.store.addArrayItem('quizzes', data);
						   mQ.showQuiz(qref);
					   }
				   }, 
				   error:function(data){
					   alert("No connection available. You need to be online to load this quiz.");
					   document.location = "#select";
				   }
				});
		} else {
			mQ.showQuiz(qref);
		}
	};
	
	this.showRegister = function(){
		$('#content').empty();
		$('#content').append("<h2>Register</h2>");
		var l = $('<div>').attr({'id':'loading'}).html("Registering...");
		$('#content').append(l);
		l.hide();
		var form =  $('<form>').attr({'id':'register'});
		form.append("<div class='formblock'>" +
			"<div class='formlabel'>Email address:</div>" +
			"<div class='formfield'><input type='text' name='email' id='email'></input></div>" +
			"</div>");
		form.append("<div class='formblock'>" +
				"<div class='formlabel'>Password:</div>" +
				"<div class='formfield'><input type='password' name='password' id='password'></input></div>" +
				"</div>");
		form.append("<div class='formblock'>" +
				"<div class='formlabel'>Password (confirm):</div>" +
				"<div class='formfield'><input type='password' name='password_confirm' id='password_confirm'></input></div>" +
				"</div>");
		form.append("<div class='formblock'>" +
				"<div class='formlabel'>First name:</div>" +
				"<div class='formfield'><input type='text' name='firstname' id='firstname'></input></div>" +
				"</div>");
		form.append("<div class='formblock'>" +
				"<div class='formlabel'>Surname:</div>" +
				"<div class='formfield'><input type='text' name='surname' id='surname'></input></div>" +
				"</div>");
		form.append("<div class='ctrl'><input type='button' name='submit' value='Register' onclick='register()' class='button'></input></div>");
		$('#content').append(form);
		//data validation
		$('#register').validate({
			rules: {
				email: {
					required: true,
					email:true
				},
				password: {
					required: true,
					minlength: 6
				},
				password_confirm: {
					required: true,
					minlength: 6
				},
				firstname: {
					required: true
				},
				surname: {
					required: true
				}
			}
			
		});
	};
	
	this.showHome = function(){
		$('#content').empty();
		
		mQ.showMenu();
		
		var searchform = $('<div>').attr({'id':'search','class':'formblock'});
		searchform.append($('<div>').attr({'id':'searchtitle','class':'formlabel'}).text("Search quizzes:"));
		var ff = $('<div>').attr({'class':'formfield'});
		var sterms = $('<input>').attr({'id':'searchterms'});
		ff.append(sterms);
		searchform.append(ff);
		$('#content').append(searchform);
		
		var searchresults = $('<div>').attr({'id':'searchresults','class':'formblock'}); 
		$('#content').append(searchresults);
		
		var suggest = $('<div>').attr({'id':'suggest','class':'formblock'});
		suggest.append($('<div>').attr({'id':'suggesttitle','class':'formlabel'}).text("or try one of these:"));
		$('#content').append(suggest);
		var suggestresults = $('<div>').attr({'id':'suggestresults','class':'formblock'}); 
		$('#content').append(suggestresults);
		if(mQ.store.get('suggest')){
			var data = mQ.store.get('suggest');
			for(var q in data){		   
				mQ.addQuizListItem(data[q],'#suggestresults');
		   }
		} else {
			$('#suggestresults').append("Loading suggestions...");
		}
		
		$('#searchterms').keypress(function (event) {
				if (event.keyCode == '13'){
					mQ.doSearch();
				}
			});
	};

	this.showLocalQuizzes = function(){
		$('#content').empty();
		mQ.showMenu();
		var localQuizzes = $('<div>').attr({'id':'localq'}); 
		localQuizzes.append("Quizzes stored locally:");
		$('#content').append(localQuizzes);
		var qs = mQ.store.get('quizzes');
		for (var q in qs){
			mQ.addQuizListItem(qs[q],'#localq');
		}
		if(!qs || qs.length == 0){
			$(localQuizzes).append("<br/>No quizzes stored locally.");
		}
	};
	
	this.showLogin = function(hash){
		$('#content').empty();
		var str = "<h2>Login";
		if(this.opts.allowregister){
			str += "(or <a onclick='mQ.showRegister()'>Register</a>)";
		}
		str += "</h2>";
		$('#content').append(str);
		var form =  $('<div>');
		form.append("<div class='formblock'>" +
			"<div class='formlabel' name='lang' id='login_username'>Email:</div>" +
			"<div class='formfield'><input type='text' name='username' id='username'></input></div>" +
			"</div>");
		
		form.append("<div class='formblock'>"+
			"<div class='formlabel'name='lang' id='login_password'>Password:</div>" +
			"<div class='formfield'><input type='password' name='password' id='password'></input></div>" +
			"</div>");
		
		form.append("<div class='ctrl'><input type='button' name='submit' value='Login' onclick='mQ.login(\""+hash+"\")' class='button'></input></div>");
		$('#content').append(form);
	};
	
	this.showMenu = function(){
		if( this.opts.menu){
			var menu = $('#menu');
			if(menu.length == 0){
				menu = $('<div>').attr({'id':'menu'});
				$('#content').append(menu);
			}
			menu.empty();
			for(var i=0;i < this.opts.menu.length;i++){
				menu.append($('<a>').attr({'href':this.opts.menu[i].link}).text(this.opts.menu[i].title));
				if( i+1 < this.opts.menu.length){
					menu.append(' | ');
				}
			}
		}
	};
	
	this.showQuiz = function(qref){
		$('#content').empty();
		Q = new Quiz();
		Q.init(mQ.quizInCache(qref));
		
		var qhead = $('<div>').attr({'id':'quizheader'});
		$('#content').append(qhead);
		
		var qs = $('<div>').attr({'id':'qs'});
		$('#content').append(qs);
		
		var question = $('<div>').attr({'id':'question'});
		$('#qs').append(question);
		
		var response = $('<div>').attr({'id':'response'});
		$('#qs').append(response);
		
		var fb = $('<div>').attr({'id':'feedback'});
		$('#qs').append(fb);
		fb.hide();
		
		var quiznav = $('<div>').attr({'id':'quiznav'});
		var quiznavprev = $('<div>').attr({'class':'quiznavprev'}).append($('<input>').attr({'id':'quiznavprevbtn','type':'button','class':'button','value':'<< Prev'}));
		quiznav.append(quiznavprev);
		
		var quiznavnext = $('<div>').attr({'class':'quiznavnext'}).append($('<input>').attr({'id':'quiznavnextbtn','type':'button','class':'button','value':'Next >>'}));
		quiznav.append(quiznavnext);
		
		var clear = $('<div>').attr({'style':'clear:both'});
		$('#content').append(quiznav);
		Q.loadQuestion();
	};
	
	this.login = function(hash){
		var username = $('#username').val();
		var password = $('#password').val();
		if(username == '' || password == ''){
			alert("Please enter your username and password");
			return false;
		}
		
		$.ajax({
			   data:{'method':'login','username':username,'password':password}, 
			   success:function(data){
				   //check for any error messages
				   if(data.login){
					// save username and password
					   mQ.store.set('username',$('#username').val());
					   mQ.store.set('displayname',data.name);
					   mQ.store.set('password',data.hash);
					   mQ.store.set('lastlogin',Date());
					   mQ.showUsername();
					   mQ.showPage(hash);
					   mQ.onLogin();
				   } else {
					   alert('Login failed');
				   }
			   }, 
			   error:function(data){
				   alert("No connection available. You need to be online to log in.");
			   }
			});
		return false;
	};
	
	this.logout = function(force){
		if(force){
			mQ.store.clear();
			mQ.store.init();
			mQ.showUsername();
			mQ.onLogout();
		} else {
			var lo = confirm('Are you sure you want to log out?\n\nYou will need an active connection to log in again.');
			if(lo){
				mQ.inQuiz = false;
				mQ.store.clear();
				mQ.store.init();
				mQ.showUsername();
				mQ.onLogout();
			}
		}
	};

	this.showPage = function(hash){
		if(!mQ.loggedIn() && hash != '#register'){
			if(!hash){
				hash = '#home';
			}
			this.showLogin(hash);
			return;
		} 
		mQ.dataUpdate();
		$('#content').empty();
		if (hash == '#register'){
			mQ.showRegister();
		} else if (hash == '#login' && !mQ.loggedIn()){
			this.showLogin();
		} else if(hash.substring(0,3) == '#qt'){
			if(getUrlVars().preview){
				mQ.loadQuiz(hash.substring(1),true);
			} else {
				mQ.loadQuiz(hash.substring(1),false);
			}
		} else if (hash == '#quizzes'){
			mQ.showLocalQuizzes();
		}else if (hash == '#results'){
			mQ.showResults();
		}  else {
			this.inQuiz = false;
			this.showHome();
		}
	};
	
	this.showResults = function(){
		$('#content').empty();
		
		mQ.showMenu();
		var results = $('<div>').attr({'id':'results'}); 
		$('#content').append(results);
		var qs = mQ.store.get('results');
		if(qs && qs.length>0){
			var result = $('<div>').attr({'class':'th'});
			result.append($('<div>').attr({'class':'thrt'}).text("Quiz"));
			result.append($('<div>').attr({'class':'thrs'}).text("Score"));
			result.append($('<div>').attr({'class':'thrr'}).text("Rank"));
			result.append("<div style='clear:both'></div>");
			results.append(result);
		} else {
			results.append("You haven't taken any quizzes yet");
		}
		for (var q in qs){
			var result = $('<div>').attr({'class':'result'});
			var d = new Date(qs[q].quizdate);
			var str = qs[q].quiztitle + "<br/><small>"+dateFormat(d,'HH:MM d-mmm-yy')+"</small>";
			result.append($('<div>').attr({'class':'rest clickable','onclick':'document.location="#'+qs[q].qref +'"','title':'try this quiz again'}).html(str));
			result.append($('<div>').attr({'class':'ress'}).text((qs[q].userscore*100/qs[q].maxscore).toFixed(0)+"%"));
			result.append($('<div>').attr({'class':'resr'}).text(qs[q].rank));
			result.append("<div style='clear:both'></div>");
			results.append(result);
		}
	};
	
	this.doSearch = function(){
		var t = $('#searchterms').val().trim();
		if(t.length > 1){
			$('#searchresults').text('Searching...');
			$.ajax({
				   data:{'method':'search','t':t,'username':mQ.store.get('username'),'password':mQ.store.get('password')}, 
				   success:function(data){
					   //check for any error messages
					   if(data && !data.error){
						   $('#searchresults').empty();
						   if(data.length == 0){
							   $('#searchresults').append('No results found.');
						   } 
						   for(var q in data){
							   mQ.addQuizListItem(data[q],'#searchresults');
						   }
					   }
				   },
				   error:function(data){
					   $('#searchresults').empty();
					   alert("Connection timeout or no connection available. You need to be online to search.");
				   }
				});
		}
	};
	
	this.showLoading = function(msg){
		var l = $('<div>').attr({'id':'loading'}).html("Loading "+msg+"...");
		$('#content').append(l);
	};

	this.loggedIn = function(){
		if(mQ.store.get('username') == null){
			mQ.showLogin();
			return false;
		} 
		return true;
	};
	
	this.dataUpdate = function(){
		if(!mQ.loggedIn()){
			return;
		}
		// check when last update made, return if too early
		var now = new Date();
		var lastupdate = new Date(mQ.store.get('lastupdate'));
		if(lastupdate > now.addMins(-DATA_CACHE_EXPIRY)){
			return;
		} 

		// send any unsubmitted responses
		var unsent = mQ.store.get('unsentresults');
		
		if(unsent){
			for(var u in unsent){
				$.ajax({
					   data:{'method':'submit','username':mQ.store.get('username'),'password':mQ.store.get('password'),'content':unsent[u]}, 
					   success:function(data){
						   
						 //check for any error messages
						   if(data && !data.error){
							   unsent[u].rank = data.rank;
							   mQ.store.addArrayItem('results',unsent[u]);
							   mQ.store.set('lastupdate',Date());
							   mQ.store.clearKey('unsentresults');
						   }
						   
					   }, 
					   error:function(data){
						   // do nothing - will send on next update
					   }
					});
			}
		}
		
		// update suggestions
		$.ajax({
			   data:{'method':'suggest','username':mQ.store.get('username'),'password':mQ.store.get('password')}, 
			   success:function(data){
				   if(data && !data.error){
					   mQ.store.clearKey('suggest');
					   for(var q in data){
						   mQ.store.addArrayItem('suggest',data[q]);
					   }
					   mQ.store.set('lastupdate',Date());
					   if($('#suggestresults')){
						   $('#suggestresults').empty();
						   var data = mQ.store.get('suggest');
						   for(var q in data){
							   mQ.addQuizListItem(data[q],'#suggestresults');
						   }
					   }
				   }
			   },
			   error:function(data){
				   // do nothing - run on next update
			   }
			});
	};
	
	this.register = function(){
		
		var username = $('#email').val();
		var password = $('#password').val();
		var passwordAgain = $('#password_confirm').val();
		var firstname = $('#firstname').val();
		var lastname = $('#surname').val();
		
		//check passwords match
		if(password != passwordAgain){
			alert('Please check the passwords match');
			return;
		}
		
		if(!$('#register').valid()){
			alert('Please check you have fully completed the form');
			return;
		}
		
		$('#register').hide();
		
		$.ajax({
			   data:{'method':'register','username':username,'password':password,'passwordagain':passwordAgain,'firstname':firstname,'lastname':lastname}, 
			   success:function(data){
				   //check for any error messages
				   if(data.login){
					// save username and password
					   mQ.store.set('username',$('#email').val());
					   mQ.store.set('displayname',data.name);
					   mQ.store.set('password',data.hash);
					   mQ.store.set('lastlogin',Date());
					   mQ.showUsername();
					   var hash = $(location).attr('hash');
					   mQ.showPage(hash);
				   } else if(data.error) {
					   $('#loading').hide();
					   $('#register').show();
					   alert(data.error);
				   } else {
					   alert('An error occurred, please try again.');
					   $('#loading').hide();
					   $('#register').show();
				   }
			   }, 
			   error:function(data){
				   alert("No connection available. You need to be online to register.");
				   $('#loading').hide();
				   $('#register').show();
			   }
			});
		
		
	};
	
	this.showUsername = function(){
		$('#logininfo').empty();
		if(mQ.store.get('displayname') != null){
			$('#logininfo').text(mQ.store.get('displayname') + " ");
		} 
		if(mQ.store.get('username') != null){
			$('#logininfo').append("<a onclick='mQ.logout()' name='lang' id='logout'>Logout</a>");
		}
	};
	
	this.cacheQuiz = function(qref){
		// check is already cached
		if(!mQ.quizInCache(qref)){
			$.ajax({
				   data:{'method':'getquiz','username':mQ.store.get('username'),'password':mQ.store.get('password'),'qref':qref}, 
				   success:function(data){
					   if(data && !data.error){
						   mQ.store.addArrayItem('quizzes', data);
					   }
				   }, 
				});
		}
	};
	
	this.addQuizListItem = function(q,list){
		var ql= $('<div>').attr({'class':'quizlist clickable','onclick':'document.location="#'+q.qref +'"'});
		var quiz = $('<span>').attr({'class':'quiztitle'});
		quiz.append(q.quiztitle);
		$(list).append(ql.append(quiz));
		if(q.description != null && q.quizdescription != ""){
			var desc = $("<span>").attr({'class':'quizdesc'});
			desc.text(" - " + q.quizdescription);
			ql.append(desc);
		}
	};
	
	this.quizInCache = function(qref){
		var qs = mQ.store.get('quizzes');
		for(var q in qs){
			if (qs[q].qref == qref){
				return qs[q];
			}
		}
		return false;
	};
}




function Store(){
	
	this.init = function(){
		if (!localStorage) {
			localStorage.setItem('username', null);
			localStorage.setItem('password', null);
			localStorage.setItem('lang', 'EN');
			localStorage.setItem('quizzes', null);
			localStorage.setItem('results', null);
		}
	}
	
	this.get = function(key){
		var value = localStorage.getItem(key);
	    return value && JSON.parse(value);
	}
	
	this.set = function(key,value){
		localStorage.setItem(key,JSON.stringify(value));
	}
	
	this.clear = function(){
		localStorage.clear();
	}
	
	this.clearKey = function(key){
		this.set(key,null);
	}
	
	this.addArrayItem = function(key,value){
		//get current array
		var c = this.get(key);
		//var count = 0;
		if(!c){
			c = [];
		} 
		c.unshift(value);
		this.set(key,c);
	}
	
}


function Quiz(){
	
	this.quiz = null;
	this.currentQuestion = 0;
	this.responses = [];
	this.matchingstate = [];
	this.matchingopt = [];
	this.feedback = "";
	this.opts = {};
	
	this.init = function(q,opts){
		this.quiz = q;
		mQ.inQuiz = true;
		this.opts = opts;
	}
	
	this.setHeader = function(){
		$('#quizheader').html(this.quiz.quiztitle + " Q" +(this.currentQuestion+1) + " of "+ this.quiz.q.length);
	}
	
	this.loadNextQuestion = function(){
		if(this.saveResponse('next')){
			if(this.feedback != ""){
				$('#question').hide();
				$('#response').hide();
				$('#feedback').empty();
				$('#feedback').append("<h2>Feedback</h2><div id='fbtext'>"+this.feedback+"</div>");
				$('#feedback').show('blind',{},500);
				$('#quiznavnextbtn').unbind('click');
				if(this.currentQuestion+1 == this.quiz.q.length){
					$('#quiznavnextbtn').bind('click',function(){
						Q.showResults();
					});
				} else {
					$('#quiznavnextbtn').bind('click',function(){
						Q.currentQuestion++;
						Q.loadQuestion();
					});
				}
			} else {
				if(this.currentQuestion+1 == this.quiz.q.length){
					Q.showResults();
				} else {
					this.currentQuestion++;
					this.loadQuestion();
				}
			}

		} else {
			alert("Please answer this question before continuing.");
		}
	}
	
	this.loadPrevQuestion = function(){
		this.saveResponse('prev')
		this.currentQuestion--;
		this.loadQuestion();
	}
	
	this.loadQuestion = function(){
		this.setHeader();
		this.setNav();
		this.feedback = "";

		$('#question').html(this.quiz.q[this.currentQuestion].text);
		this.loadResponses(this.quiz.q[this.currentQuestion]);
		$('#feedback').hide();
		$('#question').show('blind',{},500);
		$('#response').show('blind',{},500);
	}
	
	this.loadResponses = function(q){
		if(q.type == 'multichoice'){
			this.loadMultichoice(q.r);
		} else if (q.type == 'shortanswer'){
			this.loadShortAnswer();
		} else if (q.type == 'matching'){
			this.loadMatching(q.r);
		} else if (q.type == 'numerical'){
			this.loadNumerical(q.r);
		} else if (q.type == 'essay'){
			this.loadEssay();
		} else if (q.type == 'multiselect'){
			this.loadMultiselect(q.r);
		} else if (q.type == 'info'){
			this.loadInfo();
		} else {
			$('#response').empty();
		}
	}
	
	this.loadMultichoice = function(resp){
		$('#response').empty();
		
		$(function(){
			for(var i=0; i< resp.length; i++){
				(function(r){
					var d = $('<div>').attr({'class':'mcresponse','id':'div'+r.refid}).click(function(event){
						$('#'+r.refid).attr({'checked':'checked'});
						//remove class from all other responses
						var t = Q.quiz.q[Q.currentQuestion].r;
						for(var j in t){
							$('#div'+t[j].refid).removeClass('selected');
						}
						$(this).addClass('selected');
					});
					var l = $('<label>').attr({'for':r.refid});
					var o = $('<input>').attr({'type':'radio','value':r.refid,'name':'response','id':r.refid});
					if(Q.responses[Q.currentQuestion] && Q.responses[Q.currentQuestion].qrtext == r.text){
						o.attr({'checked':'checked'});
						d.addClass('selected');
					}
					l.append(o);
					l.append(r.text);
					d.append(l);
					
					$('#response').append(d);
				})(resp[i]);
			}
		});
	}
	
	this.loadMultiselect = function(resp){
		$('#response').empty();
		
		$(function(){
			for(var i=0; i< resp.length; i++){
				(function(r){
					
					var od = $('<div>').attr({'class':'od','id':'div'+r.refid});
					var mss = $('<div>').attr({'class':'mss'});
					var o = $('<input>').attr({'type':'checkbox','value':r.refid,'name':'mcresponse','id':r.refid});
					o.click(function(event){
						if ($('#'+r.refid).is(':checked')) {
							$('#div'+r.refid).addClass('selected');
					    } else {
					    	$('#div'+r.refid).removeClass('selected');
					    }
					});
					mss.append(o);
					od.append(mss);
					
					var mst = $('<div>').attr({'class':'mst'}).text(r.text);
					mst.click(function(event){
						if ($('#'+r.refid).is(':checked')) {
							$('#'+r.refid).removeAttr('checked');
							$('#div'+r.refid).removeClass('selected');
					    } else {
					    	$('#'+r.refid).attr({'checked':'checked'});
					    	$('#div'+r.refid).addClass('selected');
					    }
					});
					od.append(mst);
					od.append("<div style='clear:both'></div>");
					
					if(Q.responses[Q.currentQuestion]){
						var sel = Q.responses[Q.currentQuestion].qrtext.split('|');
						for(var i in sel){
							if(sel[i] == r.text){
								o.attr({'checked':'checked'});
								od.addClass('selected');
							}
						}
					}					
					$('#response').append(od);
				})(resp[i]);
			}
		});
	}
	
	this.loadInfo = function(){
		$('#response').empty();
	}
	
	this.loadShortAnswer = function(){
		$('#response').empty();
		var o = $('<input>').attr({'type':'text','name':'response','id':'shortanswerresponse','class':'responsefield'});
		if(this.responses[this.currentQuestion]){
			o.attr({'value':this.responses[this.currentQuestion].qrtext});
		}
		$('#response').append(o);
	}
	
	this.loadMatching = function(resp){
		$('#response').empty();
		
		this.matchingstate = [];
		this.matchingopt = [];
		
		for(var r in resp){
			var t = resp[r].text.split('-&gt;');
			if(t[0].trim() != ''){
				this.matchingstate[r] = t[0].trim();
			}
			if(t[1].trim() != ''){
				this.matchingopt[r] = t[1].trim();
			}
		}
		
		var curresp = [];
		if(this.responses[this.currentQuestion]){
			curresp = this.responses[this.currentQuestion].qrtext.split('|');
		}
		
		for(var s in this.matchingstate){
			var d = $('<div>').attr({'class':'response'});
			var st = $('<span>').attr({'class':'matchingstate','name':'matching','id':'matchingstate'+s}).text(this.matchingstate[s]);
			d.append(st);
			
			var sel = $('<select>').attr({'class':'matchingopt','name':'matching','id':'matchingopt'+s}).append($('<option>'));
			for(var o in this.matchingopt){
				var ot = $('<option>').text(this.matchingopt[o]);
				// find if a current response for this answer
				for(var i in curresp){
					var r = curresp[i].split('-&gt;');
					if(r[0].trim() == this.matchingstate[s] && r[1].trim() == this.matchingopt[o]){
						ot.attr({'selected':'selected'});
					}
				}
				sel.append(ot);
			}
			d.append(sel);
			$('#response').append(d);
			$('#response').append('<div style="clear:both;"></div>');
		}
	}
	
	this.loadNumerical = function(){
		$('#response').empty();
		var o = $('<input>').attr({'type':'text','name':'response','id':'numericalresponse','class':'responsefield'});
		if(this.responses[this.currentQuestion]){
			o.attr({'value':this.responses[this.currentQuestion].qrtext});
		}
		$('#response').append(o);
	}
	
	this.loadEssay = function(){
		$('#response').empty();
		var o = $('<textarea>').attr({'type':'text','name':'response','id':'essayresponse','class':'responsefield'});
		if(this.responses[this.currentQuestion]){
			o.text(this.responses[this.currentQuestion].qrtext);
		}
		$('#response').append(o);
	}
	
	this.saveResponse = function(nav){
		var q = this.quiz.q[this.currentQuestion];
		if(q.type == 'multichoice'){
			return this.saveMultichoice(nav);
		} else if(q.type == 'shortanswer'){
			return this.saveShortAnswer(nav);
		} else if(q.type == 'matching'){
			return this.saveMatching(nav);
		} else if(q.type == 'numerical'){
			return this.saveNumerical(nav);
		} else if(q.type == 'essay'){
			return this.saveEssay(nav);
		} else if(q.type == 'multiselect'){
			return this.saveMultiselect(nav);
		} else if(q.type == 'info'){
			return this.saveInfo(nav);
		} else {
			
		}
	}
	
	this.saveMultichoice = function(nav){
		var opt = $('input[name=response]:checked').val();
		if(opt){
			var o = Object();
			var q = this.quiz.q[this.currentQuestion];
			o.qid = q.refid;
			o.score = 0;
			o.qrtext = "";
			// mark question and get text
			for(var r in q.r){
				if(q.r[r].refid == opt){
					o.score = q.r[r].score;
					o.qrtext = q.r[r].text;
					// set feedback (if any)
					if (q.r[r].props.feedback && q.r[r].props.feedback != ''){
						this.feedback = q.r[r].props.feedback;
					}
				}
			}
			o.score = Math.min(o.score,parseFloat(q.props.maxscore));
			this.responses[this.currentQuestion] = o;

			return true;
		} else {
			if(nav == 'next'){
				return false;
			} else {
				return true;
			}	
		}
	}
	
	this.saveShortAnswer = function(nav){
		var ans = $('#shortanswerresponse').val().trim();
		if(ans != ''){
			var o = Object();
			var q = this.quiz.q[this.currentQuestion];
			o.qid = q.refid;
			o.score = 0;
			o.qrtext = ans;
			// mark question and get text
			for(var r in q.r){
				if(q.r[r].text == ans){
					o.score = q.r[r].score;
					// set feedback (if any)
					if (q.r[r].props.feedback && q.r[r].props.feedback != ''){
						this.feedback = q.r[r].props.feedback;
					}
				}
			}
			o.score = Math.min(o.score,parseFloat(q.props.maxscore));
			this.responses[this.currentQuestion] = o;

			return true;
		} else {
			if(nav == 'next'){
				return false;
			} else {
				return true;
			}	
		}
	}
	
	this.saveInfo = function(nav){
		return true;
	}
	
	this.saveMatching = function(nav){
		//check an answer given for all options
		for(var s in this.matchingstate){
			if($('#matchingopt'+s+' :selected').text() == ''){
				if(nav == 'next'){
					return false;
				} else {
					return true;
				}
			}
		}
		//now mark and save the answers
		var o = Object();
		var q = this.quiz.q[this.currentQuestion];
		o.qid = q.refid;
		o.score = 0;
		o.qrtext = '';
		for(var s in this.matchingstate){
			var resp = this.matchingstate[s] + " -&gt; " +  $('#matchingopt'+s+' :selected').text();
			for(var r in q.r){
				if(q.r[r].text == resp){
					o.score += parseFloat(q.r[r].score);
				}
			}
			o.qrtext += resp + "|";
			
		}
		o.score = Math.min(o.score,parseFloat(q.props.maxscore));
		this.responses[this.currentQuestion] = o;
		return true;
	}
	

	this.saveNumerical = function(nav){
		var ans = $('#numericalresponse').val().trim();
		if(ans != ''){
			var o = Object();
			var q = this.quiz.q[this.currentQuestion];
			o.qid = q.refid;
			o.score = 0;
			o.qrtext = ans;
			var bestans = -1;
			// mark question and get text
			for(var r in q.r){
				if(parseFloat(q.r[r].text) - parseFloat(q.r[r].props.tolerance) <= ans && ans <= parseFloat(q.r[r].text) + parseFloat(q.r[r].props.tolerance) ){
					if(parseFloat(q.r[r].score) > parseFloat(o.score)){
						o.score = q.r[r].score;
						bestans = r;
					}
				}
			}
			if(bestans != -1){
				o.score = q.r[bestans].score;
				// set feedback (if any)
				if (q.r[bestans].props.feedback && q.r[bestans].props.feedback != ''){
					this.feedback = q.r[bestans].props.feedback;
				}
			}
			
			o.score = Math.min(o.score,parseFloat(q.props.maxscore));
			this.responses[this.currentQuestion] = o;
			
			return true;
		} else {
			if(nav == 'next'){
				return false;
			} else {
				return true;
			}	
		}
	}
	
	this.saveEssay = function(nav){
		var ans = $('#essayresponse').val().trim();
		if(ans != ''){
			var o = Object();
			var q = this.quiz.q[this.currentQuestion];
			o.qid = q.refid;
			o.score = 0;
			o.qrtext = ans;
			// mark question and get text
			for(var r in q.r){
				if(q.r[r].text == ans){
					o.score = q.r[r].score;
					// set feedback (if any)
					if (q.r[r].props.feedback && q.r[r].props.feedback != ''){
						this.feedback = q.r[r].props.feedback;
					}
				}
			}
			o.score = Math.min(o.score,parseFloat(q.props.maxscore));
			this.responses[this.currentQuestion] = o;

			return true;
		} else {
			if(nav == 'next'){
				return false;
			} else {
				return true;
			}	
		}
	}
	
	this.saveMultiselect = function(nav){
		var q = this.quiz.q[this.currentQuestion];
		var c = false;
		for(var r in q.r){
			if($('#'+q.r[r].refid).attr('checked')){
				c = true;
			}
		}
		if(!c){
			if(nav == 'next'){
				return false;
			} else {
				return true;
			}
		}
		var o = Object();
		o.qid = q.refid;
		o.score = 0;
		o.qrtext = "";
		var countsel = 0;
		// mark question and get text
		for(var r in q.r){
			if($('#'+q.r[r].refid).attr('checked')){
				o.score += parseFloat(q.r[r].score);
				o.qrtext += q.r[r].text + "|";
				countsel++;
				if(q.r[r].props.feedback != ""){
					this.feedback += q.r[r].text+": "+ q.r[r].props.feedback + "<br/>";
				}
			}
		}
		//set score back to 0 if any incorrect options selected
		for(var r in q.r){
			if($('#'+q.r[r].refid).attr('checked') && parseFloat(q.r[r].score) == 0){
				o.score = 0;
			}
		}
		o.score = Math.min(o.score,parseFloat(q.props.maxscore));
		this.responses[this.currentQuestion] = o;
		
		return true;
	}
	
	this.showResults = function(){
		if(!this.saveResponse('next')){
			alert("Please answer this question before getting your results.");
			return;
		} 
		
		mQ.inQuiz = false;
		$('#content').empty();
		
		$('#content').append("<h2 name='lang' id='page_title_results'>Your results for:<br/> '"+ this.quiz.quiztitle +"':</h2>");
		// calculate score
		var total = 0;
		for(var r in this.responses){
			total += this.responses[r].score;
		}
		total = Math.min(total,this.quiz.maxscore);
		if(this.quiz.maxscore > 0){
			var percent = total*100/this.quiz.maxscore;
		} else {
			var percent = 0;
		}
		$('#content').append("<div id='quizresults'>"+ percent.toFixed(0) +"%</div>");
		
		var rank = $('<div>').attr({'id':'rank','class': 'rank'});
		$('#content').append(rank);
		rank.hide();
		
		var next = $('<div>').attr({'id':'next','class': 'next centre'});
		$('#content').append(next);
		next.hide();
		
		var d = $('<div>').attr({'class': 'resultopt clickable centre'});
		var l = $('<a>').text("Retake '"+ this.quiz.quiztitle +"'");
		d.append(l);
		var qref = this.quiz.qref;
		l.click(function(){
			mQ.loadQuiz(qref,false);
		});
		$('#content').append(d);
		
		if(mQ.opts.finallinks){
			for(var i in mQ.opts.finallinks){
				var d = $('<div>').attr({'class': 'resultopt clickable centre'});
				var l = $('<a>').attr({'href': mQ.opts.finallinks[i].link}).text(mQ.opts.finallinks[i].title);
				d.append(l);
				$('#content').append(d);
			}
			
		}
	
		//save for submission to server
		var content = Object();
		content.qref = this.quiz.qref;
		content.username = mQ.store.get('username');
		content.maxscore = this.quiz.maxscore;
		content.userscore = total;
		content.quizdate = Date.now();
		content.responses = this.responses;
		content.quiztitle = this.quiz.quiztitle;
	
		$.ajax({
		   data:{'method':'submit','username':mQ.store.get('username'),'password':mQ.store.get('password'),'content':JSON.stringify(content)}, 
		   success:function(data){
			   //check for any error messages
			   if(!data || data.error){
				   mQ.store.addArrayItem('unsentresults',content);
			   } else {
				   content.rank = data.rank;
				   mQ.store.addArrayItem('results', content);
				   // show ranking 
				   if($('#rank') && data.rank){
					   $('#rank').empty();
					   $('#rank').append("Your ranking: " + data.rank);
					   $('#rank').show();
				   }
				   if($('#next') && data.next){
					   if(data.next.length > 0){
						   $('#next').empty();
						   $('#next').append("We suggest you take '<a href='#"+ data.next[0].quizref+"'>"+ data.next[0].title+"</a>' next");
						   $('#next').show('blind');
					   }
				   }
			   }
		   }, 
		   error:function(data){
			   mQ.store.addArrayItem('unsentresults',content);
		   }
		});		
	}
	
	this.setNav = function(){
		$('#quiznavprevbtn').unbind('click');
		$('#quiznavprevbtn').bind('click',function(event){
			Q.loadPrevQuestion();
		});
		if(this.currentQuestion == 0){
			$('#quiznavprevbtn').attr('disabled', 'disabled');
		} else {
			$('#quiznavprevbtn').removeAttr('disabled');
		}
		
		$('#quiznavnextbtn').unbind('click');
		if(this.currentQuestion+1 == this.quiz.q.length){
			$('#quiznavnextbtn').attr({'value':'Get results'});
		} else {
			$('#quiznavnextbtn').attr({'value':'Next >>'});
			
		}
		$('#quiznavnextbtn').bind('click',function(){
			Q.loadNextQuestion();
		});
	}
	
}

Date.prototype.addMins= function(m){
    this.setTime(this.getTime() + (m*60000));
    return this;
}

Date.prototype.addHours= function(h){
    this.setHours(this.getHours()+h);
    return this;
}

Date.prototype.addDays= function(d){
    this.setDate(this.getDate()+d);
    return this;
}

function getUrlVars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}