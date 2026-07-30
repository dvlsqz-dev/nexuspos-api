<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;


class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Branch::all());
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(BranchRequest $request)
    {
        $validated = $request->validated();
        
        $branch = DB::transaction(function () use ($validated) {
            if ($validated['is_main'] ?? false) {
                Branch::where('is_main', true)->update(['is_main' => false]);
            }
            return Branch::create($validated);
        });
        return response()->json($branch, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch)
    {
        return response()->json($branch);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BranchRequest $request, Branch $branch)
    {
        $validated = $request->validated();

       DB::transaction(function () use ($validated, $branch) {
            if ($validated['is_main'] ?? false) {
                Branch::where('is_main', true)->where('id', '!=', $branch->id)
                    ->update(['is_main' => false]);
            }
            $branch->update($validated);
        });

        return response()->json($branch->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();
        
        return response()->json([
            'message' => 'Sucursal eliminada correctamente.',
        ]);
    }
}
