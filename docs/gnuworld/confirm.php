<?php
require("../../php_includes/cmaster.inc");

if (!check_admin_crc()) {
	require("../../php_includes/blackhole.inc");
}

$test_rbl=1;
if($loadavg5 >= CRIT_LOADAVG)
{
   header("Location: highload.php");
   exit;
}

$cTheme = get_theme_info();

function check_admin_crc() {
	global $cookie,$username,$email,$expire;
	// returns '1', if the CRC came from the admin's cr_newuser.php page,
	// returns '0', if the CRC came from the regular newuser.php page.
	if ($cookie == md5($expire . CRC_SALT_0005 . $username . $email)) {
		return(1);
	}
	return(0);
	// note: this only validates the CRC encoding, and thus to be able
	// for example to allow it to be checked against DB even if the user/ip
	// should be locked out of confirm.php.
}

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
        echo "Sorry, you can't confirm new user registrations whilst G-Lined from the network.";
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
        echo "Sorry, you can't confirm new user registrations from this IP address. ".$msg;
	echo "</h2>";
	echo "</center>\n";
	echo "</body></html>\n\n";
	die;
	}
}



if ($cookie!="") {
	//std_sanitise_username($cookie);
	std_connect();
 	$res=pg_safe_exec("select * from pendingusers where cookie='$cookie'");

 	if (pg_numrows($res)==0) {
		std_theme_styles(1); std_theme_body();
		echo "<h1>Error</h1> The URL entered is not valid.  Please check it ";
		echo "and make sure it is correct</h1><a href=\"confirm.php\">Try again.</a>";
		echo "</body></html>";
		exit;
	} else {
		$user=pg_fetch_object($res,0);
		$lowusername = strtolower( $user->user_name );
		$res=pg_safe_exec("select user_name from users where lower(user_name)='$lowusername'");
		if (pg_numrows($res)>0) {
			std_theme_styles(1); std_theme_body();
			echo "<h1>Error</h1>";
			echo "An account with that username has already been registered.  Please choose another.";
			echo "</body></html>";

			// Clean up the pendingusers
			$res = pg_safe_exec("delete from pendingusers where cookie='$cookie'");

	 		die;
	 	}
                $lowemail = strtolower( $user->email );
                $res=pg_safe_exec("select email from users where lower(email)='" . $lowemail . "'");
                if (pg_numrows($res)>0) {
			std_theme_styles(1); std_theme_body();
                        echo "<h1>Error</h1>";
                        echo "An account with that e-mail has already been registered.  Please reapply with a different e-mail.";
                        echo "</body></html>";

                        // Clean up the pendingusers
                        $res = pg_safe_exec("delete from pendingusers where cookie='$cookie'");

                       die;
                }
	}

	$valid="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$password="";
	srand((double) microtime() * 1000000);
	for ($i=0;$i<8;$i++) {
		$password=$password . $valid[rand(0,strlen($valid)-1)];
	}
	for ($i=0;$i<8;$i++) {
		$salt=$salt . $valid[rand(0,strlen($valid)-1)];
	}
	$crypt=$salt . md5($salt . $password);
	$verificationdata = prepare_dbtext_db( $user->verificationdata );

	$q = "INSERT INTO users (user_name, password, flags, email, last_updated, last_updated_by, language_id, question_id, verificationdata, post_forms, signup_ts, signup_ip, maxlogins)";
	$q .= sprintf(" VALUES ('%s', '%s', %d, '%s', %s, '%s', %d, %d, '%s', %s, %s, '%s', %d)", $user->user_name, $crypt, 4, $user->email, "date_part('epoch', CURRENT_TIMESTAMP)::int", "Web Page New User", $user->language, $user->question_id, $verificationdata, "(date_part('epoch', CURRENT_TIMESTAMP)::int+432000)", "date_part('epoch', CURRENT_TIMESTAMP)::int", cl_ip(), DEFAULT_MAX_LOGINS);

	$res=pg_safe_exec($q);
	local_seclog("New user confirmation for `" . $user->user_name . "`");

	$ucount = pg_safe_exec("SELECT count_count FROM counts WHERE count_type='1'");
	if (pg_numrows($ucount)==0) {
		pg_safe_exec("INSERT INTO counts VALUES ('1','1')");
	} else {
		$uobj=pg_fetch_object($ucount,0);
		$newcount = $uobj->count_count+1;
		if ($newcount==$MAX_ALLOWED_USERS) {
			pg_safe_exec("INSERT INTO locks VALUES (3,date_part('epoch', CURRENT_TIMESTAMP)::int,0)");
		}
		pg_safe_exec("UPDATE counts SET count_count='" . ($newcount+0) . "' WHERE count_type='1'");
	}


	$username=$user->user_name;
	if ($res) {
		$res = pg_safe_exec( "delete from pendingusers where cookie='$cookie'" );
        	custom_mail($user->email,"Successful CService Application","Your new Cservice Login password is $password\n","From: $mail_from_new\nReply-To: $mail_from_new\nX-Mailer: " . NETWORK_NAME . " Channel Service");

		echo "<html><head><title>Successful Application</title>";
		std_theme_styles(); echo "</head>"; std_theme_body();
		echo "<h1>Success!</h1>";
		echo "Your account ". $username ." has been created.  Your password is: ";
		echo "<center><table><tr><td><h1>". $password ."</h1></td></tr></table></center>";
		echo "Please note it down somewhere safe where others can't read it.<br>A copy is also being ";
		echo "emailed to you.  Please make sure that you store this securely.<br>";
		echo "You may now proceed to the <a href=\"index.php\">Login page</a>.<br>";
		echo "</body></html>";
		exit;
	} else {
		// First check to see if somebody got there first.
		$res = pg_safe_exec( "select user_name from users where lower(user_name) = '$lowusername'" );
		if( pg_numrows($res)!= 0 ) {
			std_theme_styles(1); std_theme_body();
			echo "<h1>Error</h1>";
			echo "An account with that username has already been registered.  Please choose another.";
			echo "</body></html>";

			// Clean up the pendingusers
			$res = pg_safe_exec("delete from pendingusers where cookie='$cookie'");
			exit;

		} else {

			echo "<html><head><title>An Error Occured</title>";
			std_theme_styles();
			echo "</head>";
			std_theme_body();
			echo "<h1>An Error has occured.</h1>";
			//echo pg_errormessage();
			//echo "<br>\n";
			echo "An Error has occured, it's beyond me whats going on.  Maybe ask someone in ";
			echo SERVICE_CHANNEL . "?  They probably don't know either, but it'll make you feel better.";
			echo "</body></html>";
			exit;
		}
	}
	exit; // Shouldn't get here.
} else {
	echo "<html><head><title>User Registration Confirmation</title>";
	std_theme_styles();
	echo "</head>";
	std_theme_body();
	echo "<form method=POST><h1>User Registration Confirmation</h1>Please enter the cookie you recieved in the email below.";
	echo "<input type=text name=cookie><br><input type=submit value=\"Complete Registration\">";
	echo "</form></body></html>";
}

?>
