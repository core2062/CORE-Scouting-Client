<?php
/*
TODO add openid based sign-up (perhaps a accordian to hold different sign-up methods)

This script handles: signup (because process.php requires token)
*/

//set place & type (for logging)
$place = 'signup.php';
$type = 'signup';

require 'php/general.php';

//get input variables
//variables_order must contain "C" in php.ini
$input = $_COOKIE['user'] or logout("user object was not received");
$input = json_decode($input, true);

//primary error correction is done client-side, error messages from this are not friendly

//check for missing required data (for optional, assign default)
$required = array('fName', 'lName');

$len = sizeof($required);
for(var $i = 0; i < $len; $i++){
    if (empty($input[$required[$i]]) == true){
        send_error('something wasn\'t not sent');
    }
}



//check for same username
//check for same email
//check for invalid team, email, name... (also on client side)

$input['permission'] = 1;//TODO make system to assign higher permissions automatically

$db->execute("
	db.user.insert(" . json_encode($input) . ");
");

//token and logintime are assigned at 1st login

 /*
{
   "_id": "SeanLang-2062",
   "email": "slang800@gmail.com",
   "info": {
     "fName": "Sean",
     "lName": "Lang",
     "zip": 53072,
     "browser": "Firefox"
  },
   "ip": "127.0.0.1",
   "logintime": "1327269383.167",
   "permission": 9,
   "prefs": {
     "fade": true,
     "verbose": true
  },
   "pword": "superpass",
   "stats": {
     "gender": "m"
  },
   "team": 2062,
   "token": "4f1c860728df71.38499022"
}

 */

//send confirmation email + instructions for training

?>