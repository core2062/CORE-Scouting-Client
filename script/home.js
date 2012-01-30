var fName = document.getElementById('fName');
var lName = document.getElementById('lName');
var teamNum = document.getElementById('teamNum');
var previewScoutID = document.getElementById('previewScoutID');
var signupPassword = document.getElementById('signupPassword');


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
	}, 100);//timeout used because key hasn't been pressed yet
}

function checkSignup(filter) {
	if (filter == 'fName' || filter == 'all') {


		if (filter != all) {
			return
		};
	}
	if (filter == 'lName' || filter == 'all') {

		if (filter != all) {
			return
		};
	}
}