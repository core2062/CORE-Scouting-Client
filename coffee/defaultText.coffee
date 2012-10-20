
###
	DefaultText jQuery Plugin
	this plugin handles presentation and removal of default text from text inputs as well as triggering tipsy notifications that present this default text while the input is being used
###

#TODO: make into real jquery plugin?

defaultText ->
	inputs = $('input[defaultText]')
$ ->
	$.fn.defaultText = (options) ->
		method = typeof arguments[0] is "string" and arguments[0]
		args = method and Array::slice.call(arguments, 1) or arguments
		#get a reference to the first toggle found
		self = (if (@length is 0) then null else $.data(this[0], "toggle"))

		#if a method is supplied, execute it for non-empty results
		if self and method and @length
			#if request a copy of the object, return it
			if method.toLowerCase() is "object"
				self
			#if method is defined, run it and return either it's results or the chain
			else if self[method]
				#define a result variable to return to the jQuery chain
				result = undefined
				@each (i) ->
					#apply the method to the current element
					r = $.data(this, "toggle")[method].apply(self, args)
					#if first iteration we need to check if we're done processing or need to add it to the jquery chain
					if i is 0 and r
						#if this is a jQuery item, we need to store them in a collection
						unless not r.jquery
							result = $([]).add(r)
						else #otherwise, just store the result and stop executing
							result = r
							#since we're a non-jQuery item, just cancel processing further items
							false
						#keep adding jQuery objects to the results
					else result = result.add(r) if !!r and !!r.jquery

				#return either the results (which could be a jQuery object) or the original chain
				result or this
				#everything else, return the chain
			else
				this #initializing request (only do if toggle not already initialized)
		else
			#create a new toggle for each object found
			@each ->
				new applyDefaultText(this, options)
	
	applyDefaultText = (input) ->