<?php
/**
 * Store Registry — single source of truth for per-store data.
 * Accessed via bl_stores(), bl_store($slug), bl_current_store().
 */
if (!defined('ABSPATH')) exit;

function bl_stores() {
    static $stores = null;
    if ($stores !== null) return $stores;

    $stores = [
        // Order matters: determines LOC · NO. plate on /stores/{slug}.
        // 01 Morden · 02 Thompson · 03 Brandon · 04 Portage la Prairie.
        'morden' => [
            'slug'           => 'morden',
            'name'           => 'Morden',
            'full_name'      => 'Borderland Powersports Morden',
            'phone'          => '204-822-5550',
            'phone_tel'      => '+12048225550',
            'sms_phone'      => '+12044000591',
            'email'          => 'sales@borderlandpowersports.com',
            'address'        => '167 Loren Drive',
            'city'           => 'Morden',
            'region'         => 'MB',
            'postal'         => 'R6M 0C9',
            'coords'         => ['lat' => 49.1923, 'lng' => -98.1006],
            // Verified 2026-04-19 from borderlandpowersports.com
            'hours'          => [
                'Mo' => '08:30-17:30', 'Tu' => '08:30-17:30', 'We' => '08:30-17:30',
                'Th' => '08:30-17:30', 'Fr' => '08:30-17:00', 'Sa' => '09:00-12:00', 'Su' => 'closed',
            ],
            'brands'         => ['Kawasaki', 'Polaris', 'CFMOTO'],
            'chat_slug'      => 'morden',
            'domain'         => 'https://www.borderlandmorden.com',
            'fb_page_id'     => '',
            'embed_map_url'  => 'https://www.google.com/maps/embed/v1/place?key=&q=167+Loren+Drive,+Morden,+MB',
            'photo'          => 'storefront-morden.webp',
            'area_served'    => ['Morden', 'Winkler', 'Altona', 'Carman', 'Pembina Valley'],
            'price_range'    => '$$',
            'same_as'        => [
                'https://www.facebook.com/borderlandpowersports',
                'https://www.instagram.com/borderlandpowersports/',
                'https://maps.app.goo.gl/is4zi5ytLdMJzPt59',
            ],
        ],
        'thompson' => [
            'slug'           => 'thompson',
            'name'           => 'Thompson',
            'full_name'      => 'Borderland Powersports Thompson',
            'phone'          => '204-677-2952',
            'phone_tel'      => '+12046772952',
            'sms_phone'      => '+14315004828',
            'email'          => 'sales@borderlandthompson.com',
            'address'        => '3 Nelson Road',
            'city'           => 'Thompson',
            'region'         => 'MB',
            'postal'         => 'R8N 0B3',
            'coords'         => ['lat' => 55.7435, 'lng' => -97.8558],
            // Verified 2026-04-19 from borderlandthompson.com
            'hours'          => [
                'Mo' => '08:30-17:30', 'Tu' => '08:30-17:30', 'We' => '08:30-17:30',
                'Th' => '08:30-17:30', 'Fr' => '08:30-17:30', 'Sa' => 'closed', 'Su' => 'closed',
            ],
            'brands'         => ['Polaris', 'Yamaha', 'Mercury', 'Lund', 'Marlon', 'Equinox', 'Abitibi & Co', 'ARGO', 'Swamp Rider'],
            'chat_slug'      => 'thompson',
            'domain'         => 'https://www.borderlandthompson.com',
            'fb_page_id'     => '',
            'embed_map_url'  => 'https://www.google.com/maps/embed/v1/place?key=&q=3+Nelson+Road,+Thompson,+MB',
            'photo'          => 'storefront-thompson.webp',
            'area_served'    => ['Thompson', 'The Pas', 'Flin Flon', 'Norway House', 'Northern Manitoba'],
            'price_range'    => '$$',
            'same_as'        => [
                'https://www.facebook.com/YourRicksMarine',
                'https://www.instagram.com/borderlandthompson/',
                'https://maps.app.goo.gl/z2duQZexoP8XzsfG9',
            ],
        ],
        'brandon' => [
            'slug'           => 'brandon',
            'name'           => 'Brandon',
            'full_name'      => 'Borderland Powersports Brandon',
            'phone'          => '204-725-1003',
            'phone_tel'      => '+12047251003',
            'sms_phone'      => '+14315004828',
            'email'          => 'sales@borderlandbrandon.com',
            'address'        => '206 16th Street North',
            'city'           => 'Brandon',
            'region'         => 'MB',
            'postal'         => 'R7A 2V3',
            'coords'         => ['lat' => 49.8513, 'lng' => -99.9519],
            // Verified 2026-04-19 from borderlandbrandon.com
            'hours'          => [
                'Mo' => '08:30-17:30', 'Tu' => '08:30-17:30', 'We' => '08:30-17:30',
                'Th' => '08:30-17:30', 'Fr' => '08:30-17:00', 'Sa' => '09:00-12:00', 'Su' => 'closed',
            ],
            'brands'         => ['Kawasaki', 'Suzuki'],
            'chat_slug'      => 'brandon',
            'domain'         => 'https://www.borderlandbrandon.com',
            'fb_page_id'     => '',
            'embed_map_url'  => 'https://www.google.com/maps/embed/v1/place?key=&q=206+16th+Street+North,+Brandon,+MB',
            'photo'          => 'storefront-brandon.webp',
            'area_served'    => ['Brandon', 'Virden', 'Neepawa', 'Souris', 'Minnedosa', 'Westman'],
            'price_range'    => '$$',
            'same_as'        => [
                'https://www.facebook.com/BorderlandBrandon',
                'https://www.instagram.com/borderlandbrandon/',
                'https://maps.app.goo.gl/L2C9Xn5FHWmkB1Ye6',
            ],
        ],
        'portage' => [
            'slug'           => 'portage',
            'name'           => 'Portage la Prairie',
            'full_name'      => 'Borderland Powersports Portage la Prairie',
            'phone'          => '204-239-5900',
            'phone_tel'      => '+12042395900',
            'sms_phone'      => '+14315004828',
            'email'          => 'sales@borderlandportage.com',
            'address'        => '2533 Saskatchewan Ave W',
            'city'           => 'Portage la Prairie',
            'region'         => 'MB',
            'postal'         => 'R1N 4A5',
            'coords'         => ['lat' => 49.9731, 'lng' => -98.2927],
            // Verified 2026-04-19 with John (Portage 9 to 5)
            'hours'          => [
                'Mo' => '09:00-17:00', 'Tu' => '09:00-17:00', 'We' => '09:00-17:00',
                'Th' => '09:00-17:00', 'Fr' => '09:00-17:00', 'Sa' => 'closed', 'Su' => 'closed',
            ],
            'brands'         => ['Kawasaki', 'Swamp Rider'],
            'chat_slug'      => 'portage',
            'domain'         => 'https://www.borderlandportage.com',
            'fb_page_id'     => '',
            'embed_map_url'  => 'https://www.google.com/maps/embed/v1/place?key=&q=2533+Saskatchewan+Ave+W,+Portage+la+Prairie,+MB',
            'photo'          => 'storefront-portage.webp',
            'area_served'    => ['Portage la Prairie', 'Gladstone', 'MacGregor', 'Carberry', 'Central Plains'],
            'price_range'    => '$$',
            'same_as'        => [
                'https://www.facebook.com/profile.php?id=61583148800073',
                'https://www.instagram.com/borderlandportage/',
                'https://maps.app.goo.gl/9t9gq3FwFUjyaHF98',
            ],
        ],
    ];
    return $stores;
}

