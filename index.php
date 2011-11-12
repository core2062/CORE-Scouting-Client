<html>
<head>
<title>test</title>
</head>
<body>
	<p>this  is the test</p>
	<?php echo "<p>this is another test</p>"; ?>
</body>
</html>



<?php
$html = ob_get_contents();
ob_clean ();
var_dump($html);
?>