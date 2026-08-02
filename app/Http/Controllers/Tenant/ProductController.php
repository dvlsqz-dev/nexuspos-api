<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UploadProductImageRequest;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use App\Services\DiscountResolver;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            Product::with(['category', 'unit', 'images'])->paginate(20)
        ]);
    }    

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $validated = $request->validated();

        $product = DB::transaction(function () use ($validated, $request) {
            $product = Product::create($validated);

            if($product->track_inventory){
                $branches = $request->user()->tenant->branches;

                foreach ($branches as $branch) {
                    $product->stocks()->create([
                        'branch_id' => $branch->id,
                        'quantity' => 0,
                        'min_stock' => 0,
                    ]);
                }
            }

            return $product;
        });

        return response()->json($product->load(['category', 'unit']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product, DiscountResolver $discountResolver)
    {
        $product->load(['category', 'unit', 'images', 'batches']);

        return response()->json([
            ...$product->toArray(),
            'pricing' => $discountResolver->effectivePrice($product),
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        $product->update($validated);

        return response()->json($product->load(['category', 'unit']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente.'
        ]);
    }

    public function uploadImage(UploadProductImageRequest $request, Product $product)
    {
        $validated = $request->validated();

        $image = DB::transaction(function () use ($validated, $request, $product) {
            if ($validated['is_primary'] ?? false) {
                $product->images()->update(['is_primary' => false]);
            }

            $path = $request->file('image')->store("products/{$product->id}", 'public');

            return $product->images()->create([
                'url' => Storage::disk('public')->url($path),
                'sort_order' => $product->images()->count(),
                'is_primary' => $validated['is_primary'] ?? false,
            ]);
        });

        return response()->json($image, 201);
    }

    public function deleteImage(Product $product, ProductImage $image)
    {
        abort_if($image->product_id !== $product->id, 404);

        $path = str_replace(Storage::disk('public')->url(''), '', $image->url);
        Storage::disk('public')->delete($path);

        $image->delete();

        return response()->json(['message' => 'Imagen eliminada.']);
    }
}
