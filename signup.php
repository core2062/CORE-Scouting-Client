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
        _id: '" . $scoutid . "',
        name:'" . $name . "',
        email: '" . $email . "',
        permission:" . $permission . ",
        pword:'" . $pword . "',
        team:" . $team . "
        stats:{
                gender:'',
                //other stuff
        }
    });
");

//send confirmation email + instructions for training

?>