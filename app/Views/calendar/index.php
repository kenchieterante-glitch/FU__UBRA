<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
  $title = $title ?? 'Operations Calendar';
  $events_json = $events_json ?? '[]';
  $pending_renewals = $pending_renewals ?? [];
?>

<link rel="stylesheet" href="<?= base_url('Assets/css/calendar.css') . '?v=' . @filemtime(FCPATH.'Assets/css/calendar.css') ?>">
<!-- FullCalendar 6 (CDN) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.css" rel="stylesheet">

<div class="cal-wrapper">

    <!-- ── PAGE HEADER ──────────────────────────────────────────── -->
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-calendar3"></i> Operations Calendar</h1>
            <p class="page-subtitle">Manage inspections, maintenance, and operational activities.</p>
        </div>
        <div class="header-actions">
            <button class="btn-primary" id="addEventBtn">
                <i class="bi bi-plus-lg"></i> Add Event
            </button>
            <div class="view-switcher">
                <button class="vsw-btn active" data-view="dayGridMonth">Monthly</button>
                <button class="vsw-btn" data-view="timeGridWeek">Weekly</button>
                <button class="vsw-btn" data-view="listWeek">Agenda</button>
            </div>
        </div>
    </div>

    <!-- ── MONTH NAVIGATION ─────────────────────────────────────── -->
    <div class="cal-nav-row">
        <div class="cal-nav-controls">
            <button type="button" class="cal-nav-btn" id="calPrevBtn" aria-label="Previous month"><i class="bi bi-chevron-left"></i></button>
            <div class="cal-nav-label" id="calNavLabel">&nbsp;</div>
            <button type="button" class="cal-nav-btn" id="calNextBtn" aria-label="Next month"><i class="bi bi-chevron-right"></i></button>
        </div>
        <button type="button" class="cal-nav-today" id="calTodayBtn">Today</button>
    </div>

    <!-- ── LEGEND ────────────────────────────────────────────────── -->
    <div class="cal-legend">
        <span class="legend-item"><span class="legend-dot" style="background:#f59e0b"></span> Inspection</span>
        <span class="legend-item"><span class="legend-dot" style="background:#7c3aed"></span> Maintenance</span>
        <span class="legend-item"><span class="legend-dot" style="background:#2563eb"></span> Compliance</span>
        <span class="legend-item"><span class="legend-dot" style="background:#16a34a"></span> Cleaning</span>
        <span class="legend-item"><span class="legend-dot" style="background:#dc2626"></span> Urgent Cleaning</span>
        <span class="legend-item"><span class="legend-dot" style="background:#0891b2"></span> Travel</span>
    </div>

    <!-- ── MAIN LAYOUT ───────────────────────────────────────────── -->
    <div class="cal-layout">

        <!-- Priority reminders — shown above the calendar -->
        <div class="cal-priority-row">

            <!-- Today's schedule -->
            <div class="sidebar-card">
                <div class="sc-header">
                    <div class="sc-title">Today's Schedule</div>
                    <div class="sc-date"><?= date('l, M j, Y') ?></div>
                </div>
                <div class="empty-day"><i class="bi bi-calendar-check"></i> Nothing scheduled today.</div>
            </div>

            <!-- Upcoming Events -->
            <div class="sidebar-card">
                <div class="sc-title">Upcoming Events</div>
                <!-- Static maintenance placeholders (wired to maintenance module in a later step) -->
                <div class="upcoming-item">
                    <span class="up-dot" style="background:#7c3aed"></span>
                    <div><div class="up-title">Van-03 Inspection</div><div class="up-sub">Scheduled</div></div>
                </div>
                <div class="upcoming-item">
                    <span class="up-dot" style="background:#f59e0b"></span>
                    <div><div class="up-title">AC Cleaning – Bldg A</div><div class="up-sub">Routine</div></div>
                </div>
            </div>

            <!-- Pending Renewals — real vehicles whose inspection is expired
                 or due soon (inspection_status), not a one-click "authorize"
                 since that decision belongs in Vehicle Management, not here. -->
            <div class="sidebar-card">
                <div class="sc-title">Pending Renewals</div>
                <?php if (empty($pending_renewals)): ?>
                    <div class="empty-day"><i class="bi bi-check-circle"></i> No vehicle renewals pending.</div>
                <?php else: ?>
                    <?php foreach ($pending_renewals as $v): ?>
                        <div class="pending-item">
                            <div>
                                <div class="pi-title"><?= esc($v['vehicle_name']) ?> (<?= esc($v['plate_no']) ?>)</div>
                                <div class="pi-sub">Inspection: <?= esc($v['inspection_status']) ?></div>
                            </div>
                            <a href="<?= base_url('vehicles') ?>" class="pi-action" title="Review in Vehicle Management">Review →</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="cal-top">
            <div class="cal-main">
                <div id="calendar"></div>
            </div>
        </div>

        <div class="cal-bottom-grid">
            <!-- Left sidebar -->
            <div class="cal-sidebar-left">

            <!-- Holiday list -->
            <div class="sidebar-card">
                <div class="sc-title">Holiday List</div>
                <div class="holiday-item"><span>Negros Day (Charter Day)</span><span class="hd-type">Regional Holiday</span></div>
                <div class="holiday-item"><span>Bonifacio Day</span><span class="hd-type">National Holiday</span></div>
            </div>
        </div>

            <!-- Right sidebar -->
            <div class="cal-sidebar-right">
            <!-- Event detail panel (shown when event clicked) -->
            <div id="eventDetailPanel" class="event-detail-panel" style="display:none;">
                <div class="edp-header">
                    <div class="edp-badge" id="edpBadge">Event</div>
                    <span class="edp-status" id="edpStatus">Active</span>
                </div>
                <div class="edp-title" id="edpTitle">—</div>
                <div class="edp-meta-grid" id="edpMeta"></div>
                <div class="edp-actions">
                    <button class="btn-outline-sm" id="edpNotifyBtn" onclick="notifyAssigned()"><i class="bi bi-bell"></i> <span id="edpNotifyLabel">Notify</span></button>
                    <button class="btn-outline-sm" onclick="document.getElementById('eventDetailPanel').style.display='none'">
                        <i class="bi bi-x"></i> Close
                    </button>
                </div>
            </div>

            <!-- UBRA summary -->
            <div class="ubra-card">
                <div class="ubra-header">
                    <span class="ubra-icon">U</span>
                    <div>
                        <div class="ubra-name">Mr. UBRA</div>
                        <div class="ubra-sub">Operations Assistant</div>
                    </div>
                    <span class="ubra-live-badge">Live</span>
                </div>
                <div class="ubra-section-title">Today's Summary</div>
                <ul class="ubra-list">
                    <li>Bldg A AC cleaning starts in <strong>2 days</strong>.</li>
                    <li>1 maintenance schedule due <strong>next week</strong>.</li>
                </ul>
                <div class="ubra-section-title" style="margin-top:.9rem;">Suggested Actions</div>
                <div class="ubra-btns">
                    <button class="ubra-btn" onclick="notifyDriver()">
                        <span class="ubra-btn-icon"><i class="bi bi-person-fill"></i></span>
                        <span class="ubra-btn-label">Notify Driver (Van-03)</span>
                        <span class="ubra-btn-arrow"><i class="bi bi-chevron-right"></i></span>
                    </button>
                    <button class="ubra-btn" onclick="notifyCleaning()">
                        <span class="ubra-btn-icon"><i class="bi bi-brush"></i></span>
                        <span class="ubra-btn-label">Notify Cleaning Personnel</span>
                        <span class="ubra-btn-arrow"><i class="bi bi-chevron-right"></i></span>
                    </button>
                    <button class="ubra-btn primary" onclick="generateSummary()">
                        <span class="ubra-btn-icon"><i class="bi bi-file-earmark-text"></i></span>
                        <span class="ubra-btn-label">Generate Weekly Summary</span>
                        <span class="ubra-btn-arrow"><i class="bi bi-chevron-right"></i></span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     ADD EVENT MODAL
