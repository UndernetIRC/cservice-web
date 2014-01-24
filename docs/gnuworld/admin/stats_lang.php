<?
	require("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();
	echo "<html><head><title>Lang Stats</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body("../");
	if ($admin<800) {
		echo("Oi! What are you doing here eh?");
		exit;
	}
?>
<!-- $Id: stats_lang.php,v 1.5 2002/05/20 23:58:04 nighty Exp $ //-->
<h1>Languages Stats</h1>
<h3><a href="index.php">Back</a></h3>
<hr>
<?

$lreq = pg_safe_exec("SELECT * FROM languages ORDER BY name");
if (pg_numrows($lreq)>0) {

	$ctotal = pg_safe_exec("SELECT COUNT(id) AS count FROM users");
	$crow = pg_fetch_object($ctotal,0);
	$ze_grand_total = $crow->count;

	echo "<b>" . $ze_grand_total . "</b> total user";
	if ($ze_grand_total>1) { echo "s"; }
	echo "<br>\n";

	echo "<table border=1 cellspacing=0 cellpadding=3 bgcolor=#" . $cTheme->table_bgcolor . ">\n";
	echo "<tr bgcolor=#" . $cTheme->table_headcolor . ">";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>Language</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>ID</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>% of use</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b># of use</b></font></td>\n";
	echo "<td><font color=#" . $cTheme->table_headtextcolor . "><b>graph bar</b></font></td>\n";
	echo "</tr>\n";

	// reset vars
	if (isset($namez)) { unset($namez); }
	if (isset($idz)) { unset($idz); }
	if (isset($ratioz)) { unset($ratioz); }
	if (isset($numz)) { unset($numz); }

	// gather info
	for ($x=0;$x<pg_numrows($lreq);$x++) {
		$lrow = pg_fetch_object($lreq,$x);
		$ze_id = $lrow->id;
		$ze_code = $lrow->code;
		$ze_name = $lrow->name;

		$namez[]=$ze_name;
		$idz[]=$ze_id;
		// get the count(s)
		$clang = pg_safe_exec("SELECT COUNT(id) AS count FROM users WHERE language_id='" . $ze_id . "'");
		$crowl = pg_fetch_object($clang,0);
		$ze_total_users = $crowl->count;
		$ze_ratio = round((($ze_total_users*100)/$ze_grand_total),2);
		$ratioz[]=$ze_ratio;
		$numz[]=$ze_total_users;

	}

	// sort it (highest count first)
	array_multisort($numz,SORT_NUMERIC,SORT_DESC,$namez,$idz,$ratioz);


	// display

	for ($y=0;$y<count($idz);$y++) {

		echo "<tr>\n";
		echo "<td>" . $namez[$y] . "</td>\n";
		echo "<td>" . $idz[$y] . "</td>\n";
		echo "<td>";
		if (($ratioz[$y]+0)>0) { echo "<b>"; }
		echo ($ratioz[$y]+0) . "&nbsp;%";
		if (($ratioz[$y]+0)>0) { echo "</b>"; }
		echo "</td>\n";
		echo "<td>" . ($numz[$y]+0) . "</td>\n";
		echo "<td>";
		if (($ratioz[$y]+0)==0) {
			echo "&nbsp;";
		} else {
			echo "<img src=../images/graph.jpg border=0 height=10 width=";
			$da_val = round($ratioz[$y]*2,0);
			if ($da_val == 0) { echo "1"; } else { echo $da_val; }
			echo ">";
		}
		echo "</td>\n";
		echo "</tr>\n";
	}


	echo "</table>\n";
} else {
	echo "<h2>NO LANGUAGES ?!@#</h2>";
}

?>
</body></html>
