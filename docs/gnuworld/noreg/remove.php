<?
/* $Id: remove.php,v 1.5 2004/07/25 03:31:52 nighty Exp $ */
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
echo "<html><head><title>NOREG (DELETE MODE)</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");

if ($admin<$min_lvl && $nrw_lvl<1) {
	echo "Sorry, Your admin access is too low.<br><br>\n";
	echo "</body></html>\n\n";
	die;
}

$special_pass=CRC_SALT_0010;

if ($id<=0 || $id=="") {

	echo "<b>INVALID ARGUMENTS</b> - <a href=\"./index.php\">Click here</a><br>\n";

} else {

	if ($crc!=md5("$HTTP_USER_AGENT$special_pass$ts")) {
		echo "<b>NOREG</b> Editor (DELETE MODE) - <a href=\"./index.php\">Home</a> - <a href=\"add.php\">Add a new entry</a><br><br>\n";
		echo "<h2>Are you sure you want to permanently delete this NOREG entry ?<h3><br>\n";

		$res = pg_safe_exec("select * from noreg where id='" . (int)$id . "'");
		$row = pg_fetch_object($res,0);
		$un = $row->user_name;
		$cn = $row->channel_name;
		$em = $row->email;
		if ($un!="") { echo "User : $un<br>\n"; } else { echo "Channel : $cn<br>\n"; }
		echo "E-Mail : $em<br>\n";
		echo "</h3></h2>\n";

		$ts = time();
		$crc = md5("$HTTP_USER_AGENT$special_pass$ts");
		if ($HTTP_REFERER=="") { $ref = "./index.php"; } else { $ref = urlencode($HTTP_REFERER); }

		echo "<form name=confirmdelete action=remove.php method=get>\n";
		echo "<input type=hidden name=ts value=$ts>\n";
		echo "<input type=hidden name=crc value=$crc>\n";
		echo "<input type=hidden name=id value=$id>\n";
		echo "<input type=hidden name=ref value=\"$ref\">\n";
		echo "<input type=submit value=\" OK \">&nbsp;&nbsp;&nbsp;&nbsp;<input type=button value=\" CANCEL \" onclick=\"history.go(-1);\"><br><br>\n";
		echo "<i>click <b>CANCEL</b> to go back to the list.</i><br><br>\n";
	} else {

		$query = "delete from noreg where id='" . (int)$id . "'";

		pg_safe_exec($query);
		local_seclog("Removed NOREG USER=[" . $un . "], CHANNEL=[" . $cn . "], EMAIL=[" . $em . "].");

		echo "<script language=\"JavaScript1.2\">\n";
		echo "<!--\n";
		echo "\tlocation.href='" . urldecode($ref) . "';\n";
		echo "//-->\n";
		echo "</script>\n";

	}
}

echo "For CService Admins use <b>ONLY</b>.";
?>
</body>
</html>
