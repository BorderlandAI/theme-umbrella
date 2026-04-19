<?php
$bl_store = bl_current_store();
$bl_title = $bl_store ? $bl_store['full_name'] : BORDERLAND_SITE_NAME;
$bl_phone = $bl_store ? $bl_store['phone'] : '204-239-5900';
$bl_tel   = $bl_store ? $bl_store['phone_tel'] : '+12042395900';
$bl_sms   = $bl_store['sms_phone'] ?? '+14315004828';
$bl_addr  = $bl_store && $bl_store['address'] ? $bl_store['address'] . ', ' . $bl_store['city'] . ', ' . $bl_store['region'] : '';
$bl_map   = $bl_store && $bl_store['address']
    ? 'https://maps.google.com/maps?q=' . urlencode($bl_store['address'] . ', ' . $bl_store['city'] . ', ' . $bl_store['region']) . '&t=m&z=14&output=embed'
    : 'https://maps.google.com/maps?q=Manitoba&t=m&z=5&output=embed';
?>

    <div id="footerBefore">
      <div class="inner">
        <div class="legal">
          <p>Information above is for informational purposes only and does not constitute a contractual offer. Contact us for complete details.</p>
        </div>
      </div>
    </div>

<footer id="siteFooter" role="contentinfo">
  <div class="main">
    <section class="navigation">
      <div class="inner">
        <nav>
          <div class="col new">
            <h2><a href="<?php echo esc_url(home_url('/inventory/')); ?>">Inventory</a></h2>
            <ul>
              <?php foreach (bl_stores() as $s): ?>
                <li class="menu-item"><a href="<?php echo esc_url(home_url('/stores/' . $s['slug'] . '/inventory/')); ?>"><?php echo esc_html($s['name']); ?></a></li>
              <?php endforeach; ?>
              <li class="menu-item"><a href="<?php echo esc_url(home_url('/inventory/')); ?>">All Locations</a></li>
            </ul>
          </div>

          <div class="col service">
            <h2><a href="<?php echo esc_url(home_url('/service/')); ?>">Service</a></h2>
            <ul>
              <?php foreach (bl_stores() as $s): ?>
                <li class="menu-item"><a href="<?php echo esc_url(home_url('/stores/' . $s['slug'] . '/service/')); ?>"><?php echo esc_html($s['name']); ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>

          <div class="col used">
            <h2><a href="<?php echo esc_url(home_url('/brands/')); ?>">Explore</a></h2>
            <ul>
              <li class="menu-item"><a href="<?php echo esc_url(home_url('/brands/')); ?>">Brands</a></li>
              <li class="menu-item"><a href="<?php echo esc_url(home_url('/financing/')); ?>">Financing</a></li>
              <li class="menu-item"><a href="<?php echo esc_url(home_url('/news/')); ?>">News</a></li>
              <li class="menu-item"><a href="https://shopborderland.ca" target="_blank" rel="noopener">Shop ↗</a></li>
            </ul>
          </div>

          <div class="col about">
            <h2><a href="<?php echo esc_url(home_url('/about/')); ?>">About</a></h2>
            <ul>
              <li class="menu-item"><a href="<?php echo esc_url(home_url('/about/')); ?>">About Us</a></li>
              <li class="menu-item"><a href="<?php echo esc_url(home_url('/stores/')); ?>">Locations</a></li>
              <li class="menu-item"><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a></li>
              <li class="menu-item"><a href="<?php echo esc_url(home_url('/faq/')); ?>">FAQ</a></li>
            </ul>
          </div>
        </nav>
      </div>
    </section>

    <section class="about">
      <div class="inner">
        <h2>Contact</h2>
        <?php if ($bl_store): ?>
          <h3><?php echo esc_html($bl_title); ?></h3>
          <ul>
            <?php if ($bl_addr): ?>
              <li class="address">
                <div>
                  <a href="https://www.google.com/maps/dir//<?php echo esc_attr(urlencode($bl_addr)); ?>" target="_blank" rel="noopener">
                    <span><?php echo esc_html($bl_addr); ?></span>
                  </a>
                </div>
              </li>
            <?php endif; ?>
            <li class="phone">
              <ul>
                <li class="phone general"><span class="label">Call:</span> <span class="value"><a href="tel:<?php echo esc_attr($bl_tel); ?>"><span class="nobr phone-number"><?php echo esc_html($bl_phone); ?></span></a></span></li>
              </ul>
            </li>
          </ul>
        <?php else: ?>
          <h3><?php echo esc_html(BORDERLAND_SITE_NAME); ?></h3>
          <p>Manitoba's multi-location powersports group. <a href="<?php echo esc_url(home_url('/stores/')); ?>">Find your nearest dealership</a> for direct contact details.</p>
          <ul class="footer-stores">
            <?php foreach (bl_stores() as $s): ?>
              <li><a href="<?php echo esc_url(home_url('/stores/' . $s['slug'])); ?>"><strong><?php echo esc_html($s['name']); ?></strong> — <?php echo esc_html($s['phone']); ?></a></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <div class="footer-logo-wrap"><a href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr(BORDERLAND_SITE_NAME); ?>"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo-umbrella-v2.png?v=5" alt="<?php echo esc_attr(BORDERLAND_SITE_NAME); ?>" style="max-width:180px;"/></a></div>
      </div>
    </section>
  </div>

  <?php /* map iframe disabled — was leaving empty black space */ ?>

  <div class="sub">
    <div class="inner">
      <div class="copyright">
        <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html(BORDERLAND_SITE_NAME); ?>. All rights reserved. <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy Policy</a> · <a href="<?php echo esc_url(home_url('/terms/')); ?>">Terms of Use</a>.</p>
      </div>
    </div>
  </div>
