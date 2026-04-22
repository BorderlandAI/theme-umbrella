<?php
/**
 * Template Name: Vehicle Detail
 * URL: /stores/{slug}/inventory/{stock}  — resolved via bl_resolve_stock().
 * Also supports ?id={uuid} for direct UUID loads.
 */
get_header();

$store = bl_current_store();
$stock = get_query_var('bl_stock') ?: ($_GET['stock'] ?? '');
$id    = $_GET['id'] ?? '';

$vehicle = null;
if ($id) {
    $vehicle = bl_get_vehicle($id, $store['slug'] ?? null);
} elseif ($stock && $store) {
    $vehicle = bl_resolve_stock($store['slug'], $stock);
    if ($vehicle && !empty($vehicle['id'])) {
        $vehicle = bl_get_vehicle($vehicle['id'], $store['slug']);
    }
}

if (!$vehicle) {
    status_header(404);
    ?>
    <main id="content" class="bl-page"><div class="inner">
      <h1>Vehicle Not Found</h1>
      <p>The vehicle you're looking for is no longer available. <a href="<?php echo esc_url(home_url($store ? '/stores/' . $store['slug'] . '/inventory/' : '/inventory/')); ?>">Browse current inventory</a></p>
    </div></main>
    <?php
    get_footer();
    return;
}

$title  = trim(($vehicle['year'] ?? '') . ' ' . ($vehicle['make'] ?? '') . ' ' . ($vehicle['submodel'] ?? $vehicle['model'] ?? ''));
$price  = !empty($vehicle['salePrice']) ? $vehicle['salePrice'] : ($vehicle['basePrice'] ?? null);
$vstate = strtolower($vehicle['state'] ?? '');
$state_label = $vstate === 'new' ? 'New' : 'Pre-Owned';
$images = bl_sorted_images($vehicle);
$vstore_slug = strtolower($vehicle['store'] ?? '');
$vstore = bl_store($vstore_slug) ?: $store;
?>

<main id="content" class="bl-vehicle-detail">
  <div class="inner">
    <nav class="bl-crumbs">
      <a href="<?php echo esc_url(home_url('/')); ?>">Home</a> ›
      <?php if ($vstore): ?>
        <a href="<?php echo esc_url(home_url('/stores/' . $vstore['slug'])); ?>"><?php echo esc_html($vstore['name']); ?></a> ›
        <a href="<?php echo esc_url(home_url('/stores/' . $vstore['slug'] . '/inventory/')); ?>">Inventory</a> ›
      <?php endif; ?>
      <span><?php echo esc_html($title); ?></span>
    </nav>

    <div class="detail-grid">
      <?php
      $gallery_urls = [];
      foreach ($images as $img) {
          $u = bl_inventory_image_url($img['url'] ?? '');
          if ($u) $gallery_urls[] = $u;
      }
      $gallery_json = wp_json_encode($gallery_urls);
      ?>
      <div class="gallery" data-total="<?php echo count($gallery_urls); ?>" data-gallery="<?php echo esc_attr($gallery_json); ?>">
        <?php if (!empty($gallery_urls[0])): ?>
          <div class="main-wrap">
            <img class="main" src="<?php echo esc_url($gallery_urls[0]); ?>" alt="<?php echo esc_attr($title); ?>" data-index="0" />
            <?php if (count($gallery_urls) > 1): ?>
              <button class="nav prev" type="button" aria-label="Previous image">&lsaquo;</button>
              <button class="nav next" type="button" aria-label="Next image">&rsaquo;</button>
              <span class="counter"><span class="cur">1</span> / <span class="total"><?php echo count($gallery_urls); ?></span></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <?php if (count($gallery_urls) > 1): ?>
          <div class="thumbs">
            <?php foreach ($gallery_urls as $idx => $url): ?>
              <img
                src="<?php echo esc_url($url); ?>"
                alt=""
                data-index="<?php echo (int) $idx; ?>"
                class="<?php echo $idx === 0 ? 'active' : ''; ?>" />
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="info">
        <span class="state-badge <?php echo esc_attr($vstate); ?>"><?php echo esc_html($state_label); ?></span>
        <?php if ($vstore): ?><span class="store-badge"><?php echo esc_html($vstore['name']); ?></span><?php endif; ?>
        <h1><?php echo esc_html($title); ?></h1>
        <?php if ($price): ?><p class="price">$<?php echo esc_html(number_format((float) $price, 0)); ?></p><?php endif; ?>
        <dl class="specs">
          <?php foreach (['stockNumber' => 'Stock #', 'year' => 'Year', 'make' => 'Make', 'model' => 'Model', 'submodel' => 'Trim', 'color' => 'Color', 'vin' => 'VIN', 'category' => 'Category'] as $k => $label):
            if (!empty($vehicle[$k])): ?>
              <dt><?php echo esc_html($label); ?></dt>
              <dd><?php echo esc_html($vehicle[$k]); ?></dd>
          <?php endif; endforeach; ?>
        </dl>

        <div class="cta-row">
          <?php if ($vstore): ?>
            <a class="btn primary" href="tel:<?php echo esc_attr($vstore['phone_tel']); ?>">Call <?php echo esc_html($vstore['phone']); ?></a>
          <?php endif; ?>
          <a class="btn secondary" href="<?php echo esc_url(home_url('/financing/?vehicle=' . rawurlencode($vehicle['id']))); ?>">Apply for Financing</a>
          <button type="button" class="btn tertiary" data-chat-open="sales">Chat with Us</button>
        </div>

        <?php if (!empty($vehicle['description'])): ?>
          <section class="desc">
            <h2>Description</h2>
            <div><?php echo wp_kses_post($vehicle['description']); ?></div>
          </section>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<?php
