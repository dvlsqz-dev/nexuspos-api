<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\Stock;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

it('devuelve el stock al anular una venta', function () {
    $ctx = setupSaleContext();

    $product = Product::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'price' => 30.00,
        'track_inventory' => true,
    ]);

    Stock::factory()->create([
        'product_id' => $product->id,
        'branch_id' => $ctx['branch']->id,
        'quantity' => 10,
    ]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($ctx['tenant']->id);
    $ctx['user']->givePermissionTo('sales.void');

    Sanctum::actingAs($ctx['user'], ['*']);

    $saleResponse = $this->postJson('/api/v1/sales', [
        'branch_id' => $ctx['branch']->id,
        'items' => [['product_id' => $product->id, 'quantity' => 3]],
        'payments' => [['payment_method_id' => $ctx['paymentMethod']->id, 'amount' => 90.00]],
    ]);

    $saleResponse->assertStatus(201);
    expect(Stock::where('product_id', $product->id)->first()->quantity)->toBe(7);

    $sale = Sale::first();
    $voidResponse = $this->postJson("/api/v1/sales/{$sale->id}/void");

    $voidResponse->assertStatus(200);
    expect(Stock::where('product_id', $product->id)->first()->quantity)->toBe(10);
    expect($sale->fresh()->status)->toBe('anulada');
});

it('no permite anular una venta ya anulada', function () {
    $ctx = setupSaleContext();

    $product = Product::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'price' => 30.00,
        'track_inventory' => false,
    ]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($ctx['tenant']->id);
    $ctx['user']->givePermissionTo('sales.void');

    Sanctum::actingAs($ctx['user'], ['*']);

    $this->postJson('/api/v1/sales', [
        'branch_id' => $ctx['branch']->id,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
        'payments' => [['payment_method_id' => $ctx['paymentMethod']->id, 'amount' => 30.00]],
    ])->assertStatus(201);

    $sale = Sale::first();

    $this->postJson("/api/v1/sales/{$sale->id}/void")->assertStatus(200);
    $secondVoid = $this->postJson("/api/v1/sales/{$sale->id}/void");

    $secondVoid->assertStatus(422);
});