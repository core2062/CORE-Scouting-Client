(function() {
  var __slice = [].slice;

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
      App.Router.navigate(App.Pages.default_page, {
        trigger: true,
        replace: true
      });
    }
    return $('#scouting_form').jsonForm({
      schema: {
        scout_name: {
          title: 'Scout Name',
          type: 'string',
          required: true
        },
        match: {
          title: 'Match Number',
          type: 'integer',
          maximum: 999,
          required: true
        },
        team: {
          title: 'Team Number',
          type: 'integer',
          maximum: 9999,
          required: true
        },
        alliance: {
          title: 'Alliance',
          "default": '',
          "enum": ['Red', 'Blue'],
          required: true
        },
        floor_pickup: {
          title: 'Floor Pickup',
          type: 'boolean'
        },
        climb_attempt: {
          title: 'climb_attempt',
          type: 'integer',
          "enum": [0, 1, 2, 3]
        },
        penalties_red: {
          title: 'Penalties Red',
          type: 'integer',
          required: true
        },
        penalties_yellow: {
          title: 'Penalties Yellow',
          type: 'integer',
          required: true
        },
        fouls: {
          title: 'fouls',
          type: 'integer',
          required: true
        },
        tech_fouls: {
          title: 'tech_fouls',
          type: 'integer',
          required: true
        },
        strategy: {
          title: 'Strategy',
          type: 'array',
          uniqueItems: true,
          items: {
            "enum": ['defense', 'climb', 'shoot', 'disabled / broken']
          }
        },
        pyramid: {
          title: 'pyramid',
          type: 'integer',
          required: true
        },
        high: {
          title: 'high',
          type: 'integer',
          required: true
        },
        middle: {
          title: 'middle',
          type: 'integer',
          required: true
        },
        low: {
          title: 'low',
          type: 'integer',
          required: true
        },
        miss: {
          title: 'miss',
          type: 'integer',
          required: true
        },
        comment: {
          title: 'comments',
          type: 'string',
          required: true
        }
      },
      form: [
        {
          key: "scout_name"
        }, {
          key: "strategy",
          type: "checkboxes"
        }, {
          key: "match"
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
          url: "http://fiddle.jshell.net/favicon.png",
          data: {
            data: values
          }
        }).done(function(data) {
          return notify(data);
        });
      }
    });
  });

}).call(this);
