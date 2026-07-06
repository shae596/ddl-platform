@extends('layouts.admin')

@section('title', 'Administration')

@section('content')
<div class="kpi-grid">
    <div class="kpi-card kpi-card--blue">
        <span class="kpi-card__value">{{ $stats['utilisateurs_actifs'] }}</span>
        <span class="kpi-card__label">Utilisateurs actifs</span>
    </div>
    <div class="kpi-card">
        <span class="kpi-card__value">{{ $stats['utilisateurs'] }}</span>
        <span class="kpi-card__label">Comptes total</span>
    </div>
    <div class="kpi-card kpi-card--green">
        <span class="kpi-card__value">{{ $stats['demandes'] }}</span>
        <span class="kpi-card__label">Demandes total</span>
    </div>
    <div class="kpi-card">
        <span class="kpi-card__value">{{ $stats['demandes_en_cours'] }}</span>
        <span class="kpi-card__label">Demandes en cours</span>
    </div>
</div>

<div class="panel-grid">
    <section class="panel">
        <h3>Utilisateurs par rôle</h3>
        <dl class="detail-dl">
            @foreach($parRole as $role => $count)
                <dt>{{ $role }}</dt><dd>{{ $count }}</dd>
            @endforeach
        </dl>
    </section>

    <section class="panel">
        <div class="panel__header">
            <h2>Activité récente</h2>
            <a href="{{ route('admin.historique.index') }}" class="link">Voir tout</a>
        </div>
        <ul class="timeline">
            @forelse($historiqueRecent as $action)
                <li>
                    <strong>{{ $action->action }}</strong>
                    — {{ $action->demande?->numero ?? '—' }}
                    <small>{{ $action->utilisateur?->fullName() }} · {{ $action->created_at?->format('d/m/Y H:i') }}</small>
                </li>
            @empty
                <li class="text-muted">Aucune action.</li>
            @endforelse
        </ul>
    </section>
</div>
@endsection
