<?
/* $Id: save_user.php,v 1.20 2005/03/07 08:48:25 nighty Exp $ */

require("../../php_includes/cmaster.inc");
std_init();
$cTheme = get_theme_info();

// Currently reserved for admins only
// when we go fully live this condition can be removed
if ($admin<600 && !acl(XAT_CAN_EDIT) && !acl(XTOTP_DISABLE_OTHERS)) {
	header("Location: users.php?id=$id&r=1&fc=" . md5($id . 1 . CRC_SALT_0013));
	exit;
}

// Admin's/ACLs can't edit people with higher access than themselves (level 1000 can, 1000 can do *anything*)
$isAdminLvl = get_channel_access($database,$id,1);
if ($isAdminLvl>0) { $isAdmin = 1; } else { $isAdmin = 0; }
if ($isAdmin && $isAdminLvl>=$admin && $user_id!=$id && $admin!=1000) {
	header("Location: users.php?id=$id&r=2&fc=" . md5($id . 2 . CRC_SALT_0013));
	exit;
}

// non admin's/ACL can't edit people other than themselves
if ($admin<600 && !acl(XAT_CAN_EDIT) && !acl(XTOTP_DISABLE_OTHERS)&& $user_id!=$id) {
	header("Location: users.php?id=$id&r=3&fc=" . md5($id . 3 . CRC_SALT_0013));
	exit;
}
$id=$id+0;

//activate user_log if an admin or an acl user modifies someone else than him/herself
if (($admin>0 || acl(XAT_CAN_EDIT || acl(XTOTP_DISABLE_OTHERS))) && $user_id!=$id) {
	$log = 1;
} else {
	$log = 0;
}

function set_flag($allowed,&$num,$bit,$bool)
{
  global $database;
  if (!$allowed)
    return;
  switch ($bool) {
    case "off":
	$num=(int)$num&~(int)$bit;
	break;
    case "on":
	$num=(int)$num|(int)$bit;
	break;
    default:
	$num=(int)$num&~(int)$bit;
	break;
  }
}



if ($admin<600 && !acl(XAT_CAN_EDIT) && acl(XTOTP_DISABLE_OTHERS) && !$isAdmin)
{
$res = pg_safe_exec("select * from users where id='$id'");
$row = pg_fetch_object($res,0);

$ras = pg_safe_exec("select user_name from users where id='$user_id'");
$raw = pg_fetch_object($ras,0);
$d_username=$raw->user_name;
$fchanged=0;
$new_flags = $row->flags;

$changed_totp=0;
if(isset($_POST['totp']))
	{
	if ($_POST['totp'] == "off")
		{
		disable_totp($id);
		}
	}
header( "Location: users.php?id=$id&update=1" );
die;
}

// Make sure all the data is escaped properly, and in proper format (IE, an integer)

if( !(preg_match( "/^[A-Za-z0-9_+-.]+@[A-Za-z0-9.-]+\.[A-Za-z][A-Za-z]+$/", $email )) ) {

	std_theme_styles(1); std_theme_body();

	echo( "<p><font color=\"#FF0000\">New e-mail address is invalid.</font>  It must contain a @, it must be from a valid domain, and it can only contain alpha-numeric " );
	echo( "characters (a-zA-Z0-9) or the . or - character.</p>" );
	echo "<a href=\"javascript:history.go(-1);\">Go Back</a>.<br></Body></html>\n";
	die;
}


$test = pg_safe_exec("SELECT * FROM users WHERE lower(email)='" . strtolower($email) . "' AND id!=$id");
if (pg_numrows($test)>0) {
	$uuuo = pg_fetch_object($test,0);
	std_theme_styles(1); std_theme_body();
	echo( "<p><font color=\"#FF0000\">E-mail address '$email' is already owned by user <b><a href=\"users.php?id=" . $uuuo->id . "\">" . $uuuo->user_name . "</a></b>.</font>" );
	echo( "</p>" );
	echo "<a href=\"javascript:history.go(-1);\">Go Back</a>.<br></body></html>\n";
	die;
}


$public_key = prepare_dbtext( $public_key );
$question_id = intval( $question_id );
$language_id = intval( $language_id );
//$verificationdata = prepare_dbtext( $verificationdata );
ignore_user_abort(true);
pg_safe_exec( "BEGIN WORK" );
$need_rollback=1;
$query = "UPDATE users ";
$query .= "SET ";



// Don't allow users to change their own email address
// not even admins.  if you want to change this, please talk to Isomer first.
//
// talked to coords, we decided to remove this test.
//if ($user_id!=$id) {
	$query .= "email='$email', ";
//}
$query .= "url='$url', ";
$query .= "language_id='" . $language_id . "', ";
$query .= "public_key='" . htmlspecialchars($public_key) . "', ";

