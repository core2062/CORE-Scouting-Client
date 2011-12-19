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

/* TODO float google +1 button left w/out 4px overhang  */

//set fieldset class on focus
$("input, textarea").focus(function () {
    $(this).parentsUntil($("form"), "fieldset").addClass('focus');
});

$("input, textarea").focusout(function () {
    $(this).parentsUntil($("form"), "fieldset").removeClass('focus');
});

// global vars
var pages = [{
	"name": "base",
	"description": "",
	"subpages": [],
	"modals": {
		"navigation": {
	        "login-required": false
        },
        "login": {
            "login-required": false
        },
        "contact": {
            "login-required": false
        },
        "credits": {
            "login-required": false
        },
        "edit-account": {
	        "login-required": true
        }
    },
    "minWidth": "1150px",
    "progressbar": false
}, {
    "name": "home",
    "description": "lorem",
    "subpages": {
        "front-page": {
            "description": "lorem",
            "login-required": false
        },
        "synopsis": {
            "description": "lorem",
            "login-required": false
        },
        "tour": {
            "description": "lorem",
            "login-required": false
        },
        "signup": {
            "description": "lorem",
            "login-required": false
        }
    },
    "modals": {},
    "minWidth": "1150px",
    "progressbar": false
}, {
    "name": "input",
    "description": "lorem",
    "subpages": {
        "robot": {
            "description": "lorem",
            "login-required": true
        },
        "human": {
            "description": "lorem",
            "login-required": true
        },
        "pit": {
            "description": "lorem",
            "login-required": true
        }
    },
    "modals": {},
    "minWidth": "1150px",
    "progressbar": true
}, {
    "name": "analysis",
    "description": "lorem",
    "subpages": {
        "public": {
            "description": "lorem",
            "login-required": false
        },
        "member": {
            "description": "lorem",
            "login-required": true
        },
        "data-liberation": {
            "description": "lorem",
            "login-required": true
        }
    },
    "modals": {},
    "minWidth": "1150px",
    "progressbar": false
}, {
    "name": "team-leader",
    "description": "lorem",
    "subpages": {
        "manage-scouting": {
            "description": "lorem",
            "login-required": false
        },
        "view-contribution": {
            "description": "lorem",
            "login-required": false
        },
        "view-team-members": {
            "description": "lorem",
            "login-required": false
        }
    },
    "modals": {},
    "minWidth": "1150px",
    "progressbar": false
}, {
    "name": "help",
    "description": "lorem",
    "subpages": {
        "manage-training": {
            "description": "lorem",
            "login-required": true
        },
        "documentation": {
            "description": "lorem",
            "login-required": false
        },
        "forum": {
            "description": "lorem",
            "login-required": true
        }
    },
    "modals": {},
    "minWidth": "1150px",
    "progressbar": false
}];
//TODO move public to new page - out of member analysis


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
    "modals": []
};
function fixFavicon() { //fixes favicon bug in firefox
    $('#favicon').remove();
    $('<link href="favicon.ico" rel="shortcut icon" id="favicon"/>').appendTo('head');
}

$(document).ready(function() {

    if (eatCookie('user') !== '') {
        window.user = eval(eatCookie('user'));
    } else {
        window.user = {
            "scoutid": "",
            "token": "",
            "prefs": []
        };
    }

    cacheSubpages();
    cacheModals();
    nav(); //this will trigger login() if needed
});

function cacheSubpages() {
    var len = pages.length;

    for (var i = 0; i < len; i++) {
        for (var e in pages[i].subpages) {
            cache.subpages.push(e);
        }
    }
    cache.subpages = '.' + cache.subpages.join("-c, .");
}

function cacheModals() {
    var len = pages.length;

    for (var i = 0; i < len; i++) {
        for (var e in pages[i].modals) {
            cache.modals.push(e);
        }
    }
    cache.modals = '.' + cache.modals.join("-c, .");
}

window.onpopstate = function(event) {
    // if nav() is failing, check browser support for this
    console.log(event);
    fixFavicon(); //remove this?????
    nav();
}

