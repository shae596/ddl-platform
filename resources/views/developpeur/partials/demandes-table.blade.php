<table class="data-table">
    <thead>
        <tr>
            <th>Numéro</th>
            <th>Titre</th>
            <th>Statut</th>
            <th>Priorité</th>
            <th>Délai prév.</th>
            <th>Mise à jour</th>
            @unless($compact ?? false)
                <th>Actions</th>
            @endunless
        </tr>
    </thead>
    <tbody>
        @forelse($demandes as $demande)
            <tr>
                <td>{{ $demande->numero ?? '—' }}</td>
                <td>{{ $demande->titre }}</td>
                <td><span class="badge {{ $demande->statut->badgeClass() }}">{{ $demande->statut->label() }}</span></td>
                <td><span class="badge {{ $demande->priorite->badgeClass() }}">{{ $demande->priorite->label() }}</span></td>
                <td>{{ $demande->delai_previsionnel?->format('d/m/Y') ?? '—' }}</td>
                <td>{{ $demande->updated_at?->format('d/m/Y') }}</td>
                @unless($compact ?? false)
                    <td class="actions">
                        <a href="{{ route('developpeur.demandes.show', $demande) }}">Voir</a>
                        @if($demande->aCahierDesCharges())
                            <a href="{{ route('developpeur.demandes.cahier', $demande) }}">PDF</a>
                        @endif
                    </td>
                @endunless
            </tr>
        @empty
            <tr>
                <td colspan="{{ ($compact ?? false) ? 6 : 7 }}" class="text-muted">Aucune affectation.</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if(isset($demandes) && method_exists($demandes, 'links'))
    <div class="pagination-wrap">{{ $demandes->links() }}</div>
@endif
