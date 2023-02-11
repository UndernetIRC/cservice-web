<?
/* $Id: status.php,v 1.7 2004/08/20 12:56:00 nighty Exp $ */
require("../../../php_includes/cmaster.inc");
if (ENABLE_COMPLAINTS_MODULE != 1) { die("This option is disabled. Please contact the server administrator."); }
$cTheme = get_theme_info();
$user_id = isset($_COOKIE["auth"]) ? std_security_chk($_COOKIE["auth"]) : 0;
$admin = std_admin();
std_theme_styles(1);
std_theme_body();
if (complaints_off() && !isoper($user_id)) {
	echo "<h2>The complaints system is temporarily disabled, please lodge a complaint if that is not OK with you.</h2>";
	echo "</body>\n";
	echo "</html>\n\n";
	die;
}


echo "<h2>Complaint Status Followup";
echo "</h2>\n";
echo "<hr width=100% size=1 noshade>";
std_connect();
unset($da_id);
unset($da_t);
$da_t = explode("-",$_GET["ID"]);
$da_id = $da_t[0];
$daq = pg_safe_exec("SELECT * FROM complaints WHERE id='" . (int)$da_id . "' AND status<4 AND ticket_number='" . $_GET["ID"] . "'");
if ($dao = pg_fetch_object($daq)) {
	echo "<br>";
	echo "<pre><font size=+0>";

	echo "ticket-number:\t\t\t" . $_GET["ID"] . "\n";

	echo "status:\t\t\t\t";
	echo ucfirst(strtolower($cmp_status[$dao->status])) . "\n";

	$la_type = -1; // 0: last action is user action,  1: last action is admin action
	echo "last-action:\t\t\t";
	if ($dao->reviewed_ts==0) { echo "Never"; $la_type = 0; } else {

		$xr = pg_safe_exec("SELECT * FROM complaints_threads WHERE reply_text!='' AND complaint_ref='" . (int)$dao->id . "' ORDER BY reply_ts DESC LIMIT 1");
		// select last reply with a public reply into it
		if ($xo = pg_fetch_object($xr)) {
			$dats = $xo->reply_ts;
			$la_type = 1; // assume last action is admin
			if ($xo->reply_by == 0) { $la_type = 0; } // if the last reply_by is 0, (user), .. last action is user
		} else {
			$dats = $dao->reviewed_ts;
			$la_type = 0;
		}
		echo cs_time($dats);
	}
	echo "\n";
	echo "next-action-awaited:\t\t";
	if ($dao->status > 2) { echo "none (ticket is CLOSED)\n"; } else {
		if ($la_type == 0) {
			echo "Pending CService Admin reply\n";
		}
		if ($la_type == 1) {
			echo "Pending your reply (check the URL in the mail you received to do so)\n";
		}
	}
	echo "</font></pre>\n";
} else {
	echo "<br><br><h3>Invalid TICKET number</h3>";
}
?>
</body>
</html>
