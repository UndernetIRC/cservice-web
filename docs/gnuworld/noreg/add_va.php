<?
/* $Id: add_va.php,v 1.2 2003/11/05 02:08:43 nighty Exp $ */
	$min_lvl=800;
	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = isset($_COOKIE["auth"]) ? std_security_chk($_COOKIE["auth"]) : 0;
        $admin = std_admin();
$cTheme = get_theme_info();
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='" . (int)$user_id . "'");
        $adm_usr = pg_fetch_object($res,0);
        $adm_user = $adm_usr->user_name;
	if ($admin == 0) {
		echo "Restricted to logged in CService Admins, sorry.";
		die;
	}
        if (!($admin >= $min_lvl)) {
        	echo "Sorry, your admin access is too low.";
        	die;
        }
$nrw_lvl = 0;
echo "<html><head><title>LOCKED VERIFICATION ANSWERS (ADD MODE)</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");
if ($admin<$min_lvl) {
	echo "Sorry, Your admin access is too low.<br><br>\n";
	echo "</body></html>\n\n";
	die;
}
echo "<b>LOCKED VERIFICATION ANSWERS</b> Editor (ADD MODE) - <a href=\"./index.php\">Home</a><br><br>\n";
?>
<form name=addentry method=post action=add_entry_va.php>
<table border=1 cellspacing=0 cellpadding=3>
<tr><td align=right><b>verification_answer_pattern</b>&nbsp;<br>you can use <b>*</b> or <b>?</b> wilds.&nbsp;</td><td><input type=text name=user_name size=40></td></tr>
<tr><td align=right><b>ignore upper/lower case</b>&nbsp;</td><td><input type=checkbox name=ignorecase value=1 checked></td></tr>
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
<input type=submit value=" ADD THIS LOCKED VERIFICATION ANSWER ENTRY >> ">
</form>
For CService Admins use <b>ONLY</b>.
<?

?>
</body>
</html>