════════════════════════════════════════════════════════════════ -->
<div id="addEventModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3><i class="bi bi-calendar-plus"></i> Add Calendar Event</h3>
            <button class="modal-close" onclick="closeAddModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Event Title <span class="req">*</span></label>
                <input type="text" id="evtTitle" placeholder="e.g. Van-03 Inspection">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Date <span class="req">*</span></label>
                    <input type="date" id="evtDate">
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select id="evtType" onchange="toggleCleaningZone()">
                        <option value="Inspection">Inspection</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Compliance">Compliance</option>
                        <option value="Cleaning">Cleaning</option>
                        <option value="Urgent Cleaning">Urgent Cleaning</option>
                    </select>
                </div>
            </div>
            <div class="form-group" id="evtZoneGroup" style="display:none;">
                <label>Building / Zone <span class="req">*</span></label>
                <select id="evtZone">
                    <option value="">— Select building —</option>
                    <option>Admin Building</option>
                    <option>Library</option>
                    <option>Science Building</option>
                    <option>Gymnasium</option>
                    <option>Canteen</option>
                    <option>Engineering</option>
                    <option>CCS Building</option>
                    <option>Clinic</option>
                </select>
                <p class="field-hint">Schedules a real Janitorial Monitoring assignment for this zone and notifies the Janitorial account.</p>
            </div>
            <div class="form-group" id="evtMaintZoneGroup" style="display:none;">
                <label>Building <span class="req">*</span></label>
                <select id="evtMaintZone">
                    <option value="">— Select building —</option>
                    <option>Main entrance gate</option>
                    <option>University cafeteria / bookstore / sewing</option>
                    <option>College of Law building</option>
                    <option>College of Agriculture and SIE</option>
                    <option>Museo de Vicente</option>
                    <option>Bunk house</option>
                    <option>Service / exit gate</option>
                    <option>University library</option>
                    <option>Electric pump house</option>
                    <option>Executive house</option>
                    <option>Water pump</option>
                    <option>Guest house</option>
                    <option>HRM kitchen</option>
                    <option>College of Education building</option>
                    <option>Animation Lab / ROTC office</option>
                    <option>LG Sinco Computer Center building</option>
                    <option>Sofia Soller Sinco Hall</option>
                    <option>College of Art & Sciences building</option>
                    <option>Art & Science laboratories / audio visual rooms</option>
                    <option>College of Business Economics and Accountancy</option>
                    <option>College of Nursing</option>
                    <option>Administration building</option>
                    <option>Rizal monument / social garden</option>
                    <option>Registrar's office</option>
                    <option>Business and Finance office</option>
                    <option>Old College of Industrial Engineering and Technology</option>
                    <option>Overhead water supply tank</option>
                    <option>Flag pole</option>
                </select>
                <p class="field-hint">Logs a real Safety work order for this building and notifies the Maintenance Team.</p>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea id="evtNotes" rows="2" placeholder="Optional notes…"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeAddModal()">Cancel</button>
            <button class="btn-submit" onclick="addLocalEvent()"><i class="bi bi-plus-lg"></i> Add Event</button>
        </div>
    </div>
