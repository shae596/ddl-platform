@extends('layouts.di')

@section('title', 'Cahiers des charges — DI')

@section('content')
<div class="tabs">
    <a href="{{ route('di.demandes.index', ['onglet' => 'a_examiner']) }}"
       class="tab {{ $onglet === 'a_examiner' ? 'tab--active' : '' }}">
        À examiner
        @if($stats['a_examiner'] > 0)
            <span class="tab__badge">{{ $stats['a_examiner'] }}</span>
        @endif
    </a>
    <a href="{{ route('di.demandes.index', ['onglet' => 'en_cours']) }}"
       class="tab {{ $onglet === 'en_cours' ? 'tab--active' : '' }}">
        En cours
        @if($stats['en_cours'] > 0)
            <span class="tab__badge">{{ $stats['en_cours'] }}</span>
        @endif
    </a>
    <a href="{{ route('di.demandes.index', ['onglet' => 'suivies']) }}"
       class="tab {{ $onglet === 'suivies' ? 'tab--active' : '' }}">
        Suivies / archivées
    </a>
</div>

<div class="toolbar">
    <form method="GET" class="toolbar__filters">
        <input type="hidden" name="onglet" value="{{ $onglet }}">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher…">
        <input type="text" name="service" value="{{ request('service') }}" placeholder="Direction / service">
        <select name="priorite">
            <option value="">Priorité</option>
            @foreach(\App\Enums\Priorite::cases() as $p)
                <option value="{{ $p->value }}" @selected(request('priorite') === $p->value)>{{ $p->label() }}</option>
            @endforeach
        </select>
        <select name="statut">
            <option value="">Statut</option>
            @foreach($statuts as $s)
                @if($s->value !== 'BROUILLON')
                    <option value="{{ $s->value }}" @selected(request('statut') === $s->value)>{{ $s->label() }}</option>
                @endif
            @endforeach
        </select>
        <input type="date" name="date_debut" value="{{ request('date_debut') }}" title="Date début">
        <input type="date" name="date_fin" value="{{ request('date_fin') }}" title="Date fin">
        <button type="submit" class="btn btn-outline">Filtrer</button>
    </form>
</div>

@include('di.partials.demandes-table', ['demandes' => $demandes])
@endsection
