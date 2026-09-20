<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('Assets/css/ubra.css') . '?v=' . @filemtime(FCPATH.'Assets/css/ubra.css') ?>">

<div class="ubra-wrapper">
  <div class="ubra-panel">

    <!-- ── HEADER ─────────────────────────────────────────────── -->
    <div class="ubra-accent-bar"></div>
    <div class="ubra-panel-header">
      <div class="ubra-header-text">
        <div class="ubra-eyebrow">UBRA INTELLIGENCE</div>
        <h1 class="ubra-title">Mr. UBRA</h1>
        <p class="ubra-subtext"><span class="pulse-dot"></span> Operations assistant &middot; online</p>
      </div>
      <div class="ubra-header-actions">
        <button type="button" class="ubra-clear-btn" onclick="openChatHistory()">History</button>
        <button type="button" class="ubra-clear-btn" onclick="clearChat()">Clear</button>
      </div>
    </div>

    <!-- ── BODY: rail + conversation ─────────────────────────── -->
    <div class="ubra-body">

      <!-- Left rail: icon-only quick actions -->
      <div class="ubra-rail">
        <button type="button" class="ubra-rail-btn" title="Fleet health check" aria-label="Fleet health check" onclick="quickPrompt('Give me a fleet health check — vehicle status, GPS status, and anything needing attention.')">
          <i class="bi bi-truck"></i>
        </button>
        <button type="button" class="ubra-rail-btn" title="Who is on duty today?" aria-label="Who is on duty today?" onclick="quickPrompt('Who is on duty today?')">
          <i class="bi bi-people"></i>
        </button>
        <button type="button" class="ubra-rail-btn" title="Any unassigned personnel?" aria-label="Any unassigned personnel?" onclick="quickPrompt('Are there any unassigned personnel right now?')">
          <i class="bi bi-person-dash"></i>
        </button>
        <button type="button" class="ubra-rail-btn" title="Weekly report" aria-label="Weekly report" onclick="quickPrompt('Generate a brief weekly operations report.')">
          <i class="bi bi-file-earmark-text"></i>
        </button>
      </div>

      <!-- Conversation thread -->
      <div class="ubra-convo">
        <div class="ubra-messages" id="chatMessages">
          <div class="ubra-greeting">Good <?= date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening') ?>, Operations Office.</div>
        </div>
      </div>
    </div>

    <!-- ── FOOTER: input bar ─────────────────────────────────── -->
    <div class="ubra-panel-footer">
      <div class="ubra-input-bar">
        <input
          type="text"
          id="chatInput"
          class="ubra-input"
          placeholder="Ask Mr. UBRA anything..."
          onkeydown="handleKey(event)"
          oninput="clearInputError()"
        >
        <button type="button" class="ubra-send-btn" id="sendBtn" onclick="sendMessage()">
          Send <i class="bi bi-arrow-right"></i>
        </button>
      </div>
      <div class="ubra-input-error" id="inputError">Type a message before sending.</div>
    </div>

  </div>
</div>

<!-- CHAT HISTORY MODAL — same wide "crosswise" popup treatment used
     elsewhere (Vehicle Management, Personnel Management, GPS Tracker). -->
<div class="modal" id="ubraHistoryModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3>Chat History</h3>
      <button type="button" class="modal-close-btn" onclick="closeChatHistory()" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body" id="ubraHistoryBody"></div>
  </div>
</div>

<script>
// ── State ──────────────────────────────────────────────────────
let chatHistory = [];
let isLoading   = false;

const CHAT_URL          = '<?= base_url('ubra/chat') ?>';
const HISTORY_URL       = '<?= base_url('ubra/history') ?>';
const CLEAR_HISTORY_URL = '<?= base_url('ubra/clearHistory') ?>';
const CURRENT_USER_INITIAL = '<?= strtoupper(substr(session()->get("full_name") ?? "U", 0, 1)) ?>';

