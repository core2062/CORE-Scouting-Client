(function() {
  var allPanels, bakeCookie, eatCookie, fixFavicon, getMissedPosts, json2table, limitInput, modalClose, post, postSuccess, saveMissedRequest;

  allPanels = $("#navAccordion > ul").hide();

  $("#navAccordion > p").click(function() {
    var $target;
    $target = $(this).next();
    if (!$target.hasClass("active")) {
      allPanels.removeClass("active").slideUp();
      $target.addClass("active").slideDown();
    }
    return false;
  });

  /*
  $('input[data-defaultText]').each (index, value) ->
  	p 'theheh'
  	p this
  	p index
  	p value
  	this.value = this.dataset.defaultText
  */


  fixFavicon = function() {
    $("#favicon").remove();
    return $('<link href="favicon.ico" rel="shortcut icon" id="favicon"/>').appendTo("head");
  };

  getMissedPosts = function() {
    var i, missedPost, missedPosts;
    i = 0;
    missedPosts = [];
    while (true) {
      missedPost = eatCookie("missedPost" + i);
      if (missedPost !== "") {
        missedPosts[i] = eval('(' + missedPost + ')');
        i++;
      } else {
        return missedPosts;
      }
    }
  };

  modalClose = function(runScript) {
    if (typeof pages[current.index]["modals"][current.subpage]["onClose"] !== "undefined" && typeof runScript === "undefined") {
      eval(pages[current.index]["modals"][current.subpage]["onClose"]);
    }
    return window.location = "#" + current.lastSub;
  };

  bakeCookie = function(name, value) {
    var expires;
    expires = new Date();
    expires.setTime(expires.getTime() + 15552000000.);
    if (value === "") {
      return document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
    } else {
      return document.cookie = name + "=" + value + "; expires=" + expires.toGMTString() + "; path=/";
    }
  };

  eatCookie = function(name) {
    var c, ca, cookieValue, i, nameEQ;
    nameEQ = name + "=";
    ca = document.cookie.split(";");
    cookieValue = "";
    i = 0;
    while (i < ca.length) {
      c = ca[i];
      while (c.charAt(0) === " ") {
        c = c.substring(1);
      }
      if (c.indexOf(nameEQ) === 0) {
        cookieValue = c.substring(nameEQ.length);
        break;
      }
      i++;
    }
    return cookieValue;
  };

  /* MOVE INTO AccountModel
  getToken = (password) ->
  	#
  		this function handles:
  		getting token & user object from login
  		calling login.php
  		
  	must be separate from other functions so it can be called directly from login modal
  	#
  	scoutidInput = document.getElementById "scoutid"
  	pwordInput = document.getElementById "pword"
  	user._id = scoutidInput.value #put in user object (scoutid in user object != logged in)
  	pword = pwordInput.value #limited to this function (can't be recovered after being typed in)
  	scoutidInput.value = "" #remove them from inputs
  	pwordInput.value = ""
  	if user._id is ""
  		$("#jGrowl-container").jGrowl "scoutID is blank",
  			theme: "error"
  	else if pword is ""
  		$("#jGrowl-container").jGrowl "password is blank",
  			theme: "error"
  	else
  		json = post("login.php", "{\"_id\":\"" + user._id + "\",\"pword\":\"" + pword + "\"}")
  		if json.token
  			#store stuff in temporary user object
  			user = json
  			bakeCookie "user", $.toJSON(user) #store user object in cookie
  			updateUserBar()
  			modalClose()
  			return
  		else if json isnt false
  			$("#jGrowl-container").jGrowl "server did not respond properly",
  				theme: "error"
  	window.location = "#login" #will only be run at error due to above return
  
  callLogout = -> #tells server to logout & runs logout function
  	post "process.php", '{"request":"logout"}'
  	#recheck current page in navbar, radio button hasn't been set yet so timeout is needed
  	setTimeout "$('#' + current.subpage + '-r').attr('checked', true)", 1
  	logout()
  
  logout = ->	#just removes the cookie & user object
  	#must be separate from other functions so it can be called from script returned by post() or manually by user
  	window.user = defaultUser #reset to generic user object
  	bakeCookie "user", "" #remove user object cookie
  	updateUserBar()
  	window.location = "#login" if pages[current.index][current.type][current.subpage]["login-required"] is true
  */


  /* MOVE INTO AccountView.render()
  updateUserBar = -> #also updates account modal
  	loginLabel = document.getElementById("login-r-label")
  	if eatCookie("user") isnt "" #logged in
  		$("#login").css "display", "none"
  		$("#logout").css "display", "inline"
  		loginLabel.setAttribute "original-title", "Logout"
  		loginLabel.setAttribute "onclick", "callLogout()"
  	else #not logged in
  		$("#logout").css "display", "none"
  		$("#login").css "display", "inline"
  		loginLabel.setAttribute "original-title", "Login"
  		loginLabel.setAttribute "onclick", "window.location = '#login'"
  	document.getElementById("scoutName").innerHTML = $.trim(user.info.fName + " " + user.info.lName)
  
  	#account modal
  	$("#fadeEffects").toggleSwitch "toggle", user.prefs.fade
  	$("#verboseMode").toggleSwitch "toggle", user.prefs.verbose
  */


  /* MOVE INTO AccountModel (I think)
  updateUser = (key, value) -> #newObject does not need to be a full user object
  	#user = jQuery.extend(true, user, userUpdates);#CONSIDER using this in login so only non-default stuff needs to be sent
  	user.prefs[key] = value
  	bakeCookie "user", $.toJSON(user) if eatCookie("user") isnt ""
  postUserUpdates = ->
  	post "process.php", "{\"request\": \"updateUser\"}" if eatCookie("user") isnt "" #PHP gets user object from cookie -- only run if logged in
  	modalClose false
  	#TODO: add checking in postUserUpdates() to see if user object is different
  */


  Array.prototype.has = function(checkObj) {
    return this.indexOf(checkObj) !== -1;
  };

  Array.prototype.remove = function(from, to) {
    var rest;
    rest = this.slice((to || from) + 1 || this.length);
    this.length = (from < 0 ? this.length + from : from);
    return this.push.apply(this, rest);
  };

  limitInput = function(e, limit) {
    var unicode;
    unicode = (e.charCode ? e.charCode : e.keyCode);
    if (unicode !== 8 && unicode !== 9 && unicode !== 37 && unicode !== 39) {
      if ((unicode < 48 || unicode > 57) && limit === "number") {
        return false;
      }
      if ((unicode < 65 || unicode > 90) && (unicode < 97 || unicode > 122) && limit === "letter") {
        return false;
      }
    }
  };

  json2table = function(json) {
    var e, i, len, len2, table;
    table = "<thead><tr>";
    len = json.length;
    len2 = json[0].length;
    e = 0;
    while (e < len2) {
      table += "<td>" + json[0][e] + "</td>";
      e++;
    }
    table += "</tr></thead><tbody>";
    len = json.length;
    i = 1;
    while (i < len) {
      table += "<tr>";
      len2 = json[i].length;
      e = 0;
      while (e < len2) {
        table += "<td>" + json[i][e] + "</td>";
        e++;
      }
      table += "</tr>";
      i++;
    }
    table += "</tbody>";
    return table;
  };

  post = function(filename, json, async, saveRequest) {
    var ajax;
    async = (typeof async === "undefined" ? false : async);
    saveRequest = (typeof saveRequest === "undefined" ? false : saveRequest);
    /*
    	this function handles:
    		all interfacing w/ server via AJAX
    		
    	json.globalError: holds type of globalError (which determines action), error text is still in json.error
    */

    ajax = $.ajax({
      type: "POST",
      url: filename,
      data: "data=" + json,
      async: async,
      success: function(data) {
        if (async) {
          return postSuccess(data);
        }
      },
      error: function(jqXHR) {
        $("#jGrowl-container").jGrowl("AJAX Error: " + jqXHR.status + "<br />" + jqXHR.statusText + ".", {
          sticky: true,
          theme: "error"
        });
        if (saveRequest) {
          return saveMissedRequest(filename, json);
        }
      }
    });
    if (!async) {
      return postSuccess(ajax.responseText);
    }
  };

  postSuccess = function(data) {
    var json, lastMissedPost;
    if (data.charAt(0) === "{") {
      json = eval("(" + data + ")");
    } else {
      json = {};
      json.error = "valid json was not returned";
    }
    if (json.script) {
      eval(json.script);
    }
    if (json.error) {
      $("#jGrowl-container").jGrowl("error: " + json.error, {
        theme: "error"
      });
      return false;
    }
    if (json.message && user.prefs.verbose === true) {
      $("#jGrowl-container").jGrowl("success: " + json.message, {
        theme: "message"
      });
      delete json.message;
    }
    if (missedPosts.length !== 0) {
      lastMissedPost = missedPosts.length - 1;
      post(missedPosts[lastMissedPost].filename, missedPosts[lastMissedPost].json, true, true);
      missedPosts.remove(0);
      bakeCookie("missedPost" + lastMissedPost, "");
    }
    return json;
  };

  saveMissedRequest = function(filename, json) {
    var lastMissedPost;
    missedPosts.push({
      filename: filename,
      json: json
    });
    lastMissedPost = missedPosts.length - 1;
    bakeCookie("missedPost" + lastMissedPost, $.toJSON(missedPosts[lastMissedPost]));
    if (lastMissedPost >= 149) {
      p("TODO: prompt file download");
    }
    return $("#jGrowl-container").jGrowl("although the request has failed, your data has been saved, and I will attempt to resubmit it when possible", {
      sticky: true,
      theme: "error"
    });
  };

}).call(this);
