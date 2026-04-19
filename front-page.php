<?php
/**
 * Umbrella Homepage — brand hero → 4 store cards → featured inventory → brands → news → chat CTA.
 */
get_header();

$stores = bl_stores();
$featured = bl_featured_inventory(null, 8);
$news = function_exists('bl_news_items') ? bl_news_items(3) : [];
?>

<main id="content" class="bl-home">

  <section class="bl-hero">
    <div class="inner">
      <h1>Manitoba's Powersports Group</h1>
      <p class="lede">Four dealerships. One family. Kawasaki, Polaris, Yamaha, Suzuki, Mercury, Lund, CFMOTO, and more across Brandon, Morden, Portage la Prairie, and Thompson.</p>
      <div class="cta-row">
        <a class="btn primary" href="<?php echo esc_url(home_url('/stores/')); ?>">Find Your Location</a>
        <a class="btn secondary" href="<?php echo esc_url(home_url('/inventory/')); ?>">Browse All Inventory</a>
      </div>
    </div>
  </section>

  <section class="bl-stores">
    <div class="inner">
      <h2>Our Locations</h2>
      <div class="store-grid">
        <?php foreach ($stores as $s): ?>
          <a class="store-card" href="<?php echo esc_url(home_url('/stores/' . $s['slug'] . '/inventory/')); ?>">
            <h3><?php echo esc_html($s['name']); ?></h3>
            <p class="address"><?php echo esc_html(trim(($s['address'] ? $s['address'] . ' · ' : '') . $s['city'] . ', ' . $s['region'])); ?></p>
            <p class="phone"><?php echo esc_html($s['phone']); ?></p>
            <p class="brands"><?php echo esc_html(implode(' · ', $s['brands'])); ?></p>
            <span class="arrow">Browse <?php echo esc_html($s['name']); ?> Inventory</span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php if (!empty($featured)): ?>
  <section class="bl-featured-inv">
    <div class="inner">
      <h2>Featured Inventory</h2>
      <div class="inv-grid">
        <?php foreach ($featured as $v):
          $img = bl_vehicle_cover_image($v);
          $title = trim(($v['year'] ?? '') . ' ' . ($v['make'] ?? '') . ' ' . ($v['submodel'] ?? $v['model'] ?? ''));
          $price = !empty($v['salePrice']) ? $v['salePrice'] : ($v['basePrice'] ?? null);
          $store_slug = strtolower($v['store'] ?? '');
          $store_info = bl_store($store_slug);
          $stock = $v['stockNumber'] ?? $v['id'];
          $href = $store_slug && $stock ? home_url('/stores/' . $store_slug . '/inventory/' . rawurlencode($stock)) : home_url('/inventory/');
        ?>
          <a class="inv-card" href="<?php echo esc_url($href); ?>">
            <?php if ($img): ?><div class="img" style="background-image:url('<?php echo esc_url($img); ?>')"></div><?php endif; ?>
            <div class="body">
              <?php if ($store_info): ?><span class="store-badge"><?php echo esc_html($store_info['name']); ?></span><?php endif; ?>
              <h3><?php echo esc_html($title); ?></h3>
              <?php if ($price): ?><p class="price">$<?php echo esc_html(number_format((float) $price, 0)); ?></p><?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="bl-brands-strip">
    <div class="inner">
      <h2>Brands We Carry</h2>
      <?php
      $all_brands = [];
      foreach ($stores as $s) foreach ($s['brands'] as $b) $all_brands[$b] = true;
      ?>
      <ul class="brand-pills">
        <?php foreach (array_keys($all_brands) as $b): ?>
          <li><?php echo esc_html($b); ?></li>
        <?php endforeach; ?>
      </ul>
      <p class="cta"><a href="<?php echo esc_url(home_url('/brands/')); ?>">Explore our brands</a></p>
    </div>
  </section>

  <?php if (!empty($news)): ?>
  <section class="bl-news-strip">
    <div class="inner">
      <h2>Latest News</h2>
      <div class="news-grid">
        <?php foreach ($news as $n): $s_info = bl_store($n['store']); ?>
          <a class="news-card" href="<?php echo esc_url($n['url']); ?>" target="_blank" rel="noopener">
            <?php if ($s_info): ?><span class="store-badge"><?php echo esc_html($s_info['name']); ?></span><?php endif; ?>
            <h3><?php echo esc_html($n['title']); ?></h3>
            <p><?php echo esc_html($n['excerpt']); ?></p>
            <span class="date"><?php echo esc_html(date('M j, Y', $n['date'])); ?></span>
          </a>
        <?php endforeach; ?>
      </div>
      <p class="cta"><a href="<?php echo esc_url(home_url('/news/')); ?>">All news</a></p>
    </div>
  </section>
  <?php endif; ?>

  <section class="bl-chat-cta">
    <div class="inner">
      <h2>Have a Question?</h2>
      <p>Our team is here to help — chat with us online or text any of our locations.</p>
      <button class="btn primary" onclick="if(window.openChat){openChat('sales');}return false;">Start a Chat</button>
    </div>
  </section>

</main>

<?php get_footer();