function nav() {
/*
 * Navigation Function
 * this function handles all page transitions and applies all page specific options
 * page specific options and data needed for displaying pages is stored in the JSON object 'pages'
*/

    prev.index = current.index;
    prev.type = current.type;
    prev.lastSub = current.lastSub;
    prev.subpage = current.subpage;

    if (current.subpage == location.hash.substring(1)) {
        return;
    };
    current.subpage = location.hash.substring(1).toLowerCase();

    current.index = '';

    var len = pages.length;
    for (var i = 0; i < len; i++) { //TODO add catching?
        if (typeof pages[i].subpages[current.subpage] !== 'undefined') {
            current.index = i;
            current.type = 'subpages';
            current.lastSub = current.subpage;
            break;
        }
    }

    if (current.index === '') { // TODO merge with subpage search ?
        for (var i = 0; i < len; i++) {
            if (typeof pages[i].modals[current.subpage] !== 'undefined') {
                current.index = i;
                current.type = 'modals';
                break;
            }
        }
    }

    if (current.index === '') { //page cannot be found, select default page
        window.location = '#robot'; //TODO change to default page when created
        return;
    }

    var fadetime = 500;

    if (prev.subpage == "") { // if this is the first page
        if (current.type == 'modals') {
			$('.robot-c').fadeIn(fadetime / 4);
            current.lastSub = 'robot';
            prev.index = 2;
            prev.subpage = 'robot';
        }
        prev.type = "subpages";
    }

    document.title = caps(pages[current.index].name) + ' - ' + caps(current.subpage);
	document.getElementById('body').style.minWidth = pages[current.index].minWidth

    //start page changers

    if (current.type == "subpages") { //sub-pages
        $('#' + current.subpage + '-r').attr('checked', true); //CONSIDER using [type="radio"]

        if (prev.type == 'subpages') { //sub-pages
            $(cache.subpages).fadeOut(fadetime).promise().done(function() {
                $('.' + current.subpage + '-c').fadeIn(fadetime);
            });
        } else { //modals

            if (prev.lastSub == current.subpage) { //don't fade out sub-page if is is already under the modal
                $('#overlay, #modal-container, ' + cache.modals).fadeOut(fadetime);
            } else {
                $('#overlay, #modal-container, ' + cache.subpages + ', ' + cache.modals).fadeOut(fadetime).promise().done(function() {
                    $('.' + current.subpage + '-c').fadeIn(fadetime);
                });
            }
        }
    } else { //modal
        document.getElementById('modal-title').innerHTML = current.subpage;

        if (prev.type == 'subpages') { //subpages
            $(cache['modals']).hide().promise().done(function() {
                $('#overlay').fadeIn(40);
                $('.' + current.subpage + '-c, #modal-container').fadeIn(fadetime);
            });
        } else { //modals
            $(cache.modals).fadeOut(fadetime).promise().done(function() {
                $('.' + current.subpage + '-c, #modal-container').fadeIn(fadetime);
            });
        }
    }
}

function modalclose() {
    window.location = '#' + current.lastSub;
}

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
//TODO change login button to global user-button (id = userButton)
//made as button set, name in one part (opens edit-account modal)
//other part has logout (icon & tipsy)

function loginCheck() {
    if (eatCookie('scoutid') !== '' && eatCookie('token') !== '') {
        //assume logged in, if token is wrong error will be returned by process later
        //other wise there would be too many login checks
        //on error returned from process.php, token is removed
        return;
    }


    //is login stuff filled out
    //then attempt login

    //does page require login
    //then open login modal & say page requires login

    scoutidinput = document.getElementById('scoutid');
    pwordinput = document.getElementById('pword');
    loginbutton = document.getElementById('login-button');

    window.scoutid = scoutidinput.value;
    pword = pwordinput.value;
}

