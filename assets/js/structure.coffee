define(['jquery', 'backbone', 'jgrowl', 'localstorage'], ($, Backbone) ->
	notify = (args...) -> $("#jGrowl-container").jGrowl args...

	class ProgressBar extends Backbone.View
		el: $ '#progressbar'  # element already exists in markup

		render: ->
			if @model.current_page().get('progressbar')
				@$el.css opacity: ''
			else
				@$el.css opacity: 0

		initialize: ->
			_.bindAll @
			@model.on('change:selected', @render)


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
			@model.on('change:selected', @render)


	###*
	 * represents the current user. holds all the user data and interacts with
	   the server for account functions
	###
	class Account extends Backbone.Model
		localStorage: new Backbone.LocalStorage("Account")
		#defaults:
		#	token: ''
		#	name: 'User'

		login: (username, password) ->
			$.ajax(
				url: "#{SERVER}/user/login"
				type: "POST"
				data:
					username: username
					password: password
			).done(
				(data) ->
					App.Account.save(token: data['token'])
			).fail(
				(jqXHR, textStatus, errorThrown) ->
					notify "#{errorThrown}: #{textStatus}",
						theme: 'error'
			)

		logout: () ->
			console.log 'logg\'n out'
			@save token: ''

		initialize: ->
			_.bindAll @



	###*
	 * modifies the navbar to show the correct user & login/logout controls
	###
	class AccountView extends Backbone.View
		el: $ '#account_bar'  # element already exists in markup

		render: ->
			# change name displayed in account bar
			@$el.find('[for="account_nav"]')[0].innerHTML = @model.get('name')
			if @model.get('token') is ''
				@$el.find('[for="login_nav"]').attr('original-title': 'Login')
				$('#login_icon')[0].style.display = 'inline'
				$('#logout_icon')[0].style.display = 'none'
			else
				@$el.find('[for="login_nav"]').attr('original-title': 'Logout')
				$('#login_icon')[0].style.display = 'none'
				$('#logout_icon')[0].style.display = 'inline'

		initialize: ->
			_.bindAll @
			@model.on('change:token', @render)
			@model.view = @
			@model.fetch()
			#gay hack cuz localstorage is really just made for collections
			for k,v of @model.get(0)
				@model.set(k, v)
			@model.set(0: undefined)


	class Router extends Backbone.Router
		# forward changes in the route to the navigation view
		routes:
			"*page": "change_page"

		change_page: (page) ->
			if page isnt "" then @model.change_page(page)

		initialize: (options) ->
			# assign a model during init like in a view
			@model = options.model


	class Page extends Backbone.Model
		defaults:
			name: ''
			login_required: false
			selected: false # value used by Pages for changing the active page
			progressbar: false

			# bind-able functions... empty by default
			first_load: (->)
			on_load: (->)
			on_unload: (->)
			controls: {
				#submit: (->)
			}

		trigger_control: (name) ->
			@controls[name]()

		# represents a page in the application
		sync: ->
			false # changes to Pages don't get stored anywhere

		onchange: ->
			if @get('selected')
				@get('on_load').call()
			else
				@get('on_unload').call()

		initialize: ->
			_.bindAll @
			@on('change:selected', @onchange)

			# for page specific init functions
			@get('first_load').call()


	class PageView extends Backbone.View
		render: ->
			if @model.get('selected')
				@el.style.display = 'block' # show

				# hide all controls
				for control in $('#local_controls button')
					control.style.display = 'none'
					$(control).unbind("click")

				# display all that are defined
				for control_name in _.keys(@model.get('controls'))
					control = $("##{control_name}_ctrl")[0]
					control.style.display = 'inline-block'
					$(control).bind("click", @model.get('controls')[control_name])
			else
				@el.style.display = 'none' # hide

		initialize: ->
			_.bindAll @

			@model.on('change:selected', @render)
			@model.view = @
			@el = $("\##{@model.get('name')}_content")[0]


	class PagesCollection extends Backbone.Collection
		# to determine what should be rendered in the navbar on any given page
		model: Page
		default_page: 'input'

		added_page: (page_model) ->
			#used to create the view for a page after it has been added
			new PageView({model: page_model})

		control: (name) ->

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
				notify "#{page_name} doesn't exist, redirecting to #{@default_page}...",
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
			return @where(selected: true)[0]

		initialize: ->
			_.bindAll @
			@on("add", @added_page)


	class AppView extends Backbone.View
		initialize: ->
			_.bindAll @

			@Pages = new PagesCollection()
			@Router = new Router model: @Pages
			@NavView = new NavView model: @Pages
			@ProgressBar = new ProgressBar model: @Pages

			@Account = new Account()
			@AccountView = new AccountView model: @Account

	window.App = new AppView()
	return App
)