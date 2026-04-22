<?php
/**
 * SEO head tags + JSON-LD.
 *
 * Emits:
 * - <title> override for store-scoped pages (WP page title "Store Landing" → store-specific).
 * - <meta description> per page archetype (homepage / store / inventory / brands / service / parts / contact / financing / faq / news).
 * - Open Graph + Twitter Card with og:image.
 * - Single combined <script type="application/ld+json"> @graph: Organization + all AutomotiveBusiness nodes.
 *   On per-store pages, the current store's node is emphasized via `mainEntity` references.
 * - BreadcrumbList helper `bl_emit_breadcrumb_jsonld($trail)` used by vehicle-detail and other deep pages.
 */
if (!defined('ABSPATH')) exit;

add_action('wp_head', 'bl_emit_head_seo', 1);

/**
 * Override <title> for store-scoped pages + homepage so WP page title
 * ("Store Landing", "Inventory", "Contact") does not bleed through.
 */
add_filter('pre_get_document_title', function($title) {
    $site = BORDERLAND_SITE_NAME;
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    $store = bl_current_store();
    if ($store) {
        $name = $store['full_name'];
        $city = $store['city'] . ', MB';
        $slug = preg_quote($store['slug'], '#');
        if (preg_match("#^/stores/{$slug}/inventory/[^/]+/?$#", $path))   return $title; // vehicle-detail handles its own title
        if (preg_match("#^/stores/{$slug}/inventory/?$#",      $path))    return "New & Pre-Owned Inventory — {$name}, {$city}";
        if (preg_match("#^/stores/{$slug}/service/?$#",        $path))    return "Service Department — {$name}, {$city}";
        if (preg_match("#^/stores/{$slug}/parts/?$#",          $path))    return "Parts Department — {$name}, {$city}";
        if (preg_match("#^/stores/{$slug}/contact/?$#",        $path))    return "Contact {$name} — {$city}";
        if (preg_match("#^/stores/{$slug}/?$#",                $path))    return "{$name} — {$city} Powersports Dealer";
    }

    // Unified pages
    if ($path === '/' || $path === '') return "{$site} — Manitoba's Multi-Location Powersports Group";
    if ($path === '/inventory/' || $path === '/inventory')               return "New & Used Powersports Inventory — Brandon, Morden, Portage la Prairie, Thompson | {$site}";
    if ($path === '/brands/'    || $path === '/brands')                  return "Brands We Carry — Kawasaki, Polaris, Yamaha, Suzuki, CFMOTO & More | {$site}";
    if ($path === '/financing/' || $path === '/financing')               return "Powersports Financing & Pre-Approval — Manitoba | {$site}";
    if ($path === '/contact/'   || $path === '/contact')                 return "Contact Our 4 Manitoba Dealerships | {$site}";
    if ($path === '/faq/'       || $path === '/faq')                     return "Frequently Asked Questions — {$site}";
    if ($path === '/stores/'    || $path === '/stores')                  return "Our 4 Manitoba Locations | {$site}";
    if ($path === '/news/'      || $path === '/news')                    return "News & Articles — {$site}";
    if ($path === '/about/'     || $path === '/about')                   return "About Us — {$site}";

    return $title;
}, 20);

/**
 * Strip WP's default max-image-preview robots meta so we don't duplicate it
 * with the one we emit in bl_emit_head_seo().
 */
remove_filter('wp_robots', 'wp_robots_max_image_preview_large');

