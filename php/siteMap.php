<?php
	//CONSIDER: move sitemap data to mongoDB if requred

	$pages = array(
		array(
			"name" => "home",
			"description" => "lorem",
			"embedded" => true,
			"hidden" => false,
			"full-name" => "Home",
			"subpages" => array(
				"front-page" => array(
					"full-name" => "Front Page",
					"description" => "lorem",
					"login-required" => false
				),
				"synopsis" => array(
					"full-name" => "Synopsis",
					"description" => "lorem",
					"login-required" => false
				),
				"tour" => array(
					"full-name" => "Guided Tour",
					"description" => "lorem",
					"login-required" => false
				),
				"signup" => array(
					"full-name" => "Signup",
					"description" => "lorem",
					"login-required" => false
				)
			),
			"modals" => array(),
			"minWidth" => "1150px",
			"progressbar" => "none"
		),
		array(
			"name" => "admin",
			"description" => "lorem",
			"embedded" => false,
			"hidden" => true,
			"full-name" => "Administrative",
			"subpages" => array(
				"mongo" => array(
					"full-name" => "Manage MongoDB",
					"description" => "lorem",
					"login-required" => false
				)
			),
			"modals" => array(),
			"minWidth" => "1150px",
			"progressbar" => "none"
		),
		array(
			"name" => "input",
			"description" => "lorem",
			"embedded" => true,
			"hidden" => false,
			"full-name" => "Input",
			"subpages" => array(
				"alliance" => array(
					"description" => "lorem",
					"full-name" => "Alliance Scouting",
					"login-required" => true
				),
				"pit" => array(
					"full-name" => "Pit Scouting",
					"description" => "lorem",
					"login-required" => true
				),
				"robot" => array(
					"full-name" => "Robot Scouting",
					"description" => "lorem",
					"login-required" => true
				)
			),
			"modals" => array(),
			"minWidth" => "1150px",
			"progressbar" => "block"
		),
		array(
			"name" => "analysis",
			"description" => "lorem",
			"embedded" => false,
			"hidden" => false,
			"full-name" => "Analysis",
			"subpages" => array(
				"public" => array(
					"full-name" => "Public Analysis",
					"description" => "lorem",
					"login-required" => false
				),
				"member" => array(
					"full-name" => "Member Analysis",
					"description" => "lorem",
					"login-required" => true
				),
				"data-liberation" => array(
					"full-name" => "Data Liberation",
					"description" => "lorem",
					"login-required" => true
				)
			),
			"modals" => array(),
			"minWidth" => "1150px",
			"progressbar" => "none"
		),
		array(
			"name" => "team-leader",
			"description" => "lorem",
			"embedded" => false,
			"hidden" => false,
			"full-name" => "Team Leader",
			"subpages" => array(
				"manage" => array(
					"full-name" => "Manage Scouting",
					"description" => "lorem",
					"login-required" => false
				),
				"contribution" => array(
					"full-name" => "View Contribution",
					"description" => "lorem",
					"login-required" => false
				),
				"team" => array(
					"full-name" => "View Team Members",
					"description" => "lorem",
					"login-required" => false
				)
			),
			"modals" => array(),
			"minWidth" => "1150px",
			"progressbar" => "none"
		),
		array(
			"name" => "help",
			"description" => "lorem",
			"embedded" => false,
			"hidden" => false,
			"full-name" => "Help",
			"subpages" => array(
				"training" => array(
					"full-name" => "Manage Training",
					"description" => "lorem",
					"login-required" => true
				),
				"documentation" => array(
					"full-name" => "Documentation",
					"description" => "lorem",
					"login-required" => false
				),
				"forum" => array(
					"full-name" => "Forum",
					"description" => "lorem",
					"login-required" => true
				)
			),
			"modals" => array(),
			"minWidth" => "1150px",
			"progressbar" => "none"
		),
		array(
			"name" => "base",
			"description" => "",
			"embedded" => true,
			"hidden" => false,
			"full-name" => "Other",
			"modals" => array(
				"account" => array(
					"description" => "lorem",
					"full-name" => "Edit Account",
					"login-required" => true,
					"onClose" => "postUserUpdates()"
				),
				"contact" => array(
					"full-name" => "Contact",
					"description" => "lorem",
					"login-required" => false
				),
				"credits" => array(
					"full-name" => "Credits",
					"description" => "lorem",
					"login-required" => false
				),
				"login" => array(
					"full-name" => "Login",
					"description" => "lorem",
					"login-required" => false,
					"onOpen" => "$('#scoutid').focus()"
				),
				"navigation" => array(
					"full-name" => "Navigation",
					"description" => "lorem",
					"login-required" => false
				)
			),
			"progressbar" => "none",
			"minWidth" => "1150px",
			"subpages" => array()
		)
	)
?>