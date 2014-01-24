<?
        require("../../../php_includes/cmaster.inc");
        std_init();
	$cTheme = get_theme_info();
        if ($admin <750) { echo "Page unavailable."; exit; }

        if (!$posted) {

      		std_theme_styles(1);
		std_theme_body("../");
?>
<!-- $Id: verifdatacheck.php,v 1.4 2003/05/25 06:28:20 nighty Exp $ //-->
<H1>Find users from verification answer</h1>
<form method=post>
Verification Answer <input type=text name=vanswer><br>
<input type=hidden name=posted value=1>
<input type=submit value="Locate users">
</form>
<?
        exit;
        }
        $req = "SELECT id,user_name,email,verificationdata FROM users WHERE lower(verificationdata)='" . strtolower($vanswer) . "'";
        $email_dbh = pg_safe_exec( $req );
        if( pg_numrows( $email_dbh ) > 0 ) {
                       	std_theme_styles(1);
			std_theme_body("../");
                        if ($vanswer=="") { $verifdisp = "no verification answer<br><br>"; } else { $verifdisp = "verification answer = " . $vanswer . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>case insensitive</b><br><br>"; }
                        echo "<h1>Users found : " . pg_numrows($email_dbh) . "</h1>\nwith $verifdisp";
                        for ($x=0;$x<pg_numrows($email_dbh);$x++) {
                                $email_dbh_r = pg_fetch_object($email_dbh,$x);
                                $u_id = $email_dbh_r->id;
                                $u_name = $email_dbh_r->user_name;
                                $u_email = $email_dbh_r->email;
                                $u_vd = $email_dbh_r->verificationdata;
                                echo "<li>&nbsp;<a href=\"../users.php?id=$u_id\">$u_name</a> (" . str_replace(" ","&nbsp;",$u_email) . ")";
                                if ($u_vd!="") { echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;verificationdata = <b>" . $u_vd . "</b>\n"; }
                        }
                        echo "<br><br>\n";
                        echo "<a href=\"verifdatacheck.php\">New Search</a>&nbsp;&nbsp;&nbsp;";
			if ($rid>0) {
				echo "<a href=\"../users.php?id=" . $rid . "\">Back to user details</a>";
			} else {
				echo "<a href=\"./\">Back to Admin Reports</a>";
			}
                        echo "</body></html>\n";
	        	die;
        } else {
                std_theme_styles(1);
		std_theme_body("../");
                echo "<h1>Users found : 0</h1>\n";
                echo "No Match for '$vanswer'";
                echo "<br><br><a href=\"verifdatacheck.php\">New Search</a>\n";
        }

?>
</body>
</html>
