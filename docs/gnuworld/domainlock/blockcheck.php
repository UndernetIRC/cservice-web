<?
	/* $Id: blockcheck.php,v 1.2 2004/07/25 03:31:52 nighty Exp $ */
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
echo "<html><head><title>Domain/User Lock (CHECK MODE)</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");
echo "<b>Domain/User Lock</b> Checker - <a href=\"./index.php\">Home</a><br><br>\n";

if (preg_match("/@/",$_GET["im"])) { // considere it a full email
	if (preg_match("/^[A-Za-z0-9_+-.]+@[A-Za-z0-9.-]+\.[A-Za-z][A-Za-z]+$/", $_GET["im"])) {
		if (is_email_locked(-1,$_GET["im"])) {
			echo "<h3>The e-mail address '" . $_GET["im"] . "' is LOCKED by the '" . $LOCK_MATCH . "' entry.</h3>";
		} else {
			echo "<h3>The e-mail address '" . $_GET["im"] . "' is NOT LOCKED by the DomainLock system.</h3>";
		}
	} else {
		echo "<h3>Invalid e-mail address, try again !</h3>\n";
	}
} else { // considere it a domain name
	if (preg_match("/^[A-Za-z0-9.-]+\.[A-Za-z][A-Za-z]+$/", $_GET["im"])) {
		if (is_email_locked(-1,"!!!@" . $_GET["im"])) {
			echo "<h3>The domain name '" . $_GET["im"] . "' is LOCKED by the '" . $LOCK_MATCH . "' entry.</h3>";
		} else {
			echo "<h3>The domain name '" . $_GET["im"] . "' is NOT LOCKED by the DomainLock system.</h3>";
		}
	} else {
		echo "<h3>Invalid domain name, try again !</h3>\n";
	}
}

echo "<br><br><a href=\"index.php\">go back !</a><br>\n";

?>
</body>
</html>


