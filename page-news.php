<?php
/**
 * Template Name: News Aggregator (/news)
 */
get_header();
$items = function_exists('bl_news_items') ? bl_news_items(12) : [];
?>
<main id="content" class="bl-page">
  <section class="bl-hero small">
    <div class="inner">
      <h1>News from Our Locations</h1>
      <p class="lede">Latest posts from all 4 Borderland Powersports dealerships across Manitoba.</p>
    </div>
  </section>

  <section class="bl-news">
    <div class="inner">
      <?php if (empty($items)): ?>
        <p>News feed is currently unavailable. Please check back soon.</p>
      <?php else: ?>
        <div class="news-grid">
          <?php foreach ($items as $n): $s = bl_store($n['store']); ?>
            <a class="news-card" href="<?php echo esc_url($n['url']); ?>" target="_blank" rel="noopener">
              <?php if ($s): ?><span class="store-badge"><?php echo esc_html($s['name']); ?></span><?php endif; ?>
              <h3><?php echo esc_html($n['title']); ?></h3>
              <p><?php echo esc_html($n['excerpt']); ?></p>
              <span class="date"><?php echo esc_html(date('M j, Y', $n['date'])); ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php get_footer();
