<?

require('../../../../php_includes/cmaster.inc');

/* $Id: mgr_change.php,v 1.4 2005/11/18 04:19:33 nighty Exp $ */

/* NOTE: all chars are valid excepted \x20 \x07 \x00.
$validnumbers="0123456789";
$validletters="-abcdefghijklmnopqrstuvwxyz_.#" . $validnumbers;
*/

$show = purged;

std_init();
if ($admin==0) {
	echo "Admin page only, sorry.";
	die;
}

$cTheme = get_theme_info();
$cid = "";
$uid = "";
$cname = "";
$uname = "";
// Find the channel.
// -----------------
// There are two ways to do this, one is by "id=" which is used internally
// to the site.  It's slightly faster.
// The other way is by name, designed to be used by urls directed from
// irc etc.

std_theme_styles(1);
std_theme_body("../../");


$cname = trim($cname);

if ($cid!="") {
	$rtype="c";
/*
	if (strspn($id,$validnumbers)!=strlen($id)) {
		echo("Abuse! Abuse!"); //TODO: Log this
		exit;
	}
*/
  	$channels = pg_safe_exec("SELECT * from channels,channellog WHERE channellog.channelid='$cid' AND channels.id=channellog.channelid ORDER BY channellog.ts DESC");
} else if ($cname!="") {
	$rtype="c";
	if (ord($cname)!=0x23)
		$cname="#" . $cname;
  	$cname=strtolower($cname);
	$channels = pg_safe_exec("SELECT * FROM channels,channellog WHERE lower(channels.name)='$cname' AND channels.id=channellog.channelid ORDER BY channellog.ts DESC");
} else if ($show=="purged") {
	if ($spec_chan=="") {
?>
<h1>Enter a channel name</h1>
Enter a channel name that you wish to view details about manager changes<br>
<form name=chan method="get">
<input type=text name="spec_chan" value="#*">
<input type=submit value="Go Baby!">
</form>
<br><br>
<a href="../../admin/index.php">Back to Reports</a>.
</body></html>
<?
		die;
	}
	if ($spec_chan=="*") {
		$rtype="c";
		$channels = pg_safe_exec("SELECT * FROM channels,channellog WHERE channels.id=channellog.channelid AND channellog.event=12 ORDER BY channellog.ts DESC");
	} else {
		$rtype="c";
		$match_chan = str_replace("*","%",strtolower($spec_chan));
		$channels = pg_safe_exec("SELECT * FROM channels,channellog WHERE channels.id=channellog.channelid AND channellog.event=12 AND lower(channels.name) LIKE '" . $match_chan . "' ORDER BY channellog.ts DESC");
		$unf = " AND lower(channels.name) LIKE '" . $match_chan . "'";
	}
}

// If we are here then we're working on one channel only.

if (pg_numrows($channels)==0) {
	echo ("<center><h1>No 'Manager Change' events found in channel log.</h1><br><a href=\"mgr_change.php\">retry</a></center>");
	exit;
}




if ($rtype=="c") { $channel = pg_fetch_object($channels,0); }

if ($rtype=="c") {
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
}
	if ($rtype=="c") { $chnname = $channel->name; }
	if ($rtype=="c") { if ($show=="purged") { $query="SELECT channellog.channelid,channels.name,channellog.ts,channellog.event,channellog.message FROM channellog,channels WHERE channellog.channelid=channels.id AND channellog.event='12'" . $unf . " ORDER BY channellog.ts DESC"; } else { $query="SELECT channelid,ts,event,message FROM channellog WHERE channelid='" . $channel->id . "' ORDER BY ts DESC"; } }
//	echo "Q: $query<br>";
	$logs = pg_safe_exec($query);
	echo "<TABLE border=1 WIDTH=100% cellspacing=0 cellpadding=2 BGCOLOR=#" . $cTheme->table_bgcolor . ">";
	if ($rtype=="c") { if ($show=="purged") { echo "<tr><td colspan=3><H2>Manager Changes Events matching <i>$spec_chan</i></H2>\n<a href=\"mgr_change.php\">new search</a><br></td></tr>"; } else { echo "<tr><td colspan=3><H2>Log messages for $chnname</H2></td></tr>"; } }
	if ($show=="purged") { echo "<tr bgcolor=#" . $cTheme->table_headcolor . "><td><font color=#" . $cTheme->table_headtextcolor . ">Time</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Channel</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Message</font></td></tr>"; } else { echo "<tr bgcolor=#" . $cTheme->table_headcolor . "><td><font color=#" . $cTheme->table_headtextcolor . ">Time</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Event</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Message</font></td></tr>"; }
	if (pg_numrows($logs)>0) {
		for ($row=0;$row<pg_numrows($logs);$row++) {
			$log=pg_fetch_object($logs,$row);
			echo("<tr><td>");
			echo(cs_time($log->ts) . " [" . $log->ts . "]</td><td>");
			if ($show=="purged") {
				$bla = pg_safe_exec("SELECT name FROM channels WHERE id='" . $log->channelid . "'");
				$blo = pg_fetch_object($bla,0);
				echo $blo->name . "</td><td>\n";
			} else {
				if (($log->event>6 || $log->event==0) && $rtype=="u") {echo "(old)</td><td>"; } else { echo($type[$log->event] . "</td><td>"); }
			}
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

php?>
</body></html>
