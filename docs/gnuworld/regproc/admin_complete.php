<?
/* $Id: admin_complete.php,v 1.6 2003/03/15 05:59:00 nighty Exp $ */
	$cache_page=1;
	$min_lvl=800;
        require("../../../php_includes/cmaster.inc");
        std_connect();
        $user_id = std_security_chk($auth);
$cTheme = get_theme_info();
	if ($user_id<=0) {
		echo "You must be logged in to view that page. <a href=\"../index.php\" target=\"_top\">click here</a>.<br>\n";
		echo "</body></html>\n\n";
		die;
	}
        $admin = std_admin();

        if (!acl(XWEBAXS_3)) {
        	echo "Sorry, your admin access is too low.<br>\n";
        	echo "</body></html>\n\n";
        	die;
        }
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
        $row = pg_fetch_object($res,0);
        $user_name = $row->user_name;


echo "<html><head><title>REGISTRATION PROCESS</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");


if ($force!=1) {
if ($id=="" || $id<=0 || $decision=="" || $pcts<=0) {
	echo "<b>Invalid arguments</b><br>\n";
	die;
}

	$res = pg_safe_exec("SELECT name FROM channels WHERE id='$id'");
	$row = pg_fetch_object($res,0);
	$c_name = $row->name;

	echo "<b>CHANNEL APPLICATION ACCEPT (REGISTER)</b><br><hr noshade size=2><br>\n";
	echo "<form name=accept_confirm action=admin_complete.php method=get>\n";
	echo "<input type=hidden name=force value=1>\n";
	if ($from_rejected=="Ok.") {
		echo "<input type=hidden name=from_rejected value=\"Ok.\">\n";
	}
	echo "<input type=hidden name=channel_id value=$id>\n";
	echo "<input type=hidden name=pending_cts value=$pcts>\n";
	echo "<input type=hidden name=ret value=\"" . urlencode(urldecode($rret)) . "\">\n";
	$decision=str_replace("\\&quot;","&quot;",str_replace("\r","",str_replace("\n","&lt;br&gt;",htmlspecialchars($decision))));
	echo "<input type=hidden name=decision value=\"" . $decision . "\">\n";
	echo "<h2>Are you sure you want to REGISTER channel $c_name for the following reason ?</h2>\n";
	echo str_replace("&lt;br&gt;","<br>",str_replace("\'","'",$decision));
	echo "<br><br>\n";
	echo "<input type=submit value=\" YES, REGISTER/ACCEPT THIS CHANNEL \">&nbsp;&nbsp;&nbsp;";
	echo "<input type=button value=\" NO, I'M A WEENIE \" onClick=\"location.href='$HTTP_REFERER';\">\n";
	echo "</form>\n";
} else {
if ($channel_id=="" || $channel_id<=0 || $decision=="" || $pending_cts<=0) {
	echo "<b>Invalid arguments</b><br>\n";
	die;
}

	$c = $channel_id;
	$id = $pending_cts;
	$tts = time();

	if (!($c>0 && $id>0)) { die("err..."); }

	$decision = str_replace(";",":",$decision);

	$decision2 = "by <b>$user_name</b> (CService Admin)<br>";


	if ($from_rejected=="Ok.") {
		$decision2 = $decision2 . "\n(from rejected)<br><br>\n";
	} else {
		$decision2 = $decision2 . "<br>\n";
	}
	$decision2 = $decision2 . $decision;


//	echo "$decision";die;





	$quer2 = "UPDATE pending SET status=3,last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,decision_ts=date_part('epoch', CURRENT_TIMESTAMP)::int,decision='$decision2' WHERE channel_id='$c'";
	pg_safe_exec($quer2);

	$res = pg_safe_exec("SELECT manager_id FROM pending WHERE channel_id='$c'");
	$row = pg_fetch_object($res,0);
	$m_id = $row->manager_id;

	pg_safe_exec("UPDATE channels SET registered_ts=date_part('epoch', CURRENT_TIMESTAMP)::int,last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,comment='' WHERE id='$c'");
	pg_safe_exec("INSERT INTO levels (channel_id,user_id,access,added,added_by,last_modif,last_modif_by,last_updated) VALUES ($c,$m_id,500,date_part('epoch', CURRENT_TIMESTAMP)::int,'*** REGPROC ***',date_part('epoch', CURRENT_TIMESTAMP)::int,'*** REGPROC ***',date_part('epoch', CURRENT_TIMESTAMP)::int)");
	pg_safe_exec("UPDATE users_lastseen SET last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,last_seen=date_part('epoch', CURRENT_TIMESTAMP)::int WHERE user_id='$m_id'");

	$qqq = "UPDATE pending SET reviewed='Y',reviewed_by_id='$user_id' WHERE channel_id='$c'";
	pg_safe_exec($qqq);

	$bla = pg_safe_exec("SELECT user_name FROM users WHERE id='$m_id'");
	$blo = pg_fetch_object($bla,0);
	$mgr_name = $blo->user_name;
	log_channel($c,7,"to $mgr_name");
	review_count_add($user_id);
/*
	$quer3 = "INSERT INTO mailq (user_id,channel_id,created_ts,template,var1,var2,var3,var4,var5) VALUES ($m_id,$c,date_part('epoch', CURRENT_TIMESTAMP)::int,3,'','','','','')";
	pg_safe_exec($quer3);
*/

	echo "<font color=#" . $cTheme->main_warnmsg . "><b>CHANNEL HAS BEEN ACCEPTED/REGISTERED SUCCESSFULLY</b></font>\n";

	if ($ret=="") { $ret = "../list_app.php"; }

	echo "<script language=\"JavaScript1.2\">\n<!--\n\tsetTimeout(location.href='" . urldecode($ret) . "',3000);\n//-->\n</script>\n";




}
?>
</body>
</html>






