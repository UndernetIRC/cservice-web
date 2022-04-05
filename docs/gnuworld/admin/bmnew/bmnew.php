<?
function cs_time_2($ts) {
        global $USER_TZ;
        if ($ts == 0) { return "Never"; }
        if ($USER_TZ=="") {
                $ret=date ("M d Y H:i:s",$ts);
                $ret .= " CSST";
        } else {
                $old_tz = getenv("TZ"); // keep webserver's timezone
                putenv("TZ=$USER_TZ"); // set user's timezone
                //echo "[Default=\"" . $old_tz . "\"]<br>\n";
                //echo "[User's TZ=\"" . getenv("TZ") . "\"]<br>\n";
                $tz_acronym=""; // unused for now
                $ret=date ("M d, Y H:i",$ts);
                $ret .= " " . $tz_acronym;
                putenv("TZ=$old_tz"); // restore webserver's timezone
        }
        return $ret;
}
function checkint ($x)
{
return (is_numeric($x)? intval($x)==$x : false);
}
$debug=on;
$root_dir="../";
// set to on the filters to be enabled by default
$nopurge = off;			// channel flag nopurge
$special = off;		  	// channel flag special
$mia = off;			// channel reviewed /tagged as mia
$nopurgeuser = off;		// has user NoPurge added
$manager = on;			// has no assigned manager / 500
$strict = on;			// enforced filters
$nousers = off;			// userless channels, will over-ride Manager and NoPurge user added filters. 
$def_days_seen=25;		// Default minimum days to search for inactive managers
$nopurge_username='nopurge';	// the NoPurge to check for username, in lower caps
$limit=" LIMIT 1000";		// maximum entries to show. Comment the line to disable limit completly
/* saving filters and passing them via GET */
$days_seen = $def_days_seen;
if ($_GET['saved_f'])
{
$saved_filters=explode("|", base64_decode($_GET['saved_f']));
if ($saved_filters[0]=='off')
	$nopurge = off;	
	else
	$nopurge = on;	
if ($saved_filters[1]=='off')
	$special = off;	
	else
	$special = on;	
if ($saved_filters[2]=='off')
	$mia = off;	
	else
	$mia = on;	
if ($saved_filters[3]=='off')
	$nopurgeuser = off;	
	else
	$nopurgeuser = on;	
if ($saved_filters[4]=='off')
	$manager = off;	
	else
	$manager = on;	
if ($saved_filters[5]=='off')
	$strict = off;	
	else
	$strict = on;	
if (!is_numeric($saved_filters[6]))
	$days_seen = $def_days_seen;	
	else
	$days_seen =$saved_filters[6];	
if ($saved_filters[7]=='off')
	$nousers = off;	
	else
	$nousers = on;	
}
else
if ($_POST['filter'])   // posting new filters overrides any previous selected filter
		{
		$nopurge = $_POST['nopurge'];
		$special = $_POST['special'];
		$mia = $_POST['mia'];
		$nopurgeuser = $_POST['nopurgeuser'];
		$manager = $_POST['manager'];
		$strict = $_POST['strict'];
		$nousers= $_POST['nousers'];
		if (checkint($_POST['days']))
			$days_seen=$_POST['days'];
			else
			$days_seen = $def_days_seen;

		if (!$_POST['strict'])
			$strict=off;
		}
if ($nopurge)
	$saved_filters="".$nopurge."|";
   else 
   $saved_filters="off|";
if ($special)
	$saved_filters.="".$special."|";
   else 
   $saved_filters.="off|";
if ($mia)
	$saved_filters.="".$mia."|";
   else 
   $saved_filters.="off|";
if ($nopurgeuser)
	$saved_filters.="".$nopurgeuser."|";
	   else 
   $saved_filters.="off|";
if ($manager)
	$saved_filters.="".$manager."|";
	   else 
   $saved_filters.="off|";
if ($strict)
	$saved_filters.="".$strict."|";
	   else 
   	$saved_filters.="off|";
if ($days_seen)
	$saved_filters.="".$days_seen."|";
	   else 
   	$saved_filters.="".$def_days_seen."|";
if ($nousers)
	$saved_filters.="".$nousers."";
	   else 
   	$saved_filters.="off";
$saved_filters=base64_encode($saved_filters);

