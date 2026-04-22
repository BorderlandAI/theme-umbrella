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
        <a class="contact-tile" href="#" data-chat-open="service">
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

<?php
// Service JSON-LD — emits per-store or umbrella, referencing the AutomotiveBusiness @id from the site @graph.
$service_types = [
    'Scheduled Maintenance', 'Warranty Service', 'Winterization',
    'Tire Installation', 'Oil & Filter Change', 'Brake Service',
    'Engine Diagnostics', 'Pre-Delivery Inspection',
];
$services = [];
foreach ($service_types as $type) {
    $node = [
        '@type'       => 'Service',
        'name'        => $type,
        'serviceType' => $type,
        'category'    => 'Powersports Service',
    ];
    if ($store) {
        $node['provider']   = ['@id' => BORDERLAND_SITE_URL . '/stores/' . $store['slug'] . '#business'];
        $node['areaServed'] = !empty($store['area_served'])
            ? array_map(fn($p) => ['@type' => 'Place', 'name' => $p], $store['area_served'])
            : [['@type' => 'State', 'name' => 'Manitoba']];
    } else {
        $node['provider']   = ['@id' => BORDERLAND_SITE_URL . '/#organization'];
        $node['areaServed'] = ['@type' => 'State', 'name' => 'Manitoba'];
    }
    $services[] = $node;
}
echo '<script type="application/ld+json">' . wp_json_encode([
    '@context' => 'https://schema.org',
    '@graph'   => $services,
], JSON_UNESCAPED_SLASHES) . '</script>';

get_footer();
