<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantDocument;
use App\Services\TenantActivationService;
use Illuminate\Support\Facades\Storage;

class TenantApprovalController extends Controller
{
    public function __construct( private readonly TenantActivationService $activationService ) 
    {

    }

    public function pending()
    {
        $tenants = Tenant::where('status', 'pendiente')
            ->with(['users', 'documents'])
            ->get();

        return response()->json($tenants);
    }

    public function showDocument(Tenant $tenant, TenantDocument $document)
    {
        abort_if($document->tenant_id !== $tenant->id, 404);

        return Storage::disk('tenant_documents')->response($document->file_path);
    }

    public function approve(Tenant $tenant)
    {
        $this->activationService->activate($tenant);

        return response()->json([
            'message' => 'Tenant aprobado.',
            'tenant' => $tenant->fresh('branches'),
        ]);
    }

    public function reject(Tenant $tenant)
    {
        $tenant->update(['status' => 'rechazado']);

        return response()->json([
            'message' => 'Tenant rechazado.',
            'tenant' => $tenant,
        ]);
    }
}
