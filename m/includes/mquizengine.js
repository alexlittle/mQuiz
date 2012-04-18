var Q = null;

var inQuiz = false;

function Quiz(){
	
	this.quiz = null;
	this.currentQuestion = 0;
	this.responses = [];
	this.matchingstate = [];
	this.matchingopt = [];
	
	this.init = function(q){
		this.quiz = q;
		inQuiz = true;
	}
	
	this.setHeader = function(){
		$('#quizheader').html(this.quiz.title + " Q" +(this.currentQuestion+1) + " of "+ this.quiz.q.length);
	}
	
	this.loadNextQuestion = function(){
		if(this.saveResponse('next')){
			this.currentQuestion++;
			this.loadQuestion();
		} else {
			alert("You must answer this question before continuing.");
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
		$('#question').html(this.quiz.q[this.currentQuestion].text);
		this.loadResponses(this.quiz.q[this.currentQuestion]);
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
			var feedback = null;
			// mark question and get text
			for(var r in q.r){
				if(q.r[r].refid == opt){
					o.score = q.r[r].score;
					o.qrtext = q.r[r].text;
					if (q.r[r].props.feedback && q.r[r].props.feedback != ''){
						feedback = q.r[r].props.feedback;
					}
				}
			}
			o.score = Math.min(o.score,parseInt(q.props.maxscore));
			this.responses[this.currentQuestion] = o;
			
			// show feedback (if any)
			if(feedback){
				alert("Feedback: "+feedback);
			}
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
			var feedback = null;
			// mark question and get text
			for(var r in q.r){
				if(q.r[r].text == ans){
					o.score = q.r[r].score;
					if (q.r[r].props.feedback && q.r[r].props.feedback != ''){
						feedback = q.r[r].props.feedback;
					}
				}
			}
			o.score = Math.min(o.score,parseInt(q.props.maxscore));
			this.responses[this.currentQuestion] = o;
			
			// show feedback (if any)
			if(feedback){
				alert("Feedback: "+feedback);
			}
			return true;
		} else {
			if(nav == 'next'){
				return false;
			} else {
				return true;
			}	
		}
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
		var feedback = null;
		for(var s in this.matchingstate){
			var resp = this.matchingstate[s] + " -&gt; " +  $('#matchingopt'+s+' :selected').text();
			for(var r in q.r){
				if(q.r[r].text == resp){
					o.score += parseInt(q.r[r].score);
				}
			}
			o.qrtext += resp + "|";
			
		}
		o.score = Math.min(o.score,parseInt(q.props.maxscore));
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
			var feedback = null;
			var bestans = -1;
			// mark question and get text
			for(var r in q.r){
				if(parseFloat(q.r[r].text) - parseFloat(q.r[r].props.tolerance) <= ans && ans <= parseFloat(q.r[r].text) + parseFloat(q.r[r].props.tolerance) ){
					if(parseInt(q.r[r].score) > parseInt(o.score)){
						o.score = q.r[r].score;
						bestans = r;
					}
				}
			}
			if(bestans != -1){
				o.score = q.r[bestans].score;
				if (q.r[bestans].props.feedback && q.r[bestans].props.feedback != ''){
					feedback = q.r[bestans].props.feedback;
				}
			}
			
			o.score = Math.min(o.score,parseInt(q.props.maxscore));
			this.responses[this.currentQuestion] = o;
			
			// show feedback (if any)
			if(feedback){
				alert("Feedback: "+feedback);
			}
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
			var feedback = null;
			// mark question and get text
			for(var r in q.r){
				if(q.r[r].text == ans){
					o.score = q.r[r].score;
					if (q.r[r].props.feedback && q.r[r].props.feedback != ''){
						feedback = q.r[r].props.feedback;
					}
				}
			}
			o.score = Math.min(o.score,parseInt(q.props.maxscore));
			this.responses[this.currentQuestion] = o;
			
			// show feedback (if any)
			if(feedback){
				alert("Feedback: "+feedback);
			}
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
		var feedback = null;
		var countsel = 0;
		// mark question and get text
		for(var r in q.r){
			if($('#'+q.r[r].refid).attr('checked')){
				o.score += parseInt(q.r[r].score);
				o.qrtext += q.r[r].text + "|";
				countsel++;
			}
			// TODO add feedback
		}
		//set score back to 0 if any incorrect options selected
		for(var r in q.r){
			if($('#'+q.r[r].refid).attr('checked') && parseInt(q.r[r].score) == 0){
				o.score = 0;
			}
		}
		o.score = Math.min(o.score,parseInt(q.props.maxscore));
		this.responses[this.currentQuestion] = o;
		
		return true;
	}
	
	this.showResults = function(){
		if(!this.saveResponse('next')){
			alert("You must answer this question before getting your results.");
			return;
		} 
		inQuiz = false;
		$('#content').empty();
		$('#content').append("<h2 name='lang' id='page_title_results'>Your results for:<br/> '"+ this.quiz.title +"':</h2>");
		// calculate score
		var total = 0;
		for(var r in this.responses){
			total += this.responses[r].score;
		}
		total = Math.min(total,this.quiz.maxscore);
		var percent = total*100/this.quiz.maxscore;
		$('#content').append("<div id='quizresults'>"+ percent.toFixed(0) +"%</div>");
		
		var rank = $('<div>').attr({'id':'rank','class': 'rank'});
		$('#content').append(rank);
		rank.hide();
		
		var retake = $('<div>').attr({'class': 'resultopt clickable centre'}).append('Take this quiz again');
		$('#content').append(retake);
		var refid = this.quiz.refid;
		retake.click(function(){
			loadQuiz(refid,false);
		});
		
		var takeAnother = $('<div>').attr({'class': 'resultopt clickable centre'}).append('Take another quiz');
		$('#content').append(takeAnother);
		takeAnother.click(function(){
			document.location = "#home";
		});
		
		var viewResults = $('<div>').attr({'class': 'resultopt clickable centre'}).append('View all recent results');
		$('#content').append(viewResults);
		viewResults.click(function(){
			document.location = "#results";
		});
		
		//save for submission to server
		var content = Object();
		content.quizid = this.quiz.refid;
		content.username = store.get('username');
		content.maxscore = this.quiz.maxscore;
		content.userscore = total;
		content.quizdate = Date.now();
		content.responses = this.responses;
		content.title = this.quiz.title;
	
		$.ajax({
		   data:{'method':'submit','username':store.get('username'),'password':store.get('password'),'content':JSON.stringify(content)}, 
		   success:function(data){
			   //check for any error messages
			   if(!data || data.error){
				   store.addArrayItem('unsentresults',content);
			   } else {
				   content.rank = data.rank;
				   store.addArrayItem('results', content);
				   // show ranking 
				   if($('#rank') && data.rank){
					   $('#rank').empty();
					   $('#rank').append("Your ranking: " + data.rank);
					   $('#rank').show();
				   }
			   }
		   }, 
		   error:function(data){
			   store.addArrayItem('unsentresults',content);
		   }
		});		
	}
	
	this.setNav = function(){
		if(this.currentQuestion == 0){
			$('#quiznavprevbtn').attr('disabled', 'disabled');
		} else {
			$('#quiznavprevbtn').removeAttr('disabled');
		}
		if(this.currentQuestion+1 == this.quiz.q.length){
			$('#quiznavnextbtn').attr({'onclick':'Q.showResults()','value':'Get results'});
		} else {
			$('#quiznavnextbtn').attr({'onclick':'Q.loadNextQuestion()','value':'Next >>'});
		}
	}
	
}