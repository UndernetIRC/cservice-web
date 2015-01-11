<?php

/* $Id: ip_restrict.php,v 1.6 2005/11/18 10:08:08 nighty Exp $ */
$min_lvl = 1800;
header("Pragma: no-cache");
require("../../php_includes/cmaster.inc");
std_init();

unset($axslock);
unset($da_u_adm);
$axslock = 1;
$da_u_adm = 0;
if (acl(XIPR_MOD_OWN) && ($ipruser_id == (int) $_GET["user_id"]) && ($ipr_id == (int) $_GET["ipr_id"])) {
    $axslock = 0;
}
$tmpr = @pg_safe_exec("SELECT access FROM levels WHERE channel_id=1 AND user_id=" . (int) $_GET["user_id"] . "");
if ($tmpr) {
    if ($tmpo = pg_fetch_object($tmpr)) {
        $da_u_adm = (int) $tmpo->access;
    }
}

if (acl(XIPR_MOD_OTHERS) && ($admin > $da_u_adm || $admin >= $min_lvl)) {
    $axslock = 0;
}
if ($axslock == 1) {
    die("Oi! what are you doing here ?!");
}
$cTheme = get_theme_info();
echo '<script>
    window.onunload = refreshParent;
    function refreshParent() {
        window.opener.location.reload();
    }
</script>';
std_theme_styles(1);
std_theme_body();
if (isset($_POST['save'])) {

    $new_ip = $_POST["t1ip"];
    str_replace(':', ':', $new_ip, $count6);
    str_replace('.', '.', $new_ip, $count4);
    $ip_parts = explode("/", $new_ip);

    if ($count6 > 0) {
        $isValid = filter_var($ip_parts[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        $go_ahead = false;
        if ($isValid) {
            if (count($ip_parts) === 2) {
                if (!is_int($ip_parts[1]))
                    if (($ip_parts[1] > 0) && ($ip_parts[1] < 129)) {
                        $go_ahead = true;
                    }
            } else {
                if (count($ip_parts) === 1)
                    $go_ahead = true;
            }
        }
    }
    if ($count4 > 0) {
        $isValid = filter_var($ip_parts[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        $go_ahead = false;
        if ($isValid) {
            if (count($ip_parts) === 2) {
                if (!is_int($ip_parts[1]))
                    if (($ip_parts[1] > 0) && ($ip_parts[1] < 33)) {
                        $go_ahead = true;
                    }
            } else {
                if (count($ip_parts) === 1)
                    $go_ahead = true;
            }
        }
    }
    if ($go_ahead) {
        if ($_POST['totp_only'] == '1')
            $ntype = 2;
        else
            $ntype = 1;
        if ($_POST['ipr_exp'] > 0)
            $nexp = time() + $_POST['ipr_exp'];
        else
            $nexp = 0;
        $q = "delete from ip_restrict where id=" . $ipr_id . "";
        pg_safe_exec($q);
        $q = "INSERT INTO ip_restrict (user_id, value, last_updated, last_used, expiry, description, added, added_by, type) VALUES (";
        $q .= (int) $_GET["user_id"] . ", ";
        $q .= "'" . $new_ip . "', ";
        $q .= time() . ", '0', '" . $nexp . "', '" . $_POST['descr'] . "', " . time() . ", " . (int) $user_id . ", '" . $ntype . "') ";
        //die($q);
        pg_safe_exec($q);
        //header("Location: ip_restrict.php?user_id=" . (int) $_GET["user_id"]);
        echo "<h2>IP Restriction Saved</h2>\n";
        echo "<hr width=100% size=1 noshade>\n";
        
        echo "</body>\n";
        echo "</html>\n\n";
        die;
        die;
    } else
        $err = "Invalid IP format!";
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

$cTheme = get_theme_info();
std_theme_styles(1);
std_theme_body();
$ipq = @pg_safe_exec("SELECT * FROM ip_restrict WHERE id=" . (int) $ipr_id . "");
if ($ipq) {
    $ip = pg_fetch_object($ipq);
   // print_r($ip);
    echo "<form name=addrestrict method=post onsubmit=\"return checkf(this);\">";
    echo "<input type=hidden name=user_id value=\"" . (int) $user_id . "\">\n";
    echo "<b>Edit an IP restriction :</b> <br>\n";
    echo "<table width=900 border=0 cellspacing=2 cellpadding=3>";
    echo "<tr bgcolor=#4c4c4c>";
    echo "<td align=center colspan=\"5\"   ><font color=#ffffff><label for=rtype1><b>IP( with or w/o CIDR. Do not use wildcards!)</b></label></font></td>";
    echo "</tr>";

    echo "<tr bgcolor=#eeeeee>";
    echo "<td valign=top>";
    echo "<b>IP</b><br>";
    echo "<input type=text name=t1ip size=34 maxlength=40 value=\"" . $ip->value . "\">";
    echo "</td>\n";
    echo "<td><b>Set new expire in: </b><br>";
    echo "<select name=\"ipr_exp\" id=\"ipr_exp\">";

    for ($i = 0; $i < count($ipr_expiry) - 1; $i++)
        echo '<option value="' . $ipr_expiry[$i] . '">' . secs_to_h($ipr_expiry[$i]) . '</option>
            ';
    echo '<option value="0">Never</option>';
    echo '</select>';
    echo "</td>\n";
    echo "<td ><b>Description: <br></b>";
    echo '<input type="text" name="descr" size="35" value="' . $ip->description . '"/> <br>';
    echo "</td>\n";
    echo "<td > <b>TOTP only?</b> <br>";
    if ($ip->type == 2)
        $checked = "checked";
    else
        $checked = "";
    echo '<input type="checkbox" name="totp_only" value="1" ' . $checked . '>';
    echo "</td>\n";
    echo "</tr>";

    echo "<tr><td colspan=3 align=right><input name=\"save\" id=\"save\" type=submit value=\"Save\"></td></tr>\n";
    echo "</table>\n";
}
?>