<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
helper('facilities');
$recordList = $records ?? [];
$badgeClassFor = fn($status) => match ($status) {
    'Borrowed'          => 'badge blue',
    'Overdue'           => 'badge red',
    'Pending approval'  => 'badge amber',
    'Returned'          => 'badge green',
    // A consumable's terminal state — used up, never returned — reads as
    // informational rather than a warning, so it gets its own neutral look
    // instead of falling into the same amber as "Pending approval".
    'Consumed'          => 'badge dept',
    default             => 'badge amber',
};
$borrowDetails = [];
foreach ($recordList as $r) {
    $status = $r['computed_status'] ?? $r['status'];
    $borrowDetails[$r['id']] = [
        'tool'       => $r['asset_name'] ?? 'Unknown tool',
        'code'       => $r['asset_code'] ?? '—',
        'borrower'   => $r['borrower'] ?? 'Not on record',
        'department' => $r['department'] ?: '—',
        'borrowed'   => !empty($r['borrowed_date']) ? date('M j, Y', strtotime($r['borrowed_date'])) : '—',
        'due'        => !empty($r['expected_return']) ? date('M j, Y', strtotime($r['expected_return'])) : '—',
        // A consumable can be returned across more than one "Mark Returned"
        // click — quantity is the original amount borrowed, outstanding is
        // what's still out right now (net of any earlier partial returns).
        'quantity'      => (float) ($r['quantity'] ?? 1),
        'outstanding'   => (float) ($r['outstanding_qty'] ?? $r['quantity'] ?? 1),
        'stockTracked'  => $r['current_stock'] !== null,
        'unit'          => $r['unit'] ?: 'pcs',
        'toolId'        => $r['tool_id'],
        'category'      => $r['category'] ?? null,
        'status'     => $status,
        'statusBadgeClass' => $badgeClassFor($status),
    ];
}
?>

<div class="page-header">
  <div>
    <h1><?= esc($title ?? 'Borrowing') ?></h1>
    <p class="page-subtitle">All tool borrow transactions — active, overdue, and returned.</p>
  </div>
</div>

