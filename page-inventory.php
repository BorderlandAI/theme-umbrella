<?php
/**
 * Template Name: Inventory (dual-mode, Portage light-theme layout)
 *
 * Renders either a single-store inventory (/stores/{slug}/inventory/)
 * or the unified /inventory/ across all stores with a store chip filter.
 *
 * Server-side paginated (48/page) so initial HTML stays small and crawlers
 * can walk every page. Filters are GET params; filter selects auto-submit.
 * Sort + stock-number search happen within the currently rendered page.
 */
get_header();

$store  = bl_current_store();
$state  = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : null;
$type_q = isset($_GET['type'])  ? sanitize_text_field($_GET['type'])  : null;
$make_q = isset($_GET['make'])  ? sanitize_text_field($_GET['make'])  : null;
$sort_q = isset($_GET['sort'])  ? sanitize_text_field($_GET['sort'])  : 'price-asc';
$page   = max(1, (int) ($_GET['pg'] ?? 1));

$per_page = 48;

$args = array_filter([
    'state' => $state,
    'type'  => $type_q,
    'make'  => $make_q,
]);

$units = bl_get_inventory($store['slug'] ?? null, $args);
$units = array_values($units);

// Server-side sort
usort($units, function($a, $b) use ($sort_q) {
    $pa = (float)($a['salePrice'] ?? $a['basePrice'] ?? 0);
    $pb = (float)($b['salePrice'] ?? $b['basePrice'] ?? 0);
    switch ($sort_q) {
        case 'price-desc': return $pb <=> $pa;
        case 'name-asc':
            $na = strtolower(($a['year'] ?? '') . ' ' . ($a['make'] ?? '') . ' ' . ($a['model'] ?? ''));
            $nb = strtolower(($b['year'] ?? '') . ' ' . ($b['make'] ?? '') . ' ' . ($b['model'] ?? ''));
            return strcmp($na, $nb);
        case 'price-asc':
        default:
            // Units without price float to the bottom in asc mode
            $pa = $pa ?: PHP_INT_MAX;
            $pb = $pb ?: PHP_INT_MAX;
            return $pa <=> $pb;
    }
});

$total     = count($units);
$num_pages = max(1, (int) ceil($total / $per_page));
if ($page > $num_pages) $page = $num_pages;
$offset    = ($page - 1) * $per_page;
$slice     = array_slice($units, $offset, $per_page);

// Collect unique makes across ALL units (not just current page) for the filter dropdown
$makes = [];
foreach ($units as $u) {
    if (!empty($u['make'])) $makes[$u['make']] = true;
}
ksort($makes);

$all_categories = [
    'atv'        => 'ATVs',
    'utv'        => 'Side-by-Sides',
    'dirt bike'  => 'Dirt Bikes',
    'pwc'        => 'Jet Skis',
    'snowmobile' => 'Snowmobiles',
    'boat'       => 'Boats',
    'outboard motor' => 'Outboards',
    'trailer'    => 'Trailers',
    'other'      => 'Other',
];

$page_title = $store ? ($store['name'] . ' Inventory') : 'All Borderland Inventory';
$page_lede  = $store
    ? ($store['full_name'] . ' — live inventory. Every unit stocked at ' . $store['name'] . ', sorted, filterable, and priced.')
    : 'Live inventory from Brandon, Morden, Portage la Prairie, and Thompson. Filter by location, make, or model.';

// Build a base URL for pagination + canonical pointers.
$base_path = $store ? '/stores/' . $store['slug'] . '/inventory/' : '/inventory/';
$qs_base   = array_filter([
    'state' => $state,
    'type'  => $type_q,
    'make'  => $make_q,
    'sort'  => $sort_q !== 'price-asc' ? $sort_q : null,
]);
$page_url = function($n) use ($base_path, $qs_base) {
    $q = $qs_base;
    if ($n > 1) $q['pg'] = $n;
    return home_url($base_path . ($q ? '?' . http_build_query($q) : ''));
};
?>

<?php
// Prev/Next + canonical link hints for SEO (emitted after wp_head ran, so we inject via wp_head hook would be late — inline <link> here is acceptable for pagination hints)
if ($num_pages > 1) {
    if ($page > 1)              echo '<link rel="prev" href="' . esc_url($page_url($page - 1)) . '" />' . "\n";
    if ($page < $num_pages)     echo '<link rel="next" href="' . esc_url($page_url($page + 1)) . '" />' . "\n";
}
?>

