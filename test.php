<?php
error_reporting( E_ALL );
ini_set('display_errors', 1);

require 'php/init.php';
require 'php/path.php';
$test = new path;

echo '<!DOCTYPE html>';

$test->path =
['html',
	['head',
		['meta',
			'http-equiv' => "Content-Type",
			'content' => "text/html",
			'charset' => "utf-8"
		],
		['title', 'table sorter'],
		['link','rel'=>'stylesheet','href'=>'tmp/css/base.css'],
		['link','rel'=>'stylesheet','href'=>'tmp/css/analysis.css']
	],
	['body#body',


['table.tablesorter.treeTable',
	['thead',
		['tr',
			['td','col 1'],
			['td','col 2'],
			['td','col 3']
		]
	],
	['tr',
		['td',
			'colspan'=>3,//must be set dynamically
			['table',
				['tr',//first tr in table = header (shows at expansion)
					['td','qwerty'],
					['td','ytrewq'],
					['td','ewqrty'],
				],
				['tr',
					['td','qwerty'],
					['td','ytrewq'],
					['td','ewqrty'],
				]
			]
		],
			
	],
	['tr',
		['td','qwqwwqww11'],
		['td','123424344'],
		['td','90876543333'],
	]
]


	],
	['script','src'=>'coffee/base/jquery.js'],
	['script','src'=>'tmp/js/analysis.js']
];

echo $test->compile();
?>