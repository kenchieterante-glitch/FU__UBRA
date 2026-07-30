<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('Assets/css/calendar.css') ?>">
<!-- FullCalendar 6 (CDN) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.css" rel="stylesheet">

<div class="cal-wrapper">

    <!-- ── PAGE HEADER ──────────────────────────────────────────── -->
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-calendar3"></i> Operations Calendar</h1>
            <p class="page-subtitle">Manage travel schedules, inspections, maintenance, and operational activities.</p>
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

    <!-- ── LEGEND ────────────────────────────────────────────────── -->
    <div class="cal-legend">
        <span class="legend-item"><span class="legend-dot" style="background:#d97706"></span> Travel</span>
        <span class="legend-item"><span class="legend-dot" style="background:#f59e0b"></span> Inspection</span>
        <span class="legend-item"><span class="legend-dot" style="background:#7c3aed"></span> Maintenance</span>
        <span class="legend-item"><span class="legend-dot" style="background:#2563eb"></span> Compliance</span>
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
                <?php if (empty($today_trips)): ?>
                    <div class="empty-day"><i class="bi bi-calendar-check"></i> No trips scheduled today.</div>
                <?php else: ?>
                    <?php foreach ($today_trips as $t): ?>
                    <div class="today-event travel">
                        <div class="te-time"><?= date('h:i A', strtotime($t['departure_time'])) ?></div>
                        <div class="te-body">
                            <div class="te-title">Trip Ticket #<?= esc($t['trip_id']) ?></div>
                            <div class="te-sub"><?= date('h:i A', strtotime($t['departure_time'])) ?> – <?= esc($t['destination']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Upcoming Events -->
            <div class="sidebar-card">
                <div class="sc-title">Upcoming Events</div>
                <?php if (empty($approved_trips)): ?>
                    <div class="empty-day">No upcoming approved trips.</div>
                <?php else: ?>
                    <?php foreach (array_slice($approved_trips, 0, 4) as $t): ?>
                    <div class="upcoming-item">
                        <span class="up-dot" style="background:#16a34a"></span>
                        <div>
                            <div class="up-title"><?= esc($t['destination']) ?></div>
                            <div class="up-sub"><?= date('M j', strtotime($t['travel_date'])) ?> · <?= esc($t['requester_name']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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

            <!-- Pending Approvals -->
            <div class="sidebar-card">
                <div class="sc-title">
                    Pending Approvals
                    <?php if (!empty($pending_trips)): ?>
                        <span class="pending-count"><?= count($pending_trips) ?></span>
                    <?php endif; ?>
                </div>
                <?php if (empty($pending_trips)): ?>
                    <div class="empty-day">No pending approvals.</div>
                <?php else: ?>
                    <?php foreach ($pending_trips as $t): ?>
                    <div class="pending-item">
                        <div>
                            <div class="pi-title">Driver Assignment (<?= esc($t['vehicle_plate'] ?? 'TBD') ?>)</div>
                            <div class="pi-sub"><?= date('M j', strtotime($t['travel_date'])) ?> · <?= esc($t['requester_name']) ?></div>
                        </div>
                        <a href="<?= base_url('travel') ?>" class="pi-action">Review →</a>
                    </div>
                    <?php endforeach; ?>
                    <div class="pending-item">
                        <div><div class="pi-title">Vehicle Insurance Renewal</div><div class="pi-sub">Scheduled renewal</div></div>
                        <span class="pi-action">Authorize →</span>
                    </div>
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
                    <div class="edp-badge" id="edpBadge">Travel</div>
                    <span class="edp-status" id="edpStatus">Active</span>
                </div>
                <div class="edp-title" id="edpTitle">—</div>
                <div class="edp-meta-grid" id="edpMeta"></div>
                <div class="edp-actions">
                    <button class="btn-outline-sm" id="edpNotifyBtn"><i class="bi bi-bell"></i> Notify Driver</button>
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
                </div>
                <div class="ubra-section-title">Daily Operations Summary</div>
                <ul class="ubra-list">
                    <?php if (!empty($today_trips)): ?>
                        <li><i class="bi bi-dot"></i> <?= count($today_trips) ?> trip(s) scheduled to depart today.</li>
                    <?php else: ?>
                        <li><i class="bi bi-dot"></i> No trips scheduled today.</li>
                    <?php endif; ?>
                    <?php if (!empty($pending_trips)): ?>
                        <li><i class="bi bi-dot"></i> <?= count($pending_trips) ?> driver assignment(s) pending approval.</li>
                    <?php endif; ?>
                    <li><i class="bi bi-dot"></i> Bldg A AC cleaning starts in 2 days.</li>
                    <li><i class="bi bi-dot"></i> 1 maintenance schedule due next week.</li>
                </ul>
                <div class="ubra-section-title" style="margin-top:.8rem;">Suggested Actions</div>
                <div class="ubra-btns">
                    <button class="ubra-btn" onclick="notifyDriver()"><i class="bi bi-person-fill"></i> Notify Driver (Van-03)</button>
                    <button class="ubra-btn" onclick="notifyCleaning()"><i class="bi bi-brush"></i> Notify Cleaning Personnel</button>
                    <button class="ubra-btn" onclick="generateSummary()"><i class="bi bi-file-earmark-text"></i> Generate Weekly Summary</button>
                    <button class="ubra-btn primary" onclick="openTripSchedule()"><i class="bi bi-truck"></i> Open Trip Schedule</button>
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
                    <select id="evtType">
                        <option value="Travel">Travel</option>
                        <option value="Inspection">Inspection</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Compliance">Compliance</option>
                    </select>
                </div>
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
    Travel:      '#d97706',
    Inspection:  '#f59e0b',
    Maintenance: '#7c3aed',
    Compliance:  '#2563eb',
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
    });

    cal.render();
    window._cal = cal;   // expose for view switcher

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
    const type = p.type || 'Travel';
    const statusClass = {
        Approved: 'status-approved', Completed: 'status-completed',
        Cancelled: 'status-cancelled', Pending: 'status-pending'
    }[p.status] || 'status-pending';

    document.getElementById('edpBadge').textContent   = type;
    document.getElementById('edpBadge').className     = 'edp-badge edp-' + type.toLowerCase();
    document.getElementById('edpStatus').textContent  = p.status || 'Active';
    document.getElementById('edpStatus').className    = 'edp-status ' + statusClass;
    document.getElementById('edpTitle').textContent   = event.title;

    const start = event.start ? event.start.toLocaleDateString('en-PH', {month:'short',day:'numeric',year:'numeric'}) : '—';
    const time  = event.start ? event.start.toLocaleTimeString('en-PH', {hour:'2-digit',minute:'2-digit'}) : '—';

    document.getElementById('edpMeta').innerHTML = `
        <div class="edp-row"><span>Date</span><strong>${start}</strong></div>
        <div class="edp-row"><span>Time</span><strong>${time}</strong></div>
        ${p.requester ? `<div class="edp-row"><span>Requester</span><strong>${esc(p.requester)}</strong></div>` : ''}
        ${p.driver    ? `<div class="edp-row"><span>Driver</span><strong>${esc(p.driver)}</strong></div>` : ''}
        ${p.vehicle   ? `<div class="edp-row"><span>Vehicle</span><strong>${esc(p.vehicle.trim())}</strong></div>` : ''}
        ${p.purpose   ? `<div class="edp-row full"><span>Purpose</span><strong>${esc(p.purpose)}</strong></div>` : ''}
    `;

    document.getElementById('eventDetailPanel').style.display = 'block';
}

// ── Add local event ────────────────────────────────────────────
function addLocalEvent() {
    const title = document.getElementById('evtTitle').value.trim();
    const date  = document.getElementById('evtDate').value;
    const type  = document.getElementById('evtType').value;
    const notes = document.getElementById('evtNotes').value.trim();

    if (!title || !date) { showToast('Title and date are required.', true); return; }

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

function openAddModalOnDate(dateStr) {
    document.getElementById('evtDate').value = dateStr;
    document.getElementById('addEventModal').style.display = 'flex';
}
function closeAddModal() { document.getElementById('addEventModal').style.display = 'none'; }

// ── UBRA action stubs ──────────────────────────────────────────
function notifyDriver()   { showToast('Notification sent to Van-03 driver.'); }
function notifyCleaning() { showToast('Notification sent to Cleaning Personnel.'); }
function generateSummary(){ showToast('Weekly summary report is being generated…'); }
function openTripSchedule(){ window.location.href = '<?= base_url('travel') ?>'; }

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