</div>

<!-- FullCalendar JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.js"></script>

<script>
// ── Seed events from PHP ───────────────────────────────────────
const phpEvents = <?= $events_json ?>;

// Local events added in-session (not persisted — hook to API for persistence)
const localEvents = [];

// Type → color map
const typeColors = {
    Inspection:      '#f59e0b',
    Maintenance:     '#7c3aed',
    Compliance:      '#2563eb',
    Cleaning:        '#16a34a',
    'Urgent Cleaning': '#dc2626',
    Travel:          '#0891b2',
};

// ── FullCalendar init ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const calEl = document.getElementById('calendar');

    const cal = new FullCalendar.Calendar(calEl, {
        initialView:    'dayGridMonth',
        headerToolbar:  false,            // We use our own header buttons
        height:         'auto',
        events:         [...phpEvents, ...localEvents],
        eventDisplay:   'block',
        dayMaxEvents:   3,
        eventClick:     function (info) { openEventDetail(info.event); },
        dateClick:      function (info) { openAddModalOnDate(info.dateStr); },
        eventDidMount:  function (info) {
            // Tooltip
            info.el.title = info.event.title;
        },
        // Keeps the "August 2026" label (and Agenda view's date range) in
        // sync every time the visible range changes — by nav button, view
        // switch, or the calendar's own internal date click navigation.
        datesSet:       function (info) {
            document.getElementById('calNavLabel').textContent = info.view.title;
        },
    });

    cal.render();
    window._cal = cal;   // expose for view switcher

    // ── Month/week navigation ──────────────────────────────────────
    document.getElementById('calPrevBtn').addEventListener('click', () => cal.prev());
    document.getElementById('calNextBtn').addEventListener('click', () => cal.next());
    document.getElementById('calTodayBtn').addEventListener('click', () => cal.today());

    // ── View switcher ────────────────────────────────────────────
    document.querySelectorAll('.vsw-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.vsw-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            cal.changeView(btn.dataset.view);
        });
    });

    // ── Add Event button ─────────────────────────────────────────
    document.getElementById('addEventBtn').addEventListener('click', () => {
        document.getElementById('evtDate').value = new Date().toISOString().split('T')[0];
        document.getElementById('addEventModal').style.display = 'flex';
    });

});

