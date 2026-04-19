<?php
/**
 * Borderland Umbrella Theme Functions
 * Multi-store umbrella site for www.borderlandpowersports.ca
 */

if (!defined('ABSPATH')) exit;

// ----- Constants --------------------------------------------------------
if (!defined('BORDERLAND_INVENTORY_API')) define('BORDERLAND_INVENTORY_API', 'https://inventory.borderlandgroup.ca/api/public/inventory');
if (!defined('BORDERLAND_SITE_NAME'))     define('BORDERLAND_SITE_NAME', 'Borderland Powersports');
if (!defined('BORDERLAND_SITE_URL'))      define('BORDERLAND_SITE_URL', 'https://www.borderlandpowersports.ca');
if (!defined('BORDERLAND_CHAT_WIDGET'))   define('BORDERLAND_CHAT_WIDGET', 'https://sms.borderlandgroup.ca/chat/widget.js');
if (!defined('BORDERLAND_LEAD_CRM_URL'))  define('BORDERLAND_LEAD_CRM_URL', getenv('LEAD_CRM_URL') ?: 'http://lead-crm:8126');
if (!defined('BORDERLAND_LEAD_CRM_KEY'))  define('BORDERLAND_LEAD_CRM_KEY', getenv('LEAD_CRM_API_KEY') ?: '');

// ----- Module loader ----------------------------------------------------
$inc = get_template_directory() . '/inc/';
foreach (['store-config', 'rewrites', 'inventory', 'chat', 'leads', 'schema', 'news'] as $m) {
    $f = $inc . $m . '.php';
    if (file_exists($f)) require_once $f;
}

// ----- Enqueue styles / scripts -----------------------------------------
function bl_enqueue_scripts() {
    $theme_uri = get_template_directory_uri();
    wp_enqueue_style('borderland-main',   $theme_uri . '/assets/css/main.css', [], '1.0');
    wp_enqueue_style('borderland-custom', $theme_uri . '/assets/css/custom.css', ['borderland-main'], '1.0');
    wp_enqueue_style('borderland-umbrella', $theme_uri . '/assets/css/umbrella.css', ['borderland-custom'], '3.4');
    wp_enqueue_style('swiper-css',   'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11');
    wp_enqueue_script('swiper-js',   'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11', true);
    wp_enqueue_script('jquery');
    wp_enqueue_script('borderland-main-js', $theme_uri . '/assets/js/main.js', ['jquery'], '1.0', true);
    wp_localize_script('borderland-main-js', 'BL', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('bl_lead_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'bl_enqueue_scripts');

// ----- Theme setup ------------------------------------------------------
function bl_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);
    register_nav_menus(['primary' => 'Primary Navigation', 'footer' => 'Footer Navigation']);
}
add_action('after_setup_theme', 'bl_theme_setup');

// ----- Don't cache dynamic inventory pages ------------------------------
add_action('template_redirect', function() {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
    if (preg_match('#^/(inventory|stores/[^/]+/(inventory|service|parts|contact))(/|$)#', $path)
        || (function_exists('is_page') && is_page(['inventory', 'new-inventory', 'pre-owned', 'vehicle-detail', 'promotions']))) {
        header('Cache-Control: no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
});

// ----- Helpers ----------------------------------------------------------
function bl_img($filename) {
    return get_template_directory_uri() . '/assets/images/' . $filename;
}

// ----- Back-compat shims (template files copied from Portage) -----------
if (!function_exists('portage_img')) {
    function portage_img($f) { return bl_img($f); }
}
if (!function_exists('get_portage_inventory')) {
    function get_portage_inventory($a = []) {
        $s = $a['store'] ?? null;
        unset($a['store']);
        return bl_get_inventory($s, $a);
    }
}
if (!function_exists('get_portage_vehicle')) {
    function get_portage_vehicle($id) { return bl_get_vehicle($id); }
}
if (!function_exists('portage_inventory_image_url')) {
    function portage_inventory_image_url($p) { return bl_inventory_image_url($p); }
}

// ----- Security ---------------------------------------------------------
add_filter('xmlrpc_enabled', '__return_false');

// ----- SMTP -------------------------------------------------------------
add_action('phpmailer_init', function($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = 'smtp.hostinger.com';
    $phpmailer->Port       = 465;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->SMTPSecure = 'ssl';
    $phpmailer->Username   = 'jarvis@borderlandportage.com';
    $phpmailer->Password   = getenv('SMTP_PASSWORD') ?: '';
    $phpmailer->From       = 'jarvis@borderlandportage.com';
    $phpmailer->FromName   = 'Jarvis - Borderland Group';
});
add_filter('wp_mail_content_type', function() { return 'text/html'; });

// ----- Sitemap cleanup --------------------------------------------------
add_filter('wp_sitemaps_add_provider', function($provider, $name) {
    if ($name === 'users') return false;
    return $provider;
}, 10, 2);

add_filter('wp_sitemaps_posts_query_args', function($args, $post_type) {
    if ($post_type === 'page') {
        foreach (['vehicle-detail'] as $slug) {
            $p = get_page_by_path($slug);
            if ($p) {
                $args['post__not_in'] = $args['post__not_in'] ?? [];
                $args['post__not_in'][] = $p->ID;
            }
        }
    }
    return $args;
}, 10, 2);

// Remove WP default canonical — inc/schema.php emits per-page canonical
remove_action('wp_head', 'rel_canonical');

// ----- GA4 injection (measurement ID stored in wp_option 'bl_ga4_id') ---
add_action('wp_head', function() {
    $ga = trim((string) get_option('bl_ga4_id', ''));
    if (!$ga) return;
    ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($ga); ?>"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?php echo esc_js($ga); ?>');</script>
    <?php
}, 1);
