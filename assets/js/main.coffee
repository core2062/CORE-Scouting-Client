require.config(
	paths:
		underscore: 'components/underscore/underscore'
		backbone: 'components/backbone/backbone'
		jquery: 'components/jquery/jquery.min'
	shim:
		underscore:
			exports: '_'
		backbone:
			deps: ['underscore', 'jquery']
			exports: 'Backbone'
		jsonform:
			deps: ['underscore', 'jquery']
			exports: 'jsonform'
		tipsy: ['jquery']
		jgrowl: ['jquery']
)

# general
require ['jquery', 'structure', 'tipsy', 'jgrowl', 'rainbow'], ($, App) ->
	#Chosen Select Box
	#$("select").chosen()

	#TODO: float google +1 button left w/out 4px overhang
	#TODO: make startup script to warn bad browsers
	#TODO: add stuff to prefetch subpages???

	#error logger
	window.onerror = (msg, url, line) ->
		notify "errorMsg: #{msg} on line #{line}",
			theme: 'error'
			sticky: true

		#TODO: post error to server to record

		false #let default error handler continue

	#UI Event Handlers

	#set fieldset class on focus
	$("input, textarea").focus ->
		$(this).parentsUntil($("form"), 'fieldset').addClass 'focus'

	$("input, textarea").focusout ->
		$(this).parentsUntil($("form"), 'fieldset').removeClass 'focus'

	#clear input icon
	$(".clearIcon span").click ->
		input = @previousSibling
		input.value = ""
		input.focus()

	#general functions
	String::title_case = ->
		@replace /\w\S*/g, (txt) ->
			txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase()

	#Tipsy
	$("label[title], button[title], a[title]").tipsy()

	$("input[title]").tipsy
		trigger: "focus"
		gravity: "w"

	$(".toggle-container[title]").tipsy
		trigger: "hover"
		gravity: "w"

	# friendly little aliases
	p = (text) -> console.log text
	notify = (args...) -> $("#jGrowl-container").jGrowl args...

	p 'Hello and welcome to the CSD, a intuitive scouting database and
	analysis program created by C.O.R.E. 2062.'

	App.Pages.create(
		name: "input"
		selected: true
	)
	App.Pages.create(
		name: "output"
	)
	App.Pages.create(
		name: "signup"
		progressbar: true
	)

	Backbone.history.start()
	# change to default page at startup (if there is no hash fragment)
	if Backbone.history.fragment is ''
		App.Router.navigate(App.Pages.default_page,
			trigger: true
			replace: true
		)




