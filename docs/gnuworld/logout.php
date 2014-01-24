<?
$current_page='logout.php';
require('../../php_includes/cmaster.inc');
/* $Id: logout.php,v 1.5 2004/03/07 22:04:31 nighty Exp $ */
std_init();
$ENABLE_COOKIE_TABLE = 1;
pg_safe_exec(CLEAR_COOKIES_QUERY);
pg_safe_exec("delete from webcookies where user_id=" . (int)$user_id);
if (COOKIE_DOMAIN!="") {
	SetCookie("auth","",0,"/",COOKIE_DOMAIN);
	SetCookie("totp","",0,"/",COOKIE_DOMAIN);
	SetCookie("csess","",0,"/",COOKIE_DOMAIN);
} else {
	SetCookie("auth","",0,"/");
	SetCookie("totp","",0,"/");
	SetCookie("csess","",0,"/");
}
$ENABLE_COOKIE_TABLE = 0;
if ($admin>0) { local_seclog("Logout"); }
if ($redir) {
	header("Location: " . urldecode($redir) . "\n\n");
} else {
	header("Location: main.php");
}
?>

