//counter code - used to display time entries in db, and other stats
/*
function updateStats(){
	if (!isNaN(totalUsersRate) && !isNaN(paidUsersRate) && totalUsersRate > 0 && paidUsersRate > 0){
		totalUsers += totalUsersRate;
		paidUsers += paidUsersRate;
		countTo($('.total_users'), addThousandSeparator(parseInt(totalUsers)));
		countTo($('.paid_users'), addThousandSeparator(parseInt(paidUsers)));
	} else {
		return false;
	}
}


var oldCodeLength = 0;
function formatCode(el){
	newVal = "";
	for(var i = 0; i < el.val().length; i++){
		if (newVal.length >= 14) break;
		if (!el.val()[i].match(/[\s-]/))
			newVal += el.val()[i];
		if (newVal.length < 13 && (newVal.length + 1) % 5 === 0){
			newVal += "-";
		}
	}
	el.val(newVal);
}
function countTo(el, val){
	if (el.text().length != val.length){
		el.text(val);
		el.css('width', el.width() + 'px').css('display', 'inline-block');
		return false;
	}
	var digits = el.text().split('');
	el.css('width', el.width() + 'px').css('display', 'inline-block');
	el.html("");
	var offset = [];
	var digitEles = [];
	for(var i in digits){
		var digit = $("<span></span>").text(digits[i]).appendTo(el);
		offset.push(digit.position().left);
		digitEles.push(digit);
	}
	for(i in digitEles){
		digitEles[i].css({
			top: 0,
			left: offset[i] + "px",
			position: 'absolute'
		});
	}
	var newDigits = val.split('');
	for(i in newDigits){
		if (newDigits[i] != digits[i]){
			var newDigit = $('<span></span>').text(newDigits[i]).appendTo(el);
			newDigit.css({
				top: "-10px",
				left: offset[i] + "px",
				position: 'absolute'
			});
			newDigit.animate({
				top: '+=10',
				opacity: 1.0
			}, 200), function(){
				el.html(val);
			}
			digitEles[i].animate({
				top: '+=10',
				opacity: 0.0
			}, 200, function(){
				$(this).remove();
			});
		}
	}
}
function addThousandSeparator(nStr){
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)){
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1;
}
*/

//signup stuff
var fName = document.getElementById('fName');
var lName = document.getElementById('lName');
var signupTeamNum = document.getElementById('signupTeamNum');
var previewScoutID = document.getElementById('previewScoutID');
var signupPassword = document.getElementById('signupPassword');
var email = document.getElementById('email');


function displayScoutID(){
	setTimeout(function(){
		var fNameV = fName.value;
		var lNameV = lName.value;
		var signupTeamNumV = signupTeamNum.value;

		if (fNameV !== '' && lNameV !== '' && signupTeamNumV !== ''){
			previewScoutID.value = fNameV.toLowerCase().titleCase() + lNameV.toLowerCase().titleCase() + "-" + signupTeamNumV;
		} else {
			previewScoutID.value = '';
		}
	}, 1);//timeout used because key hasn't been pressed yet
}

function checkSignup(filter){//return true if ok, return false if bad
	if (filter == 'fName' || filter == 'all'){

		if(fName.value === ''){
			highlight('error', fName);
			return false;
		} else {
			highlight('correct', fName);

			if (filter != 'all'){
				return true;
			}
		}
	}
	if (filter == 'lName' || filter == 'all'){

		if(lName.value === ''){
			highlight('error', lName);
			return false;
		} else {
			highlight('correct', lName);

			if (filter != 'all'){
				return true;
			}
		}

	}
	return true;
}

//TODO: get some code to highlight bad inputs & pulse submit button when form is good

function postSignup(){//this function will interfere with logged in users... signup must not be visible if logged in
/*
	if (checkSignup('all') == false){
		return;//jGrowl messages & highlighting deal with bad inputs
	}
	*/
	//put all info in user object, scoutid is made server-side
	user.info = {
		"fName": fName.value,
		"lName": lName.value,
		"team": signupTeamNum.value
	};
	user.account = {
		"pword": signupPassword.value,
		"email": email.value
	};

	bakeCookie('user', $.toJSON(user));
	post('signup.php','');//all data is in cookie
	logout();//remove stuff from cookie & user object (in a standard way)
}