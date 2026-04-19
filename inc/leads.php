<?php
/**
 * Lead capture — forwards form submissions to lead-crm (Docker network).
 * Endpoint: POST http://lead-crm:8126/inbound/lead
 */
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_bl_submit_lead',         'bl_submit_lead');
add_action('wp_ajax_nopriv_bl_submit_lead',  'bl_submit_lead');

function bl_submit_lead() {
    // Nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bl_lead_nonce')) {
        wp_send_json_error(['error' => 'invalid_nonce'], 400);
    }

    // Honeypot
    if (!empty($_POST['website'])) {
        wp_send_json_success(['ok' => true]); // silently drop bots
    }

    // Rate limit (5 per 15 min per IP)
    $ip = bl_client_ip();
    $rl_key = '_bl_lead_rl_' . md5($ip);
    $count = (int) get_transient($rl_key);
    if ($count >= 5) {
        wp_send_json_error(['error' => 'rate_limited'], 429);
    }
    set_transient($rl_key, $count + 1, 15 * MINUTE_IN_SECONDS);

    // Normalize store
    $store = strtolower(sanitize_text_field($_POST['store'] ?? ''));
    if ($store && !bl_store($store)) $store = ''; // unknown → treat as unassigned

    // Split name → first/last (CRM expects these separately)
    $full_name = trim(sanitize_text_field($_POST['name'] ?? ''));
    $parts = preg_split('/\s+/', $full_name, 2);
    $first = $parts[0] ?? '';
    $last  = $parts[1] ?? '';

    $context = sanitize_text_field($_POST['context'] ?? 'contact');
    // CRM requires interest_type ∈ {parts, service, finance, vehicle, general}
    $interest_type_map = [
        'contact'    => 'general',
        'homepage'   => 'general',
        'financing'  => 'finance',
        'parts'      => 'parts',
        'service'    => 'service',
        'vehicle'    => 'vehicle',
    ];
    $interest_type = $interest_type_map[$context] ?? 'general';

    $payload = [
        'source'        => 'website',           // CRM enum: website|sms|phone|facebook|walk-in|messenger|email
        'channel'       => 'web',
        'store'         => $store ?: null,
        'first_name'    => $first,
        'last_name'     => $last,
        'phone'         => sanitize_text_field($_POST['phone'] ?? ''),
        'email'         => sanitize_email($_POST['email'] ?? ''),
        'interest'      => sanitize_text_field($_POST['interest'] ?? ''),
        'interest_type' => $interest_type,
        'message'       => sanitize_textarea_field($_POST['message'] ?? ''),
        'metadata'      => [
            'site'       => 'umbrella',          // distinguish umbrella leads from Portage dealer-site leads
            'context'    => $context,
            'consent'    => !empty($_POST['consent']),
            'ua'         => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            'ip'         => $ip,
            'source_url' => esc_url_raw($_POST['source_url'] ?? wp_get_referer()),
        ],
    ];

    if (empty($first) || (empty($payload['phone']) && empty($payload['email']))) {
        wp_send_json_error(['error' => 'missing_fields'], 400);
    }

    $resp = wp_remote_post(rtrim(BORDERLAND_LEAD_CRM_URL, '/') . '/inbound/lead', [
        'timeout' => 10,
        'headers' => [
            'Content-Type' => 'application/json',
            'X-API-Key'    => BORDERLAND_LEAD_CRM_KEY,
        ],
        'body' => wp_json_encode($payload),
    ]);

    if (is_wp_error($resp)) {
        error_log('[bl_submit_lead] CRM error: ' . $resp->get_error_message());
        wp_send_json_error(['error' => 'crm_unreachable'], 502);
    }
    $code = wp_remote_retrieve_response_code($resp);
    if ($code < 200 || $code >= 300) {
        error_log('[bl_submit_lead] CRM HTTP ' . $code . ' body=' . wp_remote_retrieve_body($resp));
        wp_send_json_error(['error' => 'crm_http_' . $code], 502);
    }

    wp_send_json_success(['ok' => true]);
}

function bl_client_ip() {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}
