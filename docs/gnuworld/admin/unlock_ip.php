<?
/* <!-- $Id: unlock_ip.php,v 1.3 2002/05/20 23:58:04 nighty Exp $ //--> */
	require("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();
	if ($admin<900) {
		std_theme_styles(1); std_theme_body("../");
		echo("Oi! What are you doing here eh?");
		echo "</body></html>\n";
		exit;
	}
	if ($o<1 || $o>2 || !preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/",$IPNUM)) {
		std_theme_styles(1); std_theme_body("../");
		echo "Wrong params. Go back.";
		echo "</body></html>\n";
		die;
	}
	$ENABLE_COOKIE_TABLE=1;
	pg_safe_exec("DELETE FROM newu_ipcheck WHERE ip='" . $IPNUM . "'");
	$ENABLE_COOKIE_TABLE=0;
	header("Location: view_newu_ips.php?o=" . $o);
	die;
?>
