<?
/* $Id: admin_user_comment.php,v 1.2 2003/03/31 06:59:36 nighty Exp $ */
include("../../php_includes/cmaster.inc");
std_init();
if ($admin<600) {
	echo "You don't have access.";
	die;
}
if ($spcmode=="remove" && $admin<800) {
	echo "You don't have access.";
	die;
}
if ($spcmode=="remove") {
	$rr = pg_safe_exec("SELECT COUNT(*) AS count FROM userlog WHERE user_id='" . $uid . "' AND ts='" . $ts . "' AND event=5");
	if ($rr) {
		$oo = pg_fetch_object($rr);
		if ($oo->count==1) {
			pg_safe_exec("DELETE FROM userlog WHERE user_id='" . $uid . "' AND ts='" . $ts . "' AND event=5");
		}
	}
} else {
	log_user($uid,5,$admcmt);

}
//header("Location: users.php?id=$uid");
die;
?>
