<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? config('app.name') }}</title>

    @if(!empty($metaDescription ?? null))
        <meta name="description" content="{{ $metaDescription }}">
    @endif

    {{-- FAVICONS --}}
    <link rel="icon" type="image/png" sizes="96x96" href="{{ config('filesystems.disks.spaces.url') }}/favicon/favicon-96x96.png" />
    <link rel="icon" type="image/svg+xml" href="{{ config('filesystems.disks.spaces.url') }}/favicon/favicon.svg" />
    <link rel="shortcut icon" href="{{ config('filesystems.disks.spaces.url') }}/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ config('filesystems.disks.spaces.url') }}/favicon/apple-touch-icon.png" />
    <link rel="manifest" href="{{ config('filesystems.disks.spaces.url') }}/favicon/site.webmanifest" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-slate-900 antialiased">
    @auth
        @if(session('success'))
            <div class="fixed top-4 right-4 z-50 max-w-sm w-full">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow">
                    {{ session('success') }}
                </div>
            </div>
        @endif
    @endauth
    {{ $slot }}
</body>
</html>