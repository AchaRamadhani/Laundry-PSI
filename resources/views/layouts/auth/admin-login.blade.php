@props(['title' => null])

<!doctype html>
<html lang="id">
<head>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('css/admin-login.css') }}?v={{ filemtime(public_path('css/admin-login.css')) }}">
</head>
<body class="admin-login-body">
    <main class="admin-login-page">
        <div class="admin-login-card">
            {{ $slot }}
        </div>
    </main>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>
</html>
