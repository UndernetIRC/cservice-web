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

    if ($mode=="write" && $crc == md5( $SECURE_ID . CRC_SALT_0011 )) {
        $expire=time()+get_custom_session($user_id);
        $totp_key=$dauser->totp_key;
        $TimeStamp = Google2FA::get_timestamp();
        $secretkey = Google2FA::base32_decode($totp_key);
        $key_crc=md5( $totp_key . CRC_SALT_0011 );
        $token=filter_var($_POST['pin'], FILTER_SANITIZE_NUMBER_INT);

        if (!ip_check_totp($user_name, 0)) {
            $flash->message("Too many failed two-step verification code attempts. Please try again in 24 hours.", "error");
            header("Location: ../users.php");
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
            log_user($user_id,13);
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
            header("Location: ../users.php");
        } else {
            ip_check_totp($user_name,1);
            html_header();
            $flash->message("Invalid code, please try again!", "error");
            echo $flash->show();
            include("_twostep_form.php");
        }
    } else {
        html_header();
        $tmp_sql = pg_safe_exec("SELECT * FROM old_totp WHERE id='" . $dauser->id . "'");

        if (pg_num_rows($tmp_sql) == 1) {
            $old_totp = pg_fetch_object($tmp_sql,0);
            $totp_key=$old_totp->totp_key;
        } else {
            $totp_key=Google2FA::generate_secret_key();
        }

        if ($ID!="" && strlen($ID)<=128) {
            $id_parts=explode('.', $ID);
            $test_hash=substr(md5(CRC_SALT_0015 . $id_parts[1]), 0, 5);
            std_connect();
            $dares = pg_safe_exec("SELECT * FROM users WHERE totp_key='" . $ID . "'");
            $dauser = pg_fetch_object($dares,0);

            if ($test_hash != $id_parts[2]) {
                echo '<h3>Invalid link. Please make sure you copied the link corectly !</h3>';
            } elseif ((time()-$id_parts[1]) > TOTP_CONFIRM_INT) {
                echo '<h3>Link expired. Please restart the activation proccess to generate a new link.</h3>';
            } elseif($user_id != $dauser->id) {
                echo '<h3>Link is invalid. Does not belong to the logged in username.</h3>';
            } else {
                $query = "UPDATE users SET totp_key='".$totp_key."' WHERE id=" . ($user_id+0);
                $_SESSION['oath_uri'] = "otpauth://totp/Undernet:{$user_name}?secret={$totp_key}&issuer=Undernet&digits=6";
                pg_safe_exec($query);
                echo "<h3>Enable two-step verification</h3>";
                include("_twostep_form.php");
            }
        }
    }
}
echo "</body></html>\n";
?>
