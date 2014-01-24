<? 
//die("<img src=april_fools.jpg border=0>");
include("../../../php_includes/cmaster.inc");
$cTheme = get_theme_info();
?>
<!-- $Id: main.php,v 1.7 2002/05/20 23:58:03 nighty Exp $ //-->
<HTML>
<HEAD>
        <TITLE><? echo NETWORK_NAME ?>&nbsp;&nbsp;&nbsp;C h a n n e l&nbsp;&nbsp;&nbsp;S e r v i c e</TITLE>
</HEAD>
<FRAMESET COLS="120,*" frameborder=no framespacing=0 border=0>
<?

	echo "<FRAME SRC=\"../left.php\" NAME=left SCROLLING=AUTO>\n";
        echo "<FRAME SRC=\"nickserv.php\" NAME=right SCROLLING=AUTO>\n";
?>
</FRAMESET>
<? std_theme_body(); ?>
Viewing this page requires a browser capable of displaying frames.
</BODY>
</HTML>
