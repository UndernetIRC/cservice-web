<?
	include("../../../php_includes/cmaster.inc");
	std_init();
	if ($admin<901) {
		echo "This page is for coders only.";
		die;
	}

$x_cmds = array(
"ACCESS",
"ADDCOMMENT",
"ADDUSER",
"BAN",
"BANLIST",
"CHANINFO",
"CLEARMODE",
"DEOP",
"DEVOICE",
"FORCE",
"HELP",
"INFO",
"INVITE",
"ISREG",
"JOIN",
"KICK",
"LBANLIST",
"LOGIN",
"MODINFO",
"MOTD",
"NEWPASS",
"OP",
"OPERJOIN",
"OPERPART",
"PANIC",
"PART",
"PURGE",
"QUOTE",
"REGISTER",
"REHASH",
"REMIGNORE",
"REMOVEALL",
"REMUSER",
"SAY",
"SEARCH",
"SERVNOTICE",
"SET",
"SET AUTOJOIN",
"SET AUTOTOPIC",
"SET DESCRIPTION",
"SET INVISIBLE",
"SET KEYWORDS",
"SET LANG",
"SET MASSDEOP",
"SET MASSDEOPPRO",
"SET MAXLOGINS",
"SET MODE",
"SET NOOP",
"SET STRICTOP",
"SET URL",
"SET USERFLAGS",
"SHOWCOMMANDS",
"SHOWIGNORE",
"STATS",
"STATUS",
"SUPPORT",
"SUSPEND",
"SUSPENDME",
"TOPIC",
"UNBAN",
"UNFORCE",
"UNSUSPEND",
"VERIFY",
"VOICE");

$res = pg_safe_exec("SELECT COUNT(*) as count FROM help WHERE language_id='$lid'");
$row = pg_fetch_object($res,0);
if ($row->count==0) {
	for ($x=0;$x<count($x_cmds);$x++) {
		pg_safe_exec("INSERT INTO help (topic,language_id,contents) VALUES ('$x_cmds[$x]','$lid','')");
	}
	header("Location: edit.php?lang_id=$lid");
	die;
} else {
	echo "Table 'help' for language_id '$lid' already contains data, please empty it first.";
	die;
}
?>
