<?php
/**
 * Template Name: Store Landing (/stores/{slug})
 *
 * Editorial-industrial redesign: dealership service-board meets
 * motorsport magazine. Antonio display + JetBrains Mono for specs,
 * asymmetric featured grid, real brand decals, open/closed signal.
 */
get_header();

$store = bl_current_store();
if (!$store) {
    wp_redirect(home_url('/stores/'), 302);
    exit;
}

// ---- open/closed right now (America/Winnipeg) ----
$tz  = new DateTimeZone('America/Winnipeg');
$now = new DateTime('now', $tz);
$day_full_to_code = [
    'Monday' => 'Mo', 'Tuesday' => 'Tu', 'Wednesday' => 'We',
    'Thursday' => 'Th', 'Friday' => 'Fr', 'Saturday' => 'Sa', 'Sunday' => 'Su',
];
$today_code  = $day_full_to_code[$now->format('l')] ?? '';
$today_range = $store['hours'][$today_code] ?? 'closed';
$is_open     = false;
$today_open  = '';
$today_close = '';
if ($today_range && $today_range !== 'closed' && strpos($today_range, '-') !== false) {
    [$op, $cl] = array_pad(explode('-', $today_range), 2, '');
    $ymd = $now->format('Y-m-d');
    try {
        $odt = new DateTime("$ymd $op:00", $tz);
        $cdt = new DateTime("$ymd $cl:00", $tz);
        $is_open  = $now >= $odt && $now <= $cdt;
        $today_open  = $odt->format('g:i A');
        $today_close = $cdt->format('g:i A');
    } catch (Exception $e) { /* ignore */ }
}

$days_ordered = [
    'Mo' => 'Mon', 'Tu' => 'Tue', 'We' => 'Wed', 'Th' => 'Thu',
    'Fr' => 'Fri', 'Sa' => 'Sat', 'Su' => 'Sun',
];

// featured inventory — uniform grid, no hero
$feature_pool = bl_featured_inventory($store['slug'], 6);

// store "number" plate — index in the registry (1..4) for masthead flavor
$store_num = 0;
foreach (array_keys(bl_stores()) as $i => $slug) {
    if ($slug === $store['slug']) { $store_num = $i + 1; break; }
}

$coords_label = '';
if (!empty($store['coords']['lat']) && !empty($store['coords']['lng'])) {
    $coords_label = sprintf('%.4f° N · %.4f° W', (float) $store['coords']['lat'], abs((float) $store['coords']['lng']));
}

$dir_url = 'https://www.google.com/maps/dir/?api=1&destination=' .
    urlencode(trim(($store['address'] ? $store['address'] . ', ' : '') . $store['city'] . ', ' . $store['region']));
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Antonio:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

<style>
/* =====================================================================
   Store landing — scoped to .bl-store-page
   ===================================================================== */
body.bl-umbrella .bl-store-page {
  --accent:  #00b4d8;
  --link:    #088fc2;
  --warm:    #ff9f1c;
  --ink:     #ffffff;
  --ink-2:   #c9d7e0;
  --ink-3:   #7a8594;
  --paper:   #0b0e13;
  --paper-2: #12161c;
  --paper-3: #1a1f26;
  --rule:    rgba(255,255,255,0.08);
  --rule-2:  rgba(255,255,255,0.18);
  --display: 'Antonio', 'Oswald', 'Helvetica Neue', Impact, sans-serif;
  --mono:    'JetBrains Mono', 'IBM Plex Mono', ui-monospace, Menlo, monospace;
  background: var(--paper);
  color: var(--ink-2);
  position: relative;
  overflow-x: hidden;
}
/* noise texture */
body.bl-umbrella .bl-store-page::before {
  content: '';
  position: absolute; inset: 0;
  pointer-events: none;
  opacity: 0.035;
  z-index: 0;
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='160' height='160'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/><feColorMatrix values='0 0 0 0 1  0 0 0 0 1  0 0 0 0 1  0 0 0 1 0'/></filter><rect width='100%' height='100%' filter='url(%23n)'/></svg>");
  background-size: 160px 160px;
}
body.bl-umbrella .bl-store-page > * { position: relative; z-index: 1; }
body.bl-umbrella .bl-store-page .bl-wrap {
  max-width: 1280px; margin: 0 auto; padding: 0 clamp(20px, 4vw, 56px);
}
body.bl-umbrella .bl-store-page .kicker {
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  color: var(--ink-3);
  display: inline-flex; align-items: center; gap: 10px;
  margin: 0 0 22px;
}
body.bl-umbrella .bl-store-page .kicker::before {
  content: ''; width: 28px; height: 1px; background: var(--rule-2);
}
body.bl-umbrella .bl-store-page h2,
body.bl-umbrella .bl-store-page h3 {
  font-family: var(--display);
  color: var(--ink);
  text-transform: uppercase;
  letter-spacing: 0.01em;
  line-height: 1;
  margin: 0;
}

