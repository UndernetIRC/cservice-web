<?
require("../../../php_includes/cmaster.inc");
if (ENABLE_COMPLAINTS_MODULE != 1) { die("This option is disabled. Please contact the server administrator."); }
?>
<!-- $Id: complaints_inframe.php,v 1.1 2003/08/31 19:52:17 nighty Exp $ //-->
<HTML>
<HEAD>
        <TITLE><? echo NETWORK_NAME ?>&nbsp;&nbsp;&nbsp;C h a n n e l&nbsp;&nbsp;&nbsp;S e r v i c e&nbsp;&nbsp;C o m p l a i n t s&nbsp;&nbsp;D e p a r t m e n t</TITLE>
</HEAD>
<FRAMESET COLS="120,*" frameborder=no framespacing=0 border=0>
	<FRAME SRC=../left.php NAME=left SCROLLING=AUTO>
	<FRAME SRC=complaints.php NAME=right SCROLLING=AUTO>
	<NOFRAMES>
	<? std_theme_body(); ?>
	Viewing this page requires a browser capable of displaying frames.
	</BODY>
	</NOFRAMES>
</FRAMESET>
</HTML>
