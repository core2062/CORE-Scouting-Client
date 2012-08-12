import templator
from config import TEMPLATE_DIR
from subprocess import Popen, PIPE, CalledProcessError


def render_template(filename, *args, **kwargs):
	"""
	shortcut function to render template
	filename is the name of the template, without the .html extension or the path to the template folder
	"""
	content = open(TEMPLATE_DIR + filename + '.html').read()
	template = templator.Template(content)
	return str(template(*args, **kwargs))  # without the str flask will try to call it? .. odd stuff


def compile_coffee(code):
	"""compile coffee script (code) into JS using the coffee commandline utility without temp files"""
	cmd = ['coffee', '--print', '--stdio']  # command to input coffee script via stdin and output via stdout
	coffee = Popen(cmd, stdin=PIPE, stdout=PIPE, stderr=PIPE)
	stdoutdata, stderrdata = coffee.communicate(code)  # send data to coffee binary

	if coffee.returncode != 0:
		# raise same error that subprocess.check_call would raise in this situation
		# this error will also be raised if there is an error in your coffee script code, so you should view CalledProcessError.output if it is raised
		raise CalledProcessError(
			returncode=coffee.returncode,
			cmd=' '.join(cmd),  # put all the args together w/ spaces
			output=stderrdata,  # if it is a syntax error in your Coffee Script, this will explain what it was
		)

	return stdoutdata

print compile_coffee("""

""")