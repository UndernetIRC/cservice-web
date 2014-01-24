<? require("../../php_includes/cmaster.inc");
$cTheme = get_theme_info();
 ?>
<!-- $Id: credits.php,v 1.2 2002/05/20 23:58:03 nighty Exp $ //-->
<html>
 <head>
  <title>C S E R V I C E -- Credits</title>
 </head>
 <?
	echo "<body bgcolor=#" . $cTheme->main_bgcolor . " text=#" . $cTheme->main_textcolor;
	if ($cTheme->main_bgimage!="") {
		echo " background=\"themes/data/" . $cTheme->sub_dir . "/" . $cTheme->main_bgimage . "\"";
	}
	echo ">";
 ?>
  <font face=arial,helvetica size=+1><b>
  Cservice and Coder-com would like to credit the following people
  with their contributions to making Cmaster possible.</b><br>
  <ul>
   <li>Ahmed Mageed - Perfection isn't a state, it's a process</li>
   <li>Brian - How you find bugs I'll never know, but you find the most entertaining of the lot</li>
   <li>Joyce Cooke - Your advise is always welcome</li>
   <li>The Cmaster - You were a troublesome child, but we hope you'll turn out great</li>
   <li>Cynthia - (im)moral support.</li>
   <li>Granny Dee - For keeping the faith</li>
   <li>Phil Larkum - It's always a pleasure</li>
   <li>Robert White - For his hard work getting coders and admins to see eye to eye</li>
   <li>Gte - For coding above and beyond the call of duty</li>
   <li>Kevin Mitchell - Bring up the coders ranks</li>
   <li>Alex Dawson - Official Styliser and duck allocator</li>
   <li>Andrea Cocito - Official Political Wrangler</li>
   <li>nohican - Making sure we never sleep soundly</li>
   <li>Plexus - For being able to code, when we needed code the most</li>
   <li>ripper_ - For the complete C++ experience, it's not over until the codes elegant</li>
   <li>Robin Thellend - Without whom, this wouldn't be possible</li>
   <li>Stevo` - Bug hunter</li>
   <li>yojauta - i18n support in the bot</li>
  </ul>
</font>
</body></html>
                                           
