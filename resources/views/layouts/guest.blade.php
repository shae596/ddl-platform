<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('ddl.acronym') . ' — ' . config('ddl.organization'))</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/logo-ceni-rdc-placeholder.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="guest-body">
    @yield('content')
</body>
</html>
