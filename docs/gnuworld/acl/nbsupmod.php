<?
	$min_lvl=901;

	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = std_security_chk($auth);
        $admin = std_admin();
	$cTheme = get_theme_info();
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
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
        if (!newregs_off()) {
		echo "<html><head><title>MODIFYING NUMBER OF REQUIRED SUPPORTERS</title>";
		std_theme_styles();
		echo "</head>\n";
        	std_theme_body("../");
		echo "<b>MODIFYING NUMBER OF REQUIRED SUPPORTERS</b><br><br>\n";
        	echo "Sorry, you must <b>LOCK</b> the new registrations BEFORE applying any change here.<br><br>";
        	die("<a href=\"javascript:history.go(-1);\">Go back</a>");
        }

        if (  ( $crc == md5( CRC_SALT_0019 . $ts . $HTTP_USER_AGENT ) && $mode=="set" ) ||
             ( $crc == md5 ( CRC_SALT_0020 . $ts . $HTTP_USER_AGENT ) && $mode=="reset" ) ) {

             // update asked...
             	$nbsup = $nbsup+0;

             	if ($mode=="reset") { $nbsup = DEFAULT_REQUIRED_SUPPORTERS; }

		pg_safe_exec("UPDATE variables SET contents='" . $nbsup . "',last_updated=now()::abstime::int4 WHERE var_name='REQUIRED_SUPPORTERS'");

		header("Location: index.php\n\n");
		die;

        }

echo "<html><head><title>MODIFYING NUMBER OF REQUIRED SUPPORTERS</title>";
		std_theme_styles();
		echo "</head>\n";
        	std_theme_body("../");

echo "<b>MODIFYING NUMBER OF REQUIRED SUPPORTERS</b><br><br>\n";
if (REQUIRED_SUPPORTERS>0) {
	echo "Global DB value : <font size=+1><b>" . REQUIRED_SUPPORTERS . "</b></font>.<br>";
} else {
	echo "Global DB value : <font size=+1><b>0</b> (Instant Registration)</font>.<br>";
}
if (DEFAULT_REQUIRED_SUPPORTERS>0) {
	echo "Default local <b>config.inc</b>'s value : <font size=+1><b>" . DEFAULT_REQUIRED_SUPPORTERS . "</b></font>.<br><br>";
} else {
	echo "Default local <b>config.inc</b>'s value : <font size=+1><b>0</b> (Instant Registration)</font>.<br><br>";
}
echo "<form name=newsupnum method=get>";
echo "<font size=+0>";
echo "Set required # of supporters to <select name=nbsup>";
$tmpvar = "ahah" . REQUIRED_SUPPORTERS;
$$tmpvar = "selected ";
echo "<option " . $ahah0 . "value=0>none (Instant Registration)</option>";
echo "<option " . $ahah1 . "value=1>1 supporter</option>";
echo "<option " . $ahah2 . "value=2>2 supporters</option>";
echo "<option " . $ahah3 . "value=3>3 supporters</option>";
echo "<option " . $ahah4 . "value=4>4 supporters</option>";
echo "<option " . $ahah5 . "value=5>5 supporters</option>";
echo "<option " . $ahah6 . "value=6>6 supporters</option>";
echo "<option " . $ahah7 . "value=7>7 supporters</option>";
echo "<option " . $ahah8 . "value=8>8 supporters</option>";
echo "<option " . $ahah9 . "value=9>9 supporters</option>";
echo "<option " . $ahah10 . "value=10>10 supporters</option>";
echo "</select>&nbsp;&nbsp;<input type=submit value=Go!>\n";
echo "</font>";
$zets = time();
$zecrc = md5( CRC_SALT_0019 . $zets . $HTTP_USER_AGENT );
echo "<input type=hidden name=ts value=" . $zets . ">";
echo "<input type=hidden name=crc value=" . $zecrc . ">";
echo "<input type=hidden name=mode value=set>";
echo "</form>";
echo "<br><br>";

if (REQUIRED_SUPPORTERS != DEFAULT_REQUIRED_SUPPORTERS) {
	echo "<form name=newsupnum method=get>";
	echo "<font size=+0>";
	echo "<input type=submit value=\"Reset to local config.inc's default\">\n";
	echo "</font>";
	$zets = time();
	$zecrc = md5( CRC_SALT_0020 . $zets . $HTTP_USER_AGENT );
	echo "<input type=hidden name=ts value=" . $zets . ">";
	echo "<input type=hidden name=crc value=" . $zecrc . ">";
	echo "<input type=hidden name=mode value=reset>";
	echo "</form>";
}

?>
</body>
</html>
