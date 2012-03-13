var fName = document.getElementById('fName');
var lName = document.getElementById('lName');
var signupTeamNum = document.getElementById('signupTeamNum');
var previewScoutID = document.getElementById('previewScoutID');
var signupPassword = document.getElementById('signupPassword');
var email = document.getElementById('email');


function displayScoutID() {
	setTimeout(function(){
		var fNameV = fName.value;
		var lNameV = lName.value;
		var signupTeamNumV = signupTeamNum.value;

		if (fNameV != '' && lNameV != '' && signupTeamNumV != '') {
			previewScoutID.value = fNameV.toLowerCase().titleCase() + lNameV.toLowerCase().titleCase() + "-" + signupTeamNumV;
		} else {
			previewScoutID.value = '';
		}
	}, 1);//timeout used because key hasn't been pressed yet
}

function checkSignup(filter) {//return true if ok, return false if bad
	if (filter == 'fName' || filter == 'all') {

		if(fName.value == ''){
			highlight('error', fName);
			return false;
		} else {
			highlight('correct', fName);

			if (filter != 'all'){
				return true;
			}
		}
	}
	if (filter == 'lName' || filter == 'all') {

		if(lName.value == ''){
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
/*
function highlight(condition, element){
	//pulse, clear, error, correct
	if(condition == 'clear'){
		element.style. 
	} else if(condition == 'error') {
		
	} else {
		
	}
	element.style
}
*/
var pulseCallBack = {};
function pulse(elementID, setting){//setting is bool, true=pulse & false=stop
	//this will only work on one element at a time because pulseElement & startTime will get changed TODO: fix this
	pulseElement = document.getElementById(elementID);
	if(setting == true){
		startTime = new Date();
		
		pulseCallBack[elementID] = setInterval(function(){
			var opacity = Math.round(Math.sin((new Date() - startTime)/500)*10)*0.05+0.5;
			var spread = opacity*2+1;
			opacity = opacity*0.9 + 0.1;
			pulseElement.style.boxShadow = '0 0 ' + spread + 'px ' + spread + 'px rgba(51,102,255,' + opacity + ')';
			console.log(spread + ' and ' + opacity);
		},200);
	} else if(pulseCallBack[elementID]) {
		clearInterval(pulseCallBack[elementID]);
		pulseElement.style.boxShadow = '';
	}
}

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