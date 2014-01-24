<?
/* $Id: index.php,v 1.29 2006/05/06 01:44:50 nighty Exp $ */

$min_lvl=800;
require("../../../php_includes/cmaster.inc");
std_connect();
$user_id = std_security_chk($auth);
$admin = std_admin();
        if ($admin<=0 && !acl()) {
                echo "Sorry your admin access is too low.";
                die;
        }
$cTheme = get_theme_info();
$res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
$adm_usr = pg_fetch_object($res,0);
$adm_user = $adm_usr->user_name;

if (!acl(XWEBAXS_3) && !acl(XWEBUSR_TOASTER) && !acl(XWEBUSR_TOASTER_RDONLY)) {
	echo "Sorry, your admin access is too low.";
	die;
}

$unf = pg_safe_exec("SELECT count_count FROM counts WHERE count_type=1");
if (pg_numrows($unf)==0) {
	$MAX_UCOUNT = 0;
} else {
	$bla = pg_fetch_object($unf,0);
	$MAX_UCOUNT = $bla->count_count;
}
$less_count=-1;
$MAXU = ($MAX_ALLOWED_USERS*2);
if ($MAX_UCOUNT<$MAXU) { $less_count=$MAX_UCOUNT; $MAX_UCOUNT=$MAXU; }


echo "<html><head><title>User Toaster</title>";
std_theme_styles();
echo "</head>";
std_theme_body("../");


echo "<h2>User Toaster</h2> (Hunting Fraudulous Usernames and Usernets) ";

$res = pg_safe_exec("SELECT COUNT(*) AS count FROM noreg WHERE type=4");
$row = pg_fetch_object($res,0);
$tot_noregs = $row->count;
$ras = pg_safe_exec("SELECT COUNT(id) AS count FROM users");
$raw = pg_fetch_object($ras,0);
$tot_users = $raw->count;

/*
?>
<form name=display3 method=get action=list.php onsubmit="return check3(this);">
<input type=hidden name=mode value=4>
Search by FLAGLIST&nbsp;<select name=fl><?
$flr = pg_safe_exec("SELECT * FROM fraud_lists ORDER BY name");
while ($flo = pg_fetch_object($flr)) {
	echo "<option value=\"" . $flo->id . "\">" . $flo->name . "</option>\n";
}
?></select><br>
Order by <select name=or>
<option value=1 selected>Username</option>
<option value=3>Creation Date</option>
<option value=2>Email Addy</option>
<option value=7>Email @domain only</option>
<option value=4>Verification Answer</option>
<option value=5>User ID</option>
<option value=6>Signup IP</option>
</select>&nbsp;&nbsp;<input type=submit value="Go!"><br>
<input type=checkbox name=onlyfresh value=1>&nbsp;Hide already suspended users from output list<br>
<input type=checkbox name=showlasthost value=1>&nbsp;Show 'last_hostmask' under username in output list<br>
<?
//<input type=checkbox name=lookup_apps value=1>&nbsp;Lookup channel applications for non-500 listed users (longer)<br><br>
echo "<input type=hidden name=lookup_apps value=0>\n";
?>
</form>
<br><br>
*/
?>
<form name=display3 method=get action=list.php onsubmit="return check4(this);">
<input type=hidden name=mode value=6>
Search by CHANNEL&nbsp;<input type=text name=cname size=30 maxlength=255 value="#"><select name=listtype><option value=1>users</option><option value=2>objections</option></select><br>
Order by <select name=or>
<option value=1 selected>Username</option>
<option value=3>Creation Date</option>
<option value=2>Email Addy</option>
<option value=7>Email @domain only</option>
<option value=4>Verification Answer</option>
<option value=5>User ID</option>
<option value=6>Signup IP</option>
</select>&nbsp;&nbsp;<input type=submit value="Go!"><br>
<input type=checkbox name=onlyfresh value=1>&nbsp;Hide already suspended users from output list<br>
<input type=checkbox name=showlasthost value=1>&nbsp;Show 'last_hostmask' under username in output list<br>
<?
echo "<input type=hidden name=lookup_apps value=0>\n";
?>
</form>
<br><br>

