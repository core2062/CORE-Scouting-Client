(function() {
  var addEntry, canvas, canvasClick, clearInputs, currentEntry, errorCheck, errornum, i, img, increase, input, inputnum, reloadTrackingInput, removeTrackingInput, scoreSelect, submitData, trackingInputs, typeSelect, updateTrackingDisplay;

  input = {
    drive: {
      categories: ["p"]
    },
    comments: {
      categories: ["r", "t", "p"],
      defaultValue: ''
    },
    matchNum: {
      categories: ["r", "t"]
    },
    matchType: {
      categories: ["r", "t"],
      defaultValue: 'p'
    },
    teamNum: {
      categories: ["r", "t"],
      defaultValue: ''
    },
    pitTeamNum: {
      categories: ["p"],
      defaultValue: ''
    },
    allianceColor: {
      categories: ["r", "t"],
      defaultValue: 'red'
    },
    disabledOrNoShow: {
      categories: ["r"],
      defaultValue: false
    },
    crossesBump: {
      categories: ["r"],
      defaultValue: false
    },
    picksUpBalls: {
      categories: ["r"],
      defaultValue: false
    },
    manipulatesBridge: {
      categories: ["r"],
      defaultValue: false
    },
    getsBallsOffBridge: {
      categories: ["r"],
      defaultValue: false
    },
    usesKinect: {
      categories: ["r"],
      defaultValue: false
    },
    hybridHigh: {
      categories: ["r"],
      defaultValue: ''
    },
    hybridMiddle: {
      categories: ["r"],
      defaultValue: ''
    },
    hybridBottom: {
      categories: ["r"],
      defaultValue: ''
    },
    attemptBalance: {
      categories: ["r"],
      defaultValue: false
    },
    robotsOnBridge: {
      categories: ["r"],
      defaultValue: ''
    },
    balanceSuccess: {
      categories: ["r"],
      defaultValue: false
    },
    coopertition: {
      categories: ["r"],
      defaultValue: false
    },
    teleopHigh: {
      categories: ["r"],
      defaultValue: ''
    },
    teleopMiddle: {
      categories: ["r"],
      defaultValue: ''
    },
    teleopBottom: {
      categories: ["r"],
      defaultValue: ''
    },
    totalShots: {
      categories: ["r"],
      defaultValue: ''
    },
    fouls: {
      categories: ["r"],
      defaultValue: ''
    },
    techFouls: {
      categories: ["r"],
      defaultValue: ''
    }
  };

  for (i in input) {
    input[i].elementNode = document.getElementById(i);
    if (input[i].elementNode.type === "checkbox") {
      input[i].valueGetter = "checked";
    } else {
      input[i].valueGetter = "value";
    }
  }

  trackingInputs = [];

  img = new Image();

  canvas = document.getElementById("canvas").getContext("2d");

  img.src = "img/field.png";

  currentEntry = {};

  /*
  function getValues(){
  	for(var i in input){
  		input[i].inputValue = input[i].elementNode[input[i].valueGetter];
  	}
  }
  */


  submitData = function() {
    var submit;
    submit = {};
    for (i in input) {
      if (input[i].categories.has(current.subpage.charAt(0))) {
        submit[i] = input[i].elementNode[input[i].valueGetter];
      }
    }
    if (current.subpage.charAt(0) === "t") {
      submit.shots = trackingInputs;
    }
    if (current.subpage.charAt(0) === "p") {
      submit.teamNum = submit.pitTeamNum;
    }
    increase("matchNum");
    submit = '{"request": "input", "inputType": "' + current.subpage + '", "data": ' + $.toJSON(submit) + '}';
    if (post("process.php", submit, false, true) !== false) {
      if (current.subpage.charAt(0) === "t") {
        trackingInputs = [];
        updateTrackingDisplay();
      }
      if (current.subpage.charAt(0) === "p") {
        submit.teamNum = submit.pitTeamNum;
        delete submit.pitTeamNum;
      }
      return clearInputs();
    }
  };

  increase = function(elementid) {
    var element, startvalue;
    element = document.getElementById(elementid);
    startvalue = element.value;
    if (isNaN(startvalue) === true || startvalue === "") {
      startvalue = 1;
    } else {
      startvalue = parseFloat(element.value) + 1;
    }
    return element.value = startvalue;
  };

  errornum = 0;

  inputnum = 0;

  errorCheck = function() {
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

  };

  clearInputs = function() {
    for (i in input) {
      if (input[i].categories.has(current.subpage.charAt(0)) && input[i].defaultValue !== undefined && input[i].valueGetter !== "checked") {
        input[i].elementNode[input[i].valueGetter] = input[i].defaultValue;
      }
    }
    return $("#jGrowl-container").jGrowl("Inputs have been cleared.");
  };

  typeSelect = function(typeOfEntry) {
    currentEntry.period = typeOfEntry;
    document.getElementById(typeOfEntry).className = "selected";
    document.getElementById("hybrid").className = "";
    document.getElementById("teleop").className = "";
    return document.getElementById(typeOfEntry).className = "selected";
  };

  canvasClick = function(e) {
    currentEntry.xCoord = e.clientX - $("#canvas").offset().left;
    currentEntry.yCoord = e.clientY - $("#canvas").offset().top;
    canvas.drawImage(img, 0, 0, 300, 150);
    canvas.beginPath();
    canvas.arc(currentEntry.xCoord, currentEntry.yCoord, 10, 0, Math.PI * 2, false);
    canvas.closePath();
    canvas.fillStyle = "#000";
    return canvas.fill();
  };

  scoreSelect = function(typeOfScore) {
    document.getElementById("top").className = "";
    document.getElementById("middle").className = "";
    document.getElementById("bottom").className = "";
    if (typeOfScore === currentEntry.score) {
      typeOfScore = "";
      return currentEntry.score = "";
    } else {
      currentEntry.score = typeOfScore;
      return document.getElementById(typeOfScore).className = "selected";
    }
  };

  addEntry = function() {
    if (currentEntry.xCoord === -1 || currentEntry.yCoord === -1) {
      $("#jGrowl-container").jGrowl("placement on field was not set", {
        theme: "error"
      });
      return;
    }
    trackingInputs.push(currentEntry);
    reloadTrackingInput(currentEntry.period);
    updateTrackingDisplay();
    return $("#jGrowl-container").jGrowl("added", {});
  };

  updateTrackingDisplay = function() {
    var len, table;
    table = "<thead><tr><td>#</td><td>period</td><td>height</td><td>remove</td></tr></thead><tbody>";
    len = trackingInputs.length;
    i = 0;
    while (i < len) {
      table += "<tr><td>" + (i + 1) + "</td><td>" + trackingInputs[i].period + "</td><td>" + trackingInputs[i].score + "</td><td><button onclick=\"removeTrackingInput(" + i + ")\">X</button></td></tr>";
      i++;
    }
    table += "</tbody>";
    return document.getElementById("trackingDisplay").innerHTML = table;
  };

  removeTrackingInput = function(index) {
    trackingInputs.remove(index);
    return updateTrackingDisplay();
  };

  reloadTrackingInput = function(period) {
    canvas.drawImage(img, 0, 0, 300, 150);
    currentEntry = {
      period: period,
      xCoord: -1,
      yCoord: -1,
      score: ""
    };
    document.getElementById("top").className = "";
    document.getElementById("middle").className = "";
    document.getElementById("bottom").className = "";
    return typeSelect(currentEntry.period);
  };

  img.onload = function() {
    return reloadTrackingInput("hybrid");
  };

}).call(this);
