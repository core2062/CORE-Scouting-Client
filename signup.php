<?php
/*
TODO add openid based sign-up (perhaps a accordian to hold different sign-up methods)

This script handles:signup (because process.php requires token)
*/

//set place & type (for logging)
$place = 'signup.php';
$type = 'signup';

require 'general.php';

//get input variables
$input = $_POST['data'];
$input = json_decode($input, true);

//primary error correction is done client-side, error messages from this are not friendly

//check for missing required data (for optional, assign default)
if (isset($input['fName']) == false || isset($input['lName']) == false || isset($input['lName']) == false){
	send_error('first name not sent');
}
if ($input['lName'] == ''){
	send_error('last name not sent');
}
if ($input['email'] == ''){
	send_error('first name not sent');
}
if ($input['email'] == ''){
	send_error('first name not sent');
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