<?
/* $Id: confirm.php,v 1.3 2003/10/19 06:00:55 nighty Exp $ */
require("../../../php_includes/cmaster.inc");
if (ENABLE_COMPLAINTS_MODULE != 1) { die("This option is disabled. Please contact the server administrator."); }
std_connect();
$user_id = std_security_chk($auth);
if ($user_id > 0) { $admin = std_admin(); } else { $admin = 0; }
$cTheme = get_theme_info();
std_theme_styles(1);
std_theme_body();
pg_safe_exec("DELETE FROM complaints WHERE status=0 AND crc_expiration<date_part('epoch', CURRENT_TIMESTAMP)::int");
$r = @pg_safe_exec("SELECT id,from_email FROM complaints WHERE created_crc='" . $_GET["ID"] . "' AND status=0 AND crc_expiration>=date_part('epoch', CURRENT_TIMESTAMP)::int");
if (!$r) { echo "<h2>Invalid ID</h2>"; } else {
	if ($o = pg_fetch_object($r)) {
		$ticket_number = strtoupper($o->id . "-" . substr(md5( $o->id . CRC_SALT_0007 . "ticket" ),0,10));
		pg_safe_exec("UPDATE complaints SET status=1,nicelevel=1,ticket_number='" . $ticket_number . "' WHERE id='" . (int)$o->id . "'");
		echo "<h2>That's it !</h2><br><h3><br>";
		echo "Your complaint has been placed in the work queue and will be processed as soon as possible.<br><br>\n";
		echo "Please allow 3-5 days for processing, You will be re-contacted by e-mail.<br></h3><br><h4><br>\n";
		echo "Your ticket number is <b>" . $ticket_number . "</b>, you can see the status of your complaint by going to :<br>\n";
		$status_url = gen_server_url() . LIVE_LOCATION . "/complaints/status.php";
		$added_url_s = "?ID=" . $ticket_number;
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"status.php" . $added_url_s . "\">" . $status_url . $added_url_s . "</a><br><br>\n";
		echo "You can ABANDON / CLOSE your complaint by going to :<br>\n";
		$close_url = gen_server_url() . LIVE_LOCATION . "/complaints/ticket.php";
		$added_url_c = "?A=close&ID=" . $ticket_number . "&C=" . md5( CRC_SALT_0005 . $ticket_number . "close" );
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"ticket.php" . $added_url_c . "\">" . $close_url . $added_url_c . "</a><br><br>\n";
		echo "The " . NETWORK_NAME . " Channel Service Complaint Department Team.<br><br></h4>\n";
		echo "<br><br>";

		$mmsg = "Your complaint has been recorded in our system under the ticket number :\n\n";
		$mmsg .= "\t\t\t" . $ticket_number . "\n\n";
		$mmsg .= "You will be contacted back via e-mail about your problem, however if you want\n";
		$mmsg .= "you can check the 'status' of your complaint and or cancel it by following the links below :\n\n";
		$mmsg .= "\tView ticket status:\n\t" . $status_url . $added_url_s . "\n\n";
		$mmsg .= "\tClose ticket (CANCEL):\n\t" . $close_url . $added_url_c . "\n\n";
		$mmsg .= "\n\n";
		$mmsg .= "The " . NETWORK_NAME . " Channel Service Complaint Department Team.\n\n\n";
		custom_mail($o->from_email,"[" . NETWORK_NAME . " CService Complaints] " . $ticket_number . " - Opened",$mmsg,"From: " . NETWORK_NAME . " Channel Service <" . OBJECT_EMAIL . ">\nReply-to: DO.NOT@REPLY.THANKS\nX-Mailer: " . NETWORK_NAME . " CService Complaint Module\n\n");
		echo "<a href=\"../\" target=_top><b>Go back</b></a>\n";
	} else {
		echo "<h2>Invalid ID</h2>";
	}
}
?>
</body>
</html>
