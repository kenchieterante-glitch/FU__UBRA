<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('Assets/css/ubra.css') . '?v=' . @filemtime(FCPATH.'Assets/css/ubra.css') ?>">

<div class="ubra-wrapper">
  <div class="ubra-panel">

    <!-- ── HEADER ─────────────────────────────────────────────── -->
    <div class="ubra-accent-bar"></div>
    <div class="ubra-panel-header">
      <div class="ubra-header-id">
        <div class="ubra-header-avatar"><i class="bi bi-robot"></i></div>
        <div class="ubra-header-text">
          <h1 class="ubra-title">Mr. UBRA</h1>
          <p class="ubra-subtext"><span class="pulse-dot"></span> Online &middot; Operations assistant</p>
        </div>
      </div>
      <div class="ubra-header-actions">
        <button type="button" class="ubra-icon-btn" id="speakToggle" onclick="toggleSpeakReplies()" title="Read Mr. UBRA's answers aloud" aria-label="Voice replies" aria-pressed="false"><i class="bi bi-volume-mute" id="speakIcon"></i></button>
        <button type="button" class="ubra-icon-btn" onclick="openChatHistory()" title="Chat history" aria-label="Chat history"><i class="bi bi-clock-history"></i></button>
        <button type="button" class="ubra-icon-btn" onclick="clearChat()" title="Clear chat" aria-label="Clear chat"><i class="bi bi-trash3"></i></button>
      </div>
    </div>

    <!-- ── BODY: rail + conversation ─────────────────────────── -->
    <div class="ubra-body">

      <!-- Left rail: icon-only quick actions -->
      <div class="ubra-rail">
        <?php if (!empty($quick_actions)): foreach ($quick_actions as [$qIcon, $qTitle, $qPrompt]): ?>
        <button type="button" class="ubra-rail-btn" title="<?= esc($qTitle) ?>" aria-label="<?= esc($qTitle) ?>" onclick="quickPrompt(<?= esc(json_encode($qPrompt), 'attr') ?>)">
          <i class="bi <?= esc($qIcon) ?>"></i>
        </button>
        <?php endforeach; else: ?>
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
        <?php endif; ?>
      </div>

      <!-- Conversation thread -->
      <div class="ubra-convo">
        <div class="ubra-messages" id="chatMessages">
          <div class="ubra-welcome">
            <div class="ubra-orb" aria-hidden="true"><span class="ring r1"></span><span class="ring r2"></span><span class="core"><i class="bi bi-robot"></i></span></div>
            <?php $firstName = trim(explode(' ', trim((string) ($greet_name ?? 'there')))[0]) ?: 'there'; ?>
            <p class="ubra-welcome-hello">Good <?= date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening') ?></p>
            <h2 class="ubra-welcome-title">Hello, <span class="grad"><?= esc($firstName) ?></span>.</h2>
            <p class="ubra-welcome-sub">I'm Mr. UBRA. Ask me about your operations and I'll answer from your live UBRA data.</p>
            <div class="ubra-badges">
              <span><i class="bi bi-lightning-charge-fill"></i> Live data</span>
              <span><i class="bi bi-file-earmark-arrow-down"></i> Report downloads</span>
              <span><i class="bi bi-mic-fill"></i> Voice input</span>
            </div>
          </div>
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
        <button type="button" class="ubra-mic-btn" id="micBtn" onclick="toggleMic()" title="Speak your question" aria-label="Speak your question" style="display:none"><i class="bi bi-mic-fill"></i></button>
        <button type="button" class="ubra-send-btn" id="sendBtn" onclick="sendMessage()" title="Send" aria-label="Send">
          <i class="bi bi-arrow-up"></i>
        </button>
      </div>
      <div class="ubra-input-error" id="inputError">Type a message before sending.</div>
      <div class="ubra-voice-hint" id="voiceHint" style="display:none"></div>
      <div class="ubra-suggest">
        <?php
          $suggest = !empty($quick_actions) ? $quick_actions : [
              ['bi-truck', 'Fleet health check', 'Give me a fleet health check — vehicle status, GPS status, and anything needing attention.'],
              ['bi-people', 'Who is on duty today?', 'Who is on duty today?'],
              ['bi-person-dash', 'Any unassigned personnel?', 'Are there any unassigned personnel right now?'],
              ['bi-file-earmark-text', 'Weekly report', 'Generate a brief weekly operations report.'],
          ];
          $descs = [
              'Fleet health check' => 'Vehicle and GPS status at a glance',
              'Who is on duty today?' => 'See who is working right now',
              'Any unassigned personnel?' => 'Find people without an assignment',
              'Weekly report' => 'A short summary you can download',
          ];
          foreach ($suggest as [$sIcon, $sTitle, $sPrompt]): ?>
        <button type="button" class="ubra-suggest-card" onclick="quickPrompt(<?= esc(json_encode($sPrompt), 'attr') ?>)">
          <span class="ubra-suggest-icon"><i class="bi <?= esc($sIcon) ?>"></i></span>
          <span class="ubra-suggest-body">
            <span class="ubra-suggest-text"><?= esc($sTitle) ?></span>
            <span class="ubra-suggest-desc"><?= esc($descs[$sTitle] ?? 'Ask Mr. UBRA about this') ?></span>
          </span>
          <i class="bi bi-arrow-up-right ubra-suggest-go"></i>
        </button>
        <?php endforeach; ?>
      </div>
      <p class="ubra-disclaimer">Mr. UBRA can make mistakes. Please verify important information.</p>
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
// Rotating example questions in the input placeholder (only while the box is empty and unfocused).
(function () {
    const inp = document.getElementById('chatInput');
    if (!inp) return;
    const tips = ['Ask Mr. UBRA anything...', 'Who is on duty today?', 'Give me a fleet health check', 'Which fire extinguishers are overdue?', 'Generate a weekly operations report'];
    let i = 0;
    setInterval(function () {
        if (document.activeElement === inp || inp.value) return;
        i = (i + 1) % tips.length;
        inp.placeholder = tips[i];
    }, 3200);
})();

