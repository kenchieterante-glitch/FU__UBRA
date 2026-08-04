<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$stats = $stats ?? ['total_records' => 0, 'archived_records' => 0, 'reports_generated' => 0, 'today_activities' => 0];
$activities = $activities ?? [];
$slug = fn($s) => strtolower(str_replace(' ', '-', trim((string) $s)));
?>

<div class="rec-wrapper">

    <!-- ── PAGE HEADER ──────────────────────────────────────────── -->
    <div class="rec-header">
        <div>
            <h1 class="rec-title">Records, Archiving &amp; Reports</h1>
            <p class="rec-subtitle">View, manage and generate reports for all modules in one place.</p>
        </div>
        <div class="rec-breadcrumb">
            <a href="<?= base_url('dashboard') ?>">Home</a> / <span>Records, Archiving &amp; Reports</span>
        </div>
    </div>

    <!-- ── STATS CARDS ──────────────────────────────────────────── -->
    <div class="rec-stats-grid">
        <div class="rec-stat-card accent-blue">
            <div class="rec-stat-icon"><i class="bi bi-clipboard-data-fill"></i></div>
            <div class="rec-stat-body">
                <div class="rec-stat-value"><?= (int) $stats['total_records'] ?></div>
                <div class="rec-stat-label">Total Records</div>
                <div class="rec-stat-sub">All modules</div>
            </div>
        </div>
        <div class="rec-stat-card accent-green">
            <div class="rec-stat-icon"><i class="bi bi-archive-fill"></i></div>
            <div class="rec-stat-body">
                <div class="rec-stat-value"><?= (int) $stats['archived_records'] ?></div>
                <div class="rec-stat-label">Archived Records</div>
                <div class="rec-stat-sub">All modules</div>
            </div>
        </div>
        <div class="rec-stat-card accent-amber">
            <div class="rec-stat-icon"><i class="bi bi-file-earmark-bar-graph-fill"></i></div>
            <div class="rec-stat-body">
                <div class="rec-stat-value"><?= (int) $stats['reports_generated'] ?></div>
                <div class="rec-stat-label">Reports Generated</div>
                <div class="rec-stat-sub">All modules</div>
            </div>
        </div>
        <div class="rec-stat-card accent-purple">
            <div class="rec-stat-icon"><i class="bi bi-clock-history"></i></div>
            <div class="rec-stat-body">
                <div class="rec-stat-value"><?= (int) $stats['today_activities'] ?></div>
                <div class="rec-stat-label">Today's Activities</div>
                <div class="rec-stat-sub">All modules</div>
            </div>
        </div>
    </div>

    <!-- ── FILTER BAR ───────────────────────────────────────────── -->
    <div class="rec-filter-bar">
        <div class="rec-filter-fields">
            <div class="rec-icon-filter" id="moduleFilterGroup" data-target="module">
                <button type="button" class="rec-icon-btn active" data-value="" title="All Modules"><i class="bi bi-grid-fill"></i></button>
                <button type="button" class="rec-icon-btn" data-value="tools" title="Tools"><i class="bi bi-wrench-adjustable"></i></button>
                <button type="button" class="rec-icon-btn" data-value="vehicle" title="Vehicle"><i class="bi bi-truck"></i></button>
                <button type="button" class="rec-icon-btn" data-value="safety" title="Safety"><i class="bi bi-shield-check"></i></button>
                <button type="button" class="rec-icon-btn" data-value="janitorial" title="Janitorial"><i class="bi bi-brush"></i></button>
                <button type="button" class="rec-icon-btn" data-value="personnel" title="Personnel"><i class="bi bi-people-fill"></i></button>
            </div>
            <div class="rec-icon-filter" id="kindFilterGroup" data-target="kind">
                <button type="button" class="rec-icon-btn active" data-value="" title="All Types"><i class="bi bi-collection"></i></button>
                <button type="button" class="rec-icon-btn" data-value="record" title="Records"><i class="bi bi-file-earmark-text"></i></button>
                <button type="button" class="rec-icon-btn" data-value="archive" title="Archived"><i class="bi bi-archive"></i></button>
                <button type="button" class="rec-icon-btn" data-value="report" title="Reports"><i class="bi bi-file-earmark-bar-graph"></i></button>
            </div>
            <select id="statusFilter" onchange="filterTable()">
                <option value="">All Status</option>
                <option value="borrowed">Borrowed</option>
                <option value="returned">Returned</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="generated">Generated</option>
                <option value="archived">Archived</option>
                <option value="disposed">Disposed</option>
            </select>
            <div class="rec-date-range">
                <input type="date" id="dateFilter" onchange="filterTable()" aria-label="Date">
            </div>
        </div>
        <div class="rec-filter-actions">
            <div class="rec-search-wrap">
                <input type="text" id="searchInput" placeholder="Search records…" oninput="filterTable()">
                <i class="bi bi-search search-icon"></i>
            </div>
            <button type="button" class="rec-btn-outline" onclick="resetFilters()"><i class="bi bi-arrow-clockwise"></i> Reset</button>
        </div>
    </div>

    <!-- ── RECENT ACTIVITY HISTORY ──────────────────────────────── -->
    <div class="rec-table-card">
        <div class="rec-table-head">
            <div class="rec-table-title"><i class="bi bi-list-ul"></i> Recent Activity History</div>
            <div class="rec-table-actions">
                <a href="<?= base_url('records/export/excel') ?>" class="rec-btn-outline"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
                <a href="<?= base_url('records/export/pdf') ?>" class="rec-btn-outline"><i class="bi bi-file-earmark-pdf"></i> Export PDF</a>
                <button type="button" class="rec-btn-dark" onclick="filterArchivedOnly()"><i class="bi bi-archive"></i> Archive</button>
            </div>
        </div>

        <div class="table-scroll">
            <table class="rec-table" id="activityTable">
                <thead>
                    <tr>
                        <th>Date &amp; Time</th>
                        <th>Module</th>
                        <th>Type</th>
                        <th>Record</th>
                        <th>Action</th>
                        <th>Performed By</th>
                        <th>Status</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($activities)): ?>
                        <tr><td colspan="8" class="empty-row">No activity recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($activities as $act):
                            $modSlug    = $slug($act['module']);
                            $kindSlug   = $slug($act['kind']);
                            $actionSlug = $slug($act['action']);
                            $statusSlug = $slug($act['status']);
                            $dateVal    = $act['date'] ?? null;
                            $dateKey    = $dateVal ? date('Y-m-d', strtotime($dateVal)) : '';
                            $searchBlob = strtolower($act['module'] . ' ' . $act['record'] . ' ' . $act['record_sub'] . ' ' . $act['performed_by'] . ' ' . $act['action'] . ' ' . $act['status']);
                            $modIcon = match ($act['module']) {
                                'Tools'      => 'bi-wrench-adjustable',
                                'Vehicle'    => 'bi-truck',
                                'Safety'     => 'bi-shield-check',
                                'Janitorial' => 'bi-brush',
                                'Personnel'  => 'bi-people-fill',
                                default      => 'bi-folder-fill',
                            };
                            $kindIcon = match ($act['kind']) {
                                'Archive' => 'bi-archive-fill',
                                'Report'  => 'bi-file-earmark-bar-graph-fill',
                                default   => 'bi-file-earmark-text-fill',
                            };
                        ?>
                        <tr
                            data-type="<?= esc($act['type']) ?>"
                            data-module="<?= esc($modSlug) ?>"
                            data-kind="<?= esc($kindSlug) ?>"
                            data-status="<?= esc($statusSlug) ?>"
                            data-date="<?= esc($dateKey) ?>"
                            data-search="<?= esc($searchBlob) ?>"
                        >
                            <td>
                                <div class="datetime-cell">
                                    <span><?= $dateVal ? date('M j, Y', strtotime($dateVal)) : '—' ?></span>
                                    <span class="time-muted"><?= $dateVal ? date('h:i A', strtotime($dateVal)) : '' ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="mod-pill mod-<?= esc($modSlug) ?>"><i class="bi <?= $modIcon ?>"></i> <?= esc($act['module']) ?></span>
                            </td>
                            <td>
                                <span class="kind-pill kind-<?= esc($kindSlug) ?>"><i class="bi <?= $kindIcon ?>"></i> <?= esc($act['kind']) ?></span>
                            </td>
                            <td>
                                <div class="rec-record-name"><?= esc($act['record']) ?></div>
                                <div class="rec-record-sub"><?= esc($act['record_sub']) ?></div>
                            </td>
                            <td><span class="act-text act-<?= esc($actionSlug) ?>"><?= esc($act['action']) ?></span></td>
                            <td><?= esc($act['performed_by']) ?></td>
                            <td><span class="stat-pill stat-<?= esc($statusSlug) ?>"><?= esc($act['status']) ?></span></td>
                            <td>
                                <?php if ($act['type'] === 'report'): ?>
                                    <button type="button" class="rec-view-btn open-report-view"
                                        data-id="<?= esc($act['id']) ?>"
                                        data-report_name="<?= esc($act['record']) ?>"
                                        data-generated_by="<?= esc($act['performed_by']) ?>"
                                        data-type_module="<?= esc($act['record_sub']) ?>"
                                        data-date_range="Last 30 Days">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="rec-view-btn open-record-detail"
                                        data-type="<?= esc($act['type']) ?>"
                                        data-id="<?= esc($act['id']) ?>"
                                        data-date="<?= $dateVal ? esc(date('M j, Y', strtotime($dateVal))) : '—' ?>"
                                        data-module="<?= esc($act['module']) ?>"
                                        data-kind="<?= esc($act['kind']) ?>"
                                        data-record="<?= esc($act['record']) ?>"
                                        data-record_sub="<?= esc($act['record_sub']) ?>"
                                        data-action="<?= esc($act['action']) ?>"
                                        data-performed_by="<?= esc($act['performed_by']) ?>"
                                        data-status="<?= esc($act['status']) ?>"
                                        data-is_archived="<?= $act['is_archived'] ? '1' : '0' ?>"
                                        data-disposal_status="<?= esc($act['disposal_status']) ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="rec-table-footer">
            <div id="entriesLabel">Showing 0 of 0 entries</div>
            <div class="rec-pagination" id="paginationButtons"></div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     MODAL: RECORD DETAIL (Tools / Vehicle / Disposal rows)
