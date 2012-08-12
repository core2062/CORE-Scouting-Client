from flask import Flask, send_from_directory
from config import STATIC_DIR
from helper import compile_coffee
import evenless

app = Flask(__name__)

#if app.debug:
#	helper.compile_less()


@app.route('/favicon.ico')
def favicon():
	"""send the favicon from the typical location at /favicon.ico"""
	return send_from_directory(
		STATIC_DIR,
		'favicon.ico',
		mimetype='image/vnd.microsoft.icon',
		cache_timeout=60 * 60 * 24 * 365 * 5,  # set cache timeout to 5 years
	)


# @app.route('/<path:filename>', subdomain="static")
# def static(filename):
# 	"""send static files from separate sub-domain"""
# 	return send_from_directory(
# 		app.static_folder,
# 		filename,
# 	)


@app.route('/')
def index():
	return helper.render_template('index', 'world!')

if __name__ == "__main__":
	app.run(
		debug=True,
		host='0.0.0.0',  # make dev server public
	)