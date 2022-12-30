<?
/* $Id: reply.php,v 1.5 2002/05/20 23:58:04 nighty Exp $ */
	$min_lvl=800;
	require("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();
        if (ENABLE_NOTES==0 || (ENABLE_NOTES && $admin==0 && NOTES_ADMIN_ONLY)) {
                echo "Notes are disabled.";
                die;
        }

	if (isset($authtok)) { unset($authtok); }
	if (isset($authcsc)) { unset($authcsc); }
	$authtok = explode(":",$auth);
	$authcsc = $authtok[3];


$notesc = pg_safe_exec("SELECT message_id FROM notes WHERE from_user_id='" . $user_id . "'");
if (NOTES_LIM_TOTAL>0 && pg_numrows($notesc)>NOTES_LIM_TOTAL) {
	echo "<html><head><title>Reply to a Note</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body("../");

	echo "<h3>You are not allowed to post more notes,<br>";
	echo "you reached the limit of <b>" . NOTES_LIM_TOTAL . "</b> total notes posted.<br><br>\n";
	echo "You need the persons you sent notes to to 'Delete' them to free up notes for you.</h3>\n";
	echo "</body></html>\n\n";
	die;

}

$notesc2 = pg_safe_exec("SELECT message,from_user_id FROM notes WHERE message_id='" . $id . "'");

$notesrow = pg_fetch_object($notesc2,0);
$the_rcpt_id = $notesrow->from_user_id;
$the_orig_msg = $notesrow->message;
$rrr = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $the_rcpt_id . "'");
if (pg_numrows($rrr)==0) {
	die("You can't reply to non-existing users.");
}
$ooo = pg_fetch_object($rrr,0);
$the_rcpt_name = $ooo->user_name;

if ($crc == md5( $authcsc . $user_id . CRC_SALT_0012 )) {
	// actually post the note to the user after some checks...
	$eline = "";
	$err = 0;
	$final_msg = "";
	$rcpt_id = $the_rcpt_id;
	// check message:
	$msg = trim($msg);
	if ($msg=="") {
		$eline .= "<li> Missing Message Body\n";
		$err = 1;
	} else {
		if (strlen($msg)>300) {
			$eline .= "<li> Max Message Size Exceeded (" . strlen($msg) . "/300)\n";
			$err = 1;
		} else {
			$final_msg = str_replace("\n"," ",str_replace("\r","",$msg));
		}
	}

	if ($err) {
		// display error msg and exit.
		echo "<html><head><title>Reply to a Note</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body("../");
		echo "<h2>Error</h2><br>\n";
		echo "<ul>\n";
		echo $eline;
		echo "</ul>\n";
		echo "<br><br>\n";
		echo "<a href=\"javascript:history.go(-1);\">Go back</a>\n";
		echo "</body></html>\n\n";
	} else {

		/* --- Allowed for REPLY only ;P ---
		$vchk0 = pg_safe_exec("SELECT flags FROM users WHERE id='" . $rcpt_id . "'");
		$vrow0 = pg_fetch_object($vchk0,0);
		$rcpt_flags = $vrow0->flags;
		if ((int)$rcpt_flags & 0x0010) { // user dont want new notes
			echo "<html><head><title>Error</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body("../");
			echo "<h2>Error</h2><br>\n";
			echo "<h3>";
			echo "This user has disabled notes receiving,<br>\n";
			echo "you CAN'T send notes to that user, sorry.";
			echo "</h3>";
			echo "<br><br>\n";
			echo "<a href=\"javascript:history.go(-1);\">Go back</a>\n";
			echo "</body></html>\n\n";
			die;
		}
		*/

		$vchk1 = pg_safe_exec("SELECT message_id FROM notes WHERE user_id='" . $rcpt_id . "'");
		if (NOTES_LIM_INBOX>0 && pg_numrows($vchk1)>=NOTES_LIM_INBOX) {
			// rcpt has too much messages
			echo "<html><head><title>Error</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body("../");
			echo "<h2>Error</h2><br>\n";
			echo "<h3>";
			echo "The recipient has too many messages (" . NOTES_LIM_INBOX . "),<br>\n";
			echo "she/he should remove some of the message in order to receive more.";
			echo "</h3>";
			echo "<br><br>\n";
			echo "<a href=\"javascript:history.go(-1);\">Go back</a>\n";
			echo "</body></html>\n\n";
			die;
		}

		$vchk2 = pg_safe_exec("SELECT message_id FROM notes WHERE user_id='" . $rcpt_id . "' AND from_user_id='" . $user_id . "'");
		if (NOTES_LIM_PERUSR>0 && pg_numrows($vchk2)>=NOTES_LIM_PERUSR) {
			// user sent too much msg to same rcpt
			echo "<html><head><title>Error</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body("../");
			echo "<h2>Error</h2><br>\n";
			echo "<h3>";
			echo "You have reached the maximum number of message you can send to this user (" . NOTES_LIM_PERUSR . "),<br>\n";
			echo "she/he should remove some of the message in order for you to post more notes.";
			echo "</h3>";
			echo "<br><br>\n";
			echo "<a href=\"javascript:history.go(-1);\">Go back</a>\n";
			echo "</body></html>\n\n";
			die;
		}

		// write new note to database.
		if ($final_msg!="" && ($rcpt_id+0)>0) {

			if ($deleteorigin=="ok") {
				pg_safe_exec("DELETE FROM notes WHERE message_id='" . $id . "'");
			}

			pg_safe_exec("INSERT INTO notes (user_id,from_user_id,message,last_updated) VALUES ('" . $rcpt_id . "','" . $user_id . "','" . $final_msg . "',date_part('epoch', CURRENT_TIMESTAMP)::int)");
			$notesl = pg_safe_exec("SELECT * FROM notes WHERE user_id='" . $user_id . "'");
			$notes_left = pg_numrows($notesl);
			if ($notes_left>0) { // any notes left ? go back to notes list
				header("Location: index.php\n\n");
			} else { // otherwise, go to user's details.
				header("Location: ../users.php?id=" . $user_id . "\n\n");
			}
		} else {
			die("Internal Error !@#");
		}
	}
	die;
}


