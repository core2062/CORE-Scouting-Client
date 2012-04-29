<?php
/*

This script acts as a console that checks permissions and requests (and their arguments) to the correct command

	TODO: replace permissions system with something better
	TODO: make a request object (holds everything, like vars and send functions)
*/

//set place & type (for logging)
$place = 'process.php';
$type = 'regular';

require 'php/init.php';

//get & validate input variables
//variables_order must contain "C" in php.ini
$input['user'] = $_COOKIE['user'] or logout("user object was not received");
$input['user'] = json_decode($input['user'], true);

if (empty($input['user']['_id']) == true) {
	send_error("scoutid was not receved",'','logout();');//cannot run logout() w/out scoutid
}
if (empty($input['user']['token']) == true) {
	logout("token was not receved");
}

//check user & assign user object
$user = $db->user->findOne(
	[
		'_id' => $input['user']['_id']
	]
);

if ($user['token'] !== $input['user']['token']) {//validate token
	logout("token is incorrect, you have been logged out for security reasons");
}
if ($user['stats']['ip'] !== $vars['ip']) {//validate ip address
	logout("ip is incorrect, you have been logged out for security reasons");
}
if ($user['permission'] == 0) {
	send_error('your account is banned', 'banned account');
}

//get request data from post
$json = $_POST['data'] or send_error('no data sent');

if (empty($json) == true) {//post can send the string undefined if given no data... really gay
	send_error('no data received');
}

$input = array_merge(json_decode($json, true), $input);

//TODO: change switch into a dynamic way of calling functions, or something better

// request switch
switch ($input['request']) {
	case "poll": //for match signup, mail poll, other?

		send_error('this part is not finished');
		//get new stats, check for mail, get match signup stuff

	break;
	case "input":
		//user is not banned based on above check, therefore user has needed permissions

		require 'php/input.php';

	break;
	case "mail":

		send_error('this part is not finished');
		require 'php/mail.php';
		
		//encode chars to remove HTML or JS
		
	break;
	case "query":
	/*
		if ($user['permission'] < 3) {
			send_error('Invalid Permissions');
		}
	*/
		require 'php/query.php';

	break;
	case "scout-leader":
		if ($user['permission'] != 2 && $user['permission'] != 4) {
			send_error('invalid permissions - scout-leader only','');
		}

		send_error('this part is not finished');

	break;
	case "admin":
		if ($user['permission'] < 9) {
			send_error('invalid permissions - admin only');
		}

		require 'php/admin/admin.php';

	break;
	case "logout":
		logout();

	break;
	case "updateUser":
		//TODO: if moved to cookie-less sub-domain, make partial pref update (not sending all prefs, because not all have not changed)
		$db->user->update(
			[
				'_id' => $user['_id']
			],
			[
				'$set' => [
					'prefs' => $input['user']['prefs']
				]
			]
		);
		//TODO: check for error in prefs update?

		send_reg(['message' => 'preferences updated successfully']);

	break;
	default:
		send_error('invalid request type');
}


//generic functions
function logout($error_message = ''){//must be function to let it be called from other areas in script
	global $db;
	global $user;
	
	$db->user->update(
		[
			'_id:' => $user['_id']
		],
		[
			'$unset' => [
				'ip' => 1,
				'token' => 1
			]
		]
	); // delete token & ip for active user
	
	//TODO: check for logout error?
	
	if($error_message == ''){//if no error message is specified then assume no error
		send_reg(['message' => 'logout successful']);
	} else {
		send_error($error_message,'','logout();');
	}
}
?>