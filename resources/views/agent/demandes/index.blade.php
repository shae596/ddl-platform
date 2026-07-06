@extends('layouts.agent')

@section('title', 'Mes demandes')

@section('content')
<div class="toolbar">
    <form method="GET" class="toolbar__filters">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher…">
        <select name="statut">
            <option value="">Tous les statuts</option>
            @foreach($statuts as $statut)
                <option value="{{ $statut->value }}" @selected(request('statut') === $statut->value)>{{ $statut->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-outline">Filtrer</button>
    </form>
    <a href="{{ route('agent.demandes.create') }}" class="btn btn-primary">+ Nouvelle demande</a>
</div>

@include('agent.partials.demandes-table', ['demandes' => $demandes])
@endsection
