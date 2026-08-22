<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('Assets/css/ubra.css') . '?v=' . @filemtime(FCPATH.'Assets/css/ubra.css') ?>">

<div class="ubra-wrapper">

    <!-- ── PAGE HEADER ──────────────────────────────────────────── -->
    <div class="page-header">
        <div class="ubra-title-group">
            <div class="ubra-logo-badge">U</div>
            <div>
                <h1 class="page-title">Mr. UBRA</h1>
                <p class="page-subtitle">Foundation University's Intelligent Operations Assistant</p>
            </div>
        </div>
    </div>

    <!-- ── MAIN LAYOUT ───────────────────────────────────────────── -->
    <div class="ubra-body">

        <!-- ── LEFT: QUICK ACTIONS + CONTEXT PANEL ───────────────── -->
        <div class="ubra-left">

            <!-- Quick Action Buttons -->
            <div class="panel-card">
                <div class="panel-title"><i class="bi bi-lightning-charge-fill"></i> Quick Actions</div>
                <div class="quick-actions">
                    <button class="qa-btn" onclick="quickAction('daily_summary')">
                        <i class="bi bi-sun-fill"></i>
                        <span>Daily Summary</span>
                    </button>
                    <button class="qa-btn" onclick="quickAction('vehicle_health')">
                        <i class="bi bi-truck-front-fill"></i>
                        <span>Fleet Health</span>
                    </button>
                    <button class="qa-btn" onclick="quickAction('maintenance_due')">
                        <i class="bi bi-tools"></i>
                        <span>Maintenance Due</span>
                    </button>
                    <button class="qa-btn" onclick="quickAction('weekly_report')">
                        <i class="bi bi-file-earmark-bar-graph-fill"></i>
                        <span>Weekly Report</span>
                    </button>
                    <button class="qa-btn" onclick="quickAction('staff_summary')">
                        <i class="bi bi-people-fill"></i>
                        <span>Staff Summary</span>
                    </button>
                </div>
            </div>

            <!-- Context Panel -->
            <div class="panel-card">
                <div class="panel-title"><i class="bi bi-bar-chart-fill"></i> Today's Overview</div>
                <div class="ctx-grid" id="contextGrid">
                    <div class="ctx-item loading">Loading…</div>
                </div>
            </div>

            <!-- Suggested Prompts -->
            <div class="panel-card">
                <div class="panel-title"><i class="bi bi-chat-quote-fill"></i> Try Asking</div>
                <div class="suggestions">
                    <button class="suggest-btn" onclick="insertSuggestion('Which vehicles are currently in transit?')">
                        Which vehicles are currently in transit?
                    </button>
                    <button class="suggest-btn" onclick="insertSuggestion('Who is on duty today and any unassigned personnel?')">
                        Who is on duty today?
                    </button>
                    <button class="suggest-btn" onclick="insertSuggestion('Are there any overdue maintenance tasks?')">
                        Any overdue maintenance tasks?
                    </button>
                    <button class="suggest-btn" onclick="insertSuggestion('What assets are currently borrowed and overdue for return?')">
                        What assets are overdue for return?
                    </button>
                    <button class="suggest-btn" onclick="insertSuggestion('List all pending notifications that need action.')">
                        Pending notifications needing action
                    </button>
                </div>
            </div>

            <!-- AI Activity Panel -->
            <div class="panel-card">
                <div class="panel-title"><i class="bi bi-activity"></i> AI System Activity</div>
                <div class="activity-section">
                    <div class="act-label">Recent Recommendations</div>
                    <ul class="act-list" id="recentRecs">
                        <li><i class="bi bi-dot"></i> Automated report dispatched 10m ago</li>
                        <li><i class="bi bi-dot"></i> Personnel reassignment suggested</li>
                    </ul>
                </div>
                <div class="activity-section">
                    <div class="act-label">Recent Notifications</div>
                    <ul class="act-list">
                        <li><i class="bi bi-check-circle-fill text-success"></i> GPS tracker synced successfully</li>
                        <li><i class="bi bi-check-circle-fill text-success"></i> Maintenance alert acknowledged</li>
                    </ul>
                </div>
            </div>

        </div>

        <!-- ── CENTER: CHAT WINDOW ────────────────────────────────── -->
        <div class="ubra-chat">

            <!-- Chat header -->
            <div class="chat-header">
                <div class="chat-header-left">
                    <div class="ch-avatar">U</div>
                    <div>
                        <div class="ch-name">Mr. UBRA <span class="ch-pro">AI</span></div>
                        <div class="ch-status"><span class="pulse-dot"></span> Ready to assist</div>
                    </div>
                </div>
                <button class="clear-btn" onclick="clearChat()" title="Clear conversation">
                    <i class="bi bi-arrow-counterclockwise"></i> Clear
                </button>
            </div>

            <!-- Messages area -->
            <div class="chat-messages" id="chatMessages">
                <!-- Welcome message -->
                <div class="msg-row assistant">
                    <div class="msg-avatar">U</div>
                    <div class="msg-bubble">
                        <div class="msg-sender">Mr. UBRA <span class="msg-time"><?= date('h:i A') ?></span></div>
                        <div class="msg-text">
                            <strong>Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') ?>, Operations Office.</strong><br><br>
                            I'm Mr. UBRA, your Intelligent Operations Assistant. I have live access to your fleet, personnel, and asset data.<br><br>
                            How can I assist you today? Use the <strong>Quick Actions</strong> on the left for instant summaries, or ask me anything about UBRA operations.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input area -->
            <div class="chat-input-area">
                <div class="chat-input-wrap">
                    <textarea
                        id="chatInput"
                        class="chat-input"
                        placeholder="Ask Mr. UBRA about operations, vehicles, maintenance…"
                        rows="1"
                        onkeydown="handleKey(event)"
                        oninput="autoResize(this)"
                    ></textarea>
                    <button class="send-btn" id="sendBtn" onclick="sendMessage()">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
                <div class="input-hint">
                    Press <kbd>Enter</kbd> to send · <kbd>Shift+Enter</kbd> for new line · Responses powered by Anthropic Claude
                </div>
            </div>
        </div>

        <!-- ── RIGHT: QUICK LINKS + STATUS ───────────────────────── -->
        <div class="ubra-right">

            <div class="panel-card">
                <div class="panel-title"><i class="bi bi-grid-fill"></i> Quick Navigation</div>
                <div class="nav-links">
                    <?php
                    $navLinks = [
                        ['url'=>'dashboard',     'icon'=>'bi-speedometer2',        'label'=>'Dashboard'],
                        ['url'=>'personnel',     'icon'=>'bi-people-fill',         'label'=>'Personnel'],
                        ['url'=>'assets',        'icon'=>'bi-box-seam-fill',       'label'=>'Asset Management'],
                        ['url'=>'vehicles',      'icon'=>'bi-truck-front-fill',    'label'=>'Vehicle Management'],
                        ['url'=>'gps',           'icon'=>'bi-broadcast',           'label'=>'GPS Tracker'],
                        ['url'=>'calendar',      'icon'=>'bi-calendar3',           'label'=>'Calendar'],
                        ['url'=>'notifications', 'icon'=>'bi-bell-fill',           'label'=>'Notifications'],
                        ['url'=>'reports',       'icon'=>'bi-bar-chart-line-fill', 'label'=>'Information Hub'],
                        ['url'=>'settings',      'icon'=>'bi-gear-fill',           'label'=>'Settings'],
                    ];
                    foreach ($navLinks as $l):
                    ?>
                    <a href="<?= base_url($l['url']) ?>" class="nav-link-item">
                        <i class="bi <?= $l['icon'] ?>"></i>
                        <span><?= $l['label'] ?></span>
                        <i class="bi bi-chevron-right nav-arrow"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- UBRA capabilities -->
            <div class="panel-card cap-card">
                <div class="panel-title"><i class="bi bi-stars"></i> What Mr. UBRA Can Do</div>
                <ul class="cap-list">
                    <li><i class="bi bi-check-circle-fill"></i> Daily operations intelligence</li>
                    <li><i class="bi bi-check-circle-fill"></i> Fleet & GPS status summaries</li>
                    <li><i class="bi bi-check-circle-fill"></i> Maintenance alerts & scheduling</li>
                    <li><i class="bi bi-check-circle-fill"></i> Asset & borrowing overviews</li>
                    <li><i class="bi bi-check-circle-fill"></i> Personnel on-duty tracking</li>
                    <li><i class="bi bi-check-circle-fill"></i> Weekly report generation</li>
                    <li><i class="bi bi-check-circle-fill"></i> Notification triage</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<script>
