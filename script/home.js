var fName = document.getElementById('fName');
var lName = document.getElementById('lName');
var teamNum = document.getElementById('teamNum');
var previewScoutID = document.getElementById('previewScoutID');
var signupPassword = document.getElementById('signupPassword');
var email = document.getElementById('email');


function displayScoutID() {
	setTimeout(function(){
		var fNameV = fName.value;
		var lNameV = lName.value;
		var teamNumV = teamNum.value;

		if (fNameV != '' && lNameV != '' && teamNumV != '') {
			previewScoutID.value = fNameV.toLowerCase().titleCase() + lNameV.toLowerCase().titleCase() + "-" + teamNumV;
		} else {
			previewScoutID.value = '';
		}
	}, 1);//timeout used because key hasn't been pressed yet
}

function checkSignup(filter) {//return true if ok, return false if bad
	if (filter == 'fName' || filter == 'all') {

		highlight('error', fName);

		if (filter != 'all') return true;
	}
	if (filter == 'lName' || filter == 'all') {


		if (filter != 'all') return true;
	}
	return true; //temp
}
/*
function highlight(condition, element){
	//pulse, clear, error, correct
	if(condition == 'clear'){
		element.style. 
	} else if(condition == 'error') {
		
	} else if(condition == 'correct') {
		
	} else if(condition == 'pulse') {
		
	}
	element.style
}
*/
function pulse(elementID, setting){//setting is bool, true=pulse & false=stop
	startTime = new Date();
	pulseCallBack = setInterval(,100);
}

function postSignup(){//this function will interfere with logged in users... signup must not be visible if logged in
	if (checkSignup('all') == false){
		return;//jGrowl messages & highlighting deal with bad inputs
	}

	//put all info in user object, scoutid is made server-side
	user.info = {
		"fName": fName.value,
		"lName": lName.value,
		"team": teamNum.value
	};
	user.account = {
		"pword": signupPassword.value,
		"email": email.value
	};

	bakeCookie('user', $.toJSON(user));
	post('signup.php','');//all data is in cookie
	logout();//remove stuff from cookie & user object (in a standard way)
}