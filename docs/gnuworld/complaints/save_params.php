<?
/* $Id: save_params.php,v 1.1 2003/10/31 04:11:14 nighty Exp $ */
require("../../../php_includes/cmaster.inc");
if (ENABLE_COMPLAINTS_MODULE != 1) { die("This option is disabled. Please contact the server administrator."); }
std_init();
if (!acl(XCOMPLAINTS_ADM_REPLY) && !acl(XCOMPLAINTS_ADM_READ)) {
	$cTheme = get_theme_info();
	std_theme_styles(1);
	echo "<style type=text/css>\n";
	echo "<!--\n";
	echo "td { font-size: 10pt; }\n";
	echo "//-->\n";
	echo "</style>\n";
	std_theme_body();
	die("Your level is too low to access this page</body></html>");
}
if ($_GET["clear"]==1 && preg_match("/^[0123]¤[0-9]+¤[01]¤[01]¤[01]¤[01]¤[01]¤[01]¤[01]$/",$_COOKIE["COMPLAINTSPARAM"])) {
	setCookie("COMPLAINTSPARAM","",0,"/");
} elseif (preg_match("/^[0123]¤[0-9]+¤[01]¤[01]¤[01]¤[01]¤[01]¤[01]¤[01]$/",$_GET["P"])) {
	setCookie("COMPLAINTSPARAM",urldecode($_GET["P"]),time()+(86400*30),"/");
}
header("Location: admin.php\n\n");
die;
?>
