<?
/* $Id: delete.php,v 1.4 2002/05/20 23:58:04 nighty Exp $ */
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
if ($crc != md5( $authcsc . $id . CRC_SALT_0013 . $user_id ) ) {
	echo "<html><head><title>Delete Note</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body("../");
	echo "<h3>Invalid CRC, please use <a href=\"../\" target=_top>the interface provided</a>, thanks.</h3>";
	echo "</body></html>\n\n";
	die;

}

if ($id=="all") { // delete all notes for current user_id.
	pg_safe_exec("DELETE FROM notes WHERE user_id='" . $user_id . "'");
} else {
	$checkr = pg_safe_exec("SELECT * FROM notes WHERE message_id='" . $id . "' AND user_id='" . $user_id . "'");
	if (pg_numrows($checkr)==0) { // invalid message_id for current user !
		echo "<html><head><title>Delete Note</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body("../");
		echo "<h2>Invalid Message ID</h2>";
		echo "</body></html>\n\n";
		die;
	} else { // delete that crap ;P
		pg_safe_exec("DELETE FROM notes WHERE message_id='" . $id . "'");
	}
}

if ($id=="all") {
	$notes_left = 0;
} else {
	$notesc = pg_safe_exec("SELECT * FROM notes WHERE user_id='" . $user_id . "'");
	$notes_left = pg_numrows($notesc);
}
if ($notes_left>0) { // any notes left ? go back to notes list
	header("Location: index.php\n\n");
} else { // otherwise, go to user's details.
	header("Location: ../users.php?id=" . $user_id . "\n\n");
}
die;
?>