$res = pg_safe_exec("select * from users where id='$id'");
$row = pg_fetch_object($res,0);

$ras = pg_safe_exec("select user_name from users where id='$user_id'");
$raw = pg_fetch_object($ras,0);
$d_username=$raw->user_name;
$fchanged=0;
$new_flags = $row->flags;


//echo $new_flags."<br>\n";
$logsusp=0;
if ($suspend_user=="yes" && !((int)$row->flags & 0x0001)) {
	if ($user_id==$id) {
		std_theme_styles(1); std_theme_body();
		echo "You cannot suspend yourself .. are you crazy ? heh.<br>\n";
		echo "<a href=\"javascript:history.go(-1);\">Go Back</a>.<br></body></html>\n";
		die;
	}
	if ($suspendreason=="") {
		std_theme_styles(1); std_theme_body();
		echo "You need to supply a reason for the suspension.<br>\n";
		echo "<a href=\"javascript:history.go(-1);\">Go Back</a>.<br></body></html>\n";
		die;
	}
	$fchanged=1;
	//set_flag($admin>=$level_set_suspend,$row->flags,0x0001,"on");
	$new_flags = (int)$new_flags|0x0001;
	$logsusp=1;
}

if ($suspend_user=="no" && ((int)$row->flags & 0x0001)) {
	$fchanged=1;
	//set_flag($admin>=$level_set_suspend,(int)$row->flags,0x0001,"off");
	$new_flags = (int)$new_flags&~0x0001;
	$logsusp=2;
}
$u_username = $row->user_name;
$u_email = $row->email;
//echo $new_flags."<br>\n";
$fraudchg =0;

$lowuser = strtolower($u_username);
$iit = pg_safe_exec("SELECT * FROM noreg WHERE type=4 AND lower(user_name)='$lowuser'");
$fraudt=0;
if (pg_numrows($iit)>0 || ((int)$row->flags & 0x0008)) {
	if ($ufraud==1) { // switch to NO.
		$fraudchg = 1;
		if (pg_numrows($iit)>0) {
			pg_safe_exec("DELETE FROM noreg WHERE type=4 AND lower(user_name)='$lowuser'");
		}
		//set_flag(1,$row->flags,0x0008,"off");
		$new_flags = (int)$new_flags&~0x0008;
		$fchanged=1;
		$fraudt=1;
	}
} else {
	if ($ufraud==2) { // switch to YES.
		if (trim($fraudreason)=="") {
			std_theme_styles(1); std_theme_body();
			echo "You need to supply a valid reason to put a user in Fraud.<br>\n";
			echo "<a href=\"javascript:history.go(-1);\">Go Back</a>.<br>\n";
			echo "</body></html>\n";
			die;
		}
		$set_by=$d_username;
		$fraudchg = 1;
		pg_safe_exec("INSERT INTO noreg (user_name,email,channel_name,type,never_reg,for_review,expire_time,created_ts,set_by,reason) VALUES ('$u_username','$u_email','',4,1,0,0,now()::abstime::int4,'$set_by','$fraudreason')");
		//set_flag(1,$row->flags,0x0008,"on");
		$new_flags = (int)$new_flags|0x0008;
		$fchanged=1;
		$fraudt=2;
	}
}

$unrchg = 0;
$iiunr = pg_safe_exec("SELECT * FROM noreg WHERE type=2 AND never_reg=1 AND lower(user_name)='" .$lowuser ."'");
if (pg_numrows($iiunr)>0 && $unreg == 1) { // switch to 'NO'
	$unrchg = 1;
	pg_safe_exec("DELETE FROM noreg WHERE type=2 AND never_reg=1 AND lower(user_name)='" .$lowuser ."'");
}
if (pg_numrows($iiunr)==0 && $unreg == 2) { // switch to 'YES'
	if (trim($unregreason)=="") {
		std_theme_styles(1); std_theme_body();
		echo "You need to supply a valid reason to put this username in NeverReg.<br>\n";
		echo "<a href=\"javascript:history.go(-1);\">Go Back</a>.<br>\n";
		echo "</body></html>\n";
		die;
	}
	$unrchg = 1;
	$set_by=$d_username;
	pg_safe_exec("INSERT INTO noreg (user_name,email,channel_name,type,never_reg,for_review,expire_time,created_ts,set_by,reason) VALUES ('$u_username','','',2,1,0,0,now()::abstime::int4,'$set_by','$unregreason')");
}

