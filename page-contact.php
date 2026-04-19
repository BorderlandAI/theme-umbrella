<?php
/**
 * Template Name: Contact
 */
get_header();
$store = bl_current_store();
$stores = bl_stores();
?>
<main id="content" class="bl-page bl-contact">
  <section class="bl-hero small">
    <div class="inner">
      <h1>Contact <?php echo $store ? esc_html($store['name']) : 'Us'; ?></h1>
      <p class="lede"><?php echo $store ? 'Get in touch with our ' . esc_html($store['name']) . ' team.' : 'Reach any of our 4 Manitoba locations.'; ?></p>
    </div>
  </section>

  <div class="inner two-col">
    <div class="form-col">
      <h2>Send Us a Message</h2>
      <form id="blContactForm" class="bl-lead-form" data-context="contact">
        <?php if (!$store): ?>
          <label>Which location?
            <select name="store" required>
              <option value="">— Choose a store —</option>
              <?php foreach ($stores as $s): ?>
                <option value="<?php echo esc_attr($s['slug']); ?>"><?php echo esc_html($s['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </label>
        <?php else: ?>
          <input type="hidden" name="store" value="<?php echo esc_attr($store['slug']); ?>" />
        <?php endif; ?>
        <label>Name<input type="text" name="name" required /></label>
        <label>Email<input type="email" name="email" /></label>
        <label>Phone<input type="tel" name="phone" /></label>
        <label>Message<textarea name="message" rows="5" required></textarea></label>
        <label class="inline"><input type="checkbox" name="consent" value="1" required /> I consent to being contacted.</label>
        <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true" />
        <button type="submit" class="btn primary">Send Message</button>
        <p class="form-status" aria-live="polite"></p>
      </form>
    </div>

    <aside class="stores-col">
      <h2>Our Locations</h2>
      <ul class="loc-list">
        <?php foreach ($stores as $s): ?>
          <li<?php echo $store && $store['slug'] === $s['slug'] ? ' class="active"' : ''; ?>>
            <h3><a href="<?php echo esc_url(home_url('/stores/' . $s['slug'])); ?>"><?php echo esc_html($s['name']); ?></a></h3>
            <?php if ($s['address']): ?><p><?php echo esc_html($s['address']); ?></p><?php endif; ?>
            <p><?php echo esc_html($s['city'] . ', ' . $s['region']); ?></p>
            <p><a href="tel:<?php echo esc_attr($s['phone_tel']); ?>"><?php echo esc_html($s['phone']); ?></a></p>
            <p><?php echo esc_html(bl_hours_display($s)); ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    </aside>
  </div>
</main>

<script>
(function(){
  var form = document.getElementById('blContactForm');
  if (!form) return;
  form.addEventListener('submit', function(e){
    e.preventDefault();
    var status = form.querySelector('.form-status');
    status.textContent = 'Sending…';
    var data = new FormData(form);
    data.append('action', 'bl_submit_lead');
    data.append('nonce', (window.BL && window.BL.nonce) || '');
    data.append('source_url', window.location.href);
    fetch((window.BL && window.BL.ajax_url) || '/wp-admin/admin-ajax.php', { method:'POST', body:data, credentials:'same-origin' })
      .then(function(r){ return r.json(); })
      .then(function(j){
        if (j && j.success) { form.reset(); status.textContent = 'Thanks! We\'ll be in touch shortly.'; }
        else { status.textContent = 'Something went wrong. Please try again or call us directly.'; }
      })
      .catch(function(){ status.textContent = 'Connection issue. Please try again.'; });
  });
})();
</script>

<?php get_footer();
