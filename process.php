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