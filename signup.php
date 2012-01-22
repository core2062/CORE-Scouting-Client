<?php
/*
TODO add openid based sign-up (perhaps a accordian to hold different sign-up methods)

This script handles:signup (because process.php requires token)
*/

//set place & type (for logging)
$place = 'signup.php';
$type = 'signup';

require 'general.php';

//get & validate input variables
$input = $_POST['data'];
$input = json_decode($input, true);

//check for same username
//check for same email
//check for invalid team, email, name... (also on client side)

$permission = 1;//TODO make system to assign higher permissions automatically

$db->execute("
    db.user.insert({
        _id: '$input['scoutid']',
        name: '$input['fName']',
        email: '$input['email']',
        permission: $permission,
        ip: '$vars['ip']',
		prefs: {
			fade: $input['prefs']['fade'],
			verbose: $input['prefs']['verbose']
		},
		info: {
			fName: '$input['info']['fName']',
			lName: '$input['info']['lName']',
			zip: $input['info']['zip'],
			browser: '$input['info']['browser']'
		},
        pword: '$input['pword']',
        team: $input['team'],
        stats:{
	        gender:'$input['stats']['gender']'
        }
    });
");
//token and logintime are assigned at 1st login


//send confirmation email + instructions for training

?>