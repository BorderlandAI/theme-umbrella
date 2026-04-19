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
      <div class="gallery" data-total="<?php echo count($gallery_urls); ?>">
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
      <script>
      (function(){
        var URLS = <?php echo $gallery_json; ?>;
        if (!URLS || URLS.length < 2) return;
        var root   = document.querySelector('.bl-vehicle-detail .gallery');
        if (!root) return;
        var main   = root.querySelector('img.main');
        var thumbs = root.querySelectorAll('.thumbs img');
        var counter= root.querySelector('.counter .cur');
        var cur = 0;

        function buildButton(cls, label, text) {
          var b = document.createElement('button');
          b.className = cls;
          b.type = 'button';
          b.setAttribute('aria-label', label);
          b.textContent = text;
          return b;
        }
        function ensureLightbox() {
          var lb = document.getElementById('bl-lightbox');
          if (lb) return lb;
          lb = document.createElement('div');
          lb.id = 'bl-lightbox';
          lb.className = 'bl-lightbox';
          var img = document.createElement('img');
          img.alt = '';
          var close = buildButton('close', 'Close', '\u00D7');
          var prev  = buildButton('nav prev', 'Previous', '\u2039');
          var next  = buildButton('nav next', 'Next', '\u203A');
          lb.appendChild(img);
          lb.appendChild(close);
          lb.appendChild(prev);
          lb.appendChild(next);
          document.body.appendChild(lb);
          close.addEventListener('click', closeLightbox);
          prev.addEventListener('click', function(e){ e.stopPropagation(); show(cur - 1); openLightbox(); });
          next.addEventListener('click', function(e){ e.stopPropagation(); show(cur + 1); openLightbox(); });
          lb.addEventListener('click', function(e){ if (e.target === lb) closeLightbox(); });
          return lb;
        }
        function show(i) {
          cur = ((i % URLS.length) + URLS.length) % URLS.length;
          main.src = URLS[cur];
          main.setAttribute('data-index', cur);
          if (counter) counter.textContent = (cur + 1);
          thumbs.forEach(function(t){ t.classList.toggle('active', parseInt(t.dataset.index, 10) === cur); });
        }
        function openLightbox() {
          var lb = ensureLightbox();
          lb.querySelector('img').src = URLS[cur];
          lb.classList.add('open');
          document.body.style.overflow = 'hidden';
        }
        function closeLightbox() {
          var lb = document.getElementById('bl-lightbox');
          if (lb) { lb.classList.remove('open'); document.body.style.overflow = ''; }
        }

        thumbs.forEach(function(t){
          t.addEventListener('click', function(){ show(parseInt(t.dataset.index, 10)); });
        });
        root.querySelector('.nav.prev').addEventListener('click', function(){ show(cur - 1); });
        root.querySelector('.nav.next').addEventListener('click', function(){ show(cur + 1); });
        document.addEventListener('keydown', function(e){
          if (e.key === 'ArrowLeft')  show(cur - 1);
          if (e.key === 'ArrowRight') show(cur + 1);
          if (e.key === 'Escape')     closeLightbox();
        });
        main.addEventListener('click', openLightbox);
        main.style.cursor = 'zoom-in';
      })();
      </script>

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
          <button class="btn tertiary" onclick="if(window.openChat){openChat('sales');}return false;">Chat with Us</button>
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
// Product JSON-LD
if (!empty($vehicle)) {
    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'Product',
        'name'     => $title,
        'brand'    => ['@type' => 'Brand', 'name' => $vehicle['make'] ?? ''],
        'category' => ucfirst($vehicle['category'] ?? 'Vehicle'),
    ];
    if (!empty($images[0]['url'])) $schema['image'] = bl_inventory_image_url($images[0]['url']);
    if ($price) {
        $schema['offers'] = [
            '@type'         => 'Offer',
            'price'         => $price,
            'priceCurrency' => 'CAD',
            'availability'  => 'https://schema.org/InStock',
            'itemCondition' => ($vstate === 'new') ? 'https://schema.org/NewCondition' : 'https://schema.org/UsedCondition',
            'seller'        => ['@type' => 'AutomotiveBusiness', 'name' => $vstore['full_name'] ?? BORDERLAND_SITE_NAME],
        ];
    }
    if (!empty($vehicle['vin'])) $schema['vehicleIdentificationNumber'] = $vehicle['vin'];
    if (!empty($vehicle['year'])) $schema['productionDate'] = (string) $vehicle['year'];
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
}
get_footer();
