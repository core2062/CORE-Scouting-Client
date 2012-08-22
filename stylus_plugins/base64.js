fs = require('fs');

// var stylus = require('stylus'),
// 	nodes = stylus.nodes,
// 	utils = stylus.utils

function base64Encode(text){
	//see: http://www.nczonline.net/blog/2009/12/08/computer-science-in-javascript-base64-encoding/
	if (/([^\u0000-\u00ff])/.test(text)){
		throw new Error("Can't base64 encode non-ASCII characters.");
	} 

	var digits = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
		i = 0,
		cur, prev, byteNum,
		result=[];      

	while(i < text.length){

		cur = text.charCodeAt(i);
		byteNum = i % 3;

		switch(byteNum){
			case 0: //first byte
				result.push(digits.charAt(cur >> 2));
				break;

			case 1: //second byte
				result.push(digits.charAt((prev & 3) << 4 | (cur >> 4)));
				break;

			case 2: //third byte
				result.push(digits.charAt((prev & 0x0f) << 2 | (cur >> 6)));
				result.push(digits.charAt(cur & 0x3f));
				break;
		}

		prev = cur;
		i++;
	}

	if (byteNum === 0){
		result.push(digits.charAt((prev & 3) << 4));
		result.push("==");
	} else if (byteNum == 1){
		result.push(digits.charAt((prev & 0x0f) << 2));
		result.push("=");
	}

	return result.join("");
}

function base64(filename){
	fs.readFile(filename, function (err, data) {
		if (err) {
			throw new Error(err);
		} else {
			return 'data:image/' + filename.split('.').pop() + ';base64,' + base64Encode(data);
		}
	});
}

exports = module.exports = plugin;

//Return the plugin callback for stylus.
function plugin() {
	return function(style){
		//style.include(__dirname);
		style.define('base64', base64);
	};
}