// ── State ──────────────────────────────────────────────────────
let chatHistory = [];
let isLoading   = false;

const CHAT_URL          = '<?= base_url('ubra/chat') ?>';
const QUICK_URL         = '<?= base_url('ubra/quickAction') ?>';
const HISTORY_URL       = '<?= base_url('ubra/history') ?>';
const CLEAR_HISTORY_URL = '<?= base_url('ubra/clearHistory') ?>';

// ── Send message ───────────────────────────────────────────────
async function sendMessage(overrideText = null) {
    const input   = document.getElementById('chatInput');
    const message = overrideText || input.value.trim();
    if (!message || isLoading) return;

    appendMessage('user', message);
    chatHistory.push({ role: 'user', content: message });
    input.value = '';
    autoResize(input);

    setLoading(true);

    try {
        const fd = new FormData();
        fd.append('message', message);
        fd.append('history',  JSON.stringify(chatHistory.slice(-10)));

        const res  = await fetch(CHAT_URL, { method: 'POST', headers: csrfHeaders(), body: fd });
        const data = await res.json();
        const reply = data.reply || data.error || 'Sorry, something went wrong.';

        appendMessage('assistant', reply);
        chatHistory.push({ role: 'assistant', content: reply });

        // Refresh recent activity
        addRecentRec('Response generated · ' + new Date().toLocaleTimeString('en-PH', {hour:'2-digit',minute:'2-digit'}));

    } catch (err) {
        appendMessage('assistant', '⚠️ Connection error. Please check your network and try again.');
    } finally {
        setLoading(false);
    }
}