// ── Send message ───────────────────────────────────────────────
async function sendMessage(overrideText = null) {
    const input   = document.getElementById('chatInput');
    const message = overrideText || input.value.trim();

    if (!message) {
        if (!overrideText) showInputError();
        return;
    }
    if (isLoading) return;

    clearInputError();
    appendMessage('user', message);
    chatHistory.push({ role: 'user', content: message });
    input.value = '';

    setLoading(true);

    try {
        const fd = new FormData();
        fd.append('message', message);
        fd.append('history',  JSON.stringify(chatHistory.slice(-10)));

        const res  = await fetch(CHAT_URL, { method: 'POST', headers: csrfHeaders(), body: fd });
        const data = await res.json();
        const reply = data.reply || data.error || 'Sorry, something went wrong.';

        appendMessage('assistant', reply, data.download || null);
        chatHistory.push({ role: 'assistant', content: reply });

    } catch (err) {
        appendMessage('assistant', 'Connection error. Please check your network and try again.');
    } finally {
        setLoading(false);
    }
}

// ── Quick prompt (left rail icons) — sends immediately, same as typing
// and submitting that question. ──────────────────────────────────
function quickPrompt(text) {
    if (isLoading) return;
    sendMessage(text);
}

// ── Input validation ──────────────────────────────────────────
function showInputError() {
    document.getElementById('inputError').classList.add('visible');
    document.getElementById('chatInput').classList.add('input-error');
}
function clearInputError() {
    document.getElementById('inputError').classList.remove('visible');
    document.getElementById('chatInput').classList.remove('input-error');
}

// ── DOM: Append message bubble ─────────────────────────────────
// Every bubble gets a stable, ever-increasing data-msg-index so the History
// popup (which lists the same persisted turns) can point back at the exact
// bubble in the live thread — that's what lets "click a history row" jump
// you back into the live conversation instead of just reading a flat log.
let msgIndex = 0;

function appendMessage(role, text, download = null) {
    const container = document.getElementById('chatMessages');
    const time = new Date().toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });

    const row = document.createElement('div');
    row.className = 'ubra-msg-row ' + role;
    row.dataset.msgIndex = msgIndex++;

    if (role === 'assistant') {
        row.innerHTML = `
            <div class="ubra-avatar">U</div>
            <div class="ubra-msg-col">
                <div class="ubra-bubble assistant">${renderMarkdown(text)}</div>
                <div class="ubra-msg-time">${time}</div>
            </div>`;
    } else {
        row.innerHTML = `
            <div class="ubra-msg-col">
                <div class="ubra-bubble user">${renderMarkdown(text)}</div>
                <div class="ubra-msg-time">${time}</div>
            </div>`;
    }

    // A real, working file link (report requests) — opens in its own tab,
    // same "pop up" treatment as the Calendar's Generate Summary, instead of
    // navigating the chat away.
    if (download && download.url) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ubra-download-btn';
        btn.innerHTML = `<i class="bi bi-download"></i> ${esc(download.label || 'Download report')}`;
        btn.addEventListener('click', () => window.open(download.url, '_blank'));
        row.querySelector('.ubra-msg-col').appendChild(btn);
    }

    container.appendChild(row);
    container.scrollTop = container.scrollHeight;
}

// ── Loading state ──────────────────────────────────────────────
function setLoading(state) {
    isLoading = state;
    document.getElementById('sendBtn').disabled = state;
    document.getElementById('chatInput').disabled = state;

    const existing = document.getElementById('typingIndicator');
    if (existing) existing.remove();

    if (state) {
        const container = document.getElementById('chatMessages');
        const indicator = document.createElement('div');
        indicator.id        = 'typingIndicator';
        indicator.className = 'ubra-msg-row assistant';
        indicator.innerHTML = `
            <div class="ubra-avatar">U</div>
            <div class="ubra-msg-col">
                <div class="ubra-bubble assistant ubra-typing">
                    <span></span><span></span><span></span>
                </div>
            </div>`;
        container.appendChild(indicator);
        container.scrollTop = container.scrollHeight;
    }
}

// ── Chat history (persisted server-side) ───────────────────────
// Cache of the last rows fetched from the server, keyed in the same order
// used to assign data-msg-index — resumeFromHistory() uses this to rebuild
// the live thread on demand if it isn't populated yet (e.g. History gets
// opened before the page's own initial load finishes), instead of silently
// failing to find a bubble to jump to.
let historyRowsCache = [];

