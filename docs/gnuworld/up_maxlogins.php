<?php
error_reporting(E_ALL);
$default_gopage="login.php";
require("../../php_includes/cmaster.inc");
std_init();
//std_connect();
//$user_id = std_security_chk($auth);
$cTheme = get_theme_info();
			std_theme_styles(1);
			std_theme_body();
/* $Id: right.php,v 1.15 2005/03/07 04:48:03 nighty Exp $ */

function not_valid_va ($user_id) {
global $allow_maxlogins;
global $temp_msg;
$r = pg_safe_exec("SELECT * FROM users WHERE id='".(int)$user_id."'");
				if ($o = pg_fetch_object($r))
					{

					if((!(preg_match( "/^[A-Za-z0-9!\ \/\\.+_-]+$/", $_POST["va"] ))) && ($_POST["step"]=="yes")) { echo "Verification answer contains invalid chars.";
							ip_check($o->user_name,1);
							return true;
							}
						else
						{
						$has_totp=false;
						$good=false;
						$totp_key=$o->totp_key;
						if ((TOTP_ALLOW_ALL==1) && (TOTP_ON==1) && ($totp_key != ''))
								{
								$temp_msg=" or TOTP token";
								$has_totp=true;
								}
						if ((strtolower($_POST["va"]) === strtolower($o->verificationdata) ) && ($_POST["step"]=="yes"))
							{
							$good=true;
							}
						if (($has_totp) && (!$good))
							{
							$result = Google2FA::verify_key($totp_key, $token=filter_var($_POST["va"], FILTER_SANITIZE_NUMBER_INT));
							if ($result)
								$good=true;
							}
							if ($good)
							{
							$user_age=time()-$o->signup_ts;
							$temp_maxlogins=1;
							if ($user_age > $allow_maxlogins[2])
								{
								$temp_maxlogins=2;
								}
							if ($user_age > $allow_maxlogins[3])
								{
								$temp_maxlogins=3;
								}
							if ( $_POST["maxlogins"] > $temp_maxlogins)
								{
								echo "Chosen value is invalid. No changes were done!";
								echo '<a href ="users.php">Click here</a> to go back to your info page.';
								die;
								}
								else
								{
								$sql=pg_safe_exec("update users set maxlogins=".$_POST["maxlogins"].", last_updated=now()::abstime::int4, last_updated_by='Web Interface (" . $o->user_name . "(" . $o->id . "))'  where id =".$o->id."");

								echo "You've succesfully set maxlogins to <strong>".$_POST["maxlogins"]."</strong> !<br>";
								echo '<a href ="users.php?id='.$o->id.'">Click here</a> to go back to your info page.';
								$fmm="DELETE from ips where ipnum='" . cl_ip() . "' AND lower(user_name)='" . strtolower($o->user_name) . "'";
								log_user($o->id,15, "Changed from ".$o->maxlogins." to ".$_POST["maxlogins"].".");
								die;
								}
							$fmm="DELETE from ips where ipnum='" . cl_ip() . "' AND lower(user_name)='" . strtolower($o->user_name) . "'";

							pg_exec($fmm);
							return false;
							}
							else
							{

							echo "Wrong verification answer".$temp_msg."!";
							ip_check($o->user_name,1);
							return true;
							}
						}
						return true;
					}
					else
					{
					echo "Invalid username ID.";
					die;
					}

}


if (ALLOW_SELF_MAXLOGINS==1)
{
if ($user_id >0)
	{
	if ($_POST["step"]==="yes")
	{
	$r = pg_safe_exec("SELECT * FROM users WHERE id='".(int)$user_id."'");
	if ($o = pg_fetch_object($r))
	{
	if (ip_check($o->user_name,0))
		{
		if ((not_valid_va($user_id)) )
		{
		echo '<form method=POST><table border=0><tr><td>Enter your verification answer'.$temp_msg.': </td><td><input type="text" name="va" size="10">  <input type="submit" value="Submit"><input type=hidden value="yes" name="step"><input type=hidden value="'.$_POST["maxlogins"].'" name="maxlogins"/></td></tr></form>';
		}
		}
		else
		echo 'Too many failed attempts';
	}

	}
	else
	{
	echo '<form method=POST><table border=0><tr><td>Enter your verification answer'.$temp_msg.': </td><td><input type="text" name="va" size="10">  <input type="submit" value="Submit"><input type=hidden value="yes" name="step"><input type=hidden value="'.$_POST["maxlogins"].'" name="maxlogins"/></td></tr></form>';
	}
	}
else
	echo "Not logged in!";
}
else
	echo "You shouldn't be here, feature is disabled!";
?>
