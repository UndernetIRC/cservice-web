<?

$ENABLE_COOKIE_TABLE=0;

require("../../../php_includes/cmaster.inc");

if($loadavg5 >= CRIT_LOADAVG)
{
   header("Location: ../highload.php");
   exit;
}

$cTheme = get_theme_info();

if ($ID!="" && strlen($ID)<=128) {
	std_connect();
 	$res=pg_safe_exec("select * from pending_pwreset where cookie='" . $ID . "' AND expiration>=date_part('epoch', CURRENT_TIMESTAMP)::int");
  	if (pg_numrows($res)==0) {
		std_theme_styles(1); std_theme_body("../");
		echo "<h1>Error</h1> The URL entered is not valid.  Please check it ";
		echo "and make sure it is correct</h1><a href=\"confirm_pwreset.php\">Try again.</a>";
		echo "</body></html>";
		exit;
	} else {
		pg_safe_exec("delete from pending_pwreset where expiration<date_part('epoch', CURRENT_TIMESTAMP)::int");
		$pwreset=pg_fetch_object($res,0);
		$userid = $pwreset->user_id;
		$qid = $pwreset->question_id;
		$vdata = $pwreset->verificationdata;
	}

	// change verifdata
	$gor=pg_safe_exec("SELECT verificationdata FROM users WHERE id='" . (int)$userid . "'");
	$goro=pg_fetch_object($gor);
	$res=pg_safe_exec("UPDATE users SET question_id='" . (int)$qid . "',verificationdata='" . post2db($vdata) . "',post_forms=(date_part('epoch', CURRENT_TIMESTAMP)::int+86400*10),last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,last_updated_by='Verif Q/A Reset' WHERE id='" . (int)$userid . "'");
	$user_id = $userid;
	log_user($userid,8,"Cookie was: " . $ID . ", Old V/A was: " . $goro->verificationdata);
	$user_id = 0;
	if ($res) {
		$res = pg_safe_exec( "delete from pending_pwreset where cookie='" . $ID . "'" );

		echo "<html><head><title>Successful Verificiation Question/Answer Reset</title>";
		std_theme_styles();
		echo "</head>";
		std_theme_body("../");
		echo "<h1>Success!</h1>";
		echo "Your account verification question/answer has been changed !<br>\n";
		echo "<br><br>";
		echo "You may now proceed to the <a href=\"../index.php\" target=_top>Main page</a>.<br>";
		echo "</body></html>";
		exit;
	} else {
		echo "<html><head><title>An Error Occured</title>";
		std_theme_styles();
		echo "</head>";
		std_theme_body("../");
		echo "<h1>An Error has occured.</h1>";
		echo "An Error has occured, it's beyond me whats going on.  Maybe ask someone in ";
		echo SERVICE_CHANNEL . "?  They probably don't know either, but it'll make you feel better.";
		echo "</body></html>";
		exit;
	}
	exit; // Shouldn't get here.
} else {
	echo "<html><head><title>Verification Question/Answer Reset Confirmation</title>";
	std_theme_styles();
	echo "</head>";
	std_theme_body("../");
	echo "<form method=POST><h1>Verification Question/Answer Reset Confirmation</h1>Please enter the ID you recieved in the email below.";
	echo "<input type=text name=ID size=50 maxlength=128><br><input type=submit value=\"Complete Verification Question/Answer Reset\">";
	echo "</form></body></html>";
}

?>
