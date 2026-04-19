<?php
/**
 * Template Name: Inventory (dual-mode, Portage light-theme layout)
 *
 * Renders either a single-store inventory (/stores/{slug}/inventory/)
 * or the unified /inventory/ across all stores with a store chip filter.
 *
 * Adapted from the Portage theme's page-inventory.php — same filter
 * bar / sort / pagination / card shape, extended with store-aware
 * URLs + cross-store chip filtering.
 */
get_header();

$store = bl_current_store();
$state = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : null;
$type_q = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : null;
$make_q = isset($_GET['make']) ? sanitize_text_field($_GET['make']) : null;

$args = array_filter([
    'state' => $state,
    'type'  => $type_q,
    'make'  => $make_q,
]);

$units = bl_get_inventory($store['slug'] ?? null, $args);
$units = array_values($units);
$total = count($units);

// Collect unique makes for filter
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
        <a class="chip <?php echo empty($_GET['store']) ? 'active' : ''; ?>" href="<?php echo esc_url(home_url('/inventory/')); ?>">All Locations</a>
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

      <!-- FILTER BAR -->
      <div id="invFilters">
        <div class="cf-label">
          <svg width="16" height="14" viewBox="0 0 16 14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><path d="M2 3h12M4 7h8M6 11h4"/></svg>
          Filter by:
        </div>
        <div class="cf-fields">
          <div class="cf-field">
            <select id="invState">
              <option value="">New &amp; Used</option>
              <option value="new" <?php selected($state, 'new'); ?>>New</option>
              <option value="used" <?php selected($state, 'used'); ?>>Pre-Owned</option>
            </select>
          </div>
          <div class="cf-field">
            <select id="invCategory">
              <option value="">By type</option>
              <?php foreach ($all_categories as $cat_key => $cat_label): ?>
                <option value="<?php echo esc_attr($cat_key); ?>"><?php echo esc_html($cat_label); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if (count($makes) > 1): ?>
            <div class="cf-field">
              <select id="invMake">
                <option value="">By make</option>
                <?php foreach ($makes as $m => $_): ?>
                  <option value="<?php echo esc_attr(strtolower($m)); ?>"><?php echo esc_html($m); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>
          <div class="cf-field">
            <select id="invSort">
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
              <option value="name-asc">Name A&ndash;Z</option>
            </select>
          </div>
        </div>
        <div class="cf-search">
          <input type="text" id="invSearch" placeholder="Stock #" />
          <button type="button" class="cf-search-btn" aria-label="Search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          </button>
        </div>
      </div>

      <div id="invCount">
        Showing <strong id="invCountVisible"><?php echo $total; ?></strong>
        <span id="invCountTotal"> of <?php echo $total; ?></span> units
      </div>

      <div id="invGrid" class="inv-grid">
        <?php foreach ($units as $u):
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
        ?>
          <a class="inv-card"
             href="<?php echo esc_url($href); ?>"
             data-name="<?php echo esc_attr(strtolower($title)); ?>"
             data-stock="<?php echo esc_attr(strtolower($stock)); ?>"
             data-make="<?php echo esc_attr(strtolower($u['make'] ?? '')); ?>"
             data-category="<?php echo esc_attr(strtolower($u['type'] ?? '')); ?>"
             data-state="<?php echo esc_attr(strtolower($u['state'] ?? '')); ?>"
             data-store="<?php echo esc_attr($vstore_slug); ?>"
             data-price="<?php echo esc_attr($price ?: 0); ?>"
             data-year="<?php echo esc_attr((int)($u['year'] ?? 0)); ?>">
            <?php if ($has_image): ?>
              <div class="inv-card-img">
                <img src="<?php echo esc_url($card_img_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" />
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

      <div id="invPagination">
        <button id="invPrevPage">&larr; Prev</button>
        <span id="invPageInfo"></span>
        <button id="invNextPage">Next &rarr;</button>
      </div>

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

