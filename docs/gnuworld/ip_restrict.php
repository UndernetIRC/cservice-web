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
	$new_ip=$_POST["t1ip"];
        str_replace(':', ':', $new_ip, $count6);
        str_replace('.', '.', $new_ip, $count4);
        $ip_parts=explode("/", $new_ip);
        
        if ($count6>0)
        {
         $isValid = filter_var($ip_parts[0], FILTER_VALIDATE_IP,FILTER_FLAG_IPV6);   
         $go_ahead=false;
         if ($isValid)
         {
             if (count($ip_parts)===2)
             {
                    if (!is_int($ip_parts[1]))
                            if (($ip_parts[1]>0) && ($ip_parts[1]<129))
                            {
                                 $go_ahead=true;
                            }
                            
             }
             else
             {
                 if (count($ip_parts)===1)
                             $go_ahead=true;
             }
      
	}
        }
        if ($count4>0)
        {
         $isValid = filter_var($ip_parts[0], FILTER_VALIDATE_IP,FILTER_FLAG_IPV4);   
         $go_ahead=false;
         if ($isValid)
         {
             if (count($ip_parts)===2)
             {
                    if (!is_int($ip_parts[1]))
                            if (($ip_parts[1]>0) && ($ip_parts[1]<33))
                            {
                                 $go_ahead=true;
                            }
                            
             }
             else
             {
                 if (count($ip_parts)===1)
                             $go_ahead=true;
             }
      
	}
        }
  if ($go_ahead)
  {
      if ($_POST['totp_only'] == '1')
          $ntype=2;
      else
          $ntype=1;
      if ($_POST['ipr_exp']>0)
      $nexp=time()+$_POST['ipr_exp'];
      else
         $nexp=0;  
  $q = "INSERT INTO ip_restrict (user_id, value, last_updated, last_used, expiry, description, added, added_by, type) VALUES (";
  $q .= (int)$_GET["user_id"] . ", ";
  $q .= "'" . $new_ip . "', ";
  $q .= time() . ", '0', '". $nexp."', '".$_POST['descr']."', ".time().", " . (int)$user_id . ", '".$ntype."') ";    
 //die($q);
  pg_safe_exec($q);
header("Location: ip_restrict.php?user_id=" . (int)$_GET["user_id"]);
die;
  }
  else
      $err="Invalid IP format!";
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
	echo "<table width=900 border=0 cellspacing=2 cellpadding=3>";
	echo "<tr bgcolor=#4c4c4c>";
	echo "<td align=center colspan=\"5\"   ><font color=#ffffff><label for=rtype1><b>IP( with or w/o CIDR. Do not use wildcards!)</b></label></font></td>";
	echo "</tr>";

	echo "<tr bgcolor=#eeeeee>";
	echo "<td valign=top>";
	echo "<b>IP</b><br>";
	echo "<input type=text name=t1ip size=34 maxlength=40>";
	echo "</td>\n";
        echo "<td><b>Expires in: </b><br>";
        echo "<select name=\"ipr_exp\" id=\"ipr_exp\">";
        
        for ($i=0;$i<count($ipr_expiry)-1; $i++)
        echo '<option value="'.$ipr_expiry[$i].'">'. secs_to_h($ipr_expiry[$i]).'</option>
            ';
        echo '<option value="0">Never</option>';
        echo '</select>';
        echo "</td>\n";
         echo "<td ><b>Description: <br></b>";
        echo '<input type="text" name="descr" size="35"/> <br>';
        echo "</td>\n";
        echo "<td > <b>TOTP only?</b> <br>";
        echo '<input type="checkbox" name="totp_only" value="1">';
        echo "</td>\n";
	echo "</tr>";
        
	echo "<tr><td colspan=3 align=right><input type=submit value=\"Add\"></td></tr>\n";
	echo "</table>\n";
        
        


	echo "<br><br>";
	if ($user_id == $usr->id && ($admin>0 || has_acl($user_id)) && is_ip_restrict()) { // safety valve warning
		echo "<font size=+1 color=#ff1111><b>";
		echo "WARNING</b> : The current IP restrictions will NOT allow you to login.<br>Your current IP is : " . cl_ip() . "</font><br><br>";
	}

	echo "<table border=1 cellspacing=0 cellpadding=3 >\n";
	$amask=0;
        echo "<tr><td><b>IP</b></td><td><b>Added by</b></td><td><b>Expires on</b></td><td><b>Description</b></td>
                    <td><b>TOTP only?</b></td><td><b>Action</b></td></tr>";
	while ($ip = pg_fetch_object($ipq)) {
		$amask++;
		
                        echo "<tr>";
			echo "<td valign=\"top\"><b>";
                        if ($ip->type==0)
                            echo "<font color=#" . $cTheme->main_no . "><b>";
                        else 
                           echo "<font color=#" . $cTheme->main_yes . "><b>"; 
                        echo $ip->value."</b></font></td>\n";
		
		echo "</b></td>\n";
		$ruu = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $ip->added_by . "'");
		$ouu = pg_fetch_object($ruu);
		echo "<td valign=\"top\"> " . $ouu->user_name . "<br>\n";
		echo "<b>on</b>: " . cs_time($ip->added) . "</td>\n";
                echo "<td valign=\"top\">";
                if ($ip->expiry != 0)
                {
                    if (time()<$ip->expiry)
                        echo "" . cs_time($ip->expiry) . "</td>\n";
                    else
                        echo "-- expired --</td>\n";
                }
                else {
                echo "-- never --</td>\n";    
                }
		echo "<td width=\"200px\" valign=\"top\">";
                if ($ip->description == '')
                    echo "N/A</td>\n";
		echo "" . $ip->description . "</td>\n";
                echo "<td valign=\"top\">";
                if ($ip->type == 2)
		echo "<font color=#" . $cTheme->main_yes . "><b>YES</b></font></td>\n";
                else
                echo "<font color=#" . $cTheme->main_no . "><b>NO</b></font></td>\n";    
                echo "<td valign=\"top\"><a href=\"#\" onclick=\"window.open('edit_ipr.php?ipr_id=".(int)$ip->id . "&user_id=".(int)$_GET["user_id"]."', 'THEME','width=900,height=220')\");\"><input type=button value=\"Edit\"/></a> ";
		echo "<input type=button value=\"Delete\" onClick=\"del_id( " . (int)$ip->id . ")\"></td>\n";
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
/* pre inet type
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
*/
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
