<?

	$CAN_EDIT = 1;
	$CAN_ADD = 2;
$ENABLE_COOKIE_TABLE=0;
	include("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();

	$FORCE_GET = 1;
	if (!acl(XHELP)) {
		echo "You are not allowed to use that page.";
		die;
	}

/*
	$lang_test = $ACL_XTRA;
	if ($lang_test!=0 && $lang_test!=$lang_id && $admin<800) {
		echo "You are not allowed to use that page.";
		die;
	}
*/

	$res2 = pg_safe_exec("SELECT * FROM languages WHERE id='$lang_id'");
	if (pg_numrows($res2)==0) {
		echo "Invalid Language ID, sorry.";
		die;
	}
	$row2 = pg_fetch_object($res2,0);
	$lang_name = $row2->name;
	if (!acl(XHELP_CAN_EDIT)) {
		echo "You are not allowed to use that page. (Permission denied)";
		die;
	}

?>
<html>
<head><title>HELP TEXT MANAGER</title>
<? std_theme_styles(); ?>
</head>
<? std_theme_body("../"); ?>
<h2><b>Edit HELP TEXT for '<? echo $lang_name ?>'</b><br></h2>
<a href="index.php">&lt;&lt;&nbsp;Back</a>
<?
if (acl(XHELP_CAN_ADD)) {
	echo "<form name=addcmd action=add_cmd.php method=get>\n";
	echo "<input type=hidden name=langid value=$lang_id>\n";
	echo "<li>&nbsp;Add COMMAND named <input type=text name=cmdname size=20 maxlength=20>&nbsp;&nbsp;<input type=submit value=Go!>\n";
	echo "</form>\n";
}
echo "<table width=100% border=0 cellspacing=1 cellpadding=3>";
echo "<tr bgcolor=#" . $cTheme->table_headcolor . ">\n";

echo "<td width=20%><font color=#" . $cTheme->table_headtextcolor . "><b>COMMAND Name</b></font></td>";
echo "<td width=80%><font color=#" . $cTheme->table_headtextcolor . "><b>HELP Output</b></font></td></tr>\n\n";

$ras = pg_safe_exec("SELECT * FROM help WHERE language_id='$lang_id' ORDER BY topic");

if (pg_numrows($ras)==0) {
	echo "<td colspan=2 bgcolor=#" . $cTheme->table_headtextcolor . "><b>NO COMMANDS IN DB ?!</b>";
	if ($admin>900) { echo "&nbsp;&nbsp;&nbsp;<b>901+ option</b> : <a href=\"init_lang.php?lid=$lang_id\">Initialize Commands for this language</a>"; }
	echo "</td>\n";
} else {
	for ($x=0;$x<pg_numrows($ras);$x++) {
		$roo = pg_fetch_object($ras,$x);
		echo "<tr bgcolor=#" . $cTheme->table_bgcolor . ">\n";
		echo "<td valign=top width=20%><a href=\"edit_cmd.php?langid=$lang_id&cmdname=$roo->topic\">" . $roo->topic . "</a></td>\n";
		if (trim($roo->contents)=="") {
			echo "<td valign=top width=80%><font color=#" . $cTheme->main_no . "><i>no help text available</i></font></td>\n";
		} else {
			echo "<td valign=top width=80%><pre>" . htmlspecialchars(trim($roo->contents)) . "</pre></td>\n";
		}
		echo "</tr>";

	}
}

echo "</table>\n";

?>


</body>
</html>
