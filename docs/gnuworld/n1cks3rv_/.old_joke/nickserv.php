<?
	$min_lvl=800;
	require("../../../php_includes/cmaster.inc");
	std_connect();
	$cTheme = get_theme_info();
	std_theme_styles(1);
	std_theme_body("../");

	/*
	$rcc = pg_safe_exec("SELECT nickname,ts FROM nickserv WHERE user_id=" . (int)$user_id . "");
	if ($rco = pg_fetch_object($rcc)) {
		echo "<h3>You already reserved a nickname : " . $rco->nickname . "</h3><br>on " . cs_time($rco->ts) . "<br><br>";
		echo "<a href=\"/live\" target=_top>Back to home</a><br>\n"; 
		echo "</body></html>\n";
		die;
	}
	*/

?>
<!-- $Id: nickserv.php,v 1.20 2004/02/26 01:17:30 nighty Exp $ //-->
<?
if (trim($_POST["nickname"])!="" && check_secure_form("nickserv")) {

		echo "<img src=\"april_fools.jpg\" border=0 alt=\"\">\n";
		echo "</body></html>\n\n";
		die;

	if (strlen(trim($_POST["nickname"]))<2 || strlen(trim($_POST["nickname"]))>9 || !preg_match("/^[a-zA-Z\^\\\|\[\]\{\}\_\`][a-zA-Z0-9\^\\\|\[\]\{\}\_\`-]+$/", trim($_POST["nickname"]))) {
                echo "<h3>This nickname is invalid, pick another one !</h3><br>\n";
                echo "<a href=\"nickserv.php\">Go Back</a><br></body></html>\n\n";
                die;
	}

	unset($q);
	$rc = pg_safe_exec("SELECT COUNT(id) AS count FROM nickserv WHERE lower(nickname)='" . strtolower(trim($_POST["nickname"])) . "'");
	$ro = pg_fetch_object($rc);
	if ($ro->count==0) {
		$q = "INSERT INTO nickserv (nickname, user_id, ts) VALUES ('" . post2db(trim($_POST["nickname"])) . "', " . (int)$user_id . ", now()::abstime::int4)";
		pg_safe_exec($q);

		echo "<h3>You successfully reserved '" . trim($_POST["nickname"]) . "' for pre-registration to Nickserv !</h3><br>\n";
		echo "<a href=\"/live\" target=_top>Back to home</a><br>\n"; 
		echo "</body></html>\n\n";
		die;

	} else {
		echo "<h3>This nickname is already chosen by someone else, pick another one !</h3><br>\n";
		echo "<a href=\"nickserv.php\">Go Back</a><br></body></html>\n\n";
		die;
	}
		
}

?>
<h1>Nickserv / Nickname pre-registration</h1>
<hr>
<h4>
<form name=nickserv method=post>
<? make_secure_form("nickserv"); ?>
Please choose the nickname you would like to own in the future :<br>
<input type=text name=nickname size=12 maxlength=9><br><br>
<input type=submit value="Reserve this nickname for me">
<br><br>
<i>You can only use this form ONCE, be sure of what you choose.</i><br>
<i>Nickname should be 2 to 9 chars in length.</i><br>
</form>
</h4>
<br>
</body></html>
