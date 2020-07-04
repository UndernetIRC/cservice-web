<?
require('../../php_includes/cmaster.inc');

/* $Id: channels.php,v 1.19 2005/11/25 11:14:14 nighty Exp $ */

/* NOTE: all chars are valid excepted \x20 \x07 \x00.
$validnumbers="0123456789";
$validletters="-abcdefghijklmnopqrstuvwxyz_.#" . $validnumbers;
*/

std_init();
$cTheme = get_theme_info();
$nrw_lvl = 0;
if (acl(XWEBAXS_2)) { $nrw_lvl = 1; }
if (acl(XWEBAXS_3)) { $nrw_lvl = 2; }

// Find the channel.
// -----------------
// There are two ways to do this, one is by "id=" which is used internally
// to the site.  It's slightly faster.
// The other way is by name, designed to be used by urls directed from
// irc etc.

echo "<html><head><title>.</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body();

$name = trim($name);

if ($id!="") {

/*
	if (strspn($id,$validnumbers)!=strlen($id)) {
		echo("Abuse! Abuse!"); //TODO: Log this
		exit;
	}
*/
  	$channels = pg_safe_exec("SELECT * from channels WHERE id='$id' AND registered_ts>0");

} else if ($name!="") {

	if (ord($name)!=0x23)
		$name="#" . $name;

  	$name=strtolower($name);

/*
  	if (strspn($name,$validletters)!=strlen($name)) {
		echo("Abuse! Abuse!"); //TODO: Log this
		exit;
	}
*/
	$channels = pg_safe_exec("SELECT * FROM channels WHERE lower(name)='$name' AND registered_ts>0");

} else {
?>
<h1>Enter channel name</h1>
Enter a channel name that you wish to find out more about<br>
<form method="get">
<input type=text name="name">
<input type=submit value="Go Baby!">
</form>
</body></html>
<?
	exit;
}

// If we are here then we're working on one channel only.

if ((pg_numrows($channels)==0)) {
	echo("<center><h1>That channel does not exist</h1></center>");
	echo "</body></html>\n\n";
	exit;
}

if (pg_numrows($channels)>1) {
	echo "<center><h1>Weird?! multiple channels matching name/id ??</h1></center>\n";
	echo "<b>Listing entries</b> (id) #name [reg_ts]<br><br>\n";
	for ($x=0;$x<pg_numrows($channels);$x++) {
		$roo = pg_fetch_object($channels,$x);
		echo "(" . $roo->id . ") " . $roo->name . " [" . $roo->registered_ts . "]<br>\n";
	}
	echo "<br><br>- END OF REPORT.\n";
	echo "</body></html>\n\n";
	die;
}
unset($access);
$channel = pg_fetch_object($channels,0);
$access=get_channel_access($database,$user_id,$channel->id);

if (($channel->id == 1 || (int)$channel->flags & 0x00000002 ) && ($admin < 1) && $nrw_lvl<=0 && $access==0) {
	// hide * and channels tagged as SPECIAL for non-* persons.
	echo "<center><h2>Sorry, you can't view details of that channel.</h2></center>";
	echo "</body></html>\n\n";
	die;
}

unset($edit);
unset($force);
$edit=0;
$force=0;

switch($action) {
	case "edit":
		//$edit=1;
		$edit=0; // disabled for now
		break;
	case "force":
		if ($admin>=600) {
			$edit=1;
			$force=1;
			$access=$admin;
			log_channel($id,6,"");
		}
		break;
	default:
		if ($admin>0) { local_seclog("View '" . $channel->name . "' (" . $channel->id . ")"); } else {
			if ($admin==0 && $access>0) { local_seclog("View '" . $channel->name . "' (" . $channel->id . ") as chanop level " . (int)$access); }
		}
		break;
};

function set_flag($allowed,&$num,$bit,$bool)
{
  global $database;
  if (!$allowed)
    return;
  switch ($bool) {
    case "off":
	$num=(int)$num&~(int)$bit;
	break;
    case "on":
	$num=(int)$num|(int)$bit;
	break;
    default:
	$num=(int)$num&~(int)$bit;
	break;
  }
}

function set_number($allowed,&$num,$value,$min,$max)
{
  if (!$allowed)
    return;
  if ($min<=$value && $value<=$max || $value=0)
    $num=$value*1.0;
}

