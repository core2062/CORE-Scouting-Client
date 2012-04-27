

#id of each element to get data from
input =
	drive:
		categories: ["p"]
	comments:
		categories: ["r", "t", "p"]
		defaultValue: ''
	matchNum:
		categories: ["r", "t"]
	
	matchType:
		categories: ["r", "t"]
		defaultValue: 'p'
	
	teamNum:
		categories: ["r", "t"]
		defaultValue: ''
	
	pitTeamNum:#this is really just a pain to have... it is seperate from the ther teamNum, but means same thing
		categories: ["p"]
		defaultValue: ''
	
	allianceColor:
		categories: ["r","t"]
		defaultValue: 'red'
	
	disabledOrNoShow:
		categories: ["r"]
		defaultValue: false
	
	crossesBump:
		categories: ["r"]
		defaultValue: false
	
	picksUpBalls:
		categories: ["r"]
		defaultValue: false
	
	manipulatesBridge:
		categories: ["r"]
		defaultValue: false
	
	getsBallsOffBridge:
		categories: ["r"]
		defaultValue: false
	
	usesKinect:
		categories: ["r"]
		defaultValue: false
	
	hybridHigh:
		categories: ["r"]
		defaultValue: ''
	
	hybridMiddle:
		categories: ["r"]
		defaultValue: ''
	
	hybridBottom:
		categories: ["r"]
		defaultValue: ''
	
	attemptBalance:
		categories: ["r"]
		defaultValue: false
	
	robotsOnBridge:
		categories: ["r"]
		defaultValue: ''
	
	balanceSuccess:
		categories: ["r"]
		defaultValue: false
	
	coopertition:
		categories: ["r"]
		defaultValue: false
	
	teleopHigh:
		categories: ["r"]
		defaultValue: ''
	
	teleopMiddle:
		categories: ["r"]
		defaultValue: ''
	
	teleopBottom:
		categories: ["r"]
		defaultValue: ''
	
	totalShots:
		categories: ["r"]
		defaultValue: ''
	
	fouls:
		categories: ["r"]
		defaultValue: ''
	
	techFouls:
		categories: ["r"]
		defaultValue: ''

#TODO: add validate functions to each input... they are passed the value & must return true or false tell if it is correct
#TODO: add class for hidden sub-pages -- use for signup page during login

#TODO: fix issue with not warning about blank team num

#add fields to input object for use later

#get all inputs & assign variable
for i of input
	input[i].elementNode = document.getElementById(i)
	if input[i].elementNode.type is "checkbox"
		input[i].valueGetter = "checked"
	else
		input[i].valueGetter = "value"

#tracking input startup
trackingInputs = []
img = new Image()
canvas = document.getElementById("canvas").getContext("2d")
img.src = "img/field.png"
currentEntry = {}

###
function getValues(){
	for(var i in input){
		input[i].inputValue = input[i].elementNode[input[i].valueGetter];
	}
}
###


submitData = ->
	#document.getElementById('submit').disabled = true;#prevent 2x press (team will be cleared so it will error-out if submitted 2x)
	submit = {}
	for i of input
		submit[i] = input[i].elementNode[input[i].valueGetter] if input[i].categories.has(current.subpage.charAt(0))
	submit.shots = trackingInputs if current.subpage.charAt(0) is "t"
	submit.teamNum = submit.pitTeamNum if current.subpage.charAt(0) is "p"
	
	#console.log(submit);
	
	increase "matchNum"

	submit = '{"request": "input", "inputType": "' + current.subpage + '", "data": ' + $.toJSON(submit) + '}'

	#console.log submit

	if post("process.php", submit, false, true) isnt false #if no errors, clear inputs
		if current.subpage.charAt(0) is "t"
			trackingInputs = []
			updateTrackingDisplay()
		if current.subpage.charAt(0) is "p"
			submit.teamNum = submit.pitTeamNum
			delete submit.pitTeamNum
		clearInputs()
	#document.getElementById('submit').disabled = false;

increase = (elementid) ->
	element = document.getElementById(elementid)
	startvalue = element.value
	if isNaN(startvalue) is true or startvalue is ""
		startvalue = 1
	else
		startvalue = parseFloat(element.value) + 1
	element.value = startvalue

# global vars for errorcheck(), actual values assigned later
errornum = 0 #of inputs with errors
inputnum = 0 #of inputs filled

errorCheck = ->
	###
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
	###
	
clearInputs = ->
	for i of input
		#TODO: make resetter for iButton and remove && input[i].valueGetter !== 'checked'
		input[i].elementNode[input[i].valueGetter] = input[i].defaultValue if input[i].categories.has(current.subpage.charAt(0)) and input[i].defaultValue isnt `undefined` and input[i].valueGetter isnt "checked"
	$("#jGrowl-container").jGrowl "Inputs have been cleared."
#tracking (canvas input stuff)

typeSelect = (typeOfEntry) ->
	currentEntry.period = typeOfEntry
	document.getElementById(typeOfEntry).className = "selected"

	document.getElementById("hybrid").className = ""
	document.getElementById("teleop").className = ""
	document.getElementById(typeOfEntry).className = "selected"

#TODO: fix issue with incorrect location caused by scroll bar

canvasClick = (e) ->
	#console.log(e.clientX);
	#console.log(e.clientY);
	currentEntry.xCoord = e.clientX - $("#canvas").offset().left
	currentEntry.yCoord = e.clientY - $("#canvas").offset().top
	canvas.drawImage img, 0, 0, 300, 150
	canvas.beginPath()
	canvas.arc currentEntry.xCoord, currentEntry.yCoord, 10, 0, Math.PI * 2, false
	canvas.closePath()
	canvas.fillStyle = "#000"
	canvas.fill()

scoreSelect = (typeOfScore) ->
	document.getElementById("top").className = ""
	document.getElementById("middle").className = ""
	document.getElementById("bottom").className = ""
	if typeOfScore is currentEntry.score
		typeOfScore = "" #clicking on already selected button deselects it
		currentEntry.score = ""
	else
		currentEntry.score = typeOfScore
		document.getElementById(typeOfScore).className = "selected"
	#console.log(currentEntry);

#Tracking Robots
addEntry = ->
	if currentEntry.xCoord is -1 or currentEntry.yCoord is -1 #this doesn't support coords for each at 0 (which could happen)
		$("#jGrowl-container").jGrowl "placement on field was not set",
			theme: "error"
		return

	trackingInputs.push currentEntry
	reloadTrackingInput currentEntry.period
	updateTrackingDisplay()
	$("#jGrowl-container").jGrowl "added", {}

updateTrackingDisplay = ->
	table = "<thead><tr><td>#</td><td>period</td><td>height</td><td>remove</td></tr></thead><tbody>"
	len = trackingInputs.length
	i = 0

	while i < len
		table += "<tr><td>" + (i + 1) + "</td><td>" + trackingInputs[i].period + "</td><td>" + trackingInputs[i].score + "</td><td><button onclick=\"removeTrackingInput(" + i + ")\">X</button></td></tr>"
		i++
	table += "</tbody>"
	document.getElementById("trackingDisplay").innerHTML = table

removeTrackingInput = (index) ->
	trackingInputs.remove index
	updateTrackingDisplay()

reloadTrackingInput = (period) ->
	canvas.drawImage img, 0, 0, 300, 150
	currentEntry =
		period: period # hybrid/teleop
		xCoord: -1
		yCoord: -1
		score: ""

	document.getElementById("top").className = ""
	document.getElementById("middle").className = ""
	document.getElementById("bottom").className = ""
	typeSelect currentEntry.period

img.onload = ->
	reloadTrackingInput "hybrid"