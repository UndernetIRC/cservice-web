<?
/* $Id: add.php,v 1.6 2004/07/25 03:31:52 nighty Exp $ */
	$min_lvl=800;
	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = std_security_chk($auth);
        $admin = std_admin();
	$cTheme = get_theme_info();
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='" . (int)$user_id . "'");
        $adm_usr = pg_fetch_object($res,0);
        $adm_user = $adm_usr->user_name;
        if ($admin<=0 && !has_acl($user_id)) {
                echo "Sorry your admin access is too low.";
                die;
        }
$nrw_lvl = 0;
if (acl(XWEBAXS_2)) { $nrw_lvl = 1; }
if (acl(XWEBAXS_3)) { $nrw_lvl = 2; }
echo "<html><head><title>NOREG (ADD MODE)</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");
if ($admin<$min_lvl && $nrw_lvl<1) {
	echo "Sorry, Your admin access is too low.<br><br>\n";
	echo "</body></html>\n\n";
	die;
}
echo "<b>NOREG</b> Editor (ADD MODE) - <a href=\"./index.php\">Home</a><br><br>\n";
?>
<form name=addentry method=post action=add_entry.php>
<input type=hidden name=for_review value=0>
<table border=1 cellspacing=0 cellpadding=3>
<tr><td align=right><b>user_name</b>&nbsp;</td><td><input type=text name=user_name size=20></td></tr>
<tr><td align=right><b>email</b>&nbsp;</td><td><input type=text name=email size=20></td></tr>
<tr><td align=right><b>channel_name</b>&nbsp;</td><td><input type=text name=channel_name size=20></td></tr>
<tr><td align=right><b>type</b>&nbsp;</td><td><select name=type><option value=0>&lt;NULL&gt;</option><option value=1>Non-support</option><option value=2>Abuse</option><option value=3>Elective</option></select></td></tr>
<tr><td align=right><b>never_reg</b>&nbsp;</td><td><input type=checkbox name=never_reg value=1></td></tr>
<!--<tr><td align=right><b>for_review</b>&nbsp;</td><td><input type=checkbox name=for_review value=1></td></tr>//-->
<tr><td align=right><b>expire_time</b>&nbsp;</td><td>in <input type=text name=expire_period0 size=10 value="30">&nbsp;day(s).<input type=hidden name=expire_period1 value=0><!--<select name=expire_period1><option value=0>day(s)</option><option value=1>hour(s)</option><option value=2>second(s)</option></select>//--></td></tr>
<tr><td align=right><b>created_ts</b>&nbsp;</td><td><b>AUTOMATIC</b></td></tr>
<tr><td align=right><b>set_by</b>&nbsp;</td><td><i><? echo $adm_user ?></i><input type=hidden name=set_by value="<? echo $adm_user ?>"></td></tr>
<tr><td align=right><b>reason</b>&nbsp;</td><td><input type=text name=reason size=20></td></tr>
</table><br>
<?
        $add_pass = CRC_SALT_0011;
        $ts = time();
        $crc = md5("$ts$add_pass$HTTP_USER_AGENT");
?>
<input type=hidden name=crc value=<? echo $crc ?>>
<input type=hidden name=ts value=<? echo $ts ?>>
<input type=submit value=" ADD THIS NOREG ENTRY >> ">
</form>
For CService Admins use <b>ONLY</b>.
<?

?>
</body>
</html>