function getToken(password) { //merge function with login
    scoutidinput = document.getElementById('scoutid');
    pwordinput = document.getElementById('pword');
    loginbutton = document.getElementById('login-button'); //TODO change to global userButton

    user.scoutid = scoutidinput.value; //put in user object
    pword = pwordinput.value; //not global, pword stays in function and is deleted at return

    scoutidinput.value = ''; //remove them from inputs
    pwordinput.value = '';

    if (user.scoutid == '') {
        $('#jGrowl-container').jGrowl('ScoutID is blank', {
            theme: 'error'
        });
    } else if (pword == '') {
        $('#jGrowl-container').jGrowl('Password is blank', {
            theme: 'error'
        });
    } else {

        var json = post('login.php', '{"scoutid":"' + user.scoutid + '","pword":"' + pword + '"}');

        if (json.token) {
            user.token = json.token;
            user.prefs = json.prefs;

            bakeCookie('user', $.toJSON(user)); //store user object in cookie

            loginbutton.innerHTML = 'Logout';
            //TODO change to set stuff for user-button
            //hide login button
            //show user button with name of scout in the inner html

            return;
        } else if (json.error) {
            $('#jGrowl-container').jGrowl('Login Failure: ' + json.error, {
                theme: 'error'
            });
        } else {
            $('#jGrowl-container').jGrowl('Login Failure: Server did not respond properly', {
                theme: 'error'
            });
        }
    }

    loginbutton.innerHTML = 'Login'; //TODO change to global userButton &  the logout icon
    window.location = '#Login';
}
function logout() {
    scoutid = '';
    token = '';
    loginbutton.innerHTML = 'Login';
    loginCheck();
}


//general functions

function getkey(e) {
    var unicode = e.keyCode ? e.keyCode : e.charCode;
    return unicode;
}

function numbersonly(e) { //used for limiting form input
    var unicode = e.charCode ? e.charCode : e.keyCode
    if (unicode != 8) { //if the key isn't the backspace key (which we should allow)
        if (unicode < 48 || unicode > 57) { //if not a number
            return false //disable key press
        }
    }
}

