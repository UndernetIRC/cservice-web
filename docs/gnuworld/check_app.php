<?
require('../../php_includes/cmaster.inc');
std_connect();
$cTheme = get_theme_info();
if ($name!="") {
	if (ord($name)!=0x23)
		$name="#" . $name;
  	$name=strtolower($name);
	$lastdays = time() - (86400*5);
	$channels = pg_safe_exec("SELECT pending.created_ts,pending.channel_id FROM channels,pending WHERE lower(channels.name)='$name' AND pending.channel_id=channels.id AND (pending.decision_ts=0 OR (pending.decision_ts>0 AND pending.decision_ts>=$lastdays))");

} else {
	std_theme_styles(1);
	std_theme_body();
?>
<h1>Enter channel name</h1>
Enter a channel name that you wish to check application status<br>
<form method="get">
<input type=text name="name">
<input type=submit value="Go Baby!">
</form>
</body></html>
<?
	exit;
}

if ((pg_numrows($channels)==0)) {
	std_theme_styles(1); std_theme_body();
	echo("<center><h1>No applications matched that channel</h1><br><a href=\"check_app.php\">try again</a></center></body></html>");
	exit;
}
$channel = pg_fetch_object($channels,0);
if (($channel->id == 1) && ($admin < 1)) {
	std_theme_styles(1); std_theme_body();
	echo("<center><h1><a href=\"http://www.thex-files.com/\">Some truths are not for you</a></h1></center></body></html>");
	exit;
}
header("Location: view_app.php?id=" . $channel->created_ts . "-" . $channel->channel_id . "&back=checkapp\n\n");
die;
?>
