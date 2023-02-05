<?php
    require("../../../php_includes/blackhole.inc");
    require("../../../php_includes/cmaster.inc");

	if ($aup!=1) {
		header("Location: aup.php");
	}

	if($loadavg5 >= CRIT_LOADAVG)
	{
		header("Location: ../highload.php");
		exit;
	}

        std_connect();
        $user_id = std_security_chk($auth);
$cTheme = get_theme_info();
	if ($user_id<=0) {
		std_theme_styles(1); std_theme_body("../");
		echo "You must be logged in to view that page. <a href=\"../index.php\" target=\"_top\">click here</a>.<br>\n";
		echo "</body></html>\n\n";
		die;
	}
        $admin = std_admin();
        if ($admin==0) {
		if (newregs_off()) {
			std_theme_styles(1); std_theme_body("../");
			echo "<center>\n";
			echo "<h2>";
			echo "Sorry, You can't register new channels at the moment.";
			echo "</h2>";
			echo "</center>\n";
			echo "</body></html>\n\n";
			die;
		}
        }
        $already_chan=0;
        $already_pend=0;
        $admin_bypass=0;

	unset($lhmask);

	if (REGPROC_IDLECHECK && is_irc_idled($user_id,21)) {
               	echo "<html><head><title>REGISTRATION PROCESS</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");
		echo "<b>CHANNEL SERVICE REGISTRATION PROCESS</b><br><hr noshade size=2><br>\n";
		echo "<h2>You must login to " . BOT_NAME . " on IRC to apply to register a channel.<br></h2>\n";
		echo "<br>\n";
		echo "Then <a href=\"javascript:history.go(-1);\">go back</a> and repost the form.<br>\n";
		echo "</body></html>\n\n";
		die;
	}



	$ress = pg_safe_exec("SELECT email FROM users WHERE id='$user_id'");
	$rooo = pg_fetch_object($ress,0);
	if (is_email_locked($LOCK_REGPROC,$rooo->email)) {
                	echo "<html><head><title>REGISTRATION PROCESS</title>";
			std_theme_styles();
			echo "</head>\n";
			std_theme_body("../");
			echo "Sorry, you can't register a channel using your current e-mail address.<br>\n";
			echo "You can request a modification of your email-in-record by <a href=\"../forms/emailchange.php\">clicking here</a>.";
			echo "</body></html>\n\n";
			die;
	}


	$rsts = pg_safe_exec("SELECT signup_ts FROM users WHERE id=" . (int)$user_id);
	$rsto = pg_fetch_object($rsts);
	if ((int)$rsto->signup_ts>0) {
	    $next_channel = time_next_channel($rsto->signup_ts);
        if (has_a_channel($user_id) && sizeof($next_channel) > 0) {
            echo "<html><head><title>REGISTRATION PROCESS</title>";
			std_theme_styles();
			echo "</head>\n";
			std_theme_body("../");
			echo "<b>CHANNEL SERVICE REGISTRATION PROCESS</b><br><hr noshade size=2><br>\n";
            echo "<h2>You need to wait " . seconds2human($next_channel['seconds_next_channel']) . " before you can register a channel.</h2>";
            echo "<br>\n";
            echo "<a href=\"javascript:history.go(-1);\">Go back</a><br>\n";
            echo "</body></html>\n\n";
            die;
        }
	}

	if (!REGPROC_ALLOWMULTIPLE) {
        if (has_a_channel($user_id)) {
            $already_chan=1;
            if ($admin > 0) { // admin bypass
                $admin_bypass=1;
            }
        }
    }



        $res = pg_safe_exec("SELECT * FROM pending WHERE status<3 AND manager_id='$user_id'");
        if (pg_numrows($res)>0) {
                $already_pend=1;

	        /*
	        echo "numrow=" . pg_numrows($res) . "<br><br>";
	        for ($x=0;$x<pg_numrows($res);$x++) {
	        	$rww = pg_fetch_object($res,$x);
	                echo "channel_id=" . $rww->channel_id . "<br>";
	                echo "manager_id=" . $rww->manager_id . "<br>";
	                echo "status=" . $rww->status . "<br><br>";
                }
                */

                if ($admin >= 800) { // admin bypass pending
                        $admin_bypass=1;
                }
        }



        $res = pg_safe_exec("SELECT user_name FROM users WHERE id='$user_id'");
        $row = pg_fetch_object($res,0);
        $user_name = $row->user_name;


