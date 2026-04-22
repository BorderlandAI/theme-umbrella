/**
 * Cross-site behaviours that used to live inline in PHP templates:
 *  - Contact + financing + vehicle-detail lead form AJAX submit
 *  - Open-chat click delegation (replaces inline onclick="openChat('sales')")
 *  - Vehicle-detail gallery prev/next/thumb/lightbox
 *
 * Loaded globally (cheap, event-delegated — only binds when the target elements exist).
 */
(function() {
  'use strict';

  // ---------- Lead form AJAX (contact, financing, any .bl-lead-form) ----------
  var leadForms = document.querySelectorAll('form.bl-lead-form');
  leadForms.forEach(function(form) {
    var successMsg = form.getAttribute('data-success') ||
      "Thanks! We'll be in touch shortly.";
    var errorMsg   = form.getAttribute('data-error') ||
      "Something went wrong. Please try again or call us directly.";

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      var status = form.querySelector('.form-status');
      if (status) status.textContent = 'Sending…';
      var data = new FormData(form);
      data.append('action',     'bl_submit_lead');
      data.append('nonce',      (window.BL && window.BL.nonce) || '');
      data.append('source_url', window.location.href);
      var url = (window.BL && window.BL.ajax_url) || '/wp-admin/admin-ajax.php';
      fetch(url, { method: 'POST', body: data, credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(j) {
          if (!status) return;
          if (j && j.success) {
            form.reset();
            status.textContent = successMsg;
          } else {
            status.textContent = errorMsg;
          }
        })
        .catch(function() {
          if (status) status.textContent = 'Connection issue. Please try again.';
        });
    });
  });

  // ---------- Delegated openChat() calls ----------
  // Replaces inline onclick="openChat('sales')" in header + vehicle-detail.
  // Elements now opt-in via data-chat-open="sales|parts|service".
  document.addEventListener('click', function(e) {
    var trigger = e.target.closest('[data-chat-open]');
    if (!trigger) return;
    e.preventDefault();
    var dept = trigger.getAttribute('data-chat-open') || 'sales';
    if (typeof window.openChat === 'function') window.openChat(dept);
  });

  // ---------- Vehicle-detail gallery + lightbox ----------
  var root = document.querySelector('.bl-vehicle-detail .gallery');
  if (!root) return;
  var urlsAttr = root.getAttribute('data-gallery');
  if (!urlsAttr) return;
  var URLS; try { URLS = JSON.parse(urlsAttr); } catch (err) { URLS = []; }
  if (!URLS || URLS.length < 2) return;

  var main    = root.querySelector('img.main');
  var thumbs  = root.querySelectorAll('.thumbs img');
  var counter = root.querySelector('.counter .cur');
  var cur = 0;

  function buildButton(cls, label, text) {
    var b = document.createElement('button');
    b.className = cls;
    b.type = 'button';
    b.setAttribute('aria-label', label);
    b.textContent = text;
    return b;
  }

  function ensureLightbox() {
    var lb = document.getElementById('bl-lightbox');
    if (lb) return lb;
    lb = document.createElement('div');
    lb.id = 'bl-lightbox';
    lb.className = 'bl-lightbox';
    var img = document.createElement('img');
    img.alt = '';
    var close = buildButton('close', 'Close', '\u00D7');
    var prev  = buildButton('nav prev', 'Previous', '\u2039');
    var next  = buildButton('nav next', 'Next', '\u203A');
    lb.appendChild(img);
    lb.appendChild(close);
    lb.appendChild(prev);
    lb.appendChild(next);
    document.body.appendChild(lb);
    close.addEventListener('click', closeLightbox);
    prev.addEventListener('click', function(ev) { ev.stopPropagation(); show(cur - 1); openLightbox(); });
    next.addEventListener('click', function(ev) { ev.stopPropagation(); show(cur + 1); openLightbox(); });
    lb.addEventListener('click', function(ev) { if (ev.target === lb) closeLightbox(); });
    return lb;
  }

  function show(i) {
    cur = ((i % URLS.length) + URLS.length) % URLS.length;
    if (main) { main.src = URLS[cur]; main.setAttribute('data-index', cur); }
    if (counter) counter.textContent = (cur + 1);
    thumbs.forEach(function(t) {
      t.classList.toggle('active', parseInt(t.dataset.index, 10) === cur);
    });
  }

  function openLightbox() {
    var lb = ensureLightbox();
    lb.querySelector('img').src = URLS[cur];
    lb.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    var lb = document.getElementById('bl-lightbox');
    if (lb) { lb.classList.remove('open'); document.body.style.overflow = ''; }
  }

  thumbs.forEach(function(t) {
    t.addEventListener('click', function() { show(parseInt(t.dataset.index, 10)); });
  });
  var prevBtn = root.querySelector('.nav.prev');
  var nextBtn = root.querySelector('.nav.next');
  if (prevBtn) prevBtn.addEventListener('click', function() { show(cur - 1); });
  if (nextBtn) nextBtn.addEventListener('click', function() { show(cur + 1); });
  document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowLeft')  show(cur - 1);
    if (e.key === 'ArrowRight') show(cur + 1);
    if (e.key === 'Escape')     closeLightbox();
  });
  if (main) {
    main.addEventListener('click', openLightbox);
    main.style.cursor = 'zoom-in';
  }
})();
