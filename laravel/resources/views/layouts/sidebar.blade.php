{{-- ── Sidebar ── --}}
@php
    /**
     * Genera clases del link activo.
     * Usamos una Closure almacenada en variable para evitar
     * "Cannot redeclare function" cuando el partial se carga múltiples veces.
     */
    $navLink = function (string $routePattern): string {
        $active = request()->routeIs($routePattern);
        return $active
            ? 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold bg-primary text-white'
            : 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-white transition-colors';
    };
@endphp

<aside
    class="fixed md:static z-40 inset-y-0 left-0 w-64 bg-slate-900 text-white
           transform md:translate-x-0 transition-transform duration-200
           flex flex-col overflow-y-auto shrink-0"
    :class="open ? 'translate-x-0' : '-translate-x-full'"
>

    {{-- Logo --}}
    <div class="h-16 px-6 border-b border-slate-800 flex items-center shrink-0">
        <span class="text-lg font-black tracking-tight text-white">
            Contract<span style="color:#6366f1;">System</span>
        </span>
    </div>

    {{-- Navegación --}}
    <nav class="flex-1 px-3 py-4 space-y-0.5">

        {{-- Dashboard — todos --}}
        <a href="{{ route('dashboard') }}" class="{{ $navLink('dashboard') }}">
            <span class="material-symbols-outlined" style="font-size:20px;">dashboard</span>
            Dashboard
        </a>

        {{-- ── ADMINISTRADOR ── --}}
        @role('Administrador')

            <p class="px-3 pt-4 pb-1 text-[10px] font-black uppercase tracking-widest text-slate-600">
                Administración
            </p>

            <a href="{{ route('users.index') }}" class="{{ $navLink('users.*') }}">
                <span class="material-symbols-outlined" style="font-size:20px;">group</span>
                Usuarios
            </a>

            <a href="{{ route('operations.index') }}" class="{{ $navLink('operations.*') }}">
                <span class="material-symbols-outlined" style="font-size:20px;">corporate_fare</span>
                Operaciones
            </a>

            <a href="{{ route('contracts.index') }}" class="{{ $navLink('contracts.*') }}">
                <span class="material-symbols-outlined" style="font-size:20px;">description</span>
                Contratos
            </a>

            <a href="{{ route('contract-requests.index') }}" class="{{ $navLink('contract-requests.*') }}">
                <span class="material-symbols-outlined" style="font-size:20px;">request_quote</span>
                Solicitudes
                @php
                    $pendingAdmin = \App\Models\ContractStatusRequest::where('status','pendiente')->count();
                @endphp
                @if ($pendingAdmin > 0)
                    <span class="ml-auto inline-flex items-center justify-center size-5 rounded-full bg-yellow-400 text-[10px] font-black text-white">
                        {{ $pendingAdmin > 9 ? '9+' : $pendingAdmin }}
                    </span>
                @endif
            </a>
            

            
        @endrole

        {{-- ── SECRETARIO ── --}}
        @role('Secretario')

            <p class="px-3 pt-4 pb-1 text-[10px] font-black uppercase tracking-widest text-slate-600">
                Gestión
            </p>

            <a href="{{ route('contracts.index') }}" class="{{ $navLink('contracts.*') }}">
                <span class="material-symbols-outlined" style="font-size:20px;">description</span>
                Contratos
            </a>

            <a href="{{ route('contract-requests.index') }}" class="{{ $navLink('contract-requests.*') }}">
                <span class="material-symbols-outlined" style="font-size:20px;">request_quote</span>
                Solicitudes
                @php
                    $pendingSecretario = \App\Models\ContractStatusRequest::where('status','pendiente')->count();
                @endphp
                @if ($pendingSecretario > 0)
                    <span class="ml-auto inline-flex items-center justify-center size-5 rounded-full bg-yellow-400 text-[10px] font-black text-white">
                        {{ $pendingSecretario > 9 ? '9+' : $pendingSecretario }}
                    </span>
                @endif
            </a>

        @endrole

        {{-- ── GERENTE ── --}}
        @role('Gerente')

            <p class="px-3 pt-4 pb-1 text-[10px] font-black uppercase tracking-widest text-slate-600">
                Mi Operación
            </p>

            <a href="{{ route('contracts.index') }}" class="{{ $navLink('contracts.*') }}">
                <span class="material-symbols-outlined" style="font-size:20px;">description</span>
                Contratos operación
            </a>

            <a href="{{ route('users.index') }}" class="{{ $navLink('users.*') }}">
                <span class="material-symbols-outlined" style="font-size:20px;">group</span>
                Asesores
            </a>

            <a href="{{ route('contract-requests.index') }}" class="{{ $navLink('contract-requests.*') }}">
                <span class="material-symbols-outlined" style="font-size:20px;">request_quote</span>
                Solicitudes
                @php
                    $user            = auth()->user();
                    $pendingGerente  = \App\Models\ContractStatusRequest::where('status','pendiente')
                        ->whereHas('contract.advisor', fn($q) => $q->where('operation_id', $user->operation_id))
                        ->count();
                @endphp
                @if ($pendingGerente > 0)
                    <span class="ml-auto inline-flex items-center justify-center size-5 rounded-full bg-yellow-400 text-[10px] font-black text-white">
                        {{ $pendingGerente > 9 ? '9+' : $pendingGerente }}
                    </span>
                @endif
            </a>

        @endrole

        {{-- ── ASESOR ── --}}
        @role('Asesor')

            <p class="px-3 pt-4 pb-1 text-[10px] font-black uppercase tracking-widest text-slate-600">
                Mi trabajo
            </p>

            <a href="{{ route('contracts.index') }}" class="{{ $navLink('contracts.*') }}">
                <span class="material-symbols-outlined" style="font-size:20px;">description</span>
                Mis contratos
            </a>

        @endrole

    </nav>

    {{-- Footer: perfil del usuario --}}
    <div class="p-3 border-t border-slate-800 shrink-0">
        <a href="{{ route('profile.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition-colors group {{ $navLink('profile.*') === 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold bg-primary text-white' ? 'bg-primary' : '' }}">

            <div class="size-8 rounded-full bg-primary flex items-center justify-center shrink-0">
                <span class="text-xs font-black text-white">
                    @php
                        $parts = explode(' ', auth()->user()->name);
                        echo strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                    @endphp
                </span>
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-slate-500 truncate">
                    {{ auth()->user()->getRoleNames()->first() ?? 'Usuario' }}
                </p>
            </div>

            <span class="material-symbols-outlined text-slate-600 group-hover:text-slate-400 transition-colors"
                  style="font-size:18px;">settings</span>
        </a>
    </div>

</aside>

{{-- Overlay móvil --}}
<div
    x-show="open"
    @click="open = false"
    x-transition:enter="transition-opacity duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-black/50 z-30 md:hidden"
    style="display:none;">
</div>