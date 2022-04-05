<?
	require("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();
	std_theme_styles(1);
	std_theme_body("../");
	if ($admin<750) {
		echo("Oi! What are you doing here eh?");
		exit;
	}
?>
<!-- $Id: view_pendingusers.php,v 1.5 2003/08/17 05:47:55 nighty Exp $ //-->
<h1>Current Pending users (not confirmed)</h1><h3>
<a href="index.php">Back</a></h3>
<hr>
<?
 $ENABLE_COOKIE_TABLE = 0;

 pg_safe_exec("DELETE FROM pendingusers WHERE expire<date_part('epoch', CURRENT_TIMESTAMP)::int");

 	$r1 = pg_safe_exec("SELECT COUNT(*) AS count FROM pendingusers");

 	$s1 = pg_fetch_object($r1,0);

 echo "<b>" . $s1->count . "</b> total user(s) pending.";
 echo "<br><br>";
 echo "<i>";
 $confirm_url = gen_server_url() . LIVE_LOCATION . "/confirm.php";
 echo "Pending users are *NOT* yet active on the system, they wait the user confirmation by clicking on the email they received,<br>\n";
 echo "or by going on <a href=\"" . $confirm_url . "\">" . $confirm_url . "</a> and by entering the given cookie.<br>\n";
 echo "Pending users expire <b>24 hours</b> after the request was posted.";
 echo "</i>";



  $query = "SELECT * FROM pendingusers ORDER BY expire";

  //echo "<b>SQL Query:</b><br>" . $query . ";<br><br>";

  $res=pg_safe_exec($query);
  $bm_count=0;
  echo("<form><table border=1 bgcolor=#" . $cTheme->table_bgcolor . " cellspacing=2 cellpadding=2 width=600>\n");
  echo("<tr bgcolor=#" . $cTheme->table_tr_enlighten . "><td><b>Username</b></td><td><b>E-Mail</b></td><td><b>Lang</b></td><td><b>Verification&nbsp;data</b></td><td><b>Expire</b></td>");
if ($admin>=800) {	echo "<td><b>Cookie</b></td>";}
echo ("<td><b>Poster IP</b></td></tr>\n");
  for ($i=0;$i<pg_numrows($res);$i++) {
	$row = pg_fetch_object($res,$i);
	$bm_count++;
 	$unf = pg_safe_exec("SELECT name FROM languages WHERE id='" . $row->language . "'");
	$ooo = pg_fetch_object($unf,0);
	echo "<tr>";
	echo "<td>" . $row->user_name . "</a></td>";
 	echo "<td>" . $row->email . "</td>";
 	echo "<td>" . $ooo->name . "&nbsp;(" . $row->language . ")</td>";
 	echo "<td>" . $row->verificationdata . "</td>";
	echo "<td>" . str_replace(" ","&nbsp;",cs_time($row->expire)) . "</td>";
	if ($admin>=800) {echo "<td>" . $row->cookie . "</td>";}
	echo "<td>" . $row->poster_ip . "</td>";
	echo "</tr>";

 }
  echo "</table><h3>\n";
  if ($bm_count==0) {
  echo("There is NO new users currently pending !");
  }
  echo "</h3>";
?>
</form>
</body></html>