════════════════════════════════════════════════════════════════ -->
<div id="recordDetailModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3><i class="bi bi-info-circle"></i> Record Details</h3>
            <button class="modal-close" onclick="closeRecordDetail()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <div class="rec-detail-grid" id="recordDetailBody"></div>
        </div>
        <div class="modal-footer" id="recordDetailActions"></div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     MODAL: AUTHORIZE DISPOSAL
════════════════════════════════════════════════════════════════ -->
<div id="authorizeModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3><i class="bi bi-shield-check"></i> Authorize Disposal</h3>
            <button class="modal-close" onclick="closeAuthorizeModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="authorizeForm" method="post" action="<?= base_url('records/authorizeDisposal') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <input type="hidden" id="authType" name="type">
                <input type="hidden" id="authId" name="id">
                <div class="form-group">
                    <label>Disposal Date <span class="req">*</span></label>
                    <input type="date" name="disposal_date" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Digital Signature (Draw Below) <span class="req">*</span></label>
                    <canvas id="signatureCanvas" style="border:1px solid #ddd; border-radius:8px; width:100%; height:150px; background:#fff;"></canvas>
                    <input type="hidden" id="signatureData" name="signature" required>
                    <button type="button" onclick="clearSignature()" style="margin-top:10px; padding:5px 10px;">Clear</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAuthorizeModal()">Cancel</button>
                <button type="submit" class="btn-maroon">Authorize</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     MODAL: REPORT VIEW/EDIT
