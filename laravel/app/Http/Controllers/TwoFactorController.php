<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FA\Support\PHP\Google2FA;

class TwoFactorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        $user = auth()->user();
        $google2fa = new Google2FA();

        $secret = $user->two_factor_secret ?? $google2fa->generateSecretKey();
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        // Generar códigos de recuperación si no existen
        if ($user->two_factor_enabled && empty($user->two_factor_recovery_codes)) {
            $recoveryCodes = $this->generateRecoveryCodes();
            $user->update(['two_factor_recovery_codes' => json_encode($recoveryCodes)]);
        }

        return view('auth.2fa', [
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
            'recoveryCodes' => json_decode($user->two_factor_recovery_codes ?? '[]', true),
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate([
            'secret' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($request->secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'El código no es válido. Intenta de nuevo.']);
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $request->secret,
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
            'two_factor_confirmed_at' => now(),
        ]);

        return redirect()->route('2fa.show')->with('success', '2FA activado correctamente.');
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = auth()->user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            Session::put('2fa_authenticated', true);
            return redirect()->intended(route('dashboard'));
        }

        // Verificar código de recuperación
        $recoveryCodes = json_decode($user->two_factor_recovery_codes ?? '[]', true);
        if (in_array($request->code, $recoveryCodes)) {
            // Eliminar el código usado
            $recoveryCodes = array_diff($recoveryCodes, [$request->code]);
            $user->update(['two_factor_recovery_codes' => json_encode(array_values($recoveryCodes))]);

            Session::put('2fa_authenticated', true);
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['code' => 'Código inválido.']);
    }

    public function disable(Request $request)
    {
        $user = auth()->user();

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        Session::forget('2fa_authenticated');

        return redirect()->route('2fa.show')->with('success', '2FA desactivado correctamente.');
    }

    public function recoveryCodes()
    {
        $user = auth()->user();

        if (!$user->two_factor_enabled) {
            return back()->withErrors(['error' => '2FA no está activado.']);
        }

        $recoveryCodes = $this->generateRecoveryCodes();
        $user->update(['two_factor_recovery_codes' => json_encode($recoveryCodes)]);

        return redirect()->route('2fa.show')->with('success', 'Códigos de recuperación regenerados.');
    }

    private function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(substr(md5(uniqid()), 0, 10));
        }
        return $codes;
    }
}
