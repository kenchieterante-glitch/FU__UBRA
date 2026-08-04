<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('Assets/css/notifications.css') . '?v=' . @filemtime(FCPATH.'Assets/css/notifications.css') ?>">

<div class="notif-wrapper">

    <!-- ── PAGE HEADER ──────────────────────────────────────────── -->
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-bell-fill"></i> Notification Center</h1>
            <p class="page-subtitle">View and manage all intelligent reminders, alerts, and operational notifications.</p>
        </div>
        <div class="header-actions">
            <form method="post" action="<?= base_url('notifications/markAllRead') ?>" style="display:contents;">
                <?= csrf_field() ?>
                <button type="submit" class="btn-outline">
                    <i class="bi bi-check2-all"></i> Mark All Read
                </button>
            </form>
            <a href="<?= base_url('notifications/export') ?>" class="btn-outline">
                <i class="bi bi-download"></i> Export Log
            </a>
        </div>
    </div>

    <?php
$notifications = $notifications ?? [];
$unread_count = $unread_count ?? 0;
$today_count = $today_count ?? 0;
$upcoming_count = $upcoming_count ?? 0;
$draft_count = $draft_count ?? 0;
?>

    <!-- ── SUMMARY CARDS ────────────────────────────────────────── -->
    <div class="summary-grid">
        <div class="summary-card unread">
            <div class="sc-icon"><i class="bi bi-envelope-fill"></i></div>
            <div class="sc-body">
                <div class="sc-value"><?= $unread_count ?></div>
                <div class="sc-label">Unread Notifications</div>
                <div class="sc-sub danger">Requires immediate review</div>
            </div>
        </div>
        <div class="summary-card today">
            <div class="sc-icon"><i class="bi bi-calendar-check-fill"></i></div>
            <div class="sc-body">
                <div class="sc-value"><?= $today_count ?></div>
                <div class="sc-label">Today's Alerts</div>
                <div class="sc-sub success"><?= $today_done_count ?? 0 ?> Completed</div>
            </div>
        </div>
        <div class="summary-card upcoming">
            <div class="sc-icon"><i class="bi bi-clock-history"></i></div>
            <div class="sc-body">
                <div class="sc-value"><?= $upcoming_count ?></div>
                <div class="sc-label">Upcoming Schedules</div>
                <div class="sc-sub info">Operational milestones</div>
            </div>
        </div>
        <div class="summary-card draft">
            <div class="sc-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
            <div class="sc-body">
                <div class="sc-value"><?= $draft_count ?></div>
                <div class="sc-label">Draft Messages</div>
                <div class="sc-sub muted">Not yet sent</div>
            </div>
        </div>
    </div>

    <!-- ── BODY: LOG TABLE + RIGHT SIDEBAR ──────────────────────── -->
    <div class="notif-body">

        <!-- Notification log -->
        <div class="table-panel">
            <div class="table-toolbar">
                <h2 class="panel-title">Operational Alerts Log</h2>
                <div class="toolbar-right">
                    <div class="toolbar-search">
                      <input type="text" id="searchInput" class="search-box" placeholder="Search alerts, recipients, or categories" oninput="filterTable()">
                      <i class="bi bi-search search-icon"></i>
                    </div>
                    <div class="filter-menu-wrapper">
                        <button type="button" class="filter-btn" onclick="toggleNotificationsFilterMenu()" aria-label="Open filters">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <div class="filter-popup" id="notificationsFilterPopup">
                            <div class="filter-popup-title">Filter</div>
                            <div class="filter-row">
                                <label for="readFilter"><i class="bi bi-envelope"></i> View</label>
                                <select id="readFilter" onchange="filterTable()">
                                    <option value="">All Notifications</option>
                                    <option value="unread">Unread Only</option>
                                    <option value="read">Read Only</option>
                                </select>
                            </div>
                            <div class="filter-row">
                                <label for="catFilter"><i class="bi bi-tags"></i> Category</label>
                                <select id="catFilter" onchange="filterTable()">
                                    <option value=""> All Categories</option>
                                    <option value="Vehicle Inspection"> Vehicle Inspection</option>
                                    <option value="Air-Con Cleaning"> Air-Con Cleaning</option>
                                    <option value="Janitorial Assignment"> Janitorial Assignment</option>
                                    <option value="Inventory Low Stock"> Inventory Low Stock</option>
                                    <option value="Vehicle Expiry">🗓 Vehicle Expiry</option>
                                    <option value="Fire Extinguisher Installed"> Fire Extinguisher Installed</option>
                                    <option value="Fire Extinguisher Expiring Soon">⏳ Fire Extinguisher Expiring Soon</option>
                                    <option value="Aircon Unit Registered"> Aircon Unit Registered</option>
                                    <option value="Aircon Needs Cleaning">🧊 Aircon Needs Cleaning</option>
                                    <option value="Cleaning Scheduled"> Cleaning Scheduled</option>
                                    <option value="Urgent Cleaning Scheduled"> Urgent Cleaning Scheduled</option>
                                    <option value="Trip Ticket Request">🎫 Trip Ticket Request</option>
                                    <option value="Trip Ticket Assignment">🚚 Trip Ticket Assignment</option>
                                </select>
                            </div>
                            <div class="filter-row">
                                <label for="priFilter"><i class="bi bi-flag"></i> Priority</label>
                                <select id="priFilter" onchange="filterTable()">
                                    <option value=""> All Priorities</option>
                                    <option value="CRITICAL"> Critical</option>
                                    <option value="MODERATE"> Moderate</option>
                                    <option value="ROUTINE"> Routine</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-scroll">
                <table class="notif-table" id="notifTable">
                    <thead>
                        <tr>
                            <th style="width:28px"></th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Recipient</th>
                            <th>Date &amp; Time</th>
                            <th>Priority</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($notifications)): ?>
                            <tr><td colspan="7" class="empty-row">
                                <i class="bi bi-bell-slash" style="font-size:2rem;color:var(--border);display:block;margin-bottom:.5rem"></i>
                                No notifications yet. They'll appear here as operations generate alerts.
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($notifications as $n): ?>
                            <?php
                                $status   = $n['status'] ?? 'Pending';
                                $isRead   = (bool) ($n['is_read'] ?? false);
                                $priority = strtoupper($n['priority'] ?? 'ROUTINE');
                                $priClass = match($priority) {
                                    'CRITICAL' => 'pri-critical',
                                    'MODERATE' => 'pri-moderate',
                                    default    => 'pri-routine',
                                };
                                $catIcon  = match($n['category'] ?? '') {
                                    'Vehicle Inspection'    => 'bi-truck',
                                    'Air-Con Cleaning'      => 'bi-wind',
                                    'Janitorial Assignment' => 'bi-brush',
                                    'Inventory Low Stock'   => 'bi-box-seam',
                                    'Vehicle Expiry'        => 'bi-card-checklist',
                                    'Fire Extinguisher Installed' => 'bi-fire',
                                    'Fire Extinguisher Expiring Soon' => 'bi-hourglass-split',
                                    'Aircon Unit Registered'      => 'bi-snow2',
                                    'Aircon Needs Cleaning'       => 'bi-snow2',
                                    'Cleaning Scheduled'          => 'bi-brush',
                                    'Urgent Cleaning Scheduled'   => 'bi-exclamation-triangle',
                                    'Trip Ticket Request'         => 'bi-ticket-perforated',
                                    'Trip Ticket Assignment'      => 'bi-truck',
                                    default                 => 'bi-bell',
                                };
                            ?>
                            <tr
                                class="notif-row <?= $isRead ? 'row-read' : 'row-unread' ?>"
                                data-id="<?= $n['id'] ?>"
                                data-cat="<?= strtolower(esc($n['category'] ?? '')) ?>"
                                data-pri="<?= esc($priority) ?>"
                                data-read="<?= $isRead ? 'read' : 'unread' ?>"
                                data-search="<?= strtolower(esc(($n['category'] ?? '') . ' ' . ($n['description'] ?? '') . ' ' . ($n['recipient'] ?? ''))) ?>"
                            >
                                <td>
                                    <span class="cat-dot <?= $priClass ?>">
                                        <i class="bi <?= $catIcon ?>"></i>
                                    </span>
                                </td>
                                <td>
                                    <div class="cat-label"><?= esc($n['category'] ?? '—') ?></div>
                                    <div class="cat-sub"><?= esc($n['recipient'] ?? '') ?></div>
                                </td>
                                <td class="desc-cell"><?= esc($n['description'] ?? '—') ?></td>
                                <td class="recipient-cell"><?= esc($n['recipient'] ?? '—') ?></td>
                                <td>
                                    <div class="datetime-cell">
                                        <span><?= date('M j, Y', strtotime($n['created_at'])) ?></span>
                                        <span class="time-muted"><?= date('h:i A', strtotime($n['created_at'])) ?></span>
                                    </div>
                                </td>
                                <td><span class="pri-badge <?= $priClass ?>"><?= $priority ?></span></td>
                                <td>
                                    <?php
                                    // "Verify" now always opens the detail popup first (category,
                                    // description — building/date/expiry/installer for Fire
                                    // Extinguisher & Aircon notifications — recipient, priority) so
                                    // it can be re-checked before approving, instead of the old
                                    // one-click "instantly Done" button. $approveAs is just which
                                    // status gets recorded once Approve is actually clicked.
                                    $approveAs = match($n['category'] ?? '') {
                                        'Vehicle Inspection'    => 'verified',
                                        'Air-Con Cleaning'      => 'assigned',
                                        'Janitorial Assignment' => 'assigned',
                                        'Inventory Low Stock'   => 'ordered',
                                        'Vehicle Expiry'        => 'reviewed',
                                        'Fire Extinguisher Installed'     => 'verified',
                                        'Fire Extinguisher Expiring Soon' => 'verified',
                                        'Aircon Unit Registered'          => 'verified',
                                        'Aircon Needs Cleaning'           => 'verified',
                                        'Cleaning Scheduled'          => 'assigned',
                                        'Urgent Cleaning Scheduled'   => 'assigned',
                                        default                 => 'acknowledged',
                                    };
                                    ?>
                                    <?php if ($status === 'Pending'): ?>
                                        <button class="action-btn"
                                            onclick="openNotifDetail(<?= $n['id'] ?>, '<?= $approveAs ?>', this)"
                                            data-category="<?= esc($n['category'] ?? '—', 'attr') ?>"
                                            data-description="<?= esc($n['description'] ?? '—', 'attr') ?>"
                                            data-recipient="<?= esc($n['recipient'] ?? '—', 'attr') ?>"
                                            data-date="<?= esc(date('M j, Y g:i A', strtotime($n['created_at'])), 'attr') ?>"
                                            data-priority="<?= esc($priority, 'attr') ?>">
                                            Verify
                                        </button>
                                    <?php else: ?>
                                        <span class="action-done"><i class="bi bi-check-circle-fill"></i> Done</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span id="entriesLabel">Showing 0 of 0 notifications</span>
                <div class="pagination" id="paginationButtons"></div>
            </div>
        </div>
    </div>
