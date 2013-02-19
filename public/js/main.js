(function() {
  var __slice = [].slice;

  require.config({
    paths: {
      underscore: 'components/underscore/underscore',
      backbone: 'components/backbone/backbone',
      jquery: 'components/jquery/jquery.min'
    },
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

  require(['jquery', 'structure', 'tipsy', 'jgrowl', 'rainbow'], function($, App) {
    var notify, p;
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
    $("label[title], button[title], a[title]").tipsy();
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
	analysis program created by C.O.R.E. 2062.');
    App.Pages.create({
      name: "input",
      selected: true
    });
    App.Pages.create({
      name: "output"
    });
    App.Pages.create({
      name: "signup",
      progressbar: true
    });
    Backbone.history.start();
    if (Backbone.history.fragment === '') {
      return App.Router.navigate(App.Pages.default_page, {
        trigger: true,
        replace: true
      });
    }
  });

}).call(this);
