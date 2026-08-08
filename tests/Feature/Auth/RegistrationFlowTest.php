<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('tenant_documents');
    $this->seed(PermissionSeeder::class);
});

it('crea un tenant pendiente al registrarse', function () {
    $response = $this->postJson('/api/v1/register', [
        'business_name' => 'Tienda de Prueba',
        'nit' => '12345678',
        'business_type' => 'Tienda',
        'address' => 'Zona 1',
        'business_phone' => '55551234',
        'tax_regime' => 'general',
        'user_name' => 'Dueño Prueba',
        'dpi' => '1234567890101',
        'email' => 'dueno@prueba.com',
        'user_phone' => '55554321',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'dpi_frontal' => UploadedFile::fake()->image('dpi_frontal.jpg'),
        'dpi_trasero' => UploadedFile::fake()->image('dpi_trasero.jpg'),
        'rtu' => UploadedFile::fake()->create('rtu.pdf', 100),
        'comprobante_domicilio' => UploadedFile::fake()->create('recibo.pdf', 100),
    ]);

    $response->assertStatus(201);

    $tenant = Tenant::first();
    expect($tenant->status)->toBe('pendiente');
    expect(User::first()->tenant_id)->toBe($tenant->id);
});

it('rechaza el login de un tenant pendiente', function () {
    $tenant = Tenant::factory()->create(['status' => 'pendiente']);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'email' => 'pendiente@prueba.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'pendiente@prueba.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422);
});

it('permite el login de un tenant activo con credenciales correctas', function () {
    $tenant = Tenant::factory()->create(['status' => 'activo']);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'email' => 'activo@prueba.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'activo@prueba.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token', 'token_type', 'user', 'tenant']);
});