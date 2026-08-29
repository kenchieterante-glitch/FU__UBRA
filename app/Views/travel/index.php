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
            <p class="page-subtitle">Manage trip tickets, travel authorizations, and driver assignments.</p>
        </div>
        <div class="header-actions">
            <button class="btn-primary" onclick="document.getElementById('addTripModal').style.display='flex'">
                <i class="bi bi-plus-lg"></i> Add Trip Ticket
            </button>
        </div>
    </div>

    <!-- ── SUMMARY CARDS ───────────────────────────────────────── -->
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" onclick="filterTripsByStat('Submitted')" role="button" tabindex="0">
            <span class="stat-icon tone-gold"><i class="fa-solid fa-hourglass-half"></i></span>
            <h3>Submitted Requests</h3>
            <div class="value"><?= $submitted_count ?? 0 ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="filterTripsByStat('Approved')" role="button" tabindex="0">
            <span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span>
            <h3>Approved Trips</h3>
            <div class="value"><?= $approved_count ?? 0 ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleTripsInProgress()" role="button" tabindex="0">
            <span class="stat-icon tone-blue"><i class="fa-solid fa-truck-fast"></i></span>
            <h3>Trips In Progress</h3>
            <div class="value"><?= $in_transit_count ?? 0 ?></div>
        </div>
        <div class="stat-card">
            <span class="stat-icon tone-maroon"><i class="fa-solid fa-calendar-day"></i></span>
            <h3>Today's Trips</h3>
            <div class="value"><?= $today_count ?? 0 ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="filterTripsByStat('Completed')" role="button" tabindex="0">
            <span class="stat-icon tone-neutral"><i class="fa-solid fa-flag-checkered"></i></span>
            <h3>Completed Trips</h3>
            <div class="value"><?= $completed_count ?? 0 ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="filterTripsByStat('Rejected')" role="button" tabindex="0">
            <span class="stat-icon tone-red"><i class="fa-solid fa-circle-xmark"></i></span>
            <h3>Rejected / Cancelled</h3>
            <div class="value"><?= $cancelled_count ?? 0 ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleVehicleMonitoring()" role="button" tabindex="0">
            <span class="stat-icon tone-neutral"><i class="fa-solid fa-truck"></i></span>
            <h3>Available Vehicles</h3>
            <div class="value"><?= $available_vehicles ?? 0 ?>/<?= $total_vehicles ?? count($vehicles ?? []) ?></div>
        </div>
    </div>

    <!-- ── TRIPS IN PROGRESS ("Active Monitoring") ─────────────── -->
    <div class="table-panel" id="tripsInProgressPanel" style="display:none;">
        <div class="table-toolbar"><h3 style="margin:0;"><i class="bi bi-truck"></i> Trips In Progress</h3></div>
        <div class="table-scroll">
            <table class="travel-table">
                <thead>
                    <tr><th>Trip ID</th><th>Destination</th><th>Driver</th><th>Vehicle</th><th>Expected Return</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($trips_in_progress)): ?>
                        <tr><td colspan="5" class="empty-row">No trips currently in progress.</td></tr>
                    <?php else: ?>
                        <?php foreach ($trips_in_progress as $tip): ?>
                            <tr class="<?= $tip['overdue'] ? 'trip-overdue-row' : '' ?>">
                                <td class="trip-id"><?= esc($tip['trip_id']) ?></td>
                                <td><?= esc($tip['destination']) ?></td>
                                <td><?= esc($tip['driver']) ?></td>
                                <td><?= esc($tip['vehicle']) ?></td>
                                <td>
                                    <?= esc($tip['expected_return']) ?>
                                    <?php if ($tip['overdue']): ?><span class="status-badge badge-cancelled">Overdue</span><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── VEHICLE & DRIVER MONITORING ─────────────────────────── -->
    <div class="table-panel" id="vehicleMonitoringPanel" style="display:none;">
        <div class="table-toolbar"><h3 style="margin:0;"><i class="bi bi-truck"></i> Vehicle & Driver Monitoring</h3></div>
        <div class="table-scroll">
            <table class="travel-table">
                <thead>
                    <tr><th>Vehicle</th><th>Status</th><th>Assigned Driver</th><th>Current Trip</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($vehicle_monitoring)): ?>
                        <tr><td colspan="4" class="empty-row">No vehicles on record.</td></tr>
                    <?php else: ?>
                        <?php foreach ($vehicle_monitoring as $vm): ?>
                            <tr>
                                <td><?= esc($vm['vehicle']) ?></td>
                                <td><span class="status-badge <?= $vm['status'] === 'Active' ? 'badge-in-transit' : 'badge-approved' ?>"><?= esc($vm['status']) ?></span></td>
                                <td><?= esc($vm['driver']) ?></td>
                                <td><?= esc($vm['trip']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
                <div class="toolbar-right">
                    <div class="filter-menu-wrapper">
                      <button type="button" class="filter-btn" onclick="toggleTravelFilterMenu()" aria-label="Open filters">
                        <i class="bi bi-funnel"></i>
                      </button>
                      <div class="filter-popup" id="travelFilterPopup">
                        <div class="filter-popup-title">Filter</div>
                        <div class="filter-row">
                          <label for="statusFilter">Status</label>
                          <select id="statusFilter" onchange="filterTable()">
                            <option value="">All Statuses</option>
                            <option value="Submitted">Submitted</option>
                            <option value="Reviewed">Reviewed</option>
                            <option value="Approved">Approved</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Completed">Completed</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Cancelled">Cancelled</option>
                          </select>
                        </div>
                      </div>
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
                            <tr data-status="<?= esc($trip['status']) ?>" data-requester="<?= strtolower(esc($trip['requester_name'] ?? '')) ?>">
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
                                            <i class="bi bi-eye-fill"></i>
                                        </button>

                                        <?php if ($trip['status'] === 'Submitted'): ?>
                                            <form method="post" action="<?= base_url('travel/review/'.$trip['id']) ?>" style="display:contents;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="icon-btn" style="background:#e5f0fc;color:#1d64c9;" title="Mark Reviewed"><i class="bi bi-eyeglasses"></i></button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (in_array($trip['status'], ['Submitted', 'Reviewed'], true)): ?>
                                            <form method="post" action="<?= base_url('travel/approve/'.$trip['id']) ?>" style="display:contents;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="icon-btn" style="background:#dcfce7;color:#166534;" title="Approve"><i class="bi bi-check-lg"></i></button>
                                            </form>
                                            <form method="post" action="<?= base_url('travel/reject/'.$trip['id']) ?>" onsubmit="return confirm('Reject this trip ticket?')" style="display:contents;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="icon-btn" style="background:#fee2e2;color:#991b1b;" title="Reject"><i class="bi bi-x-lg"></i></button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (in_array($trip['status'], ['Submitted', 'Reviewed', 'Approved'], true)): ?>
                                            <form method="post" action="<?= base_url('travel/cancel/'.$trip['id']) ?>" onsubmit="return confirm('Cancel this trip ticket?')" style="display:contents;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="icon-btn" style="background:#f3f4f6;color:#6b7280;" title="Cancel Trip"><i class="bi bi-slash-circle"></i></button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (in_array($trip['status'], ['Approved', 'In Transit'], true)): ?>
                                            <form method="post" action="<?= base_url('travel/complete/'.$trip['id']) ?>" onsubmit="return confirm('Mark this trip as completed?')" style="display:contents;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="icon-btn" style="background:#e0e7ff;color:#3730a3;" title="Mark Completed"><i class="bi bi-flag-fill"></i></button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="post" action="<?= base_url('travel/delete/'.$trip['id']) ?>" onsubmit="return confirm('Archive this trip ticket?')" style="display:contents;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="icon-btn" style="background:#f3f4f6;color:#6b7280;" title="Archive"><i class="bi bi-archive-fill"></i></button>
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

            const statusHistoryHtml = (t.status_log || []).length
                ? t.status_log.map(entry => `
                    <div class="status-timeline-item">
                        <div class="status-timeline-dot"></div>
                        <div class="status-timeline-content">
                            <div class="status-timeline-top"><strong>${entry.status}</strong><span>${formatDateTime(entry.changed_at)}</span></div>
                            <div class="status-timeline-by">by ${entry.changed_by}</div>
                            ${entry.notes ? `<div class="status-timeline-notes">${entry.notes}</div>` : ''}
                        </div>
                    </div>`).join('')
                : '<div class="status-timeline-empty">No status history recorded.</div>';

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
                    <div class="status-history-panel">
                        <button type="button" class="status-history-toggle" onclick="this.parentElement.classList.toggle('open')">
                            <span><i class="bi bi-clock-history"></i> Status History</span>
                            <i class="bi bi-chevron-down status-history-caret"></i>
                        </button>
                        <div class="status-timeline">${statusHistoryHtml}</div>
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

// ── Stat card actions ────────────────────────────────────────────
function filterTripsByStat(status) {
    document.getElementById('statusFilter').value = status;
    filterTable();
    document.querySelector('.table-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function toggleTripsInProgress() {
    const panel = document.getElementById('tripsInProgressPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    if (panel.style.display === 'block') panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function toggleVehicleMonitoring() {
    const panel = document.getElementById('vehicleMonitoringPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    if (panel.style.display === 'block') panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── Table filter ───────────────────────────────────────────────
function filterTable() {
    const status = document.getElementById('statusFilter').value.toLowerCase();
    const search = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#travelTable tbody tr[data-status]');

    rows.forEach(row => {
        const rowStatus = (row.dataset.status || '').toLowerCase();
        const rowText = (row.textContent || '').toLowerCase();

        const matchStatus = !status || rowStatus === status;
        const matchSearch = !search || rowText.includes(search);
        row.style.display = matchStatus && matchSearch ? '' : 'none';
    });
}

function toggleTravelFilterMenu() {
    document.getElementById('travelFilterPopup').classList.toggle('visible');
}

document.addEventListener('click', e => {
    const wrapper = document.querySelector('.filter-menu-wrapper');
    const popup = document.getElementById('travelFilterPopup');
    if (wrapper && !wrapper.contains(e.target)) {
        popup.classList.remove('visible');
    }
});

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
function formatDateTime(d) {
    if (!d) return '—';
    const dt = new Date(d.replace(' ', 'T'));
    return isNaN(dt) ? d : dt.toLocaleString('en-PH', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
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
