<?
include("../../../php_includes/cmaster.inc");
std_connect();
$user_id = std_security_chk($auth);
$cTheme = get_theme_info();
?>
<html><head><title>REGISTRATION PROCESS</title>
<? std_theme_styles(); ?>
</head>
<? std_theme_body("../"); ?>
<b>CHANNEL SERVICE REGISTRATION - ACCEPTABLE USE POLICY</b><br><hr noshade size=2>
<a href="javascript:history.go(-1);">&lt;&lt;&nbsp;Go Back</a>
<br><br>
<table border=1 width=450 bgcolor=#<?=$cTheme->table_bgcolor?>>
<tr><td>
<center><h2><b>CHANNEL SERVICE<br><u>A</u>cceptable <u>U</u>se <u>P</u>olicy</b></h2></center>
<br>
<? if (REQUIRED_SUPPORTERS>0) { ?>
Channel registration is not meant as a means to start a new channel. It is meant for previously established channels to have an opportunity to have some stability. If you are first starting a new channel, then just start using your channel and give it time to see if a reasonable userbase develops to justify registration. Your channel must be open and active for a reasonable amount of time BEFORE you apply.<br>
<br>
<? } ?>
As a channel manager you are responsible for the channel and seeing that your channel users and ops follow all Channel Service guidelines, so READ BEFORE YOU APPLY.<br>
<br>
Channel registration application is not a right, but a privilege granted subject to conditions.<br>
<br>
<? if (REGPROC_ALLOWMULTIPLE==0) { ?>
The Channel Service Guidelines approved by <? echo NETWORK_NAME ?> admins require that <b>"The Channel Service Committee will only register 1 channel per USER (this means user, not account or e-mail address)."</b> Consequently, you can only register one channel on <? echo NETWORK_NAME ?>. Translated into language more appropriate to new services terminology, this means one channel per person, NOT username.<br>
<br>
<? } ?>
Channel Services (<? echo BOT_NAME ?>) will not be provided for any channel involved in child pornography, the trading of warez or any copywritten material including mp3's and dvd's or the illegal trading of credit cards, passwords etc.  <b>We will no longer register any bot lending, shell or bnc (vhosts) channels.</b>  If we find these activities are predominant in your channel after registration, we will permanently remove <? echo BOT_NAME ?> from the channel.<br>
 <br>
While we do not disqualify use of free email services, if we find that certain email domains are being used to abuse our guidelines we will not allow that domain to be used again.<br>
<br>
The CSC admins do reserve the right to reject any channel registration for any reason that they deem valid.<br>
<br>
Channel Services regularly monitors all registered channels for activity.  If a registered channel is not active, <? echo BOT_NAME ?> will be removed from the channel.  Channel managers are expected to be active in the channel.  If you are gone for more than 21 days, <? echo BOT_NAME ?> can be removed from the channel or in very active channels, a new channel manager can be elected by the high level ops.  If you know you are going to be gone for more than 3 weeks, appoint a temporary manager and have Channel Services set them up.  Forms are available on the web page and <? echo SERVICE_CHANNEL ?> is there to help you.<br>
<br><br><br>
<? if (REQUIRED_SUPPORTERS>0) { ?>
<center><h2><b>SUPPORTERS</b></h2></center>
<br>
<? if (REQUIRED_SUPPORTERS>1) { ?>
You need to have <? echo REQUIRED_SUPPORTERS ?> DIFFERENT supporters (people, not just <? echo REQUIRED_SUPPORTERS ?> different usernames) to register a channel.  Make sure all your supporters are active members of your channel and that they all agree to support you as manager.  If any of the people who you list as a supporter indicates they do not support your channel, you will lose the right to register any channel for 3 days.<br>
<br>
Your supporters must go to the web page and indicate whether they support your application or not or /msg <? echo BOT_NAME ?> support #channelname (YES or NO).  If all of your supporters do not respond within 3 days, your application will be rejected.  <br>
<br>
<? } else { ?>
You need to have <? echo REQUIRED_SUPPORTERS ?> supporter (<? echo REQUIRED_SUPPORTERS ?> username) to register a channel.  Make sure he/she is an active member of your channel and that he/she agrees to support you as manager.  If he/she indicates he/she does'nt support your channel, you will lose the right to register any channel for 3 days.<br>
<br>
Your supporter must go to the web page and indicate whether he/she supports your application or not or /msg <? echo BOT_NAME ?> support #channelname (YES or NO).  If he/she does not respond within 3 days, your application will be rejected.  <br>
<br>
<? } ?>
During the registration period, Channel Service will be checking to insure all your supporters are really users of the channel and are actually joining it.  They need to be logged into <? echo BOT_NAME ?> before they join so that they will be counted.  Repeat: PLEASE BE SURE YOUR SUPPORTERS LOG INTO <? echo BOT_NAME ?> BEFORE THEY JOIN THE CHANNEL OTHERWISE THEY WILL NOT BE COUNTED.<br>
<br>
<? } else { ?>
You dont need any supporters for your channel, it is called :<br><b>INSTANT REGISTRATION</b>, enjoy !<br>
<br>
<? } ?>
You are responsible for your Channel Ops whether you are online or not.  Please be careful who you give access to.  Abuse by your channel ops, whether you are there or not, can result in <? echo BOT_NAME ?> being removed from the channel.<br>
<br>
If you feel your channel qualifies for registration go to <? echo IFACE_URL ?> and FOLLOW all instructions.<br>
<br>
<? if (REQUIRED_SUPPORTERS>0) { ?>
NOTE: Filling out this application does NOT constitute registering your channel.  It is only applying to register.The entire registration process takes about 10 days.  Should your application be rejected for any reason, it cannot be altered by CService. You must reapply using this form. You may wish to print the completed form before submitting to retain the data.  <br>
<br>
You should track the progress of your application every few days. Go to the registration page at <? echo IFACE_URL ?> and select Check application.  Enter your channel name and enter or click on go baby.<br>
<? } ?>
<br>
October 6, 2002<br>
<? echo NETWORK_NAME ?> Channel Service Committee<br>
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

</body>
</html>