</footer>

      </div>
    </div>

    <div id="mobilebtns">
      <ul class="menu singleCTA">
        <li class="nav emph"><button class="hamburger hamburger--vortex" type="button" aria-label="Menu"><span class="hamburger-box"><span class="hamburger-inner"></span></span></button></li>
        <li class="cta sms-parts emph"><a href="#" onclick="if(window.openChat){openChat('parts');}return false;"><span><span>Parts</span></span></a></li>
        <li class="cta sms-sales emph"><a href="#" onclick="if(window.openChat){openChat('sales');}return false;"><span><span>Sales</span></span></a></li>
        <li class="phone emph"><a href="tel:<?php echo esc_attr($bl_tel); ?>"><span><span>Call</span></span></a></li>
      </ul>
    </div>
    <style>
      #mobilebtns>ul>li.sms-parts>a>span:before,
      .mobilebtns ul>li.sms-parts>a>span:before { content:"\f0ad"!important; font-family:FontAwesome-solid!important; }
      #mobilebtns>ul>li.sms-sales>a>span:before,
      .mobilebtns ul>li.sms-sales>a>span:before { content:"\f075"!important; font-family:FontAwesome-solid!important; }
      @media (max-width: 768px) { #ryder-chat-bubble { display: none !important; } }
    </style>
    <script>
      (function(){
        var BL_SMS_TARGET = <?php echo json_encode($bl_sms); ?>;
        function isMobile(){ return /Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent); }
        window.openChat = function(dept) {
          var msg = dept === 'parts' ? 'Hi, I have a question about parts/service' : 'Hi, I have a question about a unit';
          if (isMobile()) { window.location.href = 'sms:' + BL_SMS_TARGET + '?body=' + encodeURIComponent(msg); return; }
          var bubble = document.getElementById('ryder-chat-bubble');
          var panel  = document.getElementById('ryder-chat-panel');
          if (bubble && panel && !panel.classList.contains('open')) bubble.click();
          setTimeout(function(){
            var input = document.getElementById('ryder-chat-input');
            if (input) { input.value = msg; input.focus(); }
          }, 300);
        };
      })();
    </script>
    <?php wp_footer(); ?>
  </body>
</html>
