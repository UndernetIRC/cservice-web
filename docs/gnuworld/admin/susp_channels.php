<?
	require("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();
	std_theme_styles(1); std_theme_body("../");
	if ($admin<750) {
		echo("Oi! What are you doing here eh?");
		exit;
	}

echo "<a href=\"index.php\">Back</a><br><br>\n";
echo "<!-- $Id: susp_channels.php,v 1.2 2005/03/30 16:29:00 nighty Exp $ //-->";

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
	$end_ts_db = "now()::abstime::int4";
}

$res = @pg_safe_exec("SELECT * FROM channels WHERE last_updated>=" . $start_ts . " AND last_updated<=" . $end_ts_db . " AND (flags::int4 & 16)=16 ORDER BY last_updated DESC");
if (!$res) {
	echo "<h3>Server's PostgreSQL does'nt support bitwise operation, please upgrade to 7.2.x+ (not 7.3 yet for gnuworld)</h3>";
	echo "</body></html>\n\n";
	die;
}
echo "<TABLE border=1 WIDTH=100% cellspacing=0 cellpadding=2 BGCOLOR=#" . $cTheme->table_bgcolor . ">";
echo "<tr><td colspan=4><H2>Globally suspended channels ";
if ($_GET["ok"]==1) { echo "(custom)</h2> <a href=\"susp_channels.php\">View last " . $X_DAYS . " days</a>"; } else { echo "(last " . $X_DAYS . " days)</h2>"; }
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
	if (pg_numrows($res)==0) {
		echo "<tr><td align=center><font color=#" . $cTheme->table_headcolor . " size=+1><b>No suspended channels found for this period.</b></font></td></tr>";
	} else {
		if (pg_numrows($res)>1) { $s_addy = "s"; } else { $s_addy = ""; }
		echo "<tr><td>";
		echo "<table border=1 cellspacing=1 cellpadding=2 bgcolor=#" . $cTheme->table_bgcolor . ">";
		echo "<tr bgcolor=#" . $cTheme->table_headcolor . ">";
		echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Username</b></font></td>";
		echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Since</b></font></td>";
		echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Additionnal Info</b></font></td>";
		echo "</tr>";
		for ($x=0;$x<pg_numrows($res);$x++) {
			$row = pg_fetch_object($res,$x);
			echo "<tr><td>";
			echo "<a href=\"../channels.php?id=" . $row->id . "\">" . $row->name . "</a></td>";
			echo "<td>" . drake_duration(time()-$row->last_updated) . " ago</td><td>n/a</td></tr>\n";
		}
		echo "</table>";
		echo "</td></tr>";
	}
} else {
	echo "<tr><td align=center><font color=#" . $cTheme->table_headcolor . " size=+1><b>Choosen date is invalid or period end is before start.</b></font></td></tr>\n";
}
echo("</table>");
?>
</body>
</html>
