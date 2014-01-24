<?
	/* $Id: add.php,v 1.6 2004/07/25 03:31:52 nighty Exp $ */
	$min_lvl=800;
	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = std_security_chk($auth);
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
echo "<html><head><title>Domain/User Lock (ADD MODE)</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");
if (!acl(XDOMAIN_LOCK)) {
	echo "Sorry, Your admin access is too low.<br><br>\n";
	echo "</body></html>\n\n";
	die;
}
echo "<b>Domain/User Lock</b> Editor (ADD MODE) - <a href=\"./index.php\">Home</a><br><br>\n";
?>
<form name=addentry method=post action=add_entry.php>
<table border=1 cellspacing=0 cellpadding=3>
<tr><td align=right><b>email DOMAIN, or<br>User@ PREFIX</b>&nbsp;</td><td><input type=text name=domain size=20 maxlength=255></td></tr>
<tr><td align=right><b>LOCK_USERNAME</b>&nbsp;</td><td><input type=checkbox checked name=f1 value=1></td></tr>
<tr><td align=right><b>LOCK_REGPROC</b>&nbsp;</td><td><input type=checkbox checked name=f2 value=1></td></tr>
<tr><td align=right><b>LOCK_EMAILCHG</b>&nbsp;</td><td><input type=checkbox checked name=f3 value=1></td></tr>
<tr><td align=right><b>LOCK_LOGIN</b>&nbsp;</td><td><input type=checkbox name=f4 value=1></td></tr>
</table>
<font size=-1>
<i><b>note:</b><ul>
<li color=#<?=$cTheme->table_tr_enlighten?>><font color=#<?=$cTheme->table_tr_enlighten?>>If wildcards are used (* or ?) you should only use them for <b>domains</b> and not <b>user</b> prefixes<br><b>example :</b>&nbsp;subdomain.*, mail-3??*.*.net, *warez*.*, root@<br><b>but NOT :</b> user?*@, or *word*@.<br>Try not to ban the whole network :)~</font><br>
<li><b>If the input field above contains a @</b> it will be assimilated to a <font color=#<?=$cTheme->table_tr_enlighten?>><b>user@</b></font> prefix,<br>
and then, <b>no extra char will be allowed after the @</b> sign.<br>
<li>If the input field does <b>NOT</b> contain a <b>@</b> it will be treated as a @<font color=#<?=$cTheme->table_tr_enlighten?>><b>domain.name</b></font>.
</ul>
<br><br></font>
<?
        $add_pass = CRC_SALT_0006;
        $ts = time();
        $crc = md5("$ts$add_pass$HTTP_USER_AGENT");
?>
<input type=hidden name=crc value=<? echo $crc ?>>
<input type=hidden name=ts value=<? echo $ts ?>>
<input type=submit value=" ADD THIS DOMAIN/USER LOCK ENTRY >> ">
</form>
<?

?>
</body>
</html>


