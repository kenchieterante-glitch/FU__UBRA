<script>
// Select-many for every table, working like Aircon Care: a select-all box in the header, a tick box on each row,
// and an action bar right above the table ("N selected" + Archive / Delete / Export).
// "Select all" covers every row that matches the current filters, across all pages.
(function () {
  function rowItem(tr, table) {
    if (tr.dataset.bulkKey) return { key: tr.dataset.bulkKey };
    var f = table.dataset.bulkEndpoint ? null : tr.querySelector('form[action*="/delete/"], form[action*="/archive/"], form[action*="deleteUser/"]');
    if (f) {
      var url = f.getAttribute('action');
      var text = (f.getAttribute('onsubmit') || '') + ' ' + url;
      return { url: url, verb: /archive/i.test(text) ? 'Archive' : 'Delete' };
    }
    // No archive/delete behind this row: it can still be selected and exported.
    if (tr.querySelector('.empty-row') || tr.cells.length < 2 || tr.cells[0].tagName !== 'TD') return null;
    return { exportOnly: true };
  }

  // Checkbox look: same size everywhere, theme colour.
  var BOX = 'width:18px;height:18px;margin:0;flex:0 0 auto;cursor:pointer;accent-color:#800000;';
  // Put the checkbox on the same line as the cell's content (left of it, vertically centred) without adding a column.
  function placeBox(cell, box) {
    var wrap = document.createElement('div');
    wrap.className = 'bulk-cell';
    wrap.style.cssText = 'display:flex;align-items:center;gap:10px;';
    var body = document.createElement('div');
    body.style.cssText = 'min-width:0;';
    while (cell.firstChild) body.appendChild(cell.firstChild);
    wrap.appendChild(box);
    wrap.appendChild(body);
    cell.appendChild(wrap);
  }

  var bars = new WeakMap();   // table -> its action bar
  var active = null;          // table that currently has a selection

  function checks(table) { return Array.prototype.slice.call(table.querySelectorAll('.bulk-check')); }
  // A row counts as "shown" unless a filter hid it. (The Reports table also hides rows just to paginate; those still count.)
  function matches(table, tr) {
    if (tr.classList.contains('filtered-out')) return false;
    return table.id === 'activityTable' || tr.style.display !== 'none';
  }
  function selected(table) { return checks(table).filter(function (c) { return c.checked; }); }

  function barFor(table) {
    var bar = bars.get(table);
    if (bar) return bar;
    bar = document.createElement('div');
    bar.className = 'bulk-bar';
    bar.style.cssText = 'display:none;align-items:center;gap:10px;flex-wrap:wrap;margin:0 0 10px;padding:8px 12px;border-radius:10px;background:rgba(128,0,0,.07);border:1px solid rgba(128,0,0,.2);font-size:14px;';
    var anchor = table.closest('.table-wrap, .table-scroll') || table;
    anchor.parentNode.insertBefore(bar, anchor);
    bars.set(table, bar);
    return bar;
  }

  function btn(label, icon, danger, onclick) {
    var b = document.createElement('button');
    b.type = 'button';
    b.innerHTML = '<i class="bi ' + icon + '"></i> ' + label;
    b.style.cssText = 'border:0;border-radius:8px;padding:6px 12px;font-weight:600;cursor:pointer;color:#fff;background:' + (danger ? '#c62828' : '#800000') + ';';
    b.onclick = onclick;
    return b;
  }

  function refresh(table) {
    var bar = barFor(table), sel = selected(table);
    if (!sel.length) { bar.style.display = 'none'; bar.innerHTML = ''; if (active === table) active = null; return; }
    active = table;
    var all = checks(table).filter(function (c) { return matches(table, c.closest('tr')); });
    bar.innerHTML = '';
    var count = document.createElement('strong');
    count.textContent = sel.length + ' selected';
    bar.appendChild(count);

    if (sel.length < all.length) {
      var more = document.createElement('a');
      more.href = '#'; more.textContent = 'Select all ' + all.length;
      more.style.cssText = 'color:#800000;font-weight:600;';
      more.onclick = function (e) { e.preventDefault(); setAll(table, true); };
      bar.appendChild(more);
    }

    var keyed = sel.filter(function (c) { return c._item.key; });
    var urled = sel.filter(function (c) { return c._item.url; });
    if (table.dataset.bulkEndpoint && keyed.length) {
      (table.dataset.bulkActions || 'archive').split(',').forEach(function (k) {
        var L = { archive: ['Archive selected', 'bi-archive-fill', false], restore: ['Restore selected', 'bi-arrow-counterclockwise', false], dispose: ['Dispose selected', 'bi-trash3', true], 'delete': ['Delete permanently', 'bi-trash3-fill', true] }[k];
        if (L) bar.appendChild(btn(L[0], L[1], L[2], function () { run(table, k, keyed, sel.length); }));
      });
    } else if (urled.length) {
      var verb = urled[0]._item.verb.toLowerCase();
      bar.appendChild(btn((verb === 'archive' ? 'Archive' : 'Delete') + ' selected', verb === 'archive' ? 'bi-archive-fill' : 'bi-trash3-fill', verb !== 'archive', function () { run(table, verb, urled, sel.length); }));
    }
    bar.appendChild(btn('Print selected', 'bi-printer', false, function () { printSelected(table, sel); }));
    var clear = document.createElement('a');
    clear.href = '#'; clear.textContent = 'Clear'; clear.style.cssText = 'margin-left:auto;color:#555;';
    clear.onclick = function (e) { e.preventDefault(); setAll(table, false); };
    bar.appendChild(clear);
    bar.style.display = 'flex';
    var h = table.querySelector('.bulk-all');
    if (h) h.checked = all.length > 0 && all.every(function (c) { return c.checked; });
  }

  function setAll(table, on) {
    checks(table).forEach(function (c) { c.checked = on && matches(table, c.closest('tr')); });
    refresh(table);
  }

  // Print only the ticked rows (headers + the selected rows, without the tick boxes or action buttons).
  function printSelected(table, sel) {
    var heads = Array.prototype.slice.call(table.querySelectorAll('thead th')).map(function (th) { return th.innerText.trim(); });
    var skip = [];   // columns that only hold buttons (View / Action) are left out
    heads.forEach(function (h, i) { if (/^(actions?|view|edit|delete|archive|details?)$/i.test(h) || h === '') skip.push(i); });
    function cells(tr, tag) {
      return '<tr>' + Array.prototype.slice.call(tr.cells).map(function (td, i) {
        return skip.indexOf(i) >= 0 ? '' : '<' + tag + '>' + esc(td.innerText.replace(/\s+/g, ' ').trim()) + '</' + tag + '>';
      }).join('') + '</tr>';
    }
    var title = (document.querySelector('h1') || {}).innerText || document.title;
    var head = '<tr>' + heads.map(function (h, i) { return skip.indexOf(i) >= 0 ? '' : '<th>' + esc(h) + '</th>'; }).join('') + '</tr>';
    var body = sel.map(function (c) { return cells(c.closest('tr'), 'td'); }).join('');
    var w = window.open('', '_blank');
    if (!w) { uiToast('Allow pop-ups to print the selected rows.', true); return; }
    w.document.write('<!doctype html><html><head><meta charset="utf-8"><title>' + esc(title) + '</title><style>' +
      'body{font-family:Arial,Helvetica,sans-serif;color:#222;margin:24px}h1{font-size:18px;margin:0 0 4px;color:#800000}p{margin:0 0 14px;color:#666;font-size:12px}' +
      'table{border-collapse:collapse;width:100%}th,td{border:1px solid #bbb;padding:6px 8px;font-size:12px;text-align:left;vertical-align:top}th{background:#800000;color:#fff}' +
      '</style></head><body><h1>' + esc(title) + '</h1><p>' + sel.length + ' selected row' + (sel.length === 1 ? '' : 's') + ' — printed ' + new Date().toLocaleString() + '</p>' +
      '<table><thead>' + head + '</thead><tbody>' + body + '</tbody></table></body></html>');
    w.document.close();
    w.focus();
    setTimeout(function () { w.print(); }, 250);
  }

  function run(table, kind, items, totalSelected) {
    var word = kind === 'archive' ? 'archive' : (kind === 'restore' ? 'restore' : (kind === 'dispose' ? 'dispose of (and log as disposed)' : (table.dataset.bulkEndpoint ? 'permanently delete' : 'delete')));
    var msg = 'Are you sure you want to ' + word + ' ' + items.length + ' item' + (items.length === 1 ? '' : 's') + '?';
    if (items.length < totalSelected) msg += '\n\n' + (totalSelected - items.length) + ' of your ' + totalSelected + ' selected rows cannot be ' + (kind === 'archive' ? 'archived' : (kind === 'restore' ? 'restored' : 'deleted')) + ' here and will be left as they are.';
    if (!confirm(msg)) return;
    var p;
    if (table.dataset.bulkEndpoint) {
      var body = new URLSearchParams();
      body.append('action', kind);
      items.forEach(function (c) { body.append('keys[]', c._item.key); });
      p = fetch(table.dataset.bulkEndpoint, { method: 'POST', headers: csrfHeaders({ 'X-Requested-With': 'XMLHttpRequest' }), body: body })
        .then(function (r) { return r.json(); })
        .then(function (j) { return j.message || 'Done.'; });
    } else {
      p = items.reduce(function (chain, c) {
        return chain.then(function (n) {
          return fetch(c._item.url, { method: 'POST', headers: csrfHeaders({ 'X-Requested-With': 'XMLHttpRequest' }) })
            .then(function (r) { return n + (r.ok ? 1 : 0); });
        });
      }, Promise.resolve(0)).then(function (n) { return n + ' of ' + items.length + ' item(s) done.'; });
    }
    p.then(function (m) { try { sessionStorage.setItem('bulkMsg', m); } catch (e) {} location.reload(); })
     .catch(function () { uiToast('Something went wrong. Please try again.', true); });
  }

  function enhance(table) {
    if (table.hasAttribute('data-no-bulk') || table.closest('.modal-overlay, .modal-box') || table.querySelector('.ac-check, .hide-from-bulk')) return;
    var any = false;
    Array.prototype.slice.call(table.querySelectorAll('tbody > tr')).forEach(function (tr) {
      var item = rowItem(tr, table);
      var cell = tr.cells[0];
      if (!item || !cell || cell.colSpan > 1 || cell.querySelector('.bulk-check')) return;
      var c = document.createElement('input');
      c.type = 'checkbox'; c.className = 'bulk-check'; c._item = item; c.title = 'Select';
      c.style.cssText = BOX;
      c.addEventListener('click', function (e) { e.stopPropagation(); });
      c.addEventListener('change', function () {
        if (c.checked && active && active !== table) setAll(active, false);
        refresh(table);
      });
      placeBox(cell, c);
      any = true;
    });
    var th = table.querySelector('thead th');
    if (any && th && !th.querySelector('.bulk-all')) {
      var a = document.createElement('input');
      a.type = 'checkbox'; a.className = 'bulk-all'; a.title = 'Select all';
      a.style.cssText = BOX + 'accent-color:#fff;';
      a.addEventListener('click', function (e) { e.stopPropagation(); });
      a.addEventListener('change', function () {
        if (a.checked && active && active !== table) setAll(active, false);
        setAll(table, a.checked);
      });
      placeBox(th, a);
    }
  }


  document.querySelectorAll('table').forEach(enhance);
  // Tables that refresh themselves (e.g. live equipment lists) get their checkboxes back.
  var pending = null;
  new MutationObserver(function (muts) {
    if (muts.some(function (m) { if (!m.addedNodes.length) return false; var newTable = Array.prototype.some.call(m.addedNodes, function (n) { return n.nodeType === 1 && (n.tagName === 'TABLE' || (n.querySelector && n.querySelector('table'))); }); return newTable || (m.target.closest && m.target.closest('table') && !Array.prototype.every.call(m.addedNodes, function (n) { return n.nodeType === 1 && n.classList.contains('bulk-check'); })); })) {
      clearTimeout(pending);
      pending = setTimeout(function () { document.querySelectorAll('table').forEach(enhance); }, 100);
    }
  }).observe(document.body, { childList: true, subtree: true });

  try {
    var m = sessionStorage.getItem('bulkMsg');
    if (m) { sessionStorage.removeItem('bulkMsg'); uiToast(m, false); }
  } catch (e) {}
})();
</script>
