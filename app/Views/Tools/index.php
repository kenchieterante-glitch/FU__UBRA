<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$toolList = $tools ?? [];
$personnelList = $personnel ?? [];
$title = $title ?? 'Tools Equipment Management';
$showCategoryTabs = in_array($title, ['Power Tools', 'Consumable', 'Sports Equipment'], true);
$isConsumablePage = ($title === 'Consumable');
?>

<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle"><?= $showCategoryTabs ? 'Tools and equipment in the ' . esc($title) . ' category.' : 'Manage all university operational assets, tools, equipment, and assigned resources.' ?></p>
  </div>
  <button class="btn-add" onclick="document.getElementById('addModal').style.display='flex'">+ Add New Tool</button>
</div>

<?php if (!$showCategoryTabs): ?>
<div class="stat-cards" id="toolsStatCards">
  <div class="stat-card stat-card-clickable" onclick="filterToolsByStat('')" role="button" tabindex="0">
    <span class="stat-icon tone-maroon"><i class="bi bi-tools"></i></span>
    <h3>Total Tools</h3>
    <div class="value"><?= esc((string) ((int) ($total_tools ?? 0))) ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="filterToolsByStat('available')" role="button" tabindex="0">
    <span class="stat-icon tone-green"><i class="bi bi-check-circle-fill"></i></span>
    <h3>Available Tools</h3>
    <div class="value"><?= esc((string) ((int) ($available_tools ?? 0))) ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="filterToolsByStat('borrowed')" role="button" tabindex="0">
    <span class="stat-icon tone-neutral"><i class="bi bi-hand-index-thumb-fill"></i></span>
    <h3>Borrowed Tools</h3>
    <div class="value"><?= esc((string) ((int) ($borrowed_tools ?? 0))) ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="filterToolsByStat('maintenance')" role="button" tabindex="0">
    <span class="stat-icon tone-gold"><i class="bi bi-wrench-adjustable"></i></span>
    <h3>Needs Maintenance</h3>
    <div class="value"><?= esc((string) ((int) ($maintenance_tools ?? 0))) ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="filterToolsByStat('disposal')" role="button" tabindex="0">
    <span class="stat-icon tone-red"><i class="bi bi-trash3-fill"></i></span>
    <h3>Disposal</h3>
    <div class="value"><?= esc((string) ((int) ($disposal_tools ?? 0))) ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="filterToolsByStat('consumable')" role="button" tabindex="0">
    <span class="stat-icon tone-blue"><i class="bi bi-box2"></i></span>
    <h3>Consumable</h3>
    <div class="value"><?= esc((string) ((int) ($consumable_tools ?? 0))) ?></div>
  </div>
</div>
<?php endif; ?>