</div>

<!-- Notification detail popup — shown before approving, so an action never
     just silently marks "Done" without the reviewer seeing what it's for. -->
<div class="modal" id="notifDetailModal">
  <div class="modal-box">
    <h3><i class="bi bi-info-circle"></i> Notification Details</h3>
    <div class="notif-detail-row"><span>Category</span><strong id="ndCategory"></strong></div>
    <div class="notif-detail-row"><span>Details</span><strong id="ndDescription"></strong></div>
    <div class="notif-detail-row"><span>Recipient</span><strong id="ndRecipient"></strong></div>
    <div class="notif-detail-row"><span>Date &amp; Time</span><strong id="ndDate"></strong></div>
    <div class="notif-detail-row"><span>Priority</span><strong id="ndPriority"></strong></div>
    <div class="modal-actions">
      <button type="button" onclick="closeNotifDetail()">Close</button>
      <button type="button" class="btn-maroon" id="ndApproveBtn">Approve</button>
    </div>
  </div>
</div>

<script>
const MARK_READ_URL = '<?= base_url('notifications/markRead/') ?>';
const ACTION_URL    = '<?= base_url('notifications/action/') ?>';
const DISMISS_URL   = '<?= base_url('notifications/dismiss/') ?>';

// ── Filter table ───────────────────────────────────────────────
function filterTable() {
    const cat  = document.getElementById('catFilter').value.toLowerCase();
    const pri  = document.getElementById('priFilter').value;
    const read = document.getElementById('readFilter').value;
    const term = document.getElementById('searchInput').value.toLowerCase();

    document.querySelectorAll('#notifTable tbody .notif-row').forEach(row => {
        const matchCat  = !cat  || row.dataset.cat.includes(cat);
        const matchPri  = !pri  || row.dataset.pri === pri;
        const matchRead = !read || row.dataset.read === read;
        const matchTerm = !term || row.dataset.search.includes(term);
        row.classList.toggle('filtered-out', !(matchCat && matchPri && matchRead && matchTerm));
    });

    currentPage = 1;
    applyPagination();
}

