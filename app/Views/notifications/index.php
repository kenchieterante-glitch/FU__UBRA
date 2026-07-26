<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('Assets/css/notifications.css') ?>">

<div class="notif-wrapper">

    <!-- ── PAGE HEADER ──────────────────────────────────────────── -->
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-bell-fill"></i> Notification Center</h1>
            <p class="page-subtitle">View and manage all intelligent reminders, alerts, and operational notifications.</p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('notifications/markAllRead') ?>" class="btn-outline">
                <i class="bi bi-check2-all"></i> Mark All Read
            </a>
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
$critical_count = $critical_count ?? 0;
$email_count = $email_count ?? 0;
$flash_success = $flash_success ?? '';
?>

<?php if (!empty($flash_success)): ?>
        <div class="flash flash-success"><i class="bi bi-check-circle-fill"></i> <?= esc($flash_success) ?></div>
    <?php endif; ?>

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
                <div class="sc-sub success"><?= $today_count ?> Completed</div>
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
        <div class="summary-card critical">
            <div class="sc-icon"><i class="bi bi-exclamation-octagon-fill"></i></div>
            <div class="sc-body">
                <div class="sc-value"><?= $critical_count ?></div>
                <div class="sc-label">Critical Alerts</div>
                <div class="sc-sub danger">High Priority Actions</div>
            </div>
        </div>
        <div class="summary-card email">
            <div class="sc-icon"><i class="bi bi-send-fill"></i></div>
            <div class="sc-body">
                <div class="sc-value"><?= $email_count ?></div>
                <div class="sc-label">Recent Emails Sent</div>
                <div class="sc-sub success">API Delivery 100%</div>
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
                        <button type="button" class="search-icon-btn" onclick="toggleNotificationsSearch()" aria-label="Search alerts">
                            <i class="bi bi-search"></i>
                        </button>
                        <input type="text" id="searchInput" class="toolbar-search-input hidden" placeholder="Search alerts, recipients, or categories" oninput="filterTable()" style="display:none;">
                    </div>
                    <div class="filter-menu-wrapper">
                        <button type="button" class="filter-btn" onclick="toggleNotificationsFilterMenu()" aria-label="Open filters">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <div class="filter-popup" id="notificationsFilterPopup">
                            <div class="filter-popup-title">Filter</div>
                            <div class="filter-row">
                                <label for="catFilter"><i class="bi bi-tags"></i> Category</label>
                                <select id="catFilter" onchange="filterTable()">
                                    <option value=""> All Categories</option>
                                    <option value="Vehicle Inspection"> Vehicle Inspection</option>
                                    <option value="Travel Reminder"> Travel Reminder</option>
                                    <option value="Air-Con Cleaning"> Air-Con Cleaning</option>
                                    <option value="Janitorial Assignment"> Janitorial Assignment</option>
                                    <option value="Inventory Low Stock"> Inventory Low Stock</option>
                                    <option value="Vehicle Expiry">🗓 Vehicle Expiry</option>
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
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($notifications)): ?>
                            <tr><td colspan="8" class="empty-row">
                                <i class="bi bi-bell-slash" style="font-size:2rem;color:var(--border);display:block;margin-bottom:.5rem"></i>
                                No notifications yet. They'll appear here as operations generate alerts.
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($notifications as $n): ?>
                            <?php
                                $status   = $n['status'] ?? 'Unread';
                                $isRead   = $status !== 'Unread';
                                $priority = strtoupper($n['priority'] ?? 'ROUTINE');
                                $priClass = match($priority) {
                                    'CRITICAL' => 'pri-critical',
                                    'MODERATE' => 'pri-moderate',
                                    default    => 'pri-routine',
                                };
                                $catIcon  = match($n['category'] ?? '') {
                                    'Vehicle Inspection'    => 'bi-truck',
                                    'Travel Reminder'       => 'bi-airplane',
                                    'Air-Con Cleaning'      => 'bi-wind',
                                    'Janitorial Assignment' => 'bi-brush',
                                    'Inventory Low Stock'   => 'bi-box-seam',
                                    'Vehicle Expiry'        => 'bi-card-checklist',
                                    default                 => 'bi-bell',
                                };
                                $statusClass = match($status) {
                                    'Completed','Verified','Assigned','Resolved','Dismissed' => 'st-done',
                                    'Dismissed' => 'st-dismissed',
                                    default     => 'st-unread',
                                };
                            ?>
                            <tr
                                class="notif-row <?= $isRead ? 'row-read' : 'row-unread' ?>"
                                data-id="<?= $n['id'] ?>"
                                data-cat="<?= strtolower(esc($n['category'] ?? '')) ?>"
                                data-pri="<?= esc($priority) ?>"
                                data-search="<?= strtolower(esc(($n['category'] ?? '') . ' ' . ($n['description'] ?? '') . ' ' . ($n['recipient'] ?? ''))) ?>"
                            >
                                <td>
                                    <span class="cat-dot <?= $priClass ?>">
                                        <i class="bi <?= $catIcon ?>"></i>
                                    </span>
                                </td>
                                <td>
                                    <div class="cat-label <?= $isRead ? '' : 'bold' ?>"><?= esc($n['category'] ?? '—') ?></div>
                                    <div class="cat-sub"><?= esc($n['recipient'] ?? '') ?></div>
                                </td>
                                <td class="desc-cell"><?= esc($n['description'] ?? '—') ?></td>
                                <td><?= esc($n['recipient'] ?? '—') ?></td>
                                <td>
                                    <div class="datetime-cell">
                                        <span><?= date('M j, Y', strtotime($n['created_at'])) ?></span>
                                        <span class="time-muted"><?= date('h:i A', strtotime($n['created_at'])) ?></span>
                                    </div>
                                </td>
                                <td><span class="pri-badge <?= $priClass ?>"><?= $priority ?></span></td>
                                <td>
                                    <?php if ($isRead && in_array($status, ['Completed','Verified','Assigned','Resolved'])): ?>
                                        <span class="status-badge st-done"><?= esc($status) ?></span>
                                    <?php elseif ($status === 'Dismissed'): ?>
                                        <span class="status-badge st-dismissed">Dismissed</span>
                                    <?php else: ?>
                                        <span class="status-badge st-unread">• Unread</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $actionBtn = match($n['category'] ?? '') {
                                        'Vehicle Inspection'    => ['label' => 'Verify',  'value' => 'verified'],
                                        'Travel Reminder'       => ['label' => 'Notify',  'value' => 'notified'],
                                        'Air-Con Cleaning'      => ['label' => 'Assign',  'value' => 'assigned'],
                                        'Janitorial Assignment' => ['label' => 'Reassign','value' => 'assigned'],
                                        'Inventory Low Stock'   => ['label' => 'Order',   'value' => 'ordered'],
                                        'Vehicle Expiry'        => ['label' => 'Review',  'value' => 'reviewed'],
                                        default                 => ['label' => 'Read',    'value' => 'read'],
                                    };
                                    ?>
                                    <?php if (!in_array($status, ['Completed','Verified','Assigned','Resolved','Dismissed','Notified','Ordered','Reviewed','Read'])): ?>
                                        <button class="action-btn"
                                            onclick="doAction(<?= $n['id'] ?>, '<?= $actionBtn['value'] ?>', this)">
                                            <?= $actionBtn['label'] ?>
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

            <!-- Pagination stub -->
            <div class="table-footer">
                Showing 1–<?= min(count($notifications), 10) ?> of <?= count($notifications) ?> notifications
                <div class="pagination">
                    <button class="pg-btn active">1</button>
                    <?php if (count($notifications) > 10): ?>
                    <button class="pg-btn">2</button>
                    <button class="pg-btn">Next</button>
                    <?php endif; ?>
                </div>
            </div>
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
    const term = document.getElementById('searchInput').value.toLowerCase();

    document.querySelectorAll('#notifTable tbody .notif-row').forEach(row => {
        const matchCat  = !cat  || row.dataset.cat.includes(cat);
        const matchPri  = !pri  || row.dataset.pri === pri;
        const matchTerm = !term || row.dataset.search.includes(term);
        row.style.display = (matchCat && matchPri && matchTerm) ? '' : 'none';
    });
}

