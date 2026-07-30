<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-Cng84V8fL4Xq3Q5Z5s0w6h2lY0+u2C0Q1Z7V6l5QxJw3qQ5Z5s0w6h2lY0+u2C0Q1Z7V6l5QxJw3qQ5Z5s0w6h2lY0+u2C0Q1Z7V6l5QxJw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<div class="travel-wrapper">

    <!-- ── PAGE HEADER ─────────────────────────────────────────── -->
    <div class="page-header compact-header">
        <div>
            <h1 class="page-title">Driver's Trip Ticket</h1>
            <p class="page-subtitle">Manage trip tickets, travel authorizations, and driver assignments.</p>
        </div>
        <div class="header-actions"></div>
    </div>

    <!-- ── SUMMARY CARDS ───────────────────────────────────────── -->
    <div class="summary-grid">
        <div class="summary-card pending">
            <div class="summary-label">Pending Requests</div>
            <div class="summary-value"><?= $pending_count ?? 0 ?></div>
            <div class="summary-sub warning"><i class="bi bi-exclamation-circle"></i> Requires Approval</div>
        </div>
        <div class="summary-card approved">
            <div class="summary-label">Approved Trips</div>
            <div class="summary-value"><?= $approved_count ?? 0 ?></div>
            <div class="summary-sub success"><i class="bi bi-check-circle"></i> Scheduled Next</div>
        </div>
        <div class="summary-card today">
            <div class="summary-label">Today's Trips</div>
            <div class="summary-value"><?= $today_count ?? 0 ?></div>
            <div class="summary-sub info"><i class="bi bi-truck"></i> Active Dispatch</div>
        </div>
        <div class="summary-card completed">
            <div class="summary-label">Completed Trips</div>
            <div class="summary-value"><?= $completed_count ?? 0 ?></div>
            <div class="summary-sub muted">This academic term</div>
        </div>
        <div class="summary-card cancelled">
            <div class="summary-label">Cancelled Trips</div>
            <div class="summary-value"><?= $cancelled_count ?? 0 ?></div>
            <div class="summary-sub danger">Cancelled / Archived</div>
        </div>
        <div class="summary-card vehicles">
            <div class="summary-label">Available Vehicles</div>
            <div class="summary-value"><?= $available_vehicles ?? 0 ?>/<?= $total_vehicles ?? count($vehicles ?? []) ?></div>
            <div class="summary-sub success">Ready to assign</div>
        </div>
    </div>

    <!-- ── MAIN CONTENT: TABLE + SIDEBAR ───────────────────────── -->
    <div class="travel-body">

        <!-- Trip Table -->
        <div class="table-panel">
            <div class="table-toolbar">
                <h2 class="panel-title">Driver's Trip Tickets</h2>
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
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="toolbar-search">
                      <input type="text" id="searchInput" class="search-box" placeholder="Search requester..." oninput="filterTable()">
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
                            <tr data-status="<?= esc($trip['status']) ?>" data-requester="<?= strtolower(esc($trip['requester_name'])) ?>">
                                <td class="trip-id"><?= esc($trip['trip_id']) ?></td>
                                <td>
                                    <div class="requester-cell">
                                        <span class="req-name"><?= esc($trip['requester_name']) ?></span>
                                        <span class="req-dept"><?= esc($trip['department_name'] ?? '') ?></span>
                                    </div>
                                </td>
                                <td><?= esc($trip['destination']) ?></td>
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
                                        <?php if (!empty($trip['vehicle_plate'])): ?>
                                            <span class="staff-badge vehicle"><i class="bi bi-truck"></i> <?= esc($trip['vehicle_model']) ?> <?= esc($trip['vehicle_plate']) ?></span>
                                        <?php else: ?>
                                            <span class="staff-badge unassigned"><i class="bi bi-truck"></i> Vehicle Pending</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match($trip['status']) {
                                        'Approved'  => 'badge-approved',
                                        'Completed' => 'badge-completed',
                                        'Cancelled' => 'badge-cancelled',
                                        default     => 'badge-pending',
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

                                        <?php if ($trip['status'] === 'Pending'): ?>
                                            <button class="icon-btn approve" title="Approve Trip"
                                                onclick="openApproveModal(<?= $trip['id'] ?>, '<?= esc($trip['trip_id']) ?>', '<?= esc($trip['destination']) ?>')">
                                                <i class="bi bi-check-circle-fill"></i>
                                            </button>
                                        <?php endif; ?>
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
     MODAL: APPROVE TRIP
════════════════════════════════════════════════════════════════ -->
<div id="approveModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3><i class="bi bi-check-circle"></i> Approve Trip</h3>
            <button class="modal-close" onclick="closeApproveModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="post" id="approveForm">
            <?= csrf_field() ?>
            <div class="modal-body">
                <p class="approve-info">Approving: <strong id="approveLabel"></strong></p>
                <div class="form-group">
                        <label>Assign Driver</label>
                        <select name="assigned_driver_id">
                            <option value="">— Select Driver —</option>
                            <?php foreach (($drivers ?? []) as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= esc($p['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assign Vehicle</label>
                        <select name="assigned_vehicle_id">
                            <option value="">— Select Vehicle —</option>
                            <?php foreach (($vehicles ?? []) as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= esc($v['vehicle_name']) ?> — <?= esc($v['plate_no']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeApproveModal()">Cancel</button>
                <button type="submit" class="btn-submit approve-submit"><i class="bi bi-check-lg"></i> Approve Trip</button>
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
                <span class="modal-sub">Paperless vehicle travel authorization • FU-UBRA</span>
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
     SCRIPTS
════════════════════════════════════════════════════════════════ -->
<script>
let html5QrCodeScanner = null;

// ── Modal helpers ──────────────────────────────────────────────
function closeApproveModal(){ document.getElementById('approveModal').style.display = 'none'; }
function closeTicketModal(){ document.getElementById('ticketModal').style.display = 'none'; }

function openApproveModal(id, tripId, destination) {
    document.getElementById('approveLabel').textContent = tripId + ' → ' + destination;
    document.getElementById('approveForm').action = '<?= base_url('travel/approve/') ?>' + id;
    document.getElementById('approveModal').style.display = 'flex';
}

// ── View ticket (AJAX) ─────────────────────────────────────────
function viewTicket(id) {
    document.getElementById('ticketModal').style.display = 'flex';
    document.getElementById('ticketBody').innerHTML =
        '<div class="ticket-loading"><i class="bi bi-hourglass-split"></i> Loading trip details...</div>';

    fetch('<?= base_url('travel/getTrip/') ?>' + id)
        .then(r => r.json())
        .then(t => {
            const statusClass = {
                Approved: 'badge-approved', Completed: 'badge-completed',
                Cancelled: 'badge-cancelled', Pending: 'badge-pending'
            }[t.status] || 'badge-pending';

            document.getElementById('ticketBody').innerHTML = `
            <div class="ticket-layout">
                <div class="ticket-left">
                    <div class="ticket-section-title">Trip Information</div>
                    <div class="ticket-fields">
                        <div class="tf"><span>Trip Number</span><strong>${t.trip_id || '—'}</strong></div>
                        <div class="tf"><span>Request Date</span><strong>${formatDate(t.created_at)}</strong></div>
                        <div class="tf"><span>Travel Date</span><strong>${formatDate(t.travel_date)}</strong></div>
                        <div class="tf"><span>Department</span><strong>${t.department || '—'}</strong></div>
                        <div class="tf"><span>Scanned ID</span><strong>${t.scanned_id || '—'}</strong></div>
                        <div class="tf"><span>Departure Time</span><strong>${formatTime(t.departure_time)}</strong></div>
                        <div class="tf"><span>Expected Return</span><strong>${formatTime(t.return_time)}</strong></div>
                        <div class="tf"><span>Assigned Driver</span><strong>${t.driver_name || 'Not Assigned'}</strong></div>
                        <div class="tf"><span>Vehicle Profile</span><strong>${t.vehicle_model ? t.vehicle_model + ' (' + t.vehicle_plate + ')' : 'Not Assigned'}</strong></div>
                        <div class="tf full"><span>Destination</span><strong>${t.destination}</strong></div>
                        <div class="tf full"><span>Travel Purpose</span><strong>${t.purpose}</strong></div>
                        <div class="tf full"><span>Official Passengers</span><strong>${t.requester_name}</strong></div>
                    </div>
                    <div class="ticket-section-title" style="margin-top:1.2rem;">Approval Timeline</div>
                    <div class="timeline">
                        <div class="tl-step done">Requested</div>
                        <div class="tl-step ${['Approved','Completed'].includes(t.status) ? 'done' : 'todo'}">Dept. Head</div>
                        <div class="tl-step ${['Approved','Completed'].includes(t.status) ? 'done' : 'todo'}">Operations</div>
                        <div class="tl-step ${t.status === 'Approved' ? 'active' : t.status === 'Completed' ? 'done' : 'todo'}">Approved</div>
                        <div class="tl-step ${t.status === 'Completed' ? 'done' : 'todo'}">Completed</div>
                    </div>
                    <div class="ticket-section-title" style="margin-top:1.2rem;">Digital Signatures</div>
                    <div class="sigs">
                        <div class="sig-box"><div class="sig-line"></div><div class="sig-label">${t.requester_name}</div></div>
                        <div class="sig-box"><div class="sig-line"></div><div class="sig-label">Dept. Head</div></div>
                        <div class="sig-box"><div class="sig-line"></div><div class="sig-label">Operations Officer</div></div>
                        <div class="sig-box"><div class="sig-line"></div><div class="sig-label">${t.driver_name || 'Driver'}</div></div>
                    </div>
                </div>
                <div class="ticket-right">
                    <div class="ticket-status-panel">
                        <div class="ts-label">Security Verification</div>
                        <div class="status-badge ${statusClass}" style="margin:0.5rem auto;display:block;width:fit-content;">${t.status}</div>
                        <div id="qr-code-${t.id}" class="qr-container"></div>
                        <div class="ts-sub">Driver Verification Portal</div>
                        <div class="ts-verify success"><i class="bi bi-check-circle-fill"></i> Verification Success</div>
                    </div>
                    <div class="ubra-mini">
                        <div class="ubra-header">
                            <span class="ubra-icon">U</span>
                            <div><div class="ubra-name">Mr. UBRA</div><div class="ubra-sub">Trip Summary Agent</div></div>
                        </div>
                        <ul class="ubra-list small">
                            <li><i class="bi bi-check-circle-fill text-success"></i> Driver is ${t.driver_name ? 'available' : 'not yet assigned'}.</li>
                            <li><i class="bi bi-check-circle-fill text-success"></i> Travel schedule confirmed.</li>
                            <li><i class="bi bi-check-circle-fill text-success"></i> Vehicle inspection ${t.vehicle_plate ? 'completed' : 'pending'}.</li>
                            <li><i class="bi bi-check-circle-fill text-success"></i> QR verification ready.</li>
                        </ul>
                        <p class="ubra-note">This digital trip ticket complies with University Executive Order No. 42 (Paperless Mandate).</p>
                        <div class="ticket-action-row">
                            <button class="btn-submit" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
                            <button class="btn-cancel" onclick="closeTicketModal()"><i class="bi bi-x"></i> Close</button>
                        </div>
                    </div>
                </div>
            </div>`;
            
            // Generate QR code using qr.js
            const qrContainer = document.getElementById(`qr-code-${t.id}`);
            if (qrContainer && typeof qrcode !== 'undefined') {
                const qrData = `${window.location.origin}/guard/check/${t.id}`;
                qrContainer.innerHTML = '';
                new QRCode(qrContainer, {
                    text: qrData,
                    width: 150,
                    height: 150,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                });
            } else if (qrContainer) {
                qrContainer.innerHTML = '<i class="bi bi-qr-code" style="font-size:4rem;color:var(--maroon)"></i>';
            }
        })
        .catch(() => {
            document.getElementById('ticketBody').innerHTML =
                '<div class="ticket-loading error"><i class="bi bi-exclamation-triangle"></i> Failed to load trip details.</div>';
        });
}

function openTicketFromHeader() { alert('Please select a trip from the table to view its ticket.'); }
function openCalendar() { window.location.href = '<?= base_url('calendar') ?>'; }

// ── Table filter ───────────────────────────────────────────────
function filterTable() {
    const status = document.getElementById('statusFilter').value.toLowerCase();
    const search = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#travelTable tbody tr[data-status]');

    rows.forEach(row => {
        const rowStatus = (row.dataset.status || '').toLowerCase();
        const rowRequester = (row.dataset.requester || '').toLowerCase();
        const rowText = (row.textContent || '').toLowerCase();

        const matchStatus = !status || rowStatus === status;
        const matchRequester = !search || rowRequester.includes(search) || rowText.includes(search);
        row.style.display = matchStatus && matchRequester ? '' : 'none';
    });
}

function toggleTravelFilterMenu() {
    const popup = document.getElementById('travelFilterPopup');
    popup.classList.toggle('visible');
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

// Auto-hide flash
setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => el.style.opacity = '0');
}, 4000);
</script>

<?= $this->endSection() ?>