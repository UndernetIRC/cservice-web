<?php
require('../../../php_includes/cmaster.inc');

std_init();
$cTheme = get_theme_info();

std_theme_styles(1);
std_theme_body("../");

$min_lvl=800;

if ($admin<$min_lvl) {
	echo "You dont have access.";
	die;
}

echo "<a href=\"index.php\">Back</a><br><br>\n";
echo '<!-- $Id: view_adminlog.php,v 1.5 2005/11/26 13:13:07 nighty Exp $ //-->';

unset($X_CMD);unset($date_is_valid);
$date_is_valid=1;
$X_DAYS = 5;
$X_CMD=30;

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

$q = "SELECT users.id,users.user_name,adminlog.timestamp AS ts,adminlog.* FROM users,adminlog ";
$fc = 1;
if ($_GET['ok']==1) {
	if ($_GET['timer']==2) {
		if ($fc) { $q .= "WHERE "; } else { $q .= "AND "; }
		$fc = 0;
		$q .= "adminlog.timestamp>=" . $start_ts . " AND adminlog.timestamp<=" . $end_ts_db;
	}
	if ($_GET['cmdr']!='*') {
		if ($fc) { $q .= "WHERE "; } else { $q .= "AND "; }
		$fc = 0;
		$q .= "adminlog.cmd='" . $_GET["cmdr"] . "'";
	}
	if ($_GET['userr']>0) {
		if ($fc) { $q .= "WHERE "; } else { $q .= "AND "; }
		$fc = 0;
		$q .= "adminlog.user_id='" . $_GET["userr"] . "'";
	}
	if ($_GET['argpattern']!='' && $_GET['argpattern']!='*') {
		if ($fc) { $q .= "WHERE "; } else { $q .= "AND "; }
		$fc = 0;
		$q .= "lower(adminlog.args) LIKE '" . str_replace('?','_',str_replace('*','%',strtolower($_GET["argpattern"]))) . "'";
	}
	if ($_GET['issuepattern']!='' && $_GET['issuepattern']!='*') {
		if ($fc) { $q .= "WHERE "; } else { $q .= "AND "; }
		$fc = 0;
		$q .= "lower(adminlog.issue_by) LIKE '" . str_replace('?','_',str_replace('*','%',strtolower($_GET["issuepattern"]))) . "'";
	}
}
if ($fc) { $q .= "WHERE "; } else { $q .= "AND "; }
$q .= "users.id=adminlog.user_id ";
$q .= "ORDER BY adminlog.timestamp DESC ";
if ($_GET['mrec']>0) {
	$q .= "LIMIT " . (int)$_GET['mrec'];
} else {
	$q .= "LIMIT " . (int)$X_CMD;
}
$adminlogs = pg_safe_exec($q);

echo "<TABLE border=1 WIDTH=100% cellspacing=0 cellpadding=2 BGCOLOR=#" . $cTheme->table_bgcolor . ">";
echo "<tr><td colspan=5><H2>AdminLog ";
if ($_GET["ok"]==1) { echo "(custom)</h2> <a href=\"view_adminlog.php\">View last " . $X_CMD . " commands</a>"; } else { echo "(last " . $X_CMD . " commands)</h2>"; }
echo "<br>";
echo "<form name=evtdate method=get>\n";

