<?php
require("../../../php_includes/cmaster.inc");
global $loadavg5;
if($loadavg5 >= CRIT_LOADAVG)
{
   header("Location: ../highload.php");
   exit;
}

$cTheme = get_theme_info();

if (!empty($_GET["ID"]) && strlen($_GET["ID"])<=128) {
	std_connect();
 	$res=pg_safe_exec("select * from pending_emailchanges where phase=1 AND cookie='" . $_GET["ID"] . "' AND expiration>=date_part('epoch', CURRENT_TIMESTAMP)::int");
  	if (pg_numrows($res)==0) {
		std_theme_styles(1); std_theme_body("../");
		echo "<h1>Error</h1> The URL entered is not valid.  Please check it ";
		echo "and make sure it is correct</h1><a href=\"confirm_emailchange.php\">Try again.</a>";
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
			pg_safe_exec("delete from pending_emailchanges where phase=1 AND cookie='" . $_GET["ID"] . "'");
	 		exit;
	 	}
	}
$res=pg_safe_exec("SELECT * FROM users WHERE id=" . $userid);
$user=pg_fetch_object($res,0);

	// change email
        $cookieval = md5(CRC_SALT_0020 . uniqid("",1) . time() . $nmail);
	pg_safe_exec("update pending_emailchanges SET expiration=(date_part('epoch', CURRENT_TIMESTAMP)::int+21600),phase=2,cookie='$cookieval' WHERE phase=1 AND cookie='" . $_GET["ID"] . "'");

        $confirm_url = gen_server_url() . LIVE_LOCATION . "/forms/confirm_emailchange2.php?ID=$cookieval";

        $the_msg = "If you would like to confirm that the new email-in-record for '".$user->user_name."' should be '$nmail',\n";
        $the_msg .= "then click on the link below within 6 hours :\n\n";
        $the_msg .= "\t$confirm_url\n\n\nThank you\n" . NETWORK_NAME . " Channel Service\n\n\nPS- Please do *NOT* reply to this mail.";

        custom_mail($nmail,"E-Mail change request 2/2",$the_msg,"From: " . NETWORK_NAME . " E-Mail Change Request <" . OBJECT_EMAIL . ">\nReply-To: no.reply@thank.you\nX-Mailer: " . NETWORK_NAME . " Channel Service");

        echo "<h2>";
        echo "Please check your e-mail at '$nmail',<br>then click on the URL to complete the email change.<br><br>\n";
        echo "Note: *nothing* will be changed if you don't click that URL.\n";
        echo "</h2>\n";
        echo "</body></html>\n\n";
        die;
	exit; // Shouldn't get here.
} else {
	echo "<html><head><title>Email Change Confirmation 1/2</title>";
	std_theme_styles();
	echo "</head>";
	std_theme_body("../");
	echo "<form method=POST><h1>Email Change Confirmation 1/2</h1>Please enter the ID you received at your OLD  email below.";
	echo "<input type=text name=ID size=50 maxlength=128><br><input type=submit value=\"Continue Email Change (step 2/2)...\">";
	echo "</form></body></html>";
}
