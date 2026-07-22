<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('Assets/css/travel.css') ?>">
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-Cng84V8fL4Xq3Q5Z5s0w6h2lY0+u2C0Q1Z7V6l5QxJw3qQ5Z5s0w6h2lY0+u2C0Q1Z7V6l5QxJw3qQ5Z5s0w6h2lY0+u2C0Q1Z7V6l5QxJw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<div class="travel-wrapper">

    <!-- ── PAGE HEADER ─────────────────────────────────────────── -->
    <div class="page-header compact-header">
        <div>
            <h1 class="page-title">Driver's Trip Ticket</h1>
            <p class="page-subtitle">Manage trip tickets, travel authorizations, and driver assignments.</p>
        </div>
        <div class="header-actions">
            <button class="btn-primary" onclick="openAddModal()">
                <i class="bi bi-plus-lg"></i> New Trip Ticket
            </button>
        </div>
    </div>

    <!-- ── FLASH MESSAGES ──────────────────────────────────────── -->
    <?php if (!empty($flash_success)): ?>
        <div class="flash flash-success"><i class="bi bi-check-circle-fill"></i> <?= esc($flash_success) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
        <div class="flash flash-error"><i class="bi bi-exclamation-triangle-fill"></i> <?= $flash_error ?></div>
    <?php endif; ?>

    <!-- ── SUMMARY CARDS ───────────────────────────────────────── -->
    <div class="summary-grid">
        <div class="summary-card pending">
            <div class="summary-label">Pending Requests</div>
            <div class="summary-value"><?= $pending_count ?></div>
            <div class="summary-sub warning"><i class="bi bi-exclamation-circle"></i> Requires Approval</div>
        </div>
        <div class="summary-card approved">
            <div class="summary-label">Approved Trips</div>
            <div class="summary-value"><?= $approved_count ?></div>
            <div class="summary-sub success"><i class="bi bi-check-circle"></i> Scheduled Next</div>
        </div>
        <div class="summary-card today">
            <div class="summary-label">Today's Trips</div>
            <div class="summary-value"><?= $today_count ?></div>
            <div class="summary-sub info"><i class="bi bi-truck"></i> Active Dispatch</div>
        </div>
        <div class="summary-card completed">
            <div class="summary-label">Completed Trips</div>
            <div class="summary-value"><?= $completed_count ?></div>
            <div class="summary-sub muted">This academic term</div>
        </div>
        <div class="summary-card cancelled">
            <div class="summary-label">Cancelled Trips</div>
            <div class="summary-value"><?= $cancelled_count ?></div>
            <div class="summary-sub danger">Cancelled / Archived</div>
        </div>
        <div class="summary-card vehicles">
            <div class="summary-label">Available Vehicles</div>
            <div class="summary-value"><?= $available_vehicles ?>/<?= count($vehicles) + (int)$approved_count ?></div>
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
                    <select id="statusFilter" onchange="filterTable()">
                        <option value="">Filter: All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                    <input type="text" id="searchInput" placeholder="Search requester..." onkeyup="filterTable()">
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
                                        <span class="req-dept"><?= esc($trip['department']) ?></span>
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
                                        <!-- Ticket icon -->
                                        <button class="icon-btn ticket" title="View Trip Ticket"
                                            onclick="viewTicket(<?= $trip['id'] ?>)">
                                            <i class="bi bi-ticket-perforated"></i>
                                        </button>

                                        <?php if ($trip['status'] === 'Pending'): ?>
                                            <!-- Approve -->
                                            <button class="icon-btn approve" title="Approve Trip"
                                                onclick="openApproveModal(<?= $trip['id'] ?>, '<?= esc($trip['trip_id']) ?>', '<?= esc($trip['destination']) ?>')">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <!-- Reject -->
                                            <form method="post" action="<?= base_url('travel/reject/' . $trip['id']) ?>"
                                                onsubmit="return confirm('Cancel this trip request?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="icon-btn reject" title="Reject / Cancel">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        <?php elseif ($trip['status'] === 'Approved'): ?>
                                            <!-- Mark Complete -->
                                            <form method="post" action="<?= base_url('travel/complete/' . $trip['id']) ?>"
                                                onsubmit="return confirm('Mark this trip as completed?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="icon-btn complete" title="Mark Completed">
                                                    <i class="bi bi-flag-fill"></i>
                                                </button>
                                            </form>
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

        <!-- Sidebar -->
        <div class="travel-sidebar">
            <!-- Operational Summary -->
            <div class="sidebar-card compact-card">
                <div class="sidebar-card-title">Operational Summary</div>
                <div class="summary-list">
                    <div class="summary-list-item">
                        <span>Pending approvals</span>
                        <strong><?= $pending_count ?></strong>
                    </div>
                    <div class="summary-list-item">
                        <span>Approved trips</span>
                        <strong><?= $approved_count ?></strong>
                    </div>
                    <div class="summary-list-item">
                        <span>Vehicles ready</span>
                        <strong><?= $available_vehicles ?></strong>
                    </div>
                </div>
            </div>

            <!-- Logistics Stats -->
            <div class="sidebar-card">
                <div class="sidebar-card-title">Operational Logistics Stats</div>
                <div class="stat-row">
                    <span>Vehicle Utilization Index</span>
                    <div class="stat-bar-wrap">
                        <div class="stat-bar" style="width: 72%"></div>
                        <span>72%</span>
                    </div>
                </div>
                <div class="stat-row">
                    <span>Driver Dispatch Rate</span>
                    <div class="stat-bar-wrap">
                        <div class="stat-bar success" style="width: 88%"></div>
                        <span>88%</span>
                    </div>
                </div>
            </div>

            <!-- Mr. UBRA summary -->
            <div class="sidebar-card ubra-card">
                <div class="ubra-header">
                    <span class="ubra-icon">U</span>
                    <div>
                        <div class="ubra-name">Mr. UBRA</div>
                        <div class="ubra-sub">Operations Assistant</div>
                    </div>
                </div>
                <div class="ubra-title">Today's Travel Summary</div>
                <ul class="ubra-list">
                    <li><i class="bi bi-dot"></i> <?= $today_count ?> trip(s) scheduled to depart today.</li>
                    <?php if ($pending_count > 0): ?>
                    <li><i class="bi bi-dot"></i> <?= $pending_count ?> driver assignment(s) still pending.</li>
                    <?php endif; ?>
                    <li><i class="bi bi-dot"></i> <?= $available_vehicles ?> vehicle(s) confirmed available for dispatch.</li>
                    <?php if ($pending_count > 0): ?>
                    <li><i class="bi bi-dot"></i> <?= $pending_count ?> trip request(s) require administrative approval.</li>
                    <?php endif; ?>
                </ul>
                <div class="ubra-actions">
                    <button class="ubra-btn" onclick="openAddModal()">Assign Driver</button>
                    <button class="ubra-btn" onclick="openCalendar()">Open Calendar</button>
                    <button class="ubra-btn primary" onclick="openAddModal()">Generate Trip Ticket</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     MODAL: NEW TRIP TICKET
