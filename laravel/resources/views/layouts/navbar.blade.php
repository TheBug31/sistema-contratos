<header class="h-16 bg-white border-b border-slate-200 shadow-sm flex items-center justify-between px-4 md:px-8 shrink-0">

    {{-- ── Izquierda: toggle móvil + título de página ── --}}
    <div class="flex items-center gap-3">

        {{-- Botón hamburguesa (móvil) --}}
        <button @click="open = !open"
                class="md:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Título dinámico de la sección actual --}}
        <h1 class="text-base font-semibold text-slate-800">
            @yield('title', 'Dashboard')
        </h1>

    </div>

    {{-- ── Derecha: buscador + usuario + logout ── --}}
    <div class="flex items-center gap-3">

        {{-- Buscador (oculto en móvil muy pequeño) --}}
        <form method="GET" action="{{ route('contracts.index') }}" class="relative hidden sm:block">
            <input
                type="text"
                name="search"
                placeholder="Buscar contrato o asesor..."
                value="{{ request('search') }}"
                class="w-52 lg:w-72 pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg
                       placeholder-slate-400 text-slate-700
                       focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary
                       transition-colors"
            >
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
            </svg>
        </form>

        {{-- Separador --}}
        <div class="hidden md:block w-px h-6 bg-slate-200"></div>

        {{-- Usuario + rol + logout en un dropdown Alpine --}}
        <div class="relative" x-data="{ userMenu: false }">

            <button @click="userMenu = !userMenu"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-slate-100 transition-colors">

                {{-- Avatar con iniciales --}}
                <div class="size-8 rounded-full bg-primary flex items-center justify-center shrink-0">
                    <span class="text-xs font-black text-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        {{-- Segunda inicial si hay apellido --}}
                        @php $parts = explode(' ', auth()->user()->name); @endphp
                        {{ isset($parts[1]) ? strtoupper(substr($parts[1], 0, 1)) : '' }}
                    </span>
                </div>

                <div class="hidden md:flex flex-col items-start leading-tight">
                    <span class="text-sm font-semibold text-slate-800 max-w-[130px] truncate">
                        {{ auth()->user()->name }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium">
                        {{ auth()->user()->getRoleNames()->first() ?? 'Usuario' }}
                    </span>
                </div>

                <svg class="w-3.5 h-3.5 text-slate-400 hidden md:block transition-transform duration-200"
                     :class="userMenu ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Dropdown --}}
            <div x-show="userMenu"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.outside="userMenu = false"
                 class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg z-50 overflow-hidden"
                 style="top: 100%;">

                {{-- Info del usuario --}}
                <div class="px-4 py-3 border-b border-slate-100">
                    <p class="text-xs font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                </div>

                {{-- Opciones --}}
                <div class="py-1">
                    <a href="{{ route('profile.index') }}"
                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                        <span class="material-symbols-outlined text-slate-400" style="font-size:18px;">person</span>
                        Mi perfil
                    </a>
                </div>

                {{-- Logout --}}
                <div class="border-t border-slate-100 py-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-2.5 w-full px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors">
                            <span class="material-symbols-outlined text-red-400" style="font-size:18px;">logout</span>
                            Cerrar sesión
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>

</header>