function caps(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
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



// Easy Select Box 1.0 by http://www.codefleet.com/easy-select-box
// Minor changes by Sean Lang

// START Select Box
(function($) {

	// Disable for iOS devices (their native controls are more suitable for a touch device)
	if (navigator.userAgent.match(/iPad|iPhone|Android/i)) return false;

	$.fn.extend({
		easySelectBox: function(options) {

			var defaults = {
				className: 'select-box',
				speed: 0 //speed of opening and closing drop down in ms
			};

			var options = $.extend(defaults, options);

			return this.each(function() {
				var o = options;

				//Assign current element to variable, in this case is UL element
				var selectObj = $(this);
				//check if its a <select> tag
				if ('select' != selectObj[0].nodeName.toLowerCase()) {
					return false;
				}

				var elementName = selectObj.attr('name');
				var elementId = selectObj.attr('id');
				var lists = selectObj.children('option');
				var easySelect = null;
				var initialVal = selectObj.val();
				var displayClass = "select-box-disp";
				var initIndex = 0;

				//construct html
				var html = '';
				$.each(lists, function(i, el) {
					html += '<li><a href="#" rel="' + $(el).val() + '">' + $(el).text() + '</a></li>'; //place text
					//values.push($(el).val());//save values
					if (initialVal == $(el).val()) {
						initIndex = i;
					}
				});
				html = '<div class="' + o.className + '"><a class="' + displayClass + '" href="#">' + lists.eq(initIndex).text() + '</a><span id="selectarrow"></span><ul>' + html + '</ul></div>';

				//add to dom
				easySelect = $(html).insertAfter(selectObj);
				selectObj.hide(); //hide the select element

				//Attach click event
				easySelect.click(function(e) {
					if ($(easySelect).children('ul').is(':visible')) {
						$(easySelect).children('ul').slideUp(o.speed);
						easySelect.css('z-index', 99);
					} else {
						$(easySelect).children('ul').slideDown(o.speed);
						easySelect.css('z-index', 100);
						$('.' + o.className).not(easySelect).each(function() {
							if ($(this).children('ul').is(':visible')) {
								$(this).children('ul').slideUp(o.speed);
								$(this).css('z-index', 99);
							}
						});
					}
					e.stopPropagation();
					return false;
				});

				//close when not clicked. use document as window does not work IE
				$(document).click(function() {
					$('.' + o.className).each(function() {
						if ($(this).children('ul').is(':visible')) {
							$(this).children('ul').slideUp(o.speed);
						}
					});
				});

				//change value
				easySelect.children('ul').children('li').click(function() {
					easySelect.children('.' + displayClass).html($(this).children('a').html());
					selectObj.children('option').removeAttr('selected');
					selectObj.find('option').eq($(this).index()).attr('selected', 'selected');
					document.getElementById(elementId).onchange(); //trigger on-change event
				});
			});
		}
	});
})(jQuery);
// END Select Box

// START jGrowl

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


			/** Notification Actions **/
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
// END jGrowl


// START AJAX processing



//scoutid and pword should be stored in vars, not in form input



//new AJAX

function post(filename, json) {
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
	console.log(ajax);
	json = eval("(" + ajax.responseText + ")");
	return json;
}



// TODO replace with something better, like downloadify or just a modal

function WriteToWindow() {
	top.consoleRef = window.open('', 'myconsole', 'width=350,height=250,menubar=0,toolbar=1,status=0,scrollbars=1,resizable=1');
	//TODO fix link to style sheet, or replace completely
	top.consoleRef.document.write('<html><head><title>Scouting Data</title></head><body bgcolor=white onLoad="self.focus()"><textarea style="width:100%; height:100%;">' + writetext + '</textarea></body></html>')
	top.consoleRef.document.close()
}

//shit code below
/*
switch (RequestType) {
case "P": // Poll

//AJAX

// stuff for processing mail XML

break;
case "I": // Input
		$('#jGrowl-container').jGrowl('Submit pending...');

		if (currentpage == 'Regular'){
		var v1 = errornum
		// var 2 in mutual
		var v3 = document.getElementById('AllianceColor').value;
		var v4 = document.getElementById('AllianceScore').value;
		var v5 = document.getElementById('TeamNum').value;
		var v6 = document.getElementById('YCard').value;
		var v7 = document.getElementById('RCard').value;
		// var 8 in mutual
		var v9 = ''

		}

		if (currentpage == 'Human-Player'){
				var inputXML = "" // build XML for Human-Player
		}

		if (currentpage == 'Regular' || currentpage == 'Human-Player') {
		var v2 = document.getElementById('MatchNum').value;
		var v8 = document.getElementById('Comments').value;
		}

		if (currentpage == 'Pit'){
				var inputXML = "" // build XML for pit
		}

		var RequestText = "&c="+currentpage + "&i="+inputXML

//AJAX


		$('#jGrowl-container').jGrowl('To prevent the loss of valuable scouting data, I have compiled all of the data which you have entered for this match. <br /> Click the button below to open a window containing the scouting data, then send the text to me at <a href="mailto:slang800@gmail.com">slang800@gmail.com</a>, and I will add it to the database.<br /><button type="button" style="margin-left:187px;;" onclick="WriteToWindow();">Open</button>', {sticky: true, theme: 'error'});
		window.writetext = "&ScoutID="+ScoutID + "&pword="+pword + "&Request="+RequestType + RequestText;

		//if no errors
		if (RequestType == 'I') {
				clearinputs();
				if (currentpage == 'Regular' || currentpage == 'Human-Player') {
						increase('MatchNum');
				}
		}


break;
case "M": // Mail


//AJAX

		// Code to clear mail inputs

break;
case "Q": // Query
		if (query.length==0) {
				document.getElementById(place).innerHTML="";
				return;
		}
		var RequestText = "&q="+query + "&t="+type + "&v1="+variable + "&p="+place

//AJAX


break;
*/




/**
 * jQuery JSON Plugin
 * version: 2.3 (2011-09-17)
 *
 * This document is licensed as free software under the terms of the
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 *
 * Brantley Harris wrote this plugin. It is based somewhat on the JSON.org
 * website's http://www.json.org/json2.js, which proclaims:
 * "NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.", a sentiment that
 * I uphold.
 *
 * It is also influenced heavily by MochiKit's serializeJSON, which is
 * copyrighted 2005 by Bob Ippolito.
 */

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

$('a[title]').tipsy();
$('button[title]').tipsy();