════════════════════════════════════════════════════════════════ -->
<div id="addModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="bi bi-plus-circle"></i> New Trip Ticket</h3>
            <button class="modal-close" onclick="closeAddModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="post" action="<?= base_url('travel/add') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label>Scanned ID <span class="optional">(optional)</span></label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" id="scannedIdInput" name="scanned_id" placeholder="Scanned ID will appear here">
                        <button type="button" class="btn-outline-sm" onclick="openScannerModal()"><i class="bi bi-upc-scan"></i> Scan ID</button>
                    </div>
                </div>
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
                        <label>Department <span class="req">*</span></label>
                        <select name="department_id" required>
                            <option value="">— Select Department —</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= esc($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Destination <span class="req">*</span></label>
                    <input type="text" name="destination" placeholder="e.g. Regional IT Hub, District 4 Campus" required>
                </div>
                <div class="form-group">
                    <label>Purpose / Objective <span class="req">*</span></label>
                    <textarea name="purpose" rows="2" placeholder="Brief description of the trip purpose..." required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Travel Date <span class="req">*</span></label>
                        <input type="date" name="travel_date" required>
                    </div>
                    <div class="form-group">
                        <label>Departure Time <span class="req">*</span></label>
                        <input type="time" name="departure_time" required>
                    </div>
                    <div class="form-group">
                        <label>Expected Return <span class="req">*</span></label>
                        <input type="time" name="return_time" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Assign Driver <span class="optional">(optional)</span></label>
                        <select name="assigned_driver">
                            <option value="">— Select Driver —</option>
                            <?php foreach ($personnel as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= esc($p['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assign Vehicle <span class="optional">(optional)</span></label>
                        <select name="assigned_vehicle">
                            <option value="">— Select Vehicle —</option>
                            <?php foreach ($vehicles as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= esc($v['model']) ?> — <?= esc($v['plate_no']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn-submit"><i class="bi bi-send"></i> Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     MODAL: ID SCANNER
════════════════════════════════════════════════════════════════ -->
<div id="scannerModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="bi bi-upc-scan"></i> Scan ID</h3>
            <button class="modal-close" onclick="closeScannerModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <div id="scannerContainer" style="width: 100%;"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeScannerModal()">Close</button>
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
                            <?php foreach ($drivers as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= esc($p['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assign Vehicle</label>
                        <select name="assigned_vehicle_id">
                            <option value="">— Select Vehicle —</option>
                            <?php foreach ($vehicles as $v): ?>
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
function openAddModal()    { document.getElementById('addModal').style.display = 'flex'; }
function closeAddModal()   { document.getElementById('addModal').style.display = 'none'; }
function closeApproveModal(){ document.getElementById('approveModal').style.display = 'none'; }
function closeTicketModal(){ document.getElementById('ticketModal').style.display = 'none'; }

function openScannerModal() {
    document.getElementById('scannerModal').style.display = 'flex';
    
    // Initialize scanner
    if (!html5QrCodeScanner) {
        html5QrCodeScanner = new Html5QrcodeScanner(
            "scannerContainer", 
            { fps: 10, qrbox: { width: 250, height: 250 }, rememberLastUsedCamera: true },
            /* verbose= */ false
        );
        
        html5QrCodeScanner.render(
            (decodedText, decodedResult) => {
                // On successful scan
                document.getElementById('scannedIdInput').value = decodedText;
                closeScannerModal();
            },
            (errorMessage) => {
                // Scan error, ignore
            }
        );
    }
}

function closeScannerModal() {
    if (html5QrCodeScanner) {
        html5QrCodeScanner.clear().catch(error => {
            console.error("Failed to clear html5QrCodeScanner. " + error);
        });
        html5QrCodeScanner = null;
        document.getElementById('scannerContainer').innerHTML = '';
    }
    document.getElementById('scannerModal').style.display = 'none';
}

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
    const status    = document.getElementById('statusFilter').value.toLowerCase();
    const search    = document.getElementById('searchInput').value.toLowerCase();
    const rows      = document.querySelectorAll('#travelTable tbody tr[data-status]');

    rows.forEach(row => {
        const matchStatus    = !status || row.dataset.status.toLowerCase() === status;
        const matchRequester = !search || row.dataset.requester.includes(search);
        row.style.display = matchStatus && matchRequester ? '' : 'none';
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

// Auto-hide flash
setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => el.style.opacity = '0');
}, 4000);
</script>

<?= $this->endSection() ?>