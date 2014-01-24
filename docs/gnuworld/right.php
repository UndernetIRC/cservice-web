<?
$default_gopage="login.php";
require("../../php_includes/cmaster.inc");
std_connect();
/* $Id: right.php,v 1.15 2005/03/07 04:48:03 nighty Exp $ */
$user_id = std_security_chk($auth);
$admin = std_admin();
$cTheme = get_theme_info();
	if ($user_id > 0) {
		if ($admin>=800 && $normalproceed!=1) {
			if (newusers_off()) {
				if ($LOCKED_BY == "** SYSTEM **") {
				        std_theme_styles(1); std_theme_body();
					echo "<b>800+ NOTIFICATION MESSAGE</b><br><br>\n";
					echo "Newusers have been locked since <b>" . $LOCKED_SINCE . "</b>.<br>\n";
					$unf = pg_safe_exec("SELECT count_count FROM counts WHERE count_type=1");
					if (pg_numrows($unf)==0) {
						$MAX_UCOUNT = 0;
					} else {
						$blo = pg_fetch_object($unf,0);
						$MAX_UCOUNT = $blo->count_count;
					}
					echo "NewUsers Count has reached : <b>" . $MAX_UCOUNT . "</b>.<br><br>\n";
					echo "You can go to <a href=\"acl/index.php\">ACL Manager / Site Control</a> to change this lock.<br>\n";
					echo "You can go to <a href=\"userbrowser/index.php\">User Toaster</a> to review usernames.<br><br>\n";
					echo "You may also <a href=\"right.php?normalproceed=1\">proceed normally your login sequence</a>.<br><br>\n";
					echo "</body></html>\n\n";
					die;
				}
			}

		}
		if ($sba==1 && $admin==0) {
		        std_theme_styles(1); std_theme_body();

		        $myres=pg_safe_exec("SELECT language_id FROM users WHERE id='" . $user_id . "'");
		        if (pg_numrows($myres)==0) { $mylang = 1; } else {
		        	$myrow = pg_fetch_object($myres,0);
		        	$mylang = $myrow->language_id;
		        }

			$mq = pg_safe_exec("SELECT text FROM translations WHERE language_id='" . $mylang . "' AND response_id='9998'");
			echo "<h2>";
			if (pg_numrows($mq)>0) {
				$mo = pg_fetch_object($mq,0);
				echo str_replace("\n","<br>\n",$mo->text);
			} else {
				echo "Remember: Nobody from CService will ever ask you for your password, do NOT give out your password to anyone claiming to be CService.";
			}
			echo "</h2>";
			echo "<br><br>";
			echo "You may now <a href=\"right.php?normalproceed=1\">proceed normally your login sequence</a>.<br><br>\n";
			echo "</body></html>\n\n";
			die;
		}
		$default_gopage = "users.php?id=".$user_id;
		$req00 = "SELECT channels.id,channels.name,pending.created_ts,pending.status,pending.decision_ts,pending.manager_id FROM pending,users,channels,supporters WHERE pending.channel_id=channels.id AND supporters.user_id=users.id AND channels.id=supporters.channel_id AND pending.channel_id=supporters.channel_id AND supporters.user_id='$user_id' AND channels.registered_ts=0 AND pending.status<4 AND ( pending.decision_ts=0 OR pending.decision_ts>now()::abstime::int4-(86400*5) ) AND (supporters.support IS NULL OR supporters.support='?') ORDER BY pending.created_ts DESC";
		$levels = pg_safe_exec($req00);
		if (pg_numrows($levels)>0) { // user has some channels to support.
			if (pg_numrows($levels)>1) { $c_addy = "s"; } else { $c_addy = ""; }
		        header("Pragma: no-cache");
		        std_theme_styles(1); std_theme_body();
			echo "<b>CHANNEL SERVICE REGISTRATION - SUPPORTER PAGE</b><br><br>\n";
			echo "You have been put as a <b>SUPPORTER</b> for the following channel$c_addy :<br><br><h2>\n";
			for ($x=0;$x<pg_numrows($levels);$x++) {
				$row = pg_fetch_object($levels,$x);
				$channel_id = $row->id;
				$channel_name = $row->name;
				echo "<a href=\"regproc/support_decision.php?id=$channel_id\"><b>$channel_name</b></a><br>\n";
			}
			echo "</h2>\n";
			echo "Please click on a channel name to make your decision about that channel support.<br><br>\n";
			echo "</body></html>\n\n";
			die;
		} else { // user doesnt need to confirm any support on any channel
			$req00 = "SELECT channels.id,channels.name,pending.created_ts,pending.status,pending.decision_ts FROM pending,users,channels WHERE pending.channel_id=channels.id AND pending.manager_id=users.id AND pending.manager_id='$user_id' AND channels.registered_ts=0 AND pending.status<9 AND pending.status!=4 AND pending.reg_acknowledged='N' AND (pending.decision_ts=0 OR pending.decision_ts>now()::abstime::int4-86400*5) ORDER BY pending.created_ts DESC";
			$levels = pg_safe_exec($req00);
			if (pg_numrows($levels)>0) { // user has one or more pending applications
					header("Pragma: no-cache");
				        std_theme_styles(1); std_theme_body();
					echo "<b>CHANNEL SERVICE REGISTRATION</b> - YOUR APPLICATION<br><br>\n";
					echo "<form>\n";
					for ($x=0;$x<pg_numrows($levels);$x++) {
						$row = pg_fetch_object($levels,$x);
						$status = $row->status;
						echo "<table border=1 cellspacing=0 cellpadding=2>\n";
						echo "<tr bgcolor=#" . $cTheme->table_bgcolor . ">\n";
						if ($status==3) {
							echo "<td><b>Application number</b></td><td><b>Channel Name</b></td><td><b>Application posted on</b></td><td><b>Channel Status</b></td><td colspan=2><b>Notification date</b></td>\n";
						} else {
							echo "<td><b>Application number</b></td><td><b>Channel Name</b></td><td><b>Application posted on</b></td><td colspan=2><b>Channel Status</b></td>\n";
						}
						echo "</tr>\n";
						echo "<tr>\n";
						switch ($status) {
							case 0:
								$app_status = "Pending Supporter<br>Confirmation";
							break;
							case 1:
								$app_status = "Traffic Checking";
							break;
							case 2:
								$app_status = "Notification";
							break;
							case 3:
								$app_status = "Completed / Registered";
							break;
							case 8:
								$app_status = "Ready for review";
							break;
							default:
								$app_status = "UNKNOWN";
							break;

						}
						echo "<td><a href=\"view_app.php?id=" . $row->created_ts . "-" . $row->id . "\">" . $row->created_ts . "-" . $row->id . "</a></td><td>" . $row->name . "</td><td>" . cs_time($row->created_ts) . "</td><td>$app_status</td><td><b>";

						if ($status<3) { echo "</b><input type=button value=\"CANCEL APPLICATION\" onClick=\"location.href='regproc/cancel_application.php?id=" . $row->created_ts . "&c=" . $row->id . "';\">"; } else {
							if ($status!=8) {
								echo cs_time($row->decision_ts) . "</b></td><td><input type=button value=\"ACKNOWLEDGE\" onClick=\"location.href='regproc/registration_acknowledge.php?id=" . $row->created_ts . "&c=" . $row->id . "';\">";
							} else {
								echo cs_time($row->decision_ts) . "</b></td><td>&nbsp;";
							}
						}
						echo "</td>\n";
						echo "</tr>\n";
						echo "</table>\n";
						if ($status==3) {
							echo "NOTE: applications to be acknowledged lasts 5 days after notification";
						}
						echo "<br><br>\n";
					}
					echo "</form>\n";
				echo "</body></html>\n\n";
				die;
			} else { // user has no pending application
				// any timezone cookie defined ?
				if ($USER_TZ=="" || !isset($USER_TZ)) {
					// obviously not ;P
					header("Location: timezone.php");
					die;
				} else { // check cookie validity
					$blu = pg_safe_exec("SELECT tz_index FROM timezones WHERE tz_name='$USER_TZ' AND deleted!=1");
					if (pg_numrows($blu)==0 || pg_numrows($blu)>1) { // invalid cookie
						header("Location: timezone.php"); // redirect to TZ selection.
						die;
					}
					// good TZ
				}
				header("Location: $default_gopage");
				die;
			}
		}
	} else {
	header("Pragma: no-cache");
        std_theme_styles(1);
        if ($_COOKIE['rlogin']!="") {
        	std_theme_body("","document.forms[0].password.focus();");
        } else {
        	std_theme_body("","document.forms[0].username.focus();");
        }
?>
<center>
<font size=+2><b>Welcome to CService</b></font>
<?

//From here, you are able to add/delete users from your
//channel, submit applications for channels, edit your
//user info, change your channel's settings..<br>

echo "<br>\n";
echo "<table width=\"400\" bgcolor=#" . $cTheme->main_textcolor . ">\n";
echo "<tr><td>\n";
echo "<table cellpadding=5 bgcolor=#" . $cTheme->table_bgcolor . " width=\"100%\">\n";
echo "<tr><td><center>\n";
echo "<font color=#" . $cTheme->main_textcolor . ">\n";
echo "<font size=+2><b>CService Login</b></font>\n";
echo "<form method=post action=login.php" . $tgt . " target=body>\n";
echo "<table border=0><tr><td><font color=\"#" . $cTheme->main_textcolor . "\">Username</td><td><input type=text name=username value=\"";
if (REMEMBER_LOGIN || PREFILL_NOTICE) { echo $_COOKIE['rlogin']; }
echo "\"></td></tr>\n";
echo "<tr><td><font color=\"#" . $cTheme->main_textcolor . "\">Password</td><td><input type=password name=password></td></tr></table>\n";
echo "<br><input type=submit value=Login>\n";
echo "</center>";
if (PREFILL_NOTICE && trim($_COOKIE['rlogin']) != "") {
	echo "<br><table border=0 cellspacing=0 cellpadding=2 bgcolor=#ffff99>";
	echo "<tr><td valign=top bgcolor=#ff9999>";
	echo "<font style=\"font-size: 13px; color: #000000; font-weight: bold;\">";
	echo "For security reasons, the last username to login to the CService page from this computer is displayed in the login form.";
	echo "<br></td></tr></table>\n";
}
echo "</form>";
?>
</td></tr>
</table>
</td>
</tr>
</table>
<?
if ($sba) { // the user prolly have a cookie problem
?>
<br>
<table width=500 border=1 cellspacing=0 cellpadding=10 bgcolor=#<?=$cTheme->table_bgcolor?>>
<tr><td valign=top>
It seems that your web browser is not properly accepting the cookie we sent in order to authenticate you.<br>
Please check the following possible cause of this problem in order to be able to log in :<br>
<ul>
<li><u>Your web browser uses an HTTP proxy that filters out 'cookies' :</u><br>
<i>try to exclude <b><? echo gen_server_url() ?></b> from it</i><br><br>
<li><u>Your web browser has a restrictive 'cookie' policy :</u><br>
<i>lower the security level about (or against) cookies; accepting cookie <b>only sent back to originating server</b> should do it for that website</i><br><br>
<li><u>You time and date is set incorrectly on your local machine :</u><br>
<i>This would make the cookie expire immediately if your unix timestamp is more than one hour
above the cookie default initial expiration, correct your date and time on your system if needed</i>
<br><br>
</ul><br>
If none of this solves your problem, try re-installing your web browser or picking another one ;P<br>
</td></tr></table>
<? } ?>
If you do not have an account, <a href="newuser.php">create one</a> now!
</center>
<?
	}
?>
</body>
</html>
