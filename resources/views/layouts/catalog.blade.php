
    {{-- Хедер --}}
    @include('layouts.header')

    {{-- Контент --}}
    <main class="flex-grow-1">
        @yield('content')
    </main>

    {{-- Футер --}}
    @include('layouts.footer')

