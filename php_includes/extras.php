<?php

include ("ipv6class.php");
$rbl[0]['name'] = 'Dronebl';
$rbl[0]['host'] = 'dnsbl.dronebl.org';
$rbl[0]['3'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 3.';
$rbl[0]['5'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 5.';
$rbl[0]['6'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 6.';
$rbl[0]['7'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 7.';
$rbl[0]['8'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 8.';
$rbl[0]['9'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 9 .';
$rbl[0]['10'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 10.';
$rbl[0]['13'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 13.';
$rbl[0]['15'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 15.';
$rbl[0]['17'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 17.';
$rbl[0]['def_msg'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet';
$rbl[0]['enabled'] = 'yes';


$rbl[1]['name'] = 'efnetrbl';
$rbl[1]['host'] = 'rbl.efnetrbl.org';
$rbl[1]['1'] = 'Check www.efnetrbl.org/remove.php for removal.';
$rbl[1]['2'] = 'Check www.efnetrbl.org/remove.php for removal.';
$rbl[1]['3'] = 'Check www.efnetrbl.org/remove.php for removal.';
$rbl[1]['5'] = 'Check www.efnetrbl.org/remove.php for removal.';
$rbl[2]['def_msg'] = 'Check www.efnetrbl.org/remove.php';
$rbl[1]['enabled'] = 'yes';

$rbl[2]['name'] = 'swiftbl';
$rbl[2]['host'] = 'dnsbl.swiftbl.org';
$rbl[2]['1'] = 'Check www.swiftbl.org/lookup for removal.';
$rbl[2]['2'] = 'Check www.swiftbl.org/lookup for removal.';
$rbl[2]['3'] = 'Check www.swiftbl.org/lookup for removal.';
$rbl[2]['5'] = 'Check www.swiftbl.org/lookup for removal.';
$rbl[2]['def_msg'] = 'Check www.swiftbl.org/lookup.';
$rbl[2]['enabled'] = 'yes';

$rbl[3]['name'] = 'Unet RBL';
$rbl[3]['host'] = 'rbl.undernet.org';
$rbl[3]['3'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 3.';
$rbl[3]['5'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 5.';
$rbl[3]['6'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 6.';
$rbl[3]['7'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 7.';
$rbl[3]['8'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 8.';
$rbl[3]['9'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 9 .';
$rbl[3]['10'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 10.';
$rbl[3]['13'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 13.';
$rbl[3]['15'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 15.';
$rbl[3]['17'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet for removal 17.';
$rbl[3]['def_msg'] = 'Check www.dronebl.org/lookup_branded?network=UnderNet';
$rbl[3]['enabled'] = 'yes';

class IP4Filter {

    private static $_IP_TYPE_SINGLE = 'single';
    private static $_IP_TYPE_WILDCARD = 'wildcard';
    private static $_IP_TYPE_MASK = 'mask';
    private static $_IP_TYPE_CIDR = 'CIDR';
    private static $_IP_TYPE_SECTION = 'section';
    private $_allowed_ips = array();

    public function __construct($allowed_ips) {
        $this->_allowed_ips = $allowed_ips;
    }

    public function check($ip, $allowed_ips = null) {
        $allowed_ips = $allowed_ips ? $allowed_ips : $this->_allowed_ips;

        foreach ($allowed_ips as $allowed_ip) {
            $type = $this->_judge_ip_type($allowed_ip);
            $sub_rst = call_user_func(array($this, '_sub_checker_' . $type), $allowed_ip, $ip);

            if ($sub_rst) {
                return true;
            }
        }

        return false;
    }

    private function _judge_ip_type($ip) {
        if (strpos($ip, '*')) {
            return self :: $_IP_TYPE_WILDCARD;
        }

        if (strpos($ip, '/')) {
            $tmp = explode('/', $ip);
            if (strpos($tmp[1], '.')) {
                return self :: $_IP_TYPE_MASK;
            } else {
                return self :: $_IP_TYPE_CIDR;
            }
        }

        if (strpos($ip, '-')) {
            return self :: $_IP_TYPE_SECTION;
        }

        if (ip2long($ip)) {
            return self :: $_IP_TYPE_SINGLE;
        }

        return false;
    }

    private function _sub_checker_single($allowed_ip, $ip) {
        return (ip2long($allowed_ip) == ip2long($ip));
    }

    private function _sub_checker_wildcard($allowed_ip, $ip) {
        $allowed_ip_arr = explode('.', $allowed_ip);
        $ip_arr = explode('.', $ip);
        for ($i = 0; $i < count($allowed_ip_arr); $i++) {
            if ($allowed_ip_arr[$i] == '*') {
                return true;
            } else {
                if (false == ($allowed_ip_arr[$i] == $ip_arr[$i])) {
                    return false;
                }
            }
        }
    }

    private function _sub_checker_mask($allowed_ip, $ip) {
        list($allowed_ip_ip, $allowed_ip_mask) = explode('/', $allowed_ip);
        $begin = (ip2long($allowed_ip_ip) & ip2long($allowed_ip_mask)) + 1;
        $end = (ip2long($allowed_ip_ip) | (~ ip2long($allowed_ip_mask))) + 1;
        $ip = ip2long($ip);
        return ($ip >= $begin && $ip <= $end);
    }

    private function _sub_checker_section($allowed_ip, $ip) {
        list($begin, $end) = explode('-', $allowed_ip);
        $begin = ip2long($begin);
        $end = ip2long($end);
        $ip = ip2long($ip);
        return ($ip >= $begin && $ip <= $end);
    }

    private function _sub_checker_CIDR($CIDR, $IP) {
        list ($net, $mask) = explode('/', $CIDR);
        return ( ip2long($IP) & ~((1 << (32 - $mask)) - 1) ) == ip2long($net);
    }

}

function ip_check_white($ip) {
    $sql = "SELECT * from whitelist where IP = '" . $ip . "' ";
    if ($res = pg_safe_exec($sql)) {
        $rows = pg_num_rows($res);
        if ($rows == 1)
            return true; // whitelisted
        else
            return false; // not whitelisted
    } else
        return false;
}

function has_ipr($user_id) {
    $res = @pg_safe_exec("SELECT * FROM ip_restrict WHERE user_id=" . (int) $user_id);

    if ($res && pg_numrows($res) > 0) {
        return true;
    }

    return false;
}

function ip_check_glined($ip){
    $ip="2001:470:f51a:76e:f5f5:1a62::1";
    if (filter_var($ip, FILTER_VALIDATE_IP,FILTER_FLAG_IPV4))
    {
        return ip4_check_glined($ip);

    }
    if (filter_var($ip, FILTER_VALIDATE_IP,FILTER_FLAG_IPV6))
    {
        return ip6_check_glined($ip);

    }
}

function ip4_check_glined($ip) {
    if (!ip_check_white($ip)) {
        $ip_t = explode('.', $ip);
        $a = $ip_t[0] . '.' . $ip_t[1] . '.';
        $glines = 0;
        $sql = "SELECT host from glines where host like '*@" . $a . "%' or host like '~*@" . $a . "%' order by ID ASC"; // getting all glines that match first two bytes in IP and no ident
        if ($res = pg_safe_exec($sql)) {
            $rows = pg_num_rows($res);
            if ($rows > 0) {
                $hosts = pg_fetch_all($res);

                for ($i = 0; $i < count($hosts); $i++) {
                    $t_mask = str_replace('*@', '', $hosts[$i]['host']);  //cleaning up gline host
                    $t_mask = str_replace('~', '', $t_mask);
                    $masks[] = $t_mask;

                    $filter = new IP4Filter(
                            array($t_mask));

                    if ($filter->check($ip))
                        $glines++;
                }
            }
        }
        if ($glines == 0)
            return false; // not glined
        else
            return true; // glined
    } else
        return false;
}

function ip6_check_glined($ip) {
    $ip="2001:470:f51a:76e:f5f5:1a62::1";
    $glines = 0;
    if (!ip_check_white($ip)) {
        for ($i = 7; $i > 0; $i--) {
            $ip6 = explode(":", $ip);
            $like="";
            for ($j = 0; $j <= $i; $j++)
                $like.=$ip6[$j] . ":";
            $like = substr($like, 0, -1);
            $sel = "select * from glines where host like '*@" . $like . "%' or host like '~*@" . $like . "%' order by ID ASC";
            //echo $sel;
            if ($res = pg_safe_exec($sel)) {
                $rows = pg_num_rows($res);
                if ($rows > 0) {
                    $hosts = pg_fetch_all($res);

                    for ($k = 0; $k < count($hosts); $k++) {
                        $net=str_replace('*@', '', $hosts[$k]['host']);
                        $net=str_replace('~', '', $net);
                        $net = new IPV6Net( $net );
                        if ($net->contains( $test ) == 1)
                                $glines++;
                    }
                }
            }
        }
    if ($glines == 0)
            return false; // not glined
        else
            return true; // glined
    }
        return false;
    }

    function ip_check_rbl($ip) {
        global $rbl;
        //$ip="2001:470:c:10f8::4";
        $msg = 'clean'; // default return if IP not listed in any rbl. if changed, update newuser.php and login.php too
        $isv4 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        if (!ip_check_white($ip)) {
            for ($i = 0; $i < count($rbl); $i++) {
                if ($isv4) {
                    $rev = array_reverse(explode('.', $ip));
                    $lookup = implode('.', $rev) . '.' . $rbl[$i]['host'];
                } else {
                    $lookup = ipv6_to_arpa($ip) . '.' . $rbl[$i]['host'];
                }
                $time_check_rbl[$i] = $lookup;
                $time_check_start[$i] = microtime(true);
                $result = gethostbyname($lookup);
                $time_check_msg[$i] = $result;
                $time_check_end[$i] = microtime(true);
//	echo $result;
                $test = str_replace('127.0.0', '127.0.0', $result, $count);
                if ($count) {
                    $tmp = explode('.', $result);
                    if ($rbl[$i]['' . $tmp[3] . ''])
                        $msg = $rbl[$i]['' . $tmp[3] . ''];
                    else
                        $msg = 'clean';

                    break;
                }
            }
            if ($ip == '176.9.63.176')
                for ($i = 0; $i < count($time_check_rbl); $i++) {
                    $temptime = ($time_check_end[$i] - $time_check_start[$i]) / 60;
                    echo 'Execution time for ' . $time_check_rbl[$i] . ' : ' . number_format($temptime, 3) . ' seconds<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; with response ' . $time_check_msg[$i] . '<br>';
                }
            return $msg;
        } else
            return $msg;
    }

    if (TOTP_ON == 1)
        include("Google2FA.php");  // loading TOTP verification class

    function has_totp($user_id) {
        $res = pg_safe_exec("select users.id,users.flags,users.totp_key from users where users.id=" . (int) $user_id . "");
        if (pg_numrows($res)) {
            $user = pg_fetch_object($res, 0);
            if (TOTP_ALLOW_ALL == 1) {
                if (($user->flags & TOTP_USR_FLAG) == TOTP_USR_FLAG)
                    return $user->totp_key;
                else {
                    return false;
                }
            } else {

                $allowed = 0;
                if (isoper($user_id)) {
                    $allowed = 1;
                }
                $res = pg_safe_exec("select users.id,users.flags,levels.access from users,levels where users.id=" . (int) $user_id . " and users.id=levels.user_id and levels.channel_id=1 and levels.access>0");
                if (pg_numrows($res) == 1) {
                    $allowed = 1;
                }
                if ($allowed == 1) {
                    if (($user->flags & TOTP_USR_FLAG) == TOTP_USR_FLAG)
                        return $user->totp_key;
                    else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
    }

    function totp_ip_add($ip, $user_name) {
        global $ENABLE_COOKIE_TABLE;
        $ip_check = pg_exec("SELECT * FROM ips WHERE ipnum='" . $ip . "' AND lower(user_name)='" . strtolower($user_name) . "'");
        if (pg_numrows($ip_check) == 0) {
            $ENABLE_COOKIE_TABLE = 1;
            pg_exec("INSERT INTO ips (ipnum,user_name,expiration,hit_counts,set_on) VALUES ('" . cl_ip() . "','" . $user_name . "',0,1,now()::abstime::int4)");
            $ENABLE_COOKIE_TABLE = 0;
        } else {
            $ENABLE_COOKIE_TABLE = 1;
            $row = pg_fetch_object($ip_check);
            $newcount = $row->hit_counts + 1;
            pg_exec("UPDATE ips SET expiration='0',set_on=now()::abstime::int4,hit_counts='" . (int) $newcount . "' WHERE ipnum='" . cl_ip() . "' AND lower(user_name)='" . strtolower($user_name) . "'");
            $ENABLE_COOKIE_TABLE = 0;
        }
    }

    function check_username_similarity($username) {

        if (USRNREG_WARN_ENABLE == 1) {
            $mtime = microtime();
            $mtime = explode(" ", $mtime);
            $mtime = $mtime[1] + $mtime[0];
            $starttime = $mtime;


            $channels = explode(',', USRNREG_CHANS);
            $extrausers = explode(',', USRNREG_EUSERS);
            for ($i = 0; $i < count($channels); $i++) {
                $query = "select id from channels where id=" . $channels[$i] . "";
                $res = pg_safe_exec($query);
                if (pg_numrows($res) != 0) {
                    $chan_id = pg_fetch_object($res, 0);
                    $query2 = "select user_id from levels where channel_id=" . $chan_id->id . "";
                    $res2 = pg_safe_exec($query2);
                    if (pg_numrows($res2) != 0) {
                        for ($j = 0; $j < pg_numrows($res2); $j++) {
                            $user_id = pg_fetch_object($res2, $j);
                            $query3 = "select user_name from users where id=" . $user_id->user_id . "";
                            $res3 = pg_safe_exec($query3);
                            if (pg_numrows($res3) != 0) {
                                $user_name = pg_fetch_object($res3, 0);
                                $users_list[] = $user_name->user_name;
                            }
                        }
                    }
                }
            }
            for ($i = 0; $i < count($extrausers); $i++) {
                $users_list[] = $extrausers[$i];
            }
            $query = "select * from users where flags & 256 = 256 order by ID asc";
            $res = pg_safe_exec($query);
            if (pg_numrows($res) != 0) {
                for ($i = 0; $i < pg_numrows($res); $i++) {
                    $user = pg_fetch_object($res, $i);
                    $users_list[] = $user->user_name;
                }
            }
            //$users_list=array_unique($users_list);

            for ($i = 0; $i < count($users_list); $i++) {
                if ($users_list[$i])
                    $final_list[] = strtolower($users_list[$i]);
            }
            echo "<br><br>";
            for ($i = 0; $i < count($final_list); $i++) {
                if ($final_list[$i]) {
                    $dist = levenshtein($final_list[$i], strtolower($username));
//		echo "Distance is ".$dist." input user: ".strtolower($username)." matched against <font color=\"#FF0000\">".$final_list[$i]."</font><br>";
                    similar_text($final_list[$i], strtolower($username), $percent);
//		echo "similarity is ".$percent." <br>";
                    if (($dist < USRNREG_DIST) && ($percent > USRNREG_SIMILAR)) {
			local_seclog("Likeness match for username " . $username . " against: " . $final_list[$i] ."");
                        $error = USRNREG_ERR_MSG;
                    }
                }
            }
            return $error;
        }
    }

    function disable_totp($totp_id) {
        $r = pg_safe_exec("SELECT * FROM users WHERE id='" . (int) $totp_id . "'");
        if ($o = pg_fetch_object($r)) {
            $oldflags = $o->flags;
            $flags = $oldflags;
            $flags = $oldflags & ~TOTP_USR_FLAG;
            pg_safe_exec("UPDATE users SET flags='" . $flags . "',last_updated=now()::abstime::int4 WHERE id='" . (int) $totp_id . "'");
            $oldtotp = $o->totp_key;
            // log_user($totp_id,14,"TOTP disabled for %U. Old TOTP key: ".$oldtotp." (by %U)");
            log_user($totp_id, 14, "TOTP disabled for %U");
        }
    }

    function show_web_warning() {
        global $admin;
        global $user_id;
        $pageURL = gen_server_url() . LIVE_LOCATION;

        if (WARN_EXPIRE_SESSION) {
            if ($admin > 0) {
                $logout_counter = get_custom_session($user_id);
                //$logout_counter=WARN_EXP_SESS_GRACE;
                echo '
<script type="text/javascript">var ServerDate = new Date</script>
<script type="text/javascript" src="' . $pageURL . '/serverdate.php"></script>
<script type="text/javascript" >
var count=' . $logout_counter . ' ;
var counter=setInterval(timer, 1000); //1000 will  run it every 1 second
var temp;
var temp2;
function getCookie(name) { var re = new RegExp(name + "=([^;]+)"); var value = re.exec(document.cookie); return (value != null) ? unescape(value[1]) : null; }
function timer()
{
  count=count-1;
  if (count == ' . WARN_EXP_SESS_GRACE . ')
  {
     window.open(\'' . $pageURL . '/warning.php\', \'WARNING\',\'width=500,height=150\')
  }
  if (count % 5 == 0)
  	{
  	temp = ServerDate.now()/1000;
  	temp2 = getCookie(\'sauth\') - temp;
  	count = Math.round(temp2);
  	//document.getElementById("timer").innerHTML=count;
  	}
  if (count <0)
  {
    clearInterval(counter);
   window.top.location.href=\'' . $pageURL . '\';
         return;
  }
function secondstotime(secs)
{
    var t = new Date(1970,0,1);
    t.setSeconds(secs);
    var s = t.toTimeString().substr(0,8);
    if(secs > 86399)
    	s = Math.floor((t - Date.parse("1/1/70")) / 3600000) + s.substr(2);
    return s;
}
document.getElementById("timer").innerHTML=secondstotime(count) + " secs" ;

}
function popUpClosed() {
    window.top.location.href=\'' . $pageURL . '\';
}
</script>';
                if ($admin > 0)
                    echo '<b>Logging off in <span id="timer"></span></b><br>';
            }
        }
        return;
    }

    function set_custom_session($userid, $time) {
        $q = "select * from custom_cookies where user_id=" . $userid . "";
        $res = pg_exec($q);
        if (pg_numrows($res) == 1) {
            $u = "update custom_cookies set session_time=" . $time . " where user_id=" . $userid;
        } else {
            $u = "insert into custom_cookies values (" . $userid . ", " . $time . ")";
        }
        $res2 = pg_exec($u);
        if ($res2)
            return;
        else {
            echo "error";
            return;
        }
    }

    function get_custom_session($userid) {
        global $ENABLE_COOKIE_TABLE;
        $old_set = $ENABLE_COOKIE_TABLE;
        $ENABLE_COOKIE_TABLE = 1;
        $q = "select * from custom_cookies where user_id=" . $userid;
        $res = pg_safe_exec($q);
        if (pg_numrows($res) > 0) {
            $o = pg_fetch_object($res);
            $time = $o->session_time;
        } else
            $time = COOKIE_EXPIRE;
        $ENABLE_COOKIE_TABLE = $old_set;
        return $time;
    }

    function is_admin($user_id) {
        $q = "select * from levels where channel_id=1 and user_id=" . $user_id;
        $res = pg_safe_exec($q);
        if (pg_numrows($res) == 1)
            return true;
        else
            return false;
    }

    function formatSeconds($secs) {
        $secs = (int) $secs;
        if ($secs === 0) {
            return '0 secs';
        }
        // variables for holding values
        $mins = 0;
        $hours = 0;
        $days = 0;
        $weeks = 0;
        // calculations
        if ($secs >= 60) {
            $mins = (int) ($secs / 60);
            $secs = $secs % 60;
        }
        if ($mins >= 60) {
            $hours = (int) ($mins / 60);
            $mins = $mins % 60;
        }
        if ($hours >= 24) {
            $days = (int) ($hours / 24);
            $hours = $hours % 60;
        }
        if ($days >= 7) {
            $weeks = (int) ($days / 7);
            $days = $days % 7;
        }
        // format result
        $result = '';
        if ($weeks) {
            $result .= "{$weeks} week(s) ";
        }
        if ($days) {
            $result .= "{$days} day(s) ";
        }
        if ($hours) {
            $result .= "{$hours} hour(s) ";
        }
        if ($mins) {
            $result .= "{$mins} min(s) ";
        }
        if ($secs) {
            $result .= "{$secs} sec(s) ";
        }
        $result = rtrim($result);
        return $result;
    }

    function custom_mail($to, $subj, $msg, $header) {
        $res = true;
        if (MAIL_TYPE == 'smtp') {
            include ("smtp_client.php");

            if (DEBUG_MAIL)
                $to = "rufus@doormouse.net";
            $SMTPMail = new SMTPClient(SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, FROM_NEWUSER, $to, $subj, $msg);
            $SMTPChat = $SMTPMail->SendMail();
            if (DEBUG_MAIL) {
                echo "<h1>Talking with the SMTP Server</h1>";
                echo "<p>The server response:</p>";
                echo $SMTPChat["hello"] . "<br />";
                echo $SMTPChat["res"] . "<br />";
                echo $SMTPChat["user"] . "<br />";
                echo $SMTPChat["pass"] . "<br />";
                echo $SMTPChat["From"] . "<br />";
                echo $SMTPChat["To"] . "<br />";
                echo $SMTPChat["data"] . "<br />";
                echo $SMTPChat["send"] . "<br />";
            }
            if ($SMTPChat["hello"] == "C")
                $res = false;
        }
        if (MAIL_TYPE == 'sendmail') {
            $res = mail($to, $subj, $msg, $header);
        }
        return $res;
    }

    function seconds2human($ss) {
        $res = array();
        $s = $ss%60;
        $m = floor(($ss%3600)/60);
        $h = floor(($ss%86400)/3600);
        $d = floor(($ss%2592000)/86400);
        $M = floor($ss/2592000);

        if ($M > 0 ) { array_push($res, "$M month(s)"); }
        if ($d > 0 || sizeof($res) > 0) { array_push($res, "$d day(s)"); }
        if ($h > 0 || sizeof($res) > 0) { array_push($res, "$h hour(s)"); }
        if ($m > 0 || sizeof($res) > 0) { array_push($res, "$m minute(s)"); }
        if ($s > 0 || sizeof($res) > 0) { array_push($res, "$s second(s)"); }

        return implode(", ", $res);
    }

    function seconds2human2($time) {
        $names = array("day(s)", "month(s)", "year(s)");
        $values = array(24 * 3600, 30 * 24 * 3600, 365 * 24 * 3600);

        for ($i = count($values) - 1; $i > 0 && $time < $values[$i]; $i--)
            ;
        if ($i == 0) {
            $string = intval($time / $values[$i]) . " " . $names[$i];
        } else {
            $t1 = intval($time / $values[$i]);
            $t2 = intval(($time - $t1 * $values[$i]) / $values[$i - 1]);
            if ($t1 > 1)
                $string = "$t1 " . $names[$i];
            else
                $string = "$t1 " . $names[$i] . ", $t2 " . $names[$i - 1];
        }
        if ($string === "0 days")
            $string = "1 day ";
        return $string;
    }

    function sorted_max_logins(): array {
        $max_logins = ALLOW_MAX_LOGINS;

        usort($max_logins, function($a, $b) {
            return $b['account_age'] <=> $a['account_age'];
        });

        return $max_logins;
    }

    function user_max_logins(int $user_signup_timestamp): array {
        $account_age = time() - $user_signup_timestamp;

        foreach (sorted_max_logins() as $item) {
            if ($account_age >= $item['account_age']) {
                return $item;
            }
        }

        return array("max_logins" => DEFAULT_MAX_LOGINS, "account_age" => 0);
    }

    function time_to_next_max_logins(int $user_signup_timestamp): string {
        $account_age = time() - $user_signup_timestamp;
        $msg = '';

        foreach (sorted_max_logins() as $item) {
            if ($account_age <= $item['account_age']) {
                $msg = "You need to wait " . seconds2human($item['account_age'] - $account_age) . " before you can set MAXLOGINS " . $item['max_logins'];
                break;
            }
        }

        return $msg;
    }

    function time_next_channel(int $user_signup_timestamp): array {
        $allow_multi_chans = ALLOW_MULTI_CHANS;
        $account_age = time() - $user_signup_timestamp;

        asort($allow_multi_chans);

        foreach ($allow_multi_chans as $key => $val) {
            if ($account_age < $val) {
                return ['max_channels' => $key, 'seconds_next_channel' => $val - $account_age];
            }
        }
        return [];
    }

    function secs_to_h($secs) {
        $units = array(
            "year" => 365 * 24 * 3600,
            "month" => 30 * 24 * 3600,
            //"week"   => 7*24*3600,
            "day" => 24 * 3600,
            "hour" => 3600,
            "minute" => 60,
            "second" => 1,
        );

        // specifically handle zero
        if ($secs == 0)
            return "0 seconds";

        $s = "";

        foreach ($units as $name => $divisor) {
            if ($quot = intval($secs / $divisor)) {
                $s .= "$quot $name";
                $s .= (abs($quot) > 1 ? "s" : "") . ", ";
                $secs -= $quot * $divisor;
            }
        }

        return substr($s, 0, -2);
    }

    function ipv6_to_arpa($ip) {
        $addr = inet_pton($ip);
        $unpack = unpack('H*hex', $addr);
        $hex = $unpack['hex'];
        $arpa = implode('.', array_reverse(str_split($hex)));
        return $arpa;
    }

?>
