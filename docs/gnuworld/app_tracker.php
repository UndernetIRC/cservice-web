<?
	/* $Id: app_tracker.php,v 1.13 2004/07/25 03:31:50 nighty Exp $ */
	$min_lvl=800;
	require("../../php_includes/cmaster.inc");
	std_connect();
	$user_id = isset($_COOKIE["auth"]) ? std_security_chk($_COOKIE["auth"]) : 0;
	$admin = std_admin();
	$cTheme = get_theme_info();

	if (!acl(XWEBAXS_2) && !acl(XWEBAXS_3)) {
		die("Wrong way ;)");
	}

	if ($APPID=="" || $RETURL=="") {
        	echo "<html><head><title>Application Tracker</title>";
		std_theme_styles();
		echo "</head>\n";
        	std_theme_body();
		echo "<h2>Please access this page from the Channel Application Review page</h2>";
		echo "</body></html>\n\n";
		die;
	}

	$tmp = explode("-",$APPID);
	$channel_id = $tmp[1];
	$created_ts = $tmp[0];

	$RETOUR = urldecode($RETURL);

	echo "<html><head><title>Application Tracker</title>";
	std_theme_styles();
	echo "</head>\n";
        std_theme_body();

	echo "<center>";
	echo "<font size=+1><b>APPLICATION TRACKER</b><br>Supporters Distribution<br></font></center><hr size=2 noshade><br>\n";

//	echo "<b>.";

	$res = pg_safe_exec("SELECT channels.name FROM channels,pending WHERE channels.id='$channel_id' AND pending.created_ts='$created_ts'");
	$row = pg_fetch_object($res,0);
	$zechan = $row->name;

	// get the supporters UIDs list
	unset($res);unset($row);
	if (isset($uidlist)) { unset($uidlist); }
	$res = pg_safe_exec("SELECT * FROM supporters WHERE channel_id='$channel_id'");
	for ($x=0;$x<pg_numrows($res);$x++) {
		$row = pg_fetch_object($res,$x);
		$uidlist[] = $row->user_id;
	}
//	echo ".";

	// get full channels IDs list (supported, excluding current one of course)
	if (isset($chanlist)) { unset($chanlist); }
	for ($n=0;$n<count($uidlist);$n++) {
		$da_uid = $uidlist[$n];
		unset($res);unset($row);
		$res = pg_safe_exec("SELECT * FROM supporters,pending WHERE supporters.user_id='$da_uid' AND pending.channel_id=supporters.channel_id AND (pending.status<3 OR pending.status=8)");
		for ($x=0;$x<pg_numrows($res);$x++) {
			$row = pg_fetch_object($res,$x);
			if ($row->channel_id!=$channel_id) {
				$chanlist[] = $row->channel_id;
			}
		}
	}
//	echo ".";

	if (!isset($chanlist) || count($chanlist)==0) {
		echo "<h3>No channels supported by anyone on that application</h3>";
	} else {
		// remove duplicates from channel IDs list.
		if (isset($clistok)) { unset($clistok); }
		for ($u=0;$u<count($chanlist);$u++) {
			$curr_cid = $chanlist[$u];
			$is_in = 0;
			for ($z=0;$z<count($clistok);$z++) { if ($clistok[$z]==$curr_cid) { $is_in = 1; } }
			if (!$is_in) { $clistok[] = $curr_cid; }
		}
//		echo ".</b><br>";

		echo "<a href=\"" . $RETOUR . "\" target=right>&lt;&lt;&nbsp;Back to application</a><br><br>\n";
		echo "The following list gives out the unique channel names supported by all the supporters of the application for <b>$zechan</b>.<br>Added to that you will know how many supporters of <b>$zechan</b> are supporting each channel.<br><br>";

		echo "<h3>";
		echo count($clistok) . " channel";
		if (count($clistok)>1) { echo "s"; }
		echo " :<br></h3><h2>\n";

		// display channel list and count in their supporters number of current' app supporters
		for ($c=0;$c<count($clistok);$c++) {
			unset($res);unset($row);
			$csupcount=0;
			$res = pg_safe_exec("SELECT channels.name,pending.created_ts FROM channels,pending WHERE channels.id='$clistok[$c]' AND channels.id=pending.channel_id");
			$row = pg_fetch_object($res,0);
			$cname = $row->name;
			$ccts = $row->created_ts;
			unset($res);unset($row);
			$res = pg_safe_exec("SELECT * FROM supporters WHERE channel_id='$clistok[$c]'");
			if (isset($tmpsuplist)) { unset($tmpsuplist); }
			for ($w=0;$w<pg_numrows($res);$w++) {
				$row = pg_fetch_object($res,$w);
				$tmpsuplist[] = $row->user_id;
			}
			for ($y=0;$y<count($tmpsuplist);$y++) {
				for ($v=0;$v<count($uidlist);$v++) {
					if ($uidlist[$v]==$tmpsuplist[$y]) { $csupcount++; }
				}
			}
			echo "<a href=\"view_app.php?id=" . $ccts . "-" . $clistok[$c] . "\" target=admintrack><b>$cname</b></a>&nbsp;&nbsp;<b>(</b>$csupcount<b>)</b><br>\n";
		}

	}
	echo "</h2>";
	echo "<br><hr size=2 noshade>";
	echo "<center>";
	echo "<a href=\"javascript:window.close();\">close</a>\n";
	echo "</center>";
	echo "</body></html>\n\n";
	die;
?>
