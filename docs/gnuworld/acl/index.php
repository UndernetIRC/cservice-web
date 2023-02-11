<?
	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = isset($_COOKIE["auth"]) ? std_security_chk($_COOKIE["auth"]) : 0;
        $admin = std_admin();
	$cTheme = get_theme_info();
        if ($admin<=0 && !acl()) {
                echo "Restricted to logged in CService Admins, sorry.";
                die;
        }
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
        $adm_usr = pg_fetch_object($res,0);
        $adm_user = $adm_usr->user_name;
        if (!acl(XWEBACL) && !acl(XWEBCTL)) {
        	echo "Sorry, your admin access is too low.";
        	die;
        }

echo "<html><head><title>Access Control List Manager</title>";
if ($admin>=800) {
?>
<script language="JavaScript1.2">
<!--
function check(f) {

	if (f.XWEBACL[1].checked) {
		if (f.XCHGMGR[0].checked && f.XMAILCH[0].checked && !f.XWEBAXS[2].checked) {
			if (confirm("Allowing 'ACL Page' access to someone that is not an <?=BOT_NAME?>@Admin or a WebAxs level 3 only allows him to view ACLs list.\n\n")) {
				return(true);
			} else {
				f.XWEBACL[0].checked=true;
				f.XWEBACL[1].checked=false;
				return(false);
			}
		}
	}
	if (f.XSUSPEND_USR[1].checked || f.XUNSUSPEND_USR[1].checked) {
		if (!f.XAT_CAN_EDIT.checked) {
			if (confirm("Giving suspension and/or unsuspension access to someone automatically gives him ability to edit user records on the page if the level wasn't allowing it already, please click 'OK' to continue.")) {
				f.XAT_CAN_EDIT.checked=true;
				return(true);
			} else {
				f.XSUSPEND_USR[0].checked=true;
				f.XSUSPEND_USR[1].checked=false;
				f.XUNSUSPEND_USR[0].checked=true;
				f.XUNSUSPEND_USR[1].checked=false;
				return(false);
			}
		}
	}
	return(true);
}
function get_help(topic) {
	var da_url = 'help.php#'+topic;
	var da_win = window.open(da_url, 'ACLHELP', 'scrollbars=yes,width=350,height=200,top=30,left=30,screenX=30,screenY=30,resizable=no');
	da_win.focus();
}
//-->
</script>
<?
}
std_theme_styles();
echo "</head>\n";
std_theme_body("../");

$zets = time();

echo "<h2>";

if (acl(XWEBACL) && acl(XWEBCTL)) {
echo "ACL Manager / Site Control";
} else {
	if (acl(XWEBACL)) { echo "ACL Manager"; }
	if (acl(XWEBCTL)) { echo "Site Control"; }
}

echo "</h2><hr size=1 noshade>\n";