// ── Pagination (client-side, over already-filtered rows) ─────────
let currentPage = 1;
const NOTIF_PAGE_SIZE = 10;

function applyPagination() {
    const rows = Array.from(document.querySelectorAll('#notifTable tbody .notif-row'))
        .filter(r => !r.classList.contains('filtered-out'));
    const total = rows.length;
    const totalPages = Math.max(1, Math.ceil(total / NOTIF_PAGE_SIZE));
    if (currentPage > totalPages) currentPage = totalPages;

    rows.forEach((row, i) => {
        const page = Math.floor(i / NOTIF_PAGE_SIZE) + 1;
        row.style.display = (page === currentPage) ? '' : 'none';
    });
    document.querySelectorAll('#notifTable tbody .notif-row.filtered-out').forEach(row => {
        row.style.display = 'none';
    });

    const start = total === 0 ? 0 : (currentPage - 1) * NOTIF_PAGE_SIZE + 1;
    const end   = Math.min(currentPage * NOTIF_PAGE_SIZE, total);
    document.getElementById('entriesLabel').textContent =
        total === 0 ? 'No notifications found' : `Showing ${start}–${end} of ${total} notifications`;

    renderPagination(totalPages);
}

function renderPagination(totalPages) {
    const wrap = document.getElementById('paginationButtons');
    wrap.innerHTML = '';

    const addBtn = (label, page, opts = {}) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'pg-btn' + (opts.active ? ' active' : '');
        b.textContent = label;
        b.disabled = !!opts.disabled;
        b.onclick = () => { currentPage = page; applyPagination(); };
        wrap.appendChild(b);
    };
    const addEllipsis = () => {
        const s = document.createElement('span');
        s.className = 'pg-ellipsis';
        s.textContent = '…';
        wrap.appendChild(s);
    };

    addBtn('«', Math.max(1, currentPage - 1), { disabled: currentPage === 1 });

    const maxButtons = 5;
    let start = Math.max(1, currentPage - 2);
    let end = Math.min(totalPages, start + maxButtons - 1);
    start = Math.max(1, end - maxButtons + 1);

    if (start > 1) { addBtn('1', 1); if (start > 2) addEllipsis(); }
    for (let p = start; p <= end; p++) addBtn(String(p), p, { active: p === currentPage });
    if (end < totalPages) { if (end < totalPages - 1) addEllipsis(); addBtn(String(totalPages), totalPages); }

    addBtn('Next »', Math.min(totalPages, currentPage + 1), { disabled: currentPage === totalPages });
}

