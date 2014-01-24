<?php
require_once  "phpqrcode/qrlib.php";

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
if(isset($_GET['code']) && isset($_GET['name'])) {
        //QRcode::png("otpauth://totp/" . $_GET['name'] . "?secret=" .  base32encode($_GET['code']) . "&digits=6");
        QRcode::png("otpauth://totp/" . $_GET['name'] . "?secret=" .  $_GET['code'] . "&digits=6");
} else {
?>
<body>
<html>
<form method="GET">
	Token Name:<input name="name"><br>
	Secret:<input name="code" size="45"><br>
	<input type ="SUBMIT" value="GENERATE">
</form>
</body>
</html>
<?php
}
?>
