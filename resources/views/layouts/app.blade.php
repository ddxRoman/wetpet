<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ===================== SEO-БЛОК ===================== --}}

    {{-- Title: приоритет $seoMeta > @section('title') > дефолт --}}
    @if(!empty($seoMeta['title']))
        <title>{{ $seoMeta['title'] }}</title>
    @elseif(Route::currentRouteName() === 'account.account')
        <title>Личный кабинет {{ $user->nickname ?? '' }}</title>
    @elseif(Route::currentRouteName() === 'auth.login')
        <title>Авторизация — Зверозор</title>
    @else
        <title>@yield('title', 'Зверозор — ветеринарный агрегатор')</title>
    @endif

    {{-- Description --}}
    @if(!empty($seoMeta['description']))
        <meta name="description" content="{{ $seoMeta['description'] }}">
    @else
        <meta name="description" content="@yield('description', 'Зверозор — найдите лучшую ветеринарную клинику, врача или специалиста рядом с вами. Читайте отзывы реальных людей.')">
    @endif

    {{-- Canonical --}}
    <link rel="canonical" href="{{ $seoMeta['canonical'] ?? url()->current() }}">

    {{-- Robots --}}
    <meta name="robots" content="{{ $seoMeta['robots'] ?? 'index, follow' }}">

    {{-- ── Open Graph (Facebook, VK, Telegram) ── --}}
    <meta property="og:type"        content="{{ $seoMeta['og_type'] ?? 'website' }}">
    <meta property="og:site_name"   content="Зверозор">
    <meta property="og:locale"      content="ru_RU">
    <meta property="og:url"         content="{{ $seoMeta['canonical'] ?? url()->current() }}">
    <meta property="og:title"       content="{{ $seoMeta['og_title'] ?? ($seoMeta['title'] ?? config('app.name')) }}">
    <meta property="og:description" content="{{ $seoMeta['og_description'] ?? ($seoMeta['description'] ?? '') }}">
    @if(!empty($seoMeta['image']))
        <meta property="og:image"        content="{{ $seoMeta['image'] }}">
        <meta property="og:image:width"  content="1200">
        <meta property="og:image:height" content="630">
    @endif
    @if(!empty($seoMeta['og_article_published_at']))
        <meta property="article:published_time" content="{{ $seoMeta['og_article_published_at'] }}">
    @endif
    @if(!empty($seoMeta['og_article_modified_at']))
        <meta property="article:modified_time"  content="{{ $seoMeta['og_article_modified_at'] }}">
    @endif

    {{-- ── Twitter Card ── --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="{{ $seoMeta['og_title'] ?? ($seoMeta['title'] ?? config('app.name')) }}">
    <meta name="twitter:description" content="{{ $seoMeta['og_description'] ?? ($seoMeta['description'] ?? '') }}">
    @if(!empty($seoMeta['image']))
        <meta name="twitter:image" content="{{ $seoMeta['image'] }}">
    @endif

    {{-- ── JSON-LD Schema.org (для Google) ── --}}
    @if(!empty($seoMeta['schema']))
        <script type="application/ld+json">{!! $seoMeta['schema'] !!}</script>
    @endif

    {{-- Дополнительные мета-теги из дочерних шаблонов --}}
    @yield('seo')

    {{-- ====================================================== --}}

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite([
        'resources/css/main.css',
        'resources/css/mobile.css',
        'resources/sass/app.scss',
        'resources/js/app.js'
    ])
</head>
<body>
    <div id="app">
        <main>
            @yield('content')
        </main>
    </div>
</body>
</html>
