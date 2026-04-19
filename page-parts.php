<?php
/**
 * Template Name: Parts
 */
get_header();
$store = bl_current_store();
?>
<main id="content" class="bl-page">
  <section class="bl-hero small">
    <div class="inner">
      <h1>Parts &amp; Accessories<?php echo $store ? ' — ' . esc_html($store['name']) : ''; ?></h1>
      <p class="lede">OEM and quality aftermarket parts, plus accessories and riding gear. Our parts team can source what you need.</p>
    </div>
  </section>

  <section class="bl-tiles">
    <div class="inner">
      <div class="tile-grid-3">
        <a class="contact-tile" href="tel:<?php echo esc_attr($store['phone_tel'] ?? '+12042395900'); ?>">
          <span class="icon">📞</span>
          <h3>Call Parts</h3>
          <p><?php echo esc_html($store['phone'] ?? 'Call any location'); ?></p>
        </a>
        <a class="contact-tile" href="#" onclick="if(window.openChat){openChat('parts');}return false;">
          <span class="icon">💬</span>
          <h3>Chat with Parts Team</h3>
          <p>Quick answers during business hours</p>
        </a>
        <a class="contact-tile" href="https://shopborderland.ca" target="_blank" rel="noopener">
          <span class="icon">🛒</span>
          <h3>Shop Online</h3>
          <p>FXR apparel &amp; accessories</p>
        </a>
      </div>
    </div>
  </section>

  <?php if (!$store): ?>
  <section class="bl-stores">
    <div class="inner">
      <h2>Pick Your Location</h2>
      <div class="store-grid">
        <?php foreach (bl_stores() as $s): ?>
          <a class="store-card" href="<?php echo esc_url(home_url('/stores/' . $s['slug'] . '/parts/')); ?>">
            <h3><?php echo esc_html($s['name']); ?> Parts</h3>
            <p class="phone"><?php echo esc_html($s['phone']); ?></p>
            <span class="arrow">Order Parts</span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
</main>
<?php get_footer();
