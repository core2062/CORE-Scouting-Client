<?php
	//TODO: replace sitemap with function which scans html directory and gets config from existing files (low importance)

	$pages = [
		[
			"name" => "home",
			"description" => "lorem",
			"embedded" => true,
			"hidden" => false,
			"fullName" => "Home",
			"subpages" => [
				"front-page" => [
					"fullName" => "Front Page",
					"description" => "lorem",
					"login-required" => false
				],
				"synopsis" => [
					"fullName" => "Synopsis",
					"description" => "lorem",
					"login-required" => false
				],
				"tour" => [
					"fullName" => "Guided Tour",
					"description" => "lorem",
					"login-required" => false
				],
				"signup" => [
					"fullName" => "Signup",
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
			"fullName" => "Administrative",
			"subpages" => [
				"mongo" => [
					"fullName" => "Manage MongoDB",
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
			"fullName" => "Input",
			"subpages" => [
				"tracking" => [
					"description" => "lorem",
					"fullName" => "Tracking Scouting",
					"login-required" => true
				],
				"pit" => [
					"fullName" => "Pit Scouting",
					"description" => "lorem",
					"login-required" => true
				],
				"robot" => [
					"fullName" => "Robot Scouting",
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
			"fullName" => "Analysis",
			"subpages" => [
				"public" => [
					"fullName" => "Public Analysis",
					"description" => "lorem",
					"login-required" => false
				],
				"member" => [
					"fullName" => "Member Analysis",
					"description" => "lorem",
					"login-required" => true
				],
				"data-liberation" => [
					"fullName" => "Data Liberation",
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
			"fullName" => "Team Leader",
			"subpages" => [
				"manage" => [
					"fullName" => "Manage Scouting",
					"description" => "lorem",
					"login-required" => false
				],
				"contribution" => [
					"fullName" => "View Contribution",
					"description" => "lorem",
					"login-required" => false
				],
				"team" => [
					"fullName" => "View Team Members",
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
			"fullName" => "Help",
			"subpages" => [
				"training" => [
					"fullName" => "Manage Training",
					"description" => "lorem",
					"login-required" => true
				],
				"documentation" => [
					"fullName" => "Documentation",
					"description" => "lorem",
					"login-required" => false
				],
				"forum" => [
					"fullName" => "Forum",
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
			"fullName" => "Other",
			"modals" => [
				"account" => [
					"description" => "lorem",
					"fullName" => "Edit Account",
					"login-required" => true,
					"onClose" => "postUserUpdates()"
				],
				"contact" => [
					"fullName" => "Contact",
					"description" => "lorem",
					"login-required" => false
				],
				"credits" => [
					"fullName" => "Credits",
					"description" => "lorem",
					"login-required" => false
				],
				"login" => [
					"fullName" => "Login",
					"description" => "lorem",
					"login-required" => false,
					"onOpen" => "$('#scoutid').focus()"
				],
				"navigation" => [
					"fullName" => "Navigation",
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