<?
include("../../php_includes/cmaster.inc");
/* $Id: passwd.php,v 1.5 2003/07/19 01:26:20 nighty Exp $ */
std_connect();
$user_id = isset($_COOKIE["auth"]) ? std_security_chk($_COOKIE["auth"]) : 0;
if ($user_id==0 || $auth=="") {
	die("You must be logged in to view that page!");
}
$admin = std_admin();
$cTheme = get_theme_info();
$change_ok = 0;
if ($SECURE_ID!="" && $auth!="" && $user_id>0) {
	if (isset($authtok)) { unset($authtok); }
	if (isset($authcsc)) { unset($authcsc); }
	$authtok = explode(":",$auth);
	$authcsc = $authtok[3];

	$check_crc = md5( $user_id . CRC_SALT_0019 . $authcsc );
	if ($SECURE_ID == $check_crc) {
		$change_ok = 1;
	}
}
if (!$change_ok) {
	die("NOT ALLOWED!");
}

$dares = pg_safe_exec("SELECT * FROM users WHERE id='" . $user_id . "'");
$dauser = pg_fetch_object($dares,0);

header("Pragma: no-cache\n\n");

$errorz[0] = "Empty password is not permitted, try again !";
$errorz[1] = "Passwords do not match, try again !";
$errorz[2] = "Password is too weak, try again !";
$errorz[3] = "Password can't be your username, try again !";
$errorz[4] = "Password can't be your verification answer, try again !";
$errorz[5] = "Password can't be your e-mail addy, try again !";

if ($mode=="write" && $crc == md5( $SECURE_ID . CRC_SALT_0011 )) {
	$da_error = -1;
	if (($pass1 == $pass2) && ($pass1!="" && $pass2!="")) {
		if (($admin>0 && BOFH_PASS_ADMIN && !pw_check($pass1)) || (BOFH_PASS_USER && !pw_check($pass1)) || (strlen($pass1)<PW_MIN_CHARS) ) {
			$da_error = 2;
		} else {
			if (strtolower($dauser->user_name) == strtolower($pass1)) {
				$da_error = 3;
			} else {
				if (strtolower($dauser->verificationdata) == strtolower($pass1)) {
					$da_error = 4;
				} else {
					if (strtolower($dauser->email) == strtolower($pass1)) {
						$da_error = 5;
					} else {
						// change password
						$valid="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
						$password="";
						srand((double) microtime() * 1000000);
						for ($i=0;$i<8;$i++) {
							$salt=$salt . $valid[rand(0,strlen($valid)-1)];
						}
						$crypt=$salt . md5($salt . $pass1);
						$query = "UPDATE users SET last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,last_updated_by='** Password Change **',password='" . $crypt . "' WHERE id=" . ($user_id+0);
						pg_safe_exec($query);

						// send email
						$mailm = "";
						$mailm .= "\nHello,\n\nThis is the confirmation of your NEW password,\n";
						$mailm .= "remember it, and remember to NEVER EVER give out your password to ANYONE, even people claiming to be CService representatives.\n\n";
						$mailm .= "The new password you set is\t:\t\t" . $pass1 . "\t(" . strlen($pass1) . " chars)\n\n";
						$mailm .= "\n\nThe " . NETWORK_NAME . " Channel Service.\n\n";
						log_user($user_id,10,"");
						$ENABLE_COOKIE_TABLE = 1;
						// logout the user.
						pg_safe_exec("delete from webcookies where user_id='" . $user_id . "'");
						$ENABLE_COOKIE_TABLE = 0;
						if (trim($dauser->email)!="") {
							custom_mail($dauser->email,"Your New CService Password",$mailm,"From: " . NETWORK_NAME . " Channel Service <" . FROM_NEWUSER . ">\nReply-to: " . OBJECT_EMAIL . "\nX-Mailer: " . NETWORK_NAME . " Channel Service\n\n");
							// back to normal
							echo "<html>\n";
							echo "<head>\n";
							echo "<title>CService New Password Confirmation</title>\n";
							std_theme_styles();
							echo "</head>\n";
							std_theme_body();

							echo "<font size=+1>";
							echo "Your new password has been updated into our database,<br>\n";
							echo "and a copy of it has been sent to you via e-mail ,<br>\n";
							echo "please write it down in a safe place and remember it.<br><br>\n";
							echo "NEVER EVER give out your password to ANYONE, even people claiming to be CService representatives.<br><br>\n";
							//echo "The new password is :<br>\n";
							//echo "<center>";
							//echo "<h2>" . $pass1 . "</h2>\n";
							//echo "</center>\n";
							//echo "Your password contains a total of <b>" . strlen($pass1) . "</b> chars,<br>\n";
							//echo "ensure you don't forget any trailing dot (.) or so, all the password chars displayed above are part of it.<br><br>\n";

							echo "You have to <a href=logout.php target=body>LOGIN AGAIN</a> in order to use the website with your new password.<br>\n";
							echo "</font>";
							echo "</body></html>\n\n";
							die;
						} else {
							echo "<html>\n";
							echo "<head>\n";
							echo "<title>CService New Password Confirmation</title>\n";
							std_theme_styles();
							echo "</head>\n";
							std_theme_body();

							echo "<font size=+1>";
							echo "Your new password has been updated into our database,<br>\n";
							echo "please write it down in a safe place and remember it.<br><br>\n";
							echo "NEVER EVER give out your password to ANYONE, even people claiming to be CService representatives.<br><br>\n";
							echo "Your new password is :<br>\n";
							echo "<center>";
							echo "<h2>" . $pass1 . "</h2>\n";
							echo "</center>\n";
							echo "Your password contains a total of <b>" . strlen($pass1) . "</b> chars,<br>\n";
							echo "ensure you don't forget any trailing dot (.) or so, all the password chars displayed above are part of it.<br><br>\n";

							echo "You have to <a href=logout.php target=body>LOGIN AGAIN</a> in order to use the website with your new password.<br>\n";
							echo "</font>";
							echo "</body></html>\n\n";
							die;
						}
					}
				}
			}
		}
	} else {
		if ($pass1!="") {
			$da_error = 1;
		} else {
			$da_error = 0;
		}
	}

	header("Location: passwd.php?SECURE_ID=" . $SECURE_ID . "&da_error=" . $da_error . "\n\n");

}
echo "<html>\n";
echo "<head>\n";
echo "<title>CService New Password Form</title>\n";
std_theme_styles();
echo "</head>\n";
std_theme_body();