<form name=display0 method=get action=list.php onsubmit="return check0(this);">
<script language="JavaScript1.2">
<!--
function checkEmailadd() {
	var f = document.forms['display0'];
	if (f.st.options[f.st.selectedIndex].value == 2) {
		f.sp.value='*@';
	} else {
		f.sp.value='';
	}
	f.sp.focus();
}
//-->
</script>
<input type=hidden name=mode value=1>
Search by&nbsp;<select onchange="checkEmailadd();" name=st>
<option value=1>Username</option>
<option value=2>Email Addy</option>
<option value=3>Signup IP</option>
<option value=4>Verification Answer</option>
<option value=5>Last hostmask</option>
</select>&nbsp;<input type=text name=sp size=20 value=""> (wildcard : * ?)<br>
Order by <select name=or>
<option value=1 selected>Username</option>
<option value=3>Creation Date</option>
<option value=2>Email Addy</option>
<option value=7>Email @domain only</option>
<option value=4>Verification Answer</option>
<option value=5>User ID</option>
<option value=6>Signup IP</option>
</select>&nbsp;&nbsp;<input type=submit value="Go!"><br>
<input type=checkbox name=onlyfresh value=1>&nbsp;Hide already suspended users from output list<br>
<input type=checkbox name=showlasthost value=1>&nbsp;Show 'last_hostmask' under username in output list<br>
<?
//<input type=checkbox name=lookup_apps value=1>&nbsp;Lookup channel applications for non-500 listed users (longer)<br><br>
echo "<input type=hidden name=lookup_apps value=0>\n";
?>
</form>
<br><br>
<form name=display1 method=get action=list.php onsubmit="return check1(this);">
<input type=hidden name=mode value=2>
Display last&nbsp;<input type=text name=nb size=4 maxlength=4 value=<?
if ($MAX_UCOUNT<100) { echo $MAX_UCOUNT; } else { echo "100"; }
?>> new users
<br>
Order by <select name=or>
<option value=1>Username</option>
<option value=2>Email Addy</option>
<option value=7>Email @domain only</option>
<option value=3>Creation Date</option>
<option value=4>Verification Answer</option>
<option value=5 selected>User ID (reverse)</option>
<option value=6>Signup IP</option>
</select>&nbsp;&nbsp;<input type=submit value="Go!"><br>
<input type=checkbox name=onlyfresh value=1>&nbsp;Hide already suspended users from output list<br>
<input type=checkbox name=showlasthost value=1>&nbsp;Show 'last_hostmask' under username in output list<br>
<?
//<input type=checkbox name=lookup_apps value=1>&nbsp;Lookup channel applications for non-500 listed users (longer)<br><br>
echo "<input type=hidden name=lookup_apps value=0>\n";
?>
</form>
<br><br>
<form name=display2 method=post action=list.php onsubmit="return check2(this);">
<input type=hidden name=mode value=3>
Paste type : <select name=paste_type>
<option value=1>Any line containing a +x'd user@host</option>
<option value=2>One username per line (trailing/heading spaces will be removed)</option>
<option value=3>A copy/paste of a '/msg <?=BOT_NAME?> status #channel'</option>
</select><br>
Order by <select name=or>
<option value=1>Username</option>
<option value=2>Email Addy</option>
<option value=7>Email @domain only</option>
<option value=3>Creation Date</option>
<option value=4>Verification Answer</option>
<option value=5 selected>User ID (reverse)</option>
<option value=6>Signup IP</option>
</select><br>
Your paste below :<br>
<textarea name=the_paste cols=40 rows=7 wrap></textarea><br>
<input type=checkbox name=onlyfresh value=1>&nbsp;Hide already suspended users from output list<br>
<input type=checkbox name=showlasthost value=1>&nbsp;Show 'last_hostmask' under username in output list<br>
<?
//<input type=checkbox name=lookup_apps value=1>&nbsp;Lookup channel applications for non-500 listed users (longer)<br><br>
echo "<input type=hidden name=lookup_apps value=0>\n";
?>
<input type=submit value="Display usernames in TOASTER">
</form>
<? if (MIN_CHAN_TOASTER_QRY>0) { ?>
<br><br>
<form name=display5 method=post action=list.php onsubmit="return check5(this);">
<input type=hidden name=mode value=5>
Show users with at least <input type=text name=minchan size=7 maxlength=5 value=<?=MIN_CHAN_TOASTER_QRY?>> channel accesses ...<br>
... in the toaster.&nbsp;&nbsp;<input type=submit value=Go!>
</form>
<? } ?>
<script language="JavaScript1.2">
<!--
function check0(f) {
	if (f.sp.value=="" && f.st.value!=5) {
		alert("Please fill in the search criteria.");
		return (false);
	} else {
		return (true);
	}
}
function check1(f) {
	if (isNaN(f.nb.value) || (f.nb.value<1 || f.nb.value><?=$MAX_UCOUNT?>)) {
		alert("Please use a number that ranges from 1 to <?=$MAX_UCOUNT?>");
		return (false);
	} else {
		return (true);
	}
}
function check2(f) {
	if (f.the_paste.value=="") {
		alert("Please paste something ... at least :P");
		return (false);
	} else {
		return (true);
	}
}
function check3(f) {
	if (f.fl.value>0) {
		return (true);
	} else {
		alert("Please select a FLAGLIST to show !");
		return(false);
	}
}
function check4(f) {
	if (f.cname.value!='') {
		return (true);
	} else {
		alert("Please enter a channel name");
		return(false);
	}
}
<? if (MIN_CHAN_TOASTER_QRY>0) { ?>
function check5(f) {
	if (isNaN(f.minchan.value) || f.minchan.value<<?=MIN_CHAN_TOASTER_QRY?>) {
		alert("Please enter a number above or equal to <?=MIN_CHAN_TOASTER_QRY?> !");
		f.minchan.value='<?=MIN_CHAN_TOASTER_QRY?>';
		return(false);
	} else {
		return(true);
	}
}
<? } ?>
//-->
</script>
<br>
<?
$ratio = 0.00;
$ratio = round((($tot_noregs*100.00)/$tot_users),2)*1.00;
$ssr = @pg_safe_exec("SELECT COUNT(flags) AS count FROM users WHERE (flags::int4 & 1)=1");
if (!$ssr) { // PgSQL version doesnt support bitwise comparison (not showing count) - Update to 7.2.3
	$nosuspcount=1;
} else {
	$nosuspcount=0;
	$sso = pg_fetch_object($ssr);
	$tot_susps = $sso->count;
	$ratio_s = round((($tot_susps*100.00)/$tot_users),2)*1.00;
}
?>
<font color=#<?=$cTheme->main_textlight?>><b><? echo $tot_noregs ?></b> total FRAUD USERNAMES.</font> (<?=$ratio?> % of total users)<br>
<? if (!$nosuspcount) { ?>
<font color=#<?=$cTheme->main_textlight?>><b><? echo $tot_susps ?></b> total SUSPENDED USERNAMES.</font> (<?=$ratio_s?> % of total users)<br>
<? } ?>
<font color=#<?=$cTheme->main_textlight?>><b><? echo $tot_users ?></b> total USERS.</font><br>
<? if ($less_count!=-1) { $MAX_UCOUNT=$less_count; } ?>
<font color=#<?=$cTheme->main_textlight?>>Current NewUsers Count : <b><? echo $MAX_UCOUNT ?></b>/<? echo $MAX_ALLOWED_USERS ?>.<br><br>
</body>
</html>


