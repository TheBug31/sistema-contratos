@extends('layouts.app')

@section('title', '2FA - Autenticación de dos factores')

@section('content')

<div class="max-w-2xl mx-auto mt-10">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
            <span class="material-symbols-outlined text-primary" style="font-size:24px;">security</span>
            <h2 class="text-xl font-black text-slate-800">Autenticación de Dos Factores (2FA)</h2>
        </div>

        <div class="p-6 space-y-6">

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

            @if (auth()->user()->two_factor_enabled)
                {{-- 2FA Activado --}}
                <div class="text-center space-y-4">
                    <div class="size-20 mx-auto bg-green-100 rounded-full flex items-center justify-center">
                        <span class="material-symbols-outlined text-green-600" style="font-size:40px;">verified</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">2FA está ACTIVADO</h3>
                    <p class="text-sm text-slate-500">Tu cuenta está protegida con autenticación de dos factores.</p>

                    {{-- Códigos de recuperación --}}
                    @if (!empty($recoveryCodes))
                        <div class="mt-6 p-4 bg-slate-50 rounded-xl text-left">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Códigos de recuperación</p>
                            <p class="text-xs text-slate-500 mb-3">Guarda estos códigos en un lugar seguro. Se usan para acceder si pierdes tu dispositivo.</p>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach ($recoveryCodes as $code)
                                    <code class="block px-3 py-1.5 bg-white border border-slate-200 rounded text-xs font-mono text-center">{{ $code }}</code>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center justify-center gap-3 mt-6">
                        <form method="POST" action="{{ route('2fa.recovery-codes') }}">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                                Regenerar códigos
                            </button>
                        </form>
                        <form method="POST" action="{{ route('2fa.disable') }}">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                Desactivar 2FA
                            </button>
                        </form>
                    </div>
                </div>
            @else
                {{-- 2FA No activado --}}
                <div class="space-y-6">
                    <div class="text-center">
                        <div class="size-20 mx-auto bg-slate-100 rounded-full flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-slate-400" style="font-size:40px;">security</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Activar 2FA</h3>
                        <p class="text-sm text-slate-500 mt-1">Escanea el código QR con tu aplicación de autenticación (Google Authenticator, Authy, etc.)</p>
                    </div>

                    {{-- QR Code --}}
                    <div class="flex justify-center">
                        <div class="p-4 bg-white border border-slate-200 rounded-xl inline-block">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}"
                                 alt="QR Code para 2FA"
                                 class="w-48 h-48">
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-xl">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Código secreto (manual)</p>
                        <code class="block w-full p-3 bg-white border border-slate-200 rounded text-sm font-mono text-center break-all">{{ $secret }}</code>
                        <p class="text-[10px] text-slate-400 mt-2">Ingresa este código manualmente en tu app si no puedes escanear el QR.</p>
                    </div>

                    {{-- Formulario de confirmación --}}
                    <form method="POST" action="{{ route('2fa.enable') }}">
                        @csrf
                        <input type="hidden" name="secret" value="{{ $secret }}">

                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Código de verificación</label>
                            <input type="text" name="code" required
                                   placeholder="Ingresa el código de 6 dígitos"
                                   class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-center font-mono text-lg tracking-widest
                                          focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                          transition-colors"
                                   maxlength="6" pattern="[0-9]{6}">
                            <p class="text-[10px] text-slate-400 mt-1">Ingresa el código de 6 dígitos de tu app de autenticación.</p>
                        </div>

                        <button type="submit"
                                class="w-full mt-4 px-4 py-2.5 bg-primary text-white text-sm font-bold rounded-lg hover:opacity-90 transition-opacity">
                            Activar 2FA
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>
</div>

@endsection
