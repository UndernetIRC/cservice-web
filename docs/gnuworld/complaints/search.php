<?
/* $Id: search.php,v 1.7 2004/02/15 07:59:47 nighty Exp $ */
require("../../../php_includes/cmaster.inc");
if (ENABLE_COMPLAINTS_MODULE != 1) { die("This option is disabled. Please contact the server administrator."); }
define(HARD_SEARCH_LIMIT,100);
std_init();
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
echo "<h4>(search results for: <font color=#ff1111>[</font>" . db2disp(post2db($_POST["q"])) . "<font color=#ff1111>]</font>)";
echo "<br>Matching : ";
if ($_POST["tnum"]==1) { echo "Ticket number. "; }
if ($_POST["ereply"]==1) { echo "Email for reply. "; }
if ($_POST["fip"]==1) { echo "From IP. "; }
if ($_POST["thread"]==1) { echo "Ticket events. "; }
if ($_POST["ocomp"]==1) { echo "Original complaint. "; }
if ($_POST["ochan"]==1 || $_POST["ouser"]==1) { echo "<br>"; }
if ($_POST["ochan"]==1) { echo "Offending/Purged channel(s). "; }
if ($_POST["ouser"]==1) { echo "Authenticated/Suspended username(s). "; }
if ($_POST["qt"]>0) { echo "<br>Specific complaint type (" . $cpt_name[$_POST["qt"]] . "). "; }
echo "</h4>";
echo "</h2>\n";
echo "<hr width=100% size=1 noshade>";
echo "<a href=\"javascript:history.go(-1);\"><b>&lt;&nbsp;back</b></a><br>\n";
$matchstring = post2db(strtolower(str_replace("*","%",str_replace("?","_",str_replace("%","\%",str_replace("_","\_",N_get_pure_string($_POST["q"])))))));
unset($cidz);
$cidz = Array();

if (preg_match("/\*/",N_get_pure_string($_POST["q"])) || preg_match("/\?/",N_get_pure_string($_POST["q"]))) {
	$cmp_oper = "LIKE";
} else {
	$cmp_oper = " = ";
}
unset($rQT);
$rQT = "";
if ((int)$_POST["qt"]>0) {
	$rQT = "complaints.complaint_type='" . (int)$_POST["qt"] . "' AND ";
}

if ($_POST["thread"]==1) {
// ticket events (must be separately) [forced auto %wild%]
$q = "SELECT complaints_threads.complaint_ref FROM complaints_threads,complaints WHERE (lower(reply_text) LIKE '%" . $matchstring . "%' OR lower(actions_text) LIKE '%" . $matchstring . "%') AND " . $rQT . "complaints.id=complaints_threads.complaint_ref AND complaints.status!=99 ORDER BY created_ts DESC";
//echo $q . "<br>";
$r = pg_safe_exec($q);
while ($o = pg_fetch_object($r)) { if (!isinarray($cidz,$o->complaint_ref) && $o->complaint_ref!=0) { $cidz[] = $o->complaint_ref; } }
unset($o);unset($r);unset($q);
}

if ($_POST["ouser"]==1) {
// usernames (must be separately)
$q = "SELECT complaints.id FROM complaints,users WHERE complaints.status!=99 AND " . $rQT . "(users.id=complaints.from_id OR users.id=complaints.complaint_users_id) AND lower(users.user_name) " . $cmp_oper . " '" . $matchstring . "' ORDER BY complaints.created_ts DESC";
//echo $q . "<br>";
$r = pg_safe_exec($q);
while ($o = pg_fetch_object($r)) { if (!isinarray($cidz,$o->id) && $o->id!=0) { $cidz[] = $o->id; } }
unset($o);unset($r);unset($q);
}

