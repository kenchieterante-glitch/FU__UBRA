<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('Assets/css/safety.css') ?>">

<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle">Overview of guard activity, shifts, key issuance, and dashboard metrics.</p>
  </div>
</div>

<div class="guard-grid">
  <div class="guard-card">
    <div class="pane-header">
      <div class="pane-title"><i class="bi bi-person-badge-fill"></i> Security Guard Dashboard</div>
      <div class="pane-actions">
        <span class="guard-shift">On Duty: <strong>Guard Santos, J.</strong></span>
        <span class="guard-time" id="guardClock"></span>
      </div>
    </div>

    <div class="gc-title"><i class="bi bi-ticket-perforated-fill"></i> Trip Tickets Released Today</div>
    <div class="table-wrap">
      <table class="sj-table">
        <thead>
          <tr><th>Ticket #</th><th>Requester</th><th>Vehicle</th><th>Destination</th><th>Departure</th><th>Approved By</th><th>Status</th></tr>
        </thead>
        <tbody id="tripTicketBody"></tbody>
      </table>
    </div>
  </div>

  <div>
    <div class="guard-card">
      <div class="gc-title"><i class="bi bi-key-fill"></i> Currently Borrowed Keys</div>
      <div id="activeBorrows" class="borrow-cards"></div>
    </div>

    <div class="guard-card guard-card-narrow">
      <div class="gc-title"><i class="bi bi-activity"></i> Guard Activity Log</div>
      <ul class="act-log" id="guardActLog"></ul>
    </div>
  </div>
</div>

<script>
const tripTickets = [
  { no:'TT-2026-090', requester:'Dr. Jose Rizal', vehicle:'Van FUA-8802', dest:'Dumaguete IT Hub', dep:'08:30 AM', approvedBy:'Dir. Santos', status:'Approved' },
  { no:'TT-2026-091', requester:'Prof. Terante, K.', vehicle:'Bus FUA-8801', dest:'Silliman University', dep:'10:00 AM', approvedBy:'VP Gomez', status:'Released' },
  { no:'TT-2026-092', requester:'Dean Samson', vehicle:'Hilux FUA-4311', dest:'Valencia Campus', dep:'01:00 PM', approvedBy:'Dir. Santos', status:'Pending' },
];

const keyLogs = [
  { id:'KL-001', name:'Dela Cruz, J.', dept:'Library', key:'Library Storeroom Key', inTime:'07:30 AM', status:'Returned' },
  { id:'KL-002', name:'Magsaysay, R.', dept:'CCS', key:'Server Room Key', inTime:'08:15 AM', status:'Active' },
  { id:'KL-004', name:'Reyes, A.', dept:'Admin', key:'Admin Filing Room Key', inTime:'10:45 AM', status:'Active' },
];

const guardActLog = [
  { time:'10:45 AM', action:'Key KL-004 issued to Reyes, A. (Admin)' },
  { time:'09:00 AM', action:'Key KL-003 issued to Torres, F. (Science)' },
  { time:'10:30 AM', action:'Key KL-003 returned by Torres, F.' },
  { time:'08:15 AM', action:'Key KL-002 issued to Magsaysay, R. (CCS)' },
  { time:'07:30 AM', action:'Key KL-001 issued to Dela Cruz, J. (Library)' },
  { time:'12:00 PM', action:'Key KL-001 returned by Dela Cruz, J.' },
  { time:'07:00 AM', action:'Guard Santos, J. clocked in — Day Shift' },
];

function renderGuardDashboard() {
  document.getElementById('tripTicketBody').innerHTML = tripTickets.map(t => `
    <tr>
      <td><strong>${t.no}</strong></td>
      <td>${t.requester}</td>
      <td>${t.vehicle}</td>
      <td>${t.dest}</td>
      <td>${t.dep}</td>
      <td>${t.approvedBy}</td>
      <td><span class="tt-badge tt-${t.status.toLowerCase()}">${t.status}</span></td>
    </tr>`).join('');

  const active = keyLogs.filter(k => k.status === 'Active');
  document.getElementById('activeBorrows').innerHTML = active.length
    ? active.map(k => `
      <div class="borrow-card">
        <div class="bc-av">${k.name.charAt(0)}</div>
        <div class="bc-info">
          <div class="bc-name">${k.name}</div>
          <div class="bc-dept">${k.dept}</div>
          <div class="bc-key"><i class="bi bi-key-fill"></i> ${k.key}</div>
          <div class="bc-time"><i class="bi bi-clock"></i> Since ${k.inTime}</div>
        </div>
      </div>`).join('')
    : '<div class="no-data">No active key borrows.</div>';

  document.getElementById('guardActLog').innerHTML = guardActLog.map(l => `
    <li><span class="al-time">${l.time}</span> ${l.action}</li>`).join('');

  updateGuardClock();
}

function updateGuardClock() {
  const el = document.getElementById('guardClock');
  if (el) el.textContent = new Date().toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
}

setInterval(updateGuardClock, 1000);
window.addEventListener('DOMContentLoaded', renderGuardDashboard);
</script>

<?= $this->endSection() ?>
