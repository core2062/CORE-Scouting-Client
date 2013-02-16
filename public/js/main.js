(function() {
  var __slice = [].slice,
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  require.config({
    shim: {
      underscore: {
        exports: '_'
      },
      backbone: {
        deps: ['underscore', 'jquery'],
        exports: 'Backbone'
      },
      jsonform: {
        deps: ['underscore', 'jquery'],
        exports: 'jsonform'
      },
      tipsy: ['jquery'],
      jgrowl: ['jquery']
    }
  });

  require(['jquery', 'backbone', 'tipsy', 'jgrowl'], function($, Backbone) {
    var Account, AccountView, AppView, NavView, Page, PageView, PagesCollection, ProgressBar, Router, notify, p;
    window.onerror = function(msg, url, line) {
      notify("errorMsg: " + msg + " on line " + line, {
        theme: 'error',
        sticky: true
      });
      return false;
    };
    $("input, textarea").focus(function() {
      return $(this).parentsUntil($("form"), 'fieldset').addClass('focus');
    });
    $("input, textarea").focusout(function() {
      return $(this).parentsUntil($("form"), 'fieldset').removeClass('focus');
    });
    $(".clearIcon span").click(function() {
      var input;
      input = this.previousSibling;
      input.value = "";
      return input.focus();
    });
    String.prototype.title_case = function() {
      return this.replace(/\w\S*/g, function(txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
      });
    };
    $("label[title], button[title]").tipsy();
    $("input[title]").tipsy({
      trigger: "focus",
      gravity: "w"
    });
    $(".toggle-container[title]").tipsy({
      trigger: "hover",
      gravity: "w"
    });
    p = function(text) {
      return console.log(text);
    };
    notify = function() {
      var args, _ref;
      args = 1 <= arguments.length ? __slice.call(arguments, 0) : [];
      return (_ref = $("#jGrowl-container")).jGrowl.apply(_ref, args);
    };
    p('Hello and welcome to the CSD, a intuitive scouting database and\
	analysis program created by Sean Lang of CORE 2062.');
    ProgressBar = (function(_super) {

      __extends(ProgressBar, _super);

      function ProgressBar() {
        return ProgressBar.__super__.constructor.apply(this, arguments);
      }

      ProgressBar.prototype.el = $('#progressbar');

      ProgressBar.prototype.render = function() {
        if (this.model.current_page().get('progressbar')) {
          return this.$el.css({
            opacity: 'auto'
          });
        } else {
          return this.$el.css({
            opacity: 0
          });
        }
      };

      ProgressBar.prototype.initialize = function() {
        _.bindAll(this);
        return this.model.bind('change', this.render);
      };

      return ProgressBar;

    })(Backbone.View);
    /**
    	 * modify the navbar to highlight the correct current page
    */

    NavView = (function(_super) {

      __extends(NavView, _super);

      function NavView() {
        return NavView.__super__.constructor.apply(this, arguments);
      }

      NavView.prototype.el = $('#navbar');

      NavView.prototype.render = function() {
        var page;
        page = this.model.current_page().get('name');
        document.title = "" + (page.replace("_", " ").title_case()) + " | CSD";
        this.$el.find("\#" + page + "_nav").attr("checked", true);
        return notify("(nav) page: " + page);
      };

      NavView.prototype.initialize = function() {
        _.bindAll(this);
        return this.model.bind('change:selected', this.render);
      };

      return NavView;

    })(Backbone.View);
    /**
    	 * modifies the navbar to show the correct user & login/logout controls
    */

    AccountView = (function(_super) {

      __extends(AccountView, _super);

      function AccountView() {
        return AccountView.__super__.constructor.apply(this, arguments);
      }

      AccountView.prototype.el = $('#account_bar');

      AccountView.prototype.render = function() {
        return this.$el.find('[for="account_nav"]').html(this.model.get('name'));
      };

      AccountView.prototype.initialize = function() {
        _.bindAll(this);
        this.model.bind('change:name', this.render);
        return this.model.view = this;
      };

      return AccountView;

    })(Backbone.View);
    Router = (function(_super) {

      __extends(Router, _super);

      function Router() {
        return Router.__super__.constructor.apply(this, arguments);
      }

      Router.prototype.routes = {
        "*page": "change_page"
      };

      Router.prototype.initialize = function(options) {
        return this.model = options.model;
      };

      Router.prototype.change_page = function(page) {
        if (page !== "") {
          return this.model.change_page(page);
        }
      };

      return Router;

    })(Backbone.Router);
    Page = (function(_super) {

      __extends(Page, _super);

      function Page() {
        return Page.__super__.constructor.apply(this, arguments);
      }

      Page.prototype.sync = function() {
        return false;
      };

      Page.prototype.name = '';

      Page.prototype.login_required = false;

      Page.prototype.selected = false;

      Page.prototype.progressbar = false;

      return Page;

    })(Backbone.Model);
    PageView = (function(_super) {

      __extends(PageView, _super);

      function PageView() {
        return PageView.__super__.constructor.apply(this, arguments);
      }

      PageView.prototype.render = function() {
        if (this.model.get('selected')) {
          return this.el.style.display = 'block';
        } else {
          return this.el.style.display = 'none';
        }
      };

      PageView.prototype.initialize = function() {
        _.bindAll(this);
        this.model.bind('change:selected', this.render);
        this.model.view = this;
        return this.el = $("\#" + (this.model.get('name')) + "_content")[0];
      };

      return PageView;

    })(Backbone.View);
    PagesCollection = (function(_super) {

      __extends(PagesCollection, _super);

      function PagesCollection() {
        return PagesCollection.__super__.constructor.apply(this, arguments);
      }

      PagesCollection.prototype.model = Page;

      PagesCollection.prototype.default_page = 'input';

      PagesCollection.prototype.initialize = function() {
        _.bindAll(this);
        return this.bind("add", this.added_page);
      };

      PagesCollection.prototype.added_page = function(page_model) {
        return new PageView({
          model: page_model
        });
      };

      PagesCollection.prototype.change_page = function(page_name) {
        var page;
        page = this.find(function(page_obj) {
          return page_obj.get('name') === page_name;
        });
        try {
          this.current_page().set({
            selected: false
          });
        } catch (_error) {}
        if (page != null) {
          return page.set({
            selected: true
          });
        } else {
          notify("page doesn't exist, redirecting to " + this.default_page + "...", {
            theme: 'error',
            sticky: true
          });
          return App.Router.navigate(this.default_page, {
            trigger: true,
            replace: true
          });
        }
      };

      /**
      		 * @return Page the model of the active page
      */


      PagesCollection.prototype.current_page = function() {
        return this.find(function(page_obj) {
          return page_obj.get('selected');
        });
      };

      return PagesCollection;

    })(Backbone.Collection);
    /**
    	 * represents the current user. holds all the user data and interacts with
          the server for account functions
    */

    Account = (function(_super) {

      __extends(Account, _super);

      function Account() {
        return Account.__super__.constructor.apply(this, arguments);
      }

      return Account;

    })(Backbone.Model);
    AppView = (function(_super) {

      __extends(AppView, _super);

      function AppView() {
        return AppView.__super__.constructor.apply(this, arguments);
      }

      AppView.prototype.initialize = function() {
        this.NavView = new NavView({
          model: Pages
        });
        this.Router = new Router({
          model: Pages
        });
        this.Account = new Account;
        this.AccountView = new AccountView({
          model: this.Account
        });
        return this.ProgressBar = new ProgressBar({
          model: Pages
        });
      };

      return AppView;

    })(Backbone.View);
    return $(function() {
      window.Pages = new PagesCollection();
      Pages.create({
        name: "input",
        selected: true
      });
      Pages.create({
        name: "output"
      });
      Pages.create({
        name: "signup",
        progressbar: true
      });
      window.App = new AppView();
      Backbone.history.start();
      if (Backbone.history.fragment === '') {
        return App.Router.navigate(Pages.default_page, {
          trigger: true,
          replace: true
        });
      }
    });
  });

}).call(this);
