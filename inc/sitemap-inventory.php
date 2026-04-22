<?php
/**
 * Custom sitemap provider that enumerates every live inventory vehicle URL
 * at /stores/{slug}/inventory/{stockNumber}/ — required because the
 * vehicle-detail template is intentionally excluded from WP's page sitemap
 * (otherwise the template page itself would appear) but individual vehicles
 * have no WP post IDs so they never appear anywhere.
 *
 * Cached via a transient refreshed by a daily wp-cron event.
 */
if (!defined('ABSPATH')) exit;

if (!class_exists('WP_Sitemaps_Provider')) {
    return; // WP 5.5+ provides the base class; bail on older cores.
}

class BL_Inventory_Sitemap_Provider extends WP_Sitemaps_Provider {
    public function __construct() {
        $this->name        = 'inventory';
        $this->object_type = 'inventory';
    }

    /**
     * Single unnamed sub-type so the sitemap index shows one extra row:
     *   wp-sitemap-inventory-1.xml
     */
    public function get_object_subtypes() {
        return [];
    }

    public function get_max_num_pages($object_subtype = '') {
        $urls = bl_inventory_sitemap_urls();
        $per  = max(1, wp_sitemaps_get_max_urls('inventory'));
        $n    = max(1, (int) ceil(count($urls) / $per));
        return $n;
    }

    public function get_url_list($page_num, $object_subtype = '') {
        $urls = bl_inventory_sitemap_urls();
        $per  = max(1, wp_sitemaps_get_max_urls('inventory'));
        $offset = ($page_num - 1) * $per;
        return array_slice($urls, $offset, $per);
    }
}

add_filter('wp_sitemaps_add_provider', function($provider, $name) {
    return $provider;
}, 10, 2);

add_action('init', function() {
    if (function_exists('wp_register_sitemap_provider')) {
        wp_register_sitemap_provider('inventory', new BL_Inventory_Sitemap_Provider());
    }
}, 20);

/**
 * Returns the cached list of [loc, lastmod] entries suitable for WP sitemap output.
 * Falls back to live API call if the cache is cold.
 */
function bl_inventory_sitemap_urls() {
    $cached = get_transient('bl_inv_sitemap_v1');
    if (is_array($cached)) return $cached;
    return bl_inventory_sitemap_refresh();
}

function bl_inventory_sitemap_refresh() {
    $urls = [];
    // Pull all stores; API limit 500 is plenty for current scale (<200 units typical).
    $all = bl_get_inventory(null, ['limit' => 500]);
    if (!is_array($all)) $all = [];

    foreach ($all as $v) {
        $slug  = strtolower($v['store'] ?? '');
        $stock = $v['stockNumber'] ?? $v['id'] ?? '';
        if (!$slug || !$stock) continue;
        if (!bl_store($slug)) continue;
        $entry = [
            'loc'     => trailingslashit(home_url('/stores/' . $slug . '/inventory/' . rawurlencode($stock))),
        ];
        if (!empty($v['updatedAt'])) {
            $ts = strtotime((string) $v['updatedAt']);
            if ($ts) $entry['lastmod'] = gmdate('c', $ts);
        }
        $urls[] = $entry;
    }

    set_transient('bl_inv_sitemap_v1', $urls, 6 * HOUR_IN_SECONDS);
    return $urls;
}

// Daily refresh so lastmod stays fresh and new stock shows up even when no one browses.
add_action('bl_inv_sitemap_refresh_cron', 'bl_inventory_sitemap_refresh');
add_action('init', function() {
    if (!wp_next_scheduled('bl_inv_sitemap_refresh_cron')) {
        wp_schedule_event(time() + 60, 'daily', 'bl_inv_sitemap_refresh_cron');
    }
});