/* =============== MASTHEAD =============== */
.bl-store-mast {
  padding: clamp(40px, 7vw, 90px) 0 clamp(60px, 8vw, 110px);
  border-bottom: 1px solid var(--rule);
  position: relative;
}
.bl-store-mast__rail {
  display: flex; justify-content: space-between; align-items: center;
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--ink-3);
  padding-bottom: clamp(30px, 5vw, 60px);
  border-bottom: 1px solid var(--rule);
  margin-bottom: clamp(30px, 5vw, 60px);
}
.bl-store-mast__rail span:first-child { color: var(--accent); }
.bl-store-mast__inner {
  display: grid;
  grid-template-columns: 1fr;
  gap: clamp(24px, 4vw, 56px);
  align-items: center;
}
.bl-store-mast__inner.has-photo {
  grid-template-columns: minmax(0, 1fr) minmax(320px, 42%);
}
@media (max-width: 900px) {
  .bl-store-mast__inner.has-photo { grid-template-columns: 1fr; }
}
.bl-store-mast__photo {
  position: relative;
  overflow: hidden;
  border: 1px solid var(--rule);
  background: var(--paper-2);
  aspect-ratio: 4 / 3;
}
.bl-store-mast__photo img {
  width: 100%; height: 100%;
  object-fit: cover; object-position: center;
  display: block;
  transition: transform .6s cubic-bezier(.22,1,.36,1);
}
.bl-store-mast__photo:hover img { transform: scale(1.02); }
.bl-store-mast__photo::after {
  content: '';
  position: absolute;
  left: 0; right: 0; bottom: 0;
  height: 40%;
  background: linear-gradient(to top, rgba(11,14,19,0.6), rgba(11,14,19,0));
  pointer-events: none;
}
.bl-store-mast__photo .tag {
  position: absolute;
  left: 16px; bottom: 16px;
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--ink);
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(6px);
  padding: 6px 10px;
  border: 1px solid var(--rule-2);
}
.bl-store-mast__headline {
  font-family: var(--display);
  font-weight: 600;
  color: var(--ink);
  text-transform: uppercase;
  font-size: clamp(42px, 6vw, 84px);
  line-height: 0.95;
  letter-spacing: -0.005em;
  margin: 0;
  max-width: 14ch;
}
.bl-store-mast__headline .full {
  color: var(--ink-3);
  font-weight: 300;
  font-size: 13px;
  letter-spacing: 0.24em;
  display: block;
  margin-bottom: 16px;
  text-transform: uppercase;
  font-family: var(--mono);
}
.bl-store-mast__sub {
  display: flex; flex-wrap: wrap; align-items: center; gap: 18px;
  margin-top: clamp(24px, 4vw, 40px);
  font-family: var(--mono);
  font-size: 13px;
  letter-spacing: 0.06em;
  color: var(--ink-2);
}
.bl-store-mast__sub .dot { width: 4px; height: 4px; border-radius: 50%; background: var(--ink-3); }
.bl-store-mast__status {
  display: inline-flex; align-items: center; gap: 8px;
  font-family: var(--mono);
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  padding: 7px 12px 6px;
  border: 1px solid currentColor;
  border-radius: 999px;
  color: var(--warm);
}
.bl-store-mast__status--open { color: #19e6a4; }
.bl-store-mast__status .pulse {
  width: 8px; height: 8px; border-radius: 50%; background: currentColor;
  box-shadow: 0 0 0 0 currentColor;
  animation: bl-pulse 1.8s infinite;
}
@keyframes bl-pulse {
  0%   { box-shadow: 0 0 0 0 rgba(25,230,164, 0.45); }
  70%  { box-shadow: 0 0 0 10px rgba(25,230,164, 0); }
  100% { box-shadow: 0 0 0 0 rgba(25,230,164, 0); }
}
.bl-store-mast__cta {
  display: flex; gap: 12px; flex-wrap: wrap;
  margin-top: clamp(24px, 3vw, 36px);
}
.bl-store-mast__cta a {
  display: inline-flex; align-items: center; gap: 10px;
  font-size: 14px;
  font-weight: 600;
  padding: 13px 22px;
  border-radius: 6px;
  color: #fff !important;
  text-decoration: none !important;
  transition: background .2s ease, transform .2s ease;
  background: transparent;
  border: 1px solid var(--rule-2);
}
.bl-store-mast__cta a:hover { background: rgba(255,255,255,0.05); transform: translateY(-1px); }
.bl-store-mast__cta a.is-primary {
  background: var(--link);
  border-color: var(--link);
}
.bl-store-mast__cta a.is-primary:hover {
  background: var(--accent);
  border-color: var(--accent);
}
.bl-store-mast__cta a svg { width: 15px; height: 15px; flex-shrink: 0; }
.bl-store-mast__numplate { display: none; }

/* =============== STATUS BOARD =============== */
.bl-store-board {
  padding: clamp(50px, 7vw, 90px) 0;
  border-bottom: 1px solid var(--rule);
}
.bl-store-board__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0;
  border: 1px solid var(--rule);
  background: var(--paper-2);
}
@media (max-width: 860px) { .bl-store-board__grid { grid-template-columns: 1fr; } }
.bl-store-board__cell {
  padding: clamp(28px, 4vw, 44px);
  border-right: 1px solid var(--rule);
  position: relative;
}
.bl-store-board__cell:last-child { border-right: 0; }
@media (max-width: 860px) {
  .bl-store-board__cell { border-right: 0; border-bottom: 1px solid var(--rule); }
  .bl-store-board__cell:last-child { border-bottom: 0; }
}
.bl-store-board__label {
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  color: var(--ink-3);
  margin: 0 0 22px;
}
/* HOURS cell */
.bl-store-board__hours { list-style: none; padding: 0; margin: 0; font-family: var(--mono); font-size: 14px; }
.bl-store-board__hours li {
  display: flex; justify-content: space-between; align-items: baseline;
  padding: 10px 0;
  border-bottom: 1px dashed var(--rule);
  color: var(--ink-2);
}
.bl-store-board__hours li:last-child { border-bottom: 0; }
.bl-store-board__hours li.is-today {
  color: var(--ink);
  font-weight: 700;
}
.bl-store-board__hours li.is-today::before {
  content: '●';
  color: var(--accent);
  margin-right: 8px;
  font-size: 10px;
  position: relative; top: -2px;
}
.bl-store-board__hours li .day { text-transform: uppercase; letter-spacing: 0.1em; font-size: 12px; }
.bl-store-board__hours li .range { color: var(--ink-2); font-size: 13px; }
.bl-store-board__hours li.is-closed .range { color: var(--ink-3); }
/* LOCATION cell */
.bl-store-board__loc h3 {
  font-size: clamp(22px, 2.5vw, 30px);
  font-weight: 500;
  margin: 0 0 10px;
}
.bl-store-board__loc p {
  font-family: var(--mono);
  font-size: 13px;
  line-height: 1.55;
  color: var(--ink-2);
  margin: 0 0 22px;
}
.bl-store-board__loc a.dir {
  display: inline-flex; align-items: center; gap: 8px;
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--accent) !important;
  text-decoration: none !important;
  padding-bottom: 4px;
  border-bottom: 1px solid var(--accent);
}
.bl-store-board__loc a.dir svg { width: 14px; height: 14px; }
/* CONTACT cell */
.bl-store-board__contact .phone {
  font-family: var(--display);
  font-size: clamp(34px, 4vw, 52px);
  font-weight: 500;
  line-height: 1;
  letter-spacing: -0.01em;
  color: var(--ink) !important;
  text-decoration: none !important;
  display: block;
  margin-bottom: 14px;
}
.bl-store-board__contact .phone:hover { color: var(--accent) !important; }
.bl-store-board__contact .extras { font-family: var(--mono); font-size: 12px; color: var(--ink-3); line-height: 1.7; }
.bl-store-board__contact .extras a { color: var(--link) !important; }

