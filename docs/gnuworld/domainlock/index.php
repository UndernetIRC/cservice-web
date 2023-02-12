<?
	/* $Id: index.php,v 1.6 2004/07/25 03:31:52 nighty Exp $ */
	$min_lvl=800;
	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = isset($_COOKIE["auth"]) ? std_security_chk($_COOKIE["auth"]) : 0;
        $admin = std_admin();
	$cTheme = get_theme_info();
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
        $adm_usr = pg_fetch_object($res,0);
        $adm_user = $adm_usr->user_name;
        if ($admin==0 && !acl(XWEBAXS_2) && !acl(XWEBAXS_3) && !acl(XDOMAIN_LOCK)) {
        	echo "Sorry, your admin access is too low.";
        	die;
        }
$nrw_lvl = 0;
if (acl(XWEBAXS_2)) { $nrw_lvl = 1; }
if (acl(XWEBAXS_3)) { $nrw_lvl = 2; }

echo "<html><head><title>DOMAIN/USER LOCK</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");


echo "<b>Domain/User Lock</b> ";

if ($admin>=$min_lvl || acl(XDOMAIN_LOCK)) { echo "Editor - <a href=\"add.php\">Add a new entry</a><br><br>\n"; } else {
	echo "List<br><br>\n";
}
?>
<form name=display method=get action=list.php>
Filter search&nbsp;<input type=text name=pattern size=20 value="*"><br>
View <select name=types>
<option value="0">All Locks types</option>
<option value="1">Locks on usernames</option>
<option value="2">Locks on regproc</option>
<option value="3">Locks on Email Change Form</option>
<option value="4">Locks on Login</option>
</select>, Order by <select name=order>
<option value="0">Creation date</option>
<option value="1" selected>Domain name</option>
</select><br>
View : [<input type=radio name=view value=1> e-mail addys] - [<input type=radio name=view value=2> user prefixes] - [<input type=radio name=view value=0 checked> both]<br>
<br>
<input type=submit value=" Search... ">
</form>
<br><br><br><br>
<form name=blockcheck method=get action=blockcheck.php>
Want to check if an email provider or a full email address is locked ?<br>
enter it below (no wildcards, things containing a @ are assumed FULL adresses)<br><br>
<i>note: this is different from the above "search" because this one checks if your entry is locked ..<br>
the above one looks into the present records as they are.</i><br><br>
<b>Your entry</b>&nbsp;<input type=text name=im size=20 maxlength=255>&nbsp;<input type=submit value="Check It !">
</form>
<?

?>
</body>
</html>


