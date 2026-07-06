@extends('layouts.secretariat')

@section('title', 'Cahiers des charges')

@section('content')
<div class="tabs">
    <a href="{{ route('secretariat.demandes.index', ['onglet' => 'a_recevoir']) }}"
       class="tab {{ $onglet === 'a_recevoir' ? 'tab--active' : '' }}">
        À recevoir
        @if($stats['a_recevoir'] > 0)
            <span class="tab__badge">{{ $stats['a_recevoir'] }}</span>
        @endif
    </a>
    <a href="{{ route('secretariat.demandes.index', ['onglet' => 'a_transferer']) }}"
       class="tab {{ $onglet === 'a_transferer' ? 'tab--active' : '' }}">
        À transférer
        @if($stats['a_transferer'] > 0)
            <span class="tab__badge">{{ $stats['a_transferer'] }}</span>
        @endif
    </a>
    <a href="{{ route('secretariat.demandes.index', ['onglet' => 'transferees']) }}"
       class="tab {{ $onglet === 'transferees' ? 'tab--active' : '' }}">
        Transférées
    </a>
</div>

<div class="toolbar">
    <form method="GET" class="toolbar__filters">
        <input type="hidden" name="onglet" value="{{ $onglet }}">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher (numéro, titre, service)…">
        <select name="priorite">
            <option value="">Toutes priorités</option>
            @foreach(\App\Enums\Priorite::cases() as $p)
                <option value="{{ $p->value }}" @selected(request('priorite') === $p->value)>{{ $p->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-outline">Filtrer</button>
    </form>
</div>

@include('secretariat.partials.demandes-table', ['demandes' => $demandes])
@endsection
