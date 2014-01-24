<?
	/* $Id: list.php,v 1.6 2004/07/25 03:31:52 nighty Exp $ */
	$min_lvl=800;
	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = std_security_chk($auth);
        $admin = std_admin();
	$cTheme = get_theme_info();
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
        if (pg_numrows($res)==0) {
        	echo "Suddenly logged out ?!";
        	die;
        }
        $adm_usr = pg_fetch_object($res,0);
        $adm_user = $adm_usr->user_name;
        if ($admin==0 && !acl(XWEBAXS_2) && !acl(XWEBAXS_3) && !acl(XDOMAIN_LOCK)) {
        	echo "Sorry, your admin access is too low.";
        	die;
        }
$nrw_lvl = 0;
if (acl(XWEBAXS_2)) { $nrw_lvl = 1; }
if (acl(XWEBAXS_3)) { $nrw_lvl = 2; }



echo "<html><head><title>DOMAIN/USER LOCK (LIST MODE)</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");

echo "<b>Domain/User Lock</b> ";

if ($admin>=$min_lvl || acl(XDOMAIN_LOCK)) { echo "Editor (LIST MODE) - <a href=\"./index.php\">New search</a> - <a href=\"add.php\">Add a new entry</a><br><br>\n"; } else {
	echo "(LIST MODE) - <a href=\"./index.php\">New search</a><br><br>\n";
}
$bad_args = 0;
if ($pattern=="") { $pattern="*"; }
if ($types<0 || $types>4) { $bad_args = 1; }
if ($order<0 || $order>1) { $bad_args = 1; }
if ($bad_args) {
	echo "<b>BAD ARGUMENTS</b> - Please use <a href=\"./index.php\">this page</a> to make your choice.<br>\n";
} else {
	$clause = "WHERE";
	if ($pattern!="*") {
		$query = "WHERE lower(";
		$query = $query . "domain";
		$query = $query . ") LIKE '" . strtolower(str_replace("*","%",$pattern)) . "' ";
		$blabla="";
		$clause = "AND";
	}
	if ($view==1) {
		$query = $query . "$clause domain NOT LIKE '%@%' ";
	}
	if ($view==2) {
		$query = $query . "$clause domain LIKE '%@%' ";
	}
	$query2 = $query . "ORDER BY ";
	if ($order==0) {
		$query2 = $query2 . "last_updated DESC";
	} else {
		$query2 = $query2 . "domain";
	}

	$q_count = "SELECT count(*) AS count FROM $lock_domain_table " . $query;
	$q_res = "SELECT * FROM $lock_domain_table " . $query2;

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

		if ($types==0) { echo "Found <b>$count</b> record$c_addy matching your query :<br><br>"; }

		echo "<table border=1 cellspacing=0 cellpadding=2 bgcolor=#" . $cTheme->table_bgcolor . ">\n";
		echo "<tr bgcolor=#" . $cTheme->table_headcolor . ">\n";
		if ($admin>=$min_lvl || acl(XDOMAIN_LOCK)) { echo "<td></td><td></td>"; }
		echo "<td><font color=#" . $cTheme->table_headtextcolor . ">domain_name/user_prefix</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">LOCK_USERNAME</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">LOCK_REGPROC</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">LOCK_EMAILCHG</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">LOCK_LOGIN</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">last_updated</font></td>\n";
		echo "</tr>\n";

		$current_time=time();
		$d_count=0;
		$res1=pg_safe_exec($q_res);
		for ($x=0;$x<$count;$x++) {
			$tmp_res = pg_fetch_object($res1,$x);

			$flags = $tmp_res->flags;
			if ($types>0) { $show=0; } else { $show=1; }
			if ($types==1 && ((int)$flags & (int)$LOCK_USERNAME)) { $show=1; }
			if ($types==2 && ((int)$flags & (int)$LOCK_REGPROC)) { $show=1; }
			if ($types==3 && ((int)$flags & (int)$LOCK_EMAILCHG)) { $show=1; }
			if ($types==4 && ((int)$flags & (int)$LOCK_LOGIN)) { $show=1; }

			if ($show) {
				echo "<tr>\n";
				$d_count++;
				if ($admin>=$min_lvl || acl(XDOMAIN_LOCK)) {
					echo "<td><a href=\"edit.php?id=" . $tmp_res->id . "\">Edit</a></td>";
					echo "<td><a href=\"remove.php?id=" . $tmp_res->id . "\">Delete</a></td>";
				}
				if ($tmp_res->domain!="") { echo "<td>" . $tmp_res->domain . "</td>"; } else { echo "<td><font color=#" . $cTheme->main_no . ">N/A</font></td>"; }
				if ((int)$tmp_res->flags & (int)$LOCK_USERNAME) { echo "<td><font color=#" . $cTheme->main_yes . "><b>YES</b></font></td>"; } else { echo "<td><font color=#" . $cTheme->main_no . "><b>NO</b></font></td>"; }
				if ((int)$tmp_res->flags & (int)$LOCK_REGPROC) { echo "<td><font color=#" . $cTheme->main_yes . "><b>YES</b></font></td>"; } else { echo "<td><font color=#" . $cTheme->main_no . "><b>NO</b></font></td>"; }
				if ((int)$tmp_res->flags & (int)$LOCK_EMAILCHG) { echo "<td><font color=#" . $cTheme->main_yes . "><b>YES</b></font></td>"; } else { echo "<td><font color=#" . $cTheme->main_no . "><b>NO</b></font></td>"; }
				if ((int)$tmp_res->flags & (int)$LOCK_LOGIN) { echo "<td><font color=#" . $cTheme->main_yes . "><b>YES</b></font></td>"; } else { echo "<td><font color=#" . $cTheme->main_no . "><b>NO</b></font></td>"; }
				echo "<td>" . cs_time($tmp_res->last_updated) . "</td>";
				echo "</tr>\n";
			}
		}
		if ($admin>=$min_lvl || acl(XDOMAIN_LOCK)) { $span=8; } else { $span=6; }
		if ($d_count==0) {
			echo "<tr><td colspan=$span><b>No record found mathching your query.</b></td></tr>\n";
		} else {
			if ($types>0) {
				if ($d_count>1) { $r_addy="s"; } else { $r_addy=""; }
				echo "<tr><td colspan=$span>Found <b>$d_count</b> record$r_addy mathching your query.</b></td></tr>\n";
			}
		}
		echo "</table>\n";
	} else {

		echo "<b>No record found matching your query.</b>\n";

	}

	echo "<br><br>\n";
}
?>
</body>
</html>


