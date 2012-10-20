
/*
	DefaultText jQuery Plugin
	this plugin handles presentation and removal of default text from text inputs as well as triggering tipsy notifications that present this default text while the input is being used
*/


(function() {

  defaultText(function() {
    var inputs;
    return inputs = $('input[defaultText]');
  });

  $(function() {
    var applyDefaultText;
    $.fn.defaultText = function(options) {
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
          return new applyDefaultText(this, options);
        });
      }
    };
    return applyDefaultText = function(input) {};
  });

}).call(this);
