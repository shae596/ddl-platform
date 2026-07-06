@extends('layouts.secretariat')

@section('title', 'Tableau de bord Secrétariat')

@section('content')
<div class="kpi-grid">
    <div class="kpi-card kpi-card--blue">
        <span class="kpi-card__value">{{ $stats['a_recevoir'] }}</span>
        <span class="kpi-card__label">À recevoir</span>
    </div>
    <div class="kpi-card">
        <span class="kpi-card__value">{{ $stats['a_transferer'] }}</span>
        <span class="kpi-card__label">À transférer à la DI</span>
    </div>
    <div class="kpi-card kpi-card--green">
        <span class="kpi-card__value">{{ $stats['transferees'] }}</span>
        <span class="kpi-card__label">Transférées</span>
    </div>
</div>

<div class="panel-grid">
    <section class="panel">
        <div class="panel__header">
            <h2>Cahiers des charges récents</h2>
            <a href="{{ route('secretariat.demandes.index') }}" class="link">Voir tout</a>
        </div>
        @include('secretariat.partials.demandes-table', ['demandes' => $demandes, 'compact' => true])
    </section>

    <section class="panel" id="notifications">
        <div class="panel__header">
            <h2>Notifications</h2>
        </div>
        @forelse($notifications as $notif)
            <x-notification-item
                :notif="$notif"
                :href="$notif->demande_id ? route('secretariat.demandes.show', $notif->demande_id) : null"
            />
        @empty
            <p class="text-muted">Aucune notification.</p>
        @endforelse
    </section>
</div>
@endsection
