<?php
// Page de réglages
if (!defined('ABSPATH')) exit;

class IB_Settings {
    public static function get($key) {
        return get_option('ib_' . $key);
    }

    public static function set($key, $value) {
        update_option('ib_' . $key, $value);
    }
}