/* end filters */
	require("../../../".$root_dir."php_includes/cmaster.inc");
	std_init();
	$cTheme = get_theme_info();
	std_theme_styles(1);
	std_theme_body("../".$root_dir);

	if ($debug==on) {
		echo "<br>Nopurge: ".$nopurge."<br>";
		echo "Special: ".$special."<br>";
		echo "Mia: ".$mia."<br>";
		echo "Nopurgeuser: ".$nopurgeuser."<br>";
		echo "manager: ".$manager."<br>";
		echo "strict: ".$strict."<br>";
		echo "Days: ".$days_seen."<br>";
		echo "No Users: ".$nousers."<br>";
	}
		
	if ($admin<750 && !acl(MIA_VIEW)) {
		echo("Oi! What are you doing here eh?");
		exit;
	}
	
	define(	MIA_TAG_FLAG,1024);	// as defined per 'gnuworld/doc/cservice.sql' : DO NOT CHANGE
	define( NO_PURGE_TAG,1);	// as defined per 'gnuworld/doc/cservice.sql' : DO NOT CHANGE
	define( SPECIAL_TAG,2);	// as defined per 'gnuworld/doc/cservice.sql' : DO NOT CHANGE
/* batch tag/untag selected channels */
if ($_POST['action'])
	{
	foreach ($_POST as $value => $test) 
	{
	if ($test==on)
		{
		$data=explode("_", $value);
//		print_r($data);
			if ($data[3]=='TAG' || $data[3]=='UNTAG') 
			{
			if ($data[2]==md5( (int)$data[1] . CRC_SALT_0016 )) 
				{
				// get channel info
				$r = pg_safe_exec("SELECT * FROM channels WHERE id='".(int)$data[1]."'");
				if ($o = pg_fetch_object($r)) 
					{
					$oldflags = $o->flags; $flags = $oldflags;
					// do the action
					if ($data[3]=='TAG')
							$flags = $oldflags|MIA_TAG_FLAG;
					if ($data[3]=='UNTAG')
							$flags = $oldflags&~MIA_TAG_FLAG;
						}
					if ($debug==on)
					echo $oldflags." -> UPDATE channels SET flags='".$flags."',last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int WHERE id='".(int)$data[1]."'<br>";
					else
					pg_safe_exec("UPDATE channels SET flags='".$flags."',last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int WHERE id='".(int)$data[1]."'");										
					} else 
						{
						$oldflags = -1;
						}
				}
			}
		}
	}
/* end batch tag/untag */
	/* individual tag/untag channel via GET */
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
				if ($debug==on)
				echo $oldflags." -> UPDATE channels SET flags='".$flags."',last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int WHERE id='".(int)$_GET['channel_id']."'<br>";
				else
				pg_safe_exec("UPDATE channels SET flags='".$flags."',last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int WHERE id='".(int)$_GET['channel_id']."'");
				
			} else {
				$oldflags = -1;
			}
		}
	}
	/* end individual tag/untag */
?>
<!-- $Id: boredmanagers.php,v 1.9 2006/08/10 11:50:13 nighty Exp $ //-->
<h1>Missing Managers (<?php echo $days_seen;?> days or more)</h1><h3>
<a href="<?php echo $root_dir;?>index.php">Back</a></h3>
	<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" >
	<table>
	<tr>
	<td><strong>Minimum days missing :</strong> <input type="text" name="days" value="<?php echo $days_seen;?>" size="3" /></td></tr>
	<tr>
	<td><strong>Show channels with:</strong>
	<input type="checkbox" name="nopurge" <?php if ($nopurge==on) echo 'checked="checked"'; ?> /> No Purge flag
	<input type="checkbox" name="special" <?php if ($special==on) echo 'checked="checked"'; ?> /> Special flag
	<input type="checkbox" name="mia" <?php if ($mia==on) echo 'checked="checked"'; ?> /> MIA tag
	<input type="checkbox" name="nopurgeuser" <?php if ($nopurgeuser==on) echo 'checked="checked"'; ?> /> NoPurge added
	<input type="checkbox" name="manager" <?php if ($manager==on) echo 'checked="checked"'; ?> /> Manager
	<input type="checkbox" name="nousers" <?php if ($nousers==on) echo 'checked="checked"'; ?> /> No Users
	</td>
	</tr>
	<tr><td><strong>Enforce filters?</strong><input type="checkbox" name="strict" <?php if ($strict==on) echo 'checked="checked"'; ?> /> </td></tr>
	<tr><td><input type="submit" value="Go!" name="filter" />
	</table>
	</form>
<hr>
<?
// warning: works only on pgsql version 7.2+ (!) If you see SQL errors, you are using an obsolete version: UPGRADE!!!
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
if (($nousers==on) && ((!$manager) || ($manager==off)) )
	$query="SELECT channels.id AS channel_id, channels.name AS channel_name, channels.flags FROM channels WHERE ";
