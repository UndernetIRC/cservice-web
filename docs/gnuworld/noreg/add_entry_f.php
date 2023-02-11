<?
/* $Id: add_entry_f.php,v 1.7 2004/07/25 03:31:52 nighty Exp $ */
	$min_lvl=800;
	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = isset($_COOKIE["auth"]) ? std_security_chk($_COOKIE["auth"]) : 0;
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

echo "<b>FRAUD USERNAMES</b> Editor (ADD MODE) - <a href=\"./index.php\">Home</a> - <a href=\"add_f.php\">Add new entry</a><br><br>\n";
$add_pass = CRC_SALT_0011;
if ($crc != md5("$ts$add_pass$HTTP_USER_AGENT")) {
	echo "<b>ERROR TYPE 3</b> - <a href=\"./\">Click here</a><br>\n";
	echo "For CService Admins use <b>ONLY</b>.";
	echo "</body>\n</html>\n\n";
	die;
}

$badargs = 0;

if ($user_name=="") {
	echo "<li> You must have a <b>username</b> filled.\n";
	$badargs = 1;
}

$res = pg_safe_exec("select id,email,flags from users where lower(user_name)='" . strtolower($user_name) . "'");
if (pg_numrows($res)==0) {
	echo "<li> The username must be a valid CService username";
	$badargs = 1;
} else {
	$row = pg_fetch_object($res,0);
	$email = $row->email;
	$userid = $row->id;
	$flags = $row->flags;
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

	$newflags = (int)$flags|0x0008; // Fraud tag.
	$queryb = "update users set last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,last_updated_by='*** TAGGED AS FRAUD ***',flags='" . (int)$newflags . "' where id='" . (int)$userid . "'";

	$query = "insert into noreg (user_name,email,channel_name,type,never_reg,for_review,expire_time,created_ts,set_by,reason) ";
	$query = $query . "values ('$user_name','$email','',4,1,0,0,date_part('epoch', CURRENT_TIMESTAMP)::int,'$set_by','$reason')";

	//echo "<b>DEBUG</b>(query): $query<br><br>\n";

	pg_safe_exec($query);
	pg_safe_exec($queryb);
	local_seclog("Added FRAUD USERNAME USER=[" . $user_name . "], EMAIL=[" . $email . "].");

	echo "The <b>FRAUD USERNAME entry</b> has been sucessfully added.<br>\n";
	echo "<br>\n";
	echo "<h2>DO NOT RELOAD THIS PAGE</h2> or it will be added twice.<br>\n";
	echo "<br>\n";
	echo "<a href=\"add_f.php\">Add a new entry</a> - <a href=\"./index.php\">Go back to search mode</a><br>\n";

}
echo "For CService Admins use <b>ONLY</b>.";

?>
</body>
</html>


