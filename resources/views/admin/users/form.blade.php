@extends('layouts.admin')

@section('title', $user->exists ? 'Modifier utilisateur' : 'Nouvel utilisateur')

@section('content')
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="demande-form">
    @csrf
    @if($user->exists)
        @method('PUT')
    @endif

    <fieldset class="form-section">
        <legend>Identité</legend>
        <div class="form-grid">
            <div class="form-group">
                <label for="prenom">Prénom *</label>
                <input id="prenom" name="prenom" value="{{ old('prenom', $user->prenom) }}" required>
            </div>
            <div class="form-group">
                <label for="nom">Nom *</label>
                <input id="nom" name="nom" value="{{ old('nom', $user->nom) }}" required>
            </div>
            <div class="form-group form-group--full">
                <label for="email">E-mail *</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input id="telephone" name="telephone" value="{{ old('telephone', $user->telephone) }}">
            </div>
            <div class="form-group">
                <label for="service">Service</label>
                <input id="service" name="service" value="{{ old('service', $user->service) }}">
            </div>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>Accès</legend>
        <div class="form-grid">
            <div class="form-group">
                <label for="role">Rôle *</label>
                <select id="role" name="role" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role', $user->role?->value) === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe {{ $user->exists ? '(laisser vide pour ne pas changer)' : '*' }}</label>
                <input id="password" type="password" name="password" {{ $user->exists ? '' : 'required' }}>
            </div>
            @if($user->exists && $user->id !== auth()->id())
                <div class="form-group">
                    <label class="checkbox-row">
                        <input type="hidden" name="actif" value="0">
                        <input type="checkbox" name="actif" value="1" @checked(old('actif', $user->actif))>
                        Compte actif
                    </label>
                </div>
            @endif
        </div>
    </fieldset>

    <div class="form-actions">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Annuler</a>
        <button type="submit" class="btn btn-primary">{{ $user->exists ? 'Enregistrer' : 'Créer' }}</button>
    </div>
</form>
@endsection
