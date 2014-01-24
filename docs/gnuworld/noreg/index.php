<?
/* $Id: index.php,v 1.10 2004/07/25 03:31:52 nighty Exp $ */
	$min_lvl=800;
	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = std_security_chk($auth);
        $admin = std_admin();
	$cTheme = get_theme_info();
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='" . (int)$user_id . "'");
        $adm_usr = pg_fetch_object($res,0);
        $adm_user = $adm_usr->user_name;
        if ($admin<=0 && !acl()) {
                echo "Sorry your admin access is too low.";
                die;
        }
$nrw_lvl = 0;
if (acl(XWEBAXS_2)) { $nrw_lvl = 1; }
if (acl(XWEBAXS_3)) { $nrw_lvl = 2; }

echo "<html><head><title>NOREG</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");


echo "<b>NOREG</b> ";

if ($admin>=$min_lvl || $nrw_lvl>0) { echo "Editor - <a href=\"add.php\">Add a new entry</a><br><br>\n"; } else {
	echo "List<br><br>\n";
}
/* Force expired NOREG clean up */
pg_safe_exec("DELETE FROM noreg WHERE never_reg='0' AND expire_time<" . time());
$res = pg_safe_exec("SELECT COUNT(*) AS count FROM noreg WHERE type<4");
$row = pg_fetch_object($res,0);
$tot_noregs = $row->count;
if ($tot_noregs>1) { $e_add = "ies"; } else { $e_add = "y"; }

?>
<form name=display method=get action=list.php>
Filter by <select name=filter>
<option value="c">Channel Name</option>
<option value="u">User Name</option>
<option value="e">E-Mail</option>
</select>&nbsp;<input type=text name=pattern size=20 value=""><br>
View <select name=types>
<option value="0">All types</option>
<option value="1">only 'Non-support'</option>
<option value="2">only 'Abuse'</option>
<option value="3">only 'Elective'</option>
<option value="4">only Expired / For review</option>
</select>, Order by <select name=order>
<option value="0">Creation date</option>
<option value="1">Expiration date</option>
</select><br>
<br>
<input type=submit value=" Search... ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<font color=#<?=$cTheme->main_textlight?>>there is <b><? echo $tot_noregs ?></b> total NOREG entr<? echo $e_add ?> in-record.</font><br><br>
</form>
<?
echo "<br><br><br>\n";
echo "<b>FRAUD USERNAMES</b> ";

if ($admin>=$min_lvl || $nrw_lvl>0) { echo "Editor - <a href=\"add_f.php\">Add a new entry</a><br><br>\n"; } else {
	echo "List<br><br>\n";
}
$res = pg_safe_exec("SELECT COUNT(*) AS count FROM noreg WHERE type=4");
$row = pg_fetch_object($res,0);
$tot_noregs = $row->count;
if ($tot_noregs>1) { $e_add = "ies"; } else { $e_add = "y"; }

?>
<form name=display method=get action=list_f.php>
Search by username&nbsp;<input type=text name=pattern size=20 value=""><br>
Order by <select name=order>
<option value="0">Username</option>
<option value="1">Creation date</option>
</select><br>
<br>
<input type=submit value=" Search... ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<font color=#<?=$cTheme->main_textlight?>>there is <b><? echo $tot_noregs ?></b> total FRAUD USERNAME entr<? echo $e_add ?> in-record.</font><br><br>
</form>
<?
echo "<br><br><br>\n";
echo "<b>LOCKED USERNAMES</b> ";

if ($admin>=$min_lvl) { echo "Editor - <a href=\"add_l.php\">Add a new entry</a><br><br>\n"; } else {
	echo "List<br><br>\n";
}
$res = pg_safe_exec("SELECT COUNT(*) AS count FROM noreg WHERE type=5");
$row = pg_fetch_object($res,0);
$tot_noregs = $row->count;
if ($tot_noregs>1) { $e_add = "ies"; } else { $e_add = "y"; }

?>
<form name=display method=get action=list_l.php>
Search by username&nbsp;<input type=text name=pattern size=20 value="*"><br>
<br>
<input type=submit value=" Search... ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<font color=#<?=$cTheme->main_textlight?>>there is <b><? echo $tot_noregs ?></b> total LOCKED USERNAME entr<? echo $e_add ?> in-record.</font><br><br>
</form>
<? if ($admin>=$min_lvl) { ?>
<?
echo "<br><br><br>\n";
echo "<b>LOCKED VERIFICATION ANSWERS</b> ";

if ($admin>=$min_lvl) { echo "Editor - <a href=\"add_va.php\">Add a new entry</a><br><br>\n"; } else {
	echo "List<br><br>\n";
}
$res = pg_safe_exec("SELECT COUNT(*) AS count FROM noreg WHERE type=6");
$row = pg_fetch_object($res,0);
$tot_noregs = $row->count;
if ($tot_noregs>1) { $e_add = "ies"; } else { $e_add = "y"; }

?>
<form name=display method=get action=list_va.php>
<input type=hidden name=mode value="SE">
Search by Verification answer&nbsp;<input type=text name=pattern size=20 value="*"><br>
<br>
<input type=submit value=" Search... ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<font color=#<?=$cTheme->main_textlight?>>there is <b><? echo $tot_noregs ?></b> total LOCKED VAs entr<? echo $e_add ?> in-record.</font><br><br>
</form>
<br>
<?
echo "<form name=vastats method=get action=list_va.php>";
echo "<input type=hidden name=mode value=\"ST\">\n";
echo "List the verification answers used at least <input name=ucount type=text size=10 maxlength=5 value=150> times (above or equal to " . MIN_TIMES_VA_MATCH . ")<br>\n";
echo "<input type=checkbox value=1 checked name=ignorecase> Ignore lower/upper case when counting&nbsp;&nbsp;&nbsp;&nbsp;";
echo "<input type=submit value=Go!>\n";
echo "</form>\n";
echo "<br><br>";
?>
<? } ?>
For CService Admins use <b>ONLY</b>.
<?

?>
</body>
</html>