════════════════════════════════════════════════════════════════ -->
<div id="reportEditorModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-md">
        <div class="modal-header">
            <h3 id="reportEditorTitle"><i class="bi bi-pencil-square"></i> View Report</h3>
            <button class="modal-close" type="button" onclick="closeReportEditor()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="post" action="<?= base_url('reports/update') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="report_id" id="editorReportId">
            <div class="modal-body">
                <div class="form-group">
                    <label>Report Name</label>
                    <input type="text" name="report_name" id="editorReportName" required>
                </div>
                <div class="form-group">
                    <label>Generated By</label>
                    <input type="text" name="generated_by" id="editorGeneratedBy" required>
                </div>
                <div class="form-group">
                    <label>Type / Module</label>
                    <select name="type_module" id="editorTypeModule" required>
                        <option>Facilities Management</option>
                        <option>Asset Inventory</option>
                        <option>Vehicle Fleet</option>
                        <option>Personnel</option>
                        <option>Maintenance Compliance</option>
                        <option>Janitorial Performance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date Range</label>
                    <select name="date_range" id="editorDateRange" required>
                        <option>Last 30 Days</option>
                        <option>Last 90 Days</option>
                        <option>Last 6 Months</option>
                        <option>This Year</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeReportEditor()">Cancel</button>
                <button type="submit" class="btn-submit"><i class="bi bi-floppy"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
