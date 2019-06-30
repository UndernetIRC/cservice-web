<?
require("../../php_includes/cmaster.inc");
/* $Id: login.php,v 1.29 2005/12/13 11:49:32 nighty Exp $ */
if($loadavg1 >= CRIT_LOADAVG) {
  	header("Location: highload.php");
  	exit;
}
$cTheme = get_theme_info();
header("Pragma: no-cache");
unset($failed); unset($is_admin); unset($user_id);
$failed = 0; $is_admin = 0; $user_id = 0;
// For efficiency, don't load the db unless we have to.
if (RBL_CHECKS==1)
{
$msg=ip_check_rbl(cl_ip());
if ($msg !='clean')
	{
	echo "<html><head><title>SECURITY WARNING</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body();
	echo "<center>\n";
	echo "<h2>";
	echo "Sorry, you're not allowed to login from this IP address. ".$msg;
	echo "</h2>";
	echo "</center>\n";
	echo "</body></html>\n\n";
	die;
	}
}
if ($username != "" && $password != "") {
	if (!ip_check($username,0)) {
		echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n";
		std_theme_styles(1); std_theme_body();
        	echo "<h1>Error<br>\n";
        	echo "Too many failed login attempts for this username.</h1><br>\n";
		echo "</body>\n";
		echo "</html>\n\n";
		die;
        }
	std_connect();
	if ($username!="" && !preg_match(NON_BOGUS,$username)) {
		echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n";
		echo "<html><head><title>ERROR</title>";
		std_theme_styles();
		echo "</head>";
		std_theme_body();
		echo "<center>\n";
		echo "<h2>";
		echo "Bogus username</h2><br><a href=\"login.php\">Try again</a></center></body></html>\n\n";
		die;
	}
	$user_id = chk_password($username,$password,-1);
	if ( $user_id > 0 ) {
		
    		$res = pg_safe_exec("select users.id,users.flags,levels.access from users,levels where users.id=" . (int)$user_id . " and users.id=levels.user_id and levels.channel_id=1 and levels.access>0");
    		if (pg_numrows($res)==0) {
			if (ADMINONLY_MIRROR) {
	                       	echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n";
                        	echo "<html><head><title>This mirror is reserved for CService officials</title>";
				std_theme_styles();
				echo "</head>";
				std_theme_body();
                        	echo "<center>\n";
                        	echo "<h2>";
                        	echo "Sorry, You can't login on that website.";
                        	echo "</h2>";
				echo "<h3>It's currently reserved for CService officials only,<br>";
				echo "You can access a client mirror at : <a href=\"" . CLIENT_MIRROR_URL . "\" target=_top>" . CLIENT_MIRROR_URL . "</a>, thanks.</h3>\n";
                        	echo "</center>\n";
                        	echo "</body></html>\n\n";
                        	die;
			}
			if (site_off()) {
				echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n";
				//echo "<META HTTP-EQUIV=\"Expires\" CONTENT=\"0\">\n";
				echo "<html><head><title>PAGE LOCKED BY CSERVICE ADMINISTRATORS</title>";
				std_theme_styles();
				echo "</head>";
				std_theme_body();
				echo "<center>\n";
				echo "<h2>";
				echo "Sorry, You can't login at the moment.";
				echo "</h2>";
				echo "<a href=./ target=_top>Try Again</a>\n";
				echo "</center>\n";
				echo "</body></html>\n\n";
				die;
			}
			$is_admin = 0;
     		} else {
     			$ouu = pg_fetch_object($res,0);
     			$is_admin = $ouu->access;
     		}
		$val = microtime();
		$daval = explode(" ",$val);
		$microtime = $daval[0]*1000000000;
		$time = $daval[1];
		$cookie=md5(CRC_SALT_0007 . $time . $microtime . $username . $password . $user_id . CRC_SALT_0009);
		// Remove any previous login.
		$ENABLE_COOKIE_TABLE = 1;
		//pg_safe_exec(CLEAR_COOKIES_QUERY);
		pg_safe_exec("delete from webcookies where user_id = " . (int)$user_id);
		$ENABLE_COOKIE_TABLE = 0;

		unset($is_alumni); $is_alumni = 0;
		if (($ouu->flags & 128) && $is_admin>0) { $is_alumni = 1; }

		// check IP restrictions . . . (only for * persons or persons with an ACL set, excepted ALUMNIs (as X on IRC))
		if (($is_alumni==0 && ($is_admin>0 || acl())) || has_ipr($user_id)) {
			if (is_ip_restrict()) {
				$admin = $is_admin;
				local_seclog("Failed login (no IPR match)");
				log_webrelay("failed IPR check.");
				header("Pragma: no-cache");
				echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n";
				echo "<html>\n";
				echo "<head><title>Error</title>";
				std_theme_styles();
				echo "</head>";
				std_theme_body();
				echo "<font size=+0>\n";
				echo "You can't login to this account using your current IP number (" . cl_ip() . ").<br><br>";
				echo "<a href=\"index.php\" target=\"_top\">click here</a>.<br>\n";
				echo "</font></body></html>\n\n";
				die;
			}
		}
		if (cl_ip() == "0.0.0.0") { // safety valve. (useless?)
			header("Pragma: no-cache");
			echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n";
			echo "<html>\n";
			echo "<head><title>Error</title>";
			std_theme_styles();
			echo "</head>";
			std_theme_body();
			echo "<font size=+0>\n";
			echo "You can't login using your current IP number.<br><br>";
			echo "<a href=\"index.php\" target=\"_top\">click here</a>.<br>\n";
			echo "</font></body></html>\n\n";
			die;
		}

		if (is_suspended($user_id,"")==1) {
			header("Pragma: no-cache");
			echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n";
			echo "<html>\n";
			echo "<head><title>Error</title>";
			local_seclog("Failed login (SUSPENDED)");
			std_theme_styles();
			echo "</head>";
			std_theme_body();
			echo "<font size=+0>\n";
			echo "You have been globally suspended by a Cservice Administrator, you can't login.<br><br>";
			echo "<a href=\"index.php\" target=\"_top\">click here</a>.<br>\n";
			echo "</font></body></html>\n\n";
			die;
		}

		$ress = pg_safe_exec("SELECT tz_setting,email FROM users WHERE id='$user_id'");
		$rooo = pg_fetch_object($ress,0);
		if (is_email_locked($LOCK_LOGIN,$rooo->email)) {
			header("Pragma: no-cache");
			echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n";
	               	echo "<html><head><title>REGISTRATION PROCESS</title>\n";
			std_theme_styles();
			echo "</head>";
			std_theme_body();
			echo "Sorry, you can't login using your current e-mail address.<br>\n";
			//echo "You will need to <b>/join " . SERVICE_CHANNEL . "</b> in order to deal with this problem.";
			echo "You will need to <b>/join #usernames</b> in order to deal with this problem.";
			echo "</body></html>\n\n";
			die;
		}
		$tz_setting = trim($rooo->tz_setting);

	/*
		unset($ress);
		$ress = pg_safe_exec("SELECT * FROM noreg WHERE lower(user_name)='" . strtolower($username) . "' AND type=4");
		if (pg_numrows($ress)>0) {
			header("Pragma: no-cache");
			echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n";
	               	echo "<html><head><title>ERROR</title>";
			std_theme_styles();
			echo "</head>";
			std_theme_body();
			echo "Sorry, your account is fraudulous and thus cannot be used (FRAUD USERNAME).<br>\n";
			echo "You will need to <b>/join " . SERVICE_CHANNEL . "</b> in order to deal with this problem.";
			echo "</body></html>\n\n";
			die;
		}
	*/


		$expire=time()+get_custom_session($user_id); //COOKIE_EXPIRE; // Login expiration.

		if (($is_admin && BOFH_PASS_ADMIN) || (BOFH_PASS_USER)) {
			// prevent escaping password security (excepted for ALUMNIs)
			if ($is_alumni==0) {
				if (!pw_check($password)) {
					$is_admin=-1;
				}
			}
		}
		$dynts = time();
		$ENABLE_COOKIE_TABLE = 1;
		pg_safe_exec("INSERT INTO webcookies (user_id,cookie,expire,is_admin,tz_setting,totp_cookie) VALUES (" . (int)$user_id . ",'" . $cookie . "'," . (int)$expire . "," . (int)$is_admin . ",'" . $tz_setting . "', '".$dynts."')");

		// clear ip lockout if login is success
		pg_safe_exec("DELETE FROM ips WHERE ipnum='".cl_ip()."' AND lower(user_name)='".strtolower($username)."'");

		$ENABLE_COOKIE_TABLE = 0;
		
		$cook2 = md5( $dynts . CRC_SALT_EXT1 . $cookie );
		if (COOKIE_DOMAIN!="") {
			SetCookie("auth",$username . ":" . (int)$user_id . ":" . (int)$time . ":" . $cookie . ":" . (int)$dynts . ":" . $cook2,$expire,"/",COOKIE_DOMAIN);
			SetCookie("sauth", $expire."", $expire, "/", COOKIE_DOMAIN) or die ("Can not set cookie");
			SetCookie("sepoch",time(),$expire,"/",COOKIE_DOMAIN);
			if (REMEMBER_LOGIN || PREFILL_NOTICE) { SetCookie("rlogin",$username,2147483645,"/",COOKIE_DOMAIN); } else { SetCookie("rlogin","",0,"/",COOKIE_DOMAIN); }
		} else {
			SetCookie("auth",$username . ":" . (int)$user_id . ":" . (int)$time . ":" . $cookie . ":" . (int)$dynts . ":" . $cook2,$expire,"/");
			SetCookie("sauth", $expire."", $expire, "/") or die ("Can not set cookie");
			SetCookie("sepoch",time(),$expire,"/");
			if (REMEMBER_LOGIN || PREFILL_NOTICE) { SetCookie("rlogin",$username,2147483645,"/"); } else { SetCookie("rlogin","",0,"/"); }
		}
		$admin = (int)$is_admin;
		
		if (($is_admin && BOFH_PASS_ADMIN) || (BOFH_PASS_USER)) {
			// check password complexity BOFH stylee (excepted for ALUMNIs)
			if ($is_alumni==0) {
				if (!pw_check($password)) {
				if ($admin>0) { local_seclog("Login"); log_webrelay("authenticated at level " . $admin); }
					$unsecure_pw_url = "main.php?sba=1&SECURE_ID=" . md5( $user_id . CRC_SALT_0013 . $cookie );
					header("Location: " . $unsecure_pw_url . "\n\n");
					die;
				}
			}
		}
		if ($redir) {
			header("Location: " . urldecode($redir));
		} else {
			if (TOTP_ON==1)
			{
			$totp_key=has_totp($user_id);
			if ($totp_key)
			header ("Location: v_totp.php");
			else
			{
			if ($admin>0) { local_seclog("Login"); log_webrelay("authenticated at level " . $admin); }
			header("Location: main.php?sba=1");
			}
			//echo "$cookie";
			}
			else
			{
			if ($admin>0) { local_seclog("Login"); log_webrelay("authenticated at level " . $admin); }
			header("Location: main.php?sba=1");
			}
	        }
	        exit;
   	} else { // user_id <= 0
		$failed=1;
		local_seclog("Failed login (WRONG PASSWORD for `" . N_get_pure_string($username) . "`)");
  	}
}
echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n";
//echo "<META HTTP-EQUIV=\"Expires\" CONTENT=\"0\">\n";
?>
<html>
<head>
<title>CService Login</title>
<? std_theme_styles(); ?>
</head>
<?
if (($username!="" || $_COOKIE['rlogin']!="") && !preg_match(NON_BOGUS,$username)) {
	std_theme_body("","document.forms[0].password.focus();");
} else {
	std_theme_body("","document.forms[0].username.focus();");
}
if ($failed) {
  echo("<font color=\"#" . $cTheme->main_warnmsg . "\">Login failed. Please try again</font>");
  ip_check($username,1);
}
echo "<center>\n";
echo "<font size=+2><b>Welcome to CService</b></font>\n";
echo "<br>\n";
echo "<table width=\"400\" bgcolor=#" . $cTheme->main_textcolor . ">\n";
echo "<tr><td>\n";
echo "<table cellpadding=5 bgcolor=#" . $cTheme->table_bgcolor . " width=\"100%\">\n";
echo "<tr><td><center>\n";
echo "<font color=#" . $cTheme->main_textcolor . ">\n";
echo "<font size=+2><b>CService Login</b></font>\n";
if (preg_match("^http",$redir)) { $tgt = ""; } else { $tgt = " target=body"; }
   	if ($username!="" && !preg_match(NON_BOGUS,$username)) {
	echo "<h2>Bogus username</h2><br><a href=\"login.php\">Try again</a></td></tr></table></td></tr></table></center></body></html>\n\n";
	die;
}
echo "<form method=post action=login.php" . $tgt . ">\n";
if ($redir) { echo "<input type=hidden name=redir value=\"" . urlencode(urldecode($redir)) . "\">"; }
if (REMEMBER_LOGIN || PREFILL_NOTICE) { if (trim($username) == "") { $username = $_COOKIE['rlogin']; } }
echo "<table border=0><tr><td><font color=\"#" . $cTheme->main_textcolor . "\">Username</td><td><input type=text name=username value=\"" . $username . "\"></td></tr>\n";
echo "<tr><td><font color=\"#" . $cTheme->main_textcolor . "\">Password</td><td><input type=password name=password></td></tr></table>\n";
echo "<br><input type=submit value=Login>\n";
echo "</center>";
if (PREFILL_NOTICE && ($_COOKIE['rlogin'] != "") && ($username == $_COOKIE['rlogin'])) {
	echo "<br><table border=0 cellspacing=0 cellpadding=2 bgcolor=#ffff99>";
/*
	echo "<tr bgcolor=#ff9999><td valign=top>";
	echo "<font style=\"font-size: 13px; color: #000000; background-color: #ff9999;\"><b>IMPORTANT NOTICE</b>&nbsp;&nbsp;Please read everything before continuing.</font>\n";
	echo "</td></tr>\n";
*/
	echo "<tr><td valign=top bgcolor=#ff9999>";
	echo "<font style=\"font-size: 13px; color: #000000; font-weight: bold;\">";
	echo "For security reasons, the last username to login to the CService page from this computer is displayed in the login form.";
/*
	// Example notice for opposite display (rlogin == '')
	// ==================================================

	echo "Due to growing actions of various pseudo-hackers gathering abusively our<br>";
	echo "username's password, abusing them with <b>faked CService login pages</b>,<br>";
	echo "take good note of the following :<br>\n";
	echo "If you see this WARNING, it is because you haven't yet logged in successfully<br>";
	echo "to our website, <b>DOUBLE CHECK YOU ARE ON THE REAL SITE</b>, and<br>";
	echo "eventually re-type the correct address if you clicked an URL to get there.<br>\n";
	echo "<br>";
	echo "Once you will have successfully logged in, and in order to help you easily<br>";
	echo "see if the page you are directed to for the login is the <b>REAL</b> page,<br>";
	echo "your <b>Username</b> should be pre-filled.<br><br>";
	echo "If the Username is not pre-filled and you do not see this warning, it is<br>";
	echo "more than likely a <b>FAKE</b> page and you should <b>NOT</b> attempt to login.<br>";

	echo "<b>If you already used</b> this computer/web browser <b>to successfully login</b><br>";
	echo "to the CService page, this is <b>NOT normal</b> that the <b>Username</b> field is left <b>empty</b>.<br><br>";
	echo "<b>It should contain your username</b> (the last one that successfully logged in<br>";
	echo "through that interface).<br><br>\n";
	echo "Please <b>double-check you are really on our website</b> and type the URL<br>";
	echo "into the Location bar : <b>" . IFACE_URL . "</b><br></font>\n";
*/
	echo "<br></td></tr></table>\n";
}
echo "</form>";
?>
</td></tr>
</table>
</td>
</tr>
</table>
If you do not have an account, <a href="newuser.php">create one</a> now!
</center>
</body>
</html>
