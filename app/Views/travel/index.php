<?php
/**
 * @var string $title
 * @var array<int, array<string, mixed>> $trips
 * @var array<int, array<string, mixed>> $personnel
 * @var array<int, array<string, mixed>> $drivers
 * @var array<int, array<string, mixed>> $vehicles
 * @var array<int, array<string, mixed>> $departments
 */
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('Assets/css/travel.css') . '?v=' . @filemtime(FCPATH.'Assets/css/travel.css') ?>">

<div class="travel-wrapper">

    <!-- ── PAGE HEADER ─────────────────────────────────────────── -->
    <div class="page-header compact-header">
        <div>
            <h1 class="page-title">Driver's Trip Ticket</h1>
            <p class="page-subtitle">Monitor trip ticket requests and their current status.</p>
        </div>
        <div class="header-actions">
            <button class="btn-primary" onclick="document.getElementById('addTripModal').style.display='flex'">
                <i class="bi bi-plus-lg"></i> Add Trip Ticket
            </button>
        </div>
    </div>

    <!-- ── CATEGORY TABS ────────────────────────────────────────── -->
    <div class="stat-cards" id="tripTabs">
        <div class="stat-card stat-card-clickable" data-tab="requests" onclick="switchTripTab('requests')" role="button" tabindex="0">
            <span class="stat-icon tone-maroon"><i class="fa-solid fa-inbox"></i></span>
            <h3>Trip requests</h3>
            <div class="value"><?= count($trips ?? []) ?></div>
        </div>
        <div class="stat-card stat-card-clickable" data-tab="pending" onclick="switchTripTab('pending')" role="button" tabindex="0">
            <span class="stat-icon tone-gold"><i class="fa-solid fa-hourglass-half"></i></span>
            <h3>Pending trips</h3>
            <div class="value"><?= $pending_count ?? 0 ?></div>
        </div>
        <div class="stat-card stat-card-clickable" data-tab="today" onclick="switchTripTab('today')" role="button" tabindex="0">
            <span class="stat-icon tone-blue"><i class="fa-solid fa-calendar-day"></i></span>
            <h3>Today's trips</h3>
            <div class="value"><?= $today_count ?? 0 ?></div>
        </div>
        <div class="stat-card stat-card-clickable" data-tab="completed" onclick="switchTripTab('completed')" role="button" tabindex="0">
            <span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span>
            <h3>Completed trips</h3>
            <div class="value"><?= $completed_count ?? 0 ?></div>
        </div>
    </div>

    <!-- ── MAIN CONTENT: TABLE ─────────────────────────────────── -->
    <div class="travel-body">

        <div class="table-panel">
            <div class="table-toolbar">
                <div class="toolbar-left">
                    <div class="toolbar-search">
                      <input type="text" id="searchInput" class="search-box" placeholder="Search trips…" title="Search by requester or destination" oninput="filterTable()">
                      <i class="bi bi-search search-icon"></i>
                    </div>
                </div>
            </div>

            <div class="table-scroll">
                <table class="travel-table" id="travelTable">
                    <thead>
                        <tr>
                            <th>Trip ID</th>
                            <th>Requester</th>
                            <th>Destination</th>
                            <th>Purpose</th>
                            <th>Date &amp; Time</th>
                            <th>Assigned Staff</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($trips)): ?>
                            <tr><td colspan="8" class="empty-row">No trip tickets found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($trips as $trip): ?>
                            <?php
                                // Requests always shows everything; the other three tabs are
                                // each a specific, non-overlapping slice of it — a trip that's
                                // Completed is never also "pending" or "today's", even if its
                                // travel date is today.
                                $category = 'other';
                                if ($trip['status'] === 'Completed') {
                                    $category = 'completed';
                                } elseif ($trip['travel_date'] === date('Y-m-d') && !in_array($trip['status'], ['Rejected', 'Cancelled'], true)) {
                                    $category = 'today';
                                } elseif (in_array($trip['status'], ['Submitted', 'Reviewed'], true)) {
                                    $category = 'pending';
                                }
                            ?>
                            <tr data-status="<?= esc($trip['status']) ?>" data-category="<?= $category ?>" data-requester="<?= strtolower(esc($trip['requester_name'] ?? '')) ?>">
                                <td class="trip-id"><?= esc($trip['trip_id']) ?></td>
                                <td>
                                    <div class="requester-cell">
                                        <span class="req-name"><?= esc($trip['requester_name'] ?? 'Unknown') ?></span>
                                        <span class="req-dept"><?= esc($trip['department_name'] ?? '') ?></span>
                                    </div>
                                </td>
                                <td class="destination-cell"><?= esc($trip['destination']) ?></td>
                                <td class="purpose-cell"><?= esc($trip['purpose']) ?></td>
                                <td>
                                    <div class="datetime-cell">
                                        <span><?= date('M d, Y', strtotime($trip['travel_date'])) ?></span>
                                        <span class="time-range"><?= date('h:i A', strtotime($trip['departure_time'])) ?> – <?= date('h:i A', strtotime($trip['return_time'])) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="staff-cell">
                                        <?php if (!empty($trip['driver_name'])): ?>
                                            <span class="staff-badge driver"><i class="bi bi-person-fill"></i> <?= esc($trip['driver_name']) ?> (Driver)</span>
                                        <?php else: ?>
                                            <span class="staff-badge unassigned"><i class="bi bi-person-dash"></i> Driver Pending</span>
                                        <?php endif; ?>
                                        <?php if (!empty($trip['plate_no'])): ?>
                                            <span class="staff-badge vehicle"><i class="bi bi-truck"></i> <?= esc($trip['vehicle_name']) ?> <?= esc($trip['plate_no']) ?></span>
                                        <?php else: ?>
                                            <span class="staff-badge unassigned"><i class="bi bi-truck"></i> Vehicle Pending</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match($trip['status']) {
                                        'Reviewed'   => 'badge-reviewed',
                                        'Approved'   => 'badge-approved',
                                        'In Transit' => 'badge-in-transit',
                                        'Completed'  => 'badge-completed',
                                        'Cancelled', 'Rejected' => 'badge-cancelled',
                                        default      => 'badge-pending',
                                    };
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>"><?= esc($trip['status']) ?></span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button class="icon-btn ticket" title="View Trip Ticket"
                                            onclick="viewTicket(<?= $trip['id'] ?>)">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        <form method="post" action="<?= base_url('travel/delete/'.$trip['id']) ?>" onsubmit="return confirm('Archive this trip ticket?')" style="display:contents;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="icon-btn" style="background:#f3f4f6;color:#6b7280;" title="Archive"><i class="fa-solid fa-archive"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     MODAL: ADD TRIP TICKET