if ($_POST["tnum"]==1 ||
    $_POST["ereply"]==1 ||
    $_POST["fip"]==1 ||
    $_POST["ocomp"]==1 ||
    $_POST["ochan"]==1) {
// original complaint and others ...
$q = "SELECT complaints.id FROM complaints";
if ($_POST["ouser"]==1) { $q .= ",users"; }
$q .= " WHERE ";
$q .= "complaints.status!=99 AND " . $rQT . "(";
$or_needed = 0;
if ($_POST["tnum"]==1) {
	$q .= "lower(complaints.ticket_number) " . $cmp_oper . " '" . $matchstring . "' ";
	$or_needed = 1;
}
if ($_POST["ereply"]==1) {
	if ($or_needed == 1) { $q .= "OR "; } else { $or_needed = 1; }
	$q .= "lower(complaints.from_email) " . $cmp_oper . " '" . $matchstring . "' ";
}
if ($_POST["fip"]==1) {
	if ($or_needed == 1) { $q .= "OR "; } else { $or_needed = 1; }
	$q .= "complaints.created_ip " . $cmp_oper . " '" . $matchstring . "' ";
}
if ($_POST["ocomp"]==1) {
	if ($or_needed == 1) { $q .= "OR "; } else { $or_needed = 1; }
	// forced auto %wild%
	$q .= "(lower(complaints.complaint_text) LIKE '%" . $matchstring . "%' OR lower(complaints.complaint_logs) LIKE '%" . $matchstring . "%') ";
}
if ($_POST["ochan"]==1) {
	if ($or_needed == 1) { $q .= "OR "; } else { $or_needed = 1; }
	$q .= "(lower(complaints.complaint_channel1_name) " . $cmp_oper . " '" . $matchstring . "' OR lower(complaints.complaint_channel2_name) " . $cmp_oper . " '" . $matchstring . "') ";
}
$q .= ") ";
$q .= "ORDER BY complaints.created_ts DESC";
//echo $q . "<br>";
$r = pg_safe_exec($q);
while ($o = pg_fetch_object($r)) { if (!isinarray($cidz,$o->id) && $o->id!=0) { $cidz[] = $o->id; } }
unset($o);unset($r);unset($q);
}


if (count($cidz)==0) {
	echo "<h3>Sorry, no result for your search !</h3>\n";
} else {

	$nres = count($cidz);
	echo "<h3>" . $nres . " result";
	if ($nres>1) { echo "s"; }
	echo " for your search ...";
	if ($nres>HARD_SEARCH_LIMIT) {
		echo " (only first " . HARD_SEARCH_LIMIT . " will be shown)";
		$nres = HARD_SEARCH_LIMIT;
	}
	echo "</h3>\n";
	echo "<form name=dummy>";
	echo "<table border=1 cellspacing=0 cellpadding=5>";
	echo "<tr bgcolor=#000000>";
	echo "<td><font color=#ffffff><b>Ticket #</b></font></td>\n";
	echo "<td><font color=#ffffff><b>Last action by</b></font></td>\n";
	echo "<td><font color=#ffffff><b>Current owner</b></font></td>\n";
	echo "<td><font color=#ffffff><b>From</b></font></td>\n";
	echo "<td><font color=#ffffff><b>Date</b></font></td>\n";
	echo "<td><font color=#ffffff><b>From IP</b></font></td>\n";
	echo "<td><font color=#ffffff><b>Complaint type</b></font></td>\n";
	echo "<td><font color=#ffffff><b>Status</b></font></td>\n";
	echo "<td><font color=#ffffff><b>Actions</b></font></td>\n";
	echo "</tr>\n";
	for ($x=0;$x<$nres;$x++) {
		$r = pg_safe_exec("SELECT ticket_number,from_email,complaint_type,created_ts,created_ip,status,reviewed_by_id,current_owner FROM complaints WHERE id='" . (int)$cidz[$x] . "' AND ticket_number!=''");
		if ($o = pg_fetch_object($r)) {
			echo "<tr>";
			echo "<td><nobr>" . $o->ticket_number . "</nobr></td>\n";
			if ($o->reviewed_by_id==0) {
				echo "<td>-</td>\n";
			} else {
				$rrep = pg_safe_exec("SELECT reply_by FROM complaints_threads WHERE complaint_ref='" . (int)$cidz[$x] . "' ORDER BY reply_ts DESC LIMIT 1");
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
			echo "<td>" . $o->from_email . "</td>\n";
			echo "<td>" . cs_time($o->created_ts) . "</td>\n";
			echo "<td>" . $o->created_ip . "</td>\n";
			echo "<td>" . $cpt_name[$o->complaint_type] . "</td>\n";
			echo "<td>" . strtoupper($cmp_status[$o->status]) . "</td>\n";
			echo "<td><input type=button value=VIEW onClick=\"location.href='admin.php?view=" . $o->ticket_number . "'\"></td>\n";
			echo "</tr>\n";
		}
		unset($r);unset($o);
	}
	echo "</table>\n";
	echo "</form>\n";
}

?>
</body>
</html>
