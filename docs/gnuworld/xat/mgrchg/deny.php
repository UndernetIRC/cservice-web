<?
include("../../../../php_includes/cmaster.inc");
std_init();

/* $Id: deny.php,v 1.3 2002/05/20 23:58:05 nighty Exp $ */

$ENABLE_COOKIE_TABLE=0;
if (!acl(XCHGMGR_REVIEW) && !acl(XCHGMGR_ADMIN)) {
	echo "Wrong way ;)";
	die;
}

$cTheme = get_theme_info();
function local_headers() {
	global $cTheme;
	echo "<html><head><title>" . BOT_NAME . "@ (Reject Application)</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body("../../");

	if (acl(XCHGMGR_ADMIN)) {
		echo "<h2>" . BOT_NAME . "@ Admin (Reject Application)</h2>\n";
	} else {
		echo "<h2>" . BOT_NAME . "@ Review (Reject Application)</h2>\n";
	}
}

$pending_q = pg_safe_exec("SELECT * FROM pending_mgrchange WHERE id='$id'");
if (pg_numrows($pending_q)==0) {
	local_headers();
	echo "<b>Invalid ID</b>";
	die;
}
$pending = pg_fetch_object($pending_q,0);

if ($confirm != md5( $TS . CRC_SALT_0020 . $id ) || trim($rreason)=="") {

	local_headers();
	echo "<form name=confirmation action=deny.php method=get onsubmit=\"return checkme(this);\">\n";
	$zets = time();
	$zecrc = md5( $zets . CRC_SALT_0020 . $id );
	echo "<input type=hidden name=id value=$id>\n";
	echo "<input type=hidden name=confirm value=$zecrc>\n";
	echo "<input type=hidden name=TS value=$zets>\n";
	echo "<h3>";
	echo "Are your sure you want to <font color=#" . $cTheme->main_xat_deny . ">DENY</font> the following application :\n\n</h3>\n";

	echo "<table border=1 cellspacing=3 cellpadding=2 bgcolor=#" . $cTheme->table_bgcolor . ">\n";
	echo "<tr bgcolor=#" . $cTheme->table_headcolor . ">\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Posted on</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Channel</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Current Manager</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>New Manager</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Type</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Duration</b></font></td>\n";
	echo "</tr>\n";

	$tmp1 = pg_safe_exec("SELECT name,comment FROM channels WHERE id='$pending->channel_id'");
	$tmp2 = pg_safe_exec("SELECT user_name FROM users WHERE id='$pending->manager_id'");
	$tmp3 = pg_safe_exec("SELECT user_name FROM users WHERE id='$pending->new_manager_id'");

	$unf1 = pg_fetch_object($tmp1,0);
	$unf2 = pg_fetch_object($tmp2,0);
	$unf3 = pg_fetch_object($tmp3,0);

	$ress = pg_safe_exec("SELECT * FROM userlog WHERE user_id='$pending->manager_id' AND event='5'");
	$ress2 = pg_safe_exec("SELECT * FROM userlog WHERE user_id='$pending->new_manager_id' AND event='5'");

	if (pg_numrows($ress)>0 || pg_numrows($ress2)>0 || trim($unf1->comment)!="") {
		echo "<tr bgcolor=#" . $cTheme->table_tr_enlighten3 . ">\n";
	} else { echo "<tr bgcolor=#" . $cTheme->table_bgcolor . ">\n"; }

	echo "<td valign=top>" . cs_time(($pending->expiration-3600)) . "</td>\n";
	echo "<td valign=top><a href=\"../../channels.php?id=$pending->channel_id\">$unf1->name</a> ($pending->channel_id)<hr noshade size=1><pre><u>Reason:</u>\n$pending->reason\n";
	echo "\n<u>Comment:</u>\n";
	if (trim($unf1->comment)!="") {
		echo "<b>" . $unf1->comment . "\n</b>";
	} else {
		echo "<b>none</b>\n";
	}
	echo "</pre></td>\n";
	echo "<td valign=top align=center><a href=\"../../users.php?id=$pending->manager_id\">$unf2->user_name</a> ($pending->manager_id)<hr noshade size=1>\n";
	echo "<pre><u>Comment(s):</u>\n";

	if (pg_numrows($ress)==0) {
		echo "<b>none</b>\n";
	} else {
		for ($y=0;$y<pg_numrows($ress);$y++) {
			$ross = pg_fetch_object($ress,$y);
			echo "<big>.</big> $ross->message\n";
		}
	}
	echo "</pre></td>\n";
	echo "<td valign=top align=center><a href=\"../../users.php?id=$pending->new_manager_id\">$unf3->user_name</a> ($pending->new_manager_id)<hr noshade size=1>";
	echo "<pre><u>Comment(s):</u>\n";

	if (pg_numrows($ress2)==0) {
		echo "<b>none</b>\n";
	} else {
		for ($y=0;$y<pg_numrows($ress2);$y++) {
			$ross2 = pg_fetch_object($ress2,$y);
			echo "<big>.</big> $ross2->message\n";
		}
	}
	echo "</pre></td>\n";

	if ($pending->change_type==0) {
		echo "<td valign=top><font color=#" . $cTheme->main_yes . "><b>Temporary</b></font></td>\n";
		echo "<td valign=top>" . drake_duration($pending->opt_duration) . "</td>";
	} else {
		echo "<td valign=top><font color=#" . $cTheme->main_no . "><b>Permanent</b></font></td>\n";
		echo "<td valign=top><b>N/A</b></td>\n";
	}

	echo "</tr>\n";
	echo "</table>\n";

	echo "<font size=+1>\n";
	echo "Reject reason (mandatory) : <b>will appear as is in the email sent to 'Current Manager'</b><br>\n";
	echo "<textarea name=rreason cols=60 rows=10 wrap>";
	switch ($autoreason) {
		case 1:
			echo "Your new manager is not added as a 499 on your channel as required.";
			break;
		case 2:
			echo "Your new manager is currently 500 on another channel and can't apply to manager change.";
			break;
	}
	echo "</textarea></font>\n";

	echo "<br><br>\n";
	echo "<input type=button value=\"NO, CANCEL !!\" onClick=\"history.go(-1);\">";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<input type=submit value=\"YES, DENY IT\">\n";
?>
<script language="JavaScript1.2">
<!--
function checkme(f) {
	var all_ok = true;
	if (f.rreason.value=="") { all_ok = false; }
	if (!all_ok) {
		alert("Reject reason is mandatory !");
	}
	return(all_ok);
}
//-->
</script>
<?
	echo "</form></body></html>\n\n";
	die;
}

	$temp = pg_safe_exec("SELECT flags,comment,name FROM channels WHERE id='$pending->channel_id'");
	$channel = pg_fetch_object($temp,0);

	$usr1_q = pg_safe_exec("SELECT user_name,email,id FROM users WHERE id='$pending->manager_id'");
	$usr2_q = pg_safe_exec("SELECT user_name,email,id FROM users WHERE id='$pending->new_manager_id'");
	$usr3_q = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");

	if (pg_numrows($usr1_q)==0 || pg_numrows($usr2_q)==0 || pg_numrows($usr3_q)==0) {
		local_headers();
		echo "Doh!:@^#";
		die;
	}

	$usr1 = pg_fetch_object($usr1_q,0);
	$usr2 = pg_fetch_object($usr2_q,0);
	$usr3 = pg_fetch_object($usr3_q,0);

	$the_email  = "";
	$the_email .= "Hello,\n\n";
	$the_email .= "The manager change request for channel '" . $channel->name . "' was REJECTED for the following reason(s) :\n\n";

	$the_email .= str_replace("\'","'",$rreason);

	$the_email .= "\n\n";
	$the_email .= "Sorry.\n";
	$the_email .= "\n";
	$the_email .= "\n";
	$the_email .= "The " . NETWORK_NAME . " Channel Service.\n";

	custom_mail($usr1->email,"[" . $channel->name . "] Manager Change (Rejected)",$the_email,"From: " . $x_at_email . "\nReply-to: Dont.Reply@Thank.You\nX-Mailer: " . NETWORK_NAME . " Channel Service\n\n");

	pg_safe_exec("UPDATE pending_mgrchange SET confirmed='9' WHERE id='$id'");

	log_channel($pending->channel_id,12,"Rejected! (from " . $usr1->user_name . " (" . $usr1->id . ") to " . $usr2->user_name . " (" . $usr2->id . ")) - Reason: " . $rreason . ".");

	header("Location: ./\n\n");
	die;

?>
