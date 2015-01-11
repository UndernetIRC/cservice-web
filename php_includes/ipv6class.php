<?php

class IPv6Net {

    private $net_addr;
    private $net_addr_long; 
    private $net_mask; 
    private $net_mask_long;
    private $net_mask_bits; 
    private $net_broadcast; 
    private $net_broadcast_long; 
    private $ipv4; 
    private $valid;

    public static function isIPv4($addr) {
        return preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $addr);
    }

    private static function inet_ntogmp($addr) {

        $gmp = gmp_init(0);
        for ($bits = 0; $bits < 16; $bits++) {
            $byte = ord($addr[15 - $bits]);
            for ($b = 0; $b < 8; $b++) {
                gmp_setbit($gmp, $bits * 8 + $b, $byte & (1 << $b));
            }
        }



        return $gmp;
    }

    private static function inet_gmptofull($gmp) {
        $str = gmp_strval($gmp, 16);
        for ($i = strlen($str); $i < 32; $i++) {
            $str = '0' . $str;
        }
        $ret = '';
        for ($i = 0; $i < 8; $i++) {
            $ret .= substr($str, $i * 4, 4) . ':';
        }
        return substr($ret, 0, -1);
    }

    private static function inet_gmpton($gmp) {

        $addr = '';
        for ($bits = 0; $bits < 16; $bits++) {
            $byte = 0;
            for ($b = 0; $b < 8; $b++) {
                if (gmp_testbit($gmp, (15 - $bits) * 8 + $b)) {
                    $byte |= 1 << $b;
                }
            }
            $addr .= chr($byte);
        }

        return $addr;
    }

    public static function inet_ptofull($addr) {
        if (self::isIPv4($addr)) {
            $addr = '::' . $addr;
        }
        $net_addr = @inet_pton($addr);
        if ($net_addr == false) {
            throw new \Exception("invalid ip address {$addr}");
        }
        $net_addr_long = self::inet_ntogmp($net_addr);
        return self::inet_gmptofull($net_addr_long);
    }

    public function __construct($addr) {
        $this->valid = false;

        $cx = strpos($addr, '/');
        if ($cx) {
            $mask = trim(substr($addr, $cx + 1));
            $addr = trim(substr($addr, 0, $cx));
        } else {
            $mask = null;
        }
        $this->ipv4 = $this->isIPv4($addr);
        if ($this->ipv4) {
            $addr = '::' . $addr;
            if ($mask != null) {
                if ($this->isIPv4($mask)) {
                    $mask = '::' . $mask;
                }
            }
        }

        if ($mask == null) {
            $mask = 128;
        }
        $this->setIPv6($addr, $mask);
    }

    public function __toString() {
        if (!$this->net_addr)
            return '::0/128';
        return inet_ntop($this->net_addr) . '/' . $this->net_mask_bits;
    }

    public function getNetwork($full = false) {
        if (!$this->valid) {
            return null;
        }
        if (!$full) {
            return inet_ntop($this->net_addr);
        } else {
            return $this->inet_gmptofull($this->net_addr_long);
        }
    }

    public function getNetworkIPv4() {
        if (!$this->valid) {
            return null;
        }
        if (gmp_cmp('4294967295', $this->net_addr_long) > 0) {
            return gmp_strval($this->net_addr_long);
        } else {
            return '4294967295';
        }
    }

    public function getBroadcastIPv4() {
        if (!$this->valid) {
            return null;
        }
        if (gmp_cmp('4294967295', $this->net_broadcast_long) > 0) {
            return gmp_strval($this->net_broadcast_long);
        } else {
            return '4294967295';
        }
    }

    public function getBroadcast($full = false) {
        if (!$this->valid) {
            return null;
        }
        if (!$full) {
            return inet_ntop($this->net_broadcast);
        } else {
            return $this->inet_gmptofull($this->net_broadcast_long);
        }
    }

    public function ipv4() {
        return $this->ipv4;
    }

    public function valid() {
        return $this->valid;
    }

    public function size() {
        if (!$this->valid) {
            return 0;
        }
        return gmp_intval(gmp_add($this->net_broadcast_long, gmp_neg($this->net_addr_long)));
    }

    public function contains($ip) {
        if (!$this->valid)
            return false;
        if ($this->isIPv4($ip)) {
            $ip = '::' . $ip;
        }
        $addr = @inet_pton($ip);
        if ($addr === false)
            return false;
        $gmp = $this->inet_ntogmp($addr);
        return( gmp_cmp($this->net_addr_long, $gmp) <= 0 && gmp_cmp($gmp, $this->net_broadcast_long) <= 0 );
    }

    private function setIPv6($addr, $mask) {
        $this->net_addr = @inet_pton($addr);
        if ($this->net_addr == false) {
            throw new \Exception("invalid ip address {$addr}");
        }
        $this->valid = true;
        $this->net_addr_long = $this->inet_ntogmp($this->net_addr);

        if (preg_match('/^[0-9]+$/', $mask)) {
            $this->net_mask_bits = intval($mask);
            if ($this->ipv4 && $this->net_mask_bits != 0) {
                $this->net_mask_bits += 96;
            }
            $this->net_mask_long = gmp_mul(gmp_sub(gmp_pow(2, $this->net_mask_bits), 1), gmp_pow(2, 128 - $this->net_mask_bits));

            $this->net_mask = $this->inet_gmpton($this->net_mask_long);
        } else {
            $this->net_mask = inet_pton($mask);
            $this->net_mask_long = $this->inet_ntogmp($this->netmask);
            $this->net_mask_bits = gmp_scan0($this->net_mask_long, 0);
        }


        $this->net_addr_long = gmp_and($this->net_addr_long, $this->net_mask_long);
        $this->net_addr = $this->inet_gmpton($this->net_addr_long);
        $this->net_broadcast_long = gmp_or($this->net_addr_long, gmp_sub(gmp_pow(2, 128 - $this->net_mask_bits), 1));
        $this->net_broadcast = $this->inet_gmpton($this->net_broadcast_long);
    }

    public function subNetTo($iNetMaskBits, $iMaxValues = 1024, $iStartAt = 0) {
        $aRet = array();

        $iNetmaskDiff = $iNetMaskBits - $this->net_mask_bits;

        $rStep = gmp_pow(2, 128 - $iNetMaskBits);
        $rCurr = gmp_add($this->net_addr_long, gmp_mul($iStartAt, $rStep));

        for ($i = 0; $i < $iMaxValues; $i++) {
            if (gmp_cmp($rCurr, $this->net_broadcast_long) > 0)
                break;
            $aRet[] = inet_ntop($this->inet_gmpton($rCurr)) . "/$iNetMaskBits";
            $rCurr = gmp_add($rCurr, $rStep);
        }
        return $aRet;
    }

}

?>
