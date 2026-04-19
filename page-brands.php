<?php
/**
 * Template Name: Brands Index (/brands)
 */
get_header();

$brand_store_map = [];
foreach (bl_stores() as $s) {
    foreach ($s['brands'] as $b) {
        $brand_store_map[$b][] = $s;
    }
}
ksort($brand_store_map);
?>
<main id="content" class="bl-page">
  <section class="bl-hero small">
    <div class="inner">
      <h1>Our Brands</h1>
      <p class="lede">We proudly represent leading powersports and marine manufacturers across our 4 Manitoba locations.</p>
    </div>
  </section>

  <section class="bl-brands-index">
    <div class="inner">
      <div class="brand-grid">
        <?php foreach ($brand_store_map as $brand => $stores_with_brand):
          $logo = bl_brand_logo_url($brand);
        ?>
          <div class="brand-card">
            <?php if ($logo): ?>
              <div class="brand-logo"><img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($brand); ?> logo" /></div>
            <?php endif; ?>
            <h2><?php echo esc_html($brand); ?></h2>
            <p class="avail">Available at:</p>
            <ul>
              <?php foreach ($stores_with_brand as $s): ?>
                <li><a href="<?php echo esc_url(home_url('/stores/' . $s['slug'])); ?>"><?php echo esc_html($s['name']); ?></a></li>
              <?php endforeach; ?>
            </ul>
            <p class="cta"><a href="<?php echo esc_url(home_url('/inventory/?make=' . rawurlencode($brand))); ?>">Browse <?php echo esc_html($brand); ?> inventory</a></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php get_footer();
