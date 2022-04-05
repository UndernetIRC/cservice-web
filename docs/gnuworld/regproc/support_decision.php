<?
/* $Id: support_decision.php,v 1.4 2002/05/20 23:58:04 nighty Exp $ */
	$cache_page=1;
        require("../../../php_includes/cmaster.inc");
        std_connect();
        $user_id = std_security_chk($auth);
	if ($user_id<=0) {
		echo "You must be logged in to view that page. <a href=\"../index.php\" target=\"_top\">click here</a>.<br>\n";
		echo "</body></html>\n\n";
		die;
	}
        $admin = std_admin();
	$cTheme = get_theme_info();
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
        $row = pg_fetch_object($res,0);
        $user_name = $row->user_name;

$check1 = pg_safe_exec("SELECT * FROM supporters WHERE user_id='$user_id' AND channel_id='$id' AND (support='Y' OR support='N')");
if (pg_numrows($check1)>0) {
	header("Location: ../right.php\n\n");
	die;
}

$check2 = pg_safe_exec("SELECT * FROM supporters WHERE user_id='$user_id' AND channel_id='$id'");
if (pg_numrows($check2)==0) {
	header("Location: ../right.php\n\n");
	die;
}




echo "<html><head><title>REGISTRATION PROCESS</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");

$res = pg_safe_exec("SELECT name FROM channels WHERE id='$id'");
$row = pg_fetch_object($res,0);
$c_name = $row->name;

echo "<b>CHANNEL SERVICE REGISTRATION PROCESS</b> - SUPPORTER CONFIRMATION<br><hr noshade size=2><br>\n";


if (($crc==md5("$HTTP_USER_AGENT" . $ts . CRC_SALT_0012)) && $mode=="validate" && $id>0 && isset($id) && $decision<2 && $decision >=0) {
	// write decision.
	if ($decision) { // ok support confirmed.
		$query = "UPDATE supporters SET support='Y',last_updated=" . time() . " WHERE channel_id='$id' AND user_id='$user_id'";
		pg_safe_exec($query);

		//echo $query; die;

		$tmp = pg_safe_exec("SELECT COUNT(*) AS y_count FROM supporters WHERE support='Y' AND channel_id='$id'");
		$ooo = pg_fetch_object($tmp,0);
		$y_count = $ooo->y_count;

		$tmp2 = pg_safe_exec("SELECT COUNT(*) AS y_count FROM supporters WHERE channel_id='$id'");
		$ooo2 = pg_fetch_object($tmp2,0);
		$y_count2 = $ooo2->y_count;

		if ($y_count==$y_count2) { // move to PENDING (traffic check (status=1))
			$tts = time();
			pg_safe_exec("UPDATE pending SET status='1',last_updated=$tts,check_start_ts=$tts WHERE channel_id='$id' AND status='0'");
		}

		echo "<script language=\"JavaScript1.2\">\n<!--\n\tlocation.href='../right.php';\n//-->\n</script>\n";

	} else { // not ok. NON-SUPPORT :(
		if ($force!=1) {
			echo "<form name=confirm action=support_decision.php method=post>\n";
			echo "<b>If you want to add any comment about why you don't support application for that channel<br>\n";
			echo "you may add them in this text box and click the button below :</b><br><br>\n";
			echo "<textarea name=comments cols=50 rows=15></textarea><br><br>\n";
			echo "<input type=submit value=\" I DO NOT SUPPORT " . str_replace("\"","&quot;",$c_name) . " \"><br>\n";
			echo "<input type=hidden name=id value=$id>\n";
			echo "<input type=hidden name=ts value=$ts>\n";
			echo "<input type=hidden name=crc value=$crc>\n";
			echo "<input type=hidden name=mode value=validate>\n";
			echo "<input type=hidden name=decision value=0>\n";
			echo "<input type=hidden name=force value=1>\n";
			echo "</form>\n";
		} else {
			$tts = time();
			$query = "UPDATE supporters SET support='N',reason='" . str_replace("\\&quot;","&quot;",str_replace("\n","<br>",htmlspecialchars($comments))) . "',last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int WHERE channel_id='$id' AND user_id='$user_id'";
			pg_safe_exec($query);
			$quer2 = "UPDATE pending SET status='9',last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,decision_ts=date_part('epoch', CURRENT_TIMESTAMP)::int,decision='--AUTOMATIC (REGPROC)-- NON-SUPPORT' WHERE channel_id='$id' AND status='0'";
			pg_safe_exec($quer2);

			$res = pg_safe_exec("SELECT manager_id FROM pending WHERE channel_id='$id' AND status='9'");
			$row = pg_fetch_object($res,0);
			$m_id = $row->manager_id;
			$res = pg_safe_exec("SELECT user_name,email FROM users WHERE id='$m_id'");
			$row = pg_fetch_object($res,0);
			$u_name = $row->user_name;
			$applicant = $u_name . " (" . $m_id . ")";
			$def_email = $row->email;

			// Expiration in 3 days.
			$def_reason = "-NON SUPPORT-";
			$set_by = "* REGPROC";

			$quer3 = "INSERT INTO noreg (user_name,email,channel_name,type,expire_time,created_ts,set_by,reason) VALUES ('','','$c_name',1,(date_part('epoch', CURRENT_TIMESTAMP)::int+86400*3),date_part('epoch', CURRENT_TIMESTAMP)::int,'$set_by','$def_reason')";
			pg_safe_exec($quer3);
			$quer4 = "INSERT INTO noreg (user_name,email,channel_name,type,expire_time,created_ts,set_by,reason) VALUES ('$u_name','$def_email','',1,(date_part('epoch', CURRENT_TIMESTAMP)::int+86400*3),date_part('epoch', CURRENT_TIMESTAMP)::int,'$set_by','$def_reason')";
			pg_safe_exec($quer4);


			$tmp = pg_safe_exec("SELECT supporters.user_id,users.user_name FROM supporters,pending,users WHERE supporters.user_id=users.id AND pending.channel_id=supporters.channel_id AND pending.channel_id='$id'");
			$sup_list = "";

			for ($x=0;$x<pg_numrows($tmp);$x++) {
				$row = pg_fetch_object($tmp,$x);
				$uname = $row->user_name;
				$uuid = $row->user_id;
				$sup_list .= "$uname (" . $uuid . ")";
				if ($x!=(pg_numrows($tmp)-1)) { $sup_list .=", "; }
			}

			log_channel($id,16,"NON-SUPPORT from $user_name (" . str_replace("\\&quot;","&quot;",str_replace("\n","<br>",htmlspecialchars($comments))) . ") - Applicant was: $applicant, Supporters were: " . $sup_list);

			//echo htmlspecialchars($query); die;
			echo "<script language=\"JavaScript1.2\">\n<!--\n\tlocation.href='../right.php';\n//-->\n</script>\n";
		}
	}
	echo "</body></html>\n\n";
	die;
}