$enrchg = 0;
$lowemail = strtolower($u_email);
$iienr = pg_safe_exec("SELECT * FROM noreg WHERE type=2 AND never_reg=1 AND lower(email)='" .$lowemail ."'");
if (pg_numrows($iienr)>0 && $enreg == 1) { // switch to 'NO'
	$unrchg = 1;
	pg_safe_exec("DELETE FROM noreg WHERE type=2 AND never_reg=1 AND lower(email)='" .$lowemail ."'");
}
if (pg_numrows($iienr)==0 && $enreg == 2) { // switch to 'YES'
	if (trim($enregreason)=="") {
		std_theme_styles(1); std_theme_body();
		echo "You need to supply a valid reason to put this e-mail in NeverReg.<br>\n";
		echo "<a href=\"javascript:history.go(-1);\">Go Back</a>.<br>\n";
		echo "</body></html>\n";
		die;
	}
	$enrchg = 1;
	$set_by=$d_username;
	pg_safe_exec("INSERT INTO noreg (user_name,email,channel_name,type,never_reg,for_review,expire_time,created_ts,set_by,reason) VALUES ('','$u_email','',2,1,0,0,now()::abstime::int4,'$set_by','$enregreason')");
}

//echo $new_flags." / " . $row->flags . " / $noteacc <br>\n";
if (ENABLE_NOTES && ((NOTES_ADMIN_ONLY && $admin>0) || (NOTES_ADMIN_ONLY==0))) {
	$loganote=0;
	if ((int)$row->flags & 0x0010) {
		if ($noteacc==1) {
			//echo "turn off";
			//set_flag(1,(int)$row->flags,0x0010,"off");
			$new_flags = (int)$new_flags&~0x0010;
			$fchanged=1;
			$loganote=1;
		}
	} else {
		if ($noteacc==2) {
			//echo "turn on";
			//set_flag(1,(int)$row->flags,0x0010,"on");
			$new_flags = (int)$new_flags|0x0010;
			$fchanged=1;
			$loganote=1;
		}
	}
}

$lognopurge=0;
//echo "$admin - " . $row->flags . " - " . ((int)$row->flags & 0x0020) . " - $noexpiry<br>\n";
if ($admin>=NOPURGE_USER_EDITLEVEL) {
	if ((int)$row->flags & 0x0020) {
		if ($noexpiry==2) { // turn off idle protection
//			echo "turn off";
			$new_flags = (int)$new_flags&~0x0020;
			$fchanged=1;
			$lognopurge=1;
		}
	} else {
		if ($noexpiry==1) { // turn on idle protection
//			echo "turn on";
			$new_flags = (int)$new_flags|0x0020;
			$fchanged=1;
			$lognopurge=1;
		}
	}
}

$logdauth=0;
//echo "$admin - " . $row->flags . " - " . ((int)$row->flags & 0x0060) . " - $dauth<br>\n";
if ($admin>=800) {
	if ((int)$row->flags & 0x0060) {
		if ($dauth==2) { // turn off
//			echo "turn off";
			$new_flags = (int)$new_flags&~0x0060;
			$fchanged=1;
			$logdauth=1;
		}
	} else {
		if ($dauth==1) { // turn on
//			echo "turn on";
			$new_flags = (int)$new_flags|0x0060;
			$fchanged=1;
			$logdauth=1;
		}
	}
}

$logalumni=0;
//echo "$admin - " . $row->flags . " - " . ((int)$row->flags & 0x0080) . " - $alumni<br>\n";
if ($admin>=800 && $user_id!=$row->id) {
	if ((int)$row->flags & 0x0080) {
		if ($alumni==2) { // turn off
//			echo "turn off";
			$new_flags = (int)$new_flags&~0x0080;
			$fchanged=1;
			$logalumni=1;
		}
	} else {
		if ($alumni==1) { // turn on
//			echo "turn on";
			$new_flags = (int)$new_flags|0x0080;
			$fchanged=1;
			$logalumni=1;
		}
	}
}



$changed_totp=0;
if(isset($_POST['totp']))
	{
	if ($_POST['totp'] == "off")
		{
		disable_totp($id);
		$changed_totp=1;
		}
	}

$changed_timout=0;
//sess_timout
if ($admin >= 800)
{
$user_sess_timeout=get_custom_session($id);
if ($_POST['sess_timout'] != $user_sess_timeout)
	{
	set_custom_session($id, $_POST['sess_timout']);
	$changed_timout=1;
	}
}
$logircop=0;
//echo "$admin - " . $row->flags . " - " . ((int)$row->flags & 0x0100) . " - $ircop<br>\n";
if ($admin>=800) {
	if ((int)$row->flags & 0x0100) {
		if ($ircop==2) { // turn off
//			echo "turn off";
			$new_flags = (int)$new_flags&~0x0100;
			$fchanged=1;
			$logircop=1;
		}
	} else {
		if ($ircop==1) { // turn on
//			echo "turn on";
			$new_flags = (int)$new_flags|0x0100;
			$fchanged=1;
			$logircop=1;
		}
	}
}



