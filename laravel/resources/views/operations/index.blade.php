@extends('layouts.app')

@section('title', 'Operaciones')

@section('content')

<div
    x-data="{
        createModal: false,
        editModal:   false,
        deleteModal: false,

        {{-- Edit --}}
        opId:   null,
        opName: '',
        opDesc: '',
        opMonthlyGoal: '',
        opContractsGoal: '',
        opSalesGoal: '',

        {{-- Delete --}}
        delId:   null,
        delName: '',

        openEdit(id, name, desc, monthlyGoal, contractsGoal, salesGoal) {
            this.opId           = id;
            this.opName         = name;
            this.opDesc         = desc ?? '';
            this.opMonthlyGoal   = monthlyGoal ?? '';
            this.opContractsGoal = contractsGoal ?? '';
            this.opSalesGoal     = salesGoal ?? '';
            this.editModal      = true;
        },
        openDelete(id, name) {
            this.delId       = id;
            this.delName     = name;
            this.deleteModal = true;
        },
        close() {
            this.createModal = false;
            this.editModal   = false;
            this.deleteModal = false;
        }
    }"
    class="space-y-6"
>

    {{-- ── Título + acción ── --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Operaciones</h2>
            <p class="text-slate-500 text-sm mt-1">Gestiona las operaciones del sistema y sus KPIs.</p>
        </div>
        <button @click="createModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-sm font-bold
                       hover:opacity-90 transition-opacity shadow-sm">
            <span class="material-symbols-outlined" style="font-size:18px;">add</span>
            Nueva operación
        </button>
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

    {{-- ── Grid de operaciones ── --}}
    @if ($operations->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm py-16 text-center">
            <span class="material-symbols-outlined text-6xl text-slate-300 block mb-3">corporate_fare</span>
            <p class="text-slate-500 font-semibold">No hay operaciones registradas</p>
            <button @click="createModal = true"
                    class="mt-4 text-sm text-primary hover:underline font-bold">
                Crear la primera operación
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($operations as $operation)
                @php
                    $colors = [
                        'bg-indigo-500', 'bg-purple-500', 'bg-teal-500',
                        'bg-pink-500',   'bg-orange-500', 'bg-blue-500',
                    ];
                    $bg = $colors[$operation->id % count($colors)];
                @endphp

                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden
                            hover:shadow-md transition-shadow">

                    {{-- Header de la card --}}
                    <div class="h-2 {{ $bg }}"></div>

                    <div class="p-5">

                        {{-- Nombre + acciones --}}
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="size-10 rounded-lg {{ $bg }} flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-white" style="font-size:20px;">corporate_fare</span>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-slate-800 truncate">{{ $operation->name }}</h3>
                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        Creada {{ $operation->created_at->format('d M Y') }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 shrink-0">
                                <button
                                    @click="openEdit({{ $operation->id }}, '{{ addslashes($operation->name) }}', '{{ addslashes($operation->description ?? '') }}', '{{ $operation->monthly_goal }}', '{{ $operation->contracts_goal }}', '{{ $operation->sales_goal }}')"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/5 transition-colors"
                                    title="Editar">
                                    <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                </button>
                                <button
                                    @click="openDelete({{ $operation->id }}, '{{ addslashes($operation->name) }}')"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                    title="Eliminar">
                                    <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                </button>
                            </div>
                        </div>

                        {{-- Descripción --}}
                        @if ($operation->description)
                            <p class="text-xs text-slate-500 mb-4 line-clamp-2">{{ $operation->description }}</p>
                        @else
                            <p class="text-xs text-slate-300 italic mb-4">Sin descripción</p>
                        @endif

                        {{-- KPIs --}}
                        <div class="mb-4 p-3 bg-slate-50 rounded-lg space-y-2">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">KPIs / Metas</p>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Meta mensual:</span>
                                <span class="font-semibold text-slate-700">${{ number_format($operation->monthly_goal ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Meta contratos:</span>
                                <span class="font-semibold text-slate-700">{{ $operation->contracts_goal ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Meta ventas:</span>
                                <span class="font-semibold text-slate-700">${{ number_format($operation->sales_goal ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
                            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                <span class="material-symbols-outlined text-slate-400" style="font-size:16px;">group</span>
                                <strong class="text-slate-700">{{ $operation->users_count }}</strong>
                                usuario{{ $operation->users_count !== 1 ? 's' : '' }}
                            </div>
                            <div class="w-px h-3 bg-slate-200"></div>
                            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                <span class="material-symbols-outlined text-slate-400" style="font-size:16px;">description</span>
                                <strong class="text-slate-700">{{ $operation->contracts_count }}</strong>
                                contrato{{ $operation->contracts_count !== 1 ? 's' : '' }}
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginación --}}
        @if ($operations->hasPages())
            <div>{{ $operations->links() }}</div>
        @endif
    @endif

    {{-- ══════════════════════════════
         MODAL — Crear operación
     ══════════════════════════════ --}}
    <div x-show="createModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>

        <div x-show="createModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden" @click.stop>

            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-black text-slate-800">Nueva operación</h3>
                <button @click="close()" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:20px;">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('operations.store') }}">
                @csrf
                <div class="px-6 py-5 space-y-4">

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            Nombre <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               placeholder="Ej: Operación Norte" required
                               autofocus
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                      transition-colors">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            Descripción <span class="text-slate-400 font-normal">(opcional)</span>
                        </label>
                        <textarea name="description" rows="3"
                                  placeholder="Describe brevemente esta operación..."
                                  class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                         focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                         placeholder-slate-400 resize-none transition-colors">{{ old('description') }}</textarea>
                    </div>

                    {{-- KPIs --}}
                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">KPIs / Metas</p>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5">Meta mensual ($)</label>
                                <input type="number" name="monthly_goal" value="{{ old('monthly_goal') }}"
                                       placeholder="Ej: 50000000" step="0.01" min="0"
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                              focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                              transition-colors">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5">Meta contratos</label>
                                <input type="number" name="contracts_goal" value="{{ old('contracts_goal') }}"
                                       placeholder="Ej: 100" min="0"
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                              focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                              transition-colors">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5">Meta ventas ($)</label>
                                <input type="number" name="sales_goal" value="{{ old('sales_goal') }}"
                                       placeholder="Ej: 30000000" step="0.01" min="0"
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                              focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                              transition-colors">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="close()"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-primary text-white text-sm font-bold
                                   rounded-lg hover:opacity-90 transition-opacity shadow-sm">
                        <span class="material-symbols-outlined" style="font-size:16px;">add</span>
                        Crear operación
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════
         MODAL — Editar operación
     ══════════════════════════════ --}}
    <div x-show="editModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>

        <div x-show="editModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden" @click.stop>

            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-black text-slate-800">Editar operación</h3>
                <button @click="close()" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:20px;">close</span>
                </button>
            </div>

            <form method="POST" :action="'/operations/' + opId">
                @csrf
                @method('PUT')
                <div class="px-6 py-5 space-y-4">

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            Nombre <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="name" x-model="opName"
                               placeholder="Ej: Operación Norte" required
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                      transition-colors">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            Descripción <span class="text-slate-400 font-normal">(opcional)</span>
                        </label>
                        <textarea name="description" x-model="opDesc" rows="3"
                                  placeholder="Describe brevemente esta operación..."
                                  class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                         focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                         placeholder-slate-400 resize-none transition-colors"></textarea>
                    </div>

                    {{-- KPIs --}}
                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">KPIs / Metas</p>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5">Meta mensual ($)</label>
                                <input type="number" name="monthly_goal" x-model="opMonthlyGoal"
                                       placeholder="Ej: 50000000" step="0.01" min="0"
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                              focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                              transition-colors">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5">Meta contratos</label>
                                <input type="number" name="contracts_goal" x-model="opContractsGoal"
                                       placeholder="Ej: 100" min="0"
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                              focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                              transition-colors">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5">Meta ventas ($)</label>
                                <input type="number" name="sales_goal" x-model="opSalesGoal"
                                       placeholder="Ej: 30000000" step="0.01" min="0"
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                              focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                              transition-colors">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="close()"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-primary text-white text-sm font-bold
                                   rounded-lg hover:opacity-90 transition-opacity shadow-sm">
                        <span class="material-symbols-outlined" style="font-size:16px;">save</span>
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════
         MODAL — Eliminar operación
     ══════════════════════════════ --}}
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
                <h3 class="text-base font-black text-slate-800">Eliminar operación</h3>
            </div>

            <div class="px-6 py-5">
                <p class="text-sm text-slate-600">
                    ¿Eliminar la operación <span class="font-bold text-slate-800" x-text="delName"></span>?
                    Esta acción no se puede deshacer.
                </p>
                <p class="text-xs text-red-500 mt-2 font-medium">
                    No se puede eliminar si tiene usuarios asignados.
                </p>
            </div>

            <form method="POST" :action="'/operations/' + delId">
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

</div>{{-- fin x-data --}}

@endsection
