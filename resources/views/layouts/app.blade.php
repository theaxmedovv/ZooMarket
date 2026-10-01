<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ZooMarket') }}</title>
    @include('partials.head')
</head>
<body class="flex min-h-dvh flex-col leading-normal">
    @include('partials.site-header')
    <main>@yield('content')</main>
    @include('partials.site-footer')
</body>
</html>
