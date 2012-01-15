var fName = document.getElementById('fName');
var lName = document.getElementById('lName');
var teamNum = document.getElementById('teamNum');
var previewScoutID = document.getElementById('previewScoutID');
var signupPassword = document.getElementById('signupPassword');


function displayScoutID() {
	setTimeout(function(){
		var fNameV = fName.value;
		var lNameV = lName.value;
		var teamNumV = teamNum.value;

		if (fNameV != '' && lNameV != '' && teamNumV != '') {
			previewScoutID.value = fNameV.toLowerCase().titleCase() + lNameV.toLowerCase().titleCase() + "-" + teamNumV;
		} else {
			previewScoutID.value = '';
		}
	}, 100);//timeout used because key hasn't been pressed yet
}

function checkSignup(filter) {
	if (filter == 'fName' || filter == 'all') {


		if (filter != all) {
			return
		};
	}
	if (filter == 'lName' || filter == 'all') {

		if (filter != all) {
			return
		};
	}
}

/* WILL USE THIS LATER
//Password Meter
PasswordMeter.prototype = ({
	COMPLEXITY: {
		VERYWEAK: 0,
		WEAK: 1,
		GOOD: 2,
		STRONG: 3,
		VERYSTRONG: 4
	},

	STATUS: {
		FAILED: 0,
		PASSED: 1,
		EXCEEDED: 2
	},

	// little string helper to reverse a string
	strReverse: function(str) {
		var newstring = "";
		for (var s = 0; s < str.length; s++) {
			newstring = str.charAt(s) + newstring;
		}
		return newstring;
	},

	int2str: function(aNumber) {
		if (aNumber == 0) {
			return "0";
		} else {
			return parseInt(aNumber, 10);
		}
	},

	float2str: function(aNumber) {
		if (aNumber == 0) {
			return "0.00";
		} else {
			return parseFloat(aNumber.toFixed(2));
		}
	},

	// helper for the status
	// <0 failed
	// 0  passed
	// >0 exceeded
	determineStatus: function(aNumber) {
		if (aNumber == 0) {
			return this.STATUS.PASSED;
		} else if (aNumber > 0) {
			return this.STATUS.EXCEEDED;
		} else {
			return this.STATUS.FAILED;
		}
	},

	// helper for the status
	// 0  passed
	// !=0 failed
	determineBinaryStatus: function(aNumber) {
		if (aNumber == 0) {
			return this.STATUS.PASSED;
		} else {
			return this.STATUS.FAILED;
		}
	}
});

function PasswordMeter() {
	this.Score = {
		count: 0,
		adjusted: 0,
		beforeRedundancy: 0
	};

	// the complexity index
	this.Complexity = {
		limits: [20, 50, 60, 80, 100],
		value: this.COMPLEXITY.VERYWEAK
	};

	// the length of the password
	this.PasswordLength = {
		count: 0,
		minimum: 6,
		formula: "TBD",
		status: this.STATUS.FAILED,
		rating: 0,
		factor: 0.5,
		// per character bonus
		bonus: 10,
		// minimum reached? Get a bonus.
		penalty: -20 // if we stay under minimum, we get punished
	};

	// recommended password length
	this.RecommendedPasswordLength = {
		count: 0,
		minimum: 8,
		formula: "TBD",
		status: this.STATUS.FAILED,
		rating: 0,
		factor: 1.2,
		bonus: 10,
		penalty: -10
	};

	// Basic requirements are:
	// 1) Password Length
	// 2) Uppercase letter use
	// 3) Lowercase letter use
	// 4) Numeric character use
	// 5) Symbol use
	this.BasicRequirements = {
		count: 0,
		minimum: 3,
		// have to be matched to get the bonus
		formula: "TBD",
		status: this.STATUS.FAILED,
		rating: 0,
		factor: 1,
		bonus: 10,
		penalty: -10
	};

	// how much redundancy is permitted, if the password is
	// long enough. we will skip the redudancy penalty if this
	// number is not exceeded (meaning redundancy < this number)
	this.Redundancy = {
		value: 1,
		// 1 means, not double characters, default to start
		permitted: 2.0,
		// 2 means, in average every character can occur twice
		formula: "TBD",
		status: this.STATUS.FAILED,
		rating: 0
	};

	// number of uppercase letters, such as A-Z
	this.UppercaseLetters = {
		count: 0,
		minimum: 1,
		formula: "TBD",
		status: this.STATUS.FAILED,
		rating: 0,
		factor: 0,
		bonus: 10,
		penalty: -10
	};

	// number of lowercase letters, such as a-z
	this.LowercaseLetters = {
		count: 0,
		minimum: 1,
		formula: "TBD",
		status: this.STATUS.FAILED,
		rating: 0,
		factor: 0,
		bonus: 10,
		penalty: -10
	};

	// number of numeric characters
	this.Numerics = {
		count: 0,
		minimum: 1,
		formula: "TBD",
		status: this.STATUS.FAILED,
		rating: 0,
		factor: 0,
		bonus: 10,
		penalty: -10
	};

	// number of symbol characters
	this.Symbols = {
		count: 0,
		minimum: 1,
		formula: "TBD",
		status: this.STATUS.FAILED,
		rating: 0,
		factor: 0,
		bonus: 10,
		penalty: -10
	};

	// number of dedicated symbols in the middle
	this.MiddleSymbols = {
		count: 0,
		minimum: 1,
		formula: "TBD",
		status: this.STATUS.FAILED,
		rating: 0,
		factor: 0,
		bonus: 10,
		penalty: -10
	};

	// number of dedicated numbers in the middle
	this.MiddleNumerics = {
		count: 0,
		minimum: 1,
		formula: "TBD",
		status: this.STATUS.FAILED,
		rating: 0,
		factor: 0,
		bonus: 10,
		penalty: -10
	};

	// how many sequential characters should be checked
	// such as "abc" or "MNO" to be not part of the password
	this.SequentialLetters = {
		data: "abcdefghijklmnopqrstuvwxyz",
		length: 3,

		count: 0,

		formula: "TBD",
		status: this.STATUS.FAILED,
		rating: 0,
		factor: -1,
		bonus: 0,
		penalty: -10
	};

	// how many sequential characters should be checked
	// such as "123" to be not part of the password
	this.SequentialNumerics = {
		data: "0123456789",
		length: 3,

		count: 0,

		formula: "TBD",
		status: this.STATUS.FAILED,
		rating: 0,
		factor: -1,
		bonus: 0,
		penalty: -10
	};

	// keyboard patterns to check, typical sequences from your
	// keyboard
	this.KeyboardPatterns = {
		// german and english keyboard text
		data: ["qwertzuiop", "asdfghjkl", "yxcvbnm", "!\"Â§$%&/()=", // de
		"1234567890", // de numbers
		"qaywsxedcrfvtgbzhnujmik,ol.pÃ¶-Ã¼Ã¤+#", // de up-down
		"qwertyuiop", "asdfghjkl", "zyxcvbnm", "!@#$%^&*()_", // en
		"1234567890", // en numbers
		"qazwsxedcrfvtgbyhnujmik,ol.p;/[']\\" // en up-down
		],
		length: 4,
		// how long is the pattern to check and blame for?
		count: 0,
		// how much of these pattern can be found
		formula: "TBD",
		status: this.STATUS.FAILED,
		rating: 0,
		factor: -1,
		// each occurence is punished with that factor
		bonus: 0,
		penalty: -10
	};

	// check for repeated sequences, like in catcat
	this.RepeatedSequences = {
		length: 3,

		count: 0,
		formula: "TBD",
		status: this.STATUS.FAILED,
		rating: 0,
		factor: 0,
		bonus: 0,
		penalty: -10
	};

	// check for repeated sequences, like in catcat
	this.MirroredSequences = {
		length: 3,

		count: 0,
		formula: "TBD",
		status: this.STATUS.FAILED,
		rating: 0,
		factor: 0,
		bonus: 0,
		penalty: -10
	};

	// split password data. This means we will split the password
	// at the position and evaluate both parts independently again.
	// The final password score is composed of all three components.
	// (full * weightFull + part1 * weight1 + part2 * weight2).
	// The sum of the weight should be 1 aka 100% to work good.
	this.SplitPassword = {
		splitPosition: this.RecommendedPasswordLength.minimum,
		weight1: 0.80,
		// weight of part1
		// weight2		: 0.20, // weight of part2
		weightFull: 0.20,
		// the weight applied to the total score
		part1: "",
		// the split password part1
		// part2		: "",  // the split password part2
		part1Score: 0,
		// part2Score	: 0
	};

	// this check our password and sets all object properties accordingly
	this.checkPassword = function(password, splitPassword) {
		// do we have data to check?
		if (!password) {
			// no, leave
			password = "";
		}

		if (!splitPassword) {
			splitPassword = true;
		}

		// check the password and set all values
		var nTmpAlphaUC = -1;
		var nTmpAlphaLC = -1;
		var nTmpNumber = -1;
		var nTmpSymbol = -1;

		// how long is the password?
		this.PasswordLength.count = password.length;
		this.RecommendedPasswordLength.count = password.length;

		// split it, all characters are permitted so far
		var passwordArray = password.split("");

		// Loop through password to check for Symbol, Numeric, Lowercase
		// and Uppercase pattern matches
		for (var a = 0; a < passwordArray.length; a++) {
			// check uppercase letters
			if (passwordArray[a].match(/[A-Z]/g)) {
				if (nTmpAlphaUC != -1) {
					// check last uppercase position, when the previous one, store
					// the information
					if ((nTmpAlphaUC + 1) == a) {
						this.nConsecutiveUppercaseLetters++;
						this.nConsecutiveLetters++;
					}
				}
				// store the last uppercase position
				nTmpAlphaUC = a;

				this.UppercaseLetters.count++;
			}
			// check lowercase
			else if (passwordArray[a].match(/[a-z]/g)) {
				if (nTmpAlphaLC != -1) {
					if ((nTmpAlphaLC + 1) == a) {
						this.nConsecutiveLowercaseLetters++;
						this.nConsecutiveLetters++;
					}
				}
				nTmpAlphaLC = a;
				this.LowercaseLetters.count++;
			}
			// check numeric
			else if (passwordArray[a].match(/[0-9]/g)) {
				if (a > 0 && a < (passwordArray.length - 1)) {
					this.MiddleNumerics.count++;
				}
				if (nTmpNumber != -1) {
					if ((nTmpNumber + 1) == a) {
						this.nConsecutiveNumbers++;
						this.nConsecutiveLetters++;
					}
				}
				nTmpNumber = a;
				this.Numerics.count++;
			}
			// check all extra characters
			else if (passwordArray[a].match(new RegExp(/[^a-zA-Z0-9]/g))) {
				if (a > 0 && a < (passwordArray.length - 1)) {
					this.MiddleSymbols.count++;
				}
				if (nTmpSymbol != -1) {
					if ((nTmpSymbol + 1) == a) {
						this.nConsecutiveSymbols++;
						this.nConsecutiveLetters++;
					}
				}
				nTmpSymbol = a;
				this.Symbols.count++;
			}
		}

		// check the variance of symbols or better the redundancy
		// makes only sense for at least two characters
		if (passwordArray.length > 1) {
			var uniqueCharacters = new Array();
			for (var a = 0; a < passwordArray.length; a++) {
				var found = false;

				for (var b = a + 1; b < passwordArray.length; b++) {
					if (passwordArray[a] == passwordArray[b]) {
						found = true;
					}
				}
				if (found == false) {
					uniqueCharacters.push(passwordArray[a]);
				}
			}

			// calculate a redundancy number
			this.Redundancy.value = passwordArray.length / uniqueCharacters.length;
		}

		// Check for sequential alpha string patterns (forward and reverse) but only, if the string
		// has already a length to check for, does not make sense to check the password "ab" for the
		// sequential data "abc"
		var lowercasedPassword = password.toLowerCase();

		if (this.PasswordLength.count >= this.SequentialLetters.length) {
			for (var s = 0; s < this.SequentialLetters.data.length - this.SequentialLetters.length; s++) {
				var sFwd = this.SequentialLetters.data.substring(s, s + this.SequentialLetters.length);
				var sRev = this.strReverse(sFwd);

				if (lowercasedPassword.indexOf(sFwd) != -1) {
					this.SequentialLetters.count++;
				}
				if (lowercasedPassword.indexOf(sRev) != -1) {
					this.SequentialLetters.count++;
				}
			}
		}

		// Check for sequential numeric string patterns (forward and reverse)
		if (this.PasswordLength.count >= this.SequentialNumerics.length) {
			for (var s = 0; s < this.SequentialNumerics.data.length - this.SequentialNumerics.length; s++) {
				var sFwd = this.SequentialNumerics.data.substring(s, s + this.SequentialNumerics.length);
				var sRev = this.strReverse(sFwd);

				if (lowercasedPassword.indexOf(sFwd) != -1) {
					this.SequentialNumerics.count++;
				}
				if (lowercasedPassword.indexOf(sRev) != -1) {
					this.SequentialNumerics.count++;
				}
			}
		}

		// Check common keyboard patterns
		var patternsMatched = new Array();
		if (this.PasswordLength.count >= this.KeyboardPatterns.length) {
			for (p in this.KeyboardPatterns.data) {
				var pattern = this.KeyboardPatterns.data[p];

				for (var s = 0; s < pattern.length - this.KeyboardPatterns.length; s++) {
					var sFwd = pattern.substring(s, s + this.KeyboardPatterns.length);
					var sRev = this.strReverse(sFwd);

					if (lowercasedPassword.indexOf(sFwd) != -1) {
						if (patternsMatched[sFwd] == undefined) {
							this.KeyboardPatterns.count++;
							patternsMatched[sFwd] = sFwd;
						}
					}
					if (lowercasedPassword.indexOf(sRev) != -1) {
						if (patternsMatched[sRev] == undefined) {
							this.KeyboardPatterns.count++;
							patternsMatched[sRev] = sRev;
						}
					}
				}
			}
		}

		// Try to find repeated sequences of characters.
		if (this.PasswordLength.count > this.RepeatedSequences.length) {
			for (var s = 0; s < lowercasedPassword.length - this.RepeatedSequences.length; s++) {
				var sFwd = lowercasedPassword.substring(s, s + this.RepeatedSequences.length);

				var result = lowercasedPassword.indexOf(sFwd, s + this.RepeatedSequences.length);
				if (result != -1) {
					this.RepeatedSequences.count++;
				}
			}
		}

		// Try to find mirrored sequences of characters.
		if (this.PasswordLength.count > this.MirroredSequences.length) {
			for (var s = 0; s < lowercasedPassword.length - this.MirroredSequences.length; s++) {
				var sFwd = lowercasedPassword.substring(s, s + this.MirroredSequences.length);
				var sRev = this.strReverse(sFwd);

				var result = lowercasedPassword.indexOf(sRev, s + this.MirroredSequences.length);
				if (result != -1) {
					this.MirroredSequences.count++;
				}
			}
		}

		//*************************************************************************
		//* Initial score based on length
		//*************************************************************************
		this.Score.count = this.PasswordLength.count * this.PasswordLength.factor;

		//*************************************************************************
		//* PasswordLength
		//* credit additional length or punish "under" length
		//*************************************************************************
		if (this.PasswordLength.count < this.PasswordLength.minimum) {
			this.PasswordLength.rating = this.PasswordLength.penalty;
		} else if (this.PasswordLength.count >= this.PasswordLength.minimum) {
			// credit additional characters over minimum
			this.PasswordLength.rating = this.PasswordLength.bonus + (this.PasswordLength.count - this.PasswordLength.minimum) * this.PasswordLength.factor;
		}
		this.Score.count += this.PasswordLength.rating;

		//*************************************************************************
		//* RecommendedPasswordLength
		//* Credit reaching the recommended password length or put a
		//* penalty on it
		//*************************************************************************
		if (this.PasswordLength.count >= this.RecommendedPasswordLength.minimum) {
			this.RecommendedPasswordLength.rating =
			this.RecommendedPasswordLength.bonus + (this.PasswordLength.count - this.RecommendedPasswordLength.minimum) * this.RecommendedPasswordLength.factor;
		} else {
			this.RecommendedPasswordLength.rating = this.RecommendedPasswordLength.penalty;
		}
		this.Score.count += this.RecommendedPasswordLength.rating;

		//*************************************************************************
		//* LowercaseLetters
		//* Honor or punish the Lowercase letter use
		//*************************************************************************
		if (this.LowercaseLetters.count > 0) {
			this.LowercaseLetters.rating = this.LowercaseLetters.bonus + (this.LowercaseLetters.count * this.LowercaseLetters.factor);
		} else {
			this.LowercaseLetters.rating = this.LowercaseLetters.penalty;
		}
		this.Score.count += this.LowercaseLetters.rating;

		//*************************************************************************
		//* UppercaseLetters
		//* Honor or punish the uppercase letter use
		//*************************************************************************
		if (this.UppercaseLetters.count > 0) {
			this.UppercaseLetters.rating = this.UppercaseLetters.bonus + (this.UppercaseLetters.count * this.UppercaseLetters.factor);
		} else {
			this.UppercaseLetters.rating = this.UppercaseLetters.penalty;
		}
		this.Score.count += this.UppercaseLetters.rating;

		//*************************************************************************
		//* Numerics
		//* Honor or punish the Numerics letter use
		//*************************************************************************
		if (this.Numerics.count > 0) {
			this.Numerics.rating = this.Numerics.bonus + (this.Numerics.count * this.Numerics.factor);
		} else {
			this.Numerics.rating = this.Numerics.penalty;
		}
		this.Score.count += this.Numerics.rating;

		//*************************************************************************
		//* Symbols
		//* Honor or punish the Symbols letter use
		//*************************************************************************
		if (this.Symbols.count > 0) {
			this.Symbols.rating = this.Symbols.bonus + (this.Symbols.count * this.Symbols.factor);
		} else {
			this.Symbols.rating = this.Symbols.penalty;
		}
		this.Score.count += this.Symbols.rating;

		//*************************************************************************
		//* MiddleSymbols
		//* Honor or punish the MiddleSymbols letter use
		//*************************************************************************
		if (this.MiddleSymbols.count > 0) {
			this.MiddleSymbols.rating = this.MiddleSymbols.bonus + (this.MiddleSymbols.count * this.MiddleSymbols.factor);
		} else {
			this.MiddleSymbols.rating = this.MiddleSymbols.penalty;
		}
		this.Score.count += this.MiddleSymbols.rating;

		//*************************************************************************
		//* MiddleNumerics
		//* Honor or punish the MiddleNumerics letter use
		//*************************************************************************
		if (this.MiddleNumerics.count > 0) {
			this.MiddleNumerics.rating = this.MiddleNumerics.bonus + (this.MiddleNumerics.count * this.MiddleNumerics.factor);
		} else {
			this.MiddleNumerics.rating = this.MiddleNumerics.penalty;
		}
		this.Score.count += this.MiddleNumerics.rating;

		//*************************************************************************
		//* SequentialLetters
		//* Honor or punish the SequentialLetters letter use
		//*************************************************************************
		if (this.SequentialLetters.count == 0) {
			this.SequentialLetters.rating = this.SequentialLetters.bonus;
		} else {
			this.SequentialLetters.rating = this.SequentialLetters.penalty + (this.SequentialLetters.count * this.SequentialLetters.factor);
		}
		this.Score.count += this.SequentialLetters.rating;

		//*************************************************************************
		//* SequentialNumerics
		//* Honor or punish the SequentialNumerics letter use
		//*************************************************************************
		if (this.SequentialNumerics.count == 0) {
			this.SequentialNumerics.rating = this.SequentialNumerics.bonus;
		} else {
			this.SequentialNumerics.rating = this.SequentialNumerics.penalty + (this.SequentialNumerics.count * this.SequentialNumerics.factor);
		}
		this.Score.count += this.SequentialNumerics.rating;

		//*************************************************************************
		//* KeyboardPatterns
		//* Honor or punish the KeyboardPatterns letter use
		//*************************************************************************
		if (this.KeyboardPatterns.count == 0) {
			this.KeyboardPatterns.rating = this.KeyboardPatterns.bonus;
		} else {
			this.KeyboardPatterns.rating = this.KeyboardPatterns.penalty + (this.KeyboardPatterns.count * this.KeyboardPatterns.factor);
		}
		this.Score.count += this.KeyboardPatterns.rating;

		//*************************************************************************
		//* Count our BasicRequirements and set the status
		//*************************************************************************
		this.BasicRequirements.count = 0;

		// password length
		this.PasswordLength.status = this.determineStatus(this.PasswordLength.count - this.PasswordLength.minimum);
		if (this.PasswordLength.status != this.STATUS.FAILED) {
			// requirement met
			this.BasicRequirements.count++;
		}

		// uppercase letters
		this.UppercaseLetters.status = this.determineStatus(this.UppercaseLetters.count - this.UppercaseLetters.minimum);
		if (this.UppercaseLetters.status != this.STATUS.FAILED) {
			// requirement met
			this.BasicRequirements.count++;
		}

		// lowercase letters
		this.LowercaseLetters.status = this.determineStatus(this.LowercaseLetters.count - this.LowercaseLetters.minimum);
		if (this.LowercaseLetters.status != this.STATUS.FAILED) {
			// requirement met
			this.BasicRequirements.count++;
		}

		// numerics
		this.Numerics.status = this.determineStatus(this.Numerics.count - this.Numerics.minimum);
		if (this.Numerics.status != this.STATUS.FAILED) {
			// requirement met
			this.BasicRequirements.count++;
		}

		// symbols
		this.Symbols.status = this.determineStatus(this.Symbols.count - this.Symbols.minimum);
		if (this.Symbols.status != this.STATUS.FAILED) {
			// requirement met
			this.BasicRequirements.count++;
		}

		// judge the requirement status
		this.BasicRequirements.status = this.determineStatus(this.BasicRequirements.count - this.BasicRequirements.minimum);
		if (this.BasicRequirements.status != this.STATUS.FAILED) {
			this.BasicRequirements.rating =
			this.BasicRequirements.bonus + (this.BasicRequirements.factor * this.BasicRequirements.count);
		} else {
			this.BasicRequirements.rating = this.BasicRequirements.penalty;
		}
		this.Score.count += this.BasicRequirements.rating;

		// no basic requirements
		this.RecommendedPasswordLength.status = this.determineStatus(this.PasswordLength.count - this.RecommendedPasswordLength.minimum);
		this.MiddleNumerics.status = this.determineStatus(this.MiddleNumerics.count - this.MiddleNumerics.minimum);
		this.MiddleSymbols.status = this.determineStatus(this.MiddleSymbols.count - this.MiddleSymbols.minimum);
		this.SequentialLetters.status = this.determineBinaryStatus(this.SequentialLetters.count);
		this.SequentialNumerics.status = this.determineBinaryStatus(this.SequentialNumerics.count);
		this.KeyboardPatterns.status = this.determineBinaryStatus(this.KeyboardPatterns.count);

		this.RepeatedSequences.status = this.determineBinaryStatus(this.RepeatedSequences.count);
		this.MirroredSequences.status = this.determineBinaryStatus(this.MirroredSequences.count);

		// we apply them only, if the length is not awesome ;)
		if (this.RecommendedPasswordLength.status != this.STATUS.EXCEEDED) {
			//*************************************************************************
			//* RepeatedSequences
			//* Honor or punish the RepeatedSequences letter use
			//*************************************************************************
			if (this.RepeatedSequences.count == 0) {
				this.RepeatedSequences.rating = this.RepeatedSequences.bonus;
			} else {
				this.RepeatedSequences.rating = this.RepeatedSequences.penalty + (this.RepeatedSequences.count * this.RepeatedSequences.factor);
			}
			this.Score.count += this.RepeatedSequences.rating;

			//*************************************************************************
			//* MirroredSequences
			//* Punish the MirroredSequences
			//*************************************************************************
			if (this.MirroredSequences.count == 0) {
				this.MirroredSequences.rating = this.MirroredSequences.bonus;
			} else {
				this.MirroredSequences.rating = this.MirroredSequences.penalty + (this.MirroredSequences.count * this.MirroredSequences.factor);
			}
			this.Score.count += this.MirroredSequences.rating;
		}

		// save value before redundancy
		this.Score.beforeRedundancy = this.Score.count;

		// apply the redundancy
		// is the password length requirement fulfilled?
		if (this.RecommendedPasswordLength.status != this.STATUS.EXCEEDED) {
			// full penalty, because password is not long enough, only for a positive score
			if (this.Score.count > 0) {
				this.Score.count = this.Score.count * (1 / this.Redundancy.value);
			}
		}

		// level it out
		if (this.Score.count > 100) {
			this.Score.adjusted = 100;
		} else if (this.Score.count < 0) {
			this.Score.adjusted = 0;
		} else {
			this.Score.adjusted = this.Score.count;
		}

		// the final twist. The first part (recommended length for now) has to have a good meaning,
		// because some legacy system only evaluate the beginning and do use the rest.
		if (this.PasswordLength.count > this.SplitPassword.splitPosition && splitPassword) {
			var part1 = new PasswordMeter();
			this.SplitPassword.part1 = password.substr(0, this.SplitPassword.splitPosition);
			part1.checkPassword(this.SplitPassword.part1, false);
			this.SplitPassword.part1Score = part1.Score.adjusted;

			//var part2 = new PasswordMeter();
			//this.SplitPassword.part2 = password.substr(this.SplitPassword.splitPosition);
			//part2.checkPassword(this.SplitPassword.part2, false);
			//this.SplitPassword.part2Score = part2.Score.adjusted;

			// ok, the final score is composed of score one and score two
			var old = this.Score.count;

			// do this only, if the first part is not 100%
			if (this.SplitPassword.part1Score < 100) {
				this.Score.count =
				this.Score.count * this.SplitPassword.weightFull + this.SplitPassword.part1Score * this.SplitPassword.weight1;
				// this.SplitPassword.part2Score * this.SplitPassword.weight2;

				//alert("Changed\n" +
				//password + ": " + old + "\n" +
				//this.SplitPassword.part1 + ": " + this.SplitPassword.part1Score + "\n" +
				////this.SplitPassword.part2 + ": " + this.SplitPassword.part2Score + "\n" +
				//"New: " + this.Score.count
				//);
			} else {
				this.SplitPassword.part1Score = this.Score.count;

				//alert("Unchanged\n" +
				//password + ": " + old + "\n" +
				//this.SplitPassword.part1 + ": " + this.SplitPassword.part1Score + "\n" +
				////this.SplitPassword.part2 + ": " + this.SplitPassword.part2Score + "\n" +
				//"New: " + this.Score.count
				//);
			}
		} else {
			this.SplitPassword.part1Score = this.Score.count;
		}

		// level it out again
		if (this.Score.count > 100) {
			this.Score.adjusted = 100;
		} else if (this.Score.count < 0) {
			this.Score.adjusted = 0;
		} else {
			this.Score.adjusted = this.Score.count;
		}

		// judge it
		for (var i = 0; i < this.Complexity.limits.length; i++) {
			if (this.Score.adjusted <= this.Complexity.limits[i]) {
				this.Complexity.value = i;
				break;
			}
		}

		return this.Complexity.value;
	};
}
*/