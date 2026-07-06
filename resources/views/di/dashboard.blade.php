@extends('layouts.di')

@section('title', 'Tableau de bord — Direction Informatique')

@section('content')
<div class="kpi-grid">
    <div class="kpi-card kpi-card--blue">
        <span class="kpi-card__value">{{ $stats['a_examiner'] }}</span>
        <span class="kpi-card__label">À examiner</span>
    </div>
    <div class="kpi-card">
        <span class="kpi-card__value">{{ $stats['en_cours'] }}</span>
        <span class="kpi-card__label">En cours (analyse / validées)</span>
    </div>
    <div class="kpi-card kpi-card--green">
        <span class="kpi-card__value">{{ $stats['en_developpement'] }}</span>
        <span class="kpi-card__label">En développement</span>
    </div>
    <div class="kpi-card">
        <span class="kpi-card__value">{{ $stats['rejetees'] }}</span>
        <span class="kpi-card__label">Rejetées</span>
    </div>
</div>

<div class="panel-grid">
    <section class="panel">
        <div class="panel__header">
            <h2>Cahiers des charges récents</h2>
            <a href="{{ route('di.demandes.index') }}" class="link">Voir tout</a>
        </div>
        @include('di.partials.demandes-table', ['demandes' => $demandes, 'compact' => true])
    </section>

    <section class="panel" id="notifications">
        <div class="panel__header">
            <h2>Notifications</h2>
        </div>
        @forelse($notifications as $notif)
            <x-notification-item
                :notif="$notif"
                :href="$notif->demande_id ? route('di.demandes.show', $notif->demande_id) : null"
            />
        @empty
            <p class="text-muted">Aucune notification.</p>
        @endforelse
    </section>
</div>
@endsection
