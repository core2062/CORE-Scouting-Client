from flask import Flask, send_from_directory
from config import STATIC_DIR, TEMPLATE_DIR
from helper import compile_jade
from deps.evenless import evenless
from os import listdir

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
	# if app.debug:  # recompile less if in debug
	# 	less_dir = TEMPLATE_DIR + 'less/'
	# 	css_dir = TEMPLATE_DIR + 'css/'

	# 	for less_file in listdir(less_dir):
	# 		if less_file.endswith(".less"):  # also filters out directories

	# 			open(css_dir + less_file[:-4] + 'css', 'w').write(
	# 				evenless.compile_LESS(
	# 					open(less_dir + less_file, 'r').read(),
	# 					includepath=less_dir,
	# 				)
	# 			)

	return compile_jade('main.jade', pretty=app.debug)  # pretty is true if in debug

if __name__ == "__main__":
	app.run(
		debug=True,
		host='0.0.0.0',  # make dev server public
	)
