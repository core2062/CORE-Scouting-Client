/*    ____      _____    ______
 *  _[░░░░]_   [░░░░░]  [░░░░░░]
 * [░]````[░] [░]_`_`   [░]```[░]
 * [░]     _   `[░░░]_  [░]    [░]
 * [░]____[░]   _`_`[░] [░]___[░]
 *  `[░░░░]`   [░░░░░]  [░░░░░░]
 *    ````      `````    ``````
 * --- CORE Scouting Database ---
*/

/*       ________           ___________    _____________
 *     _[░░░░░░░░]_       _[░░░░░░░░░░░]  [░░░░░░░░░░░░░]_
 *   _[░░░]````[░░░]_    [░░░░░]```````   [░░░]``````[░░░░]
 *  [░░░]`      `[░░░]  [░░░░]`           [░░░]       `[░░░]
 * [░░░]          ```    [░░░░░]___       [░░░]         [░░░]
 * [░░░]                  ```[░░░░░]__    [░░░]         [░░░]
 * [░░░]          ___         ``[░░░░░]   [░░░]         [░░░]
 *  [░░░]_      _[░░░]           _[░░░░]  [░░░]       _[░░░]
 *   `[░░░]____[░░░]`    _______[░░░░░]   [░░░]______[░░░░]
 *     `[░░░░░░░░]`  0  [░░░░░░░░░░░]` 0  [░░░░░░░░░░░░░]`  0
 *       ````````        ```````````       `````````````
 *               --- CORE Scouting Database ---
*/

// Designer: Sean Lang
console.log('Hello and welcome to the CSD, a intuitive scouting database and analysis program created by Sean Lang of CORE 2062.');

// Google +1 Button
/*
(function () {
	var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
	po.src = 'https://apis.google.com/js/plusone.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
*/

//TODO: float google +1 button left w/out 4px overhang
//TODO: make startup script to warn bad browsers
//TODO: add stuff to prefetch subpages???


//UI Event Handlers
	//set fieldset class on focus
	$("input, textarea").focus(function () {
		$(this).parentsUntil($("form"), "fieldset").addClass('focus');
	});

	$(".clearIcon span").click(function () {
		var input = this.previousSibling;
		input.value = '';
		input.focus();
	});

	$("input, textarea").focusout(function () {
		$(this).parentsUntil($("form"), "fieldset").removeClass('focus');
	});

	// Accordion
	//not finished
	$('.accordion > p').click(function() {
		console.log('bitches');
		console.log($(this).next());
	});

// global vars
	var current = {
		"index": "",
		"type": "",
		"lastSub": "",
		"subpage": ""
	};
	var prev = {
		"index": "",
		"type": "",
		"lastSub": "",
		"subpage": ""
	};

	var cache = {
		"subpages": [],
		"modals": [],
		"nav": []
	};

	var defaultUser = {//default user object for user who isn't logged in (no cookie is stored for this user)
		"_id": "Guest",
		"permission": 1,
		"token": "",
		"info":{
			"fName": "Guest",
			"lName": "",
			"team": 0
		},
		"prefs": {
			"fade": true,
			"verbose": true
		}
	};

function fixFavicon() { //fixes favicon bug in firefox
	$('#favicon').remove();
	$('<link href="favicon.ico" rel="shortcut icon" id="favicon"/>').appendTo('head');
}

$(document).ready(function() {
	
	$('a[title]').tipsy();
	$('label[title]').tipsy();
	$('button[title]').tipsy();
	$('input[title]').tipsy({
		trigger: 'focus',
		gravity: 'w'
	});
	$('.toggle-container[title]').tipsy({
		trigger: 'hover',
		gravity: 'w'
	});

	if (eatCookie('user') !== '') {
		window.user = eval('(' + eatCookie('user') + ')');
		updateUserBar();//userbar is setup for guest by default
	} else {
		window.user = defaultUser;
	}

	buildCache();
	nav();
	
	//add error message for old browsers
});

function buildCache() {
	var len = pages.length;

	for (var i = 0; i < len; i++) {
		cache.nav.push(pages[i].name);
		for (var e in pages[i].subpages) {
			cache.subpages.push(e);
		}
		for (var e in pages[i].modals) {
			cache.modals.push(e);
		}
	}
	cache.subpages = '.' + cache.subpages.join("-c, .") + '-c';
	cache.modals = '.' + cache.modals.join("-c, .") + '-c';
	cache.nav = '.' + cache.nav.join("-n, .") + '-n';
}

window.onpopstate = function(event) {
	// if nav() is failing, check browser support for this
	console.log(event);
	fixFavicon(); //remove this?????
	nav();
}

