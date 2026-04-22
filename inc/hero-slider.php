<?php
/**
 * OEM Hero Slider — reads data/oem-hero-slides.json (scraped from brand homepages)
 * and renders a rotating Swiper carousel on the homepage.
 *
 * Data source: scraper container writes to /data/umbrella-export on its
 * twice-monthly catalog run. Per-brand files are also available
 * (data/hero-slides-{brand}.json) for future per-brand pages.
 */

if (!defined('ABSPATH')) exit;

/**
 * Load all hero slides grouped by brand.
 * Returns: [ 'kawasaki' => [slide, slide, ...], 'polaris' => [...], ... ]
 */
function bl_load_oem_hero_slides() {
    $cache_key = 'bl_oem_hero_slides_v5';
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;

    $path = get_template_directory() . '/data/oem-hero-slides.json';
    if (!file_exists($path)) return [];

    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    if (!is_array($data)) return [];

    set_transient($cache_key, $data, 900);   // 15-min cache
    return $data;
}

/**
 * Flatten grouped slides into a single list, optionally interleaved across brands
 * so consecutive slides don't all come from the same OEM.
 */
function bl_flatten_oem_hero_slides($by_brand, $interleave = true) {
    if (!$by_brand) return [];
    if (!$interleave) {
        $out = [];
        foreach ($by_brand as $brand => $slides) {
            foreach ($slides as $s) {
                $s['brand'] = $brand;
                $out[] = $s;
            }
        }
        return $out;
    }
    // Interleave: take 1 slide from each brand per round until all are consumed
    $queues = [];
    foreach ($by_brand as $brand => $slides) {
        $queues[$brand] = array_map(function ($s) use ($brand) {
            $s['brand'] = $brand;
            return $s;
        }, $slides);
    }
    $out = [];
    while (!empty($queues)) {
        foreach (array_keys($queues) as $brand) {
            if (empty($queues[$brand])) {
                unset($queues[$brand]);
                continue;
            }
            $out[] = array_shift($queues[$brand]);
        }
    }
    return $out;
}

/**
 * Render the OEM hero slider. Uses Swiper (already enqueued in functions.php).
 */
function bl_render_oem_hero_slider() {
    $by_brand = bl_load_oem_hero_slides();
    $slides = bl_flatten_oem_hero_slides($by_brand, true);
    if (!$slides) return;

    // Brand → display label (for pill on each slide)
    $brand_labels = [
        'kawasaki' => 'Kawasaki',
        'suzuki'   => 'Suzuki',
        'polaris'  => 'Polaris',
        'yamaha'   => 'Yamaha',
        'cfmoto'   => 'CFMOTO',
        'mercury'  => 'Mercury',
        'lund'     => 'Lund',
    ];
    ?>
    <section class="bl-oem-hero">
      <div class="swiper bl-oem-hero__swiper">
        <div class="swiper-wrapper">
          <?php foreach ($slides as $s):
              $img = $s['image_url'] ?? '';
              if (!$img) continue;
              $brand = $s['brand'] ?? '';
              $label = $brand_labels[$brand] ?? ucfirst($brand);
              $alt = trim($label . ' — ' . ($s['headline'] ?? ''));
          ?>
            <div class="swiper-slide bl-oem-hero__slide" data-brand="<?php echo esc_attr($brand); ?>">
              <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" />
            </div>
          <?php endforeach; ?>
        </div>
        <div class="swiper-pagination bl-oem-hero__pagination"></div>
        <div class="bl-oem-hero__prev swiper-button-prev" aria-label="Previous slide"></div>
        <div class="bl-oem-hero__next swiper-button-next" aria-label="Next slide"></div>
      </div>
    </section>

    <style>
      /* Pattern mirrors borderlandportage slider: contain + fixed heights + black letterbox */
      .bl-oem-hero { width: 100%; overflow: hidden; background: #000; }
      .bl-oem-hero__swiper { width: 100%; }
      .bl-oem-hero__slide {
        width: 100% !important;
        overflow: hidden;
        height: 476px;
        background: #000;
      }
      .bl-oem-hero__slide img {
        width: 100% !important;
        height: 476px !important;
        object-fit: contain !important;
        display: block;
      }
      .bl-oem-hero .swiper-button-prev,
      .bl-oem-hero .swiper-button-next { color: #fff; }
      .bl-oem-hero__pagination .swiper-pagination-bullet {
        background: #fff; opacity: .6;
      }
      .bl-oem-hero__pagination .swiper-pagination-bullet-active {
        opacity: 1; background: #fff;
      }
      @media (max-width: 991px) {
        .bl-oem-hero__slide,
        .bl-oem-hero__slide img { height: 340px !important; }
      }
      @media (max-width: 575px) {
        .bl-oem-hero__slide,
        .bl-oem-hero__slide img { height: 220px !important; }
      }
    </style>

    <script>
      (function () {
        function init() {
          if (typeof Swiper === 'undefined') { return setTimeout(init, 120); }
          new Swiper('.bl-oem-hero__swiper', {
            loop: true,
            autoplay: { delay: 5500, disableOnInteraction: false },
            speed: 800,
            effect: 'fade',
            fadeEffect: { crossFade: true },
            pagination: { el: '.bl-oem-hero__pagination', clickable: true },
            navigation: { prevEl: '.bl-oem-hero__prev', nextEl: '.bl-oem-hero__next' },
          });
        }
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', init);
        } else { init(); }
      })();
    </script>
    <?php
}
