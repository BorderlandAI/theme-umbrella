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
        ?>
          <a class="inv-card" href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/inventory/' . rawurlencode($stock))); ?>">
            <?php if ($img): ?><div class="img" style="background-image:url('<?php echo esc_url($img); ?>')"></div><?php endif; ?>
            <div class="body">
              <h3><?php echo esc_html($title); ?></h3>
              <?php if ($price): ?><p class="price">$<?php echo esc_html(number_format((float) $price, 0)); ?></p><?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
      <p class="cta"><a href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/inventory/')); ?>">All <?php echo esc_html($store['name']); ?> inventory</a></p>
    </div>
  </section>
  <?php endif; ?>

  <section class="bl-store-info">
    <div class="inner two-col">
      <div>
        <h2>Hours</h2>
        <p><?php echo esc_html(bl_hours_display($store)); ?></p>
        <h2>Brands</h2>
        <ul class="brand-pills">
          <?php foreach ($store['brands'] as $b): ?><li><?php echo esc_html($b); ?></li><?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h2>Quick Links</h2>
        <ul class="quick-links">
          <li><a href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/inventory/')); ?>">Inventory</a></li>
          <li><a href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/service/')); ?>">Service</a></li>
          <li><a href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/parts/')); ?>">Parts</a></li>
          <li><a href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/contact/')); ?>">Contact</a></li>
          <li><a href="<?php echo esc_url(home_url('/financing/')); ?>">Financing</a></li>
        </ul>
      </div>
    </div>
  </section>
</main>

<?php get_footer();
