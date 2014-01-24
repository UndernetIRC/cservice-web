<?
	/* $Id: add_entry.php,v 1.7 2004/07/25 03:31:52 nighty Exp $ */
	$min_lvl=800;
	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = std_security_chk($auth);
        $admin = std_admin();
	$cTheme = get_theme_info();
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
        $adm_usr = pg_fetch_object($res,0);
        $adm_user = $adm_usr->user_name;
        if ($admin==0 && !acl(XWEBAXS_2) && !acl(XWEBAXS_3) && !acl(XDOMAIN_LOCK)) {
        	echo "Sorry, your admin access is too low.";
        	die;
        }
$nrw_lvl = 0;
if (acl(XWEBAXS_2)) { $nrw_lvl = 1; }
if (acl(XWEBAXS_3)) { $nrw_lvl = 2; }
echo "<html><head><title>Domain/User Lock (ADD MODE)</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");
if ($admin<$min_lvl && !acl(XDOMAIN_LOCK)) {
	echo "Sorry, Your admin access is too low.<br><br>\n";
	echo "</body></html>\n\n";
	die;
}

echo "<b>Domain/User Lock</b> Editor (ADD MODE) - <a href=\"./index.php\">Home</a> - <a href=\"add.php\">Add new entry</a><br><br>\n";

$add_pass = CRC_SALT_0006;
if ($crc != md5("$ts$add_pass$HTTP_USER_AGENT")) {
	echo "<b>ERROR TYPE 3</b> - <a href=\"./\">Click here</a><br>\n";
	echo "For CService Admins use <b>ONLY</b>.";
	echo "</body>\n</html>\n\n";
	die;
}

$badargs = 0;

if ($f1=="") { $f1=0; }
if ($f2=="") { $f2=0; }
if ($f3=="") { $f3=0; }
if ($f4=="") { $f4=0; }

if ($domain=="") {
	echo "<li> You must enter a domain addy (user@<b>email-addy.com</b>) or a user prefix (<b>user@</b>some-isp.com) from an email address.\n";
	$badargs = 1;
}

//if (preg_match("/^[A-Za-z0-9_.-][@]$/",$domain)) { echo "MATCHES REGEXP01<br>\n"; } else { echo "DO NOT MATCH REGEXP01<br>\n"; }
//if (preg_match("/^((\*)[A-Za-z0-9.-])|[A-Za-z0-9.-]+\.(([A-Za-z][A-Za-z])|(\*))+$/",$domain)) { echo "MATCHES REGEXP02<br>\n"; } else { echo "DO NOT MATCH REGEXP02<br>\n"; }
//echo $badargs;

