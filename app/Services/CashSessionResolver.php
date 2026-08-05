<?php

namespace App\Services;

use App\Models\CashSession;
use App\Models\User;

class CashSessionResolver
{
    public function requireCurrentFor(User $user): CashSession
    {
        $session = CashSession::where('user_id', $user->id)
            ->where('status', 'abierto')
            ->first();

        abort_if(! $session, 422, 'No tienes ninguna sesión de caja abierta. Debes abrir una antes de vender.');

        return $session;
    }
}