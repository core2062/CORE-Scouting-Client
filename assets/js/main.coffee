SERVER = 'http://localhost:5000'

require.config(
	paths:
		underscore: 'components/underscore/underscore'
		backbone: 'components/backbone/backbone'
		jquery: 'components/jquery/jquery.min'
		jsonform: 'jsonform/jsonform'
		jsv: 'jsonform/deps/opt/jsv'
		jquery_ui_core: 'jsonform/deps/opt/jquery.ui.core'
		jquery_ui_widget: 'jsonform/deps/opt/jquery.ui.widget'
		jquery_ui_mouse: 'jsonform/deps/opt/jquery.ui.mouse'
		jquery_ui_sortable: 'jsonform/deps/opt/jquery.ui.sortable'
		bootstrap_dropdown: 'jsonform/deps/opt/bootstrap-dropdown'
		spectrum: 'jsonform/deps/opt/spectrum'
		#ace: 'jsonform/deps/opt/ace/ace'
		#ace_json: 'jsonform/deps/opt/ace/mode-json'
	shim:
		underscore:
			exports: '_'
		backbone:
			deps: ['underscore', 'jquery']
			exports: 'Backbone'
		jsonform: [
			'jquery'
			'underscore'
			'jsv'
			#'jquery_ui_core'
			#'jquery_ui_widget'
			#'jquery_ui_mouse'
			#'jquery_ui_sortable'
			'bootstrap_dropdown'
			'spectrum'
			#'ace'
			#'ace_json'
		]
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
		controls:
			submit: (-> console.log 'blah')
		selected: true
	)
	App.Pages.create(
		name: "output"
	)
	App.Pages.create(
		name: "signup"
		progressbar: true
	)
	App.Pages.create(
		name: "login"
		progressbar: false
	)

	Backbone.history.start()
	# change to default page at startup (if there is no hash fragment)
	if Backbone.history.fragment is ''
		App.Router.navigate(App.Pages.default_page,
			trigger: true
			replace: true
		)

	$.ajax(
		url: SERVER + "/schema/match"
	).done((data) ->
		$('#scouting_form').jsonForm(
			schema: data
			form: [
					key: "scout_name"
				,
					key: "strategy"
					type: "checkboxes"
				,
					key: "match_num"
				,
					key: "match_type"
				,
					key: "team"
				,
					key: "alliance"
				,
					key: "floor_pickup"
				,
					key: "climb_attempt"
				,
					key: "penalties_red"
				,
					key: "penalties_yellow"
				,
					key: "fouls"
				,
					key: "tech_fouls"
				,
					key: "pyramid"
				,
					key: "high"
				,
					key: "middle"
				,
					key: "low"
				,
					key: "miss"
				,
					key: "comment"
					type: "textarea"
				,
					type: "submit",
					title: "submit"
			]
			onSubmit: (errors, values) ->
				console.log errors
				console.log values
				if errors
					notify errors

				$.ajax(
					url: "#{SERVER}/commit/submit",
					data:
						data: values
				).done(
					(data) ->
						notify data
				)
		)
	)
