<?php
/**
 * Template Name: Inventory (dual-mode)
 * Store-scoped via /stores/{slug}/inventory or unified via /inventory.
 */
get_header();

$store = bl_current_store();
$state = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : null; // new|used
$type  = isset($_GET['type'])  ? sanitize_text_field($_GET['type'])  : null;
$make  = isset($_GET['make'])  ? sanitize_text_field($_GET['make'])  : null;
$q     = isset($_GET['q'])     ? sanitize_text_field($_GET['q'])     : '';

$args = array_filter([
    'state' => $state,
    'type'  => $type,
    'make'  => $make,
]);

$units = bl_get_inventory($store['slug'] ?? null, $args);
$units = array_values($units);

// Collect makes + stores present in result set
$makes = [];
$stores_in_results = [];
foreach ($units as $u) {
    if (!empty($u['make']))  $makes[$u['make']] = true;
    if (!empty($u['store'])) $stores_in_results[strtolower($u['store'])] = true;
}
ksort($makes);

$title = $store ? ($store['name'] . ' Inventory') : 'All Inventory';
?>

<main id="content" class="bl-inventory <?php echo $store ? 'scoped' : 'unified'; ?>">
  <section class="bl-hero small">
    <div class="inner">
      <h1><?php echo esc_html($title); ?></h1>
      <?php if ($store): ?>
        <p class="lede"><?php echo esc_html($store['full_name']); ?> — <?php echo esc_html($store['address'] ? $store['address'] . ', ' : '') . esc_html($store['city'] . ', ' . $store['region']); ?></p>
      <?php else: ?>
        <p class="lede">Live inventory across all 4 locations. Filter by store, make, or type.</p>
      <?php endif; ?>
    </div>
  </section>

  <div class="inner">
    <?php echo bl_inventory_degraded_notice(); ?>

    <?php if (!$store): ?>
      <nav class="store-chips" aria-label="Filter by store">
        <a class="chip <?php echo empty($_GET['store']) ? 'active' : ''; ?>" href="<?php echo esc_url(home_url('/inventory/')); ?>">All</a>
        <?php foreach (bl_stores() as $s): ?>
          <a class="chip" href="<?php echo esc_url(home_url('/stores/' . $s['slug'] . '/inventory/')); ?>"><?php echo esc_html($s['name']); ?></a>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>

    <div id="invFilters">
      <div class="cf-field cf-search">
        <input type="text" id="invSearch" placeholder="Search make, model, year, stock #…" />
      </div>
      <div class="cf-field">
        <select id="invState">
          <option value="">All Condition</option>
          <option value="new" <?php selected($state, 'new'); ?>>New</option>
          <option value="used" <?php selected($state, 'used'); ?>>Pre-Owned</option>
        </select>
      </div>
      <?php if (count($makes) > 1): ?>
        <div class="cf-field">
          <select id="invMake">
            <option value="">All Makes</option>
            <?php foreach (array_keys($makes) as $m): ?>
              <option value="<?php echo esc_attr($m); ?>"><?php echo esc_html($m); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
      <div class="cf-field">
        <select id="invSort">
          <option value="featured">Featured</option>
          <option value="priceAsc">Price: Low → High</option>
          <option value="priceDesc">Price: High → Low</option>
          <option value="yearDesc">Year: New → Old</option>
        </select>
      </div>
    </div>

    <div id="invGrid" class="inv-grid">
      <?php if (empty($units)): ?>
        <div class="inv-empty"><p>No inventory matches the current filters. Try clearing filters or <a href="<?php echo esc_url(home_url('/contact/')); ?>">contact us</a> for help.</p></div>
      <?php else: foreach ($units as $v):
        $img = bl_vehicle_cover_image($v);
        $vtitle = trim(($v['year'] ?? '') . ' ' . ($v['make'] ?? '') . ' ' . ($v['submodel'] ?? $v['model'] ?? ''));
        $price  = !empty($v['salePrice']) ? $v['salePrice'] : ($v['basePrice'] ?? null);
        $vstate = strtolower($v['state'] ?? '');
        $vmake  = $v['make'] ?? '';
        $vstore_slug = strtolower($v['store'] ?? '');
        $vstore      = bl_store($vstore_slug);
        $vstock = $v['stockNumber'] ?? $v['id'];
        $href   = $vstore_slug && $vstock ? home_url('/stores/' . $vstore_slug . '/inventory/' . rawurlencode($vstock)) : '#';
        ?>
        <a class="inv-card"
           href="<?php echo esc_url($href); ?>"
           data-title="<?php echo esc_attr(strtolower($vtitle . ' ' . $vstock)); ?>"
           data-make="<?php echo esc_attr($vmake); ?>"
           data-state="<?php echo esc_attr($vstate); ?>"
           data-price="<?php echo esc_attr((float) ($price ?: 0)); ?>"
           data-year="<?php echo esc_attr((int) ($v['year'] ?? 0)); ?>">
          <?php if ($img): ?><div class="img" style="background-image:url('<?php echo esc_url($img); ?>')"></div><?php endif; ?>
          <div class="body">
            <?php if (!$store && $vstore): ?><span class="store-badge"><?php echo esc_html($vstore['name']); ?></span><?php endif; ?>
            <span class="state-badge <?php echo esc_attr($vstate); ?>"><?php echo $vstate === 'new' ? 'New' : 'Pre-Owned'; ?></span>
            <h3><?php echo esc_html($vtitle); ?></h3>
            <?php if ($price): ?><p class="price">$<?php echo esc_html(number_format((float) $price, 0)); ?></p><?php endif; ?>
            <p class="stock">Stock #<?php echo esc_html($vstock); ?></p>
          </div>
        </a>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <script>
  (function(){
    var grid = document.getElementById('invGrid');
    if (!grid) return;
    var q = document.getElementById('invSearch');
    var m = document.getElementById('invMake');
    var s = document.getElementById('invState');
    var sort = document.getElementById('invSort');
    function apply(){
      var qv = (q.value || '').toLowerCase().trim();
      var mv = m ? m.value : '';
      var sv = s ? s.value : '';
      var cards = Array.prototype.slice.call(grid.querySelectorAll('.inv-card'));
      cards.forEach(function(c){
        var show = true;
        if (qv && c.dataset.title.indexOf(qv) === -1) show = false;
        if (mv && c.dataset.make !== mv) show = false;
        if (sv && c.dataset.state !== sv) show = false;
        c.style.display = show ? '' : 'none';
      });
      if (sort) {
        var mode = sort.value;
        var sorted = cards.slice().sort(function(a,b){
          if (mode === 'priceAsc')  return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
          if (mode === 'priceDesc') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
          if (mode === 'yearDesc')  return parseInt(b.dataset.year) - parseInt(a.dataset.year);
          return 0;
        });
        sorted.forEach(function(c){ grid.appendChild(c); });
      }
    }
    [q, m, s, sort].forEach(function(el){ if (el) el.addEventListener('input', apply); });
  })();
  </script>
</main>

<?php get_footer();
