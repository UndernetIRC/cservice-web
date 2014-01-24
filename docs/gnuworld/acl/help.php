<?
include("../../../php_includes/cmaster.inc");
std_init();
if (!acl(XWEBACL)) {
	die("Sorry, you have no access.");
}
$cTheme = get_theme_info();



?>
<!-- $Id: help.php,v 1.7 2004/03/15 23:50:11 nighty Exp $ //-->
<html>
<head><title>ACL Help</title>
<?
std_theme_styles();
echo "</head>";
std_theme_body("../");
?>
<br><h2>ACL Help</h2><br><br><br>
<ul>


<li>&nbsp;<a name="XCHGMGR"><u><b><?=BOT_NAME?>@ Manager Changes</b></u><br><i>
<b>reviewer</b>:<br>
&nbsp;&nbsp;&nbsp;-&nbsp;Can Accept requests<br>
&nbsp;&nbsp;&nbsp;-&nbsp;Can Reject requests<br><br>
<b>admin</b>:<br>
&nbsp;&nbsp;&nbsp;-&nbsp;all permissions of 'reviewer', and :<br>
&nbsp;&nbsp;&nbsp;-&nbsp;Can Revert changes<br>
&nbsp;&nbsp;&nbsp;-&nbsp;Can Turn temp changes into permanent
</i><br><br><a href="javascript:window.close();">close</a><br><br><br><br><br><br><br>&nbsp;


<li>&nbsp;<a name="XMAILCH"><u><b><?=BOT_NAME?>@ E-Mail-in-Record Changes</b></u><br><i>
<br>THIS OPTION IS NOT IMPLEMENTED YET (e.g. : NOT USED)<br><br><br>
</i><br><br><a href="javascript:window.close();">close</a><br><br><br><br><br><br><br>&nbsp;


<li>&nbsp;<a name="XAT_GLOBALS"><u><b><?=BOT_NAME?>@ Team Globals</b></u><br><i>
<b>Can view verification data</b>:<br>
&nbsp;&nbsp;&nbsp;-&nbsp;allows to see in user details the answer to the verification question, note that people with *600+ access will not require this to see it.<br><br>
<b>Can edit users</b>:<br>
&nbsp;&nbsp;&nbsp;-&nbsp;allows to edit username, this is only usefull for persons will an access lower than 600, otherwise they don't need that flag to edit users.
</i><br><br><a href="javascript:window.close();">close</a><br><br><br><br><br><br><br>&nbsp;