<div class="table-card">
  <div class="table-toolbar">
    <div class="toolbar-left">
      <div class="toolbar-search">
        <input type="text" id="toolsSearch" class="search-box" placeholder="Search tools…" title="Search by tool name, code, category, or location" oninput="filterToolsTable()">
        <i class="bi bi-search search-icon"></i>
      </div>
    </div>
    <div class="toolbar-right">
      <div class="filter-menu-wrapper">
        <button type="button" class="filter-btn" onclick="toggleToolsFilterMenu()" aria-label="Open filters">
          <i class="bi bi-funnel"></i>
        </button>
        <div class="filter-popup" id="toolsFilterPopup">
          <div class="filter-popup-title">Filter</div>
          <div class="filter-row">
            <label for="toolsCategory">Category</label>
            <select id="toolsCategory" onchange="filterToolsTable()">
              <option value="">All Categories</option>
              <option value="Power Tools">Power Tools</option>
              <option value="Consumable">Consumable</option>
              <option value="Sports Equipment">Sports Equipment</option>
              <option value="IT Equipment">IT Equipment</option>
              <option value="Media Studio">Media Studio</option>
              <option value="Janitorial">Janitorial</option>
              <option value="Tools">Tools</option>
            </select>
          </div>
          <div class="filter-row">
            <label for="toolsAvailability">Availability</label>
            <select id="toolsAvailability" onchange="filterToolsTable()">
              <option value="">All Availability</option>
              <option value="Available">Available</option>
              <option value="Borrowed">Borrowed</option>
              <option value="Maintenance">Maintenance</option>
              <option value="Disposal">Disposal</option>
            </select>
          </div>
          <div class="filter-row">
            <label for="toolsCondition">Condition</label>
            <select id="toolsCondition" onchange="filterToolsTable()">
              <option value="">All Conditions</option>
              <option value="Excellent">Excellent</option>
              <option value="Good">Good</option>
              <option value="Fair">Fair</option>
              <option value="Poor">Poor</option>
            </select>
          </div>
          <div class="filter-row">
            <label for="toolsSort">Sort By</label>
            <select id="toolsSort" onchange="applyToolsSort()">
              <option value="">Default</option>
              <option value="0-asc">Tool Name (A&ndash;Z)</option>
              <option value="0-desc">Tool Name (Z&ndash;A)</option>
              <option value="2-asc">Category (A&ndash;Z)</option>
              <option value="2-desc">Category (Z&ndash;A)</option>
              <option value="5-asc">Condition (A&ndash;Z)</option>
              <option value="5-desc">Condition (Z&ndash;A)</option>
              <option value="6-asc">Status (A&ndash;Z)</option>
              <option value="6-desc">Status (Z&ndash;A)</option>
              <?php if ($isConsumablePage): ?>
                <option value="8-asc">Stock (Low&ndash;High)</option>
                <option value="8-desc">Stock (High&ndash;Low)</option>
              <?php endif; ?>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="tools-table-scroll">
  <table id="toolsTable" class="data-table">
  <thead>
    <tr>
      <th>Tools</th>
      <th>Code</th>
      <th>Category</th>
      <th>Location</th>
      <th>Custodian</th>
      <th>Condition</th>
      <th>Status</th>
      <th>Borrower</th>
      <?php if ($isConsumablePage): ?><th>Stock</th><?php endif; ?>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($toolList)): ?>
      <?php foreach ($toolList as $t): ?>
        <tr>
          <td class="tool-name-cell"><?= esc($t['asset_name']) ?></td>
          <td><?= esc($t['asset_code']) ?></td>
          <td><?= esc($t['category']) ?></td>
          <td><?= esc($t['location']) ?></td>
          <td><?= esc($t['custodian_name'] ?? 'Unassigned') ?></td>
          <td><span class="status-badge status-<?= strtolower($t['condition_status']) ?>"><?= esc($t['condition_status']) ?></span></td>
          <td><span class="status-badge status-<?= strtolower($t['availability']) ?>"><?= esc($t['availability']) ?></span></td>
          <td><?= (($t['availability'] ?? '') === 'Borrowed') ? esc($t['borrower_name'] ?? 'Not on record') : '—' ?></td>
          <?php if ($isConsumablePage): ?>
            <?php
              $stockQty = (float) ($t['current_stock'] ?? 0);
              $reorderLevel = (float) ($t['reorder_threshold'] ?? 0);
              $unitLabel = $t['unit'] ?? 'pcs';
              $stockBadge = $stockQty <= 0 ? ['inv-out', 'Out of Stock'] : ($stockQty <= $reorderLevel ? ['inv-low', 'Low Stock'] : ['inv-ok', 'Full Stock']);
            ?>
            <td>
              <strong><?= esc((string) $stockQty) ?> <?= esc($unitLabel) ?></strong>
              <span class="inv-badge <?= $stockBadge[0] ?>"><?= $stockBadge[1] ?></span>
            </td>
          <?php endif; ?>
          <td>
            <div class="action-buttons">
              <button type="button" class="icon-btn" onclick="openToolDetail(<?= (int) $t['id'] ?>)" title="View Details" aria-label="View details for <?= esc($t['asset_name']) ?>"><i class="bi bi-eye-fill"></i></button>
              <button type="button" class="icon-btn" onclick="document.getElementById('editModal<?= $t['id'] ?>').style.display='flex'" title="Edit" aria-label="Edit <?= esc($t['asset_name']) ?>"><i class="bi bi-pencil-fill"></i></button>
              <?php if ($isConsumablePage): ?>
                <button type="button" class="icon-btn" title="Refill" aria-label="Refill <?= esc($t['asset_name']) ?>" onclick="refillToolStock(<?= (int) $t['id'] ?>, '<?= esc($t['asset_name'], 'js') ?>', '<?= esc($t['unit'] ?? 'pcs', 'js') ?>')"><i class="bi bi-upload"></i></button>
              <?php else: ?>
                <form method="post" action="<?= base_url('tools/delete/'.$t['id']) ?>" onsubmit="return confirm('Archive this tool?')" style="display:contents;">
                  <?= csrf_field() ?>
                  <button type="submit" class="icon-btn delete" title="Archive" aria-label="Archive <?= esc($t['asset_name']) ?>"><i class="bi bi-archive-fill"></i></button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="<?= $isConsumablePage ? 10 : 9 ?>">No assets recorded yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
  </div>
