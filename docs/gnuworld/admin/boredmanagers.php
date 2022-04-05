<?
	require("../../../php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();
	std_theme_styles(1);
	std_theme_body("../");
	if ($admin<600) {
		echo("Oi! What are you doing here eh?");
		exit;
	}
	define(	MIA_TAG_FLAG,		1024); // as defined per 'gnuworld/doc/cservice.sql' : DO NOT CHANGE
	if ($_GET['action']=='TAG' || $_GET['action']=='UNTAG') {
		if ($_GET['SID']==md5( (int)$_GET['channel_id'] . CRC_SALT_0016 )) {
			// get channel info
			$r = pg_safe_exec("SELECT * FROM channels WHERE id='".(int)$_GET['channel_id']."'");
			if ($o = pg_fetch_object($r)) {
				$oldflags = $o->flags; $flags = $oldflags;
				// do the action
				switch ($_GET['action']) {
					case 'TAG':
						$flags = $oldflags|MIA_TAG_FLAG;
						break;
					case 'UNTAG':
						$flags = $oldflags&~MIA_TAG_FLAG;
						break;
				}
				pg_safe_exec("UPDATE channels SET flags='".$flags."',last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int WHERE id='".(int)$_GET['channel_id']."'");

			} else {
				$oldflags = -1;
			}
		}
	}
	$lv = "";
	if ($_GET['limit_view']=='TAG' || $_GET['limit_view']=='NOTAG') {
		$lv = $_GET['limit_view'];
	} else {
		$lv = "NOTAG"; // default value
	}

?>
<!-- $Id: boredmanagers.php,v 1.9 2006/08/10 11:50:13 nighty Exp $ //-->
<h1>Missing Managers (25 days or more)</h1><h3>
<a href="index.php">Back</a></h3>
	<form name=dummy>
		<select name=tshow onChange="update_view(this)">
			<option <?=(($lv=="NOTAG")?"selected ":"")?>value="NOTAG">Show only unreviewed channels</option>
			<option <?=(($lv=="TAG")?"selected ":"")?>value="TAG">Show only reviewed/tagged channels</option>
		</select>
<hr>
<?
  $query = "";
  $query .= "SELECT ";

  $query .= "levels.user_id AS user_id, ";
  $query .= "channels.id AS channel_id, ";
  $query .= "users.user_name AS user_name, ";
  $query .= "channels.name AS channel_name, ";
  $query .= "channels.flags, ";
  $query .= "levels.access AS access, ";
  $query .= "users_lastseen.last_seen AS last_seen ";

  $query .= "FROM users,users_lastseen,levels,channels ";

  $query .= "WHERE ";

  $query .= "users.id=levels.user_id AND ";
  $query .= "levels.channel_id=channels.id AND ";
  $query .= "users.id=users_lastseen.user_id AND ";
  $query .= "users_lastseen.last_seen<=(date_part('epoch', CURRENT_TIMESTAMP)::int-25*24*60*60) AND ";
  $query .= "channels.registered_ts>0 AND ";
// warning: works only on pgsql version 7.2+ (!) If you see SQL errors, you are using an obsolete version: UPGRADE!!!
  $query .= "(channels.flags & 1)!=1 AND "; // NOPURGE
  $query .= "(channels.flags & 2)!=2 AND "; // SPECIAL
  if ($lv=="TAG") {
  	$query .= "(channels.flags & ".MIA_TAG_FLAG.")=".MIA_TAG_FLAG." AND ";
  } elseif ($lv=="NOTAG") {
  	$query .= "(channels.flags & ".MIA_TAG_FLAG.")!=".MIA_TAG_FLAG." AND ";
  }
  $query .= "levels.access=500 AND ";

  $query .= "channels.id NOT IN ( ";
  $query .= "SELECT channels.id FROM ";
  $query .= "users,users_lastseen,levels,channels ";

  $query .= "WHERE ";

  $query .= "users.id=levels.user_id AND ";
  $query .= "levels.channel_id=channels.id AND ";
  $query .= "users.id=users_lastseen.user_id AND ";
  $query .= "(";
  $query .= "users_lastseen.last_seen>(date_part('epoch', CURRENT_TIMESTAMP)::int-25*24*60*60) ";
  $query .= "OR ";
  $query .= "lower(users.user_name)='nopurge') ";
  $query .= "AND ";
  $query .= "channels.registered_ts>0 AND ";
  $query .= "levels.access=500";
  $query .= " ) ";


  $query .= " ORDER BY users_lastseen.last_seen";

  //echo "<b>SQL Query:</b><br>" . $query . ";<br><br>";

  $res=pg_safe_exec($query);

  $bm_count=0;
  echo("<table border=1 width=600 cellspacing=0 cellpadding=3 bgcolor=#" . $cTheme->table_bgcolor . ">\n");
  echo("<tr bgcolor=#" . $cTheme->table_headcolor . "><td><font color=#" . $cTheme->table_headtextcolor . "><b>User Name</b></font></td><td><font color=#" . $cTheme->table_headtextcolor . "><b>Channel</b></font></td><td><font color=#" . $cTheme->table_headtextcolor . "><b>&nbsp;</b></font></td><td><font color=#" . $cTheme->table_headtextcolor . "><b>Since</b></font></td></tr>\n");
  for ($i=0;$i<pg_numrows($res);$i++) {
	$row = pg_fetch_object($res,$i);
	if (!((int)$row->flags & 1) && !((int)$row->flags & 2)) { // pgsql '&' operator fixed display (!)
		$bm_count++;
		$ts = time();$ls = $row->last_seen;
		$t_val = $ts - $ls;$t_dur = abs($t_val);
		$the_duration=drake_duration($t_dur);
		echo("<tr><td><a href=\"../users.php?id=" . $row->user_id . "\" target=users>" .
			$row->user_name ."</a></td>" .
			"<td><a href=\"../channels.php?id=" . $row->channel_id . "\" target=channels>" .
			$row->channel_name ."</a></td>");
			if (($row->flags & MIA_TAG_FLAG)==MIA_TAG_FLAG) {
				echo "<td><input type=button onClick=\"untag(".(int)$row->channel_id.",'".md5( (int)$row->channel_id . CRC_SALT_0016 )."')\" value=\"UnTag\"></td>";
			} else {
				echo "<td><input type=button onClick=\"tagreview(".(int)$row->channel_id.",'".md5( (int)$row->channel_id . CRC_SALT_0016 )."')\" value=\"Tag as reviewed\"></td>";
			}
			echo("<td>" . cs_time($row->last_seen) . " ($the_duration)</td>" .
			"</tr>\n");
	}
 }
  echo "</table><h3>\n";
  if ($bm_count==0) {
  echo("No More Missing Managers");
  }
  if ($bm_count==1) {
  echo($bm_count . " Missing Manager");
  }
  if ($bm_count>1) {
  echo($bm_count . " Missing Managers");
  }
  echo "</h3>";
?>
<script language="JavaScript">
	<!--
		function untag(chan_id,sid) {
			location.href='boredmanagers.php?limit_view=<?=$lv?>&channel_id='+parseInt(chan_id)+'&action=UNTAG&SID='+sid;
		}
		function tagreview(chan_id,sid) {
			location.href='boredmanagers.php?limit_view=<?=$lv?>&channel_id='+parseInt(chan_id)+'&action=TAG&SID='+sid;
		}
		function update_view(sel) {
			var cc = sel.options[sel.selectedIndex].value;
			location.href='boredmanagers.php?limit_view='+cc;
		}
	//-->
</script>
</form>
</body></html>
