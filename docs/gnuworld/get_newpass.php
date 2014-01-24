<?
	/* $Id: get_newpass.php,v 1.8 2005/11/18 04:19:33 nighty Exp $ */
	require("../../php_includes/cmaster.inc");
	$cTheme = get_theme_info();
	std_theme_styles(1);
	std_theme_body();
        $username=strtolower($_POST["username"]);
        if ($crc != md5( $ts . $_SERVER["HTTP_USER_AGENT"] . CRC_SALT_0001 )) {
        	echo "<h1>Error<br><br>Please use the regular page.</h1>\n";
        	echo "<a href=forgotten_pass.php>click here</a>.";
        	echo "</body></html>\n\n";
        	die;
        }
        if (!ip_check($username,0)) {
        	echo "<h1>Error<br>\n";
        	echo "Too many failed 'forgotten password' attempts for this user.</h1><br>\n";
		echo "</body>\n";
		echo "</html>\n\n";
		die;
        }
        std_connect();
        /*
       	$res=pg_safe_exec("select * from noreg where lower(user_name)='$username' AND type=4");
        if (pg_numrows($res)>0)
        	{
        	echo "<h1>Error<br>\n";
        	echo "The USERNAME entered is fraudulous.</h1><br><h2>This username cannot be used (FRAUD USERNAME)</h2><br><br>\n";
		echo "<a href=\"forgotten_pass.php\">Try again.</a>\n";
		echo "</body>\n";
		echo "</html>\n\n";
		die;
        	}
	unset($res);
	*/
       	$res=pg_safe_exec("select * from users where lower(user_name)='" . post2db($username) . "'");
        if (pg_numrows($res)==0) {
        	echo "<h1>Error<br>\n";
        	echo "The USERNAME entered is not valid.</h1><br><h2>Please check it and make sure it is correct</h2><br><br>\n";
		echo "<a href=\"forgotten_pass.php\">Try again.</a>\n";
		echo "</body>\n";
		echo "</html>\n\n";
		die;
       	}
        $user=pg_fetch_object($res,0);
        if ((int)$user->question_id==0 || $user->verificationdata=="") {
        	echo "<h1>Error</h1><br><h3>\n";
        	echo "You must have the other authentication method enabled (PASSPHRASE)</h3><br>\n";
        	echo "If you really don't have your password anymore. then ask " . SERVICE_CHANNEL . ".<br><br>\n";
        	echo "If you are just testing this feature and you read that page, that means you<br>\n";
        	echo "will need to <b>log in</b> then go to <b>modify</b> your account and put something<br>\n";
        	echo "in the other authentication method.<br><br>\n";
        	echo "This error probably occured because you registered your <b>username</b> some time ago<br>\n";
        	echo "when this second authentication method was not yet mandatory.<br><br>\n";
		echo "<a href=\"forgotten_pass.php\">Try again.</a>\n";
		echo "</body>\n";
		echo "</html>\n\n";
		die;
       	}

	$now = time();
	$days_elapsed = (int)((int)($now - (int)$user->signup_ts)/86400);
	if ($days_elapsed < MIN_DAYS_BEFORE_SUPPORT) {
                echo "<h1>Error<br>\n";
                echo "The USERNAME entered is too newly created !</h1><br><h2>You can only process this request after your account is at least ".MIN_DAYS_BEFORE_SUPPORT." day(s) old !</h2><br><br>\n";
                echo "<a href=\"forgotten_pass.php\">Go back.</a>\n";
                echo "</body>\n";
                echo "</html>\n\n";
                die;
	}


	echo "<h1>Please answer this question for authentication</h1><br>\n";
	echo "<h3>" . $question_text[(int)$user->question_id] . "</h3><br>\n";
	echo "<form method=POST action=forgotten_pass.php>\n";
	$zets = time();
	$zecrc = md5( $user->user_name . $_SERVER["HTTP_USER_AGENT"] . $zets . CRC_SALT_0002 );
	echo "<input type=hidden name=crc value=\"" . $zecrc . "\">\n";
	echo "<input type=hidden name=ts value=\"" . $zets . "\">\n";
	echo "<input type=hidden name=username value=\"" . $user->user_name . "\">\n";
	echo "<input type=hidden name=qid value=" . (int)$user->question_id . ">\n";
	echo "<input type=text name=passphrase maxlength=30 size=40>\n";
	echo "<br><br>\n";
	echo "<input type=submit value=\" Email a password to you \">\n";
	echo "</form>\n";
?>
</body>
</html>

