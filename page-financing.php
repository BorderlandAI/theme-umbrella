<?php
/**
 * Template Name: Financing
 */
get_header();
$stores = bl_stores();
?>
<main id="content" class="bl-page bl-financing">
  <section class="bl-hero small">
    <div class="inner">
      <h1>Financing</h1>
      <p class="lede">Flexible financing for new and pre-owned powersports and marine vehicles. Submit a secure application — one of our sales teams will reach out.</p>
    </div>
  </section>

  <div class="inner two-col">
    <div class="form-col">
      <h2>Apply Now</h2>
      <form id="blFinancingForm" class="bl-lead-form" data-context="financing" data-success="Thanks! We'll be in touch within one business day." data-error="Something went wrong. Please try again or call us.">
        <label>Preferred Location
          <select name="store" required>
            <option value="">— Choose a store —</option>
            <?php foreach ($stores as $s): ?>
              <option value="<?php echo esc_attr($s['slug']); ?>"><?php echo esc_html($s['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Full Name<input type="text" name="name" required /></label>
        <label>Email<input type="email" name="email" required /></label>
        <label>Phone<input type="tel" name="phone" required /></label>
        <label>Tell us what you're looking for (year, make, model if known)<textarea name="message" rows="4"></textarea></label>
        <label class="inline"><input type="checkbox" name="consent" value="1" required /> I consent to being contacted about financing.</label>
        <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true" />
        <button type="submit" class="btn primary">Submit Application</button>
        <p class="form-status" aria-live="polite"></p>
      </form>
    </div>

    <aside class="info-col">
      <h2>How It Works</h2>
      <ol>
        <li>Submit the application — takes about 2 minutes.</li>
        <li>Our sales team reaches out within one business day.</li>
        <li>We'll walk through pre-approval options and find the right fit.</li>
      </ol>
      <p>Prefer to call? <a href="<?php echo esc_url(home_url('/stores/')); ?>">Pick a location</a> to get the right number.</p>
    </aside>
  </div>
</main>

<?php get_footer();
