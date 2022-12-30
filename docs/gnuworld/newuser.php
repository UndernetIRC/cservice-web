<?php
/* $Id: newuser.php,v 1.32 2006/03/11 18:40:34 nighty Exp $ */
require("../../php_includes/blackhole.inc");
require("../../php_includes/cmaster.inc");
$test_rbl=1;
if($loadavg5 >= CRIT_LOADAVG) { header("Location: highload.php"); die; }

std_connect();
$cTheme = get_theme_info();
$user_id = std_security_chk($_COOKIE["auth"]);
$admin = 0;
$confirm_url = gen_server_url() . LIVE_LOCATION . "/confirm.php";

// check for global lock (admin)
if (newusers_off()) {
	echo "<html><head><title>PAGE LOCKED BY CSERVICE ADMINISTRATORS</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body();
	echo "<center>\n";
	echo "<h2>";
	echo "Sorry, You can't register new users at the moment.";
	echo "</h2>";
	echo "</center>\n";
	echo "</body></html>\n\n";
	die;
}
/*
if (NEWUSERS_IPCHECK && !newu_ipcheck(0)) {
	echo "<html><head><title>SECURITY WARNING</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body();
	echo "<center>\n";
	echo "<h2>";
	echo "Sorry, your IP address already registered a username, you can only register ONE username.";
	echo "</h2>";
	echo "</center>\n";
	echo "</body></html>\n\n";
	die;
}
*/
if ($test_rbl==1)
	{
	if ($_GET['ip'])
		$user_ip=$_GET['ip'];
		else
		$user_ip=cl_ip();
	}
else
$user_ip=cl_ip();

if (RBL_CHECKS==1)
{
if (ip_check_glined($user_ip)) {
	echo "<html><head><title>SECURITY WARNING</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body();
	echo "<center>\n";
	echo "<h2>";
	echo "Sorry, you can't register new users whilst G-Lined from the network.";
	echo "</h2>";
	echo "</center>\n";
	echo "</body></html>\n\n";
	die;
}
}
if (RBL_CHECKS==1)
{
$msg=ip_check_rbl($user_ip);
if ($msg !='clean')
	{
	echo "<html><head><title>SECURITY WARNING</title>";
	std_theme_styles();
	echo "</head>\n";
	std_theme_body();
	echo "<center>\n";
	echo "<h2>";
	echo "Sorry, you can't register new users from this IP address. ".$msg;
	echo "</h2>";
	echo "</center>\n";
	echo "</body></html>\n\n";
	die;
	}
}

// check if the client is already authenticated (!)
if ($user_id > 0) {
	std_theme_styles(1);
	std_theme_body();
	echo "<p>You are already logged in.  You are not allowed to create multiple accounts.</p>";
	echo "<p>Go to your <a href=\"./\" target=\"_top\">user page</a>.</p></body></html>";
	die;
}

unset($max_step); unset($curr_step);
$max_step = 6;
if (SHOW_GFXUSRCHK && NEWUSERS_GFXCHECK) { $max_step++; }
if ((int)$_POST["showStep"]>0 && check_secure_form("step".(int)$_POST["showStep"])) { $curr_step = (int)$_POST["showStep"]; } else { $curr_step = 1; }
if (!(SHOW_GFXUSRCHK && NEWUSERS_GFXCHECK) && $curr_step==6) { $curr_step = 7; }

if ($curr_step==8) {
	if ($_POST["rCRC"]==md5( $_POST["username"] . CRC_SALT_0011 . $_SERVER["REMOTE_ADDR"] . $_POST["email"] . $_POST["gfxcode_val"] . CRC_SALT_0007 )) {
		// sets cookie so user cannot create another username within 4 hours.
		if (UNETUB_TIME>0) {
			$expire = time()+UNETUB_TIME;
			$cookie = md5( $expire . "Undernet User Block");
			setcookie("UNETUB",$cookie,$expire,"/");
		}
	}
}

std_theme_styles(1);
std_theme_body();

echo "<form name=newUsr method=POST onSubmit=\"return stepChk(this)\">\n";
echo "<input type=hidden name=showStep value=\"" . (int)((int)$curr_step+1) . "\">\n";
make_secure_form("step".(int)((int)$curr_step+1));
echo "<center>";

echo "<h1>New Username</h1>";

