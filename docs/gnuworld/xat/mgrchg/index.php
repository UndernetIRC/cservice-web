<?

include("../../../../php_includes/cmaster.inc");
std_init();

/* $Id: index.php,v 1.7 2003/05/25 06:36:29 nighty Exp $ */

$ENABLE_COOKIE_TABLE=0;

/*
if ($admin<1000) {
	echo "Temporarily closed for development (back in a few...)";
	echo "<a href=\"javascript:history.go(-1);\">back</a>.";
	die;
}
*/

if ((!acl(XCHGMGR_REVIEW) && !acl(XCHGMGR_ADMIN)) && $admin<600) {
	echo "Wrong way ;)";
	die;
}

$cTheme = get_theme_info();

echo "<html><head><title>" . BOT_NAME . "@ (Pending Review Manager Changes)</title>";
?>
<style type=text/css>
<!--
.frauduser { color: #<?=$cTheme->main_headtextcolor?>; background: #<?=$cTheme->main_frauduser?>; bgcolor: #<?=$cTheme->main_frauduser?>; }
//-->
</style>
<?
std_theme_styles();
echo "</head>\n";
std_theme_body("../../");

if (acl(XCHGMGR_ADMIN)) {
	echo "<h2>" . BOT_NAME . "@ Admin (Pending Review Manager Changes)</h2>\n";
} else {
	echo "<h2>" . BOT_NAME . "@ Review (Pending Review Manager Changes)</h2>\n";
}

echo "<a href=\"../\">Index</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
if (acl(XCHGMGR_ADMIN) || $admin>=600) {
	echo "<a href=\"view_tmp.php\">View Channels With a Temporary Manager</a><br>\n";
}
echo "<br>\n";

if (!acl(XCHGMGR_ADMIN) && !acl(XCHGMGR_REVIEW)) {
	$spc_user = 1;
} else {
	$spc_user = 0;
}

$res = pg_safe_exec("SELECT * FROM pending_mgrchange WHERE confirmed='1' ORDER BY expiration DESC");

if (pg_numrows($res)==0) {
	echo "THERE IS NO REQUEST WAITING TO BE REVIEWED";
	echo "</body></html>\n";
	die;
}


echo "<b>" . pg_numrows($res) . "</b> request(s) to process.... :<br><br>\n";

echo "<form>\n";
echo "<table border=1 cellspacing=3 cellpadding=2 bgcolor=#" . $cTheme->table_bgcolor . ">\n";
echo "<tr bgcolor=#" . $cTheme->table_headcolor . ">\n";
echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Posted on</b></font></td>\n";
echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Channel</b></font></td>\n";
echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Current Manager</b></font></td>\n";
echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>New Manager</b></font></td>\n";
echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Type</b></font></td>\n";
echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Duration</b></font></td>\n";
if (!$spc_user) {
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Action</b></font></td>\n";
}
echo "</tr>\n";

for ($x=0;$x<pg_numrows($res);$x++) {
	//echo "PASS-($x)(" . pg_numrows($res) . ")--";
	$pending = pg_fetch_object($res,$x);

	$tmp1 = @pg_safe_exec("SELECT name,comment FROM channels WHERE id='$pending->channel_id'");
//	$tmp2 = @pg_safe_exec("SELECT * FROM users,userlog WHERE users.id='$pending->manager_id' AND userlog.user_id=users.id AND userlog.event=5");
//	$tmp3 = @pg_safe_exec("SELECT * FROM users,userlog WHERE users.id='$pending->new_manager_id' AND userlog.user_id=users.id AND userlog.event=5");

	$tmp2 = @pg_safe_exec("SELECT user_name FROM users WHERE id='$pending->manager_id'");
	$tmp3 = @pg_safe_exec("SELECT user_name FROM users WHERE id='$pending->new_manager_id'");

	unset($unf1);unset($unf2);unset($unf3);
	if ($unf1 = @pg_fetch_object($tmp1,0)) { $chanok=1; } else { $chanok=0; }
	if ($unf2 = @pg_fetch_object($tmp2,0)) { $mgrok=1; } else { $mgrok=0; }
	if ($unf3 = @pg_fetch_object($tmp3,0)) { $newmgrok=1; } else { $newmgrok=0; }

	if ($mgrok) { $ress = pg_safe_exec("SELECT * FROM userlog WHERE user_id='$pending->manager_id' AND event='5'"); }
	if ($newmgrok) { $ress2 = pg_safe_exec("SELECT * FROM userlog WHERE user_id='$pending->new_manager_id' AND event='5'"); }

	if (($mgrok && pg_numrows($ress)>0) || ($newmgrok && pg_numrows($ress2)>0) || ($chanok && trim($unf1->comment)!="")) {
		echo "<tr bgcolor=#" . $cTheme->table_tr_enlighten3 . ">\n";
	} else { echo "<tr bgcolor=#" . $cTheme->table_bgcolor . ">\n"; }

	echo "<td valign=top>" . cs_time(($pending->expiration-3600)) . "</td>\n";
	echo "<td valign=top>";
	if ($chanok) {
		echo "<a href=\"../../channels.php?id=$pending->channel_id\">" . $unf1->name . "</a> (" . $pending->channel_id . ")<hr noshade size=1><u>Reason:</u><br>\n" . str_replace("\n","<br>\n",$pending->reason) . "<br>\n";
		echo "<br><u>Comment:</u><br>\n";
		if (trim($unf1->comment)!="") {
			echo "<b>" . str_replace("\n","<br>\n",$unf1->comment) . "</b>\n";
		} else {
			echo "<b>none</b>\n";
		}
	} else {
		echo "<b>Not found !@#</b>";
	}
	echo "<br></td>\n";

	if ($mgrok) {
		$tst = pg_safe_exec("SELECT id FROM noreg WHERE type=4 AND lower(user_name)='" . strtolower($unf2->user_name) . "'");
		if (pg_numrows($tst)>0) {
			echo "<td valign=top align=center><a href=\"../../users.php?id=" . $pending->manager_id . "\"><font class=frauduser>" . $unf2->user_name . "</font></a> (" . $pending->manager_id . ")<hr noshade size=1>\n";
		} else {
			echo "<td valign=top align=center><a href=\"../../users.php?id=" . $pending->manager_id . "\">" . $unf2->user_name . "</a> (" . $pending->manager_id . ")<hr noshade size=1>\n";
		}

		echo "<u>Comment(s):</u><br>\n";

		if (pg_numrows($ress)==0) {
			echo "<b>none</b>\n";
		} else {
			for ($y=0;$y<pg_numrows($ress);$y++) {
				$ross = pg_fetch_object($ress,$y);
				echo "<big>.</big> " . $ross->message . "<br>\n";
			}
		}
	} else {
		echo "<td valign=top align=center>";
		echo "<b>Not found !@#</b>";
	}
	echo "</td>\n";

	if ($newmgrok) {
		$tst = pg_safe_exec("SELECT id FROM noreg WHERE type=4 AND lower(user_name)='" . strtolower($unf3->user_name) . "'");
		if (pg_numrows($tst)>0) {
			echo "<td valign=top align=center><a href=\"../../users.php?id=" . $pending->new_manager_id . "\"><font class=frauduser>" . $unf3->user_name . "</font></a> (" . $pending->new_manager_id . ")<hr noshade size=1>";
		} else {
			echo "<td valign=top align=center><a href=\"../../users.php?id=" . $pending->new_manager_id . "\">" . $unf3->user_name . "</a> (" . $pending->new_manager_id . ")<hr noshade size=1>";
		}

		echo "<u>Comment(s):</u><br>\n";

		if (pg_numrows($ress2)==0) {
			echo "<b>none</b>\n";
		} else {
			for ($y=0;$y<pg_numrows($ress2);$y++) {
				$ross2 = pg_fetch_object($ress2,$y);
				echo "<big>.</big> " .$ross2->message . "<br>\n";
			}
		}
	} else {
		echo "<td valign=top align=center>";
		echo "<b>Not Found !@#</b>";
	}
	echo "</td>\n";

	if ($pending->change_type==0) {
		echo "<td valign=top><font color=#" . $cTheme->main_yes . "><b>Temporary</b></font></td>\n";
		echo "<td valign=top>" . drake_duration($pending->opt_duration) . "</td>";
	} else {
		echo "<td valign=top><font color=#" . $cTheme->main_no . "><b>Permanent</b></font></td>\n";
		echo "<td valign=top><b>N/A</b></td>\n";
	}

if (!$spc_user) {
	echo "<td valign=top>";
	if (!$mgrok || !$newmgrok || !$chanok) {
		echo "<input type=button value=\" MANUALLY TREATED \" onClick=\"location.href='manual.php?id=$pending->id';\">";
	} else {
		echo "<input type=button value=\" ACCEPT \" onClick=\"location.href='accept.php?id=$pending->id';\">&nbsp;&nbsp;";
		echo "<input type=button value=\" DENY \" onClick=\"location.href='deny.php?id=$pending->id';\">";
	}
	echo "</td>\n";
}
	echo "</tr>\n";
}

echo "</table>\n";
echo "</form>\n";
?>
</body>
</html>
