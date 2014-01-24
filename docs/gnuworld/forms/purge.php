<?
require('../../../php_includes/cmaster.inc');
std_init();
$res=pg_safe_exec("SELECT * FROM users WHERE id=" . $user_id);
$user=pg_fetch_object($res,0);
$cTheme = get_theme_info();
?>
<html>
<head><title><? echo NETWORK_NAME ?> Channel Service: Channel Purge Form</title>
<? std_theme_styles(); ?>
</head>
<? std_theme_body("../"); ?>
<hr>
<h1><? echo NETWORK_NAME ?> Channel Service: Channel Purge Form</h1>
<a href="index.php">Back to forms</a><br>
<hr>
<?
if ($user->verificationdata=="") {
	echo "<h2>\n";

	echo "You need to have verification information set.<br>\n";
	echo "Try <a href=\"../users.php?id=" . $user_id . "\">clicking here</a><br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}

        $now = time();
        $days_elapsed = (int)((int)($now - (int)$user->signup_ts)/86400);
        if ($days_elapsed < MIN_DAYS_BEFORE_SUPPORT) {
                echo "<h1>Error<br>\n";
                echo "Your USERNAME is too newly created !</h1><br><h2>You can only process this request after your account is at least ".MIN_DAYS_BEFORE_SUPPORT." day(s) old !</h2><br><br>\n";
                echo "<a href=\"javascript:history.go(-1);\">Go back.</a>\n";
                echo "</body>\n";
                echo "</html>\n\n";
                die;
        }

if ($user->post_forms!="" && $user->post_forms>0) {
	$curr = time();
	if ($user->post_forms>$curr) {
		echo "<h2>\n";

		echo "You will be able to post another FORM on " . cs_time($user->post_forms) . ".<br>\n";
		echo "Please <a href=\"../users.php?id=" . $user_id . "\">click here</a><br>\n";

		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;
	} else if ($user->post_forms==666) {
		echo "<h2>\n";

		echo "You can't post FORMs, because your account has been locked for FORMs.<br>\n";
		echo "Please <a href=\"../users.php?id=" . $user_id . "\">click here</a><br>\n";

		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;
	}
}

if ($crc == md5($HTTP_USER_AGENT . $ts . CRC_SALT_0008)) {

if ($verifdata=="") {
	echo "<h2>\n";

	echo "You need to supply an answer to the verification question.<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}

if ($verifdata!=$user->verificationdata) {
	echo "<h2>\n";

	echo "Invalid verification answer :(<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}

if ($reason!="resign" && $reason!="unused" && $reason!="other") {
	echo "<h2>\n";

	echo "You need to pick one of the 3 reasons.<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}

if ($reason=="resign" && $resignreason=="") {
	echo "<h2>\n";

	echo "You need to supply a reason.<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}

if ($reason=="other" && $otherreason=="") {
	echo "<h2>\n";

	echo "You need to supply a reason.<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}

$unf = pg_safe_exec("SELECT * FROM levels,users,channels WHERE lower(users.user_name)='purgeme' AND users.id=levels.user_id AND levels.access=499 AND levels.channel_id=channels.id AND lower(channels.name)='" . strtolower($channel) . "'");
if (pg_numrows($unf)==0) {
	echo "<h2>\n";

	echo "The username <i>PurgeMe</i> is not added at level 499 on your channel.<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
} else {
	$roo = pg_fetch_object($unf,0);
	$ENABLE_COOKIE_TABLE = 0;
	$chan_id = $roo->channel_id;
	$res = pg_safe_exec("SELECT * FROM pending_mgrchange WHERE channel_id='$chan_id' AND (confirmed='1' OR confirmed='3')");
	if (pg_numrows($res)>0) {
		echo "<h2>\n";

		echo "Sorry, you can't purge this channel because a Manager change request is either awaiting review or a temporary Manager change is active.<br>\n";

		echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;
	}
}


$mmsg = "\n";
$mmsg = $mmsg . "	                 " . NETWORK_NAME . " Channel Service                 \n";
$mmsg = $mmsg . "        	            	Purge Request Form                \n";
$mmsg = $mmsg . "			 ------------------------ \n";
$mmsg = $mmsg . "\n";
$mmsg = $mmsg . "Channel Name: " . $_POST["channel"] . "\n";
$mmsg = $mmsg . "Your username (not nick): " . $_POST["username"] . "\n";
$mmsg = $mmsg . "\n";
$mmsg = $mmsg . "Verification Question answer:\n";
$mmsg = $mmsg . "      a) What's your mother's maiden name?\n";
$mmsg = $mmsg . "      b) What's your dog's (or cat's) name?\n";
$mmsg = $mmsg . "      c) What's your father's birthdate?\n";
if ($verifq == 1) { $mmsg = $mmsg . "Question: a)\n"; }
if ($verifq == 2) { $mmsg = $mmsg . "Question: b)\n"; }
if ($verifq == 3) { $mmsg = $mmsg . "Question: c)\n"; }
$mmsg = $mmsg . "Answer: $verifdata\n";
$mmsg = $mmsg . "\n";
$mmsg = $mmsg . "\n";
$mmsg = $mmsg . "Reason for Purge Request (Please mark an X for one of these):\n";
$mmsg = $mmsg . "\n";
if ($reason=="unused") { $blah = "X"; } else { $blah = "_"; }
$mmsg = $mmsg . "1) " . $blah . "_ Channel no longer used\n";
if ($reason=="resign") { $blah = "X"; } else { $blah = "_"; }
$mmsg = $mmsg . "2) " . $blah . "_ Don't want to be (or can't be) manager anymore (see below *)\n";
if ($reason=="other") { $blah = "X"; } else { $blah = "_"; }
$mmsg = $mmsg . "3) " . $blah . "_ Other (see below **)\n";
$mmsg = $mmsg . "\n";
if ($reason=="resign") {
	$mmsg = $mmsg . "* :\n" . str_replace("\'","'",$resignreason) . "\n\n";
}
if ($reason=="other") {
	$mmsg = $mmsg . "** :\n" .  str_replace("\'","'",$otherreason) . "\n\n";
}
$mmsg = $mmsg . "\n";
$mmsg = $mmsg . "________________________________\n";
$mmsg = $mmsg . "Purge Request\n";
$mmsg = $mmsg . NETWORK_NAME . " Channel Service\n";
$mmsg = $mmsg . "version 03012001\n";
$mmsg = $mmsg . "Sent by [" . cl_ip() . "] at " . cs_time(time()) . " (user's timezone)\n";

$email = $user->email;

if (!file_exists("../testnet")) {
	custom_mail($purge_at_email,"Purge Request via WEB",$mmsg,"From: " . $_POST["email"] . "\nReply-To: " . $_POST["email"] . "\nX-Mailer: " . NETWORK_NAME . " Channel Service");
} else {
	echo "<h3>Testnet : dumping mail</h3>\n";
	echo str_replace("\n","<br>",$mmsg);
}

	/* make the user can re-port in 10 days. */
	pg_safe_exec("UPDATE users SET post_forms=(now()::abstime::int4+86400*10) WHERE id=" . $user_id);

	echo "<h2>";
	echo "Please allow 3-5 days for your request to be processed.\n";
	echo "</h2>\n";
	echo "</body></html>\n\n";
	die;
}
$res=pg_safe_exec("SELECT * FROM users WHERE id=" . $user_id);
$user=pg_fetch_object($res,0);
$res2=pg_safe_exec("SELECT channels.name FROM channels,levels WHERE levels.channel_id=channels.id and levels.user_id=" . $user_id . " and levels.access=500 and channels.id>1 and channels.registered_ts>0");
if (pg_numrows($res2)==0) {
        echo("I'm sorry, you don't appear to have any channels registered");
        exit;
}
$channel=pg_fetch_object($res2,0);
$c_count = pg_numrows($res2);
?>
<form method=POST>
<ol>
 <li>Your username: <b><? echo $user->user_name ?></b><input type=hidden name=username value=<? echo $user->user_name ?>>
 
<?
if ($c_count==1) { echo " <li>Your channel: <b>" . $channel->name . "</b><input type=hidden name=channel value=\"" . str_replace("\"","&quot;",$channel->name) . "\">\n"; } else {
	echo " <li>Your channel:&nbsp;";
	echo "<select name=channel>\n";
	echo "<option value=\"" . str_replace("\"","&quot;",$channel->name) . "\">" . $channel->name . "</option>\n";
	for ($x=1;$x<$c_count;$x++) {
		$channel = pg_fetch_object($res2,$x);
		echo "<option value=\"" . str_replace("\"","&quot;",$channel->name) . "\">" . $channel->name . "</option>\n";
	}
	echo "</select>\n";
}
?>
 <li>Verification Question/Answer:<br>
Question :
<?
/*
<select name=verifq>
for ($x=1;$x<=$max_question_id;$x++) {
	$checkd="";
	if ($x==$user->question_id) { $checkd=" selected"; }
	echo "<option$checkd value=$x>" . $question_text[$x] . "</option>\n";
}
</select>
*/

echo "<b>" . $question_text[$user->question_id] . "</b>";
echo "<input type=hidden name=verifq value=" . $user->question_id . ">\n";



?><br>Answer : <input type=password name=verifdata size=30 maxlength=30>
 <li>Add the username 'PurgeMe', at level 499 to the access list of your channel.  Only the manager can make this addition to the userlist.<br>/msg <? echo BOT_NAME ?> adduser <?=$channel->name ?> PurgeMe 499
 <li> Reason for purging your channel?
  <ul>
   <li> <input type=radio name=reason value=unused>Channel is unused.
   <li> <input type=radio name=reason value=resign>Don't want to be (or can't be) manager anymore. <br>
Please explain why:<br>
<textarea name=resignreason cols=40 rows=4>
</textarea><br>
(Note: If there is someone else you would like to see take
over channel management, instead of having the channel purged,<br>you should be
filling out the <a href="managerchange.php">manager change</a> form instead.)
   <li> <input type=radio name=reason value=other>Other: <textarea name=otherreason cols=35 rows=4></textarea>
  </ul>
</ol>
<input type=submit value=" Submit Query ">
<br>
Before submitting the form, make sure the PurgeMe username has been added on your channel at level 499.
<?
	$ts = time();
	$crc = md5($HTTP_USER_AGENT . $ts . CRC_SALT_0008);
?>
<input type=hidden name=ts value=<? echo $ts ?>>
<input type=hidden name=crc value=<? echo $crc ?>>
</form>
</body>
</html>
