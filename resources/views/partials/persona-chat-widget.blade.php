@auth
@if (auth()->user()->currentCourse())
<style>
  .pchat-dock { position: fixed; bottom: 0; right: 20px; width: 340px; background: #fff; border: 1px solid #dadde1; border-bottom: none; border-radius: 10px 10px 0 0; box-shadow: 0 4px 16px rgba(0,0,0,0.12); display: none; flex-direction: column; z-index: 1000; font-size: 14px; overflow: hidden; }
  @media (max-width: 767px) { .pchat-dock { width: calc(100vw - 16px); right: 8px; } }
  .pchat-dock.open { display: flex; }
  .pchat-head { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: #f0f2f5; cursor: pointer; user-select: none; }
  .pchat-head img { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; }
  .pchat-head .ph { width: 32px; height: 32px; border-radius: 50%; background: #e4e6eb; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; color: #65676b; }
  .pchat-head .pchat-title { flex: 1; font-weight: 600; font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .pchat-head .pchat-title a { color: inherit; }
  .pchat-head .pchat-actions button { background: none; border: none; cursor: pointer; color: #65676b; padding: 4px 6px; font-size: 14px; }
  .pchat-head .pchat-actions button:hover { color: #1c1e21; }
  .pchat-body { height: 360px; overflow-y: auto; padding: 12px; background: #fff; display: flex; flex-direction: column; gap: 6px; }
  .pchat-body.collapsed { display: none; }
  .pchat-empty { color: #65676b; font-size: 13px; text-align: center; margin: auto; padding: 20px; }
  .pchat-msg { max-width: 80%; padding: 8px 12px; border-radius: 16px; line-height: 1.35; word-wrap: break-word; white-space: pre-wrap; }
  .pchat-msg.user { align-self: flex-end; background: #1877f2; color: #fff; border-bottom-right-radius: 4px; }
  .pchat-msg.persona { align-self: flex-start; background: #f0f2f5; color: #1c1e21; border-bottom-left-radius: 4px; }
  .pchat-input-row { border-top: 1px solid #dadde1; padding: 8px; display: flex; gap: 6px; background: #fff; }
  .pchat-input-row.collapsed { display: none; }
  .pchat-input-row textarea { flex: 1; min-height: 36px; max-height: 120px; padding: 8px 10px; border: 1px solid #dadde1; border-radius: 18px; resize: none; font-family: inherit; font-size: 14px; }
  .pchat-input-row button { background: #1877f2; color: #fff; border: none; border-radius: 50%; width: 36px; height: 36px; cursor: pointer; font-size: 14px; flex-shrink: 0; }
  .pchat-input-row button:disabled { opacity: 0.5; cursor: not-allowed; }
  .pchat-typing { align-self: flex-start; display: inline-flex; gap: 4px; padding: 10px 14px; background: #f0f2f5; border-radius: 18px; border-bottom-left-radius: 4px; }
  .pchat-typing span { width: 7px; height: 7px; background: #90949c; border-radius: 50%; animation: pchat-bob 1.2s infinite ease-in-out; }
  .pchat-typing span:nth-child(2) { animation-delay: 0.15s; }
  .pchat-typing span:nth-child(3) { animation-delay: 0.30s; }
  @keyframes pchat-bob { 0%, 60%, 100% { transform: translateY(0); opacity: 0.45; } 30% { transform: translateY(-5px); opacity: 1; } }
</style>

<div class="pchat-dock" id="pchatDock" aria-live="polite">
  <div class="pchat-head" onclick="pchatToggleCollapse(event)">
    <div id="pchatAvatar"></div>
    <div class="pchat-title" id="pchatTitle">...</div>
    <div class="pchat-actions">
      <button type="button" title="Åbn fuld visning" onclick="pchatOpenFull(event)"><i class="fa-solid fa-up-right-from-square"></i></button>
      <button type="button" title="Luk" onclick="pchatClose(event)"><i class="fa-solid fa-xmark"></i></button>
    </div>
  </div>
  <div class="pchat-body" id="pchatBody"></div>
  <form class="pchat-input-row" id="pchatForm" onsubmit="return pchatSend(event)">
    <textarea id="pchatInput" placeholder="Skriv en besked..." rows="1"
      onkeydown="if(event.key==='Enter' && !event.shiftKey){event.preventDefault();pchatSend(event);}"></textarea>
    <button type="submit" id="pchatSendBtn" title="Send"><i class="fa-solid fa-paper-plane"></i></button>
  </form>
</div>

<script>
window.__pchatState = { conversationId: null, personaId: null, personaName: null, personaUrl: null, personaThumb: null, sending: false };

function pchatEl(id) { return document.getElementById(id); }
function pchatEscape(s) { return (s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

function pchatCsrf() {
  return document.querySelector('meta[name="csrf-token"]').content;
}

async function openPersonaChat(personaId, personaName) {
  const state = window.__pchatState;
  const dock = pchatEl('pchatDock');
  dock.classList.add('open');
  pchatEl('pchatBody').classList.remove('collapsed');
  pchatEl('pchatForm').classList.remove('collapsed');
  pchatEl('pchatTitle').innerHTML = pchatEscape(personaName || '...');
  pchatEl('pchatBody').innerHTML = '<div class="pchat-empty">Indlæser...</div>';

  try {
    const res = await fetch('{{ url('/slophub/beskeder/open') }}/' + encodeURIComponent(personaId), {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': pchatCsrf(), 'Accept': 'application/json' },
    });
    if (!res.ok) throw new Error('kunne ikke åbne samtale');
    const data = await res.json();
    state.conversationId = data.conversation.id;
    state.personaId = data.conversation.persona_id;
    state.personaName = data.conversation.persona_name;
    state.personaUrl = data.conversation.persona_url;
    state.personaThumb = data.conversation.persona_thumb;
    pchatRenderHeader();
    pchatRenderMessages(data.messages);
    pchatEl('pchatInput').focus();
  } catch (e) {
    pchatEl('pchatBody').innerHTML = '<div class="pchat-empty">Kunne ikke åbne samtalen. Prøv igen.</div>';
  }
}

function pchatRenderHeader() {
  const state = window.__pchatState;
  pchatEl('pchatAvatar').innerHTML = state.personaThumb
    ? '<img src="' + state.personaThumb + '" alt="">'
    : '<div class="ph">' + pchatEscape((state.personaName || '?').slice(0, 2).toUpperCase()) + '</div>';
  pchatEl('pchatTitle').innerHTML = '<a href="' + state.personaUrl + '">' + pchatEscape(state.personaName) + '</a>';
}

function pchatRenderMessages(messages) {
  const body = pchatEl('pchatBody');
  if (!messages || messages.length === 0) {
    body.innerHTML = '<div class="pchat-empty">Skriv en besked for at starte samtalen.</div>';
    return;
  }
  body.innerHTML = '';
  for (const m of messages) pchatAppendMessage(m);
  body.scrollTop = body.scrollHeight;
}

function pchatAppendMessage(m) {
  const body = pchatEl('pchatBody');
  const empty = body.querySelector('.pchat-empty');
  if (empty) empty.remove();
  const div = document.createElement('div');
  div.className = 'pchat-msg ' + (m.role === 'user' ? 'user' : 'persona');
  div.textContent = m.body;
  body.appendChild(div);
  body.scrollTop = body.scrollHeight;
}

function pchatShowTyping() {
  const body = pchatEl('pchatBody');
  if (document.getElementById('pchatTyping')) return;
  const t = document.createElement('div');
  t.className = 'pchat-typing';
  t.id = 'pchatTyping';
  t.setAttribute('aria-label', (window.__pchatState.personaName || '...') + ' skriver');
  t.innerHTML = '<span></span><span></span><span></span>';
  body.appendChild(t);
  body.scrollTop = body.scrollHeight;
}
function pchatHideTyping() {
  const t = pchatEl('pchatTyping');
  if (t) t.remove();
}

async function pchatSend(ev) {
  ev.preventDefault();
  const state = window.__pchatState;
  if (state.sending || !state.conversationId) return false;
  const input = pchatEl('pchatInput');
  const body = input.value.trim();
  if (!body) return false;

  state.sending = true;
  pchatEl('pchatSendBtn').disabled = true;
  input.value = '';
  pchatAppendMessage({ role: 'user', body });
  pchatShowTyping();

  try {
    const res = await fetch('{{ url('/slophub/beskeder') }}/' + state.conversationId + '/send', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': pchatCsrf(), 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ body }),
    });
    pchatHideTyping();
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      pchatAppendMessage({ role: 'persona', body: '(kunne ikke sende: ' + (err.error || 'fejl') + ')' });
    } else {
      const data = await res.json();
      pchatAppendMessage(data.persona_message);
    }
  } catch (e) {
    pchatHideTyping();
    pchatAppendMessage({ role: 'persona', body: '(netværksfejl)' });
  } finally {
    state.sending = false;
    pchatEl('pchatSendBtn').disabled = false;
    input.focus();
  }
  return false;
}

function pchatToggleCollapse(ev) {
  if (ev && ev.target && ev.target.closest('.pchat-actions')) return;
  pchatEl('pchatBody').classList.toggle('collapsed');
  pchatEl('pchatForm').classList.toggle('collapsed');
}

function pchatClose(ev) {
  ev && ev.stopPropagation();
  pchatEl('pchatDock').classList.remove('open');
}

function pchatOpenFull(ev) {
  ev && ev.stopPropagation();
  const state = window.__pchatState;
  if (state.conversationId) {
    window.location = '{{ url('/slophub/beskeder') }}/' + state.conversationId;
  }
}
</script>
@endif
@endauth
