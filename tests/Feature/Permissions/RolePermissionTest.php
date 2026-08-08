<?php

use App\Models\Product;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

it('un cajero no puede ver reportes', function () {
    $ctx = setupSaleContext();

    Sanctum::actingAs($ctx['user'], ['*']);

    $response = $this->getJson('/api/v1/reports/sales-summary');

    $response->assertStatus(403);
});

it('un cajero no puede gestionar usuarios del tenant', function () {
    $ctx = setupSaleContext();

    Sanctum::actingAs($ctx['user'], ['*']);

    $response = $this->postJson('/api/v1/tenant/users', [
        'name' => 'Nuevo Usuario',
        'email' => 'nuevo@prueba.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'cajero',
    ]);

    $response->assertStatus(403);
});

it('un admin si puede ver reportes y gestionar usuarios', function () {
    $ctx = setupSaleContext();

    app(App\Services\TenantRoleProvisioner::class)->provisionForTenant($ctx['tenant']);

    app(PermissionRegistrar::class)->setPermissionsTeamId($ctx['tenant']->id);
    $ctx['user']->givePermissionTo(['reports.view', 'users.manage']);

    Sanctum::actingAs($ctx['user'], ['*']);

    $this->getJson('/api/v1/reports/sales-summary')->assertStatus(200);

    $this->postJson('/api/v1/tenant/users', [
        'name' => 'Nuevo Usuario',
        'email' => 'nuevo@prueba.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'cajero',
    ])->assertStatus(201);
});