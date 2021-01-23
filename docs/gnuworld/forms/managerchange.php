<?
require('../../../php_includes/cmaster.inc');
std_init();
	$ENABLE_COOKIE_TABLE=0;
$res=pg_safe_exec("SELECT * FROM users WHERE id=" . $user_id);
$user=pg_fetch_object($res,0);

$cTheme = get_theme_info();

?>
<html>
<head><title><? echo NETWORK_NAME ?> Channel Service: Manager Change Form</title>
<? std_theme_styles(); ?>
</head>
<? std_theme_body("../"); ?>
<hr>
<h1><? echo NETWORK_NAME ?> Channel Service: Manager Change Form</h1>
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
if ($user->email=="") {
	echo "<h2>\n";

	echo "You need to have your email set.<br>\n";
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




if ($crc == md5($_SERVER["HTTP_USER_AGENT"] . $ts . CRC_SALT_0003) && ($ts+1800)>=time()) {

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

if ($mctype!=1 && $mctype!=2) {
	echo "<h2>\n";

	echo "You need to supply a type of new manager.<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}

if (isset($nbtype)) { unset($nbtype); }
$nbtype = 2; // can only use 'weeks'.
$nbretOK = $_POST["nbret"];
if ($mctype==1) {
switch ($nbtype) {
/*
	case 1:
		if ($nbret<22 || $nbret>49) {
			echo "<h2>\n";
			echo "Number of days ranges from 22 to 49.<br>\n";
			echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
			echo "</h2>\n";
			echo "</body>\n</html>\n\n";
			die;
		}
	break;
*/
	default:
	case 2:
		if ($nbret<3 || $nbret>7) {
			echo "<h2>\n";
			echo "Number of weeks ranges from 3 to 7.<br>\n";
			echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
			echo "</h2>\n";
			echo "</body>\n</html>\n\n";
			die;
		}
	break;
/*
	case 3:
		if ($nbret<1 || $nbret>3) {
			echo "<h2>\n";
			echo "Number of months ranges from 1 to 3.<br>\n";
			echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
			echo "</h2>\n";
			echo "</body>\n</html>\n\n";
			die;
		}
	break;
*/
}
}

if ($mcreason=="") {
	echo "<h2>\n";

	echo "You need to supply a reason.<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}

if ($new_manager=="") {
	echo "<h2>\n";

	echo "You need to supply a new manager's username.<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}

if ($new_manager==$user->user_name) {
	echo "<h2>\n";

	echo "You are already a manager on this channel.<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}

$res2 = pg_safe_exec("SELECT id,email,user_name FROM users WHERE lower(user_name)='" . strtolower($new_manager) . "'");
if (pg_numrows($res2)==0) {
	echo "<h2>\n";

	echo "The new manager needs to be a valid CService username.<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}
$newmgr = pg_fetch_object($res2,0);
$new_manager_email = $newmgr->email;
$new_manager_id = $newmgr->id;

if (is_email_locked(2,$new_manager_email)) {
	echo "<h2>\n";

	echo "The new manager has an invalid e-mail address that cannot be owner of a channel (LOCKED).<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}


	$channel = str_replace("\\\'","'",$channel);
	$blah=pg_safe_exec("SELECT id FROM channels WHERE lower(name)='" . strtolower($channel) . "' AND registered_ts>0");
	if (pg_numrows($blah)==0) {
		echo "<h2>Unexistant channel !@#</h2></body></html>\n\n";
		die;
	} else {
		$roo = pg_fetch_object($blah,0);
		$channel_id = $roo->id;
	}

$huhu = pg_safe_exec("SELECT * FROM pending_mgrchange WHERE channel_id='$channel_id' AND confirmed='3'");
if (pg_numrows($huhu)>0) {
	echo "<h2>\n";

	echo "You seem to be already the temporary manager of this channel, you cannot give it out to someone else.<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}
$huhu = pg_safe_exec("SELECT * FROM pending_mgrchange WHERE channel_id='$channel_id' AND confirmed='1'");
if (pg_numrows($huhu)>0) {
	echo "<h2>\n";

	echo "There's already a request being processed at the moment.<br>\n";

	echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
	echo "</h2>\n";
	echo "</body>\n</html>\n\n";
	die;
}



	if ($mctype==2) { // permanent change only check
		// disallow new perm manager to be already 500 somewhere
		if (has_a_channel($new_manager_id)) {
			echo "<h2>\n";

			echo "The chosen new manager is already 500 on one or more channels.<br>This user can only apply to 'Temporary' changes.<br><br>\n";

			echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
			echo "</h2>\n";
			echo "</body>\n</html>\n\n";
			die;
		}
	}

	// disallow the new manager to be new manager if :
	//	user created since < MIN_DAYS_BEFORE_TMGR and request is for a Temporary manager change
	//	user created since < MIN_DAYS_BEFORE_PMGR and request is for a Permanent manager change
	unset($is_invalid); unset($el_days);
	$is_invalid = 0; $el_days = 0;
	$now = time();
	$rrr = pg_safe_exec("SELECT signup_ts FROM users WHERE id=" . (int)$new_manager_id);
	$ooo = pg_fetch_object($rrr);
	if ((int)$ooo->signup_ts>0) {
		$el_days = (int)((int)($now - (int)$ooo->signup_ts)/86400);
		if ($mctype==2) { // permanent changes
			if ($el_days < MIN_DAYS_BEFORE_PMGR) { $is_invalid = 1; }
		} else { // temp changes
			if ($el_days < MIN_DAYS_BEFORE_TMGR) { $is_invalid = 1; }
		}
	}
	if ($is_invalid) {
			echo "<h2>\n";
			echo "The chosen new manager is too newly created (less than ";
			if ($mctype==2) { echo MIN_DAYS_BEFORE_PMGR; } else { echo MIN_DAYS_BEFORE_TMGR; }
			echo " days).<br>";
			if ($mctype==2 && $el_days>=MIN_DAYS_BEFORE_TMGR) {
				echo "This user can only apply to 'Temporary' changes.<br><br>\n";
			} else {
				echo "This user cannot apply to be a new manager yet.<br><br>\n";
			}
			echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
			echo "</h2>\n";
			echo "</body>\n</html>\n\n";
			die;
	}


	// disallow channel changed permanently to be younger registered less than 90 days ago
	$blibli = pg_safe_exec("SELECT registered_ts FROM channels WHERE id='" . $channel_id . "'");
	$buuu = pg_fetch_object($blibli,0);
	$r_ts = $buuu->registered_ts;
	if (($r_ts+(86400*90))>time()) {
		echo "<h2>\n";

		echo "The change request you have made will not be done.<br>We don't make Manager change on Newly Registered channel that is under 90 Days old.<br><br>\n";

		echo "Yours was registered on : " . cs_time($r_ts) . " (" . drake_duration((time()-$r_ts)) . " ago)<br>\n";

		echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;
	}

	$blabla = pg_safe_exec("SELECT * FROM channels,levels WHERE channels.id='$channel_id' AND levels.channel_id=channels.id AND levels.access=500");
	if (pg_numrows($blabla)>1) {
		echo "<h2>\n";

		echo "This channel has multiple managers. Please /join " . SERVICE_CHANNEL . ", you need a special procedure.<br>\n";

		echo "<a href=\"javascript:history.go(-1);\">Go Back</a><br>\n";
		echo "</h2>\n";
		echo "</body>\n</html>\n\n";
		die;
	}





	$change_type = 0;
	$opt_duration = 0;

	if ($mctype==1) { // temporary
		$change_type = 0;
/*
		if ($nbtype==1) { $opt_duration = $nbret*86400; }
		if ($nbtype==2) { $opt_duration = $nbret*86400*7; }
		if ($nbtype==3) { $opt_duration = $nbret*86400*30; }
*/
		(int)$opt_duration = ((int)$nbretOK*86400*7);
	}
	if ($mctype==2) { // perm
		$change_type = 1;
		$opt_duration = 0;
	}

	if ($change_type==0 && ($opt_duration==0 || $opt_duration>(8*86400*7)) ) {
		echo "<h2>Bogus Request !</h2></body></html>\n\n";
		die;
	}

	$expiration = time()+21600; // 6 hours
	$crc_cookie = md5( $expiration . CRC_SALT_015 . $user->email . $new_manager_id . $channel_id );

	$query  = "INSERT INTO pending_mgrchange (channel_id,manager_id,new_manager_id,change_type,opt_duration,reason,expiration,crc,confirmed,from_host) VALUES ";
	$query .= "('$channel_id','$user_id','$new_manager_id','$change_type'," . (int)$opt_duration . ",'$mcreason','$expiration','$crc_cookie',0,'" . cl_ip() . "')";

	pg_safe_exec($query);

//	echo $query;

	$mmsg = "";
	$mmsg .= "Hello,\n\nIf you would like to confirm the \"Manager Change\" request for channel '" . str_replace("\\'","'",$channel) . "',\n";
	$mmsg .= "thus giving it ";
	if ($mctype==1) {
		$mmsg .= "TEMPORARILY for $nbretOK ";
//		if ($nbtype==1) { $mmsg .= "day(s) "; }
//		if ($nbtype==2) { $mmsg .= "week(s) "; }
		$mmsg .= "week(s) ";
//		if ($nbtype==3) { $mmsg .= "month(s) "; }
	} else {
		$mmsg .= "PERMANENTLY ";
	}
	$mmsg .= "to user '$newmgr->user_name' who is a level 499 on " . str_replace("\\'","'",$channel) . ",\n\n";
	$mmsg .= "then click on the following link to confirm (say YES) within SIX HOURS, after that it will be too late :\n\n";
	$mmsg .= "\t" . gen_server_url() . LIVE_LOCATION . "/forms/confirm_mgrchange.php?ID=$crc_cookie\n\n";
	$mmsg .= "if you dont want this request to be processed, just IGNORE this mail and DO NOT click on the above URL.\n\n\n";
	$mmsg .= "The " . NETWORK_NAME . " Channel Service.\n\n";

	$mailres = custom_mail($user->email,"Manager Change Request via WEB",$mmsg,"From: $x_at_email\nReply-To: Dont.Reply@Thank.You\nX-Mailer: " . NETWORK_NAME . " Channel Service");

//	echo "<pre>$mmsg</pre>\n";

	/* make the user can re-post in 10 days. */

	if (!$mailres) { local_seclog("custom_mail() failed for " . $user->email . " from: " . $x_at_email); }

	pg_safe_exec("UPDATE users SET post_forms=(now()::abstime::int4+86400*10) WHERE id='" . $user_id . "'");

	echo "<h2>";
	echo "Please read your email at '$user->email'<br>and click on the link to CONFIRM your request<br>within <b>6 hours</b>\n";
	echo "</h2>\n";
	echo "</body></html>\n\n";
	die;
}
$res=pg_safe_exec("SELECT * FROM users WHERE id=" . $user_id);
$user=pg_fetch_object($res,0);
$res2=pg_safe_exec("SELECT channels.name,channels.id FROM channels,levels WHERE levels.channel_id=channels.id and levels.user_id=" . $user_id . " and levels.access=500 and channels.id>1 and channels.registered_ts>0");
if (pg_numrows($res2)==0) {
        echo("I'm sorry, you don't appear to have any channels registered");
        exit;
}
$multiple_ok=0;
if ($multiple==1) {
	$fc = explode(" ",$forcechannel);
	$forcechannel_C = $fc[1];
	$forcechannel_I = $fc[0];
}
if (pg_numrows($res2)>1 && $multiple==1 && isset($forcechannel) && $forcechannel!="" && preg_match("/^#/",$forcechannel_C) && $forcechannel_I>1) {
	if ($crc == md5($ts . $HTTP_USER_AGENT . $user_id . CRC_SALT_0004)) {
		$multiple_ok=1;
	}
}

if (pg_numrows($res2)>1 && $multiple_ok==0) {
	echo "<h3>\n";
	echo "You appear to have more than one channel registered, in order to fill the following form,<br>\n";
	echo "please select which channel you wish to change the manager on in the list below :<br></h3>\n";
	echo "<form method=POST>\n";
	echo "<input type=hidden name=multiple value=1>\n";
	$zets = time();
	$zecrc = md5($zets . $HTTP_USER_AGENT . $user_id . CRC_SALT_0004);
	echo "<input type=hidden name=ts value=$zets>\n";
	echo "<input type=hidden name=crc value=$zecrc>\n";
	echo "<select name=forcechannel>\n";
	for ($x=0;$x<pg_numrows($res2);$x++) {
		$channel = pg_fetch_object($res2,$x);
		$cname = $channel->name;
		echo "<option value=\"" . $channel->id . " " . str_replace("\"","&quot;",$cname) . "\">$cname</option>\n";
	}
	echo "</select>\n";
	echo "&nbsp;&nbsp;&nbsp;";
	echo "<input type=submit value=\" Go To Form \"><br>\n";
	echo "</form>\n\n</body>\n</html>\n\n";
	exit;
}
$channel=pg_fetch_object($res2,0);

?>
<form method=POST>
<ol>
 <li>Your username: <b><? echo $user->user_name ?></b><input type=hidden name=user_name value=<? echo $user->user_name ?>>
<? if ($multiple_ok == 0) { ?>
 <li>Your channel: <b><? echo str_replace("\\'","'",$channel->name) ?></b><input type=hidden name=channel value=<? echo $channel->name ?>>
<? } ?>
<? if ($multiple_ok == 1) { ?>
 <li>Your channel: <b><? echo str_replace("\\'","'",$forcechannel_C) ?></b><input type=hidden name=channel value=<? echo $forcechannel_C ?>>
<? } ?>
 <li>Verification Question/Answer:<br>
Question :
<?

echo "<b>" . $question_text[$user->question_id] . "</b>";
echo "<input type=hidden name=verifq value=" . $user->question_id . ">\n";



?><br>Answer : <input type=password name=verifdata size=30 maxlength=30>
 <li>This change for a new manager is :
  <ul>
   <li> <input type=radio name=mctype value=1>Temporary (only for a short time)<br>
   Approximate length of time till manager returns: <select name=nbret><option value=0>--</option><option value=3>3</option><option value=4>4</option><option value=5>5</option><option value=6>6</option><option value=7>7</option></select> weeks<input type=hidden name=nbtype value=2><br>
   <li> <input type=radio name=mctype value=2>Permanent (the old manager is giving up the channel 500). <br>
  </ul>
 <li>Reason for manager change :<br>
 <textarea name=mcreason cols=40 rows=5></textarea>
 <li>Username of new manager : <?
	if ($multiple_ok == 0) { $forcechannel_I=$channel->id; }
	$res3 = pg_safe_exec("SELECT users.user_name,users.id FROM users,users_lastseen,levels WHERE users.id=levels.user_id AND levels.channel_id='" . $forcechannel_I . "' AND levels.access=499 AND users_lastseen.user_id=users.id AND users_lastseen.last_seen>(now()::abstime::int4-86400*20) ORDER BY users.user_name");
	if (pg_numrows($res3)==0) {
		echo "<b>No user with level 499 on that channel seen in the last 20 days.</b><br>\n";
		$nouser=1;
	} else {
		// verify new manager is not already 500 elsewhere
		if (isset($linez)) { unset($linez); }
		$q_linez = 0;
		for ($x=0;$x<pg_numrows($res3);$x++) {
			$da_user = pg_fetch_object($res3,$x);
			$testq=pg_safe_exec("SELECT users.id FROM users,levels,channels WHERE users.id='" . $da_user->id . "' AND levels.user_id=users.id AND levels.access=500 AND channels.id=levels.channel_id AND channels.registered_ts>0");
			if (pg_numrows($testq)==0) {
				$linez[$q_linez]="<option value=" . $da_user->user_name . ">" . $da_user->user_name . "</option>\n";$q_linez++;
			} else {
				$linez[$q_linez]="<option value=" . $da_user->user_name . ">" . $da_user->user_name . " (*)</option>\n";$q_linez++;
			}
		}
		if ($q_linez==0) {
			echo "<br><b>The chosen 499 should not already be 500 on a channel<br>None of your 499 is valid for Channel manager change.</b><br><br>\n";
			$nouser=1;
		} else {

			echo "<select name=new_manager>\n";
			echo "<option value=\"\" selected> -- pick a username -- </option>\n";
			for ($x=0;$x<$q_linez;$x++) {
				echo $linez[$x];
			}
			echo "</select><br>\n";
			$nouser=0;
		}
	}

	echo "<b>note</b>: 499 that are already 500 somewhere else (allowed only for temporary changes) are marked with a (*)<br>\n";

	if ($nouser==0) {
		echo "<b>note</b>:&nbsp;If the new manager's username is not listed above, then you need to add him/her as a level 499,<br>\n";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/msg " . BOT_NAME . " adduser ";
		if ($multiple_ok==1) { echo str_replace("\\'","'",$forcechannel_C); } else { echo str_replace("\\'","'",$channel->name); }
		echo " <i>username</i> 499<br>\n";
		echo "Then <a href=\"managerchange.php\">restart this form from the begining</a>.<br>\n";
	} else {
		echo "<b>note</b>:&nbsp;You need to add a username as a level 499,<br>\n";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/msg " . BOT_NAME . " adduser ";
		if ($multiple_ok==1) { echo str_replace("\\'","'",$forcechannel_C); } else { echo str_replace("\\'","'",$channel->name); }
		echo " <i>username</i> 499<br>\n";
		echo "Then <a href=\"managerchange.php\">restart this form from the begining</a>.<br>\n";
	}

?>
</ol>
<?
	if ($nouser==0) {
		echo "<input type=submit value=\" Submit Query \">\n";
	}
	$ts = time();
	$crc = md5($_SERVER["HTTP_USER_AGENT"] . $ts . CRC_SALT_0003);
?>
<input type=hidden name=ts value=<? echo $ts ?>>
<input type=hidden name=crc value=<? echo $crc ?>>
</form>
</body>
</html>
