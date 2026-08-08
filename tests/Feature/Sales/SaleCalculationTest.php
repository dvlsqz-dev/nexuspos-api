<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Stock;
use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\PaymentMethod;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

it('calcula correctamente el total cuando el precio incluye IVA', function () {
    $ctx = setupSaleContext(['prices_include_tax' => true, 'default_tax_rate' => 12.00]);

    $product = Product::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'price' => 112.00, // ya incluye 12% de IVA
        'tax_rate' => null,
        'track_inventory' => true,
    ]);

    Stock::factory()->create([
        'product_id' => $product->id,
        'branch_id' => $ctx['branch']->id,
        'quantity' => 10,
    ]);

    Sanctum::actingAs($ctx['user'], ['*']);

    $response = $this->postJson('/api/v1/sales', [
        'branch_id' => $ctx['branch']->id,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
        'payments' => [
            ['payment_method_id' => $ctx['paymentMethod']->id, 'amount' => 112.00],
        ],
    ]);

    $response->assertStatus(201);

    $sale = Sale::first();
    expect((float) $sale->subtotal)->toBe(100.00);
    expect((float) $sale->tax)->toBe(12.00);
    expect((float) $sale->total)->toBe(112.00);

    $stock = Stock::where('product_id', $product->id)->first();
    expect($stock->quantity)->toBe(9);
});

it('rechaza una venta cuando no hay stock suficiente', function () {
    $ctx = setupSaleContext();

    $product = Product::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'price' => 50.00,
        'track_inventory' => true,
    ]);

    Stock::factory()->create([
        'product_id' => $product->id,
        'branch_id' => $ctx['branch']->id,
        'quantity' => 2,
    ]);

    Sanctum::actingAs($ctx['user'], ['*']);

    $response = $this->postJson('/api/v1/sales', [
        'branch_id' => $ctx['branch']->id,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 5],
        ],
        'payments' => [
            ['payment_method_id' => $ctx['paymentMethod']->id, 'amount' => 250.00],
        ],
    ]);

    $response->assertStatus(422);
    expect(Sale::count())->toBe(0);
});

it('genera sale_number correlativo por tenant, no global', function () {
    $ctxA = setupSaleContext();
    $ctxB = setupSaleContext();

    foreach ([$ctxA, $ctxB] as $ctx) {
        $product = Product::factory()->create([
            'tenant_id' => $ctx['tenant']->id,
            'price' => 20.00,
            'track_inventory' => false,
        ]);

        Sanctum::actingAs($ctx['user'], ['*']);

        $this->postJson('/api/v1/sales', [
            'branch_id' => $ctx['branch']->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payments' => [['payment_method_id' => $ctx['paymentMethod']->id, 'amount' => 20.00]],
        ])->assertStatus(201);
    }

    $saleA = Sale::where('tenant_id', $ctxA['tenant']->id)->first();
    $saleB = Sale::where('tenant_id', $ctxB['tenant']->id)->first();

    expect($saleA->sale_number)->toBe('000001');
    expect($saleB->sale_number)->toBe('000001');
});