<?
/* $Id: r_lastseen.php,v 1.1 2005/03/07 04:48:03 nighty Exp $ */
$min_lvl=800;
require("../../php_includes/cmaster.inc");
std_init();
if ($admin<800) { die("Oi! What are you doing here ?"); }
$cTheme = get_theme_info();
if ($_GET["crc"]==md5( CRC_SALT_0013 . $_GET["id"] . $_GET["ts"] )) {
	$r = pg_safe_exec("SELECT * FROM users_lastseen WHERE user_id=" . (int)$_GET["id"]);
	if ($o = pg_fetch_object($r)) {
		// do nothing, the record IS here ( heh ?! )
	} else {
		// insert a new record
		pg_safe_exec("INSERT INTO users_lastseen (user_id, last_seen, last_updated, last_hostmask) VALUES ( " . (int)$_GET["id"] . ", date_part('epoch', CURRENT_TIMESTAMP)::int, date_part('epoch', CURRENT_TIMESTAMP)::int, '')");
	}
	header("Location: users.php?id=" . $_GET["id"] . "\n\n");
	die;
} else {
	die("Err!");
}
?>
