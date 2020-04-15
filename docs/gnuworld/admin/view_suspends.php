<?php
require('../../../php_includes/cmaster.inc');

std_init();
$cTheme = get_theme_info();

std_theme_styles(1);
std_theme_body("../");

$min_lvl=800;

if ($admin<$min_lvl) {
	echo "You dont have access. ($nrw_lvl,$show).";
	die;
}

echo "<a href=\"index.php\">Back</a><br><br>\n";
echo "<!-- $Id: view_suspends.php,v 1.5 2005/11/18 04:19:33 nighty Exp $ //-->";

unset($X_DAYS);unset($date_is_valid);
$date_is_valid=1;
$X_DAYS=2;

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

$suspends = pg_safe_exec("SELECT * FROM users,userlog WHERE userlog.ts>=" . $start_ts . " AND userlog.ts<=" . $end_ts_db . " AND users.id=userlog.user_id AND (userlog.event=1 OR userlog.event=2) ORDER BY userlog.ts DESC");

echo "<TABLE border=1 WIDTH=100% cellspacing=0 cellpadding=2 BGCOLOR=#" . $cTheme->table_bgcolor . ">";
echo "<tr><td colspan=4><H2>Suspend/Unsuspend Events ";
if ($_GET["ok"]==1) { echo "(custom)</h2> <a href=\"view_suspends.php\">View last " . $X_DAYS . " days</a>"; } else { echo "(last " . $X_DAYS . " days)</h2>"; }
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
	if (pg_numrows($suspends)==0) {
		echo "<tr><td align=center><font color=#" . $cTheme->table_headcolor . " size=+1><b>No 'Suspend'/'Unsuspend' events found in user log for this period.</font></td></tr>";
	} else {
		echo "<tr bgcolor=#" . $cTheme->table_headcolor . "><td><font color=#" . $cTheme->table_headtextcolor . ">Time</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Username</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Event</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Message</font></td></tr>";
		for ($row=0;$row<pg_numrows($suspends);$row++) {
			$log=pg_fetch_object($suspends,$row);
			echo("<tr><td>");
			echo(cs_time($log->ts) . " [" . $log->ts . "]</td><td>");
			echo $log->user_name . "</td><td>\n";
			if ($log->event==1) { echo "SUSPEND</td><td>"; }
			if ($log->event==2) { echo "UNSUSPEND</td><td>"; }
			if ($admin<SHOW_IP_LEVEL) {
				echo(htmlentities(remove_ip($log->message,2)) . "</td></tr>\n");
			} else {
				echo(htmlentities($log->message) . "</td></tr>\n");
			}
		}
	}
} else {
	echo "<tr><td align=center><font color=#" . $cTheme->table_headcolor . " size=+1><b>Choosen date is invalid or period end is before start.</b></font></td></tr>\n";
}
echo("</table>");
?>
</body></html>
