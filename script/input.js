// global vars for errorcheck(), actual values assigned later
var errornum = 0; //# of inputs with errors
var inputnum = 0; //# of inputs filled

//get all inputs & assign variable

//id of each element to get data from
var input = {
	"yellowCard": {
		"categories": ["r"]
	},
	"foul": {
		"categories": ["r"]
	},
	"techFoul": {
		"categories": ["r"]
	},
	"hybridActivity": {
		"categories": ["r"]
	},
	"hybridStrategy": {
		"categories": ["r"]
	},
	"hybridScored": {
		"categories": ["r"]
	},
	"drive": {
		"categories": ["p"]
	},
	"comments": {
		"categories": ["r", "a", "p"]
	},
	"matchNum": {
		"categories": ["r", "a"]
	},
	"matchType": {
		"categories": ["r", "a"]
	},
	"teamNum": {
		"categories": ["r", "a"]
	},
	"pitTeamNum": {//this is really just a pain
		"categories": ["p"]
	},
	"allianceColor": {
		"categories": ["r","a"]
	},
	"wasDisabled": {
		"categories": ["r"]
	},
	"redCard": {
		"categories": ["r"]
	},
	"bridge": {
		"categories": ["r"]
	},
	"robotsBalancing": {
		"categories": ["r"]
	}/*,
	"bump": {
		"categories": ["r"]
	}*/
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
	var submit = {};

	for(var i in input){
		if(input[i].categories.has(current.subpage.charAt(0))){
			//console.log('getting:' + i);
			submit[i] = input[i].elementNode[input[i].valueGetter];
		}
	}

	if(current.subpage.charAt(0) == 'a'){
		submit.trackingInputs = trackingInputs;
		trackingInputs = [];
	}

	if(current.subpage.charAt(0) == 'p'){
		submit.teamNum = submit.pitTeamNum;
		delete submit.pitTeamNum;
	}

	console.log(submit);

	increase('matchNum');

	//submit.push('"' + i + '": "' + inputValue[i].value + '"');

console.log($.toJSON(submit));
	submit = '{"request": "input", "inputType": "' + current.subpage + '", "data": ' + $.toJSON(submit) + '}';
	console.log(submit);

	post('process.php', submit);
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

function errorcheck(){
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
}

function clearinputs(){
	if (current.subpage == 'robot') {
		inputspossible = 6;

		// TODO clear input code

	} else if (current.subpage == 'human') {
		inputspossible = 6;

		//clear input code

	} else if (current.subpage == 'pit') {
		inputspossible = 6;

		//clear input code

	}

	document.getElementById('TeamNum').value = "";
	document.getElementById('AllianceScore').value = '';
	document.getElementById('RCard').value = '';
	document.getElementById('YCard').value = '';
	document.getElementById('Comments').innerHTML = '';
	$('#jGrowl-container').jGrowl('Inputs have been cleared.');
}

//tracking (canvas input stuff)
		
	function typeSelect(typeOfEntry){
		currentEntry.type = typeOfEntry;
		document.getElementById(typeOfEntry).className = "selected";
	
		if(typeOfEntry == 'pickup'){
			document.getElementById('top').disabled = true;
			document.getElementById('middle').disabled = true;
			document.getElementById('bottom').disabled = true;

			document.getElementById('top').className = "";
			document.getElementById('middle').className = "";
			document.getElementById('bottom').className = "";
		} else {//shoot
			document.getElementById('top').disabled = false;
			document.getElementById('middle').disabled = false;
			document.getElementById('bottom').disabled = false;
		}
		
		document.getElementById('pickup').className = "";
		document.getElementById('shoot').className = "";
		document.getElementById(typeOfEntry).className = "selected";
	}
	
	function canvasClick(e){
		console.log(e.clientX);
		currentEntry.xCoord = e.clientX-$('#canvas').offset().left;
		
		console.log(e.clientY);
		currentEntry.yCoord = e.clientY-$('#canvas').offset().top;
		
		canvas.drawImage(img, 0, 0, 300, 150);//redraw image
		
		canvas.beginPath();
		canvas.arc(currentEntry.xCoord, currentEntry.yCoord, 10, 0, Math.PI*2, false);
		canvas.closePath();
		canvas.fillStyle = "#000";
		canvas.fill();
	}
	
	function scoreSelect(typeOfScore){
		if(document.getElementById(typeOfScore).disabled === true){
			return;
		}

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

		console.log(currentEntry);
		
	}

	var trackingInputs = [];

	function addEntry(){
		if(currentEntry.xCoord === -1 || currentEntry.yCoord === -1){//this doesn't support coords for each at 0 (which could happen)
			$('#jGrowl-container').jGrowl('placement on field was not set', {
				theme: 'error'
			});
			return;
		}

		trackingInputs.push(currentEntry);

		reloadTrackingInput();

		typeSelect('pickup');
	}

//tracking input startup
var img = new Image();
var canvas = document.getElementById('canvas').getContext('2d');
img.src = "img/field.png";
var currentEntry = {};

img.onload = function(){
	reloadTrackingInput();
};

function reloadTrackingInput(){
	canvas.drawImage(img, 0, 0, 300, 150);

	currentEntry = {
		type: "pickup",//pickup/shoot
		xCoord: -1,
		yCoord: -1,
		score: ""//top/middle/bottom
	};

}
	
var currentEntry = {
	type: "pickup",//pickup/shoot
	xCoord: -1,
	yCoord: -1,
	score: ""//top/middle/bottom
};

typeSelect('pickup');

