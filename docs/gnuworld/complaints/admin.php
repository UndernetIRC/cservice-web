<?
define(MAX_PER_PAGE,20);
/* $Id: admin.php,v 1.17 2005/02/03 11:57:41 nighty Exp $ */
require("../../../php_includes/cmaster.inc");
if (ENABLE_COMPLAINTS_MODULE != 1) { die("This option is disabled. Please contact the server administrator."); }
std_init();
if ($_COOKIE["COMPLAINTSPARAM"] != "" && preg_match("/^[0123]¤[0-9]+¤[01]¤[01]¤[01]¤[01]¤[01]¤[01]¤[01]$/",$_COOKIE["COMPLAINTSPARAM"])) {
	setCookie("COMPLAINTSPARAM",$_COOKIE["COMPLAINTSPARAM"],time()+(86400*30),"/"); // refresh cookie expiration
}
$cTheme = get_theme_info();
std_theme_styles(1);
echo "<style type=text/css>\n";
echo "<!--\n";
echo "td { font-size: 10pt; }\n";
echo "//-->\n";
echo "</style>\n";
std_theme_body();
if (!acl(XCOMPLAINTS_ADM_REPLY) && !acl(XCOMPLAINTS_ADM_READ)) {
	die("Your level is too low to access this page</body></html>");
}

echo "<h2>Complaint Manager";
if (COMPLAINTS_DO_FOLLOWUP) { echo "<h4>(Follow Up Active)</h4>"; }
echo "</h2>\n";
echo "<hr width=100% size=1 noshade>";

