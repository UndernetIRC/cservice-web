<?

	$min_lvl=800;
	require("../../../php_includes/cmaster.inc");
	std_connect();
	$user_id = isset($_COOKIE["auth"]) ? std_security_chk($_COOKIE["auth"]) : 0;
	$admin = std_admin();
	$cTheme = get_theme_info();
	$res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
	$adm_usr = pg_fetch_object($res,0);
	$adm_user = $adm_usr->user_name;
	if ((!acl(XCHGMGR_REVIEW) && !acl(XCHGMGR_ADMIN)) && !acl(XMAILCH_REVIEW) && !acl(XMAILCH_ADMIN) && $admin<600) {
		echo "Wrong way ;)";
		die;
	}

	echo "<html><head><title>" . BOT_NAME . "@ Index</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body("../");
	echo "<h2>" . BOT_NAME . "@ Index</h2><hr width=100% noshade size=1><br>\n";

?>
<font size=+1>
<ul>
	<? if (acl(XCHGMGR_REVIEW) || acl(XCHGMGR_ADMIN) || $admin>=600) { ?>
		<li> <a href="mgrchg/">Manager Change</a><br><br>
	<? } ?>
	<? // if (acl(XMAILCH_REVIEW) || acl(XMAILCH_ADMIN) || $admin>=600) {
	?>
		<!--<li> <a href="mailchg/">E-Mail Change</a><br><br>//-->
	<? //}
	 ?>
</ul>
</font>
</body>
</html>