if (ereg("@",$domain)) {
	if( !(preg_match( "/^[A-Za-z0-9_.-]+@+$/", $domain )) ) {
		echo "<li> [001] The user prefix <b>" . htmlspecialchars($domain) . "</b> sounds invalid.\n";
		$badargs = 1;
	}
} else {
	if (ereg("\*",$domain) && $admin<$min_lvl && !acl(XDOMAIN_LOCK)) {
		echo "<li><font color=#" . $cTheme->table_tr_enlighten . "> <b>WILDCARDS ARE RESERVED FOR LEVELS " . $min_lvl . "+&nbsp;and DOMAIN_LOCK ACL users&nbsp;;P</b></font>\n";
		$badargs=1;
	} else {
		if ((ereg("\*",$domain) || ereg("\?",$domain)) && ($admin>=$min_lvl || acl(XDOMAIN_LOCK))) {
			if( !(preg_match( "/^[A-Za-z0-9\?\*.-]+\.[A-Za-z\?\*][A-Za-z\?\*]+$/", $domain )) ) {
				echo "<li> [002] The domain name <b>" . htmlspecialchars($domain) . "</b> sounds invalid.\n";
				$badargs = 1;
			} else {
				$l = strlen($domain);
				$count_star=0;
				for ($u=0;$u<$l;$u++) {
					if (substr($domain,$x,1)=="\*") {
						$count_star++;
						if ($count_star>2) { $badargs = 1; }
						if ($count_star==2 && (strpos($domain,"*")!=0 || strrpos($domain,"*") != ($l-1))) { $badargs = 1; $spc=2;}
					}
				}
			}
		} else {
			if( !(preg_match( "/^[A-Za-z0-9.-]+\.[A-Za-z][A-Za-z]+$/", $domain )) ) {
				echo "<li> [003] The domain name <b>" . htmlspecialchars($domain) . "</b> sounds invalid.\n";
				$badargs = 1;
			}
		}
	}
	if ($badargs==1 && $spc==2) {
		echo "<li> [004] The domain name <b>" . htmlspecialchars($domain) . "</b> sounds invalid.\n";
	}
}
if ($f1==0 && $f2==0 && $f3==0 && $f4==0) {
	echo "<li> You must check a LOCK type.\n";
	$badargs = 1;
}
if ($badargs) {
	echo "<br><br>\n";
	echo "Click <a href=\"javascript:history.go(-1);\">here</a> to go back to the form.<br>\n";

} else {
	$last_updated = time();

	$flags = 0;
	if ($f1) { $flags += $LOCK_USERNAME; }
	if ($f2) { $flags += $LOCK_REGPROC; }
	if ($f3) { $flags += $LOCK_EMAILCHG; }
	if ($f4) {
		// additional check to avoid banning yourself.
		$tmp = pg_safe_exec("SELECT email FROM users WHERE id='$user_id'");
		$obj = pg_fetch_object($tmp,0);
		$u_email = $obj->email;
		$tmp = explode("@",$u_email);

/*
		$diedie=0;
		if (ereg("@",$domain)) { $low_u_email = strtolower($tmp[0]) . "@"; } else { $low_u_email = strtolower($tmp[1]); }
		if (strtolower($domain)==$low_u_email) {
			$diedie=1;
		}
		$dom = $domain;
		$regmatch = "";
		if (substr($dom,0,1)!="*") { $regmatch .= "^"; }
		$regmatch .= str_replace("*","",$dom);
		if (ereg(strtolower($regmatch),$low_u_email)) {
			$diedie=1;
		}

		if ($diedie) {
*/
		if (  matches_wild(strtolower($tmp[1]),strtolower($dom))  ||  strtolower($dom) == $tmp[0] . "@"  ) {
			echo "<br><br><br><b>Security Threat, you are banning yourself from LOGIN, heh!</b><br><br>\n";
			echo "<a href=\"javascript:history.go(-1);\">Correct your entry</a><br>\n";
			echo "</body></html>\n\n";
			die;
		}
		$flags += $LOCK_LOGIN;
	}

	$chk = "SELECT id FROM $lock_domain_table WHERE lower(domain)='" . strtolower($domain) . "'";
	$res = pg_safe_exec($chk);
	if (pg_numrows($res)>0) {
		echo "<b>This email addy/ user prefix is already locked.</b><br><br>\n";
		echo "<a href=\"javascript:history.go(-1);\">Correct your entry</a><br>\n";
		echo "</body></html>\n\n";
		die;
	}

	$query = "INSERT INTO $lock_domain_table (domain,flags,last_updated,deleted) ";
	$query = $query . "values ('$domain',$flags,$last_updated,0)";

	//echo "<b>DEBUG</b>(query): $query<br><br>\n";

	pg_safe_exec($query);
	local_seclog("Added '" . $domain . "' to DOMAIN LOCK.");

	echo "The <b>Domain/User Lock entry</b> has been sucessfully added.<br>\n";
	echo "<br>\n";
	echo "<br>\n";
	echo "<a href=\"add.php\">Add a new entry</a> - <a href=\"./index.php\">Go back to search mode</a><br>\n";

}
?>
</body>
</html>