</div>

<?php if ($isConsumablePage): ?>
<div class="table-card" style="margin-top:20px;">
  <div class="table-toolbar"><div class="toolbar-left"><h3 style="margin:0;"><i class="bi bi-clock-history"></i> Refill Log</h3></div></div>
  <div class="tools-table-scroll">
  <table class="data-table">
    <thead>
      <tr><th>Item</th><th>Quantity Added</th><th>Date &amp; Time</th><th>Performed By</th></tr>
    </thead>
    <tbody id="toolsRefillLogBody"></tbody>
  </table>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($toolList)): ?>
  <?php foreach ($toolList as $t): ?>
    <div class="modal" id="editModal<?= $t['id'] ?>">
      <div class="modal-box">
        <h3>Edit Tool</h3>
        <form action="<?= base_url('tools/edit/'.$t['id']) ?>" method="post">
          <?= csrf_field() ?>
          <label>Tool Name <span class="required-mark">*</span></label>
          <input type="text" name="asset_name" value="<?= esc($t['asset_name']) ?>" required>
          <label>Tool Code</label>
          <input type="text" name="asset_code" value="<?= esc($t['asset_code']) ?>">
          <label>Category</label>
          <select name="category">
            <option value="">— Select Category —</option>
            <?php foreach (['Power Tools', 'Consumable', 'Sports Equipment', 'IT Equipment', 'Media Studio', 'Janitorial', 'Tools'] as $cat): ?>
              <option value="<?= esc($cat) ?>" <?= $t['category'] === $cat ? 'selected' : '' ?>><?= esc($cat) ?></option>
            <?php endforeach; ?>
          </select>
          <label>Location</label>
          <input type="text" name="location" value="<?= esc($t['location']) ?>">
          <label>Custodian</label>
          <select name="custodian">
            <option value="">— Unassigned —</option>
            <?php foreach ($personnelList as $person): ?>
              <option value="<?= esc($person['full_name']) ?>" <?= ($t['custodian'] ?? '') === $person['full_name'] ? 'selected' : '' ?>><?= esc($person['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
          <label>Condition</label>
          <select name="condition_status">
            <?php foreach (['Excellent', 'Good', 'Fair', 'Poor'] as $cond): ?>
              <option value="<?= esc($cond) ?>" <?= $t['condition_status'] === $cond ? 'selected' : '' ?>><?= esc($cond) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($t['category'] === 'Consumable'): ?>
            <label>Unit</label>
            <input type="text" name="unit" list="toolUnitOptions" value="<?= esc($t['unit'] ?? 'pcs') ?>" placeholder="e.g. rolls, bottles, pcs">
          <?php endif; ?>
          <div class="modal-actions">
            <button type="button" onclick="document.getElementById('editModal<?= $t['id'] ?>').style.display='none'">Cancel</button>
            <button type="submit" class="btn-maroon">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<!-- TOOL DETAIL MODAL — view-only popup showing usage count and full
     borrow history, same layout pattern as Vehicle Management's detail
     popup (maroon header, .detail-grid / .detail-row, .history-table). -->
<div class="modal" id="toolDetailModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 id="tdTitle">Tool Detail</h3>
      <div class="modal-header-actions">
        <button type="button" class="modal-close-btn" onclick="closeToolDetail()" aria-label="Close"><i class="bi bi-x-lg"></i></button>
      </div>
    </div>
    <div class="modal-body" id="tdBody"></div>
  </div>
</div>

<script>
const toolDetails = <?= $tool_details_json ?? '{}' ?>;

function esc(s) {
  const d = document.createElement('div');
  d.textContent = String(s ?? '');
  return d.innerHTML;
}

function openToolDetail(id) {
  const t = toolDetails[id];
  if (!t) return;

  document.getElementById('tdTitle').textContent = t.name;

  const historyRows = t.history.length
    ? t.history.map(h => `<tr><td>${esc(h.borrowed)}</td><td>${esc(h.borrower)}</td><td>${esc(h.department)}</td><td>${esc(h.due)}</td><td>${esc(h.status)}</td></tr>`).join('')
    : `<tr><td colspan="5">No borrow history recorded yet.</td></tr>`;

  document.getElementById('tdBody').innerHTML = `
    <div class="detail-section">
      <div class="detail-section-title">Tool Details</div>
      <div class="detail-grid">
        <div class="detail-row"><span>Code</span><strong>${esc(t.code)}</strong></div>
        <div class="detail-row"><span>Category</span><strong>${esc(t.category)}</strong></div>
        <div class="detail-row"><span>Location</span><strong>${esc(t.location)}</strong></div>
        <div class="detail-row"><span>Custodian</span><strong>${esc(t.custodian)}</strong></div>
        <div class="detail-row"><span>Condition</span><strong>${esc(t.condition)}</strong></div>
        <div class="detail-row"><span>Availability</span><strong>${esc(t.availability)}</strong></div>
        ${t.unit ? `<div class="detail-row"><span>Stock</span><strong>${esc(t.unit)}</strong></div>` : ''}
      </div>
    </div>

    <div class="detail-section">
      <div class="detail-section-title">Usage</div>
      <div class="detail-grid">
        <div class="detail-row"><span>Times Borrowed</span><strong>${esc(t.timesBorrowed)}</strong></div>
      </div>
    </div>

    <div class="detail-section">
      <div class="detail-section-title">Borrow History</div>
      <div class="history-table-wrap">
        <table class="history-table">
          <thead><tr><th>Date Borrowed</th><th>Borrower</th><th>Department</th><th>Due Back</th><th>Status</th></tr></thead>
          <tbody>${historyRows}</tbody>
        </table>
      </div>
    </div>`;

  document.getElementById('toolDetailModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeToolDetail() {
  document.getElementById('toolDetailModal').style.display = 'none';
  document.body.style.overflow = '';
}

const toolsRefillLogEntries = <?= $refill_log_json ?? '[]' ?>;

function renderToolsRefillLog() {
  const body = document.getElementById('toolsRefillLogBody');
  if (!body) return;

  if (!toolsRefillLogEntries.length) {
    body.innerHTML = `<tr><td colspan="4">No refills recorded yet.</td></tr>`;
    return;
  }

  body.innerHTML = toolsRefillLogEntries.map(entry => {
    const dt = new Date(entry.at.replace(' ', 'T'));
    const dateStr = isNaN(dt) ? entry.at : dt.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
    return `<tr>
      <td><strong>${entry.item}</strong></td>
      <td>+${entry.qty} ${entry.unit || 'pcs'}</td>
      <td>${dateStr}</td>
      <td>${entry.by}</td>
    </tr>`;
  }).join('');
}
renderToolsRefillLog();

function filterToolsTable() {
  const search = document.getElementById('toolsSearch').value.toLowerCase();
  const category = document.getElementById('toolsCategory').value.toLowerCase();
  const availability = document.getElementById('toolsAvailability').value.toLowerCase();
  const condition = document.getElementById('toolsCondition').value.toLowerCase();
  document.querySelectorAll('#toolsTable tbody tr').forEach(row => {
    const text = row.innerText.toLowerCase();
    const categoryText = row.children[2]?.innerText.toLowerCase() ?? '';
    const conditionText = row.children[5]?.innerText.toLowerCase() ?? '';
    const availabilityText = row.children[6]?.innerText.toLowerCase() ?? '';
    const matches = text.includes(search)
      && (!category || categoryText === category)
      && (!availability || availabilityText === availability)
      && (!condition || conditionText === condition);
    row.style.display = matches ? '' : 'none';
  });
}

// "Sort By" lives in the filter popup (#toolsSort) rather than clickable
// column headers. Sorts the actual DOM row order (not just what's
// visible), so it stays correct together with filterToolsTable()'s
// show/hide. toolsOriginalOrder is captured once so picking "Default"
// can restore it without a page reload.
let toolsOriginalOrder = null;

function applyToolsSort() {
  const tbody = document.querySelector('#toolsTable tbody');
  if (!tbody) return;

  if (!toolsOriginalOrder) {
    toolsOriginalOrder = Array.from(tbody.querySelectorAll('tr'));
  }

  const value = document.getElementById('toolsSort').value;
  if (!value) {
    toolsOriginalOrder.forEach(row => tbody.appendChild(row));
    return;
  }

  const [colIndexStr, direction] = value.split('-');
  const colIndex = parseInt(colIndexStr, 10);
  const ascending = direction === 'asc';

  const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.children.length > 1);
  rows.sort((a, b) => {
    const aText = a.children[colIndex]?.innerText.trim() ?? '';
    const bText = b.children[colIndex]?.innerText.trim() ?? '';
    const aNum = parseFloat(aText);
    const bNum = parseFloat(bText);
    const bothNumeric = !isNaN(aNum) && !isNaN(bNum) && aText !== '' && bText !== '';
    const cmp = bothNumeric ? (aNum - bNum) : aText.localeCompare(bText, undefined, { sensitivity: 'base' });
    return ascending ? cmp : -cmp;
  });

  rows.forEach(row => tbody.appendChild(row));
}

// Stat cards act as quick filters into the table below — same as Vehicle
// Management: the cards stay right where they are, the table just filters
// in place, no hiding the cards and no "Back to Overview" bar.
function filterToolsByStat(kind) {
  document.getElementById('toolsSearch').value = '';
  document.getElementById('toolsCategory').value = '';
  document.getElementById('toolsAvailability').value = '';
  document.getElementById('toolsCondition').value = '';

  if (kind === 'available')   document.getElementById('toolsAvailability').value = 'Available';
  if (kind === 'borrowed')    document.getElementById('toolsAvailability').value = 'Borrowed';
  if (kind === 'maintenance') document.getElementById('toolsAvailability').value = 'Maintenance';
  if (kind === 'disposal')    document.getElementById('toolsAvailability').value = 'Disposal';
  if (kind === 'consumable')  document.getElementById('toolsCategory').value = 'Consumable';

  filterToolsTable();
}

document.querySelectorAll('.stat-card-clickable').forEach(card => {
  card.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      card.click();
    }
  });
});

