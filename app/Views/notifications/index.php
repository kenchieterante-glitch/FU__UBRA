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
            <button type="button" class="btn-outline" onclick="openComposeModal()">
                <i class="bi bi-pencil-square"></i> New Message
            </button>
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
    <div class="stat-cards" id="notifStatCards">
        <div class="stat-card unread stat-card-clickable" onclick="filterNotifByStat('unread')" role="button" tabindex="0">
            <span class="stat-icon tone-red"><i class="fa-solid fa-bell"></i></span>
            <h3>Unread Notifications</h3>
            <div class="value"><?= $unread_count ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="filterNotifByStat('today')" role="button" tabindex="0">
            <span class="stat-icon tone-gold"><i class="fa-solid fa-calendar-day"></i></span>
            <h3>Today's Alerts</h3>
            <div class="value"><?= $today_count ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="filterNotifByStat('upcoming')" role="button" tabindex="0">
            <span class="stat-icon tone-neutral"><i class="fa-solid fa-hourglass-half"></i></span>
            <h3>Upcoming Schedules</h3>
            <div class="value"><?= $upcoming_count ?></div>
        </div>
        <div class="stat-card draft stat-card-clickable" onclick="filterNotifByStat('draft')" role="button" tabindex="0">
            <span class="stat-icon tone-maroon"><i class="fa-solid fa-pen-to-square"></i></span>
            <h3>Draft Messages</h3>
            <div class="value"><?= $draft_count ?></div>
        </div>
    </div>

    <div class="stat-back-bar" id="notifBackBar" style="display:none">
        <button type="button" class="stat-back-btn" onclick="resetNotifOverview()"><i class="bi bi-arrow-left"></i> Back to Overview</button>
        <h2 class="stat-list-title" id="notifBackLabel"></h2>
    </div>

    <!-- ── BODY: LOG TABLE + RIGHT SIDEBAR ──────────────────────── -->
    <div class="notif-body">

        <!-- Notification log -->
        <div class="table-panel">
            <div class="table-toolbar">
                <h2 class="panel-title">Operational Alerts Log</h2>
                <div class="toolbar-right">
                    <div class="toolbar-search">
                      <input type="text" id="searchInput" class="search-box" placeholder="Search alerts…" title="Search by alert, recipient, or category" oninput="filterTable()">
                      <i class="bi bi-search search-icon"></i>
                    </div>
                    <div class="filter-menu-wrapper">
                        <button type="button" class="filter-btn" onclick="toggleNotificationsFilterMenu()" aria-label="Open filters">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <div class="filter-popup" id="notificationsFilterPopup">
                            <div class="filter-popup-title">Filter</div>
                            <div class="filter-row">
                                <label for="readFilter">View</label>
                                <select id="readFilter" onchange="filterTable()">
                                    <option value="">All Notifications</option>
                                    <option value="unread">Unread Only</option>
                                    <option value="read">Read Only</option>
                                </select>
                            </div>
                            <div class="filter-row">
                                <label for="catFilter">Category</label>
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
                                <label for="priFilter">Priority</label>
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
                                // Where clicking the row should take you — the module that actually
                                // owns this notification's underlying record.
                                $catRoute = match($n['category'] ?? '') {
                                    'Vehicle Inspection', 'Vehicle Expiry'                     => 'vehicles',
                                    'Inventory Low Stock'                                      => 'tools/consumable',
                                    'Janitorial Assignment', 'Cleaning Scheduled',
                                    'Urgent Cleaning Scheduled'                                => 'janitorial',
                                    'Air-Con Cleaning', 'Fire Extinguisher Installed',
                                    'Fire Extinguisher Expiring Soon', 'Aircon Unit Registered',
                                    'Aircon Needs Cleaning', 'Maintenance Scheduled'            => 'safety',
                                    'Trip Ticket Request', 'Trip Ticket Assignment'             => 'travel',
                                    default                                                     => null,
                                };
                                // Matches NotificationController::index()'s $today_count definition exactly.
                                $isToday = substr($n['created_at'] ?? '', 0, 10) === date('Y-m-d');
                                $isDraft = ($n['_kind'] ?? 'live') === 'draft';
                            ?>
                            <tr
                                class="notif-row <?= $isRead ? 'row-read' : 'row-unread' ?><?= ($catRoute && !$isDraft) ? ' row-clickable' : '' ?>"
                                data-id="<?= $n['id'] ?>"
                                data-cat="<?= strtolower(esc($n['category'] ?? '')) ?>"
                                data-pri="<?= esc($priority) ?>"
                                data-read="<?= $isRead ? 'read' : 'unread' ?>"
                                data-today="<?= $isToday ? '1' : '0' ?>"
                                data-status="<?= $isDraft ? 'draft' : 'live' ?>"
                                data-search="<?= strtolower(esc(($n['category'] ?? '') . ' ' . ($n['description'] ?? '') . ' ' . ($n['recipient'] ?? ''))) ?>"
                                <?php if ($catRoute && !$isDraft): ?>data-route="<?= base_url($catRoute) ?>" title="Go to <?= esc($n['category'] ?? 'related module') ?>"<?php endif; ?>
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
                                    <?php if ($isDraft): ?>
                                        <div class="action-buttons">
                                            <button type="button" class="action-btn" onclick="sendDraftMsg(<?= $n['id'] ?>, this)">
                                                <i class="bi bi-send"></i> Send
                                            </button>
                                            <button type="button" class="icon-btn delete" onclick="deleteDraftMsg(<?= $n['id'] ?>, this)" title="Delete draft">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    <?php elseif ($status === 'Pending'): ?>
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
  <div class="modal-box notif-detail-box">
    <div class="notif-detail-header">
      <span class="notif-detail-icon"><i class="bi bi-info-circle"></i></span>
      <h3>Notification Details</h3>
    </div>
    <div class="notif-detail-row"><span>Category</span><strong id="ndCategory"></strong></div>
    <div class="notif-detail-row"><span>Details</span><strong id="ndDescription"></strong></div>
    <div class="notif-detail-row"><span>Recipient</span><strong id="ndRecipient"></strong></div>
    <div class="notif-detail-row"><span>Date &amp; Time</span><strong id="ndDate"></strong></div>
    <div class="notif-detail-row"><span>Priority</span><strong id="ndPriority"></strong></div>
    <div class="modal-actions">
      <button type="button" class="btn-secondary" onclick="closeNotifDetail()">Close</button>
      <button type="button" class="btn-approve" id="ndApproveBtn"><i class="bi bi-check-lg"></i> Approve</button>
    </div>
  </div>
