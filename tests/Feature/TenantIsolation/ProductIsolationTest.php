<?php

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

it('no permite ver productos de otro tenant', function () {
    $tenantA = Tenant::factory()->create(['status' => 'activo']);
    $tenantB = Tenant::factory()->create(['status' => 'activo']);

    $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
    $productB = Product::factory()->create(['tenant_id' => $tenantB->id]);

    Permission::firstOrCreate(['name' => 'products.view', 'guard_name' => 'web']);

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenantA->id);
    $userA->givePermissionTo('products.view');

    Sanctum::actingAs($userA, ['*']);

    $response = $this->getJson("/api/v1/products/{$productB->id}");

    $response->assertStatus(404);
});