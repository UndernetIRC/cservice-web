<?
	require("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();
	std_theme_styles(1);
	std_theme_body("../");

	if ($admin<900) {
		echo("Oi! What are you doing here eh?");
		exit;
	}
if ($o<1 || $o>2) { $o = 1; }

if ($o==1) { $order="ts DESC"; }
if ($o==2) { $order="ip"; }
?>
<!-- $Id: view_newu_ips.php,v 1.3 2002/05/20 23:58:04 nighty Exp $ //-->
<h1>New Users Locked IPs</h1>
<a href="index.php">Back to Admin Reports</a>
<hr size=1 noshade>
<?



$ENABLE_COOKIE_TABLE=1;
pg_safe_exec("DELETE FROM newu_ipcheck WHERE expiration<date_part('epoch', CURRENT_TIMESTAMP)::int");
$res = pg_safe_exec("SELECT * FROM newu_ipcheck ORDER BY " . $order);
$num = pg_numrows($res);

if ($num==0) {
	echo "<b>No IPs are currently locked by the system</b>";
} else {
	if ($num>1) { $saddy = "s"; } else { $saddy = ""; }
	echo "<b>" . $num . "</b> IP" . $saddy . " are currently locked by the system :";
	echo "(<b> sorted by ";
	if ($o==1) { echo "Set on </b>)"; }
	if ($o==2) { echo "IP NUM </b>)"; }
	echo "<br>\n";
	echo "<form>";
	echo "<table border=1 cellspacing=1 cellpadding=2 bgcolor=#" . $cTheme->table_bgcolor . ">";
	echo "<tr bgcolor=#" . $cTheme->table_headcolor . ">";
	if ($o==1) { echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Set on</b></font></td>"; } else { echo "<td><font color=#" . $cTheme->table_headtextcolor . "><a href=\"view_newu_ips.php?o=1\"><b>Set on</b></a></font></td>"; }
	if ($o==2) { echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>IP NUM</b></font></td>"; } else { echo "<td><font color=#" . $cTheme->table_headtextcolor . "><a href=\"view_newu_ips.php?o=2\"><b>IP NUM</b></a></font></td>"; }
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Expire</b></font></td>";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Action</b></font></td>";
	echo "</tr>";
	for ($x=0;$x<$num;$x++) {
		$row = pg_fetch_object($res,$x);
		echo "<tr>";
		echo "<td>" . cs_time($row->ts) . "</td>";
		echo "<td>" . $row->ip . "</td>";
		echo "<td>" . cs_time($row->expiration) . "</td>";
		echo "<td><input type=button value=\" Unlock \" onClick=\"location.href='unlock_ip.php?IPNUM=" . $row->ip . "&o=" . $o . "';\"></td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</form>";
}

$ENABLE_COOKIE_TABLE=0;
?>
</body>
</html>
