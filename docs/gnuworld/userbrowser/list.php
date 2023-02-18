<?php
/* $Id: list.php,v 1.46 2006/05/06 01:47:08 nighty Exp $ */

ignore_user_abort(true);
require("../../../php_includes/cmaster.inc");
std_connect();

$user_id = isset($_COOKIE["auth"]) ? std_security_chk($_COOKIE["auth"]) : 0;
$admin = std_admin();
if ($admin <= 0 && !acl()) {
    echo "Sorry your admin access is too low.";
    die;
}

global $MAX_ALLOWED_USERS;

/* You can possibly change the 8 values below */
$min_lvl = 800;
define("ENABLE_FRAUD_TAG", 0);
define("ENABLE_DELNOREG_TAG", 0);
define("ENABLE_SUSP_TAG", 1);
define("ALLOW_SUSP_500", 0);
define("HOSTMASK_HIDING", ".users.undernet.org");
define("SEND_TOAST_LIST", "toaster@undernet.org");
$MAX_HIGH_DAYS = 21;
$MAX_LOW_DAYS = 60;

if ($_REQUEST["mode"] == 2 || $admin >= $min_lvl) {
    define("ENABLE_DEL_TAG", ENABLE_DELNOREG_TAG);
} else {
    define("ENABLE_DEL_TAG", 0);
}

$nb_enabled = 0;
$enabled = "";
if (ENABLE_FRAUD_TAG) {
    $nb_enabled++;
    $enabled .= "F_FRAUD ";
}
if (ENABLE_DEL_TAG) {
    $nb_enabled++;
    $enabled .= "F_DELNOREG ";
}
if (ENABLE_SUSP_TAG) {
    $nb_enabled++;
    $enabled .= "F_SUSPEND ";
}
$enabled = trim($enabled);
define("DELTA_ELTS", $nb_enabled + 5);
$first_elt_ever = (($nb_enabled * 3) + 2);

unset($F_FRAUD);
unset($F_DELNOREG);
unset($F_SUSPEND);

$enabl_tab = explode(" ", $enabled);
for ($et = 0; $et < count($enabl_tab); $et++) {
    $var = $enabl_tab[$et];
    $$var = $first_elt_ever;
    $first_elt_ever++;
}

define("F_FRAUD", $F_FRAUD ?? 0);
define("F_DELNOREG", $F_DELNOREG ?? 0);
define("F_SUSPEND", $F_SUSPEND ?? 0);

$cTheme = get_theme_info();
$res = pg_safe_exec("SELECT user_name FROM users WHERE id='" . ($user_id + 0) . "'");
if (pg_numrows($res) == 0) {
    echo "Suddenly logged out ?!";
    die;
}
$adm_usr = pg_fetch_object($res, 0);
$adm_user = $adm_usr->user_name;


if ($admin < $min_lvl && !acl(XWEBAXS_3) && !acl(XWEBUSR_TOASTER) && !acl(XWEBUSR_TOASTER_RDONLY)) {
    echo "Sorry, your admin access is too low.";
    die;
}

$unf = pg_safe_exec("SELECT count_count FROM counts WHERE count_type=1");
if (pg_numrows($unf) == 0) {
    $MAX_UCOUNT = 0;
} else {
    $bla = pg_fetch_object($unf, 0);
    $MAX_UCOUNT = $bla->count_count;
}

echo "<html><head><title>User Toaster</title>";
std_theme_styles();
echo "</head>";
std_theme_body("../");

echo "<b>User Toaster</b> (Hunting Fraud Usernames) ";

echo "<br><br><a href=\"./index.php\">New search</a>\n";

$bad_args = 0;
$less_count=-1;
if ($MAX_UCOUNT<1000) { $less_count=$MAX_UCOUNT; $MAX_UCOUNT=1000; }

$st = $_REQUEST["st"];
$sp = $_REQUEST["sp"] ?? "";
$or = $_REQUEST["or"];
$nb = $_REQUEST["nb"] ?? 100;
$mode = $_REQUEST["mode"];
$minchan = $_REQUEST["minchan"] ?? 0;
$listtype = $_REQUEST["listtype"] ?? 0;


