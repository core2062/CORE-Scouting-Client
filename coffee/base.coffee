#    ____      _____    ______
#  _[░░░░]_   [░░░░░]  [░░░░░░]
# [░]````[░] [░]_`_`   [░]```[░]
# [░]     _   `[░░░]_  [░]    [░]
# [░]____[░]   _`_`[░] [░]___[░]
#  `[░░░░]`   [░░░░░]  [░░░░░░]
#    ````      `````    ``````
# --- CORE Scouting Database ---


#       ________           ___________    _____________
#     _[░░░░░░░░]_       _[░░░░░░░░░░░]  [░░░░░░░░░░░░░]_
#   _[░░░]````[░░░]_    [░░░░░]```````   [░░░]``````[░░░░]
#  [░░░]`      `[░░░]  [░░░░]`           [░░░]       `[░░░]
# [░░░]          ```    [░░░░░]___       [░░░]         [░░░]
# [░░░]                  ```[░░░░░]__    [░░░]         [░░░]
# [░░░]          ___         ``[░░░░░]   [░░░]         [░░░]
#  [░░░]_      _[░░░]           _[░░░░]  [░░░]       _[░░░]
#   `[░░░]____[░░░]`    _______[░░░░░]   [░░░]______[░░░░]
#     `[░░░░░░░░]`  0  [░░░░░░░░░░░]` 0  [░░░░░░░░░░░░░]`  0
#       ````````        ```````````       `````````````
#               --- CORE Scouting Database ---

#Chosen Select Box
#$("select").chosen()

#ToggleJS
#$(":checkbox").toggleSwitch()

#Tipsy
$("a[title], label[title], button[title], textarea[title]").tipsy()

$("input[title]").tipsy
	trigger: "focus"
	gravity: "w"

$(".toggle-container[title]").tipsy
	trigger: "hover"
	gravity: "w"

# friendly little aliases
p = (text) -> console.log text
notify = (args...) -> $("#jGrowl-container").jGrowl args...

p 'Hello and welcome to the CSD, a intuitive scouting database and analysis program created by Sean Lang of CORE 2062.'


# Views

class ProgressBar extends Backbone.View
	el: $ '#progressbar'  # element already exists in markup

	render: ->
		if @model.current_page().get('progressbar')
			@$el.css opacity:1
		else
			@$el.css opacity:0

	initialize: ->
		_.bindAll @
		@model.bind('change:selected', @render)


class NavView extends Backbone.View
	# modify the navbar to highlight the correct current page

	el: $ '#navbar'  # element already exists in markup

	render: ->
		page = @model.current_page().get('name')

		# set the title of the page
		document.title = "#{page.replace("_", " ").title_case()} | CSD"

		# ensure that the correct navbar button is selected
		# since it is a radio button, it unselects anything else
		@$el.find("\##{page}_nav").attr "checked", true 

		notify "(nav) page: #{page}",

	initialize: ->
		_.bindAll @
		@model.bind('change:selected', @render)


class AccountView extends Backbone.View
	# NavView modifies the navbar to show the correct subpages for the current page
	el: $ '#account_bar'  # element already exists in markup

	render: ->
		@$el.find('[for="account_nav"]').html(@model.get('name')) # change name displayed in account bar

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
	# represents a page in the application
	sync: ->
		false # changes to Pages don't get stored anywhere

	name: ''
	login_required: false
	selected: false # value used by Pages for changing the active page
	progressbar: false


class PageView extends Backbone.View
	render: ->
		# fade in page

	unrender: ->
		#unfade page

	initialize: ->
		_.bindAll @
		@model.bind('change', @render)
		@model.view = @;
		@el = $ "\##{@model.get('name')}_content"  # element already exists in markup

					
class PagesCollection extends Backbone.Collection
	# to determine what should be rendered in the navbar on any given page
	model: Page
	default_page: 'home'

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
			@current_page().set(selected: false) # deselect the current page (if it's set)

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

	current_page: ->
		#return the model of the active page
		@find(
			(page_obj) ->
				return page_obj.get('selected')
		)



class Account extends Backbone.Model
	# holds all the user data and interacts with the server for account functions

	#defaults: #default user object for user who isn't logged in (no cookie is stored for this user)



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
		name: "synopsis"
	)
	Pages.create(
		name: "home"
	)
	Pages.create(
		name: "signup"
		progressbar: true
	)
	Pages.create(
		name: "account"
	)
	Pages.create(
		name: "login"
	)

	window.App = new AppView()
	Backbone.history.start()

	# change to default page at startup (if there is no hash fragment)
	if Backbone.history.fragment is ''
		App.Router.navigate(Pages.default_page,
			trigger: true
			replace: true
		)


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
$("input, textarea").focus ->
	$(this).parentsUntil($("form"), 'fieldset').addClass 'focus' #set fieldset class on focus

$("input, textarea").focusout ->
	$(this).parentsUntil($("form"), 'fieldset').removeClass 'focus'

$(".clearIcon span").click -> #clear input icon
	input = @previousSibling
	input.value = ""
	input.focus()

#general functions
String::title_case = ->
	@replace /\w\S*/g, (txt) ->
		txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase()