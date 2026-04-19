<?php
/**
 * News aggregator — pulls RSS from the 4 dealer-site blogs and merges.
 * Called from page-news.php and homepage.
 */
if (!defined('ABSPATH')) exit;

function bl_news_items($limit = 9) {
    $cache_key = 'bl_news_' . $limit;
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;

    $feeds = [
        'brandon'  => 'https://www.borderlandbrandon.com/feed/',
        'morden'   => 'https://www.borderlandpowersports.com/feed/',
        'portage'  => 'https://www.borderlandportage.com/feed/',
        'thompson' => 'https://www.borderlandthompson.com/feed/',
    ];

    include_once ABSPATH . WPINC . '/feed.php';
    $items = [];

    foreach ($feeds as $slug => $url) {
        $rss = fetch_feed($url);
        if (is_wp_error($rss)) continue;
        $n = $rss->get_item_quantity(min(6, $limit));
        foreach ($rss->get_items(0, $n) as $it) {
            $items[] = [
                'store'  => $slug,
                'title'  => $it->get_title(),
                'url'    => $it->get_permalink(),
                'date'   => strtotime($it->get_date()),
                'excerpt' => wp_trim_words(wp_strip_all_tags($it->get_description()), 25, '…'),
            ];
        }
    }

    usort($items, fn($a, $b) => $b['date'] <=> $a['date']);
    $items = array_slice($items, 0, $limit);

    set_transient($cache_key, $items, 30 * MINUTE_IN_SECONDS);
    return $items;
}
