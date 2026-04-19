<!doctype html>
<html lang="en">
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="icon" type="image/x-icon" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon.ico">
  <link rel="icon" type="image/png" sizes="192x192" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon-192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/assets/images/apple-touch-icon.png">

  <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/assets/fonts/lato/lato-bold-webfont.woff" as="font" type="font/woff" crossorigin="anonymous">
  <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/assets/fonts/lato/lato-regular-webfont.woff" as="font" type="font/woff" crossorigin="anonymous">
  <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/assets/fonts/fontawesome/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin="anonymous">

  <?php wp_head(); ?>
  </head>
  <body <?php body_class('lang-en is-pgs-site is-powersport is-template-v1-5 has-mainnav has-footer-nav bl-umbrella'); ?>>

    <div id="page">
      <div class="outer">

<?php
$bl_store  = bl_current_store();
$bl_title  = $bl_store ? $bl_store['full_name'] : BORDERLAND_SITE_NAME;
$bl_logo   = $bl_store
    ? get_template_directory_uri() . '/assets/images/logo-' . $bl_store['slug'] . '.png'
    : get_template_directory_uri() . '/assets/images/logo-umbrella-v2.png';
?>

<header id="siteHeader" role="banner" class="bl-header <?php echo $bl_store ? 'has-store' : 'umbrella'; ?>">
  <div class="site-branding">
    <div class="inner">
      <div class="logo">
        <div class="main">
          <a href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr($bl_title); ?>">
            <span><img src="<?php echo esc_url($bl_logo); ?>?v=7" alt="<?php echo esc_attr($bl_title); ?>"/></span>
          </a>
        </div>
      </div>

      <div class="infos">
        <?php if ($bl_store): ?>
          <h2><?php echo esc_html($bl_store['full_name']); ?></h2>
          <ul>
            <li class="item">
              <ul>
                <?php if ($bl_store['address']): ?>
                  <li class="address">
                    <div class="content">
                      <a href="https://www.google.com/maps/dir//<?php echo esc_attr(urlencode($bl_store['address'] . ', ' . $bl_store['city'] . ', ' . $bl_store['region'])); ?>" target="_blank" rel="noopener">
                        <span><?php echo esc_html($bl_store['address'] . ', ' . $bl_store['city'] . ', ' . $bl_store['region']); ?></span>
                      </a>
                    </div>
                  </li>
                <?php endif; ?>
                <li class="phone general">
                  <div class="content">
                    <span class="label">Call:</span>
                    <span class="value"><a href="tel:<?php echo esc_attr($bl_store['phone_tel']); ?>"><span class="nobr phone-number"><?php echo esc_html($bl_store['phone']); ?></span></a></span>
                  </div>
                </li>
              </ul>
            </li>
          </ul>
        <?php else: ?>
          <ul class="umbrella-locations" aria-label="Our 4 locations">
            <?php foreach (bl_stores() as $s): ?>
              <li>
                <a href="<?php echo esc_url(home_url('/stores/' . $s['slug'])); ?>">
                  <span class="name"><?php echo esc_html($s['name']); ?></span>
                  <span class="phone"><?php echo esc_html($s['phone']); ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div id="mainnav">
    <div class="inner">
      <nav>
        <h2><span>Menu</span></h2>
        <button class="hamburger hamburger--vortex" type="button" aria-label="Menu"><span class="hamburger-box"><span class="hamburger-inner"></span></span></button>

        <?php
        $has_menu = has_nav_menu('primary');
        if ($has_menu) {
            wp_nav_menu([
                'theme_location' => 'primary',
                'container' => false,
                'items_wrap' => '<ul>%3$s</ul>',
                'depth' => 2,
                'fallback_cb' => false,
            ]);
        } else {
            // Fallback menu until WP menu is configured
            $store_slug = $bl_store['slug'] ?? '';
            $store_prefix = $store_slug ? ('/stores/' . $store_slug) : '';
            ?>
            <ul>
              <li class="menu-item"><a href="<?php echo esc_url(home_url('/')); ?>"><span>Home</span></a></li>
              <li class="menu-item menu-item-has-children">
                <a href="<?php echo esc_url(home_url($store_prefix . '/inventory/')); ?>"><span>Inventory</span></a>
                <ul class="sub-menu">
                  <?php foreach (bl_stores() as $s): ?>
                    <li class="menu-item"><a href="<?php echo esc_url(home_url('/stores/' . $s['slug'] . '/inventory/')); ?>"><?php echo esc_html($s['name']); ?></a></li>
                  <?php endforeach; ?>
                  <li class="menu-item"><a href="<?php echo esc_url(home_url('/inventory/')); ?>"><span>All Locations</span></a></li>
                </ul>
              </li>
              <li class="menu-item"><a href="<?php echo esc_url(home_url('/brands/')); ?>"><span>Brands</span></a></li>
              <li class="menu-item menu-item-has-children">
                <a href="<?php echo esc_url(home_url($store_prefix . '/service/')); ?>"><span>Service</span></a>
                <ul class="sub-menu">
                  <?php foreach (bl_stores() as $s): ?>
                    <li class="menu-item"><a href="<?php echo esc_url(home_url('/stores/' . $s['slug'] . '/service/')); ?>"><?php echo esc_html($s['name']); ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </li>
              <li class="menu-item"><a href="<?php echo esc_url(home_url($store_prefix . '/parts/')); ?>"><span>Parts</span></a></li>
              <li class="menu-item"><a href="<?php echo esc_url(home_url('/financing/')); ?>"><span>Financing</span></a></li>
              <li class="menu-item"><a href="<?php echo esc_url(home_url('/stores/')); ?>"><span>Locations</span></a></li>
              <li class="menu-item"><a href="<?php echo esc_url(home_url('/about/')); ?>"><span>About</span></a></li>
              <li class="contact menu-item"><a href="<?php echo esc_url(home_url($store_prefix . '/contact/')); ?>"><span>Contact</span></a></li>
              <li class="menu-item shop"><a href="https://shopborderland.ca" target="_blank" rel="noopener"><span>Shop ↗</span></a></li>
            </ul>
            <?php
        }
        ?>
      </nav>
    </div>
  </div>

  <style>
    #siteHeader .mobilebtns>ul>li.sms-parts>a:before { content:"\f0ad"!important; display:block!important; font-family:FontAwesome-solid!important; font-weight:400!important; position:absolute; top:0;bottom:0;left:0;right:0; font-size:24px; width:24px;height:24px;line-height:24px; margin:auto; }
    #siteHeader .mobilebtns>ul>li.sms-sales>a:before { content:"\f075"!important; display:block!important; font-family:FontAwesome-solid!important; font-weight:400!important; position:absolute; top:0;bottom:0;left:0;right:0; font-size:24px; width:24px;height:24px;line-height:24px; margin:auto; }
  </style>
  <div class="mobilebtns">
    <ul class="menu singleCTA">
      <li class="nav emph"><button class="hamburger hamburger--vortex" type="button" aria-label="Menu"><span class="hamburger-box"><span class="hamburger-inner"></span></span></button></li>
      <li class="cta sms-parts emph"><a href="#" onclick="if(window.openChat){openChat('parts');}return false;"><span><span>Parts</span></span></a></li>
      <li class="cta sms-sales emph"><a href="#" onclick="if(window.openChat){openChat('sales');}return false;"><span><span>Sales</span></span></a></li>
      <li class="phone emph"><a href="tel:<?php echo esc_attr($bl_tel); ?>"><span><span>Call</span></span></a></li>
    </ul>
  </div>
</header>