// ── Quick action ───────────────────────────────────────────────
async function quickAction(action) {
    if (isLoading) return;

    const labels = {
        daily_summary:  '📋 Give me today\'s daily operations summary.',
        vehicle_health: '🚛 What is the current fleet health and GPS status?',
        maintenance_due:'🔧 What maintenance tasks are coming up or overdue?',
        weekly_report:  '📊 Generate a brief weekly operations report.',
        staff_summary:  '👥 Who is on duty today and are there any unassigned personnel?',
    };

    const userText = labels[action] || 'Give me an operations overview.';
    appendMessage('user', userText);
    chatHistory.push({ role: 'user', content: userText });
    setLoading(true);

    try {
        const fd = new FormData();
        fd.append('action', action);

        const res  = await fetch(QUICK_URL, { method: 'POST', headers: csrfHeaders(), body: fd });
        const data = await res.json();
        const reply = data.reply || data.error || 'Sorry, something went wrong.';

        appendMessage('assistant', reply);
        chatHistory.push({ role: 'assistant', content: reply });

    } catch (err) {
        appendMessage('assistant', '⚠️ Connection error. Please try again.');
    } finally {
        setLoading(false);
    }
}

// ── DOM: Append message bubble ─────────────────────────────────
const CURRENT_USER_INITIAL = '<?= strtoupper(substr(session()->get("full_name") ?? "U", 0, 1)) ?>';

function appendMessage(role, text) {
    const container = document.getElementById('chatMessages');
    const time = new Date().toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });

    const row = document.createElement('div');
    row.className = 'msg-row ' + role;
    row.innerHTML = `
        ${role === 'assistant' ? '<div class="msg-avatar">U</div>' : ''}
        <div class="msg-bubble">
            <div class="msg-sender">
                ${role === 'assistant' ? 'Mr. UBRA' : 'You'}
                <span class="msg-time">${time}</span>
            </div>
            <div class="msg-text">${renderMarkdown(text)}</div>
        </div>
        ${role === 'user' ? `<div class="msg-avatar user-av">${CURRENT_USER_INITIAL}</div>` : ''}
    `;
    container.appendChild(row);
    container.scrollTop = container.scrollHeight;
}

