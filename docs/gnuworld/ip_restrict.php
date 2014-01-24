<?
/* $Id: ip_restrict.php,v 1.6 2005/11/18 10:08:08 nighty Exp $ */
$min_lvl=800;
header("Pragma: no-cache");
require("../../php_includes/cmaster.inc");
std_init();

unset($axslock); unset($da_u_adm);
$axslock = 1; $da_u_adm = 0;
if (acl(XIPR_MOD_OWN) && ($user_id==(int)$_GET["user_id"])) { $axslock = 0; }
$tmpr = @pg_safe_exec("SELECT access FROM levels WHERE channel_id=1 AND user_id=" . (int)$_GET["user_id"] . "");
if ($tmpr) {
	if ($tmpo = pg_fetch_object($tmpr)) {
		$da_u_adm = (int)$tmpo->access;
	}
}
if (acl(XIPR_MOD_OTHERS) && ($admin>$da_u_adm || $admin>=$min_lvl)) { $axslock = 0; }
if ($axslock == 1) {
	die("Oi! what are you doing here ?!");
}
$cTheme = get_theme_info();

if (check_secure_form("delfrm" . $_POST["user_id"])) {
	pg_safe_exec("DELETE FROM ip_restrict WHERE user_id=" . (int)$_POST["user_id"] . " AND id=" . (int)$_POST["delid"] . "");
	header("Location: ip_restrict.php?user_id=" . (int)$_POST["user_id"]);
	die;
}

$usrq = pg_safe_exec("SELECT * FROM users WHERE id=" . (int)$_GET["user_id"] . "");
$usr = pg_fetch_object($usrq);

$err = "";
if (check_secure_form("addrestrict" . $_POST["user_id"])) {
	$a_mask = "";
	$a_r1 = 0;
	$a_r2 = 0;
	switch ($_POST["rtype"]) {
		case 1:
			if (long2ip(ip2long($_POST["t1ip"]))!=$_POST["t1ip"]) { $err .= "<li> Invalid IP (" . $_POST["t1ip"] . ")\n"; }
			$a_mask = "";
			$a_r1 = ip2long($_POST["t1ip"]);
			$a_r2 = 0;
			break;
		case 2:
			if (long2ip(ip2long($_POST["t2ip1"]))!=$_POST["t2ip1"]) { $err .= "<li> Invalid IP (from) (" . $_POST["t2ip1"] . ")\n"; }
			if (long2ip(ip2long($_POST["t2ip2"]))!=$_POST["t2ip2"]) { $err .= "<li> Invalid IP (to) (" . $_POST["t2ip2"] . ")\n"; }
			if (long2ip(ip2long($_POST["t2ip1"]))==$_POST["t2ip1"] && long2ip(ip2long($_POST["t2ip2"]))==$_POST["t2ip2"]) {
				if (ip2long($_POST["t2ip2"])<=ip2long($_POST["t2ip1"])) { $err .= "<li> Invalid RANGE (from >= to)\n"; }

			}
			$a_mask = "";
			$a_r1 = ip2long($_POST["t2ip1"]);
			$a_r2 = ip2long($_POST["t2ip2"]);
			break;
		case 3:
			if (!preg_match('/^.*\..*$/',$_POST["t3mask"]) || !preg_match('/^[a-zA-Z0-9\.\*\?_-]+$/', $_POST["t3mask"])) { $err .= "<li> Invalid MASK (" . $_POST["t3mask"] . ")\n"; }
			$a_mask = $_POST["t3mask"];
			$a_r1 = 0;
			$a_r2 = 0;
			break;
	}
	if ($err != "") {
		std_theme_styles(1);
		std_theme_body();
		echo "<h2>IP Restriction List <font size=+0>(for " . $usr->user_name . ")</font></h2>\n";
		echo "<hr width=100% size=1 noshade>\n";
		echo "ERROR : <br><ul>";
		echo $err;
		echo "</ul>\n";
		echo "<br><a href=\"javascript:history.go(-1);\"><b>&lt;&lt;&nbsp;Go Back</b></a>\n";
		echo "</body>\n";
		echo "</html>\n\n";
		die;
	} else {
		$q = "";
		$q .= "INSERT INTO ip_restrict (user_id, allowmask, allowrange1, allowrange2, added, added_by, type) VALUES (";
		$q .= (int)$_GET["user_id"] . ", ";
		$q .= "'" . $a_mask . "', ";
		$q .= $a_r1 . ", ";
		$q .= $a_r2 . ", ";
		$q .= time() . ", " . (int)$user_id . ", 1)";
		//die($q);
		pg_safe_exec($q);
		header("Location: ip_restrict.php?user_id=" . (int)$_GET["user_id"]);
		die;
	}
}

std_theme_styles(1);
std_theme_body();

echo "<h2>IP Restriction List <font size=+0>(for " . $usr->user_name . ")</font></h2>\n";
echo "<hr width=100% size=1 noshade>\n";