applyPagination();

function toggleNotificationsFilterMenu() {
    const popup = document.getElementById('notificationsFilterPopup');
    popup.classList.toggle('visible');
}

window.addEventListener('click', function (e) {
    const wrapper = e.target.closest('.filter-menu-wrapper');
    const popup = document.getElementById('notificationsFilterPopup');
    if (!wrapper) popup.classList.remove('visible');
});

// ── Notification detail popup: Verify opens this first, Approve inside
// it is what actually calls doAction() — so nothing gets marked Done
// without the reviewer seeing what it's for.
let notifDetailCtx = null;

function openNotifDetail(id, action, btn) {
    notifDetailCtx = { id, action, btn };
    document.getElementById('ndCategory').textContent = btn.dataset.category || '—';
    document.getElementById('ndDescription').textContent = btn.dataset.description || '—';
    document.getElementById('ndRecipient').textContent = btn.dataset.recipient || '—';
    document.getElementById('ndDate').textContent = btn.dataset.date || '—';
    document.getElementById('ndPriority').textContent = btn.dataset.priority || '—';
    document.getElementById('notifDetailModal').style.display = 'flex';
}

function closeNotifDetail() {
    document.getElementById('notifDetailModal').style.display = 'none';
    notifDetailCtx = null;
}

document.getElementById('ndApproveBtn').addEventListener('click', () => {
    if (!notifDetailCtx) return;
    const { id, action, btn } = notifDetailCtx;
    closeNotifDetail();
    doAction(id, action, btn);
});

