@extends('layouts.developpeur')

@section('title', $demande->numero ?? 'Détail affectation')

@section('content')
<div class="detail-header">
    <div>
        <p class="detail-meta">{{ $demande->numero }}</p>
        <h2 class="detail-title">{{ $demande->titre }}</h2>
        <div class="detail-badges">
            <span class="badge {{ $demande->statut->badgeClass() }}">{{ $demande->statut->label() }}</span>
            <span class="badge {{ $demande->priorite->badgeClass() }}">{{ $demande->priorite->label() }}</span>
        </div>
    </div>
    <div class="detail-actions">
        @if($cahierPdf ?? null)
            <a href="{{ route('developpeur.demandes.cahier', $demande) }}" class="btn btn-primary">Télécharger le cahier des charges (PDF)</a>
        @endif
        @if($demande->statut->value === 'AFFECTEE')
            <form method="POST" action="{{ route('developpeur.demandes.demarrer', $demande) }}"
                  data-confirm="Démarrer le développement de cette demande ?"
                  data-confirm-title="Démarrer"
                  data-confirm-accept="Démarrer">
                @csrf
                <button type="submit" class="btn btn-primary">Démarrer le développement</button>
            </form>
        @endif
        @if($demande->statut->value === 'EN_DEVELOPPEMENT')
            <form method="POST" action="{{ route('developpeur.demandes.passer-en-test', $demande) }}"
                  data-confirm="Passer cette demande en phase de test ? La DI sera notifiée."
                  data-confirm-title="Passer en test"
                  data-confirm-accept="Confirmer">
                @csrf
                <button type="submit" class="btn btn-primary">Passer en test</button>
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
            <dt>Délai prévisionnel DI</dt><dd>{{ $demande->delai_previsionnel?->format('d/m/Y') ?? '—' }}</dd>
            <dt>Livraison souhaitée</dt><dd>{{ $demande->date_souhaitee_livraison?->format('d/m/Y') ?? '—' }}</dd>
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
        <h3>Périmètre fonctionnel</h3>
        <p>{{ $demande->description_fonctionnelle ?? '—' }}</p>
        <p><strong>Utilisateurs cibles :</strong> {{ $demande->utilisateurs_cibles ?? '—' }}</p>
    </section>

    @if($demande->statut->value === 'EN_DEVELOPPEMENT')
        <section class="panel panel--full">
            <h3>Passer en test</h3>
            <form method="POST" action="{{ route('developpeur.demandes.passer-en-test', $demande) }}"
                  data-confirm="Passer cette demande en phase de test ? La DI sera notifiée."
                  data-confirm-title="Passer en test"
                  data-confirm-accept="Confirmer">
                @csrf
                <div class="form-group">
                    <label for="commentaire">Commentaire pour la DI (optionnel)</label>
                    <textarea id="commentaire" name="commentaire" rows="3" placeholder="Notes sur la livraison en test…">{{ old('commentaire') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Passer en test</button>
            </form>
        </section>
    @endif

    <section class="panel panel--full">
        <h3>Commentaires</h3>
        @forelse($demande->commentaires->where('interne', false)->sortByDesc('created_at') as $commentaire)
            <div class="comment-item">
                <strong>{{ $commentaire->auteur?->fullName() }}</strong>
                <small>{{ $commentaire->created_at?->format('d/m/Y H:i') }}</small>
                <p>{{ $commentaire->contenu }}</p>
            </div>
        @empty
            <p class="text-muted">Aucun commentaire public.</p>
        @endforelse

        <form method="POST" action="{{ route('developpeur.demandes.commenter', $demande) }}" style="margin-top:1rem;">
            @csrf
            <div class="form-group">
                <label for="contenu">Ajouter un commentaire (visible par la DI)</label>
                <textarea id="contenu" name="contenu" rows="3" required>{{ old('contenu') }}</textarea>
            </div>
            <button type="submit" class="btn btn-outline btn-sm">Publier</button>
        </form>
    </section>

    @include('partials.demande-historique-link', [
        'demande' => $demande,
        'href' => route('developpeur.demandes.historique', $demande),
    ])
</div>

<p class="text-muted" style="margin-top:1rem;">
    <a href="{{ route('developpeur.demandes.index') }}">← Retour à la liste</a>
</p>
@endsection
