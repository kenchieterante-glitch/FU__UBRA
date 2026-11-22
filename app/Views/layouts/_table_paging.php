<style>
  .tp-footer{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.6rem;padding:.8rem 1rem;margin-top:2px;border-top:1px solid rgba(0,0,0,.1);font-size:.8rem;color:var(--text)}
  .tp-pages{display:flex;align-items:center;gap:.3rem}
  .tp-btn{min-width:30px;height:30px;padding:0 .5rem;border-radius:6px;border:1.5px solid rgba(0,0,0,.15);background:#fff;color:#222;font-size:.78rem;cursor:pointer}
  .tp-btn:hover:not(:disabled){border-color:#800000;color:#800000}
  .tp-btn.active{background:#1f2937;border-color:#1f2937;color:#fff}
  .tp-btn:disabled{opacity:.4;cursor:not-allowed}
  .tp-gap{color:var(--text);padding:0 .2rem}
  tr.tp-hide{display:none !important}
  .tp-footer.tp-off{display:none !important}
  [data-theme="dark"] .tp-footer{color:#aaa;border-top-color:rgba(255,255,255,.12)}
  [data-theme="dark"] .tp-btn{background:#2a2a2a;color:#eee;border-color:rgba(255,255,255,.18)}
</style>
<script>
// Paging for every table, same look as the Reports page: 10 rows per page, "Showing x to y of z entries", page buttons.
// Pages work on top of each page's own filters (rows a filter hides never count) and of the select-all boxes.
(function () {
  var PAGE_SIZE = 10;
  var SKIP_IDS = { activityTable: 1, notifTable: 1 };   // these already page themselves

  function eligible(table) {
    if (table.hasAttribute('data-no-page') || table.closest('.cal-wrapper') || SKIP_IDS[table.id] || table.closest('.modal-overlay, .modal-box')) return false;
    if (table.parentElement && table.parentElement.closest('table')) return false;   // nested table
    return true;
  }

  // Group rows: a row that spans the whole table (a detail/expand row) stays with the row above it.
  function groups(table) {
    var out = [];
    Array.prototype.forEach.call(table.tBodies, function (tb) {
      Array.prototype.forEach.call(tb.rows, function (tr) {
        var first = tr.cells[0];
        if (!first) return;
        if (tr.querySelector('.empty-row')) return;                     // "nothing here" placeholder
        var spans = first.colSpan > 1 && tr.cells.length === 1;
        if (spans && out.length) { out[out.length - 1].push(tr); return; }
        if (spans) return;
        out.push([tr]);
      });
    });
    return out;
  }

  function shownBy(tr) {   // not hidden by the page's own filter
    return tr.style.display !== 'none' && !tr.classList.contains('filtered-out');
  }

  function Pager(table) {
    this.table = table;
    this.page = 1;
    this.footer = document.createElement('div');
    this.footer.className = 'tp-footer';
    this.footer.style.display = 'none';
    var anchor = table.closest('.table-wrap, .table-scroll') || table;
    anchor.parentNode.insertBefore(this.footer, anchor.nextSibling);
  }

  Pager.prototype.render = function () {
    var self = this, gs = groups(this.table);
    var rows = gs.filter(function (g) { return shownBy(g[0]); });
    var total = rows.length;
    var pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
    if (this.page > pages) this.page = pages;

    // clear then hide everything outside the current page
    gs.forEach(function (g) { g.forEach(function (tr) { tr.classList.remove('tp-hide'); }); });
    rows.forEach(function (g, i) {
      if (Math.floor(i / PAGE_SIZE) + 1 !== self.page) g.forEach(function (tr) { tr.classList.add('tp-hide'); });
    });

    if (total <= PAGE_SIZE) { this.footer.style.display = 'none'; return; }
    var start = (this.page - 1) * PAGE_SIZE + 1, end = Math.min(this.page * PAGE_SIZE, total);
    this.footer.style.display = 'flex';
    this.footer.innerHTML = '';
    var label = document.createElement('div');
    label.textContent = 'Showing ' + start + ' to ' + end + ' of ' + total + ' entries';
    var wrap = document.createElement('div');
    wrap.className = 'tp-pages';
    function add(text, page, opts) {
      opts = opts || {};
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'tp-btn' + (opts.active ? ' active' : ''); b.textContent = text; b.disabled = !!opts.disabled;
      b.onclick = function () { self.page = page; self.render(); };
      wrap.appendChild(b);
    }
    function gap() { var s = document.createElement('span'); s.className = 'tp-gap'; s.textContent = '…'; wrap.appendChild(s); }
    add('«', Math.max(1, this.page - 1), { disabled: this.page === 1 });
    var from = Math.max(1, this.page - 2), to = Math.min(pages, from + 4);
    from = Math.max(1, to - 4);
    if (from > 1) { add('1', 1); if (from > 2) gap(); }
    for (var p = from; p <= to; p++) add(String(p), p, { active: p === this.page });
    if (to < pages) { if (to < pages - 1) gap(); add(String(pages), pages); }
    add('»', Math.min(pages, this.page + 1), { disabled: this.page === pages });
    this.footer.appendChild(label);
    this.footer.appendChild(wrap);
  };

  var pagers = new WeakMap(), observer = null, timer = null;

  function run() {
    if (observer) observer.disconnect();
    document.querySelectorAll('table').forEach(function (table) {
      if (!eligible(table)) return;
      var p = pagers.get(table);
      if (!p) { p = new Pager(table); pagers.set(table, p); }
      p.render();
    });
    watch();
  }
  function watch() {
    if (!observer) observer = new MutationObserver(function (muts) {
      // ignore our own footer and the select-box wrappers; react to rows being added/removed or filtered
      var relevant = muts.some(function (m) {
        var t = m.target;
        if (t.classList && (t.classList.contains('tp-footer') || t.classList.contains('tp-pages'))) return false;
        if (m.type === 'attributes') return t.tagName === 'TR' && m.attributeName === 'style';
        var newTable = Array.prototype.some.call(m.addedNodes || [], function (n) { return n.nodeType === 1 && (n.tagName === 'TABLE' || (n.querySelector && n.querySelector('table'))); });
        return newTable || !!(t.closest && t.closest('table'));
      });
      if (relevant) { clearTimeout(timer); timer = setTimeout(run, 80); }
    });
    observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['style'] });
  }
  run();
})();
</script>
