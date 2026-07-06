<ul class="timeline">
    @forelse($demande->historiqueActions->sortByDesc('created_at') as $action)
        <li>
            <strong>{{ $action->action }}</strong>
            @if($action->ancien_statut && $action->nouveau_statut && $action->ancien_statut !== $action->nouveau_statut)
                — {{ $action->ancien_statut }} → {{ $action->nouveau_statut }}
            @endif
            @if($action->commentaire)
                <br><em>{{ $action->commentaire }}</em>
            @endif
            <small>{{ $action->created_at?->format('d/m/Y H:i') }}@if($action->utilisateur) — {{ $action->utilisateur->fullName() }}@endif</small>
        </li>
    @empty
        <li class="text-muted">Aucune action enregistrée.</li>
    @endforelse
</ul>
