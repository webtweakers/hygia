<html>
<head>
	<title>File upload and multipart/form-data example for Hygia</title>
</head>

<body>
<?php
	if (!empty($_FILES)) {
        $uploadfile = '/tmp/' . basename($_FILES['userfile']['name']);
        print 'uploading file to: ' . $uploadfile . '<br>';
		if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
			echo 'File is valid, and was successfully uploaded.<br>';
		} else {
			echo 'Possible file upload attack!<br>';
		}
	}
?>
    <form action="<?php print $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" method="post">
        Some variable: <input type="text" name="myvar1" /><br />
        Another variable: <input type="text" name="myvar2" /><br />
        Send this file: <input type="file" name="userfile" /><br />
        <input type="submit" value="Send File" />
    </form>
</body>
</html>
