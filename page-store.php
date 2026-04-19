<?php
/**
 * Template Name: Store Landing (/stores/{slug})
 */
get_header();
$store = bl_current_store();
if (!$store) {
    wp_redirect(home_url('/stores/'), 302);
    exit;
}
$store_inv = bl_featured_inventory($store['slug'], 6);

$dir_query = trim(($store['address'] ? $store['address'] . ', ' : '') . $store['city'] . ', ' . $store['region']);
$dir_url   = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($dir_query);
?>

<main id="content" class="bl-page bl-store">
  <section class="bl-hero small">
    <div class="inner">
      <h1><?php echo esc_html($store['full_name']); ?></h1>
      <p class="lede">
        <?php if ($store['address']): ?><?php echo esc_html($store['address']); ?>, <?php endif; ?>
        <?php echo esc_html($store['city'] . ', ' . $store['region']); ?>
      </p>
      <div class="cta-row">
        <a class="btn primary" href="tel:<?php echo esc_attr($store['phone_tel']); ?>">Call <?php echo esc_html($store['phone']); ?></a>
        <a class="btn secondary" href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/inventory/')); ?>">Browse Inventory</a>
        <a class="btn secondary" href="<?php echo esc_url($dir_url); ?>" target="_blank" rel="noopener">Directions</a>
      </div>
    </div>
  </section>

  <?php if (!empty($store_inv)): ?>
  <section class="bl-featured-inv">
    <div class="inner">
      <h2>Featured at <?php echo esc_html($store['name']); ?></h2>
      <div class="inv-grid">
        <?php foreach ($store_inv as $v):
          $img = bl_vehicle_cover_image($v);
          $title = trim(($v['year'] ?? '') . ' ' . ($v['make'] ?? '') . ' ' . ($v['submodel'] ?? $v['model'] ?? ''));
          $price = !empty($v['salePrice']) ? $v['salePrice'] : ($v['basePrice'] ?? null);
          $stock = $v['stockNumber'] ?? $v['id'];
          $discount = bl_unit_discount($v);
        ?>
          <a class="inv-card" href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/inventory/' . rawurlencode($stock))); ?>">
            <?php if ($img): ?>
              <div class="img" style="background-image:url('<?php echo esc_url($img); ?>')">
                <?php if ($discount > 0): ?>
                  <span class="save-badge">Save $<?php echo esc_html(number_format($discount, 0)); ?></span>
                <?php endif; ?>
              </div>
            <?php endif; ?>
            <div class="body">
              <h3><?php echo esc_html($title); ?></h3>
              <?php if ($discount > 0 && !empty($v['basePrice'])): ?>
                <p class="price">
                  <span class="sale">$<?php echo esc_html(number_format((float) $price, 0)); ?></span>
                  <span class="msrp">MSRP $<?php echo esc_html(number_format((float) $v['basePrice'], 0)); ?></span>
                </p>
              <?php elseif ($price): ?>
                <p class="price">$<?php echo esc_html(number_format((float) $price, 0)); ?></p>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
      <p class="cta"><a href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/inventory/')); ?>">All <?php echo esc_html($store['name']); ?> inventory</a></p>
    </div>
  </section>
  <?php endif; ?>

  <section class="bl-store-info">
    <div class="inner">
      <div class="store-info-grid">

        <div class="store-info-col">
          <span class="store-info-label">Hours</span>
          <p class="store-info-hours"><?php echo esc_html(bl_hours_display($store)); ?></p>
        </div>

        <div class="store-info-col">
          <span class="store-info-label">Brands</span>
          <ul class="brand-pills">
            <?php foreach ($store['brands'] as $b): ?><li><?php echo esc_html($b); ?></li><?php endforeach; ?>
          </ul>
        </div>

        <div class="store-info-col">
          <span class="store-info-label">Quick Links</span>
          <ul class="quick-links">
            <li><a href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/inventory/')); ?>">Inventory</a></li>
            <li><a href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/service/')); ?>">Service</a></li>
            <li><a href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/parts/')); ?>">Parts</a></li>
            <li><a href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/contact/')); ?>">Contact</a></li>
            <li><a href="<?php echo esc_url(home_url('/financing/')); ?>">Financing</a></li>
          </ul>
        </div>

      </div>
    </div>
  </section>
</main>

<?php get_footer();
