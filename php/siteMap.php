<?php
	//CONSIDER: move sitemap data to mongoDB if requred
	//TODO: replace sitemap with function which scans html directory and gets config from existing files (low importance]

	$pages = [
		[
			"name" => "home",
			"description" => "lorem",
			"embedded" => true,
			"hidden" => false,
			"full-name" => "Home",
			"subpages" => [
				"front-page" => [
					"full-name" => "Front Page",
					"description" => "lorem",
					"login-required" => false
				],
				"synopsis" => [
					"full-name" => "Synopsis",
					"description" => "lorem",
					"login-required" => false
				],
				"tour" => [
					"full-name" => "Guided Tour",
					"description" => "lorem",
					"login-required" => false
				],
				"signup" => [
					"full-name" => "Signup",
					"description" => "lorem",
					"login-required" => false
				]
			],
			"modals" => [],
			"minWidth" => "1150px",
			"progressbar" => "none"
		],
		[
			"name" => "admin",
			"description" => "lorem",
			"embedded" => false,
			"hidden" => true,
			"full-name" => "Administrative",
			"subpages" => [
				"mongo" => [
					"full-name" => "Manage MongoDB",
					"description" => "lorem",
					"login-required" => false
				]
			],
			"modals" => [],
			"minWidth" => "1150px",
			"progressbar" => "none"
		],
		[
			"name" => "input",
			"description" => "lorem",
			"embedded" => true,
			"hidden" => false,
			"full-name" => "Input",
			"subpages" => [
				"tracking" => [
					"description" => "lorem",
					"full-name" => "Tracking Scouting",
					"login-required" => true
				],
				"pit" => [
					"full-name" => "Pit Scouting",
					"description" => "lorem",
					"login-required" => true
				],
				"robot" => [
					"full-name" => "Robot Scouting",
					"description" => "lorem",
					"login-required" => true
				]
			],
			"modals" => [],
			"minWidth" => "1150px",
			"progressbar" => "block"
		],
		[
			"name" => "analysis",
			"description" => "lorem",
			"embedded" => true,
			"hidden" => false,
			"full-name" => "Analysis",
			"subpages" => [
				"public" => [
					"full-name" => "Public Analysis",
					"description" => "lorem",
					"login-required" => false
				],
				"member" => [
					"full-name" => "Member Analysis",
					"description" => "lorem",
					"login-required" => true
				],
				"data-liberation" => [
					"full-name" => "Data Liberation",
					"description" => "lorem",
					"login-required" => true
				]
			],
			"modals" => [],
			"minWidth" => "1150px",
			"progressbar" => "none"
		],
		[
			"name" => "team-leader",
			"description" => "lorem",
			"embedded" => false,
			"hidden" => false,
			"full-name" => "Team Leader",
			"subpages" => [
				"manage" => [
					"full-name" => "Manage Scouting",
					"description" => "lorem",
					"login-required" => false
				],
				"contribution" => [
					"full-name" => "View Contribution",
					"description" => "lorem",
					"login-required" => false
				],
				"team" => [
					"full-name" => "View Team Members",
					"description" => "lorem",
					"login-required" => false
				]
			],
			"modals" => [],
			"minWidth" => "1150px",
			"progressbar" => "none"
		],
		[
			"name" => "help",
			"description" => "lorem",
			"embedded" => false,
			"hidden" => false,
			"full-name" => "Help",
			"subpages" => [
				"training" => [
					"full-name" => "Manage Training",
					"description" => "lorem",
					"login-required" => true
				],
				"documentation" => [
					"full-name" => "Documentation",
					"description" => "lorem",
					"login-required" => false
				],
				"forum" => [
					"full-name" => "Forum",
					"description" => "lorem",
					"login-required" => true
				]
			],
			"modals" => [],
			"minWidth" => "1150px",
			"progressbar" => "none"
		],
		[
			"name" => "base",
			"description" => "",
			"embedded" => true,
			"hidden" => false,
			"full-name" => "Other",
			"modals" => [
				"account" => [
					"description" => "lorem",
					"full-name" => "Edit Account",
					"login-required" => true,
					"onClose" => "postUserUpdates(]"
				],
				"contact" => [
					"full-name" => "Contact",
					"description" => "lorem",
					"login-required" => false
				],
				"credits" => [
					"full-name" => "Credits",
					"description" => "lorem",
					"login-required" => false
				],
				"login" => [
					"full-name" => "Login",
					"description" => "lorem",
					"login-required" => false,
					"onOpen" => "$('#scoutid'].focus(]"
				],
				"navigation" => [
					"full-name" => "Navigation",
					"description" => "lorem",
					"login-required" => false
				]
			],
			"progressbar" => "none",
			"minWidth" => "1150px",
			"subpages" => []
		]
	]
?>