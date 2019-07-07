<?
/* $Id: admin_reject.php,v 1.14 2003/03/15 05:59:00 nighty Exp $ */
	$cache_page=1;
	$def_day_duration=90;
	$min_lvl=800;
        require("../../../php_includes/cmaster.inc");
        std_connect();
        $user_id = std_security_chk($auth);
$cTheme = get_theme_info();
	if ($user_id<=0) {
		echo "You must be logged in to view that page. <a href=\"../index.php\" target=\"_top\">click here</a>.<br>\n";
		echo "</body></html>\n\n";
		die;
	}
        $admin = std_admin();

        if (!acl(XWEBAXS_2) && !acl(XWEBAXS_3)) {
        	echo "Sorry, your admin access is too low.<br>\n";
        	echo "</body></html>\n\n";
        	die;
        }
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
        $row = pg_fetch_object($res,0);
        $user_name = $row->user_name;


echo "<html><head><title>REGISTRATION PROCESS</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");


if ($force!=1) {
if ($id=="" || $id<=0 || $decision=="" || $pcts<=0) {
	echo "<b>Invalid arguments</b><br>\n";
	echo "id = $id<br>\n";
	echo "decision = $decision<br>\n";
	echo "pcts = $pcts<br>\n";
	die;
}

	$res = pg_safe_exec("SELECT channels.name,users.email,users.user_name FROM channels,pending,users WHERE channels.id='$id' AND channels.id=pending.channel_id AND pending.manager_id=users.id");
	$row = pg_fetch_object($res,0);
	$c_name = $row->name;
	$app_channel = $c_name;
	$app_username = $row->user_name;
	$app_email = $row->email;
	$adm_user = $user_name;

	echo "<b>CHANNEL APPLICATION REJECT</b><br><hr noshade size=2><br>\n";
	echo "<form name=rejectconfirm action=admin_reject.php method=get>\n";
	echo "<input type=hidden name=force value=1>\n";
	echo "<input type=hidden name=channel_id value=$id>\n";
	echo "<input type=hidden name=pending_cts value=$pcts>\n";
	echo "<input type=hidden name=ret value=\"" . urlencode(urldecode($rret)) . "\">\n";
	$decision=str_replace("\\&quot;","&quot;",str_replace("\r","",str_replace("\n","<br>",htmlspecialchars($decision))));
	echo "<input type=hidden name=decision value=\"" . $decision . "\">\n";
	echo "<h2>Are you sure you want to reject channel $c_name for the following reason ?</h2>\n";
	echo str_replace("\'","'",$decision);
	echo "<br><br>\n";
	echo "<input type=submit value=\" YES, REJECT THIS CHANNEL \">&nbsp;&nbsp;&nbsp;";
	echo "<input type=button value=\" NO, I'M A WEENIE \" onClick=\"location.href='$HTTP_REFERER';\">\n";
	echo "<br><br>";
	echo "<hr width=100%><br><br>\n";
	echo "<b>If you plan to put either the applicant or the channel in NOREG,<br>\n";
	if (preg_match("/bogus_/",$QUERY_STRING)) {
		echo "please check that box : <input type=checkbox value=\"NOREG\" checked name=extaction>, and check if the information<br>\n";
	} else {
		echo "please check that box : <input type=checkbox value=\"NOREG\" name=extaction>, and check if the information<br>\n";
	}
	echo "below is correct or not, then click </b>[ YES, REJECT THIS CHANNEL ]<b> button above.</b><br><br>\n";
//	echo "Put <select name=noregtype><option value=0 selected>Both username and channel</option><option value=1>Only username</option><option value=2>Only channel</option></select>&nbsp;in NOREG.<br>\n";
	echo "If you DO NOT WANT some parts (ie. username, email or channel)<br>put in NOREG, you should uncheck the right checkboxes below.<br>\n";
	echo "<br>";
	echo "<table border=0><tr><td valign=top>\n";
	echo "<table border=1 cellspacing=0 cellpadding=3>\n";
	$tmp = "bogus_$app_username";
	if ($$tmp==1) {
		$chkusername="checked";
		$chkemail="checked";
		$chkchannel="checked";
	} else {
		$chkusername="";
		$chkemail="";
		$chkchannel="";
	}
	echo "<tr><td align=right><b>user_name</b>&nbsp;</td><td><input type=hidden name=NR_user_name size=35 value=$app_username><input type=checkbox name=put_username value=1 $chkusername >&nbsp;$app_username</td></tr>\n";
	echo "<tr><td align=right><b>email</b>&nbsp;</td><td><input type=hidden name=NR_email size=35 value=$app_email><input type=checkbox name=put_email value=1 $chkemail >&nbsp;$app_email</td></tr>\n";
	echo "<tr><td align=right><b>channel_name</b>&nbsp;</td><td><input type=hidden name=NR_channel_name size=35 value=$app_channel><input type=checkbox name=put_channel value=1>&nbsp;$app_channel</td></tr>\n";
	if ($$tmp==1) {
		echo "<tr><td align=right><b>type</b>&nbsp;</td><td>[<input checked onclick=\"fill('NR_reason',0)\" type=radio name=NR_type value=2>Abuse]&nbsp;[<input onclick=\"fill('NR_reason',1)\" type=radio name=NR_type value=4>Fraud Username]</td></tr>\n";
		echo "<tr><td align=right><b>never_reg</b>&nbsp;</td><td><input type=checkbox name=NR_never_reg value=1></td></tr>\n";
	} else {
		echo "<tr><td align=right><b>type</b>&nbsp;</td><td>[<input onclick=\"fill('NR_reason',0)\" type=radio name=NR_type value=2 checked>Abuse]&nbsp;[<input onclick=\"fill('NR_reason',1)\" type=radio name=NR_type value=4>Fraud Username]</td></tr>\n";
		echo "<tr><td align=right><b>never_reg</b>&nbsp;</td><td><input type=checkbox name=NR_never_reg value=1></td></tr>\n";
	}
	echo "<tr><td align=right><b>for_review</b>&nbsp;</td><td><input type=checkbox name=NR_for_review value=1></td></tr>\n";
	echo "<tr><td align=right><b>expire_time</b>&nbsp;</td><td>in ";

	//<input type=text name=NR_expire_period0 size=10 value=" . $def_day_duration . ">";
	echo "<select name=NR_expire_period0><option value=30>30</option><option selected value=60>60</option><option value=90>90</option></select>";

	echo "&nbsp;day(s).<input type=hidden name=NR_expire_period1 value=0></td></tr>\n";
	echo "<tr><td align=right><b>created_ts</b>&nbsp;</td><td><b>AUTOMATIC</b></td></tr>\n";
	echo "<tr><td align=right><b>set_by</b>&nbsp;</td><td><i>$adm_user</i><input type=hidden name=NR_set_by value=\"$adm_user\"></td></tr>\n";
	if ($$tmp==1) {
		echo "<tr><td align=right><b>reason</b>&nbsp;</td><td><input type=text name=NR_reason size=35 value=\"registration fraud (" . str_replace("\"","&quot;",$c_name) . ")\"></td></tr>\n";
	} else {
		echo "<tr><td align=right><b>reason</b>&nbsp;</td><td><input type=text name=NR_reason size=35 value=\" (" . str_replace("\"","&quot;",$c_name) . ")\"></td></tr>\n";
	}
	echo "</table><br><input type=reset value=\" RESET FORM \"></td>\n";

	echo "<td valign=top><table border=1 cellspacing=0 cellpadding=3>\n";
	echo "<tr><td><b>supporter_name</b><br><a href=\"javascript:check_all();\">All</a>&nbsp;&nbsp;&nbsp;<a href=\"javascript:check_none();\">None</a></td>";
	echo "<td><b>type</b></td>";
	echo "<td><b># days</b></td>";
	echo "<td><b>Reason</b></td></tr>\n";

	$uuu = pg_safe_exec("SELECT * FROM supporters,pending,users WHERE supporters.channel_id=pending.channel_id AND pending.channel_id='$id' AND supporters.user_id=users.id ORDER BY users.id");
	if (pg_numrows($uuu) == 0) {
		echo "<b>FATAL ERROR : NO SUPPORTERS TO THAT APPLICATION !@#</b><br>\n";
	} else {
		for ($x=0;$x<pg_numrows($uuu);$x++) {
			$row = pg_fetch_object($uuu,$x);
			echo "<tr>";
			$ttmp = pg_safe_exec("SELECT * FROM noreg WHERE lower(user_name)='" . strtolower($row->user_name) . "'");
			if (pg_numrows($ttmp)==0) {
				$tmp = "bogus_" . $row->user_name;
				if ($$tmp==1) {
					echo "<td><input type=checkbox checked value=1 name=supnoreg$x >&nbsp;<input type=hidden name=supname$x value=\"" . $row->user_name . "\">" . $row->user_name . "</td>";
					echo "<td>[<input onclick=\"fill('supreason$x',0)\" checked type=radio name=suptype$x value=1>Abuse]<br>[<input onclick=\"fill('supreason$x',1)\" type=radio name=suptype$x value=2>Fraud Username]</td>";
				//	echo "<td><input type=text name=supdays$x size=4 maxlength=3 value=" . $def_day_duration . "></td>";
					echo "<td><select name=supdays" . $x . "><option value=30>30</option><option selected value=60>60</option><option value=90>90</option></select></td>";
					echo "<td><input type=text name=supreason$x size=20 maxlength=255 value=\"registration fraud (" . str_replace("\"","&quot;",$c_name) . ")\"></td>";
				} else {
					echo "<td><input type=checkbox value=1 name=supnoreg$x >&nbsp;<input type=hidden name=supname$x value=\"" . $row->user_name . "\">" . $row->user_name . "</td>";
					echo "<td>[<input onclick=\"fill('supreason$x',0)\" type=radio name=suptype$x checked value=1>Abuse]<br>[<input onclick=\"fill('supreason$x',1)\" type=radio name=suptype$x value=2>Fraud Username]</td>";
				//	echo "<td><input type=text name=supdays$x size=4 maxlength=3 value=" . $def_day_duration . "></td>";
					echo "<td><select name=supdays" . $x . "><option value=30>30</option><option selected value=60>60</option><option value=90>90</option></select></td>";
					echo "<td><input type=text name=supreason$x size=20 maxlength=255 value=\"registration fraud (" . str_replace("\"","&quot;",$c_name) . ")\"></td>";
				}
			} else {
				echo "<td><input type=checkbox value=1 name=supnoreg$x >&nbsp;<b>" . $row->user_name . "</b></td>";
				echo "<td colspan=3><i>This user name is already in NOREG records (ignored)</i></td>";
			}
			echo "</tr>\n";
		}
	}


	echo "</table></td></tr></table>\n";
?>
<script language="JavaScript1.2">
<!--
	function check_all() {
		for (x=0;x<<?=pg_numrows($uuu)?>;x++) {
			eval("document.forms[0].supnoreg"+x+".checked=true;");
		}
	}
	function check_none() {
		for (x=0;x<<?=pg_numrows($uuu)?>;x++) {
			eval("document.forms[0].supnoreg"+x+".checked=false;");
		}
	}
	function fill(i_name,t) {
		if (t==0) {
			eval("document.forms[0]."+i_name+".value='registration fraud <? echo "(" . str_replace("'","\\\\'",str_replace("\"","\\\"",$c_name)) . ")"; ?>';");
		}
		if (t==1) {
			eval("document.forms[0]."+i_name+".value='bogus username <? echo "(" . str_replace("'","\\\\'",str_replace("\"","\\\"",$c_name)) . ")"; ?>';");
		}
	}
//-->
</script>
<?


	echo "</form>\n";
} else {
if ($channel_id=="" || $channel_id<=0 || $decision=="" || $pending_cts<=0) {
	echo "<b>Invalid arguments</b><br>\n";
	echo "channel_id=$channel_id<br>\n";
	echo "decision=$decision<br>\n";
	echo "pending_cts=$pending_cts<br>\n";
	echo "ret=$ret<br>\n";
	die;
}

	if ($extaction=="NOREG") {
		$added = "";
		$check_first=0;
		if ($put_username==1) { $added .= "username "; $check_first=1; }
		if ($put_email==1) { $added .= "email "; $check_first=1; }
		if ($put_channel==1) { $added .= "channel "; $check_first=1; }
		if ($NR_type=="") {
			echo "<h2> Invalid <b>type</b>\n";
			$check_first = 0;
		} else if ($NR_expire_period0=="" || ($NR_expire_period0+0)<=0) {
			echo "<h2> Invalid <b>expiration</b> value.\n";
			$check_first = 0;
		}
		if ($check_first!=1) {
			$noapplicant = 1;
		} else {
			$noapplicant = 0;
		}
	}

	$c = $channel_id;
	$id = $pending_cts;
	$tts = time();

	if (!($c>0 && $id>0)) { die("err..."); }
	$decision = str_replace(";",":",$decision);

	//$decision2 = "by <b>$user_name</b> (CService Admin)<br>" . $decision;
	$decision2 = "by CService Admin<br>" . $decision;

	$quer2 = "UPDATE pending SET status='9',last_updated=now()::abstime::int4,decision_ts=now()::abstime::int4,decision='$decision2' WHERE channel_id='$c' AND (status='0' OR status='1' OR status='2' OR status='8') AND created_ts='$id'";
	//echo "$quer2<br><br>\n";
	//$res = pg_safe_exec($quer2);
	pg_safe_exec($quer2);
	//echo "$res<br><br>\n";

	$tmp1 = pg_safe_exec("SELECT manager_id FROM pending WHERE channel_id='$c' AND created_ts='$id'");
	$tmp2 = pg_fetch_object($tmp1,0);
	$uid = $tmp2->manager_id;

/*
	$quer3 = "INSERT INTO mailq (user_id,channel_id,created_ts,template,var1,var2,var3,var4,var5) VALUES ($uid,$c,now()::abstime::int4,2,'" . str_replace("<br>","\n",$decision) . "','','','','')";
	pg_safe_exec($quer3);
*/

	$qqq = "UPDATE pending SET reviewed='Y',reviewed_by_id='$user_id' WHERE channel_id='$c'";
	pg_safe_exec($qqq);

	$tmp = pg_safe_exec("SELECT supporters.user_id,users.user_name,users.flags FROM supporters,pending,users WHERE supporters.user_id=users.id AND pending.channel_id=supporters.channel_id AND pending.channel_id='$c' ORDER BY users.id");
	$sup_list = "";
	$s_noreg=0;
	if ($extaction=="NOREG") {
		for ($x=0;$x<pg_numrows($tmp);$x++) {
			$tmp_o = pg_fetch_object($tmp,$x);
			$tmpX = "supnoreg$x";
			if ($$tmpX==1) {
				$s_noreglist[$s_noreg]=$x;
				$s_flaglist[$s_noreg]=$tmp_o->flags;
				$s_noreg++;
			}
		}
		if ($s_noreg>0) {
			$added .= " ( supporters: ";
			for ($x=0;$x<count($s_noreglist);$x++) {
				$y = $s_noreglist[$x];
				$tmp2 = "supname$y";
				$res = pg_safe_exec("SELECT * FROM noreg WHERE lower(user_name)='" . strtolower($$tmp2) . "'");
				if (pg_numrows($res)==0 && !((int)$s_flaglist[$x] & 0x0008) ) {
					$added .= $$tmp2 . " ";
				}
			}
			$added .= ")";
		}
	}
	for ($x=0;$x<pg_numrows($tmp);$x++) {
		$row = pg_fetch_object($tmp,$x);
		$uname = $row->user_name;
		$uuid = $row->user_id;
		$sup_list .= "$uname (" . $uuid . ")";
		if ($x!=(pg_numrows($tmp)-1)) { $sup_list .=", "; } else { $sup_list .="."; }
	}
	$nbsupz=pg_numrows($tmp);

	$tmp = pg_safe_exec("SELECT user_name,flags FROM users WHERE id='$uid'");
	$obj = pg_fetch_object($tmp,0);
	$applicant = $obj->user_name . " (" . $uid . ")";
	$appflags = $obj->flags;

	if ($extaction=="NOREG") {
		$sup_list .= " Added to NOREG : " . trim($added) . ".";
	}

	log_channel($c,13,"Manual Application Reject ($decision) - Applicant was: $applicant, Supporters were : " . $sup_list);
	review_count_add($user_id);

	if ($extaction=="NOREG") {
		$u_name = $NR_user_name;
		$def_email = $NR_email;
		$c_name = $NR_channel_name;
		if ($NR_reason!="") { $def_reason = $NR_reason; } else {
			if ($NR_type==2) {
				$def_reason = "registration fraud (" . str_replace("'","\\'",$c_name) . ")";
			}
			if ($NT_type==4) {
				$def_reason = "bogus username (" . str_replace("'","\\'",$c_name) . ")";
			}
		}
		$set_by = $user_name;
		if ($NR_never_reg=="") { $NR_never_reg=0; }
		if ($NR_for_review=="") { $NR_for_review=0; }
		$nb_days = $NR_expire_period0;
		if ($noapplicant==0) {
			if ($put_channel==1) {
				if ($NR_type==4) { $NR_type2 = 2;  $def_reason = "Abusive application (Fraud Usernames/Applicant) (" . str_replace("'","\\'",$c_name) . ")"; } else { $NR_type2 = $NR_type; }
				$quer3 = "INSERT INTO noreg (user_name,email,channel_name,type,never_reg,for_review,expire_time,created_ts,set_by,reason) VALUES ('','','$c_name',$NR_type2,$NR_never_reg,$NR_for_review,(now()::abstime::int4+86400*$nb_days),now()::abstime::int4,'$set_by','$def_reason')";
				//echo $quer3 . "<br><br>\n";
				pg_safe_exec($quer3);
			}
			if ($put_username!=1) { $u_name = ""; }
			if ($put_email!=1) { $def_email = ""; }
			if ($u_name!="" || $def_email!="") {
				$preq4 = "SELECT flags,id FROM users WHERE user_name='$u_name'";
				$pres4 = pg_safe_exec($preq4);
				$prow4 = pg_fetch_object($pres4,0);
				$p_uid = $prow4->id;
				$p_flags = $prow4->flags;
				if ($NR_type==4) { $p_flags = $p_flags|0x0008; // Fraud TAG
				}
				$quer4b = "UPDATE users SET last_updated=now()::abstime::int4,last_updated_by='*** TAGGED AS FRAUD ***',flags='" . $p_flags . "' WHERE id='" . $p_uid . "'";
				$quer4 = "INSERT INTO noreg (user_name,email,channel_name,type,never_reg,for_review,expire_time,created_ts,set_by,reason) VALUES ('$u_name','$def_email','',$NR_type,$NR_never_reg,$NR_for_review,(now()::abstime::int4+86400*$nb_days),now()::abstime::int4,'$set_by','$def_reason')";
				//echo $quer4 . "<br><br>\n";
				pg_safe_exec($quer4);
				pg_safe_exec($quer4b);
			}
		}
		for ($x=0;$x<$nbsupz;$x++) { // check supporters noregs/fraud
			$supnoreg_t = "supnoreg$x";
			if ($$supnoreg_t==1) {
				$supname_t = "supname$x";
				$suptype_t = "suptype$x";
				$supdays_t  = "supdays$x";
				$supreason_t = "supreason$x";
				// find user email
				if ($$supname_t!="") {
					$req = pg_safe_exec("SELECT email FROM users WHERE lower(user_name)='" . strtolower($$supname_t) . "'");
					$row = pg_fetch_object($req,0);
					$supporter_email = $row->email;
					$set_by = $user_name;
					$reason = $$supreason_t;
					if ($reason == "") {
						if ($$suptype_t==1) {
							$reason = "fraud username (" . str_replace("'","\\'",$c_name) . ")";
						}
						if ($$suptype_t==2) {
							$reason = "bogus username (" . str_replace("'","\\'",$c_name) . ")";
						}
					}
					if ($$suptype_t==1) { //Abuse (2)
						$nbdays = $$supdays_t+0;
						$expire_at = "(now()::abstime::int4+86400*$nb_days)";
						$NR_type = 2;
						$NR_neverreg = 0;
					} else if ($$suptype_t==2) { //Fraud Username (4)
						$expire_at = "0";
						$NR_type = 4;
						$NR_neverreg = 1;
					} else {
						echo "<b>BAD ARGUMENTS</b>";
						die;
					}
				}

				$bla = pg_safe_exec("SELECT * FROM noreg WHERE lower(user_name)='" . strtolower($$supname_t) . "'");

				if ($$supname_t!="" && pg_numrows($bla)==0) {
					$preq = "SELECT flags,id FROM users WHERE lower(user_name)='" . strtolower($$supname_t) . "'";
					$pres = pg_safe_exec($preq);
					$prow = pg_fetch_object($pres,0);
					$p_uid = $prow->id;
					$p_flags = $prow->flags;
					if ($NR_type==4) { $p_flags = $p_flags|0x0008; // Fraud TAG
					}
					$queryb = "UPDATE users SET last_updated=now()::abstime::int4,last_updated_by='*** TAGGED AS FRAUD ***',flags='" . $p_flags . "' WHERE id='" . $p_uid . "'";
					$query  = "INSERT INTO NOREG (user_name,email,channel_name,type,never_reg,for_review,expire_time,created_ts,set_by,reason) VALUES ";
					$query .= "('" . $$supname_t . "','$supporter_email','',$NR_type,$NR_neverreg,0,$expire_at,now()::abstime::int4,'$set_by','$reason')";
					pg_safe_exec($query);
					pg_safe_exec($queryb);
				}
			}
		}
	}

	echo "<font color=#" . $cTheme->main_warnmsg . "><b>CHANNEL HAS BEEN REJECTED SUCCESSFULLY</b></font>\n";

	if ($ret=="") { $ret = "../list_app.php"; }

	echo "<script language=\"JavaScript1.2\">\n<!--\n\tsetTimeout(location.href='" . str_replace("'","\\'",urldecode($ret)) . "',1500);\n//-->\n</script>\n";




}
?>
</body>
</html>
