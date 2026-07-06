@extends('layouts.agent')

@section('title', 'Tableau de bord Agent')

@section('content')
<div class="kpi-grid">
    <div class="kpi-card">
        <span class="kpi-card__value">{{ $stats['brouillons'] }}</span>
        <span class="kpi-card__label">Brouillons</span>
    </div>
    <div class="kpi-card kpi-card--blue">
        <span class="kpi-card__value">{{ $stats['en_cours'] }}</span>
        <span class="kpi-card__label">En cours</span>
    </div>
    <div class="kpi-card kpi-card--green">
        <span class="kpi-card__value">{{ $stats['terminees'] }}</span>
        <span class="kpi-card__label">Terminées</span>
    </div>
</div>

<div class="panel-grid">
    <section class="panel">
        <div class="panel__header">
            <h2>Mes demandes récentes</h2>
            <a href="{{ route('agent.demandes.index') }}" class="link">Voir tout</a>
        </div>
        @include('agent.partials.demandes-table', ['demandes' => $demandes, 'compact' => true])
    </section>

    <section class="panel" id="notifications">
        <div class="panel__header">
            <h2>Notifications</h2>
        </div>
        @forelse($notifications as $notif)
            <x-notification-item
                :notif="$notif"
                :href="$notif->demande_id ? route('agent.demandes.show', $notif->demande_id) : null"
            />
        @empty
            <p class="text-muted">Aucune notification.</p>
        @endforelse
    </section>
</div>
@endsection
