<?php
require("../../php_includes/cmaster.inc");
std_connect();
$cTheme = get_theme_info();
			std_theme_styles(1);
			std_theme_body();
?>
<script type="text/javascript" >
var count=<?php echo WARN_EXP_SESS_GRACE;?> ;
var counter=setInterval(timer, 1000); //1000 will  run it every 1 second
function timer()
{  
 count=count-1;
if (count <0)
  {
    clearInterval(counter);
    window.opener.popUpClosed();
    self.close();
         return;
  }

document.getElementById("timer").innerHTML=count + " seconds"; 
}
</script>
<h3>CService</h3>
<h2>Your websession will expire in <span id="timer"></span>.</h2>
<input type="submit" value="Exentend my session" onclick="window.opener.popUpClosed();  self.close();"/>
<p>(will bring you to main own username info page)</p>
