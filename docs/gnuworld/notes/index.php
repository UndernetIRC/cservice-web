<?
/* $Id: index.php,v 1.5 2002/05/20 23:58:04 nighty Exp $ */
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


echo "<html><head><title>Read Notes</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body("../");

echo "<h2>Your Notes</h2><br>\n";

echo "<a href=\"../users.php?id=" . $user_id . "\">Back to your details</a>";
$notesc = pg_safe_exec("SELECT message_id FROM notes WHERE from_user_id='" . $user_id . "'");
if (NOTES_LIM_TOTAL==0 || pg_numrows($notesc)<=NOTES_LIM_TOTAL) {
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"new.php\">Write a new note</a>";
}
echo "<br><br>\n";

echo "<form>\n";

echo "<table border=1 cellspacing=0 cellpadding=3 bgcolor=#" . $cTheme->table_bgcolor . ">\n";


$res = pg_safe_exec("SELECT * FROM notes,users WHERE notes.user_id='" . $user_id . "' AND users.id=notes.from_user_id ORDER BY notes.last_updated DESC");
if (pg_numrows($res)==0) {
	echo "<tr><td colspan=4 align=center><h3>No Notes for you</h3></td></tr>\n";
	$nonotes=1;
} else {
	$nonotes=0;
	echo "<tr bgcolor=#" . $cTheme->table_headcolor . ">";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>From</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Date</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Message</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Action</b></font></td>\n";
	echo "</tr>\n";
	for ($x=0;$x<pg_numrows($res);$x++) {
		$row = pg_fetch_object($res,$x);
		echo "<tr>";
		echo "<td valign=top>" . $row->user_name . "</td>\n";
		echo "<td valign=top>" . cs_time($row->last_updated) . " [" . $row->last_updated . "]</td>\n";
		echo "<td valign=top>" . str_replace("\n","<br>\n",htmlspecialchars($row->message)) . "</td>\n";
		echo "<td valign=top>";
		$dacrc_reply = md5( $authcsc . $row->message_id . CRC_SALT_0007 . $user_id );
		$dacrc_del = md5( $authcsc . $row->message_id . CRC_SALT_0013 . $user_id );
		echo "<input type=button value=\"Reply\" onClick=\"location.href='reply.php?id=" . $row->message_id . "&crc=" . $dacrc_reply . "'\">&nbsp;";
		echo "<input type=button value=\"Delete\" onClick=\"delete_one('" . $row->message_id . "','" . $dacrc_del . "');\">";
		echo "</td>\n";
		echo "</tr>\n";
	}
}
echo "</table>\n";
echo "<br><br>";
if (!$nonotes) {
	$dacrc_delall = md5( $authcsc . "all" . CRC_SALT_0013 . $user_id );
	echo "<input type=button value=\"Delete *all* Notes\" onClick=\"delete_all('" . $dacrc_delall . "');\">\n";
}
?>
<script language="JavaScript1.2">
<!--
function delete_one(id,crc) {
	if (confirm('Are you sure ?\n\nThis will permanently delete this note.')) {
		location.href='delete.php?id='+id+'&crc='+crc;
	}
}
function delete_all(crc) {
	if (confirm('Are your sure ?\n\nThis will permanently delete all of your notes.')) {
		location.href='delete.php?id=all&crc='+crc;
	}
}
//-->
</script>
<?
echo "</form>\n";

?>
</body>
</html>


