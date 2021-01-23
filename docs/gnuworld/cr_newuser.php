<?

/* <!-- $Id: cr_newuser.php,v 1.4 2003/08/17 05:47:55 nighty Exp $ //--> */

require("../../php_includes/cmaster.inc");
	std_init();
$cTheme = get_theme_info();
	if ($admin<800) {
		die("Wrong way, sorry!");
	}


$confirm_url = gen_server_url() . LIVE_LOCATION . "/confirm.php";

//
$lowusername = strtolower( $username ); // lower case pgsql cares
$lowemail = strtolower( $email ); // lower case pgsql cares
$failed=0; // assume success!

$headerz="<h1>Admin User Registration Page</h1><hr>";
$first_error=1;

// TODO: Also, regex match obscenities, CService CServe, IRCOp, Oper, etc in username -- lgm
// TODO: Minumum length

if ($username != "") {
	if (strlen($username) > 12 || strlen($username) < 2) {

		$failed=1;
		if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
		echo( "<p><font color=\"#" . $cTheme->main_warnmsg . "\">That username is not valid, the username must be between 2 and 12 characters in length.</font>  The current username ");
		echo( "is ". strlen( $username ) ." characters in length.  Please fix this error.</p>" );

	} else {

		$res=pg_safe_exec("select user_name from users where lower(user_name)='$lowusername'");
		if (pg_numrows($res)>0) {
			$failed=1;
			if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
			echo("<p><font color=\"#" . $cTheme->main_warnmsg . "\">That username is already in use</font>, please choose another</p>");
		} else {
			// validate the username.
			//
			if( !(preg_match( "/^[A-Za-z0-9]+$/", $username )) ) {
				$failed = 1;
				if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
				echo( "<p><font color=\"#" . $cTheme->main_warnmsg . "\">That username is not valid, valid characters are abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890</font>, ");
				echo( "please choose another username.</p>");
			}

		}
	}
}

if ($username != "") {
	$res = pg_safe_exec("SELECT * FROM noreg WHERE lower(user_name)='$lowusername'");
	if (pg_numrows($res)>0) {
		$failed=1;
		if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
		$obj = pg_fetch_object($res,0);
		echo("<p><font color=\"#" . $cTheme->main_warnmsg . "\">That username (" . $obj->user_name . ") is in NOREG mode</font>, please choose another</p>");
	}
}

if ($email != "") {

	// Added more email validation checks.  The email must contain an @, it must have atleast one character before the @ and it must
	// contain atleast one . and there must be atleast 2 characters trailing the final . and there is no _ in the hostname section.  Just
	// RFC enforcement checking.

	if( !(preg_match( "/^[A-Za-z0-9_+-.]+@[A-Za-z0-9.-]+\.[A-Za-z][A-Za-z]+$/", $email )) ) {
		$failed = 1;
		if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
		echo( "<p><font color=\"#" . $cTheme->main_warnmsg . "\">The email address is invalid.</font>  It must contain a @, it must be from a valid domain, and it can only contain alpha-numeric " );
		echo( "characters (a-zA-Z0-9) or the . or - character.</p>" );
	}

	// Now we have to go to the database.  First to see if the email domain is valid, then to see if it's in use.

	if (is_email_locked($LOCK_USERNAME,$email)) {
		$failed = 1;
		if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
		echo( "<p><font color=\"#" . $cTheme->main_warnmsg . "\">You are not allowed to create a username using this email account ($email).</p>");
	}

	$email_dbh = pg_safe_exec( "select user_name from users where lower(email) = '$lowemail'" );
	if( pg_numrows( $email_dbh ) > 0 ) {
		$failed = 1;
		if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
		echo( "<p><font color=\"#" . $cTheme->main_warnmsg . "\">There is already an account registered with that email address.</font></p>");
	}
}

if ($username == "" && $clic == 1)
	{
	$failed=1;
	if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
	echo("<p><font color=\"#" . $cTheme->main_warnmsg . "\">You must choose a username in order to process the request.</p>");
	}

if ($email == "" && $clic == 1)
	{
	$failed=1;
	if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
	echo("<p><font color=\"#" . $cTheme->main_warnmsg . "\">You must supply a valid e-mail address in order to process the request.</p>");
	}

if ($question_id == 0 && $clic == 1)
	{
	$failed=1;
	if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
	echo("<p><font color=\"#" . $cTheme->main_warnmsg . "\">You must choose a question in order to process the request.</p>");
	}

if ($verificationdata == "" && $clic == 1)
	{
	$failed=1;
	if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
	echo("<p><font color=\"#" . $cTheme->main_warnmsg . "\">You must answer to the question you picked up.</p>");
	}

