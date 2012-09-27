from flask import Flask, send_from_directory
from config import STATIC_DIR
from helper import compile_jade

app = Flask(__name__)


@app.route('/favicon.ico')
def favicon():
	"""send the favicon from the typical location at /favicon.ico"""
	return send_from_directory(
		STATIC_DIR,
		'favicon.ico',
		mimetype='image/vnd.microsoft.icon',
		cache_timeout=60 * 60 * 24 * 365 * 5,  # set cache timeout to 5 years
	)


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
		debug=True,
		host='0.0.0.0',  # make dev server public
	)