</div>

<!-- Compose new message — saved as a Draft until explicitly sent from the
     Draft Messages filtered view. -->
<div class="modal" id="composeModal">
  <div class="modal-box notif-detail-box">
    <div class="notif-detail-header">
      <span class="notif-detail-icon"><i class="bi bi-pencil-square"></i></span>
      <h3>New Message</h3>
    </div>
    <form id="composeForm">
      <label>Label</label>
      <input type="text" id="cmCategory" placeholder="e.g. General Announcement" maxlength="80">
      <label>Message <span class="required-mark">*</span></label>
      <textarea id="cmDescription" rows="4" required></textarea>
      <label>Recipient</label>
      <select id="cmRecipient">
        <option value="Operations Team">Operations Team</option>
        <?php if (!empty($supervisors)): ?>
          <optgroup label="Co-Supervisors">
            <?php foreach ($supervisors as $s): ?>
              <option value="<?= esc($s['name'], 'attr') ?>"><?= esc($s['name']) ?> — <?= esc($s['position']) ?></option>
            <?php endforeach; ?>
          </optgroup>
        <?php endif; ?>
        <?php if (!empty($fireInspectors)): ?>
          <optgroup label="Fire Extinguisher Inspectors">
            <?php foreach ($fireInspectors as $f): ?>
              <option value="<?= esc($f['name'], 'attr') ?>"><?= esc($f['name']) ?> — <?= esc($f['unit_id']) ?> (<?= esc($f['location']) ?>)</option>
            <?php endforeach; ?>
          </optgroup>
        <?php endif; ?>
      </select>
      <label>Priority</label>
      <select id="cmPriority">
        <option value="ROUTINE">Routine</option>
        <option value="MODERATE">Moderate</option>
        <option value="CRITICAL">Critical</option>
      </select>
      <div class="modal-actions">
        <button type="button" class="btn-secondary" onclick="closeComposeModal()">Cancel</button>
        <button type="submit" class="btn-approve"><i class="bi bi-save2"></i> Save Draft</button>
      </div>
    </form>
  </div>
</div>

<script>
const MARK_READ_URL   = '<?= base_url('notifications/markRead/') ?>';
const ACTION_URL      = '<?= base_url('notifications/action/') ?>';
const DISMISS_URL     = '<?= base_url('notifications/dismiss/') ?>';
const SAVE_DRAFT_URL  = '<?= base_url('notifications/saveDraft') ?>';
const SEND_DRAFT_URL  = '<?= base_url('notifications/sendDraft/') ?>';
const DELETE_DRAFT_URL = '<?= base_url('notifications/deleteDraft/') ?>';

// ── Row click → jump to the module the notification is actually about.
// Clicking Verify (or anything else interactive in the row) must NOT also
// trigger this, so it only fires when the click didn't land on a button.
document.querySelectorAll('#notifTable tbody .notif-row.row-clickable').forEach(row => {
    row.addEventListener('click', (e) => {
        if (e.target.closest('button, a')) return;
        if (row.dataset.route) window.location.href = row.dataset.route;
    });
});

// ── Filter table ───────────────────────────────────────────────
let statTodayOnly = false;
let statDraftOnly = false;