echo "<html><head><title>Reply to a Note</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body("../");


echo "<h2>Reply to a Note</h2><br>\n";

echo "<a href=\"../users.php?id=" . $user_id . "\">Back to your details</a>";
$notesr = pg_safe_exec("SELECT message_id FROM notes WHERE user_id='" . $user_id . "'");
if (pg_numrows($notesr)>0) {
	echo "&nbsp;&nbsp;<a href=\"index.php\">Back to notes list (" . pg_numrows($notesr) . ")</a>";
}

echo "<form name=reply method=post onsubmit=\"return check(this);\">\n";
echo "<br>";
echo "<table border=1 cellspacing=0 cellpadding=3 bgcolor=#" . $cTheme->table_bgcolor . ">\n";
echo "<tr>";
echo "<td valign=top align=right bgcolor=#" . $cTheme->table_headcolor . "><font color=#" . $cTheme->table_headtextcolor . "><b>To&nbsp;:&nbsp;</b></font></td>\n";
echo "<td valign=top>&nbsp;" . $the_rcpt_name . "<input type=hidden name=rcpt value=\"" . $the_rcpt_id . "\">\n";
echo "</tr>\n";

echo "<tr>";
echo "<td valign=top align=right bgcolor=#" . $cTheme->table_headcolor . "><font color=#" . $cTheme->table_headtextcolor . "><b>Original Message&nbsp;:&nbsp;</b></font></td>\n";
echo "<td valign=top>";
echo str_replace("\n","<br>\n",htmlspecialchars($the_orig_msg));
echo "</td>\n";
echo "</tr>\n";


echo "<tr>";
echo "<td valign=top align=right bgcolor=#" . $cTheme->table_headcolor . "><font color=#" . $cTheme->table_headtextcolor . "><b>Your reply&nbsp;:&nbsp;</b></font></td>\n";
echo "<td valign=top>&nbsp;<textarea name=msg cols=40 rows=4 wrap></textarea><br><i>maximum 300 characters.<br>carriage returns will be ignored.</i></td>\n";
echo "</tr>\n";

echo "<tr>";
echo "<td colspan=2 valign=top align=center>";
echo "<input type=checkbox value=\"ok\" name=deleteorigin> Delete original note upon reply<br>\n";
echo "<input type=submit value=\"Send this note\">\n";
echo "</td>\n";
echo "</tr>\n";

echo "</table>\n";

$da_crc = md5( $authcsc . $user_id . CRC_SALT_0012 );

echo "<input type=hidden name=crc value=\"" . $da_crc . "\">\n";
?>
<script language="JavaScript1.2">
<!--
function check(f) {
	if (f.msg.value.length==0 || f.msg.value.length>300) {
		alert('Your message must be between 1 and 300 characters.');
		return(false);
	}
	return(true);
}
//-->
</script>
<?
echo "</form>\n";

?>
</body>
</html>
