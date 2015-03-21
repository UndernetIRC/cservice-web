<?php

if (isset($_SESSION['oath_uri'])) { ?>
<form class="totp" name="act_totp" method="post" action="confirm.php">
  <input type="hidden" name="SECURE_ID" value="<?= $SECURE_ID ?>">
  <input type="hidden" name="crc" value="<?= md5( $SECURE_ID . CRC_SALT_0011 ) ?>">
  <input type="hidden" name="key" value="<?= md5( $totp_key . CRC_SALT_0011 ) ?>">
  <input type="hidden" name="mode" value="write">
  <p class="description">
    An authenticator app lets your generate security codes using your mobile device. We support any of <a href="javascript:void(null);" class="tsdialog" onclick="showTwoStepAppsDialog();">these apps</a>.
  </p>
  <p>To configure your authenticator app:</p>
  <ul>
    <li>Add a new time-based token.</li>
    <li>
      <span id="qr-hide-elem">
        Use your authenticator app to scan the barcode below, or <a href="javascript:void(null);" onclick="toggleQr();">enter your secret key manually</a>.
      </span>
      <span id="qr-show-elem">
        Enter the secret key below, or <a href="#" onclick="toggleQr();">scan a barcode using your authenticator app</a>.
      </span>
   </li>
 </ul>
 <div id="qr-div"><img src="qrgen.php"></div>
 <div id="key-div"><span><?= implode(" ", str_split(strtolower($totp_key), 4)) ?></span></div>
 <p>Enter the security code generated by your mobile authenticator app to make sure it's configured correctly.</p>
 <p>
   <label for="pin">6-digit code</label><br />
   <input type="text" name="pin" id="pin" placeholder="">
   <input type="submit" value=" Submit ">
 </p>
</form>
<?php } else { die; } ?>