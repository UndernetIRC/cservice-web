<?
$min_lvl=800;
require('../../php_includes/cmaster.inc');

unset($page_name);unset($udbname);unset($cdbname);
$page_name = "viewlogs_archive.php";
$udbname = "userlogmsg";
$cdbname = "logmsg";

/* NOTE: all chars are valid excepted \x20 \x07 \x00.
$validnumbers="0123456789";
$validletters="-abcdefghijklmnopqrstuvwxyz_.#" . $validnumbers;
*/

std_init();
if ($admin==0 && !acl(XLOGGING_VIEW)) {
	echo "Admin page only, sorry.";
	die;
}
$cTheme = get_theme_info();

$nrw_lvl = 0;
if (acl(XWEBAXS_2)) { $nrw_lvl = 1; }
if (acl(XWEBAXS_3)) { $nrw_lvl = 2; }

if ($show=="purged") {
	$cid = "";
	$uid = "";
	$cname = "";
	$uname = "";
}
// Find the channel.
// -----------------
// There are two ways to do this, one is by "id=" which is used internally
// to the site.  It's slightly faster.
// The other way is by name, designed to be used by urls directed from
// irc etc.

std_theme_styles(1);
std_theme_body();

$ENABLE_COOKIE_TABLE=1;

if ($cname!="") {
	$rtype="c";
	if (ord($cname)!=0x23)
		$cname="#" . $cname;
  	$cname=strtolower($cname);
	$channels = pg_safe_exec("SELECT * FROM $cdbname WHERE lower(name)='$cname'");
} else if ($uname!="") {
	$rtype="u";
  	$uname=strtolower($uname);
	$users = pg_safe_exec("SELECT * FROM $udbname WHERE lower(name)='$uname'");

} else if ($show=="purged") {
	if ($spec_chan=="") {
?>
<h1>Enter old purged channel name</h1>
Enter an old purged channel name that you wish to view details about<br>
<form name=chan method="get">
<input type=hidden name=show value=purged>
<input type=text name="spec_chan" value="#*">
<input type=submit value="Go Baby!">
</form>
<br><br>
<a href="<?=$page_name?>">Back to 'view old logs' main menu</a>.
</body></html>
<?
		die;
	}
	if ($spec_chan=="*") {
		$rtype="c";
		$channels = pg_safe_exec("SELECT * FROM $cdbname WHERE event=8");
	} else {
		$rtype="c";
		$match_chan = str_replace("*","%",strtolower($spec_chan));
		$channels = pg_safe_exec("SELECT * FROM $cdbname WHERE event=8 AND lower(name) LIKE '$match_chan'");
		$unf = " AND lower(name) LIKE '$match_chan'";
	}
} else {
?>
<h1>Enter channel name</h1>
Enter a channel name that you wish to view old channel logs<br>
<form name=chan method="get">
<input type=text name="cname">
<input type=submit value="Go Baby!">
</form>
<br><i>or</i><br>
<h1>Enter username name</h1>
Enter a user name that you wish to view old user logs<br>
<form name=user method="get">
<input type=text name="uname">
<input type=submit value="Go Baby!">
</form>
<script language="JavaScript1.2">
<!--
	document.forms[0].reset();
	document.forms[1].reset();
//-->
</script>
<br><br><h2>
<a href="<?=$page_name?>?show=purged">View old Purged Channels</a>
</h2>
</body></html>
<?
	exit;
}

// If we are here then we're working on one channel only.

if ($rtype=="c" && (pg_numrows($channels)==0)) {
	if ($show=="purged") {
		echo ("<center><h1>No 'Purge' events found in channel log.</h1><br><a href=\"$page_name?show=purged\">retry</a></center>");
	} else {
		echo("<center><h1>That channel does not exist<br>or no old channel logs were found.</h1><br><a href=\"$page_name\">retry</a></center>");
	}
	exit;
}
if ($rtype=="u" && (pg_numrows($users)==0)) {
	echo("<center><h1>That username does not exist<br>or no old user logs were found.</h1><br><a href=\"$page_name\">retry</a></center>");
	exit;
}




if ($rtype=="c") { $channel = pg_fetch_object($channels,0); }
if ($rtype=="u") { $user = pg_fetch_object($users,0); }

if ($rtype=="c") {
  $type = $channel_events;
}
if ($rtype=="u") {
	$type = $user_events;
}
	if ($rtype=="c") { $chnname = $channel->name; }
	if ($rtype=="u") { $usrname = $user->name; }
	if ($rtype=="c") { if ($show=="purged") { $query="SELECT * FROM $cdbname WHERE event='8'" . $unf . " ORDER BY ts DESC"; } else { $query="SELECT * FROM $cdbname WHERE name='$chnname' ORDER BY ts DESC"; } }
	if ($rtype=="u") { $query="SELECT * FROM $udbname WHERE name='$usrname' ORDER BY ts DESC"; }
//	echo "Q: $query<br>";
	$logs = pg_safe_exec($query);
	echo "<TABLE border=1 WIDTH=100% cellspacing=0 cellpadding=2 BORDER=0 BGCOLOR=#" . $cTheme->table_bgcolor . ">";
	if ($rtype=="c") { if ($show=="purged") { echo "<tr><td colspan=3><H2>Old Purge Events matching <i>$spec_chan</i></H2></td></tr>"; } else { echo "<tr><td colspan=3><H2>Old Log messages for $chnname</H2></td></tr>"; } }
	if ($rtype=="u") { echo "<tr><td colspan=3><H2>Old Log messages for $usrname</H2></td></tr>"; }
	echo "<tr><td colspan=3><a href=$page_name>New search</a></td></tr>\n";
	if ($cTheme->table_headimage!="") { $thi = " background=\"themes/data/" . $cTheme->sub_dir . "/" . $cTheme->table_headimage . "\""; } else { $thi = ""; }
	if ($show=="purged") { echo "<tr" . $thi . " bgcolor=#" . $cTheme->table_headcolor . "><td><font color=#" . $cTheme->table_headtextcolor . ">Time</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Channel</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Message</font></td></tr>"; } else { echo "<tr" . $thi . " bgcolor=#" . $cTheme->table_headcolor . "><td><font color=#" . $cTheme->table_headtextcolor . ">Time</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Event</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Message</font></td></tr>"; }
	if (pg_numrows($logs)!=0) {
		for ($row=0;$row<pg_numrows($logs);$row++) {
			$log=pg_fetch_object($logs,$row);
			echo("<tr><td>");
			echo(cs_time($log->ts) . " [" . $log->ts . "]</td><td>");
			if ($show=="purged") {
				echo $log->name . "</td><td>\n";
			} else {
				if (($log->event>count($user_events) || $log->event==0) && $rtype=="u") {echo "(old)</td><td>"; } else { echo($type[$log->event] . "</td><td>"); }
			}
			if (($log->event==1) && (preg_match(") NON-SUPPORT from ",$log->message))) {
				echo $log->message . "</td></tr>\n";
			} else {
				if ($admin<SHOW_IP_LEVEL) {
					$ENABLE_COOKIE_TABLE = 0;
					echo(htmlentities(remove_ip($log->message,2)) . "</td></tr>\n");
					$ENABLE_COOKIE_TABLE = 1;
				} else {
					echo(htmlentities($log->message) . "</td></tr>\n");
				}
			}
	    }
	echo("</table>");
	} else {
		echo "There are no log messages for this ";
		if ($rtype=="c") { echo "channel"; }
		if ($rtype=="u") { echo "user"; }
		echo "\n<br><br>";
	}


echo( "</form>");
$ENABLE_COOKIE_TABLE=0;
?>
</body></html>
