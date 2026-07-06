@extends('layouts.agent')

@section('title', $demande->numero ?? 'Détail demande')

@section('content')
<div class="detail-header">
    <div>
        <p class="detail-meta">{{ $demande->numero ?? 'Sans numéro' }}</p>
        <h2 class="detail-title">{{ $demande->titre }}</h2>
        <div class="detail-badges">
            <span class="badge {{ $demande->statut->badgeClass() }}">{{ $demande->statut->label() }}</span>
            <span class="badge {{ $demande->priorite->badgeClass() }}">{{ $demande->priorite->label() }}</span>
        </div>
    </div>
    <div class="detail-actions">
        @if($cahierPdf ?? null)
            <a href="{{ route('agent.demandes.cahier', $demande) }}" class="btn btn-primary">Télécharger le cahier des charges (PDF)</a>
        @endif
        @if($demande->statut->isEditableByAgent() && ($cahierPdf ?? null) && $demande->statut->value === 'BROUILLON')
            <p class="text-sm text-muted">Le cahier des charges est généré. Modifiez le formulaire puis régénérez le PDF avant soumission si nécessaire.</p>
        @endif
        @if($demande->statut->isEditableByAgent())
            <a href="{{ route('agent.demandes.edit', $demande) }}" class="btn btn-primary">Modifier</a>
        @endif
        @if($demande->statut->value === 'BROUILLON')
            <form method="POST" action="{{ route('agent.demandes.destroy', $demande) }}"
                  data-confirm="Supprimer ce brouillon ? Cette action est irréversible."
                  data-confirm-title="Supprimer le brouillon"
                  data-confirm-variant="danger"
                  data-confirm-accept="Supprimer">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline">Supprimer</button>
            </form>
        @endif
    </div>
</div>

<div class="detail-grid">
    <section class="panel">
        <h3>Identification</h3>
        <dl class="detail-dl">
            <dt>Service</dt><dd>{{ $demande->service_demandeur }}</dd>
            <dt>Demandeur</dt><dd>{{ $demande->nom_demandeur }} — {{ $demande->email_demandeur }}</dd>
            <dt>Téléphone</dt><dd>{{ $demande->telephone_demandeur ?? '—' }}</dd>
            <dt>Livraison souhaitée</dt><dd>{{ $demande->date_souhaitee_livraison?->format('d/m/Y') ?? '—' }}</dd>
            <dt>Soumission</dt><dd>{{ $demande->date_soumission?->format('d/m/Y H:i') ?? '—' }}</dd>
        </dl>
    </section>

    <section class="panel">
        <h3>Contexte</h3>
        <p><strong>Contexte :</strong> {{ $demande->contexte ?? '—' }}</p>
        <p><strong>Problématique :</strong> {{ $demande->problematique ?? '—' }}</p>
    </section>

    <section class="panel">
        <h3>Objectifs</h3>
        <p>{{ $demande->objectif_general ?? '—' }}</p>
        @if($demande->objectifs_specifiques)
            <ol>
                @foreach($demande->objectifs_specifiques as $obj)
                    <li>{{ $obj }}</li>
                @endforeach
            </ol>
        @endif
    </section>

    <section class="panel">
        <h3>Périmètre</h3>
        <p>{{ $demande->description_fonctionnelle ?? '—' }}</p>
        <p><strong>Utilisateurs :</strong> {{ $demande->utilisateurs_cibles ?? '—' }}</p>
    </section>

    @include('partials.demande-historique-link', [
        'demande' => $demande,
        'href' => route('agent.demandes.historique', $demande),
    ])
</div>
@endsection