function toggleToolsFilterMenu() {
  const popup = document.getElementById('toolsFilterPopup');
  if (!popup) return;
  popup.classList.toggle('visible');
}

// Consumables don't get archived when they run low — they get refilled.
let refillToolId = null;

let refillToolUnit = 'pcs';

function refillToolStock(id, name, unit) {
  refillToolId = id;
  refillToolUnit = unit || 'pcs';
  document.getElementById('refillItemName').textContent = name;
  document.getElementById('refillUnitLabel').textContent = refillToolUnit;
  document.getElementById('refillQtyInput').value = 1;
  document.getElementById('refillModal').style.display = 'flex';
}

function stepRefillQty(delta) {
  const input = document.getElementById('refillQtyInput');
  const next = (parseInt(input.value, 10) || 0) + delta;
  input.value = Math.max(1, next);
}

function confirmRefill() {
  const qty = document.getElementById('refillQtyInput').value;
  if (!qty || isNaN(qty) || Number(qty) <= 0 || refillToolId === null) return;

  const fd = new FormData();
  fd.append('quantity', qty);
  fetch(`<?= base_url('tools/refillStock/') ?>${refillToolId}`, { method: 'POST', headers: csrfHeaders(), body: fd })
    .then(() => window.location.reload());
}

document.addEventListener('click', e => {
  if (!e.target.closest('.filter-menu-wrapper')) {
    document.getElementById('toolsFilterPopup')?.classList.remove('visible');
  }
});

