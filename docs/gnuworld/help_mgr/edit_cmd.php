<?

        $CAN_EDIT = 1;
        $CAN_ADD = 2;
$ENABLE_COOKIE_TABLE=0;

        include("../../../php_includes/cmaster.inc");
        std_init();
        $cTheme = get_theme_info();

        $FORCE_GET = 1;
        if (!acl(XHELP)) {
                echo "You are not allowed to use that page.";
                die;
        }

        $lang_test = $ACL_XTRA;
        if ($lang_test!=0 && $lang_test!=$lang_id && $admin<800) {
                echo "You are not allowed to use that page.";
                die;
        }

        $res2 = pg_safe_exec("SELECT * FROM languages WHERE id='$langid'");
        if (pg_numrows($res2)==0) {
                echo "Invalid Language ID, sorry.";
                die;
        }

        $row2 = pg_fetch_object($res2,0);
        $lang_name = $row2->name;
        if (!acl(XHELP_CAN_EDIT)) {
                echo "You are not allowed to use that page. (Permission denied)";
                die;
        }

        $tst = pg_safe_exec("SELECT * FROM help WHERE topic='" . strtoupper($cmdname) . "' AND language_id='$langid'");
        if (pg_numrows($tst)==0) {
                echo "Nonexistent COMMAND NAME for specified language ID.";
                die;
        } else {
                $thecmd = pg_fetch_object($tst,0);
        }

        if ($crc == md5($HTTP_USER_AGENT . $ts . $user_id)) {

                $contents_ok = str_replace("\\\"","\"",str_replace("\n","\\n",str_replace("\r","",$contents)));

                $query = "UPDATE help SET contents=E'$contents_ok' WHERE topic='" . strtoupper($cmdname) . "' AND language_id='$langid'";

                //echo $query;
                pg_safe_exec($query);

                header("Location: edit.php?lang_id=$langid");
                die;
        }

?>
<html>
<head><title>HELP TEXT MANAGER</title>
<? std_theme_styles(); ?>
</head>
<? std_theme_body("../"); ?>
<h2><b>Edit HELP TEXT for '<? echo $cmdname ?>' (<? echo $lang_name ?>)</b><br></h2>
<a href="edit.php?lang_id=<? echo $langid ?>">&lt;&lt;&nbsp;Back</a>

<form name=editcmd action=edit_cmd.php method=post>
<?
        $zets = time();
        $zecrc = md5($HTTP_USER_AGENT . $zets . $user_id);
        echo "<input type=hidden name=ts value=$zets>\n";
        echo "<input type=hidden name=crc value=$zecrc>\n";
        echo "<input type=hidden name=langid value=$langid>\n";
        echo "<input type=hidden name=cmdname value=\"$cmdname\">\n";

?>
<table border=0 cellspacing=1 cellpadding=3>
<tr>
<td bgcolor=#<?=$cTheme->table_headcolor?> valign=top><font color=#<?=$cTheme->table_headtextcolor?>><b>
Language
</b></font></td><td bgcolor=#<?=$cTheme->table_headtextcolor?>>
<? echo $lang_name ?>
</td></tr>
<td bgcolor=#<?=$cTheme->table_headcolor?> valign=top><font color=#<?=$cTheme->table_headtextcolor?>><b>
Command
</b></font></td><td bgcolor=#<?=$cTheme->table_headtextcolor?>>
<? echo $cmdname ?>
</td></tr>
<td bgcolor=#<?=$cTheme->table_headcolor?> valign=top><font color=#<?=$cTheme->table_headtextcolor?>><b>
HELP Output
</b></font></td><td bgcolor=#<?=$cTheme->table_headtextcolor?>>
<textarea name=contents cols=50 rows=15><? echo trim($thecmd->contents) ?></textarea>
</td></tr>
<tr>
<td colspan=2 align=center>
<input type=submit value=" APPLY CHANGES ">
</td>
</tr>
</table>



</form>

</body>
</html>