if ($mode==1) {
	if ($st<1 || $st>5) { $bad_args=1; }
	if ($or<1 || $or>7) { $bad_args=1; }
} else {
	if ($mode==2) {
		if (!isset($or) || $or<1) { $or = 5; }
		if ($nb<1 || $nb>$MAX_UCOUNT) { $bad_args=1; }
		if ($or<1 || $or>7) { $bad_args=1; }
	} else {
		if ($mode!=5) { if ($or<1 || $or>7) { $bad_args=1; } }
		if ($mode!=3) {
			if ($mode==4) {
				if ($or<1 || $or>7) { $bad_args=1; }
			} else {
				if ($mode==5) {
					if (($minchan+0)<MIN_CHAN_TOASTER_QRY) { $bad_args=1; }
				} else {
					if ($mode==6) {
						if (trim($_REQUEST["cname"])=="") { $bad_args=1; }
						if ($listtype<1 || $listtype>2) { $bad_args=1; }

					} else {
						$bad_args=1;
					}
				}
			}
		}
	}
}

if (MIN_CHAN_TOASTER_QRY == 0 && $mode==5) { $bad_args = 1; }

if ($bad_args) {
	echo "<br><br><b>BAD ARGUMENTS</b> - Please use <a href=\"./index.php\">this page</a> to make your choice.<br>\n";
	echo "</body></html>\n\n";
	die;
}
$tmp_cname = array_key_exists("cname", $_REQUEST) ? $_REQUEST["cname"] : "";
local_seclog("Show TOASTER LIST mode=[" . $mode . "], st=[" . $st . "], sp=[" . $sp . "], or=[" . $or . "], nb=[" . $nb . "], minchan=[" . $minchan . "], cname=[" . $tmp_cname . "].");

if (isset($da_id_list)) { unset($da_id_list); }
if (isset($da_username_list)) { unset($da_username_list); }

$lookup_apps =  $_REQUEST["lookup_apps"] != 1 ? 0 : $_REQUEST["lookup_apps"];

if ($mode==1) {
	if ($sp=="" && $st!=5) {
		echo "<br><br><b>ERROR</b> - Please fill in the search criteria.<br>\n";
		echo "<a href=\"javascript:history.go(-1);\">Back</a>\n";
		echo "</body></html>\n\n";
		die;
	}
	if ($st==1) {
		$match = "/^[a-zA-Z0-9\*\?]+$/";
		if (!preg_match($match,$sp)) {
			echo "<br><br><b>ERROR</b> - Invalid Username pattern.<br>\n";
			echo "<a href=\"javascript:history.go(-1);\">Back</a>\n";
			echo "</body></html>\n\n";
			die;
		}
	}
	if ($st==2) {
		$match = "/^[a-zA-Z0-9_.\*-]+@[a-zA-Z0-9_.\*-:]+$/";
		if (!preg_match($match,$sp)) {
			echo "<br><br><b>ERROR</b> - Invalid Email pattern.<br>\n";
			echo "<a href=\"javascript:history.go(-1);\">Back</a>\n";
			echo "</body></html>\n\n";
			die;
		}
	}
	if ($st==3) {
		$match = "/^([0-9]{1,3}\.[0-9]{1,3}\.([0-9]{1,3}|\*)\.([0-9]{1,3}|\*)|[a-fA-F0-9]+:[a-fA-F0-9:]+([^:]|\*))$/";
		if (!preg_match($match,$sp)) {
			echo "<br><br><b>ERROR</b> - Invalid IP mask.<br>Valid ones: <br>192.168.*.*, 10.1.2.3.*, 127.0.0.1<br>2001:2002:aaaa:bbbb:cccc:dddd:eeee:ffff, 2001:2002:aaaa:bbbb:cccc:dddd:eeee:*, 2001:2002:aaaa:bbbb:cccc:dddd:*, 2001:2002:aaaa:bbbb:cccc:*, 2001:2002:aaaa:bbbb:*, 2001:2002:aaaa:*, 2001:2002:*, 2001:*<br>\n";
			echo "<a href=\"javascript:history.go(-1);\">Back</a>\n";
			echo "</body></html>\n\n";
			die;
		}
	}
	if (strlen(str_replace("*","",$sp))==0 && $sp!="") {
		echo "<br><br><b>ERROR</b> - Please use a stricter search criteria.<br>\n";
		echo "<a href=\"javascript:history.go(-1);\">Back</a>\n";
		echo "</body></html>\n\n";
		die;
	}

	$query = "SELECT users.id FROM users,users_lastseen WHERE users_lastseen.user_id=users.id AND ";
	if (isset($_REQUEST["onlyfresh"])) { $query .= "(users.flags::int4 & 1)!=1 AND "; } // show only users that are NOT suspended.
	switch($st) {
		case 1:
			$query .= "lower(users.user_name) ";
			break;
		case 2:
			$query .= "lower(users.email) ";
			break;
		case 3:
			$query .= "users.signup_ip ";
			break;
		case 4:
			$query .= "lower(users.verificationdata) ";
			break;
		case 5:
			$query .= "lower(users_lastseen.last_hostmask) ";
			break;
	}
	if ($st == 5 && $sp == "") { $query .= " IS NULL ORDER BY "; } else {
		$query .=" LIKE '" . str_replace("?","_",str_replace("_",'\_',str_replace("*","%",strtolower($sp)))) . "' ORDER BY ";
	}
	switch ($or) {
		case 1:
			$query .= "users.user_name";
			break;
		case 7:
		case 2:
			$query .= "users.email";
			break;
		case 3:
			$query .= "users.signup_ts DESC";
			break;
		case 4:
			$query .= "users.verificationdata";
			break;
		case 5:
			$query .= "users.id";
			break;
		case 6:
			$query .= "users.signup_ip";
			break;
	}
}
if ($mode==2) {
        if (($nb+0)<=0 || ($nb+0)>($MAX_ALLOWED_USERS*2)) {
             	echo "<br><br><b>ERROR</b> - Invalid count of users to display.<br>Count ranges 1 to " . ($MAX_ALLOWED_USERS*2) . ".<br>\n";
	        echo "<a href=\"javascript:history.go(-1);\">Back</a>\n";
        	echo "<br><br>For CService Admins use <b>ONLY</b>.";
       	 	echo "</body></html>\n\n";
       	 	die;
        }
	$query = "SELECT id FROM users ";
	if ($_REQUEST["onlyfresh"]==1) { $query .= "WHERE (flags::int4 & 1)!=1 "; } // show only users that are NOT suspended.
	$query .= "ORDER BY id DESC LIMIT " . $nb;
}
if ($mode<3) {
	$res = @pg_safe_exec($query);
	if (!$res) { //safety valve, you need PostgreSQL 7.2+ in order to have "bitwise operators" enabled.
		echo "<br><br><b>ERROR: Your PostgreSQL does'nt support bitwise operators !<br>Either upgrade to PostgreSQL 7.2.x+, or do not use the checkbox to hide suspended users.</b>";
		$count = 0;
	} else {
		$count = pg_numrows($res);
		// fill in IDs list..
		while ($ooo = pg_fetch_object($res)) {
			$da_id_list[]=$ooo->id;
		}
		show_fraud_list($da_id_list,1);
	}
}

