<?
/* $Id: toast_this.php,v 1.14 2005/03/07 14:10:51 nighty Exp $ */

	$debug_me = 0; // set to 1 to enable debug messages (no actions).
	$use_redir = 0; // Use a HTTP/302 redirection (0: use a JavaScript redirection : fixes some issues with HTTP timeouts on 'no data')
	ignore_user_abort(true);
	unset($min_lvl);
	$min_lvl=800;

	$noreg_username = 0; // 1 = noreg user as with email, 0 = noreg only email, both for period below.
	$days_noreg_with_del = 7; // <1 = forever

	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = std_security_chk($auth);
        $admin = std_admin();
	$cTheme = get_theme_info();
	unset($can_toast);
	$can_toast = 0;
	if (acl(XWEBUSR_TOASTER)) { $can_toast = 1; }
	if (acl(XWEBUSR_TOASTER_RDONLY)) { $can_toast = 0; }
	if ($admin>=$min_lvl) { $can_toast = 1; }

	if ($can_toast==0) {
		echo "<html><head><title>User Toaster - Access Error</title>";
		std_theme_styles();
		echo "</head>";
		std_theme_body("../");
		echo "<b>User Toaster</b> (Hunting Fraud Usernames) ";
		echo "<h1>Sorry, only " . $min_lvl . "+/ACL can toast usernames !</h1>\n";
		echo "<br><br><a href=\"javascript:history.go(-1);\">Back</a>\n";
		echo "</body></html>\n\n";
		die;
	}
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
        if (pg_numrows($res)==0) {
        	echo "Suddenly logged out ?!";
        	die;
        }
        $adm_usr = pg_fetch_object($res,0);
        $adm_user = $adm_usr->user_name;
        if ($admin<$min_lvl && !acl(XWEBAXS_3) && !acl(XWEBUSR_TOASTER)) {
        	echo "Sorry, your admin access is too low.";
        	die;
        }
$gcount = count($id);
$mmsg = ""; $mail_lines=0;
if (preg_match("/^[A-Za-z0-9\._-]+\@[A-Za-z0-9\._-]+\.[A-Za-z][A-Za-z]+$/",$_POST["sendlist"])) { $send_mail = 1; } else { $send_mail = 0; }

if ($debug_me) { echo "<pre><b>DEBUG MODE</b>\n\n"; }
if ($debug_me && $send_mail) { echo "(EMAIL REPORT ACTIVE to : " . $_POST["sendlist"] . ")\n\n"; }
if ($send_mail) { $mmsg .= "USER TOASTER REPORT\n\nAll the listed usernames below have been SUSPENDED by " . $adm_user . " on the User Toaster\n\n"; }
if ($send_mail) {

	$PT_types = Array(1=>"User Name",2=>"E-mail addy",3=>"Signup IP",4=>"Verification answer");
	$OR_types = Array(1=>"User Name",3=>"Creation date",2=>"E-mail addy",4=>"Verification answer",5=>"User ID (reverse)",6=>"Signup IP",7=>"E-mail @domain only");
	$PASTE_types = Array(1=>"whox output",2=>"one user per line",3=>"channel status");

	$mmsg .= "List type: ";
	if ($_POST["dmode"]==1) {
		$mmsg .= "'" . $_POST["dsp"] . "' on " . $PT_types[$_POST["dstatus"]] . "\n";
	}
	if ($_POST["dmode"]==2) {
		$mmsg .= "Last " . $_POST["dnb"] . " users\n";
	}
	if ($_POST["dmode"]==3) {
		$mmsg .= "Paste from IRC (" . $PASTE_types[$_POST["paste_type"]] . ")\n";
	}
	if ($_POST["dmode"]==6) {
		$mmsg .= "Channel '" . $_POST["ccname"] . "'\n";
	}
	$mmsg .= "Ordered by: " . $OR_types[$_POST["dorder"]] . "\n";
	$mmsg .= "\n\n";
}
if ($debug_me) { echo "\n<a href=\"javascript:history.go(-1);\">&lt;&lt;&nbsp;Back</a>\n\n"; }
$freason_ok = str_replace("'","\'",$freason);
$dreason_ok = str_replace("'","\'",$dreason);

local_seclog("Toast! mode=[" . $_POST["dmode"] . "], st=[" . $_POST["dstatus"] . "], sp=[" . $_POST["dsp"] . "], or=[" . $_POST["dorder"] . "], nb=[" . $_POST["dnb"] . "], cname=[" . $_POST["ccname"] . "].");

