<?
	require("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();
	std_theme_styles(1);
	std_theme_body("../");

	if ($admin<600) {
		echo("Oi! What are you doing here eh?");
		exit;
	}
?>
<!-- $Id: weirdos.php,v 1.7 2005/11/18 04:19:33 nighty Exp $ //-->
<h1>Interesting Channel Events (last 14 days)</h1><h3>
<a href="index.php">Back</a></h3>
<hr>
<?
  $type = $channel_events;

  $res=pg_safe_exec("select " .
	" channels.name as channel_name, " .
	" channellog.ts as ts, " .
	" channellog.channelid as id, " .
	" channellog.event as event, " .
	" channellog.message as messages " .
        "from " .
	" channels, " .
	" channellog " .
	"where ".
	" channels.id=channellog.channelid " .
	"and (" .
	" channellog.event = 4 " .
	"or " .
	" channellog.event = 5 " .
	"or " .
	" channellog.event = 6 " .
	"or " .
	" channellog.event = 9 " .
	"or " .
	" channellog.event = 10 " .
	") and " .
	" channellog.ts > now()::abstime::int4-(14*24*60*60) " .
	"order by channellog.ts desc");

  if (pg_numrows($res)<1) {
	echo("Nothing of interest has happened recently");
  	exit;
  }
  echo("<table bgcolor=#" . $cTheme->table_bgcolor . " border=1 cellpadding=3 cellspacing=0>\n");
  echo("<tr bgcolor=#" . $cTheme->table_headcolor . "><th><font color=#" . $cTheme->table_headtextcolor . ">TS</font></th><th><font color=#" . $cTheme->table_headtextcolor . ">Channel</font></th><th><font color=#" . $cTheme->table_headtextcolor . ">Event</font></th><th><font color=#" . $cTheme->table_headtextcolor . ">Message</font></th></tr>\n");
  for ($i=0;$i<pg_numrows($res);$i++) {
	$row=pg_fetch_object($res,$i);
	$e_name = $type[$row->event];
	if ($e_name == "") { $e_name = "Event #" . $row->event; }
	echo("<tr><td>" . cs_time($row->ts) . "</td><td><a href=\"../channels.php?id=" . $row->id . "\" target=_blank>" .
		$row->channel_name ."</a></td>" .
		"<td>" . $e_name . "</td>");
	if ($admin<SHOW_IP_LEVEL) {
		echo "<td>" . htmlentities(remove_ip($row->messages,2)) . "</td>";
	} else {
		echo "<td>" . htmlentities($row->messages) . "</td>";
	}
	echo "</tr>\n";
  }
  echo("</table>")
?>
</body></html>
