<?
require('../../../php_includes/cmaster.inc');
std_connect();
	$ENABLE_COOKIE_TABLE=0;
$user_id = std_security_chk($auth);
$cTheme = get_theme_info();
if ($user_id>0) {
	std_theme_styles(1); std_theme_body("../");
	echo "You should not view that page while being logged in heh ?!.<br><a href=\"../index.php\" target=\"_top\">click here</a>.<br>\n";
	echo "</body></html>\n\n";
	die;
}
?>
<html>
<head><title><? echo NETWORK_NAME ?> Channel Service: Verification Question/Answer Reset Form</title>
<? std_theme_styles(); ?>
</head>
<? std_theme_body("../"); ?>
<hr>
<h1><? echo NETWORK_NAME ?> Channel Service: Verification Question/Answer Reset Form</h1>
<a href="../index.php" target=_top>Back to login</a><br>
<hr>
<?
if ($p_username=="") {
	echo "<b>The absence is disturbing....";
	die;
}

$blo1 = pg_safe_exec("SELECT * FROM users WHERE lower(user_name)='" . strtolower($p_username) . "'");
if (pg_numrows($blo1)==0) {
	echo "<h2>\n";

	echo "This username does not exists.<br>\n";
	echo "<a href=\"javascript:history.go(-1);\">Try Again</a><br>\n";

	echo "</h2>\n";

	echo "</body></html>\n\n";
	die;
}
$ro1 = pg_fetch_object($blo1,0);
$da_id = $ro1->id;

$uadml = 0;
$ra = pg_safe_exec("SELECT access FROM levels WHERE channel_id=1 AND user_id='" . (int)$da_id . "'");
if ($ra = @pg_fetch_object($ra,0)) {
	$uadml = $ra->access;
}

if (LOCK_PWCHG_LEVEL>0 && $uadml>=LOCK_PWCHG_LEVEL) {
       	echo "<h1>Error</h1><br><h3>\n";
	echo "For security reasons this option has been disabled for you.<br>\n";
	echo "</body>\n";
	echo "</html>\n\n";
	die;
}