// read decision.

echo "<form>\n";

// ADD INFORMATION (applicant username, description)

$q = "SELECT * FROM pending WHERE channel_id='$id' AND status='0'";
$r = pg_safe_exec($q);
$o = pg_fetch_object($r,0);
$m_id = $o->manager_id;
$c_desc = $o->description;
/*
$tmp = pg_safe_exec("SELECT name FROM channels WHERE id='$channel_id'");
$tro = pg_fetch_object($tmp,0);
$chan_name = $tro->name;
*/
$tmp = pg_safe_exec("SELECT user_name FROM users WHERE id='$m_id'");
$tro = pg_fetch_object($tmp,0);
$m_name = $tro->user_name;


echo "<u>Channel name :</u> <font size=+1><b>$c_name</b></font><br><br>\n";
echo "<u>Applicant username :</u> <font size=+1><b>$m_name</b></font><br><br>\n";
echo "<u>Channel description :</u> <br><font size=+0><b>$c_desc</b></font><br><br>\n";
echo "<u>Listed supporters :</u><br><font size=+0><b>";
$q2 = "SELECT * FROM supporters WHERE channel_id='$id'";
$r2 = pg_safe_exec($q2);
$x=0;
while (pg_numrows($r2)>$x && $o2 = pg_fetch_object($r2,$x)) {
	$ttt = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $o2->user_id . "'");
	$ooo = pg_fetch_object($ttt,0);
	echo $ooo->user_name;
	echo "&nbsp;&nbsp;";
	$x++;
}

echo "</b></font><br><br><br><br>\n";

$c_name=str_replace("\"","&quot;",$c_name);

echo "<table><tr bgcolor=#" . $cTheme->main_yes . "><td align=center>\n";

echo "<h4><br>If you support this channel, click the button below :<br>\n";
echo "<input type=button value=\" I SUPPORT CHANNEL $c_name \" onClick=\"i_support();\"><br><br></h4>\n";

echo "</td></tr><tr bgcolor=#" . $cTheme->main_no . "><td align=center>\n";

echo "<h4><br>If you <b>DO NOT</b> support this channel, click the button below :<br>\n";
echo "<input type=button value=\" I DO NOT SUPPORT CHANNEL $c_name \" onClick=\"i_dontsupport();\">\n";
echo "</h4>\n";

echo "</td></tr></table>\n";
$zets = time();
$zecrc = md5("$HTTP_USER_AGENT" . $zets . CRC_SALT_0012);
?>
<script language="JavaScript1.2">
<!--
function i_support() {
	location.href = 'support_decision.php?id=<? echo $id ?>&ts=<? echo $zets ?>&crc=<? echo $zecrc ?>&mode=validate&decision=1';
	return(true);
}
function i_dontsupport() {
	location.href = 'support_decision.php?id=<? echo $id ?>&ts=<? echo $zets ?>&crc=<? echo $zecrc ?>&mode=validate&decision=0';
	return(true);
}
//-->
</script>
<?
echo "</form>\n";
echo "<br><br>\n";
echo "<a href=\"../right.php\">I dont want to decide now, bring me back to my last page please...</a>\n";
?>
</body>
</html>
