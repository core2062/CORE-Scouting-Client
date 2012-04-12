###
    ____      _____    ______
  _[░░░░]_   [░░░░░]  [░░░░░░]
 [░]````[░] [░]_`_`   [░]```[░]
 [░]     _   `[░░░]_  [░]    [░]
 [░]____[░]   _`_`[░] [░]___[░]
  `[░░░░]`   [░░░░░]  [░░░░░░]
    ````      `````    ``````
 --- CORE Scouting Database ---
###

###
       ________           ___________    _____________
     _[░░░░░░░░]_       _[░░░░░░░░░░░]  [░░░░░░░░░░░░░]_
   _[░░░]````[░░░]_    [░░░░░]```````   [░░░]``````[░░░░]
  [░░░]`      `[░░░]  [░░░░]`           [░░░]       `[░░░]
 [░░░]          ```    [░░░░░]___       [░░░]         [░░░]
 [░░░]                  ```[░░░░░]__    [░░░]         [░░░]
 [░░░]          ___         ``[░░░░░]   [░░░]         [░░░]
  [░░░]_      _[░░░]           _[░░░░]  [░░░]       _[░░░]
   `[░░░]____[░░░]`    _______[░░░░░]   [░░░]______[░░░░]
     `[░░░░░░░░]`  0  [░░░░░░░░░░░]` 0  [░░░░░░░░░░░░░]`  0
       ````````        ```````````       `````````````
               --- CORE Scouting Database ---
###

# Designer: Sean Lang


#jQuery v1.7.1 - Includes Sizzle.js
<?php echo getCoffee('base/jquery.js'); ?>

#Tipsy
<?php echo getCoffee('base/tipsy.js'); ?>

#Chosen Select Box
<?php echo getCoffee('base/chosen.js'); ?>
#$("select").chosen();

#ToggleJS
<?php echo getCoffee('base/toggle.js'); ?>
#$(":checkbox").toggleSwitch();

#jGrowl
<?php echo getCoffee('base/jgrowl.js'); ?>

#json2js
<?php echo getCoffee('base/json2js.js'); ?>

console.log 'Hello and welcome to the CSD, a intuitive scouting database and analysis program created by Sean Lang of CORE 2062.'

###
# Google +1 Button
`
(function () {
	var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
	po.src = 'https://apis.google.com/js/plusone.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
`
###

#TODO: float google +1 button left w/out 4px overhang
#TODO: make startup script to warn bad browsers
#TODO: add stuff to prefetch subpages???


#UI Event Handlers
$("input, textarea").focus ->
	$(this).parentsUntil($("form"), 'fieldset').addClass 'focus' #set fieldset class on focus

$("input, textarea").focusout ->
	$(this).parentsUntil($("form"), 'fieldset').removeClass 'focus'

$(".clearIcon span").click -> #clear input icon
	input = @previousSibling
	input.value = ""
	input.focus()

# accordion
allPanels = $("#navAccordion > ul").hide()
$("#navAccordion > p").click ->
	$target = $(this).next()
	unless $target.hasClass("active")
		allPanels.removeClass("active").slideUp()
		$target.addClass("active").slideDown()
	false

# global vars
current =
	index: ""
	type: ""
	lastSub: ""
	subpage: ""

prev =
	index: ""
	type: ""
	lastSub: ""
	subpage: ""

cache =
	subpages: []
	modals: []
	nav: []

defaultUser = #default user object for user who isn't logged in (no cookie is stored for this user)
	_id: "Guest"
	permission: 1
	token: ""
	info:
		fName: "Guest"
		lName: ""
		team: 0
	prefs:
		fade: true
		verbose: true

fixFavicon = -> #fixes favicon bug in firefox -- remove in future
	$("#favicon").remove()
	$('<link href="favicon.ico" rel="shortcut icon" id="favicon"/>').appendTo "head"

getMissedPosts = ->
	i = 0
	missedPosts = []
	loop
		missedPost = eatCookie("missedPost" + i)
		if missedPost isnt ""
			missedPosts[i] = eval('(' + missedPost + ')')
			i++
		else
			return missedPosts

