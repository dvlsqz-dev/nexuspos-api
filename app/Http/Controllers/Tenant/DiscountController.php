<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscountRequest;
use App\Models\Discount;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Discount::latest()->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DiscountRequest $request)
    {
        $discount = Discount::create($request->validated());

        return response()->json($discount, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Discount $discount)
    {
        return response()->json($discount);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DiscountRequest $request, Discount $discount)
    {
        $discount->update($request->validated());

        return response()->json($discount->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discount $discount)
    {
        $discount->delete();

        return response()->json(['message' => 'Descuento eliminado.']);
    }
}
