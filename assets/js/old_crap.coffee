# accordion
allPanels = $("#navAccordion > ul").hide()
$("#navAccordion > p").click ->
	$target = $(this).next()
	unless $target.hasClass("active")
		allPanels.removeClass("active").slideUp()
		$target.addClass("active").slideDown()
	false

#expiriment with default text
###
$('input[data-defaultText]').each (index, value) ->
	p 'theheh'
	p this
	p index
	p value
	this.value = this.dataset.defaultText
###


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


#site functions
modalClose = (runScript) -> #if runScript is defined then the script won't be run (define as false)
	eval pages[current.index]["modals"][current.subpage]["onClose"] if typeof pages[current.index]["modals"][current.subpage]["onClose"] isnt "undefined" and typeof (runScript) is "undefined"
	window.location = "#" + current.lastSub

#cookie handling functions
bakeCookie = (name, value) ->
	expires = new Date()
	expires.setTime expires.getTime() + (15552000000)
	if value is "" #set cookie, or if value is blank, set it to be removed
		document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/"
	else
		document.cookie = name + "=" + value + "; expires=" + expires.toGMTString() + "; path=/"

eatCookie = (name) ->
	nameEQ = name + "="
	ca = document.cookie.split(";")
	cookieValue = ""
	i = 0

	while i < ca.length
		c = ca[i]
		c = c.substring(1) while c.charAt(0) is " "
		if c.indexOf(nameEQ) is 0
			cookieValue = c.substring(nameEQ.length)
			break
		i++
	cookieValue




### MOVE INTO AccountModel
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
###

### MOVE INTO AccountView.render()
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
###

### MOVE INTO AccountModel (I think)
updateUser = (key, value) -> #newObject does not need to be a full user object
	#user = jQuery.extend(true, user, userUpdates);#CONSIDER using this in login so only non-default stuff needs to be sent
	user.prefs[key] = value
	bakeCookie "user", $.toJSON(user) if eatCookie("user") isnt ""
postUserUpdates = ->
	post "process.php", "{\"request\": \"updateUser\"}" if eatCookie("user") isnt "" #PHP gets user object from cookie -- only run if logged in
	modalClose false
	#TODO: add checking in postUserUpdates() to see if user object is different
###


Array::has = (checkObj) ->
	@indexOf(checkObj) isnt -1

Array::remove = (from, to) ->
	rest = @slice((to or from) + 1 or @length)
	@length = (if from < 0 then @length + from else from)
	@push.apply this, rest

limitInput = (e, limit) -> #used for limiting form input
	unicode = (if e.charCode then e.charCode else e.keyCode)
	if unicode isnt 8 and unicode isnt 9 and unicode isnt 37 and unicode isnt 39 #if the key isn't the backspace key or tab or l/r arrow
		return false if (unicode < 48 or unicode > 57) and limit is "number" #disable key press if not a number
		return false if (unicode < 65 or unicode > 90) and (unicode < 97 or unicode > 122) and limit is "letter" #disable key press if not a letter

json2table = (json) ->
	table = "<thead><tr>"
	len = json.length
	len2 = json[0].length
	e = 0
	while e < len2
		table += "<td>" + json[0][e] + "</td>"
		e++
	table += "</tr></thead><tbody>"
	len = json.length
	i = 1
	while i < len
		table += "<tr>"
		len2 = json[i].length
		e = 0
		while e < len2
			table += "<td>" + json[i][e] + "</td>"
			e++
		table += "</tr>"
		i++
	table += "</tbody>"
	table

post = (filename, json, async, saveRequest) -> #TODO: remove filename param by migrating all requests to one file
	async = (if (typeof async is "undefined") then false else async)
	saveRequest = (if (typeof saveRequest is "undefined") then false else saveRequest)
	###
	this function handles:
		all interfacing w/ server via AJAX
		
	json.globalError: holds type of globalError (which determines action), error text is still in json.error
	###
	ajax = $.ajax(
		type: "POST"
		url: filename
		data: "data=" + json
		async: async
		success: (data) ->
			postSuccess data if async #else, the function below will be called (async posts can't return anything)
		error: (jqXHR) ->
			#will trigger even if verbose is off
			$("#jGrowl-container").jGrowl "AJAX Error: " + jqXHR.status + "<br />" + jqXHR.statusText + ".",
				sticky: true
				theme: "error"

			saveMissedRequest filename, json if saveRequest
	)
	postSuccess ajax.responseText unless async

postSuccess = (data) ->
	if data.charAt(0) is "{" #not a flawless way to detect if it is json
		json = eval("(" + data + ")")
	else
		json = {}
		json.error = "valid json was not returned"
	eval json.script if json.script

	#p(json);#TODO: remove before production

	if json.error
		$("#jGrowl-container").jGrowl "error: " + json.error,
			theme: "error"

		return false #this means error
	if json.message and user.prefs.verbose is true
		$("#jGrowl-container").jGrowl "success: " + json.message,
			theme: "message"

		delete json.message

	#try to submit any requests that failed before
	if missedPosts.length isnt 0
		lastMissedPost = missedPosts.length - 1
		post missedPosts[lastMissedPost].filename, missedPosts[lastMissedPost].json, true, true
		missedPosts.remove 0 #it will be re-added if it fails to submit
		bakeCookie "missedPost" + lastMissedPost, ""
	json #if nothing is returned assume error

saveMissedRequest = (filename, json) ->
	missedPosts.push
		filename: filename
		json: json

	lastMissedPost = missedPosts.length - 1
	bakeCookie "missedPost" + lastMissedPost, $.toJSON(missedPosts[lastMissedPost])
	p "TODO: prompt file download" if lastMissedPost >= 149
	$("#jGrowl-container").jGrowl "although the request has failed, your data has been saved, and I will attempt to resubmit it when possible",
		sticky: true
		theme: "error"

#TODO: replace with something better, like downloadify or just a modal
