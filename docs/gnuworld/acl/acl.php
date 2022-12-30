<?
	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = std_security_chk($auth);
        $admin = std_admin();
        if ($admin<=0 && !acl()) {
                echo "Restricted to logged in CService Admins, sorry.";
                die;
        }
	$cTheme = get_theme_info();
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
        $adm_usr = pg_fetch_object($res,0);
        $adm_user = $adm_usr->user_name;
        if (!acl(XWEBACL) && !acl(XWEBCTL)) {
        	echo "Sorry, your admin access is too low.";
        	die;
        }

	if ($mode=="modnbsup") {
		header("Location: nbsupmod.php\n\n");
		die;
	}

	if ($mode=="remove" && $crc == md5($acl_id . CRC_SALT_0011 . $user_id) && $admin>=800) {

		$zets = time();
		$zecrc = md5($HTTP_USER_AGENT . $CRC_SALT_0009 . $user_id . $zets );

		$back_URL = Array(1=>"acl.php?mode=getlist&ts=" . $zets . "&crc=" . $zecrc,2=>"index.php");
		pg_safe_exec("DELETE FROM acl WHERE acl_id='" . $acl_id . "'");
		header("Location: " . $back_URL[$bu] . "\n\n");
		die;


	}


       if ($mode=="newacl" && $crc == md5( $HTTP_USER_AGENT . $CRC_SALT_0007 . $user_id . $ts )) {

       		$test = pg_safe_exec("SELECT * FROM levels WHERE levels.channel_id=1 AND levels.user_id='" . $userid . "' AND access>0");
       		if (pg_numrows($test)>0) { $isstaff = 1; } else { $isstaff = 0; }

       		$flags = 0;

       		if ($XCHGMGR==2) { $flags = $flags|XCHGMGR_ADMIN; } else {
       			if ($XCHGMGR==1) { $flags = $flags|XCHGMGR_REVIEW; }
       		}
       		if ($XMAILCH==2) { $flags = $flags|XMAILCH_ADMIN; } else {
       			if ($XMAILCH==1) { $flags = $flags|XMAILCH_REVIEW; }
       		}

		if ($XHELP==1) {
			$flags = $flags|XHELP;
			if ($CAN_ADD) { $flags = $flags|XHELP_CAN_ADD; }
			if ($CAN_EDIT) { $flags = $flags|XHELP_CAN_EDIT; }
		}
		if ($XHELP==0) { $xtra = 0; }

		if ($XWEBAXS==3) { $flags = $flags|XWEBAXS_3; } else {
			if ($XWEBAXS==2) { $flags = $flags|XWEBAXS_2; }
		}
		if ($XMAILCH==1 || $XMAILCH==2 || $XCHGMGR==1 || $XCHGMGR==2) {
			if ($XAT_CAN_VIEW==1) { $flags = $flags|XAT_CAN_VIEW; }
			if ($XAT_CAN_EDIT==1) { $flags = $flags|XAT_CAN_EDIT; }
		}

		if ($XWEBCTL) { $flags = $flags|XWEBCTL; }
		if ($XWEBACL) { $flags = $flags|XWEBACL; }
		if ($XDOMAIN_LOCK) { $flags = $flags|XDOMAIN_LOCK; }
		if ($XWEBUSR_TOASTER == 1) { $flags = $flags|XWEBUSR_TOASTER; }
		if ($XWEBUSR_TOASTER == 2) { $flags = $flags|XWEBUSR_TOASTER_RDONLY; }

		if ($XSUSPEND_USR) { $flags = $flags|XSUSPEND_USR; }
		if ($XUNSUSPEND_USR) { $flags = $flags|XUNSUSPEND_USR; }
		if ($XWEBSESS) { $flags = $flags|XWEBSESS; }
		if ($XLOGGING_VIEW) { $flags = $flags|XLOGGING_VIEW; }
		if ($XCOMPLAINTS_ADM>0) {
			if ($XCOMPLAINTS_ADM==2) { $flags = $flags|XCOMPLAINTS_ADM_REPLY; } else {
				if ($XCOMPLAINTS_ADM==1) { $flags = $flags|XCOMPLAINTS_ADM_READ; }
			}
		}
		if ($XIPR_AXS>0) {
			if ($XIPR_VIEW1 == 1) { $flags = $flags|XIPR_VIEW_OWN; }
			if ($XIPR_VIEW2 == 1) { $flags = $flags|XIPR_VIEW_OTHERS; }
			if ($XIPR_MOD1 == 1) { $flags = $flags|XIPR_MOD_OWN; }
			if ($XIPR_MOD2 == 1) { $flags = $flags|XIPR_MOD_OTHERS; }
		}

		if ($MIA_W ==1) { $flags = $flags|MIA_VIEW;}
		if ($XTOTP ==1) { $flags = $flags|XTOTP_DISABLE_OTHERS;}
		if ($flags == 0) { // no Access added ?! duh...
			echo "<html><head><title>Access Control List Manager</title>";
			std_theme_styles();
			echo "</head>\n";
			std_theme_body("../");

			echo "<h2>ACL Manager</h2><hr size=1 noshade>\n";
			echo "<b>";
			echo "<font color=#ff0000>";
			echo "You'd better add at least one access somewhere, don't you think so ?</font></b><br><br>\n";
			echo "<a href=\"javascript:history.go(-1);\">go back</a><br>\n";
			echo "</body></html>\n\n";
			die;

		}

		if ($uchoice == 2) {
			$qqs = pg_safe_exec("SELECT id FROM users WHERE lower(user_name)='" . strtolower($spcuname) . "'");
			if ($qqo = pg_fetch_object($qqs)) {
				$userid = $qqo->id;
			} else {
				echo "<html><head><title>Access Control List Manager</title>";
				std_theme_styles();
				echo "</head>\n";
				std_theme_body("../");
				echo "<h2>ACL Manager</h2><hr size=1 noshade>\n";
				echo "<b>";
				echo "<font color=#ff0000>";
				echo "The username is non-existant / invalid !</font></b><br><br>\n";
				echo "<a href=\"javascript:history.go(-1);\">go back</a><br>\n";
				echo "</body></html>\n\n";
				die;
			}
		}

		if ($userid==0) {
				echo "<html><head><title>Access Control List Manager</title>";
				std_theme_styles();
				echo "</head>\n";
				std_theme_body("../");
				echo "<h2>ACL Manager</h2><hr size=1 noshade>\n";
				echo "<b>";
				echo "<font color=#ff0000>";
				echo "Error: No user specified !</font></b><br><br>\n";
				echo "<a href=\"javascript:history.go(-1);\">go back</a><br>\n";
				echo "</body></html>\n\n";
				die;
		}

      		$query = "INSERT INTO acl (user_id,isstaff,flags,xtra,last_updated,last_updated_by,suspend_expire,suspend_by,deleted) VALUES ";
       		$query .= "('" . $userid . "',";
       		$query .= "'" . $isstaff . "',";
       		$query .= "'" . $flags . "',";
       		$query .= "'" . $xtra . "',";
       		$query .= "date_part('epoch', CURRENT_TIMESTAMP)::int,";
       		$query .= "'" . $user_id . "',";
       		$query .= "'0','0','0')";

		pg_safe_exec($query);

       		header("Location: index.php\n\n");
       		die;
       }