if ((int)$ro1->question_id==0 || $ro1->verificationdata=="") {
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
        $days_elapsed = (int)((int)($now - (int)$ro1->signup_ts)/86400);
        if ($days_elapsed < MIN_DAYS_BEFORE_SUPPORT) {
                echo "<h1>Error<br>\n";
                echo "Your USERNAME is too newly created !</h1><br><h2>You can only process this request after your account is at least ".MIN_DAYS_BEFORE_SUPPORT." day(s) old !</h2><br><br>\n";
                echo "<a href=\"javascript:history.go(-1);\">Go back.</a>\n";
                echo "</body>\n";
                echo "</html>\n\n";
                die;
        }

pg_safe_exec("DELETE FROM pending_pwreset WHERE expiration<date_part('epoch', CURRENT_TIMESTAMP)::int");
$blo2 = pg_safe_exec("SELECT * FROM pending_pwreset WHERE user_id='$da_id'");
if (pg_numrows($blo2)>0) {
	echo "<h2>\n";
	$ro2 = pg_fetch_object($blo2,0);
	echo "A request is already being processed, please check your email and confirm your change.<br>\n";
	echo "This confirmation possibility will expire in " . make_duration($ro2->expiration-time()) . ".<br><br>\n";
	echo "<a href=\"javascript:history.go(-1);\">Try Again</a><br>\n";

	echo "</h2>\n";

	echo "</body></html>\n\n";
	die;
}

if ($ro1->post_forms!="" && $ro1->post_forms>0) {
	$curr = time();
	if ($ro1->post_forms>$curr) {
		echo "<h2>\n";

		echo "You will be able to post another FORM on " . cs_time($ro1->post_forms) . ".<br>\n";
		echo "Please <a href=\"../\" target=\"_top\">click here</a><br>\n";

		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;
	} else if ($ro1->post_forms==666) {
		echo "<h2>\n";

		echo "You can't post FORMs, because your account has been locked for FORMs.<br>\n";
		echo "Please <a href=\"../\" target=\"_top\">click here</a><br>\n";

		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;
	}
}

$tref = gen_server_url() . LIVE_LOCATION . "/forgotten_pass.php";
$tref2 = gen_server_url() . $_SERVER['REQUEST_URI'];

if ($_SERVER['HTTP_REFERER'] != $tref && $_SERVER['HTTP_REFERER'] != $tref2) {
	echo "<h2>\n";

	echo "You can only access this form after at least you tried the 'forgotten password' option.<br>\n";
	echo "<a href=\"../forgotten_pass.php\">click here</a><br>\n";

	if ($_SERVER['REMOTE_ADDR']=='176.9.63.176') { echo "- ".$tref."<br>- ".$tref2."<br>- ".$_SERVER['HTTP_REFERER']; print_r($_SERVER); }

	echo "</body></html>\n\n";
	die;
}
if ($crc == md5($HTTP_USER_AGENT . $ts . CRC_SALT_0009)) {
	$da_username = $username;
	$da_emailaddy = $email;


	if (!(preg_match( "/^[A-Za-z0-9_+-.]+@[A-Za-z0-9.-]+\.[A-Za-z][A-Za-z]+$/", $da_emailaddy ))) {
		echo "<h2>\n";

		echo "You need to supply a valid email address for the username.<br>\n";

		echo "<a href=\"javascript:history.go(-1);\">Try Again</a><br>\n";
		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;
	}
	$res = pg_safe_exec("SELECT id FROM users WHERE lower(user_name)='" . strtolower($da_username) . "'");
	if (pg_numrows($res)==0) {
		// invalid username
		echo "<h2>\n";

		echo "This username does not exists.<br>\n";
		echo "<a href=\"javascript:history.go(-1);\">Try Again</a><br>\n";

		echo "</h2>\n";

		echo "</body></html>\n\n";
		die;
	}
	$roo = pg_fetch_object($res,0);
	$uid = $roo->id;
	$res = pg_safe_exec("SELECT user_name,email FROM users WHERE lower(email)='" . strtolower($da_emailaddy) . "' AND id='$uid'");
	if (pg_numrows($res)==0) {
		// email does not match username
		echo "<h2>\n";

		echo "Username and e-mail do not match.<br>\n";
		echo "<a href=\"javascript:history.go(-1);\">Try Again</a><br>\n";

		echo "</h2>\n";

		echo "</body></html>\n\n";
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
	if( !(preg_match( "/^[A-Za-z0-9!\ \/\\.+_-]+$/", $verifdata )) ) {
       		echo( "<h2>\nThat answer is not valid, valid characters are a->z, A->Z, 0->9 and !, /, \, ., +, _, - and space.<br>\n");
       		echo( "please choose another answer to your question.<br><br>\n");
		echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;
       	}

	if (strtolower($verifdata)==strtolower($da_username)) {
		// verif must be != username
		echo "<h2>\n";

		echo "Your verification question must be different than your username.<br>\n";

		echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;

	}
	if (strtolower($verifdata)==strtolower($da_emailaddy)) {
		// verif must be != emailaddy
		echo "<h2>\n";

		echo "Your verification question must be different than your email address.<br>\n";

		echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;

	}
	if (strlen($verifdata)<4) {
		// verif must be 4 chars or more.
		echo "<h2>\n";

		echo "Your verification question needs to be at least 4 chars long.<br>\n";

		echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;

	}
	if (is_locked_va($verifdata)) {
		// verif must not match an entry in the VA locklist (noreg type 5 - stored in 'user_name')
		echo "<h2>\n";

		echo "The verification answer you have chosen is too common. Please pick an answer that is unique and that you will remember.<br>\n";

		echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;
	}


	$cookieval = md5(CRC_SALT_0015 . uniqid("",1) . time() . $da_emailaddy . $verifdata);
	pg_safe_exec("INSERT INTO pending_pwreset (cookie,user_id,question_id,verificationdata,expiration) VALUES ('$cookieval',$uid,'$verifq','$verifdata',date_part('epoch', CURRENT_TIMESTAMP)::int+21600)");
	$confirm_url = gen_server_url() . LIVE_LOCATION . "/forms/confirm_pwreset.php?ID=$cookieval";
	$the_msg = "If you would like to confirm that the new verification question/answer for '$da_username' should be changed as requested,\n";
	$the_msg .= "then click on the link below within 6 hours :\n\n";
	$the_msg .= "\t$confirm_url\n\n\nThank you\n" . NETWORK_NAME . " Channel Service\n\n\nPS- Please do *NOT* reply to this mail.";

	custom_mail($da_emailaddy,"Verification Question/Answer Reset",$the_msg,"From: " . NETWORK_NAME . " Verification answer reset <" . OBJECT_EMAIL . ">\nReply-To: no.reply@thank.you\nX-Mailer: " . NETWORK_NAME . " Channel Service");

	echo "<h2>";
	echo "Please check your e-mail at '$da_emailaddy',<br>then click on the URL to confirm the verification question/answer reset.<br><br>\n";
	echo "Note: *nothing* will be changed if you don't click that URL.\n";
	echo "</h2>\n";
	echo "</body></html>\n\n";
	die;

}
?>
<form method=POST>
<ol>
 <li>Your username: <b><? echo $p_username ?></b><input type=hidden name=username value=<? echo $p_username ?>>
 <li>Your email: <input type=text name=email size=20 maxlength=128><br>
 You <b>must</b> provide the e-mail address you used to register username '<b><? echo $p_username ?></b>'.
 <li>Your NEW choosen verification question : <select name=verifq><?
 	$res = pg_safe_exec("SELECT question_id FROM users WHERE lower(user_name)='" . strtolower($p_username) . "'");
 	$row = pg_fetch_object($res,0);
 	$curr_qid = $row->question_id;
	for ($x=1;$x<=$max_question_id;$x++) {
		if ($curr_qid==$x) { $chkd = " selected"; } else { $chkd = ""; }
		echo "<option$chkd value=$x>" . $question_text[$x] . "</option>\n";
	}
?></select>
 <li>Your NEW answer to this question : <input type=text name=verifdata size=30 maxlength=40>
</ol>
<input type=submit value=" Submit Query ">
<?
	$ts = time();
	$crc = md5($HTTP_USER_AGENT . $ts . CRC_SALT_0009);
?>
<input type=hidden name=ts value=<? echo $ts ?>>
<input type=hidden name=crc value=<? echo $crc ?>>
</form>
</body>
</html>
