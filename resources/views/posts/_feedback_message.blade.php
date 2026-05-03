<div class="fb-comment" data-id="{{ $m['id'] }}">
  @if (!empty($m['user']['image_url']))
    <div class="av"><img src="{{ $m['user']['image_url'] }}" alt=""></div>
  @else
    <div class="av">{{ strtoupper(mb_substr($m['user']['name'] ?? '?', 0, 2)) }}</div>
  @endif
  <div class="content">
    <div class="bubble">
      <div class="who"><strong>{{ $m['user']['name'] }}</strong>@if ($m['user']['is_instructor'])<span class="teacher-badge">Underviser</span>@endif</div>
      <div class="body">{{ $m['body'] }}</div>
    </div>
    <div class="time">{{ $m['created_at_human'] }}</div>
  </div>
</div>
