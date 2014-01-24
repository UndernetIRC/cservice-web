<?
$min_lvl=800;
include("../../php_includes/cmaster.inc");
/* $Id: clear_review.php,v 1.8 2004/04/24 23:52:05 nighty Exp $ */
std_connect();
$user_id = std_security_chk($auth);
$admin = std_admin();

if ($admin<$min_lvl) {
	echo "You don't have access.";
	die;
}
if ($id=="" || !(isset($id)) || $retret=="" || !(isset($retret))) {
	header("Location: right.php\n\n");
	die;
}

$sret=urldecode($retret);
$tmp = explode("-",$id);
$created_ts = $tmp[0];
$channel_id = $tmp[1];
$res = pg_safe_exec("SELECT * FROM pending WHERE channel_id='$channel_id' AND created_ts='$created_ts' AND reviewed='Y'");
if (pg_numrows($res)==0) { // that id is already reviewed or non existing
	echo "That application is either non existant or not reviewed.";
	die;
} else {
	$o = pg_fetch_object($res,0);
	$status = $o->status;
	if ($admin>=800 || $status==2 || $status==3 || $status==8) { $do_it = 1; } else { $do_it = 0; }
}
if ($do_it==0) {

	echo "You can't clear applications' reviews from that 'status' (". ($status+0) .").";
	die;
	
}

$q = "UPDATE pending SET reviewed='N',reviewed_by_id=1,last_updated=now()::abstime::int4 WHERE channel_id='$channel_id' AND created_ts='$created_ts'";
pg_safe_exec($q);
review_count_rem($user_id);
log_channel($channel_id,18,"Cleared Application Review");
header("Location: view_app.php?id=$id&special_ret=" . urlencode($sret) . "\n\n");
die;
?>
