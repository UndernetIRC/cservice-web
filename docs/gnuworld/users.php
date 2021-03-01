<?php
require("../../php_includes/cmaster.inc");
std_init();
$cTheme = get_theme_info();
$theuser = trim($theuser);

$min_lvl = 800;
$edit_lvl = 600;
$maxuserlisted = 500;
$nrw_lvl = 0;

if (acl(XWEBAXS_2)) {
    $nrw_lvl = 1;
}
if (acl(XWEBAXS_3)) {
    $nrw_lvl = 2;
}
if (isset($canview)) {
    unset($canview);
}
if (isset($canedit)) {
    unset($canedit);
}
if ($admin >= $edit_lvl || acl(XAT_CAN_VIEW) || acl(XAT_CAN_EDIT)) {
    $canview = 1;
} else {
    $canview = 0;
}
if ($admin >= $edit_lvl || acl(XAT_CAN_EDIT) || acl(XTOTP_DISABLE_OTHERS)) {
    $canedit = 1;
} else {
    $canedit = 0;
}
if ($canedit == 0) {
    unset($edit);
}
//echo "THEUSER = $theuser, THEHOSTMASK = $thehostmask, MODE = $mode, ADMIN = $admin.<br><br>\n";

std_sanitise_username($user);
if ($theuser != "" && $mode == 1 && ($admin > 0 || acl(XAT_CAN_EDIT) || acl(XAT_CAN_VIEW))) {
    $lowuser = strtolower($theuser);
    $raw_q = "FROM users WHERE lower(user_name) LIKE '" . str_replace("*", "%", $lowuser) . "'";
//		echo "$raw_q<br><br>";
    $tcount = pg_safe_exec("SELECT COUNT(*) AS count " . $raw_q . " LIMIT " . ($maxuserlisted + 1));
    $cobj = pg_fetch_object($tcount, 0);
    $rcount = $cobj->count;
    if ($rcount > $maxuserlisted) {
        std_theme_styles(1);
        std_theme_body();
        echo "<h2>WARNING, more than $maxuserlisted results, Restrict your query a bit, please.</h2>";
        echo "<br><a href=\"javascript:history.go(-1);\">Back</a>\n";
        echo "</body></html>\n\n";
        die;
    }
    $user = pg_safe_exec("SELECT * " . $raw_q . " ORDER BY id DESC LIMIT $maxuserlisted");
    if ($rcount >= 1) {
        std_theme_styles(1);
        std_theme_body();
        echo "<h1>User Lookup (user_name)</h1>\n";
        echo "<h2>Matching <b>" . $theuser . "</b> ...</h2>($rcount matches)<br><br>\n";
        echo "<font size=+1>";
        for ($x = 0; $x < $rcount; $x++) {
            $bubu = pg_fetch_object($user, $x);
            if ($bubu->signup_ip != "" && $bubu->signup_ip != "0.0.0.0") {
                $radm = pg_safe_exec("SELECT access FROM levels WHERE user_id='" . $bubu->id . "' AND channel_id='1'");
                $da_IP = $bubu->signup_ip;
                if ($admin < SHOW_IP_LEVEL) {
                    if ($oadm = pg_fetch_object($radm)) {
                        $da_admlvl = $oadm->access;
                    } else {
                        $da_admlvl = 0;
                    }
                    if ($da_admlvl > 0 || (($bubu->flags & 256) == 256)) {
                        $da_IP = "&lt;IP_HIDDEN&gt;";
                    }
                }
                echo "<li>&nbsp;<a href=\"users.php?id=" . $bubu->id . "\">" . $bubu->user_name . "</a>&nbsp;&nbsp;&nbsp;<b>(</b>" . $da_IP . "<b>)</b>\n";
            } else {
                echo "<li>&nbsp;<a href=\"users.php?id=" . $bubu->id . "\">" . $bubu->user_name . "</a>&nbsp;&nbsp;&nbsp;<b>(</b><i>no IP info</i><b>)</b>\n";
            }
        }
        echo "<br><br><a href=\"javascript:history.go(-1);\">Back</a>\n";
        echo "</font>";
        echo "</body></html>\n\n";
        die;
    }
} else {
    if ($thehostmask != "" && $mode == 2 && ($admin > 0 || acl(XAT_CAN_EDIT) || acl(XAT_CAN_VIEW))) {
        $lowhost = strtolower($thehostmask);
        $raw_q = "FROM users,users_lastseen WHERE lower(users_lastseen.last_hostmask) LIKE '" . str_replace("*", "%", $lowhost) . "' AND users_lastseen.user_id=users.id";
        $tcount = pg_safe_exec("SELECT COUNT(*) AS count " . $raw_q . " LIMIT " . ($maxuserlisted + 1));
        $cobj = pg_fetch_object($tcount, 0);
        $rcount = $cobj->count;
        if ($rcount > $maxuserlisted) {
            std_theme_styles(1);
            std_theme_body();
            echo "<h2>WARNING, more than $maxuserlisted results, Restrict your query a bit, please.</h2>";
            echo "<br><a href=\"javascript:history.go(-1);\">Back</a>\n";
            echo "</body></html>\n\n";
            die;
        }
        $user = pg_safe_exec("SELECT * " . $raw_q . " ORDER BY lower(user_name) LIMIT $maxuserlisted");
        if ($rcount >= 1) {
            std_theme_styles(1);
            std_theme_body();
            echo "<h1>User Lookup (last_hostmask)</h1>\n";
            echo "<h2>Matching <b>" . $thehostmask . "</b> ...</h2>($rcount matches)<br><br>\n";
            echo "<font size=+1>";
            for ($x = 0; $x < $rcount; $x++) {
                $bubu = pg_fetch_object($user, $x);
                $radm = pg_safe_exec("SELECT access FROM levels WHERE user_id='" . $bubu->id . "' AND channel_id='1'");
                $da_LH = $bubu->last_hostmask;
                if ($admin < SHOW_IP_LEVEL) {
                    if ($oadm = pg_fetch_object($radm)) {
                        $da_admlvl = $oadm->access;
                    } else {
                        $da_admlvl = 0;
                    }
                    if ($da_admlvl > 0 || (($bubu->flags & 256) == 256)) {
                        $da_LH = remove_ip($da_LH, 1);
                    }
                }
                echo "<li>&nbsp;<a href=\"users.php?id=" . $bubu->id . "\">" . $bubu->user_name . "</a>&nbsp;&nbsp;&nbsp;<b>(</b>" . $da_LH . "<b>)</b>\n";
            }
            echo "<br><br><a href=\"javascript:history.go(-1);\">Back</a>\n";
            echo "</font>";
            echo "</body></html>\n\n";
            die;
        }
    } elseif ($admin > 0 || acl(XAT_CAN_EDIT) || acl(XAT_CAN_VIEW)) {
        if ($id == "") {
            std_theme_styles(1);
            std_theme_body();
            ?>
            <H1>User Lookup</h1>
                 <form name=f1 method=POST>
            Username <input type=text name=theuser>&nbsp;(wildcard : *)<br><br>
            <input type=submit value="Go baby!">
            <input type=hidden name=mode value=1>
            </form>
            <br><br>
            <form name=f2 method=POST>
            Last hostmask <input type=text name=thehostmask>&nbsp;(wildcard : *)<br><br>
            <input type=submit value="Go baby!">
            <input type=hidden name=mode value=2>
            </form>
            <?
            if ($admin > 0) {
                /*
                  $stat=pg_safe_exec("select count(*) from channels where registered_ts>0");
                  $stat=pg_fetch_object($stat,0);
                  $channel_count=$stat->count;
                  echo "Channels: $channel_count";

                  $stat=pg_safe_exec("select count(*) from users");
                  $stat=pg_fetch_object($stat,0);
                  $user_count=$stat->count;
                  echo "<br>Users: $user_count";
                */
            }

            if ($admin > 900) {
                echo "<br><br>";
                echo "<a href=\"newpass.php\">Change A User's Password</a><br>\n";
                echo "(<b>CODER</b> only feature for emergency cases, do NOT use it unless you know what you are doing)<br>\n";
            }


            echo "</body>\n</html>\n\n";

            exit;
        } else {
            $id = $id + 0;
        }
    } else {
        $id = $user_id;
    }
    $user = pg_safe_exec("select * from users where id='" . $id . "'");
}
if (pg_numrows($user) == 0) {
    std_theme_styles(1);
    std_theme_body();
    echo("<h1>No match found</h1>");
    echo "</body></html>\n\n";
    exit;
}
$user = pg_fetch_object($user, 0);
$id = $user->id;

