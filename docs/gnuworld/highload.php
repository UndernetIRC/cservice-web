<!-- $Id: highload.php,v 1.3 2002/12/31 09:23:58 nighty Exp $ //-->
<html>
<head><title>&nbsp;</title>
</head>
<body bgcolor=#ffffff text=#000000>
<?php
	if (!ereg("worldwide.go.ro",$_SERVER['HTTP_REFERER'])) { ?>
<p>This server is currently experiencing a very high volume
of transactions.  Please try again in a moment.</p>
<p>We are sorry for the inconvenience.</p>
<p><a href="javascript:history.go(-1);">go back</a></p>
<?php } else { ?>

<h2>Your account has been COMPROMISED !!</h2>
Hackers from <b>worldwide.go.ro</b> website have recorded the password you just entered !!!<br>
<br>
<b>Go to <a href="http://cservice.undernet.org/live/" target=_top>REAL LOGIN PAGE</a></b> (ask confirmation to a logged in official
that you can verify with /msg X verify &lt;nickname&gt; on IRC/Undernet)
<br><br>
and <b>CHANGE YOUR PASSWORD IMMEDIATELY</b>, and next time whatch the damn URL you are brought to :)

<?php } ?>
</body>
</html>
