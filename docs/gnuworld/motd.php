<?
        require('../../php_includes/cmaster.inc');
        std_init();

/* $Id: motd.php,v 1.2 2002/05/20 23:58:03 nighty Exp $ */

$cTheme = get_theme_info();
std_theme_styles(1);
std_theme_body();

       // orinal file by LordLuke

        if ($admin >= 800) {
                if ($reset != "") {
                        pg_safe_exec("DELETE from translations where response_id=9999");
                }
                elseif ($newmotd != "") {
                        $newmotd=str_replace ("[B]",chr(2),$newmotd);
                        $newmotd=str_replace ("[U]",chr(31),$newmotd);
                        $newmotd=str_replace ("[R]",chr(22),$newmotd);
                        $newmotd=str_replace ("[CR]","\n",$newmotd);
                        if ($nomotd) {
                                pg_safe_exec("INSERT into translations (language_id,response_id,text,last_updated,deleted) values(1,9999,'". $newmotd . "'," . time() . ",0)");
                                $nomotd = "";
                        }
                        else {
                                pg_safe_exec("UPDATE translations set text='". $newmotd . "',last_updated=" . time() . " where response_id=9999");
                        }

                }
?>
                <script language="JavaScript">
                function bold() {
                        document.motd.newmotd.value = document.motd.newmotd.value + '[B]';
                        document.motd.newmotd.focus();
                }
                function underline() {
                        document.motd.newmotd.value = document.motd.newmotd.value + '[U]';
                        document.motd.newmotd.focus();
                }
                function reverse() {
                        document.motd.newmotd.value = document.motd.newmotd.value + '[R]';
                        document.motd.newmotd.focus();
                }
                function cr() {
                        document.motd.newmotd.value = document.motd.newmotd.value + '[CR]';
                        document.motd.newmotd.focus();
                }
                </script>
                <table width="100%" align="center" bgcolor="<?=$cTheme->table_bgcolor?>" border=1>
                <tr><td colspan="5" align="center"><H2>Current Motd</H2></td></tr>
<?
                $motd=pg_safe_exec("SELECT text from translations where response_id='9999'");
                if (pg_numrows($motd) > 0) {
                        $mitem=pg_fetch_object($motd,0);
                        echo "<tr><td colspan=5 align=center>" . $mitem->text . "</td></tr>";
                }
                else {
                        echo "<tr><td colspan=5 align=center>No current Motd</td></tr>";
                        $nomotd="true";
                }
?>
                </table>
                <br><br>
                <table width="100%" align="center" bgcolor="<?=$cTheme->table_bgcolor?>" border=1>
                <tr><td colspan="5" align="center"><H2>New Motd</H2></td></tr>

                <tr>
                <form name="motd" method="post" action="motd.php">
                        <td align="right" width="50%"><input type="button" value="Bold" onclick="bold();"></td>
                        <td align="center"><input type="button" value="Underline" onclick="underline();"></td>
                        <td align="center"><input type="button" value="Reverse" onclick="reverse();"></td>
                        <td align="left" width="50%"><input type="button" value="Return" onclick="cr();"></td></tr>
                        <tr><td colspan="5" align="center"><textarea name="newmotd" cols="60" rows="5"></textarea><br></td></tr>
                        <tr><td colspan="2" align="right"><input type="submit" name="Submit" value="Set MOTD"></td>
                        <td colspan="2" align="left"><input type="submit" name="reset" value="Clear MOTD"></td></tr>
                        <input type="hidden" name="nomotd" value="<?echo $nomotd?>">
                </form>
                </table>
<?
        }
        else {
                echo "This page is not for you";
        }

?>
</body>
</html>
