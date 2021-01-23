<?
require('../../../php_includes/cmaster.inc');
std_init();
$cTheme = get_theme_info();
$res=pg_safe_exec("SELECT * FROM users WHERE id=" . $user_id);
$user=pg_fetch_object($res,0);


?>
<html>
<head><title><? echo NETWORK_NAME ?> Channel Service: E-mail Change Form</title>
<? std_theme_styles(); ?>
</head>
<? std_theme_body("../"); ?>
<hr>
<h1><? echo NETWORK_NAME ?> Channel Service: E-mail Change Form</h1>
<a href="index.php">Back to forms</a><br>
<hr>
<?
if ($user->verificationdata=="") {
	echo "<h2>\n";

	echo "You need to have verification information set.<br>\n";
	echo "Try <a href=\"../users.php?id=" . $user_id . "\">clicking here</a><br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}

        $now = time();
        $days_elapsed = (int)((int)($now - (int)$user->signup_ts)/86400);
        if ($days_elapsed < MIN_DAYS_BEFORE_SUPPORT) {
                echo "<h1>Error<br>\n";
                echo "The USERNAME entered is too newly created !</h1><br><h2>You can only process this request after your account is at least ".MIN_DAYS_BEFORE_SUPPORT." day(s) old !</h2><br><br>\n";
                echo "<a href=\"javascript:history.go(-1);\">Go back.</a>\n";
                echo "</body>\n";
                echo "</html>\n\n";
                die;
        }

if ($user->post_forms!="" && $user->post_forms>0) {
	$curr = time();
	if ($user->post_forms>$curr) {
		echo "<h2>\n";
		echo "You will be able to post another FORM on " . cs_time($user->post_forms) . ".<br>\n";
		echo "Please <a href=\"../users.php?id=" . $user_id . "\">click here</a><br>\n";

		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;
	} else if ($user->post_forms==666) {
		echo "<h2>\n";

		echo "You can't post FORMs, because your account has been locked for FORMs.<br>\n";
		echo "Please <a href=\"../users.php?id=" . $user_id . "\">click here</a><br>\n";

		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;
	}
}




if ($crc == md5($HTTP_USER_AGENT . $ts . CRC_SALT_0007)) {
	$da_username = $username;
	if ($da_username != $user->user_name) {
		echo "Hmm? hijacking pages ?";
		die;
	}

	$da_newmail = $newemail;

if (is_email_locked($LOCK_EMAILCHG,$da_newmail)) {
	echo "<h2>\n";

	echo "Your new e-mail CANNOT be '$da_newmail' (Unallowed).<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}

$email_nreg = pg_safe_exec("select * from noreg where lower(email) = '" . strtolower($da_newmail) . "'");
if (pg_numrows($email_nreg) >0) {
	echo "<h2>\n";

	echo "Your new e-mail CANNOT be '$da_newmail' (NOREG).<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}

if ($verifdata=="") {
	echo "<h2>\n";

	echo "You need to supply an answer to the verification question.<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}

if ($verifdata!=$user->verificationdata) {
	echo "<h2>\n";

	echo "Invalid verification answer :(<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}

	if (!(preg_match( "/^[A-Za-z0-9_+-.]+@[A-Za-z0-9.-]+\.[A-Za-z][A-Za-z]+$/", $da_newmail ))) {
		echo "<h2>\n";

		echo "You need to supply a valid email address for the new e-mail.<br>\n";

		echo "<a href=\"javascript:history.go(-1);\">Try Again</a><br>\n";
		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;
	}
	$da_emailaddy = $user->email;
	$check1 = pg_safe_exec("SELECT user_name FROM users WHERE lower(email)='" . strtolower($da_newmail) . "'");
	if (pg_numrows($check1)>0) {
		$tmp = pg_fetch_object($check1,0);
		echo "<h2>\n";
		if ($tmp->user_name == $user->user_name) {
			echo "Hum?! You are not changing your e-mail adress here ;P, you put the same one !<br>\n";
		} else {
			echo "This new e-mail address is already used by another CService username.<br>\n";
		}
		echo "<a href=\"javascript:history.go(-1);\">Try Again</a><br>\n";
		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;
	}

	$cookieval = md5(CRC_SALT_0020 . uniqid("",1) . time() . $da_newmail);

	pg_safe_exec("INSERT INTO pending_emailchanges (cookie,user_id,old_email,new_email,expiration,phase) VALUES ('$cookieval',$user_id,'$da_emailaddy','$da_newmail',now()::abstime::int4+21600,1)");
	$confirm_url = gen_server_url() . LIVE_LOCATION. "/forms/confirm_emailchange.php?ID=$cookieval";

	$the_msg = "If you would like to confirm that the new email-in-record for '$da_username' should be '$da_newmail',\n";
	$the_msg .= "then click on the link below within 6 hours :\n\n";
	$the_msg .= "\t$confirm_url\n\n\nThank you\n" . NETWORK_NAME . " Channel Service\n\n\nPS- Please do *NOT* reply to this mail.";
//echo $da_emailaddy;
	custom_mail($da_emailaddy,"E-Mail change request 1/2",$the_msg,"From: " . NETWORK_NAME . " E-Mail Change Request <" . OBJECT_EMAIL . ">\nReply-To: no.reply@thank.you\nX-Mailer: " . NETWORK_NAME . " Channel Service");

	/* make the user can re-port in 10 days. */
	pg_safe_exec("UPDATE users SET post_forms=(now()::abstime::int4+86400*10) WHERE id=" . $user_id);

	echo "<h2>";
	echo "Please check your e-mail at '$da_emailaddy',<br>then click on the URL to continue the email change to phase 2/2.<br><br>\n";
	echo "Note: *nothing* will be changed if you don't click that URL along with the other one in phase 2.\n";
	echo "</h2>\n";
	echo "</body></html>\n\n";
	die;
}
?>
<form method=POST>
<ol>
 <li>Your username: <b><? echo $user->user_name ?></b><input type=hidden name=username value=<? echo $user->user_name ?>>

 <li>Verification Question/Answer:<br>
Question :
<?
/*
<select name=verifq>
for ($x=1;$x<=$max_question_id;$x++) {
	$checkd="";
	if ($x==$user->question_id) { $checkd=" selected"; }
	echo "<option$checkd value=$x>" . $question_text[$x] . "</option>\n";
}
</select>
*/

echo "<b>" . $question_text[$user->question_id] . "</b>";
echo "<input type=hidden name=verifq value=" . $user->question_id . ">\n";



?><br>Answer : <input type=password name=verifdata size=30 maxlength=30>
 <li> Your new email address : <input type=text name=newemail size=40 maxlength=128>
</ol>
<input type=submit value=" Submit Query ">
<?
	$ts = time();
	$crc = md5($HTTP_USER_AGENT . $ts . CRC_SALT_0007);
?>
<input type=hidden name=ts value=<? echo $ts ?>>
<input type=hidden name=crc value=<? echo $crc ?>>
</form>
</body>
</html>
