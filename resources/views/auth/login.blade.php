@extends('layouts.guest')

@section('title', 'Connexion — ' . config('ddl.acronym'))

@section('content')
<div class="login-page">
    <div class="login-card">
        <x-logo-ceni class="login-card__logo" />

        <div class="login-card__intro">
            <h1>{{ config('ddl.acronym') }}</h1>
            <p class="login-card__expansion">{{ config('ddl.expansion') }}</p>
            <p class="login-card__subtitle">{{ config('ddl.title') }}</p>
            <p class="login-card__org">{{ config('ddl.organization_full') }}</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="login-form">
            @csrf

            <div class="form-group">
                <label for="identifiant">E-mail ou téléphone</label>
                <input
                    id="identifiant"
                    type="text"
                    name="identifiant"
                    value="{{ old('identifiant') }}"
                    placeholder="vous@ceni.cd ou 0890000000"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input id="password" type="password" name="password" required>
            </div>

            @if ($errors->any())
                <div class="alert alert-error" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
        </form>

        <p class="login-card__footer">© {{ config('ddl.organization') }} — {{ date('Y') }}</p>
    </div>
</div>
@endsection