<?php
if ($store) {
    bl_emit_breadcrumb_jsonld([
        ['Home', home_url('/')],
        [$store['full_name'], home_url('/stores/' . $store['slug'])],
        ['Inventory'],
    ]);
} else {
    bl_emit_breadcrumb_jsonld([
        ['Home', home_url('/')],
        ['Inventory'],
    ]);
}
?>
<main id="content" class="bl-inventory-page <?php echo $store ? 'scoped' : 'unified'; ?>">

  <section class="bl-hero small">
    <div class="inner">
      <h1><?php echo esc_html($page_title); ?></h1>
      <p class="lede"><?php echo esc_html($page_lede); ?></p>
    </div>
  </section>

  <div class="inner" style="padding-top:24px;">
    <?php echo bl_inventory_degraded_notice(); ?>

    <?php if (!$store): ?>
      <nav class="store-chips" aria-label="Filter by store">
        <a class="chip active" href="<?php echo esc_url(home_url('/inventory/')); ?>">All Locations</a>
        <?php foreach (bl_stores() as $s): ?>
          <a class="chip" href="<?php echo esc_url(home_url('/stores/' . $s['slug'] . '/inventory/')); ?>"><?php echo esc_html($s['name']); ?></a>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>

    <?php if (empty($units)): ?>
      <div class="inv-empty">
        <h2>Contact Us for Current Inventory</h2>
        <p>Don't see what you're looking for? We can order it in or check our other locations.</p>
        <a href="<?php echo esc_url(home_url($store ? '/stores/' . $store['slug'] . '/contact/' : '/contact/')); ?>" class="inv-btn">Contact Us</a>
      </div>
    <?php else: ?>

      <!-- FILTER BAR (GET form; selects auto-submit) -->
      <form id="invFilters" method="get" action="<?php echo esc_url(home_url($base_path)); ?>">
        <div class="cf-label">
          <svg width="16" height="14" viewBox="0 0 16 14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><path d="M2 3h12M4 7h8M6 11h4"/></svg>
          Filter by:
        </div>
        <div class="cf-fields">
          <div class="cf-field">
            <select name="state">
              <option value="">New &amp; Used</option>
              <option value="new" <?php selected($state, 'new'); ?>>New</option>
              <option value="used" <?php selected($state, 'used'); ?>>Pre-Owned</option>
            </select>
          </div>
          <div class="cf-field">
            <select name="type">
              <option value="">By type</option>
              <?php foreach ($all_categories as $cat_key => $cat_label): ?>
                <option value="<?php echo esc_attr($cat_key); ?>" <?php selected($type_q, $cat_key); ?>><?php echo esc_html($cat_label); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if (count($makes) > 1): ?>
            <div class="cf-field">
              <select name="make">
                <option value="">By make</option>
                <?php foreach ($makes as $m => $_): ?>
                  <option value="<?php echo esc_attr(strtolower($m)); ?>" <?php selected(strtolower($make_q ?? ''), strtolower($m)); ?>><?php echo esc_html($m); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>
          <div class="cf-field">
            <select name="sort">
              <option value="price-asc"  <?php selected($sort_q, 'price-asc'); ?>>Price: Low to High</option>
              <option value="price-desc" <?php selected($sort_q, 'price-desc'); ?>>Price: High to Low</option>
              <option value="name-asc"   <?php selected($sort_q, 'name-asc'); ?>>Name A&ndash;Z</option>
            </select>
          </div>
        </div>
        <div class="cf-search">
          <input type="text" id="invSearch" name="q" placeholder="Stock # (this page)" value="" />
          <button type="submit" class="cf-search-btn" aria-label="Apply filters">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          </button>
        </div>
      </form>

      <div id="invCount">
        Showing <strong><?php echo esc_html(($offset + 1) . '–' . min($offset + $per_page, $total)); ?></strong>
        of <?php echo (int) $total; ?> units
        <?php if ($num_pages > 1): ?>(page <?php echo (int) $page; ?> of <?php echo (int) $num_pages; ?>)<?php endif; ?>
      </div>

      <div id="invGrid" class="inv-grid">
        <?php $card_i = 0; foreach ($slice as $u):
          $title = trim(($u['year'] ?? '') . ' ' . ($u['make'] ?? '') . ' ' . ($u['submodel'] ?? $u['model'] ?? ''));
          $color = !empty($u['color']) ? ' - ' . $u['color'] : '';
          $price = !empty($u['salePrice']) ? $u['salePrice'] : (!empty($u['basePrice']) ? $u['basePrice'] : null);
          $price_display = $price ? '$' . number_format((float)$price, 0) : 'Contact Us';
          $has_sale = !empty($u['salePrice']) && !empty($u['basePrice']) && $u['salePrice'] < $u['basePrice'];

          $card_img_url = bl_vehicle_cover_image($u);
          $has_image    = !empty($card_img_url);

          $vstore_slug = strtolower($u['store'] ?? '');
          $vstore      = bl_store($vstore_slug);
          $stock = $u['stockNumber'] ?? $u['id'];
          $href  = $vstore_slug && $stock ? home_url('/stores/' . $vstore_slug . '/inventory/' . rawurlencode($stock)) : '#';

          // First 4 images eager + fetchpriority for LCP; rest lazy.
          $is_lcp = $card_i < 4;
          $loading_attr  = $is_lcp ? 'eager' : 'lazy';
          $priority_attr = $is_lcp ? ' fetchpriority="high"' : '';
          $card_i++;
        ?>
          <a class="inv-card"
             href="<?php echo esc_url($href); ?>"
             data-name="<?php echo esc_attr(strtolower($title)); ?>"
             data-stock="<?php echo esc_attr(strtolower($stock)); ?>">
            <?php if ($has_image): ?>
              <div class="inv-card-img">
                <img src="<?php echo esc_url($card_img_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="<?php echo $loading_attr; ?>"<?php echo $priority_attr; ?> width="220" height="200" />
              </div>
            <?php else: ?>
              <div class="inv-card-img inv-card-noimg" aria-label="Photo coming soon">
                <svg width="48" height="48" fill="none" stroke="#999" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" stroke-width="1.5"/><circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.5"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" stroke-width="1.5" stroke-linecap="round"/></svg>
                <span>Photo Coming Soon</span>
              </div>
            <?php endif; ?>
            <div class="inv-card-body">
              <?php if (!$store && $vstore): ?>
                <span class="store-badge"><?php echo esc_html($vstore['name']); ?></span>
              <?php endif; ?>
              <h3 class="inv-card-title"><?php echo esc_html(strtoupper($title . $color)); ?></h3>
              <?php if (!empty($u['stockNumber'])): ?>
                <p class="inv-card-stock">Stock #: <?php echo esc_html($u['stockNumber']); ?></p>
              <?php endif; ?>
              <div class="inv-card-price<?php echo $has_sale ? ' has-sale' : ''; ?>">
                <?php if ($has_sale): ?>
                  <span class="inv-price-was">$<?php echo number_format((float)$u['basePrice'], 0); ?></span>
                <?php endif; ?>
                <span class="inv-price-current"><?php echo esc_html($price_display); ?></span>
              </div>
              <span class="inv-btn-details">More Info</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if ($num_pages > 1): ?>
      <nav id="invPagination" aria-label="Inventory pages">
        <?php if ($page > 1): ?>
          <a rel="prev" href="<?php echo esc_url($page_url($page - 1)); ?>">&larr; Prev</a>
        <?php else: ?>
          <span class="disabled">&larr; Prev</span>
        <?php endif; ?>

        <?php
        // Compact pager: first, last, +/- 2 around current.
        $shown = [];
        $push  = function($n) use (&$shown, $num_pages) {
            if ($n < 1 || $n > $num_pages) return;
            $shown[$n] = true;
        };
        $push(1); $push($num_pages);
        for ($i = $page - 2; $i <= $page + 2; $i++) $push($i);
        ksort($shown);
        $prev = 0;
        foreach (array_keys($shown) as $n) {
            if ($prev && $n > $prev + 1) echo '<span class="gap">…</span>';
            if ($n === $page) {
                echo '<span class="current">' . (int) $n . '</span>';
            } else {
                echo '<a href="' . esc_url($page_url($n)) . '">' . (int) $n . '</a>';
            }
            $prev = $n;
        }
        ?>

        <?php if ($page < $num_pages): ?>
          <a rel="next" href="<?php echo esc_url($page_url($page + 1)); ?>">Next &rarr;</a>
        <?php else: ?>
          <span class="disabled">Next &rarr;</span>
        <?php endif; ?>
      </nav>
      <?php endif; ?>

      <div id="invNoResults">
        <h2>No units match your search</h2>
        <p>Try adjusting your search or filters above.</p>
      </div>

      <div class="inv-cta">
        <p>Don't see what you're looking for? We can order it in or check our other locations.</p>
        <a href="<?php echo esc_url(home_url($store ? '/stores/' . $store['slug'] . '/contact/' : '/contact/')); ?>" class="inv-btn">Contact Us</a>
      </div>
    <?php endif; ?>
  </div>

</main>

<?php get_footer();
