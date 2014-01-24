<?

	$CAN_EDIT = 1;
	$CAN_ADD = 2;
$ENABLE_COOKIE_TABLE=0;
	$lang_id = $langid;

	include("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();

	$FORCE_GET = 1;
	if (!acl(XHELP)) {
		echo "You are not allowed to use that page.";
		die;
	}
	$a_lid = $ACL_XTRA;
	if (($lang_id!=$a_lid && $a_lid>0) || !acl(XHELP_CAN_ADD)) {
		echo "You are not allowed to use that page.";
		die;
	}

	$res2 = pg_safe_exec("SELECT * FROM languages WHERE id='$lang_id'");
	if (pg_numrows($res2)==0) {
		echo "Invalid Language ID, sorry.";
		die;
	}
	$row2 = pg_fetch_object($res2,0);
	$lang_name = $row2->name;

	$tst = pg_safe_exec("SELECT * FROM help WHERE topic='" . strtoupper($cmdname) . "' AND language_id='$lang_id'");
	if (pg_numrows($tst)>0) {

		echo "<html>\n";
		echo "<head><title>HELP TEXT MANAGER</title>";
		std_theme_styles();
		echo "</head>\n";
		std_theme_body("../");
		echo "<font size=+1><u>ERROR</u>:<br>Command '<b>" . strtoupper($cmdname) . "</b>' already exists for specified language ID.<br></font>\n";
		echo "<br><br><a href=\"edit.php?lang_id=$lang_id\">&lt;&lt;&nbsp;Back</a>\n";
		echo "</body></html>\n\n";
		die;
	}

	$query = "INSERT INTO help (topic,language_id,contents) VALUES ('" . strtoupper($cmdname) . "','$lang_id','')";

	//echo $query;
	pg_safe_exec($query);

	header("Location: edit.php?lang_id=$lang_id");
	die;
?>