else
{
  $query .= "WHERE ";
  $query .= "users.id=levels.user_id AND ";
  $query .= "levels.channel_id=channels.id AND ";
  $query .= "users.id=users_lastseen.user_id AND ";
}
  $query .= "channels.registered_ts>0 ";
// NOPURGE flag check
if ($strict==off)
	{
	if ($nopurge==on) {
 	$query .= "AND (channels.flags & ".NO_PURGE_TAG.")=".NO_PURGE_TAG." "; 
 	}
}  
elseif ($strict==on)
	{
	if ($nopurge==on) {
	 $query .= "AND (channels.flags & ".NO_PURGE_TAG.")=".NO_PURGE_TAG." "; 
	}
 	elseif (($nopurge==off) || (!$nopurge)) {	
 	$query .= "AND (channels.flags & ".NO_PURGE_TAG.")!=".NO_PURGE_TAG." "; 
	}
	}
//SPECIAL flag check
if ($strict==off)
	{
	if ($special==on) {
 	$query .= "AND (channels.flags & ".SPECIAL_TAG.")=".SPECIAL_TAG." "; 
 	}
	}  
elseif ($strict==on)
	{
	if ($special==on) {
	 $query .= "AND (channels.flags & ".SPECIAL_TAG.")=".SPECIAL_TAG." "; 
	}
 	elseif (($special==off) || (!$special)) {	
 	$query .= "AND (channels.flags & ".SPECIAL_TAG.")!=".SPECIAL_TAG." "; 
	}
	}
if ($strict==off)
  {
  if ($mia==on) {
  	$query .= "AND (channels.flags & ".MIA_TAG_FLAG.")=".MIA_TAG_FLAG." ";
  } 
  }
  elseif ($strict==on)
  {
    if ($mia==on) {
  	$query .= "AND (channels.flags & ".MIA_TAG_FLAG.")=".MIA_TAG_FLAG." ";
  } elseif (($mia==off) || (!$mia)) {
  	$query .= "AND (channels.flags & ".MIA_TAG_FLAG.")!=".MIA_TAG_FLAG." ";
  }
  }
