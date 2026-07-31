<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Models\Unit;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Unit::all());
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(UnitRequest $request)
    {
        $unit = Unit::create($request->validated());

        return response()->json($unit, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        return response()->json($unit);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(UnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());

        return response()->json($unit->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        abort_if($unit->products()->exists(), 422, 'No puedes eliminar una unidad que tiene productos asignados.');

        $unit->delete();

        return response()->json([
            'message' => 'Unidad eliminada correctamente.',
        ]);
    }
}
