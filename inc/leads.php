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

    if ($interest_type === 'finance') {
        bl_email_finance_lead($payload);
    }

    wp_send_json_success(['ok' => true]);
}

function bl_email_finance_lead($p) {
    $to       = 'Kaitlyn Labossiere <finance@borderlandgroup.ca>';
    $store    = $p['store'] ?: 'Unassigned';
    $subject  = 'New Financing Application — ' . ucfirst($store);
    $m        = $p['metadata'] ?? [];
    $name     = trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));

    $row = function($label, $value) {
        $v = $value !== '' && $value !== null ? esc_html($value) : '<em style="color:#999;">Not provided</em>';
        return '<tr><td style="padding:10px 24px;width:40%;color:#666;font-size:14px;border-bottom:1px solid #eee;">' . esc_html($label) . '</td><td style="padding:10px 24px;font-size:14px;border-bottom:1px solid #eee;color:#222;">' . $v . '</td></tr>';
    };

    $html  = '<div style="font-family:Arial,sans-serif;max-width:650px;margin:0 auto;background:#fff;border:1px solid #ddd;border-radius:8px;overflow:hidden;">';
    $html .= '<div style="background:#e31937;color:#fff;padding:20px 24px;"><h1 style="margin:0;font-size:22px;">Financing Application</h1><p style="margin:6px 0 0;font-size:13px;opacity:.85;">Borderland Powersports &mdash; ' . esc_html(ucfirst($store)) . ' &bull; ' . date('F j, Y \a\t g:i A') . '</p></div>';
    $html .= '<table style="width:100%;border-collapse:collapse;">';
    $html .= $row('Name', $name);
    $html .= $row('Email', $p['email'] ?? '');
    $html .= $row('Phone', $p['phone'] ?? '');
    $html .= $row('Preferred Store', ucfirst($store));
    $html .= $row('Interest', $p['interest'] ?? '');
    $html .= $row('Message', $p['message'] ?? '');
    $html .= $row('Source URL', $m['source_url'] ?? '');
    $html .= '</table></div>';

    wp_mail($to, $subject, $html);
}

/**
 * Resolve the real client IP for rate-limiting purposes.
 *
 * Only trusts CF-Connecting-IP / X-Forwarded-For when the immediate upstream
 * (REMOTE_ADDR) is a known Cloudflare edge IP. Otherwise falls back to
 * REMOTE_ADDR so a malicious direct-to-origin caller can't spoof headers
 * to evade the 5/15-min rate limit.
 */
function bl_client_ip() {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!filter_var($remote, FILTER_VALIDATE_IP)) return '0.0.0.0';

    if (bl_is_cloudflare_ip($remote)) {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
    }
    return $remote;
}

/**
 * True if $ip falls inside one of Cloudflare's published IPv4/IPv6 ranges.
 * List is pulled from https://www.cloudflare.com/ips-v{4,6} and cached 24h.
 */
function bl_is_cloudflare_ip($ip) {
    $cidrs = get_transient('bl_cf_ip_ranges');
    if (!is_array($cidrs)) {
        $cidrs = bl_refresh_cloudflare_ip_ranges();
    }
    foreach ($cidrs as $cidr) {
        if (bl_ip_in_cidr($ip, $cidr)) return true;
    }
    return false;
}

function bl_refresh_cloudflare_ip_ranges() {
    $all = [];
    foreach (['https://www.cloudflare.com/ips-v4', 'https://www.cloudflare.com/ips-v6'] as $url) {
        $resp = wp_remote_get($url, ['timeout' => 5]);
        if (!is_wp_error($resp) && wp_remote_retrieve_response_code($resp) === 200) {
            $body = wp_remote_retrieve_body($resp);
            foreach (preg_split('/\s+/', trim($body)) as $line) {
                if ($line) $all[] = $line;
            }
        }
    }
    if (!$all) {
        // Fallback to a frozen snapshot so we don't fail open on a transient Cloudflare outage.
        $all = [
            // IPv4 (snapshot)
            '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
            '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
            '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
            '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
            // IPv6 (snapshot)
            '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
            '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
        ];
    }
    set_transient('bl_cf_ip_ranges', $all, DAY_IN_SECONDS);
    return $all;
}

/**
 * Returns true if $ip (v4 or v6) is contained in $cidr.
 */
function bl_ip_in_cidr($ip, $cidr) {
    if (strpos($cidr, '/') === false) return false;
    [$subnet, $mask] = explode('/', $cidr, 2);
    $mask = (int) $mask;

    $ip_bin     = @inet_pton($ip);
    $subnet_bin = @inet_pton($subnet);
    if ($ip_bin === false || $subnet_bin === false) return false;
    if (strlen($ip_bin) !== strlen($subnet_bin)) return false; // v4 vs v6 mismatch

    $bytes_full = intdiv($mask, 8);
    $bits_rem   = $mask % 8;

    if ($bytes_full > 0 && substr($ip_bin, 0, $bytes_full) !== substr($subnet_bin, 0, $bytes_full)) {
        return false;
    }
    if ($bits_rem) {
        $mask_byte = ~(0xff >> $bits_rem) & 0xff;
        if ((ord($ip_bin[$bytes_full]) & $mask_byte) !== (ord($subnet_bin[$bytes_full]) & $mask_byte)) {
            return false;
        }
    }
    return true;
}
