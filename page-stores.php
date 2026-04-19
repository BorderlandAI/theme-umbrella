<?php
/**
 * Template Name: Stores Index (/stores)
 */
get_header();
$stores = bl_stores();
?>
<main id="content" class="bl-page">
  <section class="bl-hero small">
    <div class="inner">
      <h1>Our Locations</h1>
      <p class="lede">Four dealerships serving Manitoba. Pick your closest location to see inventory, service, and contact details.</p>
    </div>
  </section>

  <section class="bl-stores">
    <div class="inner">
      <div class="store-grid">
        <?php foreach ($stores as $s): ?>
          <a class="store-card" href="<?php echo esc_url(home_url('/stores/' . $s['slug'])); ?>">
            <h3><?php echo esc_html($s['name']); ?></h3>
            <p class="address"><?php echo esc_html(trim(($s['address'] ? $s['address'] . ' · ' : '') . $s['city'] . ', ' . $s['region'])); ?></p>
            <p class="phone"><?php echo esc_html($s['phone']); ?></p>
            <p class="brands"><?php echo esc_html(implode(' · ', $s['brands'])); ?></p>
            <span class="arrow">Visit <?php echo esc_html($s['name']); ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php get_footer();
