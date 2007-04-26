<html>
<head>
	<title>Post form example for Hygia</title>
</head>
<body>
<?php
	if (!empty($_REQUEST['name']) && !empty($_REQUEST['pass'])) {
		print '	Welcome ' . $_REQUEST['name'] . '. Your password is ' . $_REQUEST['pass'] . '<br />' . "\n";
	}
?>
	<form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post">
		name: <input type="text" name="name" /><br />
		pass: <input type="password" name="pass" /><br />
		<input type="submit" value="submit" />
	</form>
</body>
</html>
