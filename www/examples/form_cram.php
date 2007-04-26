<?php
    session_start();
    if (empty($_SESSION['challenge'])) {
        $sChallenge = uniqid();
    	$_SESSION['challenge'] = $sChallenge;
    }
?>
<html>
<head>
    <title>CRAM login, flow example for Hygia</title>
    <script type="text/javascript">
        function submit_form(frm) {
            frm.response.value = frm.challenge.value + frm.pass.value;
            frm.pass.value = '';
            return true;
        }
    </script>
</head>
<body>
<?php
    $bLoggedIn = false;
    if (!empty($_REQUEST['name']) && !empty($_REQUEST['response'])) {
        if ($_REQUEST['name'] == 'demo' && $_REQUEST['response'] == $_SESSION['challenge'] . 'demo') {
            $bLoggedIn = true;
            print ' Welcome ' . $_REQUEST['name'] . ', you have been logged in!<br />' . "\n";
        } else {
        	print ' No access!<br />' . "\n";
        }
    }

    if (!$bLoggedIn) {
?>
    <form name="fLogin" action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return submit_form(this)">
        <input type="hidden" name="challenge" value="<?php print $_SESSION['challenge']; ?>" />
        <input type="hidden" name="response" value="" />
        name: <input type="text" name="name" /><br />
        pass: <input type="password" name="pass" /><br />
        <input type="submit" value="submit" /><br />
        <small>user: <b>demo</b>, pass: <b>demo</b></small>
    </form>
<?php
    }
?>
</body>
</html>
