<?

error_reporting(E_ALL);

include("../../../php_includes/cmaster.inc");
/* $Id: passwd.php,v 1.5 2003/07/19 01:26:20 nighty Exp $ */
std_connect();
$user_id = std_security_chk($auth);
$cTheme = get_theme_info();
if ($user_id==0 || $auth=="") {
		echo "<html>\n";
		echo "<head>\n";
		echo "<title>CService activate TOTP</title>\n";
		std_theme_styles();
		echo "</head>\n";
		std_theme_body();
	echo "<h3>You must be logged in to view that page!</h3>";
			echo "</body>\n";
		echo "</html>\n\n";
		die;
}

$admin = std_admin();
	if (isset($authtok)) { unset($authtok); }
	if (isset($authcsc)) { unset($authcsc); }
	$authtok = explode(":",$auth);
	$authcsc = $authtok[3];
$dares = pg_safe_exec("SELECT * FROM users WHERE id='" . $user_id . "'");
$dauser = pg_fetch_object($dares,0);
//$key=Google2FA::generate_secret_key();
header("Pragma: no-cache\n\n");
$authtok = explode(":",$auth);
$authcsc = $authtok[3];
$SECURE_ID=md5( $user_id . CRC_SALT_0019 . $authcsc );
if ($mode=="write" && $crc == md5( $SECURE_ID . CRC_SALT_0011 )) {
$expire=time()+get_custom_session($user_id);
$user_name=$authtok[0];
	if (!ip_check_totp($user_name,0)) {
		echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n";
		std_theme_styles(1); 
		std_theme_body();
        	echo "<h1>Error<br>\n";
        	echo "Too many failed TOTP attempts. Restart TOTP activation proccess in 24 hours.</h1><br>\n";
		echo "</body>\n";
		echo "</html>\n\n";
		die;
        }
     	$totp_key=$dauser->totp_key;
	$TimeStamp = Google2FA::get_timestamp();	
	$secretkey = Google2FA::base32_decode($totp_key);
	$hex_key=bin2hex($secretkey);
	$key_crc=md5( $totp_key . CRC_SALT_0011 );

	if( $key != $key_crc)
		{
		
		echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n";
		std_theme_styles(1); 
		std_theme_body();
        	echo $key;
        	echo "<h1>Error<br>\n";
        	echo "Highjack attempt !</h1><br>\n";
		echo "</body>\n";
		echo "</html>\n\n";
		die;
		}
	$token=filter_var($_POST['pin'], FILTER_SANITIZE_NUMBER_INT);
   	if ($token!="" && !preg_match(NON_BOGUS_TOTP,trim($token))) {
		echo "<html>\n";
		echo "<head>\n";
		echo "<title>CService activate TOTP</title>\n";
		std_theme_styles();
		echo "</head>\n";
		std_theme_body();
		echo '<h3> WRONG TOKEN!</h3>';
		echo '<h3> Read the instructions below on how to setup Google Two Factor Authentification on your devices using the follwing details:<br><br>';
		echo 'Your base32 key: '.$totp_key.'<br><br>';
		echo 'Your HEX key: '.$hex_key.'<br><br>';	
		echo 'Make sure you store those two keys in a secure place, you will not be able to retrieve them. <br>There are no emails being sent with those keys to your username\'s email address!<br><br>';
		echo 'On success, you\'ll be redirected to your username\'s main page.';
		echo "<form name=act_totp method=post action=confirm.php>\n";
		echo "<input type=hidden name=SECURE_ID value=\"" . $SECURE_ID . "\">\n";
		echo "<input type=hidden name=crc value=\"" . md5( $SECURE_ID . CRC_SALT_0011 ) . "\">\n";
		echo "<input type=hidden name=key value=\"" . md5( $totp_key . CRC_SALT_0011 ) . "\">\n";
		echo "<input type=hidden name=mode value=write>\n";
		echo 'Please generate a TOTP token on your device, and input it in the box below <br>';
		echo "<input type=text name=pin >";
		echo '<input type=submit value=" Submit Query ">';
		echo "</form></h3>\n";	
		include ("index.php");
		echo "</body></html>\n\n";	
			die;

	}
	//$otp       = Google2FA::oath_hotp($secretkey, $TimeStamp);
	$result = Google2FA::verify_key($totp_key, $token);
	if ($result)
		{
		$oldflags = $dauser->flags; 
		$flags = $oldflags;
		$flags = $oldflags|TOTP_USR_FLAG;
		//echo $oldflags.'->'.$flags;
		pg_safe_exec("UPDATE users SET flags='".$flags."',last_updated=now()::abstime::int4 WHERE id='".($user_id+0)."'");
		log_user($user_id,13);
		$ENABLE_COOKIE_TABLE = 0;
		$temp_totp_hash=gen_totp_cookie($totp_key);
		if (COOKIE_DOMAIN!="") 
		SetCookie("totp",$temp_totp_hash,$expire,"/",COOKIE_DOMAIN);
		else
		SetCookie("totp",$temp_totp_hash,$expire,"/");
		$fmm="UPDATE webcookies SET totp_cookie='".$temp_totp_hash."' WHERE user_id='" . (int)$user_id . "'";
		pg_exec($fmm);
		header("Location: ../main.php");
		}
		else
		{
		ip_check_totp($user_name,1);
		echo "<html>\n";
		echo "<head>\n";
		echo "<title>CService activate TOTP</title>\n";
		std_theme_styles();
		echo "</head>\n";
		std_theme_body();
		echo '<h3> WRONG TOKEN!</h3>';
		echo '<h3> Read the instructions below on how to setup Google Two Factor Authentification on your devices using the follwing details:<br><br>';
		echo 'Your base32 key: '.$totp_key.'<br><br>';
		echo 'Your HEX key: '.$hex_key.'<br><br>';	
		echo 'Make sure you store those two keys in a secure place, you will not be able to retrieve them. <br>There are no emails being sent with those keys to your username\'s email address!<br><br>';
		echo 'On success, you\'ll be redirected to your username\'s main page.';
		echo "<form name=act_totp method=post action=confirm.php>\n";
		echo "<input type=hidden name=SECURE_ID value=\"" . $SECURE_ID . "\">\n";
		echo "<input type=hidden name=crc value=\"" . md5( $SECURE_ID . CRC_SALT_0011 ) . "\">\n";
		echo "<input type=hidden name=key value=\"" . md5( $totp_key . CRC_SALT_0011 ) . "\">\n";
		echo "<input type=hidden name=mode value=write>\n";
		echo 'Please generate a TOTP token on your device, and input it in the box below <br>';
		echo "<input type=text name=pin >";
		echo '<input type=submit value=" Submit Query ">';
		echo "</form></h3>\n";	
		include ("index.php");
		echo "</body></html>\n\n";	
			die;
		}
}
//$key='H4VCYVKVIN3FMTKIJFUW25RIGE6DQJZA';
$tmp_sql = pg_safe_exec("SELECT * FROM old_totp WHERE id='" . $dauser->id . "'");
if (pg_num_rows($tmp_sql) == 1)
	{
	$old_totp = pg_fetch_object($tmp_sql,0);
	$key=$old_totp->totp_key;
	}
	else