// ── Action button (Verify / Notify / Assign / Order …) ────────
function doAction(id, action, btn) {
    const originalLabel = btn.textContent;
    btn.disabled = true;
    btn.textContent = '…';

    const fd = new FormData();
    fd.append('action', action);

    fetch(ACTION_URL + id, { method: 'POST', headers: csrfHeaders(), body: fd })
        .then(r => {
            if (!r.ok) throw new Error('Request failed (' + r.status + ')');
            return r.json();
        })
        .then(res => {
            if (!res.success) throw new Error(res.message || 'Action was not confirmed by the server.');

            const row = btn.closest('tr');
            // Mark row read visually
            row.classList.remove('row-unread');
            row.classList.add('row-read');
            row.dataset.read = 'read';
            // Replace button with Done chip
            btn.outerHTML = '<span class="action-done"><i class="bi bi-check-circle-fill"></i> Done</span>';
            decrementUnread();
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = originalLabel;
            showToast('Could not complete that action. Please try again.', true);
        });
}

// ── Row click → mark read ──────────────────────────────────────
document.querySelectorAll('.notif-row.row-unread').forEach(row => {
    row.addEventListener('click', function (e) {
        if (e.target.closest('button, a')) return;
        const id = this.dataset.id;
        fetch(MARK_READ_URL + id, { method: 'POST', headers: csrfHeaders() })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    this.classList.remove('row-unread');
                    this.classList.add('row-read');
                    this.dataset.read = 'read';
                    decrementUnread();
                }
            });
    });
});

function decrementUnread() {
    const card = document.querySelector('.summary-card.unread .sc-value');
    if (card) {
        const n = Math.max(0, parseInt(card.textContent) - 1);
        card.textContent = n;
    }

    // Keep the topbar bell badge (shared across every page, not just this
    // one) in sync live — no full page reload needed to see it drop.
    const bellBadge = document.getElementById('topbarBellBadge');
    if (bellBadge) {
        const next = Math.max(0, (parseInt(bellBadge.textContent) || 0) - 1);
        if (next === 0) {
            bellBadge.remove();
        } else {
            bellBadge.textContent = next > 99 ? '99+' : next;
        }
    }
}

function generateWeeklySummary() { showToast('Weekly summary is being compiled…'); }

function showToast(msg, isError = false) {
    const t = document.createElement('div');
    t.className = 'notif-toast' + (isError ? ' toast-error' : '');
    t.innerHTML = `<i class="bi bi-${isError ? 'exclamation-triangle' : 'check-circle'}-fill"></i> ${msg}`;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('toast-show'), 10);
    setTimeout(() => { t.classList.remove('toast-show'); setTimeout(() => t.remove(), 400); }, 3500);
}

setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => el.style.opacity = '0');
}, 4000);
</script>

<?= $this->endSection() ?>