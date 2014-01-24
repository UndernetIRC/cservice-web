<?
	require("../../../php_includes/cmaster.inc");
	std_init();
	if ($admin<800) {
		echo("Oi! What are you doing here eh?");
		exit;
	}
/* $Id: fdeauth.php,v 1.2 2002/05/20 23:58:04 nighty Exp $ */

	$ENABLE_COOKIE_TABLE=1;

	$t1 = pg_safe_exec("SELECT * FROM webcookies WHERE cookie='" . $cookie .  "' AND user_id='" . $uid . "'");
	if (pg_numrows($t1)>0) {
		$o1 = pg_fetch_object($t1,0);
		if ($admin>$o1->is_admin || $admin==1000) {
			pg_safe_exec("DELETE FROM webcookies WHERE cookie='" . $cookie .  "' AND user_id='" . $uid . "'");
		}
	}
	header("Location: view_admins.php\n\n");
	die;
?>
