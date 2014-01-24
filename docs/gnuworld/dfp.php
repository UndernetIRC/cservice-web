<?php
/* $Id: dfp.php,v 1.1 2005/11/18 04:19:33 nighty Exp $ */
require("../../php_includes/cmaster.inc");
$cTheme = get_theme_info();
std_theme_styles(1);
std_theme_body();
$iid = (int)$_GET["id"];
$ccrc = $_GET["crc"];
$r1 = pg_safe_exec("SELECT * FROM pending_passwordchanges WHERE cookie='" . post2db($ccrc) . "' AND user_id='" . $iid . "'");
if ($o1 = @pg_fetch_object($r1,0)) {
	if ($ccrc == md5($iid . "modFP" . CRC_SALT_0015 . $o1->new_crypt)) {
		// cancellation of password change
		$rez = @pg_safe_exec("UPDATE users SET password='" . $o1->old_crypt . "',last_updated=now()::abstime::int4,last_updated_by='forgotten password (" . cl_ip() . ") cancel' WHERE id='" . $iid . "'");
		if ($rez) {
			pg_safe_exec("DELETE FROM pending_passwordchanges WHERE user_id='" . $user->id . "'");
	       		echo "<h1>Success !<br><br>\n";
	       		echo "Password change has been cancelled successfully for user '" . $user->user_name . "'<br>";
	       		echo "</h1>\n";
	       		echo "</body></html>\n\n";
		       	die;
		} else {
	       		echo "<h1>Error<br><br>\n";
	       		echo "Unknown SQL Error !</h1>\n";
	       		echo "</body></html>\n\n";
		       	die;
		}
	} else {
	       	echo "<h1>Error<br><br>\n";
	       	echo "Invalid credentials !</h1>\n";
	       	echo "</body></html>\n\n";
	       	die;
	}
} else {
       	echo "<h1>Error<br><br>\n";
       	echo "Invalid credentials !</h1>\n";
       	echo "</body></html>\n\n";
       	die;
}

?>
