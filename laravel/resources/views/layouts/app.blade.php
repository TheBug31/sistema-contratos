<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard')</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    {{-- Config de Tailwind: colores y fuentes del sistema --}}
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#1e518f',
                            50:  '#eef3fa',
                            100: '#d5e2f3',
                            200: '#abc5e7',
                            300: '#7aa3d7',
                            400: '#4a80c7',
                            500: '#1e518f',
                            600: '#184273',
                            700: '#123258',
                            800: '#0c213c',
                            900: '#061120',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                    },
                },
            },
        }
    </script>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Material Symbols (disponible para cualquier vista que lo necesite) --}}
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    <style>
        body { font-family: 'Inter', sans-serif; }

        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 22px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-smoothing: antialiased;
        }
    </style>

    {{-- Estilos adicionales por vista --}}
    @stack('styles')
</head>

<body class="bg-gray-50 text-gray-900 antialiased">

<div x-data="{ open: false }" class="flex h-screen bg-gray-100">

    @include('layouts.sidebar')

    <!-- Overlay móvil -->
    <div
        x-show="open"
        @click="open = false"
        class="fixed inset-0 bg-black/40 z-30 md:hidden">
    </div>

    <div class="flex-1 flex flex-col">

        @include('layouts.navbar')

        <main class="flex-1 overflow-y-auto p-8">
            @yield('content')
        </main>

    </div>

</div>

{{-- Scripts adicionales por vista --}}
@stack('scripts')

</body>
</html>