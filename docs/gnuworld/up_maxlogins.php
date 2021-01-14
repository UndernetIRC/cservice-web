<?php
error_reporting(E_ALL);
$default_gopage = "login.php";
require("../../php_includes/cmaster.inc");
std_init();

$cTheme = get_theme_info();
std_theme_styles(1);
std_theme_body();

function not_valid_va($user_id)
{
	global $temp_msg;
	$r = pg_safe_exec("SELECT * FROM users WHERE id='" . (int)$user_id . "'");
	if ($o = pg_fetch_object($r)) {

		if ((!(preg_match("/^[A-Za-z0-9!\ \/\\.+_-]+$/", $_POST["va"]))) && ($_POST["step"] == "yes")) {
			echo "Verification answer contains invalid chars.";
			ip_check($o->user_name, 1);
			return true;
		} else {
			$has_totp = false;
			$good = false;
			$totp_key = $o->totp_key;
			if ((TOTP_ALLOW_ALL == 1) && (TOTP_ON == 1) && ($totp_key != '')) {
				$temp_msg = " or TOTP token";
				$has_totp = true;
			}
			if ((strtolower($_POST["va"]) === strtolower($o->verificationdata)) && ($_POST["step"] == "yes")) {
				$good = true;
			}
			if (($has_totp) && (!$good)) {
				$result = Google2FA::verify_key($totp_key, $token = filter_var($_POST["va"], FILTER_SANITIZE_NUMBER_INT));
				if ($result)
					$good = true;
			}
			if ($good) {
				$temp_maxlogins = user_max_logins($o->signup_ts);
				$user_max_login = filter_var($_POST["maxlogins"], FILTER_VALIDATE_INT, array("options" => array("min_range" => 1, "max_range" => $temp_maxlogins['max_logins'])));

				if (!$user_max_login) {
					echo "Max login value is invalid. Valid range is 1 - " . $temp_maxlogins['max_logins'] . "<br />";
					echo '<a href ="users.php">Click here</a> to go back to your info page.';
					die;
				} else {
				  $sql = sprintf("UPDATE users SET maxlogins=%d, last_updated=now()::abstime::int4, last_updated_by='Web Interface (%s (%d))' WHERE id=%d", $user_max_login, $o->user_name, $o->id, $o->id);
				  pg_safe_exec($sql);

					echo "You've succesfully set maxlogins to <strong>" . $user_max_login . "</strong> !<br>";
					echo '<a href ="users.php?id=' . $o->id . '">Click here</a> to go back to your info page.';
					log_user($o->id, 15, "Changed from " . $o->maxlogins . " to " . $user_max_login . ".");
					die;
				}
			} else {
				echo "Wrong verification answer" . $temp_msg . "!";
				ip_check($o->user_name, 1);
				return true;
			}
		}
		return true;
	} else {
		echo "Invalid username ID.";
		die;
	}
}


if (ALLOW_SELF_MAXLOGINS == 1) {
	if ($user_id > 0) {
		if ($_POST["step"] === "yes") {
			$r = pg_safe_exec("SELECT * FROM users WHERE id='" . (int)$user_id . "'");
			if ($o = pg_fetch_object($r)) {
				if (ip_check($o->user_name, 0)) {
					if ((not_valid_va($user_id))) {
						echo '<form method=POST><table border=0><tr><td>Enter your verification answer' . $temp_msg . ': </td><td><input type="text" name="va" size="10">  <input type="submit" value="Submit"><input type=hidden value="yes" name="step"><input type=hidden value="' . $_POST["maxlogins"] . '" name="maxlogins"/></td></tr></form>';
					}
				} else
					echo 'Too many failed attempts';
			}
		} else {
			echo '<form method=POST><table border=0><tr><td>Enter your verification answer' . $temp_msg . ': </td><td><input type="text" name="va" size="10">  <input type="submit" value="Submit"><input type=hidden value="yes" name="step"><input type=hidden value="' . $_POST["maxlogins"] . '" name="maxlogins"/></td></tr></form>';
		}
	} else
		echo "Not logged in!";
} else
	echo "You shouldn't be here, feature is disabled!";
