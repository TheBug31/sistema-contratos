@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')

@php $isAdmin = auth()->user()->hasRole('Administrador'); @endphp

<div
    x-data="{
        toggleModal: false,
        deleteModal: false,
        userId: null,
        userName: '',
        userActive: null,

        openToggle(id, name, active) {
            this.userId     = id;
            this.userName   = name;
            this.userActive = active;
            this.toggleModal = true;
        },
        openDelete(id, name) {
            this.userId     = id;
            this.userName   = name;
            this.deleteModal = true;
        },
        close() {
            this.toggleModal = false;
            this.deleteModal = false;
        }
    }"
    class="space-y-6"
>

    {{-- ── Título + acción ── --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                @if ($isAdmin) Usuarios @else Mis Asesores @endif
            </h2>
            <p class="text-slate-500 text-sm mt-1">
                @if ($isAdmin)
                    Gestiona todos los usuarios del sistema.
                @else
                    Asesores de tu operación.
                @endif
            </p>
        </div>

        @if ($isAdmin)
        <a href="{{ route('users.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-sm font-bold
                  hover:opacity-90 transition-opacity shadow-sm">
            <span class="material-symbols-outlined" style="font-size:18px;">person_add</span>
            Nuevo usuario
        </a>
        @endif
    </div>

    {{-- ── Alertas ── --}}
    @if (session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-medium">
            <span class="material-symbols-outlined text-green-500" style="font-size:20px;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium">
            <span class="material-symbols-outlined text-red-500" style="font-size:20px;">error</span>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- ── Filtros ── --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <form method="GET" action="{{ route('users.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- Búsqueda --}}
                <div class="{{ $isAdmin ? '' : 'md:col-span-2' }}">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">
                        Búsqueda
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                              style="font-size:18px;">search</span>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Nombre, email u operación..."
                               class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                      placeholder-slate-400 transition-colors">
                    </div>
                </div>

                {{-- Rol (solo admin) --}}
                @if ($isAdmin)
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">
                        Rol
                    </label>
                    <select name="role"
                            class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                   focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                        <option value="">Todos los roles</option>
                        <option value="Administrador" {{ request('role') === 'Administrador' ? 'selected' : '' }}>Administrador</option>
                        <option value="Secretario"    {{ request('role') === 'Secretario'    ? 'selected' : '' }}>Secretario</option>
                        <option value="Gerente"       {{ request('role') === 'Gerente'       ? 'selected' : '' }}>Gerente</option>
                        <option value="Asesor"        {{ request('role') === 'Asesor'        ? 'selected' : '' }}>Asesor</option>
                    </select>
                </div>
                @endif

                {{-- Estado --}}
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">
                        Estado
                    </label>
                    <select name="active"
                            class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                   focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                        <option value="">Todos</option>
                        <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Activos</option>
                        <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>

                {{-- Botones --}}
                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="flex-1 py-2.5 bg-primary text-white rounded-lg text-sm font-bold
                                   hover:opacity-90 transition-opacity">
                        Filtrar
                    </button>
                    @if (request()->hasAny(['search','role','active']))
                        <a href="{{ route('users.index') }}"
                           class="px-3 py-2.5 bg-slate-100 text-slate-500 rounded-lg hover:bg-slate-200 transition-colors"
                           title="Limpiar filtros">
                            <span class="material-symbols-outlined" style="font-size:18px;">close</span>
                        </a>
                    @endif
                </div>

            </div>
        </form>
    </div>

    {{-- ── Tabla ── --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Usuario</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Rol</th>
                        @if ($isAdmin)
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Operación</th>
                        @endif
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Estado</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Creación</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">

                    @forelse ($users as $user)
                        @php
                            $role      = $user->roles->first();
                            $roleName  = $role->name ?? '—';

                            $roleConfig = [
                                'Administrador' => ['class' => 'bg-purple-100 text-purple-700', 'icon' => 'admin_panel_settings'],
                                'Secretario'    => ['class' => 'bg-blue-100   text-blue-700',   'icon' => 'assignment_ind'],
                                'Gerente'       => ['class' => 'bg-indigo-100 text-indigo-700', 'icon' => 'manage_accounts'],
                                'Asesor'        => ['class' => 'bg-teal-100   text-teal-700',   'icon' => 'person'],
                            ];
                            $rc = $roleConfig[$roleName] ?? ['class' => 'bg-slate-100 text-slate-600', 'icon' => 'person'];

                            $words    = preg_split('/\s+/', trim($user->name));
                            $initials = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1)) ?: 'NA';

                            $colors = ['bg-indigo-500','bg-purple-500','bg-teal-500','bg-pink-500','bg-orange-500'];
                            $bg     = $colors[$user->id % count($colors)];

                            $isSelf = $user->id === auth()->id();
                        @endphp

                        <tr class="hover:bg-slate-50/70 transition-colors {{ !$user->active ? 'opacity-60' : '' }}">

                            {{-- Usuario --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="size-9 rounded-full flex items-center justify-center text-xs font-black text-white shrink-0 {{ $bg }}">
                                        {{ $initials }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-800 truncate">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-400 truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Rol --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-black {{ $rc['class'] }}">
                                    <span class="material-symbols-outlined" style="font-size:12px;">{{ $rc['icon'] }}</span>
                                    {{ $roleName }}
                                </span>
                            </td>

                            {{-- Operación (solo admin) --}}
                            @if ($isAdmin)
                            <td class="px-6 py-4 text-sm text-slate-600">
                                @if (in_array($roleName, ['Administrador', 'Secretario']))
                                    <span class="text-slate-300 text-xs">Global</span>
                                @else
                                    {{ $user->operation->name ?? '—' }}
                                @endif
                            </td>
                            @endif

                            {{-- Estado --}}
                            <td class="px-6 py-4 text-center">
                                @if ($user->active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-black bg-green-100 text-green-700">
                                        <span class="material-symbols-outlined" style="font-size:11px;">circle</span>
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-black bg-slate-100 text-slate-500">
                                        <span class="material-symbols-outlined" style="font-size:11px;">circle</span>
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            {{-- Fecha --}}
                            <td class="px-6 py-4 text-xs text-slate-400">
                                {{ $user->created_at->format('d M Y') }}
                            </td>

                            {{-- Acciones --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">

                                    {{-- Editar --}}
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/5 transition-colors"
                                       title="Editar usuario">
                                        <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                    </a>

                                    {{-- Activar / Desactivar (no aplica al propio usuario) --}}
                                    @if (!$isSelf)
                                        <button
                                            @click="openToggle({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->active ? 'true' : 'false' }})"
                                            class="p-1.5 rounded-lg transition-colors
                                                {{ $user->active
                                                    ? 'text-slate-400 hover:text-yellow-600 hover:bg-yellow-50'
                                                    : 'text-slate-400 hover:text-green-600 hover:bg-green-50' }}"
                                            title="{{ $user->active ? 'Desactivar usuario' : 'Activar usuario' }}">
                                            <span class="material-symbols-outlined" style="font-size:18px;">
                                                {{ $user->active ? 'person_off' : 'person_check' }}
                                            </span>
                                        </button>
                                    @endif

                                    {{-- Eliminar (solo admin, no a sí mismo) --}}
                                    @if ($isAdmin && !$isSelf)
                                        <button
                                            @click="openDelete({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                            title="Eliminar usuario">
                                            <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                        </button>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 6 : 5 }}" class="py-16 text-center">
                                <span class="material-symbols-outlined text-6xl text-slate-300 block mb-3">group_off</span>
                                <p class="text-slate-500 font-semibold">No se encontraron usuarios</p>
                                @if (request()->hasAny(['search','role','active']))
                                    <a href="{{ route('users.index') }}" class="text-xs text-primary hover:underline mt-1 block">
                                        Limpiar filtros
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-400 font-medium">
                Mostrando <strong class="text-slate-600">{{ $users->firstItem() ?? 0 }}</strong>
                a <strong class="text-slate-600">{{ $users->lastItem() ?? 0 }}</strong>
                de <strong class="text-slate-600">{{ $users->total() }}</strong> usuarios
            </p>
            {{ $users->links() }}
        </div>
    </div>

    {{-- ══════════════════════════════════════
         MODAL — Activar / Desactivar
    ══════════════════════════════════════ --}}
    <div x-show="toggleModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>

        <div x-show="toggleModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative z-10 w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden" @click.stop>

            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-black text-slate-800"
                    x-text="userActive ? 'Desactivar usuario' : 'Activar usuario'"></h3>
                <button @click="close()" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:20px;">close</span>
                </button>
            </div>

            <div class="px-6 py-5">
                <p class="text-sm text-slate-600">
                    ¿Estás seguro de que deseas
                    <span x-text="userActive ? 'desactivar' : 'activar'" class="font-bold"></span>
                    al usuario <span class="font-bold text-slate-800" x-text="userName"></span>?
                </p>
                <p class="text-xs text-slate-400 mt-2" x-show="userActive">
                    El usuario no podrá iniciar sesión mientras esté inactivo.
                </p>
            </div>

            <form method="POST" :action="'/users/' + userId + '/toggle'">
                @csrf
                @method('PATCH')
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="close()"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 text-sm font-bold rounded-lg text-white shadow-sm
                                   hover:opacity-90 transition-opacity"
                            :class="userActive ? 'bg-yellow-500' : 'bg-green-600'">
                        <span class="material-symbols-outlined" style="font-size:16px;"
                              x-text="userActive ? 'person_off' : 'person_check'"></span>
                        <span x-text="userActive ? 'Desactivar' : 'Activar'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         MODAL — Eliminar
    ══════════════════════════════════════ --}}
    @if ($isAdmin)
    <div x-show="deleteModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>

        <div x-show="deleteModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative z-10 w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden" @click.stop>

            <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                <div class="size-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-red-600" style="font-size:20px;">warning</span>
                </div>
                <h3 class="text-base font-black text-slate-800">Eliminar usuario</h3>
            </div>

            <div class="px-6 py-5">
                <p class="text-sm text-slate-600">
                    ¿Eliminar a <span class="font-bold text-slate-800" x-text="userName"></span>?
                    Esta acción no se puede deshacer.
                </p>
                <p class="text-xs text-red-500 mt-2 font-medium">
                    No se puede eliminar si tiene contratos asignados.
                </p>
            </div>

            <form method="POST" :action="'/users/' + userId">
                @csrf
                @method('DELETE')
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="close()"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-red-600 text-white text-sm font-bold
                                   rounded-lg hover:opacity-90 transition-opacity shadow-sm">
                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                        Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>{{-- fin x-data --}}

@endsection