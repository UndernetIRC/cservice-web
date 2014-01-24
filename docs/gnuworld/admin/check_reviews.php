<?
	unset($min_lvl);
	$min_lvl=800;
	require("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();
	std_theme_styles(1);
	std_theme_body("../");
	if ($admin<$min_lvl && $user_id!=292083 && $user_id!=3303 && $user_id!=308) { // 292083 == zep, 3303 == DaveB, 308 == Hodari
		echo("Oi! What are you doing here eh?");
		exit;
	}
?>
<!-- $Id: check_reviews.php,v 1.3 2003/01/08 16:51:31 nighty Exp $ //-->
<h1>Check reviews</h1>
<hr>
<form name=checkreviews method=post>
<input type=hidden name=action value=1>
<h4>
Show last <input type=text name=lastnum size=5 maxlength=2 value=15> channel reviews<br>
Show specific reviewer only? <select name=spcreview><option value=0 selected>-- NO --</option>
<?
	// get the list of the people that have active reviews to count in "pending".
	$q = "SELECT pending.reviewed_by_id,users.user_name FROM pending,users WHERE pending.reviewed_by_id=users.id ORDER BY pending.reviewed_by_id";
	$r = pg_safe_exec($q);
	$last_id = -1;
	while ($o = pg_fetch_object($r)) {
		if ($last_id != $o->reviewed_by_id) {
			echo "<option value=" . $o->reviewed_by_id .">" . $o->user_name . "</option>\n";
			$last_id = $o->reviewed_by_id;
		}
	}
?>
</select><br><br>
<input type=submit value=Go!>
</h4>
</form>
<?
$sCodes[0] = "INCOMING";
$sCodes[1] = "PENDING (traffic)";
$sCodes[2] = "PENDING (notification)";
$sCodes[3] = "ACCEPTED";
$sCodes[4] = "WITHDRAWN";
$sCodes[8] = "READY FOR REVIEW";
$sCodes[9] = "REJECTED";

if ($_POST["action"]==1) {
	echo "<hr>\n";
	if (($_POST["lastnum"]+0)<=0 || ($_POST["lastnum"]+0)>99) { $ln = 15; } else { $ln = $_POST["lastnum"]; }
	$Aq = "SELECT users.user_name,pending.created_ts,channels.name,channels.id,pending.status,pending.decision_ts,pending.decision,pending.last_updated FROM pending,channels,users WHERE ";
	if ($_POST["spcreview"]>0) {
		$Aq .= "pending.reviewed_by_id='" . ($_POST["spcreview"]+0) . "' AND ";
	}
	$Aq .= "pending.reviewed_by_id=users.id AND pending.channel_id=channels.id ORDER BY pending.last_updated DESC LIMIT " . $ln;
	$Ar = pg_safe_exec($Aq);
	if (pg_numrows($Ar)>0) {
		if (pg_numrows($Ar)>1) { $schan = "s"; } else { $schan = ""; }
		echo "<h3>Listing " . pg_numrows($Ar) . " channel" . $schan . " ...</h3>\n";
		echo "<table width=100% cellspacing=0 cellpadding=1 border=1>";
		echo "<tr bgcolor=#4c4c4c><td><font color=#ffffff><b>Channel</b></font></td>";
		echo "<td><font color=#ffffff><b>Reviewer</b></font></td>";
		echo "<td><font color=#ffffff><b>Status - Date/Reason</b></font></td></tr>";
		while ($Ao = pg_fetch_object($Ar)) {
			echo "<tr><td valign=top><a href=\"../view_app.php?id=" . $Ao->created_ts . "-" . $Ao->id . "\" target=_blank><pre><font size=+0>";
			echo $Ao->name . "</font></pre></a></td>";
			echo "<td valign=top><pre><font size=+0>";
			echo $Ao->user_name . "</font></pre></td>";
			echo "<td valign=top><pre><font size=+0>";
			echo "<big><b>" . $sCodes[$Ao->status] . "</b></big>";
			if ($Ao->status == 3) { echo "&nbsp;(<a href=\"../channels.php?id=" . $Ao->id . "\" target=_blank>view channel details</a>)"; }
			if ($Ao->decision_ts>0) {
				echo "\n(" . cs_time($Ao->decision_ts) . ": " . str_replace("\n","",str_replace("<br>"," ",stripslashes($Ao->decision))) . ")";
			} else {
				echo "\n<b>Reviewed on :</b> " . cs_time($Ao->last_updated);
			}
			echo "</font></pre></td></tr>";
		}
		echo "</table>\n";
	} else {
		// this can happen if in between the form load and your post for searching, someone reject or accepts an app
		// reviewed by someone else (the review info will be replaced by the one about the people that reject/accept it)
		echo "<h3>No channels have been reviewed by this user.</h3>\n";
	}
	echo "<hr><br>\n";
}
?>
<a href="index.php">Back to reports</a><br><br>
</body>
</html>
