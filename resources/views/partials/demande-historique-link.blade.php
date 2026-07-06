@props(['demande', 'href'])

<a href="{{ $href }}" class="panel panel--clickable panel--full">
    <div class="panel__header panel__header--inline">
        <h3>Historique</h3>
        <span class="panel__chevron" aria-hidden="true">→</span>
    </div>
    <p class="text-muted text-sm">
        {{ $demande->historique_actions_count ?? $demande->historiqueActions->count() }} action(s) — cliquez pour voir le détail
    </p>
</a>
