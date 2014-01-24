<?
/* $Id: wipeuser.php,v 1.1 2003/03/26 03:05:14 nighty Exp $ */
unset($min_lvl);$min_lvl=800;
$debug_me = 0;
require("../../php_includes/cmaster.inc");
std_init();
if ($admin<$min_lvl) { die("No way !"); }

if (check_secure_form("deleteuser!!!" . CRC_SALT_0008 . $_POST["username"])) {

	unset($del_q);
	$del_q[] = "DELETE FROM acl WHERE user_id='" . $_POST["id"] . "'";
	$del_q[] = "DELETE FROM levels WHERE user_id='" . $_POST["id"] . "'";
	$del_q[] = "DELETE FROM notes WHERE user_id='" . $_POST["id"] . "' OR from_user_id='" . $_POST["id"] . "'";
	$del_q[] = "DELETE FROM pending WHERE manager_id='" . $_POST["id"]. "' OR reviewed_by_id='" . $_POST["id"] . "'";
	$del_q[] = "DELETE FROM pending_emailchanges WHERE user_id='" . $_POST["id"] . "'";
	$del_q[] = "DELETE FROM pending_pwreset WHERE user_id='" . $_POST["id"] . "'";
	$del_q[] = "DELETE FROM supporters WHERE user_id='" . $_POST["id"] . "'";
	$del_q[] = "DELETE FROM userlog WHERE user_id='" . $_POST["id"] . "'";
	$del_q[] = "DELETE FROM users_lastseen WHERE user_id='" . $_POST["id"]. "'";
	$del_q[] = "DELETE FROM users WHERE id='" . $_POST["id"] . "'";

	if (!$debug_me) { // take the action
		pg_safe_exec( "BEGIN WORK" ); // be safe ;P
		$qFail = -1;
		for ($z=0;$z<count($del_q);$z++) {
			$lRes = pg_safe_exec($del_q[$z]);
			if (!$lRes) { $qFail = $z; }
		}
		if ($qFail>-1) {
			$query = "<b>The query (" . $qFail . ") failed !!!</b>, nothing was done/committed (pfui (tm))...\n\nPlease report the above <b>Warning:</b> you see on this page,\n*THEN* <a href=\"javascript:history.go(-1);\">you can go back</a> :P";
			pg_safe_exec( "ROLLBACK WORK" );
		} else {
			$query = "";
			pg_safe_exec( "COMMIT WORK" );
		}
	} else {
		$query = "\t<i>";
		$query .= str_replace("VALUES","\n\tVALUES",$nr_q) . "\n\t";
		for ($z=0;$z<count($del_q);$z++) {
			$query .= str_replace("VALUES","\n\tVALUES",$del_q[$z]) . "\n\t";
		}
		$query .= "</i>\n";
	}

	if ($query!="") {
		echo "<pre>" . $query . "</pre>";
	}

	if (!$debug_me) { @header("Location: users.php?id=" . $user_id . "\n\n"); }
	die;
} else {
	die("Invalid or expired form, please <a href=\"javascript:history.go(-1);\">go back</a>, and reload the page, then finally, click the button again.");
}
?>