if ($admin == 0 && $edit && $id != $user_id && !acl(XAT_CAN_EDIT)) {
    std_theme_styles(1);
    std_theme_body();
    echo "<h1>Not allowed !!!</h1><br>\n";
    echo "</body></html>\n\n";
    die;
}

echo "<html><head><title>CService</title>\n";
std_theme_styles();
?>
<link rel="stylesheet" type="text/css" href="./totp/css/smoothness/jquery-ui-1.8.20.custom.css" media="screen" />
<link rel="stylesheet" type="text/css" href="./totp/css/dialog-custom.css" media="screen" />
<link rel="stylesheet" type="text/css" href="./css/flash.css" media="screen" />
<script type="text/javascript" src="./totp/js/jquery-1.7.2.min.js"~></script>
<script type="text/javascript" src="./totp/js/jquery-ui-1.8.20.custom.min.js"></script>
<script>
  function showTwoStepDialog() {
    $( "#twostepdialog" ).dialog({
       dialogClass: 'twostep',
       modal: true,
       position: { my: "center", at: "center", of: $(".tsdialog") }
    });
  }
  setTimeout(function() {
      $('.flash.info').fadeOut('slow');
  }, 15000);
</script>
</head>
<?php
if (!session_id()) {
    session_start();
}
require_once("../../php_includes/FlashMessage.php");
$flash = new FlashMessage();

std_theme_body();

if ($flash->hasMessage()) {
    echo $flash->show();
}

