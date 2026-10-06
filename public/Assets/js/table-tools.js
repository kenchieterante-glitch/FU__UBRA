// Adds one working search + filter + sort toolbar above a status-box table.
// attachTableTools(panelElement) — the panel must contain a table.sj-table with a thead and tbody.
window.attachTableTools = function (panel) {
  const table = panel.querySelector('table.sj-table');
  const wrap = panel.querySelector('.table-wrap');
  if (table) window.attachRowDetails(panel);
  if (!table || !wrap || panel.querySelector('.tt-toolbar')) return;

  const body = table.querySelector('tbody');
  const dataRows = () => Array.from(body.querySelectorAll('tr')).filter(r => !r.querySelector('.empty-row'));
  if (!dataRows().length) return;

  const heads = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
  const cell = (row, i) => (row.children[i] ? row.children[i].textContent.trim() : '');
  const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  const statusIdx = heads.findIndex(h => /status|progress|result|priority|availability/i.test(h));
  const statusValues = statusIdx < 0 ? [] : Array.from(new Set(dataRows().map(r => cell(r, statusIdx)))).filter(Boolean).sort();

  // Building / Floor columns (when the table has them) get their own filters and sort choices.
  const buildingIdx = heads.findIndex(h => /^building$/i.test(h));
  const floorIdx = heads.findIndex(h => /^floor$/i.test(h));
  const floorOrder = ['Ground Floor', '1st Floor', '2nd Floor', '3rd Floor', '4th Floor', '5th Floor', '6th Floor', '7th Floor'];
  const floorRank = v => { const i = floorOrder.indexOf(v); return i < 0 ? 99 : i; };
  const uniq = i => Array.from(new Set(dataRows().map(r => cell(r, i)))).filter(v => v && v !== '—');
  const buildingValues = buildingIdx < 0 ? [] : uniq(buildingIdx).sort((a, b) => a.localeCompare(b));
  const floorValues = floorIdx < 0 ? [] : uniq(floorIdx).sort((a, b) => floorRank(a) - floorRank(b) || a.localeCompare(b));

  const original = dataRows();
  // Lists arrive newest-first: Newest = list order, Oldest = reversed, Latest = most recent date in the table (falls back to list order).
  const dateCol = (() => {
    const preferred = heads.findIndex(h => /date|borrowed|returned|inspected|time/i.test(h));
    const candidates = preferred >= 0 ? [preferred] : heads.map((h, i) => i);
    return candidates.find(i => original.some(r => !isNaN(Date.parse(cell(r, i))) && /\d{4}/.test(cell(r, i)))) ?? -1;
  })();
  const sortOptions = '<option value="newest">Newest</option><option value="oldest">Oldest</option><option value="latest">Latest</option>'
    + (buildingIdx < 0 ? '' : '<option value="building">Building (A–Z)</option>')
    + (floorIdx < 0 ? '' : '<option value="floor">Floor (Ground → upper)</option>');

  const bar = document.createElement('div');
  bar.className = 'fe-toolbar-row tt-toolbar';
  bar.innerHTML = `
    <div class="toolbar-search">
      <input type="text" class="search-box" placeholder="Search this list…">
      <i class="bi bi-search search-icon"></i>
    </div>
    <div class="fe-toolbar-actions">
      <div class="filter-menu-wrapper">
        <button type="button" class="filter-btn" aria-label="Open filters"><i class="bi bi-funnel"></i></button>
        <div class="filter-popup">
          <div class="filter-popup-title">Filter</div>
          ${statusIdx < 0 ? '' : `<div class="filter-row"><label>${esc(heads[statusIdx])}</label>
            <select class="tt-filter"><option value="">All</option>${statusValues.map(v => `<option value="${esc(v)}">${esc(v)}</option>`).join('')}</select></div>`}
          ${buildingValues.length ? `<div class="filter-row"><label>Building</label><select class="tt-building"><option value="">All buildings</option>${buildingValues.map(v => `<option value="${esc(v)}">${esc(v)}</option>`).join('')}</select></div>` : ''}
          ${floorValues.length ? `<div class="filter-row"><label>Floor</label><select class="tt-floor"><option value="">All floors</option>${floorValues.map(v => `<option value="${esc(v)}">${esc(v)}</option>`).join('')}</select></div>` : ''}
          <div class="filter-row"><label>Sort by</label><select class="tt-sort">${sortOptions}</select></div>
        </div>
      </div>
    </div>`;
  wrap.parentNode.insertBefore(bar, wrap);

  const search = bar.querySelector('.search-box');
  const filter = bar.querySelector('.tt-filter');
  const bSel = bar.querySelector('.tt-building');
  const fSel = bar.querySelector('.tt-floor');
  const sort = bar.querySelector('.tt-sort');
  const popup = bar.querySelector('.filter-popup');

  function apply() {
    const q = search.value.trim().toLowerCase();
    const f = filter ? filter.value : '';
    original.forEach(r => {
      const okQ = !q || r.textContent.toLowerCase().includes(q);
      const okF = !f || cell(r, statusIdx) === f;
      const okB = !bSel || !bSel.value || cell(r, buildingIdx) === bSel.value;
      const okFl = !fSel || !fSel.value || cell(r, floorIdx) === fSel.value;
      r.style.display = okQ && okF && okB && okFl ? '' : 'none';
    });
  }

  function reorder() {
    const v = sort.value;
    let list = original.slice();
    if (v === 'oldest') list.reverse();
    if (v === 'building') list.sort((x, y) => cell(x, buildingIdx).localeCompare(cell(y, buildingIdx)) || floorRank(cell(x, floorIdx)) - floorRank(cell(y, floorIdx)));
    if (v === 'floor') list.sort((x, y) => floorRank(cell(x, floorIdx)) - floorRank(cell(y, floorIdx)) || cell(x, buildingIdx).localeCompare(cell(y, buildingIdx)));
    if (v === 'latest' && dateCol >= 0) {
      const t = r => { const d = Date.parse(cell(r, dateCol)); return isNaN(d) ? -Infinity : d; };
      list.sort((x, y) => t(y) - t(x));
    }
    list.forEach(r => body.appendChild(r));
  }

  search.addEventListener('input', apply);
  if (filter) filter.addEventListener('change', apply);
  if (bSel) bSel.addEventListener('change', apply);
  if (fSel) fSel.addEventListener('change', apply);
  sort.addEventListener('change', reorder);
  bar.querySelector('.filter-btn').addEventListener('click', e => {
    e.stopPropagation();
    const open = !popup.classList.contains('visible');
    document.querySelectorAll('.filter-popup.visible').forEach(p => p.classList.remove('visible'));
    popup.classList.toggle('visible', open);
  });
  popup.addEventListener('click', e => e.stopPropagation());
  document.addEventListener('click', () => popup.classList.remove('visible'));
};