buildCache = ->
	len = pages.length
	i = 0

	while i < len
		cache.nav.push pages[i].name
		for e of pages[i].subpages
			cache.subpages.push e
		for e of pages[i].modals
			cache.modals.push e
		i++
	cache.subpages = "." + cache.subpages.join("-c, .") + "-c"
	cache.modals = "." + cache.modals.join("-c, .") + "-c"
	cache.nav = "." + cache.nav.join("-n, .") + "-n"

$(document).ready ->
	$("a[title], label[title], button[title], textarea[title]").tipsy()
	$("input[title]").tipsy
		trigger: "focus"
		gravity: "w"

	$(".toggle-container[title]").tipsy
		trigger: "hover"
		gravity: "w"

	user = eatCookie("user")
	if user isnt ""
		window.user = eval("(" + user + ")")
		updateUserBar()
	else
		window.user = defaultUser
	missedPosts = getMissedPosts()
	buildCache()
	nav()

	#add error message for old browsers

window.onpopstate = (event) -> #rewrite this to use HTML5 pushState()
	console.log event
	fixFavicon()
	nav()

nav = ->
	###
	Navigation Function:
		this function handles all page transitions and applies all page specific options
		page specific options and data needed for displaying pages is stored in the JSON object 'pages'
		open login modal if needed for page
	###

	#TODO: make nav change the accordion that is open in the nav modal to the current page

	if location.hash.substring(1) is ""
		location.hash = "#front-page" #default page
		return

	prev.index = current.index
	prev.type = current.type
	prev.lastSub = current.lastSub
	prev.subpage = current.subpage

	return if current.subpage is location.hash.substring(1) and current.subpage isnt ""
	current.subpage = location.hash.substring(1).toLowerCase()
	current.index = ""
	len = pages.length
	i = 0

	while i < len #TODO: add catching?
		if typeof pages[i].subpages[current.subpage] isnt "undefined"
			current.index = i
			current.type = "subpages"
			current.lastSub = current.subpage
			break
		i++

	if current.index is "" #TODO: merge with subpage search ?
		i = 0
		while i < len
			if typeof pages[i].modals[current.subpage] isnt "undefined"
				current.index = i
				current.type = "modals"
				break
			i++
	if current.index is "" #page cannot be found, select default page
		window.location = "#front-page" #default page
		return

	#check if page has been downloaded yet (add functionality later)
	#download if it hasn't been

	fadetime = 500
	if prev.subpage is "" #if this is the first page
		if current.type is "modals" #fade in page
			$(".front-page-c").fadeIn fadetime / 4
			$(".home-n").css "display", "inline" #show navbar
			#$(cache.nav).css('display','none'); not needed - nothing is shown in the beginning
			$("#front-page-r").attr "checked", true

			#set variables
			current.lastSub = "front-page"
			prev.index = 2
			prev.subpage = "front-page"

		prev.type = "subpages"

	document.title = pages[current.index]["full-name"].replace(/\-/, " ").titleCase() + " - " + current["subpage"].replace(/\-/g, " ").titleCase()
	document.getElementById("body").style.minWidth = pages[current.index].minWidth
	document.getElementById("progressbar").style.display = pages[current.index].progressbar

	#start page changers
	if current.type is "subpages" #sub-pages
		#change navbar (no fade)
		$(cache.nav).css "display", "none"
		$("." + pages[current.index].name + "-n").css "display", "inline"
		$("#" + current.subpage + "-r").attr "checked", true
		if prev.type is "subpages" #sub-pages
			if user.prefs.fade is true
				$(cache.subpages).fadeOut(fadetime).promise().done ->
					$("." + current.subpage + "-c").fadeIn fadetime
			else
				$(cache.subpages).css "display", "none"
				$("." + current.subpage + "-c").css "display", "inline"
		else #modals
			if prev.lastSub is current.subpage #don't fade out sub-page if is is already under the modal
				if user.prefs.fade is true
					$("#overlay, #modal-container, " + cache.modals).fadeOut fadetime
				else
					$("#overlay, #modal-container, " + cache.modals).css "display", "none"
			else
				if user.prefs.fade is true
					$("#overlay, #modal-container, " + cache.subpages + ", " + cache.modals).fadeOut(fadetime).promise().done ->
						$("." + current.subpage + "-c").fadeIn fadetime
				else
					$("#overlay, #modal-container, " + cache.subpages + ", " + cache.modals).css "display", "none"
					$("." + current.subpage + "-c").css "display", "inline"
	else
		document.getElementById("modal-title").innerHTML = pages[current.index]["modals"][current.subpage]["full-name"].replace(/\-/, " ").titleCase()
		if prev.type is "subpages"
			if user.prefs.fade is true
				$(cache["modals"]).hide().promise().done ->
					$("#overlay").fadeIn 40
					$("." + current.subpage + "-c, #modal-container").fadeIn fadetime
			else
				$(cache["modals"]).css "display", "none"
				$("#overlay, ." + current.subpage + "-c, #modal-container").css "display", "block"
		else
			if user.prefs.fade is true
				$(cache.modals).fadeOut(fadetime).promise().done ->
					$("." + current.subpage + "-c, #modal-container").fadeIn fadetime
			else
				$(cache.modals).css "display", "none"
				$("." + current.subpage + "-c, #modal-container").css "display", "inline"
	if pages[current.index][current.type][current.subpage]["login-required"] is true and eatCookie("user") is ""
		setTimeout "window.location = '#login'", fadetime * 2
		return
	eval pages[current.index][current.type][current.subpage]["onOpen"] if typeof pages[current.index][current.type][current.subpage]["onOpen"] isnt `undefined`