function bl_store($slug) {
    $slug = strtolower(trim((string) $slug));
    $stores = bl_stores();
    return $stores[$slug] ?? null;
}

/**
 * Current store context: returns store array for /stores/{slug}/... URLs,
 * null for unified pages. Cached per request.
 */
function bl_current_store() {
    static $resolved = false;
    static $store = null;
    if ($resolved) return $store;
    $resolved = true;

    $slug = '';
    if (function_exists('get_query_var')) {
        $slug = (string) get_query_var('bl_store');
    }
    if (!$slug && !empty($_GET['store'])) {
        $slug = sanitize_text_field($_GET['store']);
    }
    if (!$slug) return $store;

    $store = bl_store($slug);
    return $store;
}

/**
 * Pretty hours string for display — "Mon–Fri 9:00 AM – 5:30 PM · Sat/Sun Closed"
 */
function bl_hours_display($store) {
    if (empty($store['hours'])) return '';
    $wk = $store['hours']['Mo'] ?? '';
    $sa = $store['hours']['Sa'] ?? '';
    $su = $store['hours']['Su'] ?? '';
    $weekday = $wk && $wk !== 'closed' ? 'Mon–Fri ' . bl_fmt_range($wk) : '';
    $weekend = ($sa === 'closed' && $su === 'closed') ? 'Sat/Sun Closed' : ('Sat ' . bl_fmt_range($sa) . ' · Sun ' . bl_fmt_range($su));
    return trim($weekday . ' · ' . $weekend, ' ·');
}

function bl_fmt_range($hhmm_range) {
    if (!$hhmm_range || $hhmm_range === 'closed') return 'Closed';
    [$open, $close] = array_pad(explode('-', $hhmm_range), 2, '');
    return bl_fmt_time($open) . ' – ' . bl_fmt_time($close);
}

/**
 * Return theme-relative URL for a brand logo, or empty string if none exists.
 * Brands without a logo file render text-only in pills.
 */
function bl_brand_logo_url($brand) {
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '', $brand));
    $map = [
        'kawasaki'   => 'kawasaki-logo.svg',
        'suzuki'     => 'suzuki-logo.png',
        'polaris'    => 'polaris-logo.png',
        'yamaha'     => 'yamaha-logo.png',
        'mercury'    => 'mercury-logo.png',
        'lund'       => 'lund-logo.png',
        'argo'       => 'argo-logo.png',
        'cfmoto'     => 'cfmoto-logo.jpg',
        'swamprider' => 'swamprider-logo.png',
        'fxr'        => 'fxr-logo.svg',
        'equinox'    => 'equinox-logo.png',
        'abitibico'  => 'abitibico-logo.png',
        'marlon'     => 'marlon-logo.png',
        'abitibi'    => 'abitibico-logo.png',
    ];
    if (!isset($map[$slug])) return '';
    return get_template_directory_uri() . '/assets/images/brands/' . $map[$slug];
}

function bl_fmt_time($hhmm) {
    if (!preg_match('/^(\d{1,2}):(\d{2})$/', $hhmm, $m)) return $hhmm;
    $h = (int) $m[1]; $mi = $m[2];
    $ampm = $h >= 12 ? 'PM' : 'AM';
    $hh = (($h % 12) === 0) ? 12 : ($h % 12);
    return "{$hh}:{$mi} {$ampm}";
}
