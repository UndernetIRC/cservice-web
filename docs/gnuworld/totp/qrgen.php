<?php
require_once  "qrgen/phpqrcode/qrlib.php";
session_start();

function base32encode($input) {
        $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
    $output = '';
    $position = 0;
    $storedData = 0;
    $storedBitCount = 0;
    $index = 0;

    while ($index < strlen($input)) {
      $storedData <<= 8;
      $storedData += ord($input[$index]);
      $storedBitCount += 8;
      $index += 1;

      //take as much data as possible out of storedData
      while ($storedBitCount >= 5) {
        $storedBitCount -= 5;
        $output .= $alphabet[$storedData >> $storedBitCount];
        $storedData &= ((1 << $storedBitCount) - 1);
      }
    } //while

    //deal with leftover data
    if ($storedBitCount > 0) {
      $storedData <<= (5-$storedBitCount);
      $output .= $alphabet[$storedData];
    }

    return $output;
  } //base32encode

if(isset($_SESSION['oath_uri'])) {
    QRcode::png($_SESSION['oath_uri'], false, QR_ECLEVEL_L, 4);
} else { 
?>
<html>
<head>
  <link rel="stylesheet" type="text/css" href="./css/smoothness/jquery-ui-1.8.20.custom.css" media="screen" />
  <script type='text/javascript' src='./js/jquery-1.7.2.min.js'></script>
  <script type='text/javascript' src='./js/jquery-ui-1.8.20.custom.min.js'></script> 
  <script type='text/javascript' src='./js/forms.js'></script> 
 <script>
$(function(){
$("form").form();
	});
</script>
</head>
<body>
<form method="GET">
 <fieldset>
        <table border="0">
	<tr><td>Token Name: </td><td><input name="name" size="45"></td></tr>
	<tr><td>Secret: </td><td><input name="code" size="45"></td></tr>
	<tr><td colspan="2"><input type ="SUBMIT" value="GENERATE"></td></tr>
	</table>
 </fieldset>
</form>
</body>
</html>
<?php
}
?>
