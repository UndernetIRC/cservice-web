<?php
error_reporting(E_ALL);
include("../../../php_includes/cmaster.inc");
require_once("../../../php_includes/FlashMessage.php");

function html_header($show_flash=false) {
    header("Pragma: no-cache\n\n");
    echo "<html>\n<head>\n";
    echo "<title>CService enable two-step verification</title>\n";
    std_theme_styles();
    ?>
    <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <link rel="stylesheet" type="text/css" href="./css/smoothness/jquery-ui-1.8.20.custom.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="./css/dialog-custom.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="../css/flash.css" media="screen" />
    <script type="text/javascript" src="./js/jquery-1.7.2.min.js"~></script>
    <script type="text/javascript" src="./js/jquery-ui-1.8.20.custom.min.js"></script>
    <script type="text/javascript" src="./js/jquery.infieldlabel.min.js"></script>
    <script type="text/javascript" src="./js/custom.js"></script>
    </head>
    <?php
    std_theme_body("","document.forms[0].pin.focus();");
}

std_connect();
$user_id = std_security_chk($auth);
$cTheme = get_theme_info();

if ($user_id==0 || $auth=="") {
    html_header();
    echo "<h3>You must be logged in to view that page!</h3>";
} else {
    if (!session_id()) {
        session_start();
    }
    $flash = new FlashMessage();
    $admin = std_admin();
    if (isset($authtok)) { unset($authtok); }
    if (isset($authcsc)) { unset($authcsc); }

    $authtok = explode(":",$auth);
    $authcsc = $authtok[3];
    $dares = pg_safe_exec("SELECT * FROM users WHERE id='" . $user_id . "'");
    $dauser = pg_fetch_object($dares,0);
    $authtok = explode(":",$auth);
    $authcsc = $authtok[3];
    $user_name = $authtok[0];
    $SECURE_ID=md5( $user_id . CRC_SALT_0019 . $authcsc );
    $user_redirect_url = "../users.php?id={$user_id}";

    if ($mode=="write" && $crc == md5( $SECURE_ID . CRC_SALT_0011 )) {
        $expire=time()+get_custom_session($user_id);
        $totp_key=$dauser->totp_key;
        $TimeStamp = Google2FA::get_timestamp();
        $secretkey = Google2FA::base32_decode($totp_key);
        $key_crc=md5( $totp_key . CRC_SALT_0011 );
        $token=filter_var($_POST['pin'], FILTER_SANITIZE_NUMBER_INT);

        if (!ip_check_totp($user_name, 0)) {
            $flash->message("Too many failed two-step verification code attempts. Please try again in 24 hours.", "error");
            header("Location: {$user_redirect_url}");
        } elseif ($key != $key_crc) {
            html_header();
            echo "<h1>Error</h1>\n";
            echo "<h3>Highjack attempt!</h3>\n";
        } elseif (preg_match(NON_BOGUS_TOTP,trim($token)) && Google2FA::verify_key($totp_key, $token)) {
            unset($_SESSION['oath_uri']);
            $oldflags = $dauser->flags;
            $flags = $oldflags;
            $flags = $oldflags|TOTP_USR_FLAG;
            //echo $oldflags.'->'.$flags;
            pg_safe_exec("UPDATE users SET flags='".$flags."',last_updated=now()::abstime::int4 WHERE id='".($user_id+0)."'");
            log_user($user_id, 13, "TOTP enabled");
            $ENABLE_COOKIE_TABLE = 0;
            $temp_totp_hash=gen_totp_cookie($totp_key);
            if (COOKIE_DOMAIN!="") {
                SetCookie("totp",$temp_totp_hash,$expire,"/",COOKIE_DOMAIN);
            } else {
                SetCookie("totp",$temp_totp_hash,$expire,"/");
            }
            $fmm="UPDATE webcookies SET totp_cookie='".$temp_totp_hash."' WHERE user_id='" . (int)$user_id . "'";
            pg_exec($fmm);

            $flash->message("Two-step verification successfully enabled");
            header("Location: {$user_redirect_url}");
        } else {
            ip_check_totp($user_name,1);
            html_header();
            $flash->message("Invalid code, please try again!", "error");
            echo $flash->show();
            include("_twostep_form.php");
        }
    } else {
        $totp_key=Google2FA::generate_secret_key();

        if ($ID!="" && strlen($ID)<=128) {
            $id_parts=explode('.', $ID);
            $test_hash=substr(md5(CRC_SALT_0015 . $id_parts[1]), 0, 5);
            std_connect();
            $dares = pg_safe_exec("SELECT * FROM users WHERE totp_key='" . $ID . "'");
            $dauser = pg_fetch_object($dares,0);

            if ($test_hash != $id_parts[2]) {
                $flash->message("The activation link is invalid. Please make sure that you copied the link correctly!", "error");
            } elseif ((time()-$id_parts[1]) > TOTP_CONFIRM_INT) {
                $flash->message("The activation link has expired. Please try and enable two-step verification again.", "error");
            } elseif($user_id != $dauser->id) {
                $flash->message("The activation link is invalid.", "error");
            } else {
                html_header();
                $query = "UPDATE users SET totp_key='".$totp_key."' WHERE id=" . ($user_id+0);
                $_SESSION['oath_uri'] = "otpauth://totp/Undernet:{$user_name}?secret={$totp_key}&issuer=Undernet&digits=6";
                pg_safe_exec($query);
                echo "<h3>Enable two-step verification</h3>";
                include("_twostep_form.php");
            }

            if($flash->hasMessage()) {
                header("Location: {$user_redirect_url}");
                die;
            }
        }
    }
}
?>
  <div id="twostep-apps" title="Supported authenticator apps">
    <p>Any app that supports the Time-based One-Time Password (TOTP) should be supported, including the following:</p>
    <p>
      <ul>
        <li><a href="http://support.google.com/accounts/bin/answer.py?hl=en&answer=1066447" target="_blank">Google Authenticator</a> for Android, iPhone and BlackBerry</li>
        <li><a href="https://guide.duosecurity.com/third-party-accounts" target="_blank">Duo Mobile</a> for Android and iPhone</li>
        <li><a href="http://www.windowsphone.com/en-us/store/app/authenticator/021dd79f-0598-e011-986b-78e7d1fa76f8" target="_blank">Authenticator</a> for Windows Phone 7</li>
        <li><a href="https://play.google.com/store/apps/details?id=com.yubico.yubioath&hl=en" target="_blank">Yubico Authenticator</a> for Android (Note: Requires a <a href="https://www.yubico.com/products/yubikey-hardware/yubikey-neo/" target="_blank">YuiKey NEO</a>)</li>
      </ul>
    </p>
  </div>
</body></html>
