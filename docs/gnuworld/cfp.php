<?php
/* $Id: cfp.php,v 1.1 2005/11/18 04:19:33 nighty Exp $ */
require("../../php_includes/cmaster.inc");
$cTheme = get_theme_info();
std_theme_styles(1);
std_theme_body();
$iid = (int)$_GET["id"];
$ccrc = $_GET["crc"];
$r1 = pg_safe_exec("SELECT * FROM pending_passwordchanges WHERE cookie='" . post2db($ccrc) . "' AND user_id='" . $iid . "'");
if ($o1 = @pg_fetch_object($r1,0)) {
	if ($ccrc == md5($iid . "modFP" . CRC_SALT_0015 . $o1->new_crypt)) {
		// confirmation of password change
		$rez = @pg_safe_exec("UPDATE users SET password='" . $o1->new_crypt . "',last_updated=now()::abstime::int4,last_updated_by='forgotten password (" . cl_ip() . ")' WHERE id='" . $iid . "'");
		if ($rez) {
			$ru = pg_safe_exec("SELECT * FROM users WHERE id='" . $iid . "'");
			$user = pg_fetch_object($ru,0);
			custom_mail($user->email,$mail_subject_pass . $user->user_name,"Your Cservice password is: " . $o1->new_clrpass . "\nRemember it!","From: " . $mail_from_pass . "\nReply-To: " . $mail_from_pass . "\nX-Mailer: " . NETWORK_NAME . " Channel Service");
			log_user($user->id,9," [manual confirmation]");
			pg_safe_exec("DELETE FROM pending_passwordchanges WHERE user_id='" . $user->id . "'");
	       		echo "<h1>Success !<br><br>\n";
	       		echo "Password change has been approved successfully for user '" . $user->user_name . "'</h1>\n";
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
