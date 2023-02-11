<?php
        require("../../../php_includes/cmaster.inc");
        std_connect();
        $user_id = isset($_COOKIE["auth"]) ? std_security_chk($_COOKIE["auth"]) : 0;
$cTheme = get_theme_info();
	if ($user_id<=0) {
		echo "You must be logged in to view that page. <a href=\"../index.php\" target=\"_top\">click here</a>.<br>\n";
		echo "</body></html>\n\n";
		die;
	}
        $admin = std_admin();
        if ($admin==0) { check_file("../regproc.3"); }
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
        $row = pg_fetch_object($res,0);
        $user_name = $row->user_name;

$check1 = pg_safe_exec("SELECT * FROM pending WHERE manager_id='$user_id' AND channel_id='$c' AND status<3");
if (pg_numrows($check1)==0) {
	header("Location: ../right.php\n\n");
	die;
}

echo "<html><head><title>REGISTRATION PROCESS</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");

$res = pg_safe_exec("SELECT name FROM channels WHERE id='$c'");
$row = pg_fetch_object($res,0);
$c_name = $row->name;


echo "<b>CHANNEL SERVICE REGISTRATION PROCESS</b> - CANCELLATION OF YOUR APPLICATION<br><hr noshade size=2><br>\n";


if (($crc==md5("$HTTP_USER_AGENT" . $ts . CRC_SALT_0013)) && $mode=="remove" && $c>0 && isset($c) && $id>0 && isset($id)) {
	// write decision.
	$datime=time();
	$query = "UPDATE pending SET status=4,last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,decision='Cancelled by applicant',decision_ts=date_part('epoch', CURRENT_TIMESTAMP)::int WHERE channel_id='$c' AND created_ts='$id'";
	pg_safe_exec($query);
	//echo htmlspecialchars($query); die;
	$applicant = $user_name . " (" . $user_id . ")";

	$tmp = pg_safe_exec("SELECT supporters.user_id,users.user_name FROM supporters,pending,users WHERE supporters.user_id=users.id AND pending.channel_id=supporters.channel_id AND pending.channel_id='$c'");
	$sup_list = "";

	for ($x=0;$x<pg_numrows($tmp);$x++) {
		$row = pg_fetch_object($tmp,$x);
		$uname = $row->user_name;
		$uuid = $row->user_id;
		$sup_list .= "$uname (" . $uuid . ")";
		if ($x!=(pg_numrows($tmp)-1)) { $sup_list .=", "; }
	}

	log_channel($c,14,"Application Cancelled by applicant - Applicant was: $applicant, Supporters were: " . $sup_list);
	echo "<script language=\"JavaScript1.2\">\n<!--\n\tlocation.href='../right.php';\n//-->\n</script>\n";
	echo "</body></html>\n\n";
	die;
}

// read decision.
echo "<form name=confirm action=cancel_application.php method=post>\n";
echo "<h3>Are you sure you want to CANCEL your application for channel $c_name ?</h3>\n";
echo "<i>if you cancel your application, your pending channel will not be registered,<br>\n";
echo "and you will be able to apply for another channel.</i><br><br>\n";
echo "<input type=submit value=\" YES, CANCEL IT! \">&nbsp;&nbsp;<input type=button value=\" NO, DON'T DO THAT \" onClick=\"location.href='../right.php';\"><br>\n";
echo "<input type=hidden name=id value=$id>\n";
$zets = time();
$zecrc = md5("$HTTP_USER_AGENT" . $zets . CRC_SALT_0013);
echo "<input type=hidden name=ts value=$zets>\n";
echo "<input type=hidden name=c value=$c>\n";
echo "<input type=hidden name=crc value=$zecrc>\n";
echo "<input type=hidden name=mode value=remove>\n";
echo "</form>\n";

echo "</form>\n";
echo "<br><br>\n";
?>
</body>
</html>