function renderLiveThreadFromRows(rows) {
    document.getElementById('chatMessages').querySelectorAll('.ubra-msg-row').forEach(el => el.remove());
    msgIndex = 0;
    chatHistory = [];
    rows.forEach(row => {
        appendMessage(row.role, row.message);
        chatHistory.push({ role: row.role, content: row.message });
    });
}

async function loadChatHistory() {
    try {
        const res  = await fetch(HISTORY_URL, { headers: csrfHeaders() });
        const data = await res.json();
        const rows = data.history || [];
        historyRowsCache = rows;
        if (rows.length === 0) return;
        renderLiveThreadFromRows(rows);
    } catch (err) {
        // Leave the default greeting in place if history can't load.
    }
}

function esc(s) {
    const d = document.createElement('div');
    d.textContent = String(s ?? '');
    return d.innerHTML;
}

// ── Chat History popup — the full persisted log with timestamps, as
// its own reviewable list instead of just whatever's currently scrolled
// into view in the live conversation above. ─────────────────────
async function openChatHistory() {
    const body = document.getElementById('ubraHistoryBody');
    body.innerHTML = `<div class="no-data">Loading chat history...</div>`;
    document.getElementById('ubraHistoryModal').style.display = 'flex';

    try {
        const res  = await fetch(HISTORY_URL, { headers: csrfHeaders() });
        const data = await res.json();
        const rows = data.history || [];
        historyRowsCache = rows;

        body.innerHTML = rows.length
            ? `<div class="history-table-wrap">
                 <table class="history-table">
                   <thead><tr><th>Time</th><th>Speaker</th><th>Message</th></tr></thead>
                   <tbody>${rows.map((r, i) => `
                     <tr onclick="resumeFromHistory(${i})" title="Click to jump back to this point in the conversation">
                       <td>${esc(r.created_at)}</td>
                       <td>${r.role === 'assistant' ? 'Mr. UBRA' : 'You'}</td>
                       <td>${esc(r.message)}</td>
                     </tr>`).join('')}</tbody>
                 </table>
               </div>`
            : `<div class="no-data">No chat history yet.</div>`;
    } catch (err) {
        body.innerHTML = `<div class="no-data">Could not load chat history. Please try again.</div>`;
    }
}

function closeChatHistory() {
    document.getElementById('ubraHistoryModal').style.display = 'none';
}

// ── Resume from a History row — closes the popup, scrolls the live thread
// (which already holds the full persisted conversation) to that exact
// bubble, flashes it, and hands focus back to the input so typing continues
// right where you stopped instead of starting over. ─────────────────────
function resumeFromHistory(index) {
    closeChatHistory();

    // The live thread may not be populated yet (History opened before the
    // page's own initial load finished) or may be out of sync — rebuild it
    // from the same rows History just showed before trying to jump, instead
    // of silently giving up when the bubble isn't found.
    const liveCount = document.querySelectorAll('#chatMessages .ubra-msg-row').length;
    if (liveCount !== historyRowsCache.length) {
        renderLiveThreadFromRows(historyRowsCache);
    }

    const target = document.querySelector(`#chatMessages [data-msg-index="${index}"]`);
    if (!target) {
        document.getElementById('chatInput').focus();
        return;
    }

    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    const bubble = target.querySelector('.ubra-bubble');
    if (bubble) {
        bubble.classList.remove('resume-highlight');
        void bubble.offsetWidth; // restart animation if clicked again
        bubble.classList.add('resume-highlight');
    }
    document.getElementById('chatInput').focus();
}

// ── Clear chat — resets back to the initial greeting state ─────
async function clearChat() {
    chatHistory = [];
    document.getElementById('chatMessages').querySelectorAll('.ubra-msg-row').forEach(el => el.remove());
    clearInputError();

    try {
        await fetch(CLEAR_HISTORY_URL, { method: 'POST', headers: csrfHeaders() });
    } catch (err) {
        // The visible chat is already cleared either way.
    }
}

// ── Keyboard handler ───────────────────────────────────────────
function handleKey(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        sendMessage();
    }
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

// Init
loadChatHistory();
document.getElementById('chatInput').focus();
</script>

<?= $this->endSection() ?>
