<?
	require("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();
	std_theme_styles(1);
	std_theme_body("../");
?>
<h1><? echo NETWORK_NAME ?> Forms</h1>
<hr>
<h3>
<a href="purge.php">Purge Request</a><br><br>
<a href="managerchange.php">Manager Change Request</a><br><br>
<a href="emailchange.php">E-mail Change Request</a><br><br>
</h3>
<br>
</body></html>
