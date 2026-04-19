<?php
/**
 * Pretty URL rewrites for /stores/{slug}/... routes.
 * Maps to bound CMS pages, injecting bl_store + bl_stock query vars.
 */
if (!defined('ABSPATH')) exit;

add_filter('query_vars', function($vars) {
    $vars[] = 'bl_store';
    $vars[] = 'bl_stock';
    $vars[] = 'bl_scope';
    return $vars;
});

add_action('init', function() {
    $stores = '(brandon|morden|portage|thompson)';

    // /stores index
    add_rewrite_rule('^stores/?$', 'index.php?pagename=stores', 'top');

    // /stores/{slug} landing
    add_rewrite_rule('^stores/' . $stores . '/?$', 'index.php?pagename=store&bl_store=$matches[1]', 'top');

    // /stores/{slug}/inventory list
    add_rewrite_rule('^stores/' . $stores . '/inventory/?$', 'index.php?pagename=inventory&bl_store=$matches[1]&bl_scope=store', 'top');

    // /stores/{slug}/inventory/{stock} detail
    add_rewrite_rule('^stores/' . $stores . '/inventory/([^/]+)/?$', 'index.php?pagename=vehicle-detail&bl_store=$matches[1]&bl_stock=$matches[2]', 'top');

    // /stores/{slug}/service|parts|contact
    add_rewrite_rule('^stores/' . $stores . '/service/?$', 'index.php?pagename=service&bl_store=$matches[1]', 'top');
    add_rewrite_rule('^stores/' . $stores . '/parts/?$',   'index.php?pagename=parts&bl_store=$matches[1]',   'top');
    add_rewrite_rule('^stores/' . $stores . '/contact/?$', 'index.php?pagename=contact&bl_store=$matches[1]', 'top');

    // /inventory unified
    add_rewrite_rule('^inventory/?$', 'index.php?pagename=inventory&bl_scope=all', 'top');
});

/**
 * Ensure rewrite rules flush once when theme activates or when version changes.
 */
add_action('init', function() {
    $ver = '1';
    if (get_option('bl_rewrite_version') !== $ver) {
        flush_rewrite_rules(false);
        update_option('bl_rewrite_version', $ver);
    }
}, 99);
