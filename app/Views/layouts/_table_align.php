<script>
// Aligns every table's column headings with their values: text columns left, button/icon-only columns centered.
(function () {
  function isActionCell(c) {
    if (!c) return false;
    var text = (c.innerText || '').replace(/\s+/g, ' ').trim();
    var hasControl = c.querySelector('button, form, .btn, .rec-view-btn, .action-cell, input[type=checkbox]:not(.bulk-check), a.icon-btn, a[class*="btn"]');
    return !!hasControl && text.length <= 14;
  }
  function align(table) {
    var ths = table.querySelectorAll('thead th');
    if (!ths.length || table.closest('.modal-overlay, .modal-box')) return;
    var rows = Array.prototype.filter.call(table.querySelectorAll('tbody > tr'), function (r) { return r.cells.length >= ths.length && !r.querySelector('.empty-row'); });
    if (!rows.length) return;
    var sample = rows.slice(0, 8);
    Array.prototype.forEach.call(ths, function (th, i) {
      var center = sample.every(function (r) { return isActionCell(r.cells[i]); });
      th.classList.toggle('col-center', center);
      rows.forEach(function (r) { if (r.cells[i]) r.cells[i].classList.toggle('col-center', center); });
    });
  }
  var timer = null;
  function all() { document.querySelectorAll('table').forEach(align); }
  all();
  new MutationObserver(function (muts) {
    var rel = muts.some(function (m) { return m.addedNodes.length && m.target.closest && (m.target.closest('table') || Array.prototype.some.call(m.addedNodes, function (n) { return n.nodeType === 1 && (n.tagName === 'TABLE' || (n.querySelector && n.querySelector('table'))); })); });
    if (rel) { clearTimeout(timer); timer = setTimeout(all, 200); }
  }).observe(document.body, { childList: true, subtree: true });
})();
</script>
