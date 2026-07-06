<table class="data-table">
    <thead>
        <tr>
            <th>Numéro</th>
            <th>Titre</th>
            <th>Direction / Service</th>
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
                <td>{{ $demande->service_demandeur ?? '—' }}</td>
                <td><span class="badge {{ $demande->statut->badgeClass() }}">{{ $demande->statut->label() }}</span></td>
                <td><span class="badge {{ $demande->priorite->badgeClass() }}">{{ $demande->priorite->label() }}</span></td>
                <td>{{ $demande->delai_previsionnel?->format('d/m/Y') ?? '—' }}</td>
                <td>{{ $demande->updated_at?->format('d/m/Y') }}</td>
                @unless($compact ?? false)
                    <td class="actions">
                        <a href="{{ route('di.demandes.show', $demande) }}">Examiner</a>
                        @if($demande->aCahierDesCharges())
                            <a href="{{ route('di.demandes.cahier', $demande) }}">PDF</a>
                        @endif
                    </td>
                @endunless
            </tr>
        @empty
            <tr>
                <td colspan="{{ ($compact ?? false) ? 7 : 8 }}" class="text-muted">Aucune demande.</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if(isset($demandes) && method_exists($demandes, 'links'))
    <div class="pagination-wrap">{{ $demandes->links() }}</div>
@endif
