<?
	/* $Id: edit.php,v 1.9 2004/07/25 03:31:52 nighty Exp $ */
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
echo "<html><head><title>Domain/User Lock (EDIT MODE)</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");
if ($admin<$min_lvl && !acl(XDOMAIN_LOCK)) {
	echo "Sorry, Your admin access is too low.<br><br>\n";
	echo "</body></html>\n\n";
	die;
}
$baad=-1;
$special_pass = CRC_SALT_0005;

echo "<b>Domain/User Lock</b> Editor (EDIT MODE) - <a href=\"./index.php\">Home</a> - <a href=\"add.php\">Add new entry</a>";

if ($crc == md5("$special_pass$HTTP_USER_AGENT$ts")) {
	// apply changes
	$baad=0;
	if ($domain=="") {
		echo "<li> Fill with a <b>domain_name</b> or <b>user@ prefix</b> please.\n";
		$baad=1;
	}
if (preg_match("@",$domain)) {
	if( !(preg_match( "/^[A-Za-z0-9_.-]+@+$/", $domain )) ) {
		echo "<li> [001] The user prefix <b>" . htmlspecialchars($domain) . "</b> sounds invalid.\n";
		$badargs = 1;
	}
} else {
	if (preg_match("\*",$domain) && $admin<$min_lvl && !acl(XDOMAIN_LOCK)) {
		echo "<li><font color=#" . $cTheme->table_tr_enlighten . "> <b>WILDCARDS ARE RESERVED FOR LEVELS " . $min_lvl . "+&nbsp;&nbsp;;P</b></font>\n";
		$badargs=1;
	} else {
		if ((preg_match("\*",$domain) || preg_match("\?",$domain)) && ($admin>=$min_lvl || acl(XDOMAIN_LOCK))) {
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
		$baad = 1;
	}
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
		if (preg_match("@",$domain)) { $low_u_email = strtolower($tmp[0]) . "@"; } else { $low_u_email = strtolower($tmp[1]); }
		if (strtolower($domain)==$low_u_email) {
			$diedie=1;
		}
		$dom = $domain;
		$regmatch = "";
		if (substr($dom,0,1)!="*") { $regmatch .= "^"; }
		$regmatch .= str_replace("*","",$dom);
		if (preg_match(strtolower($regmatch),$low_u_email)) {
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
	$chk = "SELECT id FROM $lock_domain_table WHERE lower(domain)='" . strtolower($domain) . "' AND id!=$id";
	$res = pg_safe_exec($chk);
	if (pg_numrows($res)>0) {
		echo "<br><br><br><b>This email addy/ user prefix is already locked.</b><br><br>\n";
		echo "<a href=\"javascript:history.go(-1);\">Correct your entry</a><br>\n";
		echo "</body></html>\n\n";
		die;
	}
	if ($baad==0) {
	        $lu = time();
	        $newflags = $flags;
		$query = "UPDATE $lock_domain_table SET domain='" . $domain . "',last_updated=" . $lu . ",flags=" . $newflags . " WHERE id='$id'";
		pg_safe_exec($query);

		echo " - <font color=#" . $cTheme->main_warnmsg . "><b>CHANGES COMMITTED TO DB</b></font><br><br>\n";
	} else {
		echo "<font color=#" . $cTheme->main_warnmsg . "><b>CHANGES NOT WRITTEN YET</b></font><br><br>\n";
	}
} else {
	echo "<br><br>\n";
}


if ($id=="" || $id<=0) {
	echo "<b>INVALID ARGUMENTS</b> - <a href=\"./index.php\">Click here</a><br>\n";
} else {
	$query = "select * from $lock_domain_table where id='$id'";
	$res = pg_safe_exec($query);
	$row = pg_fetch_object($res,0);

	if ($baad==-1) {
		$id = $row->id;
		$domain = $row->domain;
		$flags = $row->flags;
	}

	if ($ref=="" || !isset($ref)) {
		$ref = "./";
		if ($HTTP_REFERER!="") { $ref = urlencode($HTTP_REFERER); }
	}

	echo "<form name=apply action=edit.php method=get>\n";
	echo "<table cellspacing=0 cellpadding=3 border=1>\n";

	echo "<tr><td align=right><b>ID</b>&nbsp;</td>\n";
	echo "<td align=left>$id<input type=hidden name=id value=$id></td></tr>\n";

	echo "<tr><td align=right><b>domain_name, or<br>User@ prefix</b>&nbsp;</td>\n";
	echo "<td align=left><input type=text name=domain value=\"$domain\" size=50 maxlength=255></td></tr>\n";

	echo "<tr><td align=right><b>LOCK_USERNAME</b>&nbsp;</td>\n";
	if ((int)$flags & (int)$LOCK_USERNAME) { echo "<td align=left><input type=checkbox name=f1 checked value=1></td></tr>\n"; } else { echo "<td align=left><input type=checkbox name=f1 value=1></td></tr>\n"; }

	echo "<tr><td align=right><b>LOCK_REGPROC</b>&nbsp;</td>\n";
	if ((int)$flags & (int)$LOCK_REGPROC) { echo "<td align=left><input type=checkbox name=f2 checked value=1></td></tr>\n"; } else { echo "<td align=left><input type=checkbox name=f2 value=1></td></tr>\n"; }

	echo "<tr><td align=right><b>LOCK_EMAILCHG</b>&nbsp;</td>\n";
	if ((int)$flags & (int)$LOCK_EMAILCHG) { echo "<td align=left><input type=checkbox name=f3 checked value=1></td></tr>\n"; } else { echo "<td align=left><input type=checkbox name=f3 value=1></td></tr>\n"; }

	echo "<tr><td align=right><b>LOCK_LOGIN</b>&nbsp;</td>\n";
	if ((int)$flags & (int)$LOCK_LOGIN) { echo "<td align=left><input type=checkbox name=f4 checked value=1></td></tr>\n"; } else { echo "<td align=left><input type=checkbox name=f4 value=1></td></tr>\n"; }


	echo "</table>\n";
	$ts = time();
	$crc = md5("$special_pass$HTTP_USER_AGENT$ts");
	echo "<br>\n";
	if ($admin>=$min_lvl || acl(XDOMAIN_LOCK)) {
?>
<ul>
<li color=#<?=$cTheme->table_tr_enlighten?>><font color=#<?=$cTheme->table_tr_enlighten?>>If wildcards are used (* or ?) you should only use them for <b>domains</b> and not <b>user</b> prefixes<br><b>example :</b>&nbsp;subdomain.*, mail-3??*.*.net, *warez*.*, root@<br><b>but NOT :</b> user?*@, or *word*@.<br>Try not to ban the whole network :)~</font><br>
<li><b>If the input field above contains a @</b> it will be assimilated to a <font color=#<?=$cTheme->table_tr_enlighten?>><b>user@</b></font> prefix,<br>
and then, <b>no extra char will be allowed after the @</b> sign.<br>
<li>If the input field does <b>NOT</b> contain a <b>@</b> it will be treated as a @<font color=#<?=$cTheme->table_tr_enlighten?>><b>domain.name</b></font>.
</ul>
<?
//		echo "<li color=#" . $cTheme->table_tr_enlighten . "><font color=#" . $cTheme->table_tr_enlighten . ">If wildcards are used (*'s) you should only use them to end a domain<br><b>example :</b>&nbsp;yahoo.*</font><br>\n";
	}
	echo "<br><br>\n";
	echo "<input type=hidden name=ts value=$ts>\n";
	echo "<input type=hidden name=crc value=$crc>\n";
	echo "<input type=hidden name=ref value=\"$ref\">\n";
	echo "<input type=submit value=\" APPLY CHANGES \">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<input type=button value=\" BACK TO LIST \" onclick=\"location.href='" . urldecode($ref) . "';\">\n";
	echo "</form><br>\n";
}

?>
</body>
</html>
