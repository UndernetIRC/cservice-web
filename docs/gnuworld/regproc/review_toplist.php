<?
	require("../../../php_includes/cmaster.inc");
	/* $Id: review_toplist.php,v 1.5 2003/03/14 04:46:10 nighty Exp $ */
	std_init();
	$cTheme = get_theme_info();
	echo "<html><head><title>.</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body("../");

	if (!acl(XWEBAXS_3)) {
		echo("Oi! What are you doing here eh?");
		exit;
	}
?>
<h1>Admin Review Top List</h1>
<hr>

<?

$q = "select statistics.stats_value_int,statistics.last_updated,users.user_name,users.id from statistics,users where statistics.stats_type=1 AND statistics.users_id=users.id order by statistics.stats_value_int DESC";
$r = pg_safe_exec($q);
$curr = "";
$prec = "";

if (pg_numrows($r)==0) { // no admins have reviewed applications
	echo "<h2>No admin has reviewed any application ever</h2>\n";
} else {
	echo "<table border=1 cellspacing=2 cellpadding=3 bgcolor=#" . $cTheme->table_bgcolor . ">\n";
	echo "<tr bgcolor=#" . $cTheme->table_sepcolor . ">\n";
	echo "<td><font color=#" . $cTheme->table_septextcolor . "><b>Admin Username</b></font></td><td><font color=#" . $cTheme->table_septextcolor . "><b># of applications reviewed</b></font></td><td><font color=#" . $cTheme->table_septextcolor . "><b>Last updated</b></font></td></tr>\n";

	// display it
	while($ro = pg_fetch_object($r)) {
		echo "<tr>\n";
		echo "<td><a href=\"../users.php?id=" . $ro->id . "\">" . $ro->user_name . "</a></td>";
		echo "<td><b>" . ($ro->stats_value_int+0) . "</b></td><td>" . cs_time($ro->last_updated) . "</td></tr>\n";
	}

	echo "</table>\n";
}
?>
<br>
<? if (preg_match("stats.php",$HTTP_REFERER)) { ?>
<a href="stats.php">Back to Newregs Status</a><br>
<? } else { ?>
<a href="../admin/index.php">Back to reports</a><br>
<? } ?>
</body></html>
