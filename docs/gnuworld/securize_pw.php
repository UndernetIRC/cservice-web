<?
/* $Id: securize_pw.php,v 1.9 2003/07/19 01:26:20 nighty Exp $ */
include("../../php_includes/cmaster.inc");
if (BOFH_PASS_USER==0 && BOFH_PASS_ADMIN==0) {
	die("Page not active.");
}
std_init();
$cTheme = get_theme_info();
if (BOFH_PASS_USER==0 && $admin==0) {
	die("Page not active for users.");
}
if (isset($securize_mode)) { unset($securize_mode); }
$securize_mode = 0;

if ($SECURE_ID!="") {
	if (isset($authtok)) { unset($authtok); }
	if (isset($authcsc)) { unset($authcsc); }
	$authtok = explode(":",$auth);
	$authcsc = $authtok[3];

	$check_crc = md5( $user_id . CRC_SALT_0013 . $authcsc );
	if ($SECURE_ID == $check_crc) {
		$securize_mode = 1;
	}
}
if (!$securize_mode) {
	die("Wrong way !");
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

if ($mode=="write" && $crc == md5( $SECURE_ID . CRC_SALT_0017 )) {
	$da_error = -1;
	if (($pass1 == $pass2) && ($pass1!="" && $pass2!="")) {
		if (!pw_check($pass1)) {
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
						$query = "UPDATE users SET last_updated=now()::abstime::int4,last_updated_by='** Password Secured **',password='" . $crypt . "' WHERE id='" . $user_id . "'";
						pg_safe_exec($query);

						// send email
						$mailm = "";
						$mailm .= "\nHello,\n\nThis is the confirmation of your NEW password according to the 'Secure Password Policy',\n";
						$mailm .= "remember it, and remember to NEVER EVER give out your password to ANYONE, even people claiming to be CService representatives.\n\n";
						$mailm .= "The new password you set is\t:\t\t" . $pass1 . "\t(" . strlen($pass1) . " chars)\n\n";
						$mailm .= "\n\nThe " . NETWORK_NAME . " Channel Service.\n\n";
						if (trim($dauser->email)!="") {
							custom_mail($dauser->email,"Your Secured CService Password",$mailm,"From: " . NETWORK_NAME . " Channel Service <" . FROM_NEWUSER . ">\nReply-to: " . OBJECT_EMAIL . "\nX-Mailer: " . NETWORK_NAME . " Channel Service\n\n");
							// back to normal
							echo "<html>\n";
							echo "<head>\n";
							echo "<title>CService Secure Password Policy Confirmation</title>\n";
							std_theme_styles();
							echo "</head>\n";
							std_theme_body();

							echo "<font size=+1>";
							echo "You new password has been updated into our database,<br>\n";
							echo "and a copy of it has been sent to you via e-mail to <b>" . $dauser->email . "</b>,<br>\n";
							echo "please write it down in a safe place and remember it.<br><br>\n";
							echo "NEVER EVER give out your password to ANYONE, even people claiming to be CService representatives.<br><br>\n";
							//echo "The new password is :<br>\n";
							//echo "<center>";
							//echo "<h2>" . $pass1 . "</h2>\n";
							//echo "</center>\n";
							//echo "You password contains a total of <b>" . strlen($pass1) . "</b> chars,<br>\n";
							//echo "ensure you don't forget any trailing dot (.) or so, all the password chars displayed above are part of it.<br><br>\n";

							echo "You have to <a href=logout.php target=body>LOGIN AGAIN</a> in order to validate your password change.<br>\n";
							echo "</font>";
							echo "</body></html>\n\n";
							die;
						} else {
							echo "<html>\n";
							echo "<head>\n";
							echo "<title>CService Secure Password Policy Confirmation</title>\n";
							std_theme_styles();
							echo "</head>\n";
							std_theme_body();

							echo "<font size=+1>";
							echo "You new password has been updated into our database,<br>\n";
							echo "please write it down in a safe place and remember it.<br><br>\n";
							echo "NEVER EVER give out your password to ANYONE, even people claiming to be CService representatives.<br><br>\n";
							echo "You new password is :<br>\n";
							echo "<center>";
							echo "<h2>" . $pass1 . "</h2>\n";
							echo "</center>\n";
							echo "You password contains a total of <b>" . strlen($pass1) . "</b> chars,<br>\n";
							echo "ensure you don't forget any trailing dot (.) or so, all the password chars displayed above are part of it.<br><br>\n";

							echo "You have to <a href=logout.php target=body>LOGIN AGAIN</a> in order to validate your password change.<br>\n";
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

	header("Location: main.php?sba=1&SECURE_ID=" . $SECURE_ID . "&da_error=" . $da_error . "\n\n");

}
echo "<html>\n";
echo "<head>\n";
echo "<title>CService Secure Password Policy</title>\n";
std_theme_styles();
echo "</head>\n";
std_theme_body();

echo "<h2>Secure your password !</h2>\n";
echo "<form name=securize method=post target=body action=securize_pw.php>\n";
echo "<input type=hidden name=SECURE_ID value=\"" . $SECURE_ID . "\">\n";
echo "<input type=hidden name=crc value=\"" . md5( $SECURE_ID . CRC_SALT_0017 ) . "\">\n";
echo "<input type=hidden name=mode value=write>\n";

echo "You current password does not match the <b>" . NETWORK_NAME . " CService Password Policy</b>.<br><br>\n";

echo "You new password MUST contain :<br>\n";
echo "<ul>";

$CAPSL_S="";$MINSL_S="";$DIGIT_S="";$OTHER_S="";
if (PW_MIN_CAPSL>1) { $CAPSL_S = "s"; }
if (PW_MIN_MINSL>1) { $MINSL_S = "s"; }
if (PW_MIN_DIGIT>1) { $DIGIT_S = "s"; }
if (PW_MIN_OTHER>1) { $OTHER_S = "s"; }

echo "<li> at least <b>" . PW_MIN_CHARS . "</b> total chars\n";
echo "<li> at least <b>" . PW_MIN_CAPSL . "</b> capitalized letter$CAPSL_S (A-Z)\n";
echo "<li> at least <b>" . PW_MIN_MINSL . "</b> lower case letter$MINSL_S (a-z)\n";
echo "<li> at least <b>" . PW_MIN_DIGIT . "</b> digit$DIGIT_S (0-9)\n";
echo "<li> at least <b>" . PW_MIN_OTHER . "</b> other$OTHER_S (any other char)\n";
echo "</ul><br>\n";

echo "<br><b>OR</b> at least 20 chars including at least one space. (passphrase).<br>\n";

echo "Please enter your <b>NEW</b> password below according to this policy :<br>\n";

echo "<pre><font face=courrier size=+0>\n";
echo "New Password : <input type=password name=pass1 maxlength=255 size=30><br>\n";
echo "Confirmation : <input type=password name=pass2 maxlength=255 size=30><br>\n";
echo "</font></pre>\n";

if ($da_error>-1) {
	echo "<font color=#" . $cTheme->main_warnmsg . "><b>" . $errorz[$da_error] . "</b></font><br><br>\n";
} else {
	echo "<br><br>";
}

echo "<input type=submit value=\" UPDATE MY PASSWORD \">\n";

echo "<br><br>\n";

if (trim($dauser->email)!="") {
	echo "<i><b>note:</b> this will also send you an e-mail to your email-in-record (<b>" . $dauser->email . "</b>).</i><br>\n";
} else {
	echo "<i><b>note:</b> this will be displayed in next screen as a confirmation, because you don't have an email-in-record.</i><br>\n";
}

echo "</form>\n";
?>
</body>
</html>
