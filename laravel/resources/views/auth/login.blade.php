<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Iniciar sesión — ContractSystem</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: '#1e518f' },
                    fontFamily: { sans: ['Inter', 'ui-sans-serif'] },
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal; font-style: normal;
            font-size: 22px; line-height: 1;
            display: inline-block; white-space: nowrap;
            -webkit-font-smoothing: antialiased;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 antialiased">
<div class="flex min-h-screen">

    {{-- ── Panel izquierdo ── --}}
    <div class="hidden lg:flex lg:w-1/2 items-center justify-center relative overflow-hidden"
         style="background-color: #1e518f;">

        {{-- Patrón decorativo --}}
        <div class="absolute inset-0 pointer-events-none opacity-10"
             style="background: radial-gradient(circle at 30% 50%, white, transparent 60%);"></div>

        {{-- Círculos decorativos --}}
        <div class="absolute -bottom-20 -left-20 size-80 rounded-full opacity-10"
             style="background: rgba(255,255,255,.15);"></div>
        <div class="absolute -top-10 -right-10 size-60 rounded-full opacity-10"
             style="background: rgba(255,255,255,.10);"></div>

        <div class="relative z-10 flex flex-col items-center text-center px-12 max-w-lg">

            {{-- Logo / ícono de la app --}}
            <div class="size-20 rounded-2xl bg-white/15 flex items-center justify-center mb-8 shadow-xl">
                <span class="material-symbols-outlined text-white" style="font-size:40px;">description</span>
            </div>

            <h1 class="text-white text-3xl font-black tracking-tight mb-4">
                Contract<span style="opacity:.7;">System</span>
            </h1>
            <p class="text-white/70 text-base leading-relaxed">
                Plataforma centralizada de gestión y control de contratos.
            </p>

            {{-- Stats decorativas --}}
            <div class="mt-12 grid grid-cols-3 gap-6 w-full">
                @foreach ([['description','Contratos','Gestión centralizada'],['group','Equipos','Multi-operación'],['verified','Seguro','Control de roles']] as [$icon, $title, $sub])
                <div class="text-center">
                    <div class="size-10 rounded-xl bg-white/10 flex items-center justify-center mx-auto mb-2">
                        <span class="material-symbols-outlined text-white/80" style="font-size:20px;">{{ $icon }}</span>
                    </div>
                    <p class="text-white text-xs font-bold">{{ $title }}</p>
                    <p class="text-white/50 text-[10px] mt-0.5">{{ $sub }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Ícono decorativo fondo --}}
        <div class="absolute bottom-8 right-8 text-white/10 pointer-events-none">
            <span class="material-symbols-outlined" style="font-size:120px;">shield_with_heart</span>
        </div>
    </div>

    {{-- ── Panel derecho: formulario ── --}}
    <div class="w-full lg:w-1/2 flex flex-col justify-center items-center bg-white px-6 py-12 md:px-12 lg:px-24">
        <div class="w-full max-w-md">

            {{-- Logo móvil --}}
            <div class="lg:hidden flex justify-center mb-10">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-xl flex items-center justify-center"
                         style="background-color:#1e518f;">
                        <span class="material-symbols-outlined text-white" style="font-size:22px;">description</span>
                    </div>
                    <span class="text-xl font-black text-slate-800">ContractSystem</span>
                </div>
            </div>

            {{-- Encabezado --}}
            <div class="mb-8 text-center lg:text-left">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Bienvenido</h2>
                <p class="text-slate-500">Ingresa tus credenciales para continuar</p>
            </div>

            {{-- Formulario --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Correo electrónico
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                              style="font-size:18px;">mail</span>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="ejemplo@correo.com"
                            required
                            autofocus
                            class="w-full pl-10 pr-4 py-3 rounded-xl border text-sm transition
                                   {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}
                                   focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                        >
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined" style="font-size:14px;">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Password --}}
                <div x-data="{ show: false }">
                    <div class="flex justify-between mb-2">
                        <label class="text-sm font-semibold text-slate-700">Contraseña</label>
                    </div>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                              style="font-size:18px;">lock</span>
                        <input
                            :type="show ? 'text' : 'password'"
                            name="password"
                            placeholder="••••••••"
                            required
                            class="w-full pl-10 pr-10 py-3 rounded-xl border border-slate-300 bg-white text-sm
                                   focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition"
                        >
                        <button type="button" @click="show = !show"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                            <span class="material-symbols-outlined" style="font-size:18px;"
                                  x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined" style="font-size:14px;">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Recordarme --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember"
                           class="rounded border-slate-300 focus:ring-primary/30"
                           style="color:#1e518f;">
                    <label for="remember" class="text-sm text-slate-600 cursor-pointer">
                        Recordarme
                    </label>
                </div>

                {{-- Error general --}}
                @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
                    <div class="flex items-center gap-2 p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm">
                        <span class="material-symbols-outlined" style="font-size:18px;">error</span>
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Botón --}}
                <button type="submit"
                        class="w-full py-3 rounded-xl text-white text-sm font-bold shadow-sm
                               hover:opacity-90 active:scale-[.98] transition-all duration-150"
                        style="background-color:#1e518f;">
                    Iniciar sesión
                </button>

            </form>

            {{-- Footer --}}
            <div class="mt-10 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-center gap-3 text-xs text-slate-400">
                <span>© {{ date('Y') }} ContractSystem</span>
                <span class="hidden sm:block">·</span>
                <span>Todos los derechos reservados</span>
            </div>

        </div>
    </div>

</div>

{{-- Alpine para el toggle de password --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>