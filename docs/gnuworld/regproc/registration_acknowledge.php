<?
/* $Id: registration_acknowledge.php,v 1.3 2002/05/20 23:58:04 nighty Exp $ */
	require("../../../php_includes/cmaster.inc");
	std_connect();

	$user_id = std_security_chk($auth);
	$admin = std_admin();
$cTheme = get_theme_info();
	if ($admin==0) { check_file("../regproc.3"); }

	$check1 = pg_safe_exec("SELECT * FROM pending WHERE manager_id='$user_id' AND channel_id='$c' AND status=3 AND reg_acknowledged='N'");
	if (pg_numrows($check1)==0) {
		header("Location: ../right.php\n\n");
		die;
	}


	std_theme_styles(1);
	std_theme_body("../");
	echo "<b>CHANNEL SERVICE APPLICATIONS</b> - ACKNOWLEDGE REGISTRATION<br><hr size=2 noshade><br>\n";

	$c_ts = $id;
	$c_id = $c;

	$res = pg_safe_exec("SELECT name FROM channels WHERE id='$c_id'");
	$row = pg_fetch_object($res,0);
	$c_name = $row->name;

	pg_safe_exec("UPDATE pending SET reg_acknowledged='Y',last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int WHERE status=3 AND channel_id='$c_id'");

	echo "You <b>ACKNOWLEDGED</b> registration for <b>$c_name</b><br>\n";

	echo "<br><br>\n";
	echo "<a href=\"../right.php\">Back to main</a>\n";
?>
</body>
</html>
