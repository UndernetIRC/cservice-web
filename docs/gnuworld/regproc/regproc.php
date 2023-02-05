<?php
	require("../../../php_includes/blackhole.inc");
    require("../../../php_includes/cmaster.inc");

	if($loadavg5 >= CRIT_LOADAVG)
	{
		header("Location: ../highload.php");
		exit;
	}

        std_connect();
        $user_id = std_security_chk($auth);
	$cTheme = get_theme_info();
	if ($user_id<=0) {
		std_theme_styles(1); std_theme_body("../");
		echo "You must be logged in to view that page. <a href=\"../index.php\" target=\"_top\">click here</a>.<br>\n";
		echo "</body></html>\n\n";
		die;
	}
        $admin = std_admin();
        if ($admin==0) {
		if (newregs_off()) {
			std_theme_styles(1); std_theme_body("../");
			echo "<center>\n";
			echo "<h2>";
			echo "Sorry, You can't register new channels at the moment.";
			echo "</h2>";
			echo "</center>\n";
			echo "</body></html>\n\n";
			die;
		}
        }
        $already_chan=0;
        $already_pend=0;
        $admin_bypass=0;

	if (!check_secure_form($username.CRC_SALT_0002)) {
               	echo "<html><head><title>REGISTRATION PROCESS</title>";
		std_theme_styles();
		echo "</head>\n";
		std_theme_body("../");
		echo "<b>CHANNEL SERVICE REGISTRATION PROCESS</b><br><hr noshade size=2><br>\n";
		echo "<h2>You must use the form on our page.<br></h2>\n";
		echo "<br>\n";
		echo "Then <a href=\"index.php\">go there</a> and post the form.<br>\n";
		echo "</body></html>\n\n";
		die;
	}

	$rsts = pg_safe_exec("SELECT signup_ts FROM users WHERE id=" . (int)$user_id);
	$rsto = pg_fetch_object($rsts);
	if ((int)$rsto->signup_ts>0) {
		$now = time();
		$days_elapsed = (int)((int)($now - (int)$rsto->signup_ts)/86400);
		if ($days_elapsed < MIN_DAYS_BEFORE_REG) {
               		echo "<html><head><title>REGISTRATION PROCESS</title>";
			std_theme_styles();
			echo "</head>\n";
			std_theme_body("../");
			echo "<b>CHANNEL SERVICE REGISTRATION PROCESS</b><br><hr noshade size=2><br>\n";
			echo "<h2>Your username must have been created since at least " . MIN_DAYS_BEFORE_REG . " days in order to apply for a new channel.<br></h2>\n";
			echo "<br>\n";
			echo "<a href=\"index.php\">Go back</a><br>\n";
			echo "</body></html>\n\n";
			die;
		}
	}

	if (REGPROC_IDLECHECK && is_irc_idled($user_id,21) && !file_exists("../testnet")) {
               	echo "<html><head><title>REGISTRATION PROCESS</title>";
		std_theme_styles();
		echo "</head>\n";
		std_theme_body("../");
		echo "<b>CHANNEL SERVICE REGISTRATION PROCESS</b><br><hr noshade size=2><br>\n";
		echo "<h2>You must login to X on IRC to apply to register a channel.<br></h2>\n";
		echo "<br>\n";
		echo "Then <a href=\"javascript:history.go(-1);\">go back</a> and repost the form.<br>\n";
		echo "</body></html>\n\n";
		die;
	}

	$ress = pg_safe_exec("SELECT email FROM users WHERE id='$user_id'");
	$rooo = pg_fetch_object($ress,0);
	if (is_email_locked($LOCK_REGPROC,$rooo->email)) {
                	echo "<html><head><title>REGISTRATION PROCESS</title>";
			std_theme_styles();
			echo "</head>\n";
			std_theme_body("../");
			echo "Sorry, you can't register a channel using your current e-mail address.<br>\n";
			echo "You can request a modification of your email-in-record by <a href=\"../forms/emailchange.php\">clicking here</a>.";
			echo "</body></html>\n\n";
			die;
	}

        if (!REGPROC_ALLOWMULTIPLE) {
	        $rrr = pg_safe_exec("SELECT * FROM levels,channels WHERE channels.id=levels.channel_id AND channels.registered_ts>0 AND levels.user_id='$user_id' AND levels.access='500'");
		        if (pg_numrows($rrr)>0 && $roo = pg_fetch_object($rrr,0)) {
        	        $already_chan=1;
	                if ($admin >= 0) { // admin bypass
	                        $admin_bypass=1;
	                }  else {
	                	echo "<html><head><title>REGISTRATION PROCESS</title>";
				std_theme_styles();
				echo "</head>\n";
				std_theme_body("../");
				echo "Sorry, you already have a channel registered to you.<br>\n";
				echo "You can only register <b>ONE</b> channel.";
				echo "</body></html>\n\n";
				die;
	                }
	        }
        }
        $res = pg_safe_exec("SELECT * FROM pending WHERE status<3 AND manager_id='" . $user_id . "'");
        if (pg_numrows($res)>0) {
                $already_pend=1;
                if ($admin >= 800) { // admin bypass (high level)
                        $admin_bypass=1;
                } else {
                	echo "<html><head><title>REGISTRATION PROCESS</title>";
			std_theme_styles();
			echo "</head>\n";
			std_theme_body("../");
			echo "Sorry, you already have a channel pending registration to you.<br>\n";
			echo "You can only register <b>ONE</b> channel.";
			echo "</body></html>\n\n";
			die;
                }
        }

        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
        $row = pg_fetch_object($res,0);
        $user_name = $row->user_name;


