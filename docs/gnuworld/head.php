<?
include("../../php_includes/cmaster.inc");
$cTheme = get_theme_info();
?>
<!-- $Id: head.php,v 1.5 2002/05/20 23:58:03 nighty Exp $ //-->
<HTML>
<HEAD>
        <TITLE><? echo NETWORK_NAME ?>&nbsp;&nbsp;&nbsp;C h a n n e l&nbsp;&nbsp;&nbsp;S e r v i c e</TITLE>

</HEAD>
<BODY BGCOLOR=#<?=$cTheme->top_bgcolor?> TEXT=#<?=$cTheme->main_textcolor?> alink=#004400 link=#004400 vlink=#004400 marginwidth=0 marginheight=0 topmargin=0 bottommargin=0 leftmargin=0 rightmargin=0<?
if ($cTheme->top_bgimage!="") {
	echo " BACKGROUND=\"themes/data/" . $cTheme->sub_dir . "/" . $cTheme->top_bgimage . "\"";
}
?>>
<TABLE WIDTH=100% CELLSPACING="0" CELLPADDING="0">
        <TR>
                <TD VALIGN=middle>
                        <IMG SRC="themes/data/<?=$cTheme->sub_dir?>/<?=$cTheme->top_logo?>">
                </TD>
		<td align=right>&nbsp;<?
		if (file_exists("testnet")) {
			echo "Selected Theme:&nbsp;<b>" . $cTheme->name . "</b>&nbsp;&nbsp;\n";
		}
		?></td>
	</tr>
</table>
</body>
</html>
