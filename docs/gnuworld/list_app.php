<?
	/* $Id: list_app.php,v 1.15 2004/03/07 22:04:31 nighty Exp $ */
	$safety_valve_num_results = 3000;

	$min_lvl=800;
	require("../../php_includes/cmaster.inc");
	std_connect();
	$user_id = isset($_COOKIE["auth"]) ? std_security_chk($_COOKIE["auth"]) : 0;
	$admin = std_admin();
	$cTheme = get_theme_info();
	if ($admin==0) { check_file("regproc.3"); check_file("regproc.1"); }

	if ($admin==0) {
		echo "This page is currently not available, try again later.";
		die;
	}
	echo "<html><head><title></title>\n";
?>
<style type=text/css>
<!--
a:link { font-family: arial,helvetica; color: #<?=$cTheme->main_linkcolor?>;  }
a:visited { font-family: arial,helvetica; color: #<?=$cTheme->main_vlinkcolor?>; }
a:hover { font-family: arial,helvetica; color: #<?=$cTheme->main_linkover?>; }
//-->
</style>
<?
	echo "</head>";
	std_theme_body();
	echo "<b>CHANNEL SERVICE APPLICATIONS</b><br><hr noshade size=2><br>\n";

	$c_mode[666]="All";$ccol[666]="#000000";
	$c_mode[0]="Incoming";$ccol[0]="#" . $cTheme->main_appst0;
	$c_mode[1]="Pending (Traffic Check)";$ccol[1]="#" . $cTheme->main_appst1;
	$c_mode[2]="Pending (Notification)";$ccol[2]="#" . $cTheme->main_appst2;
	$c_mode[3]="Accepted";$ccol[3]="#" . $cTheme->main_appst3;
	$c_mode[4]="Cancelled";$ccol[4]="#" . $cTheme->main_appst4;
	$c_mode[8]="Ready for review";$ccol[8]="#" . $cTheme->main_appst8;
	$c_mode[9]="Rejected";$ccol[9]="#" . $cTheme->main_appst9;


	echo "<form name=search_app action=list_app.php method=get onsubmit=\"return search_check(this);\">\n";
	echo "<table border=0 cellspacing=0 cellpadding=0><tr><td>\n";
	echo "<b>Search Channel Applications</b> :<br>\n";
	echo "Search in <select name=type>\n";
	if ($type=="") { $type=-2; }
	$nofs=0;
	switch ($type) {
		case 0:
			$o1=" selected";
			$xstart=0;$xend=0;
			break;
		case 1:
			$o2=" selected";
			$xstart=1;$xend=1;
			break;
		case 2:
			$o3=" selected";
			$xstart=2;$xend=2;
			break;
		case 3:
			$o4=" selected";
			$xstart=3;$xend=3;
			break;
		case 4:
			$o5=" selected";
			$xstart=4;$xend=4;
			break;
		case 5:
			$o6=" selected";
			$xstart=9;$xend=9;
			break;
		case 6:
			$o7=" selected";
			$xstart=8;$xend=8;
			break;
		default:
			$o0=" selected";
			$nofs=1;
			break;
	}
	echo "<option$o0 value=-1>- all status types -</option>\n";
	echo "<option$o1 value=0>Incoming Applications</option>\n";
	echo "<option$o2 value=1>Pending Applications (Traffic Check Only)</option>\n";
	echo "<option$o3 value=2>Pending Applications (Notification Only)</option>\n";
	echo "<option$o4 value=3>Accepted Applications</option>\n";
	echo "<option$o5 value=4>Cancelled Applications</option>\n";
	echo "<option$o6 value=5>Rejected Applications</option>\n";
	echo "<option$o7 value=6>Ready for review Applications</option>\n";

	if ($q=="") { $qq="#"; } else { $qq="$q"; }
	if ($stype=="") { $stype=-1; }
	if ($stype==0) { $st0 = " selected"; }
	if ($stype==1) { $st1 = " selected"; }
	echo "</select> with channel name <select name=stype><option$st1 value=1>starting with</option><option$st0 value=0>equals to</option></select>&nbsp;<input type=text name=q value=\"$qq\" size=20 maxlength=255>\n";
	echo "&nbsp;&nbsp;<input type=submit value=\" Go! \">\n";

	// accepted, cancelled and rejected applications limit display by date..
	$nb_days = 5;


	// webaxs team check
	if (acl(XWEBAXS_2) || acl(XWEBAXS_3)) {
		if ($force_fs=="FULL") { $chkfs = " checked"; } else { $chkfs = " checked"; } // always checked by default
		if ($nofs) { $chkfs = ""; }
		if ($force_all=="OK") { $chkd = " checked"; } else { $chkd = ""; }
		if ($force_rev=="1") { $chkdr = " checked"; } else { $chkdr = ""; }
		echo "<br><font color=#" . $cTheme->main_warnmsg . "><b>*</b></font>&nbsp;<input type=checkbox$chkfs value=\"FULL\" id=\"force_fs\" name=force_fs>&nbsp;<label for=\"force_fs\">Full Search Mode. (disabled when looking up on all applications)</label>";
		echo "<br><font color=#" . $cTheme->main_warnmsg . "><b>*</b></font>&nbsp;<input type=checkbox$chkd value=\"OK\" id=\"force_all\" name=force_all>&nbsp;<label for=\"force_all\">Force display of all channels (even older than $nb_days days in 'Accepted', 'Cancelled' or 'Rejected').</label>";
		echo "<br><font color=#" . $cTheme->main_warnmsg . "><b>*</b></font>&nbsp;<input type=checkbox$chkdr value=\"1\" id=\"force_rev\" name=force_rev>&nbsp;<label for=\"force_rev\">List only application that have not been reviewed yet.</label>";
	} else {
		$force_fs="";
		$force_all="";
		$force_rev="0";
	}
	echo "\n";

	if ($admin>=800) {
		echo "<br><br><a href=\"default_msgs.php?type=review\"><b>Edit common replies for ACCEPT / REJECT</b></a> (800+)\n";
	}

?>
<script language="JavaScript1.2">
<!--
function search_check(f) {
	if (f.q.value=="") {
		alert('You must supply a channel name to search.');
		return(false);
	}
	return(true);
}
//-->
</script>
<?
	if ($stype=="" || !isset($stype) || $type=="" || !isset($type) || $q=="" || !isset($q)) {
		echo "</td></tr></table>\n";
		echo "<br><hr size=2 noshade></form>\n";
		echo "</body></html>\n";
		die;
	}

	if (preg_match("/\x20/",$q) || preg_match("/\x07/",$q) || preg_match("/,/",$q)) {
		echo "</td></tr></table>\n";
		echo "<br><hr size=2 noshade></form>\n";
		echo "<font color=#" . $cTheme->main_warnmsg . "><b>Invalid chars in search field</b></font>\n";
		echo "</body></html>\n";
		die;
	}

	if ($type==-1) { $force_fs = ""; }

	if (!preg_match("/^#./",$q) && ($force_fs!="FULL" || $admin==0 || $type==-1)) {
		echo "</td></tr></table>\n";
		echo "<br><hr size=2 noshade></form>\n";
		echo "<font color=#" . $cTheme->main_warnmsg . "><b>Channel name must start with a # followed by at least a letter as a search criteria</b></font>\n";
		echo "</body></html>\n";
		die;
	}


	echo "</td></tr></table>\n";
	echo "<br><hr size=2 noshade></form>\n";

	$uplim=25; // max # of records to display per page.
	$o_uplim=$uplim; // keep track of up_limit.


	if ($page!=0 && $admin==0) { $page=0; }

	$globalcount=0;

	// smode defines whether or not the search is wildcarded.
	if ($stype==0) { $smode=""; }
	if ($stype==1) { $smode="%"; }

	$q = strtolower($q); // lower case channel query.
	if ($page=="") { $page=0; }

	$chekrev="";
	if ($force_rev=="1" && $admin>0) { // force only display of 'marked as reviewed' applications.
		$chekrev="(pending.reviewed='' OR pending.reviewed='N' OR pending.reviewed IS NULL) AND ";
	}


// start applications list...
	$x = $xstart;

	$oldest_ts = "(date_part('epoch', CURRENT_TIMESTAMP)::int-86400*$nb_days)";
	if (acl(XWEBAXS_2) || acl(XWEBAXS_3)) {
		if ($force_all=="OK") {
			$oldest_ts="0";
		}
	}

	$count_query  = "";
	$count_query .= "SELECT COUNT(channels.id) AS count FROM ";
	$count_query .= "pending,channels WHERE ";
	if ($type > -1) {
		$count_query .= "pending.status='" . $x . "' AND ";
	}
	$count_query .= $chekrev . "channels.id=pending.channel_id ";
	if ($type==-1) {
		// apply time limit
		$count_query .= "AND (pending.decision_ts=0 OR pending.decision_ts>=" . $oldest_ts . ") ";
	}
	if ($type>-1) {
		$count_query .= "AND ";
		if ($x==3 || $x==4 || $x==9) { // if accepted, cancelled or rejected : apply time limit.
			$count_query .= "pending.decision_ts>=" . $oldest_ts . " AND ";
		}
		if ($x!=3) { // if application is not in 'accepted' status.
			$count_query .= "channels.registered_ts=0 ";
		} else { // if application is accepted, channel *should* be registered.
			$count_query .= "channels.registered_ts>0 ";
		}
	}
	if ("$q$smode"!="#%") {
		$count_query .= "AND ";
		$count_query .= "lower(channels.name) LIKE '$q$smode'";
	}
//	echo $count_query;
	$res = pg_safe_exec($count_query);
	$row = pg_fetch_object($res,0);
	$total_app_count = $row->count; // total applications matching.

	if ($total_app_count>$safety_valve_num_results) {

		echo "<br><br><h2>Too many results (" . $total_app_count . ")</h2><br>\n";
		echo "<h3>Restrict you query</h3>\n";
		echo "<br><a href=\"javascript:history.go(-1);\">go back</a><br>\n";
		echo "</body></html>\n\n";
		die;

	}

	if ($admin==0) { $pwhat = "per search"; } else { $pwhat = "per page"; }
	$xy = $x;
	if ($type == -1) { $xy = 666; }
	echo "<font color=$ccol[$xy]><b>$c_mode[$xy] applications</b>:</font>";
	if ($total_app_count>1) { $a_addy = "s"; } else { $a_addy = ""; }
	echo "&nbsp;&nbsp;&nbsp;<i><b>$o_uplim</b> channels maximum $pwhat, <b>$total_app_count</b> total application$a_addy.</i><br>\n";

	if (($x==3 || $x==4 || $x==9) && ($force_all!="OK")) { echo " (younger than $nb_days days)"; }
	if (($x==3 || $x==4 || $x==9) && ($force_all=="OK")) { echo " <b>ADMIN VIEW ALL</b>"; }
	echo "<br><hr noshade size=1>\n";
	$total_pages = $total_app_count / $uplim;
	if (preg_match("/./",$total_pages)) {
		$tmp = explode(".",$total_pages);
		$ent = $tmp[0];
		$dec = $tmp[1];
		if ($dec>0) {
			$total_pages = $ent;
		}
	} else {
		$total_pages--;
	}

	if (($admin>0) && ((((($page)*$uplim)+1)<=$total_app_count))) {


		$pagechgactive=0;
		if ($page>0) {
			$pageprev = $page-1;
			$pagechgactive=1;
			echo "<a href=\"list_app.php?type=$type&stype=$stype&q=" . urlencode($q) . "&force_all=$force_all&force_fs=$force_fs&force_rev=$force_rev&page=$pageprev\">&lt; prev page ($pageprev)</a>\n";
		} else { echo "<font color=#" . $cTheme->main_textlight . ">&lt; prev page (*)</font>"; }
		echo "&nbsp;&nbsp;";
		echo "<b>$page</b>/$total_pages";
		echo "&nbsp;&nbsp;";
		echo "\n\n<!-- uplim=$uplim, ((((\$page+1)*\$uplim)+1)<=\$total_app_count) : (" . ((($page+1)*$uplim)+1) . "<=" . $total_app_count .") //-->\n\n";
		if ((((($page+1)*$uplim)+1)<=$total_app_count)) {
			$pagenext = $page+1;
			$pagechgactive=1;
			echo "<a href=\"list_app.php?type=$type&stype=$stype&q=" . urlencode($q) . "&force_all=$force_all&force_fs=$force_fs&force_rev=$force_rev&page=$pagenext\">($pagenext) next page &gt;</a><br>\n";
		} else {
			$pagenext = $page-1;
			echo "<font color=#" . $cTheme->main_textlight . ">(*) next page &gt;</font><br>\n";
		}
		if ($pagechgactive) {
			echo "<form method=get>\n";
			echo "<input type=hidden name=type value=$type>\n";
			echo "<input type=hidden name=stype value=$stype>\n";
			echo "<input type=hidden name=q value=\"" . $q . "\">\n";
			echo "<input type=hidden name=force_all value=$force_all>\n";
			echo "<input type=hidden name=force_fs value=$force_fs>\n";
			echo "<input type=hidden name=force_rev value=$force_rev>\n";
			echo "Go to page # <input type=text maxlength=5 name=page size=3 value=$pagenext>&nbsp;<input type=submit value=Go!></form>\n";
		} else {
			echo "<br>\n";
		}
	}

	if ($total_app_count<$uplim) { $loopmax = $total_app_count; } else { $loopmax = $uplim; }

	if ($page>0) {
		$uup = $page*$uplim;
		$uplim = "$uplim OFFSET $uup";
	}

	$data_query  = "";
	$data_query .= "SELECT pending.created_ts AS p_created_ts,users.user_name,users.id AS users_id,* FROM ";
	$data_query .= "pending,channels,users WHERE ";
	if ($type>-1) {
		$data_query .= "pending.status='" . $x . "' AND ";
	}
	$data_query .= $chekrev . "channels.id=pending.channel_id ";
	$data_query .= "AND ";
	if ($type>-1) {
		if ($x==3 || $x==4 || $x==9) { // if accepted, cancelled or rejected : apply time limit.
			$data_query .= "pending.decision_ts>=" . $oldest_ts . " AND ";
		}
	} else {
		$data_query .= "(pending.decision_ts=0 OR pending.decision_ts>=" . $oldest_ts . ") AND ";
	}
	$data_query .= " users.id=pending.manager_id ";
	if ($type>-1) {
		$data_query .= "AND ";
		if ($x!=3) { // if application is not in 'accepted' status.
			$data_query .= "channels.registered_ts=0 ";
		} else { // if application is accepted, channel *should* be registered.
			$data_query .= "channels.registered_ts>0 ";
		}
	}
	if ("$q$smode"!="#%") {
		$data_query .= "AND lower(channels.name) LIKE '$q$smode' ";
	}
	$data_query .= "ORDER BY pending.";
	if ($x==2) { $data_query .= "check_start_ts "; } else {
		if ($x==3 || $x==9 || $x==4) {
			$data_query .="decision_ts ";
		} else {
			$data_query .="created_ts ";
		}
	}
	$data_query .= " LIMIT $uplim";

	$ref_ts = time(); // reference TIMESTAMP.

	//echo $data_query . "<br>";

	$res = pg_safe_exec($data_query);
	//echo pg_numrows($res) . "<br>";


	echo "<table border=0 cellspacing=3 cellpadding=2 width=100%>\n";
	if ($loopmax>0) {
		echo "<tr bgcolor=#" . $cTheme->table_bgcolor . "><td><b>Application number</b></td><td><b>Channel name</b></td>";
		if ($type==-1) { echo "<td><b>Status</b></td>"; }
		echo "<td><b>Posted on</b></td>";
		if ($x==3 || $x==4 || $x==9) { echo "<td><b>Decision date</b></td>"; }
		if ($x==2) { echo "<td><b>Notification since</b></td>"; }
		echo "</tr>\n";
	}

	$localcount=0;
	if (pg_numrows($res)>0) {
		for ($y=0;$y<pg_numrows($res);$y++) {
			$row = pg_fetch_object($res,$y);
			//echo "[" . $row->p_created_ts . "] [" . $row->channel_id . "]<br>\n";
			$test = ($row->p_created_ts!="" && $row->channel_id!="");

			if ($test) {
				$localcount++;
				$globalcount++;
				echo "<tr>\n";
				echo "<td width=20%>\n";
				echo "<a href=\"view_app.php?id=" . $row->p_created_ts . "-" . $row->channel_id . "";
				echo "\">";
				echo $row->p_created_ts . "-" . $row->channel_id;
				echo "</a>";
				if (($x==3 || $x==4 || $x==9) && ($row->decision_ts<($ref_ts-86400*$nb_days))) {
					echo "&nbsp;<font color=#" . $cTheme->main_textlight . ">(old)</font>";
				}
				if ($row->reviewed=="Y") {
					echo "&nbsp;<font color=#" . $cTheme->table_bgcolor . ">(reviewed)</font>";
				}
				echo "</td><td>";
				echo $row->name;
				echo "</td>";
				if ($type==-1) {
					echo "<td>" . $c_mode[$row->status] . "</td>\n";
				}
				echo "<td>\n";
				echo cs_time($row->p_created_ts);
				//$tmp = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $row->manager_id ."'");
				//$tro = pg_fetch_object($tmp,0);
				echo " by <b>" . $row->user_name . "</b>";
				echo "</td>";
				if ($x==3 || $x==4 || $x==9) { echo "<td>" . cs_time($row->decision_ts) . "</td>"; }
				if ($x==2) { echo "<td>" . cs_time($row->check_start_ts) . "</td>"; }
				echo "</tr>\n";
			}
		}
	}
	echo "</table>\n";
	if ($localcount==0) {
		if ($page>0) {
			echo "<b><i>no more applications found in '$c_mode[$xy]'.</i></b><br>\n";
		} else {
			echo "<b><i>no applications found for '$c_mode[$xy]'.</i></b><br>\n";
		}
		echo "<br><a href=\"javascript:history.go(-1);\">go back</a><br>\n";
	} else {

		if ($admin>0) {
			$pagechgactive=0;
			echo "<hr width=100% noshade size=1>\n";
			if ($page>0) {
				$pageprev = $page-1;
				$pagechgactive=1;
				echo "<br><a href=\"list_app.php?type=$type&stype=$stype&q=" . urlencode($q) . "&force_all=$force_all&force_fs=$force_fs&force_rev=$force_rev&page=$pageprev\">&lt; prev page ($pageprev)</a>\n";
			} else { echo "<br><font color=#" . $cTheme->main_textlight . ">&lt; prev page (*)</font>"; }
			echo "&nbsp;&nbsp;";
			echo "<b>$page</b>/$total_pages";
			echo "&nbsp;&nbsp;";
			echo "\n\n<!-- localcount=$localcount, x=$x, uplim=$o_uplim //-->\n\n";
			if ((((($page+1)*$o_uplim)+1)<=$total_app_count)) {
				$pagenext = $page+1;
				$pagechgactive=1;
				echo "<a href=\"list_app.php?type=$type&stype=$stype&q=" . urlencode($q) . "&force_all=$force_all&force_fs=$force_fs&force_rev=$force_rev&page=$pagenext\">($pagenext) next page &gt;</a><br>\n";
			} else {
				$pagenext = $page-1;
				echo "<font color=#" . $cTheme->main_textlight . ">(*) next page &gt;</font><br>\n";
			}
			if ($pagechgactive) {
				echo "<form method=get>\n";
				echo "<input type=hidden name=type value=$type>\n";
				echo "<input type=hidden name=stype value=$stype>\n";
				echo "<input type=hidden name=q value=\"" . $q . "\">\n";
				echo "<input type=hidden name=force_all value=$force_all>\n";
				echo "<input type=hidden name=force_fs value=$force_fs>\n";
				echo "<input type=hidden name=force_rev value=$force_rev>\n";
				echo "Go to page # <input type=text maxlength=5 name=page size=3 value=$pagenext>&nbsp;<input type=submit value=Go!></form>\n";
			}
		}
	}
	$prevcount=$localcount;

//end of what could be a loop with start of app list for multiples. ;)

?>
</body>
</html>
