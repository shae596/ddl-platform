<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('ddl.acronym')) — Secrétariat</title>
    <link rel="icon" href="{{ asset(config('ddl.logo')) }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="app-body">
    <header class="app-header">
        <div class="app-header__inner">
            <div class="app-brand">
                <x-logo-ceni class="app-brand__logo" />
                <span class="app-brand__name">{{ config('ddl.acronym') }}</span>
            </div>
            <div class="app-user">
                @if(isset($notificationsNonLues) && $notificationsNonLues > 0)
                    <a href="{{ route('secretariat.dashboard') }}#notifications" class="notif-badge-link" title="Notifications non lues">
                        <span class="notif-badge">{{ $notificationsNonLues }}</span>
                    </a>
                @endif
                <span>{{ auth()->user()->fullName() }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline">Déconnexion</button>
                </form>
            </div>
        </div>
    </header>

    <div class="app-shell">
        <aside class="app-sidebar">
            <nav class="sidebar-nav">
                <a href="{{ route('secretariat.dashboard') }}" class="{{ request()->routeIs('secretariat.dashboard') ? 'active' : '' }}">Tableau de bord</a>
                <a href="{{ route('secretariat.demandes.index') }}" class="{{ request()->routeIs('secretariat.demandes.*') ? 'active' : '' }}">Cahiers des charges</a>
            </nav>
        </aside>

        <main class="app-main app-main--with-sidebar">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <h1 class="page-title">@yield('title')</h1>
            @yield('content')
        </main>
    </div>

    <x-confirm-modal />
    <script src="{{ asset('js/confirm.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
