<?
/* $Id: ticket.php,v 1.14 2004/07/25 03:31:51 nighty Exp $ */
require("../../../php_includes/cmaster.inc");
if (ENABLE_COMPLAINTS_MODULE != 1) { die("This option is disabled. Please contact the server administrator."); }
std_connect();
$user_id = std_security_chk($auth);
$admin = std_admin();
if ($_GET["A"]!="replyadm" && $_GET["A"]!="cancel" && $_GET["A"]!="resolve" && $_GET["A"]!="delete" && complaints_off() && !isoper($user_id)) {
	$cTheme = get_theme_info();
	std_theme_styles(1);
	std_theme_body();
	echo "<h2>The complaints system is temporarily disabled, please lodge a complaint if that is not OK with you.</h2>";
	echo "</body>\n";
	echo "</html>\n\n";
	die;
}
unset($da_t);unset($da_id);
$da_t = explode("-",$_GET["ID"]);
$da_id = $da_t[0];
// check for user part
switch ($_GET["A"]) {
	case 'close':
		$cTheme = get_theme_info();
		std_theme_styles(1);
		std_theme_body();
		if ($_GET["C"] == md5( CRC_SALT_0005 . $_GET["ID"] . "close" )) {
			if (check_secure_form("do_close" . $_GET["ID"] . $_POST["from_email"])) {
				$r1 = pg_safe_exec("SELECT id,from_id FROM complaints WHERE status!=4 AND id='" . (int)$da_id . "' AND ticket_number='" . $_GET["ID"] . "'");
				if ($o1 = pg_fetch_object($r1)) {
					$q = "UPDATE complaints SET current_owner=0,status=4 WHERE id='" . (int)$da_id . "' AND ticket_number='" . $_GET["ID"] . "'";
					$q2 = "INSERT INTO complaints_threads (complaint_ref,reply_by,reply_ts,reply_text,actions_text,in_reply_to) VALUES ('" . (int)$da_id . "',0,date_part('epoch', CURRENT_TIMESTAMP)::int,'** TICKET CLOSED **','',0)";
					$q3 = "DELETE FROM complaints_reference WHERE complaints_ref='" . (int)$da_id . "'";
					$r = pg_safe_exec($q);
					$updated=0;
					if ($r) {
						$r2 = pg_safe_exec($q2);
						if ($r2) {
							$updated = 1;
							pg_safe_exec($q3);
							if ( $_POST["from_id"] >0) { log_user($_POST["from_id"],12,"Ticket-number: " . $_GET["ID"]); }
						}
					}
					echo "<h2>Complaint Manager (close complaint ticket)";
					echo "</h2>\n";
					echo "<hr width=100% size=1 noshade>";
					if ($updated) {
						$mm = "";
						$mm .= "Your complaint ticket number " . $_GET["ID"] . " has been CLOSED by yourself.\n\n";
						$mm .= "Thanks for using our Complaint System.\n\n";
						custom_mail($_POST["from_email"],"[" . NETWORK_NAME . " CService Complaints] " . $_GET["ID"] . " - Closed by user",$mm,"From: " . NETWORK_NAME . " Channel Service <" . OBJECT_EMAIL . ">\nReply-to: DO.NOT@REPLY.THANKS\nX-Mailer: " . NETWORK_NAME . " CService Complaint Module\n\n");
						echo "<br><br><b>Your complaint ticket has been closed. We are glad your problem is gone.</b><br><br>";
						echo "<a href=\"../\" target=_top>Back to home page</a><br><br>\n";
					} else {
						echo "<br><br><b>For some strange reason, we couldn't close your ticket, please contact a CServie Administrator.</b><br><br>";
						echo "<a href=\"../\" target=_top>Back to home page</a><br><br>\n";
					}
				} else {
					echo "<br><br>This ticket has already been closed.";
				}
			} else {
				$r1 = pg_safe_exec("SELECT id,from_id,from_email FROM complaints WHERE id='" . (int)$da_id . "' AND ticket_number='" . $_GET["ID"] . "'");
				if ($o1 = pg_fetch_object($r1)) {
					echo "<h2>Complaint Manager (close complaint ticket)";
					echo "</h2>\n";
					echo "<hr width=100% size=1 noshade>";
					echo "<form name=do_close action=" . $_SERVER["REQUEST_URI"] . " method=post>\n";
					echo "<input type=hidden name=from_id value=\"" . $o1->from_id . "\">\n";
					echo "<input type=hidden name=from_email value=\"" . $o1->from_email . "\">\n";
					make_secure_form("do_close" . $_GET["ID"] . $o1->from_email);
					echo "<b>Are you sure you want to CLOSE this ticket ?<br><br>This means you think your case is resolved and you do NOT need more feedback.</b><br><br>\n";
					echo "<input type=button onClick=\"window.close();\" value=\"No, do NOT close the ticket\">&nbsp;&nbsp;&nbsp;&nbsp;";
					echo "<input type=submit value=\"Yes, CLOSE this ticket !\">\n";
					echo "</form>\n";
				} else {
					echo "<br><br>Invalid Ticket Number";
				}
			}
		} else {
			echo "<br><br>Invalid credentials";
		}
		echo "</body></html>\n\n";
		die;
		break;
	case 'reply':
		$cTheme = get_theme_info();
		std_theme_styles(1);
		std_theme_body();
		if ($_GET["C"] == md5( CRC_SALT_0005 . $_GET["ID"] . $_GET["RT"] . "reply-user" )) {
			if (trim($_POST["ureply"])!="" && check_secure_form("do_reply_user" . $_GET["ID"] . $_GET["RT"])) {
				$r2 = pg_safe_exec("SELECT * FROM complaints_threads WHERE complaint_ref='" . (int)$da_id . "' AND in_reply_to='" . (int)$_GET["RT"] . "'");
				if ($o2 = pg_fetch_object($r2)) {
					echo "<br><br>This message has already been replied to.<br><br>";
				} else {
					$da_reply = post2db($_POST["ureply"]);
					if (strlen($da_reply)>30720) {
						echo "<big>your 'reply' section is too big ( above 30KB )</big>.";
					} else {
						$q = "INSERT INTO complaints_threads (complaint_ref,reply_by,reply_ts,reply_text,actions_text,in_reply_to) VALUES ('" . (int)$da_id . "',0,date_part('epoch', CURRENT_TIMESTAMP)::int,'" . $da_reply . "',''," . (int)$_GET["RT"] . ")";

						echo "<h2>Complaint Manager (reply to admin)";
						echo "</h2>\n";
						echo "<hr width=100% size=1 noshade>";

						pg_safe_exec($q);

						echo "<br><br><b>Your reply has been recorded, Please wait for feedback via e-mail.</b><br><br>";
					}
				}
				echo "<a href=\"../\" target=_top>Back to home page</a><br><br>\n";

			} else {
				$r1 = pg_safe_exec("SELECT id,from_id FROM complaints WHERE id='" . (int)$da_id . "' AND status=2 AND ticket_number='" . $_GET["ID"] . "'");
				if ($o1 = pg_fetch_object($r1)) {
					$r2 = pg_safe_exec("SELECT * FROM complaints_threads WHERE complaint_ref='" . (int)$da_id . "' AND in_reply_to='" . (int)$_GET["RT"] . "'");
					if ($o2 = pg_fetch_object($r2)) {
						echo "<br><br>This message has already been replied to.<br><br>";
					} else {
						echo "<h2>Complaint Manager (reply to admin)";
						echo "</h2>\n";
						echo "<hr width=100% size=1 noshade>";
						echo "<form name=do_reply action=" . $_SERVER["REQUEST_URI"] . " method=post>\n";
						echo "<input type=hidden name=from_id value=\"" . $o1->from_id . "\">\n";
						show_ticket_events((int)$da_id,1);
						echo "<br>";
						make_secure_form("do_reply_user" . $_GET["ID"] . $_GET["RT"]);
						echo "Your reply :<br>\n<textarea name=ureply cols=40 rows=10 wrap></textarea><br><input type=submit value=\"Send this reply !\">\n";
						echo "</form>\n";
					}
				} else {
					echo "<br><br>Invalid Ticket Number";
				}
			}
		} else {
			echo "<br><br>Invalid credentials";
		}
		echo "</body></html>\n\n";
		die;
		break;
}

