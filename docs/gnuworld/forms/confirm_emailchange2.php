<?php
require("../../../php_includes/cmaster.inc");

if($loadavg5 >= CRIT_LOADAVG)
{
   header("Location: ../highload.php");
   exit;
}

$cTheme = get_theme_info();

if ($ID!="" && strlen($ID)<=128) {
	std_connect();
 	$res=pg_safe_exec("select * from pending_emailchanges where phase=2 AND cookie='$ID' AND expiration>=date_part('epoch', CURRENT_TIMESTAMP)::int");
  	if (pg_numrows($res)==0) {
		std_theme_styles(1); std_theme_body("../");
		echo "<h1>Error</h1> The URL entered is not valid.  Please check it ";
		echo "and make sure it is correct</h1><a href=\"confirm_emailchange2.php\">Try again.</a>";
		echo "</body></html>";
		exit;
	} else {
		pg_safe_exec("delete from pending_emailchanges where expiration<date_part('epoch', CURRENT_TIMESTAMP)::int");
		$email=pg_fetch_object($res,0);
		$userid = $email->user_id;
		$nmail = $email->new_email;
		$omail = $email->old_email;
		$res=pg_safe_exec("select id from users where id!=$userid AND lower(email)='" . strtolower($nmail) . "'");
		if (pg_numrows($res)>0) {
			std_theme_styles(1); std_theme_body("../");
			echo "<h1>Error</h1>";
			echo "An account with that e-mail is already known.  Please choose another.";
			echo "</body></html>";
			pg_safe_exec("delete from pending_emailchanges where phase=2 AND cookie='$ID'");
	 		exit;
	 	}
	}

	// change email

	$res=pg_safe_exec("UPDATE users SET email='$nmail',last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,last_updated_by='Email-in-record Modification' WHERE id='$userid'");
	$user_id = $userid;
	log_user($userid,7,"Changed email-in-record from: $omail(old) to: $nmail(new) - cookie was: $ID");
	$user_id = 0;
	if ($res) {
		$res = pg_safe_exec( "delete from pending_emailchanges where phase=2 AND cookie='$ID'" );

		echo "<html><head><title>Successful E-Mail Change</title>";
		std_theme_styles();
		echo "</head>";
		std_theme_body("../");
		echo "<h1>Success!</h1>";
		echo "Your account has a new email-in-record :<br>\n";
		echo "<center><table><tr><td><h1>". $nmail ."</h1></td></tr></table></center>";
		echo "You may now proceed to the <a href=\"../index.php\" target=_top>Main page</a>.<br>";
		echo "</body></html>";
		exit;
	} else {
		// First check to see if somebody got there first.
		$res=pg_safe_exec("select id from users where id!=$userid AND lower(email)='" . strtolower($email->new_email) . "'");
		if (pg_numrows($res)>0) {
			std_theme_styles(1); std_theme_body("../");
			echo "<h1>Error</h1>";
			echo "An account with that e-mail is already known.  Please choose another.";
			echo "</body></html>";
			pg_safe_exec("delete from pending_emailchanges where phase=2 AND cookie='$ID'");
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
	}
	exit; // Shouldn't get here.
} else {
	echo "<html><head><title>Email Change Confirmation 2/2</title>";
	std_theme_styles();
	echo "</head>";
	std_theme_body("../");
	echo "<form method=POST><h1>Email Change Confirmation 2/2</h1>Please enter the ID you received to your NEW email below.";
	echo "<input type=text name=ID size=50 maxlength=128><br><input type=submit value=\"Finish Email Change\">";
	echo "</form></body></html>";
}



?>
