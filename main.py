from flask import Flask
from helper import compile_jade

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

if __name__ == "__main__":
	app.run(
		debug=False,
		host='0.0.0.0',  # make dev server public
	)
