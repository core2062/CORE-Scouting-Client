
/*
	toggleJS jQuery Plug-in
*/


(function() {

  (function($) {
    var counter, defaults, toggle;
    $.toggleSwitch = {
      version: "1.0.03",
      setDefaults: function(options) {
        return $.extend(defaults, options);
      }
    };
    defaults = {
      duration: 200,
      easing: "swing",
      labelOn: "On",
      labelOff: "Off",
      resizeHandle: "auto",
      resizeContainer: "auto",
      enableDrag: true,
      enableFx: true,
      allowRadioUncheck: false,
      clickOffset: 120,
      className: "",
      classContainer: "toggle-container",
      classDisabled: "toggle-disabled",
      classFocus: "toggle-focus",
      classLabelOn: "toggle-label-on",
      classLabelOff: "toggle-label-off",
      classHandle: "toggle-handle",
      classHandleActive: "toggle-active-handle",
      init: null,
      change: null,
      click: null,
      disable: null,
      destroy: null
    };
    $.fn.toggleSwitch = function(options) {
      var args, method, result, self;
      method = typeof arguments[0] === "string" && arguments[0];
      args = method && Array.prototype.slice.call(arguments, 1) || arguments;
      self = (this.length === 0 ? null : $.data(this[0], "toggle"));
      if (self && method && this.length) {
        if (method.toLowerCase() === "object") {
          return self;
        } else if (self[method]) {
          result = void 0;
          this.each(function(i) {
            var r;
            r = $.data(this, "toggle")[method].apply(self, args);
            if (i === 0 && r) {
              if (!!r.jquery) {
                return result = $([]).add(r);
              } else {
                result = r;
                return false;
              }
            } else {
              if (!!r && !!r.jquery) {
                return result = result.add(r);
              }
            }
          });
          return result || this;
        } else {
          return this;
        }
      } else {
        return this.each(function() {
          return new toggle(this, options);
        });
      }
    };
    counter = 0;
    $.browser.iphone = navigator.userAgent.toLowerCase().indexOf("iphone") > -1;
    return toggle = function(input, options) {
      var $container, $handle, $input, $offlabel, $offspan, $onlabel, $onspan, allow, bDefaultLabelsUsed, disabled, dragStart, getDragPos, handleRight, id, labelOff, labelOn, mouse, positionHandle, self, width;
      self = this;
      $input = $(input);
      id = ++counter;
      disabled = false;
      width = {};
      mouse = {
        dragging: false,
        clicked: null
      };
      dragStart = {
        position: null,
        offset: null,
        time: null
      };
      options = $.extend({}, defaults, options, (!!$.metadata ? $input.metadata() : {}));
      bDefaultLabelsUsed = options.labelOn === defaults.labelOn && options.labelOff === defaults.labelOff;
      allow = ":checkbox, :radio";
      if (!$input.is(allow)) {
        return $input.find(allow).toggle(options);
      } else {
        if ($.data($input[0], "toggle")) {
          return;
        }
      }
      $.data($input[0], "toggle", self);
      if (options.resizeHandle === "auto") {
        options.resizeHandle = !bDefaultLabelsUsed;
      }
      if (options.resizeContainer === "auto") {
        options.resizeContainer = !bDefaultLabelsUsed;
      }
      this.toggle = function(t) {
        toggle = (arguments.length > 0 ? t : !$input[0].checked);
        return $input.attr("checked", toggle).trigger("change");
      };
      this.disable = function(t) {
        toggle = (arguments.length > 0 ? t : !disabled);
        disabled = toggle;
        $input.attr("disabled", toggle);
        $container[(toggle ? "addClass" : "removeClass")](options.classDisabled);
        if ($.isFunction(options.disable)) {
          return options.disable.apply(self, [disabled, $input, options]);
        }
      };
      this.repaint = function() {
        return positionHandle();
      };
      this.destroy = function() {
        $([$input[0], $container[0]]).unbind(".toggle");
        $(document).unbind(".toggle_" + id);
        $container.after($input).remove();
        $.data($input[0], "toggle", null);
        if ($.isFunction(options.destroy)) {
          return options.destroy.apply(self, [$input, options]);
        }
      };
      if (!$input[0].dataset.labeloff) {
        labelOff = options.labelOff;
      } else {
        labelOff = $input[0].dataset.labeloff;
      }
      if (!$input[0].dataset.labelon) {
        labelOn = options.labelOn;
      } else {
        labelOn = $input[0].dataset.labelon;
      }
      $input.wrap("<div title=\"" + $input[0].title + "\" class=\"" + $.trim(options.classContainer + " " + options.className) + "\" />").after("<div class=\"" + options.classHandle + "\"></div>" + "<div class=\"" + options.classLabelOff + "\"><span><label>" + labelOff + "</label></span></div>" + "<div class=\"" + options.classLabelOn + "\"><span><label>" + labelOn + "</label></span></div>");
      $container = $input.parent();
      $handle = $input.siblings("." + options.classHandle);
      $offlabel = $input.siblings("." + options.classLabelOff);
      $offspan = $offlabel.children("span");
      $onlabel = $input.siblings("." + options.classLabelOn);
      $onspan = $onlabel.children("span");
      if (options.resizeHandle || options.resizeContainer) {
        width.onspan = $onspan.outerWidth();
        width.offspan = $offspan.outerWidth();
      }
      if (options.resizeHandle) {
        width.handle = Math.min(width.onspan, width.offspan);
        $handle.css("width", width.handle);
      } else {
        width.handle = $handle.width();
      }
      if (options.resizeContainer) {
        width.container = Math.max(width.onspan, width.offspan) + width.handle + 20;
        $container.css("width", width.container);
        $offlabel.css("width", width.container);
      } else {
        width.container = $container.width();
      }
      handleRight = width.container - width.handle;
      positionHandle = function(animate) {
        var checked, x;
        checked = $input[0].checked;
        x = (checked ? handleRight : 0);
        animate = (arguments.length > 0 ? arguments[0] : true);
        if (animate && options.enableFx) {
          $handle.stop().animate({
            left: x
          }, options.duration, options.easing);
          $onlabel.stop().animate({
            width: x + 4
          }, options.duration, options.easing);
          $onspan.stop().animate({
            marginLeft: x - handleRight
          }, options.duration, options.easing);
          return $offspan.stop().animate({
            marginRight: -x
          }, options.duration, options.easing);
        } else {
          $handle.css("left", x);
          $onlabel.css("width", x + 4);
          $onspan.css("marginLeft", x - handleRight);
          return $offspan.css("marginRight", -x);
        }
      };
      positionHandle(false);
      getDragPos = function(e) {
        return e.pageX || (e.originalEvent.changedTouches ? e.originalEvent.changedTouches[0].pageX : 0);
      };
      $container.bind("mousedown.toggle touchstart.toggle", function(e) {
        if ($(e.target).is(allow) || disabled || (!options.allowRadioUncheck && $input.is(":radio:checked"))) {
          return;
        }
        e.preventDefault();
        mouse.clicked = $handle;
        dragStart.position = getDragPos(e);
        dragStart.offset = dragStart.position - (parseInt($handle.css("left"), 10) || 0);
        dragStart.time = (new Date()).getTime();
        return false;
      });
      if (options.enableDrag) {
        $(document).bind("mousemove.toggle_" + id + " touchmove.toggle_" + id, function(e) {
          var pct, x;
          if (mouse.clicked !== $handle) {
            return;
          }
          e.preventDefault();
          x = getDragPos(e);
          if (x !== dragStart.offset) {
            mouse.dragging = true;
            $container.addClass(options.classHandleActive);
          }
          pct = Math.min(1, Math.max(0, (x - dragStart.offset) / handleRight));
          $handle.css("left", pct * handleRight);
          $onlabel.css("width", pct * handleRight + 4);
          $offspan.css("marginRight", -pct * handleRight);
          $onspan.css("marginLeft", -(1 - pct) * handleRight);
          return false;
        });
      }
      $(document).bind("mouseup.toggle_" + id + " touchend.toggle_" + id, function(e) {
        var changed, checked, pct, x;
        if (mouse.clicked !== $handle) {
          return false;
        }
        e.preventDefault();
        changed = true;
        if (!mouse.dragging || (((new Date()).getTime() - dragStart.time) < options.clickOffset)) {
          checked = $input[0].checked;
          $input.attr("checked", !checked);
          if ($.isFunction(options.click)) {
            options.click.apply(self, [!checked, $input, options]);
          }
        } else {
          x = getDragPos(e);
          pct = (x - dragStart.offset) / handleRight;
          checked = pct >= 0.5;
          if ($input[0].checked === checked) {
            changed = false;
          }
          $input.attr("checked", checked);
        }
        $container.removeClass(options.classHandleActive);
        mouse.clicked = null;
        mouse.dragging = null;
        if (changed) {
          $input.trigger("change");
        } else {
          positionHandle();
        }
        return false;
      });
      $input.bind("change.toggle", function() {
        var $radio, el;
        positionHandle();
        if ($input.is(":radio")) {
          el = $input[0];
          $radio = $((el.form ? el.form[el.name] : ":radio[name=" + el.name + "]"));
          $radio.filter(":not(:checked)").toggle("repaint");
        }
        if ($.isFunction(options.change)) {
          return options.change.apply(self, [$input, options]);
        }
      }).bind("focus.toggle", function() {
        return $container.addClass(options.classFocus);
      }).bind("blur.toggle", function() {
        return $container.removeClass(options.classFocus);
      });
      if ($.isFunction(options.click)) {
        $input.bind("click.toggle", function() {
          return options.click.apply(self, [$input[0].checked, $input, options]);
        });
      }
      if ($input.is(":disabled")) {
        this.disable(true);
      }
      if ($.browser.msie) {
        $container.find("*").andSelf().attr("unselectable", "on");
        $input.bind("click.toggle", function() {
          return $input.triggerHandler("change.toggle");
        });
      }
      if ($.isFunction(options.init)) {
        return options.init.apply(self, [$input, options]);
      }
    };
  })(jQuery);

}).call(this);
