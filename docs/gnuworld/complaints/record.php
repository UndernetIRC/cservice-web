<?
/* $Id: record.php,v 1.9 2004/07/25 03:31:51 nighty Exp $ */

define(MAX_CONCURRENT,		2); // for same email or username
define(MAX_CONCURRENT_TIME,	172800); // on 48 sliding hours

define(MAX_CONCURRENT_IP,	5); // for same IP address
define(MAX_CONCURRENT_IP_TIME,	86400); // on 24 sliding hours


require("../../../php_includes/cmaster.inc");
if (ENABLE_COMPLAINTS_MODULE != 1) { die("This option is disabled. Please contact the server administrator."); }
std_connect();
$user_id = isset($_COOKIE["auth"]) ? std_security_chk($_COOKIE["auth"]) : 0;
if ($user_id > 0) { $admin = std_admin(); } else { $admin = 0; }
$cTheme = get_theme_info();
std_theme_styles(1);
std_theme_body();

if ((complaints_off() && !isoper($user_id)) || COMPLAINTS_ADMINCOMMENT_ID<=0) {
	echo "<h2>The complaints system is temporarily disabled, please lodge a complaint if that is not OK with you.</h2>";
	echo "</body>\n";
	echo "</html>\n\n";
	die;
}

?>
<table border=0 cellspacing=0 cellpadding=3>
<tr>
<td valign=top><img src=complaint_dpt.jpg></td>
<td valign=top>
<?
	if (!check_secure_form("complaintreq" . $_POST["from_mail"] . ($user_id+0))) {
		echo "<big>Please use <a href=\"./\" target=_top>this page</a> to enter a complaint.</big>";
		die("</td></tr></table></body></html>");
	}
	echo "<font size=+2><b>" . $cpt_name[$_GET["ct"]] . "</b></font><br>";
	unset($back_lnk); $back_lnk = "<a href=\"javascript:history.go(-1);\"><b>&lt;&lt;&nbsp;back</b></a><br>";
	if ($user_id==0) {
		if( !(preg_match( "/^[A-Za-z0-9_+-.]+@[A-Za-z0-9.-]+\.[A-Za-z][A-Za-z]+$/", $_POST["from_mail"] )) ) {
			echo $back_lnk;
			echo "<big>e-mail syntax is invalid</big>.";
			die("</td></tr></table></body></html>");
		}
	}

	$da_crc = md5( CRC_SALT_0013 . $user_id . $_POST["from_mail"] . $_POST["ct"] . $_POST["complaint_text"] . cl_ip() );
	$da_users_id = $user_id;
	switch ($_POST["ct"]) {
		case 1:
			if ( !preg_match( NON_BOGUS, trim($_POST["login"]) ) ) {
				echo $back_lnk;
				echo "<big>bogus username</big>.";
				die("</td></tr></table></body></html>");
			}
			if (!ip_check(trim($_POST["login"]),1)) {
				echo $back_lnk;
				echo "<big>too many failed attempts for username / password pair, try again later.</big>";
				die("</td></tr></table></body></html>");
			}
			$da_users_id = chk_password($_POST["login"],$_POST["passwd"]);
			if ($da_users_id == 0) {
				echo $back_lnk;
				echo "<big>username or password is invalid</big>.";
				die("</td></tr></table></body></html>");
			}
			$rf = pg_safe_exec("SELECT flags FROM users WHERE id='" . (int)$da_users_id . "'");
			$of = pg_fetch_object($rf);
			if (!((int)$of->flags & 1)) { // not suspended
				echo $back_lnk;
				echo "<big>your username is NOT suspended currently</big>.";
				die("</td></tr></table></body></html>");
			}
			$da_channel1_id = 0; $da_channel1_name = "";
			$da_channel2_id = 0; $da_channel2_name = "";
			break;
		case 2:
			$da_channel1_name = trim($_POST["channel_name1"]);
			$r = pg_safe_exec("SELECT id,registered_ts FROM channels WHERE lower(name)='" . strtolower($da_channel1_name) . "'");
			if ($o = pg_fetch_object($r)) {
				if ($o->registered_ts>0) {
					$da_channel1_id = $o->id;
				} else {
					$da_channel1_id = 0;
				}
			} else {
				$da_channel1_id = 0;
			}
			$da_channel2_name = trim($_POST["channel_name2"]);
			$r = pg_safe_exec("SELECT id,registered_ts FROM channels WHERE lower(name)='" . strtolower($da_channel2_name) . "'");
			if ($o = pg_fetch_object($r)) {
				if ($o->registered_ts>0) {
					$da_channel2_id = $o->id;
				} else {
					$da_channel2_id = 0;
				}
			} else {
				$da_channel2_id = 0;
			}
			if ($da_channel2_id == 0) {
				echo $back_lnk;
				echo "<big>You can only place complaints when the offending channel is registered with X<br><br>";
				echo "Here the channel you specified is NOT registered with X.</big>";
				die("</td></tr></table></body></html>");
			}
			if (strtolower($da_channel1_name) == strtolower($da_channel2_name)) {
				echo $back_lnk;
				echo "<big>Offender and Victim channels can't be the same !<br><br>";
				echo "</big>";
				die("</td></tr></table></body></html>");
			}
			break;
		case 3:
			$da_channel1_name = trim($_POST["channel_name1"]);
			$r = pg_safe_exec("SELECT id,registered_ts FROM channels WHERE lower(name)='" . strtolower($da_channel1_name) . "'");
			if ($o = pg_fetch_object($r)) {
				if ($o->registered_ts>0) {
					echo $back_lnk;
					echo "<big>You can't object that way to a channel that is ALREADY registered.<br>This place is for objecting anonymously to channel applications currently being processed.</big>";
					die("</td></tr></table></body></html>");
				} else {
					// check if there's an current (status 0,1,2 or 8) application for this channel
					$rapp = pg_safe_exec("SELECT * FROM pending WHERE channel_id='" . (int)$o->id . "' AND (status<3 OR status=8)");
					if ($ropp = pg_fetch_object($rapp)) {
						$da_channel1_id = $o->id;
					} else {
						echo $back_lnk;
						echo "<big>You can't object that way to a channel which application has been REJECTED, CANCELLED or ACCEPTED BUT the channel was PURGED SINCE (old application).</big>";
						die("</td></tr></table></body></html>");
					}
				}
			} else {
				echo $back_lnk;
				echo "<big>You can't object to a channel that is not being processed as an application.</big>";
				die("</td></tr></table></body></html>");
			}
			$da_channel2_id = 0; $da_channel2_name = "";
			break;
		case 4:
			$da_channel1_name = trim($_POST["channel_name1"]);
			$r = pg_safe_exec("SELECT id,registered_ts FROM channels WHERE lower(name)='" . strtolower($da_channel1_name) . "'");
			if ($o = pg_fetch_object($r)) {
				if ($o->registered_ts>0) {
					echo $back_lnk;
					echo "<big>The channel is registered, so it's not purged :)</big>";
					die("</td></tr></table></body></html>");
				} else {
					$r2 = pg_safe_exec("SELECT ts FROM channellog WHERE channelid='" . (int)$o->id . "' AND event=8 order by ts desc limit 1");
					$r3 = pg_safe_exec("SELECT ts FROM channellog WHERE channelid='" . (int)$o->id . "' AND event=7 order by ts desc limit 1");
					if ($o2 = pg_fetch_object($r2)) {
						$last_purge_ts = $o2->ts;
					} else {
						$last_purge_ts = 0;
					}
					if ($o3 = pg_fetch_object($r3)) {
						$last_reg_ts = $o3->ts;
					} else {
						$last_reg_ts = 0;
					}
					if (($last_reg_ts > $last_purge_ts) || ($last_reg_ts == 0 && $last_purge_ts == 0)) {
						echo $back_lnk;
						echo "<big>The channel you specified hasn't been PURGED as far as we can see</big>.";
						die("</td></tr></table></body></html>");
					}
					$da_channel1_id = $o->id;
				}
			} else {
				echo $back_lnk;
				echo "<big>You can't request a reconsidere for a purge about a channel that has never been registered.</big>";
				die("</td></tr></table></body></html>");
			}
			$da_channel2_id = 0; $da_channel2_name = "";
			break;
		case 5:
			$da_channel1_name = trim($_POST["channel_name1"]);
			$r = pg_safe_exec("SELECT id,registered_ts FROM channels WHERE lower(name)='" . strtolower($da_channel1_name) . "'");
			if ($o = pg_fetch_object($r)) {
				if ($o->registered_ts>0) {
					echo $back_lnk;
					echo "<big>The channel is registered, so it's not purged :)</big>";
					die("</td></tr></table></body></html>");
				} else {
					$r2 = pg_safe_exec("SELECT ts FROM channellog WHERE channelid='" . (int)$o->id . "' AND event=8 order by ts desc limit 1");
					$r3 = pg_safe_exec("SELECT ts FROM channellog WHERE channelid='" . (int)$o->id . "' AND event=7 order by ts desc limit 1");
					if ($o2 = pg_fetch_object($r2)) {
						$last_purge_ts = $o2->ts;
					} else {
						$last_purge_ts = 0;
					}
					if ($o3 = pg_fetch_object($r3)) {
						$last_reg_ts = $o3->ts;
					} else {
						$last_reg_ts = 0;
					}
					if (($last_reg_ts > $last_purge_ts) || ($last_reg_ts == 0 && $last_purge_ts == 0)) {
						echo $back_lnk;
						echo "<big>The channel you specified hasn't been PURGED as far as we can see</big>.";
						die("</td></tr></table></body></html>");
					}
					$da_channel1_id = $o->id;
				}
			} else {
				echo $back_lnk;
				echo "<big>You can't request a reason for a purge about a channel that has never been registered.</big>";
				die("</td></tr></table></body></html>");
			}
			$da_channel2_id = 0; $da_channel2_name = "";
			break;
		default:
		case 99:
			$da_channel1_id = 0; $da_channel1_name = "";
			$da_channel2_id = 0; $da_channel2_name = "";
			break;

	}
	if (trim($_POST["complaint_text"])=="") {
		echo $back_lnk;
		echo "<big>you need to explain what your complaint is about in the 'complaint summary' section</big>.";
		die("</td></tr></table></body></html>");
	}


	/* Check if the maximum complaints per user id / email hasnt been reached */
	$ssq = pg_safe_exec("SELECT COUNT(id) AS count FROM complaints WHERE created_ts>(date_part('epoch', CURRENT_TIMESTAMP)::int-" . MAX_CONCURRENT_TIME . ") AND status<=2 AND (lower(from_email)='" . strtolower($_POST["from_mail"]) . "' OR lower(inrec_email)='" . strtolower($_POST["from_mail"]) . "' OR (from_id='" . ($user_id+0) . "' AND from_id>0))");
	$sso = pg_fetch_object($ssq);
	if ($sso->count >= MAX_CONCURRENT) { // yes !
		echo $back_lnk;
		echo "<big>Too much complaints are being posted from you, please wait " . (MAX_CONCURRENT_TIME/3600) . " hours and try again.</big>.";
		die("</td></tr></table></body></html>");
	}
	unset($ssq); unset($sso);

	/* Check if the maximum complaints per IP hasnt been reached */
	$ssq = pg_safe_exec("SELECT COUNT(id) AS count FROM complaints WHERE created_ts>(date_part('epoch', CURRENT_TIMESTAMP)::int-" . MAX_CONCURRENT_IP_TIME . ") AND status<=2 AND created_ip='" . cl_ip() . "'");
	$sso = pg_fetch_object($ssq);
	if ($sso->count >= MAX_CONCURRENT_IP) { // yes !
		echo $back_lnk;
		echo "<big>Too much complaints are being posted from you, please wait " . (MAX_CONCURRENT_IP_TIME/3600) . " hours and try again.</big>.";
		die("</td></tr></table></body></html>");
	}
	unset($ssq); unset($sso);

	if (strlen(trim($_POST["complaint_text"]))>4096) {
		echo $back_lnk;
		echo "<big>your 'complaint summary' section is too big ( above 4KB )</big>.";
		die("</td></tr></table></body></html>");
	}
	if (strlen(trim($_POST["complaint_logs"]))>24576) {
		echo $back_lnk;
		echo "<big>your 'complaint logs' section is too big ( above 24KB )</big>.";
		die("</td></tr></table></body></html>");
	}