function bl_emit_head_seo() {
    $store = bl_current_store();
    $home  = home_url('/');
    $req   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $canonical = rtrim($home, '/') . $req;

    $site  = BORDERLAND_SITE_NAME;
    $title = wp_get_document_title();
    $desc  = bl_page_description($store, $req);
    $img   = bl_page_og_image($store, $req);

    echo '  <meta name="description" content="' . esc_attr($desc) . '" />' . "\n";
    echo '  <link rel="canonical" href="' . esc_url($canonical) . '" />' . "\n";
    echo '  <link rel="alternate" hreflang="en-CA" href="' . esc_url($canonical) . '" />' . "\n";
    echo '  <meta property="og:type" content="website" />' . "\n";
    echo '  <meta property="og:locale" content="en_CA" />' . "\n";
    echo '  <meta property="og:site_name" content="' . esc_attr($site) . '" />' . "\n";
    echo '  <meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
    echo '  <meta property="og:description" content="' . esc_attr($desc) . '" />' . "\n";
    echo '  <meta property="og:url" content="' . esc_url($canonical) . '" />' . "\n";
    if ($img) {
        echo '  <meta property="og:image" content="' . esc_url($img) . '" />' . "\n";
        echo '  <meta property="og:image:width" content="1200" />' . "\n";
        echo '  <meta property="og:image:height" content="630" />' . "\n";
    }
    echo '  <meta name="twitter:card" content="summary_large_image" />' . "\n";
    echo '  <meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
    echo '  <meta name="twitter:description" content="' . esc_attr($desc) . '" />' . "\n";
    if ($img) echo '  <meta name="twitter:image" content="' . esc_url($img) . '" />' . "\n";
    echo '  <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />' . "\n";

    // One combined @graph for the whole site — Organization + all AutomotiveBusiness nodes.
    // On vehicle-detail the template emits its own Product/Vehicle + BreadcrumbList.
    bl_emit_site_graph_jsonld($store);
}

/**
 * Page-level meta description resolver.
 */
function bl_page_description($store, $path) {
    $site = BORDERLAND_SITE_NAME;

    if ($store) {
        $brands = implode(', ', $store['brands']);
        $name   = $store['full_name'];
        $city   = $store['city'];
        $slug   = preg_quote($store['slug'], '#');

        if (preg_match("#^/stores/{$slug}/inventory/?$#", $path)) {
            return "Browse new and pre-owned {$brands} inventory in-stock at {$name} — {$city}, Manitoba. Filter by type, make, year and price.";
        }
        if (preg_match("#^/stores/{$slug}/service/?$#", $path)) {
            return "Certified service and maintenance for {$brands} at {$name} in {$city}, MB. Book an appointment or call our service department.";
        }
        if (preg_match("#^/stores/{$slug}/parts/?$#", $path)) {
            return "Genuine OEM parts and accessories for {$brands} at {$name}, {$city}, MB. Order online or call our parts counter.";
        }
        if (preg_match("#^/stores/{$slug}/contact/?$#", $path)) {
            return "Contact {$name}. Address, phone, hours and directions for our {$city}, Manitoba dealership.";
        }
        // Store landing fallback
        return "Visit {$name} in {$city}, Manitoba. New and pre-owned {$brands} powersports — sales, service, parts, financing.";
    }

    if ($path === '/' || $path === '')                          return "{$site} serves Manitoba with 4 dealerships — Brandon, Morden, Portage la Prairie and Thompson. New and pre-owned Kawasaki, Polaris, Yamaha, Suzuki, CFMOTO, Mercury and more.";
    if (preg_match('#^/inventory/?$#', $path))                  return "Browse new and pre-owned ATVs, UTVs, motorcycles, snowmobiles and watercraft across all 4 Borderland Manitoba locations. Kawasaki, Polaris, Yamaha, Suzuki, CFMOTO, Mercury, Lund and more.";
    if (preg_match('#^/brands/?$#', $path))                     return "Borderland Powersports carries Kawasaki, Polaris, Yamaha, Suzuki, CFMOTO, Mercury, Lund, ARGO, Swamp Rider, FXR, Abitibi & Co and Equinox across our 4 Manitoba stores.";
    if (preg_match('#^/financing/?$#', $path))                  return "Pre-approval for powersports financing in Manitoba. Quick 2-minute online application — our team responds within one business day.";
    if (preg_match('#^/contact/?$#', $path))                    return "Contact any of our 4 Manitoba locations — Brandon, Morden, Portage la Prairie or Thompson. Phone, email, hours and directions.";
    if (preg_match('#^/faq/?$#', $path))                        return "Frequently asked questions about financing, service, parts, trade-ins and multi-store inventory transfers at Borderland Powersports Manitoba.";
    if (preg_match('#^/stores/?$#', $path))                     return "Borderland Powersports has 4 Manitoba locations — Brandon, Morden, Portage la Prairie and Thompson. Find your nearest dealership.";
    if (preg_match('#^/news/?$#', $path))                       return "Latest news and articles from the Borderland Powersports group across Manitoba — brand updates, riding tips, seasonal guides.";
    if (preg_match('#^/about/?$#', $path))                      return "About Borderland Powersports — Manitoba's multi-location powersports group serving Brandon, Morden, Portage la Prairie and Thompson.";
    return "{$site} — Manitoba's multi-location powersports group. Brandon · Morden · Portage la Prairie · Thompson.";
}

