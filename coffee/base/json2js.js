(function($) {

	var escapeable = /["\\\x00-\x1f\x7f-\x9f]/g,
		meta = {
			'\b': '\\b',
			'\t': '\\t',
			'\n': '\\n',
			'\f': '\\f',
			'\r': '\\r',
			'"': '\\"',
			'\\': '\\\\'
		};

	/*
	* jQuery.toJSON
	* Converts the given argument into a JSON respresentation.
	*
	* @param o {Mixed} The json-serializble *thing* to be converted
	*
	* If an object has a toJSON prototype, that will be used to get the representation.
	* Non-integer/string keys are skipped in the object, as are keys that point to a
	* function.
	*
	*/
	$.toJSON = typeof JSON === 'object' && JSON.stringify ? JSON.stringify : function(o) {

		if (o === null) {
			return 'null';
		}

		var type = typeof o;

		if (type === 'undefined') {
			return undefined;
		}
		if (type === 'number' || type === 'boolean') {
			return '' + o;
		}
		if (type === 'string') {
			return $.quoteString(o);
		}
		if (type === 'object') {
			if (typeof o.toJSON === 'function') {
				return $.toJSON(o.toJSON());
			}
			if (o.constructor === Date) {
				var month = o.getUTCMonth() + 1,
					day = o.getUTCDate(),
					year = o.getUTCFullYear(),
					hours = o.getUTCHours(),
					minutes = o.getUTCMinutes(),
					seconds = o.getUTCSeconds(),
					milli = o.getUTCMilliseconds();

				if (month < 10) {
					month = '0' + month;
				}
				if (day < 10) {
					day = '0' + day;
				}
				if (hours < 10) {
					hours = '0' + hours;
				}
				if (minutes < 10) {
					minutes = '0' + minutes;
				}
				if (seconds < 10) {
					seconds = '0' + seconds;
				}
				if (milli < 100) {
					milli = '0' + milli;
				}
				if (milli < 10) {
					milli = '0' + milli;
				}
				return '"' + year + '-' + month + '-' + day + 'T' + hours + ':' + minutes + ':' + seconds + '.' + milli + 'Z"';
			}
			if (o.constructor === Array) {
				var ret = [];
				for (var i = 0; i < o.length; i++) {
					ret.push($.toJSON(o[i]) || 'null');
				}
				return '[' + ret.join(',') + ']';
			}
			var name, val, pairs = [];
			for (var k in o) {
				type = typeof k;
				if (type === 'number') {
					name = '"' + k + '"';
				} else if (type === 'string') {
					name = $.quoteString(k);
				} else {
					// Keys must be numerical or string. Skip others
					continue;
				}
				type = typeof o[k];

				if (type === 'function' || type === 'undefined') {
					// Invalid values like these return undefined
					// from toJSON, however those object members
					// shouldn't be included in the JSON string at all.
					continue;
				}
				val = $.toJSON(o[k]);
				pairs.push(name + ':' + val);
			}
			return '{' + pairs.join(',') + '}';
		}
	};

	/*
	* jQuery.quoteString
	* Returns a string-repr of a string, escaping quotes intelligently.
	* Mostly a support function for toJSON.
	* Examples:
	* >>> jQuery.quoteString('apple')
	* "apple"
	*
	* >>> jQuery.quoteString('"Where are we going?", she asked.')
	* "\"Where are we going?\", she asked."
	*/
	$.quoteString = function(string) {
		if (string.match(escapeable)) {
			return '"' + string.replace(escapeable, function(a) {
				var c = meta[a];
				if (typeof c === 'string') {
					return c;
				}
				c = a.charCodeAt();
				return '\\u00' + Math.floor(c / 16).toString(16) + (c % 16).toString(16);
			}) + '"';
		}
		return '"' + string + '"';
	};

})(jQuery);

jQuery.base64 = (function($){
	var _PADCHAR = "=",
	_ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
	_VERSION = "1.0";

	function _getbyte64(s, i){
		// This is oddly fast, except on Chrome/V8.
		// Minimal or no improvement in performance by using a
		// object with properties mapping chars to value (eg. 'A': 0)

		var idx = _ALPHA.indexOf(s.charAt(i));

		if(idx === -1){
			throw "Cannot decode base64";
		}
		return idx;
	}

	function _decode(s){
		var pads = 0,
		i,
		b10,
		imax = s.length,
		x = [];

		s = String(s);

		if(imax === 0){
			return s;
		}

		if(imax % 4 !== 0){
			throw "Cannot decode base64";
		}

		if(s.charAt(imax - 1) === _PADCHAR){
			pads = 1;

			if(s.charAt(imax - 2) === _PADCHAR){
				pads = 2;
			}

			// either way, we want to ignore this last block
			imax -= 4;
		}

		for(i = 0; i < imax; i += 4){
			b10 = (_getbyte64(s, i) << 18) | (_getbyte64(s, i + 1) << 12) | (_getbyte64(s, i + 2) << 6) | _getbyte64(s, i + 3);
			x.push(String.fromCharCode(b10 >> 16, (b10 >> 8) & 0xff, b10 & 0xff));
		}

		switch(pads){
			case 1:
			b10 = (_getbyte64(s, i) << 18) | (_getbyte64(s, i + 1) << 12) | (_getbyte64(s, i + 2) << 6);
			x.push(String.fromCharCode(b10 >> 16, (b10 >> 8) & 0xff));
			break;

			case 2:
			b10 = (_getbyte64(s, i) << 18) | (_getbyte64(s, i + 1) << 12);
			x.push(String.fromCharCode(b10 >> 16));
			break;
		}

		return x.join("");
	}

	function _getbyte(s, i){
		var x = s.charCodeAt(i);

		if(x > 255){
			throw "INVALID_CHARACTER_ERR: DOM Exception 5";
		}

		return x;
	}

	function _encode(s){
		if(arguments.length !== 1){
			throw "SyntaxError: exactly one argument required";
		}

		s = String(s);

		var i,
		b10,
		x = [],
		imax = s.length - s.length % 3;

		if(s.length === 0){
			return s;
		}

		for(i = 0; i < imax; i += 3){
			b10 = (_getbyte(s, i) << 16) | (_getbyte(s, i + 1) << 8) | _getbyte(s, i + 2);
			x.push(_ALPHA.charAt(b10 >> 18));
			x.push(_ALPHA.charAt((b10 >> 12) & 0x3F));
			x.push(_ALPHA.charAt((b10 >> 6) & 0x3f));
			x.push(_ALPHA.charAt(b10 & 0x3f));
		}

		switch(s.length - imax){
			case 1:
			b10 = _getbyte(s, i) << 16;
			x.push(_ALPHA.charAt(b10 >> 18) + _ALPHA.charAt((b10 >> 12) & 0x3F) + _PADCHAR + _PADCHAR);
			break;

			case 2:
			b10 = (_getbyte(s, i) << 16) | (_getbyte(s, i + 1) << 8);
			x.push(_ALPHA.charAt(b10 >> 18) + _ALPHA.charAt((b10 >> 12) & 0x3F) + _ALPHA.charAt((b10 >> 6) & 0x3f) + _PADCHAR);
			break;
		}

		return x.join("");
	}

	return {
		decode: _decode,
		encode: _encode,
		VERSION: _VERSION
	};
}(jQuery));