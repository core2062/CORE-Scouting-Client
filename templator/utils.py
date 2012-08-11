#!/usr/bin/env python
"""
General Utilities
from web.py, but cut apart and fixed to remove crap
"""

#import re
#import time
#import datetime
import itertools


class Storage(dict):
	"""
	A Storage object is like a dictionary except `obj.foo` can be used
	in addition to `obj['foo']`.

		>>> o = storage(a=1)
		>>> o.a
		1
		>>> o['a']
		1
		>>> o.a = 2
		>>> o['a']
		2
		>>> del o.a
		>>> o.a
		Traceback (most recent call last):
			...
		AttributeError: 'a'

	"""
	def __getattr__(self, key):
		try:
			return self[key]
		except KeyError, k:
			raise AttributeError(k)

	def __setattr__(self, key, value):
		self[key] = value

	def __delattr__(self, key):
		try:
			del self[key]
		except KeyError, k:
			raise AttributeError(k)

	def __repr__(self):
		return '<Storage ' + dict.__repr__(self) + '>'


def safeunicode(obj, encoding='utf-8'):
	r"""
	Converts any given object to unicode string.

		>>> safeunicode('hello')
		u'hello'
		>>> safeunicode(2)
		u'2'
		>>> safeunicode('\xe1\x88\xb4')
		u'\u1234'
	"""
	t = type(obj)
	if t is unicode:
		return obj
	elif t is str:
		return obj.decode(encoding)
	elif t in [int, float, bool]:
		return unicode(obj)
	elif hasattr(obj, '__unicode__') or isinstance(obj, unicode):
		return unicode(obj)
	else:
		return str(obj).decode(encoding)


def safestr(obj, encoding='utf-8'):
	r"""
	Converts any given object to utf-8 encoded string.

		>>> safestr('hello')
		'hello'
		>>> safestr(u'\u1234')
		'\xe1\x88\xb4'
		>>> safestr(2)
		'2'
	"""
	if isinstance(obj, unicode):
		return obj.encode(encoding)
	elif isinstance(obj, str):
		return obj
	elif hasattr(obj, 'next'):  # iterator
		return itertools.imap(safestr, obj)
	else:
		return str(obj)


def websafe(val):
	r"""Converts `val` so that it is safe for use in Unicode HTML.

>>> websafe("<'&\">")
u'&lt;&#39;&amp;&quot;&gt;'
>>> websafe(None)
u''
>>> websafe(u'\u203d')
u'\u203d'
>>> websafe('\xe2\x80\xbd')
u'\u203d'
"""
	if val is None:
		return u''
	elif isinstance(val, str):
		val = val.decode('utf-8')
	elif not isinstance(val, unicode):
		val = unicode(val)

	return htmlquote(val)


def htmlquote(text):
	r"""
Encodes `text` for raw use in HTML.
>>> htmlquote(u"<'&\">")
u'&lt;&#39;&amp;&quot;&gt;'
"""
	text = text.replace(u"&", u"&amp;")  # Must be done first!
	text = text.replace(u"<", u"&lt;")
	text = text.replace(u">", u"&gt;")
	text = text.replace(u"'", u"&#39;")
	text = text.replace(u'"', u"&quot;")
	return text


# def datestr(then, now=None):
# 	"""
# 	Converts a (UTC) datetime object to a nice string representation.

# 		>>> from datetime import datetime, timedelta
# 		>>> d = datetime(1970, 5, 1)
# 		>>> datestr(d, now=d)
# 		'0 microseconds ago'
# 		>>> for t, v in {
# 		...   timedelta(microseconds=1): '1 microsecond ago',
# 		...   timedelta(microseconds=2): '2 microseconds ago',
# 		...   -timedelta(microseconds=1): '1 microsecond from now',
# 		...   -timedelta(microseconds=2): '2 microseconds from now',
# 		...   timedelta(microseconds=2000): '2 milliseconds ago',
# 		...   timedelta(seconds=2): '2 seconds ago',
# 		...   timedelta(seconds=2*60): '2 minutes ago',
# 		...   timedelta(seconds=2*60*60): '2 hours ago',
# 		...   timedelta(days=2): '2 days ago',
# 		... }.iteritems():
# 		...     assert datestr(d, now=d+t) == v
# 		>>> datestr(datetime(1970, 1, 1), now=d)
# 		'January  1'
# 		>>> datestr(datetime(1969, 1, 1), now=d)
# 		'January  1, 1969'
# 		>>> datestr(datetime(1970, 6, 1), now=d)
# 		'June  1, 1970'
# 		>>> datestr(None)
# 		''
# 	"""
# 	def agohence(n, what, divisor=None):
# 		if divisor:
# 			n = n // divisor

# 		out = str(abs(n)) + ' ' + what  # '2 day'
# 		if abs(n) != 1:
# 			out += 's'  # '2 days'
# 		out += ' '  # '2 days '
# 		if n < 0:
# 			out += 'from now'
# 		else:
# 			out += 'ago'
# 		return out  # '2 days ago'