if ($mode==3) {
	$p_lines=explode("\n",urldecode($_POST["the_paste"]));
	$c_lines=count($p_lines);
	$p_type=($_POST["paste_type"]+0);
	for ($x=0;$x<$c_lines;$x++) {
		$da_username="";
		if ($p_type==1) { // anything with a @username.HOSTMASK_HIDING in lines...
			// searches for the first HOSTMASK_HIDING match and take everything before it until it matches a @.
			$low_line = strtolower($p_lines[$x]);
			$find_uname = strpos($low_line,strtolower(HOSTMASK_HIDING));
			if ($find_uname != false) {
				$t_line = substr($low_line,0,$find_uname); // keep the "[...] @username"(HOSTMASK_HIDING) part
				$last_at = strrpos($t_line,"@");
				$da_username = substr($t_line,($last_at+1));
			}
		}
		if ($p_type==2) { // trim() spaces, one username per line
			$trim_line = trim($p_lines[$x]);
			if ($trim_line != "") {
				$da_username = $trim_line;
			}
		}
		if ($p_type==3) { // find Auth:, list usernames from a "/msg X status" ...
			$trim_line = trim($p_lines[$x]);
			if (preg_match("/Auth: /",$trim_line)) {
				$elts = explode(" ",$trim_line);
				$elts_c = count($elts);
				$enabled = 0;
				$prev_elt = -1;
				for ($xx=0;$xx<$elts_c;$xx++) {
					if ($elts[$xx]=="Auth:") {
						if ($prev_elt == -1) {
							$enabled = 1;
						} else {
							$enabled = 0;
						}
					}
					if ($enabled && $elts[$xx]!="Auth:") {
						// determine type of space separated element
						if ($prev_elt == -1) { // username !
							$prev_elt = 1;
							//echo "***<br>";
							if (preg_match("/\//",$elts[$xx])) {
								$spp = explode("/",$elts[$xx]);
								$da_username_list[] = $spp[0];
								//echo "+user: " . $spp[0] . "<br>";
							} else {
								$da_username_list[] = $elts[$xx];
								//echo "+user: " . $elts[$xx] . "<br>";
							}
						} else {
							switch($prev_elt) {
								default:
								case 1: // previous was a username(/nickname)
									if (preg_match("/\[/",$elts[$xx])) { $prev_elt = 3; } else { $prev_elt = 2; }
									break;
								case 2: // previous was a nickname
									if (preg_match("/\[/",$elts[$xx])) { $prev_elt = 3; } else {
										$prev_elt = 1;
										if (preg_match("/\//",$elts[$xx])) {
											$spp = explode("/",$elts[$xx]);
											$da_username_list[] = $spp[0];
											//echo "+user: " . $spp[0] . "<br>";
										} else {
											$da_username_list[] = $elts[$xx];
											//echo "+user: " . $elts[$xx] . "<br>";
										}
									}
									break;
								case 3: // previous was a [level]
									$prev_elt = 1;
									if (preg_match("/\//",$elts[$xx])) {
										$spp = explode("/",$elts[$xx]);
										$da_username_list[] = $spp[0];
										//echo "+user: " . $spp[0] . "<br>";
									} else {
										$da_username_list[] = $elts[$xx];
										//echo "+user: " . $elts[$xx] . "<br>";
									}
									break;
							}
						}
						//echo $elts[$xx] . " (TYPE=" . $prev_elt . ")<br>";
					}
				}
			}
			$da_username = "";
		}
		if ($da_username!="") {
			$da_username_list[]=$da_username;
		}
	}
	show_fraud_list($da_username_list,2);
}