if ($admin > 0 || $id == $user_id || acl(XAT_CAN_EDIT)) {
    $uid_info = "<font size=-1>(" . $id . ")</font>";
} else {
    $uid_info = "";
}
echo("
	<script language=\"JavaScript1.2\">
	<!--
		function del_this_user() {
			if (confirm('Are you sure you want to REMOVE (and all associated records) the user :\\n\\n\\t" . $user->user_name . "\\n\\nThere will be no way to cancel or revert this operation.\\n\\nClick \\'OK\\' to DELETE the username.\\n\\n')) {
				document.forms[2].submit();
			}
		}
	//-->
	</script>");
if ($admin > 0)
    echo ("
        <form method=POST action=save_user.php>");
echo ("
        <TABLE WIDTH=100% cellspacing=0 cellpadding=2 BORDER=0 BGCOLOR=#" . $cTheme->table_bgcolor . ">
        <tr>
        <td colspan=2>
        <center><h1>User page for " . $user->user_name . " $uid_info</h1>");
unset($delBtn);
$delBtn = 0;
$rrr = pg_safe_exec("SELECT levels.channel_id,levels.access FROM channels,levels WHERE levels.user_id='" . $user->id . "' AND (levels.access=500 OR levels.channel_id=1) AND levels.channel_id=channels.id AND channels.registered_ts>0");
if (pg_numrows($rrr) == 0) {
    $delBtn = 1;
} else {
    $is500 = 0;
    $isAdmin = 0;
    while ($ooo = pg_fetch_object($rrr)) {
        if ($ooo->channel_id == 1) {
            $isAdmin = 1;
            $isAdminLvl = $ooo->access;
        }
        if ($ooo->access == 500) {
            $is500 = 1;
        }
    }
}
if ($edit && $isAdmin && $isAdminLvl >= $admin && $user->id != $user_id && $admin < 1000) {
    unset($edit);
}
if ($edit && $admin >= $min_lvl && $user->id != $user_id && $delBtn) {
    echo "<input type=button onClick=\"del_this_user();\" value=\"Delete this username\"> (no undo)";
    /*
      echo "<br><b><i>user is ";
      if ($isAdmin) {
      echo "a CService official";
      } else {
      echo "a channel manager";
      }
      echo ", 'Delete' option is not available.";
     */
}
echo ("</center></td></Tr>");
if ((int) $user->flags & 1) {
    echo("<tr><td colspan=2 bgcolor=#" . $cTheme->main_warnmsg . "><font color=#" . $cTheme->table_headtextcolor . " size=-1><em><b><center>~ This account is currently suspended ~</center></em></b></font></td></tr>");
}
if ($admin > 0 || acl(XAT_CAN_EDIT) || $nrw_lvl > 0) {
    $unf = pg_safe_exec("select * from noreg where type=4 and lower(user_name)='" . strtolower($user->user_name) . "'");
    if (pg_numrows($unf) > 0 || ((int) $user->flags & 0x0008)) {
        echo "<tr><td colspan=2 bgcolor=#" . $cTheme->main_frauduser . "><font color=#" . $cTheme->table_maintextcolor . " size=-1><em><b><center>~ This account is in FRAUD USERNAMES";
        if (pg_numrows($unf) > 0) {
            $unfobj = pg_fetch_object($unf, 0);
            $da_reason = $unfobj->reason;
            echo " - Reason : </b>" . $da_reason . "<b>";
        }
        echo " ~</center></em></b></font></td></tr>\n";
    }
    $unf2 = pg_safe_exec("select * from noreg where type<4 and lower(user_name)='" . strtolower($user->user_name) . "' OR lower(email)='" . strtolower($user->email) . "'");
    if (pg_numrows($unf2) > 0) {
        echo "<tr><td colspan=2 bgcolor=#" . $cTheme->table_tr_enlighten . "><font color=#" . $cTheme->main_textcolor . " size=-1><em><b><center>~ This account is in NOREG (user or email) ~</center></em></b></font></td></tr>\n";
    }
    if (is_email_locked(1, $user->email) || is_email_locked(2, $user->email) || is_email_locked(4, $user->email) || is_email_locked(8, $user->email)) {
        echo "<tr><td colspan=2 bgcolor=#" . $cTheme->table_tr_enlighten2 . "><font color=#" . $cTheme->table_headtextcolor . " size=-1><em><b><center>~ This account is in DOMAIN LOCK (email addy) ~</center></em></b></font></td></tr>\n";
    }
}


echo("<tr><td colspan=2 bgcolor=#" . $cTheme->table_sepcolor . "><font size=-1 color=#" . $cTheme->table_septextcolor . "><em><b>User Information</b></em></td></tr>");
if (!$edit) {
    if ($admin > 0) {
        local_seclog("View '" . $user->user_name . "' (" . $user->id . ")");
    }
    if (($id == $user_id) && ($admin == 0)) {
        //if ($id == $user_id) {
        $no_totp_check = 0;

        if (TOTP_ON) {
            if (has_totp($id))
                echo("<TR><TD><font size=-1><b>Email</b></td><td><font size=-1> " . $user->email . "</font><FONT COLOR=#" . $cTheme->main_textlight . " size=-1> (Hidden from public)</td></tr>");
            else
                $no_totp_check = 1;
        } else
            $no_totp_check = 1;
        if ($no_totp_check == 1) {
            echo "
				<tr><td><font size=-1><b>Email</b></td><td><font size=-1><a href=\"#\" onclick=\"window.open('show_mail.php', 'WARNING','width=500,height=120')\");\">Open Popup</a> to verify your identity in order to see the email address.</font></td></tr>";
        }
    } else
    if (($admin > 0 || acl(XAT_CAN_EDIT)))
        echo("<TR><TD><font size=-1><b>Email</b></td><td><font size=-1> " . $user->email . "</font><FONT COLOR=#" . $cTheme->main_textlight . " size=-1> (Hidden from public)</td></tr>"); // Only show for self or admin
    if ($admin > 0) {

        echo("<TR><TD><font size=-1><b>Web session timeout</b></td><td><font size=-1> " . formatSeconds(get_custom_session($id)) . "</font></td></tr>");
        if ($id == $user_id)
            echo("<TR><TD><font size=-1><b>Theme</b></td><td><font size=-1><a href=\"#\" onclick=\"window.open('theme_change.php', 'THEME','width=500,height=120')\");\">Open Popup</a> to change your theme.</font></td></tr>");
    }
    if ($user->url != "") {
        if ($user->url != "" && !preg_match("/^http:\/\//", $user->url)) {
            echo "<tr><td><font size=-1><b>Homepage </b></td><td><font size=-1><a href=\"http://$user->url\" target=\"_blank\">http://" . htmlspecialchars($user->url) . "</a></td></tr>";
        } else {
            echo "<tr><td><font size=-1><b>Homepage </b></td><td><font size=-1><a href=\"$user->url\" target=\"_blank\">" . htmlspecialchars($user->url) . "</a></td></tr>";
        }
    }

    $res = pg_safe_exec("select * from languages where id=" . $user->language_id);
    $language = pg_fetch_object($res, 0);
    echo("<tr><td><b>Language:</b></td><td> " . $language->name . "</td></tr>");
    if ((TOTP_ALLOW_ALL == 1) || ($admin > 0) || (isoper($user->user_id)==1))
    {
        if (TOTP_ON) {
            if ($id == $user_id) {
                if (!has_totp($id)) {
                    $authtok = explode(":", $auth);
                    $authcsc = $authtok[3];
                    $sec_id = md5($user_id . CRC_SALT_0019 . $authcsc);
                    echo '<tr><td><b>Two-step verification:</b></td><td> <font color=#' . $cTheme->main_no . '><b>Disabled</b></font> - Click <a href ="' . TOTP_PATH . 'activate.php?SECURE_ID=' . $sec_id . '"> here </a> to enable it (read more about two-step verification <a class="tsdialog" href="javascript:void(null);" onclick="showTwoStepDialog();">here</a>).</td></tr>';
                    if ($admin > 0 || has_ipr($user_id)) {
                        if ($user->flags & TOTP_ADMIN_IPR_FLAG) {
                            $theme = $cTheme->main_yes;
                            $status = 'Enabled';
                        } else {
                            $theme = $cTheme->main_no;
                            $status = 'Disabled';
                        }
                        printf('<tr><td><b>Two-step verification IPR check:</b></td><td><font color="#%s"><b>%s</b></font>', $theme, $status);
                    }
                } else {

                    echo '<tr><td><b>Two-step verification:</b></td><td> <font color=#' . $cTheme->main_yes . '><b>Enabled</b></font>';
                    if ((TOTP_ALLOW_SELF_OFF == 1) && ($admin == 0) && (!isoper($user->user_id))) {
                        echo " - Click <a href=\"" . TOTP_PATH . "disable_totp.php\"> here </a> to disable two-step verification.";
                    } else {
                        echo '</td></tr>';
                    }
                    if ($admin > 0 || has_ipr($user_id)) {
                        if ($user->flags & TOTP_ADMIN_IPR_FLAG) {
                            $theme = $cTheme->main_yes;
                            $status = 'Enabled';
                        } else {
                            $theme = $cTheme->main_no;
                            $status = 'Disabled';
                        }
                        printf('<tr><td><b>Two-step verification IPR check:</b></td><td><font color="#%s"><b>%s</b></font>', $theme, $status);
                    }
                }
            }
            else {
                if (SHOW_TOTP_PUBLIC || acl(XTOTP_DISABLE_OTHERS) || ($admin > 0)) {
                    if (has_totp($id)) {
                        $theme = $cTheme->main_yes;
                        $status = 'Enabled';
                    } else {
                        $theme = $cTheme->main_no;
                        $status = 'Disabled';
                    }
                    printf("<tr><td><b>Two-step verification:</b></td><td><font color=\"#%s\"><b>%s</b></font></td></tr>", $theme, $status);

                    if ($user->flags & TOTP_ADMIN_IPR_FLAG) {
                        $theme = $cTheme->main_yes;
                            $status = 'Enabled';
                    } else {
                        $theme = $cTheme->main_no;
                        $status = 'Disabled';
                    }
                    printf('<tr><td><b>Two-step verification IPR check:</b></td><td><font color="#%s"><b>%s</b></font>', $theme, $status);
                }
            }
        }
    }
    if (ENABLE_NOTES && ((NOTES_ADMIN_ONLY && $admin > 0) || (NOTES_ADMIN_ONLY == 0))) {
        echo("<tr><td><b>Accept notes:</b></td><td> ");
        if ((int) $user->flags & 0x0010) {
            echo "<font color=#" . $cTheme->main_no . "><b>No</b> (only from reply)</font>";
        } else {
            echo "<font color=#" . $cTheme->main_yes . "><b>Yes</b></font>";
        }
        echo "</td></tr>";
    }
    if ($admin >= NOPURGE_USER_VIEWLEVEL) {
        echo "<tr><td><b>Prevent username removal:</b></td><td>";
        if ((int) $user->flags & 0x0020) { // new users.flags (NOPURGE 0x0020)
            echo "<font color=#" . $cTheme->main_yes . "><b>Yes</b></font>&nbsp;(username will NOT be caught by the automatic removal)";
        } else {
            echo "<font color=#" . $cTheme->main_no . "><b>No</b></font>&nbsp;(username can be removed for being idled too much)";
        }
        echo "</td></tr>\n";
    }
    if ($user->public_key != "") {
        echo(" <tr><td colspan=2><b>Public Key:</b><br>");
        echo(" <pre>" . str_replace("\\\"", "\"", $user->public_key) . "</pre><br></td></tr>");
    }
} else { // $edit mode
    if (($admin >= 800) || (acl(XSUSPEND_USR) && acl(XUNSUSPEND_USR))) {
        if ((int) $user->flags & 1) {
            $chkd1 = " checked";
            $chkd2 = "";
        } else {
            $chkd2 = " checked";
            $chkd1 = "";
        }
        echo "<tr><td valign=top><font size=-1><b>Suspend Account</b></td><td valign=top>[<input type=radio name=suspend_user" . $chkd2 . " value=no> No]&nbsp;&nbsp;&nbsp;[<input type=radio name=suspend_user" . $chkd1 . " value=yes> Yes]&nbsp;";
        if ($chkd1 == "") {
            echo "Reason: <input type=text name=suspendreason size=30 value=\"\" maxlength=255>";
        }
        echo "</td></tr>";
    } else {
        if (acl(XUNSUSPEND_USR)) { // unsuspend ability only
            if ((int) $user->flags & 1) {
                echo("<tr><td valign=top><font size=-1><b>Unsuspend Account</b></td><td valign=top><input type=radio name=suspend_user value=no>Yes unsuspend the account<br><input type=radio name=suspend_user checked value=null>don't touch suspension status</td></tr>");
            } else { // user is not suspended, nothing to do !
                echo "<tr><td valign=top><font size=-1><b>Account is NOT suspended</b></td><td valign=top>nothing to be done</td></tr>";
            }
        }
        if (acl(XSUSPEND_USR)) { // suspend ability only
            if ((int) $user->flags & 1) { // user is already suspended, nothing to do!
                echo "<tr><td valign=top><font size=-1><b>Account is already suspended</b></td><td valign=top>nothing to be done</td></tr>";
            } else {
                echo("<tr><td valign=top><font size=-1><b>Suspend Account</b></td><td valign=top><input type=radio name=suspend_user value=yes>Yes suspend the account, with reason <input type=text name=suspendreason size=30 value=\"\" maxlength=255><br><input type=radio name=suspend_user checked value=null>don't touch suspension status</td></tr>");
            }
        }
    }

    if ($nrw_lvl > 0 || $admin >= $min_lvl) {
        $ttu = pg_safe_exec("SELECT * FROM noreg WHERE type=4 and lower(user_name)='" . strtolower($user->user_name) . "'");
        if (pg_numrows($ttu) > 0 || ((int) $user->flags & 0x0008)) {
            $chkd0 = "";
            $chkd1 = "checked";
            if (pg_numrows($ttu) > 0) {
                $tto = pg_fetch_object($ttu, 0);
                $fr = $tto->reason;
            } else {
                $fr = "";
            }
        } else {
            $chkd0 = "checked";
            $chkd1 = "";
            $fr = "";
        }
        echo "<tr><td valign=top><font size=-1><b>Username Fraud</b></td><td valign=top>[<input type=radio $chkd0 name=ufraud value=1> No]&nbsp;&nbsp;&nbsp;[<input type=radio $chkd1 name=ufraud value=2> Yes]&nbsp;Reason: <input type=text name=fraudreason size=30 value=\"" . str_replace("\"", "&quot;", $fr) . "\" maxlength=255></td></tr>";
    }

    if ($admin >= $min_lvl) {
        $ttnru = pg_safe_exec("SELECT * FROM noreg WHERE type<4 AND never_reg=1 AND lower(user_name)='" . strtolower($user->user_name) . "'");
        if (pg_numrows($ttnru) > 0) {
            $chkd0 = "";
            $chkd1 = "checked";
            $ttnruo = pg_fetch_object($ttnru, 0);
            $nrur = $ttnruo->reason;
        } else {
            $chkd0 = "checked";
            $chkd1 = "";
            $nrur = "";
        }
        echo "<tr><td valign=top><font size=-1><b>Username Never Reg</b></td><td valign=top>[<input type=radio $chkd0 name=unreg value=1> No]&nbsp;&nbsp;&nbsp;[<input type=radio $chkd1 name=unreg value=2> Yes]&nbsp;Reason: <input type=text name=unregreason size=30 value=\"" . str_replace("\"", "&quot;", $nrur) . "\" maxlength=255></td></tr>";

        $ttnre = pg_safe_exec("SELECT * FROM noreg WHERE type<4 AND never_reg=1 AND lower(email)='" . strtolower($user->email) . "'");
        if (pg_numrows($ttnre) > 0) {
            $chkd0 = "";
            $chkd1 = "checked";
            $ttnreo = pg_fetch_object($ttnre, 0);
            $erur = $ttnreo->reason;
        } else {
            $chkd0 = "checked";
            $chkd1 = "";
            $erur = "";
        }
        echo "<tr><td valign=top><font size=-1><b>E-Mail Never Reg</b></td><td valign=top>[<input type=radio $chkd0 name=enreg value=1> No]&nbsp;&nbsp;&nbsp;[<input type=radio $chkd1 name=enreg value=2> Yes]&nbsp;Reason: <input type=text name=enregreason size=30 value=\"" . str_replace("\"", "&quot;", $erur) . "\" maxlength=255></td></tr>";
    }


    if ($admin > 0 || acl(XAT_CAN_EDIT)) {
        if (LOCK_STAR_MAILEDIT && $isAdmin && $id != $user_id && $admin < 800) {
            echo("<tr><td valign=top><b>Email1:</b></td><td><input type=hidden name=email size=20 maxlength=128 value=" . $user->email . ">" . $user->email . "</td></tr>"); // Only show for self or admin
        } else {
            if (!acl(XAT_CAN_EDIT) && ($admin < $edit_lvl))
                echo("<tr><td valign=top><b>Email:</b></td><td>" . $user->email . "</td></tr>");
            else
                echo("<tr><td valign=top><b>Email:</b></td><td><input type=text name=email size=20 maxlength=128 value=" . $user->email . "></td></tr>"); // Only show for self or admin
        }
        if ($admin >= 800) {
            $user_sess_timeout = get_custom_session($id);
            echo ("<tr><td><b>Websession timout:</b></td><td><select id=\"sess_timout\" name=\"sess_timout\">");
            //.get_custom_session($id)."</td></tr>");  check_selected()
            for ($sc = 0; $sc < count(DEFAULT_SESS_TIMEOUT); $sc++) {
                echo "<option ";
                if ($user_sess_timeout == DEFAULT_SESS_TIMEOUT[$sc])
                    echo "selected ";
                echo "value=\"" . DEFAULT_SESS_TIMEOUT[$sc] . "\" > " . formatSeconds(DEFAULT_SESS_TIMEOUT[$sc]) . " </option>
                		";
            }
            echo ("</select></td></tr>");
        }
    }
    else if ($id == $user_id) {
        echo("<tr><td valign=top><b>Email:</b></td><td><input type=hidden name=email size=20 maxlength=128 value=" . $user->email . ">" . $user->email . "</td></tr>"); // Only show for self or admin
    }
    if (!acl(XAT_CAN_EDIT) && ($admin < $edit_lvl))
        echo("<tr><td valign=top><b>Homepage:</b></td><td>$user->url</td></tr>");
    else
        echo("<tr><td valign=top><b>Homepage:</b></td><td><input type=text name=url size=30 maxlenght=128 value=\"$user->url\"></td></tr>");

    $res = pg_safe_exec("select * from languages order by name");
    $autoselect = $user->language_id;
    echo "<tr><td valign=top><b>Language:</b></td>";
    if (acl(XAT_CAN_EDIT) || ($admin >= $edit_lvl)) {
        echo "<td><select name=language_id>\n";
        for ($x = 0; $x < pg_numrows($res); $x++) {
            $language = pg_fetch_object($res, $x);
            echo "<option ";
            if ($language->id == $autoselect) {
                echo "selected ";
            }
            echo "value=\"";
            echo $language->id;
            echo "\">" . $language->name . "</option>\n";
        }
        echo "</select>\n";
    } else {
        $res = pg_safe_exec("select * from languages where id=$autoselect");
        $language = pg_fetch_object($res, $x);
        echo "<td>" . $language->name;
    }
    echo "</td></tr>\n";
    // edit TOTP
    if (TOTP_ON) {
        if (($admin >= TOTP_RESET_LVL) || ((acl(XIPR_VIEW_OWN)) && (!$isAdmin)) || ((acl(XTOTP_DISABLE_OTHERS) && (!$isAdmin)))) {
            echo "<tr><td> <b>Two-step verification:</b></td><td> ";

            if (has_totp($user->id)) {
                $totp_enabled = true;
                echo "<select id=\"totp\" name =\"totp\"> <option selected value=\"on\"> Enabled </option><option value=\"off\"> Disabled </option></select></td></tr>";
            } else {
                echo " Disabled </td></tr>";
            }

            //            if ($totp_enabled) {
                echo "<tr><td><b>Two-step verification IPR check:</b></td><td>";
                if ($user->flags & TOTP_ADMIN_IPR_FLAG) {
                    echo "<select id=\"totp_ipr\" name =\"totp_ipr\"> <option selected value=\"on\"> Enabled </option><option value=\"off\"> Disabled </option></select></td></tr>";
                } else {
                    echo "<select id=\"totp_ipr\" name =\"totp_ipr\"> <option selected value=\"on\"> Enabled </option><option value=\"off\" selected> Disabled </option></select></td></tr>";
                }
                //}
        }
        else {
            $totp_status = $totp_ipr_status = 'Disabled';
            if (has_totp($user->id)) {
                $totp_enabled = true;
                if ($user->flags & TOTP_ADMIN_IPR_FLAG) {
                    $totp_ipr_status = 'Enabled';
                }
            }
            printf("<tr><td><b>Two-step verification:</b> </td><td> %s </td></tr>", $totp_status);
            printf("<tr><td><b>Two-step verification IPR check:</b> </td><td> %s </td></tr>", $totp_ipr_status);
       }

    }
    if (ENABLE_NOTES && ((NOTES_ADMIN_ONLY && $admin > 0) || (NOTES_ADMIN_ONLY == 0))) {
        echo "<tr><td valign=top><b>Accept notes:</b></td>";
        echo "<td>";
        $cyes = "";
        $cno = "";
        if ((int) $user->flags & 0x0010) {
            $cno = " checked";
        } else {
            $cyes = " checked";
        }
        echo "<input type=radio name=noteacc value=1" . $cyes . "> <font color=#" . $cTheme->main_yes . "><b>Yes</b></font>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<input type=radio name=noteacc value=2" . $cno . "> <font color=#" . $cTheme->main_no . "><b>No</b></font> (only from reply)";
        echo "</td></tr>\n";
    }

    if ($admin >= NOPURGE_USER_EDITLEVEL) {
        echo "<tr><td><b>Prevent username removal:</b></td><td>";
        $cyes = "";
        $cno = "";
        if ((int) $user->flags & 0x0020) {
            $cyes = " checked";
        } else {
            $cno = " checked";
        }
        echo "<input type=radio name=noexpiry value=1" . $cyes . "> <font color=#" . $cTheme->main_yes . "><b>Yes</b></font>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<input type=radio name=noexpiry value=2" . $cno . "> <font color=#" . $cTheme->main_no . "><b>No</b></font>";
        echo "&nbsp;(If this is set to 'Yes', username will NOT be caught by the automatic removal)</td></tr>\n";
    } else { // show it only if level is enough.
        if ($admin >= NOPURGE_USER_VIEWLEVEL) {
            echo "<tr><td><b>Prevent username expiry:</b></td><td>";
            if ((int) $user->flags & 0x0020) { // new users.flags (NOPURGE 0x0020)
                echo "<font color=#" . $cTheme->main_yes . "><b>Yes</b></font>&nbsp;(username will NOT be caught by the automatic removal)";
            } else {
                echo "<font color=#" . $cTheme->main_no . "><b>No</b></font>&nbsp;(username can be removed for being idled too much)";
            }
            echo "</td></tr>\n";
        }
    }

    echo(" <tr><td valign=top><b>Public Key:</b></td><td>");
    if (acl(XAT_CAN_EDIT) || ($admin >= $edit_lvl))
        echo(" <textarea name=public_key cols=20 rows=3>" . $user->public_key . "</textarea><br></td></tr>");
    else
        echo($user->public_key . "<br></td></tr>");
}
if (!$edit && ($admin > 0 || has_acl($user_id)) && (
        $admin >= 800 ||
        (acl(XIPR_MOD_OWN) && $user_id == $user->id) ||
        (acl(XIPR_VIEW_OWN) && $user_id == $user->id) ||
        (acl(XIPR_MOD_OTHERS) && $isAdminLvl < $admin) ||
        (acl(XIPR_VIEW_OTHERS) && $isAdminLvl < $admin)
        )) {
    // not in user edit mode .. separate page for modifications ...
    if ($admin > 0 || has_acl($user->id)) { //only if the table is present... and the user has a need for IPR entries
        echo("<tr><td colspan=2 bgcolor=#" . $cTheme->table_sepcolor . "><font size=-1 color=#" . $cTheme->table_septextcolor . "><em><b>Access IP restrictions</b> (ACL+)</em></td></tr>");
        echo "<TR><TD colspan=2><font size=-1><b>";
        // function matches_wild($string_to_match,$wildcard_mask)
        echo "This user is restricted to login on the following IP/Host masks :</b><br>\n";
        echo "<form name=dummy><ul>";
        $rip = @pg_safe_exec("SELECT * FROM ip_restrict WHERE user_id='" . (int) $user->id . "'");
        if ($rip) {
            $amask = 0;
            while ($ripo = @pg_fetch_object($rip)) {
                $amask++;
                echo "<li> ";
                echo $ripo->value;
                echo "\n";
            }
            if ($amask == 0) {
                echo "<li> <i>none</i>\n";
            }
        }
        echo "</ul>";
        echo "<br><br>";
        if (acl(XIPR_MOD_OTHERS) || ($user_id == (int) $user->id && acl(XIPR_MOD_OWN))) {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            echo "<input type=button value=\"Change\" onClick=\"location.href='ip_restrict.php?user_id=" . (int) $user->id . "';\">\n";
        }
        echo "</form></font></td></tr>";
    }
}

echo "<tr><td colspan=2 bgcolor=#" . $cTheme->table_sepcolor . "><font size=-1 color=#" . $cTheme->table_septextcolor . "><em><b>Account Information</b></em></td></tr>";
$r = pg_safe_exec("select * from users_lastseen where user_id='" . $user->id . "'");
if (pg_numrows($r) > 0) {
    $lsuser = pg_fetch_object($r, 0);
    $missingrec = 0;
} else {
    $lsuser = "";
    $missingrec = 1;
}

if ($admin > 0 || $user->id == $user_id) {
    echo("<tr bgcolor=#ffffff><td><b>Created On: </b></td><td>" . cs_time($user->created_ts) . "</td></tr>");
}

if ($admin > 0) {

    if (preg_match("/^forgotten.password./", $user->last_updated_by)) { // forgotten passord...
        if ($admin < SHOW_IP_LEVEL) { // non SHOW_IP_LEVEL+ admin, hide IP in 'forgotten password'.
            $blah = trim(preg_replace("/\((.*)\)/", "", $user->last_updated_by));
            echo("<tr bgcolor=#ffdddd><td><b>Last Updated</b> (*1+)</td><td>" . cs_time($user->last_updated) . " by " . $blah . "</td></tr>");
        } else {
            echo("<tr bgcolor=#ffdddd><td><b>Last Updated</b> (*" . SHOW_IP_LEVEL . "+)</td><td>" . cs_time($user->last_updated) . " by " . $user->last_updated_by . "</td></tr>");
        }
    } elseif ($isAdmin || ($user->flags & 256)) { // * account, or OPERFLAG set account
        if ($admin < SHOW_IP_LEVEL) {
            $blah_f = remove_ip($user->last_updated_by);
            echo("<tr bgcolor=#ffdddd><td><b>Last Updated</b> (*1+)</td><td>" . cs_time($user->last_updated) . " by " . $blah_f . "</td></tr>");
        } else {
            echo("<tr bgcolor=#ffdddd><td><b>Last Updated</b> (*" . SHOW_IP_LEVEL . "+)</td><td>" . cs_time($user->last_updated) . " by " . $user->last_updated_by . "</td></tr>");
        }
    } else {
        echo("<tr bgcolor=#ffdddd><td><b>Last Updated</b> (*1+)</td><td>" . cs_time($user->last_updated) . " by " . $user->last_updated_by . "</td></tr>");
    }
}

if ($admin > 0 || $user->id == $user_id) {
    echo("<tr><td><b>Last Seen</b></td><td>" . cs_time($lsuser->last_seen) . "");
    if ($admin > 0) {
        if ($admin >= 800 && $missingrec) {
            $tts = time();
            $tcrc = md5(CRC_SALT_0013 . $user->id . $tts);
            echo " (missing record) : <input type=button value=\"Fix user\" onClick=\"location.href='r_lastseen.php?id=" . $user->id . "&ts=" . $tts . "&crc=" . $tcrc . "'\">";
        } else {
            echo ", <b>Last Hostmask :</b> ";
            if (trim($lsuser->last_hostmask) != "") {
                if ($admin < SHOW_IP_LEVEL && ($isAdmin || ($user->flags & 256))) { // * account, or OPERFLAG set account
                    echo remove_ip($lsuser->last_hostmask);
                } else {
                    echo $lsuser->last_hostmask;
                }
            } else {
                echo "<i>never logged in on IRC</i>";
            }
        }
    }
    echo("</td></tr>");
}
if ($admin > 0) {
    if ($user->tz_setting != "") {
        echo "<tr><td><b>User's Timezone</b></td><td>" . $user->tz_setting . "&nbsp;</td></tr>\n";
    } else {
        echo "<tr><td><b>User's Timezone</b></td><td><b>** NOT SET **</b></td></tr>\n";
    }
    if ($edit) {
        if ($admin >= MOD_MAXLOGINS_LEVEL) {
            echo "<tr><td><b>User's Max Logins</b></td><td>";
            echo "<select name=maxlogins>";
            for ($ml = 1; $ml <= MAX_MAXLOGINS; $ml++) {
                echo "<option ";
                if ($ml == ($user->maxlogins + 0)) {
                    echo "selected ";
                }
                echo "value=" . $ml . ">" . $ml . "</option>\n";
            }
            echo "</select>";
            echo "&nbsp;</td></tr>\n";
        } else {
            echo "<tr><td><b>User's Max Logins</b></td><td>" . ($user->maxlogins + 0) . "&nbsp;</td></tr>\n";
        }
    } else {
        if ((ALLOW_SELF_MAXLOGINS == 1) && ($user_id == $user->id)) {
            $can_set_max_logins = time_to_next_max_logins($user->signup_ts);
            $current_max_logins = user_max_logins($user->signup_ts);

            echo "</form><tr><td><b>User's Max Logins</b></td><td><form method=\"post\" action=\"up_maxlogins.php\"><strong>" . ($user->maxlogins + 0) . "</strong>. Change to ";
            echo "<select name=\"maxlogins\" id=\"maxlogins\">";
            for ($ml = 1; $ml <= $current_max_logins['max_logins']; $ml++) {
                echo "<option ";
                if ($ml == ($user->maxlogins + 0)) {
                    echo "selected ";
                }
                echo "value=" . $ml . ">" . $ml . "</option>\n";
            }
            echo "</select>";
            echo "&nbsp;<input type=\"submit\" value=\"Confirm and set\"/></form>" . $can_set_max_logins . "</td></tr>\n";
        } else {
            echo "<tr><td><b>User's Max Logins</b></td><td>" . ($user->maxlogins + 0) . "&nbsp;</td></tr>\n";
        }
    }

    $ENABLE_COOKIE_TABLE = 1;
    $res = pg_safe_exec("SELECT * FROM webcookies WHERE user_id='" . $user->id . "' AND expire>=now()::abstime::int4");
    if (pg_numrows($res) > 0 && $user_id != $user->id) {
        echo "<tr><td colspan=2><font color=#" . $cTheme->main_yes . "><b>User is currently logged in on the website</b></font></td></tr>\n";
    }
    $ENABLE_COOKIE_TABLE = 0;

    if ($isAdmin) {
        echo "<tr><td><b>User's DISABLEAUTH setting</b></td><td>";
        if ($edit) {
            if ((int) $user->flags & 64) {
                echo "<font color=#" . $cTheme->main_yes . "><b>ON";
            } else {
                echo "<font color=#" . $cTheme->main_no . "><b>OFF";
            }
            echo "</b></font></td></tr>\n";
            /*
              if ((int)$user->flags & 64) { $ps1 = "selected "; $ps2 = ""; } else { $ps1 = ""; $ps2 = "selected "; }
              echo "<select name=dauth>";
              echo "<option " . $ps1 . "value=1>ON</option>\n";
              echo "<option " . $ps2 . "value=2>OFF</option>\n";
              echo "</select>\n";
              echo "</td></tr>\n";
             */
        } else {
            if ((int) $user->flags & 64) {
                echo "<font color=#" . $cTheme->main_yes . "><b>ON";
            } else {
                echo "<font color=#" . $cTheme->main_no . "><b>OFF";
            }
            echo "</b></font></td></tr>\n";
        }
        if ($admin >= 800) { // new
            echo "<tr><td><b>User's ALUMNI setting</b></td><td>";
            if ($edit && $user_id != $user->id) {
                if ((int) $user->flags & 128) {
                    $ps1 = "selected ";
                    $ps2 = "";
                } else {
                    $ps1 = "";
                    $ps2 = "selected ";
                }
                echo "<select name=alumni>";
                echo "<option " . $ps1 . "value=1>ON</option>\n";
                echo "<option " . $ps2 . "value=2>OFF</option>\n";
                echo "</select>\n";
                echo "</td></tr>\n";
            } else {
                echo "<input type=hidden name=alumni value=";
                if ((int) $user->flags & 128) {
                    echo "1>";
                    echo "<font color=#" . $cTheme->main_yes . "><b>ON";
                } else {
                    echo "2>";
                    echo "<font color=#" . $cTheme->main_no . "><b>OFF";
                }
                echo "</b></font></td></tr>\n";
            }
        }
    }
    if ($admin >= 800) { // new
        echo "<tr><td><b>User's OPER setting</b></td><td>";
        if ($edit) {
            if ((int) $user->flags & 256) {
                $ps1 = "selected ";
                $ps2 = "";
            } else {
                $ps1 = "";
                $ps2 = "selected ";
            }
            echo "<select name=ircop>";
            echo "<option " . $ps1 . "value=1>ON</option>\n";
            echo "<option " . $ps2 . "value=2>OFF</option>\n";
            echo "</select>\n";
            echo "</td></tr>\n";
        } else {
            if ((int) $user->flags & 256) {
                echo "<font color=#" . $cTheme->main_yes . "><b>ON";
            } else {
                echo "<font color=#" . $cTheme->main_no . "><b>OFF";
            }
        }
        echo "</b></font></td></tr>\n";
    }
}
if (!$edit) {
    if ($admin > 0 || acl(XAT_CAN_EDIT)) {
        echo "<tr><td><b>Can post next form on</b></td><td>";
        if ($user->post_forms > 0 && $user->post_forms > time()) {
            echo "" . cs_time($user->post_forms) . "";
        } else {
            if ($user->post_forms == 666) { // NEVER !@#
                echo "<b>Never</b>";
            } else {
                echo "<b>Any Time</b>";
            }
        }
        echo "</td></tr>\n";
    }
} else {
    echo "<tr><td><b>Can post next form on</b></td><td>";
    if ($user->post_forms > 0 && $user->post_forms > time()) {
        echo "" . cs_time($user->post_forms) . "";
    } else {
        if ($user->post_forms == 666) { // NEVER !@#
            echo "<b>Never</b>";
        } else {
            echo "<b>Any Time</b>";
        }
    }
    echo "&nbsp;&nbsp;";
    if ($admin >= 600) {
        echo "Change it to : <select name=chg_formpost>";
        echo "<option value=0 selected>Don't change it</option>\n";
        echo "<option value=1>Any time (unlocked)</option>\n";
        echo "<option value=2>Never (locked)</option></select>\n";
    } else {
        echo "<input type=hidden name=chg_formpost value=0>\n";
    }
    echo "</td></tr>\n";
}
if (!$edit || $admin < 800) {
    if ($user->question_id == "" || $user->question_id == 0) {
        echo "<tr><td><b>Verification question</b></td><td>** NOT SET **</td></tr>";
    } else {
        echo "<tr><td><b>Verification question</b></td><td>" . $question_text[$user->question_id] . "</td></tr>";
    }
    if ((ALLOW_SELF_MAXLOGINS == 1) && ($admin == 0) && ($user_id == $user->id)) {
        $can_set_max_logins = time_to_next_max_logins($user->signup_ts);
        $current_max_logins = user_max_logins($user->signup_ts);

        echo "<tr><td valign=\"top\"><b>User's Max Logins</b></td><td><form method=\"post\" action=\"up_maxlogins.php\"><strong>" . ($user->maxlogins + 0) . "</strong>. Change to ";
        echo "<select name=\"maxlogins\" id=\"maxlogins\">";

        for ($ml = 1; $ml <= $current_max_logins['max_logins']; $ml++) {
            echo "<option ";
            if ($ml == ($user->maxlogins + 0)) {
                echo "selected ";
            }
            echo "value=" . $ml . ">" . $ml . "</option>\n";
        }
        echo "</select>";
        echo "&nbsp;<input type=\"submit\" value=\"Confirm and set\"/></form>" . $can_set_max_logins . "</td></tr>\n";
    } else {
        echo "<tr><td><b>User's Max Logins</b></td><td>" . ($user->maxlogins + 0) . "</td></tr>\n";
    }

    if (!REGPROC_ALLOWMULTIPLE) {
        $next_channel = time_next_channel($user->signup_ts);
        echo "<tr><td valign=\"top\"><b>Channel manager max limit</b></td><td>" . user_channel_limit($user->id);

        if ($next_channel) {
            echo "<span style=\"font-style: italic; padding-left: 50px;\">You need to wait " . seconds2human($next_channel['seconds_next_channel']) . " before you can register {$next_channel['max_channels']} channel(s).</span>";
        }
        echo "</td></tr>";
    }

    if ($canview == 1) {
        echo "<tr bgcolor=#" . $cTheme->table_tr_enlighten3 . "><td valign=top>";
        echo "<font color=#" . $cTheme->main_warnmsg . "><b>" . BOT_NAME . "@Team/600+ view only</b></font></td>";
        echo "<td valign=top>Secret answer is : ";
        if ($user->id == $user_id || $admin > 0) {
            if (trim($user->verificationdata) != "") {
                echo "<font color=#" . $cTheme->table_sepcolor . "><b>" . $user->verificationdata . "</b>";
                if ($admin >= $min_lvl) {
                    if (is_locked_va($user->verificationdata)) {
                        echo "&nbsp;<i>(locked VA)</i>";
                    }
                }
                echo "</font>";
                if (preg_match("/ /", $user->verificationdata)) {
                    echo "<br><b>DISPLAY WARNING</b> secret answer contains litteral space(s) : <font color=#999999><big>";
                    echo str_replace(" ", "<font color=#ff00ff>_</font>", $user->verificationdata);
                    echo "</big></font>";
                }
                if ($admin >= 750) { // same level as for searching on VA on Admin reports ...
                    echo "<br><a href=\"admin/verifdatacheck.php?posted=1&vanswer=" . str_replace("
", "%20", $user->verificationdata) . "&rid=" . $id . "\"><font color=#808080 size=-1><b>&gt;&gt; List all users using this verification answer</b>
(*750+)</font></a>.";
                }
                echo "\n";
            } else {
                echo "<font color=#" . $cTheme->main_textcolor . "><b>** NOT SET **</b></font>";
            }
        } else {
            echo "<i>hidden</i>";
        }
        echo "</td></tr>\n";
    }
} else {
    echo "<tr><td><b>Verification question</b></td><td>";
    if ($user->question_id == "" || $user->question_id == 0) {
        $autoselect = 0;
    } else {
        $autoselect = $user->question_id;
    }
    echo "<select name=question_id>\n";
    for ($x = 0; $x <= $max_question_id; $x++) {
        echo "<option ";
        if ($autoselect == $x) {
            echo "selected ";
        }
        echo "value=$x>";
        if ($x == 0) {
            echo "--- pick a question ---";
        } else {
            echo $question_text[$x];
        }
        echo "</option>\n";
    }
    echo "</select>\n";
    echo "</td></tr>";
    echo "<tr><td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The answer to that question....</b></td><td>";
    echo "<input type=text name=verificationdata size=30 maxlength=30 value=\"" . $user->verificationdata . "\"></td></tr>\n";
}
echo "</table><br><br>";

//echo $isAdmin . " " . $isAdminLvl . "<br>";

if (( ($admin >= $edit_lvl || acl(XAT_CAN_EDIT) || acl(XTOTP_DISABLE_OTHERS)) && ((($isAdminLvl > 0 && $admin > $isAdminLvl) || $admin == 1000 || $id == $user_id) || $isAdmin == 0))) {

    // You can edit a user account if:
    // 1) you are a >= $edit_lvl admin (see top of file),
    // 2) or you have XAT_CAN_EDIT flags set in an ACL for you UNLESS
    // 3) user has higher admin access than you.
    // TODO: Allow editing and saving changes.. fair bit of stuff to do
    if (!$edit) {

    } else {
        echo "<input type=submit value=\" SAVE CHANGES \">\n";
    }
    echo "<input type=hidden name=id value=\"" . (int) $id . "\">\n";
    if ($admin > 0)
        echo "</form>\n";
    if (!$edit) {
        echo "<form method=GET>";
        echo "<input type=\"hidden\" name=\"id\" value=\"" . (int) $id . "\">";
        echo "<input type=\"hidden\" name=\"edit\" value=\"true\">";
        echo "<input type=\"submit\" value=\"Edit User\">";
        if ($update == 2) {
            echo "&nbsp;&nbsp;<font color=#" . $cTheme->main_warnmsg . "><b>FAILED User update !!!</b></font>";
        }
        if ($fc == md5($id . 1 . CRC_SALT_0013)) {
            echo "&nbsp;&nbsp;<font color=#" . $cTheme->main_warnmsg . "><b>You are not allowed to edit that user.</b></font>";
        }
        if ($fc == md5($id . 2 . CRC_SALT_0013)) {
            echo "&nbsp;&nbsp;<font color=#" . $cTheme->main_warnmsg . "><b>You can only edit admins with an access strictly lower than yours.</b></font>";
        }
        if ($fc == md5($id . 3 . CRC_SALT_0013)) {
            echo "&nbsp;&nbsp;<font color=#" . $cTheme->main_warnmsg . "><b>Non admins can't edit people other than themselves.</b></font>";
        }
        if ($update == 1) {
            echo "&nbsp;&nbsp;<font color=#" . $cTheme->main_warnmsg . "><b>User successfully updated</b></font>";
        }
        echo "</form>";
    } else {
        echo "<form method=get action=users.php>";
        echo "<input type=\"hidden\" name=\"id\" value=\"" . (int) $id . "\">";
        echo "<input type=\"submit\" value=\"Back to 'View' mode\">";
        echo "</form>";
    }
}


if (!$edit && $user_id == $id && ENABLE_NOTES && ((NOTES_ADMIN_ONLY && $admin > 0) || (NOTES_ADMIN_ONLY == 0))) {
    echo "<form>";
    $codenotes = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    $notesr = pg_safe_exec("SELECT message_id FROM notes WHERE user_id='" . $user_id . "'");
    if (pg_numrows($notesr) > 0) {
        $codenotes .= "<input type=button value=\"Read Notes (" . pg_numrows($notesr) . ")\" onClick=\"location.href='notes/index.php';\">&nbsp;&nbsp;";
    }

    $notesc = pg_safe_exec("SELECT message_id FROM notes WHERE from_user_id='" . $user_id . "'");
    if (NOTES_LIM_TOTAL == 0 || pg_numrows($notesc) <= NOTES_LIM_TOTAL) {
        $codenotes .= "<input type=button value=\"Write Note\" onClick=\"location.href='notes/new.php';\">";
    }
} else {
    $codenotes = "";
}

/*
  if ($r_admin>0 && $id==$user_id) { // you can only set it for yourself :P
  if ($uuflags & 64) { // DISABLEAUTH is ON
  echo "DISABLEAUTH is set to ON, <a href=\"disable_auth.php?go=OFF\">turn it OFF</a>.<br><br>\n";
  } else { // DISABLEAUTH is OFF
  echo "DISABLEAUTH is set to OFF, <a href=\"disable_auth.php?go=ON\">turn it ON</a>.<br><br>\n";
  }
  }
 */

if ($id == $user_id && $USER_TZ != "") {
    echo "<a href=\"timezone.php\">Change your current timezone</a> ($USER_TZ)";
    echo $codenotes;
    echo "<br><br>\n";
} else {
    if ($id == $user_id && $USER_TZ == "") {
        echo "<a href=\"timezone.php\">Change your current timezone</a> (none)";
        echo $codenotes;
        echo "<br><br>\n";
    }
}


if (!$edit && $user_id == $id && ENABLE_NOTES && ((NOTES_ADMIN_ONLY && $admin > 0) || (NOTES_ADMIN_ONLY == 0))) {
    echo "</form>\n";
}

if ($admin > 0 || acl(XLOGGING_VIEW)) {
    if ($admin >= 600 && !$edit) {
        echo "<form name=admcmt method=post action=admin_user_comment.php>\n";
        echo "<input type=hidden name=uid value=$id>";
        echo "Add an admin comment to that user : <b>requires level 600+</b><br><input type=text name=admcmt size=50 maxlength=255>&nbsp;<input type=submit value=\" Add \"></form>\n";
    }
    echo "<br>\n";
    $query = "SELECT user_id,ts,event,message FROM userlog WHERE event=5 AND user_id=" . $id . " ORDER BY ts DESC";
    //	echo "Q: $query<br>";
    $logs = pg_safe_exec($query);
    if (pg_numrows($logs) != 0) {
        echo "<TABLE border=1 WIDTH=100% cellspacing=0 cellpadding=2 BGCOLOR=#" . $cTheme->table_bgcolor . ">";
        echo "<tr><td colspan=3><H2>Admin comments</H2></td></tr>";
        echo "<tr bgcolor=#" . $cTheme->table_headcolor . "><td><font color=#" . $cTheme->table_headtextcolor . ">Time</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Message</font></td>";
        if ($admin >= 800) {
            echo "<td><font color=#" . $cTheme->table_headtextcolor . ">Action</font></td>";
        }
        echo "</tr>";
        $max = pg_numrows($logs);
        for ($row = 0; $row < $max; $row++) {
            $log = pg_fetch_object($logs, $row);
            echo("<tr><td>");
            echo(cs_time($log->ts) . " [" . $log->ts . "]</td><td>");
            echo(htmlentities($log->message) . "</td>\n");
            if ($admin >= 800) {
                echo "<td><a href=\"admin_user_comment.php?uid=" . $id . "&ts=" . $log->ts . "&spcmode=remove\">Delete</a></td>\n";
            }
            echo "</tr>";
        }
        echo("</table><br><br>");
    } else {
        echo "There are no admin comments for this user\n<br><br>";
    }
}
if ($edit && $delBtn) { // deletion secured form
    echo "<form name=deleteusername action=wipeuser.php method=post>\n";
    make_secure_form("deleteuser!!!" . CRC_SALT_0008 . $user->user_name);
    echo "<input type=hidden name=username value=\"" . $user->user_name . "\">\n";
    echo "<input type=hidden name=id value=\"" . $user->id . "\">\n";
    echo "</form>\n";
}
if ($admin < 1 && $id != $user_id) {
    //echo("You do not have access to see this users channels");
} else {
    $levels = pg_safe_exec("SELECT " .
            "  channel_id, " .
            "  user_id," .
            "  access," .
            "  levels.flags," .
            "  last_modif," .
            "  suspend_expires,suspend_by," .
            "  name " .
            " FROM " .
            "  levels," .
            "  channels " .
            " WHERE " .
            "  user_id = $id and " .
            " channels.id=levels.channel_id AND channels.registered_ts>0 ORDER BY access DESC");
    echo("
        <TABLE WIDTH=100% border=1 cellspacing=0 cellpadding=2 BORDER=0 BGCOLOR=#" . $cTheme->table_bgcolor . ">
        <tr>
        <td colspan=6>
        <center><h2>Channels</h2></center>
        </td></tr>");
    if (pg_numrows($levels) == 0) {

        echo("<tr bgcolor=#" . $cTheme->table_sepcolor . "><td colspan=5><center><font color=#" . $cTheme->table_septextcolor . " size=-1><b><em>~ This user has no access on any channel ~</b></em></td></tr>");
    } else {

        echo(" <tr bgcolor=#" . $cTheme->table_sepcolor . ">
<td width=250><font color=" . $cTheme->table_septextcolor . " size=-1><b><em>Channel</em></b></font></td>
<td width=70 align=center><font color=" . $cTheme->table_septextcolor . " size=-1><b><em>Access</font></td>
<td width=70 align=center><font color=" . $cTheme->table_septextcolor . " size=-1><b><em>Auto-Op</font></td>
<td width=70 align=center><font color=" . $cTheme->table_septextcolor . " size=-1><b><em>Auto-Voice</font></td>
<td width=70 align=center><font color=" . $cTheme->table_septextcolor . " size=-1><b><em>Auto-Invite</font></td>
<td></td>
</tr>");

        for ($row = 0; $row < pg_numrows($levels); $row++) {

            $level = pg_fetch_object($levels, $row);
            display_level($level, "N", "0", "Y");
        }
    } // of else
    echo("</table>");
}
if ($admin >= 750 || $nrw_lvl > 0) {

    /* start proc */
    $type = array(0 => "Incoming", 1 => "Pending (Traffic Check)", 2 => "Pending (Notification)", 3 => "Accepted", 8 => "Ready for review");
    $req00 = "SELECT channels.id,channels.name,pending.created_ts,pending.status,pending.decision_ts FROM pending,users,channels WHERE pending.channel_id=channels.id AND pending.manager_id=users.id AND pending.manager_id='$id' AND channels.registered_ts=0 AND (pending.status<4 OR pending.status=8) AND (pending.decision_ts=0 OR pending.decision_ts>now()::abstime::int4-86400*5) ORDER BY pending.created_ts DESC";
    $levels = pg_safe_exec($req00);
    echo ("<br>
        <TABLE WIDTH=100% border=1 cellspacing=1 cellpadding=2 BGCOLOR=#" . $cTheme->table_bgcolor . ">
        <tr>
        <td colspan=3>
        <center><h2>Pending Channels with this user as manager</h2></center>
        </td></tr>");
    if (pg_numrows($levels) == 0) {
        echo("<tr bgcolor=#" . $cTheme->main_textlight . "><td colspan=3><center><font color=#" . $cTheme->table_bgcolor . " size=-1><b><em>~ This user has no channel pending registration ~</b></em></td></tr>");
    } else {
        echo(" <tr bgcolor=#" . $cTheme->table_sepcolor . ">
<td width=250><font color=#" . $cTheme->table_septextcolor . " size=-1><b><em>Channel</em></b></font></td>
<td><font color=#" . $cTheme->table_septextcolor . " size=-1><b><em>Since</font></td>
<td><font color=#" . $cTheme->table_septextcolor . " size=-1><b><em>Status</font></td>
<td></td>
</tr>");
        for ($row = 0; $row < pg_numrows($levels); $row++) {
            $rowT = pg_fetch_object($levels, $row);
            $dts = $rowT->decision_ts;
            $sta = $rowT->status;
            $dats = time();
            echo "<tr>\n";
            echo "<td width=250><a href=\"view_app.php?id=" . $rowT->created_ts . "-" . $rowT->id . "\" target=_blank>" . $rowT->name . "</a></td>\n";
            echo "<td>" . cs_time($rowT->created_ts) . "</td>\n";
            echo "<td>" . $type[$sta] . "</td>\n";
            echo "</tr>\n";
        }
    } // of else
    echo "</TABLE>";
    /* end proc */

    /* start proc */
    $type = array(0 => "Incoming", 1 => "Pending (Traffic Check)", 2 => "Pending (Notification)", 3 => "Accepted", 8 => "Ready for review");
    $req00 = "SELECT channels.id,channels.name,pending.created_ts,pending.status,pending.decision_ts,pending.manager_id,supporters.support FROM pending,users,channels,supporters WHERE pending.channel_id=channels.id AND supporters.user_id=users.id AND channels.id=supporters.channel_id AND pending.channel_id=supporters.channel_id AND supporters.user_id='$id' AND channels.registered_ts=0 AND (pending.status<4 OR pending.status=8) AND (pending.decision_ts=0 OR pending.decision_ts>now()::abstime::int4-86400*5) ORDER BY pending.created_ts DESC";
    $levels = pg_safe_exec($req00);
    echo ("<br>
        <TABLE WIDTH=100% border=1 cellspacing=1 cellpadding=2 BGCOLOR=#" . $cTheme->table_bgcolor . ">
        <tr>
        <td colspan=5>
        <center><h2>Pending Channels with this user as supporter</h2></center>
        </td></tr>");
    if (pg_numrows($levels) == 0) {
        echo("<tr bgcolor=#" . $cTheme->main_textlight . "><td colspan=5><center><font color=" . $cTheme->table_bgcolor . " size=-1><b><em>~ This user is not a supporter for any channel at the moment ~</b></em></td></tr>");
    } else {
        echo(" <tr bgcolor=#" . $cTheme->table_sepcolor . ">
<td width=250><font color=#" . $cTheme->table_septextcolor . " size=-1><b><em>Channel</em></b></font></td>
<td><font color=#" . $cTheme->table_septextcolor . " size=-1><b><em>Since</font></td>
<td><font color=#" . $cTheme->table_septextcolor . " size=-1><b><em>Vote</font></td>
<td><font color=#" . $cTheme->table_septextcolor . " size=-1><b><em>Status</font></td>
<td><font color=#" . $cTheme->table_septextcolor . " size=-1><b><em>Applicant</font></td>
<td></td>
</tr>");
        for ($row = 0; $row < pg_numrows($levels); $row++) {
            $rowT = pg_fetch_object($levels, $row);
            $dts = $rowT->decision_ts;
            $sta = $rowT->status;
            $dats = time();
            $res2 = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $rowT->manager_id . "'");
            $row2 = pg_fetch_object($res2, 0);
            echo "<tr>\n";
            echo "<td width=250><a href=\"view_app.php?id=" . $rowT->created_ts . "-" . $rowT->id . "\" target=_blank>" . $rowT->name . "</a></td>\n";
            echo "<td>" . cs_time($rowT->created_ts) . "</td>\n";
            if ($rowT->support == "N") {
                echo "<td>NON SUPPORT</td>\n";
            } else if ($rowT->support == "Y") {
                echo "<td>SUPPORT</td>\n";
            } else {
                echo "<td><i>unknown</i></td>\n";
            }
            echo "<td>" . $type[$sta] . "</td>\n";
            echo "<td><a href=\"users.php?id=" . $rowT->manager_id . "\">" . $row2->user_name . "</a></td>\n";
            echo "</tr>\n";
        }
    } // of else
    echo "</TABLE>";
    /* end proc */
}

if ($admin > 0 || acl(XLOGGING_VIEW)) {
    $type = $user_events;
    echo "<br>\n";
    $query = "SELECT user_id,ts,event,message FROM userlog WHERE event!=5 AND user_id=$id ORDER BY ts DESC";
//	echo "Q: $query<br>";
    $logs = pg_safe_exec($query);
    echo "<TABLE border=1 WIDTH=100% cellspacing=0 cellpadding=2 BGCOLOR=#" . $cTheme->table_bgcolor . ">";
    echo "<tr><td colspan=3><H2>Log messages (last 5)</H2><a href=\"viewlogs.php?uid=" . $id . "\">View All</a></td></tr>";
    echo "<tr bgcolor=#" . $cTheme->table_headcolor . "><td><font color=#" . $cTheme->table_headtextcolor . ">Time</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Event</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Message</font></td></tr>";
    if (pg_numrows($logs) != 0) {
        if (pg_numrows($logs) >= 5) {
            $max = 5;
        } else {
            $max = pg_numrows($logs);
        }
        for ($row = 0; $row < $max; $row++) {
            $log = pg_fetch_object($logs, $row);
            echo("<tr><td>");
            echo(cs_time($log->ts) . " [" . $log->ts . "]</td><td>");
            if ($log->event > count($user_events) || $log->event == 0) {
                echo "(old)</td><td>";
            } else {
                echo($type[$log->event] . "</td><td>");
            }
            if ($admin < SHOW_IP_LEVEL) {
                echo(htmlentities(remove_ip($log->message, 2)) . "</td></tr>\n");
            } else {
                echo(htmlentities($log->message) . "</td></tr>\n");
            }
        }
        echo("</table>");
    } else {
        echo "There are no log messages for this user\n<br><br>";
    }
}
?>
  <div id="twostepdialog" title="Two-step verification">
    <p>Two-step verification adds an extra security layer to your account. Whenever you sign in to the cservice website
 you'll need to enter both your password and also a security code generated by your authenticator app.</p>
  </div>
</body>
</html>
