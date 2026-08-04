<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\CashMovementRequest;
use App\Models\CashSession;
use Illuminate\Http\Request;

class CashMovementController extends Controller
{
    public function store(CashMovementRequest $request)
    {
        $session = CashSession::where('user_id', $request->user()->id)
            ->where('status', 'abierto')
            ->first();

        abort_if(! $session, 422, 'No tienes ninguna sesión de caja abierta.');

        $movement = $session->movements()->create([
            ...$request->validated(),
            'created_at' => now(),
        ]);

        return response()->json($movement, 201);
    }

    public function index(Request $request)
    {
        $session = CashSession::where('user_id', $request->user()->id)
            ->where('status', 'abierto')
            ->first();

        abort_if(! $session, 422, 'No tienes ninguna sesión de caja abierta.');

        return response()->json($session->movements()->latest('created_at')->get());
    }
}
