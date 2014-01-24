<?
	include("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();
$ENABLE_COOKIE_TABLE=0;

	$FORCE_GET = 1;
	if (!acl(XHELP)) {
		echo "You are not allowed to use that page.";
		die;
	}

	$lang_id = $ACL_XTRA;
	if (!acl(XHELP_CAN_EDIT)) {
		echo "You are not allowed to use that page.";
		die;
	}

	$blo = pg_safe_exec("SELECT * FROM help");
	if (pg_numrows($blo)==0) {
		header("Location: init_help.php");
		die;
	}
?>
<html>
<head><title>HELP TEXT MANAGER</title>
<? std_theme_styles(); ?>
</head>
<? std_theme_body("../"); ?>
<h2><b>HELP TEXT MANAGER</b><br></h2>
<?
if ($admin==0) {
	echo "<b><u>NOTICE</u></b>: If you access this page, then you are a trusted person.\n";
}
?>
<h3>What you can do :</h3>
<?
if ($lang_id==0) {
	echo "<form name=blabla action=edit.php method=get>\n";
	echo "<li>&nbsp;Edit <select name=lang_id>";
	$res = pg_safe_exec("SELECT * FROM languages ORDER BY id");
	if (pg_numrows($res)>0) {
		for ($x=0;$x<pg_numrows($res);$x++) {
			$row = pg_fetch_object($res,$x);
			echo "<option value=" . $row->id . ">" . $row->name . "</option>";
		}
	}
	echo "</select> language definition";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<input type=submit value=Go!>";
} else {
	$res = pg_safe_exec("SELECT * FROM languages WHERE id='$lang_id'");
	if (pg_numrows($res)==0) {
		echo "<li>&nbsp;<blink>ERROR</blink>: Erroneous Language ID in 'helpmgr_users'.\n";
	} else {
		$row = pg_fetch_object($res,0);
		$lang_name = $row->name;
		echo "<li>&nbsp;<a href=\"edit.php?lang_id=$lang_id\">Edit '$lang_name' language definition</a>\n";
	}
}
if ($admin>=800) {
//	echo "<li>&nbsp;<a href=\"usr_mgr.php\">Manage Users</a> (800+)\n";
}
if ($lang_id ==0) {
	echo "</form>\n";
}
?>
</body>
</html>