<div class="table-card">
  <div class="table-toolbar">
    <div class="toolbar-left">
      <div class="toolbar-search">
        <input type="text" id="borrowingSearch" class="search-box" placeholder="Search borrowing records…" title="Search by tool name, code, or borrower" oninput="filterBorrowingTable()">
        <i class="bi bi-search search-icon"></i>
      </div>
    </div>
    <div class="toolbar-right">
      <div class="filter-menu-wrapper">
        <button type="button" class="filter-btn" onclick="toggleBorrowFilterMenu()" aria-label="Open filters">
          <i class="bi bi-funnel"></i>
        </button>
        <div class="filter-popup" id="borrowFilterPopup">
          <div class="filter-popup-title">Filter</div>
          <div class="filter-row">
            <label for="borrowStatusFilter">Status</label>
            <select id="borrowStatusFilter" onchange="filterBorrowingTable()">
              <option value="">All Statuses</option>
              <option value="Borrowed">Borrowed</option>
              <option value="Overdue">Overdue</option>
              <option value="Returned">Returned</option>
              <option value="Pending approval">Pending approval</option>
              <option value="Consumed">Consumed</option>
            </select>
          </div>
          <div class="filter-row">
            <label for="borrowCategoryFilter">Category</label>
            <select id="borrowCategoryFilter" onchange="filterBorrowingTable()">
              <option value="">All Categories</option>
              <?php foreach (array_unique(array_filter(array_column($recordList, 'category'))) as $cat): ?>
                <option value="<?= esc($cat) ?>"><?= esc(tool_cat_label($cat)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="filter-row">
            <label for="borrowSort">Sort by</label>
            <select id="borrowSort" onchange="sortBorrowingRows()">
              <option value="newest">Newest</option>
              <option value="oldest">Oldest</option>
              <option value="latest">Latest borrowed</option>
              <option value="due">Due date (soonest)</option>
              <option value="tool">Tool (A–Z)</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="tools-table-scroll">
  <table id="borrowingTable" class="data-table">
    <thead>
      <tr>
        <th>Tool</th>
        <th>Code</th>
        <th>Borrower</th>
        <th>Date Borrowed</th>
        <th>Due Date</th>
        <th>Quantity</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($recordList)): ?>
        <?php foreach ($recordList as $r): ?>
          <?php
            $computedStatus = $r['computed_status'] ?? $r['status'];
            $badgeClass = match ($computedStatus) {
                'Borrowed'          => 'badge blue',
                'Overdue'           => 'badge red',
                'Pending approval'  => 'badge amber',
                'Returned'          => 'badge green',
                default             => 'badge amber',
            };
            $rowClass = $computedStatus === 'Overdue' ? 'row-overdue' : '';
          ?>
          <tr class="borrow-row <?= $rowClass ?>" data-id="<?= (int) $r['id'] ?>" data-status="<?= esc($computedStatus) ?>" data-borrowed="<?= esc((string) ($r['borrowed_date'] ?? '')) ?>" data-due="<?= esc((string) ($r['expected_return'] ?? '')) ?>" data-name="<?= esc($r['asset_name'] ?? '') ?>" data-category="<?= esc($r['category'] ?? '') ?>">
            <td class="tool-name-cell"><?= esc($r['asset_name'] ?? 'Unknown tool') ?></td>
            <td><?= esc($r['asset_code'] ?? '—') ?></td>
            <td><?= esc($r['borrower'] ?? 'Not on record') ?></td>
            <td><?= !empty($r['borrowed_date']) ? esc(date('M j, Y', strtotime($r['borrowed_date']))) : '—' ?></td>
            <td><?= !empty($r['expected_return']) ? esc(date('M j, Y', strtotime($r['expected_return']))) : '—' ?></td>
            <td><?= esc((string) (float) ($r['quantity'] ?? 1)) ?></td>
            <td><span class="status-badge <?= $badgeClass ?>"><?= esc($computedStatus) ?></span></td>
            <td>
              <div class="action-buttons">
                <button type="button" class="icon-btn" onclick="openBorrowDetail(<?= (int) $r['id'] ?>)" title="View Details" aria-label="View borrow details for <?= esc($r['asset_name'] ?? 'tool') ?>"><i class="bi bi-eye-fill"></i></button>
                <?php if ($r['status'] === 'Borrowed' && ($r['category'] ?? null) !== 'Consumable'): ?>
                  <form id="returnForm<?= $r['id'] ?>" method="post" action="<?= base_url('tools/returnTool/' . $r['tool_id']) ?>" style="display:contents;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="quantity" id="returnQtyInput<?= $r['id'] ?>" value="<?= esc((string) (float) ($r['outstanding_qty'] ?? $r['quantity'] ?? 1)) ?>">
                    <button type="button" class="icon-btn" onclick="confirmReturnTool(<?= (int) $r['id'] ?>, '<?= esc($r['asset_name'] ?? 'this tool', 'js') ?>')" title="Mark Returned" aria-label="Mark <?= esc($r['asset_name'] ?? 'tool') ?> as returned"><i class="bi bi-arrow-counterclockwise"></i></button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8">No borrowing records yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<!-- BORROW DETAIL MODAL — same wide "crosswise" popup treatment used
     elsewhere (Vehicle Management, Personnel Management, GPS Tracker,
     Mr. UBRA), one shared modal instead of one duplicated per row. -->
<div class="modal" id="borrowDetailModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 id="bdTitle">Borrowing Detail</h3>
      <button type="button" class="modal-close-btn" onclick="closeBorrowDetail()" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body" id="bdBody"></div>
  </div>
</div>

<div class="modal" id="confirmReturnModal">
  <div class="modal-box">
    <h3>Mark as Returned</h3>
    <p>Mark <strong id="confirmReturnToolName"></strong> as returned?</p>
    <div class="refill-stepper" id="confirmReturnQtyRow" style="display:none;">
      <button type="button" onclick="stepReturnQty(-1)" aria-label="Decrease quantity">&minus;</button>
      <input type="number" id="confirmReturnQtyInput" value="1" min="1">
      <button type="button" onclick="stepReturnQty(1)" aria-label="Increase quantity">+</button>
    </div>
    <p class="refill-subtitle" id="confirmReturnQtyHint" style="display:none;"></p>
    <div class="modal-actions">
      <button type="button" onclick="document.getElementById('confirmReturnModal').style.display='none'">Cancel</button>
      <button type="button" class="btn-approve" onclick="submitReturnTool()">Yes, Mark Returned</button>
    </div>
  </div>
</div>

<script>
function esc(s) {
  const d = document.createElement('div');
  d.textContent = String(s ?? '');
  return d.innerHTML;
}