// Listing channels with no manager and/or NoPurge user added
if ($strict==on)
	{
	if ($manager==on)
		{
		$query .= " AND users_lastseen.last_seen<((date_part('epoch', CURRENT_TIMESTAMP)::int-((".$days_seen."*24*60*60)+1))) AND ";
		$query .= "levels.access=500 ";
		$query .= "AND channels.id IN ( SELECT channels.id FROM users,users_lastseen,levels,channels WHERE users.id=levels.user_id AND levels.channel_id=channels.id AND users.id=users_lastseen.user_id AND (users_lastseen.last_seen<((date_part('epoch', CURRENT_TIMESTAMP)::int-((".$days_seen."*24*60*60)+1)))) AND channels.registered_ts>0 AND levels.access=500 AND lower(users.user_name) !='".$nopurge_username."')";

		if ($nopurgeuser==on)
		$query.=" AND channels.id IN ( SELECT channels.id FROM users,levels,channels WHERE users.id=levels.user_id AND levels.channel_id=channels.id AND lower(users.user_name)='".$nopurge_username."' AND channels.registered_ts>0 AND levels.access=500)";
		else
		$query.=" AND channels.id NOT IN ( SELECT channels.id FROM users,levels,channels WHERE users.id=levels.user_id AND levels.channel_id=channels.id AND lower(users.user_name)='".$nopurge_username."' AND channels.registered_ts>0 AND levels.access=500)";
		if ($nousers==on)
		$query.=" AND channels.id NOT IN ( SELECT distinct channels.id FROM users, levels, channels where users.id=levels.user_id AND levels.channel_id=channels.id AND channels.registered_ts>0 AND ( levels.access < 500 OR levels.access > 500))";
		else
		$query.=" AND channels.id IN ( SELECT distinct channels.id FROM users, levels, channels where users.id=levels.user_id AND levels.channel_id=channels.id AND channels.registered_ts>0 AND ( levels.access < 500 OR levels.access > 500))";
		
		}
		else
		{
		$query .= "AND channels.id NOT IN ( SELECT channels.id FROM users,users_lastseen,levels,channels WHERE ";
		  $query .= "users.id=levels.user_id AND ";
 		 $query .= "levels.channel_id=channels.id AND ";
		  $query .= "users.id=users_lastseen.user_id AND ";
		  $query .= "lower(users.user_name)!='nopurge' ";
		  $query .= "AND ";
		  $query .= "channels.registered_ts>0 AND ";
		  $query .= "levels.access=500";
		  $query .= " ) ";
		if ($nopurgeuser==on)
		$query.=" AND channels.id IN ( SELECT channels.id FROM users,levels,channels WHERE users.id=levels.user_id AND levels.channel_id=channels.id AND lower(users.user_name)='".$nopurge_username."' AND channels.registered_ts>0 AND levels.access=500)";
		else
		$query.=" AND channels.id NOT IN ( SELECT channels.id FROM users,levels,channels WHERE users.id=levels.user_id AND levels.channel_id=channels.id AND lower(users.user_name)='".$nopurge_username."' AND channels.registered_ts>0 AND levels.access=500)";
		if ($nousers==on)
		$query.=" AND channels.id NOT IN ( SELECT distinct channels.id FROM users, levels, channels where users.id=levels.user_id AND levels.channel_id=channels.id AND channels.registered_ts>0 AND ( levels.access < 500 OR levels.access > 500) )";
		else
		$query.=" AND channels.id IN ( SELECT distinct channels.id FROM users, levels, channels where users.id=levels.user_id AND levels.channel_id=channels.id AND channels.registered_ts>0 AND ( levels.access < 500 OR levels.access > 500))";	
			
		}
	}
	else
	{
	if ($manager==on)
		{
		$query .= "AND levels.access=500 ";
		$query .= "AND channels.id IN ( SELECT channels.id FROM users,users_lastseen,levels,channels WHERE users.id=levels.user_id AND levels.channel_id=channels.id AND users.id=users_lastseen.user_id AND (users_lastseen.last_seen<((date_part('epoch', CURRENT_TIMESTAMP)::int-((".$days_seen."*24*60*60)+1)))) AND channels.registered_ts>0 AND levels.access=500 AND lower(users.user_name) !='".$nopurge_username."')";
		if ($nopurgeuser==on)
		$query.=" AND channels.id IN ( SELECT channels.id FROM users,levels,channels WHERE users.id=levels.user_id AND levels.channel_id=channels.id AND lower(users.user_name)='".$nopurge_username."' AND channels.registered_ts>0 AND levels.access=500)";
		if ($nousers==on)
		$query.=" AND channels.id NOT IN ( SELECT channels.id FROM users, levels, channels where users.id=levels.user_id AND levels.channel_id=channels.id AND channels.registered_ts>0 AND ( levels.access < 500 OR levels.access > 500))";
		}
		else
		{
		if (((!$nopurgeuser) || ($nopurgeuser==off)) && ((!$nousers) || ($nousers==off)) && ((!$nopurge) || ($nopurge==off)) && ((!$special) || ($special==off)) && ((!$mia) || ($mia==off)))
			{
			echo '<h3>Please select at least one filter, or enfore them.</h3>';
			die;
			}
		if ($nopurgeuser==on)
		$query.=" AND channels.id IN ( SELECT channels.id FROM users,levels,channels WHERE users.id=levels.user_id AND levels.channel_id=channels.id AND lower(users.user_name)='".$nopurge_username."' AND channels.registered_ts>0 AND levels.access=500)";
		if ($nousers==on)
		$query.=" AND channels.id NOT IN ( SELECT channels.id FROM users, levels, channels where users.id=levels.user_id AND levels.channel_id=channels.id AND channels.registered_ts>0 AND ( levels.access < 500 OR levels.access > 500))";
		}	
	}

if (($nousers==on) && ((!$manager) || ($manager==off)) )
	$query.=" ORDER BY channel_name asc";
else
$query.=" ORDER BY access desc, users_lastseen.last_seen asc";
$no_limit_query=$query;
$query.=$limit;

if ($debug==on)
 echo "<b>SQL Query:</b><br>" . $query . ";<br><br>";
 echo '<script type="text/javascript" src="js/jquery-latest.js"></script> 
<script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
<script type="text/javascript" id="js">
$(document).ready(function() { 
    // call the tablesorter plugin 
    $("table").tablesorter({ 
        // sort on the fifth column, order asc 
        sortList: [[5,0]] 
    }); 
}); 
</script>
';
echo '<script type="text/javascript">';
echo "
      checked = false;
      function checkedAll () {
        if (checked == false){checked = true}else{checked = false}
	for (var i = 0; i < document.getElementById('selections').elements.length; i++) {
	  document.getElementById('selections').elements[i].checked = checked;
	}
      }
