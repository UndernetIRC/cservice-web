<?php
require("../../php_includes/cmaster.inc");
std_connect();
if ($sel_theme)
	{
	if (COOKIE_DOMAIN!="") {
	SetCookie("cstheme",$sel_theme,2147483645,"/",COOKIE_DOMAIN) or die ("Can not set cookie");
				}
	SetCookie("cstheme",$sel_theme,2147483645,"/") or die ("Can not set cookie");;
echo '
<script type="text/javascript" >
  window.opener.popUpClosed();
  self.close();

</script>';

}
$cTheme = get_theme_info();
			std_theme_styles(1);
			std_theme_body();

	global $ENABLE_COOKIE_TABLE;
	$ECT = $ENABLE_COOKIE_TABLE;
	$ENABLE_COOKIE_TABLE = 1;
$res = pg_safe_exec("SELECT * FROM themes "); 
for ($i=0;$i<pg_numrows($res); $i++) 
{
$themes[] = pg_fetch_object($res,$i);
}
	$ENABLE_COOKIE_TABLE = $ECT;
$current= $cTheme->name;
echo "<h3>Please select the theme you want to use:</h3>";
echo '<form name="theme" method="post" >';
echo '<select name="sel_theme" onchange="document.theme.submit()">';

for ($i=0; $i<count($themes);$i++)
	{
	$selected='';
	if ($themes[$i]->name == $current)
		$selected = 'selected';
	echo '<option value="'.$themes[$i]->name.'" '.$selected.'>'.$themes[$i]->name.'</option>'; 
	}
echo '</select></form>';	

?>