if (acl(XWEBCTL)) {

echo "<table width=500 border=1 cellspacing=0 cellpadding=0>\n";
echo "<tr bgcolor=#" . $cTheme->table_headcolor . "><td><font face=arial,helvetica color=#" . $cTheme->table_headtextcolor . "><b>SITE CONTROL</b></font></td></tr>\n";
echo "<tr><td align=center>\n";
echo "<form>";
$blabla = pg_safe_exec("SELECT count_count FROM counts WHERE count_type='1'");
if (pg_numrows($blabla)==0) { $c_nu = 0; } else {
	$bloblo=pg_fetch_object($blabla,0);
	$c_nu = $bloblo->count_count;
}
echo "- Newusers Current Count : <b>" . $c_nu . "</b> out of " . $MAX_ALLOWED_USERS . "<br>(unlocking <b>new users</b> will reset that count)\n";
echo "<table width=100% border=1 cellspacing=0 cellpadding=2>\n";
echo "<tr bgcolor=#" . $cTheme->table_tr_enlighten . "><td><b>Lock Type</b></td><td><b>Status</b></td><td><b>Additional Info</b></td></tr>\n";

echo "<tr><td>Login</td>";
if (site_off()) {
	echo "<td><font color=#" . $cTheme->main_no . "><b>LOCKED</b></font>&nbsp;<input type=button onClick=\"location.href='site_status.php?switch=OFF';\" value=\" :) \"></td>";
	echo "<td>by <b>" . $LOCKED_BY . "</b>, since " . $LOCKED_SINCE . "</td>";
} else {
	echo "<td><font color=#" . $cTheme->main_yes . "><b>OPEN</b></font>&nbsp;<input type=button onClick=\"location.href='site_status.php?switch=ON';\" value=\" :( \"></td>";
	echo "<td>&nbsp;</td>";
}
echo "</tr>";

echo "<tr><td>New Regs</td>";
if (newregs_off()) {
	echo "<td><font color=#" . $cTheme->main_no . "><b>LOCKED</b></font>&nbsp;<input type=button onClick=\"location.href='newregs.php?switch=OFF';\" value=\" :) \"></td>";
	echo "<td>by <b>" . $LOCKED_BY . "</b>, since " . $LOCKED_SINCE . "</td>";
} else {
	echo "<td><font color=#" . $cTheme->main_yes . "><b>OPEN</b></font>&nbsp;<input type=button onClick=\"location.href='newregs.php?switch=ON';\" value=\" :( \"></td>";
	echo "<td>&nbsp;</td>";
}
echo "</tr>";

echo "<tr><td>New Users</td>";
if (newusers_off()) {
	echo "<td><font color=#" . $cTheme->main_no . "><b>LOCKED</b></font>&nbsp;<input type=button onClick=\"location.href='newusers.php?switch=OFF';\" value=\" :) \"></td>";
	echo "<td>by <b>" . $LOCKED_BY . "</b>, since " . $LOCKED_SINCE . "</td>";
} else {
	echo "<td><font color=#" . $cTheme->main_yes . "><b>OPEN</b></font>&nbsp;<input type=button onClick=\"location.href='newusers.php?switch=ON';\" value=\" :( \"></td>";
	echo "<td>&nbsp;</td>";
}
echo "</tr>";

echo "<tr><td>Complaint System</td>";
if (complaints_off()) {
	echo "<td><font color=#" . $cTheme->main_no . "><b>LOCKED</b></font>&nbsp;<input type=button onClick=\"location.href='complaints.php?switch=OFF';\" value=\" :) \"></td>";
	echo "<td>by <b>" . $LOCKED_BY . "</b>, since " . $LOCKED_SINCE . "</td>";
} else {
	echo "<td><font color=#" . $cTheme->main_yes . "><b>OPEN</b></font>&nbsp;<input type=button onClick=\"location.href='complaints.php?switch=ON';\" value=\" :( \"></td>";
	echo "<td>&nbsp;</td>";
}
echo "</tr>";

echo "</table>\n";
echo "<font color=#" . $cTheme->main_textlight . " size=-1>NOTE: <b>LOCKED</b> means 'Restricted to CService Staff'.\n";
echo "</form>";
echo "</td></tr>\n";
echo "</table>\n";
echo "<br>";

}

