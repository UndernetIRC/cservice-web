<?

	$CAN_EDIT = 1;
	$CAN_ADD = 2;
$ENABLE_COOKIE_TABLE=0;

	include("../../../php_includes/cmaster.inc");
	std_init();
	if ($admin<800) {
		echo "You are not allowed to use that page. (access too low)";
		die;
	}
	
	if ($mode=="addcs" || $mode=="addusr" || $mode=="remove") {
	
		if ($mode=="remove") {
		
			$query = "DELETE FROM helpmgr_users WHERE user_id='$id'";
			
			pg_safe_exec($query);

	
			header("Location: usr_mgr_redir.php");
			die;		
		
		
		} else {
			if ($mode=="addusr") {
				$tmp1 = pg_safe_exec("SELECT id FROM users WHERE lower(user_name)='" . strtolower($username) . "'");
				if (pg_numrows($tmp1)==0) {

					echo "<html>\n";
					echo "<head><title>HELP TEXT USER MANAGER</title></head>\n";
					echo "$GLOBALS[standard_body]\n";
					echo "<font size=+1><u>ERROR</u>:<br>User '<b>$username</b>' is NOT a valid CService username.<br></font>\n";
					echo "<br><br><a href=\"usr_mgr.php\">&lt;&lt;&nbsp;Back</a>\n";

					die;
				}
				$tmp2 = pg_fetch_object($tmp1,0);
				$da_user_id = $tmp2->id;
			} else {
				$da_user_id = $aid;
			}
	
			$tmp3 = pg_safe_exec("SELECT * FROM helpmgr_users WHERE user_id='$da_user_id'");
			if (pg_numrows($tmp3)>0) {

				echo "<html>\n";
				echo "<head><title>HELP TEXT USER MANAGER</title></head>\n";
				echo "$GLOBALS[standard_body]\n";
				echo "<font size=+1><u>ERROR</u>:<br>User '<b>$username</b>' is already added in the member list.<br></font>\n";
				echo "<br><br><a href=\"usr_mgr.php\">&lt;&lt;&nbsp;Back</a>\n";

				die;			
			}
	
			$tmp4 = pg_safe_exec("SELECT * FROM levels WHERE user_id='$da_user_id' AND channel_id=1 AND access>799");
			if (pg_numrows($tmp4)>0) {

				echo "<html>\n";
				echo "<head><title>HELP TEXT USER MANAGER</title></head>\n";
				echo "$GLOBALS[standard_body]\n";
				echo "<font size=+1><u>ERROR</u>:<br>User '<b>$username</b>' is implicitely in the member list (800+).<br></font>\n";
				echo "<br><br><a href=\"usr_mgr.php\">&lt;&lt;&nbsp;Back</a>\n";

				die;
			}
	
			$query = "INSERT INTO helpmgr_users (user_id,flags,language_id) VALUES ('$da_user_id','$flags','$languageid')";
			
			//echo "$query<br><br>";
			pg_safe_exec($query);
	
			header("Location: usr_mgr_redir.php");
			die;
	
		}
	
	}
	
?>
<html>
<head><title>HELP TEXT USER MANAGER</title></head>
<? echo "$GLOBALS[standard_body]\n"; ?>
<h2><b>User Manager for HELP TEXT MANAGER</b><br></h2>
<a href="index.php">&lt;&lt;&nbsp;Back</a>

<form name=addcs action=usr_mgr.php method=post>
<input type=hidden name=mode value=addcs>
<table border=0 cellspacing=5 cellpadding=5>
<tr bgcolor=#cccccc>
<td>
Add <select name=aid><option value=0>-- select --</option><?

$res = pg_safe_exec("SELECT * FROM users,levels WHERE users.id=levels.user_id AND levels.channel_id=1 AND levels.access<800");
$ras = pg_safe_exec("SELECT * FROM helpmgr_users");
if (pg_numrows($ras)>0) {
	$tst_str = "";
	for ($x=0;$x<pg_numrows($ras);$x++) {
		$row = pg_fetch_object($ras,$x);
		$tst_str .= "-" . $row->user_id . "!";	
	}
} else {
	$tst_str = "";
}
if (pg_numrows($res)>0) {
	for ($x=0;$x<pg_numrows($res);$x++) {
		$row = pg_fetch_object($res,$x);
		if (!preg_match("/-/" . $row->id . "!",$tst_str)) {
			echo "<option value=" . $row->id . ">" . $row->user_name . " (" . $row->access . ")</option>\n";		
		}
	}
}
?></select> and allow him/her to edit <select name=languageid><option value=0>* All Languages *</option><?

$toto = pg_safe_exec("SELECT * FROM languages ORDER BY id");
if (pg_numrows($toto)>0) {
	for ($x=0;$x<pg_numrows($toto);$x++) {
		$tutu = pg_fetch_object($toto,$x);
		echo "<option value=" . $tutu->id . ">" . $tutu->name . "</option>\n";
	}
}
?></select>.<br>
Create COMMAND ability : <input type=radio name=flags value=3> Yes &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type=radio name=flags value=1 checked> No

