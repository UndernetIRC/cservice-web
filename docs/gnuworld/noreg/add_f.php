<?
/* $Id: add_f.php,v 1.5 2004/07/25 03:31:52 nighty Exp $ */
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
echo "<html><head><title>FRAUD USERNAMES (ADD MODE)</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");
if ($admin<$min_lvl && $nrw_lvl<1) {
	echo "Sorry, Your admin access is too low.<br><br>\n";
	echo "</body></html>\n\n";
	die;
}
echo "<b>FRAUD USERNAMES</b> Editor (ADD MODE) - <a href=\"./index.php\">Home</a><br><br>\n";
?>
<form name=addentry method=post action=add_entry_f.php>
<table border=1 cellspacing=0 cellpadding=3>
<tr><td align=right><b>user_name</b>&nbsp;</td><td><input type=text name=user_name size=40></td></tr>
<tr><td align=right><b>created_ts</b>&nbsp;</td><td><b>AUTOMATIC</b></td></tr>
<tr><td align=right><b>set_by</b>&nbsp;</td><td><i><? echo $adm_user ?></i><input type=hidden name=set_by value="<? echo $adm_user ?>"></td></tr>
<tr><td align=right><b>reason</b>&nbsp;</td><td><input type=text name=reason size=40></td></tr>
</table><br>
<?
        $add_pass = CRC_SALT_0011;
        $ts = time();
        $crc = md5("$ts$add_pass$HTTP_USER_AGENT");
?>
<input type=hidden name=crc value=<? echo $crc ?>>
<input type=hidden name=ts value=<? echo $ts ?>>
<input type=submit value=" ADD THIS FRAUD USERNAME ENTRY >> ">
</form>
For CService Admins use <b>ONLY</b>.
<?

?>
</body>
</html>