echo "<table width=500 bgcolor=#" . $cTheme->main_textcolor . "><tr><td>\n";
echo "<table cellpadding=5 bgcolor=#" . $cTheme->table_bgcolor . " width=100%><tr><td><font color=#" . $cTheme->main_textcolor . ">";
if ($curr_step<=7) {
	echo "<font size=+2>Step " . (int)$curr_step . " / " . (int)$max_step . "</font><br><br>\n";
} else {
	echo "<font size=+2>Congratulations !</font><br><br>\n";
}

// step data START
$jsf = "";
$err = "";
$hackpc = 0;

switch ((int)$curr_step) {
	default:
	case 1:
		echo "The Children's Online Privacy Protection Act of 1998 (COPPA) requires that web page publishers obtain parental ";
		echo "consent before asking for or using personal information from children under 13. Because " . NETWORK_NAME . " cannot comply ";
		echo "with this requirement, we must have you state by clicking this box that you are at least 13 years old.<br><br>\n";
		echo "<input type=radio name=user_age id=ua1 value=1>&nbsp;<label for=ua1>I am 13 or older.</label><br>\n";
		echo "<input type=radio name=user_age id=ua2 value=0>&nbsp;<label for=ua2>I am younger than 13.</label><br>\n";
		$jsf .= "\tif (!f.user_age[0].checked) { all_ok = false; }\n";
		$jsf .= "\tvar msg = 'You must specify that you are 13 or older !';\n";
		break;
	case 2:
		if ((int)$_POST["user_age"]!=1) { $err .= "<li> You must be 13 or older.\n"; }
		if ($err!="") { err_newuser($err); } else {
			echo "<input type=hidden name=is13 value=1>\n";

			echo "Choose a username.  This is how you are known to the channel service bots. ";
			echo "This does <em>not</em> need to be the same as your nick. A valid username ";
			echo "is made up of the letters 'A' to 'Z' and the numbers '0' to '9'. THIS IS NOT NICK REGISTRATION. YOU ";
			echo "ARE NOT REGISTERING YOUR NICKNAME. Can we possibly make it clearer that this is NOT NICK REGISTRATION. ";
			echo "If you refer to it as nick registration, do not be surprised if we don't know what you are talking about.<br><br>\n";
			echo "<label>Username: <input type=text name=username maxlength=12>\n";
			$jsf .= "\tif (f.username.value.length < 2) { all_ok = false; }\n";
			$jsf .= "\tvar msg = 'Your username must be 2 to 12 letters long !';\n";
		}
		break;
	case 3:
		$err.= !ip_check_white($user_ip) ? check_username_similarity($_POST["username"]) : "";
		if (strlen($_POST["username"])<2 || strlen($_POST["username"])>12) { $err .= "<li> Your username must be 2 to 12 chars long.\n"; }
		if (!preg_match("/^[A-Za-z0-9]+$/",$_POST["username"])) { $err .= "<li> Your username must be made of letters (A-Z, a-z) and numbers (0-9).\n"; }
		$ru = pg_safe_exec("SELECT id FROM users WHERE lower(user_name)='" . strtolower($_POST["username"]) . "'");
		if ($ou=@pg_fetch_object($ru)) { $err .= "<li> The username you picked is already in use by someone else.\n"; }
		if (is_locked_username($_POST["username"])) { $err .= "<li> Username is LOCKED : Usernames matching <b>" . $ulockinfo->user_name . "</b> are disallowed for the following reason : <b>" . $ulockinfo->reason . "</b>.\n"; }
		$res = pg_safe_exec("SELECT * FROM noreg WHERE lower(user_name)='" . post2db(strtolower($_POST["username"])) . "'");
		if (pg_numrows($res)>0) { $err .= "<li> That username (" . $_POST["username"] . ") is in NOREG mode, please choose another.\n"; }
		if ($err!="") { err_newuser($err); } else {
			echo "<input type=hidden name=is13 value=1>\n";
			echo "<input type=hidden name=username value=\"" . post2input($_POST["username"]) . "\">\n";
			echo "<input type=hidden name=username_crc value=\"" . md5( CRC_SALT_0008 . $_POST["username"] . "UCHECK" ) . "\">\n";

			echo "Please enter your primary email address. Use your ISP email address where possible. Your email address will never be used ";
			echo "to send you unsolicited email.  It will be used to send you information on how to obtain your ";
			echo "password.<br><br>\n";
			echo "<label>Email Address: <input type=text name=email maxlength=128>\n";
			$jsf .= "\tif (f.email.value == '') { all_ok = false; }\n";
			$jsf .= "\tvar msg = 'Please type in your e-mail address !';\n";
		}

		break;
	case 4:
	  if (md5( CRC_SALT_0008 . $_POST["username"] . "UCHECK" )!=$_POST["username_crc"]) { $err .= "<li> <b>Attempt to hack page content !</b> (username)\n"; $hackpc = 1; }
		if (!is_email_valid($_POST["email"])) { $err .= "<li> Your e-mail address is invalid.\n"; }
		if (is_email_locked($LOCK_USERNAME,$_POST["email"])) { $err .= "<li> You are not allowed to create an account using this email address (" . $_POST["email"] . ")\n"; }
		$email_nreg = pg_safe_exec("SELECT * FROM noreg WHERE lower(email)='" . post2db(strtolower($_POST["email"])) . "' and user_name='*'");
		if (pg_numrows($email_nreg)>0) { $err .= "<li> This email account (" . $_POST["email"] . ") is in NOREG, you can't use it for username registration.\n"; }
		$email_dbh = pg_safe_exec("SELECT user_name FROM users WHERE lower(email)='" . post2db(strtolower($_POST["email"])) . "'");
		if (pg_numrows($email_dbh)>0) { $err .= "<li> There is already an account registered with that email address.<br>You can only have one account per person. If you have lost your password and require a new one to be resent <a href=\"forgotten_pass.php\">click here</a>.\n"; }
		if ($err!="") { err_newuser($err); } else {
			echo "<input type=hidden name=is13 value=1>\n";
			echo "<input type=hidden name=username value=\"" . post2input($_POST["username"]) . "\">\n";
			echo "<input type=hidden name=username_crc value=\"" . post2input($_POST["username_crc"]) . "\">\n";
			echo "<input type=hidden name=email value=\"" . post2input($_POST["email"]) . "\">\n";
			echo "<input type=hidden name=email_crc value=\"" . md5( CRC_SALT_0009 . $_POST["email"] . "ECHECK" ) . "\">\n";

			echo "Select your preferred language for CService's " . BOT_NAME . " bot to communicate with you on IRC. ";
			echo "Note that all functions are not supported in all languages. ";
			echo "If we cannot communicate with your in your language of choice, we will ";
			echo "use English.<br><br>\n";
			echo "<label>Language: <select name=language>\n";
			$res=pg_safe_exec("select * from languages ORDER by name");
			for ($i=0;$i<pg_numrows($res);$i++) {
				$lang=pg_fetch_object($res,$i);
				echo "<option ";
				if ($lang->id==1) { echo "selected "; }
				echo "value=\"" . $lang->id . "\">" . $lang->name . "</option>\n";
			}
			echo "</select>\n";
		}
		break;
	case 5:
		if (md5( CRC_SALT_0008 . $_POST["username"] . "UCHECK" )!=$_POST["username_crc"]) { $err .= "<li> <b>Attempt to hack page content !</b> (username)\n"; $hackpc = 1; }
		if (md5( CRC_SALT_0009 . $_POST["email"] . "ECHECK" )!=$_POST["email_crc"]) { $err .= "<li> <b>Attempt to hack page content !</b> (email)\n"; $hackpc = 2; }
		if ($err!="") { err_newuser($err); } else {
			echo "<input type=hidden name=is13 value=1>\n";
			echo "<input type=hidden name=username value=\"" . post2input($_POST["username"]) . "\">\n";
			echo "<input type=hidden name=username_crc value=\"" . post2input($_POST["username_crc"]) . "\">\n";
			echo "<input type=hidden name=email value=\"" . post2input($_POST["email"]) . "\">\n";
			echo "<input type=hidden name=email_crc value=\"" . post2input($_POST["email_crc"]) . "\">\n";
			echo "<input type=hidden name=language value=\"" . post2input($_POST["language"]) . "\">\n";

			echo "<p>Select a question and give an answer that you will remember. This information will be used as additional verification if you forget your password or need to change your email-of-record.</p>\n";
			echo "<select name=question_id>\n";
			echo "<option selected value=0>--- click here ---</option>\n";
			for ($x=1;$x<=$max_question_id;$x++) {
				echo "<option value=" . $x;
				if( $x == $question_id ) {
					echo( " selected" );
				}
				echo ">" . $question_text[$x] . "</option>\n";
			}
			echo "</select><br>\n";
			echo "<input type=text size=25 maxlength=30 name=verificationdata>\n";
			$jsf .= "\tif (f.question_id.options[f.question_id.selectedIndex].value == 0) { all_ok = false; }\n";
			$jsf .= "\tif (f.verificationdata.value.length < 4) { all_ok = false; }\n";
			$jsf .= "\tvar msg = 'Please pick a question and fill in the answer for it (4 chars minimum) !';\n";
		}
		break;
	case 6:
		if (md5( CRC_SALT_0008 . $_POST["username"] . "UCHECK" )!=$_POST["username_crc"]) { $err .= "<li> <b>Attempt to hack page content !</b> (username)\n"; $hackpc = 1; }
		if (md5( CRC_SALT_0009 . $_POST["email"] . "ECHECK" )!=$_POST["email_crc"]) { $err .= "<li> <b>Attempt to hack page content !</b> (email)\n"; $hackpc = 2; }
		if ((int)$_POST["question_id"]<=0 || (int)$_POST["question_id"]>$max_question_id) { $err .= "<li> Please select a question.\n"; }
		if (strlen($_POST["verificationdata"])<4) { $err .= "<li> Please choose an answer with at least 4 chars in it.\n"; }
		if(!(preg_match( "/^[A-Za-z0-9!\ \/\\.+_-]+$/", $_POST["verificationdata"] ))) { $err .= "<li> The entered verification data contains invalid chars.\n"; }
		if (strtolower($_POST["email"])==strtolower($_POST["verificationdata"])) { $err .= "<li> Your verification answer must be different than your email.\n"; }
		if (strtolower($_POST["username"])==strtolower($_POST["verificationdata"])) { $err .= "<li> Your verification answer must be different than your username.\n"; }
		if (is_locked_va($_POST["verificationdata"])) { $err .= "<li> The verification answer you have chosen is too common. Please pick an answer that is unique and that you will remember.\n"; }
		if ($err!="") { err_newuser($err); } else {
			echo "<input type=hidden name=is13 value=1>\n";
			echo "<input type=hidden name=username value=\"" . post2input($_POST["username"]) . "\">\n";
			echo "<input type=hidden name=username_crc value=\"" . post2input($_POST["username_crc"]) . "\">\n";
			echo "<input type=hidden name=email value=\"" . post2input($_POST["email"]) . "\">\n";
			echo "<input type=hidden name=email_crc value=\"" . post2input($_POST["email_crc"]) . "\">\n";
			echo "<input type=hidden name=language value=\"" . post2input($_POST["language"]) . "\">\n";
			echo "<input type=hidden name=question_id value=\"" . post2input($_POST["question_id"]) . "\">\n";
			echo "<input type=hidden name=verificationdata value=\"" . post2input($_POST["verificationdata"]) . "\">\n";
			echo "<input type=hidden name=verificationdata_crc value=\"" . md5( CRC_SALT_0010 . $_POST["verificationdata"] . "VCHECK" ) . "\">\n";

			// generate the code to enter and the CRC to check it after post.
			// this solution will hopefully prevent any automated username registration procedure.
			$code_length = 10; // generated code number of chars
			$code_base = md5( CRC_SALT_0001 . time() . microtime() . CRC_SALT_0009 . uniqid(1) ); // base of code ( with strlen(this)>$code_length ! ).
			$code = strtoupper(substr(str_replace("1","",str_replace("i","",str_replace("I","",str_replace("o","",str_replace("O","",str_replace("l","",str_replace("L","",str_replace("0","",$code_base)))))))),0,$code_length));
			$ts = time()+180; // expires 3 mins after page load.
			$crckey = md5( $ts . $_SERVER["HTTP_USER_AGENT"] . $_SERVER["REMOTE_ADDR"] . CRC_SALT_0010 . $code . CRC_SALT_0008 );
			echo "<input type=hidden name=gfxcode_crc value=\"" . $crckey . "\">\n";
			echo "<input type=hidden name=gfxcode_ts value=\"" . $ts . "\">\n";
			$ENABLE_COOKIE_TABLE = 1;
			pg_safe_exec("INSERT INTO gfxcodes VALUES ('" . $code . "','" . $crckey . "','" . $ts . "')");
			$ENABLE_COOKIE_TABLE = 0;
			echo "<img src=\"gfx_code.php?crc=" . $crckey . "\" border=0 alt=\"TYPE THAT CODE IN THE INPUT BOX BELOW\"><br>\n";
			echo "<p>Now please enter the code you see in the above picture into the below input box (upper/lower case doesn't matter).</p>\n";
			echo "<input type=text size=35 maxlength=" . $code_length . " name=gfxcode_val value=\"\">\n";
			$jsf .= "\tif (f.gfxcode_val.value == '') { all_ok = false; }\n";
			$jsf .= "\tvar msg = 'Please fill in the CODE !';\n";


		}
		break;
	case 7:
		if (SHOW_GFXUSRCHK && NEWUSERS_GFXCHECK) {




			// check GFX code
			if ($_POST["gfxcode_crc"] != md5( $_POST["gfxcode_ts"] . $_SERVER["HTTP_USER_AGENT"] . $_SERVER["REMOTE_ADDR"] . CRC_SALT_0010 . strtoupper($_POST["gfxcode_val"]) . CRC_SALT_0008)) {
				$err .= "<li> You entered an invalid code from the picture.\n";
			} else { // CRC logic is fine, now check with the database temporary record...
				$ENABLE_COOKIE_TABLE = 1;
				pg_safe_exec("DELETE FROM gfxcodes WHERE expire<date_part('epoch', CURRENT_TIMESTAMP)::int");
				$r = pg_safe_exec("SELECT expire FROM gfxcodes WHERE crc='" . $gfxcode_crc . "' AND code='" . strtoupper($gfxcode_val) . "'");
				if (pg_numrows($r)==0) {
					$err .= "<li> You entered an invalid/expired code from the picture.\n";
				} else {
					pg_freeresult($r);
				}
				$ENABLE_COOKIE_TABLE = 0;
			}
			if (md5( CRC_SALT_0008 . $_POST["username"] . "UCHECK" )!=$_POST["username_crc"]) { $err .= "<li> <b>Attempt to hack page content !</b> (username)\n"; $hackpc = 1; }
			if (md5( CRC_SALT_0009 . $_POST["email"] . "ECHECK" )!=$_POST["email_crc"]) { $err .= "<li> <b>Attempt to hack page content !</b> (email)\n"; $hackpc = 2; }
			if (md5( CRC_SALT_0010 . $_POST["verificationdata"] . "VCHECK" )!=$_POST["verificationdata_crc"]) { $err .= "<li> <b>Attempt to hack page content !</b> (v/a)\n"; $hackpc = 3; }
		} else {
			// check step5 (see step6)
			if (md5( CRC_SALT_0008 . $_POST["username"] . "UCHECK" )!=$_POST["username_crc"]) { $err .= "<li> <b>Attempt to hack page content !</b> (username)\n"; $hackpc = 1; }
			if (md5( CRC_SALT_0009 . $_POST["email"] . "ECHECK" )!=$_POST["email_crc"]) { $err .= "<li> <b>Attempt to hack page content !</b> (email)\n"; $hackpc = 2; }
			if ((int)$_POST["question_id"]<=0 || (int)$_POST["question_id"]>$max_question_id) { $err .= "<li> Please select a question.\n"; }
			if (strlen($_POST["verificationdata"])<4) { $err .= "<li> Please choose an answer with at least 4 chars in it.\n"; }
			if(!(preg_match( "/^[A-Za-z0-9!\ \/\\.+_-]+$/", $_POST["verificationdata"] ))) { $err .= "<li> The entered verification data contains invalid chars.\n"; }
			if (strtolower($_POST["email"])==strtolower($_POST["verificationdata"])) { $err .= "<li> Your verification answer must be different than your email.\n"; }
			if (strtolower($_POST["username"])==strtolower($_POST["verificationdata"])) { $err .= "<li> Your verification answer must be different than your username.\n"; }
			if (!preg_match("/^[A-Za-z0-9]+$/",$_POST["username"])) { $err .= "<li> Your username must be made of letters (A-Z, a-z) and numbers (0-9).\n"; }
			if (is_locked_va($_POST["verificationdata"])) { $err .= "<li> The verification answer you have chosen is too common. Please pick an answer that is unique and that you will remember.\n"; }
		}
		if ($err!="") { err_newuser($err); } else {
			echo "<input type=hidden name=is13 value=1>\n";
			echo "<input type=hidden name=username value=\"" . post2input($_POST["username"]) . "\">\n";
			echo "<input type=hidden name=username_crc value=\"" . post2input($_POST["username_crc"]) . "\">\n";
			echo "<input type=hidden name=email value=\"" . post2input($_POST["email"]) . "\">\n";
			echo "<input type=hidden name=email_crc value=\"" . post2input($_POST["email_crc"]) . "\">\n";
			echo "<input type=hidden name=language value=\"" . post2input($_POST["language"]) . "\">\n";
			echo "<input type=hidden name=question_id value=\"" . post2input($_POST["question_id"]) . "\">\n";
			echo "<input type=hidden name=verificationdata value=\"" . post2input($_POST["verificationdata"]) . "\">\n";
			if (SHOW_GFXUSRCHK && NEWUSERS_GFXCHECK) {
				echo "<input type=hidden name=verificationdata_crc value=\"" . post2input($_POST["verificationdata_crc"]) . "\">\n";
			} else {
				echo "<input type=hidden name=verificationdata_crc value=\"" . md5( CRC_SALT_0010 . $_POST["verificationdata"] . "VCHECK" ) . "\">\n";
			}
			echo "<input type=hidden name=gfxcode_crc value=\"" . post2input($_POST["gfxcode_crc"]) . "\">\n";
			echo "<input type=hidden name=gfxcode_ts value=\"" . post2input($_POST["gfxcode_ts"]) . "\">\n";
			echo "<input type=hidden name=gfxcode_val value=\"" . post2input($_POST["gfxcode_val"]) . "\">\n";
			echo "<input type=hidden name=ipaddr value=\"" . $_SERVER["REMOTE_ADDR"] . "\">\n";
			echo "<input type=hidden name=rCRC value=\"" . md5( $_POST["username"] . CRC_SALT_0011 . $_SERVER["REMOTE_ADDR"] . $_POST["email"] . $_POST["gfxcode_val"] . CRC_SALT_0007 ) . "\">\n";

			echo "<p>Individuals are granted the use of a Channel Service username specifically to gain access to channel services. ";
			echo "While you need not use channel services to have a username, be aware that attempts to register multiple usernames to ";
			echo "the same person will result in a suspension of all the offending names. ";
			echo "Don't jeopardize your access to channel services by trying to register multiple usernames on our system.<br>\n";
			echo "<br>Applying for a username while connected from a compromised computer is not allowed.</p>\n";
			if (DEFAULT_MAX_LOGINS > 1) {
				printf("<p>Please, be aware that MAX LOGINS is set to %d by default. You can always decrease it to 1, or increase it to %d after a period of time, the time period to wait is visible on your profile page.</p>\n", DEFAULT_MAX_LOGINS, sorted_max_logins()[0]['max_logins']);
			}
			echo "<p>Now click 'Submit' to finish your application.</p>\n";
		}
		break;
	case 8: // final POST
		if (md5( CRC_SALT_0008 . $_POST["username"] . "UCHECK" )!=$_POST["username_crc"]) { $err .= "<li> <b>Attempt to hack page content !</b> (username)\n"; $hackpc = 1; }
		if (md5( CRC_SALT_0009 . $_POST["email"] . "ECHECK" )!=$_POST["email_crc"]) { $err .= "<li> <b>Attempt to hack page content !</b> (email)\n"; $hackpc = 2; }
		if (md5( CRC_SALT_0010 . $_POST["verificationdata"] . "VCHECK" )!=$_POST["verificationdata_crc"]) { $err .= "<li> <b>Attempt to hack page content !</b> (v/a)\n"; $hackpc = 3; }
		if ($_POST["rCRC"]!=md5( $_POST["username"] . CRC_SALT_0011 . $_SERVER["REMOTE_ADDR"] . $_POST["email"] . $_POST["gfxcode_val"] . CRC_SALT_0007 )) { $err .= "<li> <b>Something went wrong ! Sorry.</b>\n"; }


                if (strlen($_POST["username"])<2 || strlen($_POST["username"])>12) { $err .= "<li> Your username must be 2 to 12 chars long.\n"; }
                if (!preg_match("/^[A-Za-z0-9]+$/",$_POST["username"])) { $err .= "<li> Your username must be made of letters (A-Z, a-z) and numbers (0-9).\n"; }
                $ru = pg_safe_exec("SELECT id FROM users WHERE lower(user_name)='" . strtolower($_POST["username"]) . "'");
                if ($ou=@pg_fetch_object($ru)) { $err .= "<li> The username you picked is already in use by someone else.\n"; }
                if (is_locked_username($_POST["username"])) { $err .= "<li> Username is LOCKED : Usernames matching <b>" . $ulockinfo->user_name . "</b> are disallowed for the following reason : <b>" . $ulockinfo->reason . "</b>.\n"; }
                $res = pg_safe_exec("SELECT * FROM noreg WHERE lower(user_name)='" . post2db(strtolower($_POST["username"])) . "'");
                if (pg_numrows($res)>0) { $err .= "<li> That username (" . $_POST["username"] . ") is in NOREG mode, please choose another.\n"; }



		if ($err == "") {
			$ENABLE_COOKIE_TABLE = 1;
			pg_safe_exec("DELETE FROM gfxcodes WHERE crc='" . post2db($_POST["gfxcode_crc"]) . "' AND code='" . post2db(strtoupper($_POST["gfxcode_val"])) . "'");
			$ENABLE_COOKIE_TABLE = 0;
			// checks if cookie disallowing new username is present.
			if ($_COOKIE["UNETUB"]!="" && UNETUB_TIME>0) {
				err_newuser("Your IP has already registered a username, you can only signup for ONE username."); $err = 1;
			} elseif (NEWUSERS_IPCHECK && !newu_ipcheck(1)) {
				err_newuser("Your IP has already registered a username, you can only signup for ONE username."); $err = 1;
			} else {
				$cookie=md5(microtime() . time() . CRC_SALT_0003 . $_POST["username"] . $_POST["email"]);
				$expire=time()+86400; // 1 day
				$language = (int)$_POST["language"];
				// Run any user typed field that hasn't already been sanity checked through the prepare_dbtext() function.
				$verificationdata = post2db($_POST["verificationdata"]);
				local_seclog("New user request for `" . N_get_pure_string($_POST["username"]) . "`");
				pg_safe_exec("insert into pendingusers (user_name,cookie,expire,email,language,question_id,verificationdata,poster_ip) values ('" . post2db($_POST["username"]) . "','" . post2db($cookie) . "'," . (int)$expire . ",'" . post2db(strtolower($_POST["email"])) . "'," . $language . "," . (int)$question_id . ",'" . $verificationdata . "','" . cl_ip() . "')");
				$boundary=md5(time());
				custom_mail($_POST["email"],$mail_subject_new,"To continue the registration process go to " . $confirm_url . "?cookie=" . $cookie,
					"Content-type: text/plain; charset=iso-8859-1\nFrom: " . $mail_from_new . "\nReply-To: " . $mail_from_new . "\nX-Mailer: " . NETWORK_NAME . " Channel Service"
				);
				echo("<p>Your registration email has been sent.  Please wait to receive it, and then follow the instructions it contains to continue</p>");
			}
		} else {
			err_newuser($err); $err = 1;
		}
		break;

}

