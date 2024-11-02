<?php
require("../../../php_includes/cmaster.inc");
global $loadavg5;
	$ENABLE_COOKIE_TABLE=0;
if($loadavg5 >= (2*CRIT_LOADAVG))
{
   header("Location: ../highload.php");
   exit;
}

$cTheme = get_theme_info();
$pattern = '/^[0-9a-f]+$/';

if (!empty($_GET["ID"]) && preg_match($pattern, $_GET["ID"])) {
	std_connect();
	pg_safe_exec("delete from pending_mgrchange where expiration<date_part('epoch', CURRENT_TIMESTAMP)::int AND confirmed='0'");
 	$res=pg_safe_exec("select * from pending_mgrchange where crc='" . $_GET["ID"] . "' AND expiration>=date_part('epoch', CURRENT_TIMESTAMP)::int AND confirmed='0'");
  	if (pg_numrows($res)==0) {
		std_theme_styles(1); std_theme_body("../");
		echo "<h1>Error</h1> The URL entered is not valid.  Please check it ";
		echo "and make sure it is correct</h1><a href=\"confirm_mgrchange.php\">Try again.</a>";
		echo "</body></html>";
		exit;
	}
        pg_safe_exec("UPDATE pending_mgrchange SET confirmed='1' WHERE crc='" . $_GET["ID"] . "'");
	$query = sprintf("SELECT pm.channel_id, c.name FROM pending_mgrchange pm INNER JOIN channels c on c.id = pm.channel_id WHERE pm.crc = '%s'", $_GET["ID"]);
	$res = pg_safe_exec($query);
	$channel = pg_fetch_object($res,0);

//	log_channel($tutu->channel_id,12,"Manager Change Request");

	echo "<html><head><title>Successful Confirmation</title>";
	std_theme_styles();
	echo "</head>";
	std_theme_body("../");
	echo "<h1>Successfull Confirmation!</h1>";
	echo "You have confirmed your 'Manager Change Request' for channel<br>\n";
	echo "<center><table><tr><td><h1>". $channel->name ."</h1></td></tr></table></center>";
	echo "Your request will be reviewed by CService Admins, Please allow 3-5 days for your request to be processed.<br><br>";
	echo "You may now proceed to the <a href=\"../index.php\" target=_top>Main page</a>.<br>";
	echo "</body></html>";
	exit;

} else {
	echo "<html><head><title>Manager Change Confirmation</title>";
	std_theme_styles();
	echo "</head>";
	std_theme_body("../");
	echo "<form method=POST><h1>Manager Change Confirmation</h1>Please enter the ID you recieved in the email below.";
	echo "<input type=text name=ID size=50 maxlength=128><br><input type=submit value=\"Comfirm Manager Change\">";
	echo "</form></body></html>";
}