/**
 * Resolve best og:image per page. Prefer the store storefront on store pages,
 * otherwise the umbrella logo. 1200×630 variants are TODO — caller should crop.
 */
function bl_page_og_image($store, $path) {
    if ($store && !empty($store['photo'])) {
        $candidate = get_template_directory_uri() . '/assets/images/stores/' . $store['photo'];
        $on_disk   = get_template_directory() . '/assets/images/stores/' . $store['photo'];
        if (file_exists($on_disk)) return $candidate;
    }
    // Homepage / unified pages: logo as safe fallback until a 1200×630 hero ships.
    return get_template_directory_uri() . '/assets/images/logo-umbrella-v2.png';
}

/**
 * Convert "HH:MM-HH:MM" range + day code into schema.org OpeningHoursSpecification.
 */
function bl_opening_hours_spec($store) {
    if (empty($store['hours'])) return [];
    $dayMap = [
        'Mo' => 'Monday', 'Tu' => 'Tuesday', 'We' => 'Wednesday', 'Th' => 'Thursday',
        'Fr' => 'Friday', 'Sa' => 'Saturday', 'Su' => 'Sunday',
    ];
    $out = [];
    foreach ($store['hours'] as $d => $range) {
        if ($range === 'closed' || !$range || !isset($dayMap[$d])) continue;
        [$open, $close] = array_pad(explode('-', $range), 2, '');
        if (!$open || !$close) continue;
        $out[] = [
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => 'https://schema.org/' . $dayMap[$d],
            'opens'     => $open,
            'closes'    => $close,
        ];
    }
    return $out;
}

/**
 * Build an AutomotiveBusiness node for one store.
 */
function bl_store_node($store) {
    $org_id = BORDERLAND_SITE_URL . '/#organization';
    $node = [
        '@type'            => 'AutomotiveBusiness',
        '@id'              => BORDERLAND_SITE_URL . '/stores/' . $store['slug'] . '#business',
        'name'             => $store['full_name'],
        'url'              => BORDERLAND_SITE_URL . '/stores/' . $store['slug'] . '/',
        'telephone'        => $store['phone_tel'] ?? $store['phone'],
        'email'            => $store['email'],
        'priceRange'       => $store['price_range'] ?? '$$',
        'currenciesAccepted' => 'CAD',
        'paymentAccepted'  => 'Cash, Credit Card, Debit, Financing',
        'parentOrganization' => ['@id' => $org_id],
        'address'          => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $store['address'],
            'addressLocality' => $store['city'],
            'addressRegion'   => $store['region'],
            'postalCode'      => $store['postal'] ?? '',
            'addressCountry'  => 'CA',
        ],
    ];
    if (!empty($store['coords']['lat'])) {
        $node['geo'] = [
            '@type'     => 'GeoCoordinates',
            'latitude'  => $store['coords']['lat'],
            'longitude' => $store['coords']['lng'],
        ];
        $node['hasMap'] = 'https://www.google.com/maps/search/?api=1&query=' . $store['coords']['lat'] . ',' . $store['coords']['lng'];
    }
    $ohs = bl_opening_hours_spec($store);
    if ($ohs) $node['openingHoursSpecification'] = $ohs;

    if (!empty($store['area_served'])) {
        $node['areaServed'] = array_map(fn($p) => ['@type' => 'Place', 'name' => $p], $store['area_served']);
    }

    if (!empty($store['brands'])) {
        // Brand list — Brand nodes (not Product) so we don't emit incomplete Product snippets
        $node['brand'] = array_map(fn($b) => ['@type' => 'Brand', 'name' => $b], $store['brands']);
    }

    if (!empty($store['same_as'])) {
        $node['sameAs'] = $store['same_as'];
    }

    // Storefront photo as image
    if (!empty($store['photo'])) {
        $on_disk = get_template_directory() . '/assets/images/stores/' . $store['photo'];
        if (file_exists($on_disk)) {
            $node['image'] = get_template_directory_uri() . '/assets/images/stores/' . $store['photo'];
        }
    }

    return $node;
}