// BreadcrumbList + Product/Vehicle JSON-LD
if (!empty($vehicle)) {
    $canonical = home_url('/stores/' . ($vstore['slug'] ?? 'morden') . '/inventory/' . ($vehicle['stockNumber'] ?? $vehicle['id']) . '/');
    $cover_img = !empty($images[0]['url']) ? bl_inventory_image_url($images[0]['url']) : '';
    $seller_ref = !empty($vstore['slug']) ? BORDERLAND_SITE_URL . '/stores/' . $vstore['slug'] . '#business' : null;

    $gallery = [];
    foreach ($images as $img) {
        $u = bl_inventory_image_url($img['url'] ?? '');
        if ($u) $gallery[] = $u;
    }

    // Breadcrumb
    if ($vstore) {
        bl_emit_breadcrumb_jsonld([
            ['Home', home_url('/')],
            [$vstore['name'], home_url('/stores/' . $vstore['slug'])],
            ['Inventory', home_url('/stores/' . $vstore['slug'] . '/inventory/')],
            [$title],
        ]);
    }

    // Skip Product/Vehicle schema entirely if no image — Google flags image-less products as critical
    if (!$cover_img) {
        get_footer();
        return;
    }

    // Combined @graph: Product (for shopping/rich results) + Vehicle (for vehicle-specific)
    $product = [
        '@type'    => 'Product',
        '@id'      => $canonical . '#product',
        'name'     => $title,
        'url'      => $canonical,
        'brand'    => ['@type' => 'Brand', 'name' => $vehicle['make'] ?? ''],
        'category' => ucfirst($vehicle['category'] ?? 'Vehicle'),
    ];
    if (!empty($vehicle['stockNumber'])) $product['sku']        = $vehicle['stockNumber'];
    if (!empty($vehicle['stockNumber'])) $product['productID']  = $vehicle['stockNumber'];
    if (!empty($vehicle['model']))       $product['model']      = $vehicle['model'];
    if ($cover_img)                      $product['image']      = $gallery ?: $cover_img;

    // description — prefer feed copy, else compose from year/make/model/category/store
    $desc_raw = trim((string) ($vehicle['description'] ?? ''));
    if ($desc_raw === '') {
        $parts = array_filter([
            $vehicle['year']     ?? null,
            $vehicle['make']     ?? null,
            $vehicle['model']    ?? null,
        ]);
        $cat   = !empty($vehicle['category']) ? ucfirst($vehicle['category']) : 'powersports vehicle';
        $where = !empty($vstore['full_name']) ? $vstore['full_name'] . ' in ' . $vstore['city'] . ', MB' : 'Borderland Powersports Manitoba';
        $desc_raw = trim(implode(' ', $parts)) . ' ' . $cat . ' available at ' . $where . '.';
    }
    $product['description'] = wp_strip_all_tags($desc_raw);

    if ($price) {
        // Canada-wide in-store pickup. No paid shipping offered — dealer delivery quoted on request.
        $shipping = [
            '@type' => 'OfferShippingDetails',
            'shippingRate' => [
                '@type'         => 'MonetaryAmount',
                'value'         => '0',
                'currency'      => 'CAD',
            ],
            'shippingDestination' => [
                '@type'              => 'DefinedRegion',
                'addressCountry'     => 'CA',
            ],
            'deliveryTime' => [
                '@type'              => 'ShippingDeliveryTime',
                'handlingTime'       => ['@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 1, 'unitCode' => 'DAY'],
                'transitTime'        => ['@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 0, 'unitCode' => 'DAY'],
            ],
        ];

        // Powersports vehicles are non-returnable once sold / registered.
        $return_policy = [
            '@type'                 => 'MerchantReturnPolicy',
            'applicableCountry'     => 'CA',
            'returnPolicyCategory'  => 'https://schema.org/MerchantReturnNotPermitted',
        ];

        $product['offers'] = [
            '@type'                  => 'Offer',
            'price'                  => $price,
            'priceCurrency'          => 'CAD',
            'url'                    => $canonical,
            'availability'           => 'https://schema.org/InStock',
            'itemCondition'          => ($vstate === 'new') ? 'https://schema.org/NewCondition' : 'https://schema.org/UsedCondition',
            'seller'                 => $seller_ref
                ? ['@id' => $seller_ref]
                : ['@type' => 'AutomotiveBusiness', 'name' => $vstore['full_name'] ?? BORDERLAND_SITE_NAME],
            'shippingDetails'        => $shipping,
            'hasMerchantReturnPolicy'=> $return_policy,
        ];
    }
    if (!empty($vehicle['vin']))  $product['gtin']         = $vehicle['vin'];

    // Vehicle node (more specific, for Google Vehicle listings)
    $vehicle_node = [
        '@type'    => 'Vehicle',
        '@id'      => $canonical . '#vehicle',
        'name'     => $title,
        'url'      => $canonical,
        'brand'    => ['@type' => 'Brand', 'name' => $vehicle['make'] ?? ''],
    ];
    if (!empty($vehicle['stockNumber']))       $vehicle_node['sku']                         = $vehicle['stockNumber'];
    if (!empty($vehicle['vin']))               $vehicle_node['vehicleIdentificationNumber'] = $vehicle['vin'];
    if (!empty($vehicle['year']))              $vehicle_node['vehicleModelDate']            = (string) $vehicle['year'];
    if (!empty($vehicle['model']))             $vehicle_node['model']                       = $vehicle['model'];
    if (!empty($vehicle['color']))             $vehicle_node['color']                       = $vehicle['color'];
    if (!empty($vehicle['category']))          $vehicle_node['bodyType']                    = ucfirst($vehicle['category']);
    if (!empty($vehicle['engineDisplacement'])) $vehicle_node['vehicleEngine']              = ['@type' => 'EngineSpecification', 'engineDisplacement' => $vehicle['engineDisplacement']];
    if (isset($vehicle['mileage']) && $vstate !== 'new') {
        $vehicle_node['mileageFromOdometer'] = ['@type' => 'QuantitativeValue', 'value' => (int) $vehicle['mileage'], 'unitCode' => 'KMT'];
    }
    if ($cover_img) $vehicle_node['image'] = $gallery ?: $cover_img;
    if ($price) $vehicle_node['offers'] = $product['offers'] ?? null;
    $vehicle_node = array_filter($vehicle_node, fn($v) => $v !== null);

    $payload = [
        '@context' => 'https://schema.org',
        '@graph'   => [$product, $vehicle_node],
    ];
    echo '<script type="application/ld+json">' . wp_json_encode($payload, JSON_UNESCAPED_SLASHES) . '</script>';
}
get_footer();
