@extends('layouts.admin')

@section('title', 'Utilisateurs')

@section('content')
<div class="toolbar">
    <form method="GET" class="toolbar__filters">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher…">
        <select name="role">
            <option value="">Tous les rôles</option>
            @foreach($roles as $role)
                <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </select>
        <select name="actif">
            <option value="">Actif / inactif</option>
            <option value="1" @selected(request('actif') === '1')>Actifs</option>
            <option value="0" @selected(request('actif') === '0')>Inactifs</option>
        </select>
        <button type="submit" class="btn btn-outline">Filtrer</button>
    </form>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Nouvel utilisateur</a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Nom</th>
            <th>E-mail</th>
            <th>Rôle</th>
            <th>Service</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users as $user)
            <tr>
                <td>{{ $user->fullName() }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->role->label() }}</td>
                <td>{{ $user->service ?? '—' }}</td>
                <td>
                    @if($user->actif)
                        <span class="badge badge-green">Actif</span>
                    @else
                        <span class="badge badge-gray">Inactif</span>
                    @endif
                </td>
                <td class="actions">
                    <a href="{{ route('admin.users.edit', $user) }}">Modifier</a>
                    @if($user->actif && $user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:inline;"
                              data-confirm="Désactiver le compte de {{ $user->fullName() }} ?"
                              data-confirm-title="Désactiver l'utilisateur"
                              data-confirm-variant="danger"
                              data-confirm-accept="Désactiver">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-link btn-link--danger">Désactiver</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-muted">Aucun utilisateur.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="pagination-wrap">{{ $users->links() }}</div>
@endsection