// ── Event detail panel ─────────────────────────────────────────
function openEventDetail(event) {
    const p    = event.extendedProps || {};
    const type = p.type || 'Event';
    const statusClass = {
        Approved: 'status-approved', Completed: 'status-completed',
        Cancelled: 'status-cancelled', Pending: 'status-pending'
    }[p.status] || 'status-pending';

    document.getElementById('edpBadge').textContent   = type;
    document.getElementById('edpBadge').className     = 'edp-badge edp-' + type.toLowerCase().replace(/\s+/g, '');
    document.getElementById('edpStatus').textContent  = p.status || 'Active';
    document.getElementById('edpStatus').className    = 'edp-status ' + statusClass;
    document.getElementById('edpTitle').textContent   = event.title;

    const start = event.start ? event.start.toLocaleDateString('en-PH', {month:'short',day:'numeric',year:'numeric'}) : '—';
    const time  = event.start ? event.start.toLocaleTimeString('en-PH', {hour:'2-digit',minute:'2-digit'}) : '—';

    document.getElementById('edpMeta').innerHTML = `
        <div class="edp-row"><span>Date</span><strong>${start}</strong></div>
        <div class="edp-row"><span>Time</span><strong>${time}</strong></div>
        ${p.requester  ? `<div class="edp-row"><span>Requester</span><strong>${esc(p.requester)}</strong></div>` : ''}
        ${p.driver     ? `<div class="edp-row"><span>Driver</span><strong>${esc(p.driver)}</strong></div>` : ''}
        ${p.vehicle    ? `<div class="edp-row"><span>Vehicle</span><strong>${esc(p.vehicle.trim())}</strong></div>` : ''}
        ${p.purpose    ? `<div class="edp-row full"><span>Purpose</span><strong>${esc(p.purpose)}</strong></div>` : ''}
        ${p.zone       ? `<div class="edp-row"><span>Zone</span><strong>${esc(p.zone)}</strong></div>` : ''}
        ${p.assignedTo ? `<div class="edp-row"><span>Assigned To</span><strong>${esc(p.assignedTo)}</strong></div>` : ''}
    `;

    // The Notify button always targets whoever this specific event is
    // actually assigned to — janitorial staff on a cleaning event, the
    // Maintenance Team on a work order, the driver on a trip — not a
    // hardcoded "driver" regardless of event type.
    const notifyBtn = document.getElementById('edpNotifyBtn');
    if (p.assignedTo) {
        notifyBtn.style.display = '';
        document.getElementById('edpNotifyLabel').textContent = 'Notify ' + p.assignedTo;
        notifyBtn.dataset.recipient = p.assignedTo;
        notifyBtn.dataset.title     = event.title;
        notifyBtn.dataset.category  = type;
    } else {
        notifyBtn.style.display = 'none';
    }

    document.getElementById('eventDetailPanel').style.display = 'block';
}

async function notifyAssigned() {
    const btn = document.getElementById('edpNotifyBtn');
    const recipient = btn.dataset.recipient;
    if (!recipient) return;

    btn.disabled = true;
    try {
        const res  = await fetch('<?= base_url('calendar/notify') ?>', {
            method: 'POST',
            headers: csrfHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ recipient, title: btn.dataset.title, category: btn.dataset.category }),
        });
        const data = await res.json();
        showToast(data.message || (data.success ? 'Notification sent.' : 'Could not send notification.'), !data.success);
    } catch (err) {
        showToast('Could not send notification. Please try again.', true);
    } finally {
        btn.disabled = false;
    }
}

// Cleaning / Urgent Cleaning need a real building/zone (they create a real
// Janitorial assignment); Maintenance needs a real building too (it creates a
// real Safety work order) — so each field only appears/is required for its type.
function toggleCleaningZone() {
    const type = document.getElementById('evtType').value;
    const isCleaning = type === 'Cleaning' || type === 'Urgent Cleaning';
    document.getElementById('evtZoneGroup').style.display = isCleaning ? 'block' : 'none';
    document.getElementById('evtMaintZoneGroup').style.display = type === 'Maintenance' ? 'block' : 'none';
}

