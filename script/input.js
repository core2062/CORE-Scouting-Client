// global vars for page navigation
// TODO remove & replace vars with sitemap
var subpages = ['Regular', 'Human-Player', 'Pit']; //the first element will be the default page
var modals = ['Navagation', 'Login', 'Contact'];

// global vars for errorcheck(), actual values assigned later
var errornum = 0; //# of inputs with errors
var inputnum = 0; //# of inputs filled

function increase(elementid){
var startvalue = document.getElementById(elementid).value;
	if (isNaN(startvalue) == true || startvalue == '') {
		startvalue = 1;
	}
	else {
		startvalue = parseFloat(document.getElementById(elementid).value) + 1;
	}
document.getElementById(elementid).value = startvalue;
}

function errorcheck(){
	if (currentpage == 'Regular') {
		inputspossible = 6;
		
		// TODO error checking code
		
	} else if (currentpage == 'Human-Player') {
		inputspossible = 6;
		
		//error checking code
		
	} else if (currentpage == 'Pit') {
		inputspossible = 6;
		
		//error checking code
		
	}
	
	$("#progressbar-value").animate( { width: inputnum / inputspossible * 100 + "%" } );
	$("#errorbar-value").animate( { width: errornum / inputspossible * 100 + "%" } );
}

function clearinputs(){
	if (currentpage == 'Regular') {
		inputspossible = 6;
		
		// TODO clear input code
		
	} else if (currentpage == 'Human-Player') {
		inputspossible = 6;
		
		//clear input code
		
	} else if (currentpage == 'Pit') {
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

$('#AllianceColor').easySelectBox({speed:100});
$('#to').easySelectBox({speed:100});

$('#Nav-options-l').tipsy(); //TODO make a array (and loop through it) for all elements to apply tipsy to, or just search for "a" links with a title attribute -->