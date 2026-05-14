@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1><a href="{{ url('/simulation/beskeder') }}" style="color: #1877f2;"><i class="fa-solid fa-arrow-left"></i> Beskeder</a></h1>
</div>

<style>
.conv-wrap { background: #fff; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: column; height: calc(100vh - 120px); max-height: 780px; }
.conv-head { display: flex; gap: 12px; align-items: center; padding: 12px 16px; border-bottom: 1px solid #dadde1; }
.conv-head img, .conv-head .ph { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; }
.conv-head .ph { background: #e4e6eb; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #65676b; }
.conv-head a.name { font-weight: 700; color: #1c1e21; font-size: 16px; }
.conv-head .meta { color: #65676b; font-size: 12px; }
.conv-msgs { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 8px; background: #f8f9fa; }
.msg { max-width: 72%; padding: 9px 14px; border-radius: 18px; line-height: 1.4; word-wrap: break-word; white-space: pre-wrap; font-size: 14px; }
.msg.user { align-self: flex-end; background: #1877f2; color: #fff; border-bottom-right-radius: 4px; }
.msg.persona { align-self: flex-start; background: #e4e6eb; color: #1c1e21; border-bottom-left-radius: 4px; }
.msg-time { font-size: 11px; color: #65676b; align-self: center; margin: 4px 0; }
.conv-input { border-top: 1px solid #dadde1; padding: 10px 12px; display: flex; gap: 8px; background: #fff; }
.conv-input textarea { flex: 1; min-height: 40px; max-height: 160px; padding: 10px 12px; border: 1px solid #dadde1; border-radius: 20px; resize: none; font-family: inherit; font-size: 14px; }
.conv-input button { background: #1877f2; color: #fff; border: none; border-radius: 50%; width: 40px; height: 40px; cursor: pointer; }
.conv-input button:disabled { opacity: 0.5; cursor: not-allowed; }
.typing { align-self: flex-start; display: inline-flex; gap: 5px; padding: 12px 16px; background: #e4e6eb; border-radius: 18px; border-bottom-left-radius: 4px; }
.typing span { width: 8px; height: 8px; background: #90949c; border-radius: 50%; animation: conv-bob 1.2s infinite ease-in-out; }
.typing span:nth-child(2) { animation-delay: 0.15s; }
.typing span:nth-child(3) { animation-delay: 0.30s; }
@keyframes conv-bob { 0%, 60%, 100% { transform: translateY(0); opacity: 0.45; } 30% { transform: translateY(-5px); opacity: 1; } }
</style>

<div class="conv-wrap">
  <div class="conv-head">
    <a href="{{ url('/simulation/profiler/' . $conversation->persona_id) }}">
      <img src="{{ url('/simulation/profiler/' . $conversation->persona_id . '/thumb') }}" alt="" onerror="this.replaceWith(Object.assign(document.createElement('div'),{className:'ph',textContent:'{{ strtoupper(substr($conversation->persona_name, 0, 2)) }}'}))">
    </a>
    <div style="flex:1;">
      <a class="name" href="{{ url('/simulation/profiler/' . $conversation->persona_id) }}">{{ $conversation->persona_name }}</a>
      @if (!empty($persona['demographics']))
        <div class="meta">{{ $persona['demographics']['occupation_hint'] ?? '' }} · {{ $persona['demographics']['region'] ?? '' }}</div>
      @endif
    </div>
  </div>

  <div class="conv-msgs" id="convMsgs">
    @foreach ($messages as $m)
      @if ($m->role === 'persona' && $m->status === 'pending')
        <div class="typing pending-bubble" data-pending-id="{{ $m->id }}" aria-label="{{ $conversation->persona_name }} skriver">
          <span></span><span></span><span></span>
        </div>
      @elseif ($m->role === 'persona' && $m->status === 'failed')
        <div class="msg persona" style="background:#fee2e2;color:#991b1b;">{{ $m->error_message ?: 'Beskeden kunne ikke genereres.' }}</div>
      @else
        <div class="msg {{ $m->role === 'user' ? 'user' : 'persona' }}">{{ $m->body }}</div>
      @endif
    @endforeach
    @if ($messages->isEmpty())
      <div style="margin:auto; color:#65676b;">Skriv en besked for at starte samtalen.</div>
    @endif
  </div>

  <form class="conv-input" onsubmit="return convSend(event)">
    <textarea id="convInput" placeholder="Skriv en besked..." rows="1"
      onkeydown="if(event.key==='Enter' && !event.shiftKey){event.preventDefault();convSend(event);}"></textarea>
    <button type="submit" id="convSendBtn" title="Send"><i class="fa-solid fa-paper-plane"></i></button>
  </form>
</div>

<script>
const convId = {{ $conversation->id }};
const convMsgs = document.getElementById('convMsgs');
const convInput = document.getElementById('convInput');
const convSendBtn = document.getElementById('convSendBtn');
let convSending = false;

function convScroll() { convMsgs.scrollTop = convMsgs.scrollHeight; }
convScroll();

function convEscape(s) { return (s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

function convAppend(role, body) {
  const div = document.createElement('div');
  div.className = 'msg ' + (role === 'user' ? 'user' : 'persona');
  div.textContent = body;
  convMsgs.appendChild(div);
  convScroll();
}

function convAddPendingBubble(messageId) {
  const t = document.createElement('div');
  t.className = 'typing pending-bubble';
  t.dataset.pendingId = String(messageId);
  t.setAttribute('aria-label', {{ json_encode($conversation->persona_name) }} + ' skriver');
  t.innerHTML = '<span></span><span></span><span></span>';
  convMsgs.appendChild(t);
  convScroll();
  pollPendingMessage(messageId);
}

function convReplacePendingWith(messageId, body, role = 'persona', failed = false) {
  const bubble = convMsgs.querySelector('[data-pending-id="' + messageId + '"]');
  const div = document.createElement('div');
  div.className = 'msg ' + (role === 'user' ? 'user' : 'persona');
  if (failed) {
    div.style.background = '#fee2e2';
    div.style.color = '#991b1b';
  }
  div.textContent = body;
  if (bubble) {
    bubble.replaceWith(div);
  } else {
    convMsgs.appendChild(div);
  }
  convScroll();
}

const pollingFor = new Set();
async function pollPendingMessage(messageId) {
  if (pollingFor.has(messageId)) return;
  pollingFor.add(messageId);
  const url = '{{ url('/simulation/beskeder/' . $conversation->id . '/messages') }}/' + messageId;
  // Poll up to ~3 minutes; bail if it never completes (job timeout is 180s).
  let attempts = 0;
  const maxAttempts = 95; // 95 * 2s = 190s
  while (attempts++ < maxAttempts) {
    await new Promise(r => setTimeout(r, 2000));
    try {
      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
      if (!res.ok) continue;
      const data = await res.json();
      if (data.status === 'complete') {
        convReplacePendingWith(messageId, data.body, 'persona');
        pollingFor.delete(messageId);
        return;
      }
      if (data.status === 'failed') {
        convReplacePendingWith(messageId, data.error_message || 'Beskeden kunne ikke genereres.', 'persona', true);
        pollingFor.delete(messageId);
        return;
      }
    } catch {}
  }
  convReplacePendingWith(messageId, '(beskeden tog for lang tid — prøv igen)', 'persona', true);
  pollingFor.delete(messageId);
}

// Pick up any pending bubbles already rendered (e.g. user navigated back to a chat with an in-flight reply)
document.querySelectorAll('.pending-bubble').forEach(el => {
  const id = parseInt(el.dataset.pendingId, 10);
  if (id) pollPendingMessage(id);
});

async function convSend(ev) {
  ev.preventDefault();
  if (convSending) return false;
  const body = convInput.value.trim();
  if (!body) return false;
  convSending = true;
  convSendBtn.disabled = true;
  convInput.value = '';
  convAppend('user', body);
  try {
    const res = await fetch('{{ url('/simulation/beskeder/' . $conversation->id . '/send') }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ body }),
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      convAppend('persona', '(kunne ikke sende: ' + (err.error || 'fejl') + ')');
    } else {
      const data = await res.json();
      // Server returned a pending persona placeholder — render the typing bubble and start polling.
      convAddPendingBubble(data.persona_message.id);
    }
  } catch (e) {
    convAppend('persona', '(netværksfejl)');
  } finally {
    convSending = false;
    convSendBtn.disabled = false;
    convInput.focus();
  }
  return false;
}
convInput.focus();
</script>

@endsection
