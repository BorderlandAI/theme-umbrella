<?php
/**
 * Template Name: Financing
 */
get_header();
$stores = bl_stores();
?>

<style>
/* ── Financing Page Styles ─────────────────────────────────────── */
.bl-financing .fin-inner{max-width:780px;margin:0 auto;padding:20px 0 60px;}
.bl-financing .fin-subtitle{color:#666;font-size:15px;margin:0 0 10px;}

/* Success */
.fin-success{background:#fff;border:1px solid #ddd;border-radius:8px;padding:50px 40px;text-align:center;margin-top:30px;}
.fin-success h2{color:#10b981;font-size:28px;margin:0 0 12px;}
.fin-success p{color:#666;font-size:16px;line-height:1.6;margin:0 0 24px;}
.fin-success a{display:inline-block;padding:12px 32px;background:linear-gradient(135deg,#001B5E 0%,#066b91 100%);color:#fff;text-decoration:none;border-radius:6px;font-weight:600;font-size:15px;}
.fin-success a:hover{filter:brightness(1.12);}

/* Error */
.fin-error{background:#fff5f5;border:1px solid #f5c6cb;border-radius:8px;padding:20px 24px;color:#721c24;margin-top:16px;display:none;}

/* Stepper */
.fin-stepper{display:flex;justify-content:center;gap:0;margin:30px 0 36px;position:relative;}
.fin-step-dot{display:flex;flex-direction:column;align-items:center;flex:1;position:relative;cursor:pointer;}
.fin-step-dot .dot{width:40px;height:40px;border-radius:50%;background:#f0f0f0;border:2px solid #ccc;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;color:#999;transition:all .3s;z-index:2;position:relative;}
.fin-step-dot.active .dot{background:#088fc2;border-color:#088fc2;color:#fff;}
.fin-step-dot.done .dot{background:#10b981;border-color:#10b981;color:#fff;}
.fin-step-dot .dot-label{font-size:12px;color:#888;margin-top:8px;text-align:center;transition:color .3s;}
.fin-step-dot.active .dot-label{color:#088fc2;font-weight:600;}
.fin-step-dot.done .dot-label{color:#10b981;}
.fin-step-dot:not(:last-child)::after{content:'';position:absolute;top:19px;left:calc(50% + 24px);width:calc(100% - 48px);height:2px;background:#ddd;z-index:1;}
.fin-step-dot.done:not(:last-child)::after{background:#10b981;}

/* Card */
.fin-card{background:#fff;border:1px solid #ddd;border-radius:8px;padding:36px 32px;margin-top:0;}
.fin-section{display:none;}
.fin-section.visible{display:block;animation:finFade .35s ease;}
@keyframes finFade{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}
.fin-section h2{font-size:22px;font-weight:700;color:#222;margin:0 0 6px;}
.fin-section .fin-sub{color:#888;font-size:14px;margin:0 0 28px;}

/* Fields */
.fin-field{margin-bottom:20px;}
.fin-field label{display:block;font-size:13px;font-weight:600;color:#555;margin-bottom:6px;}
.fin-field input,.fin-field select,.fin-field textarea{width:100%;padding:12px 14px;background:#fff;border:1px solid #ccc;border-radius:6px;color:#333;font-size:15px;font-family:inherit;transition:border-color .2s;box-sizing:border-box;}
.fin-field input:focus,.fin-field select:focus,.fin-field textarea:focus{outline:none;border-color:#088fc2;box-shadow:0 0 0 2px rgba(8,143,194,0.15);}
.fin-field select{appearance:auto;}
.fin-field textarea{resize:vertical;min-height:80px;}
.fin-row{display:flex;gap:16px;}
.fin-row .fin-field{flex:1;}

/* Radio group */
.fin-radios{display:flex;gap:12px;flex-wrap:wrap;}
.fin-radios label{display:flex;align-items:center;gap:8px;padding:10px 18px;background:#fff;border:1px solid #ccc;border-radius:6px;cursor:pointer;font-size:14px;font-weight:400;color:#555;transition:all .2s;}
.fin-radios label:hover{border-color:#999;}
.fin-radios input[type=radio]{accent-color:#088fc2;width:16px;height:16px;}
.fin-radios input[type=radio]:checked+span{color:#222;font-weight:600;}
.fin-radios label:has(input:checked){border-color:#088fc2;background:#eef9fd;}

/* Buttons */
.fin-btns{display:flex;justify-content:space-between;margin-top:32px;gap:12px;}
.fin-btn{padding:14px 36px;border:none;border-radius:6px;font-size:15px;font-weight:700;cursor:pointer;transition:all .2s;text-transform:uppercase;letter-spacing:.5px;}
.fin-btn-next{background:linear-gradient(135deg,#001B5E 0%,#066b91 100%);color:#fff;margin-left:auto;box-shadow:0 1px 3px rgba(0,0,0,0.15);}
.fin-btn-next:hover{filter:brightness(1.12);box-shadow:0 3px 8px rgba(0,0,0,0.25);}
.fin-btn-back{background:transparent;color:#888;border:1px solid #ccc;}
.fin-btn-back:hover{border-color:#999;color:#555;}
.fin-btn-submit{background:#10b981;color:#fff;margin-left:auto;font-size:16px;}
.fin-btn-submit:hover{background:#059669;}
.fin-btn-submit:disabled{opacity:.6;cursor:not-allowed;}

/* Trade-in conditional */
.fin-tradein-fields{display:none;margin-top:16px;padding-top:16px;border-top:1px solid #ddd;}
.fin-tradein-fields.show{display:block;animation:finFade .3s ease;}

/* Review */
.fin-review{margin-top:20px;}
.fin-review-section{margin-bottom:24px;}
.fin-review-section h3{font-size:14px;text-transform:uppercase;letter-spacing:1px;color:#088fc2;margin:0 0 10px;padding-bottom:8px;border-bottom:1px solid #ddd;}
.fin-review-row{display:flex;justify-content:space-between;padding:6px 0;font-size:14px;}
.fin-review-row .rlabel{color:#888;}
.fin-review-row .rvalue{color:#333;font-weight:500;text-align:right;max-width:55%;}
.fin-review-row .rvalue.empty{color:#bbb;font-style:italic;}

.fin-disclaimer{font-size:12px;color:#888;line-height:1.6;margin-top:24px;padding:16px;background:#f9f9f9;border-radius:6px;border:1px solid #ddd;}

/* Responsive */
@media(max-width:600px){
  .fin-card{padding:24px 18px;}
  .fin-row{flex-direction:column;gap:0;}
  .fin-step-dot .dot-label{font-size:10px;}
  .fin-step-dot .dot{width:34px;height:34px;font-size:13px;}
  .fin-btns{flex-direction:column;}
  .fin-btn{width:100%;text-align:center;}
  .fin-btn-next,.fin-btn-submit{margin-left:0;}
}
</style>

<main id="content" class="bl-page bl-financing">
  <section class="bl-hero small">
    <div class="inner">
      <h1>Financing Application</h1>
      <p class="lede">Flexible financing for new and pre-owned powersports and marine vehicles. Complete the form below &mdash; all fields are optional, fill in what you can.</p>
    </div>
  </section>

  <div class="inner">
    <div class="fin-inner">

      <div id="finSuccess" class="fin-success" style="display:none;">
        <h2>Thank You!</h2>
        <p>Your financing application has been submitted successfully.<br>Our team will review your information and get back to you shortly.</p>
        <a href="<?php echo esc_url(home_url('/inventory/')); ?>">Browse Inventory</a>
      </div>

      <div id="finError" class="fin-error">Something went wrong &mdash; please try again or call your nearest location.</div>

      <div id="finWizard">
        <!-- Stepper -->
        <div class="fin-stepper">
          <div class="fin-step-dot active" data-step="1">
            <div class="dot">1</div>
            <div class="dot-label">Vehicle</div>
          </div>
          <div class="fin-step-dot" data-step="2">
            <div class="dot">2</div>
            <div class="dot-label">Personal</div>
          </div>
          <div class="fin-step-dot" data-step="3">
            <div class="dot">3</div>
            <div class="dot-label">Employment</div>
          </div>
          <div class="fin-step-dot" data-step="4">
            <div class="dot">4</div>
            <div class="dot-label">Review</div>
          </div>
        </div>

        <div class="fin-card">
          <form id="finForm">
            <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true" />

            <!-- ─── Step 1: Vehicle Interest ─── -->
            <div class="fin-section visible" data-step="1">
              <h2>Vehicle Interest</h2>
              <p class="fin-sub">Tell us what you're looking for.</p>

              <div class="fin-field">
                <label for="fin_store">Preferred Location</label>
                <select id="fin_store" name="store">
                  <option value="">— Any location —</option>
                  <?php foreach ($stores as $s): ?>
                    <option value="<?php echo esc_attr($s['slug']); ?>"><?php echo esc_html($s['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="fin-field">
                <label for="vehicle_interest">Vehicle of Interest</label>
                <input type="text" id="vehicle_interest" name="vehicle_interest" placeholder="e.g. 2025 Kawasaki Mule PRO-FXT">
              </div>

              <div class="fin-field">
                <label>Condition Preference</label>
                <div class="fin-radios">
                  <label><input type="radio" name="vehicle_condition" value="New"><span>New</span></label>
                  <label><input type="radio" name="vehicle_condition" value="Pre-Owned"><span>Pre-Owned</span></label>
                  <label><input type="radio" name="vehicle_condition" value="Either"><span>Either</span></label>
                </div>
              </div>

              <div class="fin-field">
                <label for="payment_range">Preferred Monthly Payment</label>
                <select id="payment_range" name="payment_range">
                  <option value="">Select a range...</option>
                  <option value="Under $200">Under $200 / month</option>
                  <option value="$200 - $400">$200 - $400 / month</option>
                  <option value="$400 - $600">$400 - $600 / month</option>
                  <option value="$600+">$600+ / month</option>
                  <option value="Not Sure">Not Sure</option>
                </select>
              </div>

              <div class="fin-btns">
                <button type="button" class="fin-btn fin-btn-next" onclick="finGoStep(2)">Next &rarr;</button>
              </div>
            </div>

            <!-- ─── Step 2: Personal Information ─── -->
            <div class="fin-section" data-step="2">
              <h2>Personal Information</h2>
              <p class="fin-sub">Tell us about yourself.</p>

              <div class="fin-row">
                <div class="fin-field">
                  <label for="first_name">First Name</label>
                  <input type="text" id="first_name" name="first_name" placeholder="First name">
                </div>
                <div class="fin-field">
                  <label for="last_name">Last Name</label>
                  <input type="text" id="last_name" name="last_name" placeholder="Last name">
                </div>
              </div>

              <div class="fin-row">
                <div class="fin-field">
                  <label for="dob">Date of Birth</label>
                  <input type="date" id="dob" name="dob">
                </div>
                <div class="fin-field">
                  <label for="phone">Phone</label>
                  <input type="tel" id="phone" name="phone" placeholder="204-555-1234">
                </div>
              </div>

              <div class="fin-field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com">
              </div>

              <div class="fin-field">
                <label for="address">Street Address</label>
                <input type="text" id="address" name="address" placeholder="123 Main St">
              </div>

              <div class="fin-row">
                <div class="fin-field">
                  <label for="city">City</label>
                  <input type="text" id="city" name="city" placeholder="City">
                </div>
                <div class="fin-field">
                  <label for="province">Province</label>
                  <select id="province" name="province">
                    <option value="">Select...</option>
                    <option value="AB">Alberta</option>
                    <option value="BC">British Columbia</option>
                    <option value="MB" selected>Manitoba</option>
                    <option value="NB">New Brunswick</option>
                    <option value="NL">Newfoundland and Labrador</option>
                    <option value="NS">Nova Scotia</option>
                    <option value="NT">Northwest Territories</option>
                    <option value="NU">Nunavut</option>
                    <option value="ON">Ontario</option>
                    <option value="PE">Prince Edward Island</option>
                    <option value="QC">Quebec</option>
                    <option value="SK">Saskatchewan</option>
                    <option value="YT">Yukon</option>
                  </select>
                </div>
                <div class="fin-field">
                  <label for="postal_code">Postal Code</label>
                  <input type="text" id="postal_code" name="postal_code" placeholder="R1N 0A1">
                </div>
              </div>

              <div class="fin-row">
                <div class="fin-field">
                  <label for="time_at_address">Time at Address</label>
                  <select id="time_at_address" name="time_at_address">
                    <option value="">Select...</option>
                    <option value="Less than 1 year">Less than 1 year</option>
                    <option value="1 - 3 years">1 - 3 years</option>
                    <option value="3 - 5 years">3 - 5 years</option>
                    <option value="5+ years">5+ years</option>
                  </select>
                </div>
                <div class="fin-field">
                  <label for="housing_status">Housing Status</label>
                  <select id="housing_status" name="housing_status">
                    <option value="">Select...</option>
                    <option value="Own">Own</option>
                    <option value="Rent">Rent</option>
                    <option value="Live with Family">Live with Family</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>

              <div class="fin-btns">
                <button type="button" class="fin-btn fin-btn-back" onclick="finGoStep(1)">&larr; Back</button>
                <button type="button" class="fin-btn fin-btn-next" onclick="finGoStep(3)">Next &rarr;</button>
              </div>
            </div>

            <!-- ─── Step 3: Employment & Income ─── -->
            <div class="fin-section" data-step="3">
              <h2>Employment &amp; Income</h2>
              <p class="fin-sub">Help us understand your financial situation.</p>

              <div class="fin-row">
                <div class="fin-field">
                  <label for="employment_status">Employment Status</label>
                  <select id="employment_status" name="employment_status">
                    <option value="">Select...</option>
                    <option value="Full-Time">Full-Time</option>
                    <option value="Part-Time">Part-Time</option>
                    <option value="Self-Employed">Self-Employed</option>
                    <option value="Retired">Retired</option>
                    <option value="Student">Student</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
                <div class="fin-field">
                  <label for="employer">Employer Name</label>
                  <input type="text" id="employer" name="employer" placeholder="Company name">
                </div>
              </div>

              <div class="fin-row">
                <div class="fin-field">
                  <label for="job_title">Job Title</label>
                  <input type="text" id="job_title" name="job_title" placeholder="Your role">
                </div>
                <div class="fin-field">
                  <label for="time_at_job">Time at Current Job</label>
                  <select id="time_at_job" name="time_at_job">
                    <option value="">Select...</option>
                    <option value="Less than 1 year">Less than 1 year</option>
                    <option value="1 - 3 years">1 - 3 years</option>
                    <option value="3 - 5 years">3 - 5 years</option>
                    <option value="5+ years">5+ years</option>
                  </select>
                </div>
              </div>

              <div class="fin-row">
                <div class="fin-field">
                  <label for="monthly_income">Monthly Gross Income</label>
                  <input type="text" id="monthly_income" name="monthly_income" placeholder="$ amount">
                </div>
                <div class="fin-field">
                  <label for="other_income">Other Income Sources</label>
                  <input type="text" id="other_income" name="other_income" placeholder="Optional">
                </div>
              </div>

              <div class="fin-btns">
                <button type="button" class="fin-btn fin-btn-back" onclick="finGoStep(2)">&larr; Back</button>
                <button type="button" class="fin-btn fin-btn-next" onclick="finGoStep(4)">Next &rarr;</button>
              </div>
            </div>

            <!-- ─── Step 4: Trade-In & Review ─── -->
            <div class="fin-section" data-step="4">
              <h2>Trade-In &amp; Review</h2>
              <p class="fin-sub">Almost done! Add trade-in info and review your application.</p>

              <div class="fin-field">
                <label>Do you have a trade-in?</label>
                <div class="fin-radios">
                  <label><input type="radio" name="has_tradein" value="Yes" onchange="finToggleTradein()"><span>Yes</span></label>
                  <label><input type="radio" name="has_tradein" value="No" checked onchange="finToggleTradein()"><span>No</span></label>
                </div>
              </div>

              <div class="fin-tradein-fields" id="finTradeinFields">
                <div class="fin-field">
                  <label for="tradein_vehicle">Trade-In Vehicle</label>
                  <input type="text" id="tradein_vehicle" name="tradein_vehicle" placeholder="e.g. 2018 Honda CRF250L">
                </div>
                <div class="fin-row">
                  <div class="fin-field">
                    <label for="tradein_mileage">Mileage / Hours</label>
                    <input type="text" id="tradein_mileage" name="tradein_mileage" placeholder="e.g. 12,000 km">
                  </div>
                  <div class="fin-field">
                    <label for="tradein_condition">Condition</label>
                    <select id="tradein_condition" name="tradein_condition">
                      <option value="">Select...</option>
                      <option value="Excellent">Excellent</option>
                      <option value="Good">Good</option>
                      <option value="Fair">Fair</option>
                      <option value="Poor">Poor</option>
                    </select>
                  </div>
                </div>
                <div class="fin-field">
                  <label for="tradein_owing">Amount Still Owing</label>
                  <input type="text" id="tradein_owing" name="tradein_owing" placeholder="$ amount (if any)">
                </div>
              </div>

              <div class="fin-field">
                <label for="comments">Additional Comments</label>
                <textarea id="comments" name="comments" placeholder="Anything else you'd like us to know?"></textarea>
              </div>

              <!-- Review Summary built via JS DOM methods -->
              <div class="fin-review" id="finReviewSummary"></div>

              <div class="fin-disclaimer">
                By submitting this application, you authorize Borderland Powersports to review the information provided for the purpose of evaluating financing options. This does not guarantee approval. A member of our team will contact you to discuss your options.
              </div>

              <div class="fin-btns">
                <button type="button" class="fin-btn fin-btn-back" onclick="finGoStep(3)">&larr; Back</button>
                <button type="button" id="finSubmitBtn" class="fin-btn fin-btn-submit" onclick="finSubmit()">Submit Application &rarr;</button>
              </div>
            </div>

          </form>
        </div><!-- .fin-card -->
      </div><!-- #finWizard -->

    </div><!-- .fin-inner -->
  </div><!-- .inner -->
</main>

<script>
(function(){
  var current = 1;
  var dots = document.querySelectorAll('.fin-step-dot');
  var sections = document.querySelectorAll('.fin-section');

  window.finGoStep = function(n) {
    if (n < 1 || n > 4) return;
    sections.forEach(function(s){ s.classList.remove('visible'); });
    var target = document.querySelector('.fin-section[data-step="'+n+'"]');
    if (target) target.classList.add('visible');
    dots.forEach(function(d){
      var ds = parseInt(d.getAttribute('data-step'));
      d.classList.remove('active','done');
      if (ds < n) d.classList.add('done');
      if (ds === n) d.classList.add('active');
    });
    current = n;
    if (n === 4) finBuildReview();
    var wrap = document.querySelector('.fin-stepper');
    if (wrap) wrap.scrollIntoView({behavior:'smooth',block:'start'});
  };

  dots.forEach(function(d){
    d.addEventListener('click', function(){
      var step = parseInt(d.getAttribute('data-step'));
      if (step <= current || d.classList.contains('done')) finGoStep(step);
    });
  });

  window.finToggleTradein = function(){
    var checked = document.querySelector('input[name="has_tradein"]:checked');
    document.getElementById('finTradeinFields').classList.toggle('show', !!(checked && checked.value === 'Yes'));
  };

  // Build review using DOM methods (no innerHTML with user content)
  function makeReviewRow(label, value) {
    var row = document.createElement('div');
    row.className = 'fin-review-row';
    var lSpan = document.createElement('span');
    lSpan.className = 'rlabel';
    lSpan.textContent = label;
    var vSpan = document.createElement('span');
    if (value) {
      vSpan.className = 'rvalue';
      vSpan.textContent = value;
    } else {
      vSpan.className = 'rvalue empty';
      vSpan.textContent = 'Not provided';
    }
    row.appendChild(lSpan);
    row.appendChild(vSpan);
    return row;
  }

  function makeReviewSection(title, rows) {
    var sec = document.createElement('div');
    sec.className = 'fin-review-section';
    var h3 = document.createElement('h3');
    h3.textContent = title;
    sec.appendChild(h3);
    rows.forEach(function(r){ sec.appendChild(r); });
    return sec;
  }

  function fv(id) {
    var el = document.getElementById(id);
    return el ? el.value.trim() : '';
  }
  function rv(label, id) { return makeReviewRow(label, fv(id)); }
  function radioVal(name) {
    var c = document.querySelector('input[name="'+name+'"]:checked');
    return c ? c.value : '';
  }
  function rvRadio(label, name) { return makeReviewRow(label, radioVal(name)); }

  function finBuildReview(){
    var wrap = document.getElementById('finReviewSummary');
    while (wrap.firstChild) wrap.removeChild(wrap.firstChild);

    var storeSel = document.getElementById('fin_store');
    var storeName = (storeSel && storeSel.value) ? storeSel.options[storeSel.selectedIndex].text : '';

    wrap.appendChild(makeReviewSection('Vehicle Interest', [
      makeReviewRow('Location', storeName),
      rv('Vehicle', 'vehicle_interest'),
      rvRadio('Condition', 'vehicle_condition'),
      rv('Monthly Payment', 'payment_range')
    ]));

    wrap.appendChild(makeReviewSection('Personal Information', [
      rv('First Name', 'first_name'),
      rv('Last Name', 'last_name'),
      rv('Date of Birth', 'dob'),
      rv('Phone', 'phone'),
      rv('Email', 'email'),
      rv('Address', 'address'),
      rv('City', 'city'),
      rv('Province', 'province'),
      rv('Postal Code', 'postal_code'),
      rv('Time at Address', 'time_at_address'),
      rv('Housing', 'housing_status')
    ]));

    wrap.appendChild(makeReviewSection('Employment & Income', [
      rv('Status', 'employment_status'),
      rv('Employer', 'employer'),
      rv('Job Title', 'job_title'),
      rv('Time at Job', 'time_at_job'),
      rv('Monthly Income', 'monthly_income'),
      rv('Other Income', 'other_income')
    ]));

    var tradeinRows = [rvRadio('Has Trade-In', 'has_tradein')];
    if (radioVal('has_tradein') === 'Yes') {
      tradeinRows.push(rv('Vehicle', 'tradein_vehicle'));
      tradeinRows.push(rv('Mileage/Hours', 'tradein_mileage'));
      tradeinRows.push(rv('Condition', 'tradein_condition'));
      tradeinRows.push(rv('Amount Owing', 'tradein_owing'));
    }
    tradeinRows.push(rv('Comments', 'comments'));
    wrap.appendChild(makeReviewSection('Trade-In', tradeinRows));
  }

  window.finSubmit = function(){
    var btn = document.getElementById('finSubmitBtn');
    var errBox = document.getElementById('finError');
    btn.disabled = true;
    btn.textContent = 'Submitting\u2026';
    errBox.style.display = 'none';

    var form = document.getElementById('finForm');
    var fd = new FormData(form);

    var first = (fd.get('first_name') || '').trim();
    var last  = (fd.get('last_name')  || '').trim();
    fd.set('name', (first + ' ' + last).trim() || 'Not provided');
    fd.set('interest', fd.get('vehicle_interest') || '');

    // Bundle extra financing fields into message for CRM notes
    var lines = [];
    function addLine(label, key){ var v=(fd.get(key)||'').trim(); if(v) lines.push(label+': '+v); }
    addLine('Vehicle Condition', 'vehicle_condition');
    addLine('Monthly Payment Range', 'payment_range');
    addLine('Date of Birth', 'dob');
    addLine('Address', 'address');
    addLine('City', 'city');
    addLine('Province', 'province');
    addLine('Postal Code', 'postal_code');
    addLine('Time at Address', 'time_at_address');
    addLine('Housing Status', 'housing_status');
    addLine('Employment Status', 'employment_status');
    addLine('Employer', 'employer');
    addLine('Job Title', 'job_title');
    addLine('Time at Job', 'time_at_job');
    addLine('Monthly Gross Income', 'monthly_income');
    addLine('Other Income', 'other_income');
    addLine('Has Trade-In', 'has_tradein');
    if ((fd.get('has_tradein')||'') === 'Yes') {
      addLine('Trade-In Vehicle', 'tradein_vehicle');
      addLine('Trade-In Mileage/Hours', 'tradein_mileage');
      addLine('Trade-In Condition', 'tradein_condition');
      addLine('Trade-In Amount Owing', 'tradein_owing');
    }
    var userComments = (fd.get('comments') || '').trim();
    if (userComments) lines.push('Comments: ' + userComments);
    fd.set('message', lines.join('\n'));

    fd.set('action', 'bl_submit_lead');
    fd.set('nonce', (typeof BL !== 'undefined' ? BL.nonce : ''));
    fd.set('context', 'financing');
    fd.set('source_url', window.location.href);
    fd.set('consent', '1');

    fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (data && data.success) {
        document.getElementById('finWizard').style.display = 'none';
        document.getElementById('finSuccess').style.display = '';
        window.scrollTo({top:0,behavior:'smooth'});
      } else {
        errBox.style.display = '';
        btn.disabled = false;
        btn.textContent = 'Submit Application \u2192';
      }
    })
    .catch(function(){
      errBox.style.display = '';
      btn.disabled = false;
      btn.textContent = 'Submit Application \u2192';
    });
  };
})();
</script>

<?php get_footer();
