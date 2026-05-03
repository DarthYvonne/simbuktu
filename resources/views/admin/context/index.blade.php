@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>Opsætning @if ($course)<span style="font-weight: 400; color: #65676b; font-size: 14px;">· {{ $course->name }}</span>@endif</h1>
</div>

@include('admin._opsaetning_tabs')

@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if (session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

@if (!$course)
  <div class="alert alert-error">Du har intet aktivt kursus.</div>
@else

@php $mode = $course->context_mode ?? 'auto'; @endphp

<form method="POST" action="{{ url('/slophub/admin/context/mode') }}" class="card">
  @csrf
  <h3 style="margin-bottom: 10px;">Hvilken kontekst skal personas reagere ud fra?</h3>
  <p style="color: #65676b; font-size: 13px; margin-bottom: 12px;">Valget gælder for dette kursus. Injiceres i alle prompts der styrer persona-reaktioner.</p>
  <label style="display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; border: 1px solid {{ $mode === 'auto' ? '#1877f2' : '#dadde1' }}; border-radius: 10px; margin-bottom: 10px; cursor: pointer;">
    <input type="radio" name="context_mode" value="auto" {{ $mode === 'auto' ? 'checked' : '' }} onchange="this.form.submit()">
    <div>
      <strong>Automatisk — nyheds-digest</strong>
      <div style="font-size: 13px; color: #65676b; line-height: 1.5;">Systemet henter overskrifter fra DR + TV2 + Politiken hver 30. minut. Hver 6. time distilleres de 14 dage til en "hvad fylder i dansk offentlighed"-opsummering. Personas refererer naturligt aktuelle begivenheder.</div>
    </div>
  </label>
  <label style="display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; border: 1px solid {{ $mode === 'manual' ? '#1877f2' : '#dadde1' }}; border-radius: 10px; cursor: pointer;">
    <input type="radio" name="context_mode" value="manual" {{ $mode === 'manual' ? 'checked' : '' }} onchange="this.form.submit()">
    <div>
      <strong>Manuel — fri tekst</strong>
      <div style="font-size: 13px; color: #65676b; line-height: 1.5;">Du beskriver selv samtiden. Brug hvis du vil simulere en anden tid ("det er 2028, Grønland er uafhængigt"), et fiktivt scenarie, eller en specifik krise.</div>
    </div>
  </label>
</form>

@if ($mode === 'auto')

<div class="card">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
    <h3 style="margin: 0;">Aktuel opsamling</h3>
    <form method="POST" action="{{ url('/slophub/admin/context/build') }}" style="display: inline;">
      @csrf
      <button class="btn btn-primary"><i class="fa-solid fa-rotate"></i> Lav opsamling</button>
    </form>
  </div>

  @if (!$digest)
    <div style="color: #65676b; font-size: 13px; padding: 10px 0;">
      Ingen opsamling endnu. Klik "Lav opsamling" — systemet henter nyheder og distillerer dem automatisk.
    </div>
  @else
    @php
    $ongoing = \App\Services\News\NewsDigester::toArray($digest['ongoing_situations'] ?? []);
    $breaking = \App\Services\News\NewsDigester::toArray($digest['recent_breaking'] ?? []);
    @endphp
    <div style="color: #65676b; font-size: 12px; margin-bottom: 12px;">Genereret {{ \Carbon\Carbon::parse($digest['generated_at'])->diffForHumans() }} fra {{ $digest['headline_count'] ?? 0 }} overskrifter.</div>

    @if (!empty($ongoing))
      <div style="background: #f0f2f5; border-radius: 8px; padding: 12px 14px; margin-bottom: 10px;">
        <div style="font-size: 11px; font-weight: 700; color: #65676b; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px;">Igangværende</div>
        <ul style="margin-left: 18px; font-size: 14px; line-height: 1.6;">
          @foreach ($ongoing as $s)
            <li>{{ $s }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    @if (!empty($breaking))
      <div style="background: #fef9e7; border-radius: 8px; padding: 12px 14px;">
        <div style="font-size: 11px; font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px;">Senest (1-2 døgn)</div>
        <ul style="margin-left: 18px; font-size: 14px; line-height: 1.6;">
          @foreach ($breaking as $s)
            <li>{{ $s }}</li>
          @endforeach
        </ul>
      </div>
    @endif
  @endif
</div>

<details class="card">
  <summary style="cursor: pointer; font-weight: 600;">Se seneste {{ $headlines->count() }} rå overskrifter (i alt {{ $totalHeadlines }} i buffer)</summary>
  <div style="margin-top: 10px;">
    @foreach ($headlines as $h)
      <div style="padding: 6px 0; border-bottom: 1px solid #f0f2f5; font-size: 13px;">
        <span style="color: #65676b; font-size: 11px; text-transform: uppercase;">{{ $h->source }} · {{ $h->published_at?->format('d/m H:i') }}</span>
        <div><a href="{{ $h->url }}" target="_blank" style="color: #1c1e21;">{{ $h->title }}</a></div>
      </div>
    @endforeach
  </div>
</details>

@else

<form method="POST" action="{{ url('/slophub/admin/context/manual') }}" class="card">
  @csrf
  <h3 style="margin-bottom: 8px;">Manuel kontekst</h3>
  <p style="color: #65676b; font-size: 13px; margin-bottom: 10px;">Beskriv samtiden som personerne i dette kursus skal reagere ud fra. Indsættes direkte i alle persona-prompts.</p>
  <textarea name="manual_context" style="width: 100%; min-height: 260px; padding: 12px 14px; border: 1px solid #dadde1; border-radius: 8px; font-family: inherit; font-size: 14px; line-height: 1.5;" placeholder="F.eks.:&#10;Det er efteråret 2026. Iran og Israel er i åben krig. Danmark har sendt F-35 jetfly...">{{ $course->manual_context }}</textarea>
  <div style="text-align: right; margin-top: 10px;">
    <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem</button>
  </div>
</form>

@endif

@endif
@endsection
