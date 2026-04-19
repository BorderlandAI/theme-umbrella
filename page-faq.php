<?php
/**
 * Template Name: FAQ
 */
get_header();
$faqs = [
    ['Where are your dealerships located?', 'We have 4 locations across Manitoba: Brandon, Morden, Portage la Prairie, and Thompson. See the <a href="' . esc_url(home_url('/stores/')) . '">Locations</a> page for hours, phones, and addresses.'],
    ['What brands do you carry?', 'Across our group: Kawasaki, Polaris, Yamaha, Suzuki, CFMOTO, Mercury, Lund, Equinox, Abitibi &amp; Co, and ARGO. Brand mix varies by location — check the <a href="' . esc_url(home_url('/brands/')) . '">Brands</a> page.'],
    ['Do you offer financing?', 'Yes. Apply online at <a href="' . esc_url(home_url('/financing/')) . '">/financing</a> — our team responds within one business day.'],
    ['Can I buy parts online?', 'Yes — FXR apparel and accessories ship from <a href="https://shopborderland.ca" target="_blank" rel="noopener">shopborderland.ca</a>. OEM parts are ordered through your local dealership.'],
    ['How do I book a service appointment?', 'Call the location nearest you, or tap the chat widget and ask for Parts &amp; Service. We\'ll get a slot booked.'],
    ['Do you take trade-ins?', 'Yes. Any location can appraise ATVs, side-by-sides, dirt bikes, snowmobiles, jet skis, and boats.'],
];
?>
<main id="content" class="bl-page bl-faq">
  <section class="bl-hero small">
    <div class="inner">
      <h1>Frequently Asked Questions</h1>
      <p class="lede">Answers to the most common questions we hear across our group.</p>
    </div>
  </section>

  <section class="bl-prose">
    <div class="inner">
      <?php foreach ($faqs as $f): ?>
        <details>
          <summary><strong><?php echo esc_html($f[0]); ?></strong></summary>
          <div><?php echo wp_kses_post($f[1]); ?></div>
        </details>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<?php
// FAQPage JSON-LD
$faq_items = [];
foreach ($faqs as $f) {
    $faq_items[] = [
        '@type' => 'Question',
        'name'  => $f[0],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => wp_strip_all_tags($f[1])],
    ];
}
echo '<script type="application/ld+json">' . wp_json_encode([
    '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faq_items,
], JSON_UNESCAPED_SLASHES) . '</script>';

get_footer();