if (!($mode=="editacl" && $crc == md5( $HTTP_USER_AGENT . $CRC_SALT_0010 . $user_id . $ts )) &&
   !($mode=="getlist" && $crc == md5( $HTTP_USER_AGENT . $CRC_SALT_0009 . $user_id . $ts )) &&
   !($mode=="applyacl" && $crc == md5( $HTTP_USER_AGENT . $CRC_SALT_0008 . $user_id . $ts ))) {

	echo "<html><head><title>Access Control List Manager</title>";
			std_theme_styles();
			echo "</head>\n";
			std_theme_body("../");
	echo "<h2>ACL Manager</h2><hr size=1 noshade>\n";
	echo "<b>";
	echo "<font color=#ff0000>Page Error</font></b><br>\n";
	echo "</body></html>\n\n";
	die;

}

$zets = time();


if ($mode=="applyacl" && $crc == md5( $HTTP_USER_AGENT . $CRC_SALT_0008 . $user_id . $ts )) {

       		$test = pg_safe_exec("SELECT * FROM levels WHERE levels.channel_id=1 AND levels.user_id='" . $userid . "' AND access>0");
       		if (pg_numrows($test)>0) { $isstaff = 1; } else { $isstaff = 0; }

       		$flags = 0;

       		if ($XCHGMGR==2) { $flags = $flags|XCHGMGR_ADMIN; } else {
       			if ($XCHGMGR==1) { $flags = $flags|XCHGMGR_REVIEW; }
       		}

       		if ($XMAILCH==2) { $flags = $flags|XMAILCH_ADMIN; } else {
       			if ($XMAILCH==1) { $flags = $flags|XMAILCH_REVIEW; }
       		}

		if ($XHELP==1) {
			$flags = $flags|XHELP;
			if ($CAN_ADD) { $flags = $flags|XHELP_CAN_ADD; }
			if ($CAN_EDIT) { $flags = $flags|XHELP_CAN_EDIT; }
		}
		if ($XHELP==0) { $xtra = 0; }

		if ($XWEBAXS==3) { $flags = $flags|XWEBAXS_3; } else {
			if ($XWEBAXS==2) { $flags = $flags|XWEBAXS_2; }
		}
		if ($XMAILCH==1 || $XMAILCH==2 || $XCHGMGR==1 || $XCHGMGR==2) {
			if ($XAT_CAN_VIEW==1) { $flags = $flags|XAT_CAN_VIEW; }
			if ($XAT_CAN_EDIT==1) { $flags = $flags|XAT_CAN_EDIT; }
		}

		if ($XWEBCTL) { $flags = $flags|XWEBCTL; }
		if ($XWEBACL) { $flags = $flags|XWEBACL; }
		if ($XDOMAIN_LOCK) { $flags = $flags|XDOMAIN_LOCK; }
		if ($XWEBUSR_TOASTER == 1) { $flags = $flags|XWEBUSR_TOASTER; }
		if ($XWEBUSR_TOASTER == 2) { $flags = $flags|XWEBUSR_TOASTER_RDONLY; }

		if ($XSUSPEND_USR) { $flags = $flags|XSUSPEND_USR; }
		if ($XUNSUSPEND_USR) { $flags = $flags|XUNSUSPEND_USR; }
		if ($XWEBSESS) { $flags = $flags|XWEBSESS; }
		if ($XLOGGING_VIEW) { $flags = $flags|XLOGGING_VIEW; }
		if ($XCOMPLAINTS_ADM>0) {
			if ($XCOMPLAINTS_ADM==2) { $flags = $flags|XCOMPLAINTS_ADM_REPLY; } else {
				if ($XCOMPLAINTS_ADM==1) { $flags = $flags|XCOMPLAINTS_ADM_READ; }
			}
		}
		if ($XIPR_AXS>0) {
			if ($XIPR_VIEW1 == 1) { $flags = $flags|XIPR_VIEW_OWN; }
			if ($XIPR_VIEW2 == 1) { $flags = $flags|XIPR_VIEW_OTHERS; }
			if ($XIPR_MOD1 == 1) { $flags = $flags|XIPR_MOD_OWN; }
			if ($XIPR_MOD2 == 1) { $flags = $flags|XIPR_MOD_OTHERS; }
		}
		if ($MIA_W ==1) { $flags = $flags|MIA_VIEW;}
		if ($XTOTP ==1) { $flags = $flags|XTOTP_DISABLE_OTHERS;}
		if ($flags == 0) { // no Access added/anymore ?! duh...
			if ($admin>=800) {
				echo "<html><head><title>Access Control List Manager</title>";
				std_theme_styles();
				echo "</head>\n";
				std_theme_body("../");

				echo "<h2>ACL Manager</h2><hr size=1 noshade>\n";
				echo "<b>";
				echo "<font color=#ff0000>";
				echo "You'd better put at least one access somewhere, don't you think so ?<br>Or you'd rather <b>remove</b> the ACL :)</font></b><br><br>\n";
				echo "<a href=\"javascript:history.go(-1);\">go back</a><br>\n";
				echo "</body></html>\n\n";
				die;
			} else {
				// triggered by someone who hasnt full access over ACL controls,
				// so if we end up with a zeroed flag, the user's ACL is gonna be deleted.

				$query = "DELETE FROM acl WHERE user_id='" . $userid . "'";
				pg_safe_exec($query);
				header("Location: index.php\n\n");
				die;

			}
		}

      		$query = "UPDATE acl SET ";
       		$query .= "isstaff='" . $isstaff . "',";
       		$query .= "flags='" . $flags . "',";
       		$query .= "xtra='" . $xtra . "',";
       		$query .= "last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,";
       		$query .= "last_updated_by='" . $user_id . "' ";
       		$query .= " WHERE user_id='" . $userid . "'";

		pg_safe_exec($query);
		//echo $query;

       		header("Location: index.php\n\n");
       		die;




}

