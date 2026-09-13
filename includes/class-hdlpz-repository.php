<?php

namespace htrxuan\hdlpz;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The admin-configured zone list, postcode matching, and the ONLY place a zone fee is ever
 * resolved.
 *
 * Several competing delivery-zone / local-pickup plugins computed the matching zone and its
 * fee in client-side JavaScript from a zone list already embedded in the page, then trusted a
 * hidden checkout field carrying either the chosen zone id or the fee amount itself when the
 * order was placed. Editing that field let a shopper claim a zone their address doesn't
 * actually belong to, or submit a lower fee outright (CWE-602, Client-Side Enforcement of
 * Server-Side Security).
 *
 * This plugin has no such field to tamper with in the first place: there is no zone id, and no
 * fee, submitted by the shopper anywhere. find_zone_for_postcode() is the ONLY way a zone is
 * ever selected, and it is always called (see HDLPZ_Frontend) with the shipping postcode
 * WooCommerce's own customer/session object is currently tracking -- never a value read from
 * $_POST/$_GET. The fee actually charged always comes from get_fee(), which re-reads the
 * zone's own currently-saved configuration by its id -- never a number a caller supplies.
 */
class HDLPZ_Repository
{
    public static function get_zones()
    {
        $zones = get_option('hdlpz_zones', array());
        return is_array($zones) ? $zones : array();
    }

    /**
     * $rows: array of ['name' => ..., 'postcodes' => 'comma, separated, patterns', 'fee' =>
     * ..., 'note' => ...]. A row with a blank name is dropped (matches this suite's
     * "leave the last row blank to skip it" convention for repeatable admin forms).
     */
    public static function save_zones($rows)
    {
        $zones = array();
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $name = isset($row['name']) ? sanitize_text_field($row['name']) : '';
                if ('' === $name) {
                    continue;
                }
                $patterns = self::parse_postcodes(isset($row['postcodes']) ? (string) $row['postcodes'] : '');
                if (empty($patterns)) {
                    continue;
                }
                $fee = isset($row['fee']) ? (float) $row['fee'] : 0.0;
                if ($fee < 0) {
                    $fee = 0.0;
                }
                $zones[] = array(
                    'id'        => isset($row['id']) && $row['id'] ? sanitize_key($row['id']) : wp_generate_password(8, false, false),
                    'name'      => $name,
                    'postcodes' => $patterns,
                    'fee'       => $fee,
                    'note'      => isset($row['note']) ? sanitize_text_field($row['note']) : '',
                );
            }
        }
        update_option('hdlpz_zones', $zones);
        return $zones;
    }

    private static function parse_postcodes($raw)
    {
        $parts = array_map('trim', explode(',', $raw));
        $parts = array_filter($parts, function ($p) {
            return '' !== $p;
        });
        return array_values(array_map(array(__CLASS__, 'normalize'), $parts));
    }

    private static function normalize($postcode)
    {
        return strtoupper(preg_replace('/\s+/', '', (string) $postcode));
    }

    /**
     * A pattern ending in "*" matches any postcode sharing that prefix; otherwise it must
     * match exactly. Both the pattern and the postcode being tested are normalized the same
     * way, so "90210" and " 90210 " (or lower-case input) always compare equal.
     */
    private static function pattern_matches($pattern, $postcode)
    {
        if ('*' === substr($pattern, -1)) {
            $prefix = substr($pattern, 0, -1);
            return '' !== $prefix && 0 === strpos($postcode, $prefix);
        }
        return $pattern === $postcode;
    }

    /**
     * The only lookup that ever selects a zone. Always takes a postcode string -- never a
     * zone id from a caller -- and returns the first zone (in admin-configured order) whose
     * postcode patterns match it, or null if nothing matches.
     */
    public static function find_zone_for_postcode($postcode)
    {
        $postcode = self::normalize($postcode);
        if ('' === $postcode) {
            return null;
        }
        foreach (self::get_zones() as $zone) {
            foreach ($zone['postcodes'] as $pattern) {
                if (self::pattern_matches($pattern, $postcode)) {
                    return $zone;
                }
            }
        }
        return null;
    }

    /**
     * Re-reads a zone's currently-saved fee by id -- used only to confirm a resolved zone's
     * fee at the moment it's applied, never to accept a fee from anywhere else.
     */
    public static function get_zone($zone_id)
    {
        foreach (self::get_zones() as $zone) {
            if ($zone['id'] === $zone_id) {
                return $zone;
            }
        }
        return null;
    }
}