function nav() {
	/*
	Navigation Function:
		this function handles all page transitions and applies all page specific options
		page specific options and data needed for displaying pages is stored in the JSON object 'pages'
		open login modal if needed for page
	*/

	if (location.hash.substring(1) == "") {
		location.hash = '#front-page';//default page
		return;
	}

	prev.index = current.index;
	prev.type = current.type;
	prev.lastSub = current.lastSub;
	prev.subpage = current.subpage;

	if (current.subpage == location.hash.substring(1) && current.subpage != "") {
		return;
	}

	current.subpage = location.hash.substring(1).toLowerCase();

	current.index = '';

	var len = pages.length;
	for (var i = 0; i < len; i++) { //TODO: add catching?
		if (typeof pages[i].subpages[current.subpage] !== 'undefined') {
			current.index = i;
			current.type = 'subpages';
			current.lastSub = current.subpage;
			break;
		}
	}

	if (current.index === '') { // TODO: merge with subpage search ?
		for (var i = 0; i < len; i++) {
			if (typeof pages[i].modals[current.subpage] !== 'undefined') {
				current.index = i;
				current.type = 'modals';
				break;
			}
		}
	}

	if (current.index === '') { //page cannot be found, select default page
		window.location = '#front-page';//default page
		return;
	}
	
	//check if page has been downloaded yet (add functionality later)
	//download if it hasn't been

	var fadetime = 500;

	if (prev.subpage == "") { // if this is the first page
		if (current.type == 'modals') {
			//fade in page
			$('.front-page-c').fadeIn(fadetime / 4);
			
			//show navbar
			//$(cache.nav).css('display','none'); - nothing is shown in the beginning
			$('.home-n').css('display','inline');
			$('#front-page-r').attr('checked', true);
			
			//set variables
			current.lastSub = 'front-page';
			prev.index = 2;
			prev.subpage = 'front-page';
		}
		prev.type = "subpages";
	}

	document.title = pages[current.index]['full-name'].replace(/\-/,' ').titleCase() + ' - ' + current['subpage'].replace(/\-/g,' ').titleCase();
	document.getElementById('body').style.minWidth = pages[current.index].minWidth;
	document.getElementById('progressbar').style.display = pages[current.index].progressbar;


	//start page changers
	if (current.type == "subpages"){ //sub-pages
		
		//change navbar (no fade)
		$(cache.nav).css('display','none');
		$('.' + pages[current.index].name + '-n').css('display','inline');
		$('#' + current.subpage + '-r').attr('checked', true);

		if (prev.type == 'subpages') { //sub-pages
			if(user.prefs.fade == true){
				$(cache.subpages).fadeOut(fadetime).promise().done(function() {
					$('.' + current.subpage + '-c').fadeIn(fadetime);
				});
			} else {
				$(cache.subpages).css('display','none');
				$('.' + current.subpage + '-c').css('display','inline');
			}
		} else { //modals
			if (prev.lastSub == current.subpage) { //don't fade out sub-page if is is already under the modal
				if(user.prefs.fade == true){
					$('#overlay, #modal-container, ' + cache.modals).fadeOut(fadetime);
				} else {
					$('#overlay, #modal-container, ' + cache.modals).css('display','none');
				}
			} else {
				if(user.prefs.fade == true){
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
			if(user.prefs.fade == true){
				$(cache['modals']).hide().promise().done(function() {
					$('#overlay').fadeIn(40);
					$('.' + current.subpage + '-c, #modal-container').fadeIn(fadetime);
				});
			} else {
				$(cache['modals']).css('display','none');
				$('#overlay, .' + current.subpage + '-c, #modal-container').css('display','block');
			}
		} else { //modals
			if(user.prefs.fade == true){
				$(cache.modals).fadeOut(fadetime).promise().done(function() {
					$('.' + current.subpage + '-c, #modal-container').fadeIn(fadetime);
				});
			} else {
				$(cache.modals).css('display','none');
				$('.' + current.subpage + '-c, #modal-container').css('display','inline');
			}
		}
	}
	
	if(pages[current.index][current.type][current.subpage]['login-required'] == true && eatCookie('user') == ''){
		setTimeout("window.location = '#login'", fadetime + 1);
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
	//CONSIDER expanding the bottom code to work on all page types
	if(typeof pages[current.index]['modals'][current.subpage]['onClose'] !== 'undefined' && typeof(runScript) === 'undefined'){
		eval(pages[current.index]['modals'][current.subpage]['onClose']);
	}

	window.location = '#' + current.lastSub;
}

//Cookie Handling Functions
	function bakeCookie(name, value) {
		var expires = new Date();
		expires.setTime(expires.getTime() + (15552000000));
		document.cookie = name + "=" + value + "; expires=" + expires.toGMTString() + "; path=/";
	}

	function eatCookie(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') c = c.substring(1);
			if (c.indexOf(nameEQ) == 0) {
				var cookieValue = c.substring(nameEQ.length);
				break;
			} else
			var cookieValue = "";
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

	if (user._id == '') {
		$('#jGrowl-container').jGrowl('scoutID is blank', {
			theme: 'error'
		});
	} else if (pword == '') {
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
		} else if (json != false) {
			$('#jGrowl-container').jGrowl('server did not respond properly', {
				theme: 'error'
			});
		}
	}
	window.location = '#login';//will only be run at error due to above return
}

function callLogout(){//tells server to logout & runs logout function
	post('process.php','{"request":"logout"}');
	//recheck current page in navbar, radio button hasn't been set yet so timeout is needed
	setTimeout("$('#' + current.subpage + '-r').attr('checked', true)",1);
	console.log(current.subpage);
	logout();
}

function logout() {	//just removes the cookie & user object
	//must be separate from other functions so it can be called from script returned by post() or manually by user

	window.user = defaultUser;//reset to generic user object

	bakeCookie('user', '');//remove user object cookie
	
	updateUserBar();
	
	if(pages[current.index][current.type][current.subpage]['login-required'] == true){
		window.location = '#login';
	}
}

function updateUserBar(){//also updates account modal
	var loginLabel = document.getElementById('login-r-label')

	if(eatCookie('user') != ''){//logged in
		$('#login').css('display','none');
		$('#logout').css('display','inline');
		loginLabel.setAttribute('original-title','Logout');
		loginLabel.setAttribute('onclick',"callLogout()");
	} else {//not logged in
		$('#logout').css('display','none');
		$('#login').css('display','inline');
		loginLabel.setAttribute('original-title','Login');
		loginLabel.setAttribute('onclick',"window.location = '#login'");
	}

	document.getElementById('scoutName').innerHTML = $.trim(user.info.fName + ' ' + user.info.lName);

	//account modal
	$('#fadeEffects').toggleSwitch("toggle", user.prefs.fade);
	$('#verboseMode').toggleSwitch("toggle", user.prefs.verbose);
}

function updateUser(key, value){//newObject does not need to be a full user object
	//user = jQuery.extend(true, user, userUpdates);//CONSIDER using this in login so only non-default stuff needs to be sent
	user.prefs[key] = value;
	if(eatCookie('user') != ''){
		bakeCookie('user', $.toJSON(user));
	}
}

function postUserUpdates(){
	console.log('running postUserUpdates');
	if(eatCookie('user') != ''){//only run if logged in
		post('process.php', '{"request": "updateUser"}');//PHP gets user object from cookie
	}
	modalClose(false);
	//TODO: add checking to see if user object is different
}

//general functions
String.prototype.titleCase = function () {
	return this.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
};

function limitInput(e, limit) { //used for limiting form input
	var unicode = e.charCode ? e.charCode : e.keyCode
	if (unicode != 8 && unicode != 9 && unicode != 37 && unicode != 39) { //if the key isn't the backspace key or tab or l/r arrow
		if ((unicode < 48 || unicode > 57) && limit == 'number') { //if not a number
			return false //disable key press
		}

		if ((unicode < 65 || unicode > 90) && (unicode < 97 || unicode > 122) && limit == 'letter') { //if not a letter
			return false //disable key press
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

function post(filename, json) {
	/*
	this function handles:
		all interfacing w/ server via AJAX
		
	json.globalError: holds type of globalError (which determines action), error text is still in json.error
	*/
	var ajax = $.ajax({
		type: "POST",
		url: filename,
		data: 'data=' + json,
		async: false,
		success: function() {

		},
		error: function() {
			$('#jGrowl-container').jGrowl('AJAX Error Code: ' + xmlhttp.status + '<br />Request was not successful.', {
				sticky: true,
				theme: 'error'
			});
		}
	});
	json = eval("(" + ajax.responseText + ")");
	console.log(json);
	
	if(json.script){//script must be run before error returns (like for logout function)
		eval(json.script);
	}
	
	if(json.error){
		$('#jGrowl-container').jGrowl('error: ' + json.error, {
			theme: 'error'
		});
		return false; //this means error
	}
	
	if(json.message && user.prefs.verbose == true){
		$('#jGrowl-container').jGrowl('success: ' + json.message, {
			theme: 'message'
		});
		delete json.message;
	}
	
	return json;//if nothing is returned assume error
}


//TODO: replace with something better, like downloadify or just a modal
/*
function WriteToWindow() {
	top.consoleRef = window.open('', 'myconsole', 'width=350,height=250,menubar=0,toolbar=1,status=0,scrollbars=1,resizable=1');
	//TODO: fix link to style sheet, or replace completely
	top.consoleRef.document.write('<html><head><title>Scouting Data</title></head><body bgcolor=white onLoad="self.focus()"><textarea style="width:100%; height:100%;">' + writetext + '</textarea></body></html>')
	top.consoleRef.document.close()
}
*/

//Libraries

//Tipsy
	(function($) {

		function maybeCall(thing, ctx) {
			return (typeof thing == 'function') ? (thing.call(ctx)) : thing;
		};

		function Tipsy(element, options) {
			this.$element = $(element);
			this.options = options;
			this.enabled = true;
			this.fixTitle();
		};

		Tipsy.prototype = {
			show: function() {
				var title = this.getTitle();
				if (title && this.enabled) {
					var $tip = this.tip();

					$tip.find('.tipsy-inner')[this.options.html ? 'html' : 'text'](title);
					$tip[0].className = 'tipsy'; // reset classname in case of dynamic gravity
					$tip.remove().css({
						top: 0,
						left: 0,
						visibility: 'hidden',
						display: 'block'
					}).prependTo(document.body);

					var pos = $.extend({}, this.$element.offset(), {
						width: this.$element[0].offsetWidth,
						height: this.$element[0].offsetHeight
					});

					var actualWidth = $tip[0].offsetWidth,
						actualHeight = $tip[0].offsetHeight,
						gravity = maybeCall(this.options.gravity, this.$element[0]);

					var tp;
					switch (gravity.charAt(0)) {
					case 'n':
						tp = {
							top: pos.top + pos.height + this.options.offset,
							left: pos.left + pos.width / 2 - actualWidth / 2
						};
						break;
					case 's':
						tp = {
							top: pos.top - actualHeight - this.options.offset,
							left: pos.left + pos.width / 2 - actualWidth / 2
						};
						break;
					case 'e':
						tp = {
							top: pos.top + pos.height / 2 - actualHeight / 2,
							left: pos.left - actualWidth - this.options.offset
						};
						break;
					case 'w':
						tp = {
							top: pos.top + pos.height / 2 - actualHeight / 2,
							left: pos.left + pos.width + this.options.offset
						};
						break;
					}

					if (gravity.length == 2) {
						if (gravity.charAt(1) == 'w') {
							tp.left = pos.left + pos.width / 2 - 15;
						} else {
							tp.left = pos.left + pos.width / 2 - actualWidth + 15;
						}
					}

					$tip.css(tp).addClass('tipsy-' + gravity);
					$tip.find('.tipsy-arrow')[0].className = 'tipsy-arrow tipsy-arrow-' + gravity.charAt(0);
					if (this.options.className) {
						$tip.addClass(maybeCall(this.options.className, this.$element[0]));
					}

					if (this.options.fade) {
						$tip.stop().css({
							opacity: 0,
							display: 'block',
							visibility: 'visible'
						}).animate({
							opacity: this.options.opacity
						}, this.options.fadeInTime);
					} else {
						$tip.css({
							visibility: 'visible',
							opacity: this.options.opacity
						});
					}
				}
			},

			hide: function() {
				if (this.options.fade) {
					this.tip().stop().fadeOut(this.options.fadeOutTime, function() {
						$(this).remove();
					});
				} else {
					this.tip().remove();
				}
			},

			fixTitle: function() {
				var $e = this.$element;
				if ($e.attr('title') || typeof($e.attr('original-title')) != 'string') {
					$e.attr('original-title', $e.attr('title') || '').removeAttr('title');
				}
			},

			getTitle: function() {
				var title, $e = this.$element,
					o = this.options;
				this.fixTitle();
				var title, o = this.options;
				if (typeof o.title == 'string') {
					title = $e.attr(o.title == 'title' ? 'original-title' : o.title);
				} else if (typeof o.title == 'function') {
					title = o.title.call($e[0]);
				}
				title = ('' + title).replace(/(^\s*|\s*$)/, "");
				return title || o.fallback;
			},

			tip: function() {
				if (!this.$tip) {
					this.$tip = $('<div class="tipsy"></div>').html('<div class="tipsy-arrow"></div><div class="tipsy-inner"></div>');
				}
				return this.$tip;
			},

			validate: function() {
				if (!this.$element[0].parentNode) {
					this.hide();
					this.$element = null;
					this.options = null;
				}
			},

			enable: function() {
				this.enabled = true;
			},
			disable: function() {
				this.enabled = false;
			},
			toggleEnabled: function() {
				this.enabled = !this.enabled;
			}
		};

		$.fn.tipsy = function(options) {

			if (options === true) {
				return this.data('tipsy');
			} else if (typeof options == 'string') {
				var tipsy = this.data('tipsy');
				if (tipsy) tipsy[options]();
				return this;
			}

			options = $.extend({}, $.fn.tipsy.defaults, options);

			function get(ele) {
				var tipsy = $.data(ele, 'tipsy');
				if (!tipsy) {
					tipsy = new Tipsy(ele, $.fn.tipsy.elementOptions(ele, options));
					$.data(ele, 'tipsy', tipsy);
				}
				return tipsy;
			}

			function enter() {
				var tipsy = get(this);
				tipsy.hoverState = 'in';
				if (options.delayIn == 0) {
					tipsy.show();
				} else {
					tipsy.fixTitle();
					setTimeout(function() {
						if (tipsy.hoverState == 'in') tipsy.show();
					}, options.delayIn);
				}
			};

			function leave() {
				var tipsy = get(this);
				tipsy.hoverState = 'out';
				if (options.delayOut == 0) {
					tipsy.hide();
				} else {
					setTimeout(function() {
						if (tipsy.hoverState == 'out') tipsy.hide();
					}, options.delayOut);
				}
			};

			if (!options.live) this.each(function() {
				get(this);
			});

			if (options.trigger != 'manual') {
				var binder = options.live ? 'live' : 'bind',
					eventIn = options.trigger == 'hover' ? 'mouseenter' : 'focus',
					eventOut = options.trigger == 'hover' ? 'mouseleave' : 'blur';
				this[binder](eventIn, enter)[binder](eventOut, leave);
			}
			return this;
		};

		$.fn.tipsy.defaults = {
			className: null,
			delayIn: 0,
			delayOut: 0,
			fade: true,
			fadeInTime: 500,
			fadeOutTime: 100,
			fallback: '',
			gravity: 'n',
			html: false,
			live: false,
			offset: 0,
			opacity: 0.8,
			title: 'title',
			trigger: 'hover'
		};

		// Overwrite this method to provide options on a per-element basis.
		// For example, you could store the gravity in a 'tipsy-gravity' attribute:
		// return $.extend({}, options, {gravity: $(ele).attr('tipsy-gravity') || 'n' });
		// (remember - do not modify 'options' in place!)
		$.fn.tipsy.elementOptions = function(ele, options) {
			return $.metadata ? $.extend({}, options, $(ele).metadata()) : options;
		};

		$.fn.tipsy.autoNS = function() {
			return $(this).offset().top > ($(document).scrollTop() + $(window).height() / 2) ? 's' : 'n';
		};

		$.fn.tipsy.autoWE = function() {
			return $(this).offset().left > ($(document).scrollLeft() + $(window).width() / 2) ? 'e' : 'w';
		};

		/**
		 * yields a closure of the supplied parameters, producing a function that takes
		 * no arguments and is suitable for use as an autogravity function like so:
		 *
		 * @param margin (int) - distance from the viewable region edge that an
		 *		element should be before setting its tooltip's gravity to be away
		 *		from that edge.
		 * @param prefer (string, e.g. 'n', 'sw', 'w') - the direction to prefer
		 *		if there are no viewable region edges effecting the tooltip's
		 *		gravity. It will try to vary from this minimally, for example,
		 *		if 'sw' is preferred and an element is near the right viewable
		 *		region edge, but not the top edge, it will set the gravity for
		 *		that element's tooltip to be 'se', preserving the southern
		 *		component.
		 */
		$.fn.tipsy.autoBounds = function(margin, prefer) {
			return function() {
				var dir = {
					ns: prefer[0],
					ew: (prefer.length > 1 ? prefer[1] : false)
				},
					boundTop = $(document).scrollTop() + margin,
					boundLeft = $(document).scrollLeft() + margin,
					$this = $(this);

				if ($this.offset().top < boundTop) dir.ns = 'n';
				if ($this.offset().left < boundLeft) dir.ew = 'w';
				if ($(window).width() + $(document).scrollLeft() - $this.offset().left < margin) dir.ew = 'e';
				if ($(window).height() + $(document).scrollTop() - $this.offset().top < margin) dir.ns = 's';

				return dir.ns + (dir.ew ? dir.ew : '');
			}
		};
	})(jQuery);


//jQuery selectBox - https://github.com/claviska/jquery-selectBox
	if (jQuery)(function($) {

		$.extend($.fn, {

			selectBox: function(method, data) {

				var typeTimer, typeSearch = '',
					isMac = navigator.platform.match(/mac/i);

				//
				// Private methods
				//
				var init = function(select, data) {

					var options;

					// Disable for iOS devices (their native controls are more suitable for a touch device)
					if (navigator.userAgent.match(/iPad|iPhone|Android|IEMobile|BlackBerry/i)) return false;

					// Element must be a select control
					if (select.tagName.toLowerCase() !== 'select') return false;

					select = $(select);
					if (select.data('selectBox-control')) return false;

					var control = $('<a class="selectBox" />'),
						inline = select.attr('multiple') || parseInt(select.attr('size')) > 1;

					var settings = data || {};

					control.width(select.outerWidth()).addClass(select.attr('class')).attr('title', select.attr('title') || '').attr('tabindex', parseInt(select.attr('tabindex'))).css('display', 'inline-block').bind('focus.selectBox', function() {
						if (this !== document.activeElement) $(document.activeElement).blur();
						if (control.hasClass('selectBox-active')) return;
						control.addClass('selectBox-active');
						select.trigger('focus');
					}).bind('blur.selectBox', function() {
						if (!control.hasClass('selectBox-active')) return;
						control.removeClass('selectBox-active');
						select.trigger('blur');
					});

					if (!$(window).data('selectBox-bindings')) {
						$(window).data('selectBox-bindings', true).bind('scroll.selectBox', hideMenus).bind('resize.selectBox', hideMenus);
					}

					if (select.attr('disabled')) control.addClass('selectBox-disabled');

					// Focus on control when label is clicked
					select.bind('click.selectBox', function(event) {
						control.focus();
						event.preventDefault();
					});

					// Generate control
					if (inline) {

						//
						// Inline controls
						//
						options = getOptions(select, 'inline');

						control.append(options).data('selectBox-options', options).addClass('selectBox-inline selectBox-menuShowing').bind('keydown.selectBox', function(event) {
							handleKeyDown(select, event);
						}).bind('keypress.selectBox', function(event) {
							handleKeyPress(select, event);
						}).bind('mousedown.selectBox', function(event) {
							if ($(event.target).is('A.selectBox-inline')) event.preventDefault();
							if (!control.hasClass('selectBox-focus')) control.focus();
						}).insertAfter(select);

						// Auto-height based on size attribute
						if (!select[0].style.height) {

							var size = select.attr('size') ? parseInt(select.attr('size')) : 5;

							// Draw a dummy control off-screen, measure, and remove it
							var tmp = control.clone().removeAttr('id').css({
								position: 'absolute',
								top: '-9999em'
							}).show().appendTo('body');
							tmp.find('.selectBox-options').html('<li><a>\u00A0</a></li>');
							optionHeight = parseInt(tmp.find('.selectBox-options A:first').html('&nbsp;').outerHeight());
							tmp.remove();

							control.height(optionHeight * size);

						}

						disableSelection(control);

					} else {

						//
						// Dropdown controls
						//
						var label = $('<span class="selectBox-label" />'),
							arrow = $('<span class="selectBox-arrow" />');

						// Update label
						label.attr('class', getLabelClass(select)).text(getLabelText(select));

						options = getOptions(select, 'dropdown');
						options.appendTo('BODY');

						control.data('selectBox-options', options).addClass('selectBox-dropdown').append(label).append(arrow).bind('mousedown.selectBox', function(event) {
							if (control.hasClass('selectBox-menuShowing')) {
								hideMenus();
							} else {
								event.stopPropagation();
								// Webkit fix to prevent premature selection of options
								options.data('selectBox-down-at-x', event.screenX).data('selectBox-down-at-y', event.screenY);
								showMenu(select);
							}
						}).bind('keydown.selectBox', function(event) {
							handleKeyDown(select, event);
						}).bind('keypress.selectBox', function(event) {
							handleKeyPress(select, event);
						}).insertAfter(select);

						// Set label width
						var labelWidth = control.width() - arrow.outerWidth() - parseInt(label.css('paddingLeft')) - parseInt(label.css('paddingLeft'));
						label.width(labelWidth);

						disableSelection(control);

					}

					// Store data for later use and show the control
					select.addClass('selectBox').data('selectBox-control', control).data('selectBox-settings', settings).hide();

				};


				var getOptions = function(select, type) {
					var options;

					switch (type) {

					case 'inline':


						options = $('<ul class="selectBox-options" />');

						if (select.find('OPTGROUP').length) {

							select.find('OPTGROUP').each(function() {

								var optgroup = $('<li class="selectBox-optgroup" />');
								optgroup.text($(this).attr('label'));
								options.append(optgroup);

								generateOptions($(this).find('OPTION'), options);

							});

						} else {
							generateOptions(select.find('OPTION'), options);
						}

						options.find('A').bind('mouseover.selectBox', function(event) {
							addHover(select, $(this).parent());
						}).bind('mouseout.selectBox', function(event) {
							removeHover(select, $(this).parent());
						}).bind('mousedown.selectBox', function(event) {
							event.preventDefault(); // Prevent options from being "dragged"
							if (!select.selectBox('control').hasClass('selectBox-active')) select.selectBox('control').focus();
						}).bind('mouseup.selectBox', function(event) {
							hideMenus();
							selectOption(select, $(this).parent(), event);
						});

						disableSelection(options);

						return options;

					case 'dropdown':
						options = $('<ul class="selectBox-dropdown-menu selectBox-options" />');

						if (select.find('OPTGROUP').length) {

							select.find('OPTGROUP').each(function() {

								var optgroup = $('<li class="selectBox-optgroup" />');
								optgroup.text($(this).attr('label'));
								options.append(optgroup);
								generateOptions($(this).find('OPTION'), options);

							});

						} else {

							if (select.find('OPTION').length > 0) {
								generateOptions(select.find('OPTION'), options);
							} else {
								options.append('<li>\u00A0</li>');
							}

						}

						options.data('selectBox-select', select).css('display', 'none').appendTo('BODY').find('A').bind('mousedown.selectBox', function(event) {
							event.preventDefault(); // Prevent options from being "dragged"
							if (event.screenX === options.data('selectBox-down-at-x') && event.screenY === options.data('selectBox-down-at-y')) {
								options.removeData('selectBox-down-at-x').removeData('selectBox-down-at-y');
								hideMenus();
							}
						}).bind('mouseup.selectBox', function(event) {
							if (event.screenX === options.data('selectBox-down-at-x') && event.screenY === options.data('selectBox-down-at-y')) {
								return;
							} else {
								options.removeData('selectBox-down-at-x').removeData('selectBox-down-at-y');
							}
							selectOption(select, $(this).parent());
							hideMenus();
						}).bind('mouseover.selectBox', function(event) {
							addHover(select, $(this).parent());
						}).bind('mouseout.selectBox', function(event) {
							removeHover(select, $(this).parent());
						});

						// Inherit classes for dropdown menu
						var classes = select.attr('class') || '';
						if (classes !== '') {
							classes = classes.split(' ');
							for (var i in classes) options.addClass(classes[i] + '-selectBox-dropdown-menu');
						}

						disableSelection(options);

						return options;

					}

				};


				var getLabelClass = function(select) {
					var selected = $(select).find('OPTION:selected');
					return ('selectBox-label ' + (selected.attr('class') || '')).replace(/\s+$/, '');
				};


				var getLabelText = function(select) {
					var selected = $(select).find('OPTION:selected');
					return selected.text() || '\u00A0';
				};


				var setLabel = function(select) {
					select = $(select);
					var control = select.data('selectBox-control');
					if (!control) return;
					control.find('.selectBox-label').attr('class', getLabelClass(select)).text(getLabelText(select));
				};


				var destroy = function(select) {

					select = $(select);
					var control = select.data('selectBox-control');
					if (!control) return;
					var options = control.data('selectBox-options');

					options.remove();
					control.remove();
					select.removeClass('selectBox').removeData('selectBox-control').data('selectBox-control', null).removeData('selectBox-settings').data('selectBox-settings', null).show();

				};


				var refresh = function(select) {
					select = $(select);
					select.selectBox('options', select.html());
				};


				var showMenu = function(select) {

					select = $(select);
					var control = select.data('selectBox-control'),
						settings = select.data('selectBox-settings'),
						options = control.data('selectBox-options');
					if (control.hasClass('selectBox-disabled')) return false;

					hideMenus();

					var borderBottomWidth = isNaN(control.css('borderBottomWidth')) ? 0 : parseInt(control.css('borderBottomWidth'));

					// Menu position
					options.width(control.innerWidth()).css({
						top: control.offset().top + control.outerHeight() - borderBottomWidth,
						left: control.offset().left
					});

					// Show menu
					switch (settings.menuTransition) {

					case 'fade':
						options.fadeIn(settings.menuSpeed);
						break;

					case 'slide':
						options.slideDown(settings.menuSpeed);
						break;

					default:
						options.show(settings.menuSpeed);
						break;

					}

					// Center on selected option
					var li = options.find('.selectBox-selected:first');
					keepOptionInView(select, li, true);
					addHover(select, li);

					control.addClass('selectBox-menuShowing');

					$(document).bind('mousedown.selectBox', function(event) {
						if ($(event.target).parents().andSelf().hasClass('selectBox-options')) return;
						hideMenus();
					});

				};


				var hideMenus = function() {

					if ($(".selectBox-dropdown-menu").length === 0) return;
					$(document).unbind('mousedown.selectBox');

					$(".selectBox-dropdown-menu").each(function() {

						var options = $(this),
							select = options.data('selectBox-select'),
							control = select.data('selectBox-control'),
							settings = select.data('selectBox-settings');

						switch (settings.menuTransition) {

						case 'fade':
							options.fadeOut(settings.menuSpeed);
							break;

						case 'slide':
							options.slideUp(settings.menuSpeed);
							break;

						default:
							options.hide(settings.menuSpeed);
							break;

						}

						control.removeClass('selectBox-menuShowing');

					});

				};


				var selectOption = function(select, li, event) {

					select = $(select);
					li = $(li);
					var control = select.data('selectBox-control'),
						settings = select.data('selectBox-settings');

					if (control.hasClass('selectBox-disabled')) return false;
					if (li.length === 0 || li.hasClass('selectBox-disabled')) return false;

					if (select.attr('multiple')) {

						// If event.shiftKey is true, this will select all options between li and the last li selected
						if (event.shiftKey && control.data('selectBox-last-selected')) {

							li.toggleClass('selectBox-selected');

							var affectedOptions;
							if (li.index() > control.data('selectBox-last-selected').index()) {
								affectedOptions = li.siblings().slice(control.data('selectBox-last-selected').index(), li.index());
							} else {
								affectedOptions = li.siblings().slice(li.index(), control.data('selectBox-last-selected').index());
							}

							affectedOptions = affectedOptions.not('.selectBox-optgroup, .selectBox-disabled');

							if (li.hasClass('selectBox-selected')) {
								affectedOptions.addClass('selectBox-selected');
							} else {
								affectedOptions.removeClass('selectBox-selected');
							}

						} else if ((isMac && event.metaKey) || (!isMac && event.ctrlKey)) {
							console.log(isMac);
							li.toggleClass('selectBox-selected');
						} else {
							li.siblings().removeClass('selectBox-selected');
							li.addClass('selectBox-selected');
						}

					} else {
						li.siblings().removeClass('selectBox-selected');
						li.addClass('selectBox-selected');
					}

					if (control.hasClass('selectBox-dropdown')) {
						control.find('.selectBox-label').text(li.text());
					}

					// Update original control's value
					var i = 0,
						selection = [];
					if (select.attr('multiple')) {
						control.find('.selectBox-selected A').each(function() {
							selection[i++] = $(this).attr('rel');
						});
					} else {
						selection = li.find('A').attr('rel');
					}

					// Remember most recently selected item
					control.data('selectBox-last-selected', li);

					// Change callback
					if (select.val() !== selection) {
						select.val(selection);
						setLabel(select);
						select.trigger('change');
					}

					return true;

				};


				var addHover = function(select, li) {
					select = $(select);
					li = $(li);
					var control = select.data('selectBox-control'),
						options = control.data('selectBox-options');

					options.find('.selectBox-hover').removeClass('selectBox-hover');
					li.addClass('selectBox-hover');
				};


				var removeHover = function(select, li) {
					select = $(select);
					li = $(li);
					var control = select.data('selectBox-control'),
						options = control.data('selectBox-options');
					options.find('.selectBox-hover').removeClass('selectBox-hover');
				};


				var keepOptionInView = function(select, li, center) {

					if (!li || li.length === 0) return;

					select = $(select);
					var control = select.data('selectBox-control'),
						options = control.data('selectBox-options'),
						scrollBox = control.hasClass('selectBox-dropdown') ? options : options.parent(),
						top = parseInt(li.offset().top - scrollBox.position().top),
						bottom = parseInt(top + li.outerHeight());

					if (center) {
						scrollBox.scrollTop(li.offset().top - scrollBox.offset().top + scrollBox.scrollTop() - (scrollBox.height() / 2));
					} else {
						if (top < 0) {
							scrollBox.scrollTop(li.offset().top - scrollBox.offset().top + scrollBox.scrollTop());
						}
						if (bottom > scrollBox.height()) {
							scrollBox.scrollTop((li.offset().top + li.outerHeight()) - scrollBox.offset().top + scrollBox.scrollTop() - scrollBox.height());
						}
					}

				};


				var handleKeyDown = function(select, event) {

					//
					// Handles open/close and arrow key functionality
					//
					select = $(select);
					var control = select.data('selectBox-control'),
						options = control.data('selectBox-options'),
						settings = select.data('selectBox-settings'),
						totalOptions = 0,
						i = 0;

					if (control.hasClass('selectBox-disabled')) return;

					switch (event.keyCode) {

					case 8:
						// backspace
						event.preventDefault();
						typeSearch = '';
						break;

					case 9:
						// tab
					case 27:
						// esc
						hideMenus();
						removeHover(select);
						break;

					case 13:
						// enter
						if (control.hasClass('selectBox-menuShowing')) {
							selectOption(select, options.find('LI.selectBox-hover:first'), event);
							if (control.hasClass('selectBox-dropdown')) hideMenus();
						} else {
							showMenu(select);
						}
						break;

					case 38:
						// up
					case 37:
						// left
						event.preventDefault();

						if (control.hasClass('selectBox-menuShowing')) {

							var prev = options.find('.selectBox-hover').prev('LI');
							totalOptions = options.find('LI:not(.selectBox-optgroup)').length;
							i = 0;

							while (prev.length === 0 || prev.hasClass('selectBox-disabled') || prev.hasClass('selectBox-optgroup')) {
								prev = prev.prev('LI');
								if (prev.length === 0) {
									if (settings.loopOptions) {
										prev = options.find('LI:last');
									} else {
										prev = options.find('LI:first');
									}
								}
								if (++i >= totalOptions) break;
							}

							addHover(select, prev);
							selectOption(select, prev, event);
							keepOptionInView(select, prev);

						} else {
							showMenu(select);
						}

						break;

					case 40:
						// down
					case 39:
						// right
						event.preventDefault();

						if (control.hasClass('selectBox-menuShowing')) {

							var next = options.find('.selectBox-hover').next('LI');
							totalOptions = options.find('LI:not(.selectBox-optgroup)').length;
							i = 0;

							while (next.length === 0 || next.hasClass('selectBox-disabled') || next.hasClass('selectBox-optgroup')) {
								next = next.next('LI');
								if (next.length === 0) {
									if (settings.loopOptions) {
										next = options.find('LI:first');
									} else {
										next = options.find('LI:last');
									}
								}
								if (++i >= totalOptions) break;
							}

							addHover(select, next);
							selectOption(select, next, event);
							keepOptionInView(select, next);

						} else {
							showMenu(select);
						}
						break;
					}
				};

				var handleKeyPress = function(select, event) {

					// Handles type-to-find functionality
					select = $(select);
					var control = select.data('selectBox-control'),
						options = control.data('selectBox-options');
					if (control.hasClass('selectBox-disabled')) return;
					switch (event.keyCode) {
					case 9:
						// tab
					case 27:
						// esc
					case 13:
						// enter
					case 38:
						// up
					case 37:
						// left
					case 40:
						// down
					case 39:
						// right
						// Don't interfere with the keydown event!
						break;
					default:
						// Type to find
						if (!control.hasClass('selectBox-menuShowing')) showMenu(select);
						event.preventDefault();
						clearTimeout(typeTimer);
						typeSearch += String.fromCharCode(event.charCode || event.keyCode);
						options.find('A').each(function() {
							if ($(this).text().substr(0, typeSearch.length).toLowerCase() === typeSearch.toLowerCase()) {
								addHover(select, $(this).parent());
								keepOptionInView(select, $(this).parent());
								return false;
							}
						});
						// Clear after a brief pause
						typeTimer = setTimeout(function() {
							typeSearch = '';
						}, 1000);
						break;
					}
				};

				var enable = function(select) {
					select = $(select);
					select.attr('disabled', false);
					var control = select.data('selectBox-control');
					if (!control) return;
					control.removeClass('selectBox-disabled');
				};

				var disable = function(select) {
					select = $(select);
					select.attr('disabled', true);
					var control = select.data('selectBox-control');
					if (!control) return;
					control.addClass('selectBox-disabled');
				};

				var setValue = function(select, value) {
					select = $(select);
					select.val(value);
					value = select.val();
					var control = select.data('selectBox-control');
					if (!control) return;
					var settings = select.data('selectBox-settings'),
						options = control.data('selectBox-options');

					// Update label
					setLabel(select);

					// Update control values
					options.find('.selectBox-selected').removeClass('selectBox-selected');
					options.find('A').each(function() {
						if (typeof(value) === 'object') {
							for (var i = 0; i < value.length; i++) {
								if ($(this).attr('rel') == value[i]) {
									$(this).parent().addClass('selectBox-selected');
								}
							}
						} else {
							if ($(this).attr('rel') == value) {
								$(this).parent().addClass('selectBox-selected');
							}
						}
					});
					if (settings.change) settings.change.call(select);
				};

				var setOptions = function(select, options) {

					select = $(select);
					var control = select.data('selectBox-control'),
						settings = select.data('selectBox-settings');

					switch (typeof(data)) {

					case 'string':
						select.html(data);
						break;

					case 'object':
						select.html('');
						for (var i in data) {
							if (data[i] === null) continue;
							if (typeof(data[i]) === 'object') {
								var optgroup = $('<optgroup label="' + i + '" />');
								for (var j in data[i]) {
									optgroup.append('<option value="' + j + '">' + data[i][j] + '</option>');
								}
								select.append(optgroup);
							} else {
								var option = $('<option value="' + i + '">' + data[i] + '</option>');
								select.append(option);
							}
						}
						break;
					}

					if (!control) return;

					// Remove old options
					control.data('selectBox-options').remove();

					// Generate new options
					var type = control.hasClass('selectBox-dropdown') ? 'dropdown' : 'inline',
						options = getOptions(select, type);
					control.data('selectBox-options', options);

					switch (type) {
					case 'inline':
						control.append(options);
						break;
					case 'dropdown':
						// Update label
						setLabel(select);
						$("BODY").append(options);
						break;
					}
				};

				var disableSelection = function(selector) {
					$(selector).css('MozUserSelect', 'none').bind('selectstart', function(event) {
						event.preventDefault();
					});
				};

				var generateOptions = function(originalOptions, options) {
					originalOptions.each(function() {
						var self = $(this);
						var li = $('<li />'),
							a = $('<a />');
						li.addClass(self.attr('class'));
						li.data(self.data());
						a.attr('rel', self.val()).text(self.text());
						li.append(a);
						if (self.attr('disabled')) li.addClass('selectBox-disabled');
						if (self.attr('selected')) li.addClass('selectBox-selected');
						options.append(li);
					});
				};

				// Public methods
				switch (method) {
				case 'control':
					return $(this).data('selectBox-control');
				case 'settings':
					if (!data) return $(this).data('selectBox-settings');
					$(this).each(function() {
						$(this).data('selectBox-settings', $.extend(true, $(this).data('selectBox-settings'), data));
					});
					break;
				case 'options':
					$(this).each(function() {
						setOptions(this, data);
					});
					break;
				case 'value':
					// Empty string is a valid value
					if (data === undefined) return $(this).val();
					$(this).each(function() {
						setValue(this, data);
					});
					break;
				case 'refresh':
					$(this).each(function() {
						refresh(this);
					});
					break;
				case 'enable':
					$(this).each(function() {
						enable(this);
					});
					break;
				case 'disable':
					$(this).each(function() {
						disable(this);
					});
					break;
				case 'destroy':
					$(this).each(function() {
						destroy(this);
					});
					break;
				default:
					$(this).each(function() {
						init(this, method);
					});
					break;
				}
				return $(this);
			}
		});
	})(jQuery);


//ToggleJS
	//TODO: make toggle change with l/r arrow keys when on keyboard focus
	(function($) {
		// set default options
		$.toggleSwitch = {
			version: "1.0.03",
			setDefaults: function(options) {
				$.extend(defaults, options);
			}
		};

		$.fn.toggleSwitch = function(options) {
			var method = typeof arguments[0] == "string" && arguments[0];
			var args = method && Array.prototype.slice.call(arguments, 1) || arguments;
			// get a reference to the first toggle found
			var self = (this.length == 0) ? null : $.data(this[0], "toggle");

			// if a method is supplied, execute it for non-empty results
			if (self && method && this.length) {

				// if request a copy of the object, return it
				if (method.toLowerCase() == "object") return self;
				// if method is defined, run it and return either it's results or the chain
				else if (self[method]) {
					// define a result variable to return to the jQuery chain
					var result;
					this.each(function(i) {
						// apply the method to the current element
						var r = $.data(this, "toggle")[method].apply(self, args);
						// if first iteration we need to check if we're done processing or need to add it to the jquery chain
						if (i == 0 && r) {
							// if this is a jQuery item, we need to store them in a collection
							if ( !! r.jquery) {
								result = $([]).add(r);
								// otherwise, just store the result and stop executing
							} else {
								result = r;
								// since we're a non-jQuery item, just cancel processing further items
								return false;
							}
							// keep adding jQuery objects to the results
						} else if ( !! r && !! r.jquery) {
							result = result.add(r);
						}
					});

					// return either the results (which could be a jQuery object) or the original chain
					return result || this;
					// everything else, return the chain
				} else
				return this;
				// initializing request (only do if toggle not already initialized)
			} else {
				// create a new toggle for each object found
				return this.each(function() {
					new toggle(this, options);
				});
			};
		};

		// count instances
		var counter = 0;
		// detect iPhone
		$.browser.iphone = (navigator.userAgent.toLowerCase().indexOf("iphone") > -1);

		var toggle = function(input, options) {
			var self = this,
				$input = $(input),
				id = ++counter,
				disabled = false,
				width = {},
				mouse = {
					dragging: false,
					clicked: null
				},
				dragStart = {
					position: null,
					offset: null,
					time: null
				}
				// make a copy of the options and use the metadata if provided
				,
				options = $.extend({}, defaults, options, ( !! $.metadata ? $input.metadata() : {}))
				// check to see if we're using the default labels
				,
				bDefaultLabelsUsed = (options.labelOn == ON && options.labelOff == OFF)
				// set valid field types
				,
				allow = ":checkbox, :radio";

			// only do for checkboxes buttons, if matches inside that node
			if (!$input.is(allow)) return $input.find(allow).toggle(options);
			// if toggle already exists, stop processing
			else if ($.data($input[0], "toggle")) return;

			// store a reference to this marquee
			$.data($input[0], "toggle", self);

			// if using the "auto" setting, then don't resize handle or container if using the default label (since we'll trust the CSS)
			if (options.resizeHandle == "auto") options.resizeHandle = !bDefaultLabelsUsed;
			if (options.resizeContainer == "auto") options.resizeContainer = !bDefaultLabelsUsed;

			// toggles the state of a button (or can turn on/off)
			this.toggle = function(t) {
				var toggle = (arguments.length > 0) ? t : !$input[0].checked;
				$input.attr("checked", toggle).trigger("change");
			};

			// disable/enable the control
			this.disable = function(t) {
				var toggle = (arguments.length > 0) ? t : !disabled;
				// mark the control disabled
				disabled = toggle;
				// mark the input disabled
				$input.attr("disabled", toggle);
				// set the diabled styles
				$container[toggle ? "addClass" : "removeClass"](options.classDisabled);
				// run callback
				if ($.isFunction(options.disable)) options.disable.apply(self, [disabled, $input, options]);
			};

			// repaint the button
			this.repaint = function() {
				positionHandle();
			};

			// this will destroy the toggle style
			this.destroy = function() {
				// remove behaviors
				$([$input[0], $container[0]]).unbind(".toggle");
				$(document).unbind(".toggle_" + id);
				// move the checkbox to it's original location
				$container.after($input).remove();
				// kill the reference
				$.data($input[0], "toggle", null);
				// run callback
				if ($.isFunction(options.destroy)) options.destroy.apply(self, [$input, options]);
			};

			$input.wrap('<div title="' + $input[0].title + '" class="' + $.trim(options.classContainer + ' ' + options.className) + '" />').after('<div class="' + options.classHandle + '"></div>' + '<div class="' + options.classLabelOff + '"><span><label>' + options.labelOff + '</label></span></div>' + '<div class="' + options.classLabelOn + '"><span><label>' + options.labelOn + '</label></span></div>');

			var $container = $input.parent(),
				$handle = $input.siblings("." + options.classHandle),
				$offlabel = $input.siblings("." + options.classLabelOff),
				$offspan = $offlabel.children("span"),
				$onlabel = $input.siblings("." + options.classLabelOn),
				$onspan = $onlabel.children("span");


			// if we need to do some resizing, get the widths only once
			if (options.resizeHandle || options.resizeContainer) {
				width.onspan = $onspan.outerWidth();
				width.offspan = $offspan.outerWidth();
			}

			// automatically resize the handle
			if (options.resizeHandle) {
				width.handle = Math.min(width.onspan, width.offspan);
				$handle.css("width", width.handle);
			} else {
				width.handle = $handle.width();
			}

			// automatically resize the control
			if (options.resizeContainer) {
				width.container = (Math.max(width.onspan, width.offspan) + width.handle + 20);
				$container.css("width", width.container);
				// adjust the off label to match the new container size
				$offlabel.css("width", width.container);
			} else {
				width.container = $container.width();
			}

			var handleRight = width.container - width.handle;

			var positionHandle = function(animate) {
				var checked = $input[0].checked,
					x = (checked) ? handleRight : 0,
					animate = (arguments.length > 0) ? arguments[0] : true;

				if (animate && options.enableFx) {
					$handle.stop().animate({
						left: x
					}, options.duration, options.easing);
					$onlabel.stop().animate({
						width: x + 4
					}, options.duration, options.easing);
					$onspan.stop().animate({
						marginLeft: x - handleRight
					}, options.duration, options.easing);
					$offspan.stop().animate({
						marginRight: -x
					}, options.duration, options.easing);
				} else {
					$handle.css("left", x);
					$onlabel.css("width", x + 4);
					$onspan.css("marginLeft", x - handleRight);
					$offspan.css("marginRight", -x);
				}
			};

			// place the buttons in their default location
			positionHandle(false);

			var getDragPos = function(e) {
				return e.pageX || ((e.originalEvent.changedTouches) ? e.originalEvent.changedTouches[0].pageX : 0);
			};

			// monitor mouse clicks in the container
			$container.bind("mousedown.toggle touchstart.toggle", function(e) {
				// abort if disabled or allow clicking the input to toggle the status (if input is visible)
				if ($(e.target).is(allow) || disabled || (!options.allowRadioUncheck && $input.is(":radio:checked"))) return;

				e.preventDefault();
				mouse.clicked = $handle;
				dragStart.position = getDragPos(e);
				dragStart.offset = dragStart.position - (parseInt($handle.css("left"), 10) || 0);
				dragStart.time = (new Date()).getTime();
				return false;
			});

			// make sure dragging support is enabled
			if (options.enableDrag) {
				// monitor mouse movement on the page
				$(document).bind("mousemove.toggle_" + id + " touchmove.toggle_" + id, function(e) {
					// if we haven't clicked on the container, cancel event
					if (mouse.clicked != $handle) {
						return
					}
					e.preventDefault();

					var x = getDragPos(e);
					if (x != dragStart.offset) {
						mouse.dragging = true;
						$container.addClass(options.classHandleActive);
					}

					// make sure number is between 0 and 1
					var pct = Math.min(1, Math.max(0, (x - dragStart.offset) / handleRight));

					$handle.css("left", pct * handleRight);
					$onlabel.css("width", pct * handleRight + 4);//overcome 3px border radius
					$offspan.css("marginRight", -pct * handleRight);
					$onspan.css("marginLeft", -(1 - pct) * handleRight);
					return false;
				});
			}

			// monitor when the mouse button is released
			$(document).bind("mouseup.toggle_" + id + " touchend.toggle_" + id, function(e) {
				if (mouse.clicked != $handle) {
					return false
				}
				e.preventDefault();

				// track if the value has changed
				var changed = true;

				// if not dragging or click time under a certain millisecond, then just toggle
				if (!mouse.dragging || (((new Date()).getTime() - dragStart.time) < options.clickOffset)) {
					var checked = $input[0].checked;
					$input.attr("checked", !checked);

					// run callback
					if ($.isFunction(options.click)) options.click.apply(self, [!checked, $input, options]);
				} else {
					var x = getDragPos(e);

					var pct = (x - dragStart.offset) / handleRight;
					var checked = (pct >= 0.5);

					// if the value is the same, don't run change event
					if ($input[0].checked == checked) changed = false;

					$input.attr("checked", checked);
				}

				// remove the active handler class
				$container.removeClass(options.classHandleActive);
				mouse.clicked = null;
				mouse.dragging = null;
				// run any change event for the element
				if (changed) $input.trigger("change");
				// if the value didn't change, just reset the handle
				else positionHandle();
				return false;
			});

			// animate when we get a change event
			$input.bind("change.toggle", function() {
				// move handle
				positionHandle();

				// if a radio element, then we must repaint the other elements in it's group to show them as not selected
				if ($input.is(":radio")) {
					var el = $input[0];

					// try to use the DOM to get the grouped elements, but if not in a form get by name attr
					var $radio = $(el.form ? el.form[el.name] : ":radio[name=" + el.name + "]");

					// repaint the radio elements that are not checked
					$radio.filter(":not(:checked)").toggle("repaint");
				}

				// run callback
				if ($.isFunction(options.change)) options.change.apply(self, [$input, options]);
			})
			// if the element has focus, we need to highlight the container
			.bind("focus.toggle", function() {
				$container.addClass(options.classFocus);
			})
			// if the element has focus, we need to highlight the container
			.bind("blur.toggle", function() {
				$container.removeClass(options.classFocus);
			});

			// if a click event is registered, we must register on the checkbox so it's fired if triggered on the checkbox itself
			if ($.isFunction(options.click)) {
				$input.bind("click.toggle", function() {
					options.click.apply(self, [$input[0].checked, $input, options]);
				});
			}

			// if the field is disabled, mark it as such
			if ($input.is(":disabled")) this.disable(true);

			// special behaviors for IE
			if ($.browser.msie) {
				// disable text selection in IE, other browsers are controlled via CSS
				$container.find("*").andSelf().attr("unselectable", "on");
				// IE needs to register to the "click" event to make changes immediately (the change event only occurs on blur)
				$input.bind("click.toggle", function() {
					$input.triggerHandler("change.toggle");
				});
			}

			// run the init callback
			if ($.isFunction(options.init)) options.init.apply(self, [$input, options]);
		};

		var defaults = {
			duration: 200 // the speed of the animation
			,
			easing: "swing" // the easing animation to use
			,
			labelOn: "YES" // the text to show when toggled on
			,
			labelOff: "NO" // the text to show when toggled off
			,
			resizeHandle: "auto" // determines if handle should be resized
			,
			resizeContainer: "auto" // determines if container should be resized
			,
			enableDrag: true // determines if we allow dragging
			,
			enableFx: true // determines if we show animation
			,
			allowRadioUncheck: false // determine if a radio button should be able to be unchecked
			,
			clickOffset: 120 // if millseconds between a mousedown & mouseup event this value, then considered a mouse click
			// define the class statements
			,
			className: "",
			classContainer: "toggle-container",
			classDisabled: "toggle-disabled",
			classFocus: "toggle-focus",
			classLabelOn: "toggle-label-on",
			classLabelOff: "toggle-label-off",
			classHandle: "toggle-handle",
			classHandleActive: "toggle-active-handle"

			// event handlers
			,
			init: null // callback that occurs when a toggle is initialized
			,
			change: null // callback that occurs when the button state is changed
			,
			click: null // callback that occurs when the button is clicked
			,
			disable: null // callback that occurs when the button is disabled/enabled
			,
			destroy: null // callback that occurs when the button is destroyed
		},
			ON = defaults.labelOn,
			OFF = defaults.labelOff;

	})(jQuery);

$(":checkbox").toggleSwitch();

//jGrowl
	(function($) {

		/** Raise jGrowl Notification on a jGrowl Container **/
		$.fn.jGrowl = function(m, o) {
			if ($.isFunction(this.each)) {
				var args = arguments;

				return this.each(function() {
					var self = this;

					/** Create a jGrowl Instance on the Container if it does not exist **/
					if ($(this).data('jGrowl.instance') === undefined) {
						$(this).data('jGrowl.instance', $.extend(new $.fn.jGrowl(), {
							notifications: [],
							element: null,
							interval: null
						}));
						$(this).data('jGrowl.instance').startup(this);
					}

					/** Optionally call jGrowl instance methods, or just raise a normal notification **/
					if ($.isFunction($(this).data('jGrowl.instance')[m])) {
						$(this).data('jGrowl.instance')[m].apply($(this).data('jGrowl.instance'), $.makeArray(args).slice(1));
					} else {
						$(this).data('jGrowl.instance').create(m, o);
					}
				});
			};
		};

		$.extend($.fn.jGrowl.prototype, {

			/** Default JGrowl Settings **/
			defaults: {
				pool: 0,
				header: '',
				group: '',
				sticky: false,
				position: 'bottom-right',
				glue: 'before',
				theme: 'default',
				check: 250,
				life: 7000,
				closeDuration: 'normal',
				openDuration: 'normal',
				easing: 'swing',
				closer: true,
				closeTemplate: '&times;',
				closerTemplate: '<div>[ close all ]</div>',
				log: function(e, m, o) {},
				beforeOpen: function(e, m, o) {},
				afterOpen: function(e, m, o) {},
				open: function(e, m, o) {},
				beforeClose: function(e, m, o) {},
				close: function(e, m, o) {},
				animateOpen: {
					opacity: 'show'
				},
				animateClose: {
					opacity: 'hide'
				}
			},

			notifications: [],

			/** jGrowl Container Node **/
			element: null,

			/** Interval Function **/
			interval: null,

			/** Create a Notification **/
			create: function(message, o) {
				var o = $.extend({}, this.defaults, o);

				this.notifications.push({
					message: message,
					options: o
				});

				o.log.apply(this.element, [this.element, message, o]);
			},

			render: function(notification) {
				var self = this;
				var message = notification.message;
				var o = notification.options;

				var notification = $('<div class="jGrowl-notification' + ((o.group != undefined && o.group != '') ? ' ' + o.group : '') + '">' + '<div class="jGrowl-close">' + o.closeTemplate + '</div>' + '<div class="jGrowl-header">' + o.header + '</div>' + '<div class="jGrowl-message">' + message + '</div></div>').data("jGrowl", o).addClass(o.theme).children('div.jGrowl-close').bind("click.jGrowl", function() {
					$(this).parent().trigger('jGrowl.close');
				}).parent();


				// Notification Actions
				$(notification).bind("mouseover.jGrowl", function() {
					$('div.jGrowl-notification', self.element).data("jGrowl.pause", true);
				}).bind("mouseout.jGrowl", function() {
					$('div.jGrowl-notification', self.element).data("jGrowl.pause", false);
				}).bind('jGrowl.beforeOpen', function() {
					if (o.beforeOpen.apply(notification, [notification, message, o, self.element]) != false) {
						$(this).trigger('jGrowl.open');
					}
				}).bind('jGrowl.open', function() {
					if (o.open.apply(notification, [notification, message, o, self.element]) != false) {
						if (o.glue == 'after') {
							$('div.jGrowl-notification:last', self.element).after(notification);
						} else {
							$('div.jGrowl-notification:first', self.element).before(notification);
						}

						$(this).animate(o.animateOpen, o.openDuration, o.easing, function() {
							// Fixes some anti-aliasing issues with IE filters.
							if ($.browser.msie && (parseInt($(this).css('opacity'), 10) === 1 || parseInt($(this).css('opacity'), 10) === 0)) this.style.removeAttribute('filter');

							if ($(this).data("jGrowl") != null) // Happens when a notification is closing before it's open.
							$(this).data("jGrowl").created = new Date();

							$(this).trigger('jGrowl.afterOpen');
						});
					}
				}).bind('jGrowl.afterOpen', function() {
					o.afterOpen.apply(notification, [notification, message, o, self.element]);
				}).bind('jGrowl.beforeClose', function() {
					if (o.beforeClose.apply(notification, [notification, message, o, self.element]) != false) $(this).trigger('jGrowl.close');
				}).bind('jGrowl.close', function() {
					// Pause the notification, lest during the course of animation another close event gets called.
					$(this).data('jGrowl.pause', true);
					$(this).animate(o.animateClose, o.closeDuration, o.easing, function() {
						if ($.isFunction(o.close)) {
							if (o.close.apply(notification, [notification, message, o, self.element]) !== false) $(this).remove();
						} else {
							$(this).remove();
						}
					});
				}).trigger('jGrowl.beforeOpen');

				/** Add a Global Closer if more than one notification exists **/
				if ($('div.jGrowl-notification:parent', self.element).size() > 1 && $('div.jGrowl-closer', self.element).size() == 0 && this.defaults.closer != false) {
					$(this.defaults.closerTemplate).addClass('jGrowl-closer').addClass(this.defaults.theme).appendTo(self.element).animate(this.defaults.animateOpen, this.defaults.speed, this.defaults.easing).bind("click.jGrowl", function() {
						$(this).siblings().trigger("jGrowl.beforeClose");

						if ($.isFunction(self.defaults.closer)) {
							self.defaults.closer.apply($(this).parent()[0], [$(this).parent()[0]]);
						}
					});
				};
			},

			/** Update the jGrowl Container, removing old jGrowl notifications **/
			update: function() {
				$(this.element).find('div.jGrowl-notification:parent').each(function() {
					if ($(this).data("jGrowl") != undefined && $(this).data("jGrowl").created != undefined && ($(this).data("jGrowl").created.getTime() + parseInt($(this).data("jGrowl").life)) < (new Date()).getTime() && $(this).data("jGrowl").sticky != true && ($(this).data("jGrowl.pause") == undefined || $(this).data("jGrowl.pause") != true)) {

						// Pause the notification, lest during the course of animation another close event gets called.
						$(this).trigger('jGrowl.beforeClose');
					}
				});

				if (this.notifications.length > 0 && (this.defaults.pool == 0 || $(this.element).find('div.jGrowl-notification:parent').size() < this.defaults.pool)) this.render(this.notifications.shift());

				if ($(this.element).find('div.jGrowl-notification:parent').size() < 2) {
					$(this.element).find('div.jGrowl-closer').animate(this.defaults.animateClose, this.defaults.speed, this.defaults.easing, function() {
						$(this).remove();
					});
				}
			},

			/** Setup the jGrowl Notification Container **/
			startup: function(e) {
				this.element = $(e).addClass('jGrowl').append('<div class="jGrowl-notification"></div>');
				this.interval = setInterval(function() {
					$(e).data('jGrowl.instance').update();
				}, parseInt(this.defaults.check));

				if ($.browser.msie && parseInt($.browser.version) < 7 && !window["XMLHttpRequest"]) {
					$(this.element).addClass('ie6');
				}
			},

			/** Shutdown jGrowl, removing it and clearing the interval **/
			shutdown: function() {
				$(this.element).removeClass('jGrowl').find('div.jGrowl-notification').remove();
				clearInterval(this.interval);
			},

			close: function() {
				$(this.element).find('div.jGrowl-notification').each(function() {
					$(this).trigger('jGrowl.beforeClose');
				});
			}
		});

	})(jQuery);


//json2js
	(function($) {

		var escapeable = /["\\\x00-\x1f\x7f-\x9f]/g,
			meta = {
				'\b': '\\b',
				'\t': '\\t',
				'\n': '\\n',
				'\f': '\\f',
				'\r': '\\r',
				'"': '\\"',
				'\\': '\\\\'
			};

		/**
		 * jQuery.toJSON
		 * Converts the given argument into a JSON respresentation.
		 *
		 * @param o {Mixed} The json-serializble *thing* to be converted
		 *
		 * If an object has a toJSON prototype, that will be used to get the representation.
		 * Non-integer/string keys are skipped in the object, as are keys that point to a
		 * function.
		 *
		 */
		$.toJSON = typeof JSON === 'object' && JSON.stringify ? JSON.stringify : function(o) {

			if (o === null) {
				return 'null';
			}

			var type = typeof o;

			if (type === 'undefined') {
				return undefined;
			}
			if (type === 'number' || type === 'boolean') {
				return '' + o;
			}
			if (type === 'string') {
				return $.quoteString(o);
			}
			if (type === 'object') {
				if (typeof o.toJSON === 'function') {
					return $.toJSON(o.toJSON());
				}
				if (o.constructor === Date) {
					var month = o.getUTCMonth() + 1,
						day = o.getUTCDate(),
						year = o.getUTCFullYear(),
						hours = o.getUTCHours(),
						minutes = o.getUTCMinutes(),
						seconds = o.getUTCSeconds(),
						milli = o.getUTCMilliseconds();

					if (month < 10) {
						month = '0' + month;
					}
					if (day < 10) {
						day = '0' + day;
					}
					if (hours < 10) {
						hours = '0' + hours;
					}
					if (minutes < 10) {
						minutes = '0' + minutes;
					}
					if (seconds < 10) {
						seconds = '0' + seconds;
					}
					if (milli < 100) {
						milli = '0' + milli;
					}
					if (milli < 10) {
						milli = '0' + milli;
					}
					return '"' + year + '-' + month + '-' + day + 'T' + hours + ':' + minutes + ':' + seconds + '.' + milli + 'Z"';
				}
				if (o.constructor === Array) {
					var ret = [];
					for (var i = 0; i < o.length; i++) {
						ret.push($.toJSON(o[i]) || 'null');
					}
					return '[' + ret.join(',') + ']';
				}
				var name, val, pairs = [];
				for (var k in o) {
					type = typeof k;
					if (type === 'number') {
						name = '"' + k + '"';
					} else if (type === 'string') {
						name = $.quoteString(k);
					} else {
						// Keys must be numerical or string. Skip others
						continue;
					}
					type = typeof o[k];

					if (type === 'function' || type === 'undefined') {
						// Invalid values like these return undefined
						// from toJSON, however those object members
						// shouldn't be included in the JSON string at all.
						continue;
					}
					val = $.toJSON(o[k]);
					pairs.push(name + ':' + val);
				}
				return '{' + pairs.join(',') + '}';
			}
		};

		/**
		 * jQuery.quoteString
		 * Returns a string-repr of a string, escaping quotes intelligently.
		 * Mostly a support function for toJSON.
		 * Examples:
		 * >>> jQuery.quoteString('apple')
		 * "apple"
		 *
		 * >>> jQuery.quoteString('"Where are we going?", she asked.')
		 * "\"Where are we going?\", she asked."
		 */
		$.quoteString = function(string) {
			if (string.match(escapeable)) {
				return '"' + string.replace(escapeable, function(a) {
					var c = meta[a];
					if (typeof c === 'string') {
						return c;
					}
					c = a.charCodeAt();
					return '\\u00' + Math.floor(c / 16).toString(16) + (c % 16).toString(16);
				}) + '"';
			}
			return '"' + string + '"';
		};

	})(jQuery);