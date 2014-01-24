<?
	require("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();
	echo "<html><head><title>Sponsor Link Click Statistics</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body("../");
	if ($user_id!=323 && ($admin<901 || HOSTING_CLICK_CHECK==0)) {
		echo("Oi! What are you doing here eh?");
		exit;
	}
?>
<!-- $Id: sponsor_clicks.php,v 1.2 2003/01/16 06:37:25 nighty Exp $ //-->
<h1>Sponsor Link Click Statistics</h1>
<h3><a href="index.php">Back</a></h3>
<hr>
<?
	$fd = @fopen(HOSTING_STATS_FILENAME,"r");
	if ($fd) {
		$c_val = fread($fd,filesize(HOSTING_STATS_FILENAME));
		$co = explode(" ",$c_val);
		echo "<pre><font size=+0>";
		echo "Current count :\t\t<b><font size=+2>" . ($co[0]+0) . "</font> click";
		if ($co[0]>1) { echo "s"; }
		echo "</b> on the sponsor link in the footer<br>\n";
		echo "Since :\t\t\t<b><font size=+1>" . cs_time($co[1]);
		echo "</font></b><br>\n";
		echo "Last click :\t\t<b><font size=+1>" . cs_time($co[2]);
		echo "</font></b><br>\n";
		echo "</font></pre>";
		fclose($fd);
	} else {
		echo "<h3>Unable to open the file, duh?! Try again</h3>";
	}

?>
</body>
</html>
