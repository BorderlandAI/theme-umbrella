/**
 * Inventory page: auto-submit filter form on select change.
 * Stock-number search is client-side against the currently rendered page
 * (which contains up to 48 cards — use pagination for the rest).
 */
(function() {
  var form = document.getElementById('invFilters');
  if (!form) return;

  var selects = form.querySelectorAll('select[name]');
  selects.forEach(function(sel) {
    sel.addEventListener('change', function() { form.submit(); });
  });

  var search = form.querySelector('#invSearch');
  var grid   = document.getElementById('invGrid');
  var noRes  = document.getElementById('invNoResults');
  if (search && grid) {
    var cards = Array.prototype.slice.call(grid.querySelectorAll('.inv-card'));
    search.addEventListener('input', function() {
      var q = search.value.toLowerCase().trim();
      var visible = 0;
      cards.forEach(function(c) {
        var n = (c.getAttribute('data-name') || '').toLowerCase();
        var s = (c.getAttribute('data-stock') || '').toLowerCase();
        var match = !q || n.indexOf(q) !== -1 || s.indexOf(q) !== -1;
        c.style.display = match ? '' : 'none';
        if (match) visible++;
      });
      if (noRes) noRes.style.display = visible === 0 ? '' : 'none';
    });
  }
})();