/*
	if ($user_id>0 && $_GET["ct"]>1) {
		if ($_POST["ziplogfile"]=="none" || $_POST["ziplogfile_size"] == 0) {
                        echo "You must 'browse' and choose a file to upload (zero length)<br><a href=\"javascript:history.go(-1);\">Back</a>\n";
                        echo "</body></html>\n\n";
                        die;
                }
                if ($_POST["ziplogfile_type"] != "application/x-gzip") {
                        echo "(" . $_POST["ziplogfile_type"] . ") - Valid file type is ZIP archive.<br><a href=\"javascript:history.go(-1);\">Back</a>\n";
                        echo "</body></html>\n\n";
                        die;
                }
                if ($_POST["ziplogfile_size"]>102400) {
                        echo "Maximum file size is 102400 bytes (100 Kbytes).<br><a href=\"javascript:history.go(-1);\">Back</a>\n";
                        echo "</body></html>\n\n";
                        die;
                }
		$complaint_filename = $da_crc . "-" . $_POST["ziplogfile_name"];
                $handle = opendir("/tmp");
                while (($file = readdir($handle)) !== false) {
                        if ($file == $complaint_filename) {
                                echo "File '/tmp/" . $complaint_filename . "' already exists !@#<br><a href=\"javascript:history.go(-1);\">Back</a>\n";
                                echo "</body></html>\n\n";
                                die;
                        }
                }
                $picture = $complaint_filename;
                system("mv " . $_POST["ziplogfile"] . " /tmp/" . $complaint_filename);
	}
*/
$uInfo = get_user_info($da_users_id);

