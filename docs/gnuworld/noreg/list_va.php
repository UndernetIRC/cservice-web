<?
/* $Id: list_va.php,v 1.2 2003/11/05 02:08:43 nighty Exp $ */
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
        if ($admin == 0) {
                echo "Restricted to logged in CService Admins, sorry.";
                die;
        }
        if (!($admin >= $min_lvl)) {
        	echo "Sorry, your admin access is too low.";
        	die;
        }
$nrw_lvl = 0;
if (acl(XWEBAXS_2)) { $nrw_lvl = 1; }
if (acl(XWEBAXS_3)) { $nrw_lvl = 2; }



echo "<html><head><title>LOCKED VERIFICATION ANSWERS (LIST MODE)</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");

echo "<b>LOCKED VERIFICATION ANSWERS</b> ";

if ($admin>=$min_lvl) { echo "Editor (LIST MODE) - <a href=\"./index.php\">New search</a> - <a href=\"add_va.php\">Add a new entry</a><br><br>\n"; } else {
	echo "(LIST MODE) - <a href=\"./index.php\">New search</a><br><br>\n";
}
$bad_args = 0;
if ($mode == "SE") {
	if ($pattern=="") { $pattern="*"; }
	if ($order<0 || $order>1) { $bad_args = 1; }
}
if ($mode == "ST") {
	if (($ucount+0)<MIN_TIMES_VA_MATCH) { $bad_args = 1; }
}
if ($bad_args) {
	echo "<b>BAD ARGUMENTS</b> - Please use <a href=\"./index.php\">this page</a> to make your choice.<br>\n";
} else {
	if ($mode == "SE") {
		if ($pattern!="*") {
			$query = "where type=6 AND lower(";
			$query = $query . "user_name";
			$query = $query . ") like '" . strtolower(str_replace("*","%",$pattern)) . "' ";
			$blabla="";
		} else {
			$query = $query . "where type=6 ";
		}
		$query2 = $query . "order by user_name";

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
	}
	if ($mode == "ST") {
	        if ($_GET["ignorecase"]==1) {
	                $query = "SELECT DISTINCT lower(verificationdata),count(*) FROM users GROUP BY lower(verificationdata) HAVING count(*) >= " . (int)$_GET["ucount"] . " ORDER BY count(*) DESC";
	        } else {
	                $query = "SELECT DISTINCT verificationdata,count(*) FROM users GROUP BY verificationdata HAVING count(*) >= " . (int)$_GET["ucount"] . " ORDER BY count(*) DESC";
	        }
		$res1=pg_safe_exec($query);
		$count = pg_numrows($res1);
	}

	if ($count>0) {
		$c_addy = "";
		if ($count>1) { $c_addy = "s"; }

		echo "Found <b>$count</b> record$c_addy matching your query :";
		echo "<br><br>\n";

		if ($mode == "SE") {
			echo "<table border=1 cellspacing=0 cellpadding=2 bgcolor=#" . $cTheme->table_bgcolor . ">\n";
			echo "<tr bgcolor=#" . $cTheme->table_headcolor . ">\n";
			if ($admin>=$min_lvl) { echo "<td></td>"; }
			echo "<td><font color=#" . $cTheme->table_headtextcolor . ">verification_answer_pattern</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">created_ts</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">set_by</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">reason</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">id</font></td>\n";
			echo "</tr>\n";

			$current_time=time();
			if ($mode == "SE") { $res1=pg_safe_exec($q_res); }
			for ($x=0;$x<$count;$x++) {
				$tmp_res = pg_fetch_object($res1,$x);
				echo "<tr>\n";
				if ($admin>=$min_lvl) {
					echo "<td><a href=\"remove_va.php?id=" . $tmp_res->id . "\">Delete</a></td>";
				}
				if (substr($tmp_res->user_name,0,1)=="!") {
					echo "<td><b>" . substr($tmp_res->user_name,1) . "</b> <i>(ignore case)</i></td>";
				} else {
					echo "<td><b>" . $tmp_res->user_name . "</b></td>";
				}
				echo "<td>" . cs_time($tmp_res->created_ts) . "</td>";
				echo "<td>" . $tmp_res->set_by . "</td>";
				echo "<td>" . $tmp_res->reason . "</td>";
				echo "<td>" . $tmp_res->id . "</td>";
				echo "</tr>\n";
			}

			echo "</table>\n";
		}

		if ($mode == "ST") {
	                echo "<pre><font size=+1>";
	                echo "<b>Count\t\tVA";
	                if ($_GET["ignorecase"]==1) { echo " (lowercase'd)"; }
	                echo "\n---------------------------------------------------</b>\n";
	                while ($row = pg_fetch_object($res1)) {
	                        echo $row->count . "\t\t";
	                        if ($_GET["ignorecase"]==1) {
					if ($row->lower=="") { echo "<b><i>&lt;empty&gt;</i></b>"; } else {
						echo $row->lower;
					}
				} else {
					if ($row->verificationdata=="") { echo "<b><i>&lt;empty&gt;</i></b>"; } else {
						echo $row->verificationdata;
					}
				}
	                        echo "\n";
	                }
	                echo "</font></pre>";
		}
	} else {

		echo "<b>No record found matching your query.</b>\n";

	}

	echo "<br><br>\n";
}
echo "For CService Admins use <b>ONLY</b>.";
?>
</body>
</html>