/* =============== BRAND WALL =============== */
.bl-store-brands {
  padding: clamp(50px, 7vw, 90px) 0;
  border-bottom: 1px solid var(--rule);
}
.bl-store-brands__head {
  display: grid; grid-template-columns: auto 1fr; align-items: baseline; gap: 32px;
  margin-bottom: clamp(30px, 4vw, 50px);
}
@media (max-width: 760px) { .bl-store-brands__head { grid-template-columns: 1fr; gap: 12px; } }
.bl-store-brands__headline {
  font-family: var(--display);
  font-size: clamp(36px, 5vw, 68px);
  font-weight: 500;
  color: var(--ink);
  text-transform: uppercase;
  line-height: 0.95;
  letter-spacing: -0.005em;
}
.bl-store-brands__count {
  font-family: var(--mono);
  font-size: 13px;
  color: var(--ink-3);
  text-transform: uppercase;
  letter-spacing: 0.18em;
  text-align: right;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--rule);
}
@media (max-width: 760px) { .bl-store-brands__count { text-align: left; } }
.bl-store-brands__count strong { color: var(--ink); font-size: 18px; letter-spacing: 0; }
.bl-store-brands__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 14px;
}
.bl-store-brands__decal {
  display: flex; flex-direction: column; gap: 12px;
  padding: 22px 20px;
  background: var(--paper-2);
  border: 1px solid var(--rule);
  transition: border-color .2s ease, transform .2s ease, background .2s ease;
}
.bl-store-brands__decal:hover { border-color: var(--accent); background: var(--paper-3); transform: translateY(-2px); }
.bl-store-brands__decal .logo {
  background: #fff;
  border-radius: 4px;
  height: 68px;
  display: flex; align-items: center; justify-content: center;
  padding: 10px 14px;
}
.bl-store-brands__decal .logo img {
  max-height: 100%; max-width: 100%;
  width: auto; height: auto;
  object-fit: contain;
  display: block;
}
.bl-store-brands__decal .logo.is-text {
  background: transparent;
  border: 1px dashed var(--rule-2);
  color: var(--ink);
  font-family: var(--display);
  font-size: 28px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}