// check that


$query = "INSERT INTO complaints ( from_id, from_email, inrec_email, complaint_type, complaint_text, complaint_logs, ";
$query .= "complaint_channel1_id, complaint_channel1_name, complaint_channel2_id, complaint_channel2_name, complaint_users_id, status, nicelevel, reviewed_by_id, reviewed_ts, created_ts, created_ip, created_crc, crc_expiration, ticket_number, current_owner";
$query .= " ) VALUES ( ";
$query .= "" . (int)$da_users_id . ", ";
$query .= "'" . $_POST["from_mail"] . "', ";
if ($da_users_id>0) { $query .= "'" . $uInfo->email . "', "; } else { $query .= "'', "; }
$query .= "" . $_POST["ct"] . ", ";
$query .= "'" . post2db(str_replace(";",":",$_POST["complaint_text"])) . "', ";
$query .= "'" . post2db(str_replace(";",":",$_POST["complaint_logs"])) . "', ";

$query .= "" . (int)$da_channel1_id . ", ";
$query .= "'" . post2db($da_channel1_name) . "', ";
$query .= "" . (int)$da_channel2_id . ", ";
$query .= "'" . post2db($da_channel2_name) . "', ";

$query .= "" . (int)$da_users_id . ", ";
$query .= "0, 0, 0, 0, date_part('epoch', CURRENT_TIMESTAMP)::int, '" . cl_ip() . "', '" . $da_crc . "', (date_part('epoch', CURRENT_TIMESTAMP)::int+172800), '', 0";
$query .= " )";
echo "<br>";
$res = @pg_safe_exec($query);
$tq = pg_safe_exec("SELECT id FROM complaints WHERE created_crc='" . $da_crc . "'");
if ($to = pg_fetch_object($tq)) {
	$da_c_id = $to->id;
	$ticket_number = strtoupper($da_c_id . "-" . substr(md5( $da_c_id . CRC_SALT_0007 . "ticket" ),0,10));

	if ($_POST["ct"]==3) { // if 'pending application anonymous objection', insert an ADMIN comment in the given application to notify
			       // that the complaint has been recorded...
		unset($issuer_id); unset($da_cmt); unset($notif_q);
		$issuer_id = COMPLAINTS_ADMINCOMMENT_ID; // this ID needs to be a permanently valid user !!!!! (see config.inc)
		$da_cmt = "";
		$da_cmt .= "**** AUTOMATIC ****<br><br>";
		$da_cmt .= "<b>An anonymous objection has been posted through the Complaints System.</b><br>";
		$da_cmt .= "<a href=\"complaints/admin.php?view=" . $ticket_number . "\">click here</a> to go to that complaint.<br><br>";
		$notif_q = "INSERT INTO objections (channel_id,user_id,comment,created_ts,admin_only) VALUES ('" . (int)$da_channel1_id . "','" . (int)$issuer_id . "','" . post2db($da_cmt) . "',date_part('epoch', CURRENT_TIMESTAMP)::int,'Y')";
		pg_safe_exec($notif_q);
	}


} else {
	$ticket_number = "N/A";
}
if (!$res) { die($back_lnk . "<b>SQL ERROR</b><br><br></td></tr></table></body></html>"); } else {
	if ($da_users_id>0) { log_user($da_users_id,11,"Type: " . $cpt_name[$_POST["ct"]] . ", Ticket-Number: " . $ticket_number); }
	$mmsg = "";
	$mmsg .= "\n\n";
	$mmsg .= "We recently received a complaint to CService using this e-mail address (" . $_POST["from_mail"] . ") for the reply.\n";
	$mmsg .= "If you haven't sent any complaint and don't know what this is all about, then just delete this message and DO NOT CLICK below.\n\n";
	$mmsg .= "If you are the person that sent that complaint, please confirm it by clicking the link below within 48 hours :\n\n";
	$confirm_url = gen_server_url() . LIVE_LOCATION . "/complaints/confirm.php";
	$mmsg .= "\t\t" . $confirm_url . "?ID=" . $da_crc . "\n\n";
	$mmsg .= "\nThe " . NETWORK_NAME . " Channel Service.\n\n";
	custom_mail($_POST["from_mail"],"[" . NETWORK_NAME . " CService Complaints] Confirmation request",$mmsg,"From: " . NETWORK_NAME . " Channel Service <" . OBJECT_EMAIL . ">\nReply-to: DO.NOT@REPLY.THANKS\nX-Mailer: " . NETWORK_NAME . " CService Complaint Module\n\n");
}
$dq = pg_safe_exec("SELECT id FROM complaints WHERE (status=0 OR status=99) AND crc_expiration<date_part('epoch', CURRENT_TIMESTAMP)::int");
while ($do = pg_fetch_object($dq)) {
	pg_safe_exec("DELETE FROM complaints_reference WHERE complaints_ref='" . (int)$do->id . "'");
	pg_safe_exec("DELETE FROM complaints_threads WHERE complaint_ref='" . (int)$do->id . "'");
	pg_safe_exec("DELETE FROM complaints WHERE id='" . (int)$do->id . "'");
}
echo "<h2>Your complaint has been successfully recorded into our database...<br>...however you need to complete one more step :</h2>";
echo "<h3>";
echo "A confirmation e-mail message has been sent to your e-mail address (" . $_POST["from_mail"] . ")<br>";
echo "you need to <u>click on the link it contains to confirm</u> your e-mail address is correct for further processing.<br>";
echo "You must do this <u>within 48 hours</u> from now.</h3><br><i>\n";
echo "If your e-mail address is not correct (*sigh* you were asked for a good one!),<br>";
echo "please just re-post your request with a correct/working e-mail address.<br><br>";
echo "</i>";
echo "<br><br>";

die("</td></tr></table></body></html>");
?>
