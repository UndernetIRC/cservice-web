<?php

require('../../../../php_includes/cmaster.inc');

std_init();
global $admin, $user_id;
if ($admin==0) {
	echo "Admin page only, sorry.";
	die;
}

$cTheme = get_theme_info();
std_theme_styles(1);
std_theme_body("../../");

if (empty($_POST["spec_chan"])) {
?>
<h1>Enter a channel name</h1>
Enter a channel name that you wish to view details about manager changes<br>
<form name=chan method="post">
<input type=text name="spec_chan" value="#*">
<input type=submit value="Go Baby!">
</form>
<br><br>
<a href="../../admin/index.php">Back to Reports</a>.
</body></html>
<?php
    die;
	}
if ($_POST["spec_chan"] == "*") {
    $channels = pg_safe_exec("SELECT * FROM channels,channellog WHERE channels.id=channellog.channelid AND channellog.event=12 ORDER BY channellog.ts DESC");
} else {
    $match_chan = str_replace("*","%",strtolower($_POST["spec_chan"]));
    $channels = pg_safe_exec("SELECT * FROM channels,channellog WHERE channels.id=channellog.channelid AND channellog.event=12 AND lower(channels.name) LIKE '" . $match_chan . "' ORDER BY channellog.ts DESC");
    $unf = " AND lower(channels.name) LIKE '" . $match_chan . "'";
}

// If we are here then we're working on one channel only.
if (pg_numrows($channels)==0) {
	echo ("<center><h1>No 'Manager Change' events found in channel log.</h1><br><a href=\"mgr_change.php\">retry</a></center>");
	exit;
}

$type = array(
    1 => "Misc",
    2 => "Join",
    3 => "Part",
    4 => "Oper Forced Join",
    5 => "Oper Forced Part",
    6 => "Admin Force",
    7 => "Register",
    8 => "Purge",
    9 => "Comment",
    10 => "Remove All",
    11 => "Left Idled Chan",
    12 => "Manager Change");


$query="SELECT channellog.channelid,channels.name,channellog.ts,channellog.event,channellog.message FROM channellog,channels WHERE channellog.channelid=channels.id AND channellog.event='12'" . $unf . " ORDER BY channellog.ts DESC";

$logs = pg_safe_exec($query);
echo "<TABLE border=1 WIDTH=100% cellspacing=0 cellpadding=2 BGCOLOR=#" . $cTheme->table_bgcolor . ">";
echo "<tr><td colspan=3><H2>Manager Changes Events matching <i>" . $_POST["spec_chan"] . "</i></H2>\n<a href=\"mgr_change.php\">new search</a><br></td></tr>";
echo "<tr bgcolor=#" . $cTheme->table_headcolor . "><td><font color=#" . $cTheme->table_headtextcolor . ">Time</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Channel</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Message</font></td></tr>";
if (pg_numrows($logs)>0) {
    for ($row=0;$row<pg_numrows($logs);$row++) {
        $log=pg_fetch_object($logs,$row);
        echo("<tr><td>");
        echo(cs_time($log->ts) . " [" . $log->ts . "]</td><td>");
        $bla = pg_safe_exec("SELECT name FROM channels WHERE id='" . $log->channelid . "'");
        $blo = pg_fetch_object($bla,0);
        echo $blo->name . "</td><td>\n";
        if ($admin<SHOW_IP_LEVEL) {
            echo(htmlentities(remove_ip($log->message,2)) . "</td></tr>\n");
        } else {
            echo(htmlentities($log->message) . "</td></tr>\n");
        }
    }
    echo("</table>");
} else {
    echo "There are no log messages for this channel";
    echo "\n<br><br>";
}
echo( "</form>");
?>
</body></html>