echo "<html><head><title>REGISTRATION PROCESS</title>";
std_theme_styles();
echo "</head>\n";
std_theme_body("../");


echo "<b>CHANNEL SERVICE REGISTRATION PROCESS</b><br><hr noshade size=2><br>\n";

/* Force expired NOREG clean up */
pg_safe_exec("DELETE FROM noreg WHERE never_reg='0' AND for_review='0' AND expire_time<" . time());

$res = pg_safe_exec("SELECT * FROM noreg WHERE lower(user_name)='" . strtolower($user_name) . "'");

if (pg_numrows($res)>0) {
	$row = pg_fetch_object($res,0);

	if ($row->id >0) {
		$type = $row->type;
		if ($type==0) { $noreg_type="<b>&lt;NO REASON&gt;</b>"; }
		if ($type==1) { $noreg_type="<b>Non Support</b>"; }
		if ($type==2) { $noreg_type="<b>Abuse</b>"; }
		if ($type>=3) { $noreg_type="<b>" . $row->reason . "</b>"; }

		if ($row->never_reg==1) { $exp_str = "will <b>never</b> expire"; }
		if ($row->never_reg==0) { $exp_str = "will expire on <b>" . cs_time($row->expire_time) . "</b>"; }

		echo "You can't register any channel at the moment (NOREG)<br>\nReason: $noreg_type.<br>\nThis restriction $exp_str.<br>\n";
		echo "<a href=\"../main.php\" target=\"_top\">Back to main</a>.\n";
		echo "</body></html>\n\n";
		die;
	}
} else {

	$ras = pg_safe_exec("SELECT flags FROM users WHERE lower(user_name)='" . strtolower($user_name) . "'");
	$ros = pg_fetch_object($ras,0);
	if ((int)$ros->flags & 0x0008) {
		echo "You can't register any channel at the moment (NOREG)<br>\nReason: Fraud User.<br>\nThis restriction will never expire.<br>\n";
		echo "<a href=\"../main.php\" target=\"_top\">Back to main</a>.\n";
		echo "</body></html>\n\n";
		die;
	}
}


if ($already_pend) {
	if ($admin_bypass) {
		echo "<font color=#" . $cTheme->main_warnmsg . "><b>ADMIN BYPASS</b>($admin)</font>: You have one or more channels already pending registration to you.<br><br>\n";
	} else {
		echo "Sorry, you already have a channel pending registration to you.<br>\n";
		echo "You can only register <b>ONE</b> channel at the time.<br><br>";
		echo "<a href=\"../right.php\">Back to main</a>.\n";
		echo "</body></html>\n\n";
		die;
	}
}

if (!REGPROC_ALLOWMULTIPLE && $already_chan) {
	if ($admin_bypass) {
		echo "<font color=#" . $cTheme->main_warnmsg . "><b>ADMIN BYPASS</b>($admin)</font>: You have one or more channels already registered to you.<br><br>\n";
	} else {
		echo "Sorry, you already have one or more channels registered to you.<br>\n";
		echo "You can only register <b>" . user_channel_limit($user_id) . "</b> channel(s).";
		echo "</body></html>\n\n";
		die;
	}
}

if ($aup==1) { $aup_ok=" checked"; } else { $aup_ok=""; }

if ($ii_channelname=="") { $ii_channelname="#"; }
$ii_description=str_replace("\\\\","\\",str_replace("\\'","'",str_replace("\\\"","\"",urldecode($ii_description))));
$ii_channelname=str_replace("\\\\","\\",str_replace("\\'","'",str_replace("\\\"","\"",urldecode($ii_channelname))));


?>
In order to process your channel registration, you need to accept our <a href="aup.php"><b>A</b>cceptable <b>U</b>se <b>P</b>olicy</a>.<br>
<form name=regproc action=regproc.php method=post onsubmit="return check(this);">
<table border=1 cellspacing=0 cellpadding=3>

<tr>
<td align=right><b>Your username :&nbsp;&nbsp;</b></td>
<td><input type=hidden name=username value="<? echo $user_name ?>"><? echo $user_name ?></td>
</tr>

