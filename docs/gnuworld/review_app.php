<?
include("../../php_includes/cmaster.inc");
/* $Id: review_app.php,v 1.6 2003/03/14 04:46:09 nighty Exp $ */
std_connect();
$user_id = isset($_COOKIE["auth"]) ? std_security_chk($_COOKIE["auth"]) : 0;
$admin = std_admin();

if (!acl(XWEBAXS_2) && !acl(XWEBAXS_3)) {
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
$do_it = 0;
$res = pg_safe_exec("SELECT * FROM pending WHERE channel_id='$channel_id' AND created_ts='$created_ts' AND (reviewed='' OR reviewed IS NULL OR reviewed='N')");
if (pg_numrows($res)==0) { // that id is already reviewed or non existing
	echo "That application is either non existant or already reviewed.";
	die;
} else {
	$o = pg_fetch_object($res,0);
	$status = $o->status;
	if ($status==2 || $status==3 || $status==8) { $do_it = 1; } else { $do_it = 0; }
}
if ($do_it==0) {

	echo "You can't review applications from that 'status' (". ($status+0) .").";
	die;

}
$q = "UPDATE pending SET reviewed='Y',reviewed_by_id='$user_id',last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int WHERE channel_id='$channel_id' AND created_ts='$created_ts'";
pg_safe_exec($q);
review_count_add($user_id);
log_channel($channel_id,17,"Reviewed Application");
//header("Location: view_app.php?id=$id&special_ret=" . urlencode($sret) . "\n\n");

if (preg_match("/\//",$sret)) {
	$fslpos = strpos($sret,"/");
	$ssret = substr($sret,$fslpos+2);
	$sslpos = strpos($ssret,"/");
	$zret = str_replace(" ","+",str_replace("#","%23",substr($ssret,$sslpos)));
} else {
	$zret = $sret;
}

header("Location: " . $zret . "\n\n");
die;
?>
