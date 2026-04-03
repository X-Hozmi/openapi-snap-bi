<?php

namespace App\Utils;

/**
 * IPMatcher - Advanced IP address matching utility
 *
 * Supports IPv4 and IPv6 address matching with CIDR notation
 * Compatible with PHP 8.0+
 *
 * @author X-Hozmi
 *
 * @version 1.0.0
 */
class IPMatcher
{
    /**
     * Check if an IP address matches against a list of allowed IPs/CIDRs
     *
     * @param  string  $ip  The IP address to check
     * @param  list<string>|string  $allowedIps  Array of IPs/CIDRs or comma-separated string
     * @return bool True if IP matches any of the allowed IPs/CIDRs
     */
    public static function matches(string $ip, array|string $allowedIps): bool
    {
        // Convert string to array if needed
        if (is_string($allowedIps)) {
            $allowedIps = array_filter(array_map('trim', explode(',', $allowedIps)));
        }

        foreach ($allowedIps as $allowedIp) {
            $allowedIp = trim($allowedIp);

            if (self::isMatch($ip, $allowedIp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP address matches a single IP or CIDR notation
     *
     * @param  string  $ip  The IP address to check
     * @param  string  $cidr  IP address or CIDR notation
     * @return bool True if IP matches
     */
    public static function isMatch(string $ip, string $cidr): bool
    {
        // Validate input IP
        if (! self::isValidIP($ip)) {
            return false;
        }

        // If not CIDR notation, do direct comparison
        if (! str_contains($cidr, '/')) {
            return $ip === $cidr;
        }

        // Determine IP version and use appropriate matching
        if (self::isIPv6($ip)) {
            return self::matchIPv6($ip, $cidr);
        }

        return self::matchIPv4($ip, $cidr);
    }

    /**
     * Check if IP is valid (IPv4 or IPv6)
     *
     * @param  string  $ip  The IP address to validate
     * @return bool True if valid
     */
    public static function isValidIP(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Check if IP is IPv6
     *
     * @param  string  $ip  The IP address to check
     * @return bool True if IPv6
     */
    public static function isIPv6(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Check if IP is IPv4
     *
     * @param  string  $ip  The IP address to check
     * @return bool True if IPv4
     */
    public static function isIPv4(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Match IPv4 address against CIDR notation
     *
     * @param  string  $ip  IPv4 address
     * @param  string  $cidr  CIDR notation (e.g., 192.168.1.0/24)
     * @return bool True if IP is within CIDR range
     */
    protected static function matchIPv4(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr);

        // Validate subnet
        if (! self::isIPv4($subnet)) {
            return false;
        }

        // Validate mask (0-32)
        $mask = (int) $mask;
        if ($mask < 0 || $mask > 32) {
            return false;
        }

        // Convert IPs to long integers
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        // Calculate network mask
        $maskLong = $mask === 0 ? 0 : (-1 << (32 - $mask));

        // Compare network portions
        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    /**
     * Match IPv6 address against CIDR notation
     *
     * @param  string  $ip  IPv6 address
     * @param  string  $cidr  CIDR notation (e.g., 2001:db8::/32)
     * @return bool True if IP is within CIDR range
     */
    protected static function matchIPv6(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr);

        // Validate subnet
        if (! self::isIPv6($subnet)) {
            return false;
        }

        // Validate mask (0-128)
        $mask = (int) $mask;
        if ($mask < 0 || $mask > 128) {
            return false;
        }

        // Convert IPs to binary strings
        $ipBinary = inet_pton($ip);
        $subnetBinary = inet_pton($subnet);

        if ($ipBinary === false || $subnetBinary === false) {
            return false;
        }

        // Calculate how many bytes to compare fully and remaining bits
        $bytesToCompare = intdiv($mask, 8);
        $remainingBits = $mask % 8;

        // Compare full bytes
        if ($bytesToCompare > 0) {
            if (substr($ipBinary, 0, $bytesToCompare) !== substr($subnetBinary, 0, $bytesToCompare)) {
                return false;
            }
        }

        // Compare remaining bits if any
        if ($remainingBits > 0) {
            $ipByte = ord($ipBinary[$bytesToCompare]);
            $subnetByte = ord($subnetBinary[$bytesToCompare]);
            $bitmask = 0xFF << (8 - $remainingBits);

            if (($ipByte & $bitmask) !== ($subnetByte & $bitmask)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Convert CIDR to IP range
     *
     * @param  string  $cidr  CIDR notation
     * @return array<string, string>|null Array with 'start' and 'end' keys, or null if invalid
     */
    public static function cidrToRange(string $cidr): ?array
    {
        if (! str_contains($cidr, '/')) {
            return null;
        }

        [$subnet, $mask] = explode('/', $cidr);

        if (self::isIPv4($subnet)) {
            return self::cidrToRangeIPv4($subnet, (int) $mask);
        }

        if (self::isIPv6($subnet)) {
            return self::cidrToRangeIPv6($subnet, (int) $mask);
        }

        return null;
    }

    /**
     * Convert IPv4 CIDR to range
     *
     * @param  string  $subnet  IPv4 subnet
     * @param  int  $mask  CIDR mask
     * @return array<string, string> Array with 'start' and 'end' keys
     */
    protected static function cidrToRangeIPv4(string $subnet, int $mask): array
    {
        $subnetLong = ip2long($subnet);
        $maskLong = $mask === 0 ? 0 : (-1 << (32 - $mask));

        $start = $subnetLong & $maskLong;
        $end = $start + pow(2, (32 - $mask)) - 1;

        return [
            'start' => long2ip($start) ?: '',
            'end' => long2ip($end) ?: '',
        ];
    }

    /**
     * Convert IPv6 CIDR to range
     *
     * @param  string  $subnet  IPv6 subnet
     * @param  int  $mask  CIDR mask
     * @return array<string, string> Array with 'start' and 'end' keys
     */
    protected static function cidrToRangeIPv6(string $subnet, int $mask): array
    {
        $subnetBinary = inet_pton($subnet);
        if ($subnetBinary === false) {
            return ['start' => '', 'end' => ''];
        }

        $bytesToCompare = intdiv($mask, 8);
        $remainingBits = $mask % 8;

        // Make mutable copy
        $startBinary = $subnetBinary;
        $endBinary = $subnetBinary;

        // Start: Apply mask to subnet
        if ($remainingBits > 0) {
            $bitmask = 0xFF << (8 - $remainingBits);
            $startByte = ord($startBinary[$bytesToCompare]);
            $endByte = $startByte;

            $startBinary[$bytesToCompare] = chr($startByte & $bitmask);
            $endBinary[$bytesToCompare] = chr($endByte | (0xFF >> $remainingBits));
        }

        // Zero out remaining start bytes
        for ($i = $bytesToCompare + ($remainingBits > 0 ? 1 : 0); $i < 16; $i++) {
            $startBinary[$i] = chr(0);
            $endBinary[$i] = chr(0xFF);
        }

        return [
            'start' => inet_ntop($startBinary) ?: '',
            'end' => inet_ntop($endBinary) ?: '',
        ];
    }

    /**
     * Normalize IP address (expand compressed format)
     *
     * @param  string  $ip  IP address
     * @return string|null Normalized IP or null if invalid
     */
    public static function normalize(string $ip): ?string
    {
        $binary = inet_pton($ip);
        if ($binary === false) {
            return null;
        }

        $normalized = inet_ntop($binary);

        return $normalized === false ? null : $normalized;
    }
}
