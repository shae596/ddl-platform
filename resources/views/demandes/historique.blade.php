@extends($layout)

@section('title', 'Historique — '.$demande->numero)

@section('content')
<div class="detail-header">
    <div>
        <p class="detail-meta">{{ $demande->numero }}</p>
        <h2 class="detail-title">Historique — {{ $demande->titre }}</h2>
    </div>
</div>

<section class="panel panel--full">
    @include('partials.demande-historique-timeline', ['demande' => $demande])
</section>

<p class="text-muted" style="margin-top:1rem;">
    <a href="{{ $backUrl }}">← Retour à la demande</a>
</p>
@endsection
