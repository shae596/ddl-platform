@extends('layouts.admin')

@section('title', 'Historique des actions')

@section('content')
<div class="toolbar">
    <form method="GET" class="toolbar__filters">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher (action, demande, commentaire)…">
        <select name="action">
            <option value="">Toutes les actions</option>
            @foreach($actions as $action)
                <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
            @endforeach
        </select>
        <input type="date" name="date_debut" value="{{ request('date_debut') }}">
        <input type="date" name="date_fin" value="{{ request('date_fin') }}">
        <button type="submit" class="btn btn-outline">Filtrer</button>
    </form>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Action</th>
            <th>Demande</th>
            <th>Utilisateur</th>
            <th>Statuts</th>
            <th>Commentaire</th>
        </tr>
    </thead>
    <tbody>
        @forelse($historique as $entry)
            <tr>
                <td>{{ $entry->created_at?->format('d/m/Y H:i') }}</td>
                <td><strong>{{ $entry->action }}</strong></td>
                <td>{{ $entry->demande?->numero ?? '—' }} — {{ Str::limit($entry->demande?->titre ?? '', 40) }}</td>
                <td>{{ $entry->utilisateur?->fullName() ?? '—' }}</td>
                <td>
                    @if($entry->ancien_statut && $entry->nouveau_statut && $entry->ancien_statut !== $entry->nouveau_statut)
                        {{ $entry->ancien_statut }} → {{ $entry->nouveau_statut }}
                    @else
                        —
                    @endif
                </td>
                <td>{{ Str::limit($entry->commentaire ?? '—', 60) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-muted">Aucune entrée.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="pagination-wrap">{{ $historique->links() }}</div>
@endsection
