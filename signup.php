<?php
$link = mysql_connect("mysql2.000webhost.com","a7984559_scouter","Crutr7tusakus2aWacUF") or die('Could not connect: ' . mysql_error());      //build MySQL Link
mysql_select_db('a7984559_scout') or die('Could not select database'); //select database

// 0 = account banned (only account without input)
// 1 = low account, only input access
// 2 = low account, input + scout leader access
// 3 = normal account, no direct SQL Query, no scout leader access, can access other analisis
// 8 = near admin account all access of 3 level permission + scout leader access
// 9 = admin account, full access, direct SQL Query access







//signup will not have signup for scout leaders (i do that personally via admin page - for now)


?>