const borrowDetails = <?= json_encode($borrowDetails, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function openBorrowDetail(id) {
  const r = borrowDetails[id];
  if (!r) return;

  document.getElementById('bdTitle').textContent = r.tool;
  document.getElementById('bdBody').innerHTML = `
    <div class="detail-section">
      <div class="detail-section-title">Borrowing Details</div>
      <div class="detail-grid">
        <div class="detail-row"><span>Tool</span><strong>${esc(r.tool)}</strong></div>
        <div class="detail-row"><span>Code</span><strong>${esc(r.code)}</strong></div>
        <div class="detail-row"><span>Borrower</span><strong>${esc(r.borrower)}</strong></div>
        <div class="detail-row"><span>Department</span><strong>${esc(r.department)}</strong></div>
        <div class="detail-row"><span>Date Borrowed</span><strong>${esc(r.borrowed)}</strong></div>
        <div class="detail-row"><span>Due Date</span><strong>${esc(r.due)}</strong></div>
        <div class="detail-row"><span>Quantity</span><strong>${esc(r.quantity)}</strong></div>
        <div class="detail-row"><span>Status</span><strong><span class="status-badge ${r.statusBadgeClass}">${esc(r.status)}</span></strong></div>
      </div>
    </div>`;

  document.getElementById('borrowDetailModal').style.display = 'flex';
}

function closeBorrowDetail() {
  document.getElementById('borrowDetailModal').style.display = 'none';
}

function filterBorrowingTable() {
  const search = document.getElementById('borrowingSearch').value.toLowerCase();
  const status = document.getElementById('borrowStatusFilter').value;
  const borrowCategory = document.getElementById('borrowCategoryFilter').value;
  document.querySelectorAll('#borrowingTable tbody tr').forEach(row => {
    const okSearch = row.innerText.toLowerCase().includes(search);
    const okStatus = !status || row.dataset.status === status;
    const okCat = !borrowCategory || row.dataset.category === borrowCategory;
    row.style.display = okSearch && okStatus && okCat ? '' : 'none';
  });
}

function sortBorrowingRows() {
  const mode = document.getElementById('borrowSort').value;
  const body = document.querySelector('#borrowingTable tbody');
  const rows = Array.from(body.querySelectorAll('.borrow-row'));
  rows.sort((a, b) => {
    if (mode === 'latest') return (b.dataset.borrowed || '').localeCompare(a.dataset.borrowed || '');
    if (mode === 'due') return (a.dataset.due || '9999').localeCompare(b.dataset.due || '9999');
    if (mode === 'tool') return a.dataset.name.localeCompare(b.dataset.name);
    const diff = Number(a.dataset.id) - Number(b.dataset.id);
    return mode === 'oldest' ? diff : -diff;
  });
  rows.forEach(r => body.appendChild(r));
}

function toggleBorrowFilterMenu() {
  document.getElementById('borrowFilterPopup').classList.toggle('visible');
}

document.addEventListener('click', e => {
  if (!e.target.closest('.filter-menu-wrapper')) {
    document.getElementById('borrowFilterPopup')?.classList.remove('visible');
  }
});

let pendingReturnId = null;

function confirmReturnTool(id, toolName) {
  pendingReturnId = id;
  const r = borrowDetails[id] || {};
  document.getElementById('confirmReturnToolName').textContent = toolName;

  const qtyRow = document.getElementById('confirmReturnQtyRow');
  const qtyHint = document.getElementById('confirmReturnQtyHint');
  const qtyInput = document.getElementById('confirmReturnQtyInput');
  const showStepper = r.stockTracked && r.outstanding > 1;

  qtyRow.style.display = showStepper ? 'flex' : 'none';
  qtyHint.style.display = showStepper ? 'block' : 'none';
  if (showStepper) {
    qtyInput.value = r.outstanding;
    qtyInput.max = r.outstanding;
    qtyHint.textContent = `${r.outstanding} ${r.unit} currently borrowed on this record.`;
  }

  document.getElementById('confirmReturnModal').style.display = 'flex';
}

function stepReturnQty(delta) {
  const input = document.getElementById('confirmReturnQtyInput');
  const max = Number(input.max) || 1;
  const next = (parseInt(input.value, 10) || 0) + delta;
  input.value = Math.min(Math.max(1, next), max);
}

function submitReturnTool() {
  if (!pendingReturnId) return;
  const r = borrowDetails[pendingReturnId] || {};
  if (r.stockTracked && r.outstanding > 1) {
    document.getElementById('returnQtyInput' + pendingReturnId).value = document.getElementById('confirmReturnQtyInput').value;
  }
  document.getElementById('returnForm' + pendingReturnId).submit();
}
</script>

<?= $this->endSection() ?>