</script>";
  $res=pg_safe_exec($query);
  $bm_count=0;
  $showed_ids=array();
  echo("<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?saved_f=".$saved_filters."\" id=\"selections\" ><table border=1 width=1200 cellspacing=0 cellpadding=3 bgcolor=#" . $cTheme->table_bgcolor . " id=\"channels\" class=\"tablesorter\">\n");
  echo("<thead><tr bgcolor=#" . $cTheme->table_headcolor . "><th><font color=#" . $cTheme->table_headtextcolor . "><b>Select</b></font></th><th><font color=#" . $cTheme->table_headtextcolor . "><b>User Name</b></font></th><th><font color=#" . $cTheme->table_headtextcolor . "><b>Channel</b></font></th><th><font color=#" . $cTheme->table_headtextcolor . "><b>&nbsp;</b></font></th><th><font color=#" . $cTheme->table_headtextcolor . "><b>Chan Flags</b></font></th><th colspan=\"2\"><font color=#" . $cTheme->table_headtextcolor . "><b>Since</b></font></th></tr></thead><tbody>\n");
  for ($i=0;$i<pg_numrows($res);$i++) {
	$row = pg_fetch_object($res,$i);
/*	if ($debug==on)
	{
	print_r($row);
	echo '<br>';
	}
*/
	//if (!((int)$row->flags & 1) && !((int)$row->flags & 2))  // pgsql '&' operator fixed display (!)
		if (($row->channel_id !=1) && (!in_array($row->channel_id, $showed_ids)))
		{
		$showed_ids[]=$row->channel_id;
		if ($row->access < 500)
			$row->user_name=$nopurge_username;
		$bm_count++;
		$ts = time();$ls = $row->last_seen;
		$t_val = $ts - $ls;$t_dur = abs($t_val);
		$the_duration=drake_duration($t_dur);
		if (($row->flags & MIA_TAG_FLAG)==MIA_TAG_FLAG)
			$action="UNTAG";
		else
			$action="TAG";
			if (strtolower($row->user_name)==$nopurge_username)
				 echo("<tr><td><input type=\"checkbox\" name=\"check_".$row->channel_id."_".md5( (int)$row->channel_id . CRC_SALT_0016 )."_".$action."\" /></td><td> </td>");
				 else
		echo("<tr><td><input type=\"checkbox\" name=\"check_".$row->channel_id."_".md5( (int)$row->channel_id . CRC_SALT_0016 )."_".$action."\" /></td><td><a href=\"".$root_dir."../users.php?id=" . $row->user_id . "\" target=users>".
			$row->user_name ."</a></td>");
			echo (
			"<td><a href=\"".$root_dir."../channels.php?id=" . $row->channel_id . "\" target=channels>" .
			$row->channel_name ."</a></td>");
			if (($row->flags & MIA_TAG_FLAG)==MIA_TAG_FLAG) {
				echo "<td><a href=\"".$root_dir."bmnew/bmnew.php?action=UNTAG&channel_id=".(int)$row->channel_id."&SID=".md5( (int)$row->channel_id . CRC_SALT_0016 )."&saved_f=".$saved_filters."\">Set MIA flag OFF</a></td>";
			} else {
				echo "<td><a href=\"".$root_dir."bmnew/bmnew.php?action=TAG&channel_id=".(int)$row->channel_id."&SID=".md5( (int)$row->channel_id . CRC_SALT_0016 )."&saved_f=".$saved_filters."\">Set MIA flag ON</a></td></td>";
			}
			$flags='';
			if (($row->flags & NO_PURGE_TAG)==NO_PURGE_TAG)
				$flags.="NoPurge ";
			if (($row->flags & SPECIAL_TAG)==SPECIAL_TAG)	
				$flags.="Special ";
			if (($row->flags & MIA_TAG_FLAG)==MIA_TAG_FLAG)	
				$flags.="MIA ";	
			echo '<td><strong>'.$flags.'</strong></td>';		
			if ((strtolower($row->user_name)==$nopurge_username) || (!$row->user_name))
			echo '<td colspan="2" align="center"> n/a </td></tr>';
			else
			echo("<td>" . cs_time_2($row->last_seen) . "</td><td> $the_duration</td>" .
			"</tr>\n
			");
	}
 }
  echo '</tbody> <tr ><td>All: <input type=\'checkbox\' name=\'checkall\' onclick=\'checkedAll();\'>
</td><td colspan="6" align="center" ><input type="submit" name="action" value="Set MIA ON/OFF for selected" /></td></tr></table>'; ?>
  <?
if($debug==on)
print_r($showed_ids);
  echo "</form><h3>\n";
   if ($bm_count==0) {
  echo("0 Results ".$out_of."");
  }
  if ($bm_count==1) {
  echo($bm_count . " Result  ".$out_of."");
  }
  if ($bm_count>1) {
  echo($bm_count . " Results  ".$out_of."");
  }
  echo "</h3>";
  echo 'rev. 0.2';
// rev. 0.2 - fixed 'No users'-on and 'Mananager'-off search results
?>
</body></html>
