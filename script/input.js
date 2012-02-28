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
	"teamNum": {
		"categories": ["r", "a", "p"]
	},
	"allianceColor": {
		"categories": ["r"]
	},
	"wasDisabled": {
		"categories": ["r", "a"]
	},
	"redCard": {
		"categories": ["r", "a"]
	},
	"bridge": {
		"categories": ["r", "a"]
	},
	"bump": {
		"categories": ["r", "a"]
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
	var submit = [];

	for(var i in input){
		if(input[i].categories.has(current.subpage.charAt(0))){
			submit[i] = input[i].elementNode[input[i].valueGetter];
		}
	}

	console.log(submit);

	//submit.push('"' + i + '": "' + inputValue[i].value + '"');


	//submit = '{"request": "input", "inputType": "' + current.subpage + '", "data": {' + submit.join(', ') + '}}';

	//post('process.php', submit);
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
	if (currentpage == 'robot') {
		inputspossible = 6;

		// TODO clear input code

	} else if (currentpage == 'human') {
		inputspossible = 6;

		//clear input code

	} else if (currentpage == 'pit') {
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