if ($use_redir==0 && $debug_me==0) {
	echo "<html><head><title>User Toaster</title></head>\n";
	echo "<body bgcolor=#ffffff><br><br><h2>Processing . . .</h2><br>\n";
}
$had_q_out = 0;
for ($x=0;$x<$gcount;$x++) {
	$fraud="fraud_" . $id[$x];
	$delnoreg="delete_" . $id[$x];
	$suspend="susptag_" . $id[$x];
	$flagList="flagadd_" . $id[$x];
	$flagListR="flagrem_" . $id[$x];
	$notalready=1;
	$t_mmsg = "";
	if ($debug_me && $had_q_out) { echo "\n\n"; }
	$had_q_out = 0;
	if ($debug_me) { echo "User ID : <b>" . $id[$x] . "</b>"; }
	if ($send_mail) { $t_mmsg .= "User ID : " . $id[$x]; }
	if ($debug_me) { echo "\tDel/Noreg : <b>"; }
	if ($$delnoreg==1) {
		if ($admin<$min_lvl) { die("Sorry you can't do that ;)"); }
		if ($debug_me) { echo $$delnoreg; }
		unset($del_q);
		$del_q[] = "DELETE FROM acl WHERE user_id='" . $id[$x] . "'";
		$del_q[] = "DELETE FROM levels WHERE user_id='" . $id[$x] . "'";
		$del_q[] = "DELETE FROM notes WHERE user_id='" . $id[$x] . "' OR from_user_id='" . $id[$x] . "'";
		$del_q[] = "DELETE FROM pending WHERE manager_id='" . $id[$x]. "' OR reviewed_by_id='" . $id[$x] . "'";
		$del_q[] = "DELETE FROM pending_emailchanges WHERE user_id='" . $id[$x] . "'";
		$del_q[] = "DELETE FROM pending_pwreset WHERE user_id='" . $id[$x] . "'";
		$del_q[] = "DELETE FROM supporters WHERE user_id='" . $id[$x] . "'";
		$del_q[] = "DELETE FROM userlog WHERE user_id='" . $id[$x] . "'";
		$del_q[] = "DELETE FROM fraud_list_data WHERE user_id='" . $id[$x] . "'";
		$del_q[] = "DELETE FROM users_lastseen WHERE user_id='" . $id[$x]. "'";
		$del_q[] = "DELETE FROM users WHERE id='" . $id[$x] . "'";
		$nr_q = "INSERT INTO noreg (user_name,email,channel_name,type,never_reg,for_review,expire_time,created_ts,set_by,reason) VALUES (";
		if ($noreg_username == 1) { $nr_q .= "'" . $username[$x] . "',"; } else { $nr_q .= "'',"; }
		$nr_q .= "'" . $email[$x] . "','',2,";
		if ($days_noreg_with_del < 1) { //forever
			$nr_q .= "1,0,0,";
		} else {
			$nr_q .= "0,0,date_part('epoch', CURRENT_TIMESTAMP)::int+(86400*" . $days_noreg_with_del . "),";
		}
		$nr_q .= "date_part('epoch', CURRENT_TIMESTAMP)::int,'" . $adm_user . " (Toaster)','" . $dreason_ok . "')";
		if (!$debug_me) { // take the action
			for ($z=0;$z<count($del_q);$z++) {
				@pg_safe_exec($del_q[$z]);
			}
			pg_safe_exec($nr_q);
			$query = "";
		} else {
			$query = "\t<i>";
			$query .= str_replace("VALUES","\n\tVALUES",$nr_q) . "\n\t";
			for ($z=0;$z<count($del_q);$z++) {
				$query .= str_replace("VALUES","\n\tVALUES",$del_q[$z]) . "\n\t";
			}
			$query .= "</i>\n";
		}
		if ($debug_me) { echo "</b>\tFraud Tag : <b>-</b>\tSuspend Tag : <b>-"; }
		if ($debug_me) {
			echo "</b>\tFlagList+ : <b>-</b>\tUser Name : <b>" . $username[$x] . "</b>";
			if (strlen($username[$x]<4)) { echo "\t"; }
			if (strlen($username[$x]<=10)) { echo "\t"; }
			echo "\tEMail : <b>" . $email[$x] . "</b>\n" . $query;
		}
	} else {
		if ($debug_me) { echo "0</b>"; }
		if ($debug_me) { echo "\tFraud Tag : <b>"; }
		$bla = pg_safe_exec("SELECT * FROM noreg WHERE user_name='" . $username[$x] . "' AND type=4");
		if (pg_numrows($bla)>0) { $notalready=0; }
		if ($$fraud==1 && $notalready) {
			if ($debug_me) { echo $$fraud; }
			$daq = "INSERT INTO noreg (user_name,email,channel_name,type,never_reg,for_review,expire_time,created_ts,set_by,reason) VALUES ('" . $username[$x] . "','" . $email[$x] . "','',4,1,0,0,date_part('epoch', CURRENT_TIMESTAMP)::int,'" . $adm_user . "','" . $freason_ok . "')";
			$prereq = "SELECT flags FROM users WHERE id='" . $id[$x] . "'";
			$preres = pg_safe_exec($prereq);
			$prerow = pg_fetch_object($preres,0);
			$uflags = $prerow->flags;
			$newflags = (int)$uflags|0x0008; // Fraud TAG.
			$da2q = "UPDATE users SET last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,last_updated_by='*** TAGGED AS FRAUD ***',flags='" . $newflags . "' WHERE id='" . $id[$x] . "'";
			if (!$debug_me) { // take the action.
				pg_safe_exec($daq);
				pg_safe_exec($da2q);
			}
			if ($debug_me) { $query1 = "\t<i>" . str_replace("VALUES","\n\t\tVALUES",$daq) . ";</i>"; }
		} else {
			if ($notalready) {
				if ($debug_me) { echo "0"; }
			} else {
				if ($debug_me) { echo "*"; }
			}
			$query = "";
		}
		if ($debug_me) {
			echo "</b>\tSuspend Tag : <b>";
		}
		$bls = pg_safe_exec("SELECT flags,signup_ip,verificationdata FROM users WHERE id='" . $id[$x] . "'");
		$ols = pg_fetch_object($bls);
		$query = "";
		if ((int)$ols->flags & 0x0001) { // suspended already
			if ($debug_me) { echo "*"; }
		} else {
			if ($$suspend==1) {
				if ($debug_me) { echo "1"; }
				if ($send_mail) { $mmsg .= $t_mmsg; }
				$new_u_flags = (int)$ols->flags|0x0001; // global Suspension tag
				$query = "UPDATE users SET last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,last_updated_by='Suspended by Toaster',flags='" . $new_u_flags . "' WHERE id='" . $id[$x] . "'";
				if (!$debug_me) { // take action
					unset($raction);
					$raction = pg_safe_exec($query);
					log_user($id[$x],1,"global suspend for %U (%I) [toaster: " . str_replace("'","'",$_POST[sreason]) . "]");
				} else {
					$query2 = "\n\t<i>" . $query;
					$query2 .= "</i>";
				}
			} else {
				if ($debug_me) { echo "0"; }
			}
		}

		if ($$flagList!="") {
			$checkFL = pg_safe_exec("SELECT id FROM fraud_lists WHERE lower(name)='" . strtolower($$flagList) . "'");
			if (pg_numrows($checkFL)>0) { // already an existing list ... check the user
				$FLobj = pg_fetch_object($checkFL);
				$checkU = pg_safe_exec("SELECT * FROM fraud_list_data WHERE user_id='" . $id[$x] . "' AND list_id='" . $FLobj->id . "'");
				if (pg_numrows($checkU)>0) { // user already in this list.. doing nothing ...

				} else { // user not in the list .. adding the user in it ..
					$queryX = "INSERT INTO fraud_list_data (list_id,user_id) VALUES ('" . $FLobj->id . "','" . $id[$x] . "')";
					if ($debug_me) { $query3 = "\t<i>" . $queryX . "\n"; } else {
						pg_safe_exec($queryX);
					}
				}
			} else { // create the list and add the user in it...
				$queryA = "INSERT INTO fraud_lists (name) VALUES ('" . strtoupper($$flagList) . "')";
				$last_ID = "??";
				if ($debug_me) { $query3 = "\t<i>" . $queryA . "\n"; } else {
					$resA = pg_safe_exec($queryA);
					$checkA = pg_safe_exec("SELECT id FROM fraud_lists WHERE name='" . strtoupper($$flagList) . "'");
					$objA = pg_fetch_object($checkA);
					$last_ID = $objA->id;
				}
				$queryB = "INSERT INTO fraud_list_data (list_id,user_id) VALUES ('" . $last_ID . "','" . $id[$x] . "')";
				if ($debug_me) { $query3 .= "\t" . $queryB . "</i>"; } else {
					pg_safe_exec($queryB);
				}
			}
		}

		if ($$flagListR!="") {
			$ttt = str_replace(" ","",$$flagListR);
			$ttt2 = explode(",",$ttt);
			for ($zz=0;$zz<count($ttt2);$zz++) {
				$unf0 = pg_safe_exec("SELECT id FROM fraud_lists WHERE lower(name)='" . strtolower($ttt2[$zz]) . "'");
				if ($objunf0 = pg_fetch_object($unf0)) {
					$queryX = "DELETE FROM fraud_list_data WHERE user_id='" . $id[$x] . "' AND list_id='" . $objunf0->id . "'";
					if ($debug_me) { $query3 = "\t<i>" . $queryX . "\n"; } else {
						pg_safe_exec($queryX);
						// check if anymore user are lest in the list
						$qchk1 = pg_safe_exec("SELECT COUNT(user_id) AS count FROM fraud_list_data WHERE list_id='" . $objunf0->id . "' LIMIT 1");
						$qobj1 = pg_fetch_object($qchk1);
						if ($qobj1->count==0) { // no more, remove the list
							pg_safe_exec("DELETE FROM fraud_lists WHERE id='" . $objunf0->id . "'");
						}
					}
				}
			}
		}

		if ($debug_me) {
			echo "</b>\tFlagList+ : <b>";
			if ($$flagList=="") { echo "-"; } else { echo $$flagList; }
			echo "</b>\tUser Name : <b>" . $username[$x] . "</b>";
			if (strlen($username[$x])<4) { echo "\t"; }
			if (strlen($username[$x])<=10) { echo "\t"; }
			echo "\tEMail : <b>" . $email[$x] . "</b>\n" . $query1 . $query2 . $query3;
			if ($query1 != "" || $query2 != "" || $query3 != "") { $had_q_out = 1; }
			$query1 = ""; $query2 = ""; $query3 = "";
		}
		if ($send_mail && $$suspend==1) {
			$mmsg .="\tFlagList+ : ";
			if ($$flagList=="") { $mmsg .= "-"; }else { $mmsg .= $$flagList; }
			$mmsg .="\tUser Name : " . $username[$x];
			if (strlen($username[$x])<4) { $mmsg .= "\t"; }
			if (strlen($username[$x])<=10) { $mmsg .= "\t"; }
			$mmsg .= "\tSignup IP : " . $ols->signup_ip;
			if (strlen($ols->signup_ip)<12) { $mmsg .= "\t"; }
			if ($ols->signup_ip=="") { $mmsg .= "\t"; }
			$res0 = pg_safe_exec("SELECT COUNT(levels.access) AS count FROM channels,levels WHERE levels.user_id='" . $id[$x] . "' AND levels.channel_id=channels.id AND channels.registered_ts>0");
			$row0 = pg_fetch_object($res0);
			@pg_freeresult($res0);
			$mmsg .= "\t# axs : " . $row0->count;
			$mmsg .= "\tVerif.Answer : " . $ols->verificationdata;
			if (strlen($ols->verificationdata)<9) { $mmsg .= "\t"; }
			if ($ols->verificationdata=="") { $mmsg .= "\t"; }
			$mmsg .= "\tEMail : " . $email[$x] . "\n";
			$mail_lines++;
		}
	}
}

