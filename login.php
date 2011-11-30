<?php
 // remove this section before production
require_once('FirePHP/fb.php');
ob_start();

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
?>

<?php
// TODO finish token based login

$m = new Mongo(); // connect
$db = $m->selectDB("user");

$token = uniqid("",true);
fb($token);



?>