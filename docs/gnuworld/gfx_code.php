<?
/* $Id: gfx_code.php,v 1.2 2003/02/09 13:01:05 nighty Exp $ */
require("../../php_includes/blackhole.inc");
require("../../php_includes/cmaster.inc");

if (SHOW_GFXUSRCHK && NEWUSERS_GFXCHECK) {
	if (!extension_loaded("gd")) { die("GD Library not present !"); }
	$ENABLE_COOKIE_TABLE = 1;

	$fontList = get_font_face_list();
	unset($dFID); $dFID = rand(0,(count($fontList)-1)); // random available font face

	pg_safe_exec("DELETE FROM gfxcodes WHERE expire<now()::abstime::int4");
	$r = pg_safe_exec("SELECT code FROM gfxcodes WHERE crc='" . $_GET["crc"] . "'");
	if ($o = pg_fetch_object($r)) {
		$code_gen = $o->code;
		// generate the picture
		if (GFX_SECURE_MODE==1) {
			img_label($code_gen,SPECIFIC_FONT);
		} else {
			img_label2($code_gen,SPECIFIC_FONT2);
		}
	} else {
		img_label("INVALID CRC !!!",SPECIFIC_FONT,24,"#ff1111","#ffffff");
	}
} else {
	die("Page is disabled.");
}
?>