if ((int)$curr_step!=8) {
if ($err == "") {
	if ((int)$curr_step<7) {
		echo "<p align=right><input type=submit value=\"NEXT &gt;&gt;\"></p>\n";
	} else {
		echo "<p align=right><input type=submit value=\"SUBMIT\"></p>\n";
	}
} else {
	echo "<p align=center><input type=button value=\"GO BACK\" onClick=\"history.go(-1)\"></p>\n";
}
}

// step data END

// take appropriate action upon $hackpc>0 : (commented by default)
$hacktp = Array(1=>"username",2=>"email",3=>"V/A",4=>"(unknown)");
if ($hackpc>3 || $hackpc<0) { $hackpc = 4; }
if ($hackpc>0) {
	local_seclog("User tried to hack 'newuser.php' modifying " . $hacktp[$hackpc] . ".");
}

echo "<script language=\"JavaScript\">\n";
echo "<!--\n";
echo "function stepChk(f) {\n";
echo "\tvar all_ok = true;\n";
echo $jsf;
echo "\tif (!all_ok) { alert(msg); }\n";
echo "\treturn all_ok;\n";
echo "}\n";
echo "//-->\n";
echo "</script>\n";

echo "\t</td></tr></table>\n";
echo "</td></tr></table>\n";
echo "<br>\n";

?>
</form>
</body>
</html>
