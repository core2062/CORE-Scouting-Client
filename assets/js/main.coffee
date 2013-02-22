require.config(
	paths:
		underscore: 'components/underscore/underscore'
		backbone: 'components/backbone/backbone'
		jquery: 'components/jquery/jquery.min'
		jsonform: 'jsonform/jsonform'
		jsv: 'jsonform/deps/opt/jsv'
	shim:
		underscore:
			exports: '_'
		backbone:
			deps: ['underscore', 'jquery']
			exports: 'Backbone'
		jsonform: ['jquery', 'underscore', 'jsv']
		tipsy: ['jquery']
		jgrowl: ['jquery']
)


require ['jquery', 'structure', 'tipsy', 'jgrowl', 'jsonform', 'rainbow'], ($, App) ->
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

	$('#input_content').jsonForm(
		schema:
			scout_name:
				title: 'Scout Name'
				type: 'string'
				required: true
			match:
				title: 'Match Number'
				type: 'integer'
				maximum: 999
				required: true
			team:
				title: 'Team Number'
				type: 'integer'
				maximum: 9999
				required: true
			alliance:
				title: 'Alliance'
				default: ''
				enum: [
					'Red'
					'Blue'
				]
				required: true
			floor_pickup:
				title: 'Floor Pickup'
				type: 'boolean'
			climb_attempt:
				title: 'climb_attempt'
				type: 'integer'
				enum: [
					0
					1
					2
					3
				]
			penalties_red:
				title: 'Penalties Red'
				type: 'integer'
				required: true
			penalties_yellow:
				title: 'Penalties Yellow'
				type: 'integer'
				required: true
			fouls:
				title: 'fouls'
				type: 'integer'
				required: true
			tech_fouls:
				title: 'tech_fouls'
				type: 'integer'
				required: true
			strategy:
				title: 'Strategy'
				type: 'array'
				uniqueItems: true
				items: {
					enum: [
						'defense'
						'climb'
						'shoot'
						'disabled / broken'
					]
				}
			pyramid:
				title: 'pyramid'
				type: 'integer'
				required: true
			high:
				title: 'high'
				type: 'integer'
				required: true
			middle:
				title: 'middle'
				type: 'integer'
				required: true
			low:
				title: 'low'
				type: 'integer'
				required: true
			miss:
				title: 'miss'
				type: 'integer'
				required: true
			comments:
				title: 'comments'
				type: 'string'
				required: true
		form: [
			{
				key: "strategy"
				type: "checkboxes"
			},
			{
				"key": "comment",
				"type": "textarea"
			}
		]
		onSubmit: (errors, values) ->
			if errors
				$('#res').html('<p>I beg your pardon?</p>')
			else
				$('#res').html("<p>Hello #{values.name}.#{if values.age then "<br/>You are #{values.age}." else ''}</p>")
	)
