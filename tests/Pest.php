<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\PaymentMethod;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function setupSaleContext(array $tenantOverrides = []): array
{
    $tenant = Tenant::factory()->create(array_merge(['status' => 'activo'], $tenantOverrides));
    $branch = Branch::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    $user->givePermissionTo(['sales.create', 'sales.view']);

    $cashRegister = CashRegister::factory()->create(['branch_id' => $branch->id]);
    $cashSession = CashSession::factory()->create([
        'cash_register_id' => $cashRegister->id,
        'user_id' => $user->id,
        'status' => 'abierto',
        'opening_amount' => 0,
        'opened_at' => now(),
    ]);

    $paymentMethod = PaymentMethod::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Efectivo']);

    return compact('tenant', 'branch', 'user', 'cashSession', 'paymentMethod');
}
