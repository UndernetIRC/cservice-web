<?
/* $Id: object_app.php,v 1.5 2003/06/11 01:16:33 nighty Exp $ */
	$min_lvl=800;
	require("../../php_includes/cmaster.inc");
	std_connect();
	$user_id = std_security_chk($auth);
	$admin = std_admin();
$cTheme = get_theme_info();
	if ($user_id==0) {
		echo "You must be logged in to view that page !<br><br>";
		echo "<a href=\"index.php\" target=_top>click here</a><br>\n";
		die;
	}
	if ($admin>0) {
		if ($admin_only=="Y") {
			$admin_only="Y";
		} else {
			$admin_only="N";
		}
	} else {
		$admin_only="N";
	}

	if (trim($comment)=="") {
		die("Please enter something in the comment !<br><br><a href=\"javascript:history.go(-1);\">go back</a>.");
	}

$nrw_lvl = 0;
if (acl(XWEBAXS_2)) { $nrw_lvl = 1; }
if (acl(XWEBAXS_3)) { $nrw_lvl = 2; }

	if ($admin_only!="N" && $nrw_lvl<1) { $admin_only="N"; }
	$s_URI=explode("?",$HTTP_REFERER);

	$res = pg_safe_exec("SELECT user_name FROM users WHERE id='" . $user_id . "'");
	$row = pg_fetch_object($res,0);
	$username = $row->user_name;


	$res = pg_safe_exec("SELECT * FROM objections WHERE user_id='$user_id' AND channel_id='$channel_id' AND admin_only='N'");
	if (pg_numrows($res)>0) {
		$row = pg_fetch_object($res,0);
		$c_id = $row->channel_id;
		if ($c_id>0 && $admin==0) {
			if ($channel_id==$c_id) {
				header("Location: view_app.php?" . $s_URI[1] . "&err=2&comment=" . urlencode($comment) . "\n\n");
			} else {
				echo "Hmmm. Please come to #CService and say you had an error type 42.\n";
			}
			die;
		}
	}
	if ($user_name!=$username || $comment=="" || strlen($comment)>700 || $channel_id=="" || $channel_id==0) {
		header("Location: view_app.php?" . $s_URI[1] . "&err=1&comment=" . urlencode($comment) . "\n\n");
		die;

	} else {
		$s_comment=str_replace("\\&quot;","&quot;",str_replace("\n","<br>",htmlspecialchars($comment)));
		$s_ts=time();
		$query = "INSERT INTO objections (channel_id,user_id,comment,created_ts,admin_only) VALUES ($channel_id,$user_id,'$s_comment',$s_ts,'$admin_only')";
		pg_safe_exec($query);
		//echo htmlspecialchars($query);

		header("Location: view_app.php?" . $s_URI[1]);
		die;
	}


?>
