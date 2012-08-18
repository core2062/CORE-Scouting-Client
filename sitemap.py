from deps.templator import templator
from config import TEMPLATE_DIR

"""this script holds the sitemap, which is used to build the rest of the site and also does rendering of the templates"""

pages = [
	{
		'name': 'home',
		'description': 'lorem',  # not used yet
		'embedded': True,
		'full_name': 'Home',
		'subpages': ['front_page', 'synopsis', 'tour', 'signup'],  # subpages will be found in resources/template/{page_name}/{subpage_name}
		'modals': [],
		'min_width': '1150px',
		'progressbar': 'none'
	},
	{
		'name': 'admin',
		'description': 'lorem',
		'embedded': False,
		'full_name': 'Administrative',
		'subpages': ['mongo'],
		'modals': [],
		'min_width': '1150px',
		'progressbar': 'none'
	},
	{
		'name': 'input',
		'description': 'lorem',
		'embedded': True,
		'full_name': 'Input',
		'subpages': ['tracking', 'pit', 'robot'],
		'modals': [],
		'min_width': '1150px',
		'progressbar': 'block'
	},
	{
		'name': 'analysis',
		'description': 'lorem',
		'embedded': True,
		'full_name': 'Analysis',
		'subpages': ['public', 'member', 'data_liberation'],
		'modals': [],
		'min_width': '1150px',
		'progressbar': 'none'
	},
	{
		'name': 'team_leader',
		'description': 'lorem',
		'embedded': False,
		'full_name': 'Team Leader',
		'subpages': ['manage', 'contribution', 'team'],
		'modals': {},
		'min_width': '1150px',
		'progressbar': 'none'
	},
	{
		'name': 'help',
		'description': 'lorem',
		'embedded': False,
		'full_name': 'Help',
		'subpages': ['training', 'documentation'],
		'modals': [],
		'min_width': '1150px',
		'progressbar': 'none'
	},
	{
		'name': 'other',
		'description': '',
		'embedded': True,
		'full_name': 'Other',
		'subpages': [],
		'modals': ['account', 'contact', 'credits', 'login', 'navigation'],
		'min_width': '1150px',
		'progressbar': 'none',
	}
]


# loop through pages
i = 0
while i in range(len(pages)):
	if not pages[i]['embedded']:  # remove pages not marked as embedded
		del pages[i]
	else:  # page is supposed to be embedded
		templates = templator.Render(TEMPLATE_DIR + '/' + pages[i]['name'])

		for subpage_type in ('subpages', 'modals'):
			for subpage_index in range(len(pages[i][subpage_type])):
				subpage = pages[i][subpage_type][subpage_index]
				pages[i][subpage_type][subpage_index] = {'name': subpage}
				template_function = getattr(templates, subpage)
				template_result = template_function()
				print ':::::::::::::::::::::::::::::::::::::::::::'
				print template_result

				for attr_name in ('name', 'full_name', 'description', 'content', 'sidebar'):  # look for all possible, relevant, attributes
					try:
						pages[i][subpage_type][subpage_index][attr_name] = getattr(template_result, attr_name)
					except AttributeError:  # if it's not there then that's ok
						pass

				print '-------'
				print subpage
				print pages[i][subpage_type][subpage_index]
		i += 1

print pages
