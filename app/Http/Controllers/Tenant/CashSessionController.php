<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\CloseCashSessionRequest;
use App\Http\Requests\OpenCashSessionRequest;
use App\Models\CashSession;
use App\Services\CashSessionService;
use Illuminate\Http\Request;

class CashSessionController extends Controller
{
    public function __construct( private readonly CashSessionService $cashSessionService) {

    }

    public function open(OpenCashSessionRequest $request)
    {
        $existingOpen = CashSession::where('user_id', $request->user()->id)
            ->where('status', 'abierto')
            ->first();

        abort_if($existingOpen, 422, 'Ya tienes una sesión de caja abierta. Debes cerrarla antes de abrir otra.');

        $registerAlreadyOpen = CashSession::where('cash_register_id', $request->validated('cash_register_id'))
            ->where('status', 'abierto')
            ->first();

        abort_if($registerAlreadyOpen, 422, 'Esta caja ya tiene una sesión abierta por otro usuario.');

        $session = CashSession::create([
            'cash_register_id' => $request->validated('cash_register_id'),
            'user_id' => $request->user()->id,
            'opening_amount' => $request->validated('opening_amount'),
            'status' => 'abierto',
            'opened_at' => now(),
        ]);

        return response()->json($session, 201);
    }

    public function current(Request $request)
    {
        $session = CashSession::where('user_id', $request->user()->id)
            ->where('status', 'abierto')
            ->with(['cashRegister.branch', 'movements'])
            ->first();

        if (! $session) {
            return response()->json(['message' => 'No tienes ninguna sesión de caja abierta.'], 404);
        }

        return response()->json([
            ...$session->toArray(),
            'expected_amount' => $this->cashSessionService->calculateExpectedAmount($session),
        ]);
    }

    public function close(CloseCashSessionRequest $request, CashSession $cashSession)
    {
        abort_if($cashSession->user_id !== $request->user()->id, 403, 'No puedes cerrar una sesión de caja que no es tuya.');
        abort_if($cashSession->status !== 'abierto', 422, 'Esta sesión ya está cerrada.');

        $expectedAmount = $this->cashSessionService->calculateExpectedAmount($cashSession);
        $closingAmount = $request->validated('closing_amount');

        $cashSession->update([
            'closing_amount' => $closingAmount,
            'status' => 'cerrado',
            'closed_at' => now(),
        ]);

        return response()->json([
            ...$cashSession->fresh()->toArray(),
            'expected_amount' => $expectedAmount,
            'difference' => round($closingAmount - $expectedAmount, 2),
        ]);
    }
}
