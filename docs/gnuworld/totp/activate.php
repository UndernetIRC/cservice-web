<?
include("../../../php_includes/cmaster.inc");
/* $Id: passwd.php,v 1.5 2003/07/19 01:26:20 nighty Exp $ */
std_connect();
$user_id = std_security_chk($auth);
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
if (isset($_POST['no']))
	{
	header ("Location: ../users.php");
	die;
	}
if (!$change_ok) {
	
	die("NOT ALLOWED!");
}

$dares = pg_safe_exec("SELECT * FROM users WHERE id='" . $user_id . "'");
$dauser = pg_fetch_object($dares,0);

header("Pragma: no-cache\n\n");
$error[0] = "You already have TOTP enabled. You can not reactivate it again. Consult #usernames for more help.";
$user_name=$authtok[0];
	if (!ip_check_totp($user_name,0)) {
		echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n";
		std_theme_styles(1); std_theme_body();
        	echo "<h1>Error<br>\n";
        	echo "Too many failed TOTP attempts. Restart TOTP activation proccess in 24 hours.</h1><br>\n";
		echo "</body>\n";
		echo "</html>\n\n";
		die;
        }
if (has_totp($user_id))
	{
	echo "<html>\n";
	echo "<head>\n";
	echo "<title>CService activate TOTP</title>\n";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body();

	$errors='<h3>'.$error[0].'</h3>';
	echo $errors;
	echo "</body></html>\n\n";
	die;
	}
$key=Google2FA::generate_secret_key();
$cookieval = md5(CRC_SALT_0015 . uniqid("",1) . time() . $key );
$cookieval.='.'.time();
$tmp_cookie=substr(md5(CRC_SALT_0015 . time()), 0, 5);
$cookieval.='.'.$tmp_cookie;
//echo $cookieval;
if ($mode=="write" && $crc == md5( $SECURE_ID . CRC_SALT_0011 )) {
	$tmp_sql=pg_safe_exec("delete from old_totp WHERE id='" .$dauser->id . "'");
	if (($_POST['new_key']!="1") && ($dauser->totp_key !='') && (strlen($dauser->totp_key) < 33))
		{
			$tmp_sql=pg_safe_exec("insert into old_totp values ('". $dauser->id  ."', '".$dauser->totp_key."')");
		}
	$query = "UPDATE users SET totp_key='".$cookieval."' WHERE id=" . ($user_id+0);
	pg_safe_exec($query);
	$mailm = "";
	$mailm .= "\nHello,\n\nThis is the confirmation of your TOTP activation.\n";
	$confirm_url = gen_server_url() . substr($REQUEST_URI,0,strrpos($REQUEST_URI,"/")) ."/confirm.php?ID=$cookieval";
	$mailm .= "Make sure you're logged in to CService webpage, then click the link ".$confirm_url." and follow the instructions to proceede.\n\n";
	$mailm .= "\n\nThe " . NETWORK_NAME . " Channel Service.\n\n";
	custom_mail($dauser->email,"CService TOTP activtion",$mailm,"From: " . NETWORK_NAME . " Channel Service <" . FROM_NEWUSER . ">\nReply-to: " . OBJECT_EMAIL . "\nX-Mailer: " . NETWORK_NAME . " Channel Service\n\n");
	if (TOTP_DEBUG == 1)
		echo '<pre>'.$mailm.'</pre>';
							echo "<html>\n";
							echo "<head>\n";
							echo "<title>CService activate TOTP</title>\n";
							std_theme_styles();
							echo "</head>\n";
							std_theme_body();

							echo "<font size=+1>";
							echo "Your activation email was sent. Please check your inbox and follow the instructions.<br>\n";
							echo "</font>";
							echo "</body></html>\n\n";
							die;
}
echo "<html>\n";
echo "<head>\n";
echo "<title>CService activate TOTP</title>\n";
std_theme_styles();
echo "</head>\n";
std_theme_body();

echo "<h2>TOTP activation :</h2>\n";
echo "<form name=act_totp method=post action=activate.php>\n";
echo "<input type=hidden name=SECURE_ID value=\"" . $SECURE_ID . "\">\n";
echo "<input type=hidden name=crc value=\"" . md5( $SECURE_ID . CRC_SALT_0011 ) . "\">\n";
echo "<input type=hidden name=mode value=write>\n";
echo "Are you sure you want to enable TOTP? This is not reversible by you, you'll need to ask a CService Admin to disable it!<br>\n";
if (($dauser->totp_key !='') && (strlen($dauser->totp_key) < 33))
echo '<input type="checkbox" value="1" name="new_key" id="new_key" checked="checked"/> Generate a new TOTP key. (If you want to use the old stored key, uncheck this box.)<br><br>';
echo "<input type=submit name=yes value=\"YES\">  ";
echo "<input type=submit name=no value=\"NO\" onclick=\"window.location=\'users.php\'\"><br>\n";
echo "</font></pre>\n";

if (trim($dauser->email)!="") {
	echo "<br><i><b>note:</b> A confirmation email will be sent (<b>" . $dauser->email . "</b>).</i><br>\n";
} else {
	echo "<br><i><b>note:</b> this will be displayed in next screen as a confirmation, because you don't have an email-in-record.</i><br>\n";
}

echo "</form>\n";
?>
</body>
</html>
