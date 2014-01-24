<?
require("../../php_includes/cmaster.inc");

std_init();
$cTheme = get_theme_info();
std_theme_styles(1);
std_theme_body();

if ($admin<901) { // coder only.
	echo "<h1>Admin Access Too Low</h1></body></html>\n\n";
	die;
}

$username=trim($username);
$password=trim($password);
$passcheck=trim($passcheck);

//std_sanitise_username($username);
if ($username=="" || $password=="") {
	echo("<h1>Coder Password Reset</h1>\n");
	echo("<b><blink>YOU SHALL AVOID USING THAT PAGE</blink></b><br><hr>\n");
	echo "<form method=post>\n";
	echo "<pre>\n";
	echo "<font size=+0>\n";
	echo(" Username:\t\t\t<input type=text maxlength=12 name=username>\n");
	echo(" New Password:\t\t\t<input type=password maxlength=15 name=password>\n");
	echo " New Password again:\t<input type=password maxlength=15 name=passcheck>\n";
	echo "<font size=-1>Password will be shown on next page, just to be sure you get the good one, heh ;P</font>\n";
	echo "\n\n<input type=submit value=\"Change It\">&nbsp;&nbsp;&nbsp;&nbsp;This will not ask your for any confirmation !\n";
	echo "</font>\n";
	echo "</pre></form>";
	echo "<hr>\n";
	echo "<font color=#" . $cTheme->main_no . " size=+1><b>This page is for CODERS only</b> (901+)</font>\n";
	echo "</body></html>\n\n";
	die;
}

if ($password!=$passcheck) {
	echo "<h1>Passwords do not match</h1><a href=\"javascript:history.go(-1);\">&lt;&lt;&nbsp;Back</a></body></html>\n\n";
	die;
}

$lowuser=strtolower($username);
$userr = pg_safe_exec("SELECT * FROM users WHERE lower(user_name)='" . $lowuser . "'");
if (pg_numrows($userr)==0) {
	echo "<h1>Unexistant username ('" . $username . "')</h1><a href=\"javascript:history.go(-1);\">&lt;&lt;&nbsp;Back</a></body></html>\n\n";
	die;
}

$user = pg_fetch_object($userr,0);
$blah = pg_safe_exec("SELECT access FROM levels WHERE channel_id='1' AND user_id='" . $user->id . "'");
if (pg_numrows($blah)>0) {
	$row = pg_fetch_object($blah,0);
	if ($admin<=$row->access) {
		echo "<h1>You cant modify CService staff (*) password that have a higher (" . $row->access . ") access than yours (" . $admin . ")</h1><a href=\"javascript:history.go(-1);\">&lt;&lt;&nbsp;Back</a></body></html>\n\n";
		die;
	}
}

	$valid="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.\$*_";
	srand((double) microtime() * 1000000);
	for ($i=0;$i<8;$i++) {
		$salt=$salt . $valid[rand(0,strlen($valid)-1)];
	}
	$crypt=$salt . md5($salt . $password);
	$res=pg_safe_exec("update users set password='" . $crypt . "', " .
		     " last_updated=now()::abstime::int4, " .
		     " last_updated_by='<b>Password Changed by SYSTEM</b> (" . date("YmdHis") . $user_id . ")' " .
		     " where " .
		     "  id='" . $user->id . "'");
	if (!$res) {
		echo "<h1>Something went wrong</h1>\n";
		echo "Following SQL query :<br><br>\n";
		echo "<b>" . $sqry . "</b><br><br>\n";
		echo "couldnt be executed properly, try manual change.<br>\n";
		echo "<a href=\"javascript:history.go(-1);\">&lt;&lt;&nbsp;Back</a>";
		echo "</body></html>\n\n";
		die;
	}
	echo "<h1>Password Changed</h1>\n<br>";
	echo "Successfully set password to [<font size=+2>" . $password . "</font>] for user <b>" . $user->user_name . "</b> (" . $user->id . ")\n<br>";
	echo "<br><a href=\"newpass.php\">&lt;&lt;&nbsp;New Password Reset&nbsp;&gt;&gt;</a></body></html>\n\n";
	die;
?>
