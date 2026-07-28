<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterTenantRequest;
use App\Models\Tenant;
use App\Models\TenantDocument;
use App\Services\TenantRoleProvisioner;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function __construct( private readonly TenantRoleProvisioner $roleProvisioner) 
    {

    }


    public function store(RegisterTenantRequest $request)
    {
        $validated = $request->validated();

        $tenant = DB::transaction(function () use ($validated, $request) {
            $tenant = Tenant::create([
                'name' => $validated['business_name'],
                'nit' => $validated['nit'],
                'legal_name' => $validated['legal_name'] ?? null,
                'business_type' => $validated['business_type'],
                'address' => $validated['address'],
                'phone' => $validated['business_phone'],
                'tax_regime' => $validated['tax_regime'],
                'default_tax_rate' => $validated['tax_regime'] === 'pequeno_contribuyente' ? 5.00 : 12.00,
                'status' => 'pendiente',
            ]);

            $user = $tenant->users()->create([
                'name' => $validated['user_name'],
                'dpi' => $validated['dpi'],
                'email' => $validated['email'],
                'phone' => $validated['user_phone'],
                'password' => $validated['password'],
            ]);

            foreach (['dpi_frontal', 'dpi_trasero', 'rtu', 'comprobante_domicilio'] as $type) {
                $path = $request->file($type)->store("tenant-{$tenant->id}", 'tenant_documents');

                TenantDocument::create([
                    'tenant_id' => $tenant->id,
                    'type' => $type,
                    'file_path' => $path,
                    'uploaded_at' => now(),
                ]);
            }

            $this->roleProvisioner->provisionForTenant($tenant);
            $this->roleProvisioner->assignOwnerRole($user);

            return $tenant;
        });

        return response()->json([
            'message' => 'Registro recibido. Tu cuenta será revisada y activada pronto.',
            'tenant_id' => $tenant->id,
        ], 201);
    }
}
