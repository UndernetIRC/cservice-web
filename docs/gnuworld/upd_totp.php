<?
require("../../php_includes/cmaster.inc");
std_connect();
/* $Id: right.php,v 1.15 2005/03/07 04:48:03 nighty Exp $ */
$user_id = std_security_chk($auth);
$admin = std_admin();
$cTheme = get_theme_info();
if ($user_id > 0) {
	if ($admin>=TOTP_RESET_LVL)
		{
		if (!is_numeric($_GET['id']))
			{
			echo '<h3>Bogous userId!</h3><a href="javascript:history.go(-1)">Click</a> to go back.';
			die;
			}
			else
			{
			$totp_id=$_GET['id'];
			$r = pg_safe_exec("SELECT * FROM users WHERE id='".(int)$totp_id."'");
				if ($o = pg_fetch_object($r)) 
					{
					$oldflags = $o->flags; $flags = $oldflags;
					$flags = $oldflags&~TOTP_USR_FLAG;
					pg_safe_exec("UPDATE users SET flags='".$flags."',last_updated=now()::abstime::int4 WHERE id='".(int)$totp_id."'");
					$oldtotp=$o->totp_key;
					// log_user($totp_id,14,"TOTP disabled for %U. Old TOTP key: ".$oldtotp." (%I)");
					log_user($totp_id,14,"TOTP disabled for %U. (%I)");
					header('Location: ' . $_SERVER['HTTP_REFERER']);
					die;
					}
					else
					{
					echo '<h3>userId does not exist!</h3><a href="javascript:history.go(-1)">Click</a> to go back.';
					die;
					}
			}
		
		}
}
?>