`

		} else { //
			if (prev.lastSub == current.subpage) { //
				if(user.prefs.fade === true){
					$('#overlay, #modal-container, ' + cache.modals).fadeOut(fadetime);
				} else {
					$('#overlay, #modal-container, ' + cache.modals).css('display','none');
				}
			} else {
				if(user.prefs.fade === true){
					$('#overlay, #modal-container, ' + cache.subpages + ', ' + cache.modals).fadeOut(fadetime).promise().done(function() {
						$('.' + current.subpage + '-c').fadeIn(fadetime);
					});
				} else {
					$('#overlay, #modal-container, ' + cache.subpages + ', ' + cache.modals).css('display','none');
					$('.' + current.subpage + '-c').css('display','inline');
				}
			}
		}
	} else { //modal
		document.getElementById('modal-title').innerHTML = pages[current.index]['modals'][current.subpage]['full-name'].replace(/\-/,' ').titleCase();

		if (prev.type == 'subpages'){ //subpages
			if(user.prefs.fade === true){
				$(cache['modals']).hide().promise().done(function() {
					$('#overlay').fadeIn(40);
					$('.' + current.subpage + '-c, #modal-container').fadeIn(fadetime);
				});
			} else {
				$(cache['modals']).css('display','none');
				$('#overlay, .' + current.subpage + '-c, #modal-container').css('display','block');
			}
		} else { //modals
			if(user.prefs.fade === true){
				$(cache.modals).fadeOut(fadetime).promise().done(function() {
					$('.' + current.subpage + '-c, #modal-container').fadeIn(fadetime);
				});
			} else {
				$(cache.modals).css('display','none');
				$('.' + current.subpage + '-c, #modal-container').css('display','inline');
			}
		}
	}
	
	if(pages[current.index][current.type][current.subpage]['login-required'] === true && eatCookie('user') === ''){
		//TODO: figure out a way to do this without a timeout & without screwing up the page below
		setTimeout("window.location = '#login'", fadetime*2);
		return;
	}
	/*
	else:
		assume logged in, if token is wrong error will be returned by process later
		other wise there would be too many login checks
		on error returned from process.php, token is removed
	*/
	
	if(typeof pages[current.index][current.type][current.subpage]['onOpen'] !== undefined){
		eval(pages[current.index][current.type][current.subpage]['onOpen']);
	}
}

//site functions
function modalClose(runScript) {//if runScript is defined then the script won't be run (define as false)
	//TODO: expanding the bottom code to work on all page types
	if(typeof pages[current.index]['modals'][current.subpage]['onClose'] !== 'undefined' && typeof(runScript) === 'undefined'){
		eval(pages[current.index]['modals'][current.subpage]['onClose']);
	}

	window.location = '#' + current.lastSub;
}

//Cookie Handling Functions
	function bakeCookie(name, value) {
		var expires = new Date();
		expires.setTime(expires.getTime() + (15552000000));
		if(value === ''){//set cookie, or if value is blank, set it to be removed
			document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
		} else {
			document.cookie = name + "=" + value + "; expires=" + expires.toGMTString() + "; path=/";
		}
	}

	function eatCookie(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		var cookieValue = "";
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') c = c.substring(1);
			if (c.indexOf(nameEQ) === 0) {
				cookieValue = c.substring(nameEQ.length);
				break;
			}
		}
		return cookieValue;
	}

function getToken(password) {
	/*
	this function handles:
		getting token & user object from login
		calling login.php
		
	must be separate from other functions so it can be called directly from login modal
	*/
	scoutidInput = document.getElementById('scoutid');
	pwordInput = document.getElementById('pword');

	user._id = scoutidInput.value; //put in user object (scoutid in user object != logged in)
	pword = pwordInput.value; //limited to this function (can't be recovered after being typed in)

	scoutidInput.value = ''; //remove them from inputs
	pwordInput.value = '';

	if (user._id === '') {
		$('#jGrowl-container').jGrowl('scoutID is blank', {
			theme: 'error'
		});
	} else if (pword === '') {
		$('#jGrowl-container').jGrowl('password is blank', {
			theme: 'error'
		});
	} else {
		var json = post('login.php', '{"_id":"' + user._id + '","pword":"' + pword + '"}');

		if (json.token) {
			//store stuff in temporary user object
			user = json;

			bakeCookie('user', $.toJSON(user)); //store user object in cookie

			updateUserBar();
			
			modalClose();
			
			return;
		} else if (json !== false) {
			$('#jGrowl-container').jGrowl('server did not respond properly', {
				theme: 'error'
			});
		}
	}
	window.location = '#login';//will only be run at error due to above return
}

function callLogout(){#tells server to logout & runs logout function
	post('process.php','{"request":"logout"}');
	#recheck current page in navbar, radio button hasn't been set yet so timeout is needed
	setTimeout("$('#' + current.subpage + '-r').attr('checked', true)",1);
	logout();
}

function logout() {	#just removes the cookie & user object
	#must be separate from other functions so it can be called from script returned by post() or manually by user

	window.user = defaultUser;#reset to generic user object

	bakeCookie('user', '');#remove user object cookie
	
	updateUserBar();
	
	if(pages[current.index][current.type][current.subpage]['login-required'] === true){
		window.location = '#login';
	}
}

function updateUserBar(){#also updates account modal
	var loginLabel = document.getElementById('login-r-label');

	if(eatCookie('user') !== ''){#logged in
		$('#login').css('display','none');
		$('#logout').css('display','inline');
		loginLabel.setAttribute('original-title','Logout');
		loginLabel.setAttribute('onclick',"callLogout()");
	} else {#not logged in
		$('#logout').css('display','none');
		$('#login').css('display','inline');
		loginLabel.setAttribute('original-title','Login');
		loginLabel.setAttribute('onclick',"window.location = '#login'");
	}

	document.getElementById('scoutName').innerHTML = $.trim(user.info.fName + ' ' + user.info.lName);

	#account modal
	$('#fadeEffects').toggleSwitch("toggle", user.prefs.fade);
	$('#verboseMode').toggleSwitch("toggle", user.prefs.verbose);
}

function updateUser(key, value){#newObject does not need to be a full user object
	#user = jQuery.extend(true, user, userUpdates);#CONSIDER using this in login so only non-default stuff needs to be sent
	user.prefs[key] = value;
	if(eatCookie('user') !== ''){
		bakeCookie('user', $.toJSON(user));
	}
}

function postUserUpdates(){
	if(eatCookie('user') !== ''){#only run if logged in
		post('process.php', '{"request": "updateUser"}');#PHP gets user object from cookie
	}
	modalClose(false);
	#TODO: add checking in postUserUpdates() to see if user object is different
}

#general functions
String.prototype.titleCase = function () {
	return this.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
};

Array.prototype.has = function(checkObj){
	return (this.indexOf(checkObj) != -1);
};

Array.prototype.remove = function(from, to) {
  var rest = this.slice((to || from) + 1 || this.length);
  this.length = from < 0 ? this.length + from : from;
  return this.push.apply(this, rest);
};

function limitInput(e, limit) { #used for limiting form input
	var unicode = e.charCode ? e.charCode : e.keyCode;
	if (unicode != 8 && unicode != 9 && unicode != 37 && unicode != 39) { #if the key isn't the backspace key or tab or l/r arrow
		if ((unicode < 48 || unicode > 57) && limit == 'number') { #if not a number
			return false; #disable key press
		}

		if ((unicode < 65 || unicode > 90) && (unicode < 97 || unicode > 122) && limit == 'letter') { #if not a letter
			return false; #disable key press
		}
	}
}

function json2table (json) {
	var table = '<thead><tr>';
	var len = json.length;
	var len2 = json[0].length;

	for (e=0; e<len2; e++){
		table += '<td>' + json[0][e] + '</td>';
	}

	table += '</tr></thead><tbody>';

	len = json.length;
	for (i=1; i<len; i++){
		table += '<tr>';
		len2 = json[i].length;
		for (e=0; e<len2; e++){
			table += '<td>' + json[i][e] + '</td>';
		}
		table += '</tr>';
	}
	table += '</tbody>';
	return table;
}

function post(filename, json, async, saveRequest){
	async = (typeof async == "undefined")? false :async;
	saveRequest = (typeof saveRequest == "undefined")? false :saveRequest;
	/*
	this function handles:
		all interfacing w/ server via AJAX

	TODO: add reattempt code
		
	json.globalError: holds type of globalError (which determines action), error text is still in json.error
	*/
	
	var ajax = $.ajax({
		type: "POST",
		url: filename,
		data: 'data=' + json,
		async: async,
		success: function(data){
			if(async) postSuccess(data);#else, the function below will be called (async posts can't return anything)
		},
		error: function(jqXHR){
			#will trigger even if verbose is off
			$('#jGrowl-container').jGrowl('AJAX Error: ' + jqXHR.status + '<br />' + jqXHR.statusText + '.', {
				sticky: true,
				theme: 'error'
			});

			if(saveRequest){
				saveMissedRequest(filename, json);
			}
		}
	});

	if(!async) return postSuccess(ajax.responseText);
}

function postSuccess(data){
	if(data.charAt(0) == '{'){#not a flawless way to detect if it is json
		json = eval('(' + data + ')');
	} else {
		json = {};
		json.error = 'valid json was not returned';
	}
	
	#console.log(json);#TODO: remove before production
	
	if(json.script){#script must be run before error returns (like for logout function)
		eval(json.script);
	}
	
	if(json.error){
		$('#jGrowl-container').jGrowl('error: ' + json.error, {
			theme: 'error'
		});
		return false; #this means error
	}
	
	if(json.message && user.prefs.verbose === true){
		$('#jGrowl-container').jGrowl('success: ' + json.message, {
			theme: 'message'
		});
		delete json.message;
	}

	#try to submit any requests that failed before
	if(missedPosts.length !== 0){
		var lastMissedPost = missedPosts.length - 1;
		post(missedPosts[lastMissedPost].filename, missedPosts[lastMissedPost].json, true, true);
		missedPosts.remove(0);#it will be re-added if it fails to submit
		bakeCookie('missedPost' + lastMissedPost, "");
	}
	
	return json;#if nothing is returned assume error
}

function saveMissedRequest(filename, json){
	missedPosts.push({"filename": filename, "json": json});
	var lastMissedPost = missedPosts.length - 1;
	bakeCookie('missedPost' + (lastMissedPost), $.toJSON(missedPosts[lastMissedPost]));

	if(lastMissedPost >= 149){
		console.log('TODO: prompt file download');
	}

	$('#jGrowl-container').jGrowl('although the request has failed, your data has been saved, and I will attempt to resubmit it when possible', {
		sticky: true,
		theme: 'error'
	});
}


#TODO: replace with something better, like downloadify or just a modal

var colorList = ['rgb(97,28,252)','rgb(11,223,129)','rgb(27,98,240)','rgb(109,250,21)','rgb(40,247,81)','rgb(1,166,194)','rgb(197,2,196)','rgb(207,4,184)','rgb(210,181,5)','rgb(183,1,208)','rgb(140,7,237)','rgb(12,128,223)','rgb(7,142,213)','rgb(35,87,245)','rgb(15,228,121)','rgb(46,72,250)','rgb(250,106,47)','rgb(19,112,233)','rgb(76,43,254)','rgb(26,101,239)','rgb(217,172,8)','rgb(28,96,241)','rgb(158,227,3)','rgb(102,251,25)','rgb(205,187,4)','rgb(174,1,216)','rgb(228,15,156)','rgb(252,52,99)','rgb(252,99,53)','rgb(31,243,93)','rgb(125,244,13)','rgb(153,4,230)','rgb(230,152,17)','rgb(29,95,242)','rgb(10,219,134)','rgb(245,122,35)','rgb(152,231,4)','rgb(107,250,22)','rgb(209,183,5)','rgb(137,8,239)','rgb(234,20,147)','rgb(233,19,148)','rgb(238,24,139)','rgb(254,88,62)','rgb(179,212,1)','rgb(1,169,191)','rgb(253,95,55)','rgb(186,1,206)','rgb(202,190,3)','rgb(165,2,222)','rgb(21,109,235)','rgb(61,254,57)','rgb(3,156,202)','rgb(254,83,66)','rgb(52,252,66)','rgb(203,3,189)','rgb(155,229,3)','rgb(8,139,216)','rgb(55,63,253)','rgb(167,221,1)','rgb(159,3,226)','rgb(42,248,78)','rgb(145,6,235)','rgb(116,17,247)','rgb(242,30,129)','rgb(25,103,238)','rgb(9,218,135)','rgb(240,133,28)','rgb(248,113,42)','rgb(227,14,158)','rgb(70,48,254)','rgb(53,65,252)','rgb(3,201,158)','rgb(224,162,13)','rgb(136,239,9)','rgb(130,11,242)','rgb(5,147,210)','rgb(63,55,254)','rgb(22,236,107)','rgb(6,212,143)','rgb(38,247,82)','rgb(226,14,159)','rgb(2,163,197)','rgb(149,5,232)','rgb(48,251,71)','rgb(192,200,1)','rgb(147,234,5)','rgb(92,253,32)','rgb(196,196,2)','rgb(214,7,176)','rgb(218,171,9)','rgb(175,1,214)','rgb(171,1,218)','rgb(20,111,234)','rgb(219,169,9)','rgb(1,191,170)','rgb(170,1,219)','rgb(1,173,187)','rgb(5,210,147)','rgb(95,253,30)','rgb(58,253,59)','rgb(27,100,240)','rgb(206,185,4)','rgb(252,100,52)','rgb(1,188,173)','rgb(229,16,154)','rgb(228,157,15)','rgb(253,92,58)','rgb(49,251,69)','rgb(3,204,154)','rgb(254,66,84)','rgb(235,21,145)','rgb(6,144,212)','rgb(231,151,18)','rgb(73,46,254)','rgb(250,47,105)','rgb(2,160,200)','rgb(88,34,254)','rgb(253,59,91)','rgb(81,40,254)','rgb(194,1,198)','rgb(244,124,34)','rgb(143,6,236)','rgb(200,192,2)','rgb(180,1,211)','rgb(37,246,84)','rgb(2,196,164)','rgb(105,23,251)','rgb(110,249,20)','rgb(69,254,49)','rgb(101,252,26)','rgb(236,23,142)','rgb(113,248,19)','rgb(45,74,250)','rgb(222,165,11)','rgb(254,77,72)','rgb(59,59,253)','rgb(118,247,16)','rgb(162,224,2)','rgb(85,254,36)','rgb(249,109,44)','rgb(14,123,226)','rgb(33,90,244)','rgb(251,49,104)','rgb(4,205,153)','rgb(50,68,251)','rgb(246,37,120)'];

function colorBackground(){
	var i = Math.floor(Math.random()*colorList.length);

	if(i > colorList.length - 6) i = 0;#not really random, but easy and quick

	bodyElement.style.backgroundColor = colorList[i];
	cElement.setAttribute('fill', colorList[i+1] + ' !important');
	sElement.style.fill = colorList[i+2] + '!important';
	dElement.style.fill = colorList[i+3] + '!important';
	#TODO: make above work w/ css

	if(current.subpage == 'front-page'){
		bigcElement.style.color = colorList[i+4];
		bigsElement.style.color = colorList[i+5];
		bigdElement.style.color = colorList[i+6];
	}
}

function rainbow(seizureMode){
	window.bodyElement = document.getElementById('body');
	window.bigcElement = document.getElementById('bigC');
	window.bigsElement = document.getElementById('bigS');
	window.bigdElement = document.getElementById('bigD');
	window.cElement = document.getElementById('c');
	window.sElement = document.getElementById('s');
	window.dElement = document.getElementById('d');
	window.startTime2 = new Date();
	setInterval("colorBackground()",150);
}`