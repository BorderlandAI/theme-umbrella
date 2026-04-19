<?php
/**
 * Inventory helpers — thin wrapper around inventory.borderlandgroup.ca public API.
 * Transient-cached (5 min list, 60 s detail) with stale-fallback.
 */
if (!defined('ABSPATH')) exit;

/**
 * Fetch list of vehicles. $store null = all stores.
 * $args: state=new|used, type=ATV|UTV|..., make=Kawasaki, limit, etc.
 */
function bl_get_inventory($store = null, $args = []) {
    if ($store) $args['store'] = $store;
    $args = array_filter($args, fn($v) => $v !== null && $v !== '');

    $cache_key = 'inv_' . md5(serialize($args));
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;

    $url = BORDERLAND_INVENTORY_API . (empty($args) ? '' : '?' . http_build_query($args));
    $response = wp_remote_get($url, ['timeout' => 15]);
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (is_array($data)) {
            set_transient($cache_key, $data, 300);
            update_option('_inv_stale_' . $cache_key, $data, false);
            delete_transient('_inv_api_degraded');
            return $data;
        }
    }
    $stale = get_option('_inv_stale_' . $cache_key);
    if ($stale !== false) {
        set_transient('_inv_api_degraded', true, 300);
        return $stale;
    }
    return [];
}

/**
 * Fetch single vehicle by UUID.
 */
function bl_get_vehicle($id, $store = null) {
    if (empty($id)) return null;
    $cache_key = 'inv_detail_' . md5($id);
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;

    $url = BORDERLAND_INVENTORY_API . '?' . http_build_query(['id' => $id]);
    $response = wp_remote_get($url, ['timeout' => 15]);
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (is_array($data) && !empty($data['id'])) {
            if ($store && !empty($data['store']) && strtolower($data['store']) !== strtolower($store)) return null;
            set_transient($cache_key, $data, 60);
            return $data;
        }
    }
    return null;
}

/**
 * Resolve stock-number → UUID via store's inventory list (cached). Returns vehicle or null.
 */
function bl_resolve_stock($store, $stock) {
    if (!$store || !$stock) return null;
    $list = bl_get_inventory($store);
    foreach ($list as $v) {
        if (!empty($v['stockNumber']) && strcasecmp($v['stockNumber'], $stock) === 0) return $v;
        if (!empty($v['id']) && strcasecmp($v['id'], $stock) === 0) return $v; // fall-through: UUID in URL also works
    }
    return null;
}

/**
 * Convert image path to full URL (OEM external or upload relative).
 */
function bl_inventory_image_url($path) {
    if (!$path) return '';
    if (strpos($path, 'http') === 0) return $path;
    return 'https://inventory.borderlandgroup.ca' . $path;
}

/**
 * Return the best cover image for a vehicle: prefer source=upload (real dealership photos)
 * over source=oem (stock manufacturer photos). Returns URL string or empty.
 */
function bl_vehicle_cover_image($vehicle) {
    $imgs = $vehicle['images'] ?? [];
    if (!$imgs) return '';
    $upload = null; $oem = null;
    foreach ($imgs as $img) {
        $src = $img['source'] ?? '';
        $url = $img['url'] ?? '';
        if (!$url) continue;
        if ($src === 'upload' && !$upload) $upload = $url;
        elseif (!$oem) $oem = $url;
    }
    return bl_inventory_image_url($upload ?: $oem);
}

/**
 * Return vehicle images sorted so real dealership photos (source=upload)
 * come first, then OEM stock photos. Preserves position within each group.
 */
function bl_sorted_images($vehicle) {
    $imgs = $vehicle['images'] ?? [];
    if (!$imgs) return [];
    $upload = []; $oem = []; $other = [];
    foreach ($imgs as $img) {
        $src = $img['source'] ?? '';
        if ($src === 'upload')      $upload[] = $img;
        elseif ($src === 'oem')     $oem[]    = $img;
        else                         $other[]  = $img;
    }
    return array_merge($upload, $oem, $other);
}

/**
 * True if the vehicle has at least one uploaded (real dealership) photo.
 */
function bl_has_real_photo($vehicle) {
    foreach ($vehicle['images'] ?? [] as $img) {
        if (($img['source'] ?? '') === 'upload') return true;
    }
    return false;
}

/**
 * Featured picker: round-robin across makes so a store that carries four
 * brands doesn't end up with a Kawasaki-only shelf. Within each make we
 * still prefer units with real dealership photos over OEM stock shots.
 * Units without any image are dropped (they'd render as empty cards).
 */
function bl_featured_inventory($store = null, $limit = 8) {
    $pool = bl_get_inventory($store, ['state' => 'new', 'limit' => 120]);

    // Drop units with no usable image at all
    $pool = array_values(array_filter($pool, fn($v) => !empty($v['images'])));

    // Group by make
    $by_make = [];
    foreach ($pool as $v) {
        $make = $v['make'] ?: 'Other';
        $by_make[$make][] = $v;
    }

    // Within each make, bubble units with real dealership photos to the front
    foreach ($by_make as &$units) {
        usort($units, fn($a, $b) => (int) bl_has_real_photo($b) - (int) bl_has_real_photo($a));
    }
    unset($units);

    // Round-robin pick
    $picked = [];
    while (count($picked) < $limit) {
        $took = 0;
        foreach ($by_make as $make => $units) {
            if (!$units) { unset($by_make[$make]); continue; }
            $picked[] = array_shift($by_make[$make]);
            $took++;
            if (count($picked) >= $limit) break 2;
        }
        if ($took === 0) break;
    }

    return $picked;
}

/**
 * Short HTML notice for when the inventory API is degraded (serving stale cache).
 */
function bl_inventory_degraded_notice() {
    if (!get_transient('_inv_api_degraded')) return '';
    $store = bl_current_store();
    $phone = $store['phone'] ?? '204-239-5900';
    return '<div class="inv-degraded-notice" style="background:#7a3b00;color:#fff;padding:10px 14px;border-radius:6px;margin:10px 0">⚠️ Inventory is temporarily showing cached data. Please call <a href="tel:' . esc_attr(preg_replace('/\D/', '', $phone)) . '" style="color:#fff;text-decoration:underline">' . esc_html($phone) . '</a> for live availability.</div>';
}
