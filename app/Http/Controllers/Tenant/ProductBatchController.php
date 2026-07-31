<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductBatchRequest;
use App\Models\Product;
use App\Models\ProductBatch;

class ProductBatchController extends Controller
{
    public function index(Product $product)
    {
        return response()->json($product->batches()->with('branch')->get());
    }

    public function store(ProductBatchRequest $request, Product $product)
    {
        abort_if(! $product->tracks_batches, 422, 'Este producto no maneja lotes ni vencimientos.');

        $batch = $product->batches()->create($request->validated());

        return response()->json($batch, 201);
    }

    public function update(ProductBatchRequest $request, Product $product, ProductBatch $batch)
    {
        abort_if($batch->product_id !== $product->id, 404);

        $batch->update($request->validated());

        return response()->json($batch->fresh());
    }

    public function destroy(Product $product, ProductBatch $batch)
    {
        abort_if($batch->product_id !== $product->id, 404);

        $batch->delete();

        return response()->json(['message' => 'Lote eliminado.']);
    }
}