echo "<h2>Password modification :</h2>\n";
echo "<form name=modpass method=post action=passwd.php>\n";
echo "<input type=hidden name=SECURE_ID value=\"" . $SECURE_ID . "\">\n";
echo "<input type=hidden name=crc value=\"" . md5( $SECURE_ID . CRC_SALT_0011 ) . "\">\n";
echo "<input type=hidden name=mode value=write>\n";


echo "Your new password MUST contain :<br>\n";
echo "<ul>";

$CAPSL_S="";$MINSL_S="";$DIGIT_S="";$OTHER_S="";
if (PW_MIN_CAPSL>1) { $CAPSL_S = "s"; }
if (PW_MIN_MINSL>1) { $MINSL_S = "s"; }
if (PW_MIN_DIGIT>1) { $DIGIT_S = "s"; }
if (PW_MIN_OTHER>1) { $OTHER_S = "s"; }

echo "<li> at least <b>" . PW_MIN_CHARS . "</b> total chars\n";
if (BOFH_PASS_USER || ($admin>0 && (BOFH_PASS_USER || BOFH_PASS_ADMIN))) {
	echo "<li> at least <b>" . PW_MIN_CAPSL . "</b> capitalized letter$CAPSL_S (A-Z)\n";
	echo "<li> at least <b>" . PW_MIN_MINSL . "</b> lower case letter$MINSL_S (a-z)\n";
	echo "<li> at least <b>" . PW_MIN_DIGIT . "</b> digit$DIGIT_S (0-9)\n";
	echo "<li> at least <b>" . PW_MIN_OTHER . "</b> other$OTHER_S (any other char)\n";
}
echo "</ul><br>\n";

if (BOFH_PASS_USER || ($admin>0 && (BOFH_PASS_USER || BOFH_PASS_ADMIN))) {
	echo "<b>OR</b> at least 20 chars including at least one space. (passphrase).<br><br><br>\n";
}

echo "Please enter your <b>NEW</b> password below according to the above criteria :<br>\n";

echo "<pre><font face=courrier size=+0>\n";
echo "New Password : <input type=password name=pass1 maxlength=255 size=30><br>\n";
echo "Confirmation : <input type=password name=pass2 maxlength=255 size=30><br>\n";
echo "</font></pre>\n";

if ($da_error>-1) {
	echo "<font color=#ff0000><b>" . $errorz[$da_error] . "</b></font><br><br>\n";
} else {
	echo "<br><br>";
}

echo "<input type=submit value=\" CHANGE MY PASSWORD \">\n";

echo "<br><br>\n";

if (trim($dauser->email)!="") {
	echo "<i><b>note:</b> this will also send you an e-mail to your email-in-record .</i><br>\n";
} else {
	echo "<i><b>note:</b> this will be displayed in next screen as a confirmation, because you don't have an email-in-record.</i><br>\n";
}

echo "</form>\n";
?>
</body>
</html>