# 	oneday = 24 * 60 * 60

# 	if not then:
# 		return ""
# 	if not now:
# 		now = datetime.datetime.utcnow()
# 	if type(now).__name__ == "DateTime":
# 		now = datetime.datetime.fromtimestamp(now)
# 	if type(then).__name__ == "DateTime":
# 		then = datetime.datetime.fromtimestamp(then)
# 	elif type(then).__name__ == "date":
# 		then = datetime.datetime(then.year, then.month, then.day)

# 	delta = now - then
# 	deltaseconds = int(
# 		delta.days * oneday + delta.seconds + delta.microseconds * 1e-06)
# 	deltadays = abs(deltaseconds) // oneday
# 	if deltaseconds < 0:
# 		deltadays *= -1  # fix for oddity of floor

# 	if deltadays:
# 		if abs(deltadays) < 4:
# 			return agohence(deltadays, 'day')

# 		try:
# 			out = then.strftime('%B %e')  # e.g. 'June  3'
# 		except ValueError:
# 			# %e doesn't work on Windows.
# 			out = then.strftime('%B %d')  # e.g. 'June 03'

# 		if then.year != now.year or deltadays < 0:
# 			out += ', %s' % then.year
# 		return out

# 	if int(deltaseconds):
# 		if abs(deltaseconds) > (60 * 60):
# 			return agohence(deltaseconds, 'hour', 60 * 60)
# 		elif abs(deltaseconds) > 60:
# 			return agohence(deltaseconds, 'minute', 60)
# 		else:
# 			return agohence(deltaseconds, 'second')

# 	deltamicroseconds = delta.microseconds
# 	if delta.days:
# 		deltamicroseconds = int(delta.microseconds - 1e6)  # datetime oddity
# 	if abs(deltamicroseconds) > 1000:
# 		return agohence(deltamicroseconds, 'millisecond', 1000)

# 	return agohence(deltamicroseconds, 'microsecond')


# def numify(string):
# 	"""
# 	Removes all non-digit characters from `string`.

# 		>>> numify('800-555-1212')
# 		'8005551212'
# 		>>> numify('800.555.1212')
# 		'8005551212'

# 	"""
# 	return ''.join([c for c in str(string) if c.isdigit()])


# def denumify(string, pattern):
# 	"""
# 	Formats `string` according to `pattern`, where the letter X gets replaced
# 	by characters from `string`.

# 		>>> denumify("8005551212", "(XXX) XXX-XXXX")
# 		'(800) 555-1212'

# 	"""
# 	out = []
# 	for c in pattern:
# 		if c == "X":
# 			out.append(string[0])
# 			string = string[1:]
# 		else:
# 			out.append(c)
# 	return ''.join(out)


# def commify(n):
# 	"""
# 	Add commas to an integer `n`.

# 		>>> commify(1)
# 		'1'
# 		>>> commify(123)
# 		'123'
# 		>>> commify(1234)
# 		'1,234'
# 		>>> commify(1234567890)
# 		'1,234,567,890'
# 		>>> commify(123.0)
# 		'123.0'
# 		>>> commify(1234.5)
# 		'1,234.5'
# 		>>> commify(1234.56789)
# 		'1,234.56789'
# 		>>> commify('%.2f' % 1234.5)
# 		'1,234.50'
# 		>>> commify(None)
# 		>>>

# 	"""
# 	if n is None:
# 		return None
# 	n = str(n)
# 	if '.' in n:
# 		dollars, cents = n.split('.')
# 	else:
# 		dollars, cents = n, None

# 	r = []
# 	for i, c in enumerate(str(dollars)[::-1]):
# 		if i and (not (i % 3)):
# 			r.insert(0, ',')
# 		r.insert(0, c)
# 	out = ''.join(r)
# 	if cents:
# 		out += '.' + cents
# 	return out


# def dateify(datestring):
# 	"""
# 	Formats a numified `datestring` properly.
# 	"""
# 	return denumify(datestring, "XXXX-XX-XX XX:XX:XX")


# def nthstr(n):
# 	"""
# 	Formats an ordinal.
# 	Doesn't handle negative numbers.

# 		>>> nthstr(1)
# 		'1st'
# 		>>> nthstr(0)
# 		'0th'
# 		>>> [nthstr(x) for x in [2, 3, 4, 5, 10, 11, 12, 13, 14, 15]]
# 		['2nd', '3rd', '4th', '5th', '10th', '11th', '12th',
# 			'13th', '14th', '15th']
# 		>>> [nthstr(x) for x in [91, 92, 93, 94, 99, 100, 101, 102]]
# 		['91st', '92nd', '93rd', '94th', '99th', '100th', '101st', '102nd']
# 		>>> [nthstr(x) for x in [111, 112, 113, 114, 115]]
# 		['111th', '112th', '113th', '114th', '115th']

# 	"""

# 	assert n >= 0
# 	if n % 100 in [11, 12, 13]:
# 		return '%sth' % n
# 	return {1: '%sst', 2: '%snd', 3: '%srd'}.get(n % 10, '%sth') % n
