@extends('layouts.secretariat')

@section('title', $demande->numero ?? 'Détail du cahier des charges')

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
            <a href="{{ route('secretariat.demandes.cahier', $demande) }}" class="btn btn-primary">Télécharger le cahier des charges (PDF)</a>
        @endif
        @if($demande->statut->value === 'SOUMISE')
            <form method="POST" action="{{ route('secretariat.demandes.recevoir', $demande) }}">
                @csrf
                <button type="submit" class="btn btn-primary">Accuser réception</button>
            </form>
        @endif
        @if($demande->statut->value === 'RECUE_SECRETARIAT')
            <form method="POST" action="{{ route('secretariat.demandes.transferer', $demande) }}"
                  data-confirm="Transférer ce cahier des charges à la Direction Informatique ?"
                  data-confirm-title="Transférer à la DI"
                  data-confirm-accept="Transférer">
                @csrf
                <button type="submit" class="btn btn-primary">Transférer à la DI</button>
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
            <dt>Date de soumission</dt><dd>{{ $demande->date_soumission?->format('d/m/Y H:i') ?? '—' }}</dd>
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
        'href' => route('secretariat.demandes.historique', $demande),
    ])
</div>

<p class="text-muted" style="margin-top:1rem;">
    <a href="{{ route('secretariat.demandes.index') }}">← Retour à la liste</a>
</p>
@endsection
