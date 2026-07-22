<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('Assets/css/settings.css') ?>">

<div class="stg-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-file-shield"></i> Records & Archiving</h1>
            <p class="page-subtitle">Manage, archive, and dispose of operational records</p>
        </div>
        <a href="<?= base_url('records/export/csv') ?>" class="btn-add">
            <i class="bi bi-download"></i> Export All
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert-success" style="margin-bottom: 20px;"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert-error" style="margin-bottom: 20px;"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <nav class="stg-nav" style="margin-bottom: 20px; position: static;">
        <button class="stg-nav-btn active" data-tab="borrow" onclick="switchRecordTab('borrow')">
            <i class="bi bi-box"></i> Borrow Logs
        </button>
        <button class="stg-nav-btn" data-tab="travel" onclick="switchRecordTab('travel')">
            <i class="bi bi-route"></i> Trip Tickets
        </button>
        <button class="stg-nav-btn" data-tab="reports" onclick="switchRecordTab('reports')">
            <i class="bi bi-file-earmark-text"></i> Reports
        </button>
        <button class="stg-nav-btn" data-tab="disposal" onclick="switchRecordTab('disposal')">
            <i class="bi bi-trash3"></i> Disposal Log
        </button>
    </nav>

    <div class="stg-content">
        <!-- Borrow Logs Tab -->
        <div id="tab-borrow" class="tab-pane active">
            <div class="tab-title-row">
                <div class="tab-title">Borrow Logs</div>
            </div>
            <table class="stg-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Asset</th>
                        <th>Borrower</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Archived</th>
                        <th>Disposal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($borrowRecords)): ?>
                        <tr><td colspan="8" class="empty-row">No records found</td></tr>
                    <?php else: ?>
                        <?php foreach ($borrowRecords as $r): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td><?= $r['tool_id'] ?? '—' ?></td>
                            <td><?= $r['borrower'] ?? '—' ?></td>
                            <td><?= $r['borrowed_date'] ?? '—' ?></td>
                            <td><span class="badge <?= $r['status'] == 'Borrowed' ? 'amber' : 'green' ?>"><?= $r['status'] ?? '—' ?></span></td>
                            <td><?= $r['is_archived'] ? '<span class="badge green">Yes</span>' : '<span class="badge">No</span>' ?></td>
                            <td>
                                <span class="badge <?= $r['disposal_status'] == 'Disposed' ? 'red' : ($r['disposal_status'] == 'For Disposal' ? 'amber' : '') ?>">
                                    <?= $r['disposal_status'] ?? 'None' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!$r['is_archived'] && $r['disposal_status'] == 'None'): ?>
                                    <form method="post" action="<?= base_url('records/markForDisposal/borrow/' . $r['id']) ?>" style="display:inline;">
                                        <button type="submit" class="btn-sm">Mark for Disposal</button>
                                    </form>
                                <?php elseif ($r['disposal_status'] == 'For Disposal'): ?>
                                    <button class="btn-sm" onclick="openAuthorizeModal('borrow', <?= $r['id'] ?>)">Authorize</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Trip Tickets Tab -->
        <div id="tab-travel" class="tab-pane">
            <div class="tab-title-row">
                <div class="tab-title">Trip Tickets</div>
            </div>
            <table class="stg-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Trip ID</th>
                        <th>Requester</th>
                        <th>Destination</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Archived</th>
                        <th>Disposal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($travelRecords)): ?>
                        <tr><td colspan="9" class="empty-row">No records found</td></tr>
                    <?php else: ?>
                        <?php foreach ($travelRecords as $r): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td><?= $r['trip_id'] ?? '—' ?></td>
                            <td><?= $r['requester'] ?? '—' ?></td>
                            <td><?= $r['destination'] ?? '—' ?></td>
                            <td><?= $r['travel_date'] ?? '—' ?></td>
                            <td><span class="badge <?= $r['status'] == 'Completed' ? 'green' : ($r['status'] == 'Approved' ? 'blue' : 'amber') ?>"><?= $r['status'] ?? '—' ?></span></td>
                            <td><?= $r['is_archived'] ? '<span class="badge green">Yes</span>' : '<span class="badge">No</span>' ?></td>
                            <td>
                                <span class="badge <?= $r['disposal_status'] == 'Disposed' ? 'red' : ($r['disposal_status'] == 'For Disposal' ? 'amber' : '') ?>">
                                    <?= $r['disposal_status'] ?? 'None' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!$r['is_archived'] && $r['disposal_status'] == 'None'): ?>
                                    <form method="post" action="<?= base_url('records/markForDisposal/travel/' . $r['id']) ?>" style="display:inline;">
                                        <button type="submit" class="btn-sm">Mark for Disposal</button>
                                    </form>
                                <?php elseif ($r['disposal_status'] == 'For Disposal'): ?>
                                    <button class="btn-sm" onclick="openAuthorizeModal('travel', <?= $r['id'] ?>)">Authorize</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Reports Tab -->
        <div id="tab-reports" class="tab-pane">
            <div class="tab-title-row">
                <div class="tab-title">Reports</div>
            </div>
            <table class="stg-table">
                <thead>
                    <tr>
                        <th>Report Name</th>
                        <th>Generated By</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Archived</th>
                        <th>Disposal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportRecords)): ?>
                        <tr><td colspan="7" class="empty-row">No reports found</td></tr>
                    <?php else: ?>
                        <?php foreach ($reportRecords as $r): ?>
                        <tr>
                            <td class="rpt-name"><?= esc($r['report_name']) ?></td>
                            <td><?= esc($r['generated_by']) ?></td>
                            <td><?= isset($r['created_at']) ? date('M j, Y', strtotime($r['created_at'])) : '—' ?></td>
                            <td><span class="badge"><?= esc($r['type_module'] ?? 'General') ?></span></td>
                            <td><?= $r['is_archived'] ? '<span class="badge green">Yes</span>' : '<span class="badge">No</span>' ?></td>
                            <td>
                                <span class="badge <?= $r['disposal_status'] == 'Disposed' ? 'red' : ($r['disposal_status'] == 'For Disposal' ? 'amber' : '') ?>">
                                    <?= $r['disposal_status'] ?? 'None' ?>
                                </span>
                            </td>
                            <td>
                                <button class="action-btn action-btn-secondary open-report-view"
                                    data-id="<?= esc($r['id']) ?>"
                                    data-report_name="<?= esc($r['report_name']) ?>"
                                    data-generated_by="<?= esc($r['generated_by']) ?>"
                                    data-type_module="<?= esc($r['type_module'] ?? 'General') ?>"
                                    data-date_range="<?= esc($r['date_range'] ?? 'Last 30 Days') ?>">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <a href="<?= base_url('reports/download/' . (int)$r['id']) ?>" class="action-btn action-btn-download" style="background: #28a745; color: white;">
                                    <i class="bi bi-download"></i> Download
                                </a>
                                <?php if (!$r['is_archived'] && $r['disposal_status'] == 'None'): ?>
                                    <form method="post" action="<?= base_url('records/markForDisposal/report/' . $r['id']) ?>" style="display:inline;">
                                        <button type="submit" class="btn-sm">Mark for Disposal</button>
                                    </form>
                                <?php elseif ($r['disposal_status'] == 'For Disposal'): ?>
                                    <button class="btn-sm" onclick="openAuthorizeModal('report', <?= $r['id'] ?>)">Authorize</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Disposal Log Tab -->
        <div id="tab-disposal" class="tab-pane">
            <div class="tab-title-row">
                <div class="tab-title">Disposal Log</div>
            </div>
            <table class="stg-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Record Type</th>
                        <th>Record ID</th>
                        <th>Authorized By</th>
                        <th>Date</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($disposalLogs)): ?>
                        <tr><td colspan="6" class="empty-row">No disposal records</td></tr>
                    <?php else: ?>
                        <?php foreach ($disposalLogs as $log): ?>
                        <tr>
                            <td><?= $log['id'] ?></td>
                            <td><?= ucfirst($log['record_type']) ?></td>
                            <td><?= $log['record_id'] ?></td>
                            <td><?= $log['authorized_by'] ?></td>
                            <td><?= $log['authorized_at'] ?></td>
                            <td><?= $log['notes'] ?? '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Authorize Disposal Modal -->
<div id="authorizeModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3><i class="bi bi-shield-check"></i> Authorize Disposal</h3>
            <button class="modal-close" onclick="closeAuthorizeModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="authorizeForm" method="post" action="<?= base_url('records/authorizeDisposal') ?>">
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

<!-- Report View/Edit Modal -->
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
                        <option>Travel Operations</option>
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
let isDrawing = false;
let lastX = 0;
let lastY = 0;
const canvas = document.getElementById('signatureCanvas');
const ctx = canvas.getContext('2d');

function switchRecordTab(tab) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.stg-nav-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    document.querySelector('[data-tab="' + tab + '"]').classList.add('active');
}

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
    draw({clientX: touch.clientX - rect.left, clientY: touch.clientY - rect.top});
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

document.querySelectorAll('.open-report-view').forEach(btn => {
    btn.addEventListener('click', () => openReportView(btn));
});

document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.style.display = 'none'; });
});

window.addEventListener('load', () => {
    if (document.readyState === 'complete') {
        initCanvas();
    }
});
</script>

<?= $this->endSection() ?>
