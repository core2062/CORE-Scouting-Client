(function() {
  var SERVER,
    __slice = [].slice;

  SERVER = 'http://localhost:5000';

  require.config({
    paths: {
      underscore: 'components/underscore/underscore',
      backbone: 'components/backbone/backbone',
      jquery: 'components/jquery/jquery.min',
      jsonform: 'jsonform/jsonform',
      jsv: 'jsonform/deps/opt/jsv',
      jquery_ui_core: 'jsonform/deps/opt/jquery.ui.core',
      jquery_ui_widget: 'jsonform/deps/opt/jquery.ui.widget',
      jquery_ui_mouse: 'jsonform/deps/opt/jquery.ui.mouse',
      jquery_ui_sortable: 'jsonform/deps/opt/jquery.ui.sortable',
      bootstrap_dropdown: 'jsonform/deps/opt/bootstrap-dropdown',
      spectrum: 'jsonform/deps/opt/spectrum'
    },
    shim: {
      underscore: {
        exports: '_'
      },
      backbone: {
        deps: ['underscore', 'jquery'],
        exports: 'Backbone'
      },
      jsonform: ['jquery', 'underscore', 'jsv', 'bootstrap_dropdown', 'spectrum'],
      tipsy: ['jquery'],
      jgrowl: ['jquery']
    }
  });

  require(['jquery', 'structure', 'tipsy', 'jgrowl', 'jsonform', 'rainbow'], function($, App) {
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
      controls: {
        submit: (function() {
          return console.log('blah');
        })
      },
      selected: true
    });
    App.Pages.create({
      name: "output"
    });
    App.Pages.create({
      name: "signup",
      progressbar: true
    });
    App.Pages.create({
      name: "login",
      progressbar: false
    });
    Backbone.history.start();
    if (Backbone.history.fragment === '') {
      App.Router.navigate(App.Pages.default_page, {
        trigger: true,
        replace: true
      });
    }
    return $.ajax({
      url: SERVER + "/schema/match"
    }).done(function(data) {
      return $('#scouting_form').jsonForm({
        schema: data,
        form: [
          {
            key: "scout_name"
          }, {
            key: "strategy",
            type: "checkboxes"
          }, {
            key: "match_num"
          }, {
            key: "match_type"
          }, {
            key: "team"
          }, {
            key: "alliance"
          }, {
            key: "floor_pickup"
          }, {
            key: "climb_attempt"
          }, {
            key: "penalties_red"
          }, {
            key: "penalties_yellow"
          }, {
            key: "fouls"
          }, {
            key: "tech_fouls"
          }, {
            key: "pyramid"
          }, {
            key: "high"
          }, {
            key: "middle"
          }, {
            key: "low"
          }, {
            key: "miss"
          }, {
            key: "comment",
            type: "textarea"
          }, {
            type: "submit",
            title: "submit"
          }
        ],
        onSubmit: function(errors, values) {
          console.log(errors);
          console.log(values);
          if (errors) {
            notify(errors);
          }
          return $.ajax({
            url: "" + SERVER + "/commit/submit",
            data: {
              data: values
            }
          }).done(function(data) {
            return notify(data);
          });
        }
      });
    });
  });

}).call(this);
