<?
        require("../../php_includes/cmaster.inc");
        std_init();
$cTheme = get_theme_info();

        if ($admin <1) { echo "Page unavailable."; exit; }

	$email = trim($email);

        if (!$email) {
std_theme_styles(1);
std_theme_body();

?>
<H1>Find user from email</h1>
<form method=post>
Email Address <input type=text name=email>&nbsp;(wildcard: *)<br>
<input type=submit value="Locate user">
</form>
<?
        exit;
        }

        // check email RFC compliance
	if( !(preg_match( "/^[\*A-Za-z0-9_+-.]+@[\*A-Za-z0-9.-]+$/", $email )) ) {
	        std_theme_styles(1); std_theme_body();
		echo( "<p><font color=\"#" . $cTheme->main_warnmsg . "\">Your email address mask is invalid.</font>  It must contain a @, it must be from a valid domain, and it can only contain alpha-numeric " );
		echo( "characters (a-zA-Z0-9) or the ., the -, or the * character.</p>" );
		echo "<br><br><a href=\"javascript:history.go(-1);\">go back</a>\n";
		echo "</body></html>\n";
		die;
	}

        $lowemail = str_replace("*","%",strtolower( $email )); // lower case pgsql cares
        $req = "SELECT id,user_name,email FROM users WHERE lower(email) LIKE '" . $lowemail . "' LIMIT 502";
        $email_dbh = pg_safe_exec( $req );
        if( pg_numrows( $email_dbh ) > 0 ) {
                if (pg_numrows($email_dbh)==1) {
                        $email_dbh_r=pg_fetch_object($email_dbh,0);
                        header("Location: users.php?id=$email_dbh_r->id");
                } else {
                	if (pg_numrows($email_dbh)>500) {
                        	std_theme_styles(1); std_theme_body();
                        	echo "<h1>More than 500 matches to '$email*'</h1>\n";
				echo "Please narrow your query.<br><br>\n";
				echo "<a href=\"javascript:history.go(-1);\">Go Back</a>\n";
                        	echo "</body></html>\n";
	        		die;
                	} else {
                        	std_theme_styles(1); std_theme_body();
                        	echo "<h1>" . pg_numrows($email_dbh) . " matches for '$email'</h1>\n";
                        	for ($x=0;$x<pg_numrows($email_dbh);$x++) {
                                	$email_dbh_r = pg_fetch_object($email_dbh,$x);
                                	$u_id = $email_dbh_r->id;
                                	$u_name = $email_dbh_r->user_name;
                                	$u_email = $email_dbh_r->email;
	                                echo "<li>&nbsp;<a href=\"users.php?id=$u_id\">$u_name</a> (" . str_replace(" ","&nbsp;",$u_email) . ")\n";
                        	}
                        	echo "</body></html>\n";
	        		die;
	        	}
	        }
        } else {
                std_theme_styles(1); std_theme_body();
                echo "No Match for '$email'";
                echo "<br><br><a href=\"javascript:history.go(-1);\">go back</a></body></html>\n";
        }

?>
