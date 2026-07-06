<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('ddl.acronym'))</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/logo-ceni-rdc-placeholder.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="app-body">
    <header class="app-header">
        <div class="app-header__inner">
            <div class="app-brand">
                <img src="{{ asset('assets/logo-ceni-rdc-placeholder.svg') }}" alt="{{ config('ddl.organization') }}" class="app-brand__logo">
                <span class="app-brand__name">{{ config('ddl.acronym') }}</span>
            </div>
            <div class="app-user">
                <span>{{ auth()->user()->fullName() }} ({{ auth()->user()->role->label() }})</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline">Déconnexion</button>
                </form>
            </div>
        </div>
    </header>

    <main class="app-main">
        <h1 class="page-title">@yield('title')</h1>
        @yield('content')
    </main>
</body>
</html>
