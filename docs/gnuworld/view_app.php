<?
/* $Id: view_app.php,v 1.22 2004/03/07 22:04:31 nighty Exp $ */

	$min_lvl=800;
	require("../../php_includes/cmaster.inc");
	std_connect();
	$user_id = std_security_chk($auth);
	$admin = std_admin();
	if ($admin==0) { check_file("regproc.3"); check_file("regproc.1"); }
	if ($id=="" || !(isset($id))) {
		header("Location: list_app.php\n\n");
		die;
	}
	$cTheme = get_theme_info();
	std_theme_styles(1);
	std_theme_body();

	echo "<b>CHANNEL SERVICE APPLICATIONS</b> - VIEW APPLICATION<br><hr size=2 noshade><br>\n";

	if ($special_ret!="") {
		echo "<a href=\"" . str_replace("#","%23",$special_ret) . "\">Back to list/check/previous page</a><br><br>\n";
		$backlink=urldecode(str_replace("#","%23",$special_ret));
	} else {
		if (!is_needle_in_haystack("right.php", $HTTP_REFERER)) {
			if (!is_needle_in_haystack("list_app.php", $HTTP_REFERER)) {
				if ($back=="checkapp") {
					echo "<a href=\"check_app.php\">Check another application</a><br><br>\n";
					$backlink="check_app.php";
				} else {
					echo "<a href=\"list_app.php\">Back to Application List</a><br><br>\n";
					$backlink="list_app.php";
				}
			} else {
				echo "<a href=\"$HTTP_REFERER\">Back to Application List</a><br><br>\n";
				$backlink=$HTTP_REFERER;
			}
		} else {
			echo "<a href=\"right.php\">Back to previous page</a><br><br>\n";
			$backlink="right.php";
		}
	}

	if ($id=="" || !(isset($id))) {
		echo "</body></html>\n\n";
		die;
	}
	$nrw_lvl=0;
	if (acl(XWEBAXS_2)) { $nrw_lvl=1; }
	if (acl(XWEBAXS_3)) { $nrw_lvl=2; }
	$tmp = explode("-",$id);
	$channel_id = $tmp[1];
	$created_ts = $tmp[0];
	$force_supporters=0;
	if ($special==1) { $zeaddy="_h"; } else { $zeaddy=""; }
	if ($admin>0) { // admin tools
		echo "<font color=#" . $cTheme->main_warnmsg . "><b>ADMIN SPECIAL FEATURES</b>:<br></font>\n";
		$ros = pg_safe_exec("SELECT status,join_count,unique_join_count FROM pending$zeaddy WHERE channel_id='$channel_id' AND created_ts='$created_ts'");
		$ptmp = pg_fetch_object($ros,0);
		$c_stats=$ptmp->status;
		$show_counts=0;
		$show_ctime=0;
		switch ($c_stats) {
			case 0:
				$show_counts=0;
				$show_ctime=0;
				break;
			case 1:
				$show_counts=1;
				$show_ctime=1;
				break;
			case 2:
				$show_counts=1;
				$show_ctime=1;
				break;
			case 3:
				$show_counts=1;
				$show_ctime=0;
				break;
			case 4:
				$show_counts=1;
				$show_ctime=0;
				break;
			case 8:
				$show_counts=1;
				$show_ctime=0;
				break;
			case 9:
				$show_counts=1;
				$show_ctime=0;
				break;
		}
		echo "<b>Supporters</b>: (<font color=#" . $cTheme->main_support . "><b>username</b></font>=support, <font color=#" . $cTheme->main_nonsupport . "><b>username</b></font>=non-support, <font color=#" . $cTheme->main_notyet . "><b>username</b></font>=awaiting confirmation)<br>\n";
		echo "<font size=+1>\n";
		$cnt = pg_safe_exec("SELECT COUNT(*) AS ccount FROM supporters WHERE channel_id='$channel_id'");
		$roo = pg_fetch_object($cnt,0);
		$ccount = $roo->ccount;
		$res = pg_safe_exec("SELECT * FROM supporters WHERE channel_id='$channel_id'");
		$total_supjoins=0;
		$zetypes[1]="Non-Support";
		$zetypes[2]="Abuse";
		$zetypes[3]="Elective";
		$zetypes[4]="Fraud Username";

		if (($admin>=$min_lvl || $nrw_lvl>=1) && ($c_stats!=9)) {
			echo "<form name=reject action=regproc/admin_reject.php method=get onsubmit=\"return check_rej(this);\">\n";
		}
		if ($nrw_lvl>0 || $admin>0) {
			$sup_count = 1;
			if ($c_stats!=9 && $c_stats!=3 && $c_stats!=4 && $nrw_lvl>0) {
				echo "<br>";
				echo "<script language=\"JavaScript1.2\">\n<!--\n";
				echo "function admintracker() {\n";
			        echo "\tmyurl = 'app_tracker.php?APPID=" . $id . "&RETURL=" . urlencode($_SERVER['REQUEST_URI']) . "';\n";
			        echo "\tpopwin = open(myurl,'AppTracker','toolbar=no,directories=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=no,width=250,height=500');\n";
			        echo "\tpopwin.focus();\n";
				echo "}\n";
				echo "//-->\n</script>\n";
				echo "<a href=\"javascript:admintracker();\"><b>Admin Tracker</b></a><br>\n";
			}
			echo "<br><table border=1 cellspacing=0 cellpadding=3>\n";
			echo "<tr bgcolor=#" . $cTheme->table_sepcolor . ">\n";
			echo "<td><font color=#" . $cTheme->table_septextcolor . "><b>Type</b></font></td>";
			echo "<td><font color=#" . $cTheme->table_septextcolor . "><b>Username</b></font></td>";
			echo "<td><font color=#" . $cTheme->table_septextcolor . "><b>User ID</b></font></td>";
			echo "<td><font color=#" . $cTheme->table_septextcolor . "><b>Noreg/Fraud/<br>Suspend</b></font></td>";
			if ($nrw_lvl>=1 && $c_stats!=9 && $c_stats!=3 && $c_stats!=4) { echo "<td><font color=#" . $cTheme->table_septextcolor . "><b>mark</b></font></td>"; }
			if ($show_counts) { echo "<td><font color=#" . $cTheme->table_septextcolor . "><b>Join Count</b></font></td>"; }
			if ($sup_count) { echo "<td><font color=#" . $cTheme->table_septextcolor . "><b># channels<br>supported</b></font></td>"; }
			echo "<td><font color=#" . $cTheme->table_septextcolor . "><b># accesses</b></font></td>";
			echo "<td><font color=#" . $cTheme->table_septextcolor . "><b>E-mail</b></font></td>";
			echo "<td><font color=#" . $cTheme->table_septextcolor . "><b>Verification data</b></font></td>";
			echo "</tr>\n";

			$ress = pg_safe_exec("SELECT users_lastseen.last_hostmask,users.id,users.user_name,users.email,users.flags,users.verificationdata,users.question_id FROM pending,users,users_lastseen WHERE pending.manager_id=users.id AND pending.created_ts='$created_ts' AND pending.channel_id='$channel_id' AND users_lastseen.user_id=users.id");
			if (pg_numrows($ress)==0) { // no aplicant ????!!
				echo "<h2>BOGUS APPLICATION</h2>\n";
				die;
			}
			$ruw = pg_fetch_object($ress,0);
			echo "<tr bgcolor=#" . $cTheme->table_tr_enlighten . ">\n";
			echo "<td>Applicant</td>\n";
			echo "<td><a href=\"users.php?id=" . $ruw->id . "\"><font color=#" . $cTheme->main_textcolor . "><b>" . $ruw->user_name . "</b></font></a><br>";
			echo "<font size=-1><nobr><i>";
			if ($ruw->last_hostmask!="") {
				echo htmlspecialchars($ruw->last_hostmask);
			} else {
				echo "<b>never logged in</b>";
			}
			echo "</i></nobr></font>";
			echo "</td>";
			echo "<td>" . $ruw->id . "</td>\n";
			$lowuser = strtolower($ruw->user_name);
			$bli = pg_safe_exec("select type from noreg where lower(user_name)='$lowuser'");
			if (pg_numrows($bli)>0) {
				$row = pg_fetch_object($bli,0);
				$zetype = $row->type;
			} else {
				if ((int)$ruw->flags & 0x0008) {
					$zetype = 4;
				} else {
					$zetype = 0;
				}
			}
			if ($zetype>0 && $zetype<5) {
				echo "<td><b>" . $zetypes[$zetype] . "</b></td>";
			} else {
				if ((int)$ruw->flags & 0x0001) {
					echo "<td><b>Suspended!!</b></td>";
				} else {
					echo "<td>NO</td>";
				}
			}
			if ($nrw_lvl>=1 && $c_stats!=9 && $c_stats!=3 && $c_stats!=4) { echo "<td><input type=checkbox value=1 name=bogus_" . $ruw->user_name . "></td>"; }
			if ($show_counts) { echo "<td align=center><i>N/A</i></td>"; }
			if ($sup_count) {
				$tmp0 = pg_safe_exec("SELECT COUNT(*) AS s_count FROM supporters,pending WHERE supporters.channel_id=pending.channel_id AND (pending.status<3 OR pending.status=8) AND supporters.user_id='" . $ruw->id . "'");
				$obj0 = pg_fetch_object($tmp0,0);
				$s_count = $obj0->s_count;
				echo "<td align=center>" . $s_count . "</td>";
			}
			echo "<td align=center>";
			$blah = pg_safe_exec("SELECT COUNT(*) AS count FROM users,levels,channels WHERE users.id='" . $ruw->id . "' AND users.id=levels.user_id AND channels.id=levels.channel_id AND channels.registered_ts>0 AND levels.access>0");
			$bloh = pg_fetch_object($blah,0);
			echo $bloh->count;
			echo "</td>";
			echo "<td>" . $ruw->email . "</td>";
			if ($ruw->question_id>0 && $ruw->verificationdata!="") {
				echo "<td>" . $ruw->verificationdata . "</td>";
			} else {
				echo "<td><i>*** NOT SET ***</i></td>\n";
			}

			echo "</tr>\n";
			for ($x=0;$x<$ccount;$x++) {
				$row = pg_fetch_object($res,$x);
				$j_c=$row->join_count;
				if ($j_c>0) { echo "<tr bgcolor=#" . $cTheme->table_tr_enlighten2 . ">\n"; } else { echo "<tr>\n"; }
				$tmp = pg_safe_exec("SELECT users_lastseen.last_hostmask,users.user_name,users.email,users.flags,users.question_id,users.verificationdata FROM users,users_lastseen WHERE users.id='" . $row->user_id . "' AND users_lastseen.user_id=users.id");
				$tro = @pg_fetch_object($tmp,0);
				$tcol = "#" . $cTheme->main_notyet;
				if ($row->support=="Y") { $tcol = "#" . $cTheme->main_support; }
				if ($row->support=="N") { $tcol = "#" . $cTheme->main_nonsupport; }
				if ($row->user_id==$user_id) { $force_supporters=1; }
				if ($j_c=="") { $j_c=0; }
				if ($j_c>0) { $total_supjoins++; }
				if ($j_c>0) { $j_c = "<b>$j_c</b>"; }
				echo "<td>Supporter</td>\n";
				if ($tro->user_name == "") {
					echo "<td><b><i>* removed user *</i></b><br>";
				} else {
					echo "<td><a href=\"users.php?id=" . $row->user_id . "\"><font color=$tcol><b>" . $tro->user_name . "</b></font></a><br>";
				}
				echo "<nobr><font size=-1><i>";
				if ($tro->last_hostmask!="") {
					echo htmlspecialchars($tro->last_hostmask);
				} else {
					echo "<b>never logged in</b>";
				}
				echo "</i></font><nobr>\n";
				echo "</td>";
				echo "<td>" . $row->user_id . "</td>\n";
				$da_uid = $row->user_id;
				$lowuser = strtolower($tro->user_name);
				$bli = pg_safe_exec("select type from noreg where lower(user_name)='$lowuser'");
				if (pg_numrows($bli)>0) {
					$row = pg_fetch_object($bli,0);
					$zetype = $row->type;
				} else {
					$zetype = 0;
				}

				if ($zetype>0 && $zetype<5) {
					echo "<td><b>" . $zetypes[$zetype] . "</b></td>";
				} else {
					if ((int)$tro->flags & 0x0001) {
						echo "<td><b>Suspended!!</b></td>";
					} else {
						echo "<td>NO</td>";
					}
				}

				if ($nrw_lvl>=1 && $c_stats!=9 && $c_stats!=3 && $c_stats!=4) { echo "<td><input type=checkbox value=1 name=bogus_" . $tro->user_name . "></td>"; }

				if ($show_counts) { echo "<td align=center>" . $j_c . "</td>"; }
				if ($sup_count) {
					$tmp0 = pg_safe_exec("SELECT COUNT(*) AS s_count FROM supporters,pending WHERE supporters.channel_id=pending.channel_id AND (pending.status<3 OR pending.status=8) AND supporters.user_id='" . $da_uid . "'");
					$obj0 = pg_fetch_object($tmp0,0);
					$s_count = $obj0->s_count;
					echo "<td align=center>" . $s_count . "</td>";
				}
				echo "<td align=center>";
				$unf = pg_safe_exec("SELECT users.id FROM users,levels,channels WHERE users.id='" . $da_uid . "' AND users.id=levels.user_id AND channels.id=levels.channel_id AND channels.registered_ts>0 AND levels.access>0");
				echo pg_numrows($unf);
				echo "</td>";

				echo "<td>" . $tro->email . "</td>";
				if ($tro->question_id>0 && $tro->verificationdata!="") { echo "<td>" . $tro->verificationdata . "</td>\n"; } else {
					echo "<td><i>*** NOT SET ***</i></td>";
				}

				echo "</tr>\n";
			}
			echo "</table>\n";
		} else {
			for ($x=0;$x<$ccount;$x++) {
				$row = pg_fetch_object($res,$x);
				$tmp = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $row->user_id . "'");
				$tro = pg_fetch_object($tmp,0);
				$tcol = "#" . $cTheme->main_notyet;
				if ($row->support=="Y") { $tcol = "#" . $cTheme->main_support; }
				if ($row->support=="N") { $tcol = "#" . $cTheme->main_nonsupport; }
				if ($row->user_id==$user_id) { $force_supporters=1; }
				$j_c=$row->join_count;
				if ($j_c=="") { $j_c=0; }
				if ($j_c>0) { $total_supjoins++; }
				if ($j_c>0) { $j_c = "<b>$j_c</b>"; }
				echo "<a href=\"users.php?id=" . $row->user_id . "\"><font color=$tcol><b>" . $tro->user_name . "</b></font></a>";
				if ($show_counts) { echo " (" . $j_c . ")\n"; }
				echo "&nbsp;&nbsp;&nbsp;\n";
				if ($x==4) { echo "<br>\n"; }
			}
		}
		echo "</font>\n";
		echo "<br><br>\n";

		$nb_days_history = 30;
		$tmpr = pg_safe_exec("SELECT COUNT(event) AS count FROM channellog WHERE channelid=" . ($channel_id+0) . " AND event=15 AND ts>=(now()::abstime::int4-(86400*" . $nb_days_history . "))");
		$tmpo = pg_fetch_object($tmpr,0);
		$tc = $tmpo->count;
		if ($tc>=1) { $tc--; } // dont count the "current registration".
		echo "<b>application_30days_history</b> = ";
		if ($tc>0) { echo "<a href=\"viewlogs.php?cid=" . ($channel_id+0) . "\" target=_blank>"; }
		echo $tc . " time";
		if ($tc>1) { echo "s"; }
		if ($tc>0) { echo "</a>"; }
		echo " (current application excluded from count)<br>";
		echo "<br>\n";

		if (REQUIRED_SUPPORTERS>0 && $show_counts) {
			echo "<b>unique_supporters_join_count</b> = " . $total_supjoins . "<br>\n";
			echo "<b>join_count</b> = " . $ptmp->join_count . "<br>\n";
			if ($ptmp->unique_join_count=="") { $ujc=0; } else { $ujc=$ptmp->unique_join_count; }
			echo "<b>unique_join_count</b> = " . $ujc . "<br><br>\n";
		}
		if ($c_stats == 1 || $c_stats == 2) {
			echo "<br>";
			if ($c_stats == 1) {
				if ($show_ctime) { echo "<i>NOTE: user counts are updated real-time, join counts are updated on a 30 minutes period basis</i><br>\n"; }
				echo "- <b>Traffic check</b>: in progress...<br>\n";
			} else {
				echo "- <b>Traffic check</b>: <font color=#00ee00><b>PASSED</b></font><br>\n";
				echo "- <b>Notification check</b>: in progress...<br>\n";
			}
		}
		if (($admin>=$min_lvl || $nrw_lvl>=1) && ($c_stats<3 || $c_stats==9 || $c_stats==8)) {
			echo "<br><br>\n";
			echo "<table border=1 cellspacing=0 cellpadding=5>\n";
			echo "<tr>";
			if ($c_stats!=9) {
				echo "<td valign=top align=left>\n";

//				echo "<form name=reject action=regproc/admin_reject.php method=get onsubmit=\"return check_rej(this);\">\n";
				echo "<b>Reject channel for a particular reason</b>:<br>\n";
				echo "<input type=hidden name=id value=$channel_id>\n";
				echo "<input type=hidden name=pcts value=$created_ts>\n";
				echo "<input type=hidden name=rret value=\"" . urlencode($HTTP_REFERER) . "\">\n";
				echo "<script language=\"JavaScript1.2\">\n";
				echo "<!--\n";
				echo "\tfunction check_rej(f) {\n";
				echo "\t\tif (f.decision.value=='') {\n";
				echo "\t\t\talert('You must supply a reason !!!');\n";
				echo "\t\t\treturn(false);\n";
				echo "\t\t}\n";
				echo "\t\treturn(true);\n";
				echo "\t}\n";
				$rmc = "";
				$rml = "";
				$rrm = pg_safe_exec("SELECT * FROM default_msgs WHERE type=2");
				$yy = 0;
				$opt = "";
				while ($rrmo = pg_fetch_object($rrm)) {
					$yy++;
					$opt .= "<option value=\"" . $yy . "\">" . $rrmo->label . "</option>\n";
					$rml .= ",'" . post2db($rrmo->label) . "'";	
					$temp=post2db($rrmo->content);
					$temp=str_replace("''", "\'", $temp);
					$rmc .= ",'" . $temp . "'";
				}
				echo "\tvar rej_msgs_c = [''" . $rmc . "];\n";
				echo "\tvar rej_msgs_l = [''" . $rml . "];\n";
				echo "\tfunction upd_rej_r(opt) {\n";
				echo "\t\tdocument.forms['reject'].decision.value = rej_msgs_c[parseInt(opt.options[opt.selectedIndex].value)];\n";
				echo "\t}\n";
				echo "//-->\n";
				echo "</script>\n";
				if ($opt != "") {
					echo "<select name=dmsgR onChange=\"upd_rej_r(this)\"><option value=0>-- pick a reason --</option>" . $opt . "</select><br>\n";
				}
				echo "<textarea name=decision cols=40 rows=7></textarea><br><input type=submit value=\" REJECT THIS CHANNEL \">\n";
				echo "</form>\n";

				echo "</td>";
			}
			if ($nrw_lvl>1) {
				echo "<td valign=top align=left>\n";

				if ($c_stats!=9) {
					echo "<form name=accept action=regproc/admin_complete.php method=get onsubmit=\"return check_com(this);\">\n";
				} else {
					echo "<form name=accept action=regproc/admin_complete.php method=get onsubmit=\"return check_com(this);\">\n";
					echo "<input type=hidden name=from_rejected value=\"Ok.\">";
				}
				echo "<b>Accept/Register channel for a particular reason</b>:<br>\n";
				echo "<input type=hidden name=id value=$channel_id>\n";
				echo "<input type=hidden name=pcts value=$created_ts>\n";
				echo "<input type=hidden name=rret value=\"" . urlencode($HTTP_REFERER) . "\">\n";
				echo "<script language=\"JavaScript1.2\">\n";
				echo "<!--\n";
				echo "\tfunction check_com(f) {\n";
				echo "\t\tif (f.decision.value=='') {\n";
				echo "\t\t\talert('You must supply a reason !!!');\n";
				echo "\t\t\treturn(false);\n";
				echo "\t\t}\n";
				echo "\t\treturn(true);\n";
				echo "\t}\n";
				$amc = "";
				$aml = "";
				$arm = pg_safe_exec("SELECT * FROM default_msgs WHERE type=1");
				$yy = 0;
				$opt = "";
				while ($armo = pg_fetch_object($arm)) {
					$yy++;
					$opt .= "<option value=\"" . $yy . "\">" . $armo->label . "</option>\n";
					$aml .= ",'" . post2db($armo->label) . "'";
					$temp=post2db($armo->content);
					$temp=str_replace("''", "\'", $temp);
					$amc .= ",'" . $temp . "'";
				}
				echo "\tvar acc_msgs_c = [''" . $amc . "];\n";
				echo "\tvar acc_msgs_l = [''" . $aml . "];\n";
				echo "\tfunction upd_acc_r(opt) {\n";
				echo "\t\tdocument.forms['accept'].decision.value = acc_msgs_c[parseInt(opt.options[opt.selectedIndex].value)];\n";
				echo "\t}\n";
				echo "//-->\n";
				echo "</script>\n";
				if ($opt != "") {
					echo "<select name=dmsgA onChange=\"upd_acc_r(this)\"><option value=0>-- pick a reason --</option>" . $opt . "</select><br>\n";
				}
				echo "<textarea name=decision cols=40 rows=7></textarea><br><input type=submit value=\" REGISTER THIS CHANNEL \">\n";

				echo "</form>\n";

				echo "</td>";
			}
			echo "</tr></table>\n";
			echo "<br>\n";
		}
		if (($admin>=$min_lvl || $nrw_lvl>=1) && ($c_stats==3 || $c_stats==4)) { echo "</form>\n"; }
		echo "<hr size=2 noshade><br>\n";
	}


	if ($channel_id<=0 || $created_ts<=0) {
		echo "<b>NO CHANNEL APPLICATION MATCHING THAT ID</b><br>\n";
		echo "</body></html>\n\n";
		die;
	}

	$res = pg_safe_exec("SELECT COUNT(*) AS count FROM pending WHERE channel_id='$channel_id' AND created_ts='$created_ts'");
	$row = pg_fetch_object($res,0);
	$count = $row->count;
	if ($count==0) {
		echo "<b>NO CHANNEL APPLICATION MATCHING THAT ID</b><br>\n";
		echo "</body></html>\n\n";
		die;
	}


	$ras = pg_safe_exec("SELECT * FROM pending WHERE channel_id='$channel_id' AND created_ts='$created_ts'");
	$ptable = pg_fetch_object($ras,0);
	$c_mode[0]="Incoming";$ccol[0]="#" . $cTheme->main_appst0;
	$c_mode[1]="Pending";$ccol[1]="#" . $cTheme->main_appst1;
	$c_mode[2]="Pending";$ccol[2]="#" . $cTheme->main_appst2;
	$c_mode[3]="Accepted";$ccol[3]="#" . $cTheme->main_appst3;
	$c_mode[4]="Cancelled by applicant";$ccol[4]="#" . $cTheme->main_appst4;
	$c_mode[8]="Ready for review";$ccol[8]="#" . $cTheme->main_appst8;
	$c_mode[9]="Rejected";$ccol[9]="#" . $cTheme->main_appst9;
	$status_msg=$c_mode[$ptable->status];
	$status_color=$ccol[$ptable->status];
	$status=$ptable->status;
	$ackd = $ptable->reg_acknowledged;
	if ($ackd=="" || $ackd=="N") { $ackd=0; } else { $ackd=1; }
	$manager_id = $ptable->manager_id;
	$decision_ts = $ptable->decision_ts;
	$decision = $ptable->decision;
	$channel_description = $ptable->description;

	if (preg_match("/</",$channel_description) || preg_match("/>/",$channel_description)) {
		$channel_description = htmlspecialchars($channel_description);
	}

	$res = pg_safe_exec("SELECT name FROM channels WHERE id='$channel_id'");
	$row = pg_fetch_object($res,0);
	$channel_name = $row->name;

	$posted_on = cs_time($created_ts);

	if ($user_id>0) {
		$res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
		$row = pg_fetch_object($res,0);
		$uu_name = $row->user_name;
	} else {
		$uu_name = "";
	}


	$res = pg_safe_exec("SELECT user_name FROM users WHERE id='$manager_id'");
	$row = pg_fetch_object($res,0);
	$posted_by = $row->user_name;

	echo "<form name=object action=object_app.php method=post onsubmit=\"return check(this);\">\n";
	echo "<table border=0 cellspacing=0 cellpadding=3>\n";

	echo "<tr><td valign=center align=right><u>Channel :</u>&nbsp;</td><td valign=center><font size=+3><b>$channel_name</b></font></td></tr>\n";
	echo "<tr><td valign=center align=right><u>Posted on :</u>&nbsp;</td><td valign=center><font size=+1><b>$posted_on</b></font></td></tr>\n";
	echo "<tr><td valign=center align=right><u>by user :</u>&nbsp;</td><td valign=center><font size=+1>";
	if ($nrw_lvl>0 || $admin>=$min_lvl) {
		echo "<a href=\"users.php?id=$manager_id\"><b>$posted_by</b></a>";
	} else {
		echo "<b>$posted_by</b>";
	}
	echo "</font></td></tr>\n";
	if ($nrw_lvl>0 && $status==3) {
		if ($ackd) { $ackd_txt="YES"; } else { $ackd_txt="NO"; }
		echo "<tr><td valign=center align=right><u>Acknowledged :</u>&nbsp;</td><td valign=center><font size=+1><b>$ackd_txt</b></font></td></tr>\n";
	}
	echo "<tr><td valign=center align=right><u>Current status :</u>&nbsp;</td><td valign=center><font size=+2><b><font color=" . $status_color . ">" . $status_msg . "</font></b></font></td></tr>\n";
	$backlink2 = $backlink;
	if ($backlink2=="list_app.php") { $backlink2 =  gen_server_url() . $_SERVER['REQUEST_URI']; }
	if (REQUIRED_SUPPORTERS>0 && $nrw_lvl>0) {
		echo "<tr><td valign=center align=right><font color=#ff0000><b>*</b></font>&nbsp;<u>Review :</u>&nbsp;</td><td valign=center><font size=+1><b>";
		if ($ptable->reviewed=="Y") {
			$rrev = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $ptable->reviewed_by_id . "'");
			$rev = pg_fetch_object($rrev,0);
			echo "</b><font color=#ffffff>Reviewed by " . $rev->user_name . "</font><b>&nbsp;&nbsp;&nbsp;";
			if ($admin>=800) {
			// || $status==2 || $status==3 || $status==8) {
				echo "<a href=\"clear_review.php?id=$id&retret=" . urlencode($backlink2) . "\">Clear</a>";
			}
		} else {
			if ($status==2 || $status==3 || $status==8) {
				echo "<a href=\"review_app.php?id=$id&retret=" . urlencode($backlink2) . "\">Mark as reviewed now</a>";
			} else {
				if ($status<2) {
					echo "Wait until 'Pending Notification' to review, please.";
				} else {
					echo "** Was Not Reviewed **";
				}
			}
		}
		echo "</b></font></td></tr>\n";
	}
	echo "<tr><td valign=top align=right><u>Description :</u>&nbsp;</td><td valign=center><font size=+1><b>" . str_replace("&lt;br&gt;","<br>",$channel_description) . "</b></font></td></tr>\n";
	$bla = pg_safe_exec("SELECT * FROM supporters WHERE user_id='$user_id' AND channel_id='$channel_id'");
	if (pg_numrows($bla)>0) {
		$supporter=1;
	} else {
		$supporter=0;
	}

	if (REQUIRED_SUPPORTERS>0 && $user_id>0 && ($user_id==$manager_id || $supporter)) {
		echo "<tr><td valign=top align=right><u>Supporters :</u>&nbsp;";
		if (strtolower($posted_by)==strtolower($uu_name)) {
			echo "<br>\n";
			echo "<font color=#" . $cTheme->main_support . "><b>username</b></font> = support&nbsp;<br>\n";
			echo "<font color=#" . $cTheme->main_notyet . "><b>username</b></font> = awaiting confirmation&nbsp;";
		}
		echo "</td><td valign=center><font size=+1>";
		$cnt = pg_safe_exec("SELECT COUNT(*) AS ccount FROM supporters WHERE channel_id='$channel_id'");
		$roo = pg_fetch_object($cnt,0);
		$ccount = $roo->ccount;
		$res = pg_safe_exec("SELECT * FROM supporters WHERE channel_id='$channel_id'");
		for ($x=0;$x<$ccount;$x++) {
			$row = pg_fetch_object($res,$x);
			$tmp = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $row->user_id . "'");
			$tro = pg_fetch_object($tmp,0);
			$tcol = "#" . $cTheme->main_notyet;
			if (strtolower($posted_by)==strtolower($uu_name)) {
				if ($row->support=="Y") { $tcol="#" . $cTheme->main_support; }
				if ($row->support=="N") { $tcol="#" . $cTheme->main_nonsupport; }
			} else { $tcol = "#000000"; }
			if ($user_id!=$row->user_id) { echo "<font color=$tcol>" . $tro->user_name . "</font><br>\n"; } else { echo "<font color=#ffff00>" . $tro->user_name . "</font><br>\n"; }
		}
		echo "</font></td></tr>\n";
	}
	if (in_array($status, array(3, 4, 8, 9)) && $decision_ts>0) {
		$decision_date = cs_time($decision_ts);
		$decision_comment = str_replace("\'","'",$decision);

		echo "<tr><td valign=center align=right><u>Decision date :</u>&nbsp;</td><td valign=center><font size=+1><b>$decision_date</b></font></td></tr>\n";
		echo "<tr><td valign=top align=right><u>Decision comment :</u>&nbsp;</td><td bgcolor=#eeeeee valign=center><font size=+1><b>$decision_comment</b></font></td></tr>\n";
	}

	if (in_array($status, array(0, 1, 2, 8))) {
		$c_id=0;
		if ($user_id>0) {
			$res = pg_safe_exec("SELECT * FROM objections WHERE user_id='$user_id' AND channel_id='$channel_id' AND admin_only='N'");
			if (pg_numrows($res)>0) {
				$row = pg_fetch_object($res,0);
				$c_id = $row->channel_id;
			}
		}

		if ($c_id==0 || $admin>0) {
			echo "<tr bgcolor=#" . $cTheme->table_bgcolor . "><td colspan=2 align=center><b>Add an Objection for that application</b><br><i>note: you can only post <b>ONE</b> objection per channel.</i></td></tr>\n";
		} else {
			if ($user_id>0) { echo "<tr bgcolor=#" . $cTheme->table_bgcolor . "><td colspan=2 align=center><b>You have already posted an objection for that channel</b></i></td></tr><tr><td colspan=2>&nbsp;</td></tr>\n"; }
		}
		if ($user_id > 0) { //logged in = force username in objections.
			if ($c_id==0 || $admin>0) {
				$rrr = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
				$roX = pg_fetch_object($rrr,0);
				$user_name = $roX->user_name;
				if ($err==1) {
					echo "<tr><td valign=center align=left colspan=2><font color=#" . $cTheme->main_warnmsg . "><b>Please check your entry, comment acceptable size is 1-700 chars.</b></font></td></tr>\n";
				}
				if ($err==2) {
					echo "<tr><td valign=center align=left colspan=2><font color=#" . $cTheme->main_warnmsg . "><b>You have already posted one objection, sorry.</b></font></td></tr>\n";
				}
				echo "<tr><td valign=center align=right><u>Your username :</u>&nbsp;</td><td valign=center><font size=+1><b>$user_name</b><input type=hidden name=user_name value=\"$user_name\"></font></td></tr>\n";
				if ($admin>=$min_lvl || $nrw_lvl>0) {
					echo "<tr><td valign=center align=right><font color=#" . $cTheme->main_warnmsg . "><b>*</b></font>&nbsp;<u>Objection type :</u>&nbsp;</td><td valign=center><select name=admin_only><option selected value=\"N\">User objection</option><option value=\"Y\">Admin comment</option></select></td></tr>\n";
				}
				echo "<tr><td valign=top align=right><u>Your comment :</u>&nbsp;</td><td valign=center><font size=+1><textarea name=comment cols=50 rows=7>$comment</textarea></font></td></tr>\n";
				echo "<tr><td align=center colspan=2>";
				if ($admin==0 || $nrw_lvl<1) {
					echo "<input type=hidden name=admin_only value=\"N\">\n";
				}
				echo "<input type=hidden name=channel_id value=$channel_id><input type=submit value=\" SUBMIT THIS OBJECTION";
				if ($admin>0 && $nrw_lvl>0) {
					echo "/ADMIN COMMENT";
				}
				echo " \"><br><br>&nbsp;</td></tr>\n";
			}
		} else {
			echo "<tr><td colspan=2 valign=center align=center>\n";
			echo "<br><br><br>\n";
			echo "You must be <b>logged in</b> in order to post objections<br><br>\n";
			echo "<a href=\"login.php?redir=" . urlencode($_SERVER['REQUEST_URI']) . "\" target=\"_top\">Click here to log in</a>\n";
			echo "<br><br><br>\n";
			echo "</td></tr>\n";
		}

	}

	if (($admin>=$min_lvl || $nrw_lvl>1) && $status==8) {
		echo "<td><td colspan=2>&nbsp;</td></tr>\n";
		echo "<tr bgcolor=#" . $cTheme->table_bgcolor . "><td colspan=2 align=center><b>Add an admin comment for that application</b></td></tr>\n";
		$rrr = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $user_id . "'");
		$roX = pg_fetch_object($rrr,0);
		$user_name = $roX->user_name;
		if ($err==1) {
			echo "<tr><td valign=center align=left colspan=2><font color=#" . $cTheme->main_warnmsg . "><b>Please check your entry, comment acceptable size is 1-700 chars.</b></font></td></tr>\n";
		}
		echo "<tr><td valign=center align=right><u>Your username :</u>&nbsp;</td><td valign=center><font size=+1><b>$user_name</b><input type=hidden name=user_name value=\"$user_name\"></font></td></tr>\n";
		echo "<tr><td valign=center align=right><font color=#" . $cTheme->main_warnmsg . "><b>*</b></font>&nbsp;<u>Objection type :</u>&nbsp;</td><td valign=center><select name=admin_only><option value=\"Y\">Admin comment</option></select></td></tr>\n";
		echo "<tr><td valign=top align=right><u>Your comment :</u>&nbsp;</td><td valign=center><font size=+1><textarea name=comment cols=50 rows=7>$comment</textarea></font></td></tr>\n";
		echo "<tr><td align=center colspan=2>";
		echo "<input type=hidden name=channel_id value=$channel_id><input type=submit value=\" SUBMIT THIS ADMIN COMMENT \"><br><br>&nbsp;</td></tr>\n";
	}

	if ($status<3 || $status==8) {
		if ($status==8) {
			echo "<tr><td colspan=2>&nbsp;</td></tr>\n";
		}
		echo "<tr bgcolor=#" . $cTheme->table_bgcolor . "><td colspan=2 align=center><b>Current Objections</b></td></tr>\n";
		$ocount=0;
		if ($admin>0) { $cnt = pg_safe_exec("SELECT COUNT(*) AS t_ocount FROM objections WHERE channel_id='$channel_id'"); }
		if ($admin==0) { $cnt = pg_safe_exec("SELECT COUNT(*) AS t_ocount FROM objections WHERE channel_id='$channel_id' AND admin_only!='Y'"); }
		$row = pg_fetch_object($cnt,0);
		$t_ocount = $row->t_ocount;
		if ($t_ocount==0) {
			echo "<tr><td colspan=2 align=center><b><i>no objections for this application</i></b></td></tr>\n";
		} else {
			if ($admin==0) { $res = pg_safe_exec("SELECT * FROM objections WHERE channel_id='$channel_id' AND admin_only!='Y' ORDER BY created_ts"); }
			if ($admin>0) { $res = pg_safe_exec("SELECT * FROM objections WHERE channel_id='$channel_id' ORDER BY created_ts"); }
			for ($x=0;$x<$t_ocount;$x++) {
				$row = pg_fetch_object($res,$x);
				$tmp = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $row->user_id ."'");
				$trow = pg_fetch_object($tmp,0);
				if ($trow->user_name!="") {
					echo "<tr><td colspan=2 valign=center align=left><br><br>\n";
					if ($row->admin_only=="Y") { echo "<b>Posted by (<font color=#" . $cTheme->main_warnmsg . "><b>ADMIN COMMENT</b></font>)"; } else { echo "<b>Posted by </font>";	}
					echo ":</b>&nbsp;";
					if ($admin>0) { echo "<a href=\"users.php?id=" . $row->user_id . "\">"; }
					echo $trow->user_name;
					if ($admin>0) { echo "</a>"; }
					echo "<br>\n";
					echo "<b>On :</b>&nbsp;" . cs_time($row->created_ts) . "<br>\n";
					echo "<table width=50% border=0 cellspacing=0 cellpadding=0><tr><td>\n";
					echo "<b>Comment :</b><br>" . $row->comment . "</td></tr></table>\n";
					echo "<br><br></td></tr>\n";
					$ocount++;
				}
			}
			echo "<tr><td colspan=2 bgcolor=#" . $cTheme->table_bgcolor . " align=center>\n";
			if ($ocount>1) { $o_addy="s"; } else { $o_addy=""; }
			echo "<b>$ocount</b> total objection$o_addy";
			if ($admin>0) {
				echo "/admin comment$o_addy";
			}
			echo " for $channel_name.\n";
			echo "</td></tr>\n";
		}
	}
	echo "</table>\n";
?>
<script language="JavaScript1.2">
<!--
function check(f) {
	if (f.user_name.value=="") {
		alert('USERNAME is required.');
		return(false);
	}
	if (f.comment.value=="") {
		alert('You cannot add an objection without a comment.');
		return(false);
	}
	return(true);
}
//-->
</script>
<?
	echo "</form>\n";





?>
</body>
</html>
