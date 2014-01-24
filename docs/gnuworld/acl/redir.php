<?
/* $Id: redir.php,v 1.1 2003/08/31 19:52:16 nighty Exp $ */
header("Location: " . urldecode($_GET["RET"]) . "\n\n");
die;
?>
