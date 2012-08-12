import re
from subprocess import Popen, PIPE, CalledProcessError

"""
	compile indentation based LESS (evenLESS) into CSS
	this module relies on the lessc binary (which comes with the less node package) for compiling LESS into CSS, but the compile_evenLESS method can still be used to compile evenLESS into regular LESS without lessc
"""


def compile(evenLESS):
	"""
		compile evenLESS directly into CSS and return the CSS as a string
		this is the reccomended way of using the evenLESS compiler, but it relies on lessc
	"""
	return compile_LESS(compile_evenLESS(evenLESS))


#functions for tokens:
def _statement(scanner, token):
	"""token for rules, selectors and even mixins. the characters which must be added are determined by indentation"""
	return "statement", token


def _indent(scanner, token): return "indent", token


def _comment(scanner, token):
	"""token for a comment, this also captures any whitespace in front of the comment and newlines in the comment, so the indentation of the comment, and linebreaks are preserved without creating / parsing indent tokens. Any  are also captured because """
	return "comment", token


def _newline(scanner, token): return ("newline",)


def compile_evenLESS(evenLESS):
	"""convert indentation based LESS (evenLESS) into regular LESS"""
	scanner = re.Scanner([
		(r"[s+}s+]", None),
		(r"[\t]*//.*", _comment),
		(r"[\t]*\/\*(.|\n)*(?!\/\*)(.|\n)*\*\/", _comment),  # css style
		(r"\t", _indent),
		(r"\n", _newline),
		(r"[^\n/]*", _statement),
		(r"[\s+]", None),
	])

	tokens, remainder = scanner.scan(evenLESS)

	#check if there is any code that didn't get tokenized
	if remainder != "":
		print 'ERROR: invalid syntax on line ' + str(evenLESS.count('\n') - remainder.count('\n'))  # get line num by subtracting total remaining lines from input lines

	#parse all the data in to another array to combine indents with statments
	lines = []
	indents = 0
	for token in tokens:
		if token[0] == 'indent':
			indents += 1
		elif token[0] == 'statement':
			lines.append((indents, token[1]))
			indents = 0
		elif token[0] == 'newline' or token[0] == 'comment':
			lines.append(token)  # directly add blank lines or comments (they need no processing)
		else:
			return 'ERROR: unexpected token'

	del tokens  # not needed anymore

	i = 0
	output = ''

	while i in range(len(lines)):
		if lines[i][0] == 'newline':
			output += "\n"
		elif lines[i][0] == 'comment':
			output += lines[i][1]
		else:  # statement

			text = lines[i][1].strip()

			output += "\t" * lines[i][0] + text  # print indentation and text

			#look ahead to the next statement to find indentation
			next_indentation = 0  # if there isn't another line then assume 0 indentation to close all brackets at the end of the file
			for e in range(i + 1, len(lines)):
				if lines[e][0] != 'newline' and lines[e][0] != 'comment':
					next_indentation = lines[e][0]
					break

			if text[-1] != '{' and text[-1] != ';':  # don't add a ';' or '{' if it is already there
				if lines[i][0] + 1 == next_indentation:  # must be beginning of a block if next line has one more indent
					output += '{'
				elif lines[i][0] >= next_indentation:  # must be a rule
					output += ';'
				else:
					return 'ERROR: unexpected indent on line ' + output.count('\n')

			# deal with closing blocks
			if next_indentation < lines[i][0]:
				output += '}' * (lines[i][0] - next_indentation)

		i += 1

	return output


def compile_LESS(less_code, version=False, verbose=False, silent=False, strictimports=False, help=False, compress=False, yuicompress=False, nocolor=False, includepath='', optimization=1):
	"""
		compile LESS code into CSS using the lessc binary (which must be installed for this to work) and return a string containing the compiled CSS
		all data is sent via stdin (no files are needed, except for imports)
		version and help are both options which, if true, become the only option sent. they're used to print help/version text, not compile LESS
	"""

	cmd = ['lessc']  # inital command

	# format command based on options specified for function

	if version:
		cmd += ['--version']
	elif help:
		cmd += ['--help']
	else:  # if version and help are both false, begin parsing options for actually compiling LESS
		if verbose:
			cmd += ['--verbose']
		if silent:
			cmd += ['--silent']
		if strictimports:
			cmd += ['--strict-imports']
		if compress:
			cmd += ['--compress']
		if yuicompress:
			cmd += ['--yui-compress']
		if nocolor:
			cmd += ['--no-color']
		if includepath != '':
			cmd += ['--include-path ' + includepath]
		if optimization != 1:  # if not default
			cmd += ['0' + optimization]  # formatted like 00, 01, or 02

		cmd += ['-']  # '-' means no file and makes LESS read from stdin

	lessc = Popen(cmd, stdin=PIPE, stdout=PIPE, stderr=PIPE)
	stdoutdata, stderrdata = lessc.communicate(less_code)  # send data to lessc

	if lessc.returncode != 0:
		# raise same error that subprocess.check_call would raise in this situation
		# this error will also be raised if there is an error in your LESS code, so you should view CalledProcessError.output if it is raised
		raise CalledProcessError(
			returncode=lessc.returncode,
			cmd=' '.join(cmd),  # put all the args together w/ spaces
			output=stderrdata,  # if it is a syntax error in your LESS, this will explain what it was
		)

	return stdoutdata
