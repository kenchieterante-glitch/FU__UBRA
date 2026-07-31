/* ============================================================
   FU-UBRA Global JavaScript
   File: public/assets/js/fuubra.js
   ============================================================ */

/* ---- TAB SWITCHER ---- */
/**
 * switchTab(clickedEl, targetPanelId)
 * Pass the clicked .tab element and the id of the panel to show.
 * Hides all known tab panels, shows the target one.
 */
function switchTab(clickedEl, targetPanelId) {
  /* Deactivate all sibling tabs */
  const tabRow = clickedEl.closest('.tabs');
  if (tabRow) {
    tabRow.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  }
  clickedEl.classList.add('active');

  /* All possible tab panel IDs across the whole app */
  const allPanels = [
    's1','s2','s3','s4','s5'
  ];
  allPanels.forEach(id => {
    const panel = document.getElementById(id);
    if (panel) panel.style.display = 'none';
  });

  const target = document.getElementById(targetPanelId);
  if (target) target.style.display = 'block';
}

/* ---- MODAL HELPERS ---- */
function openModal(modalId) {
  const m = document.getElementById(modalId);
  if (m) m.classList.add('show');
}
function closeModal(modalId) {
  const m = document.getElementById(modalId);
  if (m) m.classList.remove('show');
}

/* Close modal when clicking outside the .modal box */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.modal-bg').forEach(bg => {
    bg.addEventListener('click', function (e) {
      if (e.target === bg) bg.classList.remove('show');
    });
  });
});

/* ---- MR. UBRA AI CHAT ---- */
async function sendAI() {
  const input   = document.getElementById('ai-input');
  const msgWrap = document.getElementById('ai-messages');
  const text    = input.value.trim();
  if (!text) return;
  input.value = '';

  const now = () => new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

  /* Render user bubble */
  msgWrap.innerHTML += `
    <div class="ai-msg user">
      <div>
        <div class="bubble">${escapeHtml(text)}</div>
        <div class="msg-time" style="text-align:right">${now()}</div>
      </div>
    </div>`;
  msgWrap.scrollTop = msgWrap.scrollHeight;

  /* Typing indicator */
  const typingId = 'typing-' + Date.now();
  msgWrap.innerHTML += `
    <div class="ai-msg" id="${typingId}">
      <div class="ai-avatar" style="width:32px;height:32px;font-size:11px;flex-shrink:0">U</div>
      <div><div class="bubble" style="color:var(--text-muted)">Mr. UBRA is thinking…</div></div>
    </div>`;
  msgWrap.scrollTop = msgWrap.scrollHeight;

  const systemPrompt = `You are Mr. UBRA, the Intelligent Operations Assistant for Foundation University's FU-UBRA operational management system. You have expert knowledge of the university's fleet management, personnel, asset tracking, safety monitoring, and janitorial operations. Be helpful, professional, and concise. When asked about operational data, provide realistic sample responses relevant to a university context. Always refer to yourself as "Mr. UBRA".`;

  try {
    const res = await fetch('https://api.anthropic.com/v1/messages', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        model: 'claude-sonnet-4-6',
        max_tokens: 1000,
        system: systemPrompt,
        messages: [{ role: 'user', content: text }]
      })
    });

    const data  = await res.json();
    const reply = data.content?.[0]?.text || 'I encountered an issue. Please try again.';

    document.getElementById(typingId)?.remove();
    msgWrap.innerHTML += `
      <div class="ai-msg">
        <div class="ai-avatar" style="width:32px;height:32px;font-size:11px;flex-shrink:0">U</div>
        <div>
          <div class="bubble">${reply.replace(/\n/g, '<br>')}</div>
          <div class="msg-time">${now()}</div>
        </div>
      </div>`;
  } catch (err) {
    document.getElementById(typingId)?.remove();
    msgWrap.innerHTML += `
      <div class="ai-msg">
        <div class="ai-avatar" style="width:32px;height:32px;font-size:11px;flex-shrink:0">U</div>
        <div><div class="bubble" style="color:var(--red)">Connection error. Check your API configuration in app/Config/Anthropic.php.</div></div>
      </div>`;
  }

  msgWrap.scrollTop = msgWrap.scrollHeight;
}

/* Enter key sends message */
document.addEventListener('DOMContentLoaded', function () {
  const aiInput = document.getElementById('ai-input');
  if (aiInput) {
    aiInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendAI();
      }
    });
  }
});

/* Clear AI chat */
function clearAIChat() {
  const wrap = document.getElementById('ai-messages');
  if (wrap) wrap.innerHTML = '<div style="text-align:center;color:var(--text-muted);font-size:12px;padding:20px">Chat cleared. Ask Mr. UBRA anything.</div>';
}

/* ---- HTML ESCAPE HELPER ---- */
function escapeHtml(str) {
  return str
    .replace(/&/g,  '&amp;')
    .replace(/</g,  '&lt;')
    .replace(/>/g,  '&gt;')
    .replace(/"/g,  '&quot;')
    .replace(/'/g,  '&#039;');
}