.bl-store-brands__decal .name {
  font-family: var(--mono);
  font-size: 11px;
  color: var(--ink-3);
  text-transform: uppercase;
  letter-spacing: 0.2em;
  margin: 0;
}

/* =============== ACTION TILES =============== */
.bl-store-services {
  padding: clamp(50px, 7vw, 90px) 0;
  border-bottom: 1px solid var(--rule);
}
.bl-store-services__grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0;
  border: 1px solid var(--rule);
}
@media (max-width: 980px) { .bl-store-services__grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 560px) { .bl-store-services__grid { grid-template-columns: 1fr; } }
.bl-store-services__tile {
  position: relative;
  padding: clamp(30px, 3.5vw, 48px) clamp(22px, 2.5vw, 34px);
  background: var(--paper-2);
  color: var(--ink-2) !important;
  text-decoration: none !important;
  display: flex; flex-direction: column; gap: 30px;
  min-height: 260px;
  border-right: 1px solid var(--rule);
  transition: background .3s ease;
  overflow: hidden;
}
.bl-store-services__tile:last-child { border-right: 0; }
@media (max-width: 980px) {
  .bl-store-services__tile:nth-child(2) { border-right: 0; }
  .bl-store-services__tile:nth-child(-n+2) { border-bottom: 1px solid var(--rule); }
}
@media (max-width: 560px) {
  .bl-store-services__tile { border-right: 0; border-bottom: 1px solid var(--rule); }
  .bl-store-services__tile:last-child { border-bottom: 0; }
}
.bl-store-services__tile::after {
  content: '';
  position: absolute;
  left: 0; right: 0; bottom: 0;
  height: 3px;
  background: var(--accent);
  transform: scaleX(0);
  transform-origin: left center;
  transition: transform .4s cubic-bezier(.22,1,.36,1);
}
.bl-store-services__tile:hover { background: var(--paper-3); }
.bl-store-services__tile:hover::after { transform: scaleX(1); }
.bl-store-services__tile .icon {
  width: 44px; height: 44px;
  color: var(--accent);
}
.bl-store-services__tile .icon svg { width: 100%; height: 100%; }
.bl-store-services__tile .num {
  position: absolute;
  top: 18px; right: 22px;
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 0.2em;
  color: var(--ink-3);
}
.bl-store-services__tile .kick {
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  color: var(--ink-3);
  margin: 0;
}
.bl-store-services__tile h3 {
  font-family: var(--display);
  font-size: clamp(30px, 3vw, 42px);
  font-weight: 500;
  line-height: 0.95;
  color: var(--ink);
  margin: 6px 0 0;
}
.bl-store-services__tile .sub {
  font-family: inherit;
  color: var(--ink-2);
  font-size: 14px;
  line-height: 1.55;
  margin: 10px 0 0;
}
.bl-store-services__tile .go {
  margin-top: auto;
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--accent);
  display: inline-flex; align-items: center; gap: 8px;
}
.bl-store-services__tile .go svg { width: 14px; height: 14px; transition: transform .3s ease; }
.bl-store-services__tile:hover .go svg { transform: translateX(4px); }

