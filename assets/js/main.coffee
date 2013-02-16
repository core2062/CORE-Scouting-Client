require.config(
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
require ['jquery', 'backbone', 'tipsy', 'jgrowl'], ($, Backbone) ->
	#Chosen Select Box
	#$("select").chosen()

	#TODO: float google +1 button left w/out 4px overhang
	#TODO: make startup script to warn bad browsers
	#TODO: add stuff to prefetch subpages???
	#TODO: add error message for old browsers

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
	$("label[title], button[title]").tipsy()

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
	analysis program created by Sean Lang of CORE 2062.'


	class ProgressBar extends Backbone.View
		el: $ '#progressbar'  # element already exists in markup

		render: ->
			if @model.current_page().get('progressbar')
				@$el.css opacity: ''
			else
				@$el.css opacity: 0

		initialize: ->
			_.bindAll @
			p @model
			@model.bind('change:selected', @render)


	###*
	 * modify the navbar to highlight the correct current page
	###
	class NavView extends Backbone.View
		el: $ '#navbar'  # element already exists in markup

		render: ->
			page = @model.current_page().get('name')

			# set the title of the page
			document.title = "#{page.replace("_", " ").title_case()} | CSD"

			# ensure that the correct navbar button is selected
			# since it is a radio button, it unselects anything else
			@$el.find("\##{page}_nav").attr "checked", true

			notify "(nav) page: #{page}"

		initialize: ->
			_.bindAll @
			@model.bind('change:selected', @render)

	###*
	 * modifies the navbar to show the correct user & login/logout controls
	###
	class AccountView extends Backbone.View
		el: $ '#account_bar'  # element already exists in markup

		render: ->
			# change name displayed in account bar
			@$el.find('[for="account_nav"]').html(@model.get('name'))

		initialize: ->
			_.bindAll @
			@model.bind('change:name', @render)
			@model.view = @


	# Models/Controllers

	class Router extends Backbone.Router
		# forward changes in the route to the navigation view
		routes:
			"*page": "change_page"

		initialize: (options) ->
			# assign a model during init like in a view
			@model = options.model

		change_page: (page) ->
			if page isnt "" then @model.change_page(page)


	class Page extends Backbone.Model
		defaults:
			name: ''
			login_required: false
			selected: false # value used by Pages for changing the active page
			progressbar: false

			# bind-able functions... empty by default
			onload: (->)
			onunload: (->)

		# represents a page in the application
		sync: ->
			false # changes to Pages don't get stored anywhere

		onchange: ->
			if @get('selected')
				@get('onload').call()
			else
				@get('onunload').call()

		initialize: ->
			_.bindAll @
			@bind('change:selected', @onchange)



	class PageView extends Backbone.View
		render: ->
			if @model.get('selected')
				@el.style.display = 'block' # show
			else
				@el.style.display = 'none' # hide

		initialize: ->
			_.bindAll @
			@model.bind('change:selected', @render)
			@model.view = @
			@el = $("\##{@model.get('name')}_content")[0]

	class PagesCollection extends Backbone.Collection
		# to determine what should be rendered in the navbar on any given page
		model: Page
		default_page: 'input'

		initialize: ->
			_.bindAll @
			@bind("add", @added_page)

		added_page: (page_model) ->
			#used to create the view for a page after it has been added
			new PageView({model: page_model})

		change_page: (page_name) ->
			# update the active page. this should only be called by the router
			page = @find(
				(page_obj) ->
					return page_obj.get('name') is page_name
			)

			try
				# deselect the current page (if it's set)
				@current_page().set(selected: false)

			if page?
				page.set(selected: true)
			else
				# make jgrowl error and don't change page if page doesn't exist
				notify "page doesn't exist, redirecting to #{@default_page}...",
					theme: 'error'
					sticky: true

				App.Router.navigate(@default_page,
					trigger: true
					replace: true
				)

		###*
		 * @return Page the model of the active page
		###
		current_page: ->
			return @find(
				(page_obj) ->
					return page_obj.get('selected')
			)


	###*
	 * represents the current user. holds all the user data and interacts with
       the server for account functions
	###
	class Account extends Backbone.Model
		#defaults: #default user object for user who isn't logged in (no
		#cookie is stored for this user)



	class AppView extends Backbone.View

		initialize: ->
			@NavView = new NavView model: Pages
			@Router = new Router model: Pages

			@Account = new Account
			@AccountView = new AccountView model: @Account

			@ProgressBar = new ProgressBar model: Pages

	$ ->
		window.Pages = new PagesCollection()

		Pages.create(
			name: "input"
			selected: true
			onload: ->
				p 'load input'
			onunload: ->
				p "unload input"
		)
		Pages.create(
			name: "output"
		)
		Pages.create(
			name: "signup"
			progressbar: true
		)

		window.App = new AppView()
		Backbone.history.start()

		# change to default page at startup (if there is no hash fragment)
		if Backbone.history.fragment is ''
			App.Router.navigate(Pages.default_page,
				trigger: true
				replace: true
			)