<tr>
<td align=right><b>YES, I accept the <a href="aup.php">A.U.P.</a> :&nbsp;&nbsp;</b></td>
<td><input type=checkbox name=aup value=1<? echo $aup_ok ?>></td>
</tr>

<tr>
<td align=right><b>The channel you want to register :&nbsp;&nbsp;</b></td>
<td><input type=text name=channel_name size=30 maxlength=255 value="<? echo $ii_channelname ?>"></td>
</tr>

<tr>
<td align=center colspan=2>The channel name <b>MUST</b> start with a <b>#</b></td>
</tr>


<tr>
<td valign=top align=right><b>Description/Purpose of your channel :&nbsp;&nbsp;<br><i>max. 300 chars</i>&nbsp;&nbsp;</b></td>
<td><textarea name=description cols=30 rows=10><? echo $ii_description ?></textarea></td>
</tr>
<? if (REQUIRED_SUPPORTERS>0) {?>
<tr>
<td colspan=2><b>Supporter<? if (REQUIRED_SUPPORTERS>1) {echo "s"; } ?> :&nbsp;&nbsp;</b>(You must provide <? echo REQUIRED_SUPPORTERS ?> username<? if (REQUIRED_SUPPORTERS>1) {echo "s"; } ?> NOT including yourself)<br><font color=#990000><b>WARNING</b></font>: You are required to provide <b>CService username<? if (REQUIRED_SUPPORTERS>1) {echo "s"; } ?></b> NOT e-mail addresse<? if (REQUIRED_SUPPORTERS>1) {echo "s"; } ?>.</td>
</tr>
<? } ?>
<? switch (REQUIRED_SUPPORTERS) {
	case 1:
?>
<tr>
<td colspan=2 align=center><input type=text name=supporter0 size=30 maxlength=20 value="<? echo $ii_supporter0 ?>"></td>
</tr>
<?
		break;
	case 2:
?>
<tr>
<td><input type=text name=supporter0 size=30 maxlength=20 value="<? echo $ii_supporter0 ?>"></td>
<td><input type=text name=supporter1 size=30 maxlength=20 value="<? echo $ii_supporter1 ?>"></td>
</tr>
<?
		break;
	case 3:
?>
<tr>
<td><input type=text name=supporter0 size=30 maxlength=20 value="<? echo $ii_supporter0 ?>"></td>
<td><input type=text name=supporter1 size=30 maxlength=20 value="<? echo $ii_supporter1 ?>"></td>
</tr>

<tr>
<td colspan=2 align=center><input type=text name=supporter2 size=30 maxlength=20 value="<? echo $ii_supporter2 ?>"></td>
</tr>
<?
		break;
	case 4:
?>
<tr>
<td><input type=text name=supporter0 size=30 maxlength=20 value="<? echo $ii_supporter0 ?>"></td>
<td><input type=text name=supporter1 size=30 maxlength=20 value="<? echo $ii_supporter1 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter2 size=30 maxlength=20 value="<? echo $ii_supporter2 ?>"></td>
<td><input type=text name=supporter3 size=30 maxlength=20 value="<? echo $ii_supporter3 ?>"></td>
</tr>
<?
		break;
	case 5:
?>
<tr>
<td><input type=text name=supporter0 size=30 maxlength=20 value="<? echo $ii_supporter0 ?>"></td>
<td><input type=text name=supporter1 size=30 maxlength=20 value="<? echo $ii_supporter1 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter2 size=30 maxlength=20 value="<? echo $ii_supporter2 ?>"></td>
<td><input type=text name=supporter3 size=30 maxlength=20 value="<? echo $ii_supporter3 ?>"></td>
</tr>

<tr>
<td colspan=2 align=center><input type=text name=supporter4 size=30 maxlength=20 value="<? echo $ii_supporter4 ?>"></td>
</tr>
<?
		break;
	case 6:
?>
<tr>
<td><input type=text name=supporter0 size=30 maxlength=20 value="<? echo $ii_supporter0 ?>"></td>
<td><input type=text name=supporter1 size=30 maxlength=20 value="<? echo $ii_supporter1 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter2 size=30 maxlength=20 value="<? echo $ii_supporter2 ?>"></td>
<td><input type=text name=supporter3 size=30 maxlength=20 value="<? echo $ii_supporter3 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter4 size=30 maxlength=20 value="<? echo $ii_supporter4 ?>"></td>
<td><input type=text name=supporter5 size=30 maxlength=20 value="<? echo $ii_supporter5 ?>"></td>
</tr>
<?
		break;
	case 7:
?>
<tr>
<td><input type=text name=supporter0 size=30 maxlength=20 value="<? echo $ii_supporter0 ?>"></td>
<td><input type=text name=supporter1 size=30 maxlength=20 value="<? echo $ii_supporter1 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter2 size=30 maxlength=20 value="<? echo $ii_supporter2 ?>"></td>
<td><input type=text name=supporter3 size=30 maxlength=20 value="<? echo $ii_supporter3 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter4 size=30 maxlength=20 value="<? echo $ii_supporter4 ?>"></td>
<td><input type=text name=supporter5 size=30 maxlength=20 value="<? echo $ii_supporter5 ?>"></td>
</tr>

<tr>
<td colspan=2 align=center><input type=text name=supporter6 size=30 maxlength=20 value="<? echo $ii_supporter6 ?>"></td>
</tr>
<?
		break;
	case 8:
?>
<tr>
<td><input type=text name=supporter0 size=30 maxlength=20 value="<? echo $ii_supporter0 ?>"></td>
<td><input type=text name=supporter1 size=30 maxlength=20 value="<? echo $ii_supporter1 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter2 size=30 maxlength=20 value="<? echo $ii_supporter2 ?>"></td>
<td><input type=text name=supporter3 size=30 maxlength=20 value="<? echo $ii_supporter3 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter4 size=30 maxlength=20 value="<? echo $ii_supporter4 ?>"></td>
<td><input type=text name=supporter5 size=30 maxlength=20 value="<? echo $ii_supporter5 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter6 size=30 maxlength=20 value="<? echo $ii_supporter6 ?>"></td>
<td><input type=text name=supporter7 size=30 maxlength=20 value="<? echo $ii_supporter7 ?>"></td>
</tr>
<?
		break;
	case 9:
?>
<tr>
<td><input type=text name=supporter0 size=30 maxlength=20 value="<? echo $ii_supporter0 ?>"></td>
<td><input type=text name=supporter1 size=30 maxlength=20 value="<? echo $ii_supporter1 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter2 size=30 maxlength=20 value="<? echo $ii_supporter2 ?>"></td>
<td><input type=text name=supporter3 size=30 maxlength=20 value="<? echo $ii_supporter3 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter4 size=30 maxlength=20 value="<? echo $ii_supporter4 ?>"></td>
<td><input type=text name=supporter5 size=30 maxlength=20 value="<? echo $ii_supporter5 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter6 size=30 maxlength=20 value="<? echo $ii_supporter6 ?>"></td>
<td><input type=text name=supporter7 size=30 maxlength=20 value="<? echo $ii_supporter7 ?>"></td>
</tr>

<tr>
<td colspan=2 align=center><input type=text name=supporter8 size=30 maxlength=20 value="<? echo $ii_supporter8 ?>"></td>
</tr>
<?
		break;
	case 10:
?>
<tr>
<td><input type=text name=supporter0 size=30 maxlength=20 value="<? echo $ii_supporter0 ?>"></td>
<td><input type=text name=supporter1 size=30 maxlength=20 value="<? echo $ii_supporter1 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter2 size=30 maxlength=20 value="<? echo $ii_supporter2 ?>"></td>
<td><input type=text name=supporter3 size=30 maxlength=20 value="<? echo $ii_supporter3 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter4 size=30 maxlength=20 value="<? echo $ii_supporter4 ?>"></td>
<td><input type=text name=supporter5 size=30 maxlength=20 value="<? echo $ii_supporter5 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter6 size=30 maxlength=20 value="<? echo $ii_supporter6 ?>"></td>
<td><input type=text name=supporter7 size=30 maxlength=20 value="<? echo $ii_supporter7 ?>"></td>
</tr>

<tr>
<td><input type=text name=supporter8 size=30 maxlength=20 value="<? echo $ii_supporter8 ?>"></td>
<td><input type=text name=supporter9 size=30 maxlength=20 value="<? echo $ii_supporter9 ?>"></td>
</tr>
<?
		break;
	}
