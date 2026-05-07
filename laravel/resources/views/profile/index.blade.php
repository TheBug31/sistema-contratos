@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')

@php
    $user     = auth()->user();
    $roleName = $user->getRoleNames()->first() ?? 'Usuario';

    $roleConfig = [
        'Administrador' => ['icon' => 'admin_panel_settings', 'color' => 'text-purple-600', 'bg' => 'bg-purple-100'],
        'Secretario'    => ['icon' => 'assignment_ind',       'color' => 'text-blue-600',   'bg' => 'bg-blue-100'],
        'Gerente'       => ['icon' => 'manage_accounts',      'color' => 'text-indigo-600', 'bg' => 'bg-indigo-100'],
        'Asesor'        => ['icon' => 'person',               'color' => 'text-teal-600',   'bg' => 'bg-teal-100'],
    ];
    $rc = $roleConfig[$roleName] ?? ['icon' => 'person', 'color' => 'text-slate-600', 'bg' => 'bg-slate-100'];

    $words    = preg_split('/\s+/', trim($user->name));
    $initials = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1)) ?: 'NA';
@endphp

<div class="max-w-3xl mx-auto space-y-6">

    {{-- ── Encabezado / tarjeta de identidad ── --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Banner con avatar encima --}}
        <div class="h-28 bg-primary relative">
            <div class="absolute inset-0 bg-gradient-to-r from-black/20 via-transparent to-black/10"></div>

            {{-- Badge de rol flotando arriba a la derecha --}}
            <span class="absolute top-4 right-4 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black
                         {{ $rc['bg'] }} {{ $rc['color'] }}">
                <span class="material-symbols-outlined" style="font-size:14px;">{{ $rc['icon'] }}</span>
                {{ $roleName }}
            </span>

            {{-- Avatar sobresaliendo del banner --}}
            <div class="absolute -bottom-8 left-6
                        size-16 rounded-2xl bg-primary border-4 border-white shadow-lg
                        flex items-center justify-center">
                <span class="text-xl font-black text-white">{{ $initials }}</span>
            </div>
        </div>

        {{-- Info del usuario --}}
        <div class="px-6 pt-12 pb-5">
            <h2 class="text-xl font-black text-slate-900">{{ $user->name }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $user->email }}</p>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2">
                @if ($user->operation)
                    <div class="flex items-center gap-1.5 text-xs text-slate-400">
                        <span class="material-symbols-outlined" style="font-size:14px;">corporate_fare</span>
                        {{ $user->operation->name }}
                    </div>
                @endif
                <div class="flex items-center gap-1.5 text-xs text-slate-400">
                    <span class="material-symbols-outlined" style="font-size:14px;">calendar_today</span>
                    Miembro desde {{ $user->created_at->format('d M Y') }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── Alertas ── --}}
    @if (session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-medium">
            <span class="material-symbols-outlined text-green-500" style="font-size:20px;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
            <div class="flex items-center gap-2 font-semibold mb-1">
                <span class="material-symbols-outlined text-red-500" style="font-size:18px;">error</span>
                Corrige los siguientes errores:
            </div>
            <ul class="list-disc list-inside text-red-600 text-xs ml-6 space-y-0.5">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Formulario ── --}}
    <form method="POST" action="{{ route('profile.update') }}"
          x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- ── Información personal ── --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">
                    Información personal
                </h3>

                <div class="space-y-4">

                    {{-- Nombre --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            Nombre completo <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="name"
                               value="{{ old('name', $user->name) }}"
                               required
                               class="w-full px-3 py-2.5 bg-slate-50 border rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                      transition-colors {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-slate-200' }}">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            Correo electrónico <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                                  style="font-size:18px;">mail</span>
                            <input type="email" name="email"
                                   value="{{ old('email', $user->email) }}"
                                   required
                                   class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border rounded-lg text-sm
                                          focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                          transition-colors {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-200' }}">
                        </div>
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Rol (solo lectura) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Rol</label>
                        <div class="flex items-center gap-2 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                            <span class="material-symbols-outlined {{ $rc['color'] }}" style="font-size:18px;">{{ $rc['icon'] }}</span>
                            <span class="text-sm text-slate-600 font-medium">{{ $roleName }}</span>
                            <span class="ml-auto text-[10px] text-slate-400">No editable</span>
                        </div>
                    </div>

                    {{-- Operación (solo lectura) --}}
                    @if ($user->operation)
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Operación</label>
                        <div class="flex items-center gap-2 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                            <span class="material-symbols-outlined text-slate-400" style="font-size:18px;">corporate_fare</span>
                            <span class="text-sm text-slate-600 font-medium">{{ $user->operation->name }}</span>
                            <span class="ml-auto text-[10px] text-slate-400">No editable</span>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            {{-- ── Cambiar contraseña ── --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">
                    Cambiar contraseña
                </h3>
                <p class="text-xs text-slate-400 mb-4">
                    Deja los campos en blanco si no deseas cambiar tu contraseña.
                </p>

                <div class="space-y-4">

                    {{-- Contraseña actual --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            Contraseña actual
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                                  style="font-size:18px;">lock</span>
                            <input :type="showCurrent ? 'text' : 'password'"
                                   name="current_password"
                                   placeholder="Tu contraseña actual"
                                   class="w-full pl-9 pr-10 py-2.5 bg-slate-50 border rounded-lg text-sm
                                          focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                          transition-colors {{ $errors->has('current_password') ? 'border-red-400 bg-red-50' : 'border-slate-200' }}">
                            <button type="button" @click="showCurrent = !showCurrent"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <span class="material-symbols-outlined" style="font-size:18px;"
                                      x-text="showCurrent ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nueva contraseña --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            Nueva contraseña
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                                  style="font-size:18px;">lock_reset</span>
                            <input :type="showNew ? 'text' : 'password'"
                                   name="new_password"
                                   placeholder="Mínimo 6 caracteres"
                                   class="w-full pl-9 pr-10 py-2.5 bg-slate-50 border rounded-lg text-sm
                                          focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                          transition-colors {{ $errors->has('new_password') ? 'border-red-400 bg-red-50' : 'border-slate-200' }}">
                            <button type="button" @click="showNew = !showNew"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <span class="material-symbols-outlined" style="font-size:18px;"
                                      x-text="showNew ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                        @error('new_password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirmar nueva --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            Confirmar nueva contraseña
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                                  style="font-size:18px;">lock_reset</span>
                            <input :type="showConfirm ? 'text' : 'password'"
                                   name="new_password_confirmation"
                                   placeholder="Repite la nueva contraseña"
                                   class="w-full pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                          focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                          transition-colors">
                            <button type="button" @click="showConfirm = !showConfirm"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <span class="material-symbols-outlined" style="font-size:18px;"
                                      x-text="showConfirm ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- ── Botón guardar ── --}}
        <div class="flex justify-end mt-6">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-bold
                           rounded-lg hover:opacity-90 transition-opacity shadow-sm">
                <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                Guardar cambios
            </button>
        </div>

    </form>

</div>

@endsection