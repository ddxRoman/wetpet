{{-- Хедер --}}
    @include('layouts.header')

    @vite(['resources/css/catalog-cards.css'])


    {{-- Контент --}}
    <main class="flex-grow-1">
        @yield('content')
    </main>

    {{-- Футер --}}
    @include('layouts.footer')