<li>&nbsp;<a name="XHELP"><u><b><?=BOT_NAME?>@ Help management</b></u><br><i>
you can choose whether you want to allow actions on all or on a specific language.<br><br>
its quite dumb, or useless to uncheck the <b>Can EDIT HELP text replies</b> checkbox, since without that option, the user will not be able to change the replies (just view them listed).<br>
The <b>Can ADD commands to the HELP set</b> should be use with care, since it will allow the user to add a reply trigger.<br>
(e.g. add a command like 'HELLO', allowing to set a reply for the '/msg <?=BOT_NAME?> help HELLO' reply.
</i><br><br><a href="javascript:window.close();">close</a><br><br><br><br><br><br><br>&nbsp;


<li>&nbsp;<a name="XWEBAXS"><u><b>WebAxs/Channel Review team</b></u><br><i>
<b>reviewer</b>:<br>
&nbsp;&nbsp;&nbsp;-&nbsp;Can post admin comments<br>
&nbsp;&nbsp;&nbsp;-&nbsp;Can edit NOREG entries<br>
&nbsp;&nbsp;&nbsp;-&nbsp;Can view FULL applications<br>
&nbsp;&nbsp;&nbsp;-&nbsp;Can Reject channel applications<br><br>
<b>admin</b>:<br>
&nbsp;&nbsp;&nbsp;-&nbsp;all permissions of 'reviewer', and :<br>
&nbsp;&nbsp;&nbsp;-&nbsp;Can Accept channel applications<br>
&nbsp;&nbsp;&nbsp;-&nbsp;View only access to 'User Toaster'
</i><br><br><a href="javascript:window.close();">close</a><br><br><br><br><br><br><br>&nbsp;


<li>&nbsp;<a name="XWEBCTL"><u><b>Site Control Access</b></u><br><i>
This allows the user to control LOCKS over :<br>
&nbsp;&nbsp;&nbsp;-&nbsp;New users<br>
&nbsp;&nbsp;&nbsp;-&nbsp;New registrations<br>
&nbsp;&nbsp;&nbsp;-&nbsp;Site access
</i><br><br><a href="javascript:window.close();">close</a><br><br><br><br><br><br><br>&nbsp;


<li>&nbsp;<a name="XWEBACL"><u><b>ACL Page Access</b></u><br><i>
This allows the user to view the ACL page, it is needed to use the 'Site Control' feature, or to be able to change some of the ACLs when you have the proper 'admin' access to one.
</i><br><br><a href="javascript:window.close();">close</a><br><br><br><br><br><br><br>&nbsp;


<li>&nbsp;<a name="XDOMAIN_LOCK"><u><b>DomainLock ADD/REMOVE Access</b></u><br><i>
This allows the user to add and or remove lock ups on domain names or email addresses in the 'DomainLock' sub system.
</i><br><br><a href="javascript:window.close();">close</a><br><br><br><br><br><br><br>&nbsp;



<li>&nbsp;<a name="XWEBUSR_TOASTER"><u><b>User Toaster Access</b></u><br><i>
This allows the user to actually USE the 'User Toaster', which means that user will be able to put usernames to 'Fraud' from that page.
</i><br><br><a href="javascript:window.close();">close</a><br><br><br><br><br><br><br>&nbsp;


<li>&nbsp;<a name="XSUSPEND_USR"><u><b>User suspension access</b></u><br><i>
This allows the user to actually 'SUSPEND' a user from within the EDIT mode on user details.<br>
When used along with XUNSUSPEND_USR it gives SUSPEND/UNSUSPEND powers like 800+ have on the page.<br>
Note that this ACL requires the 'Can edit users' from the <a href="#XAT_GLOBALS">XAT_GLOBALS</a> enabled.
</i><br><br><a href="javascript:window.close();">close</a><br><br><br><br><br><br><br>&nbsp;


<li>&nbsp;<a name="XUNSUSPEND_USR"><u><b>User suspension access</b></u><br><i>
This allows the user to actually 'UNSUSPEND' a user from within the EDIT mode on user details.<br>
When used along with XSUSPEND_USR it gives SUSPEND/UNSUSPEND powers like 800+ have on the page.<br>
Note that this ACL requires the 'Can edit users' from the <a href="#XAT_GLOBALS">XAT_GLOBALS</a> enabled.
</i><br><br><a href="javascript:window.close();">close</a><br><br><br><br><br><br><br>&nbsp;


<li>&nbsp;<a name="XWEBSESS"><u><b>Current web sessions view</b></u><br><i>
This allows the user to view the list of the currently logged in officials,<br>
and also to KILL the sessions of the officials with a strictly lower level on * than his own.<br>
</i><br><br><a href="javascript:window.close();">close</a><br><br><br><br><br><br><br>&nbsp;


<li>&nbsp;<a name="XLOGGING_VIEW"><u><b>Admin logging view</b></u><br><i>
This allows the user to view the admin's actions log.<br>
</i><br><br><a href="javascript:window.close();">close</a><br><br><br><br><br><br><br>&nbsp;


<li>&nbsp;<a name="XCOMPLAINTS_ADM"><u><b>Complaints Admins access</b></u><br><i>
This allows the user to manager, view and reply to Complaints tickets.<br>
</i><br><br><a href="javascript:window.close();">close</a><br><br><br><br><br><br><br>&nbsp;


<li>&nbsp;<a name="XIPR_AXS"><u><b>IP/Host List access</b></u><br><i>
This allows the user to, depending on set rights, view/modify own/others IP/Host access lists.<br>
</i><br><br><a href="javascript:window.close();">close</a><br><br><br><br><br><br><br>&nbsp;


</ul>
<br><br><br><br>
</body>
</html>
