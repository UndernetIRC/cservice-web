<?php
error_reporting(E_ALL);
$default_gopage="login.php";
require("../../php_includes/cmaster.inc");
std_init();
//std_connect();
//$user_id = isset($_COOKIE["auth"]) ? std_security_chk($_COOKIE["auth"]) : 0;
$cTheme = get_theme_info();
			std_theme_styles(1);
			std_theme_body();
/* $Id: right.php,v 1.15 2005/03/07 04:48:03 nighty Exp $ */

function not_valid_va ($user_id) {
$r = pg_safe_exec("SELECT * FROM users WHERE id='".(int)$user_id."'");
				if ($o = pg_fetch_object($r)) 
					{
					
					if((!(preg_match( "/^[A-Za-z0-9!\ \/\\.+_-]+$/", $_POST["va"] ))) && ($_POST["step"]=="yes")) { echo "Verification answer contains invalid chars."; 						
							ip_check($o->user_name,1);
							return true;
							}
						else
						{
						if ((strtolower($_POST["va"]) === strtolower($o->verificationdata) ) && ($_POST["step"]=="yes"))
							{
							echo "<b>Your email address is: </b>".$o->email;
							$fmm="DELETE from ips where ipnum='" . cl_ip() . "' AND lower(user_name)='" . strtolower($o->user_name) . "'";
							pg_exec($fmm);
							return false;
							}
							else
							{
							echo "Wrong verification answer!";
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
		echo '<form method=POST><table border=0><tr><td>Enter your verification answer: </td><td><input type="text" name="va" size="10">  <input type="submit" value="Submit"><input type=hidden value="yes" name="step"></td></tr></form>';
		}
		}
		else
		echo 'Too many failed';
	}
	
	}
	else
	{
	echo '<form method=POST><table border=0><tr><td>Enter your verification answer: </td><td><input type="text" name="va" size="10">  <input type="submit" value="Submit"><input type=hidden value="yes" name="step"></td></tr></form>';
	}
	}
else
	echo "Not logged in!";
?>
