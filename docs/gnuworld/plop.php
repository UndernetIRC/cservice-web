<?
	if ($_SERVER['REMOTE_ADDR']!="87.88.127.30") { die("plop"); }
	include_once('../../php_includes/cmaster.inc');
	$r = pg_safe_exec("SELECT id,user_name FROM users");
	while ($o = pg_fetch_object($r)) {
		if (!preg_match("/^[A-Za-z0-9]+$/",$o->user_name)) {
			echo "BOGUS FOUND :: " . $o->user_name . " (" . $o->id . ")<br>\n";
		}
	}
?>
