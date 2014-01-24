<?
/* $Id: add_entry.php,v 1.5 2004/07/25 03:31:52 nighty Exp $ */
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
echo "<html><head><title>NOREG (ADD MODE)</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");
if ($admin<$min_lvl && $nrw_lvl<1) {
	echo "Sorry, Your admin access is too low.<br><br>\n";
	echo "</body></html>\n\n";
	die;
}

echo "<b>NOREG</b> Editor (ADD MODE) - <a href=\"./index.php\">Home</a> - <a href=\"add.php\">Add new entry</a><br><br>\n";

$add_pass = CRC_SALT_0011;
if ($crc != md5("$ts$add_pass$HTTP_USER_AGENT")) {
	echo "<b>ERROR TYPE 3</b> - <a href=\"./\">Click here</a><br>\n";
	echo "For CService Admins use <b>ONLY</b>.";
	echo "</body>\n</html>\n\n";
	die;
}

$badargs = 0;

if ($user_name!="" && $channel_name!="") {
	echo "<li> You can't have a <b>username</b> AND a <b>channel name</b> set in the same NOREG entry.\n";
	$badargs = 1;
}
if ($user_name=="" && $channel_name=="") {
	echo "<li> You must have either a <b>username</b> or a <b>channel name</b> filled.\n";
	$badargs = 1;
}
if ($type=="") {
	echo "<li> Invalid <b>type</b>\n";
	$badargs = 1;
}
if ($expire_period0=="" || $expire_period0<=0) {
	echo "<li> Invalid <b>expiration</b> value.\n";
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
	if ($expire_period1==0) { $multiplier = 86400; }
	if ($expire_period1==1) { $multiplier = 3600; }
	if ($expire_period1==2) { $multiplier = 1; }
	$exp_in_sec = (int)$expire_period0*(int)$multiplier;
	if ($never_reg!=1) { $never_reg=0; }
	if ($for_review!=1) { $for_review=0; }

	$query = "insert into noreg (user_name,email,channel_name,type,never_reg,for_review,expire_time,created_ts,set_by,reason) ";
	$query = $query . "values ('$user_name','$email','$channel_name','$type','$never_reg','$for_review',(now()::abstime::int4+" . (int)$exp_in_sec . "),now()::abstime::int4,'$set_by','$reason')";

	//echo "<b>DEBUG</b>(query): $query<br><br>\n";

	pg_safe_exec($query);
	local_seclog("Added NOREG USER=[" . $user_name . "], CHANNEL=[" . $channel_name . "], EMAIL=[" . $email . "].");

	echo "The <b>NOREG entry</b> has been sucessfully added.<br>\n";
	echo "<br>\n";
	echo "<h2>DO NOT RELOAD THIS PAGE</h2> or it will be added twice.<br>\n";
	echo "<br>\n";
	echo "<a href=\"add.php\">Add a new entry</a> - <a href=\"./index.php\">Go back to search mode</a><br>\n";

}
echo "For CService Admins use <b>ONLY</b>.";

?>
</body>
</html>


