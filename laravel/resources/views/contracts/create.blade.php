@extends('layouts.app')

@section('title', 'Asignar Contratos')

@section('content')

<div class="max-w-2xl mx-auto space-y-6">

    {{-- ── Encabezado ── --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('contracts.index') }}"
           class="p-2 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
            <span class="material-symbols-outlined" style="font-size:20px;">arrow_back</span>
        </a>
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Asignar Contratos</h2>
            <p class="text-slate-500 text-sm mt-0.5">Asignación masiva por rango de números consecutivos.</p>
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
            <ul class="list-disc list-inside space-y-0.5 text-red-600 text-xs ml-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Formulario ── --}}
    <form method="POST" action="{{ route('contracts.store') }}"
          x-data="{
              startNumber: '{{ old('start_number', '') }}',
              quantity: '{{ old('quantity', 1) }}',
              get endNumber() {
                  const s = parseInt(this.startNumber);
                  const q = parseInt(this.quantity);
                  if (!s || !q || q < 1) return '—';
                  return s + q - 1;
              },
              get rangeLabel() {
                  const s = parseInt(this.startNumber);
                  const q = parseInt(this.quantity);
                  if (!s || !q || q < 1) return null;
                  if (q === 1) return 'Contrato CL-' + s;
                  return 'Contratos CL-' + s + ' al CL-' + (s + q - 1);
              }
          }">
        @csrf

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

            {{-- Sección: Rango de contratos --}}
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider mb-4">
                    Rango de contratos
                </h3>
                <div class="grid grid-cols-2 gap-4">

                    {{-- Número inicial --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            Número inicial <span class="text-red-400">*</span>
                        </label>
                        <input
                            type="number"
                            name="start_number"
                            x-model="startNumber"
                            value="{{ old('start_number') }}"
                            placeholder="Ej: 1001"
                            min="1"
                            required
                            class="w-full px-3 py-2.5 bg-slate-50 border rounded-lg text-sm
                                   focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                   transition-colors {{ $errors->has('start_number') ? 'border-red-400 bg-red-50' : 'border-slate-200' }}"
                        >
                        @error('start_number')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Cantidad --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            Cantidad <span class="text-red-400">*</span>
                        </label>
                        <input
                            type="number"
                            name="quantity"
                            x-model="quantity"
                            value="{{ old('quantity', 1) }}"
                            placeholder="Ej: 50"
                            min="1"
                            max="500"
                            required
                            class="w-full px-3 py-2.5 bg-slate-50 border rounded-lg text-sm
                                   focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                   transition-colors {{ $errors->has('quantity') ? 'border-red-400 bg-red-50' : 'border-slate-200' }}"
                        >
                        @error('quantity')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-[10px] text-slate-400 mt-1">Máximo 500 por asignación.</p>
                    </div>

                </div>

                {{-- Preview del rango --}}
                <div x-show="rangeLabel"
                     class="mt-4 flex items-center gap-2.5 px-4 py-3 bg-primary/5 border border-primary/15 rounded-xl">
                    <span class="material-symbols-outlined text-primary" style="font-size:18px;">info</span>
                    <div class="text-sm">
                        <span class="font-bold text-primary" x-text="rangeLabel"></span>
                        <span class="text-slate-500 ml-1">
                            — <span x-text="quantity"></span> contrato<span x-show="quantity > 1">s</span>
                            en estado <strong>Limpio</strong>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Sección: Asesor --}}
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider mb-4">
                    Asesor asignado
                </h3>

                <div x-data="{ search: '', selected: '{{ old('advisor_id', '') }}', selectedName: '' }"
                     class="space-y-3">

                    {{-- Buscador de asesor --}}
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                              style="font-size:18px;">search</span>
                        <input type="text" x-model="search" placeholder="Buscar asesor..."
                               class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                      placeholder-slate-400 transition-colors">
                    </div>

                    {{-- Lista de asesores --}}
                    <div class="space-y-1.5 max-h-56 overflow-y-auto pr-1">
                        @forelse ($advisors as $advisor)
                            @php
                                $words    = preg_split('/\s+/', trim($advisor->name));
                                $initials = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1)) ?: 'NA';
                                $colors   = ['bg-indigo-500','bg-purple-500','bg-teal-500','bg-pink-500','bg-orange-500'];
                                $bg       = $colors[$advisor->id % count($colors)];
                            @endphp
                            <label
                                x-show="search === '' || '{{ strtolower($advisor->name) }}'.includes(search.toLowerCase())"
                                class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                :class="selected == '{{ $advisor->id }}'
                                    ? 'border-primary bg-primary/5'
                                    : 'border-slate-200 hover:border-slate-300'"
                            >
                                <input type="radio" name="advisor_id" value="{{ $advisor->id }}"
                                       x-model="selected" class="sr-only"
                                       {{ old('advisor_id') == $advisor->id ? 'checked' : '' }}>

                                <div class="size-9 rounded-full flex items-center justify-center text-xs font-black text-white shrink-0 {{ $bg }}">
                                    {{ $initials }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-700 truncate">{{ $advisor->name }}</p>
                                    <p class="text-[10px] text-slate-400 truncate">{{ $advisor->operation->name ?? 'Sin operación' }}</p>
                                </div>

                                <span class="size-4 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
                                      :class="selected == '{{ $advisor->id }}' ? 'border-primary bg-primary' : 'border-slate-300'">
                                    <span class="material-symbols-outlined text-white" style="font-size:11px;"
                                          x-show="selected == '{{ $advisor->id }}'">check</span>
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-slate-400 text-center py-6">No hay asesores disponibles.</p>
                        @endforelse
                    </div>

                    @error('advisor_id')
                        <p class="text-red-500 text-xs">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Sección: Información del Cliente --}}
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider mb-4">
                    Información del Cliente
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Nombre del cliente</label>
                        <input type="text" name="client_name" value="{{ old('client_name') }}"
                               placeholder="Ej: Juan Pérez"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                      transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Documento</label>
                        <input type="text" name="client_document" value="{{ old('client_document') }}"
                               placeholder="Ej: 12345678"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                      transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Teléfono</label>
                        <input type="text" name="client_phone" value="{{ old('client_phone') }}"
                               placeholder="Ej: 3001234567"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                      transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Valor del contrato</label>
                        <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0"
                               placeholder="Ej: 5000000"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                      transition-colors">
                    </div>
                </div>
            </div>

            {{-- Sección: Fechas del Contrato --}}
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider mb-4">
                    Fechas del Contrato
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Fecha de entrega</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                                  style="font-size:18px;">calendar_today</span>
                            <input type="date" name="delivered_at" value="{{ old('delivered_at', now()->format('Y-m-d')) }}"
                                   class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                          focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                          transition-colors">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Fecha de firma</label>
                        <input type="date" name="signed_at" value="{{ old('signed_at') }}"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                      transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Fecha de vencimiento</label>
                        <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                      transition-colors">
                    </div>
                </div>
            </div>

            {{-- Sección: Observación --}}
            <div class="px-6 py-5">
                <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider mb-4">
                    Observación Inicial
                </h3>
                <textarea name="observation" rows="3" placeholder="Notas sobre esta asignación (opcional)..."
                          class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                 placeholder-slate-400 resize-none transition-colors"
                >{{ old('observation') }}</textarea>
            </div>

            {{-- Footer: acciones --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('contracts.index') }}"
                   class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-bold
                               rounded-lg hover:opacity-90 transition-opacity shadow-sm">
                    <span class="material-symbols-outlined" style="font-size:18px;">assignment</span>
                    Asignar contratos
                </button>
            </div>

        </div>
    </form>

</div>

@endsection