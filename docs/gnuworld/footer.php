<?
include("../../php_includes/cmaster.inc");
$cTheme = get_theme_info();
?>
<!-- $Id: footer.php,v 1.11 2005/10/05 07:21:10 nighty Exp $ //-->
<HTML>
<head><title>FOOTER</title>
<style type=text/css>
<!--
a:link { font-family: arial,helvetica; color: #<?=$cTheme->bottom_linkcolor?>;  }
a:visited { font-family: arial,helvetica; color: #<?=$cTheme->bottom_linkcolor?>; }
a:hover { font-family: arial,helvetica; color: #<?=$cTheme->bottom_linkover?>; }
//-->
</style>
</head>
<BODY BGCOLOR="#<?=$cTheme->bottom_bgcolor?>" TEXT="#<?=$cTheme->bottom_textcolor?>" LINK="#<?=$cTheme->bottom_linkcolor?>" VLINK="#<?=$cTheme->bottom_linkcolor?>" ALINK="#<?=$cTheme->bottom_linkover?>" TOPMARGIN=1 LEFTMARGIN=1 MARGINHEIGHT=1 MARGINWIDTH=1<?
if ($cTheme->bottom_bgimage!="") {
	echo " BACKGROUND=\"themes/data/" . $cTheme->sub_dir . "/" . $cTheme->bottom_bgimage . "\"";
}
?>>
<TABLE WIDTH=100%>
        <TR>
                <TD>
                        <?
//if (HOSTING_LOGO=="" && HOSTING_URL=="") {
	echo "&nbsp;";
/* } else {
	if (HOSTING_CLICK_CHECK) {
		$_t_URL = "sponsor_redirection.php?redir=" . urlencode(HOSTING_URL);
		$_t_Over = "onMouseOver=\"window.status='" . HOSTING_URL . "'; return true;\" onMouseOut=\"window.status=''; return true;\" ";
	} else {
		$_t_URL = HOSTING_URL;
		$_t_Over = "";
	}
	echo "<font face=\"arial\" size=-2>Sponsored by : </font>";
	if (HOSTING_LOGO!="") {
		echo "<a href=\"" . $_t_URL . "\" " . $_t_Over . "target=_blank><img align=absmiddle src=images/" . HOSTING_LOGO . " border=0 alt=\"" . HOSTING_URL . "\"></a>\n";
	} else {
		echo "<a href=\"" . $_t_URL . "\" " . $_t_Over . "target=_blank>" . HOSTING_URL . "</a>\n";
	}
}
*/
                        ?>
                </TD>
                <TD WIDTH=50%>
                        <FONT FACE="arial" SIZE="-2">The contents of these pages are copyright by the <? echo NETWORK_NAME ?> Channel Service.
		You may only link to the main page of the website.<br>This page is maintained by <a href="mailto:<? echo NETWORK_EMAIL ?>">The <? echo NETWORK_NAME ?> Channel Service</a>.&nbsp;<a href="legal.php" target=right>Privacy and linking policies</a> [<A href="credits.php" target=right>Other Credits</a>]
                </TD>
        </TR>
</TABLE>
</BODY>