$ipq = @pg_safe_exec("SELECT * FROM ip_restrict WHERE user_id=" . (int)$usr->id . "");
if ($ipq) {

	echo "<form name=addrestrict method=post onsubmit=\"return checkf(this);\">";
	make_secure_form("addrestrict" . (int)$usr->id);
	echo "<input type=hidden name=user_id value=\"" . (int)$usr->id . "\">\n";

	echo "<a href=\"users.php?id=" . (int)$usr->id . "\"><b>Back to user details</b></a><br><br>\n";

	echo "<b>Add an IP restriction :</b> (<u>note:</u> clearing the below list means <b>NO</b> restriction)<br>\n";
	echo "<table width=600 border=0 cellspacing=2 cellpadding=3>";
	echo "<tr bgcolor=#4c4c4c>";
	echo "<td align=center><font color=#ffffff><label for=rtype1><b>Single IP</b></label><br><input id=rtype1 type=radio name=rtype value=1></font></td>";
	echo "<td align=center><font color=#ffffff><label for=rtype2><b>IP range</b></label><br><input id=rtype2 type=radio name=rtype value=2></font></td>";
	echo "<td align=center><font color=#ffffff><label for=rtype3><b>Host or IP mask</b></label><br><input id=rtype3 type=radio name=rtype value=3></font></td>";
	echo "</tr>";

	echo "<tr bgcolor=#eeeeee>";
	echo "<td valign=top>";
	echo "<b>IP</b><br>";
	echo "<input type=text name=t1ip size=20 maxlength=15>";
	echo "</td>\n";
	echo "<td valign=top>";
	echo "<b>from IP</b><br>";
	echo "<input type=text name=t2ip1 size=20 maxlength=15>";
	echo "<br><br>";
	echo "<b>to IP</b><br>";
	echo "<input type=text name=t2ip2 size=20 maxlength=15>";
	echo "</td>\n";
	echo "<td valign=top>";
	echo "<b>Mask</b><br>";
	echo "<input type=text name=t3mask size=35 maxlength=255>";
	echo "<br>";
	echo "<i>";
	echo "wildcards <b>*</b> and <b>?</b> are allowed.";
	echo "</i>";
	echo "</td>\n";
	echo "</tr>";

	echo "<tr><td colspan=3 align=right><input type=submit value=\"Add\"></td></tr>\n";
	echo "</table>\n";

	echo "<br><br>";
	if ($user_id == $usr->id && ($admin>0 || has_acl($user_id)) && is_ip_restrict()) { // safety valve warning
		echo "<font size=+1 color=#ff1111><b>";
		echo "WARNING</b> : The current IP restrictions will NOT allow you to login.<br>Your current IP is : " . cl_ip() . "</font><br><br>";
	}

	echo "<table border=1 cellspacing=0 cellpadding=3>\n";
	$amask=0;
	while ($ip = pg_fetch_object($ipq)) {
		$amask++;
		echo "<tr>";
		if ($ip->allowrange2 != 0) { // IP range
			echo "<td>Range</td>";
			echo "<td><b>";
			echo long2ip($ip->allowrange1) . "</b>-<b>" . long2ip($ip->allowrange2);
		} elseif ($ip->allowrange1 != 0) { // single IP
			echo "<td>IP</td>";
			echo "<td><b>";
			echo long2ip($ip->allowrange1);
		} elseif ($ip->allowmask != "") { // IP / Host mask
			echo "<td>Mask</td>";
			echo "<td><b>";
			echo $ip->allowmask;
		}
		echo "</b></td>\n";
		$ruu = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $ip->added_by . "'");
		$ouu = pg_fetch_object($ruu);
		echo "<td><b>added_by</b>: " . $ouu->user_name . "<br>\n";
		echo "<b>on</b>: " . cs_time($ip->added) . "</td>\n";
		echo "<td><input type=button value=\"Delete\" onClick=\"del_id( " . (int)$ip->id . ")\"></td>\n";
		echo "</tr>\n";
	}
	if ($amask==0) {
		echo "<tr>";
		echo "<td align=center><b>";
		echo "no IP in the restriction list";
		echo "</b></td>\n";
		echo "</tr>";
	}
	echo "</table>\n";


}
?>
<script language="JavaScript">
<!--
function checkf(f) {
	var all_ok = true;
	var one_chk = false;
	if (f.rtype[0].checked) { // single IP
		one_chk = true;
		if (f.t1ip.value == '') { all_ok = false; }
	}
	if (f.rtype[1].checked) { // range
		one_chk = true;
		if (f.t2ip1.value == '') { all_ok = false; }
		if (f.t2ip2.value == '') { all_ok = false; }
	}
	if (f.rtype[2].checked) { // hostmask
		one_chk = true;
		if (f.t3mask.value == '') { all_ok = false; }
	}
	if (!one_chk) { all_ok = false; }
	if (!all_ok) {
		alert('Please, fill in all the required fields !');
	}
	return all_ok;
}
function del_id( id ) {
	if (confirm('Are you sure you want to delete this IP restriction ?')) {
		document.forms[1].delid.value = parseInt(id);
		document.forms[1].submit();
	}
}
//-->
</script>
</form>
<form name=delfrm method=POST>
<?
	make_secure_form("delfrm" . $usr->id);
	echo "<input type=hidden name=user_id value=\"" . $usr->id . "\">";
?>
<input type=hidden name=delid value=0>
</form>
</body>
</html>
