<?
	require("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();
	std_theme_styles(1);
	std_theme_body("../");
	if (!acl(XWEBSESS)) {
		echo("Oi! What are you doing here eh?");
		exit;
	}
?>
<!-- $Id: view_admins.php,v 1.9 2004/07/25 03:31:51 nighty Exp $ //-->
<h1>Admins Logged to the website</h1><h3>
<a href="index.php">Back</a></h3>
<hr>
<?
 $ENABLE_COOKIE_TABLE = 1;

 	pg_safe_exec("DELETE FROM webcookies WHERE expire<now()::abstime::int4");

 	$r1 = pg_safe_exec("SELECT COUNT(*) AS count FROM webcookies WHERE is_admin>0");
 	$r2 = pg_safe_exec("SELECT COUNT(*) AS count FROM webcookies WHERE is_admin=0");
	$active_mins = 2;
	$r3 = pg_safe_exec("SELECT COUNT(*) AS count FROM webcookies WHERE is_admin=0 AND expire>(now()::abstime::int4+" . $active_mins . "*60)");
 	$s1 = pg_fetch_object($r1,0);
 	$s2 = pg_fetch_object($r2,0);
	$s3 = pg_fetch_object($r3,0);

 echo "<b>" . $s1->count . "</b> CService official(s) logged in,&nbsp;";
 echo "<b>" . $s2->count . "</b> regular user(s) logged in,&nbsp;";
	if ($s3->count>$s2->count) { $s3c = $s2->count; } else { $s3c = $s3->count; }
 echo "<b>" . $s3c . "</b> active (idle <=" . $active_mins . " minutes).<br>\n";

 echo "Login timeout : <b>" . round((COOKIE_EXPIRE/3600),0) . "</b> hour(s).<br>\n";

  $query = "SELECT * FROM webcookies WHERE is_admin>0 AND expire>=now()::abstime::int4 ORDER BY expire DESC";

  //echo "<b>SQL Query:</b><br>" . $query . ";<br><br>";

  $res=pg_safe_exec($query);
  $bm_count=0;
  echo("<form><table border=1 bgcolor=#" . $cTheme->table_bgcolor . " cellspacing=2 cellpadding=2 width=600>\n");
  if ($admin>=800) { $last_col = "<td><b>&nbsp;</b></td>"; } else { $last_col = ""; }
  echo("<tr bgcolor=#" . $cTheme->table_headcolor . "><td><font color=#" . $cTheme->table_headtextcolor . "><b>Username</b></font></td><td><font color=#" . $cTheme->table_headtextcolor . "><b>Level</b></font></td><td><font color=#" . $cTheme->table_headtextcolor . "><b>Last Access</b></font></td><td><font color=#" . $cTheme->table_headtextcolor . "><b>Expire</b></font></td>" . $last_col . "</tr>\n");
  for ($i=0;$i<pg_numrows($res);$i++) {
	$row = pg_fetch_object($res,$i);
	$bm_count++;
 	$ENABLE_COOKIE_TABLE = 0;
	$unf = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $row->user_id . "'");
	$ooo = pg_fetch_object($unf,0);
	echo "<tr>";
	echo "<td><a href=\"../users.php?id=" . $row->user_id . "\" target=_blank>" . $ooo->user_name . "</a></td>";
 	$ENABLE_COOKIE_TABLE = 1;
	echo "<td>" . $row->is_admin . "</td>";
	echo "<td>" . cs_time(($row->expire-get_custom_session($row->user_id))) . "</td>";
	$session_time=formatSeconds(get_custom_session($row->user_id));
	echo "<td>in " . $session_time . "</td>";
	echo "<td>";
	if (($admin>$row->is_admin || $admin==1000) && $row->user_id!=$user_id) {
		echo "<input type=button value=\"Force deauth\" onclick=\"if (confirm('This will LOGOUT user \\'" . $ooo->user_name . "\\'')) { location.href='fdeauth.php?cookie=" . $row->cookie . "&uid=" . $row->user_id . "'; }\">";
	} else {
		echo "&nbsp;";
	}
	echo "</td>";
	echo "</tr>";

 }
  echo "</table><h3>\n";
  if ($bm_count==0) {
  echo("No official is logged in");
  }
  echo "</h3>";
?>
</form>
</body></html>