<style>
  /* =============== INVENTORY PAGE — light theme =============== */
  .bl-inventory-page { background:#fff; min-height:60vh; }
  .bl-inventory-page .inv-empty, .bl-inventory-page .inv-cta, .bl-inventory-page #invNoResults {
    text-align:center; padding:40px 20px; background:#fafafa; border:1px solid #ddd; border-radius:4px; margin:20px 0;
  }
  .bl-inventory-page .inv-empty h2, .bl-inventory-page #invNoResults h2 { color:#222; margin-bottom:10px; font-weight:700; }
  .bl-inventory-page .inv-empty p, .bl-inventory-page .inv-cta p, .bl-inventory-page #invNoResults p { color:#666; font-size:15px; margin:0 0 16px; }
  .bl-inventory-page #invNoResults { display:none; }
  .bl-inventory-page .inv-btn {
    display:inline-block; padding:10px 24px; background:#111; color:#fff !important;
    text-decoration:none; border:1px solid #111; border-radius:3px;
    font-weight:700; font-size:13px; letter-spacing:0.8px; text-transform:uppercase;
    transition:background .2s;
  }
  .bl-inventory-page .inv-btn:hover { background:#000; }

  /* FILTER BAR */
  #invFilters {
    background:#fff; border-bottom:1px solid #e5e5e5;
    padding:14px 0 18px; margin-bottom:22px;
    display:flex; flex-wrap:wrap; gap:10px; align-items:center;
  }
  .cf-label {
    display:inline-flex; align-items:center; gap:6px;
    font-size:11px; font-weight:700; letter-spacing:1.2px; text-transform:uppercase;
    color:#666; padding-right:6px;
  }
  .cf-fields { display:flex; flex-wrap:wrap; gap:8px; flex:1; }
  .cf-field { flex:0 1 165px; min-width:130px; }
  .cf-search { display:flex; align-items:stretch; gap:0; margin-left:auto; }
  .cf-search input {
    width:160px; padding:8px 12px; border:1px solid #ccc; border-right:none;
    border-radius:3px 0 0 3px; font-size:14px; font-family:inherit; color:#222;
    outline:none; background:#fff; box-sizing:border-box;
  }
  .cf-search-btn {
    padding:0 14px; background:#111; color:#fff; border:1px solid #111;
    border-radius:0 3px 3px 0; cursor:pointer;
    display:inline-flex; align-items:center; justify-content:center;
  }
  .cf-search-btn:hover { background:#000; }
  #invFilters select {
    width:100%; padding:8px 28px 8px 12px; border:1px solid #ccc; border-radius:3px;
    font-size:14px; font-family:inherit; color:#222; outline:none; cursor:pointer;
    box-sizing:border-box; -webkit-appearance:none; appearance:none;
    background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%23666' fill='none' stroke-width='1.4'/%3E%3C/svg%3E") no-repeat right 10px center;
  }
  #invFilters input::placeholder { color:#999; }
  #invFilters input:focus, #invFilters select:focus { border-color:#888; }

  /* COUNT */
  #invCount { margin-bottom:14px; font-size:13px; color:#888; }
  #invCount strong { color:#222; font-weight:700; }

  /* GRID */
  .bl-inventory-page .inv-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:20px; margin:0 0 24px; }
  .bl-inventory-page .inv-card {
    display:flex; border:1px solid #ddd; border-radius:4px; overflow:hidden;
    background:#fff; transition:box-shadow .2s; color:#222 !important;
    text-decoration:none !important;
  }
  .bl-inventory-page .inv-card:hover { box-shadow:0 2px 12px rgba(0,0,0,0.12); }
  .bl-inventory-page .inv-card-img {
    flex:0 0 220px; height:200px; overflow:hidden; background:#f0f0f0;
    display:flex; align-items:center; justify-content:center; aspect-ratio:auto;
  }
  .bl-inventory-page .inv-card-img img { width:100%; height:100%; object-fit:cover; }
  .bl-inventory-page .inv-card-noimg { background:#e8e8e8 !important; flex-direction:column; gap:8px; color:#999; font-size:13px; }
  .bl-inventory-page .inv-card-body {
    flex:1; padding:16px 20px; display:flex; flex-direction:column; justify-content:center;
  }
  .bl-inventory-page .inv-card-title {
    margin:4px 0 10px; font-size:15px; line-height:1.35; color:#222;
    font-weight:700; text-transform:uppercase;
  }
  .bl-inventory-page .inv-card-stock {
    color:#666; font-size:13px; font-style:italic;
    margin:0 0 8px; padding-bottom:8px; border-bottom:1px solid #ddd;
  }
  .bl-inventory-page .inv-card-price { margin-bottom:12px; }
  .bl-inventory-page .inv-price-current { font-size:20px; font-weight:800; color:#000; }
  .bl-inventory-page .inv-price-was {
    font-size:16px; font-weight:600; color:#999;
    text-decoration:line-through; margin-right:8px;
  }
  .bl-inventory-page .has-sale .inv-price-current { color:#16a34a; }
  .bl-inventory-page .inv-btn-details {
    display:inline-block; padding:10px 28px; text-decoration:none !important;
    border:none; border-radius:3px; font-size:13px; font-weight:700;
    letter-spacing:0.8px; text-transform:uppercase; transition:background .2s;
    background:#111; color:#fff !important; align-self:flex-start;
  }
  .bl-inventory-page .inv-card:hover .inv-btn-details { background:#000; }

  /* store chip row (unified mode) */
  .bl-inventory-page .store-chips { padding:0 0 16px; justify-content:center; }

  /* PAGINATION */
  #invPagination { display:none; align-items:center; justify-content:center; gap:16px; margin:4px 0 20px; }
  #invPagination button {
    padding:8px 18px; background:#333; color:#eee; border:1px solid #444;
    border-radius:4px; font-size:14px; font-weight:600; cursor:pointer;
    transition:background .2s;
  }
  #invPagination button:hover:not(:disabled) { background:#444; }
  #invPagination button:disabled { opacity:0.4; cursor:default; }
  #invPageInfo { font-size:14px; color:#666; min-width:100px; text-align:center; }

  /* RESPONSIVE */
  @media (max-width:900px) { .bl-inventory-page .inv-grid { grid-template-columns:1fr; } }
  @media (max-width:680px) {
    .bl-inventory-page .inv-card { flex-direction:column; }
    .bl-inventory-page .inv-card-img { flex:none; width:100%; height:200px; }
    #invFilters { padding:10px 0 12px; gap:8px; }
    .cf-fields { flex:1 1 100%; }
    .cf-field { flex:1 1 calc(50% - 4px); min-width:0; }
    .cf-search { width:100%; margin-left:0; }
    .cf-search input { flex:1; }
  }
</style>

<script>
(function() {
  var PAGE_SIZE = 12;
  var currentPage = 1;

  var search     = document.getElementById('invSearch');
  var stateFilt  = document.getElementById('invState');
  var makeFilter = document.getElementById('invMake');
  var catFilter  = document.getElementById('invCategory');
  var sort       = document.getElementById('invSort');
  var grid       = document.getElementById('invGrid');
  var countVis   = document.getElementById('invCountVisible');
  var countTotal = document.getElementById('invCountTotal');
  var noResults  = document.getElementById('invNoResults');
  var pagination = document.getElementById('invPagination');
  var prevBtn    = document.getElementById('invPrevPage');
  var nextBtn    = document.getElementById('invNextPage');
  var pageInfo   = document.getElementById('invPageInfo');
  if (!grid || !search) return;

  var cards = Array.prototype.slice.call(grid.querySelectorAll('.inv-card'));
  cards.forEach(function(c) { c._match = true; });

  function applySort() {
    var s = sort.value;
    cards.sort(function(a, b) {
      switch (s) {
        case 'name-asc':   return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
        case 'price-asc':  return (parseFloat(a.getAttribute('data-price')) || 999999) - (parseFloat(b.getAttribute('data-price')) || 999999);
        case 'price-desc': return (parseFloat(b.getAttribute('data-price')) || 0) - (parseFloat(a.getAttribute('data-price')) || 0);
        default: return 0;
      }
    });
    cards.forEach(function(card) { grid.appendChild(card); });
  }

  function renderPage() {
    var filtered = cards.filter(function(c) { return c._match; });
    var total = filtered.length;
    var totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
    if (currentPage > totalPages) currentPage = totalPages;

    var start = (currentPage - 1) * PAGE_SIZE;
    var end   = start + PAGE_SIZE;

    cards.forEach(function(card) {
      var idx = filtered.indexOf(card);
      card.style.display = (card._match && idx >= start && idx < end) ? '' : 'none';
    });

    if (countVis && countTotal) {
      if (total === 0) {
        countVis.textContent = '0';
        countTotal.textContent = ' of 0';
      } else if (totalPages <= 1) {
        countVis.textContent = String(total);
        countTotal.textContent = ' of ' + total;
      } else {
        countVis.textContent = (start + 1) + '\u2013' + Math.min(end, total);
        countTotal.textContent = ' of ' + total;
      }
    }

    noResults.style.display = total === 0 ? '' : 'none';
    grid.style.display      = total === 0 ? 'none' : '';

    if (totalPages > 1) {
      pagination.style.display = 'flex';
      pageInfo.textContent = 'Page ' + currentPage + ' of ' + totalPages;
      prevBtn.disabled = currentPage === 1;
      nextBtn.disabled = currentPage === totalPages;
    } else {
      pagination.style.display = 'none';
    }
  }

  function filterAndRender() {
    var q  = search.value.toLowerCase().trim();
    var st = stateFilt  ? stateFilt.value  : '';
    var m  = makeFilter ? makeFilter.value : '';
    var c  = catFilter  ? catFilter.value  : '';
    cards.forEach(function(card) {
      var nameMatch = !q || card.getAttribute('data-name').indexOf(q) !== -1
                        || (card.getAttribute('data-stock') || '').indexOf(q) !== -1;
      card._match = nameMatch
                 && (!st || card.getAttribute('data-state') === st)
                 && (!m  || card.getAttribute('data-make')  === m)
                 && (!c  || card.getAttribute('data-category') === c);
    });
    currentPage = 1;
    renderPage();
  }

  prevBtn.addEventListener('click', function() {
    if (currentPage > 1) { currentPage--; renderPage(); window.scrollTo(0, grid.offsetTop - 100); }
  });
  nextBtn.addEventListener('click', function() {
    var filtered = cards.filter(function(c) { return c._match; });
    if (currentPage < Math.ceil(filtered.length / PAGE_SIZE)) { currentPage++; renderPage(); window.scrollTo(0, grid.offsetTop - 100); }
  });

  search.addEventListener('input', filterAndRender);
  if (stateFilt)  stateFilt.addEventListener('change', filterAndRender);
  if (makeFilter) makeFilter.addEventListener('change', filterAndRender);
  if (catFilter)  catFilter.addEventListener('change', filterAndRender);
  sort.addEventListener('change', function() { applySort(); currentPage = 1; renderPage(); });

  applySort();
  renderPage();
})();
</script>

<?php get_footer();