echo "<html><head><title>REGISTRATION PROCESS</title>\n";
?>
<script language="JavaScript1.2">
<!--
function reg_form() {
	document.forms[0].submit();
}
//-->
</script>
<?
std_theme_styles();
echo "</head>\n";
std_theme_body("../");


echo "<b>CHANNEL SERVICE REGISTRATION PROCESS</b> - CONFIRMATION<br><hr noshade size=2><br>\n";

/* below is a fully hidden form to avoid retyping all if you do a mistake and must go back to the form */
echo "<form name=back action=index.php method=post>\n";
echo "<input type=hidden name=aup value=$aup>\n";
echo "<input type=hidden name=ii_channelname value=\"" . urlencode($channel_name) . "\">\n";
echo "<input type=hidden name=ii_description value=\"" . urlencode($description) . "\">\n";
$suptest="ok";
for ($x=0;$x<REQUIRED_SUPPORTERS;$x++) {
	$newvar = "supporter" . $x;
	echo "<input type=hidden name=ii_supporter" . $x . " value=\"" . $$newvar . "\">\n";
	if ($$newvar=="") { $suptest="failed"; }
}
echo "</form>\n";

if ($username=="" || $channel_name=="" || $aup!=1 || $description=="" || strlen($description)>300 || $suptest=="failed") {
	echo "<b>Invalid arguments</b><br><br>\n";

	echo "(*) DEBUG INFO : ";
	if ($username=="") { echo "UN "; }
	if ($channel_name=="") { echo "CN "; }
	if ($aup!=1) { echo "AUP "; }
	if ($description=="") { echo "DE "; }
	if (strlen($description)>300) { echo "DE+ "; }
	if (REQUIRED_SUPPORTERS>0) {
		if ($supporter0=="") { echo "S0 "; }
		if (REQUIRED_SUPPORTERS>1) {
			if ($supporter1=="") { echo "S1 "; }
			if (REQUIRED_SUPPORTERS>2) {
				if ($supporter2=="") { echo "S2 "; }
				if (REQUIRED_SUPPORTERS>3) {
					if ($supporter3=="") { echo "S3 "; }
					if (REQUIRED_SUPPORTERS>4) {
						if ($supporter4=="") { echo "S4 "; }
						if (REQUIRED_SUPPORTERS>5) {
							if ($supporter5=="") { echo "S5 "; }
							if (REQUIRED_SUPPORTERS>6) {
								if ($supporter6=="") { echo "S6 "; }
								if (REQUIRED_SUPPORTERS>7) {
									if ($supporter7=="") { echo "S7 "; }
									if (REQUIRED_SUPPORTERS>8) {
										if ($supporter8=="") { echo "S8 "; }
										if (REQUIRED_SUPPORTERS>9) {
											if ($supporter9=="") { echo "S9 "; }
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
	echo "<br><br>\n";

	echo "<a href=\"./\">click here</a>\n";
	echo "</body></html>\n";
	die;
}

$channel_nameF=trim(str_replace("\\\"","\"",str_replace("\\\\","\\",$channel_name)));
$channel_name=strtolower($channel_nameF);
$username=strtolower($username);
$description=str_replace("\\&quot;","&quot;",str_replace("\n","<br>",htmlspecialchars($description)));
for ($x=0;$x<REQUIRED_SUPPORTERS;$x++) {
	$newvar = "supporter" . $x;
	$supporters[$x]=strtolower($$newvar);
}
unset($control_chars);
$control_chars = "";
for ($x=1;$x<33;$x++) { // everything below and including 'space' is not acceptable for ircu.
	$control_chars .= chr($x);
}
if ( strcspn($channel_nameF,$control_chars)!=strlen($channel_nameF) || preg_match("/,/",$channel_nameF) || preg_match("@",$channel_nameF) ) {
	echo "The channel name can't contain any <b>space</b>, <b>null</b> , <b>comma (,)</b>, <b>@</b> or <b>control chars</b>.<br>\n";
	echo "<a href=\"javascript:reg_form();\">Correct your entry</a>.\n";
	echo "</body></html>\n\n";
	die;
}

$dlist[0]="warez";
$dlist[1]="vivo";
$dlist[2]="password";
$dlist[3]="mp3";
$count=count($dlist);
for ($x=0;$x<$count;$x++) {
	if ( preg_match("/" . strtolower($dlist[$x]) . "/",strtolower($channel_nameF)) ) {
		echo "CService does not allow registration of channels involved in illegal activities, such as warez, mp3s, vivos, passwords...<br>\n";
		echo "<a href=\"javascript:reg_form();\">Correct your entry</a>.\n";
		echo "</body></html>\n\n";
		die;
	}
}


//if ( !preg_match("^#.", $channel_nameF) ) {

if (isset($allow)) { unset($allow); }
$allow = 1;
$x = ord(substr($channel_nameF,1,1));
if ($x<48) { $allow=0; }
if ($x<65 && $x>57) { $allow=0; }
if ($x<97 && $x>90) { $allow=0; }
if ($x<192 && $x>122) { $allow=0; }
if ($x==208 ||
    $x==215 ||
    $x==240 ||
    $x==247
   ) { $allow=0; }
if ($x==138 ||
    $x==140 ||
    $x==154 ||
    $x==156 ||
    $x==159
   ) { $allow=1; }

//if (!preg_match("/^#[A-Za-z0-9].*$/",$channel_nameF)) {
if (substr($channel_nameF,0,1)!="#" || $allow==0) {
	echo "The channel name <b>MUST</b> start with a <b>#</b> followed by a letter or a number.<br>\n";
	echo "<a href=\"javascript:reg_form();\">Correct your entry</a>.\n";
	echo "</body></html>\n\n";
	die;
}

if (strtolower($channel_nameF) != C_strtolower($channel_nameF)) {
	echo "The channel name contains disallowed for now chars.<br>\n";
	echo "This block is temporary but no one in " . SERVICE_CHANNEL . " can change this.<br>\n";
	echo "This will be reviewed as soon as possible.... we are sorry for the inconvenience.<br><br>\n";
	echo "<a href=\"javascript:reg_form();\">Correct your entry</a>.\n";
	echo "</body></html>\n\n";
	die;
}

$check_user=0;
for ($x=0;$x<REQUIRED_SUPPORTERS;$x++) {
	if ($username==$supporters[$x]) { $check_user=1; }
}
if ($check_user) {
	echo "You must <b>NOT</b> include yourself as a supporter.<br>\n";
	echo "<a href=\"javascript:reg_form();\">Correct your entry</a>.\n";
	echo "</body></html>\n\n";
	die;
}

$check_dups=0;
for ($x=0;$x<REQUIRED_SUPPORTERS;$x++) {
	for ($y=0;$y<REQUIRED_SUPPORTERS;$y++) {
		if ($supporters[$x]==$supporters[$y] && $x!=$y) { $check_dups=1; }
	}
}
if ($check_dups) {

	echo "You have duplicate usernames in the supporter list<br>\n";
	echo "You must enter " . REQUIRED_SUPPORTERS . " <b>different</b> usernames.<br><br>\n";
	echo "<a href=\"javascript:reg_form();\">Correct your entry</a>.\n";
	echo "</body></html>\n\n";
	die;
}

$check_invalid=0;
for ($x=0;$x<REQUIRED_SUPPORTERS;$x++) {
	$res = pg_safe_exec("SELECT id FROM users WHERE lower(user_name)='" . strtolower($supporters[$x]) . "'");
	if (pg_numrows($res)==0) { $check_invalid=1; }
}
if ($check_invalid) {
	echo "All your supporters must be valid CService usernames.<br>\n";
	echo "Some supporters were found as invalid CService usernames.<br>\n";
	echo "List of invalid supporters :<br>\n";
	for ($x=0;$x<REQUIRED_SUPPORTERS;$x++) {
		$res = pg_safe_exec("SELECT id,user_name FROM users WHERE lower(user_name)='" . strtolower($supporters[$x]) . "'");
		if (pg_numrows($res)<1) {
			echo "<li> " . $supporters[$x] . "\n";
			echo "<script language=\"JavaScript1.2\">\n";
			echo "<!--\n";
			echo "document.forms[0].ii_supporter$x.value='* '+document.forms[0].ii_supporter$x.value;\n";
			echo "//-->\n";
			echo "</script>\n";
		}
	}
	echo "<br><br><a href=\"javascript:reg_form();\">Correct your entry</a>.\n";
	echo "</body></html>\n\n";
	die;
}

$check_invalid=0; unset($too_new_ids);
for ($x=0;$x<REQUIRED_SUPPORTERS;$x++) {
	$res = pg_safe_exec("SELECT id,signup_ts FROM users WHERE lower(user_name)='" . strtolower($supporters[$x]) . "'");
	$now = time();
	if ($row = pg_fetch_object($res)) {
		$days_elapsed = (int)((int)($now - (int)$row->signup_ts)/86400);
		if ($days_elapsed < MIN_DAYS_BEFORE_SUPPORT) {
			$check_invalid = 1;
			$too_new_ids[] = $x;
		}
	}
}
if ($check_invalid) {
	echo "<h2>One or more of your supporters are too newly created (less than " . MIN_DAYS_BEFORE_SUPPORT . " days) to be supporters.<br></h2>\n";
	echo "<br>\n";
	echo "List of invalid supporters :<br>\n";
	for ($x=0;$x<count($too_new_ids);$x++) {
		echo "<li> " . $supporters[$too_new_ids[$x]] . "\n";
		echo "<script language=\"JavaScript1.2\">\n";
		echo "<!--\n";
		echo "document.forms[0].ii_supporter" . $too_new_ids[$x] . ".value='* '+document.forms[0].ii_supporter" . $too_new_ids[$x] . ".value;\n";
		echo "//-->\n";
		echo "</script>\n";
	}
	echo "<br><br><a href=\"javascript:reg_form();\">Correct your entry</a>.\n";
	echo "</body></html>\n\n";
	die;
}


$check_invalid=0;$s_index=0;
for ($x=0;$x<REQUIRED_SUPPORTERS;$x++) {
	$res = pg_safe_exec("SELECT * FROM noreg WHERE lower(user_name)='" . strtolower($supporters[$x]) . "'");
	if (pg_numrows($res)>0) {
		$check_invalid=1;
		$noreg_supids[$s_index]=$x;
		$s_index++;
	}
}
if ($check_invalid) {
	echo "<h2>One or more of your supporters are in NOREG.<br></h2>\n";
	echo "<br>\n";
	echo "List of invalid supporters :<br>\n";
	for ($x=0;$x<count($noreg_supids);$x++) {
		echo "<li> " . $supporters[$noreg_supids[$x]] . "\n";
		echo "<script language=\"JavaScript1.2\">\n";
		echo "<!--\n";
		echo "document.forms[0].ii_supporter" . $noreg_supids[$x] . ".value='* '+document.forms[0].ii_supporter" . $noreg_supids[$x] . ".value;\n";
		echo "//-->\n";
		echo "</script>\n";
	}
	echo "<br><br><a href=\"javascript:reg_form();\">Correct your entry</a>.\n";
	echo "</body></html>\n\n";
	die;
}

$check_invalid=0;$s_index=0;
for ($x=0;$x<REQUIRED_SUPPORTERS;$x++) { // second check for FRAUD flags.
	$res = pg_safe_exec("SELECT flags FROM users WHERE lower(user_name)='" . strtolower($supporters[$x]) . "'");
	if (pg_numrows($res)>0) {
		$row = pg_fetch_object($res,0);
		if ((int)$row->flags & 0x0008) {
			$check_invalid=1;
			$noreg_supids[$s_index]=$x;
			$s_index++;
		}
	}
}
if ($check_invalid) {
	echo "<h2>One or more of your supporters are in NOREG.<br></h2>\n";
	echo "<br>\n";
	echo "List of invalid supporters :<br>\n";
	for ($x=0;$x<count($noreg_supids);$x++) {
		echo "<li> " . $supporters[$noreg_supids[$x]] . "\n";
		echo "<script language=\"JavaScript1.2\">\n";
		echo "<!--\n";
		echo "document.forms[0].ii_supporter" . $noreg_supids[$x] . ".value='* '+document.forms[0].ii_supporter" . $noreg_supids[$x] . ".value;\n";
		echo "//-->\n";
		echo "</script>\n";
	}
	echo "<br><br><a href=\"javascript:reg_form();\">Correct your entry</a>.\n";
	echo "</body></html>\n\n";
	die;
}



if (REGPROC_IDLECHECK) {
	$check_invalid=0;$s_index=0;
	for ($x=0;$x<REQUIRED_SUPPORTERS;$x++) {
		//$res = pg_safe_exec("SELECT users.id AS id FROM users,users_lastseen WHERE users.id=users_lastseen.user_id AND users_lastseen.last_seen<(date_part('epoch', CURRENT_TIMESTAMP)::int-86400*21) AND lower(users.user_name)='" . strtolower($supporters[$x]) . "'");
		//if (pg_numrows($res)>0) {
		$res = pg_safe_exec("SELECT id FROM users WHERE lower(user_name)='" . strtolower($supporters[$x]) . "'");
		$row = pg_fetch_object($res,0);
		if (is_irc_idled($row->id,21)) {
			$check_invalid=1;
			$idled_supids[$s_index]=$x;
			$s_index++;
		}
	}
	if ($check_invalid && !file_exists("../testnet")) {
		echo "<h2>All your supporters must login to " . BOT_NAME . " on IRC to apply to register a channel.<br><br>Have all your supporters login to " . BOT_NAME . ", then try again posting the form.</h2>\n";
		echo "<br>\n";
		echo "<br><br><a href=\"javascript:reg_form();\">Go back to form</a>.\n";
		echo "</body></html>\n\n";
		die;
	}
}

$check_invalid=0;$s_index=0;
for ($x=0;$x<REQUIRED_SUPPORTERS;$x++) {
	$res = pg_safe_exec("SELECT email FROM users WHERE lower(users.user_name)='" . strtolower($supporters[$x]) . "'");
	$row = pg_fetch_object($res,0);
	$email = strtolower($row->email);
	if (is_email_locked($LOCK_REGPROC,$email)) {
		$check_invalid=1;
		$badmail_supids[$s_index]=$x;
		$s_index++;
	}
}
if ($check_invalid) {
	echo "<h2>One or more of your supporters is using an invalid email address.<br></h2>\n";
	echo "<br>\n";
	echo "List of invalid supporters :<br>\n";
	for ($x=0;$x<count($badmail_supids);$x++) {
		echo "<li> " . $supporters[$badmail_supids[$x]] . "\n";
		echo "<script language=\"JavaScript1.2\">\n";
		echo "<!--\n";
		echo "document.forms[0].ii_supporter" . $badmail_supids[$x] . ".value='* '+document.forms[0].ii_supporter" . $badmail_supids[$x] . ".value;\n";
		echo "//-->\n";
		echo "</script>\n";
	}
	echo "<br><br><a href=\"javascript:reg_form();\">Correct your entry</a>.\n";
	echo "</body></html>\n\n";
	die;
}



/* checks if supporters are not already supporting more than '$max_chans' channels. */
unset($check_invalid); unset($max_chans);
$check_invalid=0;

if (isset($invalid_supps)) { unset($invalid_supps); }
$idx = 0;

for ($x=0;$x<REQUIRED_SUPPORTERS;$x++) {
	$res = pg_safe_exec("SELECT COUNT(*) AS s_count FROM users,channels,supporters,pending WHERE lower(users.user_name)='" . strtolower($supporters[$x]) . "' AND pending.channel_id=supporters.channel_id AND users.id=supporters.user_id AND (pending.status<3 OR pending.status=8) AND channels.id=supporters.channel_id AND channels.registered_ts=0");
	$obj = pg_fetch_object($res,0);
	$s_count = $obj->s_count;
	if ($s_count>=MAX_CONCURRENT_SUPPORTS) { $check_invalid=1; $invalid_supps[$idx]=$x; $idx++; }
}
if ($check_invalid) {
	echo "All your supporters must be supporting no more than " . MAX_CONCURRENT_SUPPORTS . " channels.<br>\n";
	echo "Some supporters were found as being put as supporter on too many channels.<br>\n";
	echo "List of invalid supporters :<br>\n";
	for ($x=0;$x<count($invalid_supps);$x++) {
		echo "<li> " . $supporters[$invalid_supps[$x]] . "\n";
		echo "<script language=\"JavaScript1.2\">\n";
		echo "<!--\n";
		echo "document.forms[0].ii_supporter" . $invalid_supps[$x] . ".value='* '+document.forms[0].ii_supporter" . $invalid_supps[$x] . ".value;\n";
		echo "//-->\n";
		echo "</script>\n";
	}
	echo "<br><br><a href=\"javascript:reg_form();\">Correct your entry</a>.\n";
	echo "</body></html>\n\n";
	die;
}

/* Force expired NOREG clean up */
pg_safe_exec("DELETE FROM noreg WHERE never_reg='0' AND for_review='0' AND expire_time<" . time());


$res = pg_safe_exec("SELECT * FROM noreg WHERE lower(user_name)='" . strtolower($username) . "'");

if (pg_numrows($res)>0) {
	$row = pg_fetch_object($res,0);
	$type = $row->type;
	if ($type==0) { $noreg_type="<b>&lt;NO REASON&gt;</b>"; }
	if ($type==1) { $noreg_type="<b>Non Support</b>"; }
	if ($type==2) { $noreg_type="<b>Abuse</b>"; }
	if ($type>=3) { $noreg_type="<b>" . $row->reason . "</b>"; }

	if ($row->never_reg==1) { $exp_str = "will <b>never</b> expire"; }
	if ($row->never_reg==0) { $exp_str = "will expire on <b>" . cs_time($row->expire_time) . "</b>"; }

	echo "You can't register any channel at the moment (NOREG)<br>\nReason: $noreg_type.<br>\nThis restriction $exp_str.<br>\n";
	echo "<a href=\"../main.php\" target=\"_top\">Back to main</a>.\n";
	echo "</body></html>\n\n";
	die;
} else {

	$ras = pg_safe_exec("SELECT flags FROM users WHERE lower(user_name)='" . strtolower($username) . "'");
	$ros = pg_fetch_object($ras,0);
	if ((int)$ros->flags & 0x0008) {
		echo "You can't register any channel at the moment (NOREG)<br>\nReason: Fraud User.<br>\nThis restriction will never expire.<br>\n";
		echo "<a href=\"../main.php\" target=\"_top\">Back to main</a>.\n";
		echo "</body></html>\n\n";
		die;
	}
}

//$res = pg_safe_exec("SELECT * FROM noreg WHERE lower(channel_name)='" . str_replace("%","\%",$channel_name) . "'");
$res = pg_safe_exec("SELECT * FROM noreg WHERE lower(channel_name)='" . $channel_name . "'");

if (pg_numrows($res)>0) {
	$row = pg_fetch_object($res,0);
	$type = $row->type;
	if ($type==0) { $noreg_type="<b>&lt;NO REASON&gt;</b>"; }
	if ($type==1) { $noreg_type="<b>Non Support</b>"; }
	if ($type==2) { $noreg_type="<b>Abuse</b>"; }
	if ($type==3) { $noreg_type="<b>" . $row->reason . "</b>"; }

	if ($row->never_reg==1) { $exp_str = "will <b>never</b> expire"; }
	if ($row->never_reg==0) { $exp_str = "will expire on <b>" . cs_time($row->expire_time) . "</b>"; }

	echo "The channel <b>$channel_name</b> is NON REGISTRABLE (NOREG)<br>\nReason: $noreg_type.<br>\nThis restriction $exp_str.<br>\n";
	echo "<a href=\"javascript:reg_form();\">Correct your entry</a>.\n";
	echo "</body></html>\n\n";
	die;
}

$res = pg_safe_exec("SELECT id FROM channels WHERE lower(name)='$channel_name' AND registered_ts>0");

if (pg_numrows($res)>0) {
	echo "The channel <b>$channel_name</b> is already registered by someone else.<br>\n";
	echo "<a href=\"javascript:reg_form();\">Correct your entry</a>.\n";
	echo "</body></html>\n\n";
	die;
}

$res = pg_safe_exec("SELECT pending.created_ts FROM pending,channels WHERE lower(channels.name)='$channel_name' AND pending.channel_id=channels.id AND (pending.status<3 OR pending.status=8) AND channels.registered_ts=0");

if (pg_numrows($res)>0) {
	$roo = pg_fetch_object($res,0);
	echo "The channel <b>$channel_nameF</b> is currently being registered by someone else.<br>\n";
	echo "Since : " . cs_time($roo->created_ts) . "<br>\n";
	echo "<a href=\"javascript:reg_form();\">Correct your entry</a>.\n";
	echo "</body></html>\n\n";
	die;
}

// check time
/*




*/


$res = pg_safe_exec("SELECT id FROM channels WHERE lower(name)='$channel_name' AND registered_ts=0");
if (pg_numrows($res)>0) {
	// channel already exists with _ts=0 (ie. PURGED), wipe this record to place the new one.
	$roo = pg_fetch_object($res,0);
	$cc_id = $roo->id;
	pg_safe_exec("DELETE FROM pending WHERE channel_id='$cc_id'");
	pg_safe_exec("DELETE FROM objections WHERE channel_id='$cc_id'");
	pg_safe_exec("DELETE FROM supporters WHERE channel_id='$cc_id'");
	pg_safe_exec("DELETE FROM levels WHERE channel_id='$cc_id'");
	pg_safe_exec("DELETE FROM bans WHERE channel_id='$cc_id'");
	pg_safe_exec("DELETE FROM pending_traffic WHERE channel_id='$cc_id'");
	log_channel($cc_id,15,"New Incoming Application");
	$reusechan=1;
} else {
	$reusechan=0;
}


/* START OF WRITING APPLICATION PROCESS */
$need_rollback=1;
ignore_user_abort(true);
pg_safe_exec("BEGIN WORK");

$curr_ts=time();
$channel_id=0;$manager_id=0;
$res = pg_safe_exec("SELECT id FROM users WHERE lower(user_name)='" . strtolower($username) . "'");
$row = pg_fetch_object($res,0);
if ($row->id >0) { $manager_id=$row->id; }
if ($manager_id != $user_id) { die("Nice try! he he...."); }




if ($reusechan) {
	$channel_id=$cc_id;
	if (REQUIRED_SUPPORTERS>0) {
		$channels_q = "UPDATE channels SET name='$channel_nameF',mass_deop_pro=0,flood_pro=0,flags=0,limit_offset=3,limit_period=20,limit_grace=1,limit_max=0,userflags=0,url='',description='',keywords='',registered_ts=0,channel_ts=0,channel_mode='',comment='',last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int WHERE id='$channel_id'";
	} else {
		$channels_q = "UPDATE channels SET name='$channel_nameF',mass_deop_pro=0,flood_pro=0,flags=0,limit_offset=3,limit_period=20,limit_grace=1,limit_max=0,userflags=0,url='',description='',keywords='',registered_ts=date_part('epoch', CURRENT_TIMESTAMP)::int,channel_ts=0,channel_mode='',comment='',last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int WHERE id='$channel_id'";
	}
	$lastreq = pg_safe_exec($channels_q);
} else {
	if (REQUIRED_SUPPORTERS>0) {
		$channels_q = "INSERT INTO channels (name,url,description,keywords,registered_ts,channel_ts,channel_mode,comment,last_updated,mass_deop_pro,flood_pro,flags,limit_offset,limit_period,limit_grace,limit_max,userflags) VALUES ('$channel_nameF','','','',0,0,'','',date_part('epoch', CURRENT_TIMESTAMP)::int,0,0,0,3,20,1,0,0)";
	} else {
		$channels_q = "INSERT INTO channels (name,url,description,keywords,registered_ts,channel_ts,channel_mode,comment,last_updated,mass_deop_pro,flood_pro,flags,limit_offset,limit_period,limit_grace,limit_max,userflags) VALUES ('$channel_nameF','','','',date_part('epoch', CURRENT_TIMESTAMP)::int,0,'','',date_part('epoch', CURRENT_TIMESTAMP)::int,0,0,0,3,20,1,0,0)";
	}
	$lastreq = pg_safe_exec($channels_q);
	$res = pg_safe_exec("SELECT id FROM channels WHERE name='$channel_nameF'");
	$row = pg_fetch_object($res,0);
	if ($row->id >0) { $channel_id=$row->id; }
	log_channel($channel_id,15,"New Incoming Application");
}

if (!$lastreq) {
	pg_safe_exec("ROLLBACK WORK");
	die("Fatal Error while processing application (upd/ins_chan): <a href=\"javascript:history.go(-1);\">click here</a>.\n");
}

if (REQUIRED_SUPPORTERS>0) {
	$pending_q = "INSERT INTO pending (channel_id,manager_id,created_ts,decision_ts,decision,comments,description,last_updated,reg_acknowledged,check_start_ts) VALUES ($channel_id,$manager_id,date_part('epoch', CURRENT_TIMESTAMP)::int,0,'','','$description',date_part('epoch', CURRENT_TIMESTAMP)::int,'N',0)";
} else {
	$pending_q = "INSERT INTO pending (channel_id,manager_id,created_ts,decision_ts,decision,comments,description,last_updated,reg_acknowledged,check_start_ts,status) VALUES ($channel_id,$manager_id,date_part('epoch', CURRENT_TIMESTAMP)::int,date_part('epoch', CURRENT_TIMESTAMP)::int,'** INSTANT REGISTRATION **','','$description',date_part('epoch', CURRENT_TIMESTAMP)::int,'Y',0,3)";
}
$lastreq = pg_safe_exec($pending_q);

if (!$lastreq) {
	pg_safe_exec("ROLLBACK WORK");
	die("Fatal Error while processing application (ins_pend): <a href=\"javascript:history.go(-1);\">click here</a>.\n");
}

if (count($supporters)!=REQUIRED_SUPPORTERS) {
	pg_safe_exec("ROLLBACK WORK");
	die("Fatal Error while processing application (nb_supp) : <a href=\"javascript:history.go(-1);\">click here</a>.\n");
}

if (REQUIRED_SUPPORTERS>0) {
	for ($x=0;$x<REQUIRED_SUPPORTERS;$x++) {
		$res = pg_safe_exec("SELECT id FROM users WHERE lower(user_name)='" . strtolower($supporters[$x]) . "'");
		$row = pg_fetch_object($res,0);
		$sup_id = $row->id;
		$supporters_q[$x] = "INSERT INTO supporters (channel_id,user_id,reason,last_updated) VALUES ($channel_id,$sup_id,'',date_part('epoch', CURRENT_TIMESTAMP)::int)";
		pg_safe_exec($supporters_q[$x]);
	}
} else {
	// INSTANT REGISTRATION :: Finish registering the channel properly (ie. add the manager)
	pg_safe_exec("UPDATE channels SET registered_ts=date_part('epoch', CURRENT_TIMESTAMP)::int,last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,comment='' WHERE id='$channel_id'");
	pg_safe_exec("INSERT INTO levels (channel_id,user_id,access,added,added_by,last_modif,last_modif_by,last_updated) VALUES ($channel_id,$manager_id,500,date_part('epoch', CURRENT_TIMESTAMP)::int,'*** REGPROC ***',date_part('epoch', CURRENT_TIMESTAMP)::int,'*** REGPROC ***',date_part('epoch', CURRENT_TIMESTAMP)::int)");
	pg_safe_exec("UPDATE users_lastseen SET last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,last_seen=date_part('epoch', CURRENT_TIMESTAMP)::int WHERE user_id='$manager_id'");

//	pg_safe_exec("UPDATE pending SET status=3,last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,decision_ts=date_part('epoch', CURRENT_TIMESTAMP)::int,decision='** INSTANT REGISTRATION **' WHERE channel_id='$channel_id' AND created_ts=''");
}

pg_safe_exec("COMMIT WORK");

if (REQUIRED_SUPPORTERS>0) {
	echo "<h2>";
	echo "Your application has been recorded.<br>\n";
	echo "Please allow 5-7 days for processing<br><br>\n";
	echo "</h2>";
} else {
	echo "<h2>";
	echo "Channel <b>" . $channel_nameF . "</b> has been registered to you.<br>\n";
	echo "Congratulations !<br><br>\n";
	echo "</h2>";
	echo "<b>Please allow at least about 10 minutes for " . BOT_NAME . " to refresh its online database.</b>";
	echo "<br><br>";
}


echo "<a href=\"../main.php\" target=\"body\">Back to the main menu</a><br>\n";

ignore_user_abort(false);
?>
</body>
</html>
