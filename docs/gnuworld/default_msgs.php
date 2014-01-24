<?php
/* $Id: default_msgs.php,v 1.2 2004/03/18 15:30:42 nighty Exp $ */
unset($min_lvl);
header("Pragma: no-cache");
$min_lvl=800;
require("../../php_includes/cmaster.inc");
std_connect();
$user_id = std_security_chk($auth);
$admin = std_admin();
$cTheme = get_theme_info();
if ($admin<$min_lvl) {
	echo "Not allowed, sorry.";
	die;
}
if ($_GET["type"]=="remove" && $_GET["id"]>0) {
	pg_safe_exec("DELETE FROM default_msgs WHERE id='" . (int)$_GET["id"] . "'");
	if ($_GET["type2"]==1) {
		js_redir("default_msgs.php?type=review");
	} else {
		js_redir("default_msgs.php?type=complaints");
	}
}
echo "<html><head><title></title>\n";
?>
<style type=text/css>
<!--
a:link { font-family: arial,helvetica; color: #<?=$cTheme->main_linkcolor?>;  }
a:visited { font-family: arial,helvetica; color: #<?=$cTheme->main_vlinkcolor?>; }
a:hover { font-family: arial,helvetica; color: #<?=$cTheme->main_linkover?>; }
//-->
</style>
<?php
	echo "</head>";
	std_theme_body();
	echo "<form name=dmesg method=GET>\n";

	if ($_GET["type"]=="addacc") {
		if ($_GET["posted"]!=1 || trim($_GET["rlabel"])=="" || trim($_GET["rcontent"])=="") {
			echo "<h3>Add default reply for ACCEPT</h3><a href=\"default_msgs.php?type=review\">Go Back</a><hr width=100% noshade size=1><br>\n";
			echo "Label :<br>\n";
			echo "<input type=text name=rlabel size=60 maxlength=255><br>\n";
			echo "Content :<br>\n";
			echo "<textarea name=rcontent cols=60 rows=10 wrap></textarea><br>\n";
			echo "<br>\n";
			echo "<input type=hidden name=posted value=1>\n";
			echo "<input type=hidden name=type value=\"addacc\">\n";
			echo "<input type=submit value=\"Add\">\n";
		} else {
			$rq = pg_safe_exec("INSERT INTO default_msgs (type, label, content) VALUES (1, '" . post2db($_GET["rlabel"]) . "', '" . post2db($_GET["rcontent"]) . "')");
			if (!$rq) {
				die(pg_errormessage());
			} else {
				js_redir("default_msgs.php?type=review");
			}
		}
	}
	if ($_GET["type"]=="addrej") {
		if ($_GET["posted"]!=1 || trim($_GET["rlabel"])=="" || trim($_GET["rcontent"])=="") {
			echo "<h3>Add default reply for REJECT</h3><a href=\"default_msgs.php?type=review\">Go Back</a><hr width=100% noshade size=1><br>\n";
			echo "Label :<br>\n";
			echo "<input type=text name=rlabel size=60 maxlength=255><br>\n";
			echo "Content :<br>\n";
			echo "<textarea name=rcontent cols=60 rows=10 wrap></textarea><br>\n";
			echo "<br>\n";
			echo "<input type=hidden name=posted value=1>\n";
			echo "<input type=hidden name=type value=\"addrej\">\n";
			echo "<input type=submit value=\"Add\">\n";
		} else {
			$rq = pg_safe_exec("INSERT INTO default_msgs (type, label, content) VALUES (2, '" . post2db($_GET["rlabel"]) . "', '" . post2db($_GET["rcontent"]) . "')");
			if (!$rq) {
				die(pg_errormessage());
			} else {
				js_redir("default_msgs.php?type=review");
			}
		}

	}
	if ($_GET["type"]=="addcom") {
		if ($_GET["posted"]!=1 || trim($_GET["rlabel"])=="" || trim($_GET["rcontent"])=="") {
			echo "<h3>Add default reply for COMPLAINTS</h3><a href=\"default_msgs.php?type=complaints\">Go Back</a><hr width=100% noshade size=1><br>\n";
			echo "Label :<br>\n";
			echo "<input type=text name=rlabel size=60 maxlength=255><br>\n";
			echo "Content :<br>\n";
			echo "<textarea name=rcontent cols=60 rows=10 wrap></textarea><br>\n";
			echo "<br>\n";
			echo "<input type=hidden name=posted value=1>\n";
			echo "<input type=hidden name=type value=\"addcom\">\n";
			echo "<input type=submit value=\"Add\">\n";
		} else {
			$rq = pg_safe_exec("INSERT INTO default_msgs (type, label, content) VALUES (3, '" . post2db($_GET["rlabel"]) . "', '" . post2db($_GET["rcontent"]) . "')");
			if (!$rq) {
				die(pg_errormessage());
			} else {
				js_redir("default_msgs.php?type=complaints");
			}
		}

	}

	if ($_GET["type"]=="modify" && $_GET["id"]>0) {
		if ($_GET["posted"]!=1 || trim($_GET["rlabel"])=="" || trim($_GET["rcontent"])=="") {
			$umr = pg_safe_exec("SELECT * FROM default_msgs WHERE id='" . (int)$_GET["id"] . "'");
			$umo = pg_fetch_object($umr);
			echo "<h3>Edit the reply</h3><a href=\"javascript:history.go(-1);\">Go Back</a><hr width=100% noshade size=1><br>\n";
		echo "Label :<br>\n";
			echo "<input type=text name=rlabel size=60 maxlength=255 value=\"" . post2input($umo->label) . "\"><br>\n";
			echo "Content :<br>\n";
			echo "<textarea name=rcontent cols=60 rows=10 wrap>" . post2textarea($umo->content) . "</textarea><br>\n";
			echo "<br>\n";
			echo "<input type=hidden name=posted value=1>\n";
			echo "<input type=hidden name=type value=\"modify\">\n";
			echo "<input type=hidden name=type2 value=\"" . $_GET["type2"] . "\">\n";
			echo "<input type=hidden name=id value=\"" . $_GET["id"] . "\">\n";
			echo "<input type=submit value=\"Save changes\">\n";
		} else {
			$rq = pg_safe_exec("UPDATE default_msgs SET label='" . post2db($_GET["rlabel"]) . "', content='" . post2db($_GET["rcontent"]) . "' WHERE id='" . (int)$_GET["id"] . "'");
			if (!$rq) {
				die(pg_errormessage());
			} else {
				if ($_GET["type2"]==1) {
					js_redir("default_msgs.php?type=review");
				} else {
					js_redir("default_msgs.php?type=complaints");
				}
			}
		}
	}
	if ($_GET["type"]=="complaints") {
		echo "<h3>Edit default complaint replies</h3><a href=\"complaints/admin.php\"><b>Complaints Manager Home</b></a><hr width=100% noshade size=1><br>\n";
		$crm = pg_safe_exec("SELECT * FROM default_msgs WHERE type=3");
		echo "<b>COMPLAINT REPLIES</b> <input type=button value=\"Add\" onClick=\"location.href='default_msgs.php?type=addcom'\"><br>\n";
		echo "<table width=650 border=1 cellspacing=0 cellpadding=5 bordercolor=#000000>\n";
		echo "<tr bgcolor=#eeeeee>";
		echo "<td>Label</td><td>Content</td><td>Action</td>\n";
		echo "</tr>\n";
		while ($crmo = pg_fetch_object($crm)) {
			echo "<tr>";
			echo "<td valign=top>" . db2disp($crmo->label) . "</td>\n";
			echo "<td valign=top>" . db2disp($crmo->content) . "</td>\n";
			echo "<td valign=top>";
			echo "<input type=button value=\"Del\" onClick=\"del_msg(" . $crmo->id . ",2)\"><br>\n";
			echo "<input type=button value=\"Edit\" onClick=\"edt_msg(" . $crmo->id . ",2)\"><br>\n";
			echo "&nbsp;</td>";
			echo "</tr>\n";
		}
		echo "</table><br><br><br>\n";
	}
	if ($_GET["type"]=="review") {
		echo "<h3>Edit default ACCEPT/REJECT replies</h3><hr width=100% noshade size=1><br>\n";
		$arm = pg_safe_exec("SELECT * FROM default_msgs WHERE type=1");
		echo "<b>ACCEPT MESSAGES</b> <input type=button value=\"Add\" onClick=\"location.href='default_msgs.php?type=addacc'\"><br>\n";
		echo "<table width=650 border=1 cellspacing=0 cellpadding=5 bordercolor=#000000>\n";
		echo "<tr bgcolor=#eeeeee>";
		echo "<td>Label</td><td>Content</td><td>Action</td>\n";
		echo "</tr>\n";
		while ($armo = pg_fetch_object($arm)) {
			echo "<tr>";
			echo "<td valign=top>" . db2disp($armo->label) . "</td>\n";
			echo "<td valign=top>" . db2disp($armo->content) . "</td>\n";
			echo "<td valign=top>";
			echo "<input type=button value=\"Del\" onClick=\"del_msg(" . $armo->id . ",1)\"><br>\n";
			echo "<input type=button value=\"Edit\" onClick=\"edt_msg(" . $armo->id . ",1)\"><br>\n";
			echo "&nbsp;</td>";
			echo "</tr>\n";
		}
		echo "</table><br><br><br>\n";


		$rrm = pg_safe_exec("SELECT * FROM default_msgs WHERE type=2");
		echo "<b>REJECT MESSAGES</b> <input type=button value=\"Add\" onClick=\"location.href='default_msgs.php?type=addrej'\"><br>\n";
		echo "<table width=650 border=1 cellspacing=0 cellpadding=5 bordercolor=#000000>\n";
		echo "<tr bgcolor=#eeeeee>";
		echo "<td>Label</td><td>Content</td><td>Action</td>\n";
		echo "</tr>\n";
		while ($rrmo = pg_fetch_object($rrm)) {
			echo "<tr>";
			echo "<td valign=top>" . db2disp($rrmo->label) . "</td>\n";
			echo "<td valign=top>" . db2disp($rrmo->content) . "</td>\n";
			echo "<td valign=top>";
			echo "<input type=button value=\"Del\" onClick=\"del_msg(" . $rrmo->id . ",1)\"><br>\n";
			echo "<input type=button value=\"Edit\" onClick=\"edt_msg(" . $rrmo->id . ",1)\"><br>\n";
			echo "&nbsp;</td>";
			echo "</tr>\n";
		}
		echo "</table><br><br><br>\n";
	}

?>
<script language="JavaScript">
<!--
function del_msg(id,type2) {
	if (confirm('Are you sure you want to remove this default message ?')) {
		location.href='default_msgs.php?type=remove&type2='+parseInt(type2)+'&id='+parseInt(id);
	}
}
function edt_msg(id,type2) {
	location.href='default_msgs.php?type=modify&type2='+parseInt(type2)+'&id='+parseInt(id);
}
//-->
</script>
</form>
</body>
</html>
