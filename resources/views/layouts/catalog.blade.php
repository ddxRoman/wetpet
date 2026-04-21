<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- Хедер --}}
    @include('layouts.header')

    {{-- Контент --}}
    <main class="flex-grow-1">
        @yield('content')
    </main>

    {{-- Футер --}}
    @include('layouts.footer')

</body>
</html>
