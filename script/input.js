// global vars for errorcheck(), actual values assigned later
var errornum = 0; //# of inputs with errors
var inputnum = 0; //# of inputs filled

/*
document.getElementById('TeamNum');
document.getElementById('AllianceScore');
document.getElementById('RCard');
document.getElementById('YCard');
document.getElementById('Comments');
*/

function increase(elementid){
var element = document.getElementById(elementid);
var startvalue = element.value;
	if (isNaN(startvalue) == true || startvalue == '') {
		startvalue = 1;
	}
	else {
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