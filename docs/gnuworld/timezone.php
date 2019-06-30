<?
	require("../../php_includes/cmaster.inc");
/* $Id: timezone.php,v 1.5 2004/09/13 12:29:02 nighty Exp $ */
	$zonetab_name = "/usr/share/zoneinfo/zone.tab";
	std_init();
$cTheme = get_theme_info();
	if ($user_id==0) {
		die("Error: you must be logged in.");
	}
	if (($crc == md5( $ts . $user_id . $HTTP_USER_AGENT )) && ($tzname!="")) {
		// set the timezone cookie and go back to right.php
		// if the timezone is a known one
		$rr = pg_safe_exec("SELECT COUNT(*) AS count FROM timezones WHERE deleted!=1 AND tz_name='" . $tzname . "'");
		$oo = pg_fetch_object($rr);
		if ($oo->count==0) {
			echo "Timezone Name ERROR !<br>\n";
		} else {
			pg_safe_exec("UPDATE users SET tz_setting='" . $tzname . "' WHERE id='" . $user_id . "'");
			$ENABLE_COOKIE_TABLE = 1;
			pg_safe_exec("UPDATE webcookies SET tz_setting='" . $tzname . "' WHERE user_id='" . $user_id . "'");
			$ENABLE_COOKIE_TABLE = 0;
			header("Location: right.php");
		}
		die;
	}

	// reminder :
	// cat /usr/share/zoneinfo/zone.tab |grep \/ | awk '{ print "<option value=\"" $3 "\">" $3 " (" $1 ")</option>" }'
	//

	// generate the timezone choice form from db
	std_theme_styles(1);
	std_theme_body();

	$zets = time();
	$zecrc = md5( $zets . $user_id . $HTTP_USER_AGENT );
	if (preg_match("users.php",$HTTP_REFERER)) {
		echo "<h1>Change your current timezone</h1>\n";
	} else {
		echo "<h1>Pick up your current timezone</h1>\n";
	}
?>
	<br><br>
	<form name=tz action=timezone.php method=post>
	<select name=tzname>
	<option value="">-- click here --</option>
<?
	$tz_query = "SELECT * FROM timezones";
	$res = pg_safe_exec($tz_query);
	if (pg_numrows($res)==0) { // table empty, first run : let's fill it.
		$tmp_fname = "/tmp/webtz.tmp." . md5(time());
		if (file_exists($zonetab_name)) { $fp = fopen($zonetab_name,"r"); } else { $fp=0; }
		if (!$fp) { // invalid zone.tab file.
			if (file_exists("../../zone.tab")) { $fp = fopen("../../zone.tab","r"); } else { $fp=0; }
			$zonetab_name = "../../zone.tab";
			if (!$fp) {
				echo "</select><br><br>\n";
				echo "<h1><br>Unable to read file '$zonetab_name' and table 'timezones' is empty, check your config.<br></h1>\n";
				echo "</form></body></html>\n\n";
				die;
			}
		}
		fclose($fp);
		$fp = fopen($zonetab_name,"r");
		$fs = filesize($zonetab_name);
		$alllines = explode("\n",fread($fp,$fs));
		fclose($fp);
		$l_index=0;
		for ($x=0;$x<count($alllines);$x++) {
			$linepos=0;
			if (preg_match("/",$alllines[$x])) {
				$ccode = "";
				$tznam = "";
				// first .. ccode...
				$let = substr($alllines[$x],$linepos,1);
				while ($let!=" " && $let!="" && $let!="\x09") {
					$ccode .= $let;
					$linepos++;
					$let = substr($alllines[$x],$linepos,1);
				}
				// skip blank spaces and next text block
				while (($let==" " || $let=="\x09") && $let!="") {
					$linepos++;
					$let = substr($alllines[$x],$linepos,1);
				}
				while ($let!=" " && $let!="" && $let!="\x09") {
					$linepos++;
					$let = substr($alllines[$x],$linepos,1);
				}
				while (($let==" " || $let=="\x09") && $let!="") {
					$linepos++;
					$let = substr($alllines[$x],$linepos,1);
				}
				// .. then .. tznam ...
				while ($let!=" " && $let!="" && $let!="\x09") {
					$tznam .= $let;
					$linepos++;
					$let = substr($alllines[$x],$linepos,1);
				}
				$lines[$l_index]=$ccode . "¤" . $tznam;
				$l_index++;
			}
		}
		for ($x=0;$x<count($lines);$x++) {
			$tz_temp = explode("¤",$lines[$x]);
			$tz_countrycode = $tz_temp[0];
			$tz_name = $tz_temp[1];
			if ($tz_name!="") {
				$query = "INSERT INTO timezones (tz_name,tz_countrycode,tz_acronym,deleted,last_updated) VALUES ('$tz_name','$tz_countrycode','',0,now()::abstime::int4)";
				pg_safe_exec($query);
			}
		}
		$res = pg_safe_exec("SELECT * FROM timezones WHERE deleted!=1 ORDER BY tz_name");
	} else {
		$res = pg_safe_exec("SELECT * FROM timezones WHERE deleted!=1 ORDER BY tz_name");
	}
	// generate drop down list.
	for ($x=0;$x<pg_numrows($res);$x++) {
		$obj = pg_fetch_object($res,$x);
		$tz_name = $obj->tz_name;
		$tz_countrycode = $obj->tz_countrycode;
		if ($tz_name == $USER_TZ) { $blah = " selected"; } else { $blah = ""; }
		echo "<option$blah value=\"$tz_name\">$tz_name ($tz_countrycode)</option>\n";
	}

?>
	</select><br><br>
<?
	if (preg_match("users.php",$HTTP_REFERER)) {
		echo "<input type=submit value=\" Change my default timezone to the above ! \"><br><br><a href=\"users.php?id=$user_id\">go back</a>\n";
	} else {
		echo "<input type=submit value=\" Record the above as my default timezone ! \">\n";
	}
?>
	<input type=hidden name=ts value=<? echo $zets ?>>
	<input type=hidden name=crc value=<? echo $zecrc ?>>
	</form>
</body>
</html>
