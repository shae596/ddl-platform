@extends('layouts.di')

@section('title', $demande->numero ?? 'Examen du cahier des charges')

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
            <a href="{{ route('di.demandes.cahier', $demande) }}" class="btn btn-primary">Télécharger le PDF</a>
        @endif
        @if($demande->statut->value === 'TRANSFEREE_DI')
            <form method="POST" action="{{ route('di.demandes.prendre-en-charge', $demande) }}">
                @csrf
                <button type="submit" class="btn btn-primary">Prendre en charge</button>
            </form>
        @endif
        @if(in_array($demande->statut->value, ['TRANSFEREE_DI', 'EN_ANALYSE'], true))
            <form method="POST" action="{{ route('di.demandes.mettre-en-attente', $demande) }}"
                  data-confirm="Mettre cette demande en attente ?"
                  data-confirm-title="Mise en attente"
                  data-confirm-accept="Mettre en attente">
                @csrf
                <button type="submit" class="btn btn-outline">Mettre en attente</button>
            </form>
        @endif
        @if($demande->statut->value === 'EN_ATTENTE')
            <form method="POST" action="{{ route('di.demandes.reprendre', $demande) }}">
                @csrf
                <button type="submit" class="btn btn-primary">Reprendre l'analyse</button>
            </form>
        @endif
        @if(in_array($demande->statut->value, ['EN_ANALYSE', 'EN_ATTENTE'], true))
            <form method="POST" action="{{ route('di.demandes.valider', $demande) }}"
                  data-confirm="Valider ce cahier des charges ?"
                  data-confirm-title="Validation"
                  data-confirm-accept="Valider">
                @csrf
                <button type="submit" class="btn btn-primary">Valider</button>
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
            <dt>Soumission</dt><dd>{{ $demande->date_soumission?->format('d/m/Y H:i') ?? '—' }}</dd>
            <dt>Délai prévisionnel</dt><dd>{{ $demande->delai_previsionnel?->format('d/m/Y') ?? 'Non défini' }}</dd>
            @if($demande->motif_rejet)
                <dt>Motif rejet</dt><dd class="text-danger">{{ $demande->motif_rejet }}</dd>
            @endif
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

    @if($demande->statut->value === 'EN_ANALYSE')
        <section class="panel panel--full">
            <h3>Décision — analyse en cours</h3>
            <div class="action-panels">
                <form method="POST" action="{{ route('di.demandes.rejeter', $demande) }}" class="action-panel"
                      data-confirm="Confirmer le rejet de ce cahier des charges ? L'agent sera notifié."
                      data-confirm-title="Rejeter la demande"
                      data-confirm-variant="danger"
                      data-confirm-accept="Rejeter">
                    @csrf
                    <h4>Rejeter</h4>
                    <div class="form-group">
                        <label for="motif_rejet">Motif (min. 20 caractères) *</label>
                        <textarea id="motif_rejet" name="motif_rejet" rows="3" required minlength="20">{{ old('motif_rejet') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Rejeter</button>
                </form>

                <form method="POST" action="{{ route('di.demandes.demander-correction', $demande) }}" class="action-panel">
                    @csrf
                    <h4>Demander des informations complémentaires</h4>
                    <div class="form-group">
                        <label for="commentaire">Message à l'agent (min. 20 caractères) *</label>
                        <textarea id="commentaire" name="commentaire" rows="3" required minlength="20">{{ old('commentaire') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-outline">Renvoyer à l'agent</button>
                </form>
            </div>
        </section>
    @endif

    <section class="panel">
        <h3>Délai prévisionnel de traitement</h3>
        <form method="POST" action="{{ route('di.demandes.delai', $demande) }}" class="form-inline">
            @csrf
            <div class="form-group">
                <label for="delai_previsionnel">Date prévisionnelle</label>
                <input type="date" id="delai_previsionnel" name="delai_previsionnel"
                       value="{{ old('delai_previsionnel', $demande->delai_previsionnel?->format('Y-m-d')) }}"
                       min="{{ now()->format('Y-m-d') }}">
            </div>
            <button type="submit" class="btn btn-outline btn-sm">Enregistrer</button>
        </form>
    </section>

    @if($demande->statut->value === 'VALIDEE')
        <section class="panel panel--full">
            <h3>Affecter à un développeur</h3>
            <form method="POST" action="{{ route('di.demandes.affecter', $demande) }}"
                  data-confirm="Affecter cette demande au(x) développeur(s) sélectionné(s) ?"
                  data-confirm-title="Affectation"
                  data-confirm-accept="Affecter">
                @csrf
                <div class="form-group">
                    <label for="affectation-devs">Développeur(s)</label>
                    <div class="checkbox-list" id="affectation-devs">
                    @forelse($developpeurs as $dev)
                        <label class="checkbox-row">
                            <input type="checkbox" name="developpeur_ids[]" value="{{ $dev->id }}"
                                @checked(in_array($dev->id, old('developpeur_ids', [])))>
                            <span>{{ $dev->fullName() }} — {{ $dev->email }}</span>
                        </label>
                    @empty
                        <p class="text-muted">Aucun développeur actif disponible.</p>
                    @endforelse
                    </div>
                </div>
                @if($developpeurs->isNotEmpty())
                    <button type="submit" class="btn btn-primary">Affecter</button>
                @endif
            </form>
        </section>
    @endif

    @if($demande->affectationsDev->where('actif', true)->isNotEmpty())
        <section class="panel">
            <h3>Développeurs affectés</h3>
            <ul>
                @foreach($demande->affectationsDev->where('actif', true) as $aff)
                    <li>{{ $aff->developpeur?->fullName() }} — depuis {{ $aff->created_at?->format('d/m/Y') }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="panel panel--full">
        <h3>Commentaires</h3>
        @forelse($demande->commentaires->sortByDesc('created_at') as $commentaire)
            <div class="comment-item">
                <strong>{{ $commentaire->auteur?->fullName() }}</strong>
                @if($commentaire->interne)
                    <span class="badge badge-gray">Interne</span>
                @endif
                <small>{{ $commentaire->created_at?->format('d/m/Y H:i') }}</small>
                <p>{{ $commentaire->contenu }}</p>
            </div>
        @empty
            <p class="text-muted">Aucun commentaire.</p>
        @endforelse

        <form method="POST" action="{{ route('di.demandes.commenter', $demande) }}" style="margin-top:1rem;">
            @csrf
            <div class="form-group">
                <label for="contenu">Ajouter un commentaire</label>
                <textarea id="contenu" name="contenu" rows="3" required>{{ old('contenu') }}</textarea>
            </div>
            <label class="checkbox-row">
                <input type="checkbox" name="interne" value="1" @checked(old('interne'))>
                Commentaire interne (non visible par l'agent)
            </label>
            <button type="submit" class="btn btn-outline btn-sm">Publier</button>
        </form>
    </section>

    @include('partials.demande-historique-link', [
        'demande' => $demande,
        'href' => route('di.demandes.historique', $demande),
    ])
</div>

<p class="text-muted" style="margin-top:1rem;">
    <a href="{{ route('di.demandes.index') }}">← Retour à la liste</a>
</p>
@endsection