?>
<tr>
<td align=center colspan=2><input type=submit value=" <? if (REQUIRED_SUPPORTERS>0) { echo "PROCEED"; } else { echo "REGISTER"; } ?> ">&nbsp;&nbsp;&nbsp;&nbsp;<input type=button value=" CLEAR FORM " onClick="location.href='./';"></td>
</tr>
</table>
<script language="JavaScript1.2">
<!--
function clear_form() {
	var f = document.forms[0];
	f.aup.checked=false;
	f.channel_name.value='#';
	f.description.value='';
<? if (REQUIRED_SUPPORTERS>0) { ?>
	f.supporter0.value='';
	<? if (REQUIRED_SUPPORTERS>1) { ?>
		f.supporter1.value='';
		<? if (REQUIRED_SUPPORTERS>2) { ?>
			f.supporter2.value='';
			<? if (REQUIRED_SUPPORTERS>3) { ?>
				f.supporter3.value='';
				<? if (REQUIRED_SUPPORTERS>4) { ?>
					f.supporter4.value='';
					<? if (REQUIRED_SUPPORTERS>5) { ?>
						f.supporter5.value='';
						<? if (REQUIRED_SUPPORTERS>6) { ?>
							f.supporter6.value='';
							<? if (REQUIRED_SUPPORTERS>7) { ?>
								f.supporter7.value='';
								<? if (REQUIRED_SUPPORTERS>8) { ?>
									f.supporter8.value='';
									<? if (REQUIRED_SUPPORTERS>9) { ?>
										f.supporter9.value='';
									<? } ?>
								<? } ?>
							<? } ?>
						<? } ?>
					<? } ?>
				<? } ?>
			<? } ?>
		<? } ?>
	<? } ?>
<? } ?>
	return(true);
}
function check(f) {
	var state = true;
	var mf = '';
	if (!f.aup.checked) { mf = mf + '- You must ACCEPT the AUP.\n'; state = false; }
	if (f.channel_name.value=="") { mf = mf + '- The channel name\n'; state = false; }
	if (f.description.value=="") { mf = mf + '- The channel description\n'; state = false; }
<? if (REQUIRED_SUPPORTERS>0) { ?>
	if (f.supporter0.value=="") { mf = mf + '- Supporter 1\n'; state = false; }
	<? if (REQUIRED_SUPPORTERS>1) { ?>
		if (f.supporter1.value=="") { mf = mf + '- Supporter 2\n'; state = false; }
		<? if (REQUIRED_SUPPORTERS>2) { ?>
			if (f.supporter2.value=="") { mf = mf + '- Supporter 3\n'; state = false; }
			<? if (REQUIRED_SUPPORTERS>3) { ?>
				if (f.supporter3.value=="") { mf = mf + '- Supporter 4\n'; state = false; }
				<? if (REQUIRED_SUPPORTERS>4) { ?>
					if (f.supporter4.value=="") { mf = mf + '- Supporter 5\n'; state = false; }
					<? if (REQUIRED_SUPPORTERS>5) { ?>
						if (f.supporter5.value=="") { mf = mf + '- Supporter 6\n'; state = false; }
						<? if (REQUIRED_SUPPORTERS>6) { ?>
							if (f.supporter6.value=="") { mf = mf + '- Supporter 6\n'; state = false; }
							<? if (REQUIRED_SUPPORTERS>7) { ?>
								if (f.supporter7.value=="") { mf = mf + '- Supporter 8\n'; state = false; }
								<? if (REQUIRED_SUPPORTERS>8) { ?>
									if (f.supporter8.value=="") { mf = mf + '- Supporter 9\n'; state = false; }
									<? if (REQUIRED_SUPPORTERS>9) { ?>
										if (f.supporter9.value=="") { mf = mf + '- Supporter 10\n'; state = false; }
									<? } ?>
								<? } ?>
							<? } ?>
						<? } ?>
					<? } ?>
				<? } ?>
			<? } ?>
		<? } ?>
	<? } ?>
<? } ?>
	if (state) {
		if (f.description.value.length>300) {
			alert('Description exceeds 300 chars.\n');
			return(false);
		}
	}
	if (!state) {
		alert('** Missing Required Fields **\n\n'+mf);
	}
	return(state);
}
//-->
</script>
<? make_secure_form($user_name.CRC_SALT_0002); ?>
</form>
</body>
</html>
