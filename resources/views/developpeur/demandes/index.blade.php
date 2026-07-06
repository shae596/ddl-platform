@extends('layouts.developpeur')

@section('title', 'Mes affectations')

@section('content')
<div class="tabs">
    <a href="{{ route('developpeur.demandes.index', ['onglet' => 'a_demarrer']) }}"
       class="tab {{ $onglet === 'a_demarrer' ? 'tab--active' : '' }}">
        À démarrer
        @if($stats['a_demarrer'] > 0)
            <span class="tab__badge">{{ $stats['a_demarrer'] }}</span>
        @endif
    </a>
    <a href="{{ route('developpeur.demandes.index', ['onglet' => 'en_developpement']) }}"
       class="tab {{ $onglet === 'en_developpement' ? 'tab--active' : '' }}">
        En développement
        @if($stats['en_developpement'] > 0)
            <span class="tab__badge">{{ $stats['en_developpement'] }}</span>
        @endif
    </a>
    <a href="{{ route('developpeur.demandes.index', ['onglet' => 'en_test']) }}"
       class="tab {{ $onglet === 'en_test' ? 'tab--active' : '' }}">
        En test
    </a>
    <a href="{{ route('developpeur.demandes.index', ['onglet' => 'terminees']) }}"
       class="tab {{ $onglet === 'terminees' ? 'tab--active' : '' }}">
        Terminées
    </a>
</div>

<div class="toolbar">
    <form method="GET" class="toolbar__filters">
        <input type="hidden" name="onglet" value="{{ $onglet }}">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher…">
        <select name="priorite">
            <option value="">Toutes priorités</option>
            @foreach(\App\Enums\Priorite::cases() as $p)
                <option value="{{ $p->value }}" @selected(request('priorite') === $p->value)>{{ $p->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-outline">Filtrer</button>
    </form>
</div>

@include('developpeur.partials.demandes-table', ['demandes' => $demandes])
@endsection
