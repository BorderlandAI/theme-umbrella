/**
 * Borderland Group — GA4 conversion tracking.
 * Shared across Portage / Battery / 3R Supply / Umbrella themes.
 *
 * Fires:
 *   - generate_lead   : any form submit (with form_id + form_category params)
 *   - phone_call      : click on <a href="tel:...">
 *   - email_click     : click on <a href="mailto:...">
 *   - outbound_click  : click on any http(s) link to a different host
 *
 * Event params include a `store` value supplied via window.BL_STORE for
 * cross-property reporting.
 */
(function () {
  if (typeof window === 'undefined') return;
  window.dataLayer = window.dataLayer || [];
  function track(name, params) {
    try {
      if (typeof window.gtag === 'function') {
        window.gtag('event', name, Object.assign({ transport_type: 'beacon' }, params || {}));
      } else {
        window.dataLayer.push(['event', name, params || {}]);
      }
    } catch (e) { /* swallow — never break page */ }
  }

  var STORE = window.BL_STORE || 'unknown';

  function classifyForm(form) {
    var id = (form.id || '').toLowerCase();
    var cls = (form.className || '').toLowerCase();
    var action = (form.action || '').toLowerCase();
    var dataCat = form.getAttribute('data-ga-category');
    if (dataCat) return dataCat;
    if (id.indexOf('fin') === 0 || cls.indexOf('finance') >= 0 || action.indexOf('financ') >= 0) return 'finance';
    if (id.indexOf('contact') >= 0 || cls.indexOf('contact') >= 0) return 'contact';
    if (id.indexOf('vehicle') >= 0 || cls.indexOf('vehicle') >= 0 || cls.indexOf('inquiry') >= 0) return 'vehicle_inquiry';
    if (id.indexOf('parts') >= 0 || cls.indexOf('parts') >= 0) return 'parts_inquiry';
    if (id.indexOf('service') >= 0 || cls.indexOf('service') >= 0) return 'service_inquiry';
    return 'general';
  }

  function isLeadForm(form) {
    if (!form) return false;
    if (form.getAttribute('data-ga-track') === 'false') return false;
    var role = (form.getAttribute('role') || '').toLowerCase();
    if (role === 'search') return false;
    var cls = (form.className || '').toLowerCase();
    if (/(^|\s)(search|filter|filters|signup-widget)(\s|$)/.test(cls)) return false;
    var action = (form.action || '').toLowerCase();
    if (action.indexOf('/wp-login') >= 0) return false;
    return true;
  }

  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (!isLeadForm(form)) return;
    track('generate_lead', {
      store: STORE,
      form_id: form.id || '',
      form_category: classifyForm(form),
      page_path: location.pathname,
    });
  }, true);

  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a) return;
    var href = a.getAttribute('href') || '';
    if (href.indexOf('tel:') === 0) {
      track('phone_call', {
        store: STORE,
        phone_number: href.replace(/^tel:/, ''),
        page_path: location.pathname,
        link_text: (a.textContent || '').trim().slice(0, 80),
      });
      return;
    }
    if (href.indexOf('mailto:') === 0) {
      track('email_click', {
        store: STORE,
        email: href.replace(/^mailto:/, '').split('?')[0],
        page_path: location.pathname,
      });
      return;
    }
    if (/^https?:\/\//i.test(href)) {
      try {
        var u = new URL(href);
        if (u.host && u.host !== location.host) {
          track('outbound_click', {
            store: STORE,
            outbound_host: u.host,
            outbound_url: href.slice(0, 200),
          });
        }
      } catch (err) { /* ignore */ }
    }
  }, true);
})();
