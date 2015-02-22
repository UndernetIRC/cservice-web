
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
	header ("Location: ../users.php?id={$user_id}");
	die;
	}
if (!$change_ok) {
	
	die("NOT ALLOWED!");
}

if (!session_id()) {
    session_start();
}
require_once("../../../php_includes/FlashMessage.php");
$flash = new FlashMessage();

$dares = pg_safe_exec("SELECT * FROM users WHERE id='" . $user_id . "'");
$dauser = pg_fetch_object($dares,0);

$error[0] = "You already have two-step verification enabled. You can not reactivate it again. Consult #usernames for more help.";
$user_name=$authtok[0];

if (!ip_check_totp($user_name,0)) {
    $flash->message("Too many failed two-step verification code attempts. Please try again in 24 hours.", "error");
    header("Location: ../users.php?id={$user_id}");
    die;
}

if (has_totp($user_id))
{
    $flash->message("You already have two-step verification enabled. You can not enable it again. Consult #usernames for more help.");
    header("Location: ../users.php?id={$user_id}");
	die;
}

header("Pragma: no-cache\n\n");

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
	$mailm .= "\nHello,\n\nThis is a confirmation that you have chosen to enable two-step verification,\nto complete the activation follow the instructions below.\n\n";
    preg_match('/(.+)\/totp.*/', $REQUEST_URI, $m);
	$confirm_url = gen_server_url() . preg_replace('/\/totp.*/','', $REQUEST_URI) ."/main.php?entotp=1&ID=$cookieval";
	$mailm .= "Make sure that you are logged in to the CService webpage, then click on this link ".$confirm_url." and follow the instructions.\n\n";
	$mailm .= "\n\nThe " . NETWORK_NAME . " Channel Service.\n\n";
	custom_mail($dauser->email,"CService enable two-step verification",$mailm,"From: " . NETWORK_NAME . " Channel Service <" . FROM_NEWUSER . ">\nReply-to: " . OBJECT_EMAIL . "\nX-Mailer: " . NETWORK_NAME . " Channel Service\n\n");
    $flash->message("An activation email for two-step verification has been sent to you,<br>please check your inbox and follow the instructions.");
    header("Location: ../users.php?id={$user_id}");
    die;
}
echo "<html>\n";
echo "<head>\n";
echo "<title>CService enable two-step verification</title>\n";
std_theme_styles();
echo "</head>\n";
std_theme_body();

echo "<h2>Enable two-step verification :</h2>\n";
echo "<form name=act_totp method=post action=activate.php>\n";
echo "<input type=hidden name=SECURE_ID value=\"" . $SECURE_ID . "\">\n";
echo "<input type=hidden name=crc value=\"" . md5( $SECURE_ID . CRC_SALT_0011 ) . "\">\n";
echo "<input type=hidden name=mode value=write>\n";
echo "Are you sure you want to enable two-step verification?<br>\n";
if (($dauser->totp_key !='') && (strlen($dauser->totp_key) < 33))
echo '<input type="checkbox" value="1" name="new_key" id="new_key" checked="checked"/> Generate a new two-step verification key. (If you want to use the old stored key, uncheck this box.)<br><br>';
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
