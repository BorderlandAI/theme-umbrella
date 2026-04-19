<?php
/**
 * Kawasaki Catalog Functions
 * Loaded via functions.php include
 */

if (!function_exists('get_kawasaki_catalog')) {
    function get_kawasaki_catalog($category) {
        $cache_key = 'kw_catalog_' . md5($category);
        $cached = get_transient($cache_key);
        if ($cached !== false) return $cached;

        $json_path = get_template_directory() . '/data/kawasaki-catalog.json';
        if (!file_exists($json_path)) return array();

        $raw = file_get_contents($json_path);
        $data = json_decode($raw, true);
        if (!is_array($data)) return array();

        $result = isset($data[$category]) ? $data[$category] : array();
        set_transient($cache_key, $result, 3600);
        return $result;
    }
}