// ── Loading state ──────────────────────────────────────────────
function setLoading(state) {
    isLoading = state;
    const btn   = document.getElementById('sendBtn');
    const input = document.getElementById('chatInput');
    btn.disabled   = state;
    input.disabled = state;

    // Remove existing typing indicator
    const existing = document.getElementById('typingIndicator');
    if (existing) existing.remove();

    if (state) {
        const container = document.getElementById('chatMessages');
        const indicator = document.createElement('div');
        indicator.id        = 'typingIndicator';
        indicator.className = 'msg-row assistant';
        indicator.innerHTML = `
            <div class="msg-avatar">U</div>
            <div class="msg-bubble typing-bubble">
                <div class="typing-dots">
                    <span></span><span></span><span></span>
                </div>
            </div>`;
        container.appendChild(indicator);
        container.scrollTop = container.scrollHeight;
    }
}

// ── Chat history (persisted server-side, so it survives navigating away
// and coming back — not just kept in this page's own memory) ──────────
async function loadChatHistory() {
    try {
        const res  = await fetch(HISTORY_URL, { headers: csrfHeaders() });
        const data = await res.json();
        const rows = data.history || [];
        if (rows.length === 0) return;

        document.getElementById('chatMessages').innerHTML = '';
        rows.forEach(row => {
            appendMessage(row.role, row.message);
            chatHistory.push({ role: row.role, content: row.message });
        });
    } catch (err) {
        // Leave the default welcome message in place if history can't load.
    }
}

// ── Clear chat ─────────────────────────────────────────────────
async function clearChat() {
    chatHistory = [];
    const container = document.getElementById('chatMessages');
    container.innerHTML = '';
    appendMessage('assistant',
        'Conversation cleared. How can I assist you with operations today?');

    try {
        await fetch(CLEAR_HISTORY_URL, { method: 'POST', headers: csrfHeaders() });
    } catch (err) {
        // The visible chat is already cleared either way; the server-side
        // log just might not be until the next successful attempt.
    }
}

// ── Insert suggestion ──────────────────────────────────────────
function insertSuggestion(text) {
    const input = document.getElementById('chatInput');
    input.value = text;
    autoResize(input);
    input.focus();
}

// ── Keyboard handler ───────────────────────────────────────────
function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

// ── Auto-resize textarea ───────────────────────────────────────
function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 140) + 'px';
}

// ── Light markdown renderer ────────────────────────────────────
function renderMarkdown(text) {
    return text
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
        .replace(/`(.+?)`/g, '<code>$1</code>')
        .replace(/^#{1,3} (.+)$/gm, '<strong>$1</strong>')
        .replace(/^[-•] (.+)$/gm, '<li>$1</li>')
        .replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>')
        .replace(/\n\n/g, '</p><p>')
        .replace(/\n/g, '<br>');
}

// ── Recent activity list ───────────────────────────────────────
function addRecentRec(text) {
    const list = document.getElementById('recentRecs');
    const li   = document.createElement('li');
    li.innerHTML = `<i class="bi bi-dot"></i> ${text}`;
    list.insertBefore(li, list.firstChild);
    if (list.children.length > 4) list.removeChild(list.lastChild);
}

// ── Load context snapshot ──────────────────────────────────────
async function loadContext() {
    // Pull stats from the reports chart endpoint as a lightweight context source
    try {
        const res  = await fetch('<?= base_url('reports/chartData') ?>');
        const data = await res.json();
        renderContext(data);
    } catch (e) {
        document.getElementById('contextGrid').innerHTML =
            '<div class="ctx-item"><div class="ctx-label">Status</div><div class="ctx-value">Live</div></div>';
    }
}

function renderContext(data) {
    // Fallback static context — real values come from PHP-rendered page
    document.getElementById('contextGrid').innerHTML = `
        <div class="ctx-item">
            <div class="ctx-icon"><i class="bi bi-truck"></i></div>
            <div class="ctx-label">Vehicles</div>
            <div class="ctx-value" id="ctxVehicles">—</div>
        </div>
        <div class="ctx-item">
            <div class="ctx-icon"><i class="bi bi-people"></i></div>
            <div class="ctx-label">On Duty</div>
            <div class="ctx-value" id="ctxDuty">—</div>
        </div>
        <div class="ctx-item">
            <div class="ctx-icon"><i class="bi bi-bell"></i></div>
            <div class="ctx-label">Alerts</div>
            <div class="ctx-value" id="ctxAlerts">—</div>
        </div>
    `;
}

// Init
loadContext();
loadChatHistory();
document.getElementById('chatInput').focus();

// Flash auto-hide
setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => el.style.opacity = '0');
}, 4000);
</script>

<?= $this->endSection() ?>