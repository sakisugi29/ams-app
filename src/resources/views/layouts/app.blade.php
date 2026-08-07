<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <title>ams-app</title>
    @yield('css')
</head>
<body>
    <header class="header">
        <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="COACHTECH" class="logo">
        <nav class="header_nav">
            @yield('nav')
        </nav>
    </header>
    <main class="main">
        @yield('content')
    </main>
</body>
</html>