echo "<html><head><title>Access Control List Manager</title>";
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
std_theme_styles();
echo "</head>\n";
std_theme_body("../");


if ($mode=="editacl") {
	echo "<h2>Modifying ACL</h2><a href=\"index.php\">Back to ACL Manager</a><hr size=1 noshade>\n";
}
if ($mode=="getlist") {
	echo "<h2>ACL Full List</h2><a href=\"index.php\">Back to ACL Manager</a><hr size=1 noshade>\n";
}

echo "<br>\n";

if ($mode=="editacl") {

	$res = pg_safe_exec("SELECT acl.flags as acl_flags,acl.acl_id,acl.xtra,users.user_name,acl.last_updated,acl.last_updated_by FROM acl,users WHERE acl.user_id='" . $userid . "' AND acl.user_id=users.id");
	if (pg_numrows($res)==0) {
		echo "<h3>no ACL defined for that user !</h3>\n";
	} else {
		$row = pg_fetch_object($res,0);


$ztest1 = ((acl(XCHGMGR_ADMIN) && !((int)$row->acl_flags & XCHGMGR_ADMIN)) || $admin>=800);
$ztest2 = ((acl(XMAILCH_ADMIN) && !((int)$row->acl_flags & XMAILCH_ADMIN)) || $admin>=800);
$ztest3 = ((acl(XWEBAXS_3) && !((int)$row->acl_flags & XWEBAXS_3)) || $admin>=800);
$noallow = 0;
if (!$ztest1 && !$ztest2 && !$ztest3 && $admin<800) { $noallow = 1; }

		echo "<table border=0 cellspacing=30 cellpadding=5>\n";
		echo "<tr>\n";
		echo "<td bgcolor=#" . $cTheme->main_acl_edit . " valign=top><font face=arial,helvetica size=-1>\n";
		echo "<form name=applyacl action=acl.php method=post onsubmit=\"return check(this);\">\n";
		echo "<input type=hidden name=mode value=applyacl>\n";
		echo "<input type=hidden name=ts value=" . $zets . ">\n";
		echo "<input type=hidden name=userid value=" . $userid . ">\n";
		echo "<input type=hidden name=crc value=" . md5($HTTP_USER_AGENT . $CRC_SALT_0008 . $user_id . $zets ) . ">\n";

		if ($noallow) {
			echo "<b>Nothing you can EDIT in </b>" . $row->user_name . "<b>'s ACLs.</b><br>\n";
		} else {
			echo "Following <b>ACL</b> is applied for user <b>" . $row->user_name . "</b>:<br>";
			echo "&nbsp;&nbsp;&nbsp;<b>Last modified on :</b>&nbsp;" . cs_time($row->last_updated) . "<br>\n";

			$unf = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $row->last_updated_by . "'");
			$damodifier = "** SYSTEM **";
			if (pg_numrows($unf)>0) {
				$ooo = pg_fetch_object($unf,0);
				$damodifier = $ooo->user_name;
			}

			echo "&nbsp;&nbsp;&nbsp;<b>Last modified by :</b>&nbsp;" . $damodifier . " (" . $row->last_updated_by . ")<br>";
			if ($admin>=800) {
				echo "<input type=button onclick=\"location.href='acl.php?mode=remove&acl_id=" . $row->acl_id . "&crc=" . md5($row->acl_id . CRC_SALT_0011 . $user_id) . "&bu=2';\" value=\"Remove ACL\"><br>\n";
			}
			echo "<br>\n";
		}


		//echo "<ul>\n";

if ($ztest1) {
		echo "<li> <b>" . BOT_NAME . "@ Manager Changes</b>:&nbsp;(<a href=\"javascript:get_help('XCHGMGR');\">help</a>)<br>";
		$c1="";$c2="";$c3="";
		if ((int)$row->acl_flags & XCHGMGR_ADMIN) { $c3 = " checked"; } else {
			if  ((int)$row->acl_flags & XCHGMGR_REVIEW) { $c2 = " checked"; } else {
				$c1 = " checked";
			}
		}

		echo $spc . "<input type=radio name=XCHGMGR value=0" . $c1 . "> NO ACCESS<br>";
		echo $spc . "<input type=radio name=XCHGMGR value=1" . $c2 . "> Reviewer<br>";
		if ($admin>=800) {
		echo $spc . "<input type=radio name=XCHGMGR value=2" . $c3 . "> Admin<br>";
		}
		echo "<br>\n";
} else {
		echo "<input type=hidden name=XCHGMGR value=";
		if ((int)$row->acl_flags & XCHGMGR_ADMIN) { echo "2"; } else {
			if ((int)$row->acl_flags & XCHGMGR_REVIEW) { echo "1"; } else {
				echo "0";
			}
		}
		echo ">\n";
}
if ($ztest2) {
		echo "<li> <b>" . BOT_NAME . "@ E-Mail-in-Record Changes</b>:&nbsp;(<a href=\"javascript:get_help('XMAILCH');\">help</a>)<br>";
		$c1="";$c2="";$c3="";
		if ((int)$row->acl_flags & XMAILCH_ADMIN) { $c3 = " checked"; } else {
			if  ((int)$row->acl_flags & XMAILCH_REVIEW) { $c2 = " checked"; } else {
				$c1 = " checked";
			}
		}

		echo $spc . "<input type=radio name=XMAILCH value=0" . $c1 . "> NO ACCESS<br>";
		echo $spc . "<input type=radio name=XMAILCH value=1" . $c2 . "> Reviewer<br>";
		if ($admin>=800) {
		echo $spc . "<input type=radio name=XMAILCH value=2" . $c3 . "> Admin<br>";
		}
		echo "<br>\n";
} else {
		echo "<input type=hidden name=XMAILCH value=";
		if ((int)$row->acl_flags & XMAILCH_ADMIN) { echo "2"; } else {
			if ((int)$row->acl_flags & XMAILCH_REVIEW) { echo "1"; } else {
				echo "0";
			}
		}
		echo ">\n";
}
if ($ztest1 || $ztest2) {
	echo "<li> <b>" . BOT_NAME . "@ Team (Email+Mgr changes) Globals</b>:&nbsp;(<a href=\"javascript:get_help('XAT_GLOBALS');\">help</a>)<br>";
	$c1 = ""; $c2 = "";
	if ((int)$row->acl_flags & XAT_CAN_VIEW) { $c1 = " checked"; }
	if ((int)$row->acl_flags & XAT_CAN_EDIT) { $c2 = " checked"; }
	echo $spc . "<input type=checkbox name=XAT_CAN_VIEW value=1" . $c1 . "> Can view verification data<br>";
	echo $spc . "<input type=checkbox name=XAT_CAN_EDIT value=1" . $c2 . "> Can edit users<br>";
	echo "<br>\n";
}

if ($admin>=800) {
		echo "<li> <b>" . BOT_NAME . "@ Help Management</b>:&nbsp;(<a href=\"javascript:get_help('XHELP');\">help</a>)<br>";
		$c1="";$c2="";$c3="";$c4="";
		if ((int)$row->acl_flags & XHELP) {
			$c2 = " checked";
			if ((int)$row->acl_flags & XHELP_CAN_ADD) { $c3 = " checked"; }
			if ((int)$row->acl_flags & XHELP_CAN_EDIT) { $c4 = " checked"; }
		} else {
			$c1 = " checked";
		}

		echo $spc . "<input type=radio name=XHELP value=0" . $c1 . "> NO ACCESS<br>";
		echo $spc . "<input type=radio name=XHELP value=1" . $c2 . "> Help Manager<br>";
		echo $spc . "<select name=xtra><option value=0>-- All Languages --</option>\n";
		$bla = pg_safe_exec("SELECT * FROM languages ORDER BY name");
		for ($x=0;$x<pg_numrows($bla);$x++) {
			$blo = pg_fetch_object($bla,$x);
			echo "<option";
			if ($blo->id == $row->xtra) { echo " selected"; }
			echo " value=" . $blo->id . ">" . $blo->name . "</option>\n";
		}
		echo "</select><br>";
		echo $spc . "<input type=checkbox name=CAN_ADD value=1" . $c3 . "> Can ADD commands to the HELP set<br>";
		echo $spc . "<input type=checkbox name=CAN_EDIT value=1" . $c4 . "> Can EDIT HELP text replies (recommended)<br>";
		echo "<br>\n";

		echo "<li> <b>" . BOT_NAME . "@ TOTP disable for others</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XHELP');\">help</a>)<br>";
		$xtop1= "checked";
		if ((int)$row->acl_flags & XTOTP_DISABLE_OTHERS) {
		$xtop1=''; $xtop2= " checked"; }
echo $spc . "<input type=radio name=XTOTP value=0 ". $xtop1."> Disabled<br>";
echo $spc . "<input type=radio name=XTOTP value=1 ". $xtop2."> Enabled<br>";


		echo "<li> <b>MIA system</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('MIA');\">help</a>)<br>";
		$mi1= "checked";
		if ((int)$row->acl_flags & MIA_VIEW) {
		$mi1=''; $mi2= " checked"; }
echo $spc . "<input type=radio name=MIA_W value=0 ". $mi1."> Disabled<br>";
echo $spc . "<input type=radio name=MIA_W value=1 ". $mi2."> Enabled<br>";
} else {
		if ((int)$row->acl_flags & XHELP) {
			echo "<input type=hidden name=XHELP value=1>\n";
		} else {
			echo "<input type=hidden name=XHELP value=0>\n";
		}
		echo "<input type=hidden name=xtra value=" . $row->xtra . ">";
		if ((int)$row->acl_flags & XHELP_CAN_ADD) { echo "<input type=hidden name=CAN_ADD value=1>\n"; } else {
			echo "<input type=hidden name=CAN_ADD value=0>\n";
		}
		if ((int)$row->acl_flags & XHELP_CAN_EDIT) { echo "<input type=hidden name=CAN_EDIT value=1>\n"; } else {
			echo "<input type=hidden name=CAN_EDIT value=0>\n";
		}

}
if ($ztest3) {
		echo "<li> <b>WebAXS Team (Channel Review Team)</b>:&nbsp;(<a href=\"javascript:get_help('XWEBAXS');\">help</a>)<br>";
		$c1="";$c2="";$c3="";
		if ((int)$row->acl_flags & XWEBAXS_3) { $c3 = " checked"; } else {
			if  ((int)$row->acl_flags & XWEBAXS_2) { $c2 = " checked"; } else {
				$c1 = " checked";
			}
		}
		echo $spc . "<input type=radio name=XWEBAXS value=0" . $c1 . "> NO ACCESS<br>";
		echo $spc . "<input type=radio name=XWEBAXS value=2" . $c2 . "> Reviewer (level 2)<br>";
		if ($admin>=800) { echo $spc . "<input type=radio name=XWEBAXS value=3" . $c3 . "> Admin (level 3)<br>"; }
		echo "<br>\n";
} else {
		if ((int)$row->acl_flags & XWEBAXS_3) {
			echo "<input type=hidden name=XWEBAXS value=3>\n";
		} else {
			if ($row->acl_flags & XWEBAXS_2) {
				echo "<input type=hidden name=XWEBAXS value=2>\n";
			} else {
				echo "<input type=hidden name=XWEBAXS value=0>\n";
			}
		}
}
if ($admin>=800) {
		$c1="";$c2="";
		if (((int)$row->acl_flags & XWEBCTL) == XWEBCTL) { $c2 = " checked"; } else { $c1 = " checked"; }
		echo "<li> <b>Site Control Access</b>:&nbsp;(<a href=\"javascript:get_help('XWEBCTL');\">help</a>)<br>";
		echo $spc . "<input type=radio name=XWEBCTL value=0" . $c1 . "> Disabled<br>";
		echo $spc . "<input type=radio name=XWEBCTL value=1" . $c2 . "> Enabled<br>";
		echo "<br>\n";

		$c1="";$c2="";
		if (((int)$row->acl_flags & XWEBACL) == XWEBACL) { $c2 = " checked"; } else { $c1 = " checked"; }
		echo "<li> <b>ACL Page access</b>:&nbsp;(<a href=\"javascript:get_help('XWEBACL');\">help</a>)<br>";
		echo $spc . "<input type=radio name=XWEBACL value=0" . $c1 . "> Disabled<br>";
		echo $spc . "<input type=radio name=XWEBACL value=1" . $c2 . "> Enabled<br>";
		echo "<br>\n";

		$c1="";$c2="";
		if (((int)$row->acl_flags & XDOMAIN_LOCK) == XDOMAIN_LOCK) { $c2 = " checked"; } else { $c1 = " checked"; }
		echo "<li> <b>DomainLock ADD/REMOVE Access</b>:&nbsp;(<a href=\"javascript:get_help('XDOMAIN_LOCK');\">help</a>)<br>";
		echo $spc . "<input type=radio name=XDOMAIN_LOCK value=0" . $c1 . "> Disabled<br>";
		echo $spc . "<input type=radio name=XDOMAIN_LOCK value=1" . $c2 . "> Enabled<br>";
		echo "<br>\n";


		$c1="";$c2="";$c3="";
		if (((int)$row->acl_flags & XWEBUSR_TOASTER) == XWEBUSR_TOASTER) { $c2 = " checked"; } else { $c1 = " checked"; }
		if (((int)$row->acl_flags & XWEBUSR_TOASTER_RDONLY) == XWEBUSR_TOASTER_RDONLY) { $c3 = " checked"; }
		echo "<li> <b>User Toaster access</b>:&nbsp;(<a href=\"javascript:get_help('XWEBUSR_TOASTER');\">help</a>)<br>";
		echo $spc . "<input type=radio name=XWEBUSR_TOASTER value=0" . $c1 . "> Disabled<br>";
		echo $spc . "<input type=radio name=XWEBUSR_TOASTER value=1" . $c2 . "> Enabled (view/post)<br>";
		echo $spc . "<input type=radio name=XWEBUSR_TOASTER value=2" . $c3 . "> Enabled (view only)<br>";
		echo "<br>\n";

		$c1="";$c2="";
		if (((int)$row->acl_flags & XSUSPEND_USR) == XSUSPEND_USR) { $c2 = " checked"; } else { $c1 = " checked"; }
		echo "<li> <b>User suspension access</b>:&nbsp;(<a href=\"javascript:get_help('XSUSPEND_USR');\">help</a>)<br>";
		echo $spc . "<input type=radio name=XSUSPEND_USR value=0" . $c1 . "> Disabled<br>";
		echo $spc . "<input type=radio name=XSUSPEND_USR value=1" . $c2 . "> Enabled<br>";
		echo "<br>\n";

		$c1="";$c2="";
		if (((int)$row->acl_flags & XUNSUSPEND_USR) == XUNSUSPEND_USR) { $c2 = " checked"; } else { $c1 = " checked"; }
		echo "<li> <b>User unsuspension access</b>:&nbsp;(<a href=\"javascript:get_help('XUNSUSPEND_USR');\">help</a>)<br>";
		echo $spc . "<input type=radio name=XUNSUSPEND_USR value=0" . $c1 . "> Disabled<br>";
		echo $spc . "<input type=radio name=XUNSUSPEND_USR value=1" . $c2 . "> Enabled<br>";
		echo "<br>\n";

		$c1="";$c2="";
		if (((int)$row->acl_flags & XWEBSESS) == XWEBSESS) { $c2 = " checked"; } else { $c1 = " checked"; }
		echo "<li> <b>Current web sessions view</b>:&nbsp;(<a href=\"javascript:get_help('XWEBSESS');\">help</a>)<br>";
		echo $spc . "<input type=radio name=XWEBSESS value=0" . $c1 . "> Disabled<br>";
		echo $spc . "<input type=radio name=XWEBSESS value=1" . $c2 . "> Enabled<br>";
		echo "<br>\n";

		$c1="";$c2="";
		if (((int)$row->acl_flags & XLOGGING_VIEW) == XLOGGING_VIEW) { $c2 = " checked"; } else { $c1 = " checked"; }
		echo "<li> <b>Admin logging view</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XLOGGING_VIEW');\">help</a>)<br>";
		echo $spc . "<input type=radio name=XLOGGING_VIEW value=0" . $c1 . "> Disabled<br>";
		echo $spc . "<input type=radio name=XLOGGING_VIEW value=1" . $c2 . "> Enabled<br>";
		echo "<br>\n";

		$c1="";$c2="";$c3="";
		if (((int)$row->acl_flags & XCOMPLAINTS_ADM_REPLY) == XCOMPLAINTS_ADM_REPLY) {
			$c1 = ""; $c2 = "";
			$c3 = " checked";
		} else {
			if (((int)$row->acl_flags & XCOMPLAINTS_ADM_READ) == XCOMPLAINTS_ADM_READ) {
				$c1 = ""; $c3 = "";
				$c2 = " checked";
			} else {
				$c2 = ""; $c3 = "";
				$c1 = " checked";
			}
		}
		echo "<li> <b>Complaints Admin Access</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XCOMPLAINTS_ADM');\">help</a>)<br>";
		echo $spc . "<input type=radio name=XCOMPLAINTS_ADM value=0" . $c1 . "> NO ACCESS<br>";
		echo $spc . "<input type=radio name=XCOMPLAINTS_ADM value=1" . $c2 . "> Read only<br>";
		echo $spc . "<input type=radio name=XCOMPLAINTS_ADM value=2" . $c3 . "> Read & Reply<br>";
		echo "<br>\n";

		$c1="";$c2="";$c3="";$c4="";$c5="";$c6="";
		if (((int)$row->acl_flags & XIPR_VIEW_OWN) == XIPR_VIEW_OWN) { $c1 = ""; $c2 = " checked"; $c3 = " checked"; }
		if (((int)$row->acl_flags & XIPR_VIEW_OTHERS) == XIPR_VIEW_OTHERS) { $c1 = ""; $c2 = " checked"; $c4 = " checked"; }
		if (((int)$row->acl_flags & XIPR_MOD_OWN) == XIPR_MOD_OWN) { $c1 = ""; $c2 = " checked"; $c5 = " checked"; }
		if (((int)$row->acl_flags & XIPR_MOD_OTHERS) == XIPR_MOD_OTHERS) { $c1 = ""; $c2 = " checked"; $c6 = " checked"; }
		if ($c2 == "") { $c1 = " checked"; }
		echo "<li> <b>IP/Host list Access</b>:&nbsp;&nbsp;&nbsp;(<a href=\"javascript:get_help('XIPR_AXS');\">help</a>)<br>";
		echo $spc . "<input type=radio name=XIPR_AXS value=0" . $c1 . "> NO ACCESS<br>";
		echo $spc . "<input type=radio name=XIPR_AXS value=1" . $c2 . "> Access as follows:<br>";
		echo $spc. $spc . "<input type=checkbox name=XIPR_VIEW1 value=1" . $c3 . "> View own<br>";
		echo $spc. $spc . "<input type=checkbox name=XIPR_VIEW2 value=1" . $c4 . "> View others<br>";
		echo $spc. $spc . "<input type=checkbox name=XIPR_MOD1 value=1" . $c5 . "> Change own<br>";
		echo $spc. $spc . "<input type=checkbox name=XIPR_MOD2 value=1" . $c6 . "> Change others<br>";
		echo "<br>\n";


} else {

		if ((int)$row->acl_flags & XWEBCTL) {
			echo "<input type=hidden name=XWEBCTL value=1>\n";
		} else {
			echo "<input type=hidden name=XWEBCTL value=0>\n";
		}
		if ((int)$row->acl_flags & XWEBACL) {
			echo "<input type=hidden name=XWEBACL value=1>\n";
		} else {
			echo "<input type=hidden name=XWEBACL value=0>\n";
		}
		if ((int)$row->acl_flags & XDOMAIN_LOCK) {
			echo "<input type=hidden name=XDOMAIN_LOCK value=1>\n";
		} else {
			echo "<input type=hidden name=XDOMAIN_LOCK value=0>\n";
		}
		if ((int)$row->acl_flags & XWEBUSR_TOASTER) {
			echo "<input type=hidden name=XWEBUSR_TOASTER value=1>\n";
		} else {
			echo "<input type=hidden name=XWEBUSR_TOASTER value=0>\n";
		}
		if ((int)$row->acl_flags & XSUSPEND_USR) {
			echo "<input type=hidden name=XSUSPEND_USR value=1>\n";
		} else {
			echo "<input type=hidden name=XSUSPEND_USR value=0>\n";
		}
		if ((int)$row->acl_flags & XUNSUSPEND_USR) {
			echo "<input type=hidden name=XUNSUSPEND_USR value=1>\n";
		} else {
			echo "<input type=hidden name=XUNSUSPEND_USR value=0>\n";
		}
		if ((int)$row->acl_flags & XWEBSESS) {
			echo "<input type=hidden name=XWEBSESS value=1>\n";
		} else {
			echo "<input type=hidden name=XWEBSESS value=0>\n";
		}

		if ((int)$row->acl_flags & XLOGGING_VIEW) {
			echo "<input type=hidden name=XLOGGING_VIEW value=1>\n";
		} else {
			echo "<input type=hidden name=XLOGGING_VIEW value=0>\n";
		}
		if ((int)$row->acl_flags & XCOMPLAINTS_ADM_REPLY) {
			echo "<input type=hidden name=XCOMPLAINTS_ADM value=2>\n";
		} else {
			if ((int)$row->acl_flags & XCOMPLAINTS_ADM_READ) {
				echo "<input type=hidden name=XCOMPLAINTS_ADM value=1>\n";
			} else {
				echo "<input type=hidden name=XCOMPLAINTS_ADM value=0>\n";
			}
		}
		$xxs = 0;
		if (((int)$row->acl_flags & XIPR_VIEW_OWN) == XIPR_VIEW_OWN) {
			echo "<input type=hidden name=XIPR_VIEW1 value=1>";
			$xxs = 1;
		} else {
			echo "<input type=hidden name=XIPR_VIEW1 value=0>";
		}
		if (((int)$row->acl_flags & XIPR_VIEW_OTHERS) == XIPR_VIEW_OTHERS) {
			echo "<input type=hidden name=XIPR_VIEW2 value=1>";
			$xxs = 1;
		} else {
			echo "<input type=hidden name=XIPR_VIEW2 value=0>";
		}
		if (((int)$row->acl_flags & XIPR_MOD_OWN) == XIPR_MOD_OWN) {
			echo "<input type=hidden name=XIPR_MOD1 value=1>";
			$xxs = 1;
		} else {
			echo "<input type=hidden name=XIPR_MOD1 value=0>";
		}
		if (((int)$row->acl_flags & XIPR_MOD_OTHERS) == XIPR_MOD_OTHERS) {
			echo "<input type=hidden name=XIPR_MOD2 value=1>";
			$xxs = 1;
		} else {
			echo "<input type=hidden name=XIPR_MOD2 value=0>";
		}
		echo "<input type=hidden name=XIPR_AXS value=" . (int)$xxs . ">";
}

		if (!$noallow) {
			echo "<br><br>";

			echo $spc . $spc . $spc . "<input type=submit value=\" APPLY ACL CHANGES \">\n";
		}

		echo "</form>";


		echo "</font></td>\n";
		echo "</tr>\n";
		echo "</table>\n";
	}
}

