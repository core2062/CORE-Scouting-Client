(function() {
  var __slice = [].slice;

  window.SERVER = 'http://10.120.162.5:5000';

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
      spectrum: 'jsonform/deps/opt/spectrum',
      localstorage: "components/backbone.localStorage/backbone.localStorage"
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
      selected: true,
      first_load: function() {
        return $.ajax({
          url: SERVER + "/schema/match"
        }).done(function(data) {
          return $('#scouting_form').jsonForm({
            schema: data,
            form: [
              {
                key: 'match_num'
              }, {
                key: 'match_type'
              }, {
                key: 'team'
              }, {
                key: 'alliance'
              }, {
                key: "defends"
              }, {
                key: "climbs"
              }, {
                key: "shoots"
              }, {
                key: "disabled"
              }, {
                key: "no_show"
              }, {
                key: 'floor_pickup'
              }, {
                key: 'climb_attempt'
              }, {
                key: 'penalties_red'
              }, {
                key: 'penalties_yellow'
              }, {
                key: 'fouls'
              }, {
                key: 'tech_fouls'
              }, {
                key: 'auto_high'
              }, {
                key: 'auto_middle'
              }, {
                key: 'auto_low'
              }, {
                key: 'auto_miss'
              }, {
                key: 'pyramid'
              }, {
                key: 'high'
              }, {
                key: 'middle'
              }, {
                key: 'low'
              }, {
                key: 'miss'
              }, {
                key: "comment",
                type: "textarea"
              }, {
                type: "submit",
                title: "submit"
              }
            ],
            onSubmitValid: function(values) {
              console.log(values);
              return $.ajax({
                url: "" + SERVER + "/submit",
                type: "POST",
                data: {
                  data: JSON.stringify(values)
                }
              }).done(function(data) {
                return notify(data['message']);
              });
            }
          });
        });
      }
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
      controls: {
        submit: function() {
          return App.Account.login($('#username').val(), $('#password').val());
        }
      },
      on_load: function() {
        if (App.Account.get('token') !== '') {
          return App.Account.logout();
        }
      },
      progressbar: false
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
