<?
	require("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();
	std_theme_styles(1);
	std_theme_body("../");
	if ($admin<750) {
		echo("Oi! What are you doing here eh?");
		exit;
	}

echo "<a href=\"index.php\">Back</a><br><br>\n";
echo "<!-- $Id: view_judgereg.php,v 1.2 2004/05/27 16:09:45 nighty Exp $ //-->";

unset($X_DAYS);unset($date_is_valid);
$date_is_valid=1;
$X_DAYS=5;
if ($_GET["ok"]==1) {
	$ts_start_noon = mktime(12,0,0,$_GET["m"],$_GET["d"],$_GET["y"]);
	$ts_end_noon = mktime(12,0,0,$_GET["m2"],$_GET["d2"],$_GET["y2"]);
	if ($ts_start_noon > $ts_end_noon) { $date_is_valid = 0; }
	if (!checkdate($_GET["m"],$_GET["d"],$_GET["y"])) { $date_is_valid = 0; }
	if (!checkdate($_GET["m2"],$_GET["d2"],$_GET["y2"])) { $date_is_valid = 0; }
	$start_ts = mktime(0,0,0,$_GET["m"],$_GET["d"],$_GET["y"]);
	$end_ts = mktime(23,59,59,$_GET["m2"],$_GET["d2"],$_GET["y2"]);
	$end_ts_db = $end_ts;
} else {
	$today_noon = mktime(12,0,0,date("m"),date("d"),date("Y"));
	$x_days_ago = ($today_noon - ( 86400 * $X_DAYS ));
	$x_days_ago_start = (mktime(0,0,1,date("m",$x_days_ago),date("d",$x_days_ago),date("Y",$x_days_ago)) - 1);
	$start_ts = $x_days_ago_start;
	$end_ts = time();
	$end_ts_db = "date_part('epoch', CURRENT_TIMESTAMP)::int";
}
$res = pg_safe_exec("SELECT channels.id AS chanid,channels.registered_ts AS ts,channels.name AS name,users.id AS uid,users.user_name AS uname,users_lastseen.last_hostmask AS hostmask,users_lastseen.last_seen AS lastseen_ts FROM levels,channels,users,users_lastseen WHERE levels.access=500 AND levels.added_by='The Judge' AND channels.id=levels.channel_id AND channels.registered_ts>=" . $start_ts . " AND channels.registered_ts<=" . $end_ts . " AND users.id=levels.user_id AND users_lastseen.user_id=levels.user_id");
$count = pg_numrows($res);
echo "<TABLE border=1 WIDTH=100% cellspacing=0 cellpadding=2 BGCOLOR=#" . $cTheme->table_bgcolor . ">";
echo "<tr><td colspan=4><H2>Channels Registered by The Judge (Automatic Registration Procedure) ";
if ($_GET["ok"]==1) { echo "(custom)</h2> <a href=\"view_judgereg.php\">View last " . $X_DAYS . " days</a>"; } else { echo "(last " . $X_DAYS . " days)</h2>"; }
echo "<br>";
echo "<form name=evtdate method=get>\n";
echo "Showing between ";
echo "<b><select name=m>";

for ($x=1;$x<=12;$x++) {
	echo "<option ";
	if (date("m",$start_ts)==$x) { echo "selected "; }
	echo "value=" . $x . ">" . date("M",mktime(12,0,0,$x,15,date("Y"))) . "</option>\n";
}
echo "</select>,<select name=d>";
for ($x=1;$x<=31;$x++) {
	echo "<option ";
	if (date("d",$start_ts)==$x) { echo "selected "; }
echo "value=" . $x . ">" . $x . "</option>\n";
}
echo "</select> <select name=y>";
for ($x=2000;$x<=date("Y");$x++) {
	echo "<option ";
	if (date("Y",$start_ts)==$x) { echo "selected "; }
	echo "value=" . $x . ">" . $x . "</option>\n";
}
echo "</select> 00:00:00 (" . date("O",$start_ts) . ")</b> and <b><select name=m2>";
for ($x=1;$x<=12;$x++) {
	echo "<option ";
	if (date("m",$end_ts)==$x) { echo "selected "; }
	echo "value=" . $x . ">" . date("M",mktime(12,0,0,$x,15,date("Y"))) . "</option>\n";
}
echo "</select>, <select name=d2>";
for ($x=1;$x<=31;$x++) {
	echo "<option ";
	if (date("d",$end_ts)==$x) { echo "selected "; }
	echo "value=" . $x . ">" . $x . "</option>\n";
}
echo "</select> <select name=y2>";
for ($x=2000;$x<=date("Y");$x++) {
	echo "<option ";
	if (date("Y",$end_ts)==$x) { echo "selected "; }
	echo "value=" . $x . ">" . $x . "</option>\n";
}
echo "</select> ";
echo date("H:i:s",$end_ts);
echo " (" . date("O",$end_ts) . ")</b>.<br>";
echo "<input type=submit value=\"Refresh display with date above\">";
echo "<input type=hidden name=ok value=1>";
echo "</form>\n";
echo "</td></tr>";
if ($date_is_valid) {
	if ($count==0) {
		echo "<tr><td align=center><font color=#" . $cTheme->table_headcolor . " size=+1><b>There is no channel automatically registered for this period.</b></font></td></tr>";
	} else {
		echo "<tr bgcolor=#" . $cTheme->table_headcolor . "><td><font color=#" . $cTheme->table_headtextcolor . ">Time</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Channel</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Manager Information</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Idle (days)</font></td></tr>";
		for ($row=0;$row<$count;$row++) {
			$log=pg_fetch_object($res,$row);
			echo "<tr><td>";
			echo cs_time($log->ts) . " [" . $log->ts . "]</td><td>";
			echo "<a href=\"../channels.php?id=" . $log->chanid . "\" target=_blank>" . $log->name . "</a></td><td>";
			echo "<a href=\"../users.php?id=" . $log->uid . "\" target=_blank><b>" . $log->uname . "</b></a> (" . $log->hostmask . ")</td><td>\n";
			echo (int)((time()-$log->lastseen_ts)/86400) . "\n";
			echo "</td></tr>\n";
	        }
	}
} else {
	echo "<tr><td align=center><font color=#" . $cTheme->table_headcolor . " size=+1><b>Choosen date is invalid or period end is before start.</b></font></td></tr>\n";
}
echo("</table>");
?>
</body>
</html>