if ($debug_me) { echo "\n\nFRAUD Reason : <b>" . $freason . "</b>\n"; }
if ($debug_me) { echo "\n\nDEL/NOREG Reason : <b>" . $dreason . "</b>\n"; }
if ($debug_me) { echo "\n\nSUSPEND Reason : <b>" . $sreason . "</b>\n"; }
if ($send_mail) { $mmsg .="\n\nSUSPEND Reason : " . $sreason . "\n"; }
if ($debug_me) { echo "\n\n<a href=\"javascript:history.go(-1);\">&lt;&lt;&nbsp;Back</a>\n"; }
if ($debug_me && $send_mail) {
	if ($mail_lines==0) { echo "No mail will be sent (no actions !)\n\n"; } else { echo "Mail to be sent to " . $_POST["sendlist"] . "\n\n"; }
	echo "Mail message below:\n\n";
	echo $mmsg;
	echo "\n\n";
}
if (!$debug_me && $send_mail && $mail_lines>0) { //send the mail !!
	custom_mail($_POST["sendlist"],"[TOASTER] " . $adm_user . " : " . $mail_lines . " username(s) suspended",$mmsg,
		"From: " . TOASTER_FROM_MAIL . "\nReply-To: DO.NOT@REPLY.THANKS\nX-Mailer: " . NETWORK_NAME . " Channel Service");
}
if ($debug_me) { echo "</pre>\n"; }
if ($debug_me) { die; }

if ($use_redir) {
	header("Location: ./index.php");
} else {
	echo "<script language=\"JavaScript1.2\">\n";
	echo "<!--\n";
	echo "location.href='index.php';\n";
	echo "//-->\n";
	echo "</script>\n";
	echo "</body>\n";
	echo "</html>\n\n";
}
die;
?>
