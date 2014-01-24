<?
/* $Id: sponsor_redirection.php,v 1.1 2003/01/16 06:34:03 nighty Exp $ */
include("../../php_includes/cmaster.inc");

function hosting_stats_add_click() {
	if (HOSTING_CLICK_CHECK) {
		$fd = @fopen(HOSTING_STATS_FILENAME,"r+");
		if ($fd) {
			$_TS = time();
			$rd_file = fread($fd,filesize(HOSTING_STATS_FILENAME));
			if (strlen($rd_file)>3) {
				$rd_obj = explode(" ",$rd_file);
				$rd_obj[0] = ($rd_obj[0]+0);
				$rd_obj[1] = ($rd_obj[1]+0);
				$rd_obj[2] = ($rd_obj[2]+0);
			} else {
				$rd_obj[0] = 0;
				$rd_obj[1] = 0;
				$rd_obj[2] = 0;
			}
			if ($rd_obj[1]>0) { // already started
				$rd_obj[2] = $_TS;
			} else { // start it
				$rd_obj[1] = $_TS;
				$rd_obj[2] = $_TS;
			}
			$rd_obj[0] = ($rd_obj[0]+1);
			$_to_write = implode(" ",$rd_obj);
			rewind($fd);
			fwrite($fd,$_to_write,strlen($_to_write));
/*
			$amount = fread($fd,filesize(HOSTING_STATS_FILENAME));
			$amt = (($amount*1)+1);
			rewind($fd);
			fwrite($fd,$amt,strlen($amt));
*/
			fclose($fd);
		}
	}
}

if (HOSTING_CLICK_CHECK) {
	$l_ref = strtolower($_SERVER["HTTP_REFERER"]);
	$l_chk = str_replace("/","\/",strtolower( IFACE_URL ));
	if (preg_match("/^" . $l_chk . ".*$/",$l_ref)) {
		hosting_stats_add_click();
	}
	$l_url = "./";
	if ($_GET["redir"]!="") { $l_url = $_GET["redir"]; }
	header("Location: " . $l_url . "\n\n");
	die;
} else {
	die("This page is disabled.");
}
?>
