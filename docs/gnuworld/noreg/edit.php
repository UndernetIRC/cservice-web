<?
/* $Id: edit.php,v 1.4 2003/11/05 02:08:43 nighty Exp $ */
	$min_lvl=800;
	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = std_security_chk($auth);
        $admin = std_admin();
$cTheme = get_theme_info();
        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='" . (int)$user_id . "'");
        $adm_usr = pg_fetch_object($res,0);
        $adm_user = $adm_usr->user_name;
        if ($admin == 0) {
                echo "Restricted to logged in CService Admins, sorry.";
                die;
        }
        if (!($admin > 1)) {
        	echo "Sorry, your admin access is too low.";
        	die;
        }
$nrw_lvl = 0;
if (acl(XWEBAXS_2)) { $nrw_lvl = 1; }
if (acl(XWEBAXS_3)) { $nrw_lvl = 2; }
echo "<html><head><title>NOREG (EDIT MODE)</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");
if ($admin<$min_lvl && $nrw_lvl<1) {
	echo "Sorry, Your admin access is too low.<br><br>\n";
	echo "</body></html>\n\n";
	die;
}
$baad=-1;
$special_pass = CRC_SALT_0010;

echo "<b>NOREG</b> Editor (EDIT MODE) - <a href=\"./index.php\">Home</a> - <a href=\"add.php\">Add new entry</a>";

if ($crc == md5("$special_pass$HTTP_USER_AGENT$ts")) {
	// apply changes
	$baad=0;
	if ($user_name!="" && $channel_name!="") {
		echo "<li> Either fill <b>channel_name</b> or <b>user_name</b> please.\n";
		$baad=1;
	}
	if ($user_name=="" && $channel_name=="") {
		echo "<li> Missing <b>user_name</b> or <b>channel_name</b>.\n";
		$baad=1;
	}
	if ($reason=="") {
		echo "<li> Missing <b>reason</b>.\n";
		$baad=1;
	}
	if ($baad==0) {
		$queries[0]="update noreg set user_name='$user_name' where id='" . (int)$id . "'";
		$queries[1]="update noreg set email='$email' where id='" . (int)$id . "'";
		$queries[2]="update noreg set channel_name='$channel_name' where id='" . (int)$id . "'";
		$queries[3]="update noreg set type='" . (int)$type . "' where id='" . (int)$id . "'";
		$queries[4]="update noreg set never_reg='$never_reg' where id='" . (int)$id . "'";
		$queries[5]="update noreg set for_review='$for_review' where id='" . (int)$id . "'";
		// no modification for 'expire_time'
		// no modification for 'created_ts'
		// no modification for 'set_by'
		$queries[6]="update noreg set reason='$reason' where id='" . (int)$id . "'";

		for ($x=0;$x<count($queries);$x++) {
			pg_safe_exec($queries[$x]);
		}

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
	$query = "select * from noreg where id='$id'";
	$res = pg_safe_exec($query);
	$row = pg_fetch_object($res,0);

	if ($baad==-1) {
		$id = $row->id;
		$user_name = $row->user_name;
		$email = $row->email;
		$channel_name = $row->channel_name;
		$type = $row->type;
		$never_reg = $row->never_reg;
		$for_review = $row->for_review;
		$expire_time = $row->expire_time;
		$created_ts = $row->created_ts;
		$set_by = $row->set_by;
		$reason = $row->reason;
	}

	if ($ref=="" || !isset($ref)) {
		$ref = "./";
		if ($HTTP_REFERER!="") { $ref = urlencode($HTTP_REFERER); }
	}

	echo "<form name=apply action=edit.php method=get>\n";
	echo "<table cellspacing=0 cellpadding=3 border=1>\n";

	echo "<tr><td align=right><b>ID</b>&nbsp;</td>\n";
	echo "<td align=left>$id<input type=hidden name=id value=$id></td></tr>\n";

	echo "<tr><td align=right><b>user_name</b>&nbsp;</td>\n";
	echo "<td align=left><input type=text name=user_name value=\"$user_name\" size=50></td></tr>\n";

	echo "<tr><td align=right><b>email</b>&nbsp;</td>\n";
	echo "<td align=left><input type=text name=email value=\"$email\" size=50></td></tr>\n";

	echo "<tr><td align=right><b>channel_name</b>&nbsp;</td>\n";
	echo "<td align=left><input type=text name=channel_name value=\"$channel_name\" size=50></td></tr>\n";

	echo "<tr><td align=right><b>type</b>&nbsp;</td>\n";
	echo "<td align=left>";
	echo "<select name=type>\n";
	if ($type==0) { echo "<option selected value=0>*&lt;NULL&gt;</option>\n"; } else { echo "<option value=0>&lt;NULL&gt;</option>\n"; }
	if ($type==1) { echo "<option selected value=1>*Non-support</option>\n"; } else { echo "<option value=1>Non-support</option>\n"; }
	if ($type==2) { echo "<option selected value=2>*Abuse</option>\n"; } else { echo "<option value=2>Abuse</option>\n"; }
	if ($type==3) { echo "<option selected value=3>*Elective</option>\n"; } else { echo "<option value=3>Elective</option>\n"; }
	echo "</select>\n";
	echo "</td></tr>\n";

	echo "<tr><td align=right><b>never_reg</b>&nbsp;</td>\n";
	if ($never_reg) { echo "<td align=left><input type=checkbox name=never_reg checked value=1></td></tr>\n"; } else { echo "<td align=left><input type=checkbox name=never_reg value=1></td></tr>\n"; }

	echo "<tr><td align=right><b>for_review</b>&nbsp;</td>\n";
	if ($for_review) { echo "<td align=left><input type=checkbox name=for_review checked value=1></td></tr>\n"; } else { echo "<td align=left><input type=checkbox name=for_review value=1></td></tr>\n"; }


	echo "<tr><td align=right><b>expire_time</b>&nbsp;</td>\n";
	echo "<td align=left>";
	// TBA IF NEEDED (MODIFICATION)
	//echo date("m/d/Y H:i:s",$expire_time);
	if (!$never_reg) { echo cs_time($expire_time); } else { echo "*<b>never</b>*"; }
	echo "</td></tr>\n";

	echo "<tr><td align=right><b>created_ts</b>&nbsp;</td>\n";
	echo "<td align=left>";
	//echo date("m/d/Y H:i:s",$created_ts);
	echo cs_time($created_ts);
	echo "</td></tr>\n";

	echo "<tr><td align=right><b>set_by</b>&nbsp;</td>\n";
	echo "<td align=left>$set_by</td></tr>\n";

	echo "<tr><td align=right><b>reason</b>&nbsp;</td>\n";
	echo "<td align=left><input type=text name=reason size=50 value=\"$reason\"></td></tr>\n";


	echo "</table>\n";
	$ts = time();
	$crc = md5("$special_pass$HTTP_USER_AGENT$ts");
	echo "<br>\n";
	echo "<input type=hidden name=ts value=$ts>\n";
	echo "<input type=hidden name=crc value=$crc>\n";
	echo "<input type=hidden name=ref value=\"$ref\">\n";
	echo "<input type=submit value=\" APPLY CHANGES \">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<input type=button value=\" BACK TO LIST \" onclick=\"location.href='" . urldecode($ref) . "';\">\n";
	echo "</form><br>\n";
}

echo "For CService Admins use <b>ONLY</b>.";
?>
</body>
</html>
