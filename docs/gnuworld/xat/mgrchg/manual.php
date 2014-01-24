<?
include("../../../../php_includes/cmaster.inc");
std_init();

/* $Id: manual.php,v 1.2 2003/05/25 06:44:02 nighty Exp $ */

$ENABLE_COOKIE_TABLE=0;
if (!acl(XCHGMGR_ADMIN)) {
	echo "Wrong way ;)";
	die;
}
$cTheme = get_theme_info();
function local_headers() {
	global $cTheme;
	echo "<html><head><title>" . BOT_NAME . "@ (Remove from the list)</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body("../../");

	echo "<h2>" . BOT_NAME . "@ Admin (Remove from the list)</h2>\n";
}

$pending_q = pg_safe_exec("SELECT * FROM pending_mgrchange WHERE id='" . $id . "'");
if (pg_numrows($pending_q)==0) {
	local_headers();
	echo "<b>Invalid ID</b>";
	die;
}
$pending = pg_fetch_object($pending_q,0);

if ($confirm != md5( $TS . CRC_SALT_0020 . $id )) {

	local_headers();
	echo "<form name=confirmation action=manual.php method=get>\n";
	$zets = time();
	$zecrc = md5( $zets . CRC_SALT_0020 . $id );
	echo "<input type=hidden name=id value=$id>\n";
	echo "<input type=hidden name=confirm value=$zecrc>\n";
	echo "<input type=hidden name=TS value=$zets>\n";
	echo "<h3>";
	echo "Are your sure you want to <font color=#" . $cTheme->main_text . ">REMOVE FROM THE LIST</font> the following application :\n\n</h3>\n";

	echo "<table border=1 cellspacing=3 cellpadding=2 bgcolor=#" . $cTheme->table_bgcolor . ">\n";
	echo "<tr bgcolor=#" . $cTheme->table_headcolor . ">\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Posted on</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Channel</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Current Manager</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>New Manager</b></font></td>\n";
	echo "</tr>\n";

	$tmp1 = @pg_safe_exec("SELECT name,comment FROM channels WHERE id='$pending->channel_id'");
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
		echo "<a href=\"../../channels.php?id=$pending->channel_id\">" . $unf1->name . "</a> (" . $pending->channel_id . ")<hr noshade size=1><pre><u>Reason:</u>\n$pending->reason\n";
		echo "\n<u>Comment:</u>\n";
		if (trim($unf1->comment)!="") {
			echo "<b>" . $unf1->comment . "\n</b>";
		} else {
			echo "<b>none</b>\n";
		}
		echo "</pre>";
	} else {
		echo "<b>Not Found !@#</b>";
	}
	echo "</td>\n";
	echo "<td valign=top align=center>";
	if ($mgrok) {
		echo "<a href=\"../../users.php?id=$pending->manager_id\">$unf2->user_name</a> ($pending->manager_id)<hr noshade size=1>\n";
		echo "<pre><u>Comment(s):</u>\n";

		if (pg_numrows($ress)==0) {
			echo "<b>none</b>\n";
		} else {
			for ($y=0;$y<pg_numrows($ress);$y++) {
				$ross = pg_fetch_object($ress,$y);
				echo "<big>.</big> $ross->message\n";
			}
		}
		echo "</pre>";
	} else {
		echo "<b>Not Found !@#</b>";
	}
	echo "</td>\n";
	echo "<td valign=top align=center>";
	if ($newmgrok) {
		echo "<a href=\"../../users.php?id=$pending->new_manager_id\">$unf3->user_name</a> ($pending->new_manager_id)<hr noshade size=1>";
		echo "<pre><u>Comment(s):</u>\n";

		if (pg_numrows($ress2)==0) {
			echo "<b>none</b>\n";
		} else {
			for ($y=0;$y<pg_numrows($ress2);$y++) {
				$ross2 = pg_fetch_object($ress2,$y);
				echo "<big>.</big> $ross2->message\n";
			}
		}
		echo "</pre>";
	} else {
		echo "<b>Not Found !@#</b>";
	}
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";

	echo "<br><br>\n";
	echo "<input type=button value=\"NO, CANCEL !!\" onClick=\"history.go(-1);\">";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<input type=submit value=\"YES, REMOVE THIS ENTRY FROM THE LIST IT HAS BEEN TREATED MANUALLY\">\n";


	echo "</form></body></html>\n\n";
	die;
}


pg_safe_exec("DELETE FROM pending_mgrchange WHERE id='" . ($_GET["id"]+0) . "'");
if ($chanok) { $chanstr = "OK" ; } else { $chanstr = "NO"; }
if ($mgrok) { $mgrstr = "OK" ; } else { $mgrstr = "NO"; }
if ($newmgrok) { $newmgrstr = "OK" ; } else { $newmgrstr = "NO"; }
log_channel($pending->channel_id,12,"Manually treated / Removed from the list [CHAN=" . $chanstr . ",MGR=" . $mgrstr . ",NEWMGR=" . $newmgrstr . "]");
if ($_GET["r"]==2) {
	header("Location: ./view_tmp.php\n\n");
} else {
	header("Location: ./\n\n");
}
die;

?>