function toggleNotificationsSearch() {
    const input = document.getElementById('searchInput');
    const isHidden = input.classList.contains('hidden');

    if (isHidden) {
        input.classList.remove('hidden');
        input.style.display = '';
        input.focus();
    } else {
        input.classList.add('hidden');
        input.value = '';
        input.style.display = 'none';
        filterTable();
    }
}

function toggleNotificationsFilterMenu() {
    const popup = document.getElementById('notificationsFilterPopup');
    popup.classList.toggle('visible');
}

window.addEventListener('click', function (e) {
    const wrapper = e.target.closest('.filter-menu-wrapper');
    const popup = document.getElementById('notificationsFilterPopup');
    if (!wrapper) popup.classList.remove('visible');
});

// ── Action button (Verify / Notify / Assign / Order …) ────────
function doAction(id, action, btn) {
    btn.disabled = true;
    btn.textContent = '…';

    const fd = new FormData();
    fd.append('action', action);

    fetch(ACTION_URL + id, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const row = btn.closest('tr');
                // Mark row read visually
                row.classList.remove('row-unread');
                row.classList.add('row-read');
                // Replace button with Done chip
                btn.outerHTML = '<span class="action-done"><i class="bi bi-check-circle-fill"></i> Done</span>';
                // Update status badge
                const badge = row.querySelector('.status-badge');
                if (badge) {
                    badge.textContent = res.new_status;
                    badge.className   = 'status-badge st-done';
                }
                decrementUnread();
            }
        })
        .catch(() => { btn.disabled = false; btn.textContent = 'Retry'; });
}

// ── Row click → mark read ──────────────────────────────────────
document.querySelectorAll('.notif-row.row-unread').forEach(row => {
    row.addEventListener('click', function (e) {
        if (e.target.closest('button, a')) return;
        const id = this.dataset.id;
        fetch(MARK_READ_URL + id, { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    this.classList.remove('row-unread');
                    this.classList.add('row-read');
                    const badge = this.querySelector('.status-badge.st-unread');
                    if (badge) badge.remove();
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
}

function generateWeeklySummary() { showToast('Weekly summary is being compiled…'); }

function showToast(msg) {
    const t = document.createElement('div');
    t.className = 'notif-toast';
    t.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${msg}`;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('toast-show'), 10);
    setTimeout(() => { t.classList.remove('toast-show'); setTimeout(() => t.remove(), 400); }, 3500);
}

setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => el.style.opacity = '0');
}, 4000);
</script>

<?= $this->endSection() ?>