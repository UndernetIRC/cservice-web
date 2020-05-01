<?
include("../../../php_includes/cmaster.inc");
std_connect();
$user_id = std_security_chk($auth);
$cTheme = get_theme_info();
?>
<html><head><title>Registration Process</title>
<style type=text/css>
<!--
a:link { font-family: arial,helvetica; color: #004400;  }
a:visited { font-family: arial,helvetica; color: #004400; }
a:hover { font-family: arial,helvetica; color: #80809A; }
th, td { padding: 15px; }
 .tab { margin-left: 40px; }
//-->

</style>
<? std_theme_styles(); ?>
</head>
<? std_theme_body("../"); ?>
</head>
<body bgcolor=#f0f0f0 text=#000000 link=#004400 vlink=#004400 alink=#80809A>
<font face="arial,helvetica" size=-1>
<center><hr noshade size=2>
<a href="javascript:history.go(-1);">&lt;&lt;&nbsp;Go back</a>
<br><br>
<center><table border=1 width=800 bgcolor=#ffffff>
<tr><td>
<center><h2><u>A</u>cceptable <u>U</u>se <u>P</u>olicy</h2></center>
<br>
<? if (REQUIRED_SUPPORTERS>0) { ?>
The purpose of channel registration is not to start a new channel, but rather for previously <b>established channels</b> to have an opportunity to ensure stability. If you are first starting a new channel then simply start using your channel and give it time to see if a reasonable user base develops to justify registration. Your channel must be open and active for a reasonable amount of time <b>before</b> you apply. It is important that you gather C scores too. ChanFix is a service that tracks the activity of channel operators.<br>
<? } ?>
<br>
Channel registration application is not a right, but a privilege granted subject to conditions. First of all, you need to check that the channel is not already registered. This may be done through the use of the following <? echo BOT_NAME ?> command:<br>
<br>
	<p class="tab"><b>/msg <? echo BOT_NAME ?> chaninfo #channel</b><br></p>
<br>
Your username must be at least <b><? echo MIN_DAYS_BEFORE_REG ?> days old</b> in order to apply for channel registration. Supporters’ usernames must be <b><? echo MIN_DAYS_BEFORE_SUPPORT ?> days old</b> in order to qualify as a supporter and their usernames must not have a history of abuse, otherwise they cannot support your application.<br>
<br>
In 2016, the maximum number of channels you were able to register increased to a maximum of <b>three</b>, however there are conditions that need to be met before being able to do so. These are as follows:<br>
 <ul>
 <li>1 year or newer        – you can register <b>one</b> channel</li>
 <li>1-2 years old          – you can register up to <b>two</b> channels</li> 
 <li>3 years old and above  – you can register up to <b>three</b> channels</li>
</ul>
Channel Services (<? echo BOT_NAME ?>) will not be provided for any channel:<br>
 <ul>
 <li>involved in child pornography or trade of illegal content</li>
 <li>provide a bot lending service</li>
 <li>filled with a considerable number of compromised machines</li>
</ul>
If CService finds these activities are predominant in your channel following registration, <? echo BOT_NAME ?> may end up being permanently removed.<br>
<br>
CService administrators reserve the right to reject any channel registration for any reason that they deem valid.<br>
<br>
Usernames expire after <b>180 days of inactivity</b>. If a username expires and they are the manager of a registered channel, a vote to find a replacement manager may be called. If no suitable candidate is found as a replacement manager, the channel will be purged at that time. If you expect to be absent for months you are encouraged to appoint a temporary channel manager to avoid a channel purge. Please inquire on the website or in <b><? echo SERVICE_CHANNEL ?></b> for more information.<br>
<br>
<? if (REQUIRED_SUPPORTERS>0) { ?>
<center><h2>Supporters</h2></center>
<br>
You need to have <b><? echo REQUIRED_SUPPORTERS ?> different</b> supporters in order to register a channel. These must represent unique people, not just different usernames. Make sure all your supporters are active members of your channel and that they all agree to support you as manager. If any of the people who you list as a supporter indicates they do not support your channel, you will lose the right to register any channel for <b>3 days</b>.<br>
<br>
Your supporters must visit the CService web page and indicate whether they support your application or not, or send the following command on IRC:<br>
<br>
	<p class="tab"><b>/msg <? echo BOT_NAME ?> support #channel yes|no</b><br></p>
<br>
If after <b>3 days</b> all of your supporters have not confirmed their support, your application will be rejected. In such a situation you may submit a new application (using the same supporters or a different list).<br>
<br>
<? } ?>
If you feel like your channel qualifies for registration go to <a href="<? echo IFACE_URL ?>"><? echo IFACE_URL ?></a> and follow all instructions.<br>
<br>
<b>Note</b>: The registration period can take <b>3-5 days</b> and depends on channel activity. Those listed as supporters should be active and state that support as soon as possible to speed up the process! Should your application be rejected for any reason and you wish to reapply, please correct errors and submit a new application.<br>
<br>
You can track the status of your application at any time by visiting <a href="<? echo IFACE_URL ?>"><? echo IFACE_URL ?></a> and selecting "<b>Check App</b>".<br>
<br>
If there is anything you are unsure of or would like clarify about the registration process, please don't hesitate to drop by <b><? echo SERVICE_CHANNEL ?></b> and ask our friendly team.<br>
<br>
Best of luck!<br>
<br>
<b><? echo NETWORK_NAME ?> Channel Services Committee</b><br>
May 1st, 2020<br>
<br>
</td></tr></table>
<br>
<br>
<? if ($user_id>0) { ?>
<form>
<input type=button value="I ACCEPT THE ABOVE" onClick="location.href='./index.php?aup=1'">
&nbsp;&nbsp;
<input type=button value="I REFUSE THE ABOVE" onClick="location.href='../right.php'">
</form>
<? } ?>
</center>

</body>
</html>