if (!acl(XCOMPLAINTS_ADM_REPLY) || $admin==0) { die("ERROR-1337: You cannot access that page, sorry."); }
// ... then the admin only part(s)...
$cTheme = get_theme_info();
std_theme_styles(1);
std_theme_body();
if (!acl(XCOMPLAINTS_ADM_REPLY) && !acl(XCOMPLAINTS_ADM_READ)) {
	die("Your level is too low to access this page</body></html>");
}

echo "<h2>Complaint Manager";
switch ($_GET["A"]) {
	case 'replyadm':
		echo " (reply to user)";
		break;
	case 'cancel':
		echo " (force ticket close)";
		break;
	case 'resolve':
		echo " (ticket resolve)";
		break;
	case 'delete':
		echo " (ticket removal)";
		break;
}
if (COMPLAINTS_DO_FOLLOWUP) { echo "<h4>(Follow Up Active)</h4>"; }
echo "</h2>\n";
echo "<hr width=100% size=1 noshade>";
echo "<b>Ticket number :</b> " . $_GET["ID"] . "<br><br>\n";

if (
	($_GET["A"] == "replyadm" && $_GET["C"] == md5( CRC_SALT_0005 . $_GET["ID"] . $_GET["RT"] . "reply-admin")) ||
	($_GET["A"] == "cancel" && $_GET["C"] == md5( CRC_SALT_0005 . $_GET["ID"] . "cancel")) ||
	($_GET["A"] == "resolve" && $_GET["C"] == md5( CRC_SALT_0005 . $_GET["ID"] . "resolve")) ||
	($admin>=800 && $_GET["A"] == "delete" && $_GET["C"] == md5( CRC_SALT_0005 . $_GET["ID"] . "delete"))
   ) {

	$r1 = pg_safe_exec("SELECT id,from_id,complaint_text,complaint_logs,from_email FROM complaints WHERE id='" . (int)$da_id . "' AND ticket_number='" . $_GET["ID"] . "'");
	if ($daobj = pg_fetch_object($r1)) {
		$passok=1;
		if ($_GET["A"]=="replyadm") {
			$r2 = pg_safe_exec("SELECT * FROM complaints_threads WHERE reply_by>0 AND complaint_ref='" . (int)$da_id . "' AND reply_text!='' AND in_reply_to='" . (int)$_GET["RT"] . "'");
			if ($o2 = pg_fetch_object($r2)) {
				$passok=0; // message has already been 'user-replied' to.
			}
		}

if ($passok) {
switch ($_GET["A"]) {
	case 'cancel':
		$q = "UPDATE complaints SET current_owner=0,status=4 WHERE id='" . (int)$da_id . "' AND ticket_number='" . $_GET["ID"] . "'";
		$q2 = "INSERT INTO complaints_threads (complaint_ref,reply_by,reply_ts,reply_text,actions_text,in_reply_to) VALUES ('" . (int)$da_id . "'," . (int)$user_id . ",date_part('epoch', CURRENT_TIMESTAMP)::int,'** TICKET CANCELLED **','',0)";
		$q3 = "DELETE FROM complaints_reference WHERE complaints_ref='" . (int)$da_id . "'";
		$r = pg_safe_exec($q);
		$updated=0;
		if ($r) {
			$r2 = pg_safe_exec($q2);
			if ($r2) {
				$updated = 1;
				pg_safe_exec($q3);
				if ( $daobj->from_id >0) { log_user($daobj->from_id,12,"Ticket-number: " . $_GET["ID"] . " (cancelled by admin)"); }
			}
		}
		if ($updated) {
			$mm = "";
			$mm .= "Your complaint ticket number " . $_GET["ID"] . " has been CLOSED by a CService Admin\n\n";
			$mm .= "Thanks for using our Complaint System.\n\n";
			custom_mail($daobj->from_email,"[" . NETWORK_NAME . " CService Complaints] " . $_GET["ID"] . " - Closed by Admin",$mm,"From: " . NETWORK_NAME . " Channel Service <" . OBJECT_EMAIL . ">\nReply-to: DO.NOT@REPLY.THANKS\nX-Mailer: " . NETWORK_NAME . " CService Complaint Module\n\n");
			echo "<br><br><b>This complaint ticket has been closed. The user is being notified of this fact.</b><br><br>";
			echo "<a href=\"admin.php\">Back to Complaint Admin</a><br><br>\n";
		} else {
			echo "<br><br><b>For some strange reason, we couldn't close this ticket, please contact a Site Administrator.</b><br><br>";
			echo "<a href=\"admin.php\">Back to Complaint Admin</a><br><br>\n";
		}
		break;
	case 'replyadm':
		if (check_secure_form("do_reply_admin" . $_GET["ID"] . $_GET["RT"])) {
			$do_notify_user = 1;
			$da_reply = trim(post2db(str_replace(";",":",$_POST["areply"])));
			$da_actions = trim(post2db(str_replace(";",":",$_POST["aactions"])));
			$do_the_reply = 1;
			if ((strlen($da_reply)+strlen($da_actions))>30720) {
				$do_the_reply = 0;
				echo "<big>your 'reply' section added to your 'admin actions' section are too big ( above 30KB total )</big>.";
			}
			if ($do_the_reply == 1) {
				if ($da_reply == "") { $do_notify_user = 0; }
				$q = "INSERT INTO complaints_threads (complaint_ref,reply_by,reply_ts,reply_text,actions_text,in_reply_to) VALUES ('" . (int)$da_id . "'," . (int)$user_id . ",date_part('epoch', CURRENT_TIMESTAMP)::int,'" . $da_reply . "','" . $da_actions . "'," . (int)$_GET["RT"] . ")";
				$qx = ""; $qs = "";
				if ($_GET["RT"]==0 && $_POST["newstatus"]==0) {
					$qx = "UPDATE complaints SET current_owner=" . (int)$user_id . ",reviewed_by_id=" . (int)$user_id . ",reviewed_ts=date_part('epoch', CURRENT_TIMESTAMP)::int,status=2 WHERE id='" . (int)$da_id . "' AND ticket_number='" . $_GET["ID"] . "'";
				}
				if ($_POST["newstatus"]>2) {
					$somemore = "";
					if ($_GET["RT"]==0) {
						$somemore = "current_owner=0,reviewed_by_id=" . (int)$user_id . ",reviewed_ts=date_part('epoch', CURRENT_TIMESTAMP)::int,";
					} else {
						$somemore = "current_owner=0,";
					}
					pg_safe_exec("DELETE FROM complaints_reference WHERE complaints_ref='" . (int)$da_id . "'");
					$qs = "UPDATE complaints SET " . $somemore . "status=" . $_POST["newstatus"] . " WHERE id='" . (int)$da_id . "' AND ticket_number='" . $_GET["ID"] . "'";
				}
				pg_safe_exec($q);
	//			echo $q . "<br>";
				if ($qx!="") { pg_safe_exec($qx); }
				if ($qs!="") {
					pg_safe_exec($qs);
	//				echo $qs . "<br>";
					$qq = "INSERT INTO complaints_threads (complaint_ref,reply_by,reply_ts,reply_text,actions_text,in_reply_to) VALUES ('" . (int)$da_id . "'," . (int)$user_id . ",date_part('epoch', CURRENT_TIMESTAMP)::int,'*** TICKET " . strtoupper($cmp_status[$_POST["newstatus"]]) . " ***','',0)";
	//				echo $qq . "<br>";
					pg_safe_exec($qq);
				}

				$mumuq = pg_safe_exec("SELECT id FROM complaints_threads WHERE complaint_ref='" . (int)$da_id . "' ORDER BY reply_ts DESC LIMIT 1");
				if ($mommoq = pg_fetch_object($mumuq)) {
					$da_rt = $mommoq->id;
				} else { $da_rt = 0; }

				if ($da_rt>0) {
					if ($do_notify_user) {
						$mm = "";
						$mm .= "Your complaint has received a reply from a CService Admin,\n";
						$mm .= "you will find the reply below :\n\n";
						$mm .= "============================================================================\n";
						$mm .= N_get_pure_string($_POST["areply"]) . "\n\n";
						$mm .= "============================================================================\n";
						if ($_POST["newstatus"]>0) {
							$mm .= "Thanks for using our Complaint system, this is the last event of this ticket.\n";
							$mm .= "You'll receive a confirmation right after this message.\n\n";
						} else {
							$mm .= "You can reply to this message by following the next link :\n";
							$da_url = gen_server_url() . LIVE_LOCATION . "/complaints/ticket.php?A=reply&ID=" . $_GET["ID"] . "&C=" . md5( CRC_SALT_0005 . $_GET["ID"] . $da_rt . "reply-user" ) ."&RT=" . $da_rt;
							$mm .= $da_url . "\n\n";
						}
						custom_mail($daobj->from_email,"[" . NETWORK_NAME . " CService Complaints] " . $_GET["ID"] . " - Admin Reply",$mm,"From: " . NETWORK_NAME . " Channel Service <" . OBJECT_EMAIL . ">\nReply-to: DO.NOT@REPLY.THANKS\nX-Mailer: " . NETWORK_NAME . " CService Complaint Module\n\n");
						echo "<br><br><b>Your reply has been recorded, The user is being notified by e-mail.</b><br><br>";
					} else {
						echo "<br><br><b>Your reply has been recorded (admin only), The user is NOT being notified.</b><br><br>";
					}

					if ($_POST["newstatus"]>0) { // status also changed ... (notify user anyway of this state)
						$mm = "";
						$mm .= "Your complaint ticket number " . $_GET["ID"] . " has been marked as " . strtoupper($cmp_status[$_POST["newstatus"]]) . " by a CService Admin\n\n";
						$mm .= "Thanks for using our Complaint System.\n\n";
						custom_mail($daobj->from_email,"[" . NETWORK_NAME . " CService Complaints] " . $_GET["ID"] . " - " . ucfirst($cmp_status[$_POST["newstatus"]]) . " by admin",$mm,"From: " . NETWORK_NAME . " Channel Service <" . OBJECT_EMAIL . ">\nReply-to: DO.NOT@REPLY.THANKS\nX-Mailer: " . NETWORK_NAME . " CService Complaint Module\n\n");
						echo "<br><br><b>Additionally the complaint ticket is now '" . strtoupper($cmp_status[$_POST["newstatus"]]) . "', The user is being notified by e-mail.</b><br><br>";
					}

				} else {
					echo "<br><br><b>Your reply could not be recorded correctly ! Mail not sent, You might retry or contact the site administrator.<br><br>";
				}
			}
			echo "<a href=\"admin.php\">Back to Complaint Admin</a><br><br>\n";
		} else {
			echo "<form name=do_reply action=\"" . $_SERVER["REQUEST_URI"] . "\" method=post>\n";
			echo "<input type=hidden name=from_id value=\"" . $daobj->from_id . "\">\n";

			echo "<table width=100% border=1 cellpadding=5 cellspacing=0>";
			echo initial_complaint($_GET["ID"]);
			echo "</table>\n";

			if ($_GET["RT"]>0) {
				show_ticket_events($da_id);
				echo "<br>";
			} else {
				echo "<b><u>You are replying to the initial complaint of this ticket :</u></b><br>";
				echo db2disp($daobj->complaint_text);
				if ($daobj->complaint_logs!="") {
					echo "<br><br><b><u>optional logs :</u></b><br>";
					echo db2disp($daobj->complaint_logs);
				}
				echo "<br><br>";
			}
			make_secure_form("do_reply_admin" . $_GET["ID"] . $_GET["RT"]);
			echo "<script language=\"JavaScript\">\n";
			echo "<!--\n";
			$comc = "";
			$coml = "";
			$rcom = pg_safe_exec("SELECT * FROM default_msgs WHERE type=3");
			$yy = 0;
			$opt = "";
			while ($rcomo = pg_fetch_object($rcom)) {
				$yy++;
				$opt .= "<option value=\"" . $yy . "\">" . $rcomo->label . "</option>\n";
				$coml .= ",'" . post2db($rcomo->label) . "'";
				$comc .= ",'" . post2db($rcomo->content) . "'";
			}
			echo "\tvar com_msgs_c = [''" . $comc . "];\n";
			echo "\tvar com_msgs_l = [''" . $coml . "];\n";
			echo "\tfunction upd_com_r(opt) {\n";
			echo "\t\tdocument.forms['do_reply'].areply.value = com_msgs_c[parseInt(opt.options[opt.selectedIndex].value)];\n";
			echo "\t}\n";
			echo "//-->\n";
			echo "</script>\n";
			echo "Your reply :";
			if ($opt != "") {
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<select name=dmsgC onChange=\"upd_com_r(this)\"><option value=0>-- pick a common reply --</option>" . $opt . "</select><br>\n";
			} else {
				echo "<br>\n";
			}
			echo "<textarea name=areply cols=70 rows=20 wrap></textarea><br>";
			echo "Pass this TICKET into <select name=newstatus><option value=0 selected>Being processed</option>\n";
			echo "<option value=3>Resolved</option>\n";
			echo "<option value=4>Abandonned/Cancelled</option>\n";
			if ($admin>=800) { echo "<option value=99>Deleted</option>\n"; }
			echo "</select>\n";
			echo "<br><br>";

			echo "For information the ADMIN actions taken :<br>\n(this will be kept for admin's eyes only)<br>\n<textarea name=aactions cols=70 rows=20 wrap></textarea><br>";
			echo "<input type=submit value=\"Send this reply !\">\n";
			echo "</form>\n";
		}



		break;
	case 'resolve':
		$q = "UPDATE complaints SET status=3 WHERE id='" . (int)$da_id . "' AND ticket_number='" . $_GET["ID"] . "'";
		$q2 = "INSERT INTO complaints_threads (complaint_ref,reply_by,reply_ts,reply_text,actions_text,in_reply_to) VALUES ('" . (int)$da_id . "'," . (int)$user_id . ",date_part('epoch', CURRENT_TIMESTAMP)::int,'** TICKET RESOLVED **','',0)";
		$q3 = "DELETE FROM complaints_reference WHERE complaints_ref='" . (int)$da_id . "'";
		$r = pg_safe_exec($q);
		$updated=0;
		if ($r) {
			$r2 = pg_safe_exec($q2);
			if ($r2) {
				$updated = 1;
				pg_safe_exec($q3);
				if ( $daobj->from_id >0) { log_user($daobj->from_id,12,"Ticket-number: " . $_GET["ID"] . " (resolved by admin)"); }
			}
		}
		if ($updated) {
			$mm = "";
			$mm .= "Your complaint ticket number " . $_GET["ID"] . " has been marked as RESOLVED by a CService Admin\n\n";
			$mm .= "Thanks for using our Complaint System.\n\n";
			custom_mail($daobj->from_email,"[" . NETWORK_NAME . " CService Complaints] " . $_GET["ID"] . " - Resolved / Closed",$mm,"From: " . NETWORK_NAME . " Channel Service <" . OBJECT_EMAIL . ">\nReply-to: DO.NOT@REPLY.THANKS\nX-Mailer: " . NETWORK_NAME . " CService Complaint Module\n\n");
			echo "<br><br><b>This complaint ticket has been resolved. The user is being notified of this fact.</b><br><br>";
			echo "<a href=\"admin.php\">Back to Complaint Admin</a><br><br>\n";
		} else {
			echo "<br><br><b>For some strange reason, we couldn't resolve this ticket, please contact a Site Administrator.</b><br><br>";
			echo "<a href=\"admin.php\">Back to Complaint Admin</a><br><br>\n";
		}
		break;
	case 'delete':
		$q = "UPDATE complaints SET status=99,created_crc='',crc_expiration=(date_part('epoch', CURRENT_TIMESTAMP)::int+(86400*15)) WHERE id='" . (int)$da_id . "' AND ticket_number='" . $_GET["ID"] . "'";
		$q2 = "INSERT INTO complaints_threads (complaint_ref,reply_by,reply_ts,reply_text,actions_text,in_reply_to) VALUES ('" . (int)$da_id . "'," . (int)$user_id . ",date_part('epoch', CURRENT_TIMESTAMP)::int,'** TICKET REMOVED/DELETED **','',0)";
		$q3 = "DELETE FROM complaints_reference WHERE complaints_ref='" . (int)$da_id . "'";
		$r = pg_safe_exec($q);
		$updated=0;
		if ($r) {
			$r2 = pg_safe_exec($q2);
			if ($r2) {
				$updated = 1;
				pg_safe_exec($q3);
				if ( $daobj->from_id >0) { log_user($daobj->from_id,12,"Ticket-number: " . $_GET["ID"] . " (removed by admin)"); }
			}
		}
		if ($updated) {
			echo "<br><br><b>This complaint ticket has been removed. The user is NOT being notified of this fact.</b><br><br>";
			echo "<a href=\"admin.php\">Back to Complaint Admin</a><br><br>\n";
		} else {
			echo "<br><br><b>For some strange reason, we couldn't remove this ticket, please contact a Site Administrator.</b><br><br>";
			echo "<a href=\"admin.php\">Back to Complaint Admin</a><br><br>\n";
		}
		break;
}
} else { echo "<br><br>This message has already been replied to."; }

} else {
	echo "<br><br>Invalid Ticket Number.";
}

} else {
	echo "<br><br>Invalid credentials.";
}
?>
</body>
</html>
