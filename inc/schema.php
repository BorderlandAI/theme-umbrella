<?php
/**
 * JSON-LD schema: LocalBusiness per store + Organization on homepage.
 * Also emits canonical + basic SEO tags per page.
 */
if (!defined('ABSPATH')) exit;

add_action('wp_head', 'bl_emit_head_seo', 1);

function bl_emit_head_seo() {
    $store = bl_current_store();
    $home  = home_url('/');
    $req   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $canonical = rtrim($home, '/') . $req;

    // Title + description logic
    $site = BORDERLAND_SITE_NAME;
    if ($store) {
        $title = $store['full_name'];
        $desc  = 'Visit ' . $store['full_name'] . ' in ' . $store['city'] . ', MB. New and pre-owned ' . implode(', ', $store['brands']) . ' powersports.';
    } elseif (is_front_page() || is_home()) {
        $title = $site . ' — Manitoba\'s Multi-Location Powersports Group';
        $desc  = $site . ' serves Manitoba with 4 dealerships in Brandon, Morden, Portage la Prairie, and Thompson. Kawasaki, Polaris, Yamaha, Suzuki, and more.';
    } else {
        $title = wp_get_document_title();
        $desc  = $site . ' — Brandon, Morden, Portage la Prairie, Thompson.';
    }

    echo '  <meta name="description" content="' . esc_attr($desc) . '" />' . "\n";
    echo '  <link rel="canonical" href="' . esc_url($canonical) . '" />' . "\n";
    echo '  <meta property="og:locale" content="en_CA" />' . "\n";
    echo '  <meta property="og:site_name" content="' . esc_attr($site) . '" />' . "\n";
    echo '  <meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
    echo '  <meta property="og:description" content="' . esc_attr($desc) . '" />' . "\n";
    echo '  <meta property="og:url" content="' . esc_url($canonical) . '" />' . "\n";
    echo '  <meta name="twitter:card" content="summary_large_image" />' . "\n";
    echo '  <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />' . "\n";

    if ($store) {
        bl_emit_localbusiness_jsonld($store);
    } elseif (is_front_page() || is_home()) {
        bl_emit_organization_jsonld();
    }
}

function bl_emit_localbusiness_jsonld($store) {
    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'AutomotiveBusiness',
        'name'        => $store['full_name'],
        'telephone'   => $store['phone'],
        'email'       => $store['email'],
        'url'         => home_url('/stores/' . $store['slug']),
        'address'     => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $store['address'],
            'addressLocality' => $store['city'],
            'addressRegion'   => $store['region'],
            'postalCode'      => $store['postal'],
            'addressCountry'  => 'CA',
        ],
    ];
    if (!empty($store['coords']['lat'])) {
        $schema['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => $store['coords']['lat'], 'longitude' => $store['coords']['lng']];
    }
    if (!empty($store['hours'])) {
        $oh = [];
        foreach ($store['hours'] as $day => $range) {
            if ($range === 'closed' || !$range) continue;
            $oh[] = $day . ' ' . $range;
        }
        if ($oh) $schema['openingHours'] = $oh;
    }
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}

function bl_emit_organization_jsonld() {
    $stores  = bl_stores();
    $locations = [];
    foreach ($stores as $s) {
        $locations[] = [
            '@type'     => 'AutomotiveBusiness',
            'name'      => $s['full_name'],
            'telephone' => $s['phone'],
            'url'       => home_url('/stores/' . $s['slug']),
            'address'   => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $s['address'],
                'addressLocality' => $s['city'],
                'addressRegion'   => $s['region'],
                'addressCountry'  => 'CA',
            ],
        ];
    }
    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => BORDERLAND_SITE_NAME,
        'url'      => BORDERLAND_SITE_URL,
        'logo'     => bl_img('logo.png'),
        'hasLocation' => $locations,
    ];
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}
