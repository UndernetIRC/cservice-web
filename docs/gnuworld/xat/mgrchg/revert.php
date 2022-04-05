<?
include("../../../../php_includes/cmaster.inc");
std_init();

/* $Id: revert.php,v 1.7 2002/05/20 23:58:05 nighty Exp $ */

$ENABLE_COOKIE_TABLE=0;
if (!is_xat_admin()) {
	echo "Wrong way ;)";
	die;
}

$cTheme = get_theme_info();

function local_headers() {
	global $cTheme;
	echo "<html><head><title>" . BOT_NAME . "@ (Revert Application)</title>";
	std_theme_styles();
	echo "</head>";
	std_theme_body("../../");

	echo "<h2>" . BOT_NAME . "@ Admin (Revert Application)</h2>\n";
}

$pending_q = pg_safe_exec("SELECT * FROM pending_mgrchange WHERE id='$id' AND confirmed='3'");
if (pg_numrows($pending_q)==0) {
	local_headers();
	echo "<b>Invalid ID</b>";
	die;
}
$pending = pg_fetch_object($pending_q,0);

if ($confirm != md5( $TS . CRC_SALT_0020 . $id )) {

	local_headers();
	echo "<form name=confirmation action=revert.php method=get>\n";
	$zets = time();
	$zecrc = md5( $zets . CRC_SALT_0020 . $id );
	echo "<input type=hidden name=id value=$id>\n";
	echo "<input type=hidden name=confirm value=$zecrc>\n";
	echo "<input type=hidden name=TS value=$zets>\n";
	echo "<h3>";
	echo "Are your sure you want to <font color=#" . $cTheme->main_xat_revert . ">REVERT TO OLD MANAGER</font> the following application :\n\n</h3>\n";

	echo "<table border=1 cellspacing=3 cellpadding=2 bgcolor=#" . $cTheme->table_bgcolor . ">\n";
	echo "<tr bgcolor=#" . $cTheme->table_headcolor . ">\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Posted on</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Channel</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Old Manager</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Temporary Manager</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Type</b></font></td>\n";
	if (($pending->opt_duration-time())<=0) {
		echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Expired since</b></font></td>\n";
	} else {
		echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Expire in</b></font></td>\n";
	}
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
	echo "<td valign=top><a href=\"../../channels.php?id=$pending->channel_id\">$unf1->name</a> ($pending->channel_id)</td>\n";
	echo "<td valign=top align=center><a href=\"../../users.php?id=$pending->manager_id\">$unf2->user_name</a> ($pending->manager_id)</td>\n";
	echo "<td valign=top align=center><a href=\"../../users.php?id=$pending->new_manager_id\">$unf3->user_name</a> ($pending->new_manager_id)</td>\n";

	if ($pending->change_type==0) {
		echo "<td valign=top><font color=#" . $cTheme->main_yes . "><b>Temporary</b></font></td>\n";

		if (($pending->opt_duration-time())<=0) {
			echo "<td valign=top>" . drake_duration(time()-$pending->opt_duration) . "</td>";
		} else {
			echo "<td valign=top>" . drake_duration($pending->opt_duration-time()) . "</td>";
		}
	}

	echo "</tr>\n";
	echo "</table>\n";

	echo "<br><br>\n";
	echo "<input type=button value=\"NO, CANCEL !!\" onClick=\"history.go(-1);\">";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<input type=submit value=\"YES, REVERT IT\">\n";


	echo "</form></body></html>\n\n";
	die;
}




$toc = $pending->change_type;

if (isset($queriez)) { unset($queriez); }
$q_idx = 0;


	$temp = pg_safe_exec("SELECT flags,comment,name FROM channels WHERE id='$pending->channel_id'");
	$channel = pg_fetch_object($temp,0);

	$curr_flags = $channel->flags;
	$new_flags = (int)$curr_flags|0x00080000; // autotopic
	$new_flags = (int)$new_flags|2097152; // autojoin & stuff
	$new_flags = (int)$new_flags&~0x00000020; // no more temp manager

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

	$curr_comment = str_replace("'","\'",trim($channel->comment));
	$new_comment = " - " . "Reverted back temp managership from \'" . $usr2->user_name . "\' to \'" . $usr1->user_name . "\' on [" . date("M d Y H:i:s",time()-date("Z")) . " GMT/UTC] by: " . $usr3->user_name . ".";

	$the_email  = "";
	$the_email .= "Hello,\n\n";
	$the_email .= "The *TEMPORARY* manager change request for channel '$channel->name' has expired and was reverted to original owner.\n\n";
	$the_email .= "User '" . $usr1->user_name . "' is now unsuspended, and\n";
	$the_email .= "user '" . $usr2->user_name . "' is now level 499.\n\n";
	$the_email .= "\n";
	$the_email .= "Regards.\n";
	$the_email .= "\n";
	$the_email .= "\n";
	$the_email .= "The " . NETWORK_NAME . " Channel Service.\n";

	//echo $the_email; die;

	pg_safe_exec("UPDATE levels SET last_modif=date_part('epoch', CURRENT_TIMESTAMP)::int,last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,suspend_expires=0,suspend_level=NULL,suspend_by='',last_modif_by='*** MANAGER CHANGE ***' WHERE access=500 AND user_id='$pending->manager_id' AND channel_id='$pending->channel_id'");
	pg_safe_exec("UPDATE levels SET access=499,last_modif=date_part('epoch', CURRENT_TIMESTAMP)::int,last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,last_modif_by='*** MANAGER CHANGE ***' WHERE access=500 AND user_id='$pending->new_manager_id' AND channel_id='$pending->channel_id'");
	$chan_update = "UPDATE channels SET last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,flags='" . $new_flags . "',comment='" . str_replace("\n","\\n",$new_comment) . "' WHERE id='$pending->channel_id'";
	pg_safe_exec($chan_update);

	custom_mail($usr1->email,"[" . $channel->name . "] Manager Change (Reverted)",$the_email,"From: " . $x_at_email . "\nCc: " . $usr2->email . "\nReply-to: Dont.Reply@Thank.You\nX-Mailer: " . NETWORK_NAME . " Channel Service\n\n");

	pg_safe_exec("UPDATE pending_mgrchange SET confirmed='8' WHERE id='$id'");

	log_channel($pending->channel_id,12,"Channel reverted back from " . $usr2->user_name . " (" . $usr2->id . ") to " . $usr1->user_name . " (" . $usr1->id . ").");

header("Location: view_tmp.php\n\n");
die;

?>
