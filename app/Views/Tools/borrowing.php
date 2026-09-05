<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php $recordList = $records ?? []; ?>

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
          <tr class="<?= $rowClass ?>">
            <td class="tool-name-cell"><?= esc($r['asset_name'] ?? 'Unknown tool') ?></td>
            <td><?= esc($r['asset_code'] ?? '—') ?></td>
            <td><?= esc($r['borrower'] ?? 'Not on record') ?></td>
            <td><?= !empty($r['borrowed_date']) ? esc(date('M j, Y', strtotime($r['borrowed_date']))) : '—' ?></td>
            <td><?= !empty($r['expected_return']) ? esc(date('M j, Y', strtotime($r['expected_return']))) : '—' ?></td>
            <td><span class="status-badge <?= $badgeClass ?>"><?= esc($computedStatus) ?></span></td>
            <td>
              <div class="action-buttons">
                <button type="button" class="icon-btn" onclick="document.getElementById('viewModal<?= $r['id'] ?>').style.display='flex'" title="View Details" aria-label="View borrow details for <?= esc($r['asset_name'] ?? 'tool') ?>"><i class="fa-solid fa-eye"></i></button>
                <?php if ($r['status'] === 'Borrowed'): ?>
                  <form id="returnForm<?= $r['id'] ?>" method="post" action="<?= base_url('tools/returnTool/' . $r['tool_id']) ?>" style="display:contents;">
                    <?= csrf_field() ?>
                    <button type="button" class="icon-btn" onclick="confirmReturnTool('returnForm<?= $r['id'] ?>', '<?= esc($r['asset_name'] ?? 'this tool', 'js') ?>')" title="Mark Returned" aria-label="Mark <?= esc($r['asset_name'] ?? 'tool') ?> as returned"><i class="fa-solid fa-rotate-left"></i></button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="7">No borrowing records yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

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
  ?>
  <div class="modal" id="viewModal<?= $r['id'] ?>">
    <div class="modal-box">
      <h3><?= esc($r['asset_name'] ?? 'Unknown tool') ?></h3>
      <label>Code</label>
      <p><?= esc($r['asset_code'] ?? '—') ?></p>
      <label>Borrower</label>
      <p><?= esc($r['borrower'] ?? 'Not on record') ?></p>
      <label>Department</label>
      <p><?= esc($r['department'] ?? '—') ?></p>
      <label>Date Borrowed</label>
      <p><?= !empty($r['borrowed_date']) ? esc(date('M j, Y', strtotime($r['borrowed_date']))) : '—' ?></p>
      <label>Due Date</label>
      <p><?= !empty($r['expected_return']) ? esc(date('M j, Y', strtotime($r['expected_return']))) : '—' ?></p>
      <label>Status</label>
      <p><span class="status-badge <?= $badgeClass ?>"><?= esc($computedStatus) ?></span></p>
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('viewModal<?= $r['id'] ?>').style.display='none'">Close</button>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<div class="modal" id="confirmReturnModal">
  <div class="modal-box">
    <h3>Mark as Returned</h3>
    <p>Mark <strong id="confirmReturnToolName"></strong> as returned?</p>
    <div class="modal-actions">
      <button type="button" onclick="document.getElementById('confirmReturnModal').style.display='none'">Cancel</button>
      <button type="button" class="btn-approve" onclick="submitReturnTool()">Yes, Mark Returned</button>
    </div>
  </div>
</div>

<script>
function filterBorrowingTable() {
  const search = document.getElementById('borrowingSearch').value.toLowerCase();
  document.querySelectorAll('#borrowingTable tbody tr').forEach(row => {
    row.style.display = row.innerText.toLowerCase().includes(search) ? '' : 'none';
  });
}

let pendingReturnFormId = null;

function confirmReturnTool(formId, toolName) {
  pendingReturnFormId = formId;
  document.getElementById('confirmReturnToolName').textContent = toolName;
  document.getElementById('confirmReturnModal').style.display = 'flex';
}

function submitReturnTool() {
  if (pendingReturnFormId) document.getElementById(pendingReturnFormId).submit();
}
</script>

<?= $this->endSection() ?>