/* =============== FEATURED INVENTORY =============== */
.bl-store-feat {
  padding: clamp(50px, 7vw, 90px) 0;
  border-bottom: 1px solid var(--rule);
}
.bl-store-feat__head {
  display: flex; justify-content: space-between; align-items: baseline;
  margin-bottom: clamp(30px, 4vw, 50px);
  gap: 20px; flex-wrap: wrap;
}
.bl-store-feat__headline {
  font-family: var(--display);
  font-size: clamp(36px, 5vw, 68px);
  font-weight: 500;
  color: var(--ink);
  text-transform: uppercase;
  line-height: 0.95;
}
.bl-store-feat__headline em {
  font-style: normal;
  color: var(--accent);
  font-weight: 400;
}
.bl-store-feat__all {
  font-family: var(--mono);
  font-size: 12px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--ink) !important;
  text-decoration: none !important;
  padding-bottom: 6px;
  border-bottom: 1px solid var(--accent);
  white-space: nowrap;
}
.bl-store-feat__all:hover { color: var(--accent) !important; }
.bl-store-feat__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}
@media (max-width: 900px) { .bl-store-feat__grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 560px) { .bl-store-feat__grid { grid-template-columns: 1fr; } }
.bl-store-feat__card {
  display: block;
  text-decoration: none !important;
  color: inherit !important;
  background: var(--paper-2);
  border: 1px solid var(--rule);
  overflow: hidden;
  transition: border-color .2s ease, transform .2s ease;
}
.bl-store-feat__card:hover { border-color: var(--accent); transform: translateY(-2px); }
.bl-store-feat__card .img {
  aspect-ratio: 4 / 3;
  background-size: cover;
  background-position: center;
  background-color: #0f1318;
  transition: transform .5s cubic-bezier(.22,1,.36,1);
}
.bl-store-feat__card:hover .img { transform: scale(1.03); }
.bl-store-feat__card .body {
  padding: 18px 20px 22px;
  display: flex; flex-direction: column; gap: 6px;
}
.bl-store-feat__card .stk {
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--ink-3);
}
.bl-store-feat__card h3 {
  font-family: var(--display);
  font-weight: 500;
  font-size: 22px;
  line-height: 1.05;
  color: var(--ink);
  margin: 4px 0 2px;
  text-transform: none;
  letter-spacing: -0.005em;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
.bl-store-feat__card .price {
  font-family: var(--display);
  font-weight: 600;
  font-size: 22px;
  color: var(--accent);
}

/* =============== OUTRO =============== */
.bl-store-outro {
  position: relative;
  padding: clamp(80px, 11vw, 140px) 0;
  overflow: hidden;
  text-align: center;
}
.bl-store-outro__watermark {
  position: absolute;
  left: 50%; top: 50%;
  transform: translate(-50%, -50%);
  font-family: var(--display);
  font-weight: 700;
  color: transparent;
  -webkit-text-stroke: 1px rgba(255,255,255,0.06);
  font-size: clamp(120px, 22vw, 360px);
  line-height: 0.82;
  white-space: nowrap;
  letter-spacing: -0.02em;
  pointer-events: none;
  user-select: none;
  z-index: 0;
}
.bl-store-outro__body { position: relative; z-index: 1; }
.bl-store-outro__kicker {
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 0.25em;
  text-transform: uppercase;
  color: var(--ink-3);
  margin-bottom: 24px;
}
.bl-store-outro__headline {
  font-family: var(--display);
  font-weight: 500;
  color: var(--ink);
  font-size: clamp(34px, 4.2vw, 58px);
  line-height: 1;
  letter-spacing: -0.005em;
  margin: 0 auto 34px;
  max-width: 820px;
}
.bl-store-outro__phone {
  font-family: var(--display);
  font-weight: 600;
  font-size: clamp(44px, 7vw, 92px);
  color: var(--accent) !important;
  text-decoration: none !important;
  line-height: 1;
  display: inline-block;
  letter-spacing: -0.01em;
}
.bl-store-outro__phone:hover { color: #49d1f0 !important; }

/* =============== STAGGERED REVEAL =============== */
.bl-store-page .reveal {
  opacity: 0;
  transform: translateY(18px);
  animation: bl-reveal .9s cubic-bezier(.22,1,.36,1) forwards;
}
@keyframes bl-reveal { to { opacity: 1; transform: translateY(0); } }
.bl-store-page .reveal--1 { animation-delay: .05s; }
.bl-store-page .reveal--2 { animation-delay: .18s; }
.bl-store-page .reveal--3 { animation-delay: .31s; }
.bl-store-page .reveal--4 { animation-delay: .44s; }
</style>

<main id="content" class="bl-page bl-store-page" data-store="<?php echo esc_attr($store['slug']); ?>">

  <!-- =============== MASTHEAD =============== -->
  <section class="bl-store-mast">
    <div class="bl-wrap">
      <div class="bl-store-mast__rail reveal reveal--1">
        <span><?php printf('LOC · NO. %02d', $store_num); ?></span>
        <span><?php echo esc_html($store['city'] . ', ' . $store['region'] . ' · CA'); ?></span>
        <?php if ($coords_label): ?><span><?php echo esc_html($coords_label); ?></span><?php endif; ?>
      </div>

      <?php
      $photo_file = !empty($store['photo']) ? $store['photo'] : '';
      $photo_path = $photo_file ? get_template_directory() . '/assets/images/stores/' . $photo_file : '';
      $photo_url  = $photo_file ? get_template_directory_uri() . '/assets/images/stores/' . $photo_file : '';
      $has_photo  = $photo_path && file_exists($photo_path);
      ?>
      <div class="bl-store-mast__inner <?php echo $has_photo ? 'has-photo' : ''; ?>">
        <div>
          <h1 class="bl-store-mast__headline reveal reveal--2">
            <span class="full">Borderland Powersports</span>
            <?php echo esc_html($store['name']); ?>
          </h1>

          <div class="bl-store-mast__sub reveal reveal--3">
            <span class="bl-store-mast__status <?php echo $is_open ? 'bl-store-mast__status--open' : ''; ?>">
              <span class="pulse"></span>
              <?php echo $is_open ? 'Open Now' : 'Closed'; ?>
            </span>
            <span class="dot"></span>
            <span>
              <?php if ($is_open && $today_close): ?>
                Until <?php echo esc_html($today_close); ?>
              <?php elseif ($today_range !== 'closed' && $today_open): ?>
                Today <?php echo esc_html($today_open); ?>&ndash;<?php echo esc_html($today_close); ?>
              <?php else: ?>
                Closed today
              <?php endif; ?>
            </span>
            <?php if ($store['address']): ?>
              <span class="dot"></span>
              <span><?php echo esc_html($store['address']); ?></span>
            <?php endif; ?>
          </div>

          <div class="bl-store-mast__cta reveal reveal--4">
            <a class="is-primary" href="tel:<?php echo esc_attr($store['phone_tel']); ?>">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13 1.05.37 2.07.72 3.06a2 2 0 0 1-.45 2.11L8.09 10.28a16 16 0 0 0 6 6l1.39-1.39a2 2 0 0 1 2.11-.45c.99.35 2.01.59 3.06.72A2 2 0 0 1 22 16.92z"/></svg>
              <?php echo esc_html($store['phone']); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/inventory/')); ?>">
              Browse Inventory
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <?php if ($store['address']): ?>
              <a href="<?php echo esc_url($dir_url); ?>" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                Directions
              </a>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($has_photo): ?>
          <div class="bl-store-mast__photo reveal reveal--4">
            <img src="<?php echo esc_url($photo_url); ?>" alt="<?php echo esc_attr($store['full_name']); ?> storefront" loading="lazy" />
            <span class="tag"><?php echo esc_html($store['city']); ?>, <?php echo esc_html($store['region']); ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>


  <!-- =============== STATUS BOARD =============== -->
  <section class="bl-store-board">
    <div class="bl-wrap">
      <p class="kicker">01 &nbsp;/&nbsp; The Details</p>
      <div class="bl-store-board__grid">
        <!-- HOURS -->
        <div class="bl-store-board__cell">
          <p class="bl-store-board__label">Hours · America/Winnipeg</p>
          <ul class="bl-store-board__hours">
            <?php foreach ($days_ordered as $code => $short):
              $range = $store['hours'][$code] ?? 'closed';
              $is_today_row = ($code === $today_code);
              $closed_row = ($range === 'closed' || !$range);
              $display = 'Closed';
              if (!$closed_row && strpos($range, '-') !== false) {
                  [$o, $c] = array_pad(explode('-', $range), 2, '');
                  try {
                      $display = (new DateTime("1970-01-01 $o:00"))->format('g:i A') . ' – ' . (new DateTime("1970-01-01 $c:00"))->format('g:i A');
                  } catch (Exception $e) { $display = $range; }
              }
            ?>
              <li class="<?php echo $is_today_row ? 'is-today' : ''; ?> <?php echo $closed_row ? 'is-closed' : ''; ?>">
                <span class="day"><?php echo esc_html($short); ?></span>
                <span class="range"><?php echo esc_html($display); ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- LOCATION -->
        <div class="bl-store-board__cell bl-store-board__loc">
          <p class="bl-store-board__label">Location</p>
          <h3><?php echo esc_html($store['city']); ?>, <?php echo esc_html($store['region']); ?></h3>
          <p>
            <?php if ($store['address']): ?><?php echo esc_html($store['address']); ?><br><?php endif; ?>
            <?php echo esc_html($store['city'] . ', ' . $store['region']); ?><?php if (!empty($store['postal'])): ?>, <?php echo esc_html($store['postal']); ?><?php endif; ?>
          </p>
          <a class="dir" href="<?php echo esc_url($dir_url); ?>" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            Get Directions
          </a>
        </div>

        <!-- CONTACT -->
        <div class="bl-store-board__cell bl-store-board__contact">
          <p class="bl-store-board__label">Contact</p>
          <a class="phone" href="tel:<?php echo esc_attr($store['phone_tel']); ?>"><?php echo esc_html($store['phone']); ?></a>
          <div class="extras">
            <?php if (!empty($store['email'])): ?>
              <?php echo esc_html($store['email']); ?><br>
            <?php endif; ?>
            <a href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/contact/')); ?>">Send a message</a>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- =============== BRAND WALL =============== -->
  <section class="bl-store-brands">
    <div class="bl-wrap">
      <p class="kicker">02 &nbsp;/&nbsp; Brands Stocked</p>
      <div class="bl-store-brands__head">
        <h2 class="bl-store-brands__headline">What we carry<br>in <?php echo esc_html($store['city']); ?></h2>
        <p class="bl-store-brands__count"><strong><?php echo count($store['brands']); ?></strong> &nbsp;Brand<?php echo count($store['brands']) === 1 ? '' : 's'; ?> on the floor</p>
      </div>

      <div class="bl-store-brands__grid">
        <?php foreach ($store['brands'] as $brand):
          $logo = bl_brand_logo_url($brand);
        ?>
          <div class="bl-store-brands__decal">
            <?php if ($logo): ?>
              <div class="logo">
                <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($brand); ?> logo" loading="lazy">
              </div>
            <?php else: ?>
              <div class="logo is-text"><?php echo esc_html($brand); ?></div>
            <?php endif; ?>
            <p class="name"><?php echo esc_html($brand); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>


  <!-- =============== ACTION TILES =============== -->
  <?php
  $store_slug   = $store['slug'];
  $service_url  = home_url('/stores/' . $store_slug . '/service/');
  $parts_url    = home_url('/stores/' . $store_slug . '/parts/');
  $finance_url  = home_url('/financing/');
  $contact_url  = home_url('/stores/' . $store_slug . '/contact/');

  $icon_wrench = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>';
  $icon_cog    = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>';
  $icon_dollar = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>';
  $icon_msg    = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>';
  $arrow       = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';

  $tiles = [
      ['url' => $service_url, 'kick' => 'Service / Bay',    'title' => 'Service', 'sub' => 'Factory-trained technicians, OEM parts, seasonal prep.',    'icon' => $icon_wrench, 'num' => '03.1'],
      ['url' => $parts_url,   'kick' => 'Parts / Counter',  'title' => 'Parts',   'sub' => 'OEM + aftermarket, rider gear, and accessories in stock.',  'icon' => $icon_cog,    'num' => '03.2'],
      ['url' => $finance_url, 'kick' => 'Finance / Desk',   'title' => 'Financing','sub' => 'Flexible approvals on new + pre-owned — one form, fast turn.','icon' => $icon_dollar,'num' => '03.3'],
      ['url' => $contact_url, 'kick' => 'Front / Desk',     'title' => 'Contact', 'sub' => 'Talk to a real human at ' . $store['name'] . '.',           'icon' => $icon_msg,    'num' => '03.4'],
  ];
  ?>
  <section class="bl-store-services">
    <div class="bl-wrap">
      <p class="kicker">03 &nbsp;/&nbsp; On-Site</p>
      <div class="bl-store-services__grid">
        <?php foreach ($tiles as $t): ?>
          <a class="bl-store-services__tile" href="<?php echo esc_url($t['url']); ?>">
            <span class="num"><?php echo esc_html($t['num']); ?></span>
            <div class="icon"><?php echo $t['icon']; /* trusted inline SVG constant */ ?></div>
            <p class="kick"><?php echo esc_html($t['kick']); ?></p>
            <h3><?php echo esc_html($t['title']); ?></h3>
            <p class="sub"><?php echo esc_html($t['sub']); ?></p>
            <span class="go">
              Enter <?php echo $arrow; ?>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>


  <!-- =============== FEATURED INVENTORY =============== -->
  <?php if (!empty($feature_pool)): ?>
  <section class="bl-store-feat">
    <div class="bl-wrap">
      <p class="kicker">04 &nbsp;/&nbsp; Featured Floor</p>
      <div class="bl-store-feat__head">
        <h2 class="bl-store-feat__headline">On the floor<br>at <em><?php echo esc_html($store['name']); ?></em></h2>
        <a class="bl-store-feat__all" href="<?php echo esc_url(home_url('/stores/' . $store['slug'] . '/inventory/')); ?>">See all inventory</a>
      </div>

      <div class="bl-store-feat__grid">
        <?php foreach ($feature_pool as $v):
          $img   = bl_vehicle_cover_image($v);
          $title = trim(($v['year'] ?? '') . ' ' . ($v['make'] ?? '') . ' ' . ($v['submodel'] ?? $v['model'] ?? ''));
          $price = !empty($v['salePrice']) ? $v['salePrice'] : ($v['basePrice'] ?? null);
          $stock = $v['stockNumber'] ?? $v['id'];
          $href  = home_url('/stores/' . $store['slug'] . '/inventory/' . rawurlencode($stock));
        ?>
          <a class="bl-store-feat__card" href="<?php echo esc_url($href); ?>">
            <?php if ($img): ?>
              <div class="img" style="background-image:url('<?php echo esc_url($img); ?>')"></div>
            <?php else: ?>
              <div class="img"></div>
            <?php endif; ?>
            <div class="body">
              <span class="stk">Stock #<?php echo esc_html($stock); ?></span>
              <h3><?php echo esc_html($title); ?></h3>
              <?php if ($price): ?><span class="price">$<?php echo esc_html(number_format((float) $price, 0)); ?></span><?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>


  <!-- =============== OUTRO =============== -->
  <section class="bl-store-outro">
    <div class="bl-store-outro__watermark" aria-hidden="true"><?php echo esc_html(strtoupper($store['city'])); ?></div>
    <div class="bl-wrap bl-store-outro__body">
      <p class="bl-store-outro__kicker">Drop in or dial</p>
      <p class="bl-store-outro__headline">
        <?php if ($is_open): ?>
          We&rsquo;re open until <?php echo esc_html($today_close); ?> today. Swing by <?php echo esc_html($store['name']); ?>.
        <?php else: ?>
          Closed right now. Ring us first thing — <?php echo esc_html($store['name']); ?>.
        <?php endif; ?>
      </p>
      <a class="bl-store-outro__phone" href="tel:<?php echo esc_attr($store['phone_tel']); ?>"><?php echo esc_html($store['phone']); ?></a>
    </div>
  </section>

</main>

<?php get_footer();
