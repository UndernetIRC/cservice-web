<?
	$min_lvl = 800;
	require("../../../php_includes/cmaster.inc");
	std_init();
$cTheme = get_theme_info();
	std_theme_styles(1);
	std_theme_body("../");
	if ($admin<1) {
		echo("Oi! What are you doing here eh?");
		exit;
	}

$nrw_lvl = 0;
if (acl(XWEBAXS_2)) { $nrw_lvl = 1; }
if (acl(XWEBAXS_3)) { $nrw_lvl = 2; }

/* $Id: stats.php,v 1.4 2002/05/21 01:59:45 nighty Exp $ */

?>
<h1>New Registrations Status</h1>
<hr>

<?

$q = "select pending.channel_id from channels,pending where pending.status=0 AND channels.id=pending.channel_id";
$r = pg_safe_exec($q);
$nb_incoming=pg_numrows($r);

$q = "select pending.channel_id from channels,pending where pending.status=1 AND channels.id=pending.channel_id";
$r = pg_safe_exec($q);
$nb_pending=pg_numrows($r);

$q = "select pending.channel_id from channels,pending where pending.status=2 AND channels.id=pending.channel_id";
$r = pg_safe_exec($q);
$nb_notif=pg_numrows($r);

$q = "select pending.channel_id from channels,pending where pending.status=2 and pending.reviewed='Y' AND channels.id=pending.channel_id";
$r = pg_safe_exec($q);
$nb_rev=pg_numrows($r);

$q = "select pending.channel_id from channels,pending where pending.status=3 AND channels.id=pending.channel_id";
$r = pg_safe_exec($q);
$nb_completed=pg_numrows($r);

$q = "select pending.channel_id from channels,pending where pending.status=4 AND channels.id=pending.channel_id";
$r = pg_safe_exec($q);
$nb_cancelled=pg_numrows($r);

$q = "select pending.channel_id from channels,pending where pending.status=8 AND channels.id=pending.channel_id";
$r = pg_safe_exec($q);
$nb_review=pg_numrows($r);

$q = "select pending.channel_id from channels,pending where pending.status=9 AND channels.id=pending.channel_id";
$r = pg_safe_exec($q);
$nb_rejected=pg_numrows($r);


$nb_total = $nb_incoming+$nb_pending+$nb_notif+$nb_completed+$nb_cancelled+$nb_review+$nb_rejected;

$p_total = 100*1.0;
if ($nb_total==0) {
	$p_total = 0;$p_incoming = 0;$p_pending = 0;$p_notif = 0;
	$p_rev = 0;$p_completed = 0;$p_cancelled = 0;$p_review = 0;$p_rejected = 0;
} else {
	$p_incoming = (($nb_incoming*100.0)/$nb_total)*1.0;
	$p_pending = (($nb_pending*100.0)/$nb_total)*1.0;
	$p_notif = (($nb_notif*100.0)/$nb_total)*1.0;
	if ($nb_notif>0) { $p_rev = (($nb_rev*100.0)/$nb_notif)*1.0; } else { $p_rev = 0; }
	$p_completed = (($nb_completed*100.0)/$nb_total)*1.0;
	$p_cancelled = (($nb_cancelled*100.0)/$nb_total)*1.0;
	$p_review = (($nb_review*100.0)/$nb_total)*1.0;
	$p_rejected = (($nb_rejected*100.0)/$nb_total)*1.0;
}

$p_incoming=round($p_incoming,2);
$p_pending=round($p_pending,2);
$p_notif=round($p_notif,2);
$p_rev=round($p_rev,2);
$p_completed=round($p_completed,2);
$p_cancelled=round($p_cancelled,2);
$p_review=round($p_review,2);
$p_rejected=round($p_rejected,2);

echo "<table>\n";
echo "<tr><td>- incoming</td><td> : <b>$nb_incoming</b> <font color=#" . $cTheme->main_textlight . " size=-1>($p_incoming %)</font></td></tr>\n";
echo "<tr><td>- pending</td><td> : <b>$nb_pending</b> <font color=#" . $cTheme->main_textlight . " size=-1>($p_pending %)</font></td></tr>\n";
echo "<tr><td>- notification</td><td> : <b>$nb_notif</b> <font color=#" . $cTheme->main_textlight . " size=-1>($p_notif %)</font> [<b>$nb_rev</b> reviewed  <font color=#" . $cTheme->main_textlight . " size=-1>($p_rev %)</font>]";
if ($nrw_lvl>1) {
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"review_toplist.php\">admin top list</a>";
}
echo "</td></tr>\n";
echo "<tr><td>- registered</td><td> : <b>$nb_completed</b> <font color=#" . $cTheme->main_textlight . " size=-1>($p_completed %)</font></td></tr>\n";
echo "<tr><td>- cancelled</td><td> : <b>$nb_cancelled</b> <font color=#" . $cTheme->main_textlight . " size=-1>($p_cancelled %)</font></td></tr>\n";
echo "<tr><td>- pending review</td><td> : <b>$nb_review</b> <font color=#" . $cTheme->main_textlight . " size=-1>($p_review %)</font></td></tr>\n";
echo "<tr><td>- rejected</td><td> : <b>$nb_rejected</b> <font color=#" . $cTheme->main_textlight . " size=-1>($p_rejected %)</font></td></tr>\n";
echo "<tr><td colspan=2>&nbsp;</td></tr>\n";
echo "<tr><td valign=top>Applications<br>processed so far</td><td> : <b>$nb_total</b> <font color=#" . $cTheme->main_textlight . " size=-1>($p_total %)</font></td></tr>\n";
echo "</table>\n";

?>
<br>
<a href="../admin/index.php">Back to reports</a><br>
</body></html>