// Clicking a record in a status table opens a popup with all of its details.
window.attachRowDetails = function (panel) {
  const table = panel.querySelector('table.sj-table');
  if (!table || table.dataset.rowDetails) return;
  table.dataset.rowDetails = '1';
  const heads = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
  const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  table.querySelectorAll('tbody tr').forEach(row => {
    if (row.querySelector('.empty-row') || row.hasAttribute('onclick')) return;
    row.style.cursor = 'pointer';
    row.title = 'Click to view details';
    row.addEventListener('click', e => {
      if (e.target.closest('a, button, input, select, form')) return;
      const cells = Array.from(row.children);
      const fields = cells.map((c, i) => ({ k: heads[i] || '', v: c.innerHTML.trim(), t: c.textContent.trim() })).filter(f => f.k && f.k !== 'Action');
      const ov = document.createElement('div');
      ov.className = 'tt-modal-overlay';
      ov.innerHTML = `<div class="tt-modal"><div class="tt-modal-head"><h3>${esc(fields[0] ? fields[0].t : 'Details')}</h3><button type="button" class="tt-modal-x" aria-label="Close">&times;</button></div>
        <div class="tt-modal-grid">${fields.slice(1).map(f => `<div><div class="tt-k">${esc(f.k)}</div><div class="tt-v">${f.t ? f.v : '—'}</div></div>`).join('')}</div></div>`;
      const close = () => { ov.remove(); document.removeEventListener('keydown', onKey); };
      const onKey = ev => { if (ev.key === 'Escape') close(); };
      ov.addEventListener('click', ev => { if (ev.target === ov || ev.target.closest('.tt-modal-x')) close(); });
      document.addEventListener('keydown', onKey);
      document.body.appendChild(ov);
    });
  });
};
