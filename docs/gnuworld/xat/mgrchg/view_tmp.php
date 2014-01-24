<?

include("../../../../php_includes/cmaster.inc");
std_init();

/* $Id: view_tmp.php,v 1.6 2003/05/25 06:36:29 nighty Exp $ */

$ENABLE_COOKIE_TABLE=0;
if (!acl(XCHGMGR_ADMIN) && $admin<600) {
	echo "Wrong way ;)";
	die;
}

if (!acl(XCHGMGR_ADMIN)) {
	$spc_user = 1;
} else {
	$spc_user = 0;
}

$cTheme = get_theme_info();


echo "<html><head><title>" . BOT_NAME . "@ (Channels with a temporary manager)</title>";
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

if ($spc_user) {
	echo "<h2>" . BOT_NAME . "@ Review (Channels with a temporary manager)</h2>\n";
} else {
	echo "<h2>" . BOT_NAME . "@ Admin (Channels with a temporary manager)</h2>\n";
}

echo "<a href=\"../\">Index</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"./\">View Channels awaiting review</a>\n";
echo "<br><br>\n";

$res = pg_safe_exec("SELECT * FROM pending_mgrchange WHERE confirmed='3' ORDER BY opt_duration");

if (pg_numrows($res)==0) {
	echo "THERE IS NO CHANNEL CURRENTLY USING A TEMP MANAGER";
	echo "</body></html>\n";
	die;
}

echo "<b>" . pg_numrows($res) . "</b> item(s) in list.... :<br><br>\n";

echo "<form>\n";
echo "<table border=1 cellspacing=3 cellpadding=2 bgcolor=#" . $cTheme->table_bgcolor . ">\n";
echo "<tr bgcolor=#" . $cTheme->table_headcolor . ">\n";
echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Posted on</b></font></td>\n";
echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Channel</b></font></td>\n";
echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Temp Manager</b></font></td>\n";
echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Old Manager</b></font></td>\n";
echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Type</b></font></td>\n";
echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Expire in</b></font></td>\n";
if (!$spc_user) {
echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Action</b></font></td>\n";
}
echo "</tr>\n";

for ($x=0;$x<pg_numrows($res);$x++) {
	//echo "PASS-($x)(" . pg_numrows($res) . ")--";
	$pending = pg_fetch_object($res,$x);
	$tmp1 = @pg_safe_exec("SELECT name,comment FROM channels WHERE id='$pending->channel_id'");
	$tmp2 = @pg_safe_exec("SELECT user_name FROM users WHERE id='$pending->manager_id'");
	$tmp3 = @pg_safe_exec("SELECT user_name FROM users WHERE id='$pending->new_manager_id'");

	if ($unf1 = @pg_fetch_object($tmp1,0)) { $chanok=1; } else { $chanok=0; }
	if ($unf2 = @pg_fetch_object($tmp2,0)) { $mgrok=1; } else { $mgrok=0; }
	if ($unf3 = @pg_fetch_object($tmp3,0)) { $newmgrok=1; } else { $newmgrok=0; }

	if ($mgrok) { $ress = pg_safe_exec("SELECT * FROM userlog WHERE user_id='$pending->manager_id' AND event='5'"); }
	if ($newmgrok) { $ress2 = pg_safe_exec("SELECT * FROM userlog WHERE user_id='$pending->new_manager_id' AND event='5'"); }

	if (($pending->opt_duration-time())<=0) {
 		echo "<tr bgcolor=#" . $cTheme->table_tr_enlighten . ">\n";
	} else {
		if (($mgrok && pg_numrows($ress)>0) || ($newmgrok && pg_numrows($ress2)>0) || ($chanok && trim($unf1->comment)!="")) {
			echo "<tr bgcolor=#" . $cTheme->table_tr_enlighten3 . ">\n";
		} else { echo "<tr bgcolor=#" . $cTheme->table_bgcolor . ">\n"; }
	}

	echo "<td valign=top>" . cs_time(($pending->expiration-3600)) . "</td>\n";
	echo "<td valign=top>";
	if ($chanok) {
		echo "<a href=\"../../channels.php?id=$pending->channel_id\">$unf1->name</a> ($pending->channel_id)</td>\n";
	} else {
		echo "<b>Not Found !@#</b></td>";
	}
	if ($newmgrok) {
		$tst = pg_safe_exec("SELECT id FROM noreg WHERE type=4 AND lower(user_name)='" . strtolower($unf3->user_name) . "'");
		if (pg_numrows($tst)>0) {
			echo "<td valign=top align=center><a href=\"../../users.php?id=" . $pending->new_manager_id . "\"><font class=frauduser>" . $unf3->user_name . "</font></a> (" . $pending->new_manager_id . ")";
		} else {
			echo "<td valign=top align=center><a href=\"../../users.php?id=" . $pending->new_manager_id . "\">" . $unf3->user_name . "</a> (" . $pending->new_manager_id . ")";
		}
	} else {
		echo "<td valign=top align=center>";
		echo "<b>Not Found !@#</b>";
	}
	echo "</td>\n";

	if ($mgrok) {
		$tst = pg_safe_exec("SELECT id FROM noreg WHERE type=4 AND lower(user_name)='" . strtolower($unf2->user_name) . "'");
		if (pg_numrows($tst)>0) {
			echo "<td valign=top align=center><a href=\"../../users.php?id=" . $pending->manager_id . "\"><font class=frauduser>" . $unf2->user_name . "</font></a> (" . $pending->manager_id . ")\n";
		} else {
			echo "<td valign=top align=center><a href=\"../../users.php?id=" . $pending->manager_id . "\">" . $unf2->user_name . "</a> (" . $pending->manager_id . ")\n";
		}
	} else {
		echo "<td valign=top align=center>";
		echo "<b>Not Found !@#</b>";
	}

	echo "</td>\n";

	$show_revert=1;

	if ($pending->change_type==0) {
		echo "<td valign=top><font color=#" . $cTheme->main_yes . "><b>Temporary</b></font></td>\n";
		if (($pending->opt_duration-time())<=0) {
			echo "<td valign=top><b>EXPIRED</b></td>";
			$show_revert=0;
		} else {
			echo "<td valign=top>" . drake_duration($pending->opt_duration-time()) . "</td>";
			$show_revert=0;
		}
	}

$show_revert=1;

if (!$spc_user) {
	if ($show_revert) {
		echo "<td valign=top>";
		if ($chanok && $mgrok && $newmgrok) {
			echo "<input type=button value=\" REVERT \" onClick=\"location.href='revert.php?id=$pending->id';\">&nbsp;&nbsp;";
			echo "<input type=button value=\" GO PERM. \" onClick=\"location.href='go_perm.php?id=$pending->id';\">";
		} else {
			echo "<input type=button value=\" MANUALLY TREATED \" onClick=\"location.href='manual.php?id=$pending->id&r=2';\">";
		}
		echo "</td>\n";
	} else {
		echo "<td>&nbsp;</td>\n";
	}
}
	echo "</tr>\n";
}

echo "</table>\n";
echo "</form>\n";
?>
</body>
</html>
