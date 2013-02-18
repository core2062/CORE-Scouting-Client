# randomly sorted list of colors
colorList = ['#FF9200','#B900B9','#CEF800','#0269B8','#00D40E','#2303C1','#7702BD','#FFCD00','#FF6200','#FF0700','#FFFD00','#00B88F']

window.rainbow = (seizureMode) ->
	window.rainbow_vars =
		i: 0
		body: document.getElementsByTagName("body")[0]
		c: document.getElementById("c")
		s: document.getElementById("s")
		d: document.getElementById("d")
	setInterval(
		(->
			window.rainbow_vars.i++
			if window.rainbow_vars.i >= colorList.length - 0 then window.rainbow_vars.i = 0
			console.log window.rainbow_vars.i
			window.rainbow_vars.body.style.backgroundColor = colorList[window.rainbow_vars.i]
			#window.rainbow_vars.c.setAttribute "fill", "#{colorList[window.rainbow_vars.i + 1]} !important"
			#window.rainbow_vars.s.style.fill = "#{colorList[window.rainbow_vars.i + 2]} !important"
			#window.rainbow_vars.d.style.fill = "#{colorList[window.rainbow_vars.i + 3]} !important"
		),
		25,
	)