// Arriving from the Dashboard's stat boxes (e.g. tools?filter=borrowed).
const toolsUrlFilter = new URLSearchParams(window.location.search).get('filter');
if (toolsUrlFilter) filterToolsByStat(toolsUrlFilter);
</script>

<datalist id="toolUnitOptions">
  <option value="pcs">
  <option value="rolls">
  <option value="bottles">
  <option value="box">
  <option value="liter">
  <option value="pack">
</datalist>

<!-- ADD MODAL -->
<div class="modal" id="addModal">
  <div class="modal-box">
    <h3>Add New Tool</h3>
    <form action="<?= base_url('tools/add') ?>" method="post">
      <?= csrf_field() ?>
      <p class="required-note">Fields marked <span class="required-mark">*</span> are required.</p>
      <label>Tool Name <span class="required-mark">*</span></label>
      <input type="text" name="asset_name" placeholder="e.g. Cordless drill" required>
      <label>Tool Code</label>
      <input type="text" name="asset_code" placeholder="e.g. TL-0042">
      <label>Category</label>
      <select name="category" onchange="document.getElementById('addUnitField').style.display = this.value === 'Consumable' ? 'block' : 'none'">
        <option value="">— Select Category —</option>
        <option value="Power Tools">Power Tools</option>
        <option value="Consumable">Consumable</option>
        <option value="Sports Equipment">Sports Equipment</option>
        <option value="IT Equipment">IT Equipment</option>
        <option value="Media Studio">Media Studio</option>
        <option value="Janitorial">Janitorial</option>
        <option value="Tools">Tools</option>
      </select>
      <div id="addUnitField" style="display:none">
        <label>Unit</label>
        <input type="text" name="unit" list="toolUnitOptions" placeholder="e.g. rolls, bottles, pcs">
      </div>
      <label>Location</label>
      <input type="text" name="location" placeholder="e.g. Shelf B-3">
      <label>Custodian</label>
      <select name="custodian">
        <option value="">— Unassigned —</option>
        <?php foreach ($personnelList as $person): ?>
          <option value="<?= esc($person['full_name']) ?>"><?= esc($person['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
      <label>Condition</label>
      <select name="condition_status">
        <option>Excellent</option>
        <option>Good</option>
        <option>Fair</option>
        <option>Poor</option>
      </select>
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-maroon">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- REFILL MODAL -->
<div class="modal" id="refillModal">
  <div class="modal-box">
    <div class="refill-header">
      <h3>Restock</h3>
    </div>
    <p class="refill-subtitle">Refill "<span id="refillItemName"></span>"</p>
    <div class="refill-stepper">
      <button type="button" onclick="stepRefillQty(-1)" aria-label="Decrease quantity">&minus;</button>
      <input type="number" id="refillQtyInput" value="1" min="1">
      <button type="button" onclick="stepRefillQty(1)" aria-label="Increase quantity">+</button>
      <span id="refillUnitLabel"></span>
    </div>
    <div class="modal-actions">
      <button type="button" onclick="document.getElementById('refillModal').style.display='none'">Cancel</button>
      <button type="button" class="btn-approve" onclick="confirmRefill()">Confirm Refill</button>
    </div>
  </div>
</div>

<?= $this->endSection() ?>