// ── State ──────────────────────────────────────────────────────
let chatHistory = [];
let isLoading   = false;

const CHAT_URL          = '<?= base_url('ubra/chat') ?>';
const HISTORY_URL       = '<?= base_url('ubra/history') ?>';
const CLEAR_HISTORY_URL = '<?= base_url('ubra/clearHistory') ?>';
const CURRENT_USER_INITIAL = '<?= strtoupper(substr(session()->get("full_name") ?? "U", 0, 1)) ?>';

// ── Voice: speak your question (mic) and hear the answer (Voice replies) ─────────
// Typing keeps working exactly as before; the mic is an extra choice. Uses the
// browser's built-in speech features (Chrome / Edge), so no extra service is needed.
let speakReplies = false, askedByVoice = false, lastVoiceText = '', recognizer = null, listening = false;
try { speakReplies = localStorage.getItem('ubra_speak') === '1'; } catch (e) {}
const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;

function paintSpeakToggle() {
    const b = document.getElementById('speakToggle'); if (!b) return;
    b.setAttribute('aria-pressed', speakReplies ? 'true' : 'false');
    document.getElementById('speakIcon').className = 'bi ' + (speakReplies ? 'bi-volume-up-fill' : 'bi-volume-mute');
    b.title = speakReplies ? 'Voice replies ON - click to turn off' : 'Voice replies OFF - click to turn on';
    b.classList.toggle('active', speakReplies);
}
function toggleSpeakReplies() {
    speakReplies = !speakReplies;
    try { localStorage.setItem('ubra_speak', speakReplies ? '1' : '0'); } catch (e) {}
    paintSpeakToggle();
    if (speakReplies) speakText('Voice replies are on.'); else stopSpeaking();
}
// Say "UBRA" as a word (oo-bra), not letter by letter.
function sayable(t) { return String(t).replace(/\bU\.?B\.?R\.?A\.?\b/gi, 'Oobrah').replace(/\bFU[- ]?Oobrah\b/g, 'F U Oobrah'); }
function stopSpeaking() { try { window.speechSynthesis && speechSynthesis.cancel(); } catch (e) {} }
function speakText(text) {
    if (!('speechSynthesis' in window)) return;
    const plain = String(text)
        .replace(/```[\s\S]*?```/g, ' ')
        .replace(/\[([^\]]+)\]\([^)]+\)/g, '$1')
        .replace(/[*_#>`|]/g, ' ')
        .replace(/\s+/g, ' ').trim()
        .slice(0, 600); // keep spoken answers short
    if (!plain) return;
    try {
        stopSpeaking();
        const u = new SpeechSynthesisUtterance(sayable(plain));
        u.lang = 'en-US'; u.rate = 0.97;
        speechSynthesis.speak(u);
    } catch (e) {}
}
function voiceHint(msg) {
    const h = document.getElementById('voiceHint'); if (!h) return;
    h.textContent = msg || ''; h.style.display = msg ? 'block' : 'none';
}
function toggleMic() {
    if (!SpeechRec || isLoading) return;
    if (listening) { try { recognizer.stop(); } catch (e) {} return; }
    stopSpeaking();
    recognizer = new SpeechRec();
    recognizer.lang = 'en-US';
    recognizer.interimResults = true;
    recognizer.maxAlternatives = 1;
    let finalText = '';
    const input = document.getElementById('chatInput');
    recognizer.onstart = () => { listening = true; document.getElementById('micBtn').classList.add('listening'); voiceHint('Listening... speak your question.'); };
    recognizer.onresult = (e) => {
        let interim = '';
        for (let i = e.resultIndex; i < e.results.length; i++) {
            const t = e.results[i][0].transcript;
            if (e.results[i].isFinal) finalText += t; else interim += t;
        }
        input.value = (finalText + interim).trim();
    };
    recognizer.onerror = (e) => {
        const msgs = { 'not-allowed': 'Microphone is blocked. Allow it in the browser address bar, or just type.', 'service-not-allowed': 'Microphone is blocked. Allow it in the browser address bar, or just type.', 'no-speech': 'I did not hear anything. Try again or type.', 'audio-capture': 'No microphone found. You can still type.', 'network': 'Voice needs an internet connection. You can still type.' };
        voiceHint(msgs[e.error] || 'Voice did not work. You can still type.');
    };
    recognizer.onend = () => {
        listening = false; document.getElementById('micBtn').classList.remove('listening');
        const said = (finalText || input.value).trim();
        if (said) { voiceHint(''); lastVoiceText = said; sendMessage(said); }
    };
    try { recognizer.start(); } catch (e) { voiceHint('Voice did not start. You can still type.'); }
}
(function initVoice() {
    paintSpeakToggle();
    if (!SpeechRec) { voiceHint('Voice input needs Chrome or Edge. You can still type.'); return; }
    document.getElementById('micBtn').style.display = '';
})();

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
    stopSpeaking();
    askedByVoice = !!overrideText && overrideText === lastVoiceText; // a spoken question gets a spoken answer
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
        if (speakReplies || askedByVoice) speakText(reply);

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
            <div class="ubra-avatar"><i class="bi bi-robot"></i></div>
            <div class="ubra-msg-col">
                <div class="ubra-bubble assistant">${renderMarkdown(text)}</div>
                <div class="ubra-msg-meta"><span class="ubra-msg-time">${time}</span><button type="button" class="ubra-copy-btn" title="Copy answer" aria-label="Copy answer"><i class="bi bi-clipboard"></i> Copy</button></div>
            </div>`;
        row.querySelector('.ubra-copy-btn').addEventListener('click', function () {
            const btn = this, plain = row.querySelector('.ubra-bubble').innerText;
            const done = () => { btn.innerHTML = '<i class="bi bi-check2"></i> Copied'; setTimeout(() => { btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy'; }, 1500); };
            if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(plain).then(done, done); else done();
        });
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
            <div class="ubra-avatar"><i class="bi bi-robot"></i></div>
            <div class="ubra-msg-col">
                <div class="ubra-bubble assistant ubra-typing" role="status" aria-label="Mr. UBRA is typing">
                    <span></span><span></span><span></span><em>Mr. UBRA is typing</em>
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