function set_text($allowed,&$text,$value)
{
  if (!$allowed)
    return;
 //   $text = str_replace("\\", "\\\\", $value );
 //   $text=str_replace("'","\'",$value);
 $text = $value;
 // nighty - BUG066 (semi resolved)
 // pgSQL only need parsing on single quote since update syntax uses it as a delimiter
 // all other chars ar OK .. to insert a single quote .. double it in the SQL cmd.
}




if ($button == "Save Changes") {
	// I really should have thought of a for loop sooner..
	if (((is_suspended($user_id,$channel->name)==1) || ((is_suspended("",$channel->name)==1)) && ($admin==0)) || (($admin==0) && (int)$channel->flags & 0x00000100)) {
		echo "Not allowed.";
		die;
	}
	if ($admin<=0) { die("Not Allowed"); }
	set_flag($access>=$level_set_nopurge ,$channel->flags,0x00000001,$nopurge);
	set_flag($access>=$level_set_special ,$channel->flags,0x00000002,$special);
	set_flag($access>=$level_set_noreg   ,$channel->flags,0x00000004,$noreg);
	//set_flag($access>=$level_set_neverreg,$channel->flags,0x00000008,$neverreg);
	set_flag($access>=$level_set_suspend ,$channel->flags,0x00000010,$suspend);
	set_flag($access>=$level_set_tempman ,$channel->flags,0x00000020,$tempman);
	set_flag($access>=$level_set_caution ,$channel->flags,0x00000040,$cautioned);
	set_flag($access>=$level_set_vacation,$channel->flags,0x00000080,$vacation);
	set_flag($access>=$level_set_secret  ,$channel->flags,0x00000100,$secret);

	//set_flag($access>=$level_set_alwaysop ,$channel->flags,0x00010000,$alwaysop);
	set_flag($access>=$level_set_strictop ,$channel->flags,0x00020000,$strictop);
	set_flag($access>=$level_set_noop     ,$channel->flags,0x00040000,$noop);
	set_flag($access>=$level_set_autotopic,$channel->flags,0x00080000,$autotopic);

	set_number($access>=$level_set_massdeoppro,$channel->mass_deop_pro,$massdeop,0,10);
	set_number($access>=$level_set_floodpro   ,$channel->flood_pro,$floodpro,0,20);

	set_text($access>=$level_set_url,$channel->url,$url);
	set_text($access>=$level_set_desc,$channel->description,$desc);
	set_text($access>=$level_set_keywords,$channel->keywords,$keywords);

	if (($access >= $level_status2) || ($admin > 0)){
		set_text($access>=$level_set_mode,$channel->channel_mode,$mode);
		//set_text($access>=$level_set_mode,$channel->channel_key,$key);
		//set_number($access>=$level_set_mode,$channel->channel_limit,$limit,0,1000);
	}


	$updateq = "";

	$c_limit = $channel->channel_limit;
	if ($c_limit == "") { $c_limit = 0; }

	$c_desc = trim($channel->description);
	$c_mode = trim($channel->channel_mode);
	$c_keywords = trim($channel->keywords);
	$c_url = trim($channel->url);

	$count_chars =0;
	for ($x=0;$x<strlen($c_desc);$x++) {
		if (!(substr($c_desc,$x,1)=="\\" && substr($c_desc,$x+1,1)=="'")) {
			$count_chars++;
		}

		if (ord(substr($c_desc,$x,1))<32) {
			echo "<b>ERROR</b>: Channel description contains invalid control chars.<br>\n";
			echo "<br><a href=\"javascript:history.go(-1);\">go back</a>\n";
			echo "</body></html>\n\n";
			die;
		}
	}
	//echo "COUNT = $count_chars\n";
	if ($count_chars>80) {
		echo "<b>ERROR</b>: Channel description contains too much chars.<br>\n";
		echo "<br><a href=\"javascript:history.go(-1);\">go back</a>\n";
		echo "</body></html>\n\n";
		die;
	}

	$count_spc=0;
	for ($x=0;$x<strlen($c_mode);$x++) {
		if (ord(substr($c_mode,$x,1))<32) {
			echo "<b>ERROR</b>: Channel Modes contains invalid control chars.<br>\n";
			echo "<br><a href=\"javascript:history.go(-1);\">go back</a>\n";
			echo "</body></html>\n\n";
			die;
		}
		if (ord(substr($c_mode,$x,1))==32) {
			$count_spc++;
		}
	}
	$test = 0;
	if ($count_spc==0) {
		$test = strspn($c_mode,"+stnimlkprSTNIMLKPR")!=strlen($c_mode);
	} else if ($counr_spc==1) {
		$tmp = explode(" ",$c_mode);
		if (!preg_match("/k/",strtolower($tmp[0])) && !preg_match("/l/",strtolower($tmp[0]))) {
			$t2 = 1;
			$t1 = 1;
		} else {
			$t1 = strspn($tmp[0],"+stnimlkprSTNIMLKPR")!=strlen($tmp[0]);
			if (preg_match("/l/",strtolower($tmp[0])) && preg_match("/k/",strtolower($tmp[0]))) {
				$t2 = 1;
			} else {
				if (preg_match("/l/",strtolower($tmp[0]))) {
					$t2 = strspn($tmp[1],"0123456789")!=strlen($tmp[1]);
				} else if (preg_match("/k/",strtolower($tmp[0]))) {
					$t2 = strspn($tmp[1],",'\\\"+-\x20\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F")==strlen($tmp[1]);
				}
			}
		}
		$test = ($t1 || $t2);
	} else {
		$tmp = explode(" ",$c_mode);
		if (!preg_match("/k/",strtolower($tmp[0])) && !preg_match("/l/",strtolower($tmp[0]))) {
			$test = 1;
		} else {
			$t1 = strspn($tmp[0],"+stnimlkprSTNIMLKPR")!=strlen($tmp[0]);
			if (preg_match("/l/",strtolower($tmp[0])) && preg_match("/k/",strtolower($tmp[0]))) {
				if (strpos(strtolower($tmp[0]),"l")<strpos(strtolower($tmp[0]),"/k/")) {
					$t2 = strspn($tmp[1],"0123456789")!=strlen($tmp[1]);
					$t3 = strspn($tmp[2],",'\\\"+-\x20\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F")==strlen($tmp[2]);
				} else {
					$t2 = strspn($tmp[2],"0123456789")!=strlen($tmp[2]);
					$t3 = strspn($tmp[1],",'\\\"+-\x20\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F")==strlen($tmp[1]);
				}
				$test = ($t1 || $t2 || $t3);
			} else {
				if (preg_match("/l/",strtolower($tmp[0]))) {
					$t2 = strspn($tmp[1],"0123456789")!=strlen($tmp[1]);
				} else if (preg_match("/k/",strtolower($tmp[0]))) {
					$t2 = strspn($tmp[1],",'\\\"+-\x20\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F")==strlen($tmp[1]);
				}
				$test = ($t1 || $t2);
			}
		}
	}
	if ($test) {
		echo "<b>ERROR</b>: Channel Modes are invalid.<br>\n";
		echo "<br><a href=\"javascript:history.go(-1);\">go back</a>\n";
		echo "</body></html>\n\n";
		die;
	}

	$c_keywords = htmlentities($c_keywords);

	$updateq = " UPDATE channels SET flags=" . $channel->flags . ", " .
	         "  mass_deop_pro = " . $channel->mass_deop_pro . "," .
		 "  flood_pro = " . $channel->flood_pro . "," .
		 "  url = '" . $c_url . "'," .
		 "  description = '" . $c_desc . "'," .
		 "  keywords = '" . $c_keywords . "'," .
		 "  last_updated = now()::abstime::int4, " .
		 "  channel_mode = '" . $c_mode . "' " .
		 " WHERE id = " . $channel->id;

	//echo "Query:<br>$updateq<br><br>";
	pg_safe_exec($updateq);


} else if ($button=="Add this user") {
	if ((is_suspended($user_id,$channel->name)==1) || (is_suspended("",$channel->name)==1)) {
		echo "Not allowed.";
		echo "</body></html>\n\n";
		die;
	}
	if ($admin<=0 || (int)$add_access<=0) { die("Not Allowed"); }
	echo("$add_user_id<br>");
	$add_access=$add_access+0; // quick simple way of sanitising
	// this seems to be messing up the script.
	//std_sanitise_username($add_user_id);
	$low_user_name_to_add = strtolower($add_user_id);
	echo("$low_user_name_to_add<br>");
	$res=pg_safe_exec("select * from users where lower(user_name)='$low_user_name_to_add'");
	if (pg_numrows($res)<1) {
		echo("Can't find user $add_user_id");
	} else {
		$roo = pg_fetch_object($res,0);
		$bla = pg_safe_exec("select * from noreg where type=4 and lower(user_name)='" . strtolower($roo->user_name) . "'");
		if (pg_numrows($bla)>0) {
			echo "Sorry, this username is FRAUDULOUS you can't add it to any channel.";
			echo "</body></html>\n\n";
			die;
		}
	}
	if ($add_access>=$access) {
		echo("Can't add acceses higher or equal to your own");
	} else {
		$user2=pg_fetch_object($res,0);
		// must check to be sure the channel does not have this user.
		$check=pg_safe_exec("select user_id from levels where user_id = $user2->id and channel_id = $channel->id");

		if (pg_numrows($check)>0) {

			echo("This user already exists on this channel");

		}
		else {
			echo "A: uid: $add_user_id(".$user2->id.") lvl: $add_access<br>\n";
			$query=("insert into levels" .
				" (channel_id,user_id,access,flags,added,added_by,".
				"  last_modif,last_modif_by,last_updated,deleted) " .
				" values " .
				"  (" . $channel->id . "," . $user2->id . "," .
				"$add_access ,0,now()::abstime::int4," .
				"  '" . $user->user_name . " (via web)'," .
				"  now()::abstime::int4,'" .$user->user_name . " (via web)'," .
				"  now()::abstime::int4,0)");
			//pg_safe_exec($query);
		}
	}
}
if ($admin>0) { $cid_info = "<font size=-1>(" . $channel->id . ")</font>"; } else { $cid_info = ""; }
echo("
<TABLE WIDTH=100% cellspacing=0 cellpadding=2 BORDER=0 BGCOLOR=#" . $cTheme->table_bgcolor . ">
<tr>
	<td colspan=2>
	<center><h1>Channel Information for $channel->name $cid_info</h1>");
if (preg_match("/MSIE/",$HTTP_USER_AGENT)) {
	echo "<font size=-2>mIRC v5.81+/MSIE Users only: Click <A HREF=\"irc://" . IRC_SERVER . "/" . substr($channel->name,1) . "\">here</a> to join this channel</font>";
 }
	echo ("</center></Td>
</tr>
<form method=\"post\">\n");

if (($access >= $level_status) || ($admin > 0)) {
		echo("<tr><td colspan=2 bgcolor=#" . $cTheme->table_sepcolor . "><font size=-1 color=#" . $cTheme->table_septextcolor . "><em><b>Channel Settings</font></b></em></td></tr><tr><td colspan=2><font size=-1>");


	if (!$force && $edit && (int)$channel->flags & 0x00000100) {
		echo "<font color=#ff1111><b>Cannot EDIT channel, it is LOCKED !</b></font><br><br>\n";
	}

	if (((is_suspended($user_id,$channel->name)==1) || ((is_suspended("",$channel->name)==1)) && ($admin==0)) || ((int)$channel->flags & 0x00000100)) {
		$edit = 0;
	}


	if ($channel->flags != 0 || $edit) {

		flag($edit && $access>=$level_set_nopurge,(int)$channel->flags & 0x00000001,"* No Purge","nopurge","Y");
		flag($edit && $access>=$level_set_special,(int)$channel->flags & 0x00000002,"* Special","special","Y");
		flag($edit && $access>=$level_set_noreg,(int)$channel->flags & 0x00000004,"* No Register","noreg","Y");
		flag($edit && $access>=$level_set_suspend,(int)$channel->flags & 0x00000010,"* Channel Suspension","suspend","Y");
		flag($edit && $access>=$level_set_tempman,(int)$channel->flags & 0x00000020,"* Temp Manager","tempman","Y");
		flag($edit && $access>=$level_set_caution,(int)$channel->flags & 0x00000040,"* Cautioned","cautioned","Y");
		flag($edit && $access>=$level_set_caution,(int)$channel->flags & 0x00000080,"* Manager on vacation","vacation","Y");
		flag($edit && $access>=$level_set_secret,(int)$channel->flags & 0x000000100, "* Channel Locked","secret","Y");

		//flag($edit && $access>=$level_set_alwaysop,(int)$channel->flags & 0x00010000," Always Op","alwaysop","Y");
		flag($edit && $access>=$level_set_strictop,(int)$channel->flags & 0x00020000," Strict Op","strictop","Y");
		flag($edit && $access>=$level_set_noop,(int)$channel->flags & 0x00040000," No Op","noop","Y");
		flag($edit && $access>=$level_set_autotopic,(int)$channel->flags & 0x00080000," Auto Topic","autotopic","Y");
		//flag($edit && $access>=$level_set_oponly,(int)$channel->flags & 0x00100000," Op Only","oponly","Y");
		flag($edit && $access>=$level_set_autojoin,(int)$channel->flags & 0x00200000," Auto Join","autojoin","Y");
		$cpurged=0;
	} else {
		if ($channel->registered_ts==0) {
			echo "<font size=-1>Purged</font>&nbsp;<img src=images/protect.gif border=0><br>\n";
			$cpurged=1;
		} else {
			$cpurged=0;
		}
	}



	number($edit && $access>=$level_set_massdeoppro,$channel->mass_deop_pro,"Mass Deop Protection","massdeop");
	number($edit && $access>=$level_set_floodpro,$channel->flood_pro,"Flood Protection","floodpro");
	if ($edit && $access>=$level_set_url) {
		if ($channel->url!="" && !preg_match("/^https?:\/\//",$channel->url)) {
			echo "<b>Channel Homepage: </b><input type=text size=50 name=url maxlength=75 value=\"http://" . $channel->url . "\"><br>\n";
		} else {
			echo "<b>Channel Homepage: </b><input type=text size=50 name=url maxlength=75 value=\"" . $channel->url . "\"><br>\n";
		}
	} elseif ($channel->url != "") {
		$chan_url = (!preg_match("/^https?:\/\//", $channel->url) ? 'http://' : '') . htmlspecialchars($channel->url);
		echo "<b>Channel Homepage: </b><a href=\"" . $chan_url . "\" target=\"_blank\">" . $chan_url . "</a><br>\n";
	}

	if ($edit && $access>=$level_set_desc) {
		echo "<b>Description: </b><input type=text name=desc size=50 maxlength=80 value=\"" . $channel->description . "\"><br>\n";
	} else {
		echo "<b>Description: </b>" . htmlspecialchars($channel->description) . "<br>\n";
	}
// nighty - BUG062
//	text($edit && $access>=$level_set_url,$channel->url,"Channel Homepage","url");
//	text($edit && $access>=$level_set_desc,$channel->description,"Description","desc");
	text($edit && $access>=$level_set_keywords,$channel->keywords,"Keywords","keywords");
	if (($access >= $level_status2) || ($admin >0)) {
		text($edit && $access>=$level_set_mode,$channel->channel_mode,"Saved channel mode","mode");
	}
	echo "</td></tr>";
}
echo("<tr><td colspan=2 bgcolor=#" . $cTheme->table_sepcolor . "><font size=-1 color=#" . $cTheme->table_septextcolor . "><b><em>Channel History</font></b></td></tr>");
echo("<tr><td width=150><font size=-1><b>Registered on</b></td><td><font size=-1> ". cs_time($channel->registered_ts) ."</td></tr>\n");
echo("<tr><td width=150><font size=-1><b>Channel Created:</b></td><td><font size=-1> ". cs_time($channel->channel_ts) ."</td></tr>\n");
echo("<tr><td width-150><font size=-1><b>Last Change:</b></td><td><font size=-1> ". cs_time($channel->last_updated) ."</td></tr>\n");

if ($channel->comment!="" && $admin>0) {
	echo("<tr bgcolor=#" . $cTheme->table_tr_enlighten3 . "><td width-150><font size=-1><b>Admin Comment:</b></td><td><font size=-1> ". $channel->comment ."</td></tr>\n");
}

echo "<tr>";
$chanprob=0;$userprob=0;$noaxs=0;
if (((is_suspended($user_id,$channel->name)==1) || (is_suspended("",$channel->name)==1)) && ($admin==0)){
	$noedit = 1;
	if (is_suspended($user_id,$channel->name)==1) { $userprob=1; }
	if (is_suspended("",$channel->name)==1) { $chanprob=1; }
} else {
	$noedit = 0;
}
$ruu = pg_safe_exec("SELECT levels.user_id FROM users,channels,levels WHERE users.id='$user_id' AND users.id=levels.user_id AND channels.id=levels.channel_id AND channels.id='" . $channel->id . "' AND levels.access>0 AND channels.registered_ts>0");
if (pg_numrows($ruu)==0) { $noaxs=1; }

if ((($access > $level_modinfo) || ($admin > $level_modinfo)) && !$noedit && $cpurged==0) {
		echo("<tr><td colspan=2 bgcolor=#" . $cTheme->table_sepcolor . "><font size=-1  color=#" . $cTheme->table_septextcolor . "><em><b>Change Settings</b></em></font></b></td></tr><tr>");

if ((($access > $level_modinfo) || ($admin > $level_modinfo)) && !$noedit && !$edit && $cpurged==0 && (is_suspended($user_id,$channel_name)!=1) && (is_suspended("",$channel_name)!=1) && (is_suspended($user_id,"")!=1)) {
if ($access>=$level_status) {
	echo("<td><form action=\"channels.php\" method=get>
	<input type=hidden name=id value=\"$channel->id\">\n");
	echo "<input type=hidden name=action value=\"edit\">";
	echo("<input type=hidden name=\"target\" value=\"channel\">
	<input type=\"submit\" value=\"Edit Settings\">
	</form></td> ");
	if ($admin < 1) { echo "</tr>"; }
}
}

if (($admin>$access) && (!$edit)) {

	echo("<td><form action=\"channels.php\" method=get>
	<input type=hidden name=id value=\"$channel->id\">
	<input type=hidden name=action value=\"force\">
	<input type=\"submit\" value=\"Force Login\">
	</form></td></tr>");

}
if (!$noedit && $edit) {
	echo( "<tr><td><input type=\"submit\" value=\"Save Changes\" name=\"button\"></form></td>");

	echo("<td><form action=\"channels.php\" method=get>
	<input type=hidden name=id value=\"$channel->id\">
	<input type=\"submit\" value=\"Exit without save\">
	</form></td></tr> ");
}
} else {
		echo "<tr><td colspan=2><font size=+0 color=#" . $cTheme->main_warnmsg . "><b>";
		if ($userprob) { echo "<li> You are globally suspended\n"; }
		if ($chanprob) { echo "<li> You are suspended on this channel\n"; }
		if ($noaxs) { echo "<li> You don't have access to this channel\n"; }
		echo "</b></Font></td></tr>\n";
}

echo "</table><br><br>";
if ($edit && $access>=400) {
php?>

<h3>Add a user to this channel</h3>
<form method="post">
<table border=0>
<tr><td>User Name</td><td><input type="text" name="add_user_id" maxlength=12> (NOT NICKNAME)</td></tr>
<tr><td>Access Level</td><td><input type="text" size=6 maxlength=4  name="add_access"></td></tr>
</table>
<input type="submit" name="button" value="Add this user">
</form>
<?php

}


$levels = pg_safe_exec("select " .
  "  levels.channel_id, " .
  "  levels.user_id," .
  "  levels.access," .
  "  levels.flags," .
  "  levels.added," .
  "  levels.added_by," .
  "  levels.last_modif," .
  "  levels.last_modif_by," .
  "  levels.suspend_expires,levels.suspend_by," .
  "  users.flags AS uflags," .
  "  users.user_name " .
  " from " .
  "  levels," .
  "  users" .
  " where " .
  "  channel_id = " . $channel->id . " and " .
  "  users.id=levels.user_id order by access desc");
echo("
<TABLE WIDTH=100% border=1 cellspacing=0 cellpadding=2 BGCOLOR=#" . $cTheme->table_bgcolor . ">
<tr>
        <td colspan=7>
        <center><h2>Users with access to this channel</h2>
</center></Td>
</tr>");

if (pg_numrows($levels)==0) {

	echo("<b>There are no users with access on this channel</b>");

} else {

	echo("<tr bgcolor=#" . $cTheme->table_sepcolor . ">
	<td width=150><FONT color=#" . $cTheme->table_septextcolor . " size=-1><em><b>User</font></td>");
	$lastmod = 0;
	if ($admin>0) {
		echo "<td valign=top><FONT color=#" . $cTheme->table_septextcolor . " size=-1><em><b>Infos</font></td>";
		$lastmod = 1;
	}
	echo ("<td align=center width=50><FONT color=#" . $cTheme->table_septextcolor . " size=-1><em><b>Access</font></td>
	<td align=center width=100><FONT color=#" . $cTheme->table_septextcolor . " size=-1><em><b>AutoOp?</font></td>
	<td align=center width=100><FONT color=#" . $cTheme->table_septextcolor . " size=-1><em><b>AutoVoice?</font></td>
	<td align=center width=100><FONT color=#" . $cTheme->table_septextcolor . " size=-1><em><b>AutoInvite?</font></td>");
	echo("<td><FONT color=#" . $cTheme->table_septextcolor . " size=-1><em><b>Access Suspended?</font></td>");
	// we aren't going to edit users on the web yet
	//echo("<td align=left width=50><font color=#" . $cTheme->table_septextcolor . " size=-1><em><b>Edit?</font></td></tr>");
	echo "</tr>\n";
	for ($row=0;$row<pg_numrows($levels);$row++) {

		$level = pg_fetch_object($levels,$row);
		display_level($level,"N",$access,"N",$lastmod);
	}
} // of else

echo("</table>");

$bans = pg_safe_exec("SELECT channel_id,id,banmask,set_by,set_ts,level,expires,reason FROM bans WHERE (expires=0 OR expires>now()::abstime::int4) AND channel_id=$channel->id order by set_ts desc");

if (pg_numrows($bans)!=0) {
echo(" <br><br>
<TABLE WIDTH=100% cellspacing=0 cellpadding=2 BORDER=1 BGCOLOR=#" . $cTheme->table_bgcolor . ">
<tr>
        <td colspan=6>
        <center><h2>Bans for this channel (" . (int)pg_numrows($bans) . " total)</h2>
</center></Td>
</tr>");

	echo(" <tr bgcolor=#" . $cTheme->table_headcolor . ">
	<td><font color=#" . $cTheme->table_headtextcolor . ">Banmask</td>
	<td><font color=#" . $cTheme->table_headtextcolor . ">Set by</td>
	<td><font color=#" . $cTheme->table_headtextcolor . ">Set at</td>
	<td><font color=#" . $cTheme->table_headtextcolor . ">Duration</td>
	<td><font color=#" . $cTheme->table_headtextcolor . ">Level</td>
	<td><font color=#" . $cTheme->table_headtextcolor . ">Reason</td>");
//	if ($edit) { echo "<td><font color=#" . $cTheme->table_headtextcolor . ">Remove?</font>"; }

	echo("</td></tr>");

	for ($row=0;$row<pg_numrows($bans);$row++) {
		$ban = pg_fetch_object($bans,$row);
		echo(" <tr><td>". $ban->banmask . "</td><td>". $ban->set_by ."</td>");
        echo("<td>" . cs_time($ban->set_ts) . " [". $ban->set_ts . "]</td>");
        if ($ban->expires == 0) {
            echo("<td>permanent</td>");
        } else {
            echo("<td>". drake_duration($ban->expires-$ban->set_ts) . " [" . (($ban->expires-$ban->set_ts)/3600) . " hour(s)]</td>");
        }
		echo("     <td>". $ban->level . "</td><td>". htmlspecialchars($ban->reason) . "</td>");
//		if ($edit) { echo "<td><input type=checkbox name=".$ban->id."_delete></td>"; }
		echo("</tr>");
	}

	echo("</table>");

} // of if
if ($admin>0) {
  $type = $channel_events;

  	echo("<br>");

	$query="SELECT channelid,ts,event,message FROM channellog WHERE channelid=$channel->id ORDER BY ts DESC";
//	echo "Q: $query<br>";
	$logs = pg_safe_exec($query);
	if (pg_numrows($logs)==0) {
		echo "There are no log messages for this channel\n<br><br>";
	} else {
		echo "<TABLE width=100% border=1 cellspacing=0 cellpadding=2 BGCOLOR=#" . $cTheme->table_bgcolor . ">";
		echo "<tr><td colspan=3><H2>Log messages (last 5)</H2><a href=\"viewlogs.php?cid=" . $channel->id . "\">View All</a></td></tr>";
		echo "<tr bgcolor=#" . $cTheme->table_headcolor . "><td><font color=#" . $cTheme->table_headtextcolor . ">Time</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Event</font></td><td><font color=#" . $cTheme->table_headtextcolor . ">Message</font></td></tr>";
		if (pg_numrows($logs)>=5) { $max=5; } else { $max=pg_numrows($logs); }
		for ($row=0;$row<$max;$row++) {
			$log=pg_fetch_object($logs,$row);
			echo("<tr><td>");
			echo(cs_time($log->ts) . " [" . $log->ts . "]</td><td>");
			echo($type[$log->event] . "</td><td>");
			if ($admin<SHOW_IP_LEVEL) {
				echo(htmlentities(remove_ip($log->message,2)) . "</td></tr>\n");
			} else {
				echo(htmlentities($log->message) . "</td></tr>\n");
			}
	        }
		echo("</table>");
	}

}

echo( "</form>");

?>
</body></html>