if (acl(XWEBACL)) {

echo "<b>";
if ($admin>=800 || acl(XCHGMGR_ADMIN) || acl(XMAILCH_ADMIN) || acl(XWEBAXS_3)) {
echo "This interface below allows you to centralize access edition for various points in the website</b>\n";
} else {
echo "This interface below allows you to list current ACLs</b>\n";
}



echo "<table border=0 cellspacing=30 cellpadding=5>\n";
echo "<tr>\n";
if ($admin>=800 || acl(XCHGMGR_ADMIN) || acl(XMAILCH_ADMIN) || acl(XWEBAXS_3)) {
echo "<td bgcolor=#" . $cTheme->main_acl_create . " valign=top><font face=arial,helvetica size=-1>\n";
echo "<form name=newacl action=acl.php method=post onsubmit=\"return check(this);\">\n";
echo "<input type=hidden name=mode value=newacl>\n";
echo "<input type=hidden name=ts value=" . $zets . ">\n";
echo "<input type=hidden name=crc value=" . md5($HTTP_USER_AGENT . $CRC_SALT_0007 . $user_id . $zets ) . ">\n";

echo "<p align=center><font size=+1><b>Create ACL</b></font></p>\n";

echo "<i>admins with a level of 800 or above have<br>automatically the equivalent of full rights over ACLs</i><br>\n";
$spc = "&nbsp;&nbsp;&nbsp;&nbsp;";

echo "<br>";
echo "Add <b>ACL</b> for ";

@pg_safe_exec("DELETE FROM acl WHERE user_id=0"); // cleanup invalid records if any

if ($admin>=800 && ACL_FOR_ANY==1) { echo ":<br>\n<input type=radio name=uchoice value=1 checked>&nbsp;"; } else { echo "<input type=hidden name=uchoice value=1>"; }
echo "<select onClick=\"document.forms[1].uchoice[0].checked=true;\" name=userid>\n";
$res = pg_safe_exec("SELECT * FROM levels,users WHERE levels.channel_id=1 AND levels.access>0 AND levels.access<800 AND users.id=levels.user_id ORDER BY users.user_name");
for ($x=0;$x<pg_numrows($res);$x++) {
	$row = pg_fetch_object($res,$x);
	$sres = pg_safe_exec("SELECT * FROM acl WHERE user_id='" . $row->user_id . "'");
	if (pg_numrows($sres)==0) {
		echo "<option value=" . $row->user_id . ">" . $row->user_name . " (*" . $row->access . ")</option>\n";
	}
}
echo "</select> ";
if ($admin>=800 && ACL_FOR_ANY==1) { echo "<br>\n<input type=radio name=uchoice value=2>&nbsp;Username : <input onClick=\"document.forms[1].uchoice[1].checked=true;\" type=text name=spcuname size=20 maxlength=12><br>\n"; }
echo "with following rights :<br><br>\n";
//echo "<ul>\n";

if (acl(XCHGMGR_ADMIN)) {

echo "<li> <b>" . BOT_NAME . "@ Manager Changes</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XCHGMGR');\">help</a>)<br>";
echo $spc . "<input type=radio name=XCHGMGR value=0 checked> NO ACCESS<br>";
echo $spc . "<input type=radio name=XCHGMGR value=1> Reviewer<br>";
if ($admin>=800) {
echo $spc . "<input type=radio name=XCHGMGR value=2> Admin<br>";
}
echo "<br>\n";

}

if (acl(XMAILCH_ADMIN)) {

echo "<li> <b>" . BOT_NAME . "@ E-Mail-in-Record Changes</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XMAILCH');\">help</a>)<br>";
echo $spc . "<input type=radio name=XMAILCH value=0 checked> NO ACCESS<br>";
echo $spc . "<input type=radio name=XMAILCH value=1> Reviewer<br>";
if ($admin>=800) {
echo $spc . "<input type=radio name=XMAILCH value=2> Admin<br>";
}
echo "<br>\n";

}

if (acl(XCHGMGR_ADMIN) || acl(XMAILCH_ADMIN)) {
	echo "<li> <b>" . BOT_NAME . "@ Team (Email+Mgr changes) Globals</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XAT_GLOBALS');\">help</a>)<br>";
	echo $spc . "<input type=checkbox name=XAT_CAN_VIEW value=1> Can view verification data<br>";
	echo $spc . "<input type=checkbox name=XAT_CAN_EDIT value=1> Can edit users<br>";
	echo "<br>\n";
}

if ($admin>=800) {

echo "<li> <b>" . BOT_NAME . "@ Help Management</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XHELP');\">help</a>)<br>";
echo $spc . "<input type=radio name=XHELP value=0 checked> NO ACCESS<br>";
echo $spc . "<input type=radio name=XHELP value=1> Help Manager<br>";
echo $spc . "<select name=xtra><option value=0>-- All Languages --</option>\n";
$bla = pg_safe_exec("SELECT * FROM languages ORDER BY name");
for ($x=0;$x<pg_numrows($bla);$x++) {
	$blo = pg_fetch_object($bla,$x);
	echo "<option value=" . $blo->id . ">" . $blo->name . "</option>\n";
}
echo "</select><br>";
echo $spc . "<input type=checkbox name=CAN_ADD value=1> Can ADD commands to the HELP set<br>";
echo $spc . "<input type=checkbox name=CAN_EDIT value=1 checked> Can EDIT HELP text replies (recommended)<br>";
echo "<br>\n";

echo "<li> <b>" . BOT_NAME . "@ TOTP disable for others</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XHELP');\">help</a>)<br>";
echo $spc . "<input type=radio name=XTOTP value=0 checked> Disabled<br>";
echo $spc . "<input type=radio name=XTOTP value=1 > Enabled<br>";

echo "<li> <b>MIA system</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('MIA');\">help</a>)<br>";
echo $spc . "<input type=radio name=MIA_W value=0 checked> Disabled<br>";
echo $spc . "<input type=radio name=MIA_W value=1 > Enabled<br>";
}

if (acl(XWEBAXS_3)) {

echo "<li> <b>WebAXS Team (Channel Review Team)</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XWEBAXS');\">help</a>)<br>";
echo $spc . "<input type=radio name=XWEBAXS value=0 checked> NO ACCESS<br>";
echo $spc . "<input type=radio name=XWEBAXS value=2> Reviewer (level 2)<br>";
if ($admin>=800) {
echo $spc . "<input type=radio name=XWEBAXS value=3> Admin (level 3)<br>";
}
echo "<br>\n";

}

if ($admin>=800) {

echo "<li> <b>Site Control Access</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XWEBCTL');\">help</a>)<br>";
echo $spc . "<input type=radio name=XWEBCTL value=0 checked> Disabled<br>";
echo $spc . "<input type=radio name=XWEBCTL value=1> Enabled<br>";
echo "<br>\n";

echo "<li> <b>ACL Page access</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XWEBACL');\">help</a>)<br>";
echo $spc . "<input type=radio name=XWEBACL value=0 checked> Disabled<br>";
echo $spc . "<input type=radio name=XWEBACL value=1> Enabled<br>";
echo "<br>\n";

echo "<li> <b>DomainLock ADD/REMOVE access</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XDOMAIN_LOCK');\">help</a>)<br>";
echo $spc . "<input type=radio name=XDOMAIN_LOCK value=0 checked> Disabled<br>";
echo $spc . "<input type=radio name=XDOMAIN_LOCK value=1> Enabled<br>";
echo "<br>\n";

echo "<li> <b>User Toaster access</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XWEBUSR_TOASTER');\">help</a>)<br>";
echo $spc . "<input type=radio name=XWEBUSR_TOASTER value=0 checked> Disabled<br>";
echo $spc . "<input type=radio name=XWEBUSR_TOASTER value=1> Enabled (view/post)<br>";
echo $spc . "<input type=radio name=XWEBUSR_TOASTER value=2> Enabled (view only)<br>";
echo "<br>\n";

echo "<li> <b>User suspension access</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XSUSPEND_USR');\">help</a>)<br>";
echo $spc . "<input type=radio name=XSUSPEND_USR value=0 checked> Disabled<br>";
echo $spc . "<input type=radio name=XSUSPEND_USR value=1> Enabled<br>";
echo "<br>\n";

echo "<li> <b>User unsuspension access</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XUNSUSPEND_USR');\">help</a>)<br>";
echo $spc . "<input type=radio name=XUNSUSPEND_USR value=0 checked> Disabled<br>";
echo $spc . "<input type=radio name=XUNSUSPEND_USR value=1> Enabled<br>";
echo "<br>\n";


echo "<li> <b>Current web sessions view</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XWEBSESS');\">help</a>)<br>";
echo $spc . "<input type=radio name=XWEBSESS value=0 checked> Disabled<br>";
echo $spc . "<input type=radio name=XWEBSESS value=1> Enabled<br>";
echo "<br>\n";

echo "<li> <b>Admin logging view</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XLOGGING_VIEW');\">help</a>)<br>";
echo $spc . "<input type=radio name=XLOGGING_VIEW value=0 checked> Disabled<br>";
echo $spc . "<input type=radio name=XLOGGING_VIEW value=1> Enabled<br>";
echo "<br>\n";

echo "<li> <b>Complaints Admin Access</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XCOMPLAINTS_ADM');\">help</a>)<br>";
echo $spc . "<input type=radio name=XCOMPLAINTS_ADM value=0 checked> NO ACCESS<br>";
echo $spc . "<input type=radio name=XCOMPLAINTS_ADM value=1> Read only<br>";
echo $spc . "<input type=radio name=XCOMPLAINTS_ADM value=2> Read & Reply<br>";
echo "<br>\n";

echo "<li> <b>IP/Host list Access</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XIPR_AXS');\">help</a>)<br>";
echo $spc . "<input type=radio name=XIPR_AXS value=0 checked> NO ACCESS<br>";
echo $spc . "<input type=radio name=XIPR_AXS value=1> Access as follows:<br>";
echo $spc. $spc . "<input type=checkbox name=XIPR_VIEW1 value=1> View own<br>";
echo $spc. $spc . "<input type=checkbox name=XIPR_VIEW2 value=1> View others<br>";
echo $spc. $spc . "<input type=checkbox name=XIPR_MOD1 value=1> Change own<br>";
echo $spc. $spc . "<input type=checkbox name=XIPR_MOD2 value=1> Change others<br>";
echo "<br>\n";
}

echo "<br><br>";

echo $spc . $spc . $spc . "<input type=submit value=\" CREATE ACL \">\n";
echo "</form>";
echo "</font></td>\n";
}
echo "<td bgcolor=#" . $cTheme->main_acl_edit . " valign=top><font face=arial,helvetica size=-1>\n";
echo "<form name=editacl action=acl.php method=get>\n";
echo "<input type=hidden name=mode value=editacl>\n";
echo "<input type=hidden name=ts value=" . $zets . ">\n";
echo "<input type=hidden name=crc value=" . md5($HTTP_USER_AGENT . $CRC_SALT_0010 . $user_id . $zets ) . ">\n";

if ($admin>=800 || acl(XCHGMGR_ADMIN) || acl(XMAILCH_ADMIN) || acl(XWEBAXS_3)) {
echo "<p align=center><font size=+1><b>Edit ACL</b></font></p>\n";
echo "<br><br><br>\n";
$res = pg_safe_exec("SELECT * FROM acl,users WHERE acl.user_id=users.id ORDER BY lower(users.user_name)");
if (pg_numrows($res)>0) {
	echo "Edit <b>ACL</b> for ";
	echo "<select name=userid>\n";
}
for ($x=0;$x<pg_numrows($res);$x++) {
	$row = pg_fetch_object($res,$x);
	echo "<option value=\"" . $row->user_id . "\">" . $row->user_name;
	$sres = pg_safe_exec("SELECT * FROM levels WHERE channel_id=1 AND user_id='" . $row->user_id . "' AND access>0");
	if (pg_numrows($sres)>0) {
		$srow = pg_fetch_object($sres,0);
		echo " (*" . $srow->access . ")";
	}
	echo "</option>\n";
}
if (pg_numrows($res)>0) {
	echo "</select>";
	echo "<br>" . $spc . "<input type=submit value=\"Go!\">\n";
} else {
	echo "<font size=+0 color=#" . $cTheme->main_warnmsg . "><b>no ACL defined</b></font>";
}

echo "</form>\n";
echo "<br><br><br>\n";
}
echo "<p align=center><font size=+1><b>Get ACL List</b></font></p>\n";
echo "<form name=getlist action=acl.php method=get>\n";
echo "<input type=hidden name=mode value=getlist>\n";
echo "<input type=hidden name=ts value=" . $zets . ">\n";
echo "<input type=hidden name=crc value=" . md5($HTTP_USER_AGENT . $CRC_SALT_0009 . $user_id . $zets ) . ">\n";

echo $spc . "<input type=submit value=\"Go!\">\n";

echo "</form>\n";

echo "<br><br><br>\n";
echo "<p align=center><font size=+1><b>Required # Supporters</b></font></p>\n";
echo "<form name=getlist action=acl.php method=get>\n";
echo "<input type=hidden name=mode value=modnbsup>\n";
echo "<input type=hidden name=ts value=" . $zets . ">\n";
echo "<input type=hidden name=crc value=" . md5($HTTP_USER_AGENT . $CRC_SALT_0012 . $user_id . $zets ) . ">\n";

echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

if (REQUIRED_SUPPORTERS>0) {
	echo "<big><b>" . REQUIRED_SUPPORTERS . "</b></big>";
} else {
	echo "<big><b>none</b></big>";
}

if ($admin>900) {
echo $spc . "<input type=submit value=\"Modify\">\n";
}

echo "</form>\n";

echo "</font></td>\n";


echo "</tr>\n";

echo "</table>\n";

}


?><br>
</body>
</html>
