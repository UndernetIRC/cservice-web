<?
	$min_lvl=800; // for WEBACCESS TEAM admin.
	$noregwrite_lvl=1;

        require("../../php_includes/cmaster.inc");
        header("Pragma: no-cache");
        std_connect();
        $current_page="left.php";
        $user_id = std_security_chk($auth);
        $admin = std_admin();

	$cTheme = get_theme_info();

	if (isset($authtok)) { unset($authtok); }
	if (isset($authcsc)) { unset($authcsc); }
	$authtok = explode(":",$auth);
	$authcsc = $authtok[3];
?>
<!-- $Id: left.php,v 1.23 2004/08/10 11:57:52 nighty Exp $ //-->
<?
        echo "<html><head><title>LEFT MENU</title>";
?>
<style type=text/css>
<!--
a:link { font-family: arial,helvetica; color: #<?=$cTheme->left_linkcolor?>; font-size: 9pt; }
a:visited { font-family: arial,helvetica; color: #<?=$cTheme->left_linkcolor?>; font-size: 9pt; }
a:hover { font-family: arial,helvetica; color: #<?=$cTheme->left_linkover?>; font-size: 9pt; }
//-->
</style>

<?
        echo "</head>\n";
        echo "<BODY BGCOLOR=#" . $cTheme->left_bgcolor . " TEXT=#" . $cTheme->left_textcolor . " alink=#" . $cTheme->left_linkover . " link=#" . $cTheme->left_linkcolor . " vlink=#" . $cTheme->left_linkcolor;
	if ($cTheme->left_bgimage!="") {
		echo " background=\"themes/data/" . $cTheme->sub_dir . "/" . $cTheme->left_bgimage . "\"";
	}
	echo "><FONT FACE=\"Arial,Helvetica,sans-serif\" size=\"-1\">";

	if ($mode!="empty") {
		echo "<a HREF=\"main.php\" TARGET=\"body\"><b>Home</b></a><br><hr noshade size=1>\n";
	}

        if ($user_id > 0) {
        	if ($mode!="empty") {
			echo("<b><A HREF=\"users.php?id=$user_id\" TARGET=\"right\">My Information</a><br><br>\n");
?>
<A HREF="channels.php" TARGET="right">Channel Information</a><br><br>
<? if (!has_a_noreg() && !has_a_channel()) { ?>
<a href="regproc/index.php" target=right><b>Register A Channel</b></a><br><br>
<? } ?>

<a href="check_app.php" target="right"><b>Check App</b></a><br><br>
<a href="forms/index.php" target="right"><b>Forms</b></a><br><br>
<? if (ENABLE_COMPLAINTS_MODULE == 1) { ?>
<!--<a href="complaints/complaints.php" target="right"><b>Complaints</b></a><br><br>//-->
<a href="/docs/xcmds.txt" target="right"><b>All X Commands</b></a><br><br>
<? } ?>
<? } ?>
<?
unset($sec_id);
$sec_id = md5( $user_id . CRC_SALT_0019 . $authcsc );
?>
<a href="passwd.php?SECURE_ID=<?=$sec_id?>" TARGET="right"><b>New Password</b></a><br><br>
<A HREF="logout.php" TARGET="body"><b>Log out</b></a><br><br>

<?
        } else {
?>

<b>
<A HREF="login.php" TARGET="right">Log In</A><BR><br>
<A HREF="newuser.php" TARGET="right">Register!</A><BR><br>
<A HREF="forgotten_pass.php" target="right">Forgotten password</A><BR><br>
<? if (ENABLE_COMPLAINTS_MODULE == 1) { ?>
<!--<a href="complaints/complaints.php" target="right"><b>Complaints</b></a><br><br>//-->
<? } ?>
<? if (CSERVICE_SITE_URL!="") { ?>
<A HREF="<?=CSERVICE_SITE_URL?>" target=_top>CService Site</a><br><br>
<? } ?>
<hr noshade size=1>
<?

        }
        if (($admin>0 || has_acl($user_id)) && $mode!="empty") {

?>
	<? if ($admin>0) { ?>
		<b>Admin Tools:<br>
		
	<? } else { ?>
		<b>ACL Tools:<br>
		<? if (acl(XAT_CAN_VIEW) || acl(XAT_CAN_EDIT)) { ?>
		<a href="users.php" target=right>User Lookup</a><br>
		<? } ?>
	<? } ?>
        <? if ($admin>=600) { ?>
                <a href="/collector/" target="_blank">Collector</a><br><br>
        <? } ?>
	<? if ($admin>0) { ?>
		<a HREF="list_app.php" target="right"><b>List Applications</b></a><br><br>
		<a href="users.php" target=right>User Lookup</a><br>
		<a href="lookup_email.php" target=right>Mail Lookup</a><br>
	<? } ?>
	<? if ($admin>=800) { ?>
		<a href="cr_newuser.php" target=right>New User</a><br><br>
	<? } else { echo "<br>"; } ?>
	<? if (acl(XWEBAXS_2) || acl(XWEBAXS_3)) { //minimum required admin level
	?>
		<a href="noreg/index.php" target=right>Noreg Admin</a><br><br>
	<? } ?>
	<? if ($admin>0 && !acl(XWEBAXS_3) && !acl(XWEBAXS_2)) { //minimum required admin level
	?>
		<a href="noreg/index.php" target=right>Noreg List</a><br><br>
	<? } ?>
	<? if ($admin>=800 || acl(XWEBACL)) { ?>
		<a href="acl/index.php" target=right>ACL Manager</a><br><br>
	<? } else { ?>
		<? if (acl(XWEBCTL)) { ?>
			<a href="acl/index.php" target=right>Site Control</a><br><br>
		<? }
	} ?>
	<? if ($admin>0 || acl(XDOMAIN_LOCK)) { ?>
		<a href="domainlock/index.php" target=right>DomainLock</a><br><br>
	<? } ?>
	<? if ($admin>=800) { //minimum required admin level ?>
		<a href="motd.php" target=right><? echo BOT_NAME ?>'s Motd</a><br>
	<? } ?>
	<? if (ENABLE_COMPLAINTS_MODULE && acl(XCOMPLAINTS_ADM_REPLY) || acl(XCOMPLAINTS_ADM_READ)) { ?>
		<!--<a href="complaints/admin.php" target=right>Complaints Manager</a><br>//-->
	<? } ?>
	<? if (acl(XHELP)) { ?>
		<a href="help_mgr/index.php" target=right><? echo BOT_NAME ?>'s HELP</a><br>
	<? } ?>
	<? if (acl(XCHGMGR_ADMIN) || acl(XMAILCH_ADMIN)) { ?>
		<a href="xat/index.php" target=right><? echo BOT_NAME ?>@ Admin</a><br><br>
	<? } else { ?>
		<? if (acl(XCHGMGR_REVIEW) || acl(XMAILCH_REVIEW) || $admin>=600) { ?>
			<a href="xat/index.php" target=right><? echo BOT_NAME ?>@ Review</a><br><br>
		<? } else { echo "<br>\n"; } ?>
	<? } ?>
	<? if (acl(XWEBAXS_3) || acl(XWEBUSR_TOASTER) || acl(XWEBUSR_TOASTER_RDONLY)) { ?>
		<a href="userbrowser/index.php" target=right>User Toaster</a><br>
	<? } ?>
	<? if ($admin>0 || acl(XWEBSESS) || acl(XWEBAXS_3)) { //minimum required admin level ?>
		<a href="admin/index.php" target=right>Reports</a><br>
	<? } ?>
	<? if ($admin>0 || acl(XLOGGING_VIEW)) { //minimum required admin level ?>
		<a href="viewlogs.php" target=right>View Logs</a><br>
		<a href="viewlogs_archive.php" target=right>View Old Logs</a><br><br>
	<? } else { echo "<br>"; } ?>
<?
		if ($is_alumni) {
			echo "uid:$user_id<br>admlvl:(none)<br>";	
		}
		if ($admin>0) {
	        	echo("uid:$user_id<br>admlvl:$admin<br>"); // Admin only!
	        	if ($admin>900) { // must stay strict (coders only)
		        	if ($loadavg5 < CRIT_LOADAVG) { $zecolor = "#" . $cTheme->left_loadavg0; }
	        		if ($loadavg5 >= CRIT_LOADAVG && $loadavg5 < CRIT_MAX_LOADAVG) { $zecolor = "#" . $cTheme->left_loadavg1; }
	        		if ($loadavg5 >= CRIT_MAX_LOADAVG) { $zecolor = "#" . $cTheme->left_loadavg2; }
	        		echo "<font size=-2 color=$zecolor>loadavg:<br>" . $loadavg1 . ", " . $loadavg5 . ", " . $loadavg15 . ".</font>";
	        	}
	        }
        }
?>
</body>
</html>