if ($mode=="getlist") {

	$res = pg_safe_exec("SELECT acl.flags as acl_flags,acl.acl_id,acl.user_id,users.user_name,acl.xtra,acl.last_updated,acl.last_updated_by FROM acl,users WHERE acl.user_id=users.id ORDER BY users.user_name");
	if (pg_numrows($res)==0) {
		echo "<h3>no ACL defined</h3>\n";
	} else {

		echo "<form>\n";
		echo "<table border=1 cellspacing=0 cellpadding=2>\n";
		echo "<tr bgcolor=#" . $cTheme->table_tr_enlighten . ">\n";
		echo "<td><b>user_name</b></td>";
		echo "<td><b>* access</b></td>";
		echo "<td><b>XCHGMGR</b><br>(<a href=\"javascript:get_help('XCHGMGR');\">help</a>)</td>";
		echo "<td><b>XMAILCH</b><br>(<a href=\"javascript:get_help('XMAILCH');\">help</a>)</td>";
		echo "<td><b>XAT_GLOBALS</b><br>(<a href=\"javascript:get_help('XAT_GLOBALS');\">help</a>)</td>";
		echo "<td><b>XHELP</b><br>(<a href=\"javascript:get_help('XHELP');\">help</a>)</td>";
		echo "<td><b>MIA</b><br>(<a href=\"javascript:get_help('MIA');\">help</a>)</td>";
		echo "<td><b>XWEBAXS</b><br>(<a href=\"javascript:get_help('XWEBAXS');\">help</a>)</td>";
		echo "<td><b>XWEBCTL</b><br>(<a href=\"javascript:get_help('XWEBCTL');\">help</a>)</td>";
		echo "<td><b>XWEBACL</b><br>(<a href=\"javascript:get_help('XWEBACL');\">help</a>)</td>";
		echo "<td><b>XDOMAIN_LOCK</b><br>(<a href=\"javascript:get_help('XDOMAIN_LOCK');\">help</a>)</td>";
		echo "<td><b>XWEBUSR_TOASTER</b><br>(<a href=\"javascript:get_help('XWEBUSR_TOASTER');\">help</a>)</td>";
		echo "<td><b>XSUSPEND_USR</b><br>(<a href=\"javascript:get_help('XSUSPEND_USR');\">help</a>)</td>";
		echo "<td><b>XUNSUSPEND_USR</b><br>(<a href=\"javascript:get_help('XUNSUSPEND_USR');\">help</a>)</td>";
		echo "<td><b>XWEBSESS</b><br>(<a href=\"javascript:get_help('XWEBSESS');\">help</a>)</td>";
		echo "<td><b>XLOGGING_VIEW</b><br>(<a href=\"javascript:get_help('XLOGGING_VIEW');\">help</a>)</td>";
		echo "<td><b>XCOMPLAINTS_ADM</b><br>(<a href=\"javascript:get_help('XCOMPLAINTS_ADM');\">help</a>)</td>";
		echo "<td><b>XIPR_AXS</b><br>(<a href=\"javascript:get_help('XIPR_AXS');\">help</a>)</td>";
		if ($admin>=800) {
			echo "<td><b>action</b></td>";
		}
		echo "</tr>";

		for($x=0;$x<pg_numrows($res);$x++) {
			$row = pg_fetch_object($res,$x);
			echo "<tr>";
			echo "<td><a href=\"../users.php?id=" . $row->user_id . "\">" . $row->user_name . "</a></td>";
			$sres = pg_safe_exec("SELECT * FROM levels WHERE user_id='" . $row->user_id . "' AND channel_id=1 AND access>0");
			if (pg_numrows($sres)>0) {
				$srow = pg_fetch_object($sres,0);
				$axs = $srow->access;
			} else { $axs = "<i>none</i>"; }
			echo "<td>" . $axs . "</td>";

			echo "<td>";
			if ((int)$row->acl_flags & XCHGMGR_ADMIN) {
				echo "<font color=#" . $cTheme->main_no . "><b>Admin</b></font>";
			} else {
				if ((int)$row->acl_flags & XCHGMGR_REVIEW) {
					echo "<font color=#" . $cTheme->main_yes . "><b>Reviewer</b></font>";
				} else {
					echo "<i>NO ACCESS</i>";
				}
			}
			echo "</td>";

			echo "<td>";
			if ((int)$row->acl_flags & XMAILCH_ADMIN) {
				echo "<font color=#" . $cTheme->main_no . "<b>Admin</b></font>";
			} else {
				if ((int)$row->acl_flags & XMAILCH_REVIEW) {
					echo "<font color=#" . $cTheme->main_yes . "><b>Reviewer</b></font>";
				} else {
					echo "<i>NO ACCESS</i>";
				}
			}
			echo "</td>";

			echo "<td>";
			echo "Can&nbsp;view:&nbsp;";
			if ($axs>=600 || (int)$row->acl_flags & XAT_CAN_VIEW || $row->acl_flags & XAT_CAN_EDIT) {
				echo "<font color=#" . $cTheme->main_yes . "><b>Yes</b></font>";
				if ($axs>=600) { echo "&nbsp;(600+)"; }
			} else {
				echo "<font color=#" . $cTheme->main_no . "><b>No</b></font>";
			}
			echo "<br>\n";
			echo "Can&nbsp;edit:&nbsp;";
			if ($axs>=600 || (int)$row->acl_flags & XAT_CAN_EDIT) {
				echo "<font color=#" . $cTheme->main_yes . "><b>Yes</b></font>";
				if ($axs>=600) { echo "&nbsp;(600+)"; }
			} else {
				echo "<font color=#" . $cTheme->main_no . "><b>No</b></font>";
			}
			echo "</td>";

			echo "<td>";
			if ((int)$row->acl_flags & XHELP) {
				if ($row->xtra==0) { echo "<font color=#" . $cTheme->main_no . "><b>ALL LANGUAGES</b></font><br>"; } else {
					$bli = pg_safe_exec("SELECT name FROM languages WHERE id='" . $row->xtra . "'");
					$blo = pg_fetch_object($bli,0);
					echo "<font color=#" . $cTheme->main_yes . "><b>" . $blo->name . "</b></font><br>";
				}
				echo "ADD:<b>";
				if ($row->acl_flags & XHELP_CAN_ADD) { echo "YES"; } else { echo "NO"; }
				echo "</b> - EDIT:<b>";
				if ($row->acl_flags & XHELP_CAN_EDIT) { echo "YES"; } else { echo "NO"; }
				echo "</b>";
			} else {
				echo "<i>NO ACCESS</i>";
			}
			echo "</td>";

			if ((int)$row->acl_flags & MIA_VIEW) {
			echo "<td><font color=#" . $cTheme->main_yes . "><b>ENABLED</b></Font></td>";
			} else {
				echo "<td><i>NO ACCESS</i></td>";
			}

			echo "<td>";
			if ((int)$row->acl_flags & XWEBAXS_3) {
				echo "<font color=#" . $cTheme->main_no . "><b>Admin</b> (level&nbsp;3)</font>";
			} else {
				if ((int)$row->acl_flags & XWEBAXS_2) {
					echo "<font color=#" . $cTheme->main_yes . "><b>Reviewer</b> (level&nbsp;2)</font>";
				} else {
					echo "<i>NO ACCESS</i>";
				}
			}
			echo "</td>";


			if ((int)$row->acl_flags & XWEBCTL) {
				echo "<td><font color=#" . $cTheme->main_yes . "><b>ENABLED</b></Font></td>";
			} else {
				echo "<td><i>NO ACCESS</i></td>";
			}

			if ((int)$row->acl_flags & XWEBACL) {
				echo "<td><font color=#" . $cTheme->main_yes . "><b>ENABLED</b></Font></td>";
			} else {
				echo "<td><i>NO ACCESS</i></td>";
			}

			if ((int)$row->acl_flags & XDOMAIN_LOCK) {
				echo "<td><font color=#" . $cTheme->main_yes . "><b>ENABLED</b></Font></td>";
			} else {
				echo "<td><i>NO ACCESS</i></td>";
			}


			if ((int)$row->acl_flags & XWEBUSR_TOASTER) {
				echo "<td><font color=#" . $cTheme->main_yes . "><b>ENABLED</b> (view+post)</Font></td>";
			} else {
				if (((int)$row->acl_flags & XWEBUSR_TOASTER_RDONLY) == XWEBUSR_TOASTER_RDONLY) {
					echo "<td><font color=#" . $cTheme->main_yes . "><b>ENABLED</b> (view only)</Font></td>";
				} else {
					echo "<td><i>NO ACCESS</i></td>";
				}
			}

			if ((int)$row->acl_flags & XSUSPEND_USR) {
				echo "<td><font color=#" . $cTheme->main_yes . "><b>ENABLED</b></Font></td>";
			} else {
				echo "<td><i>NO ACCESS</i></td>";
			}

			if ((int)$row->acl_flags & XUNSUSPEND_USR) {
				echo "<td><font color=#" . $cTheme->main_yes . "><b>ENABLED</b></Font></td>";
			} else {
				echo "<td><i>NO ACCESS</i></td>";
			}

			if ((int)$row->acl_flags & XWEBSESS) {
				echo "<td><font color=#" . $cTheme->main_yes . "><b>ENABLED</b></Font></td>";
			} else {
				echo "<td><i>NO ACCESS</i></td>";
			}

			if ((int)$row->acl_flags & XLOGGING_VIEW) {
				echo "<td><font color=#" . $cTheme->main_yes . "><b>ENABLED</b></Font></td>";
			} else {
				echo "<td><i>NO ACCESS</i></td>";
			}

			if ((int)$row->acl_flags & XCOMPLAINTS_ADM_REPLY) {
				echo "<td><font color=#" . $cTheme->main_yes . "><b>READ+REPLY</b></Font></td>";
			} else {
				if ((int)$row->acl_flags & XCOMPLAINTS_ADM_READ) {
					echo "<td><font color=#" . $cTheme->main_yes . "><b>READ ONLY</b></Font></td>";
				} else {
					echo "<td><i>NO ACCESS</i></td>";
				}
			}

			$ax = 0; $axx = "";
			if (((int)$row->acl_flags & XIPR_VIEW_OWN) == XIPR_VIEW_OWN) {
				if ($ax==1) { $axx .= ", "; }
				$axx .= "View Own"; $ax=1;
			}
			if (((int)$row->acl_flags & XIPR_VIEW_OTHERS) == XIPR_VIEW_OTHERS) {
				if ($ax==1) { $axx .= ", "; }
				$axx .= "View Others"; $ax=1;
			}
			if (((int)$row->acl_flags & XIPR_MOD_OWN) == XIPR_MOD_OWN) {
				if ($ax==1) { $axx .= ", "; }
				$axx .= "Change own"; $ax=1;
			}
			if (((int)$row->acl_flags & XIPR_MOD_OTHERS) == XIPR_MOD_OTHERS) {
				if ($ax==1) { $axx .= ", "; }
				$axx .= "Change others"; $ax=1;
			}
			if ($ax==1) {
				echo "<td><font color=#" . $cTheme->main_yes . "><b>ENABLED</b><br>" . $axx . "</font></td>";
			} else {
				echo "<td><i>NO ACCESS</i></td>";
			}

			if ($admin>=800) {
				$edit_url = "acl.php?mode=editacl&ts=" . $zets . "&crc=" . md5($HTTP_USER_AGENT . $CRC_SALT_0008 . $user_id . $zets ) . "&userid=" . $row->user_id;

				echo "<td><input type=button onclick=\"location.href='" . $edit_url . "';\" value=\"Edit ACL\"><br><input type=button onclick=\"location.href='acl.php?mode=remove&acl_id=" . $row->acl_id . "&crc=" . md5($row->acl_id . CRC_SALT_0011 . $user_id) . "&bu=1';\" value=\"Remove ACL\">";
				echo "<br><b>Last modif.:</b><br>\n";
				$unf = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $row->last_updated_by . "'");
				$damodifier = "** SYSTEM **";
				if (pg_numrows($unf)>0) {
					$ooo = pg_fetch_object($unf,0);
					$damodifier = $ooo->user_name;
				}
				echo "<a href=\"../users.php?id=" . $row->last_updated_by . "\">" . $damodifier . "</a> (" . cs_time($row->last_updated) . ")";
				echo "</td>";
			}
			echo "</tr>";
		}

		echo "</table>\n";
		echo "</form>\n";
	}
}

?>
</body>
</html>