echo "<input type=radio name=timer value=1";
if ($_GET['ok']==0 || $_GET['timer']==1) { echo " checked"; }
echo "> Anytime<br>\n";
echo "<input type=radio name=timer value=2";
if ($_GET['ok']==1 && $_GET['timer']==2) { echo " checked"; }
echo "> Showing between ";
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
echo "<br>\n";
echo "Show <select name=cmdr>";
echo "<option value=\"*\">ALL commands</option>\n";
/*
$rr = pg_safe_exec("SELECT DISTINCT(cmd) FROM adminlog ORDER BY cmd");
while ($oo = pg_fetch_object($rr)) {
	echo "<option ";
	if ($_GET['ok']==1 && $_GET['cmdr']==$oo->cmd) { echo "selected "; }
	echo "value=\"" . $oo->cmd . "\">" . $oo->cmd . "</option>\n";
}
*/
echo "</select> entries.<br>\n";
echo "<br>\n";
echo "Restrict on username <select name=userr>";
echo "<option value=\"0\">- none -</option>\n";
$rr = pg_safe_exec("SELECT DISTINCT(user_id) FROM adminlog");
unset($idz); unset($unamez); $idz = Array(); $unamez = Array(); $cnt = 0;
while ($oo = pg_fetch_object($rr)) {
	$ur = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $oo->user_id . "'");
	if ($or = @pg_fetch_object($ur)) {
		$unamez[] = $or->user_name;
	} else {
		$unamez[] = "DEL #" . $oo->user_id;
	}
	$idz[] = $oo->user_id;
	$cnt++;
}
for ($x=0;$x<$cnt;$x++) {
	echo "<option ";
	if ($_GET['ok']==1 && $_GET['userr']==$idz[$x]) { echo "selected "; }
	echo "value=\"" . $idz[$x] . "\">" . htmlentities($unamez[$x]) . "</option>\n";
}
echo "</select><br>\n";
echo "<br>\n";
echo "Arguments pattern match (? and * wilds allowed) : <input type=text name=argpattern size=50 ";
if ($_GET['ok']==1) { echo "value=\"" . post2input($_GET['argpattern']) . "\" "; }
echo "maxlength=255><br>\n";
echo "<br>\n";
echo "Issued by pattern match (? and * wilds allowed) : <input type=text name=issuepattern size=50 ";
if ($_GET['ok']==1) { echo "value=\"" . post2input($_GET['issuepattern']) . "\" "; }
echo "maxlength=255><br>\n";
echo "<br>\n";
echo "Show max <select name=mrec>";
echo "<option value=100" . ((int)$_GET['mrec']==100?" selected":"") . ">100 records</option>\n";
echo "<option value=200" . ((int)$_GET['mrec']==200?" selected":"") . ">200 records</option>\n";
echo "<option value=500" . ((int)$_GET['mrec']==500?" selected":"") . ">500 records</option>\n";
echo "</select>&nbsp;&nbsp;";
echo "<input type=submit value=\"Search !\">";
echo "<input type=hidden name=ok value=1>";
echo "</form>\n";
echo "</td></tr>";
if ($date_is_valid) {
	if (pg_numrows($adminlogs)==0) {
		echo "<tr><td colspan=5 align=center><font color=#" . $cTheme->table_headcolor . " size=+1><b>No 'AdminLog' entry found.</b></font></td></tr>";
	} else {
		echo "<tr><td colspan=5 align=center><font color=#" . $cTheme->table_headcolor . " size=+1><b>Showing " . pg_numrows($adminlogs) . " records.</b></font></td></tr>";
		echo "<tr bgcolor=#" . $cTheme->table_headcolor . ">";
		echo "<td><font color=#" . $cTheme->table_headtextcolor . ">Time</font></td>";
		echo "<td><font color=#" . $cTheme->table_headtextcolor . ">Username</font></td>";
		echo "<td><font color=#" . $cTheme->table_headtextcolor . ">Command</font></td>";
		echo "<td><font color=#" . $cTheme->table_headtextcolor . ">Arguments</font></td>";
		echo "<td><font color=#" . $cTheme->table_headtextcolor . ">Issued by</font></td>";
		echo "</tr>";
		for ($row=0;$row<pg_numrows($adminlogs);$row++) {
			$log=pg_fetch_object($adminlogs,$row);
			echo("<tr><td>");
			echo(cs_time($log->ts) . " [" . $log->ts . "]</td><td>");
			echo htmlentities($log->user_name) . "</td><td>\n";
			echo htmlentities($log->cmd) . "</td><td>\n";
			echo htmlentities($log->args) . "</td><td>\n";
			if ($admin<SHOW_IP_LEVEL) {
				echo(htmlentities(remove_ip($log->issue_by)) . "</td></tr>\n");
			} else {
				echo(htmlentities($log->issue_by) . "</td></tr>\n");
			}
		}
	}
} else {
	echo "<tr><td align=center><font color=#" . $cTheme->table_headcolor . " size=+1><b>Choosen date is invalid or period end is before start.</b></font></td></tr>\n";
}
echo("</table>");
?>
</body></html>
