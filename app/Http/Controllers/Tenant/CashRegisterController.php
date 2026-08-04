<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\CashRegisterRequest;
use App\Models\CashRegister;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            CashRegister::whereHas('branch')->with('branch')->get()
        );
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(CashRegisterRequest $request)
    {
        $cashRegister = CashRegister::create($request->validated());

        return response()->json($cashRegister, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CashRegister $cashRegister)
    {
        abort_if($cashRegister->branch->tenant_id !== request()->user()->tenant_id, 404);

        return response()->json($cashRegister->load('branch'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(CashRegisterRequest $request, CashRegister $cashRegister)
    {
        abort_if($cashRegister->branch->tenant_id !== $request->user()->tenant_id, 404);

        $cashRegister->update($request->validated());

        return response()->json($cashRegister->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CashRegister $cashRegister)
    {
        abort_if($cashRegister->branch->tenant_id !== request()->user()->tenant_id, 404);

        $cashRegister->delete();

        return response()->json(['message' => 'Caja eliminada.']);
    }
}