const BASE_URL = '<?= base_url() ?>';

// ── Filtering ────────────────────────────────────────────────────
let currentPage = 1;
const PAGE_SIZE = 10;

function getIconFilterValue(groupId) {
    const active = document.querySelector(`#${groupId} .rec-icon-btn.active`);
    return active ? active.dataset.value : '';
}

function setIconFilterValue(groupId, value) {
    document.querySelectorAll(`#${groupId} .rec-icon-btn`).forEach(btn => {
        btn.classList.toggle('active', btn.dataset.value === value);
    });
}

document.querySelectorAll('.rec-icon-filter').forEach(group => {
    group.addEventListener('click', e => {
        const btn = e.target.closest('.rec-icon-btn');
        if (!btn) return;
        group.querySelectorAll('.rec-icon-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        filterTable();
    });
});

function filterTable() {
    const mod    = getIconFilterValue('moduleFilterGroup');
    const kind   = getIconFilterValue('kindFilterGroup');
    const status = document.getElementById('statusFilter').value;
    const date   = document.getElementById('dateFilter').value;
    const term   = document.getElementById('searchInput').value.toLowerCase().trim();

    document.querySelectorAll('#activityTable tbody tr[data-type]').forEach(row => {
        const matchMod    = !mod || row.dataset.module === mod;
        const matchKind   = !kind || row.dataset.kind === kind;
        const matchStatus = !status || row.dataset.status === status;
        const matchDate   = !date || row.dataset.date === date;
        const matchTerm   = !term || row.dataset.search.includes(term);
        const matches = matchMod && matchKind && matchStatus && matchDate && matchTerm;
        row.classList.toggle('filtered-out', !matches);
    });

    currentPage = 1;
    applyPagination();
}

function resetFilters() {
    setIconFilterValue('moduleFilterGroup', '');
    setIconFilterValue('kindFilterGroup', '');
    document.getElementById('statusFilter').value = '';
    document.getElementById('dateFilter').value = '';
    document.getElementById('searchInput').value = '';
    filterTable();
}

function filterArchivedOnly() {
    setIconFilterValue('kindFilterGroup', 'archive');
    filterTable();
    document.querySelector('.rec-table-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── Pagination (client-side, over already-filtered rows) ─────────
function applyPagination() {
    const rows = Array.from(document.querySelectorAll('#activityTable tbody tr[data-type]'))
        .filter(r => !r.classList.contains('filtered-out'));
    const total = rows.length;
    const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
    if (currentPage > totalPages) currentPage = totalPages;

    rows.forEach((row, i) => {
        const page = Math.floor(i / PAGE_SIZE) + 1;
        row.style.display = (page === currentPage) ? '' : 'none';
    });

    document.querySelectorAll('#activityTable tbody tr[data-type].filtered-out').forEach(row => {
        row.style.display = 'none';
    });

    const start = total === 0 ? 0 : (currentPage - 1) * PAGE_SIZE + 1;
    const end   = Math.min(currentPage * PAGE_SIZE, total);
    document.getElementById('entriesLabel').textContent =
        total === 0 ? 'No entries found' : `Showing ${start} to ${end} of ${total} entries`;

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

    addBtn('»', Math.min(totalPages, currentPage + 1), { disabled: currentPage === totalPages });
}

// ── Record detail modal (Tools / Vehicle / Disposal) ──────────────
function openRecordDetail(btn) {
    const d = btn.dataset;
    const rows = [
        ['Date & Time', d.date],
        ['Module', d.module],
        ['Type', d.kind],
        ['Record', d.record],
        ['Reference', d.record_sub],
        ['Action', d.action],
        ['Performed By', d.performed_by],
        ['Status', d.status],
    ];
    document.getElementById('recordDetailBody').innerHTML = rows.map(([label, val]) =>
        `<div class="rec-detail-row"><span>${label}</span><strong>${val || '—'}</strong></div>`
    ).join('');

    const actions = document.getElementById('recordDetailActions');
    actions.innerHTML = '';

    if (d.type !== 'disposal') {
        if (d.is_archived !== '1' && d.disposal_status === 'None') {
            const form = document.createElement('form');
            form.method = 'post';
            form.action = `${BASE_URL}records/markForDisposal/${d.type}/${d.id}`;
            form.innerHTML = '<button type="submit" class="btn-cancel">Mark for Disposal</button>';
            actions.appendChild(form);
        } else if (d.disposal_status === 'For Disposal') {
            const btn2 = document.createElement('button');
            btn2.type = 'button';
            btn2.className = 'btn-maroon';
            btn2.innerHTML = '<i class="bi bi-shield-check"></i> Authorize Disposal';
            btn2.onclick = () => { closeRecordDetail(); openAuthorizeModal(d.type, d.id); };
            actions.appendChild(btn2);
        }
    }

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'btn-cancel';
    closeBtn.textContent = 'Close';
    closeBtn.onclick = closeRecordDetail;
    actions.appendChild(closeBtn);

    document.getElementById('recordDetailModal').style.display = 'flex';
}

function closeRecordDetail() {
    document.getElementById('recordDetailModal').style.display = 'none';
}

document.querySelectorAll('.open-record-detail').forEach(btn => {
    btn.addEventListener('click', () => openRecordDetail(btn));
});

// ── Authorize Disposal modal + signature pad ──────────────────────
let isDrawing = false;
let lastX = 0;
let lastY = 0;
const canvas = document.getElementById('signatureCanvas');
const ctx = canvas.getContext('2d');

function openAuthorizeModal(type, id) {
    document.getElementById('authType').value = type;
    document.getElementById('authId').value = id;
    document.getElementById('authorizeModal').style.display = 'flex';
    clearSignature();
    initCanvas();
}

function closeAuthorizeModal() {
    document.getElementById('authorizeModal').style.display = 'none';
}

function initCanvas() {
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;
    ctx.strokeStyle = '#1a1a1a';
    ctx.lineWidth = 2;
}

canvas.addEventListener('mousedown', startDraw);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDraw);
canvas.addEventListener('mouseout', stopDraw);

canvas.addEventListener('touchstart', (e) => {
    const touch = e.touches[0];
    const rect = canvas.getBoundingClientRect();
    isDrawing = true;
    lastX = touch.clientX - rect.left;
    lastY = touch.clientY - rect.top;
});
canvas.addEventListener('touchmove', (e) => {
    e.preventDefault();
    const touch = e.touches[0];
    const rect = canvas.getBoundingClientRect();
    draw({ clientX: touch.clientX - rect.left, clientY: touch.clientY - rect.top });
});
canvas.addEventListener('touchend', stopDraw);

function startDraw(e) {
    isDrawing = true;
    const rect = canvas.getBoundingClientRect();
    lastX = e.clientX - rect.left;
    lastY = e.clientY - rect.top;
}

function draw(e) {
    if (!isDrawing) return;
    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX || e.offsetX) - rect.left;
    const y = (e.clientY || e.offsetY) - rect.top;

    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
    ctx.lineTo(x, y);
    ctx.stroke();

    lastX = x;
    lastY = y;

    document.getElementById('signatureData').value = canvas.toDataURL('image/png');
}

function stopDraw() {
    isDrawing = false;
}

function clearSignature() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    document.getElementById('signatureData').value = '';
}

// ── Report view/edit modal ────────────────────────────────────────
function openReportView(button) {
    const editor = document.getElementById('reportEditorModal');
    document.getElementById('editorReportId').value = button.dataset.id || '';
    document.getElementById('editorReportName').value = button.dataset.report_name || '';
    document.getElementById('editorGeneratedBy').value = button.dataset.generated_by || '';
    document.getElementById('editorTypeModule').value = button.dataset.type_module || 'General';
    document.getElementById('editorDateRange').value = button.dataset.date_range || 'Last 30 Days';
    document.getElementById('reportEditorTitle').innerHTML = '<i class="bi bi-eye"></i> View / Edit Report';
    editor.style.display = 'flex';
}

function closeReportEditor() {
    document.getElementById('reportEditorModal').style.display = 'none';
}

document.querySelectorAll('.open-report-view').forEach(btn => {
    btn.addEventListener('click', () => openReportView(btn));
});

document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.style.display = 'none'; });
});

window.addEventListener('load', () => {
    if (document.readyState === 'complete') initCanvas();
});

// Flash auto-hide
setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => el.style.opacity = '0');
}, 4000);

// Initial pagination render
applyPagination();
</script>

<?= $this->endSection() ?>