if (($_GET["view"]+0)<=0) {
	unset($chkstatus);
	if (($_GET["status"]+0)<=0) { $chkstatus = 1; } else { $chkstatus = (int)$_GET["status"]; }
	if ($chkstatus>4 && $chkstatus!=31337) { $chkstatus = 1; }
	$page_thing = "";
	$da_page = $_GET["page"]+0;
	unset($da_filt); unset($da_ct);

	if ($_GET["filter"] == "" || !isset($_GET["filter"])) {
		if ($_COOKIE["COMPLAINTSPARAM"] != "" && preg_match("/^[0123]¤[0-9]+¤[01]¤[01]¤[01]¤[01]¤[01]¤[01]¤[01]$/",$_COOKIE["COMPLAINTSPARAM"])) {
			$valz = explode("¤",$_COOKIE["COMPLAINTSPARAM"]);
			$da_filt = $valz[0]; // COOKIE DEFAULT IF PRESENT ...
		} else {
			$da_filt = 3; // all filters (1+2) by default if none set
		}
	} else {
		$da_filt = ($_GET["filter"]+0);
	}

	if ($_GET["ct"] == "" || !isset($_GET["ct"])) {
		if ($_COOKIE["COMPLAINTSPARAM"] != "" && preg_match("/^[0123]¤[0-9]+¤[01]¤[01]¤[01]¤[01]¤[01]¤[01]¤[01]$/",$_COOKIE["COMPLAINTSPARAM"])) {
			$valz = explode("¤",$_COOKIE["COMPLAINTSPARAM"]);
			$da_ct = $valz[1]; // COOKIE DEFAULT IF PRESENT ...
		} else {
			$da_ct = 0; // all complaint types
		}
	} else {
		$da_ct = ($_GET["ct"]+0);
	}

	//echo $_COOKIE["COMPLAINTSPARAM"] . "<br><br>\n";

	$offset = $da_page*MAX_PER_PAGE;
	$page_thing = " LIMIT " . MAX_PER_PAGE . " OFFSET " . $offset;

	$ctchk = "";
	if ($da_ct>0) {
		$ctchk = "complaint_type='" . $da_ct . "' AND ";
	}

	$ord_add = "";
	if ($chkstatus == 3 || $chkstatus == 4) { $ord_add = " DESC"; }

	if ($chkstatus == 31337) {
		if ($ctchk!="") { $ctchk2 = "complaints." . $ctchk; } else { $ctchk2 = ""; }
		$main_query = "SELECT * FROM complaints,complaints_reference WHERE " . $ctchk2 . "complaints.id=complaints_reference.complaints_ref AND complaints.status!=4 AND (complaints.current_owner='" . (int)$user_id . "' OR complaints.current_owner=0) AND complaints_reference.referenced_to='" . (int)$user_id . "' ORDER BY complaints_reference.reference_ts";
	} else {
		$main_query = "SELECT * FROM complaints WHERE " . $ctchk . "status='" . $chkstatus . "' ORDER BY created_ts" . $ord_add;
		if ($da_filt & 2) { // restrict to only those originally replied by you or owned by you
			$main_query = "SELECT * FROM complaints WHERE status='" . $chkstatus . "' AND " . $ctchk . "(reviewed_by_id=0 OR reviewed_by_id='" . (int)$user_id . "' OR current_owner='" . (int)$user_id . "') ORDER BY created_ts" . $ord_add;
		}
	}
	$rcount = pg_safe_exec(str_replace("SELECT * FROM","SELECT id FROM",$main_query));
	$r = pg_safe_exec($main_query . $page_thing);
	$nc = pg_numrows($rcount);

	$rc31337 = pg_safe_exec("SELECT COUNT(complaints_reference.referenced_to) AS count FROM complaints_reference,complaints WHERE (complaints.current_owner='" . (int)$user_id . "' OR complaints.current_owner=0) AND complaints_reference.referenced_to='" . (int)$user_id . "' AND complaints_reference.complaints_ref=complaints.id"); $oc31337 = pg_fetch_object($rc31337);
	$rc31338 = pg_safe_exec("SELECT COUNT(complaints_reference.referenced_to) AS count FROM complaints_reference,complaints WHERE (complaints.current_owner='" . (int)$user_id . "' OR complaints.current_owner=0) AND complaints_reference.referenced_to='" . (int)$user_id . "' AND complaints_reference.complaints_ref=complaints.id AND complaints_reference.is_new='1'"); $oc31338 = pg_fetch_object($rc31338);
	$rc1 = pg_safe_exec("SELECT COUNT(id) AS count FROM complaints WHERE status=1"); $oc1 = pg_fetch_object($rc1);
	if (COMPLAINTS_DO_FOLLOWUP) {
		$rc2 = pg_safe_exec("SELECT COUNT(id) AS count FROM complaints WHERE status=2 AND reviewed_by_id='" . (int)$user_id . "'"); $oc2 = pg_fetch_object($rc2);
	} else {
		$rc2 = pg_safe_exec("SELECT COUNT(id) AS count FROM complaints WHERE status=2"); $oc2 = pg_fetch_object($rc2);
	}
	$rc3 = pg_safe_exec("SELECT COUNT(id) AS count FROM complaints WHERE status=3"); $oc3 = pg_fetch_object($rc3);
	$rc4 = pg_safe_exec("SELECT COUNT(id) AS count FROM complaints WHERE status=4"); $oc4 = pg_fetch_object($rc4);
	if (COMPLAINTS_DO_FOLLOWUP) {
		$rcFU = pg_safe_exec("SELECT COUNT(id) AS count FROM complaints WHERE status=2"); $ocFU = pg_fetch_object($rcFU);
	}

	if ($chkstatus!=31337) { echo "<a href=\"admin.php?status=31337&filter=" . $da_filt . "&page=0&ct=" . $da_ct . "\">"; }
	echo "<b>" . $oc31337->count . "</b> ticket";
	if ($oc31337->count>1) { echo "s"; }
	echo " referenced to you total";
	echo " (<b>" . $oc31338->count . "</b> new)";
	if ($chkstatus!=31337) { echo "</a>"; }
	echo "<br>\n";

	if ($chkstatus!=1) { echo "<a href=\"admin.php?status=1&filter=" . $da_filt . "&page=0&ct=" . $da_ct  . "\">"; }
	echo "<b>" . $oc1->count . "</b> ticket";
	if ($oc1->count>1) { echo "s"; }
	echo " incoming total";
	if ($chkstatus!=1) { echo "</a>"; }
	echo "<br>\n";

	if (COMPLAINTS_DO_FOLLOWUP) { $add = "by you"; } else { $add = "total"; }
	if ($chkstatus!=2) { echo "<a href=\"admin.php?status=2&filter=" . $da_filt . "&page=0&ct=" . $da_ct  . "\">"; }
	echo "<b>" . $oc2->count . "</b> ticket";
	if ($oc2->count>1) { echo "s"; }
	echo " being processed " . $add;
	if ($chkstatus!=2) { echo "</a>"; }
	echo "<br>\n";
	if (COMPLAINTS_DO_FOLLOWUP) {
		echo "<b>" . $ocFU->count . "</b> ticket";
		if ($ocFU->count>1) { echo "s"; }
		echo " being processed total";
		echo "<br>\n";
	}
	if ($chkstatus!=3) { echo "<a href=\"admin.php?status=3&filter=" . $da_filt . "&page=0&ct=" . $da_ct  . "\">"; }
	echo "<b>" . $oc3->count . "</b> ticket";
	if ($oc3->count>1) { echo "s"; }
	echo " resolved total";
	if ($chkstatus!=3) { echo "</a>"; }
	echo "<br>\n";
	if ($chkstatus!=4) { echo "<a href=\"admin.php?status=4&filter=" . $da_filt . "&page=0&ct=" . $da_ct  . "\">"; }
	echo "<b>" . $oc4->count . "</b> ticket";
	if ($oc4->count>1) { echo "s"; }
	echo " abandonned total";
	if ($chkstatus!=4) { echo "</a>"; }
	echo "<br>\n";
	echo "<br>";

	echo "<form name=dummy method=POST action=search.php onsubmit=\"return check_search(this);\">";

	echo "<script language=\"JavaScript1.2\">\n";
	echo "<!--\n";
	echo "function check_search(f) {\n";
	echo "\tif (f.q.value=='') {\n";
	echo "\t\talert('Please fill in the search criteria !');\n";
	echo "\t\treturn false;\n";
	echo "\t}\n";
	echo "\tif (!f.tnum.checked && !f.ereply.checked && !f.fip.checked && !f.thread.checked && !f.ocomp.checked && !f.ochan.checked && !f.ouser.checked) {\n";
	echo "\t\talert('Please check at least one field to search on !');\n";
	echo "\t\treturn false;\n";
	echo "\t}\n";
	echo "\treturn true;\n";
	echo "}\n";
	echo "function update_filter(obj) {\n";
	echo "\tvar curr_filter = parseInt(document.forms[0].filter.value);\n";
	echo "\tvar to_toggle_value = parseInt(obj.value);\n";
	echo "\tif ( curr_filter & to_toggle_value ) { // this filter is active, disable it!\n";
	echo "\t\tcurr_filter = curr_filter &~ to_toggle_value;\n";
	echo "\t} else { // this filter is inactive, enable it!\n";
	echo "\t\tcurr_filter = curr_filter | to_toggle_value;\n";
	echo "\t}\n";
	echo "\tvar url = 'admin.php?status=" . $_GET["status"] . "&filter='+curr_filter+'&page=0&ct=" . $da_ct  . "';\n";
	echo "\tlocation.href = url;\n";
	echo "}\n";
	echo "function update_filt_elt(obj) {\n";
	echo "\tvar new_ct = obj.options[obj.selectedIndex].value;\n";
	echo "\tvar url = 'admin.php?status=" . $_GET["status"] . "&filter=" . $da_filt . "&page=0&ct='+new_ct;\n";
	echo "\tlocation.href = url;\n";
	echo "}\n";
	echo "function save_params() {\n";
	echo "\tvar morep = '';\n";
	echo "\tvar frm = document.forms[0];\n";
	echo "\tif (frm.tnum.checked) { morep = morep + '¤1'; } else { morep = morep + '¤0'; }\n";
	echo "\tif (frm.ereply.checked) { morep = morep + '¤1'; } else { morep = morep + '¤0'; }\n";
	echo "\tif (frm.fip.checked) { morep = morep + '¤1'; } else { morep = morep + '¤0'; }\n";
	echo "\tif (frm.thread.checked) { morep = morep + '¤1'; } else { morep = morep + '¤0'; }\n";
	echo "\tif (frm.ocomp.checked) { morep = morep + '¤1'; } else { morep = morep + '¤0'; }\n";
	echo "\tif (frm.ochan.checked) { morep = morep + '¤1'; } else { morep = morep + '¤0'; }\n";
	echo "\tif (frm.ouser.checked) { morep = morep + '¤1'; } else { morep = morep + '¤0'; }\n";
	echo "\tvar url = 'save_params.php?P=" . $da_filt . "¤" . $da_ct  . "'+morep;\n";
	echo "\tlocation.href = url;\n";
	echo "}\n";
	echo "function drop_params() {\n";
	echo "\tvar url = 'save_params.php?clear=1';\n";
	echo "\tlocation.href = url;\n";
	echo "}\n";
	echo "//-->\n";
	echo "</script>\n";

	if ($chkstatus != 31337) {
		echo "<input onClick=\"update_filter(this);\" type=checkbox ";
		if ((int)$da_filt & 1) { echo "checked "; }
		echo "name=filter0 value=1> Only show tickets waiting admin action.<br>\n";

		if (!COMPLAINTS_DO_FOLLOWUP) {
			echo "<input onClick=\"update_filter(this);\" type=checkbox ";
			if ((int)$da_filt & 2) { echo "checked "; }
			echo "name=filter1 value=2> Only show tickets incoming and/or that you first replied and/or owned by you.<br>\n";
		}
	}
	echo "Show <select name=ct onChange=\"update_filt_elt(this);\"><option value=0>- all types of complaints -</option>\n";
	for ($x=1;$x<=MAX_COMPLAINT_TYPE;$x++) {
		echo "<option ";
		if ($da_ct  == $x) { echo "selected "; }
		echo "value=" . $x . ">" . $cpt_name[$x] . "</option>\n";
	}
	echo "<option ";
	if ($da_ct == 99) { echo "selected "; }
	echo "value=99>other...</option>\n";
	echo "</select>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<input type=button value=\"Remember params\" onClick=\"save_params()\">";
	if ($_COOKIE["COMPLAINTSPARAM"] != "" && preg_match("/^[0123]¤[0-9]+¤[01]¤[01]¤[01]¤[01]¤[01]¤[01]¤[01]$/",$_COOKIE["COMPLAINTSPARAM"])) {
		echo "&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<input type=button value=\"Forget params\" onClick=\"drop_params()\">";
	}
	echo "<br>\n";

	echo "Full search : <input type=text name=q size=40 maxlength=255><br>Search in : <select name=qt><option value=0>- all types of complaints -</option>";
	for ($x=1;$x<=MAX_COMPLAINT_TYPE;$x++) {
		echo "<option value=" . $x . ">" . $cpt_name[$x] . "</option>\n";
	}
	echo "<option value=99>other...</option>\n";
	echo "</select> <input type=submit value=Search!> (Searches in all status. Wildcards <b>*</b> and <b>?</b> are allowed)";
	echo "<br>";
	if ($_COOKIE["COMPLAINTSPARAM"] != "" && preg_match("/^[0123]¤[0-9]+¤[01]¤[01]¤[01]¤[01]¤[01]¤[01]¤[01]$/",$_COOKIE["COMPLAINTSPARAM"])) {
		$valz = explode("¤",$_COOKIE["COMPLAINTSPARAM"]);
		if ($valz[2] == 1) { $tnum_c = " checked"; } else { $tnum_c = ""; }
		if ($valz[3] == 1) { $ereply_c = " checked"; } else { $ereply_c = ""; }
		if ($valz[4] == 1) { $fip_c = " checked"; } else { $fip_c = ""; }
		if ($valz[5] == 1) { $thread_c = " checked"; } else { $thread_c = ""; }
		if ($valz[6] == 1) { $ocomp_c = " checked"; } else { $ocomp_c = ""; }
		if ($valz[7] == 1) { $ochan_c = " checked"; } else { $ochan_c = ""; }
		if ($valz[8] == 1) { $ouser_c = " checked"; } else { $ouser_c = ""; }
	} else {
		$tnum_c = " checked";
		$ereply_c = "";
		$fip_c = "";
		$thread_c = " checked";
		$ocomp_c = " checked";
		$ochan_c = " checked";
		$ouser_c = " checked";
	}

	echo "Search on : <input type=checkbox value=1 id=tnum name=tnum" . $tnum_c . "> <label for=tnum>Ticket number</label>, ";
	echo "<input type=checkbox value=1 id=ereply name=ereply" . $ereply_c . "> <label for=ereply>Email for reply</label>, ";
	echo "<input type=checkbox value=1 id=fip name=fip" . $fip_c . "> <label for=fip>From IP</label>, ";
	echo "<input type=checkbox value=1 id=thread name=thread" . $thread_c . "> <label for=thread><i><b>Ticket events</b></i></label>, ";
	echo "<input type=checkbox value=1 id=ocomp name=ocomp" . $ocomp_c . "> <label for=ocomp><i><b>Original complaint</b></i></label>. ";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "( <i><b>bold italic</b></i> : auto leading/trailing wildcards. )";
	echo "<br>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<input type=checkbox value=1 id=ochan name=ochan" . $ochan_c . "> <label for=ochan>Offending/Purged channel(s)</label>, ";
	echo "<input type=checkbox value=1 id=ouser name=ouser" . $ouser_c . "> <label for=ouser>Authenticated/Suspended Username(s)</label>. ";
	echo "<br>";
	echo "<input type=hidden name=filter value=" . ($da_filt+0) . ">\n";
	if ($admin>=800) {
		echo "<br><br><a href=\"../default_msgs.php?type=complaints\"><b>Edit Common Complaint Replies</b></a> (800+)<br>";
	}
	echo "<br><br>\n";
	echo "<b>" . MAX_PER_PAGE . "</b> records max. per page.<br>";

	if ($nc>0) {

		$nbpages = (int)($nc / MAX_PER_PAGE);
		if ($nc % MAX_PER_PAGE > 0) { $nbpages++; }
		if ($nbpages>1) {
			echo "<u>Page :</u> ";

			if ($da_page == 0) { echo "<font color=#4c4c4c><b>&lt;&nbsp;previous</b></font>&nbsp;&nbsp;"; } else {
				echo "<a href=\"admin.php?status=" . $_GET["status"] . "&filter=" . $da_filt . "&page=" . ($da_page-1) . "&ct=" . $da_ct  . "\"><b>&lt;&nbsp;previous</b></a>&nbsp;&nbsp;";
			}
			for ($x=0;$x<$nbpages;$x++) {
				if ($da_page != $x) { echo "<a href=\"admin.php?status=" . $_GET["status"] . "&filter=" . $da_filt . "&page=" . $x . "&ct=" . $da_ct  . "\">"; } else { echo "<big>"; }
				echo $x;
				if ($da_page != $x) { echo "</a>"; } else { echo "</big>"; }
				echo "&nbsp;&nbsp;";
			}
			if (($da_page+1) == $nbpages) { echo "<font color=#4c4c4c><b>next&nbsp;&gt;</b></font>&nbsp;&nbsp;"; } else {
				echo "<a href=\"admin.php?status=" . $_GET["status"] . "&filter=" . $da_filt . "&page=" . ($da_page+1) . "&ct=" . $da_ct  . "\"><b>next&nbsp;&gt;</b></a>&nbsp;&nbsp;";
			}

		}
/*
		echo "<br>";
		$showmax = (($da_page*MAX_PER_PAGE)+MAX_PER_PAGE);
		if ($showmax > $nc) { $showmax = $nc; }
		echo "Showing tickets <b>" . (($da_page*MAX_PER_PAGE)+1) . "</b> to <b>" . $showmax . "</b>.";
*/
		echo "<br><br>\n";
		echo "<table border=1 cellspacing=0 cellpadding=5>";
		echo "<tr bgcolor=#000000>";
		echo "<td><font color=#ffffff><b>Ticket #</b></font></td>\n";
		echo "<td><font color=#ffffff><b>Last action by</b></font></td>\n";
//		if ($chkstatus==31337 || ($chkstatus!=31337 && !((int)$_GET["filter"] & 2))) {
			echo "<td><font color=#ffffff><b>Current Owner</b></font></td>\n";
//		}
		echo "<td><font color=#ffffff><b>From</b></font></td>\n";
		echo "<td><font color=#ffffff><b>Date</b></font></td>\n";
		echo "<td><font color=#ffffff><b>From IP</b></font></td>\n";
		echo "<td><font color=#ffffff><b>Complaint type</b></font></td>\n";
		if ($chkstatus==31337) {
			echo "<td><font color=#ffffff><b>Status</b></font></td>\n";
		}
		echo "<td><font color=#ffffff><b>Actions</b></font></td>\n";
		echo "</tr>\n";
		$cRec = 0;
		while ($o = pg_fetch_object($r)) {
			$showrecord = 1;
			if ($chkstatus!=31337 && $da_filt & 1 && $o->reviewed_by_id>0) { // not in referenced complaints, filter active, replied already once
				$rrepx = pg_safe_exec("SELECT reply_by FROM complaints_threads WHERE reply_text!='' AND complaint_ref='" . (int)$o->id . "' ORDER BY reply_ts DESC LIMIT 1");
				if ($robjx = pg_fetch_object($rrepx)) {
					if ($robjx->reply_by>0) { $showrecord = 0; }
				}
			}
			if ($showrecord) {
				$cRec++;
				echo "<tr";
				if ($chkstatus==31337 && (int)$o->complaints_ref>0 && (int)$o->is_new==1) {
					echo " bgcolor=#ffff11";
				}
				echo ">";
				echo "<td><nobr>" . $o->ticket_number . "</nobr></td>\n";
				if ($o->reviewed_by_id==0) {
					echo "<td>-</td>\n";
				} else {
					$rrep = pg_safe_exec("SELECT reply_by FROM complaints_threads WHERE complaint_ref='" . (int)$o->id . "' ORDER BY reply_ts DESC LIMIT 1");
					if ($robj = pg_fetch_object($rrep)) {
						$showid = $robj->reply_by;
					} else {
						$showid = $o->reviewed_by_id;
					}
					if  ($showid==0) {
						echo "<td><b>**<i>user</i>**</b></td>\n";
					} else {
						$ureq = pg_safe_exec("SELECT user_name FROM users WHERE id='" . (int)$showid . "'");
						if ($uobj = pg_fetch_object($ureq)) {
							echo "<td>" . $uobj->user_name . "</td>\n";
						} else {
							echo "<td><i>*unknown*</i></td>\n";
						}
					}
				}
//				if ($chkstatus==31337 || ($chkstatus!=31337 && !((int)$_GET["filter"] & 2))) {
					echo "<td>";
					if ($o->current_owner == 0) { echo "<i>none</i>"; } else {
						$rrR = pg_safe_exec("SELECT user_name FROM users WHERE id='" . (int)$o->current_owner . "'");
						if ($ooO = pg_fetch_object($rrR)) {
							echo "<a href=\"../users.php?id=" . $o->current_owner . "\" target=_blank>" . $ooO->user_name . "</a>";
						} else {
							echo "*not_found*";
						}
					}
					echo "</td>";
//				}
				echo "<td>" . $o->from_email;
				if ($o->from_id>0) {
					echo " (";
					$rr = pg_safe_exec("SELECT user_name FROM users WHERE id='" . (int)$o->from_id . "'");
					if ($oo = pg_fetch_object($rr)) {
						echo "<a href=\"../users.php?id=" . $o->from_id . "\" target=_blank>" . $oo->user_name . "</a>";
					} else {
						echo "*not_found*";
					}
					echo ")";
				}
				echo "</td>\n";
				echo "<td>" . cs_time($o->created_ts) . "</td>\n";
				echo "<td>" . $o->created_ip . "</td>\n";
				echo "<td>" . $cpt_name[$o->complaint_type] . "</td>\n";
				if ($chkstatus==31337) {
					echo "<td><nobr>";
					echo strtoupper($cmp_status[$o->status]);
					echo "</nobr></td>\n";
				}
				echo "<td><input type=button onClick=\"location.href='admin.php?view=" . $o->ticket_number . "'\" value=\"VIEW\"></td>\n";
				echo "</tr>\n";
			}
		}
		echo "</table>\n";
		if ($cRec == 0) {
			echo "<h3>No complaint to display.</h3>\n";
		}
		if ($nbpages>1) {
			echo "<br>\n";
			echo "<u>Page :</u> ";

			if ($da_page == 0) { echo "<font color=#4c4c4c><b>&lt;&nbsp;previous</b></font>&nbsp;&nbsp;"; } else {
				echo "<a href=\"admin.php?status=" . $_GET["status"] . "&filter=" . $da_filt . "&page=" . ($da_page-1) . "&ct=" . $da_ct  . "\"><b>&lt;&nbsp;previous</b></a>&nbsp;&nbsp;";
			}
			for ($x=0;$x<$nbpages;$x++) {
				if ($da_page != $x) { echo "<a href=\"admin.php?status=" . $_GET["status"] . "&filter=" . $da_filt . "&page=" . $x . "&ct=" . $da_ct  . "\">"; } else { echo "<big>"; }
				echo $x;
				if ($da_page != $x) { echo "</a>"; } else { echo "</big>"; }
				echo "&nbsp;&nbsp;";
			}
			if (($da_page+1) == $nbpages) { echo "<font color=#4c4c4c><b>next&nbsp;&gt;</b></font>&nbsp;&nbsp;"; } else {
				echo "<a href=\"admin.php?status=" . $_GET["status"] . "&filter=" . $da_filt . "&page=" . ($da_page+1) . "&ct=" . $da_ct  . "\"><b>next&nbsp;&gt;</b></a>&nbsp;&nbsp;";
			}

		}

	} else {
		echo "<h3>No complaint to display.</h3>\n";
	}
	echo "</form>";
} else { // details

	if (($_POST["switchto"]+0)>1 && (($_POST["switchto"]+0)<5 || ($_POST["switchto"]+0)==99) && check_secure_form("modifycomplaint" . $_GET["view"])) {
		if ($_POST["switchto"]==99) { // delete
			$q = "DELETE FROM complaints WHERE id='" . (int)$_POST["compid"] . "'";
			// To be checked for bugs (referenced keys) !@#
		} else {
			$q = "UPDATE complaints SET current_owner='" . (int)$user_id . "',reviewed_by_id='" . (int)$user_id . "',reviewed_ts=now()::abstime::int4,status='" . $_POST["switchto"] . "' WHERE id='" . (int)$_POST["compid"] . "'";
		}
		pg_safe_exec($q);
		echo "<a href=\"admin.php\"><b>&lt;&nbsp;back</b></a><br><br>\n";
	} else {
		if ($_POST["newowner"]>0) {
			echo "<a href=\"admin.php\"><b>&lt;&nbsp;back</b></a><br><br>\n";

			$q0 = "DELETE FROM complaints_reference WHERE complaints_ref='" . (int)$_POST["compid"] . "'";
			$lr = pg_safe_exec("SELECT id FROM complaints_threads WHERE complaint_ref='" . (int)$_POST["compid"] . "' ORDER BY reply_ts DESC LIMIT 1");
			$lo = @pg_fetch_object($lr);
			$q1_more = "";
			if ((int)$lo->id == 0) { // so if you transfer it when never replied, it doesnt "bug"
				$q1_more = "status=2,reviewed_by_id='" . (int)$user_id . "',reviewed_ts=now()::abstime::int4,";
			}
			$q1 = "UPDATE complaints SET " . $q1_more . "current_owner='" . (int)$_POST["newowner"] . "' WHERE id='" . (int)$_POST["compid"]. "'";
			$q2 = "INSERT INTO complaints_reference (complaints_ref,referenced_by,referenced_to,reference_ts,is_new) VALUES ('" . (int)$_POST["compid"] . "','" . (int)$user_id . "','" . (int)$_POST["newowner"] . "',now()::abstime::int4,1)";
			$nr = pg_safe_exec("SELECT user_name FROM users WHERE id='" . (int)$_POST["newowner"] . "'");
			$no = pg_fetch_object($nr);
			$q3 = "INSERT INTO complaints_threads (complaint_ref,reply_by,reply_ts,reply_text,actions_text,in_reply_to) VALUES ('" . (int)$_POST["compid"] . "'," . (int)$user_id . ",now()::abstime::int4,'','*** CHANGED TICKET OWNERSHIP TO : " . $no->user_name . " ***'," . (int)$lo->id . ")";

			//echo $q0 . "<br>\n";
			//echo $q1 . "<br>\n";
			//echo $q2 . "<br>\n";
			//echo $q3 . "<br>\n";

			pg_safe_exec($q0);
			pg_safe_exec($q1);
			pg_safe_exec($q2);
			pg_safe_exec($q3);

		} else {
			if (check_secure_form("modifycomplaint" . $_GET["view"])) {
				echo "<a href=\"admin.php\"><b>&lt;&nbsp;back</b></a><br><br>\n";
			} else {
				echo "<a href=\"javascript:history.go(-1);\"><b>&lt;&nbsp;back</b></a><br><br>\n";
			}
		}
	}

	$idt = explode("-",$_GET["view"]);
	$r = pg_safe_exec("SELECT * FROM complaints WHERE id='" . (int)$idt[0] . "' AND ticket_number='" . $_GET["view"] . "'");
	if ($o = pg_fetch_object($r)) {

		echo "<h3>Details on TICKET #" . $_GET["view"] . " <font size=+0>(<b>" . $cmp_status[$o->status] . "</b>)</font></h3>";
		echo "<form name=modify method=post action=admin.php?view=" . $_GET["view"] . ">";
		echo "<input type=hidden name=compid value=" . $o->id . ">\n";
		make_secure_form("modifycomplaint" . $_GET["view"]);
		echo "<table width=100% border=1 cellpadding=5 cellspacing=0>";

		echo initial_complaint( $_GET["view"], 0);

/*
		echo "<tr>";
		echo "<td bgcolor=#990000 valign=top align=right><font color=#ffffff>";
		echo "Status</font></td>";
		echo "<td width=99% valign=top>";
		echo $o->status . " (" . $cmp_status[$o->status] . ")";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "Switch to : <select name=switchto><option value=0>---</option><option value=2>(2) being processed</option><option value=3>(3) resolved</option><option value=4>(4) abandonned</option><option value=99>(99) deleted</option></select>&nbsp;<input type=submit value=Go!>";
		echo "</td></tr>\n";
*/

		echo "<tr>";
		echo "<td bgcolor=#990000 valign=top align=right><font color=#ffffff>";
		echo "Currently&nbsp;owned&nbsp;by</font></td>";
		echo "<td width=99% valign=top>";
		if ($o->current_owner==0) {
			echo "<b>NOT&nbsp;OWNED</b>";
		} else {
			$rx = pg_safe_exec("SELECT user_name FROM users WHERE id='" . (int)$o->current_owner . "'");
			$ox = pg_fetch_object($rx);
			echo "<b>" . $ox->user_name . "</b>";
		}

		if ($o->status <= 2) { // active complaint not in an "end" state (such as closed, resolved, cancelled, abandonned, deleted etc..)
			if ($admin>=800 || $o->current_owner == $user_id || $o->current_owner == 0) {
				echo "&nbsp;&nbsp;&nbsp;&nbsp;";
				if ($o->current_owner != $user_id && $o->current_owner != 0) {
					echo "(*)&nbsp;";
				}
				$rl = @pg_safe_exec("SELECT users.user_name,users.id,acl.flags FROM acl,users WHERE (((acl.flags & " . XCOMPLAINTS_ADM_READ . ")=" . XCOMPLAINTS_ADM_READ . ") OR ((acl.flags & " . XCOMPLAINTS_ADM_REPLY . ")=" . XCOMPLAINTS_ADM_REPLY . ")) AND users.id=acl.user_id ORDER BY lower(users.user_name)");
				if ($rl) {
					echo "Transfer ownership to <select name=newowner>";
					echo "<option value=0 selected>- DON'T CHANGE -</option>\n";
					$r8 = pg_safe_exec("SELECT levels.access,users.user_name,users.id FROM levels,users WHERE levels.access>=800 AND levels.channel_id=1 AND users.id=levels.user_id ORDER BY levels.access");
					while ($ol = pg_fetch_object($rl)) {
					   	if ((int)$ol->flags & XCOMPLAINTS_ADM_READ) { $acl_st = "+"; }
					   	if ((int)$ol->flags & XCOMPLAINTS_ADM_REPLY) { $acl_st = "*"; }
						echo "<option value=" . $ol->id . ">" . $ol->user_name . " (ACL" . $acl_st . ")</option>\n";
					}
					echo "<option value=0>--------------</option>\n";
					while ($o8 = pg_fetch_object($r8)) {
						echo "<option value=" . $o8->id . ">" . $o8->user_name . " (*" . $o8->access . ")</option>\n";
					}
					echo "</select>";
				} else {
					// the DB software doesnt allow bitwise operations .. (old pgsql)
					$rl = pg_safe_exec("SELECT users.user_name,users.id,acl.flags FROM acl,users WHERE acl.user_id=users.id ORDER BY lower(users.user_name)");
					$r8 = pg_safe_exec("SELECT levels.access,users.user_name,users.id FROM levels,users WHERE levels.access>=800 AND levels.channel_id=1 AND users.id=levels.user_id ORDER BY levels.access");
					echo "Transfer ownership to <select name=newowner>";
					echo "<option value=0 selected>- DON'T CHANGE -</option>\n";
					while ($ol = pg_fetch_object($rl)) {
						if (((int)$ol->flags & XCOMPLAINTS_ADM_READ) ||
						   ((int)$ol->flags & XCOMPLAINTS_ADM_REPLY)) {
						   	if ((int)$ol->flags & XCOMPLAINTS_ADM_READ) { $acl_st = "+"; }
						   	if ((int)$ol->flags & XCOMPLAINTS_ADM_REPLY) { $acl_st = "*"; }
							echo "<option value=" . $ol->id . ">" . $ol->user_name . " (ACL" . $acl_st . ")</option>\n";
						}
					}
					echo "<option value=0>--------------</option>\n";
					while ($o8 = pg_fetch_object($r8)) {
						echo "<option value=" . $o8->id . ">" . $o8->user_name . " (*" . $o8->access . ")</option>\n";
					}
					echo "</select>";
				}
				echo "&nbsp;&nbsp;<input type=button value=Go! onClick=\"switch_owner()\">\n";
			}
		}


		echo "</td></tr>\n";

		echo "</table>";

		if (
			(acl(XCOMPLAINTS_ADM_REPLY) && COMPLAINTS_DO_FOLLOWUP==0) ||
			(acl(XCOMPLAINTS_ADM_REPLY) && COMPLAINTS_DO_FOLLOWUP && $o->reviewed_by_id==$user_id)
		    ) {
			echo "<br>";
			if ($o->status<3) { echo "<input type=button onClick=\"post_reply()\" value=\"Post Reply\">&nbsp;&nbsp;"; }
			if ($o->status<3) { echo "<input type=button onClick=\"to_resolved()\" value=\"Mark as RESOLVED\">&nbsp;&nbsp;"; }
			if ($o->status<3) { echo "<input type=button onClick=\"to_abandonned()\" value=\"Mark as ABANDONNED\">&nbsp;&nbsp;"; }
			if ($admin>=800 && $o->status!=99) {
				echo "<input type=button onClick=\"to_deleted()\" value=\"Mark as DELETED\">";
			}
		}

		if ($o->reviewed_by_id>0) {
			echo "<br><br>";
			$lastid_RT = show_ticket_events($o->id);


		} else {
			$lastid_RT = 0;
		}
		if (
			(acl(XCOMPLAINTS_ADM_REPLY) && COMPLAINTS_DO_FOLLOWUP==0) ||
			(acl(XCOMPLAINTS_ADM_REPLY) && COMPLAINTS_DO_FOLLOWUP && ($o->reviewed_by_id==$user_id || $o->current_owner==$user_id))
		    ) {

		$RT = (int)$lastid_RT;
?>
<script language="JavaScript1.2">
<!--
<? if ($o->status<3) { ?>
function switch_owner() {
	if (document.forms[0].newowner.options[document.forms[0].newowner.selectedIndex].value > 0) {
		if (confirm('Are you sure you want to tranfer/change ownership of this ticket/complaint ?')) {
			document.forms[0].submit();
		}
	} else {
		alert('Please select a new OWNER !');
	}
}
function post_reply() {
	document.location.href='ticket.php?A=replyadm&ID=<?=$_GET["view"]?>&C=<? echo md5( CRC_SALT_0005 . $_GET["view"] . $RT . "reply-admin"); ?>&RT=<?=$RT?>';
}
function to_resolved() {
	if (confirm('Are you sure you want to mark this ticket/complaint as \'RESOLVED\' ?\n\nThis will notify the user the case is resolved and close the ticket.\n\nThere\'s no UNDO to this function.')) {
		document.location.href='ticket.php?A=resolve&ID=<?=$_GET["view"]?>&C=<? echo md5( CRC_SALT_0005 . $_GET["view"] . "resolve"); ?>';
	}
}
function to_abandonned() {
	if (confirm('Are you sure you want to mark this ticket/complaint as \'ABANDONNED\' ?\n\nThis will notify the user the case is cancelled and close the ticket.\n\nThere\'s no UNDO to this function.')) {
		document.location.href='ticket.php?A=cancel&ID=<?=$_GET["view"]?>&C=<? echo md5( CRC_SALT_0005 . $_GET["view"] . "cancel"); ?>';
	}
}
<? } ?>
<? if ($admin>=800 && $o->status!=99) { ?>
function to_deleted() {
	if (confirm('Are you sure you want to DELETE this ticket/complaint ?\n\nThis will *NOT* notify the user and close the ticket.\n\nThere\'s no UNDO to this function.')) {
		document.location.href='ticket.php?A=delete&ID=<?=$_GET["view"]?>&C=<? echo md5( CRC_SALT_0005 . $_GET["view"] . "delete"); ?>';
	}
}
<? } ?>
//-->
</script>
<?
		}
		echo "</form>";
	} else {
		echo "<br><br><h3>Invalid TICKET number</h3>";
	}
}
?>
</body>
</html>
