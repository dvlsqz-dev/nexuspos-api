<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Auth::guard('web')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas.'],
            ]);
        }

        $tenant = $user->tenant;

        if ($tenant->status !== 'activo') {
            throw ValidationException::withMessages([
                'email' => [$this->statusMessage($tenant->status)],
            ]);
        }

        $token = $user->createToken('nexuspos-ui')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'tenant' => $tenant,
        ]);
    }

    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    private function statusMessage(string $status): string
    {
        return match ($status) {
            'pendiente' => 'Tu cuenta aún está en revisión. Te notificaremos cuando sea aprobada.',
            'suspendido' => 'Tu cuenta está suspendida. Contacta a soporte.',
            'rechazado' => 'Tu solicitud de registro fue rechazada.',
            default => 'Tu cuenta no está activa.',
        };
    }
}