//echo $new_flags."<br>\n";
if ($fchanged) {
	$query .= "flags='" . $new_flags . "', ";
}
if ($admin>=800) {
	$query .= "question_id=$question_id, ";
	$query .= "verificationdata='$verificationdata', ";
}
if ($admin>=MOD_MAXLOGINS_LEVEL) {
	if (($maxlogins+0)<=0 || ($maxlogins+0)>MAX_MAXLOGINS) { $maxlogins = 1; }
	$query .= "maxlogins=" . ($maxlogins+0) . ", ";
}
if ($chg_formpost>0 && $chg_formpost<3) {
	$new_pforms = 0;
	if ($chg_formpost==1) {
		$new_pforms = 0;
	}
	if ($chg_formpost==2) {
		$new_pforms = 666;
	}
	$query .= "post_forms=" . $new_pforms . ", ";
}

$query .= "last_updated=now()::abstime::int4, ";
if ($fraudt==2) {
	$query .= "last_updated_by='*** TAGGED AS FRAUD ***' WHERE id=$id";
} else {
	if ($logsusp==1) { // do not show who suspended the user is the "suspension" is turned ON. (*grin*)
		$query .= "last_updated_by='Web Interface (** CService Administrator **)' WHERE id=$id";
	} else {
		$query .= "last_updated_by='Web Interface (" . $d_username . "(" . $user_id . "))' WHERE id=$id";
	}
}
//echo("$query");
$result = pg_safe_exec($query);

//disable_totp($id);

if( $result ) {
	$result = 1;
	if ($logsusp==1) {
		$result = log_user($id,1,"global suspend for %U (%I) - Reason: " . $suspendreason);
	}
	if ($logsusp==2) {
		$result = log_user($id,2,"global unsuspend for %U (%I)");
	}
	if ($log) {
		if ($result) {
			// 3 = EV_ADMINMODIF
			$add_reason = "";
			if ($logsusp==1 || $logsusp==2) { $add_reason .="- Suspension\n"; }
			if ($admin>=800 && $verificationdata!=$row->verificationdata) { $add_reason .="- Verification Answer\n"; }
			if ($admin>=800 && $question_id!=$row->question_id) { $add_reason .="- Verification Question\n"; }
			if ($loganote==1 && ENABLE_NOTES && ((NOTES_ADMIN_ONLY && $admin>0) || (NOTES_ADMIN_ONLY==0))) { $add_reason .="- Notes Acceptation Status\n"; }
			if ($lognopurge==1 && $admin>NOPURGE_USER_EDITLEVEL) { $add_reason .="- NOPURGE_ON_IDLE status\n"; }
			if ($logdauth==1 && $admin>=800) { $add_reason .="- DISABLEAUTH status\n"; }
			if ($logalumni==1 && $admin>=800) { $add_reason .="- ALUMNI status\n"; }
			if ($logircop==1 && $admin>=800) { $add_reason .="- OPER status\n"; }
			if ($email!=$row->email) { $add_reason .="- E-Mail (old: " . $row->email . ",new: " . $email . ")\n"; }
			if ($url!=$row->url) { $add_reason .="- Homepage\n"; }
			if ($language_id!=$row->language_id) { $add_reason .="- Language\n"; }
			if ($fraudchg==1) { $add_reason .="- Fraud User Status\n"; }
			if ($unrchg==1) { $add_reason .="- Username NeverReg Status\n"; }
			if ($enrchg==1) { $add_reason .="- E-Mail NeverReg Status\n"; }
			if (htmlspecialchars($public_key)!=$row->public_key) { $add_reason .="- Public Key\n"; }
			if ($chg_formpost>0) { $add_reason .="- Form Post\n"; }
			if ($admin>=MOD_MAXLOGINS_LEVEL && $maxlogins!=$row->maxlogins) { $add_reason .= "- Maxlogins\n"; }
			if ($changed_totp == 1) { $add_reason .= "- TOTP disabled\n";}
			if ($changed_timout == 1) { $add_reason .= "- Web session timeout\n";}
			if ($add_reason!="") { $add_reason = "Fields modified:\n" . $add_reason; } else { $add_reason = "No fields modified"; }
			$result = log_user($id,3,$add_reason);
		}
	}
	if( $result ) {

		pg_safe_exec( "COMMIT WORK" );
		ignore_user_abort(false);
		header( "Location: users.php?id=$id&update=1" );
		exit;
	}
}

pg_safe_exec( "ROLLBACK WORK" );
ignore_user_abort(false);
//header( "Location: users.php?id=$id&update=2" );
die;
?>
