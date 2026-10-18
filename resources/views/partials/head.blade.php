{{-- Shared <head> assets for the layout and the standalone home page. --}}
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('brand/favicon-32.png') }}">
<link rel="icon" type="image/png" sizes="64x64" href="{{ asset('brand/favicon-64.png') }}">
<link rel="apple-touch-icon" href="{{ asset('brand/apple-touch-icon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@vite(['resources/css/app.css', 'resources/js/app.js'])
