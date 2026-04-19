<?php
/**
 * Template Name: Service
 */
get_header();
$store = bl_current_store();
?>
<main id="content" class="bl-page">
  <section class="bl-hero small">
    <div class="inner">
      <h1>Service &amp; Maintenance<?php echo $store ? ' — ' . esc_html($store['name']) : ''; ?></h1>
      <p class="lede">Factory-trained technicians, OEM parts, and expert care for every powersports and marine vehicle we sell.</p>
    </div>
  </section>

  <section class="bl-tiles">
    <div class="inner">
      <div class="tile-grid-3">
        <a class="contact-tile" href="tel:<?php echo esc_attr($store['phone_tel'] ?? '+12042395900'); ?>">
          <span class="icon">📞</span>
          <h3>Call Us</h3>
          <p><?php echo esc_html($store['phone'] ?? 'Call any location'); ?></p>
        </a>
        <a class="contact-tile" href="#" onclick="if(window.openChat){openChat('parts');}return false;">
          <span class="icon">🔧</span>
          <h3>Book Service</h3>
          <p>Chat with our service team</p>
        </a>
        <a class="contact-tile" href="<?php echo esc_url(home_url(($store ? '/stores/' . $store['slug'] : '') . '/contact/')); ?>">
          <span class="icon">✉️</span>
          <h3>Message Us</h3>
          <p>Send a message online</p>
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
          <a class="store-card" href="<?php echo esc_url(home_url('/stores/' . $s['slug'] . '/service/')); ?>">
            <h3><?php echo esc_html($s['name']); ?> Service</h3>
            <p class="phone"><?php echo esc_html($s['phone']); ?></p>
            <span class="arrow">Book Service</span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
</main>
<?php get_footer();
