(function() {
  var colorList;

  colorList = ['#FF9200', '#B900B9', '#CEF800', '#0269B8', '#00D40E', '#2303C1', '#7702BD', '#FFCD00', '#FF6200', '#FF0700', '#FFFD00', '#00B88F'];

  window.rainbow = function(seizureMode) {
    window.rainbow_vars = {
      i: 0,
      body: document.getElementsByTagName("body")[0]
    };
    return setInterval((function() {
      window.rainbow_vars.i++;
      if (window.rainbow_vars.i >= colorList.length - 0) {
        window.rainbow_vars.i = 0;
      }
      console.log(window.rainbow_vars.i);
      return window.rainbow_vars.body.style.backgroundColor = colorList[window.rainbow_vars.i];
    }), 25);
  };

}).call(this);
