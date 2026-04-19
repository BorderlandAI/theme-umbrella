<?php
/**
 * Ryder chat widget loader.
 * Per-store pages pin data-store; unified pages use data-prompt-location="true".
 */
if (!defined('ABSPATH')) exit;

add_action('wp_footer', function() {
    $store = bl_current_store();
    $slug  = $store ? $store['chat_slug'] : 'umbrella';
    $prompt = $store ? 'false' : 'true';
    printf(
        '<script async src="%s" data-store="%s" data-prompt-location="%s"></script>' . "\n",
        esc_url(BORDERLAND_CHAT_WIDGET),
        esc_attr($slug),
        esc_attr($prompt)
    );
});
