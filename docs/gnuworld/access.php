<?

require('../../php_includes/cmaster.inc');

$validnumbers="0123456789";
$validletters="-abcdefghijklmnopqrstuvwxyz_.#" . $validnumbers;

/* $Id: access.php,v 1.3 2002/05/20 23:58:03 nighty Exp $ */

std_init();
if ($admin==0) {
	die("Temporarily out of service.");
}
$cTheme = get_theme_info();
echo "<html>\n";
echo "<head><title>Access</title>\n";
std_theme_styles();
std_theme_body();

// THEMES INTEGRATION NOT FINISHED (PHP File Deprecated)

if (strspn($channel,$validletters)!=strlen($channel)) {
	echo("Abuse! Abuse!");
	exit;
}

if ($action == "edit") {
	$access=get_channel_access($database,$user_id,$channel);

	$query="select " .
  "  levels.channel_id, " .
  "  levels.user_id," .
  "  levels.access," .
  "  levels.flags," .
  "  levels.suspend_expires, " .
  "  levels.suspend_by," .
  "  levels.last_modif," .
  "  users.user_name " .
  " from " .
  "  levels," .
  "  users" .
  " where " .
  "  channel_id = " . $channel . " and " .
  "  users.id=$user and users.id=levels.user_id";
 	$levels = pg_safe_exec($database,$query);
//	echo "Q: $query";

 	if (pg_numrows($levels)==0) {
		echo("<H1>Error: No such access for $user on $channel</h1>");
	} else {
		$level = pg_fetch_object($levels,$row);
		echo "<CENTER>

  <TABLE cellpadding=2 cellspacing=0 STYLE=\"{ border-color: #000000; border-width: 3px; border-style: solid}\"  BORDER=0 width=100% BGCOLOR=white>
  <TR><TD colspan=2 align=center><H1>Access for $level->user_name</H1></TD></TR>
  <TR BGCOLOR=003151><TD colspan=2>&nbsp</TD></TR>
  <FORM METHOD=post>
  <INPUT TYPE=\"hidden\" name=\"action\" value=\"save\">

\n";

		if ((($access > $level->access) && ($access >=$level_modinfo)) || ($admin >= $level_modinfo)) {

			echo "
			  <TR>
			  	<TD width=150><FONT COLOR=black size=-1><b>Access Level</b></TD>
			  	<TD><INPUT TYPE=\"text\" NAME=\"new_access\" VALUE=\"$level->access\" size=6 maxlength=4></TD>
			  </TR>";

  		} else {

	  		echo "
			  <TR>
			  	<TD width=150><FONT COLOR=black size=-1><b>Access Level</b></TD>
			  	<TD><font size=-1>$level->access</TD>
			  </TR>";

  		}

  		if ((($access > $level->access) && ($access >=$level_modinfo)) || ($user_id == $user) || ($admin >= $level_modinfo)) {

			if ($level->flags&0x01) { $aop_check="CHECKED"; }
			if ($level->flags&0x08) { $av_check="CHECKED"; }

			echo "
			<TR>
			  	<TD VALIGN=top><FONT COLOR=black size=-1><b>AutoMode</B></TD>
			  	<TD><FONT COLOR=black><INPUT TYPE=radio NAME=new_automode VALUE=\"AutoOp\" $aop_checked>AutoOp
					<BR><INPUT TYPE=radio NAME=new_automode VALUE=\"AutoVoice\" $av_checked>AutoVoice
					<BR><INPUT TYPE=radio NAME=new_automode VALUE=\"None\">None
				</TD>
			</TR>
			";
		}

		if ((($access > $level->access) && ($access >=$level_modinfo)) || ($admin >= $level_modinfo)) {

			echo "
			  <TR>
			  	<TD><FONT COLOR=black size=-1><b>Set Suspend</b></TD>
			  	<TD><INPUT TYPE=\"text\" NAME=\"new_suspend\" VALUE=\"$level->suspend\"></TD></TR>
			  	";

  		}
		if ($level->suspend_expires>time()) {
        		$expire=$level->suspend_expires;
        		$now=time();
		        $duration=$expire-$now;
			echo("
		<TR>
			<td>&nbsp;</td><td bgcolor=red><FONT COLOR=white size=-1><EM><b>Current Suspend by " . $level->suspend_by . " expires in " . drake_duration($duration) . "</TD></TR>");
 		}

		// TODO show last modif
		echo "
			<TR>
				<TD><FONT COLOR=black>Last Modificaton</TD>
				<TD><FONT COLOR=black>By " . $level->last_modified_by . " at ". cs_time($level->last_modif) ."</TD>
			</TR>
			";
	}
	echo "
	<TR><TD ALIGN=CENTER COLSPAN=2><INPUT TYPE=\"submit\" Value=\"Save Changes\"></TD></TR>
	</TABLE>";
} else if ($action == "save") {
	echo "<PRE>
	Access:   $new_access
	AutoMode: $new_automode
	Suspend:  $new_suspend
</PRE>";
	echo "<H1>Changes not committed to disk</h1>";
} else {
	echo "<H1>Error: Improperly accessed</H1>";
}


?>
</body></html>

