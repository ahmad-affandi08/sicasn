<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name') }}</title>
    <script nonce="{{ Illuminate\Support\Facades\Vite::cspNonce() }}">
        (function () {
            try {
                var tersimpan = localStorage.getItem('tema');
                var gelap = tersimpan ? tersimpan === 'gelap' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', gelap);
            } catch (e) {}
        })();
    </script>
    @viteReactRefresh
    @vite(['resources/js/app.tsx'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
