<?php
if (!defined('__ACCESS_PLUGIN_ROOT__')) {
    throw new Exception('Bootstrap file not found');
}

class Access_Ip
{
    /**
     * Check if a string is a valid IPv4 address
     * @param string $str test string
     * @return bool return true if it is a valid IPv4 address
     */
    public static function isIPv4($str)
    {
        if (filter_var($str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return true;
        }
        return false;
    }

    /**
     * Check if a string is a valid IPv6 address
     * @param string $str test string
     * @return bool return true if it is a valid IPv6 address
     */
    public static function isIPv6($str)
    {
        if (filter_var($str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return true;
        }
        return false;
    }

    /**
     * Check if a string is a valid IP address, and return its version
     * @param string $str test string
     * @return number|null return version or null if it is not a ip address
     */
    public static function matchIPVersion($str)
    {
        if (Access_Ip::isIPv4($str)) {
            return 4;
        }
        if (Access_Ip::isIPv6($str)) {
            return 6;
        }
        return null;
    }

    /**
     * Check if an ip address is in the CIDR subnet.
     * @param string $cidr - an ipv6 address, ex 1.2.3.5/24, ::1, 2001:288:5200::1, :: ,etc.
     * @param string $addr - an ip subnet, ex 1.2.3.4 or 2001:288:5400/39 or 2001:288:5432:/64 or 2001:288:5478::/64..
     * @return bool return true if $addr is inside the $cidr subnet, or return false.
     */
    public static function matchCIDR($cidr, $addr)
    {
        $cidrVersion = Access_Ip::matchIPVersion(explode('/', $cidr)[0]);
        $addrVersion = Access_Ip::matchIPVersion($addr);

        if ($cidrVersion !== $addrVersion) {
            return false;
        }

        if ($cidrVersion === 4) {
            return Access_Ip::matchCIDRv4($cidr, $addr);
        }

        if ($cidrVersion === 6) {
            return Access_Ip::matchCIDRv6($cidr, $addr);
        }

        return false;
    }

    /**
     * Check if an ipv4 address is in the CIDRv4 subnet.
     * @param $cidr string an ipv4 subnet ex .. 1.2.3.5/24
     * @param $addr string an ipv4 address ex.. 1.2.3.4
     * @return bool return true if $ip is inside the $cidr subnet, or return false.
     */
    public static function matchCIDRv4($cidr, $addr)
    {
        $parts = explode('/', $cidr);
        $cidr_ip = $parts[0];
        $cidr_mask = count($parts) >= 2 ? $parts[1] : '32';
        return (ip2long($addr) >> (32 - $cidr_mask) == ip2long($cidr_ip) >> (32 - $cidr_mask));
    }

    /**
     * Convert an ipv6 address to bin string
     * @param string $addr - an ipv6 address
     * @return string return the binary string of an ipv6 address if parameter ip6 is an ipv6 address,
     *         else it return an empty string.
     */
    public static function ExpandIPv6Notation2Bin($addr)
    {
        if (strpos($addr, '::') !== false) {
            $addr = str_replace('::', str_repeat(':0', 8 - substr_count($addr, ':')) . ':', $addr);
        }
        $ip6parts = explode(':', $addr);
        $res = "";
        foreach ($ip6parts as $part) {
            $res .= str_pad(base_convert($part, 16, 2), 16, 0, STR_PAD_LEFT);
        }
        return $res;
    }

    /**
     * Check if an ipv6 address is in the CIDRv6 subnet.
     * @param string $cidr - an ipv6 subnet, ex 2001:288:5400/39 or 2001:288:5432:/64 or 2001:288:5478::/64..
     * @param string $addr - an ipv6 address, ex ::1, 2001:288:5200::1, :: ,etc.
     * @return bool return true if $addr is inside the $cidr subnet, or return false.
     */
    public static function MatchCIDRv6($cidr, $addr)
    {
        $parts = explode('/', $cidr);
        $cidr_ip = $parts[0];
        $cidr_mask = count($parts) >= 2 ? $parts[1] : '128';
        $cidr_bin = substr(Access_Ip::ExpandIPv6Notation2Bin($cidr_ip), 0, $cidr_mask);
        $ip_bin = substr(Access_Ip::ExpandIPv6Notation2Bin($addr), 0, $cidr_mask);
        if (!strcmp($cidr_bin, $ip_bin))
            return true;
        return false;
    }
}