/**
 * Emit the site-wide @graph: Organization + all 4 AutomotiveBusiness children + WebSite.
 * On a per-store page the current store's node appears first (same payload — order is the only hint).
 */
function bl_emit_site_graph_jsonld($current_store = null) {
    $org_id = BORDERLAND_SITE_URL . '/#organization';
    $website_id = BORDERLAND_SITE_URL . '/#website';

    $stores = bl_stores();
    $store_nodes = [];
    $sub_refs = [];
    foreach ($stores as $s) {
        $node = bl_store_node($s);
        $store_nodes[$s['slug']] = $node;
        $sub_refs[] = ['@id' => $node['@id']];
    }

    // Put the current store's node first if applicable
    if ($current_store && isset($store_nodes[$current_store['slug']])) {
        $first = $store_nodes[$current_store['slug']];
        unset($store_nodes[$current_store['slug']]);
        $store_nodes = array_merge([$current_store['slug'] => $first], $store_nodes);
    }

    $org = [
        '@type'     => 'Organization',
        '@id'       => $org_id,
        'name'      => BORDERLAND_SITE_NAME,
        'url'       => BORDERLAND_SITE_URL,
        'logo'      => [
            '@type'  => 'ImageObject',
            'url'    => bl_img('logo-umbrella-v2.png'),
        ],
        'subOrganization' => $sub_refs,
        'areaServed'      => [
            '@type' => 'State',
            'name'  => 'Manitoba',
            'address' => ['@type' => 'PostalAddress', 'addressCountry' => 'CA', 'addressRegion' => 'MB'],
        ],
    ];

    $website = [
        '@type'         => 'WebSite',
        '@id'           => $website_id,
        'url'           => BORDERLAND_SITE_URL,
        'name'          => BORDERLAND_SITE_NAME,
        'inLanguage'    => 'en-CA',
        'publisher'     => ['@id' => $org_id],
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => BORDERLAND_SITE_URL . '/inventory/?q={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $graph = array_merge([$org, $website], array_values($store_nodes));
    $payload = [
        '@context' => 'https://schema.org',
        '@graph'   => $graph,
    ];
    echo '<script type="application/ld+json">' . wp_json_encode($payload, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}

/**
 * Emit a BreadcrumbList JSON-LD block.
 *
 * @param array $trail List of [name, url] tuples. Last item's url may be omitted.
 *
 * Example:
 *   bl_emit_breadcrumb_jsonld([
 *     ['Home', home_url('/')],
 *     ['Morden', home_url('/stores/morden')],
 *     ['Inventory', home_url('/stores/morden/inventory/')],
 *     ['2025 Polaris Ranger XP 1000']  // current
 *   ]);
 */
function bl_emit_breadcrumb_jsonld($trail) {
    $items = [];
    $pos = 1;
    foreach ($trail as $node) {
        $item = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => $node[0],
        ];
        if (!empty($node[1])) $item['item'] = $node[1];
        $items[] = $item;
    }
    $payload = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ];
    echo '<script type="application/ld+json">' . wp_json_encode($payload, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}
