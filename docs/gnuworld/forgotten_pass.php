<?
/* $Id: forgotten_pass.php,v 1.8 2005/11/18 04:19:33 nighty Exp $ */
require("../../php_includes/cmaster.inc");
$cTheme = get_theme_info();
std_theme_styles(1);
std_theme_body();
if ($_POST["username"]!="" && (int)$_POST["qid"]>0 && $_POST["passphrase"]=="") {
 	echo "<h1>Error<br>\n";
 	echo "you forgot to answer, heh ;)</h1><br><br>\n";
 	echo "<a href=forgotten_pass.php>Back to the begining...</a><br>\n";
 	echo "</body></html>\n\n";
 	die;
}
if ($_POST["username"]!="" && $_POST["passphrase"]!="" && (int)$_POST["qid"]>0) {
        //TODO: Sanitise username
        //TODO: Sanitise passphrase
        if ($crc != md5( $_POST["username"] . $_SERVER["HTTP_USER_AGENT"] . $_POST["ts"] . CRC_SALT_0002 )) {
        	echo "<h1>Error<br><br>Please use the regular page.</h1>\n";
        	echo "<a href=forgotten_pass.php>click here</a>.";
        	echo "</body></html>\n\n";
        	die;
        }
        std_connect();
        $username=strtolower($_POST["username"]);
        $res=pg_safe_exec("SELECT * FROM users WHERE lower(user_name)='" . post2db($username) . "' AND verificationdata='" . post2db($_POST["passphrase"]) . "' AND question_id='" . (int)$_POST["qid"] . "'");
        if (pg_numrows($res)==0) {
?>
<h1>Error</h1>
The PASSPHRASE entered is not valid.  Please check it and make sure it is correct</h1>
<a href="forgotten_pass.php">Try again.</a><br><br>
<?
ip_check($username,1);
echo "If you forgot your verification answer then go to the <a href=\"forms/pwreset.php?p_username=$username\">Verification Question/Answer Reset Form</a>\n";
?>
</body>
</html>
<?
                die;
        } else {
		$user=pg_fetch_object($res,0);
		$tst=pg_safe_exec("select last_request_ts from lastrequests where ip='" . cl_ip() . "'");
		if ($tst && time()-$tst<$min_time_between_requests) {
			echo "<h1>Error</h1><h3><br>\nYour IP (" . cl_ip() . ") is trying to reconnect too fast -- Throttled.</h3>\n";
			echo "</body></html>\n\n";
			die;
		}
		$valid="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.$*_";
		$password="";
		srand((double) microtime() * 1000000);
		for ($i=0;$i<8;$i++) {
			$password=$password . $valid[rand(0,strlen($valid)-1)];
		}
		for ($i=0;$i<8;$i++) {
			$salt=$salt . $valid[rand(0,strlen($valid)-1)];
		}
		$crypt=$salt . md5($salt . $password);
		$doconf = 0;
		if (LOCK_PWCHG_LEVEL>0 || (CONFIRM_STAR_PWRESET && is_email_valid(CONFIRM_STAR_PWRESET_MAIL))) {
			$ra = pg_safe_exec("SELECT access FROM levels WHERE channel_id=1 AND user_id='" . $user->id . "'");
			if ($oa = @pg_fetch_object($ra,0)) {
				if (LOCK_PWCHG_LEVEL>0 && LOCK_PWCHG_LEVEL<=$oa->access) { // lock prevails...
					echo "<h1>Error</h1><h3><br>\nFor security reasons, this option has been disabled for you.</h3>\n";
					echo "</body></html>\n\n";
					die;
				} elseif ((CONFIRM_STAR_PWRESET && is_email_valid(CONFIRM_STAR_PWRESET_MAIL)) && $oa->access>0) {
					$rp = pg_safe_exec("SELECT * FROM pending_passwordchanges WHERE user_id='" . $user->id . "'");
					if ($op = @pg_fetch_object($rp,0)) {
						echo "<h1>Error</h1><h3><br>\nA pending password change is already in progress for you.</h3>\n";
						echo "</body></html>\n\n";
						die;
					} else {
						$Xcrc = md5($user->id . "modFP" . CRC_SALT_0015 . $crypt);
						pg_safe_exec("INSERT INTO pending_passwordchanges VALUES ('" . post2db($Xcrc) . "','" . $user->id . "','" . $user->password . "','" . $crypt . "','" . post2db($password) . "',now()::abstime::int4)");
						if (LOCK_ON_PWCHG) { $crypt = "*"; } else { $crypt = $user->password; }
						$ss = "[Forgotten Password] Confirmation request for '" . $user->username . "'";
						$mm = "";
						$mm .= "------- SECURITY -------\n\n";
						$mm .= "Forgotten password request for * account:\n";
						$mm .= "USER_ID = " . $user->id . "\n";
						$mm .= "USER_LEVEL = *" . $oa->access . "\n";
						$mm .= "USERNAME = " . $user->user_name . "\n";
						$mm .= "USER_EMAIL = " . $user->email . "\n";
						$mm .= "REQUEST_IP = " . cl_ip() . "\n";
						$mm .= "REQUEST_HOST = " . cl_host() . "\n";
						$ts = time();
						$mm .= "REQUEST_TIME = " . cs_time($ts) . " (" . $ts . ")\n";
						$c_URL = gen_server_url() . LIVE_LOCATION . "/cfp.php?id=" . $user->id . "&crc=" . $Xcrc;
						$a_URL = gen_server_url() . LIVE_LOCATION . "/dfp.php?id=" . $user->id . "&crc=" . $Xcrc;
						$mm .= "\n";
						$mm .= "CONFIRMATION_URL = " . $c_URL . "\n";
						$mm .= "\n";
						$mm .= "CANCEL_URL = " . $a_URL . "\n";
						$mm .= "\n";
						$mm .= "-------------------------\n\n";
						$doconf = 1;
						custom_mail(CONFIRM_STAR_PWRESET_MAIL,$ss,$mm,"From: Channel Service <no.reply@cscweb.undernet.org>\nX-Mailer: CSC-1.1\n\n");
					}
				}
			}
		}
		if (!$doconf || LOCK_ON_PWCHG) {
			$res=pg_safe_exec("update users set password='" . $crypt . "', " .
			     	" last_updated = now()::abstime::int4, " .
			     	" last_updated_by = 'forgotten password (" . cl_ip() . ")' " .
			     	" where " .
			     	"  id='". $user->id . "'");
		}
		if ($res && !$doconf) {
			custom_mail($user->email,$mail_subject_pass . $user->user_name,"Your Cservice password is: " . $password . "\nRemember it!","From: " . $mail_from_pass . "\nReply-To: " . $mail_from_pass . "\nX-Mailer: " . NETWORK_NAME . " Channel Service");
			log_user($user->id,9," ");
		}
		pg_safe_exec("delete from lastrequests where ip='" . cl_ip() . "'");
		pg_safe_exec("insert into lastrequests (ip,last_request_ts) values ('" . cl_ip() . "',now()::abstime::int4)");

?>
<html>
<head><title>Request Successful</title></head>
<?
	std_theme_body();
	if ($doconf) {
		echo "Your new password is pending CService's approval, You will be notified.<br>\n";
		if (LOCK_ON_PWCHG) {
			echo "Your account is locked out until the new password is approved (or not).<br>\n";
		} else {
			echo "Your old password remains active until the new one has been approved.<br>\n";
		}
	} else {
		echo "Your new password has been mailed out to you.";
	}
?>
</body>
</html>
<?
		die;
    	}
}
    $zets = time();
    $zecrc = md5( $zets . $_SERVER["HTTP_USER_AGENT"] . CRC_SALT_0001 );
?>
<html>
<head><title>Forgotten Password recovery</title></head>
<?std_theme_body()?>
<form method=POST action=get_newpass.php>
<input type=hidden name=ts value=<? echo $zets ?>>
<input type=hidden name=crc value=<? echo $zecrc ?>>
<h1>Forgotten Password Recovery</h1>
Please enter the user name to recover :
<input type=text name=username><br><br>

<input type=submit value="Process to next step >>">
</form>
</body>
</html>