════════════════════════════════════════════════════════════════ -->
<div id="addTripModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="bi bi-ticket-perforated"></i> New Trip Ticket</h3>
            <button class="modal-close" onclick="document.getElementById('addTripModal').style.display='none'"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="post" action="<?= base_url('travel/add') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Requester <span class="req">*</span></label>
                        <select name="requester_id" required>
                            <option value="">— Select Requester —</option>
                            <?php foreach ($personnel as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= esc($p['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Travel Date <span class="req">*</span></label>
                        <input type="date" name="travel_date" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Destination <span class="req">*</span></label>
                    <input type="text" name="destination" required>
                </div>
                <div class="form-group">
                    <label>Purpose <span class="req">*</span></label>
                    <textarea name="purpose" rows="2" required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Departure Time <span class="req">*</span></label>
                        <input type="time" name="departure_time" required>
                    </div>
                    <div class="form-group">
                        <label>Return Time <span class="req">*</span></label>
                        <input type="time" name="return_time" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Assign Driver <span class="optional">(optional)</span></label>
                        <select name="assigned_driver_id">
                            <option value="">— Select Driver —</option>
                            <?php foreach ($drivers as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= esc($d['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assign Vehicle <span class="optional">(optional)</span></label>
                        <select name="assigned_vehicle_id">
                            <option value="">— Select Vehicle —</option>
                            <?php foreach ($vehicles as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= esc($v['vehicle_name']) ?> — <?= esc($v['plate_no']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('addTripModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-submit"><i class="bi bi-check-lg"></i> Create Trip Ticket</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     MODAL: DIGITAL TRIP TICKET
════════════════════════════════════════════════════════════════ -->
<div id="ticketModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-ticket">
        <div class="modal-header">
            <div>
                <h3><i class="bi bi-ticket-perforated"></i> Digital Trip Ticket</h3>
                <span class="modal-sub">Paperless vehicle travel authorization • UBRA</span>
            </div>
            <div class="ticket-header-right">
                <button class="btn-outline-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
                <button class="modal-close" onclick="closeTicketModal()"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
        <div class="modal-body" id="ticketBody">
            <div class="ticket-loading"><i class="bi bi-hourglass-split"></i> Loading trip details...</div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     MODAL: TICKET SENT TO DRIVER (pop verification)
════════════════════════════════════════════════════════════════ -->
<?php $ticketSent = session()->getFlashdata('ticket_sent'); ?>
<?php if (!empty($ticketSent)): ?>
<div id="ticketSentModal" class="modal-overlay" style="display:flex;">
    <div class="modal-box modal-sm" style="text-align:center;">
        <div class="modal-body">
            <div style="font-size:2.6rem;color:var(--success);margin-bottom:.5rem;"><i class="bi bi-send-check-fill"></i></div>
            <h3 style="justify-content:center;">Trip Ticket Sent</h3>
            <p class="approve-info">
                <strong><?= esc($ticketSent['trip_id']) ?></strong> for <?= esc($ticketSent['destination']) ?>
                has been sent to <strong><?= esc($ticketSent['driver']) ?></strong>.
            </p>
            <p class="ts-sub">They'll see it under their assigned trips and can report to the gate for dispatch clearance.</p>
        </div>
        <div class="modal-footer" style="justify-content:center;">
            <button type="button" class="btn-submit" onclick="document.getElementById('ticketSentModal').style.display='none'"><i class="bi bi-check-lg"></i> Got it</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════════════════════════ -->
<script>
// ── Modal helpers ──────────────────────────────────────────────
function closeTicketModal(){ document.getElementById('ticketModal').style.display = 'none'; }

// ── View ticket (AJAX) ─────────────────────────────────────────
function viewTicket(id) {
    document.getElementById('ticketModal').style.display = 'flex';
    document.getElementById('ticketBody').innerHTML =
        '<div class="ticket-loading"><i class="bi bi-hourglass-split"></i> Loading trip details...</div>';

    fetch('<?= base_url('travel/getTrip/') ?>' + id)
        .then(r => r.json())
        .then(t => {
            const statusClass = {
                Submitted: 'badge-pending', Reviewed: 'badge-reviewed', Approved: 'badge-approved',
                'In Transit': 'badge-in-transit', Completed: 'badge-completed',
                Cancelled: 'badge-cancelled', Rejected: 'badge-cancelled'
            }[t.status] || 'badge-pending';

            // The gate status only turns green once the guard has actually
            // scanned the driver back in (check_out_time set) — dispatched-
            // but-not-returned and not-yet-dispatched are distinct, non-green states.
            let verifyTone, verifyIcon, verifyText;
            if (t.check_out_time) {
                verifyTone = 'success'; verifyIcon = 'bi-check-circle-fill'; verifyText = 'Trip Completed';
            } else if (t.check_in_time) {
                verifyTone = 'info'; verifyIcon = 'bi-truck'; verifyText = 'Dispatched — Awaiting Return';
            } else {
                verifyTone = 'pending'; verifyIcon = 'bi-hourglass-split'; verifyText = 'Verification Pending';
            }

            document.getElementById('ticketBody').innerHTML = `
            <div class="ticket-layout">
                <div class="ticket-left">
                    <div class="ticket-section-title">Trip Information</div>
                    <div class="ticket-fields">
                        <div class="tf"><span>Trip Number</span><strong>${t.trip_id || '—'}</strong></div>
                        <div class="tf"><span>Request Date</span><strong>${formatDate(t.created_at)}</strong></div>
                        <div class="tf"><span>Travel Date</span><strong>${formatDate(t.travel_date)}</strong></div>
                        <div class="tf"><span>Department</span><strong>${t.department_name || '—'}</strong></div>
                        <div class="tf"><span>Departure Time</span><strong>${formatTime(t.departure_time)}</strong></div>
                        <div class="tf"><span>Expected Return</span><strong>${formatTime(t.return_time)}</strong></div>
                        <div class="tf"><span>Assigned Driver</span><strong>${t.driver_name || 'Not Assigned'}</strong></div>
                        <div class="tf"><span>Vehicle Profile</span><strong>${t.vehicle_name ? t.vehicle_name + ' (' + t.plate_no + ')' : 'Not Assigned'}</strong></div>
                        <div class="tf full"><span>Destination</span><strong>${t.destination}</strong></div>
                        <div class="tf full"><span>Travel Purpose</span><strong>${t.purpose}</strong></div>
                        <div class="tf full"><span>Requested By</span><strong>${t.requester_name || '—'}</strong></div>
                    </div>
                </div>
                <div class="ticket-right">
                    <div class="ticket-status-panel">
                        <div class="ts-label">Security Verification</div>
                        <div class="status-badge ${statusClass}" style="margin:0.5rem auto;display:block;width:fit-content;">${t.status}</div>
                        <div class="ts-sub">Trip ID: ${t.trip_id || '—'}</div>
                        <div class="ts-verify ${verifyTone}"><i class="bi ${verifyIcon}"></i> ${verifyText}</div>
                    </div>
                    <div class="ticket-action-row" style="margin-top:1rem;">
                        <button class="btn-submit" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
                        <button class="btn-cancel" onclick="closeTicketModal()"><i class="bi bi-x"></i> Close</button>
                    </div>
                </div>
            </div>`;
        })
        .catch(() => {
            document.getElementById('ticketBody').innerHTML =
                '<div class="ticket-loading error"><i class="bi bi-exclamation-triangle"></i> Failed to load trip details.</div>';
        });
}

// ── Category tabs ─────────────────────────────────────────────
let activeTripTab = 'requests';

function switchTripTab(tab) {
    activeTripTab = tab;
    filterTable();
}

document.querySelectorAll('#tripTabs .stat-card-clickable').forEach(card => {
    card.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            card.click();
        }
    });
});

// ── Table filter ───────────────────────────────────────────────
function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#travelTable tbody tr[data-category]');

    rows.forEach(row => {
        const rowCategory = row.dataset.category || '';
        const rowText = (row.textContent || '').toLowerCase();

        const matchTab = activeTripTab === 'requests' || rowCategory === activeTripTab;
        const matchSearch = !search || rowText.includes(search);
        row.style.display = matchTab && matchSearch ? '' : 'none';
    });
}

// ── Date / Time formatters ─────────────────────────────────────
function formatDate(d) {
    if (!d) return '—';
    const dt = new Date(d);
    return isNaN(dt) ? d : dt.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
}
function formatTime(t) {
    if (!t) return '—';
    const [h, m] = t.split(':');
    const hour = parseInt(h);
    return (hour % 12 || 12) + ':' + m + ' ' + (hour >= 12 ? 'PM' : 'AM');
}
// ── Close modals on overlay click ─────────────────────────────
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) overlay.style.display = 'none';
    });
});

setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => el.style.opacity = '0');
}, 4000);
</script>

<?= $this->endSection() ?>
