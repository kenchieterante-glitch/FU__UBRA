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
      <button type="button" class="ubra-clear-btn" onclick="clearChat()">Clear</button>
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

        appendMessage('assistant', reply);
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
function appendMessage(role, text) {
    const container = document.getElementById('chatMessages');
    const time = new Date().toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });

    const row = document.createElement('div');
    row.className = 'ubra-msg-row ' + role;

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
async function loadChatHistory() {
    try {
        const res  = await fetch(HISTORY_URL, { headers: csrfHeaders() });
        const data = await res.json();
        const rows = data.history || [];
        if (rows.length === 0) return;

        document.getElementById('chatMessages').querySelectorAll('.ubra-msg-row').forEach(el => el.remove());
        rows.forEach(row => {
            appendMessage(row.role, row.message);
            chatHistory.push({ role: row.role, content: row.message });
        });
    } catch (err) {
        // Leave the default greeting in place if history can't load.
    }
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