// ── Add event ─────────────────────────────────────────────────
function addLocalEvent() {
    const title = document.getElementById('evtTitle').value.trim();
    const date  = document.getElementById('evtDate').value;
    const type  = document.getElementById('evtType').value;
    const notes = document.getElementById('evtNotes').value.trim();

    if (!title || !date) { showToast('Title and date are required.', true); return; }

    if (type === 'Cleaning' || type === 'Urgent Cleaning') {
        scheduleCleaning(type === 'Urgent Cleaning', date, notes);
        return;
    }

    if (type === 'Maintenance') {
        scheduleMaintenance(date, title, notes);
        return;
    }

    const color = typeColors[type] || '#7B0D0D';
    window._cal.addEvent({
        id:              'local-' + Date.now(),
        title:           title,
        start:           date,
        backgroundColor: color,
        borderColor:     color,
        extendedProps:   { type, notes, status: 'Scheduled' },
    });

    showToast('Event added to calendar.');
    closeAddModal();
    document.getElementById('evtTitle').value = '';
    document.getElementById('evtNotes').value = '';
}

// Persists a real Janitorial Monitoring assignment for the chosen zone/date
// and notifies the Janitorial account — not just a calendar-only note.
function scheduleCleaning(urgent, date, notes) {
    const zone = document.getElementById('evtZone').value;
    if (!zone) { showToast('Select a building/zone for the cleaning schedule.', true); return; }

    fetch('<?= base_url('calendar/scheduleCleaning') ?>', {
        method: 'POST',
        headers: csrfHeaders({ 'Content-Type': 'application/json' }),
        body: JSON.stringify({ zone, date, urgent, notes }),
    })
        .then(r => r.json().then(body => ({ ok: r.ok, body })))
        .then(({ ok, body }) => {
            if (!ok || !body.success) throw new Error(body.message || 'Could not schedule cleaning.');

            window._cal.addEvent(body.event);
            showToast(body.message);
            closeAddModal();
            document.getElementById('evtTitle').value = '';
            document.getElementById('evtNotes').value = '';
            document.getElementById('evtZone').value = '';
        })
        .catch(err => showToast(err.message, true));
}

// Persists a real Safety work order for the chosen building/date and notifies
// the Maintenance Team — not just a calendar-only note.
function scheduleMaintenance(date, issue, notes) {
    const location = document.getElementById('evtMaintZone').value;
    if (!location) { showToast('Select a building for the maintenance work order.', true); return; }
    if (!issue) { showToast('Event title is used as the work order description — please enter one.', true); return; }

    fetch('<?= base_url('calendar/scheduleMaintenance') ?>', {
        method: 'POST',
        headers: csrfHeaders({ 'Content-Type': 'application/json' }),
        body: JSON.stringify({ location, date, issue, notes }),
    })
        .then(r => r.json().then(body => ({ ok: r.ok, body })))
        .then(({ ok, body }) => {
            if (!ok || !body.success) throw new Error(body.message || 'Could not log maintenance work order.');

            window._cal.addEvent(body.event);
            showToast(body.message);
            closeAddModal();
            document.getElementById('evtTitle').value = '';
            document.getElementById('evtNotes').value = '';
            document.getElementById('evtMaintZone').value = '';
        })
        .catch(err => showToast(err.message, true));
}

function openAddModalOnDate(dateStr) {
    document.getElementById('evtDate').value = dateStr;
    document.getElementById('addEventModal').style.display = 'flex';
}
function closeAddModal() { document.getElementById('addEventModal').style.display = 'none'; }

// ── UBRA action stubs ──────────────────────────────────────────
function notifyDriver()   { showToast('Notification sent to Van-03 driver.'); }
function notifyCleaning() { showToast('Notification sent to Cleaning Personnel.'); }
function generateSummary(){ showToast('Weekly summary report is being generated…'); }

// ── Utility ────────────────────────────────────────────────────
function esc(s) {
    const d = document.createElement('div');
    d.textContent = String(s ?? '');
    return d.innerHTML;
}

function showToast(msg, isError = false) {
    const t = document.createElement('div');
    t.className = 'cal-toast' + (isError ? ' toast-error' : '');
    t.innerHTML = `<i class="bi bi-${isError ? 'exclamation-triangle' : 'check-circle-fill'}"></i> ${msg}`;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('toast-show'), 10);
    setTimeout(() => { t.classList.remove('toast-show'); setTimeout(() => t.remove(), 400); }, 3500);
}

// Flash auto-hide
setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => el.style.opacity = '0');
}, 4000);

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.style.display = 'none'; });
});
</script>

<?= $this->endSection() ?>