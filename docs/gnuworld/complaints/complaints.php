<?
/* $Id: complaints.php,v 1.5 2004/07/25 03:31:51 nighty Exp $ */
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
<td valign=top><img border=0 alt=" * ! B O O M ! *" src=complaint_dpt.jpg></td>
<td valign=top>
<? if (($_GET["ct"]+0)>0 && (($_GET["ct"]+0)<(MAX_COMPLAINT_TYPE+1) || $_GET["ct"]==99)) {
	echo "<font size=+2><b>" . $cpt_name[$_GET["ct"]] . "</b></font><br><a href=\"javascript:history.go(-1);\"><b>&lt;&lt;&nbsp;back</b></a><br><br>";
	$from_email = "";
	if ($user_id==0) {
		if (strtolower(trim($_GET["email"])) == strtolower(trim($_GET["email2"]))) {
			$from_email = strtolower(trim($_GET["email"]));
			if( !(preg_match( "/^[A-Za-z0-9_+-.]+@[A-Za-z0-9.-]+\.[A-Za-z][A-Za-z]+$/", $from_email )) ) {
				echo "<big>e-mail syntax is invalid</big>.";
				die("</td></tr></table></body></html>");
			} else {

			}
		} else {
			echo "<big>Please check your e-mail, both entries do not match</big>.";
			die("</td></tr></table></body></html>");
		}
	} else {

		if ($user_id > 0 && $_GET["ct"]==1) {
			echo "<b>Apparently its not suspended, you are authenticated :)</b>";
			die("</td></tr></table></body></html>");
		}

		$uInfo = get_user_info();
		$from_email = $uInfo->email;
		if ($_GET["mailconf"]!=1) {
			echo "The e-mail address we have in record for you is <b>" . $from_email . "</b>,<br>";
			echo "<u><b>if this e-mail address is incorrect</b></u>, or<br>";
			echo "<u><b>if you can't receive e-mail there anymore</b></u>, please :<br>";
			echo "<ul>";
			echo "<li> Download and complete the <a href=\"../../docs/Email.txt\">text form</a>\n";
			echo "<li> mail it from your new e-mail address to <a href=\"mailto:" . XAT_EMAIL . "\"><b>" . XAT_EMAIL . "</b></a>\n";
			echo "</ul>";
			echo "You can alternatively specify an e-mail for us to answer to your complaint and process your request<br>";
			echo "note that <b>this will NOT change your e-mail in record of your username</b>, you would still need to<br>";
			echo "follow the steps described above to change it.<br>";
			echo "<form name=confirm method=get>";
			echo "<input type=hidden name=ct value=" . $_GET["ct"] . ">\n";
			echo "<input type=text name=email size=50 maxlength=255> your e-mail address<br>";
			echo "<input type=text name=email2 size=50 maxlength=255> your e-mail address again (confirmation)<br>";
			echo "<i>you have to choose a working e-mail address where you can read mail</i><br>";
			echo "<input type=hidden name=mailconf value=1>\n";
			echo "<input type=hidden name=nmail value=1>\n";
			echo "<br><br><input type=submit value=\"Use the specified e-mail\">\n";
			echo "<br><br><br><br>";
			echo "If you CAN read mail received at <b>" . $from_email . "</b>, then you can <input type=button value=\"Continue...\" onClick=\"location.href='complaints.php?ct=" . $_GET["ct"] . "&mailconf=1'\"><br><br>\n";
			echo "</form>";
			echo "</td></tr></table></body></html>\n\n";
			die;
		}

		if ($_GET["nmail"]==1 && $_GET["email"]=="" && $_GET["email2"]=="") {
			echo "<big>You must specify an e-mail address</big>.";
			die("</td></tr></table></body></html>");
		}

		if ($_GET["email"]!="" && $_GET["email2"]!="") {
			if (strtolower(trim($_GET["email"])) == strtolower(trim($_GET["email2"]))) {
				$from_email = strtolower(trim($_GET["email"]));
				if( !(preg_match( "/^[A-Za-z0-9_+-.]+@[A-Za-z0-9.-]+\.[A-Za-z][A-Za-z]+$/", $from_email )) ) {
					echo "<big>e-mail syntax is invalid</big>.";
					die("</td></tr></table></body></html>");
				} else {

				}
			} else {
				echo "<big>Please check your e-mail, both entries do not match</big>.";
				die("</td></tr></table></body></html>");
			}
		}

	}


	echo "<form name=complaintreq method=post action=record.php>\n";
	make_secure_form("complaintreq" . $from_email . ($user_id+0),1800);
	if ($user_id==0) {
		echo "<input type=hidden name=from_mail value=\"" . $from_email . "\">\n";
		echo "<input type=hidden name=from_id value=0>\n";
	} else {
		//echo "<INPUT TYPE=hidden name=\"MAX_FILE_SIZE\" value=102400>";
		echo "<input type=hidden name=from_mail value=\"" . $from_email . "\">\n";
		echo "<input type=hidden name=from_id value=" . ($user_id+0) . ">\n";
	}
	echo "<input type=hidden name=ct value=\"" . $_GET["ct"] . "\">\n";
switch($_GET["ct"]) {
	case 1: // username suspended
		if ($user_id==0) {
			echo "<b>Your username</b> :<br><input type=text name=login size=20 maxlength=12><br><b>Your password</b> :<br><input type=password name=passwd size=20 maxlength=255><br><i>This check will work even if the login page says you're suspended</i><br><br>\n";
			echo "<b>Complaint summary</b> :<br><textarea name=complaint_text cols=60 rows=10 wrap></textarea><br><br>\n";
			echo "<b>Complaint logs</b> : (<i>optional</i>)<br><textarea name=complaint_logs cols=60 rows=10 wrap></textarea><br><br>\n";
		} else {
			echo "<b>Apparently its not suspended, you are authenticated :)</b>";
		}
		break;
	case 2: // members of regged chan spam my channel
		if ($user_id==0) {
			echo "<b>Your username</b> : (<i>optional</i>)<br><input type=text name=user_name size=30 maxlength=20><br><br>\n";
		}
		echo "<b>Your channel name</b> :<br><input type=text name=channel_name1 size=60 maxlength=255><br><br>\n";
		echo "<b>The offending channel name</b> :<br><input type=text name=channel_name2 size=60 maxlength=255><br><br>\n";
		echo "<b>Complaint summary</b> :<br><textarea name=complaint_text cols=60 rows=10 wrap></textarea><br><br>\n";
		echo "<b>Complaint logs</b> : (<i>optional</i>)<br><textarea name=complaint_logs cols=60 rows=10 wrap></textarea><br><br>\n";
		//if ($user_id>0) { echo "<b>Upload ZIP file (100K max)</b> : (<i>optional</i>)<br><input type=file size=45 name=ziplogfile><br><br>\n"; }
		break;
	case 3: // object anonymously to an application
		echo "<b>The channel name you want to object to</b> :<br><input type=text name=channel_name1 size=60 maxlength=255><br><br>\n";
		echo "<b>Complaint summary</b> :<br><textarea name=complaint_text cols=60 rows=10 wrap></textarea><br><br>\n";
		echo "<b>Complaint logs</b> : (<i>optional</i>)<br><textarea name=complaint_logs cols=60 rows=10 wrap></textarea><br><br>\n";
		//if ($user_id>0) { echo "<b>Upload ZIP file (100K max)</b> : (<i>optional</i>)<br><input type=file size=45 name=ziplogfile><br><br>\n"; }
		break;
	case 4: // my channel purged / reconsidere
		echo "<b>Your purged channel name</b> :<br><input type=text name=channel_name1 size=60 maxlength=255><br><br>\n";
		echo "<b>Complaint summary</b> :<br><textarea name=complaint_text cols=60 rows=10 wrap></textarea><br><br>\n";
		echo "<b>Complaint logs</b> : (<i>optional</i>)<br><textarea name=complaint_logs cols=60 rows=10 wrap></textarea><br><br>\n";
		//if ($user_id>0) { echo "<b>Upload ZIP file (100K max)</b> : (<i>optional</i>)<br><input type=file size=45 name=ziplogfile><br><br>\n"; }
		break;
	case 5: // my channel purged / know why?
		echo "<b>Your purged channel name</b> :<br><input type=text name=channel_name1 size=60 maxlength=255><br><br>\n";
		echo "<b>Complaint summary</b> :<br><textarea name=complaint_text cols=60 rows=10 wrap></textarea><br><br>\n";
		echo "<b>Complaint logs</b> : (<i>optional</i>)<br><textarea name=complaint_logs cols=60 rows=10 wrap></textarea><br><br>\n";
		//if ($user_id>0) { echo "<b>Upload ZIP file (100K max)</b> : (<i>optional</i>)<br><input type=file size=45 name=ziplogfile><br><br>\n"; }
		break;
	default:
	case 99: // other complaints types (against csc personel etc... for example)
		echo "<b>Complaint summary</b> :<br><textarea name=complaint_text cols=60 rows=10 wrap></textarea><br><br>\n";
		echo "<b>Complaint logs</b> : (<i>optional</i>)<br><textarea name=complaint_logs cols=60 rows=10 wrap></textarea><br><br>\n";
		//if ($user_id>0) { echo "<b>Upload ZIP file (100K max)</b> : (<i>optional</i>)<br><input type=file size=45 name=ziplogfile><br><br>\n"; }
		break;
}
	if (!($_GET["ct"]==1 && $user_id>0)) {
		echo "<p><input type=submit value=\"SUBMIT\">&nbsp;&nbsp;<input type=button value=\"CANCEL\" onClick=\"history.go(-1)\"></p>\n";
	}
	echo "</form>";
   } else {
	echo "<font size=+1>Welcome to the <b>" . NETWORK_NAME . " CService Complaint Department</b></font><br><br>";
	if ($user_id==0) {
		echo "If you were <a href=\"../login.php?redir=" . urlencode("complaints/complaints_inframe.php") . "\"><b>logged in</b></a>, you could access more features<br>and be asked less questions.<br><br>\n";
	} else {
		echo "You are authenticated. Good :)<br>";
		if ($admin>0) {
			echo ".. and you're a CService official, even better !!!<br>";
		}
		echo "<br>\n";
	}
?>
<form name=complaint method=get>
<select name=ct>
<option selected value=0>-- please select a complaint type --</option>
<?
	for ($x=1;$x<=MAX_COMPLAINT_TYPE;$x++) {
		echo "<option value=" . $x . ">" . $cpt_name[$x] . "</option>\n";
	}
?>
<option value=0>--</option>
<option value=99>other...</option>
</select><?
if ($user_id>0) { echo "&nbsp;"; } else {
	echo "<br><input type=text name=email size=30 maxlength=255>&nbsp;e-mail address<br>\n";
	echo "<input type=text name=email2 size=30 maxlength=255>&nbsp;e-mail address again (confirmation)<br>\n";
	echo "<font size=-1><i>this e-mail adress must be valid and will only be used to answer to you.</i></font>&nbsp;";
}
?><input type=submit value=Go!>
</form>
<br><br>
<!--<i>This system is in <b>draft</b> mode.<br>//-->
Your request will be processed faster if you pick the right <b>complaint type</b>.</i><br><br>
For requests concerning non registered / non pending channels,<br>IRC operator issues, G:Lines or K:Lines please contact <a href="mailto:<?=ABUSE_GLOBAL_EMAIL?>"><b><?=ABUSE_GLOBAL_EMAIL?></b></a>.
<? } ?>
</td></tr></table>
</body>
</html>