if ($verificationdata != "" && $clic == 1)
	{
	if( !(preg_match( "/^[A-Za-z0-9!\ \/\\.+_-]+$/", $verificationdata )) )
		{
		$failed = 1;
		if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
        	echo( "<p><font color=\"#" . $cTheme->main_warnmsg . "\">That answer is not valid, valid characters are a->z, A->Z, 0->9 and !, /, \, ., +, _, - and space.</font>, ");
        	echo( "please choose another answer to the question.</p>");
        	}
	}

if (strtolower($verificationdata)==strtolower($email) && $clic == 1)
	{
	$failed=1;
	if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
	echo("<p><font color=\"#" . $cTheme->main_warnmsg . "\">The answer must be different than the email address.</p>");
	}

if (strtolower($verificationdata)==strtolower($username) && $clic == 1)
	{
	$failed=1;
	if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
	echo("<p><font color=\"#" . $cTheme->main_warnmsg . "\">The answer must be different than the username.</p>");
	}

if (strlen($verificationdata)<4 && $clic == 1)
	{
	$failed=1;
	if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
	echo("<p><font color=\"#" . $cTheme->main_warnmsg . "\">The answer must be at least 4 chars long.</p>");
	}


if ($email != "" and $username != "" and $failed == 0) {
	// Sanitise email
	//


	$expire=time()+7*24*60*60; // 7 days - sendmails email timeout
	$cookie=md5($expire . CRC_SALT_0005 . $username . $email); // special encoding for admin created usernames.

	$language = intval( $language ); // Force int
	// Run any user typed field that hasn't already been sanity checked through the prepare_dbtext() function.
	$verificationdata = prepare_dbtext( $verificationdata );
	pg_safe_exec("insert into pendingusers (user_name,cookie,expire,email,language,question_id,verificationdata) values ('$username','$cookie',$expire,'$lowemail',$language,$question_id,'$verificationdata')");
	$boundary=md5(time());
	custom_mail($email,$mail_subject_new,"To continue the registration process go to $confirm_url?cookie=$cookie&email=$email&username=$username&expire=$expire",
		"From: $mail_from_new\nReply-To: $mail_from_new\nX-Mailer: " . NETWORK_NAME . " Channel Service"
	);

	if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }

	echo "<b>";
	echo "The e-mail has been sent to </b>" . $email . "<b>,<br>with confirmation URL = </b>$confirm_url?cookie=$cookie&email=$email&username=$username&expire=$expire<b><br>\n";
	echo "The user will have 7 days to click on that link to confirm his new username (email confirmation)<br><br>\n";
	echo "The cookie has a special encoding that will allow the user to use the confirmation page in any case of possible lock.\n";
	echo "</b>";

	echo "<br><br>";
	echo "<a href=\"main.php\" target=body>Home</a>\n";

	echo "</body></html>\n\n";

	die;
}
if ($first_error) { std_theme_styles(1); std_theme_body(); echo $headerz; $first_error=0; }
?>

<form method=post>
<input type=hidden name=user_age value=1>
<table border=0 cellspacing=0 cellpadding=0>
<tr>
<td><b>Username&nbsp;&nbsp;</b></td><td align=right><input type=text name=username size=30 value="<?=$username?>" maxlength=12></td></tr>
<tr>
<td><b>Email Address&nbsp;&nbsp;</b></td><td align=right><input type=text size=30 name=email value="<?=$email?>" maxlength=128></td></tr>
<tr>
<td><b>Language&nbsp;&nbsp;</b></td><td align=right><select name="language"><?
// language selector...
$res=pg_safe_exec("select * from languages ORDER by name");
for ($i=0;$i<pg_numrows($res);$i++) {
 $lang=pg_fetch_object($res,$i);
 echo "<option ";
 if ($lang->id==1) { echo "selected "; }
 echo "value=\"" . $lang->id . "\">" . $lang->name . "</option>\n";
}
?></select></td></tr>
<tr>
<td><b>Verif. Question&nbsp;&nbsp;</b></td><td align=right><select name=question_id><option selected value=0>--- click here ---</option><?
for ($x=1;$x<=$max_question_id;$x++) {
	echo "<option value=$x";
	if( $x == $question_id ) {
		echo( " selected" );
	}
	echo ">$question_text[$x]</option>\n";
}
?></select></td></tr>
<tr>
<td><b>Verif. Answer&nbsp;&nbsp;</b></td><td align=right><input type=text size=30 maxlength=30 name=verificationdata value="<?=$verificationdata?>"></td></tr>
<tr>
<td colspan=2 align=center><br><br>
<input type=hidden name=clic value=1>
<input type=submit>
<input type=hidden name=posted value=1>
</td></tr></table>
</form>
</body>
</html>
