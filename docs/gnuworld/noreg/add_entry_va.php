<?
/* $Id: add_entry_va.php,v 1.3 2004/07/25 03:31:52 nighty Exp $ */
	$min_lvl=800;
	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = std_security_chk($auth);
        $admin = std_admin();
$cTheme = get_theme_info();
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='" . (int)$user_id . "'");
        $adm_usr = pg_fetch_object($res,0);
        $adm_user = $adm_usr->user_name;
        if ($admin == 0) {
                echo "Restricted to logged in CService Admins, sorry.";
                die;
        }
        if (!($admin >= 800)) {
        	echo "Sorry, your admin access is too low.";
        	die;
        }
$nrw_lvl = 0;
if (acl(XWEBAXS_2)) { $nrw_lvl = 1; }
if (acl(XWEBAXS_3)) { $nrw_lvl = 2; }
echo "<html><head><title>LOCKED VERIFICATION ANSWERS (ADD MODE)</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");
if ($admin<$min_lvl) {
	echo "Sorry, Your admin access is too low.<br><br>\n";
	echo "</body></html>\n\n";
	die;
}

echo "<b>LOCKED VERIFICATION ANSWERS</b> Editor (ADD MODE) - <a href=\"./index.php\">Home</a> - <a href=\"add_va.php\">Add new entry</a><br><br>\n";
$add_pass = CRC_SALT_0011;
if ($crc != md5("$ts$add_pass$HTTP_USER_AGENT")) {
	echo "<b>ERROR TYPE 3</b> - <a href=\"./\">Click here</a><br>\n";
	echo "For CService Admins use <b>ONLY</b>.";
	echo "</body>\n</html>\n\n";
	die;
}

$badargs = 0;

if ($user_name=="") {
	echo "<li> You must put something for <b>verification_answer_pattern</b>.\n";
	$badargs = 1;
}

if ($user_name!="" && !preg_match("/^[A-Za-z0-9?*]+$/",$user_name)) {
	echo "<li> Invalid <b>verification_answer_pattern</b>.\n";
	$badargs = 1;
}

if ($user_name=="*") {
	echo "<li> You cant LOCK everyone, seriously :P.\n";
	$badargs = 1;
}

if (strlen($user_name)<4 && preg_match("/\*/",$user_name)) {
	echo "<li> Pattern too wide. The pattern lenght must be at least 4 chars among with wildcards.\n";
	$badargs = 1;
}

if ($set_by=="") {
	echo "<li> Invalid <b>set_by</b> value (NULL).\n";
	$badargs = 1;
}
if ($reason=="") {
	echo "<li> Invalid <b>reason</b> value (NULL).\n";
	$badargs = 1;
}
if ($badargs) {
	echo "<br><br>\n";
	echo "Click <a href=\"javascript:history.go(-1);\">here</a> to go back to the form.<br>\n";

} else {
	if ($ignorecase == 1) { $dava = strtolower($user_name); $csens = "NO"; $user_name = "!" . strtolower($user_name); } else { $dava = $user_name; $csens = "YES"; }
	$query = "insert into noreg (user_name,email,channel_name,type,never_reg,for_review,expire_time,created_ts,set_by,reason) ";
	$query = $query . "values ('" . $user_name . "','','',6,1,0,0,now()::abstime::int4,'$set_by','$reason')";

	//echo "<b>DEBUG</b>(query): $query<br><br>\n";

	pg_safe_exec($query);
	local_seclog("Added LOCKED VA '" . $dava . "' (case sensitive : " . $csens . ").");

	echo "The <b>LOCKED VERIFICATION ANSWER entry</b> has been sucessfully added.<br>\n";
	echo "<br>\n";
	echo "<h2>DO NOT RELOAD THIS PAGE</h2> or it will be added twice.<br>\n";
	echo "<br>\n";
	echo "<a href=\"add_va.php\">Add a new entry</a> - <a href=\"./index.php\">Go back to search mode</a><br>\n";

}
echo "For CService Admins use <b>ONLY</b>.";

?>
</body>
</html>


