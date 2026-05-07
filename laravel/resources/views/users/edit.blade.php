@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')

@php
    $authUser  = auth()->user();
    $isAdmin   = $authUser->hasRole('Administrador');
    $isGerente = $authUser->hasRole('Gerente');

    $userRole = $user->roles->first()?->name;

    $roleConfig = [
        'Administrador' => ['icon' => 'admin_panel_settings', 'color' => 'text-purple-500', 'desc' => 'Acceso total'],
        'Secretario'    => ['icon' => 'assignment_ind',       'color' => 'text-blue-500',   'desc' => 'Gestión global'],
        'Gerente'       => ['icon' => 'manage_accounts',      'color' => 'text-indigo-500', 'desc' => 'Su operación'],
        'Asesor'        => ['icon' => 'person',               'color' => 'text-teal-500',   'desc' => 'Sus contratos'],
    ];

    $availableRoles = $isGerente
        ? $roles->where('name', 'Asesor')
        : ($isAdmin ? $roles : $roles->whereNotIn('name', ['Administrador']));
@endphp

{{-- Encabezado --}}
<div class="flex items-center gap-4 mb-6">
    <a href="{{ route('users.index') }}"
       class="p-2 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
        <span class="material-symbols-outlined" style="font-size:20px;">arrow_back</span>
    </a>
    <div class="flex-1 min-w-0">
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Editar Usuario</h2>
        <p class="text-slate-500 text-sm mt-0.5 flex items-center gap-2">
            Editando a <strong>{{ $user->name }}</strong>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black
                {{ $user->active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                <span class="material-symbols-outlined" style="font-size:10px;">circle</span>
                {{ $user->active ? 'Activo' : 'Inactivo' }}
            </span>
        </p>
    </div>
</div>

{{-- Errores --}}
@if ($errors->any())
    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
        <div class="flex items-center gap-2 font-semibold mb-1">
            <span class="material-symbols-outlined text-red-500" style="font-size:18px;">error</span>
            Corrige los siguientes errores:
        </div>
        <ul class="list-disc list-inside text-red-600 text-xs ml-6 space-y-0.5">
            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('users.update', $user) }}"
      x-data="{
          showPass: false,
          showConfirm: false,
          changePass: {{ $errors->has('password') ? 'true' : 'false' }},
          selectedRole: '{{ old('roles.0', $userRole) }}',
          get needsOperation() {
              return !['Administrador','Secretario'].includes(this.selectedRole);
          }
      , needsOperation: true, updateRole(val) { this.needsOperation = !['Administrador','Secretario'].includes(val); } }">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ── Columna izquierda ── --}}
        <div class="space-y-6">

            {{-- Datos personales --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">Datos personales</h3>
                <div class="space-y-4">

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            Nombre completo <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="name"
                               value="{{ old('name', $user->name) }}"
                               placeholder="Ej: Juan Pérez" required
                               class="w-full px-3 py-2.5 bg-slate-50 border rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                      transition-colors {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-slate-200' }}">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            Correo electrónico <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                                  style="font-size:18px;">mail</span>
                            <input type="email" name="email"
                                   value="{{ old('email', $user->email) }}"
                                   placeholder="usuario@ejemplo.com" required
                                   class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border rounded-lg text-sm
                                          focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                          transition-colors {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-200' }}">
                        </div>
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                </div>
            </div>

            {{-- Contraseña --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest">Contraseña</h3>
                    <button type="button" @click="changePass = !changePass"
                            class="text-xs font-bold transition-colors"
                            :class="changePass ? 'text-red-500' : 'text-primary hover:opacity-80'">
                        <span x-text="changePass ? 'Cancelar' : 'Cambiar contraseña'"></span>
                    </button>
                </div>

                <div x-show="!changePass"
                     class="flex items-center gap-3 px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <span class="material-symbols-outlined text-slate-400" style="font-size:20px;">lock</span>
                    <p class="text-sm text-slate-500">La contraseña actual se mantiene sin cambios.</p>
                </div>

                <div x-show="changePass" class="space-y-4" style="display:none;">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Nueva contraseña</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                                  style="font-size:18px;">lock</span>
                            <input :type="showPass ? 'text' : 'password'" name="password"
                                   placeholder="Mínimo 6 caracteres"
                                   class="w-full pl-9 pr-10 py-2.5 bg-slate-50 border rounded-lg text-sm
                                          focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                          transition-colors {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-slate-200' }}">
                            <button type="button" @click="showPass = !showPass"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <span class="material-symbols-outlined" style="font-size:18px;"
                                      x-text="showPass ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Confirmar nueva contraseña</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                                  style="font-size:18px;">lock_reset</span>
                            <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation"
                                   placeholder="Repite la nueva contraseña"
                                   class="w-full pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                          focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
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

        {{-- ── Columna derecha ── --}}
        <div class="space-y-6">

            {{-- Rol --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">Rol</h3>
                <div class="space-y-2">
                    @foreach ($availableRoles as $role)
                        @php $rc = $roleConfig[$role->name] ?? ['icon' => 'person', 'color' => 'text-slate-400', 'desc' => '']; @endphp
                        <label class="radio-label flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all border-slate-200 hover:border-slate-300">
                            <input type="radio" name="roles[]" value="{{ $role->name }}"
                                   @change="selectedRole = '{{ $role->name }}'"
                                   class="radio-input sr-only"
                                   x-model="selectedRole"
                                   {{ old('roles.0', $userRole) === $role->name ? 'checked' : '' }}>
                            <span class="material-symbols-outlined {{ $rc['color'] }}" style="font-size:22px;">{{ $rc['icon'] }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-700">{{ $role->name }}</p>
                                <p class="text-[10px] text-slate-400">{{ $rc['desc'] }}</p>
                            </div>
                            <span class="radio-ring size-4 rounded-full border-2 border-slate-300 flex items-center justify-center shrink-0 transition-all">
                                <span class="radio-dot size-2 rounded-full bg-primary opacity-0 transition-opacity"></span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('roles') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- Operación --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5"
                 x-show="needsOperation">
                <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">Operación</h3>

                @if ($isGerente)
                    <input type="hidden" name="operation_id" value="{{ $authUser->operation_id }}">
                    <div class="flex items-center gap-3 px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="material-symbols-outlined text-primary" style="font-size:20px;">corporate_fare</span>
                        <div>
                            <p class="text-sm font-bold text-slate-700">{{ $authUser->operation->name ?? '—' }}</p>
                            <p class="text-[10px] text-slate-400">Asignado automáticamente</p>
                        </div>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach ($operations as $op)
                            <label class="radio-label flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all border-slate-200 hover:border-slate-300">
                                <input type="radio" name="operation_id" value="{{ $op->id }}"
                                       class="radio-input sr-only"
                                       {{ old('operation_id', $user->operation_id) == $op->id ? 'checked' : '' }}>
                                <span class="material-symbols-outlined text-slate-400" style="font-size:20px;">corporate_fare</span>
                                <span class="text-sm font-semibold text-slate-700 flex-1">{{ $op->name }}</span>
                                <span class="radio-ring size-4 rounded-full border-2 border-slate-300 flex items-center justify-center shrink-0 transition-all">
                                    <span class="radio-dot size-2 rounded-full bg-primary opacity-0 transition-opacity"></span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('operation_id') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                @endif
            </div>

            {{-- Acciones --}}
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('users.index') }}"
                   class="px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200
                          rounded-lg hover:bg-slate-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-bold
                               rounded-lg hover:opacity-90 transition-opacity shadow-sm">
                    <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                    Guardar cambios
                </button>
            </div>

        </div>
    </div>
</form>

@push('scripts')
<script>
    document.querySelectorAll('input[type="radio"].radio-input').forEach(radio => {
        const update = (r) => {
            const label = r.closest('.radio-label');
            const ring  = label?.querySelector('.radio-ring');
            const dot   = label?.querySelector('.radio-dot');
            if (!label || !ring || !dot) return;
            if (r.checked) {
                label.classList.replace('border-slate-200', 'border-primary');
                label.classList.add('bg-primary/5');
                ring.classList.replace('border-slate-300', 'border-primary');
                dot.classList.replace('opacity-0', 'opacity-100');
            } else {
                label.classList.replace('border-primary', 'border-slate-200');
                label.classList.remove('bg-primary/5');
                ring.classList.replace('border-primary', 'border-slate-300');
                dot.classList.replace('opacity-100', 'opacity-0');
            }
        };
        update(radio);
        radio.addEventListener('change', () => {
            document.querySelectorAll(`input[name="${radio.name}"].radio-input`).forEach(r => update(r));
        });
    });
</script>
@endpush

@endsection