@extends('layouts.developpeur')

@section('title', 'Tableau de bord — Développeur')

@section('content')
<div class="kpi-grid">
    <div class="kpi-card kpi-card--blue">
        <span class="kpi-card__value">{{ $stats['a_demarrer'] }}</span>
        <span class="kpi-card__label">À démarrer</span>
    </div>
    <div class="kpi-card">
        <span class="kpi-card__value">{{ $stats['en_developpement'] }}</span>
        <span class="kpi-card__label">En développement</span>
    </div>
    <div class="kpi-card kpi-card--green">
        <span class="kpi-card__value">{{ $stats['en_test'] }}</span>
        <span class="kpi-card__label">En test</span>
    </div>
    <div class="kpi-card">
        <span class="kpi-card__value">{{ $stats['terminees'] }}</span>
        <span class="kpi-card__label">Terminées</span>
    </div>
</div>

<div class="panel-grid">
    <section class="panel">
        <div class="panel__header">
            <h2>Mes affectations récentes</h2>
            <a href="{{ route('developpeur.demandes.index') }}" class="link">Voir tout</a>
        </div>
        @include('developpeur.partials.demandes-table', ['demandes' => $demandes, 'compact' => true])
    </section>

    <section class="panel" id="notifications">
        <div class="panel__header">
            <h2>Notifications</h2>
        </div>
        @forelse($notifications as $notif)
            <x-notification-item
                :notif="$notif"
                :href="$notif->demande_id ? route('developpeur.demandes.show', $notif->demande_id) : null"
            />
        @empty
            <p class="text-muted">Aucune notification.</p>
        @endforelse
    </section>
</div>
@endsection