$key=Google2FA::generate_secret_key();
if ($ID!="" && strlen($ID)<=128) 
{
	$id_parts=explode('.', $ID);
	$test_hash=substr(md5(CRC_SALT_0015 . $id_parts[1]), 0, 5);
	if ($test_hash != $id_parts[2])
			{
			echo "<html>\n";
			echo "<head>\n";
			echo "<title>CService activate TOTP</title>\n";
			std_theme_styles();
			echo "</head>\n";
			std_theme_body();
			echo '<h3>Invalid link. Please make sure you copied the link corectly !</h3>'; 
			echo "</body></html>\n\n";
			die;		
			}
	$now=time();
	if (($now-$id_parts[1]) > TOTP_CONFIRM_INT)
		{
			echo "<html>\n";
			echo "<head>\n";
			echo "<title>CService activate TOTP</title>\n";
			std_theme_styles();
			echo "</head>\n";
			std_theme_body();
			echo '<h3>Link expired. Please restart the activation proccess to generate a new link.</h3>'; 
			echo "</body></html>\n\n";
			die;		
		}
std_connect();
$dares = pg_safe_exec("SELECT * FROM users WHERE totp_key='" . $ID . "'");
$dauser = pg_fetch_object($dares,0);

if($user_id != $dauser->id)
	{

			echo "<html>\n";
			echo "<head>\n";
			echo "<title>CService activate TOTP</title>\n";
			std_theme_styles();
			echo "</head>\n";
			std_theme_body();
			echo '<h3>Link is invalid. Does not belong to the logged in username.</h3>'; 
			echo "</body></html>\n\n";
			die;		

	}
	else
	{
			echo "<html>\n";
			echo "<head>\n";
			echo "<title>CService activate TOTP</title>\n";
			std_theme_styles();
			echo "</head>\n";
			std_theme_body();
			$hex_key=bin2hex(Google2FA::base32_decode($key));
			$query = "UPDATE users SET totp_key='".$key."' WHERE id=" . ($user_id+0);
			pg_safe_exec($query);
			echo '<h3> Read the instructions below on how to setup Google Two Factor Authentification on your devices using the follwing details:<br><br>';
			echo 'Your base32 key: '.$key.'<br><br>';
			echo 'Your HEX key: '.$hex_key.'<br><br>';	
			echo 'Make sure you store those two keys in a secure place, you will not be able to retrieve them. <br>There are no emails being sent with those keys to your username\'s email address!<br><br>';
echo "<form name=act_totp method=post action=confirm.php>\n";
echo "<input type=hidden name=SECURE_ID value=\"" . $SECURE_ID . "\">\n";
echo "<input type=hidden name=crc value=\"" . md5( $SECURE_ID . CRC_SALT_0011 ) . "\">\n";
echo "<input type=hidden name=key value=\"" . md5( $key . CRC_SALT_0011 ) . "\">\n";
echo "<input type=hidden name=mode value=write>\n";
echo 'Please generate a TOTP token on your device, and input it in the box below <br>';
echo "<input type=text name=pin >";
echo '<input type=submit value=" Submit Query ">';
echo "</form></h3>\n";
include ("index.php");
			echo "</body></html>\n\n";
			}
}
?>