function filterTable() {
    const cat  = document.getElementById('catFilter').value.toLowerCase();
    const pri  = document.getElementById('priFilter').value;
    const read = document.getElementById('readFilter').value;
    const term = document.getElementById('searchInput').value.toLowerCase();

    document.querySelectorAll('#notifTable tbody .notif-row').forEach(row => {
        const matchStatus = statDraftOnly
            ? row.dataset.status === 'draft'
            : row.dataset.status !== 'draft';
        const matchCat   = !cat  || row.dataset.cat.includes(cat);
        const matchPri   = !pri  || row.dataset.pri === pri;
        const matchRead  = !read || row.dataset.read === read;
        const matchToday = !statTodayOnly || row.dataset.today === '1';
        const matchTerm  = !term || row.dataset.search.includes(term);
        row.classList.toggle('filtered-out', !(matchStatus && matchCat && matchPri && matchRead && matchToday && matchTerm));
    });

    currentPage = 1;
    applyPagination();
}

// ── Summary cards → filter shortcuts ─────────────────────────────
const notifStatLabels = {
    unread:   'Unread Notifications',
    today:    "Today's Alerts",
    upcoming: 'Upcoming Schedules',
    draft:    'Draft Messages',
};

function filterNotifByStat(kind) {
    document.getElementById('searchInput').value = '';
    document.getElementById('catFilter').value = '';
    document.getElementById('priFilter').value = '';
    document.getElementById('readFilter').value = '';
    statTodayOnly = false;
    statDraftOnly = false;

    if (kind === 'unread')   document.getElementById('readFilter').value = 'unread';
    if (kind === 'upcoming') document.getElementById('priFilter').value = 'ROUTINE';
    if (kind === 'today')    statTodayOnly = true;
    if (kind === 'draft')    statDraftOnly = true;

    document.getElementById('notifStatCards')?.style.setProperty('display', 'none');
    document.getElementById('notifBackBar').style.display = 'flex';
    document.getElementById('notifBackLabel').textContent = notifStatLabels[kind] ?? 'Filtered';

    filterTable();
    document.querySelector('.table-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetNotifOverview() {
    document.getElementById('notifStatCards')?.style.setProperty('display', '');
    document.getElementById('notifBackBar').style.display = 'none';
    document.getElementById('searchInput').value = '';
    document.getElementById('catFilter').value = '';
    document.getElementById('priFilter').value = '';
    document.getElementById('readFilter').value = '';
    statTodayOnly = false;
    statDraftOnly = false;
    filterTable();
    document.getElementById('notifStatCards')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

document.querySelectorAll('.stat-card-clickable').forEach(card => {
    card.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            card.click();
        }
    });
});

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

filterTable();

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
    const card = document.querySelector('.stat-card.unread .value');
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

// ── Compose / Draft Messages ────────────────────────────────────
function openComposeModal() {
    document.getElementById('composeForm').reset();
    document.getElementById('composeModal').style.display = 'flex';
}

function closeComposeModal() {
    document.getElementById('composeModal').style.display = 'none';
}

document.getElementById('composeForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const description = document.getElementById('cmDescription').value.trim();
    if (!description) {
        showToast('Please enter a message.', true);
        return;
    }
    const fd = new FormData();
    fd.append('category', document.getElementById('cmCategory').value.trim());
    fd.append('description', description);
    fd.append('recipient', document.getElementById('cmRecipient').value.trim());
    fd.append('priority', document.getElementById('cmPriority').value);

    fetch(SAVE_DRAFT_URL, { method: 'POST', headers: csrfHeaders(), body: fd })
        .then(r => r.json())
        .then(res => {
            if (!res.success) throw new Error(res.message || 'Could not save draft.');
            closeComposeModal();
            showToast('Draft saved.');
            setTimeout(() => window.location.reload(), 500);
        })
        .catch(err => showToast(err.message || 'Could not save that draft.', true));
});

function sendDraftMsg(id, btn) {
    if (!confirm('Send this message now? It will appear as a new notification.')) return;
    btn.disabled = true;
    fetch(SEND_DRAFT_URL + id, { method: 'POST', headers: csrfHeaders() })
        .then(r => r.json())
        .then(res => {
            if (!res.success) throw new Error(res.message || 'Could not send that message.');
            showToast('Message sent.');
            setTimeout(() => window.location.reload(), 500);
        })
        .catch(err => {
            btn.disabled = false;
            showToast(err.message || 'Could not send that message.', true);
        });
}

function deleteDraftMsg(id, btn) {
    if (!confirm('Delete this draft? This cannot be undone.')) return;
    btn.disabled = true;
    fetch(DELETE_DRAFT_URL + id, { method: 'POST', headers: csrfHeaders() })
        .then(r => r.json())
        .then(res => {
            if (!res.success) throw new Error(res.message || 'Could not delete that draft.');
            const row = btn.closest('tr');
            row.remove();
            const card = document.querySelector('.stat-card.draft .value');
            if (card) card.textContent = Math.max(0, parseInt(card.textContent) - 1);
            applyPagination();
        })
        .catch(err => {
            btn.disabled = false;
            showToast(err.message || 'Could not delete that draft.', true);
        });
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