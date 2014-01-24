<?
/* $Id: list_f.php,v 1.5 2004/07/25 03:31:52 nighty Exp $ */
	$min_lvl=800;
	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = std_security_chk($auth);
        $admin = std_admin();
	$cTheme = get_theme_info();
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='" . (int)$user_id . "'");
        if (pg_numrows($res)==0) {
        	echo "Suddenly logged out ?!";
        	die;
        }
        $adm_usr = pg_fetch_object($res,0);
        $adm_user = $adm_usr->user_name;
        if ($admin<=0 && !acl()) {
                echo "Sorry your admin access is too low.";
                die;
        }

$nrw_lvl = 0;
if (acl(XWEBAXS_2)) { $nrw_lvl = 1; }
if (acl(XWEBAXS_3)) { $nrw_lvl = 2; }



echo "<html><head><title>FRAUD USERNAMES (LIST MODE)</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");

echo "<b>FRAUD USERNAMES</b> ";

if ($admin>=$min_lvl || $nrw_lvl>0) { echo "Editor (LIST MODE) - <a href=\"./index.php\">New search</a> - <a href=\"add_f.php\">Add a new entry</a><br><br>\n"; } else {
	echo "(LIST MODE) - <a href=\"./index.php\">New search</a><br><br>\n";
}
$bad_args = 0;
if ($pattern=="") { $pattern="*"; }
if ($order<0 || $order>1) { $bad_args = 1; }
if ($bad_args) {
	echo "<b>BAD ARGUMENTS</b> - Please use <a href=\"./index.php\">this page</a> to make your choice.<br>\n";
} else {
	if ($pattern!="*") {
		$query = "where type=4 AND lower(";
		$query = $query . "user_name";
		$query = $query . ") like '" . strtolower(str_replace("*","%",$pattern)) . "' ";
		$blabla="";
	} else {
		$query = $query . "where type=4 ";
	}
	$query2 = $query . "order by ";
	if ($order==0) {
		$query2 = $query2 . "created_ts DESC";
	} else {
		$query2 = $query2 . "user_name";
	}

	$q_count = "select count(*) as count from noreg " . $query;
	$q_res = "select * from noreg " . $query2;

	//echo "$q_count<br>\n";
	$res0=pg_safe_exec($q_count);
	if (pg_numrows($res0)>0) {
		$res_count=pg_fetch_object($res0,0);
		$count = $res_count->count;
	} else {
		$res_count=0;
		$count=0;
	}

	if ($count>0) {
		$c_addy = "";
		if ($count>1) { $c_addy = "s"; }

		echo "Found <b>$count</b> record$c_addy matching your query :";
		echo "<br><br>\n";

		echo "<table border=1 cellspacing=0 cellpadding=2 bgcolor=#" . $cTheme->table_bgcolor . ">\n";
		echo "<tr bgcolor=#" . $cTheme->table_headcolor . ">\n";
		if ($admin>=$min_lvl || $nrw_lvl>0) { echo "<td></td>"; }
		echo "<td><font color=#" . $cTheme->table_headtextcolor . ">user_name</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">email-in-record</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">created_ts</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">set_by</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">reason</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">id</font></td>\n";
		echo "</tr>\n";

		$zetypes[0]="<font color=#" . $cTheme->main_warnmsg . ">N/A</font>";
		$zetypes[1]="Non-support";
		$zetypes[2]="Abuse";
		$zetypes[3]="Elective";
		$zetypes[4]="Username Fraud";

		$current_time=time();

		$res1=pg_safe_exec($q_res);
		for ($x=0;$x<$count;$x++) {
			$tmp_res = pg_fetch_object($res1,$x);
			echo "<tr>\n";
			if ($admin>=$min_lvl || $nrw_lvl>0) {
				echo "<td><a href=\"remove_f.php?id=" . $tmp_res->id . "\">Delete</a></td>";
			}
			if ($tmp_res->user_name!="") { echo "<td>" . $tmp_res->user_name . "</td>"; } else { echo "<td><font color=#" . $cTheme->main_warnmsg . ">N/A</font></td>"; }
			if ($tmp_res->email!="") { echo "<td>" . $tmp_res->email . "</td>"; } else { echo "<td><font color=#" . $cTheme->main_warnmsg . ">N/A</font></td>"; }
			echo "<td>" . cs_time($tmp_res->created_ts) . "</td>";
			echo "<td>" . $tmp_res->set_by . "</td>";
			echo "<td>" . $tmp_res->reason . "</td>";
			echo "<td>" . $tmp_res->id . "</td>";
			echo "</tr>\n";
		}

		echo "</table>\n";
	} else {

		echo "<b>No record found matching your query.</b>\n";

	}

	echo "<br><br>\n";
}
echo "For CService Admins use <b>ONLY</b>.";
?>
</body>
</html>