/*
if ($mode==4) {
	$q = "SELECT users.id FROM users,fraud_list_data WHERE fraud_list_data.list_id='" . $fl . "' AND users.id=fraud_list_data.user_id";
	$r = pg_safe_exec($q);
	while ($o = pg_fetch_object($r)) {
		$da_id_list[] = $o->id;
	}
	show_fraud_list($da_id_list,1);
}
*/

if ($mode==5) {
	if (($minchan+0)<MIN_CHAN_TOASTER_QRY) { $minchan = MIN_CHAN_TOASTER_QRY; }
	$q = "SELECT DISTINCT user_id,count(*) FROM levels GROUP BY user_id HAVING count(*) >= " . $minchan . " ORDER BY count(*) DESC";
	$r = pg_safe_exec($q);
	while ($o = pg_fetch_object($r)) {
		$da_id_list[] = $o->user_id;
	}
	show_fraud_list($da_id_list,1);
}

if ($mode==6) {
	if ($listtype == 1) {
		$qchk = "SELECT COUNT(id) AS count FROM channels WHERE registered_ts>0 AND lower(channels.name)='" . strtolower(trim(post2db($_REQUEST["cname"]))) . "'";
		$rchk = pg_safe_exec($qchk);
		$ochk = pg_fetch_object($rchk);
	        if ($ochk->count==0) {
	             	echo "<br><br><b>ERROR</b> - The channel '" . db2disp(post2db($_REQUEST["cname"])) . "' is NOT registered.<br>\n";
		        echo "<a href=\"javascript:history.go(-1);\">Back</a>\n";
	        	echo "<br><br>For CService Admins use <b>ONLY</b>.";
	       	 	echo "</body></html>\n\n";
	       	 	die;
	        }
		$q = "SELECT * FROM channels,levels WHERE channels.registered_ts>0 AND lower(channels.name)='" . strtolower(trim(post2db($_REQUEST["cname"]))) . "' AND levels.channel_id=channels.id AND levels.access>0 ORDER BY access DESC";
		$r = pg_safe_exec($q);
		while ($o = pg_fetch_object($r)) {
			$da_id_list[] = $o->user_id;
		}
		show_fraud_list($da_id_list,1);
	}
	if ($listtype == 2) {
		//$slock = "";
		$slock = " AND pending.status!=3";
		$qchk = "SELECT channels.id FROM channels,pending WHERE lower(channels.name)='" . strtolower(trim(post2db($_REQUEST["cname"]))) . "' AND pending.channel_id=channels.id" . $slock;
		$rchk = pg_safe_exec($qchk);
	        if ($ochk = pg_fetch_object($rchk)) {
	        	$chan_id = $ochk->id;
	        } else {
	             	echo "<br><br><b>ERROR</b> - The channel '" . db2disp(post2db($_REQUEST["cname"])) . "' is NOT a valid incoming/pending application.<br>\n";
		        echo "<a href=\"javascript:history.go(-1);\">Back</a>\n";
	        	echo "<br><br>For CService Admins use <b>ONLY</b>.";
	       	 	echo "</body></html>\n\n";
	       	 	die;
	        }
		$q = "SELECT * FROM objections WHERE channel_id='" . $chan_id . "' AND admin_only='N'";
		$r = pg_safe_exec($q);
		while ($o = pg_fetch_object($r)) {
			$da_id_list[] = $o->user_id;
		}
		show_fraud_list($da_id_list,1);
	}
}

echo "<br><br><a href=\"./index.php\">New search</a>\n";
echo "<br><br>\n";
?>
</body>
</html>
