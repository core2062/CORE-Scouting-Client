from flask import Flask
import json
from config import TEMPLATE_DIR
from subprocess import check_output

app = Flask(__name__)


@app.route('/')
def index():
	return compile_jade(
		'main.jade',
		pretty=app.debug,  # pretty is true if in debug
		options={
			'debug': app.debug
		}
	)


def compile_jade(file_name, pretty=False, options=None, path=''):
	"""
		compile a jade file in the template directory and return the resulting HTML
		user input should not be passed through the options object without being sterilized because it is sent through the shell
	"""

	file_path = TEMPLATE_DIR + file_name

	cmd = ['jade']  # the base command

	if pretty:
		cmd += ['--pretty']  # add pretty option if requested

	if options != None:
		# add json options object if specified & escape any single quotes in it
		cmd += ['--obj', "'" + json.dumps(options, separators=(',', ':')).replace("'", r"'\''") + "'"]

	if path == '':
		path = file_path
	else:
		path = TEMPLATE_DIR + path

	cmd += ['--path', path]

	cmd += ['<', file_path]  # add the file that is being compiled in the template directory (the "<" sends the file & output over stdio)

	return check_output(' '.join(cmd), shell=True)


if __name__ == "__main__":
	app.run(
		debug=False,
		host='0.0.0.0',  # make dev server public
	)
