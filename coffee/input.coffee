// global vars for errorcheck(), actual values assigned later
`
var errornum = 0; //# of inputs with errors
var inputnum = 0; //# of inputs filled

//get all inputs & assign variable

//id of each element to get data from
var input = {
	"drive": {
		"categories": ["p"]
	},
	"comments": {
		"categories": ["r", "t", "p"],
		"defaultValue": ''
	},
	"matchNum": {
		"categories": ["r", "t"]
	},
	"matchType": {
		"categories": ["r", "t"],
		"defaultValue": 'p'
	},
	"teamNum": {
		"categories": ["r", "t"],
		"defaultValue": ''
	},
	"pitTeamNum": {//this is really just a pain
		"categories": ["p"],
		"defaultValue": ''
	},
	"allianceColor": {
		"categories": ["r","t"],
		"defaultValue": 'red'
	},
	"disabledOrNoShow": {
		"categories": ["r"],
		"defaultValue": false
	},
	"crossesBump": {
		"categories": ["r"],
		"defaultValue": false
	},
	"picksUpBalls": {
		"categories": ["r"],
		"defaultValue": false
	},
	"manipulatesBridge": {
		"categories": ["r"],
		"defaultValue": false
	},
	"getsBallsOffBridge": {
		"categories": ["r"],
		"defaultValue": false
	},
	"usesKinect": {
		"categories": ["r"],
		"defaultValue": false
	},
	"hybridHigh": {
		"categories": ["r"],
		"defaultValue": ''
	},
	"hybridMiddle": {
		"categories": ["r"],
		"defaultValue": ''
	},
	"hybridBottom": {
		"categories": ["r"],
		"defaultValue": ''
	},
	"attemptBalance": {
		"categories": ["r"],
		"defaultValue": false
	},
	"robotsOnBridge": {
		"categories": ["r"],
		"defaultValue": ''
	},
	"balanceSuccess": {
		"categories": ["r"],
		"defaultValue": false
	},
	"coopertition": {
		"categories": ["r"],
		"defaultValue": false
	},
	"teleopHigh": {
		"categories": ["r"],
		"defaultValue": ''
	},
	"teleopMiddle": {
		"categories": ["r"],
		"defaultValue": ''
	},
	"teleopBottom": {
		"categories": ["r"],
		"defaultValue": ''
	},
	"totalShots": {
		"categories": ["r"],
		"defaultValue": ''
	},
	"fouls": {
		"categories": ["r"],
		"defaultValue": ''
	},
	"techFouls": {
		"categories": ["r"],
		"defaultValue": ''
	}
};


//TODO: add class for hidden sub-pages -- use for signup page during login

//add fields to input object for use later
for(var i in input){
	input[i].elementNode = document.getElementById(i);
	if(input[i].elementNode.type == "checkbox"){
		input[i].valueGetter = 'checked';
	} else {
		input[i].valueGetter = 'value';
	}
}

/*
function getValues(){
	for(var i in input){
		input[i].inputValue = input[i].elementNode[input[i].valueGetter];
	}
}
*/

function submitData(){
	//document.getElementById('submit').disabled = true;//prevent 2x press (team will be cleared so it will error-out if submitted 2x)

	var submit = {};

	for(var i in input){
		if(input[i].categories.has(current.subpage.charAt(0))) submit[i] = input[i].elementNode[input[i].valueGetter];
	}

	if(current.subpage.charAt(0) == 't'){
		submit.shots = trackingInputs;
	}

	if(current.subpage.charAt(0) == 'p'){
		submit.teamNum = submit.pitTeamNum;
	}

	//console.log(submit);

	increase('matchNum');

	submit = '{"request": "input", "inputType": "' + current.subpage + '", "data": ' + $.toJSON(submit) + '}';
	//console.log(submit);

	if(post('process.php', submit, false, true) !== false){//if no errors, clear inputs
		if(current.subpage.charAt(0) == 't'){
			trackingInputs = [];
			updateTrackingDisplay();
		}

		if(current.subpage.charAt(0) == 'p'){
			submit.teamNum = submit.pitTeamNum;
			delete submit.pitTeamNum;
		}

		clearInputs();
	}
	//document.getElementById('submit').disabled = false;
}

function increase(elementid){
	var element = document.getElementById(elementid);
	var startvalue = element.value;

	if(isNaN(startvalue) === true || startvalue === '') {
		startvalue = 1;
	} else {
		startvalue = parseFloat(element.value) + 1;
	}

	element.value = startvalue;
}

function errorCheck(){
/*
	if (currentpage == 'robot') {
		inputspossible = 6;

		// TODO error checking code

	} else if (currentpage == 'human') {
		inputspossible = 6;

		//error checking code

	} else if (currentpage == 'pit') {
		inputspossible = 6;

		//error checking code

	}

	$("#progressbar-value").animate( { width: inputnum / inputspossible * 100 + "%" } );
	$("#errorbar-value").animate( { width: errornum / inputspossible * 100 + "%" } );
*/
}

function clearInputs(){
	for(var i in input){
		if(input[i].categories.has(current.subpage.charAt(0)) && input[i].defaultValue !== undefined && input[i].valueGetter !== 'checked'){
			//TODO: make resetter for iButton and remove && input[i].valueGetter !== 'checked'
			input[i].elementNode[input[i].valueGetter] = input[i].defaultValue;

		}
	}
	$('#jGrowl-container').jGrowl('Inputs have been cleared.');
}

//tracking (canvas input stuff)
		
	function typeSelect(typeOfEntry){
		currentEntry.period = typeOfEntry;
		document.getElementById(typeOfEntry).className = "selected";
		
		document.getElementById('hybrid').className = "";
		document.getElementById('teleop').className = "";
		document.getElementById(typeOfEntry).className = "selected";
	}
	
	//TODO: fix issue with incorrect location caused by scroll bar
	function canvasClick(e){
		//console.log(e.clientX);
		currentEntry.xCoord = e.clientX-$('#canvas').offset().left;
		
		//console.log(e.clientY);
		currentEntry.yCoord = e.clientY-$('#canvas').offset().top;
		
		canvas.drawImage(img, 0, 0, 300, 150);//redraw image
		
		canvas.beginPath();
		canvas.arc(currentEntry.xCoord, currentEntry.yCoord, 10, 0, Math.PI*2, false);
		canvas.closePath();
		canvas.fillStyle = "#000";
		canvas.fill();
	}
	
	function scoreSelect(typeOfScore){

		document.getElementById('top').className = "";
		document.getElementById('middle').className = "";
		document.getElementById('bottom').className = "";

		if(typeOfScore == currentEntry.score){

			typeOfScore = "";//clicking on already selected button deselects it
			currentEntry.score = "";

		} else {

			currentEntry.score = typeOfScore;
			document.getElementById(typeOfScore).className = "selected";

		}

		//console.log(currentEntry);
		
	}


//Tracking Robots
	function addEntry(){
		if(currentEntry.xCoord === -1 || currentEntry.yCoord === -1){//this doesn't support coords for each at 0 (which could happen)
			$('#jGrowl-container').jGrowl('placement on field was not set', {
				theme: 'error'
			});
			return;
		}
		/*
		if(matchTimer == -1){
			$('#jGrowl-container').jGrowl('timer was not started', {
				theme: 'error'
			});
			return;
		}
		*/

		//currentEntry.time = matchTimer;

		trackingInputs.push(currentEntry);

		reloadTrackingInput(currentEntry.period);
		updateTrackingDisplay();

		$('#jGrowl-container').jGrowl('added', {});
	}

	function updateTrackingDisplay(){
		var table = '<thead><tr><td>#</td><td>period</td><td>height</td><td>remove</td></tr></thead><tbody>';
		len = trackingInputs.length;
		for(var i = 0; i < len; i++){
			table += '<tr><td>' + (i+1) + '</td><td>' + trackingInputs[i].period + '</td><td>' + trackingInputs[i].score + '</td><td><button onclick="removeTrackingInput(' + i + ')">X</button></td></tr>';
		}
		table += '</tbody>';
		document.getElementById('trackingDisplay').innerHTML = table;
	}

	function removeTrackingInput(index){
		trackingInputs.remove(index);
		updateTrackingDisplay();
	}

	//tracking input startup
	var trackingInputs = [];
	var img = new Image();
	var canvas = document.getElementById('canvas').getContext('2d');
	img.src = "img/field.png";
	var currentEntry = {};

	img.onload = function(){
		reloadTrackingInput('hybrid');
	};

	function reloadTrackingInput(period){
		canvas.drawImage(img, 0, 0, 300, 150);

		currentEntry = {
			period: period,// hybrid/teleop
			xCoord: -1,
			yCoord: -1,
			score: ''
		};

		document.getElementById('top').className = "";
		document.getElementById('middle').className = "";
		document.getElementById('bottom').className = "";

		typeSelect(currentEntry.period);
	}
`