</td>
<td>
<input type=submit value="ADD THIS OFFICIAL">
</td>
</tr>
</table>

</form>

<? if ($admin>900) { ?>

<form name=addusr action=usr_mgr.php method=post>
<input type=hidden name=mode value=addusr>
<font color=#990000><b>CODER ONLY FEATURE (* Level > 900)</b> - Warning! This adds any valid username.</font><br>(only coders see lines with that cyan background in that page)
<table border=0 cellspacing=5 cellpadding=5>
<tr bgcolor=#99cccc>
<td>
Add <input type=text name=username size=20 maxlenght=12> and allow him/her to edit <select name=languageid><?

$toto = pg_safe_exec("SELECT * FROM languages ORDER BY id");
if (pg_numrows($toto)>0) {
	for ($x=0;$x<pg_numrows($toto);$x++) {
		$tutu = pg_fetch_object($toto,$x);
		echo "<option value=" . $tutu->id . ">" . $tutu->name . "</option>\n";
	}
}

?></select>.<br>
Create COMMAND ability : <input type=radio name=flags value=3> Yes &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type=radio name=flags value=1 checked> No

</td>
<td>
<input type=submit value="ADD THIS USERNAME">
</td>
</tr>
</table>

</form>

<? } ?>

<h2>Current Members</h2>
are currently members all levels 800+ and the following others ....<br><br>
<?
$res = pg_safe_exec("SELECT * FROM helpmgr_users");
if (pg_numrows($res)==0) {
	echo "<h1>No current members.</h1>";
	echo "</body></html>\n\n";
	die;
}
?>
<form>
<table border=0 cellspacing=2 cellpadding=5>
<tr bgcolor=#eeeeee>
<td><b>username</b></td>
<td><b>CAN_EDIT ?</b></td>
<td><b>CAN_ADD ?</b></td>
<td><b>Affected Language</b></td>
<td><b>* Level</b></td>
<td><b>Action</b></td>
</tr>
<?
	for ($x=0;$x<pg_numrows($res);$x++) {
		$row = pg_fetch_object($res,$x);
		if ($row->flags & $CAN_EDIT) { $can_edit = "<font color=#009900><b>YES</b></font>"; } else { $can_edit = "<font color=#990000><b>NO</b></font>"; }
		if ($row->flags & $CAN_ADD) { $can_add = "<font color=#009900><b>YES</b></font>"; } else { $can_add = "<font color=#990000><b>NO</b></font>"; }
		$nq = "SELECT * FROM users WHERE id='" . $row->user_id . "'";
		$nl = "SELECT * FROM levels WHERE user_id='" . $row->user_id . "' AND channel_id='1'";
		$rq = pg_safe_exec($nq);
		$rl = pg_safe_exec($nl);
		if (pg_numrows($rq)==0) {
			$username = "<i>unknown</i> (removed ?uh!)";
		} else {
			$rii = pg_fetch_object($rq,0);
			$username = $rii->user_name;
		}
		if (pg_numrows($rl)==0) {
			$star_level = 0;
		} else {
			$roo = pg_fetch_object($rl,0);
			$star_level = $roo->access;
		}
		if ($row->language_id ==0) { $affected_lang = "<font color=#ff00ff><b>ALL</b></font>"; } else {
			$blah = pg_safe_exec("SELECT name FROM languages WHERE id='" . $row->language_id . "'");
			if (pg_numrows($blah)==0) {
				$affected_lang = "<i>unknown</i>";
			} else {
				$unf = pg_fetch_object($blah,0);
				$affected_lang = $unf->name;
			}
		}
		if ($star_level==0) {
			if ($admin>900) {
				echo "<tr bgcolor=#99cccc>\n";
				echo "<td><a href=\"../users.php?id=" . $row->user_id . "\">$username</a></td>\n";
				echo "<td align=center>$can_edit</td>\n";
				echo "<td align=center>$can_add</td>\n";
				echo "<td align=center>$affected_lang</td>\n";
				echo "<td align=center>$star_level</td>\n";
				echo "<td><input type=button value=\"Remove\" onClick=\"location.href='usr_mgr.php?mode=remove&id=" . $row->user_id . "';\"></td>\n";
				echo "</tr>\n";
			}
		} else {
			echo "<tr bgcolor=#ffffff>\n";
			echo "<td><a href=\"../users.php?id=" . $row->user_id . "\">$username</a></td>\n";
			echo "<td align=center>$can_edit</td>\n";
			echo "<td align=center>$can_add</td>\n";
			echo "<td align=center>$affected_lang</td>\n";
			echo "<td align=center>$star_level</td>\n";
			echo "<td><input type=button value=\"Remove\" onClick=\"location.href='usr_mgr.php?mode=remove&id=" . $row->user_id . "';\"></td>\n";
			echo "</tr>\n";		
		}		

	
	}
?>

</table>
</form>
</body>
</html>
